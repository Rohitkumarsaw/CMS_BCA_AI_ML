<?php
$pageTitle = 'Edit Internship';

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$user_id = $_SESSION['user_id'];

$internship_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($internship_id <= 0) {
    setFlashMessage('danger', 'Invalid internship ID.');
    redirect('internship.php');
}

$stmt = $pdo->prepare("SELECT * FROM internships WHERE id = ? AND user_id = ?");
$stmt->execute([$internship_id, $user_id]);
$internship = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$internship) {
    setFlashMessage('danger', 'Internship not found.');
    redirect('internship.php');
}

$errors = [];
$old_certificate = $internship['certificate_path'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company = sanitize($_POST['company'] ?? '');
    $role = sanitize($_POST['role'] ?? '');
    $duration_start = sanitize($_POST['duration_start'] ?? '');
    $duration_end = sanitize($_POST['duration_end'] ?? '');
    $location = sanitize($_POST['location'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $skills_gained = sanitize($_POST['skills_gained'] ?? '');
    $payment = !empty($_POST['payment']) ? (float)sanitize($_POST['payment']) : null;
    $status = sanitize($_POST['status'] ?? '');

    if (empty($company)) {
        $errors[] = 'Company is required.';
    }
    if (empty($role)) {
        $errors[] = 'Role is required.';
    }
    if (empty($duration_start)) {
        $errors[] = 'Start date is required.';
    }
    if (empty($duration_end)) {
        $errors[] = 'End date is required.';
    }
    if (empty($status)) {
        $errors[] = 'Status is required.';
    }

    $certificate_path = $old_certificate;
    if (isset($_FILES['certificate']) && $_FILES['certificate']['error'] === UPLOAD_ERR_OK) {
        $new_cert = uploadFile($_FILES['certificate'], 'certificates', ALLOWED_CERT_TYPES);
        if ($new_cert === false) {
            $errors[] = 'Certificate upload failed or invalid file type. Allowed: ' . implode(', ', ALLOWED_CERT_TYPES);
        } else {
            if (!empty($old_certificate) && file_exists($old_certificate)) unlink($old_certificate);
            $certificate_path = $new_cert;
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE internships SET company = ?, role = ?, duration_start = ?, duration_end = ?, location = ?, description = ?, skills_gained = ?, certificate_path = ?, payment = ?, status = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$company, $role, $duration_start, $duration_end, $location, $description, $skills_gained, $certificate_path, $payment, $status, $internship_id, $user_id]);

        $detail = "Company: " . $company . " - Role: " . $role;
        setFlashMessage('success', 'Internship updated successfully.');
        notifyEmail('Internship', 'updated', $detail);
        redirect('internship.php');
    }
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<?php include 'includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1"><i class="fas fa-edit me-2 text-primary"></i>Edit Internship</h4>
                    <p class="text-muted mb-0">Update internship experience</p>
                </div>
                <a href="internship.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <?= csrfField() ?>
<form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="company" class="form-label">Company *</label>
                                <input type="text" class="form-control" id="company" name="company" required
                                       value="<?= htmlspecialchars($internship['company']) ?>"
                                       placeholder="Enter company name">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">Role *</label>
                                <input type="text" class="form-control" id="role" name="role" required
                                       value="<?= htmlspecialchars($internship['role']) ?>"
                                       placeholder="e.g., Software Development Intern">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="duration_start" class="form-label">Start Date *</label>
                                <input type="date" class="form-control" id="duration_start" name="duration_start" required
                                       value="<?= $internship['duration_start'] ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="duration_end" class="form-label">End Date *</label>
                                <input type="date" class="form-control" id="duration_end" name="duration_end" required
                                       value="<?= $internship['duration_end'] ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location"
                                       value="<?= htmlspecialchars($internship['location'] ?? '') ?>"
                                       placeholder="e.g., Bangalore / Remote">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="payment" class="form-label">Payment / Stipend</label>
                                <input type="number" step="0.01" class="form-control" id="payment" name="payment"
                                       value="<?= htmlspecialchars($internship['payment'] ?? '') ?>"
                                       placeholder="e.g., 10000">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"
                                          placeholder="Brief description of the internship"><?= htmlspecialchars($internship['description'] ?? '') ?></textarea>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="skills_gained" class="form-label">Skills Gained</label>
                                <textarea class="form-control" id="skills_gained" name="skills_gained" rows="2"
                                          placeholder="Comma separated skills"><?= htmlspecialchars($internship['skills_gained'] ?? '') ?></textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="certificate" class="form-label">Certificate</label>
                                <?php if (!empty($old_certificate)): ?>
                                    <div class="mb-2">
                                        <span class="badge bg-success"><i class="fas fa-file me-1"></i>Current certificate attached</span>
                                        <a href="<?= htmlspecialchars($old_certificate) ?>" class="ms-2 small" download>Download</a>
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="certificate" name="certificate" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">Leave empty to keep current file. Allowed: PDF, JPG, PNG</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <input type="text" class="form-control" id="status" name="status" list="status_list" required value="<?= htmlspecialchars($internship['status'] ?? '') ?>" placeholder="Applied, Interviewing, Selected, Rejected, Completed, or custom">
                                <datalist id="status_list">
                                    <option value="Applied">
                                    <option value="Interviewing">
                                    <option value="Selected">
                                    <option value="Rejected">
                                    <option value="Completed">
                                </datalist>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Internship
                            </button>
                            <a href="internship.php" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
