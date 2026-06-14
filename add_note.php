<?php
$pageTitle = 'Add New Note';
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$userId = $_SESSION['user_id'];
$userSemester = $_SESSION['user_semester'];
$subjects = getSemesterSubjects($userSemester);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $subject = sanitize($_POST['subject']);
    $type = sanitize($_POST['type']);
    $semester = (int)$_POST['semester'];
    $tags = sanitize($_POST['tags']);
    
    $filePath = '';
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $filePath = uploadFile($_FILES['file'], 'notes', ALLOWED_NOTE_TYPES);
        if (!$filePath) {
            setFlashMessage('danger', 'Invalid file type or upload failed');
            header('Location: add_note.php');
            exit;
        }
    }
    
    $query = "INSERT INTO notes (user_id, title, subject, type, file_path, semester, tags, created_at) 
              VALUES (:user_id, :title, :subject, :type, :file_path, :semester, :tags, NOW())";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':user_id' => $userId,
        ':title' => $title,
        ':subject' => $subject,
        ':type' => $type,
        ':file_path' => $filePath,
        ':semester' => $semester,
        ':tags' => $tags
    ]);
    
    setFlashMessage('success', 'Note added successfully');
    header('Location: notes.php');
    exit;
}

include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';
?>
<div class="app-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-plus-circle me-2"></i>Add New Note</h2>
            <a href="notes.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Notes
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="subject" class="form-label">Subject *</label>
                            <input type="text" class="form-control" id="subject" name="subject" list="subject_list" required placeholder="Type or select a subject">
                            <datalist id="subject_list">
                                <?php foreach ($subjects as $subj): ?>
                                    <option value="<?php echo $subj; ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Type *</label>
                            <input type="text" class="form-control" id="type" name="type" list="type_list" required placeholder="PDF, Image, Text, Video, or custom">
                            <datalist id="type_list">
                                <option value="PDF">
                                <option value="Image">
                                <option value="Text">
                                <option value="Video">
                            </datalist>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="semester" class="form-label">Semester *</label>
                            <select class="form-select" id="semester" name="semester" required>
                                <?php for ($i = 1; $i <= 8; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $i == $userSemester ? 'selected' : ''; ?>>
                                        Semester <?php echo $i; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="file" class="form-label">File</label>
                            <input type="file" class="form-control" id="file" name="file">
                            <small class="text-muted">PDF, Images, Text files, Videos</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="tags" class="form-label">Tags</label>
                            <input type="text" class="form-control" id="tags" name="tags" placeholder="Comma separated tags">
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Save Note
                        </button>
                        <a href="notes.php" class="btn btn-secondary ms-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
