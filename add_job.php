<?php
$pageTitle = 'Add Job';
$extraCSS = ['style.css', 'jobs.css'];
$extraJS = ['main.js', 'jobs.js'];

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobTitle = sanitize($_POST['job_title']);
    $company = sanitize($_POST['company']);
    $location = sanitize($_POST['location']);
    $applicationDate = sanitize($_POST['application_date']);
    $status = sanitize($_POST['status']);
    $jobLink = sanitize($_POST['job_link']);
    $salary = sanitize($_POST['salary']);

    if (empty($jobTitle) || empty($company) || empty($applicationDate) || empty($status)) {
        setFlashMessage('danger', 'Please fill in all required fields');
        header('Location: add_job.php');
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO jobs (user_id, job_title, company, location, application_date, status, job_link, salary, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$userId, $jobTitle, $company, $location, $applicationDate, $status, $jobLink, $salary]);

    setFlashMessage('success', 'Job application added successfully');
    header('Location: jobs.php');
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
                    <h4 class="mb-1"><i class="fas fa-plus-circle me-2 text-primary"></i>Add Job Application</h4>
                    <p class="text-muted mb-0">Record a new job application</p>
                </div>
                <a href="jobs.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
            </div>

            <?= getFlashMessage() ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <?= csrfField() ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="job_title" class="form-label">Job Title *</label>
                                <input type="text" class="form-control" id="job_title" name="job_title" required placeholder="e.g., Software Engineer">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="company" class="form-label">Company *</label>
                                <input type="text" class="form-control" id="company" name="company" required placeholder="e.g., Google">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location" placeholder="e.g., Bangalore / Remote">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="application_date" class="form-label">Application Date *</label>
                                <input type="date" class="form-control" id="application_date" name="application_date" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <input type="text" class="form-control" id="status" name="status" list="status_list" required placeholder="Applied, Interviewing, Selected, Rejected, or custom">
                                <datalist id="status_list">
                                    <option value="Applied">
                                    <option value="Interviewing">
                                    <option value="Selected">
                                    <option value="Rejected">
                                </datalist>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="salary" class="form-label">Salary</label>
                                <input type="text" class="form-control" id="salary" name="salary" placeholder="e.g., ₹5,00,000/year">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="job_link" class="form-label">Job Posting Link</label>
                                <input type="url" class="form-control" id="job_link" name="job_link" placeholder="https://...">
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save Job Application
                            </button>
                            <a href="jobs.php" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
