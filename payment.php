<?php
$pageTitle = 'Payments';
$extraCSS = ['style.css', 'payment.css'];
$extraJS = ['main.js', 'payment.js'];

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];

$filter_semester = isset($_GET['semester']) ? (int)$_GET['semester'] : 0;
$filter_status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

$where = "WHERE user_id = ?";
$params = [$userId];

if ($filter_semester > 0) {
    $where .= " AND semester = ?";
    $params[] = $filter_semester;
}
if (!empty($filter_status)) {
    $where .= " AND status = ?";
    $params[] = $filter_status;
}

$pagi = paginate($pdo, 'payments', $where, $params, 20);
$stmt = $pdo->prepare("SELECT * FROM payments $where ORDER BY semester ASC, payment_date DESC" . $pagi['sql']);
$stmt->execute($params);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$semesterSummary = [];
$stmtSummary = $pdo->prepare("SELECT semester, SUM(CASE WHEN status = 'Paid' THEN amount ELSE 0 END) as paid_amount, SUM(CASE WHEN status = 'Unpaid' THEN amount ELSE 0 END) as unpaid_amount, SUM(CASE WHEN status = 'Partial' THEN amount ELSE 0 END) as partial_amount, SUM(amount) as total_amount, COUNT(*) as count FROM payments WHERE user_id = ? GROUP BY semester ORDER BY semester");
$stmtSummary->execute([$userId]);
$semesterSummary = $stmtSummary->fetchAll(PDO::FETCH_ASSOC);

$totalPaid = 0;
$totalUnpaid = 0;
$totalPartial = 0;
foreach ($semesterSummary as $sem) {
    $totalPaid += $sem['paid_amount'];
    $totalUnpaid += $sem['unpaid_amount'];
    $totalPartial += $sem['partial_amount'];
}

$chartSemesters = [];
$chartPaid = [];
$chartUnpaid = [];
$chartPartial = [];
foreach ($semesterSummary as $sem) {
    $chartSemesters[] = 'Sem ' . $sem['semester'];
    $chartPaid[] = (float)$sem['paid_amount'];
    $chartUnpaid[] = (float)$sem['unpaid_amount'];
    $chartPartial[] = (float)$sem['partial_amount'];
}

$statusColors = [
    'Paid' => 'success',
    'Unpaid' => 'danger',
    'Partial' => 'warning'
];

include 'includes/header.php';
include 'includes/navbar.php';
?>

<?php include 'includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1"><i class="fas fa-credit-card me-2 text-primary"></i>Payments</h4>
                    <p class="text-muted mb-0">Track your semester fees and payments</p>
                </div>
                <a href="add_payment.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Add Payment
                </a>
            </div>

            <?= getFlashMessage() ?>

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="text-success mb-1">₹<?= number_format($totalPaid) ?></h3>
                            <small class="text-muted">Total Paid</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="text-danger mb-1">₹<?= number_format($totalUnpaid) ?></h3>
                            <small class="text-muted">Total Unpaid</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="text-warning mb-1">₹<?= number_format($totalPartial) ?></h3>
                            <small class="text-muted">Partial</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="text-primary mb-1">₹<?= number_format($totalPaid + $totalUnpaid + $totalPartial) ?></h3>
                            <small class="text-muted">Grand Total</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0"><i class="fas fa-filter me-2 text-primary"></i>Filter</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Semester</label>
                            <select name="semester" class="form-select">
                                <option value="0">All Semesters</option>
                                <?php for ($i = 1; $i <= 8; $i++): ?>
                                    <option value="<?= $i ?>" <?= $filter_semester == $i ? 'selected' : '' ?>>Semester <?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="Paid" <?= $filter_status === 'Paid' ? 'selected' : '' ?>>Paid</option>
                                <option value="Unpaid" <?= $filter_status === 'Unpaid' ? 'selected' : '' ?>>Unpaid</option>
                                <option value="Partial" <?= $filter_status === 'Partial' ? 'selected' : '' ?>>Partial</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
                            <a href="payment.php" class="btn btn-outline-secondary"><i class="fas fa-redo"></i></a>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (!empty($chartSemesters)): ?>
            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i>Payments Per Semester</h6>
                </div>
                <div class="card-body">
                    <canvas id="paymentChart" height="300"></canvas>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($semesterSummary)): ?>
            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0"><i class="fas fa-receipt me-2 text-primary"></i>Per Semester Fee Summary</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Semester</th>
                                    <th>Paid</th>
                                    <th>Unpaid</th>
                                    <th>Partial</th>
                                    <th>Total</th>
                                    <th>Records</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($semesterSummary as $sem): ?>
                                    <tr>
                                        <td><strong>Semester <?= $sem['semester'] ?></strong></td>
                                        <td class="text-success fw-bold">₹<?= number_format($sem['paid_amount']) ?></td>
                                        <td class="text-danger fw-bold">₹<?= number_format($sem['unpaid_amount']) ?></td>
                                        <td class="text-warning fw-bold">₹<?= number_format($sem['partial_amount']) ?></td>
                                        <td class="text-primary fw-bold">₹<?= number_format($sem['total_amount']) ?></td>
                                        <td><?= $sem['count'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0"><i class="fas fa-table me-2 text-primary"></i>Payment Records</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Semester</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th>Transaction ID</th>
                                    <th>Receipt</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($payments)): ?>
                                    <?php foreach ($payments as $index => $payment): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><strong>Sem <?= $payment['semester'] ?></strong></td>
                                            <td><?= htmlspecialchars($payment['payment_type']) ?></td>
                                            <td class="fw-bold">₹<?= number_format($payment['amount']) ?></td>
                                            <td><i class="fas fa-calendar me-1 text-muted"></i><?= formatDate($payment['payment_date']) ?></td>
                                            <td><?= htmlspecialchars($payment['payment_method']) ?></td>
                                            <td>
                                                <?php if (!empty($payment['transaction_id'])): ?>
                                                    <code><?= htmlspecialchars($payment['transaction_id']) ?></code>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($payment['receipt_path'])): ?>
                                                    <a href="<?= htmlspecialchars($payment['receipt_path']) ?>" class="btn btn-sm btn-outline-success" download>
                                                        <i class="fas fa-download me-1"></i>Receipt
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $statusColors[$payment['status']] ?? 'secondary' ?>">
                                                    <?= htmlspecialchars($payment['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="edit_payment.php?id=<?= $payment['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                                <a href="view.php?type=payment&id=<?= $payment['id'] ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                                <a href="delete.php?type=payment&id=<?= $payment['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" data-confirm="this payment"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>No payment records found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <?= paginationLinks($pagi['page'], $pagi['totalPages']) ?>
    </div>
</div>

<?php if (!empty($chartSemesters)): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('paymentChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartSemesters) ?>,
                datasets: [
                    {
                        label: 'Paid',
                        data: <?= json_encode($chartPaid) ?>,
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Unpaid',
                        data: <?= json_encode($chartUnpaid) ?>,
                        backgroundColor: 'rgba(239, 68, 68, 0.7)',
                        borderColor: 'rgba(239, 68, 68, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Partial',
                        data: <?= json_encode($chartPartial) ?>,
                        backgroundColor: 'rgba(245, 158, 11, 0.7)',
                        borderColor: 'rgba(245, 158, 11, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });
    });
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
