<?php
// =============================================================
//  FILE: api/chat.php
//  FUNGSI: Backend Asisten Berita — mendukung MULTI PROVIDER
//          (Google Gemini & OpenRouter).
//
//  ENDPOINT: POST /api/chat.php
//
//  PROVIDER DIDETEKSI OTOMATIS dari api/config.php
// =============================================================

ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// =============================================================
//  LOAD KONFIGURASI
// =============================================================
$config = require __DIR__ . '/config.php';

$provider = $config['provider'] ?? 'gemini';

// Validasi API key sesuai provider
if ($provider === 'openrouter') {
    $apiKey = $config['openrouter_api_key'] ?? '';
    $model = $config['openrouter_model'] ?? 'anthropic/claude-sonnet-4-20250514';
    $keyName = 'OpenRouter';
    $setupUrl = 'https://openrouter.ai/keys';
} else {
    $apiKey = $config['gemini_api_key'] ?? '';
    $model = $config['gemini_model'] ?? 'gemini-2.0-flash';
    $keyName = 'Google Gemini';
    $setupUrl = 'https://aistudio.google.com/apikey';
}

if (empty($apiKey) || $apiKey === 'isi-disini') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => "API key {$keyName} belum diatur!",
        'setup_instructions' => [
            'provider' => $provider,
            'langkah' => "Dapatkan API key gratis dari {$keyName}:",
            'url' => $setupUrl,
            'file' => 'Buka file api/config.php dan isi API key kamu'
        ]
    ]);
    exit;
}

// =============================================================
//  PARSE INPUT
// =============================================================

$message = '';
$history = [];
$uploadedFiles = [];

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (strpos($contentType, 'multipart/form-data') !== false) {
    $message = $_POST['message'] ?? '';
    $history = json_decode($_POST['history'] ?? '[]', true) ?: [];
    if (isset($_FILES['files']) && is_array($_FILES['files'])) {
        $uploadedFiles = $_FILES['files'];
    }
} else {
    $input = json_decode(file_get_contents('php://input'), true);
    $message = $input['message'] ?? '';
    $history = $input['history'] ?? [];
}

if (empty(trim($message)) && empty($uploadedFiles)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Pesan tidak boleh kosong']);
    exit;
}

$message = mb_substr($message, 0, 10000);

// =============================================================
//  PROSES FILE UPLOAD
// =============================================================

$imageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp'];
$textTypes = [
    'text/plain', 'text/html', 'text/css', 'text/javascript', 'text/csv',
    'application/json', 'application/xml', 'application/x-httpd-php',
    'application/javascript',
];
$codeExtensions = [
    'php', 'js', 'html', 'css', 'py', 'java', 'cpp', 'c', 'h', 'rb',
    'go', 'rs', 'ts', 'jsx', 'tsx', 'json', 'xml', 'yaml', 'yml',
    'md', 'sql', 'sh', 'bash', 'ps1', 'bat', 'ini', 'cfg', 'conf',
    'log', 'env', 'gitignore', 'makefile', 'dockerfile', 'kt', 'swift',
    'r', 'lua', 'dart', 'scala', 'perl', 'pl', 'vue', 'svelte', 'prisma',
];
$docTypes = ['application/pdf'];

$tempDir = __DIR__ . '/../uploads/chat/';
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0755, true);
}

$processedFiles = [];
$totalFiles = 0;
$maxFiles = 20;
$maxTotalSize = 50 * 1024 * 1024;
$totalSize = 0;

