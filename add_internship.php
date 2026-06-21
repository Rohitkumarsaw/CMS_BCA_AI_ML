<?php
$pageTitle = 'Add Internship';

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company = sanitize($_POST['company']);
    $role = sanitize($_POST['role']);
    $durationStart = sanitize($_POST['duration_start']);
    $durationEnd = sanitize($_POST['duration_end']);
    $location = sanitize($_POST['location']);
    $description = sanitize($_POST['description']);
    $skillsGained = sanitize($_POST['skills_gained']);
    $payment = !empty($_POST['payment']) ? (float)sanitize($_POST['payment']) : null;
    $status = sanitize($_POST['status']);

    if (empty($company) || empty($role) || empty($durationStart) || empty($durationEnd) || empty($status)) {
        setFlashMessage('danger', 'Please fill in all required fields');
        header('Location: add_internship.php');
        exit;
    }

    $certificatePath = '';
    if (isset($_FILES['certificate']) && $_FILES['certificate']['error'] === UPLOAD_ERR_OK) {
        $certificatePath = uploadFile($_FILES['certificate'], 'certificates', ALLOWED_CERT_TYPES);
        if (!$certificatePath) {
            setFlashMessage('danger', 'Certificate upload failed or invalid file type');
            header('Location: add_internship.php');
            exit;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO internships (user_id, company, role, duration_start, duration_end, location, description, skills_gained, payment, certificate_path, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        $userId,
        $company,
        $role,
        $durationStart,
        $durationEnd,
        $location,
        $description,
        $skillsGained,
        $payment,
        $certificatePath,
        $status
    ]);

    $detail = "Company: " . $company . " - Role: " . $role;
    setFlashMessage('success', 'Internship added successfully');
    notifyEmail('Internship', 'added', $detail);
    header('Location: internship.php');
    exit;
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<?php include 'includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1"><i class="fas fa-plus-circle me-2 text-primary"></i>Add Internship</h4>
                    <p class="text-muted mb-0">Record a new internship experience</p>
                </div>
                <a href="internship.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
            </div>

            <?= getFlashMessage() ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <?= csrfField() ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="company" class="form-label">Company *</label>
                                <input type="text" class="form-control" id="company" name="company" required placeholder="Enter company name">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">Role *</label>
                                <input type="text" class="form-control" id="role" name="role" required placeholder="e.g., Software Development Intern">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="duration_start" class="form-label">Start Date *</label>
                                <input type="date" class="form-control" id="duration_start" name="duration_start" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="duration_end" class="form-label">End Date *</label>
                                <input type="date" class="form-control" id="duration_end" name="duration_end" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location" placeholder="e.g., Bangalore / Remote">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="payment" class="form-label">Payment / Stipend</label>
                                <input type="number" step="0.01" class="form-control" id="payment" name="payment" placeholder="e.g., 10000">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Brief description of the internship"></textarea>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="skills_gained" class="form-label">Skills Gained</label>
                                <textarea class="form-control" id="skills_gained" name="skills_gained" rows="2" placeholder="Comma separated skills, e.g., React, Node.js, Git"></textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="certificate" class="form-label">Certificate</label>
                                <input type="file" class="form-control" id="certificate" name="certificate" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">Allowed: PDF, JPG, PNG</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <input type="text" class="form-control" id="status" name="status" list="status_list" required placeholder="Applied, Interviewing, Selected, Completed, or custom">
                                <datalist id="status_list">
                                    <option value="Applied">
                                    <option value="Interviewing">
                                    <option value="Selected">
                                    <option value="Completed">
                                </datalist>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save Internship
                            </button>
                            <a href="internship.php" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
