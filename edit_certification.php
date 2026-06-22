<?php
$pageTitle = 'Edit Certification';
$extraCSS = ['style.css', 'certifications.css'];
$extraJS = ['main.js', 'certifications.js'];

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$user_id = $_SESSION['user_id'];

$cert_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($cert_id <= 0) {
    setFlashMessage('danger', 'Invalid certification ID.');
    redirect('certifications.php');
}

$stmt = $pdo->prepare("SELECT * FROM certifications WHERE id = ? AND user_id = ?");
$stmt->execute([$cert_id, $user_id]);
$cert = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cert) {
    setFlashMessage('danger', 'Certification not found.');
    redirect('certifications.php');
}

$errors = [];
$old_file = $cert['certificate_path'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $certName = sanitize($_POST['cert_name'] ?? '');
    $issuingOrg = sanitize($_POST['issuing_org'] ?? '');
    $date = sanitize($_POST['date'] ?? '');
    $duration = sanitize($_POST['duration'] ?? '');
    $link = sanitize($_POST['link'] ?? '');

    if (empty($certName)) {
        $errors[] = 'Certification name is required.';
    }
    if (empty($issuingOrg)) {
        $errors[] = 'Issuing organization is required.';
    }
    if (empty($date)) {
        $errors[] = 'Date is required.';
    }

    $certificatePath = $old_file;
    if (isset($_FILES['certificate']) && $_FILES['certificate']['error'] === UPLOAD_ERR_OK) {
        $newFile = uploadFile($_FILES['certificate'], 'certificates', ALLOWED_CERT_TYPES);
        if ($newFile === false) {
            $errors[] = 'Invalid certificate file. Allowed types: ' . implode(', ', ALLOWED_CERT_TYPES) . '. Max size: 5MB.';
        } else {
            if (!empty($old_file) && file_exists($old_file)) unlink($old_file);
            $certificatePath = $newFile;
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE certifications SET cert_name = ?, issuing_org = ?, date = ?, duration = ?, certificate_path = ?, link = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$certName, $issuingOrg, $date, $duration, $certificatePath, $link, $cert_id, $user_id]);

        $detail = "Certification: " . $certName . " - Issuer: " . $issuingOrg;
        setFlashMessage('success', 'Certification updated successfully.');
        notifyEmail('Certification', 'updated', $detail);
        logActivity($pdo, $user_id, $_SESSION['user_name'] ?? 'User', 'Updated', 'Certification', $cert_id, $detail);
        redirect('certifications.php');
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
                    <h4 class="mb-1"><i class="fas fa-edit me-2 text-primary"></i>Edit Certification</h4>
                    <p class="text-muted mb-0">Update certification details</p>
                </div>
                <a href="certifications.php" class="btn btn-secondary">
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
                    <form method="POST" enctype="multipart/form-data">
                        <?= csrfField() ?>
<form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cert_name" class="form-label">Certification Name *</label>
                                <input type="text" class="form-control" id="cert_name" name="cert_name" required placeholder="e.g., AWS Cloud Practitioner" value="<?= htmlspecialchars($cert['cert_name']) ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="issuing_org" class="form-label">Issuing Organization *</label>
                                <input type="text" class="form-control" id="issuing_org" name="issuing_org" required placeholder="e.g., Amazon Web Services" value="<?= htmlspecialchars($cert['issuing_org']) ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="date" class="form-label">Date Obtained *</label>
                                <input type="date" class="form-control" id="date" name="date" required value="<?= $cert['date'] ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="duration" class="form-label">Duration</label>
                                <input type="text" class="form-control" id="duration" name="duration" placeholder="e.g., 3 months, Self-paced" value="<?= htmlspecialchars($cert['duration'] ?? '') ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="certificate" class="form-label">Certificate File</label>
                                <?php if (!empty($old_file)): ?>
                                    <div class="mb-2">
                                        <span class="badge bg-success"><i class="fas fa-file me-1"></i>Current file attached</span>
                                        <a href="<?= htmlspecialchars($old_file) ?>" class="ms-2 small" download>Download</a>
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="certificate" name="certificate" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">Leave empty to keep current file. Allowed: PDF, JPG, PNG (Max 5MB)</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="link" class="form-label">Verification Link</label>
                                <input type="url" class="form-control" id="link" name="link" placeholder="https://..." value="<?= htmlspecialchars($cert['link'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Certification
                            </button>
                            <a href="certifications.php" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