if (!empty($uploadedFiles)) {
    $fileArray = [];
    if (is_array($uploadedFiles['name'])) {
        $count = count($uploadedFiles['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($uploadedFiles['error'][$i] === UPLOAD_ERR_OK) {
                $fileArray[] = [
                    'name' => $uploadedFiles['name'][$i],
                    'tmp_name' => $uploadedFiles['tmp_name'][$i],
                    'type' => $uploadedFiles['type'][$i],
                    'error' => $uploadedFiles['error'][$i],
                    'size' => $uploadedFiles['size'][$i],
                ];
            }
        }
    } elseif ($uploadedFiles['error'] === UPLOAD_ERR_OK) {
        $fileArray[] = $uploadedFiles;
    }

    if (count($fileArray) > $maxFiles) {
        $fileArray = array_slice($fileArray, 0, $maxFiles);
    }

    foreach ($fileArray as $file) {
        $totalSize += $file['size'];
        if ($totalSize > $maxTotalSize) continue;

        $origName = basename($file['name']);
        $mimeType = $file['type'];
        $tmpPath = $file['tmp_name'];
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        if (empty($mimeType) || $mimeType === 'application/octet-stream') {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $tmpPath);
            finfo_close($finfo);
        }

        $isCodeFile = in_array($ext, $codeExtensions);
        $safeName = uniqid('chat_') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $origName);
        $destPath = $tempDir . $safeName;

        if (!move_uploaded_file($tmpPath, $destPath)) continue;

        $fileInfo = [
            'orig_name' => $origName,
            'path' => $destPath,
            'mime' => $mimeType,
            'ext' => $ext,
            'size' => $file['size'],
        ];

        // === IMAGES ===
        if (in_array($mimeType, $imageTypes) || in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'])) {
            $imgData = base64_encode(file_get_contents($destPath));
            if (in_array($ext, ['jpg', 'jpeg'])) $imgMime = 'image/jpeg';
            elseif ($ext === 'png') $imgMime = 'image/png';
            elseif ($ext === 'gif') $imgMime = 'image/gif';
            elseif ($ext === 'webp') $imgMime = 'image/webp';
            else $imgMime = $mimeType;

            $fileInfo['type'] = 'image';
            $fileInfo['base64'] = $imgData;
            $fileInfo['img_mime'] = $imgMime;

        // === TEXT / CODE ===
        } elseif ($isCodeFile || in_array($mimeType, $textTypes) || $ext === 'txt' || $ext === 'md') {
            $content = file_get_contents($destPath);
            if (strlen($content) > 50000) {
                $content = mb_substr($content, 0, 50000) . "\n\n[... file terpotong, terlalu panjang]";
            }
            $fileInfo['type'] = 'text';
            $fileInfo['content'] = $content;
            $fileInfo['language'] = $ext;

        // === PDF / DOCUMENTS ===
        } elseif (in_array($mimeType, $docTypes) || $ext === 'pdf') {
            $fileInfo['type'] = 'document';
            // Try to extract text from PDF using a simple approach
            $pdfContent = file_get_contents($destPath);
            // Simple PDF text extraction (strip PDF formatting)
            $text = '';
            if (preg_match_all('/\((.*?)\)/s', $pdfContent, $matches)) {
                $text = implode("\n", $matches[1]);
            }
            if (strlen($text) < 50) {
                $text = "[PDF: {$origName}] File PDF terlampir. Tidak bisa mengekstrak teks secara otomatis.";
            } else {
                $text = mb_substr($text, 0, 30000);
                $text = "[PDF: {$origName}]\n\n" . $text;
            }
            $fileInfo['type'] = 'text'; // Treat as text content for both providers
            $fileInfo['content'] = $text;
            $fileInfo['language'] = '';

        } else {
            $fileInfo['type'] = 'unsupported';
            $fileInfo['error'] = "Tipe file {$ext} tidak didukung.";
            $fileInfo['content'] = "[{$origName}] Binary file.";
        }

        $processedFiles[] = $fileInfo;
        $totalFiles++;
    }
}

// =============================================================
//  FUNGSI: KIRIM KE GEMINI API
// =============================================================

