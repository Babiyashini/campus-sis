<?php
require_once '../config/database.php';
requireLogin();
$page_title = 'Make Payment';
include '../includes/header.php';
include '../includes/sidebar.php';

$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    header('Location: students.php');
    exit();
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $invoice_no = generateInvoiceNumber();
        $amount = (float)$_POST['amount'];
        $total_amount = (float)$_POST['total_amount'];
        $due = $total_amount - $amount;
        $status = $due <= 0 ? 'paid' : ($amount > 0 ? 'partial' : 'due');

        $stmt = $pdo->prepare("
            INSERT INTO payments (invoice_no, student_id, payment_date, total_amount, paid_amount, due_amount, status, payment_method, description)
            VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([
            $invoice_no,
            $student_id,
            $total_amount,
            $amount,
            $due,
            $status,
            $_POST['payment_method'],
            sanitize($_POST['description'])
        ]);

        if ($result) {
            $message = 'Payment processed successfully! Invoice: ' . $invoice_no;
            $messageType = 'success';
        } else {
            $message = 'Failed to process payment.';
            $messageType = 'danger';
        }
    } catch (PDOException $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

$stmt = $pdo->prepare("SELECT SUM(paid_amount) as paid, SUM(due_amount) as due FROM payments WHERE student_id = ?");
$stmt->execute([$student_id]);
$summary = $stmt->fetch();
$totalPaid = $summary['paid'] ?? 0;
$totalDue = $summary['due'] ?? 0;
$totalFees = $totalPaid + $totalDue;
?>
<div class="container-fluid fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800"><i class="fas fa-credit-card me-2 text-primary"></i>Make Payment</h1>
        <a href="view_student.php?id=<?php echo $student_id; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Profile
        </a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
            <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0"><i class="fas fa-user me-2 text-primary"></i>Student Information</h6>
                </div>
                <div class="card-body">
                    <div class="student-info-section">
                        <div class="info-row">
                            <span class="info-label">Full Name</span>
                            <span class="info-value"><?php echo htmlspecialchars($student['full_name']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Student ID</span>
                            <span class="info-value"><?php echo htmlspecialchars($student['student_number']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Program</span>
                            <span class="info-value"><?php echo htmlspecialchars($student['program']); ?></span>
                        </div>
                    </div>
                    <hr>
                    <h6>Payment Summary</h6>
                    <div class="info-row">
                        <span class="info-label">Total Fees</span>
                        <span class="info-value"><strong>LKR <?php echo number_format($totalFees, 2); ?></strong></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Paid Amount</span>
                        <span class="info-value text-success"><strong>LKR <?php echo number_format($totalPaid, 2); ?></strong></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Due Amount</span>
                        <span class="info-value text-danger"><strong>LKR <?php echo number_format($totalDue, 2); ?></strong></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0"><i class="fas fa-credit-card me-2 text-success"></i>Payment Details</h6>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Total Amount *</label>
                                <div class="input-group">
                                    <span class="input-group-text">LKR</span>
                                    <input type="number" name="total_amount" class="form-control" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Amount *</label>
                                <div class="input-group">
                                    <span class="input-group-text">LKR</span>
                                    <input type="number" name="amount" class="form-control" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Method</label>
                                <select name="payment_method" class="form-select">
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="online">Online Banking</option>
                                    <option value="card">Credit/Debit Card</option>
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="e.g., Semester 1 Tuition Fee"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-credit-card me-2"></i>Process Payment
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
