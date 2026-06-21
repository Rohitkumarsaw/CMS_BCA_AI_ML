<?php
$pageTitle = 'Edit Profile';

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$userId = $_SESSION['user_id'];

$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$userId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

$profileStmt = $pdo->prepare("SELECT * FROM profiles WHERE user_id = ?");
$profileStmt->execute([$userId]);
$profile = $profileStmt->fetch(PDO::FETCH_ASSOC);

$name = $profile['name'] ?? $user['name'] ?? '';
$college = $profile['college'] ?? $user['college'] ?? '';
$semester = $profile['semester'] ?? $user['semester'] ?? 1;
$rollNo = $profile['roll_no'] ?? $user['roll_no'] ?? '';
$email = $profile['email'] ?? $user['email'] ?? '';
$phone = $profile['phone'] ?? $user['phone'] ?? '';
$address = $profile['address'] ?? $user['address'] ?? '';
$photoPath = $profile['photo_path'] ?? $user['photo_path'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $college = sanitize($_POST['college']);
    $semester = (int)$_POST['semester'];
    $rollNo = sanitize($_POST['roll_no']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);

    if (empty($name)) {
        setFlashMessage('danger', 'Name is required');
        header('Location: edit_profile.php');
        exit;
    }

    $photoPath = $profile['photo_path'] ?? $user['photo_path'] ?? '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadedPath = uploadFile($_FILES['photo'], 'profiles', ALLOWED_IMAGE_TYPES);
        if ($uploadedPath) {
            $oldPhoto = $profile['photo_path'] ?? $user['photo_path'] ?? '';
            if (!empty($oldPhoto) && file_exists($oldPhoto)) {
                unlink($oldPhoto);
            }
            $photoPath = $uploadedPath;
        } else {
            setFlashMessage('danger', 'Photo upload failed. Allowed types: JPG, JPEG, PNG, GIF');
            header('Location: edit_profile.php');
            exit;
        }
    }

    $pdo->prepare("UPDATE users SET name = ?, college = ?, semester = ?, roll_no = ?, email = ?, phone = ?, address = ?, photo_path = ? WHERE id = ?")
        ->execute([$name, $college, $semester, $rollNo, $email, $phone, $address, $photoPath, $userId]);

    $existingProfile = $pdo->prepare("SELECT id FROM profiles WHERE user_id = ?");
    $existingProfile->execute([$userId]);
    if ($existingProfile->fetch()) {
        $pdo->prepare("UPDATE profiles SET name = ?, college = ?, semester = ?, roll_no = ?, email = ?, phone = ?, address = ?, photo_path = ? WHERE user_id = ?")
            ->execute([$name, $college, $semester, $rollNo, $email, $phone, $address, $photoPath, $userId]);
    } else {
        $pdo->prepare("INSERT INTO profiles (user_id, name, college, semester, roll_no, email, phone, address, photo_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$userId, $name, $college, $semester, $rollNo, $email, $phone, $address, $photoPath]);
    }

    $_SESSION['user_name'] = $name;
    $_SESSION['user_semester'] = $semester;

    setFlashMessage('success', 'Profile updated successfully');
    header('Location: profile.php');
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
                    <h4 class="mb-1"><i class="fas fa-edit me-2 text-primary"></i>Edit Profile</h4>
                    <p class="text-muted mb-0">Update your profile information</p>
                </div>
                <a href="profile.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
            </div>

            <?= getFlashMessage() ?>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <?= csrfField() ?>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required placeholder="Enter your full name">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="college" class="form-label">College</label>
                                        <input type="text" class="form-control" id="college" name="college" value="<?= htmlspecialchars($college) ?>" placeholder="Enter your college name">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="semester" class="form-label">Semester</label>
                                        <select class="form-select" id="semester" name="semester">
                                            <?php for ($i = 1; $i <= 8; $i++): ?>
                                                <option value="<?= $i ?>" <?= $semester == $i ? 'selected' : '' ?>>
                                                    Semester <?= $i ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="roll_no" class="form-label">Roll Number</label>
                                        <input type="text" class="form-control" id="roll_no" name="roll_no" value="<?= htmlspecialchars($rollNo) ?>" placeholder="Enter your roll number">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="Enter your email">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Phone</label>
                                        <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>" placeholder="Enter your phone number">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="photo" class="form-label">Profile Photo</label>
                                        <input type="file" class="form-control" id="photo" name="photo" accept=".jpg,.jpeg,.png,.gif">
                                        <small class="text-muted">Allowed: JPG, JPEG, PNG, GIF (Max 5MB)</small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Current Photo</label>
                                        <div>
                                            <?php if (!empty($photoPath)): ?>
                                                <img src="<?= htmlspecialchars($photoPath) ?>" alt="Current Photo" class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                                    <i class="fas fa-user fa-2x text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea class="form-control" id="address" name="address" rows="3" placeholder="Enter your address"><?= htmlspecialchars($address) ?></textarea>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Save Changes
                                    </button>
                                    <a href="profile.php" class="btn btn-secondary ms-2">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Profile Tips</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled small">
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Keep your profile up to date</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Use a professional photo</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Add your correct email</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Keep roll number accurate</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