function sendToGemini($apiKey, $model, $message, $history, $processedFiles, $systemPrompt, $config) {
    $contents = [];

    foreach ($history as $msg) {
        $role = ($msg['role'] === 'user' || $msg['role'] === 'model') ? $msg['role'] : 'user';
        $contents[] = [
            'role' => $role,
            'parts' => [['text' => $msg['text'] ?? '']],
        ];
    }

    $userParts = [];

    if (!empty(trim($message))) {
        $userParts[] = ['text' => $message];
    }

    foreach ($processedFiles as $file) {
        if ($file['type'] === 'image' && isset($file['base64'])) {
            $userParts[] = [
                'inline_data' => [
                    'mime_type' => $file['img_mime'],
                    'data' => $file['base64'],
                ],
            ];
        } elseif ($file['type'] === 'text' && isset($file['content'])) {
            $userParts[] = [
                'text' => "\n\n--- FILE: {$file['orig_name']} ---\n```{$file['language']}\n{$file['content']}\n```\n",
            ];
        }
    }

    if (!empty($userParts)) {
        $contents[] = ['role' => 'user', 'parts' => $userParts];
    }

    $requestBody = [
        'contents' => $contents,
        'system_instruction' => ['parts' => [['text' => $systemPrompt]]],
        'generationConfig' => [
            'temperature' => $config['temperature'] ?? 0.7,
            'maxOutputTokens' => $config['max_output_tokens'] ?? 8192,
            'topP' => $config['top_p'] ?? 0.95,
            'topK' => $config['top_k'] ?? 40,
        ],
        'safetySettings' => [
            ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
            ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
            ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
            ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
        ],
    ];

    $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($requestBody),
        // 60 detik sudah lebih dari cukup untuk jawaban 900 token;
        // kalau lebih lama dianggap gangguan dan dialihkan ke model cadangan.
        CURLOPT_TIMEOUT => 60,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    // Jangan pakai curl_close(): di PHP 8.5+ fungsi itu sudah deprecated
    // dan bisa menyisakan pesan notice yang merusak output JSON.
    // Handle akan dibebaskan otomatis oleh garbage collector.

    if ($curlError) {
        return ['success' => false, 'error' => "Koneksi error: {$curlError}"];
    }

    $data = json_decode($response, true);

    if ($httpCode !== 200) {
        $errorMsg = $data['error']['message'] ?? 'Unknown error';
        return ['success' => false, 'error' => "Gemini Error ({$httpCode}): {$errorMsg}"];
    }

    $responseText = '';
    if (isset($data['candidates'][0]['content']['parts'])) {
        foreach ($data['candidates'][0]['content']['parts'] as $part) {
            if (isset($part['text'])) {
                $responseText .= $part['text'];
            }
        }
    }

    if (empty($responseText)) {
        $finishReason = $data['candidates'][0]['finishReason'] ?? '';
        if ($finishReason === 'SAFETY') {
            return ['success' => false, 'error' => 'Respons diblokir karena alasan keamanan.'];
        }
        return ['success' => false, 'error' => 'Tidak ada respons dari AI.'];
    }

    return [
        'success' => true,
        'response' => $responseText,
    ];
}

// =============================================================
//  FUNGSI: KIRIM KE OPENROUTER API
//  (OpenAI-compatible format — support banyak model)
// =============================================================

