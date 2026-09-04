<?php
require_once '../config/database.php';
requireLogin();
$page_title = 'View Academic Year';
include '../includes/header.php';
include '../includes/sidebar.php';

$year_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM academic_years WHERE year_id = ?");
$stmt->execute([$year_id]);
$year = $stmt->fetch();

if (!$year) {
    header('Location: academic_years.php');
    exit();
}

$students = $pdo->prepare("
    SELECT DISTINCT s.* 
    FROM students s
    JOIN student_academic_records sar ON s.student_id = sar.student_id
    WHERE sar.academic_year_id = ?
    ORDER BY s.full_name
");
$students->execute([$year_id]);
$studentList = $students->fetchAll();
?>
<div class="container-fluid fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Academic Year: <?php echo htmlspecialchars($year['year_name']); ?></h1>
        <a href="academic_years.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0"><i class="fas fa-calendar me-2 text-primary"></i>Year Details</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Year Name</label>
                            <p><?php echo htmlspecialchars($year['year_name']); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Status</label>
                            <p><span class="badge bg-<?php echo $year['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                <?php echo ucfirst($year['status']); ?>
                            </span></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Start Date</label>
                            <p><?php echo date('d M Y', strtotime($year['start_date'])); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">End Date</label>
                            <p><?php echo date('d M Y', strtotime($year['end_date'])); ?></p>
                        </div>
                        <div class="col-12">
                            <label class="fw-bold">Current Year</label>
                            <p><?php echo $year['is_current'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>'; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0"><i class="fas fa-users me-2 text-primary"></i>Students (<?php echo count($studentList); ?>)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Program</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($studentList as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['student_number']); ?></td>
                                    <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['program']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($studentList)): ?>
                                <tr><td colspan="3" class="text-center py-3 text-muted">No students enrolled this year.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
