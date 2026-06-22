<?php
$pageTitle = 'Add New Project';
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
    $description = sanitize($_POST['description']);
    $semester = (int)$_POST['semester'];
    $subject = sanitize($_POST['subject']);
    $techStack = sanitize($_POST['tech_stack']);
    $link = sanitize($_POST['link']);
    $category = sanitize($_POST['category']);
    $startDate = sanitize($_POST['start_date']);
    $endDate = sanitize($_POST['end_date']);
    
    $filePath = '';
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $filePath = uploadFile($_FILES['file'], 'projects');
        if (!$filePath) {
            setFlashMessage('danger', 'File upload failed');
            header('Location: add_project.php');
            exit;
        }
    }
    
    $imagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imagePath = uploadFile($_FILES['image'], 'projects', ALLOWED_IMAGE_TYPES);
        if (!$imagePath) {
            setFlashMessage('danger', 'Image upload failed or invalid type');
            header('Location: add_project.php');
            exit;
        }
    }
    
    $query = "INSERT INTO projects (user_id, title, description, semester, subject, tech_stack, file_path, image_path, link, category, start_date, end_date, created_at) 
              VALUES (:user_id, :title, :description, :semester, :subject, :tech_stack, :file_path, :image_path, :link, :category, :start_date, :end_date, NOW())";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':user_id' => $userId,
        ':title' => $title,
        ':description' => $description,
        ':semester' => $semester,
        ':subject' => $subject,
        ':tech_stack' => $techStack,
        ':file_path' => $filePath,
        ':image_path' => $imagePath,
        ':link' => $link,
        ':category' => $category,
        ':start_date' => $startDate,
        ':end_date' => $endDate
    ]);
    
    $detail = "Project: " . $title . " - Type: " . $category;
    setFlashMessage('success', 'Project added successfully');
    notifyEmail('Project', 'added', $detail);
    logActivity($pdo, $userId, $_SESSION['user_name'] ?? 'User', 'Added', 'Project', $pdo->lastInsertId(), $detail);
    header('Location: projects.php');
    exit;
}

include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';
?>
<div class="app-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-plus-circle me-2"></i>Add New Project</h2>
            <a href="projects.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Projects
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
                            <label for="category" class="form-label">Category *</label>
                            <input type="text" class="form-control" id="category" name="category" list="category_list" required placeholder="Academic, Personal, Internship, Final Year, or custom">
                            <datalist id="category_list">
                                <option value="Academic">
                                <option value="Personal">
                                <option value="Internship">
                                <option value="Final Year">
                            </datalist>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="semester" class="form-label">Semester *</label>
                            <select class="form-select" id="semester" name="semester" required>
                                <?php for ($i = 1; $i <= 8; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $i == $userSemester ? 'selected' : ''; ?>>
                                        Semester <?php echo $i; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" list="subject_list" placeholder="Type or select a subject">
                            <datalist id="subject_list">
                                <?php foreach ($subjects as $subj): ?>
                                    <option value="<?php echo $subj; ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="tech_stack" class="form-label">Tech Stack</label>
                            <input type="text" class="form-control" id="tech_stack" name="tech_stack" placeholder="Comma separated technologies">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="file" class="form-label">Project File</label>
                            <input type="file" class="form-control" id="file" name="file">
                            <small class="text-muted">Upload project files (ZIP, PDF, etc.)</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="image" class="form-label">Project Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="text-muted">Upload project screenshot or image</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="link" class="form-label">Project Link</label>
                            <input type="url" class="form-control" id="link" name="link" placeholder="https://...">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Save Project
                        </button>
                        <a href="projects.php" class="btn btn-secondary ms-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
