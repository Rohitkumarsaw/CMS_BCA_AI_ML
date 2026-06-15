<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('about_settings.php');
}

$action = $_POST['action'] ?? '';
header('Content-Type: application/json');

switch ($action) {

    case 'update_about':
        requireCSRF();
        $aboutText = $_POST['about_text'] ?? '';

        $stmt = $pdo->prepare("SELECT id FROM system_settings LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch();

        if ($row) {
            $stmt = $pdo->prepare("UPDATE system_settings SET about_text = ? WHERE id = ?");
            $stmt->execute([$aboutText, $row['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO system_settings (about_text) VALUES (?)");
            $stmt->execute([$aboutText]);
        }

        echo json_encode(['status' => 'success', 'message' => 'About section updated successfully!']);
        exit;

    case 'update_partner':
        requireCSRF();
        $partnerName = $_POST['partner_name'] ?? '';
        $partnerDetails = $_POST['partner_details'] ?? '';

        // Convert newlines to pipe for DB storage
        $partnerDetails = str_replace(["\r\n", "\r", "\n"], '|', $partnerDetails);

        $stmt = $pdo->prepare("SELECT id FROM system_settings LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch();

        if ($row) {
            $stmt = $pdo->prepare("UPDATE system_settings SET partner_name = ?, partner_details = ? WHERE id = ?");
            $stmt->execute([$partnerName, $partnerDetails, $row['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO system_settings (partner_name, partner_details) VALUES (?, ?)");
            $stmt->execute([$partnerName, $partnerDetails]);
        }

        echo json_encode(['status' => 'success', 'message' => 'Partner section updated successfully!']);
        exit;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        exit;
}
