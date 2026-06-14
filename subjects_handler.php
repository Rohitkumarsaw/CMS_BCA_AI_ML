<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $semester = intval($_POST['semester'] ?? 1);
    $subjectName = sanitize($_POST['subject_name'] ?? '');

    if (empty($subjectName)) {
        setFlashMessage('danger', 'Subject name is required.');
    } else {
        $stmt = $pdo->prepare("SELECT id FROM user_subjects WHERE user_id = ? AND semester = ? AND subject_name = ?");
        $stmt->execute([$userId, $semester, $subjectName]);
        if ($stmt->fetch()) {
            setFlashMessage('danger', 'Subject already exists for this semester.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO user_subjects (user_id, semester, subject_name) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $semester, $subjectName]);
            setFlashMessage('success', 'Subject added successfully.');
        }
    }
    redirect('manage_subjects.php?semester=' . $semester);

} elseif ($action === 'edit') {
    $id = intval($_POST['id'] ?? 0);
    $subjectName = sanitize($_POST['subject_name'] ?? '');

    if ($id <= 0 || empty($subjectName)) {
        setFlashMessage('danger', 'Invalid request.');
    } else {
        $stmt = $pdo->prepare("SELECT semester FROM user_subjects WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        $sub = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$sub) {
            setFlashMessage('danger', 'Subject not found.');
        } else {
            $stmt = $pdo->prepare("SELECT id FROM user_subjects WHERE user_id = ? AND semester = ? AND subject_name = ? AND id != ?");
            $stmt->execute([$userId, $sub['semester'], $subjectName, $id]);
            if ($stmt->fetch()) {
                setFlashMessage('danger', 'Subject name already exists in this semester.');
            } else {
                $stmt = $pdo->prepare("UPDATE user_subjects SET subject_name = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$subjectName, $id, $userId]);
                
                $stmt = $pdo->prepare("UPDATE syllabus SET subject = ? WHERE user_id = ? AND subject = (SELECT subject_name FROM user_subjects WHERE id = ?)");
                $stmt->execute([$subjectName, $userId, $id]);
                // Can't use subquery like that, do it differently
                $stmt = $pdo->prepare("SELECT subject_name FROM user_subjects WHERE id = ? AND user_id = ?");
                $stmt->execute([$id, $userId]);
                $oldName = $stmt->fetchColumn();
                if ($oldName) {
                    $stmt = $pdo->prepare("UPDATE syllabus SET subject = ? WHERE user_id = ? AND semester = ? AND subject = ?");
                    $stmt->execute([$subjectName, $userId, $sub['semester'], $oldName]);
                }
                
                setFlashMessage('success', 'Subject updated successfully.');
            }
        }
    }
    redirect('manage_subjects.php');

} elseif ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);

    if ($id <= 0) {
        setFlashMessage('danger', 'Invalid request.');
    } else {
        $stmt = $pdo->prepare("SELECT subject_name, semester FROM user_subjects WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        $sub = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($sub) {
            $stmt = $pdo->prepare("DELETE FROM syllabus WHERE user_id = ? AND semester = ? AND subject = ?");
            $stmt->execute([$userId, $sub['semester'], $sub['subject_name']]);

            $stmt = $pdo->prepare("DELETE FROM user_subjects WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
            setFlashMessage('success', 'Subject deleted successfully.');
        } else {
            setFlashMessage('danger', 'Subject not found.');
        }
    }
    redirect('manage_subjects.php');

} else {
    setFlashMessage('danger', 'Invalid action.');
    redirect('manage_subjects.php');
}
