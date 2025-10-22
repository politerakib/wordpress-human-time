<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/json');

start_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
    exit;
}

if (!isset($_FILES['file']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No file uploaded.']);
    exit;
}

$file = $_FILES['file'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File upload failed with error code ' . $file['error']]);
    exit;
}

if ($file['size'] > MAX_FILE_SIZE_BYTES) {
    http_response_code(413);
    echo json_encode(['success' => false, 'message' => 'File exceeds the maximum allowed size of 100MB.']);
    exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);

if ($mimeType === false || is_forbidden_mime($mimeType)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'The uploaded file type is not allowed.']);
    exit;
}

$originalName = sanitize_filename($file['name']);
$extension = pathinfo($originalName, PATHINFO_EXTENSION);
$randomName = bin2hex(random_bytes(16));
$storedFileName = $extension ? $randomName . '.' . strtolower($extension) : $randomName;

$uploadDirectory = __DIR__ . '/uploads/';
if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to prepare upload directory.']);
    exit;
}

$storedPath = $uploadDirectory . $storedFileName;

if (!move_uploaded_file($file['tmp_name'], $storedPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to store uploaded file.']);
    exit;
}

$uploadedAt = new DateTimeImmutable('now');
$expiryAt = $uploadedAt->add(new DateInterval('PT' . FILE_RETENTION_SECONDS . 'S'));
$token = generate_token(18);

try {
    $db = get_db_connection();
    $stmt = $db->prepare('INSERT INTO files (file_name, stored_path, token, upload_time, expiry_time, file_size, mime_type) VALUES (?, ?, ?, ?, ?, ?, ?)');

    if (!$stmt) {
        throw new RuntimeException('Database statement preparation failed: ' . $db->error);
    }

    $relativePath = 'uploads/' . $storedFileName;
    $uploadTime = $uploadedAt->format('Y-m-d H:i:s');
    $expiryTime = $expiryAt->format('Y-m-d H:i:s');
    $fileSize = (int) $file['size'];

    $stmt->bind_param('sssssis', $originalName, $relativePath, $token, $uploadTime, $expiryTime, $fileSize, $mimeType);

    if (!$stmt->execute()) {
        throw new RuntimeException('Failed to save upload metadata: ' . $stmt->error);
    }

    $stmt->close();
} catch (Throwable $e) {
    @unlink($storedPath);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

$downloadUrl = rtrim(BASE_URL, '/') . '/download.php?token=' . urlencode($token);

$response = [
    'success' => true,
    'message' => 'File uploaded successfully.',
    'data' => [
        'fileName' => $originalName,
        'fileSize' => format_bytes((int) $file['size']),
        'expiresAt' => $expiryAt->format(DateTimeInterface::ATOM),
        'downloadUrl' => $downloadUrl,
        'token' => $token,
    ],
];

echo json_encode($response);
