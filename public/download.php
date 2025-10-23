<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

start_session();

$token = trim($_GET['token'] ?? '');
$downloadAction = isset($_GET['download']);
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

function fetch_file_by_token(string $token): ?array
{
    if ($token === '') {
        return null;
    }

    $db = get_db_connection();
    $stmt = $db->prepare('SELECT id, title, file_name, stored_path, token, upload_time, expiry_time, file_size, mime_type, download_count FROM files WHERE token = ? LIMIT 1');

    if (!$stmt) {
        throw new RuntimeException('Database error: ' . $db->error);
    }

    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $file = $result->fetch_assoc() ?: null;
    $stmt->close();

    return $file;
}

function remove_file_and_record(array $file): void
{
    $fullPath = realpath(__DIR__ . '/' . $file['stored_path']);
    if ($fullPath && str_starts_with($fullPath, realpath(__DIR__ . '/uploads'))) {
        @unlink($fullPath);
    }

    $db = get_db_connection();
    $stmt = $db->prepare('DELETE FROM files WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $file['id']);
        $stmt->execute();
        $stmt->close();
    }
}

if ($token !== '' && $downloadAction) {
    try {
        $file = fetch_file_by_token($token);
    } catch (Throwable $e) {
        http_response_code(500);
        echo 'An error occurred.';
        exit;
    }

    if (!$file) {
        http_response_code(404);
        echo 'File not found.';
        exit;
    }

    $expiry = new DateTimeImmutable($file['expiry_time']);
    $now = new DateTimeImmutable('now');

    if ($expiry <= $now) {
        remove_file_and_record($file);
        http_response_code(410);
        echo 'This file has expired and is no longer available.';
        exit;
    }

    $fullPath = realpath(__DIR__ . '/' . $file['stored_path']);
    $uploadsRoot = realpath(__DIR__ . '/uploads');

    if ($fullPath === false || $uploadsRoot === false || !str_starts_with($fullPath, $uploadsRoot) || !is_file($fullPath)) {
        http_response_code(404);
        echo 'File not found.';
        exit;
    }

    $downloadName = basename($file['file_name']);
    $safeName = str_replace('"', '', $downloadName);

    header('Content-Description: File Transfer');
    header('Content-Type: ' . ($file['mime_type'] ?: 'application/octet-stream'));
    header('Content-Disposition: attachment; filename="' . addcslashes($safeName, "\\\"") . '"; filename*=UTF-8\'\'' . rawurlencode($safeName));
    header('Expires: 0');
    header('Cache-Control: no-store, must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . (string) $file['file_size']);
    header('Accept-Ranges: none');

    $shouldIncrement = $requestMethod === 'GET' && empty($_SERVER['HTTP_RANGE'] ?? '');

    readfile($fullPath);

    if ($shouldIncrement) {
        $fingerprint = hash('sha256', implode('|', [
            (string) $file['id'],
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? '',
        ]));
        $requestSignature = hash('sha256', implode('|', [
            $_SERVER['REQUEST_URI'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $_SERVER['HTTP_ACCEPT'] ?? '',
            $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '',
        ]));
        $now = microtime(true);
        $minInterval = 30.0; // seconds

        if (!isset($_SESSION['recent_download_hits']) || !is_array($_SESSION['recent_download_hits'])) {
            $_SESSION['recent_download_hits'] = [];
        }

        $recentRecord = $_SESSION['recent_download_hits'][$fingerprint] ?? null;
        $lastTimestamp = is_array($recentRecord) ? (float) ($recentRecord['timestamp'] ?? 0.0) : 0.0;
        $lastSignature = is_array($recentRecord) ? (string) ($recentRecord['signature'] ?? '') : '';

        $isDuplicate = ($now - $lastTimestamp) < $minInterval && $lastSignature === $requestSignature;

        if (!$isDuplicate) {
            $db = get_db_connection();
            $update = $db->prepare('UPDATE files SET download_count = download_count + 1 WHERE id = ?');
            if ($update) {
                $update->bind_param('i', $file['id']);
                $update->execute();
                $update->close();
                $_SESSION['recent_download_hits'][$fingerprint] = [
                    'timestamp' => $now,
                    'signature' => $requestSignature,
                ];
            }
        }

        // Clean up stale fingerprints to keep the session lean
        foreach ($_SESSION['recent_download_hits'] as $storedFingerprint => $record) {
            $timestamp = is_array($record) ? (float) ($record['timestamp'] ?? 0.0) : (float) $record;
            if (($now - $timestamp) > 300) { // 5 minutes
                unset($_SESSION['recent_download_hits'][$storedFingerprint]);
            }
        }
    }

    exit;
}

$pageTitle = 'Download file';
$fileRecord = null;
$errorMessage = null;

if ($token !== '') {
    try {
        $fileRecord = fetch_file_by_token($token);
    } catch (Throwable $e) {
        $errorMessage = 'Unable to load file metadata. Please try again later.';
    }

    if ($fileRecord) {
        $expiry = new DateTimeImmutable($fileRecord['expiry_time']);
        $now = new DateTimeImmutable('now');
        if ($expiry <= $now) {
            remove_file_and_record($fileRecord);
            $fileRecord = null;
            $errorMessage = 'This file has expired and is no longer available.';
        } else {
            if (!empty($fileRecord['title'])) {
                $pageTitle = 'Download ' . $fileRecord['title'];
            } else {
                $pageTitle = 'Download ' . $fileRecord['file_name'];
            }
        }
    } elseif (!$errorMessage) {
        $errorMessage = 'We could not find a file for that token.';
    }
}

ob_start();
?>
<section class="mx-auto w-full max-w-3xl">
    <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-xl dark:border-slate-800 dark:bg-slate-900">
        <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Retrieve a shared file</h1>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Enter a download token to load file details, or follow the direct link shared with you.</p>
        <form class="mt-6 flex flex-col gap-3 sm:flex-row" method="get">
            <input type="text" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES) ?>" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-brand focus:ring-brand dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100" placeholder="Enter download token" required>
            <button type="submit" class="rounded-full bg-brand px-6 py-3 text-sm font-semibold uppercase tracking-wide text-white transition hover:bg-brand-dark">Search</button>
        </form>
        <?php if ($errorMessage): ?>
            <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-500/40 dark:bg-red-500/10 dark:text-red-200">
                <?= htmlspecialchars($errorMessage, ENT_QUOTES) ?>
            </div>
        <?php endif; ?>

        <?php if ($fileRecord): ?>
            <?php
                $baseUrl = rtrim(BASE_URL, '/');
                $downloadPagePath = $baseUrl . '/download/' . rawurlencode($fileRecord['token']);
                $downloadFilePath = $downloadPagePath . '/file';
            ?>
            <div class="mt-8 space-y-4 rounded-3xl border border-slate-200 bg-slate-50 p-6 dark:border-slate-800 dark:bg-slate-800/60">
                <?php if (!empty($fileRecord['title'])): ?>
                    <div>
                        <p class="text-sm uppercase text-slate-500 dark:text-slate-400">Title</p>
                        <p class="text-lg font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($fileRecord['title'], ENT_QUOTES) ?></p>
                    </div>
                <?php endif; ?>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm uppercase text-slate-500 dark:text-slate-400">File name</p>
                        <p class="text-lg font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($fileRecord['file_name'], ENT_QUOTES) ?></p>
                    </div>
                    <div class="text-sm text-slate-600 dark:text-slate-300">Size: <?= format_bytes((int) $fileRecord['file_size']) ?></div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-sm uppercase text-slate-500 dark:text-slate-400">Uploaded</p>
                        <p class="text-sm text-slate-700 dark:text-slate-200"><?= format_datetime(new DateTimeImmutable($fileRecord['upload_time'])) ?></p>
                    </div>
                    <div>
                        <p class="text-sm uppercase text-slate-500 dark:text-slate-400">Expires</p>
                        <?php $expiry = new DateTimeImmutable($fileRecord['expiry_time']); ?>
                        <p class="text-sm text-slate-700 dark:text-slate-200"><?= format_datetime($expiry) ?></p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Time remaining: <?= remaining_time_string($expiry) ?></p>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-xs font-medium uppercase tracking-wide text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                    Downloaded <?= (int) $fileRecord['download_count'] ?> <?= (int) $fileRecord['download_count'] === 1 ? 'time' : 'times' ?>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <a href="<?= htmlspecialchars($downloadPagePath, ENT_QUOTES) ?>" class="flex flex-1 items-center justify-center gap-2 rounded-full border border-slate-300 px-6 py-3 text-sm font-semibold uppercase tracking-wide text-slate-700 transition hover:border-brand hover:text-brand dark:border-slate-700 dark:text-slate-200 dark:hover:border-brand dark:hover:text-brand">
                        <i class="fa-solid fa-link"></i>
                        View download page
                    </a>
                    <a href="<?= htmlspecialchars($downloadFilePath, ENT_QUOTES) ?>" class="flex flex-1 items-center justify-center gap-2 rounded-full bg-brand px-6 py-3 text-sm font-semibold uppercase tracking-wide text-white shadow-lg shadow-brand/30 transition hover:bg-brand-dark">
                        <i class="fa-solid fa-download"></i>
                        Download file
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php
$pageContent = ob_get_clean();
include __DIR__ . '/includes/layout.php';
