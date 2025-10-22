CREATE TABLE IF NOT EXISTS files (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    file_name VARCHAR(255) NOT NULL,
    stored_path VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    upload_time DATETIME NOT NULL,
    expiry_time DATETIME NOT NULL,
    file_size BIGINT UNSIGNED NOT NULL,
    mime_type VARCHAR(128) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_files_expiry ON files (expiry_time);
