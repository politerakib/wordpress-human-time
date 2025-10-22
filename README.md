# FileShare24

A responsive, production-ready file sharing platform built with PHP (MySQLi), TailwindCSS, and vanilla JavaScript. Upload any file, share a secure link instantly, and let the system clean everything up automatically after 24 hours.

## Features

- 🗂️ Drag-and-drop uploads with live progress indicator
- 🔗 Instant share links with copy-to-clipboard convenience
- ⏳ Automatic expiration and clean-up after 24 hours
- 🌓 Polished TailwindCSS UI with dark/light mode toggle
- 🔒 Hardened upload pipeline with MIME validation and CSRF protection
- 📥 Download portal that hides direct file paths and streams through PHP

## Requirements

- PHP 8.1+
- MySQL 5.7+/MariaDB 10+
- Apache with mod_php and mod_headers (for the provided `.htaccess` rules)

## Installation

1. **Clone the repository** and move the `public/` directory to your web root (or point your virtual host to it).
2. **Create the database** and table structure:
   ```sql
   SOURCE public/database.sql;
   ```
3. **Configure database credentials** by updating `public/config.php`:
   ```php
   const DB_HOST = 'localhost';
   const DB_USER = 'your_db_user';
   const DB_PASS = 'your_db_password';
   const DB_NAME = 'your_db_name';
   const BASE_URL = 'https://your-domain.com';
   ```
4. Ensure the `public/uploads/` directory is writable by the web server user.
5. (Optional) Schedule the clean-up script to remove expired files:
   ```bash
   php /path/to/public/delete_old.php
   ```
   Run this script every hour (or as desired) via cron.

## Usage

- Visit the homepage to drag-and-drop a file or browse from disk.
- Share the generated download URL with recipients.
- Recipients see a branded download page with file metadata and expiry details. Files stream through PHP and never expose the underlying storage path.

## Security Considerations

- Uploaded file names are sanitized and storage names are randomized.
- MIME types are validated to block executable uploads.
- CSRF tokens protect the upload form, and session data never leaves the server.
- `.htaccess` rules disable directory indexing and script execution from the `uploads/` directory.

## Folder Structure

```
public/
├── assets/
│   └── js/
│       └── app.js
├── delete_old.php
├── download.php
├── includes/
│   ├── db.php
│   ├── footer.php
│   ├── functions.php
│   ├── header.php
│   └── layout.php
├── index.php
├── upload.php
├── uploads/
│   └── .gitkeep
├── .htaccess
├── config.php
└── database.sql
```

## License

This project is provided as-is without warranty. Feel free to adapt it to your needs.
