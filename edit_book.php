<?php
$pageTitle = 'Edit Book';
$extraCSS = ['style.css', 'library.css'];
$extraJS = ['main.js', 'library.js'];

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$user_id = $_SESSION['user_id'];

$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($book_id <= 0) {
    setFlashMessage('danger', 'Invalid book ID.');
    redirect('library.php');
}

$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ? AND user_id = ?");
$stmt->execute([$book_id, $user_id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    setFlashMessage('danger', 'Book not found.');
    redirect('library.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $author = sanitize($_POST['author'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $borrow_date = sanitize($_POST['borrow_date'] ?? '');
    $return_date = sanitize($_POST['return_date'] ?? '');
    $status = sanitize($_POST['status'] ?? '');

    if (empty($title)) {
        $errors[] = 'Book title is required.';
    }
    if (empty($author)) {
        $errors[] = 'Author is required.';
    }
    if (empty($subject)) {
        $errors[] = 'Subject is required.';
    }
    if (empty($borrow_date)) {
        $errors[] = 'Borrow date is required.';
    }
    if (empty($return_date)) {
        $errors[] = 'Return date is required.';
    }
    if (empty($status)) {
        $errors[] = 'Status is required.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, subject = ?, borrow_date = ?, return_date = ?, status = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$title, $author, $subject, $borrow_date, $return_date, $status, $book_id, $user_id]);

        setFlashMessage('success', 'Book updated successfully.');
        redirect('library.php');
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
                    <h4 class="mb-1"><i class="fas fa-edit me-2 text-primary"></i>Edit Book</h4>
                    <p class="text-muted mb-0">Update book details</p>
                </div>
                <a href="library.php" class="btn btn-secondary">
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
                    <form method="POST">
                        <?= csrfField() ?>
<form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="title" class="form-label">Book Title *</label>
                                <input type="text" class="form-control" id="title" name="title" required
                                       value="<?= htmlspecialchars($book['title']) ?>"
                                       placeholder="e.g., Data Structures and Algorithms">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="author" class="form-label">Author *</label>
                                <input type="text" class="form-control" id="author" name="author" required
                                       value="<?= htmlspecialchars($book['author']) ?>"
                                       placeholder="e.g., Thomas H. Cormen">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="subject" class="form-label">Subject *</label>
                                <input type="text" class="form-control" id="subject" name="subject" required
                                       value="<?= htmlspecialchars($book['subject']) ?>"
                                       placeholder="e.g., Computer Science">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="borrow_date" class="form-label">Borrow Date *</label>
                                <input type="date" class="form-control" id="borrow_date" name="borrow_date" required
                                       value="<?= $book['borrow_date'] ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="return_date" class="form-label">Return Date *</label>
                                <input type="date" class="form-control" id="return_date" name="return_date" required
                                       value="<?= $book['return_date'] ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <input type="text" class="form-control" id="status" name="status" list="status_list" required value="<?= htmlspecialchars($book['status'] ?? '') ?>" placeholder="Borrowed, Returned, or custom">
                                <datalist id="status_list">
                                    <option value="Borrowed">
                                    <option value="Returned">
                                </datalist>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Book
                            </button>
                            <a href="library.php" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
