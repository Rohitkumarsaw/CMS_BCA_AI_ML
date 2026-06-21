<?php
/**
 * Backup & Restore Handler
 */

require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$action = $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];

if ($action === 'export') {
    // =============================================
    // BACKUP — Export full database as .sql
    // =============================================

    $tables = [];
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }

    $output = "-- ============================================\n";
    $output .= "-- CMS (BCA AI/ML) — Full Database Backup\n";
    $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $output .= "-- User ID: " . (int)$userId . "\n";
    $output .= "-- ============================================\n\n";
    $output .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

    foreach ($tables as $table) {
        // Drop table
        $output .= "DROP TABLE IF EXISTS `$table`;\n";

        // Create table
        $createStmt = $pdo->query("SHOW CREATE TABLE `$table`");
        $createRow = $createStmt->fetch(PDO::FETCH_NUM);
        $output .= $createRow[1] . ";\n\n";

        // Insert data
        $rowsStmt = $pdo->query("SELECT * FROM `$table`");
        $rows = $rowsStmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($rows)) {
            $columns = array_keys($rows[0]);
            $colNames = implode('`, `', $columns);

            foreach ($rows as $row) {
                $vals = [];
                foreach ($columns as $col) {
                    $val = $row[$col];
                    if ($val === null) {
                        $vals[] = 'NULL';
                    } else {
                        $vals[] = $pdo->quote($val);
                    }
                }
                $valStr = implode(', ', $vals);
                $output .= "INSERT INTO `$table` (`$colNames`) VALUES ($valStr);\n";
            }
            $output .= "\n";
        }
    }

    $output .= "SET FOREIGN_KEY_CHECKS = 1;\n\n";
    $output .= "-- Backup complete\n";

    // Force download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="bca_portal_backup_' . date('Ymd_His') . '.sql"');
    header('Content-Length: ' . strlen($output));
    header('Pragma: no-cache');
    header('Expires: 0');
    notifyEmail('Backup', 'exported');
    echo $output;
    exit;
}

if ($action === 'import') {
    // =============================================
    // RESTORE — Upload and execute .sql file
    // =============================================

    set_time_limit(0);
    ini_set('memory_limit', '256M');

    $response = ['success' => false, 'message' => ''];

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['sql_file'])) {
        $response['message'] = 'No file uploaded.';
        echo json_encode($response);
        exit;
    }

    $file = $_FILES['sql_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds server upload limit.',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form size limit.',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was selected.',
        ];
        $response['message'] = $uploadErrors[$file['error']] ?? 'Upload failed with unknown error.';
        echo json_encode($response);
        exit;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'sql') {
        $response['message'] = 'Invalid file format. Only .sql files are allowed.';
        echo json_encode($response);
        exit;
    }

    $content = file_get_contents($file['tmp_name']);
    if ($content === false || trim($content) === '') {
        $response['message'] = 'File is empty or unreadable.';
        echo json_encode($response);
        exit;
    }

    // Split SQL into statements, respecting string literals
    $statements = [];
    $len = strlen($content);
    $i = 0;
    $buf = '';
    $inSingle = false;
    $inDouble = false;
    $inLineComment = false;
    $inBlockComment = false;

    while ($i < $len) {
        $ch = $content[$i];
        $next = ($i + 1 < $len) ? $content[$i + 1] : '';

        // Line comment --
        if (!$inBlockComment && !$inSingle && !$inDouble && $ch === '-' && $next === '-') {
            $inLineComment = true;
            $buf .= '--';
            $i += 2;
            continue;
        }

        // Block comment /* */
        if (!$inLineComment && !$inSingle && !$inDouble && $ch === '/' && $next === '*') {
            $inBlockComment = true;
            $buf .= '/*';
            $i += 2;
            continue;
        }

        // End block comment
        if ($inBlockComment && $ch === '*' && $next === '/') {
            $inBlockComment = false;
            $buf .= '*/';
            $i += 2;
            continue;
        }

        // End line comment
        if ($inLineComment && ($ch === "\n" || $ch === "\r")) {
            $inLineComment = false;
            $buf .= $ch;
            $i++;
            continue;
        }

        // Toggle single-quote string (respect escapes)
        if (!$inBlockComment && !$inLineComment && !$inDouble && $ch === "'") {
            if ($i === 0 || $content[$i - 1] !== '\\') {
                $inSingle = !$inSingle;
            }
            $buf .= $ch;
            $i++;
            continue;
        }

        // Toggle double-quote string (respect escapes)
        if (!$inBlockComment && !$inLineComment && !$inSingle && $ch === '"') {
            if ($i === 0 || $content[$i - 1] !== '\\') {
                $inDouble = !$inDouble;
            }
            $buf .= $ch;
            $i++;
            continue;
        }

        // Statement terminator (only outside strings and comments)
        if (!$inSingle && !$inDouble && !$inLineComment && !$inBlockComment && $ch === ';') {
            $stmt = trim($buf);
            if ($stmt !== '') {
                $statements[] = $stmt;
            }
            $buf = '';
            $i++;
            continue;
        }

        // Skip comment text entirely (don't add to buffer)
        if ($inLineComment || $inBlockComment) {
            $i++;
            continue;
        }

        $buf .= $ch;
        $i++;
    }

    // Remaining buffer after last semicolon
    $stmt = trim($buf);
    if ($stmt !== '') {
        $statements[] = $stmt;
    }

    if (empty($statements)) {
        $response['message'] = 'No valid SQL statements found in the file.';
        echo json_encode($response);
        exit;
    }

    // Execute all statements
    try {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Disable FK checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

        foreach ($statements as $stmt) {
            $pdo->exec($stmt);
        }

        // Re-enable FK checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

        $response['success'] = true;
        $response['message'] = 'Database restored successfully. All data has been imported.';
        notifyEmail('Backup', 'imported');
    } catch (PDOException $e) {
        $response['message'] = 'Restore failed: ' . $e->getMessage();
    }

    echo json_encode($response);
    exit;
}

// Invalid action
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid action.']);
