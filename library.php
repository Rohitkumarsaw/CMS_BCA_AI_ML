<?php
$pageTitle = 'Library';
$extraCSS = ['style.css', 'library.css'];
$extraJS = ['main.js', 'library.js'];

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];

$filterStatus = isset($_GET['status']) ? sanitize($_GET['status']) : '';

$where = "WHERE user_id = ?";
$params = [$userId];

if (!empty($filterStatus)) {
    $where .= " AND status = ?";
    $params[] = $filterStatus;
}

$pagi = paginate($pdo, 'books', $where, $params, 20);
$stmt = $pdo->prepare("SELECT * FROM books $where ORDER BY borrow_date DESC" . $pagi['sql']);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statusStmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM books WHERE user_id = ? GROUP BY status");
$statusStmt->execute([$userId]);
$statusCounts = $statusStmt->fetchAll(PDO::FETCH_KEY_PAIR);

$dueStmt = $pdo->prepare("SELECT * FROM books WHERE user_id = ? AND status = 'Borrowed' AND return_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
$dueStmt->execute([$userId]);
$dueSoon = $dueStmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<?php include 'includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1"><i class="fas fa-book-open me-2 text-primary"></i>Library</h4>
                    <p class="text-muted mb-0">Track your borrowed books</p>
                </div>
                <a href="add_book.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Add Book
                </a>
            </div>

            <?= getFlashMessage() ?>

            <?php if (!empty($dueSoon)): ?>
                <div class="alert alert-warning">
                    <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Books Due Soon</h6>
                    <p class="mb-0">You have <?= count($dueSoon) ?> book(s) due within the next 7 days. Please return them on time.</p>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($dueSoon as $book): ?>
                            <li><strong><?= htmlspecialchars($book['title']) ?></strong> - Due: <?= formatDate($book['return_date']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="text-primary mb-1"><?= count($books) ?></h3>
                            <small class="text-muted">Total Books</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="text-warning mb-1"><?= $statusCounts['Borrowed'] ?? 0 ?></h3>
                            <small class="text-muted">Currently Borrowed</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="text-success mb-1"><?= $statusCounts['Returned'] ?? 0 ?></h3>
                            <small class="text-muted">Returned</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0"><i class="fas fa-filter me-2 text-primary"></i>Filter</h6>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="Borrowed" <?= $filterStatus === 'Borrowed' ? 'selected' : '' ?>>Borrowed</option>
                                <option value="Returned" <?= $filterStatus === 'Returned' ? 'selected' : '' ?>>Returned</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
                            <a href="library.php" class="btn btn-outline-secondary"><i class="fas fa-redo"></i></a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <?php if (!empty($books)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Subject</th>
                                        <th>Borrow Date</th>
                                        <th>Return Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($books as $book): ?>
                                        <tr>
                                            <td>
                                                <strong><i class="fas fa-book me-1 text-primary"></i><?= htmlspecialchars($book['title']) ?></strong>
                                            </td>
                                            <td><?= htmlspecialchars($book['author']) ?></td>
                                            <td><?= htmlspecialchars($book['subject']) ?></td>
                                            <td><small><?= formatDate($book['borrow_date']) ?></small></td>
                                            <td><small><?= formatDate($book['return_date']) ?></small></td>
                                            <td>
                                                <span class="badge bg-<?= $book['status'] === 'Borrowed' ? 'warning' : 'success' ?>">
                                                    <?= htmlspecialchars($book['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="edit_book.php?id=<?= $book['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                                <a href="view.php?type=book&id=<?= $book['id'] ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                                <a href="delete.php?type=book&id=<?= $book['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" data-confirm="this book"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No books found</p>
                            <a href="add_book.php" class="btn btn-primary mt-2">
                                <i class="fas fa-plus me-1"></i>Add Your First Book
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?= paginationLinks($pagi['page'], $pagi['totalPages']) ?>
    </div>

<?php include 'includes/footer.php'; ?>
