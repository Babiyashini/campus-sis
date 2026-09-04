<?php
require_once '../config/database.php';
requireLogin();
$page_title = 'Student Profile';
include '../includes/header.php';
include '../includes/sidebar.php';

$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    header('Location: students.php');
    exit();
}

$payments = $pdo->prepare("SELECT * FROM payments WHERE student_id = ? ORDER BY payment_date DESC");
$payments->execute([$student_id]);
$studentPayments = $payments->fetchAll();

$totalPaid = array_sum(array_column($studentPayments, 'paid_amount'));
$totalDue = array_sum(array_column($studentPayments, 'due_amount'));

// Get academic records for transcript
$transcript = $pdo->prepare("
    SELECT sar.*, m.module_code, m.module_name, m.credit_hours, 
           s.semester_name, s.semester_number, ay.year_name
    FROM student_academic_records sar
    JOIN modules m ON sar.module_id = m.module_id
    JOIN semesters s ON sar.semester_id = s.semester_id
    JOIN academic_years ay ON sar.academic_year_id = ay.year_id
    WHERE sar.student_id = ?
    ORDER BY ay.year_name ASC, s.semester_number ASC
");
$transcript->execute([$student_id]);
$transcriptData = $transcript->fetchAll();

$totalCredits = 0;
$totalGradePoints = 0;
$totalModules = 0;
?>
<div class="container-fluid fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800"><i class="fas fa-user-graduate me-2 text-primary"></i>Student Profile</h1>
        <div>
            <a href="edit_student.php?id=<?php echo $student_id; ?>" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            <a href="students.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Profile Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-body text-center">
                    <div class="profile-avatar"><?php echo strtoupper(substr($student['full_name'], 0, 2)); ?></div>
                    <h4 class="profile-name"><?php echo htmlspecialchars($student['full_name']); ?></h4>
                    <p class="profile-id"><?php echo htmlspecialchars($student['student_number']); ?></p>
                    <span class="badge bg-<?php echo $student['status'] == 'active' ? 'success' : 'secondary'; ?>" style="font-size: 0.9rem; padding: 6px 14px;">
                        <?php echo ucfirst($student['status']); ?>
                    </span>
                    <p class="mt-3"><strong><?php echo htmlspecialchars($student['program']); ?></strong></p>
                    <p class="text-muted">Year <?php echo $student['year_of_study']; ?></p>
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="card shadow mt-4">
                <div class="card-header">
                    <h6 class="m-0"><i class="fas fa-credit-card me-2 text-primary"></i>Payment Summary</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Fees:</span>
                        <strong>LKR <?php echo number_format($totalPaid + $totalDue, 2); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Paid Amount:</span>
                        <strong class="text-success">LKR <?php echo number_format($totalPaid, 2); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Due Amount:</span>
                        <strong class="text-danger">LKR <?php echo number_format($totalDue, 2); ?></strong>
                    </div>
                    <a href="make_payment.php?student_id=<?php echo $student_id; ?>" class="btn btn-primary w-100 mt-3">
                        <i class="fas fa-credit-card me-2"></i>Make Payment
                    </a>
                </div>
            </div>
        </div>

        <!-- Personal Information -->
        <div class="col-xl-8 col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0"><i class="fas fa-user me-2 text-primary"></i>Personal Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Full Name</label>
                            <p><?php echo htmlspecialchars($student['full_name']); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Student ID</label>
                            <p><?php echo htmlspecialchars($student['student_number']); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">IC Number</label>
                            <p><?php echo htmlspecialchars($student['ic_number']); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Gender</label>
                            <p><?php echo htmlspecialchars($student['gender']); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Date of Birth</label>
                            <p><?php echo date('d M Y', strtotime($student['dob'])); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Email</label>
                            <p><a href="mailto:<?php echo htmlspecialchars($student['email']); ?>"><?php echo htmlspecialchars($student['email']); ?></a></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Phone</label>
                            <p><?php echo htmlspecialchars($student['phone']); ?></p>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="fw-bold">Address</label>
                            <p><?php echo nl2br(htmlspecialchars($student['address'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Student Transcript -->
            <div class="card shadow mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0"><i class="fas fa-file-alt me-2 text-success"></i>Student Transcript</h6>
                    <a href="transcript.php?student_id=<?php echo $student_id; ?>" class="btn btn-sm btn-success" target="_blank">
                        <i class="fas fa-print me-1"></i>Print Transcript
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($transcriptData)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Year</th>
                                        <th>Semester</th>
                                        <th>Module Code</th>
                                        <th>Module Name</th>
                                        <th style="text-align:center;">Credits</th>
                                        <th style="text-align:center;">Grade</th>
                                        <th style="text-align:center;">Grade Points</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transcriptData as $record): 
                                        $totalCredits += $record['credit_hours'];
                                        $totalModules++;
                                        if ($record['grade_points']) {
                                            $totalGradePoints += $record['grade_points'];
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($record['year_name']); ?></td>
                                        <td><?php echo htmlspecialchars($record['semester_name']); ?></td>
                                        <td><strong><?php echo htmlspecialchars($record['module_code']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($record['module_name']); ?></td>
                                        <td style="text-align:center;"><?php echo $record['credit_hours']; ?></td>
                                        <td style="text-align:center;">
                                            <?php if ($record['grade']): ?>
                                                <span class="badge bg-<?php echo $record['grade'] >= 'B' ? 'success' : ($record['grade'] >= 'C' ? 'warning' : 'danger'); ?>" style="font-size: 0.9rem; padding: 6px 14px;">
                                                    <?php echo htmlspecialchars($record['grade']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align:center;"><?php echo $record['grade_points'] ? number_format($record['grade_points'], 2) : '-'; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                        <td style="text-align:center;"><strong><?php echo $totalCredits; ?> Credits</strong></td>
                                        <td colspan="2" style="text-align:center;">
                                            <strong>GPA: <?php echo $totalGradePoints > 0 ? number_format($totalGradePoints / $totalModules, 2) : 'N/A'; ?></strong>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted py-3">No transcript data available. Add academic records to generate transcript.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
