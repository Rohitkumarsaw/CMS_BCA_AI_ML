<?php
/**
 * CMS (BCA AI/ML) - Configuration
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'bca_portal_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'CMS (BCA AI/ML)');
define('SITE_URL', 'http://localhost/bca-portal');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB

define('ALLOWED_NOTE_TYPES', ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'mp4']);
define('ALLOWED_HOMEWORK_TYPES', ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'zip']);
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_CERT_TYPES', ['pdf', 'jpg', 'jpeg', 'png']);
define('ALLOWED_RECEIPT_TYPES', ['pdf', 'jpg', 'jpeg', 'png']);
