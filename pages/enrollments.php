<?php
require_once '../config/database.php';
requireLogin();
$page_title = 'Enrollments';
include '../includes/header.php';
include '../includes/sidebar.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM enrollments WHERE enrollment_id = ?")->execute([$id]);
    header('Location: enrollments.php?deleted=1');
    exit();
}

// Get all enrollments with details - FIXED QUERY (removed program_name)
$enrollments = $pdo->query("
    SELECT e.*, 
           s.full_name, 
           s.student_number, 
           s.program,
           c.course_name, 
           c.course_code
    FROM enrollments e 
    JOIN students s ON e.student_id = s.student_id 
    JOIN courses c ON e.course_id = c.course_id
    ORDER BY e.created_at DESC
")->fetchAll();

// Get students for dropdown
$students = $pdo->query("SELECT student_id, student_number, full_name FROM students ORDER BY full_name")->fetchAll();
$courses = $pdo->query("SELECT course_id, course_code, course_name FROM courses ORDER BY course_code")->fetchAll();

// Handle Add Enrollment
$message = '';
$messageType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_enrollment'])) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO enrollments (student_id, course_id, semester, year, status, enrollment_date)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $result = $stmt->execute([
            (int)$_POST['student_id'],
            (int)$_POST['course_id'],
            (int)$_POST['semester'],
            date('Y'),
            $_POST['status']
        ]);
        if ($result) {
            $message = 'Enrollment added successfully!';
            $messageType = 'success';
        }
    } catch (PDOException $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Handle Edit Enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_enrollment'])) {
    try {
        $stmt = $pdo->prepare("
            UPDATE enrollments SET grade = ?, status = ? WHERE enrollment_id = ?
        ");
        $result = $stmt->execute([
            $_POST['grade'],
            $_POST['status'],
            (int)$_POST['enrollment_id']
        ]);
        if ($result) {
            $message = 'Enrollment updated successfully!';
            $messageType = 'success';
            // Refresh data
            $enrollments = $pdo->query("
                SELECT e.*, s.full_name, s.student_number, c.course_name, c.course_code
                FROM enrollments e 
                JOIN students s ON e.student_id = s.student_id 
                JOIN courses c ON e.course_id = c.course_id
                ORDER BY e.created_at DESC
            ")->fetchAll();
        }
    } catch (PDOException $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'danger';
    }
}
?>
<div class="container-fluid fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800"><i class="fas fa-clipboard-list me-2 text-primary"></i>Enrollment Management</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEnrollmentModal">
            <i class="fas fa-plus me-2"></i>New Enrollment
        </button>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
            <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Enrollment deleted successfully!</div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($enrollments); ?></div>
                <div class="stat-label">Total Enrollments</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($enrollments, function($e) { return $e['status'] == 'enrolled'; })); ?></div>
                <div class="stat-label">Active Enrollments</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($enrollments, function($e) { return $e['status'] == 'completed'; })); ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($enrollments, function($e) { return !empty($e['grade']); })); ?></div>
                <div class="stat-label">Graded</div>
            </div>
        </div>
    </div>

    <!-- Rest of the page remains the same -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Semester</th>
                            <th>Year</th>
                            <th>Grade</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrollments as $enrollment): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($enrollment['full_name']); ?></strong>
                                <br><small class="text-muted"><?php echo htmlspecialchars($enrollment['student_number']); ?></small>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($enrollment['course_code']); ?></strong>
                                <br><small class="text-muted"><?php echo htmlspecialchars($enrollment['course_name']); ?></small>
                            </td>
                            <td>Semester <?php echo $enrollment['semester']; ?></td>
                            <td><?php echo $enrollment['year']; ?></td>
                            <td>
                                <?php if ($enrollment['grade']): ?>
                                    <span class="badge bg-<?php echo $enrollment['grade'] >= 'B' ? 'success' : ($enrollment['grade'] >= 'C' ? 'warning' : 'danger'); ?>">
                                        <?php echo htmlspecialchars($enrollment['grade']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Not Graded</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $enrollment['status'] == 'completed' ? 'success' : ($enrollment['status'] == 'enrolled' ? 'primary' : 'danger'); ?>">
                                    <?php echo ucfirst($enrollment['status']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="editEnrollment(<?php echo $enrollment['enrollment_id']; ?>, '<?php echo $enrollment['grade']; ?>', '<?php echo $enrollment['status']; ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="enrollments.php?delete=<?php echo $enrollment['enrollment_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this enrollment?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($enrollments)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No enrollments found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Enrollment Modal -->
<div class="modal fade" id="addEnrollmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2 text-primary"></i>Add New Enrollment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
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
                    <div class="mb-3">
                        <label class="form-label">Course *</label>
                        <select name="course_id" class="form-select" required>
                            <option value="">Select Course</option>
                            <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['course_id']; ?>">
                                <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester *</label>
                        <input type="number" name="semester" class="form-control" min="1" max="8" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="enrolled">Enrolled</option>
                            <option value="completed">Completed</option>
                            <option value="withdrawn">Withdrawn</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_enrollment" class="btn btn-primary">Save Enrollment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Enrollment Modal -->
<div class="modal fade" id="editEnrollmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2 text-warning"></i>Edit Enrollment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="enrollment_id" id="edit_enrollment_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Grade</label>
                        <select name="grade" id="edit_grade" class="form-select">
                            <option value="">Not Graded</option>
                            <option value="A+">A+</option>
                            <option value="A">A</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B">B</option>
                            <option value="B-">B-</option>
                            <option value="C+">C+</option>
                            <option value="C">C</option>
                            <option value="C-">C-</option>
                            <option value="D+">D+</option>
                            <option value="D">D</option>
                            <option value="D-">D-</option>
                            <option value="F">F</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="edit_status" class="form-select">
                            <option value="enrolled">Enrolled</option>
                            <option value="completed">Completed</option>
                            <option value="withdrawn">Withdrawn</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_enrollment" class="btn btn-warning">Update Enrollment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editEnrollment(id, grade, status) {
    document.getElementById('edit_enrollment_id').value = id;
    document.getElementById('edit_grade').value = grade || '';
    document.getElementById('edit_status').value = status || 'enrolled';
    new bootstrap.Modal(document.getElementById('editEnrollmentModal')).show();
}
</script>
<?php include '../includes/footer.php'; ?>
