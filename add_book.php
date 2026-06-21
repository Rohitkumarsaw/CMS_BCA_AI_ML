<?php
$pageTitle = 'Add Book';
$extraCSS = ['style.css', 'library.css'];
$extraJS = ['main.js', 'library.js'];

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $author = sanitize($_POST['author']);
    $subject = sanitize($_POST['subject']);
    $borrowDate = sanitize($_POST['borrow_date']);
    $returnDate = sanitize($_POST['return_date']);
    $status = sanitize($_POST['status']);

    if (empty($title) || empty($author) || empty($subject) || empty($borrowDate) || empty($returnDate) || empty($status)) {
        setFlashMessage('danger', 'Please fill in all required fields');
        header('Location: add_book.php');
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO books (user_id, title, author, subject, borrow_date, return_date, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$userId, $title, $author, $subject, $borrowDate, $returnDate, $status]);

    setFlashMessage('success', 'Book added successfully');
    notifyEmail('Book', 'added');
    header('Location: library.php');
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
                    <h4 class="mb-1"><i class="fas fa-plus-circle me-2 text-primary"></i>Add Book</h4>
                    <p class="text-muted mb-0">Add a book to your library</p>
                </div>
                <a href="library.php" class="btn btn-secondary">
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
                                <label for="title" class="form-label">Book Title *</label>
                                <input type="text" class="form-control" id="title" name="title" required placeholder="e.g., Data Structures and Algorithms">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="author" class="form-label">Author *</label>
                                <input type="text" class="form-control" id="author" name="author" required placeholder="e.g., Thomas H. Cormen">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="subject" class="form-label">Subject *</label>
                                <input type="text" class="form-control" id="subject" name="subject" required placeholder="e.g., Computer Science">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="borrow_date" class="form-label">Borrow Date *</label>
                                <input type="date" class="form-control" id="borrow_date" name="borrow_date" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="return_date" class="form-label">Return Date *</label>
                                <input type="date" class="form-control" id="return_date" name="return_date" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <input type="text" class="form-control" id="status" name="status" list="status_list" required placeholder="Borrowed, Returned, or custom">
                                <datalist id="status_list">
                                    <option value="Borrowed">
                                    <option value="Returned">
                                </datalist>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save Book
                            </button>
                            <a href="library.php" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
