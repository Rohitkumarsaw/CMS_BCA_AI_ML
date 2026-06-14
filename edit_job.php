<?php
$pageTitle = 'Edit Job';
$extraCSS = ['style.css', 'jobs.css'];
$extraJS = ['main.js', 'jobs.js'];

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$user_id = $_SESSION['user_id'];

$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($job_id <= 0) {
    setFlashMessage('danger', 'Invalid job ID.');
    redirect('jobs.php');
}

$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ? AND user_id = ?");
$stmt->execute([$job_id, $user_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    setFlashMessage('danger', 'Job not found.');
    redirect('jobs.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobTitle = sanitize($_POST['job_title'] ?? '');
    $company = sanitize($_POST['company'] ?? '');
    $location = sanitize($_POST['location'] ?? '');
    $applicationDate = sanitize($_POST['application_date'] ?? '');
    $status = sanitize($_POST['status'] ?? '');
    $jobLink = sanitize($_POST['job_link'] ?? '');
    $salary = sanitize($_POST['salary'] ?? '');

    if (empty($jobTitle)) {
        $errors[] = 'Job title is required.';
    }
    if (empty($company)) {
        $errors[] = 'Company is required.';
    }
    if (empty($applicationDate)) {
        $errors[] = 'Application date is required.';
    }
    if (empty($status)) {
        $errors[] = 'Status is required.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE jobs SET job_title = ?, company = ?, location = ?, application_date = ?, status = ?, job_link = ?, salary = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$jobTitle, $company, $location, $applicationDate, $status, $jobLink, $salary, $job_id, $user_id]);

        setFlashMessage('success', 'Job application updated successfully.');
        redirect('jobs.php');
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
                    <h4 class="mb-1"><i class="fas fa-edit me-2 text-primary"></i>Edit Job Application</h4>
                    <p class="text-muted mb-0">Update job application details</p>
                </div>
                <a href="jobs.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
            </div>

            <?= getFlashMessage() ?>
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
                    <form method="POST">
                        <?= csrfField() ?>
<form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="job_title" class="form-label">Job Title *</label>
                                <input type="text" class="form-control" id="job_title" name="job_title" required placeholder="e.g., Software Engineer" value="<?= htmlspecialchars($job['job_title']) ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="company" class="form-label">Company *</label>
                                <input type="text" class="form-control" id="company" name="company" required placeholder="e.g., Google" value="<?= htmlspecialchars($job['company']) ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location" placeholder="e.g., Bangalore / Remote" value="<?= htmlspecialchars($job['location'] ?? '') ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="application_date" class="form-label">Application Date *</label>
                                <input type="date" class="form-control" id="application_date" name="application_date" required value="<?= $job['application_date'] ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <input type="text" class="form-control" id="status" name="status" list="status_list" required value="<?= htmlspecialchars($job['status'] ?? '') ?>" placeholder="Applied, Interviewing, Selected, Rejected, or custom">
                                <datalist id="status_list">
                                    <option value="Applied">
                                    <option value="Interviewing">
                                    <option value="Selected">
                                    <option value="Rejected">
                                </datalist>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="salary" class="form-label">Salary</label>
                                <input type="text" class="form-control" id="salary" name="salary" placeholder="e.g., ₹5,00,000/year" value="<?= htmlspecialchars($job['salary'] ?? '') ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="job_link" class="form-label">Job Posting Link</label>
                                <input type="url" class="form-control" id="job_link" name="job_link" placeholder="https://..." value="<?= htmlspecialchars($job['job_link'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Job Application
                            </button>
                            <a href="jobs.php" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
