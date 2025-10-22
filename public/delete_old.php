<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

$deletedCount = 0;
$filesRemoved = 0;

try {
    $db = get_db_connection();
    $query = "SELECT id, stored_path FROM files WHERE expiry_time <= NOW()";
    $result = $db->query($query);

    if ($result) {
        $uploadsRoot = realpath(__DIR__ . '/uploads');

        while ($row = $result->fetch_assoc()) {
            $fullPath = realpath(__DIR__ . '/' . $row['stored_path']);
            if ($fullPath && $uploadsRoot && str_starts_with($fullPath, $uploadsRoot) && is_file($fullPath)) {
                if (@unlink($fullPath)) {
                    $filesRemoved++;
                }
            }

            $stmt = $db->prepare('DELETE FROM files WHERE id = ?');
            if ($stmt) {
                $stmt->bind_param('i', $row['id']);
                if ($stmt->execute()) {
                    $deletedCount++;
                }
                $stmt->close();
            }
        }
        $result->free();
    }
} catch (Throwable $e) {
    fwrite(STDERR, 'Cleanup failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

echo sprintf("Expired records removed: %d\nFiles deleted: %d\n", $deletedCount, $filesRemoved);
