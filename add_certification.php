<?php
$pageTitle = 'Add Certification';
$extraCSS = ['style.css', 'certifications.css'];
$extraJS = ['main.js', 'certifications.js'];

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $certName = sanitize($_POST['cert_name']);
    $issuingOrg = sanitize($_POST['issuing_org']);
    $date = sanitize($_POST['date']);
    $duration = sanitize($_POST['duration']);
    $link = sanitize($_POST['link']);

    if (empty($certName) || empty($issuingOrg) || empty($date)) {
        setFlashMessage('danger', 'Please fill in all required fields');
        header('Location: add_certification.php');
        exit;
    }

    $certificatePath = '';
    if (isset($_FILES['certificate']) && $_FILES['certificate']['error'] === UPLOAD_ERR_OK) {
        $certificatePath = uploadFile($_FILES['certificate'], 'certificates', ALLOWED_CERT_TYPES);
        if (!$certificatePath) {
            setFlashMessage('danger', 'Certificate upload failed or invalid file type');
            header('Location: add_certification.php');
            exit;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO certifications (user_id, cert_name, issuing_org, date, duration, certificate_path, link, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$userId, $certName, $issuingOrg, $date, $duration, $certificatePath, $link]);

    setFlashMessage('success', 'Certification added successfully');
    notifyEmail('Certification', 'added');
    header('Location: certifications.php');
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
                    <h4 class="mb-1"><i class="fas fa-plus-circle me-2 text-primary"></i>Add Certification</h4>
                    <p class="text-muted mb-0">Add a new certification to your profile</p>
                </div>
                <a href="certifications.php" class="btn btn-secondary">
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
                                <label for="cert_name" class="form-label">Certification Name *</label>
                                <input type="text" class="form-control" id="cert_name" name="cert_name" required placeholder="e.g., AWS Cloud Practitioner">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="issuing_org" class="form-label">Issuing Organization *</label>
                                <input type="text" class="form-control" id="issuing_org" name="issuing_org" required placeholder="e.g., Amazon Web Services">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="date" class="form-label">Date Obtained *</label>
                                <input type="date" class="form-control" id="date" name="date" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="duration" class="form-label">Duration</label>
                                <input type="text" class="form-control" id="duration" name="duration" placeholder="e.g., 3 months, Self-paced">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="certificate" class="form-label">Certificate File</label>
                                <input type="file" class="form-control" id="certificate" name="certificate" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">Allowed: PDF, JPG, PNG (Max 5MB)</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="link" class="form-label">Verification Link</label>
                                <input type="url" class="form-control" id="link" name="link" placeholder="https://...">
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save Certification
                            </button>
                            <a href="certifications.php" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
