<?php
require_once '../config/database.php';
requireLogin();
$page_title = 'Dashboard';
include '../includes/header.php';
include '../includes/sidebar.php';

// Student Statistics
$totalStudents = $pdo->query("SELECT COUNT(*) as count FROM students")->fetch()['count'];
$activeStudents = $pdo->query("SELECT COUNT(*) as count FROM students WHERE status = 'active'")->fetch()['count'];
$newThisMonth = $pdo->query("SELECT COUNT(*) as count FROM students WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())")->fetch()['count'];

$totalPrograms = $pdo->query("SELECT COUNT(DISTINCT program) as count FROM students")->fetch()['count'];
$totalCourses = $pdo->query("SELECT COUNT(*) as count FROM courses")->fetch()['count'];
$totalModules = $pdo->query("SELECT COUNT(*) as count FROM modules")->fetch()['count'];

// Program Distribution
$programDistribution = $pdo->query("
    SELECT program, COUNT(*) as count 
    FROM students 
    GROUP BY program 
    ORDER BY count DESC
")->fetchAll();

// Recent Students
$recentStudents = $pdo->query("
    SELECT * FROM students 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetchAll();

// Upcoming Payments
$upcomingPayments = $pdo->query("
    SELECT p.*, s.full_name, s.student_number 
    FROM payments p 
    JOIN students s ON p.student_id = s.student_id 
    WHERE p.status IN ('due', 'partial')
    ORDER BY p.payment_date ASC 
    LIMIT 5
")->fetchAll();

// Gender Distribution
$genderStats = $pdo->query("
    SELECT gender, COUNT(*) as count 
    FROM students 
    GROUP BY gender
")->fetchAll();
?>
<div class="container-fluid fade-in">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient-primary text-white" style="background: linear-gradient(135deg, #4361ee, #7209b7);">
                <div class="card-body py-4 px-5">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="text-white fw-bold mb-1">Welcome back, <?php echo sanitize($_SESSION['full_name']); ?>! 👋</h1>
                            <p class="text-white-50 mb-0 fs-5">Here's what's happening with your students today.</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <i class="fas fa-graduation-cap fa-4x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon primary"><i class="fas fa-users"></i></div>
                <div class="stat-number"><?php echo number_format($totalStudents); ?></div>
                <div class="stat-label">Total Students</div>
                <div class="stat-change up"><i class="fas fa-arrow-up"></i> <?php echo $newThisMonth; ?> new this month</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon success"><i class="fas fa-user-check"></i></div>
                <div class="stat-number"><?php echo number_format($activeStudents); ?></div>
                <div class="stat-label">Active Students</div>
                <div class="stat-change up"><i class="fas fa-arrow-up"></i> <?php echo round(($activeStudents / max($totalStudents, 1)) * 100); ?>% of total</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon info"><i class="fas fa-book-open"></i></div>
                <div class="stat-number"><?php echo number_format($totalPrograms); ?></div>
                <div class="stat-label">Programs Offered</div>
                <div class="stat-change up"><i class="fas fa-arrow-up"></i> <?php echo $totalCourses; ?> total courses</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon warning"><i class="fas fa-credit-card"></i></div>
                <div class="stat-number"><?php echo $pdo->query("SELECT COUNT(*) FROM payments WHERE status != 'paid'")->fetchColumn(); ?></div>
                <div class="stat-label">Pending Payments</div>
                <div class="stat-change down"><i class="fas fa-arrow-down"></i> Requires attention</div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-2 text-primary"></i> Program Distribution
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Program</th>
                                    <th>Students</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($programDistribution as $program): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($program['program']); ?></td>
                                    <td><strong><?php echo $program['count']; ?></strong></td>
                                    <td>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-primary" style="width: <?php echo ($program['count'] / max($totalStudents, 1)) * 100; ?>%;"></div>
                                        </div>
                                        <small class="text-muted"><?php echo round(($program['count'] / max($totalStudents, 1)) * 100); ?>%</small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user me-2 text-success"></i> Gender Distribution
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <?php 
                        $maleCount = 0;
                        $femaleCount = 0;
                        foreach ($genderStats as $g) {
                            if ($g['gender'] == 'Male') $maleCount = $g['count'];
                            if ($g['gender'] == 'Female') $femaleCount = $g['count'];
                        }
                        ?>
                        <div class="col-6">
                            <div style="width: 100px; height: 100px; margin: 0 auto;">
                                <canvas id="genderChart"></canvas>
                            </div>
                        </div>
                        <div class="col-6 text-start">
                            <div class="mb-2">
                                <span class="badge bg-primary me-2">👤</span>
                                <strong>Male:</strong> <?php echo $maleCount; ?>
                            </div>
                            <div class="mb-2">
                                <span class="badge bg-danger me-2">👩</span>
                                <strong>Female:</strong> <?php echo $femaleCount; ?>
                            </div>
                            <div>
                                <span class="badge bg-secondary me-2">📊</span>
                                <strong>Total:</strong> <?php echo $totalStudents; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Students & Payments -->
    <div class="row g-4">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-user-plus me-2 text-primary"></i> Recent Students</span>
                    <a href="students.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Program</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentStudents as $student): ?>
                                <tr>
                                    <td>
                                        <a href="view_student.php?id=<?php echo $student['student_id']; ?>" class="text-decoration-none fw-bold">
                                            <?php echo htmlspecialchars($student['full_name']); ?>
                                        </a>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($student['student_number']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($student['program']); ?></td>
                                    <td><span class="badge bg-<?php echo $student['status'] == 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($student['status']); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recentStudents)): ?>
                                <tr><td colspan="3" class="text-center py-4 text-muted">No students registered yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-exclamation-triangle me-2 text-warning"></i> Pending Payments</span>
                    <a href="payments.php" class="btn btn-sm btn-warning">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Invoice</th>
                                    <th>Due</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($upcomingPayments as $payment): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($payment['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($payment['invoice_no']); ?></td>
                                    <td>LKR <?php echo number_format($payment['due_amount'], 2); ?></td>
                                    <td><span class="badge bg-<?php echo $payment['status'] == 'partial' ? 'warning' : 'danger'; ?>"><?php echo ucfirst($payment['status']); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($upcomingPayments)): ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">No pending payments.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gender Chart
    const ctx = document.getElementById('genderChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Male', 'Female'],
                datasets: [{
                    data: [<?php echo $maleCount; ?>, <?php echo $femaleCount; ?>],
                    backgroundColor: ['#4361ee', '#ef476f'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '70%'
            }
        });
    }
});
</script>
<?php include '../includes/footer.php'; ?>
