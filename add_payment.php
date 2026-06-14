<?php
$pageTitle = 'Add Payment';

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $semester = (int)$_POST['semester'];
    $paymentType = sanitize($_POST['payment_type']);
    $amount = (float)$_POST['amount'];
    $paymentDate = sanitize($_POST['payment_date']);
    $paymentMethod = sanitize($_POST['payment_method']);
    $transactionId = sanitize($_POST['transaction_id']);
    $status = sanitize($_POST['status']);

    if ($semester < 1 || $semester > 8 || empty($paymentType) || $amount <= 0 || empty($paymentDate) || empty($paymentMethod) || empty($status)) {
        setFlashMessage('danger', 'Please fill in all required fields');
        header('Location: add_payment.php');
        exit;
    }

    $receiptPath = '';
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
        $receiptPath = uploadFile($_FILES['receipt'], 'receipts', ALLOWED_RECEIPT_TYPES);
        if (!$receiptPath) {
            setFlashMessage('danger', 'Receipt upload failed or invalid file type');
            header('Location: add_payment.php');
            exit;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO payments (user_id, semester, payment_type, amount, payment_date, payment_method, transaction_id, receipt_path, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        $userId,
        $semester,
        $paymentType,
        $amount,
        $paymentDate,
        $paymentMethod,
        $transactionId,
        $receiptPath,
        $status
    ]);

    setFlashMessage('success', 'Payment record added successfully');
    header('Location: payment.php');
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
                    <h4 class="mb-1"><i class="fas fa-plus-circle me-2 text-primary"></i>Add Payment</h4>
                    <p class="text-muted mb-0">Record a new payment transaction</p>
                </div>
                <a href="payment.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
            </div>

            <?= getFlashMessage() ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <?= csrfField() ?>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="semester" class="form-label">Semester *</label>
                                <select class="form-select" id="semester" name="semester" required>
                                    <option value="">Select Semester</option>
                                    <?php for ($i = 1; $i <= 8; $i++): ?>
                                        <option value="<?= $i ?>">Semester <?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="payment_type" class="form-label">Payment Type *</label>
                                <input type="text" class="form-control" id="payment_type" name="payment_type" list="payment_type_list" required placeholder="e.g., Tuition Fee, Exam Fee">
                                <datalist id="payment_type_list">
                                    <option value="Tuition Fee">
                                    <option value="Exam Fee">
                                    <option value="Admission Fees">
                                    <option value="Hostel Fee">
                                    <option value="Library Fee">
                                    <option value="Lab Fee">
                                    <option value="Sports Fee">
                                    <option value="Miscellaneous">
                                </datalist>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="amount" class="form-label">Amount (₹) *</label>
                                <input type="number" class="form-control" id="amount" name="amount" required min="0" step="0.01" placeholder="Enter amount">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="payment_date" class="form-label">Payment Date *</label>
                                <input type="date" class="form-control" id="payment_date" name="payment_date" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="payment_method" class="form-label">Payment Method *</label>
                                <input type="text" class="form-control" id="payment_method" name="payment_method" list="payment_method_list" required placeholder="Online Banking, Credit Card, UPI, or custom">
                                <datalist id="payment_method_list">
                                    <option value="Online Banking">
                                    <option value="Credit Card">
                                    <option value="Debit Card">
                                    <option value="Cash">
                                    <option value="UPI">
                                    <option value="Loan">
                                    <option value="Cheque">
                                    <option value="Demand Draft">
                                    <option value="Scholarship">
                                </datalist>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="transaction_id" class="form-label">Transaction ID</label>
                                <input type="text" class="form-control" id="transaction_id" name="transaction_id" placeholder="Optional">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="receipt" class="form-label">Receipt</label>
                                <input type="file" class="form-control" id="receipt" name="receipt" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">Allowed: PDF, JPG, PNG</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <input type="text" class="form-control" id="status" name="status" list="status_list" required placeholder="Paid, Unpaid, Partial, or custom">
                                <datalist id="status_list">
                                    <option value="Paid">
                                    <option value="Unpaid">
                                    <option value="Partial">
                                </datalist>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save Payment
                            </button>
                            <a href="payment.php" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
