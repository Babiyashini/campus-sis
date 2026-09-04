<?php
require_once '../config/database.php';
requireLogin();
$page_title = 'Students';
include '../includes/header.php';
include '../includes/sidebar.php';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM students WHERE student_id = ?")->execute([$id]);
    header('Location: students.php?deleted=1');
    exit();
}

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$program_filter = isset($_GET['program']) ? sanitize($_GET['program']) : '';

$query = "SELECT * FROM students WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (full_name LIKE ? OR student_number LIKE ? OR email LIKE ? OR ic_number LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
}

if (!empty($program_filter)) {
    $query .= " AND program = ?";
    $params[] = $program_filter;
}

$query .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();

// Get programs for filter
$programs = $pdo->query("SELECT DISTINCT program FROM students ORDER BY program")->fetchAll();

// Get statistics
$totalStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$activeStudents = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'active'")->fetchColumn();
$newThisMonth = $pdo->query("SELECT COUNT(*) FROM students WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())")->fetchColumn();
?>
<div class="container-fluid fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800"><i class="fas fa-user-graduate me-2 text-primary"></i>Student Management</h1>
        <div>
            <a href="students.php?export=csv" class="btn btn-success me-2">
                <i class="fas fa-file-export me-2"></i>Export
            </a>
            <a href="add_student.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add Student
            </a>
        </div>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>Student deleted successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon primary"><i class="fas fa-users"></i></div>
                <div class="stat-number"><?php echo number_format($totalStudents); ?></div>
                <div class="stat-label">Total Students</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon success"><i class="fas fa-user-check"></i></div>
                <div class="stat-number"><?php echo number_format($activeStudents); ?></div>
                <div class="stat-label">Active Students</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon info"><i class="fas fa-user-plus"></i></div>
                <div class="stat-number"><?php echo number_format($newThisMonth); ?></div>
                <div class="stat-label">New This Month</div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search by name, ID, email, or IC..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="program" class="form-select" onchange="this.form.submit()">
                        <option value="">All Programs</option>
                        <?php foreach ($programs as $p): ?>
                            <option value="<?php echo htmlspecialchars($p['program']); ?>" <?php echo $program_filter == $p['program'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['program']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="students.php" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
                <div class="col-md-2">
                    <span class="text-muted"><?php echo count($students); ?> results</span>
                </div>
            </form>
        </div>
    </div>

    <!-- Students Table -->
    <div class="card shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Program</th>
                            <th>Year</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($students)): ?>
                            <?php foreach ($students as $student): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($student['student_number']); ?></strong>
                                </td>
                                <td>
                                    <a href="view_student.php?id=<?php echo $student['student_id']; ?>" class="text-decoration-none fw-bold">
                                        <?php echo htmlspecialchars($student['full_name']); ?>
                                    </a>
                                    <?php if ($student['gender'] == 'Male'): ?>
                                        <span class="text-primary">♂</span>
                                    <?php else: ?>
                                        <span class="text-danger">♀</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($student['program']); ?></td>
                                <td><?php echo $student['year_of_study']; ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $student['status'] == 'active' ? 'success' : ($student['status'] == 'graduated' ? 'info' : 'secondary'); ?>" style="font-size: 0.85rem; padding: 6px 14px;">
                                        <?php echo ucfirst($student['status']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="view_student.php?id=<?php echo $student['student_id']; ?>" class="btn btn-sm btn-info" title="View Profile">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit_student.php?id=<?php echo $student['student_id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="students.php?delete=<?php echo $student['student_id']; ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Delete this student permanently?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-users fa-3x mb-3 d-block text-muted"></i>
                                    No students found. <a href="add_student.php">Add your first student</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>