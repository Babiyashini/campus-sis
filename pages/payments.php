<?php
require_once '../config/database.php';
requireLogin();
$page_title = 'Payments';
include '../includes/header.php';
include '../includes/sidebar.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM payments WHERE payment_id = ?")->execute([$id]);
    header('Location: payments.php?deleted=1');
    exit();
}

// Get all payments
$payments = $pdo->query("
    SELECT p.*, s.full_name, s.student_number, s.program
    FROM payments p 
    JOIN students s ON p.student_id = s.student_id 
    ORDER BY p.payment_date DESC
")->fetchAll();

// Get students for dropdown
$students = $pdo->query("SELECT student_id, student_number, full_name FROM students ORDER BY full_name")->fetchAll();

// Handle Add Payment
$message = '';
$messageType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_payment'])) {
    try {
        $invoice_no = generateInvoiceNumber();
        $amount = (float)$_POST['amount'];
        $total = (float)$_POST['total_amount'];
        $due = $total - $amount;
        $status = $due <= 0 ? 'paid' : ($amount > 0 ? 'partial' : 'due');
        
        $stmt = $pdo->prepare("
            INSERT INTO payments (invoice_no, student_id, payment_date, total_amount, paid_amount, due_amount, status, payment_method, description)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([
            $invoice_no,
            (int)$_POST['student_id'],
            $_POST['payment_date'],
            $total,
            $amount,
            $due,
            $status,
            $_POST['payment_method'],
            sanitize($_POST['description'])
        ]);
        if ($result) {
            $message = 'Payment recorded successfully! Invoice: ' . $invoice_no;
            $messageType = 'success';
            // Refresh data
            $payments = $pdo->query("
                SELECT p.*, s.full_name, s.student_number, s.program
                FROM payments p 
                JOIN students s ON p.student_id = s.student_id 
                ORDER BY p.payment_date DESC
            ")->fetchAll();
        }
    } catch (PDOException $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Calculate totals
$totalAmount = array_sum(array_column($payments, 'total_amount'));
$totalPaid = array_sum(array_column($payments, 'paid_amount'));
$totalDue = array_sum(array_column($payments, 'due_amount'));
$totalTransactions = count($payments);
$pendingCount = count(array_filter($payments, function($p) { return $p['status'] != 'paid'; }));
?>
<div class="container-fluid fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800"><i class="fas fa-credit-card me-2 text-primary"></i>Payment Management</h1>
        <div>
            <a href="payments.php?export=csv" class="btn btn-success me-2">
                <i class="fas fa-file-export me-2"></i>Export
            </a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                <i class="fas fa-plus me-2"></i>New Payment
            </button>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
            <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>Payment deleted successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number">LKR <?php echo number_format($totalAmount, 0); ?></div>
                <div class="stat-label">Total Invoices</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-left: 4px solid var(--success-color);">
                <div class="stat-number text-success">LKR <?php echo number_format($totalPaid, 0); ?></div>
                <div class="stat-label">Total Paid</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-left: 4px solid var(--danger-color);">
                <div class="stat-number text-danger">LKR <?php echo number_format($totalDue, 0); ?></div>
                <div class="stat-label">Total Due</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-left: 4px solid var(--warning-color);">
                <div class="stat-number"><?php echo $pendingCount; ?></div>
                <div class="stat-label">Pending Payments</div>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="paymentsTable">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Student</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Due</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($payments)): ?>
                            <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($payment['invoice_no']); ?></strong>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($payment['full_name']); ?></strong>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($payment['student_number']); ?></small>
                                </td>
                                <td><?php echo date('d M Y', strtotime($payment['payment_date'])); ?></td>
                                <td><strong>LKR <?php echo number_format($payment['total_amount'], 2); ?></strong></td>
                                <td class="text-success">LKR <?php echo number_format($payment['paid_amount'], 2); ?></td>
                                <td class="text-danger">LKR <?php echo number_format($payment['due_amount'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $payment['status'] == 'paid' ? 'success' : ($payment['status'] == 'partial' ? 'warning' : 'danger'); ?>" style="font-size: 0.85rem; padding: 6px 14px;">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="make_payment.php?student_id=<?php echo $payment['student_id']; ?>" class="btn btn-sm btn-primary" title="Add Payment">
                                        <i class="fas fa-plus"></i>
                                    </a>
                                    <a href="payments.php?delete=<?php echo $payment['payment_id']; ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Delete this payment permanently?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-credit-card fa-3x mb-3 d-block text-muted"></i>
                                    No payments recorded. Click "New Payment" to add one.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($payments)): ?>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="3" class="text-end">TOTALS:</td>
                            <td>LKR <?php echo number_format($totalAmount, 2); ?></td>
                            <td class="text-success">LKR <?php echo number_format($totalPaid, 2); ?></td>
                            <td class="text-danger">LKR <?php echo number_format($totalDue, 2); ?></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-credit-card me-2 text-primary"></i>Record New Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Student *</label>
                            <select name="student_id" class="form-select" required>
                                <option value="">Select Student</option>
                                <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['student_id']; ?>">
                                    <?php echo htmlspecialchars($student['student_number'] . ' - ' . $student['full_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Date *</label>
                            <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
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
                            <small class="text-muted">Enter 0 for due payments</small>
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
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="paid">Paid</option>
                                <option value="partial">Partial</option>
                                <option value="due">Due</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Optional notes about this payment"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_payment" class="btn btn-primary">
                        <i class="fas fa-credit-card me-2"></i>Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>