function sendToOpenRouter($apiKey, $model, $message, $history, $processedFiles, $systemPrompt, $config) {
    // Bangun messages array
    $messages = [];

    // System prompt
    $messages[] = ['role' => 'system', 'content' => $systemPrompt];

    // Conversation history
    foreach ($history as $msg) {
        $role = $msg['role'] === 'model' ? 'assistant' : 'user';
        $messages[] = ['role' => $role, 'content' => $msg['text'] ?? ''];
    }

    // Bangun content untuk pesan user saat ini (bisa multimodal)
    $userContent = [];

    // Text message
    if (!empty(trim($message))) {
        $userContent[] = ['type' => 'text', 'text' => $message];
    }

    // Files
    foreach ($processedFiles as $file) {
        if ($file['type'] === 'image' && isset($file['base64'])) {
            // Image as base64 data URL
            $dataUrl = "data:{$file['img_mime']};base64,{$file['base64']}";
            $userContent[] = [
                'type' => 'image_url',
                'image_url' => ['url' => $dataUrl],
            ];
        } elseif ($file['type'] === 'text' && isset($file['content'])) {
            $userContent[] = [
                'type' => 'text',
                'text' => "\n\n--- FILE: {$file['orig_name']} ---\n```{$file['language']}\n{$file['content']}\n```\n",
            ];
        }
    }

    // Send content to OpenRouter
    if (count($userContent) === 1 && $userContent[0]['type'] === 'text') {
        // Single text message → send as plain string (simpler)
        $messages[] = ['role' => 'user', 'content' => $userContent[0]['text']];
    } elseif (count($userContent) >= 1) {
        // Multiple parts or non-text content → send structured array
        $messages[] = ['role' => 'user', 'content' => $userContent];
    } else {
        // Should not happen
        $messages[] = ['role' => 'user', 'content' => $message ?: '...'];
    }

    $requestBody = [
        'model' => $model,
        'messages' => $messages,
        'max_tokens' => $config['max_output_tokens'] ?? 8192,
        'temperature' => $config['temperature'] ?? 0.7,
        'top_p' => $config['top_p'] ?? 0.95,
    ];

    $apiUrl = 'https://openrouter.ai/api/v1/chat/completions';

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            "Authorization: Bearer {$apiKey}",
            'HTTP-Referer: http://localhost/website-berita',
            'X-Title: KimiNEWS',
        ],
        CURLOPT_POSTFIELDS => json_encode($requestBody),
        CURLOPT_TIMEOUT => 120,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    // Lihat catatan di sendToGemini(): curl_close() sengaja tidak dipakai
    // karena deprecated di PHP 8.5+ dan bisa merusak output JSON.

    if ($curlError) {
        return ['success' => false, 'error' => "Koneksi error: {$curlError}"];
    }

    $data = json_decode($response, true);

    if ($httpCode !== 200) {
        $errorMsg = $data['error']['message'] ?? $data['error']['code'] ?? 'Unknown error';
        return ['success' => false, 'error' => "OpenRouter Error ({$httpCode}): {$errorMsg}"];
    }

    $responseText = $data['choices'][0]['message']['content'] ?? '';

    if (empty($responseText)) {
        $finishReason = $data['choices'][0]['finish_reason'] ?? '';
        if ($finishReason === 'length') {
            return ['success' => false, 'error' => 'Respons terlalu panjang, coba tanya yang lebih spesifik.'];
        }
        return ['success' => false, 'error' => 'Tidak ada respons dari AI.'];
    }

    return [
        'success' => true,
        'response' => $responseText,
    ];
}

// =============================================================
//  EKSEKUSI — Pilih provider otomatis
// =============================================================

try {
    // Model utama + daftar cadangan. Google kadang mengembalikan
    // 503 "high demand" pada model tertentu; kalau itu terjadi kita
    // otomatis coba model cadangan supaya chat tetap terjawab.
    $fallbackList = $provider === 'openrouter'
        ? ($config['openrouter_fallback_models'] ?? [])
        : ($config['gemini_fallback_models'] ?? []);

    $attemptModels = array_unique(array_merge([$model], array_values($fallbackList)));

    foreach ($attemptModels as $attemptModel) {
        if ($provider === 'openrouter') {
            $result = sendToOpenRouter(
                $apiKey,
                $attemptModel,
                $message,
                $history,
                $processedFiles,
                $config['system_prompt'],
                $config
            );
        } else {
            $result = sendToGemini(
                $apiKey,
                $attemptModel,
                $message,
                $history,
                $processedFiles,
                $config['system_prompt'],
                $config
            );
        }

        if ($result['success']) {
            break;
        }

        // Gangguan sementara (overload / rate limit / koneksi) saja
        // yang dicoba ulang dengan model berikutnya. Error lain
        // (misalnya key salah, isi ditolak) langsung dilaporkan.
        $isTransient = (bool) preg_match(
            '/\b(429|500|502|503|504)\b|high demand|Koneksi error|timed? ?out/i',
            $result['error'] ?? ''
        );

        if (!$isTransient) {
            break;
        }
    }
} catch (\Throwable $e) {
    $result = ['success' => false, 'error' => 'Internal error: ' . $e->getMessage()];
}

// =============================================================
//  CLEANUP
// =============================================================
foreach ($processedFiles as $file) {
    if (isset($file['path']) && file_exists($file['path'])) {
        @unlink($file['path']);
    }
}

// =============================================================
//  RESPON
// =============================================================
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
