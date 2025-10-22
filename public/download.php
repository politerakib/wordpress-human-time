<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

start_session();

$token = trim($_GET['token'] ?? '');
$downloadAction = isset($_GET['download']);

function fetch_file_by_token(string $token): ?array
{
    if ($token === '') {
        return null;
    }

    $db = get_db_connection();
    $stmt = $db->prepare('SELECT id, file_name, stored_path, token, upload_time, expiry_time, file_size, mime_type FROM files WHERE token = ? LIMIT 1');

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
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . (string) $file['file_size']);

    readfile($fullPath);
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
            $pageTitle = 'Download ' . $fileRecord['file_name'];
        }
    } elseif (!$errorMessage) {
        $errorMessage = 'We could not find a file for that token.';
    }
}

ob_start();
?>
<section class="mx-auto w-full max-w-3xl">
    <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-xl dark:border-slate-800 dark:bg-slate-900">
        <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Find your file</h1>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Enter your download token to retrieve file details, or open the direct link someone shared with you.</p>
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
            <div class="mt-8 space-y-4 rounded-3xl border border-slate-200 bg-slate-50 p-6 dark:border-slate-800 dark:bg-slate-800/60">
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
                        <p class="text-xs text-slate-500 dark:text-slate-400">Time left: <?= remaining_time_string($expiry) ?></p>
                    </div>
                </div>
                <a href="download.php?token=<?= urlencode($fileRecord['token']) ?>&download=1" class="flex items-center justify-center gap-2 rounded-full bg-brand px-6 py-3 text-sm font-semibold uppercase tracking-wide text-white shadow-lg shadow-brand/30 transition hover:bg-brand-dark">
                    <i class="fa-solid fa-download"></i>
                    Download file
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php
$pageContent = ob_get_clean();
include __DIR__ . '/includes/layout.php';
