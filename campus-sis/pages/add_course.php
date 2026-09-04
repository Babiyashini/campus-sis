<?php
require_once '../config/database.php';
requireLogin();
$page_title = 'Add Course';
include '../includes/header.php';
include '../includes/sidebar.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO courses (course_code, course_name, program, credit_hours, semester, year, description)
            VALUES (?, ?, ?, ?, ?, YEAR(NOW()), ?)
        ");
        $result = $stmt->execute([
            sanitize($_POST['course_code']),
            sanitize($_POST['course_name']),
            sanitize($_POST['program']),
            (int)$_POST['credit_hours'],
            (int)$_POST['semester'],
            sanitize($_POST['description'])
        ]);

        if ($result) {
            $message = '<div class="alert alert-success">Course added successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Failed to add course.</div>';
        }
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Add Course</h1>
        <a href="courses.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>

    <?php echo $message; ?>

    <div class="card shadow">
        <div class="card-body">
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Course Code *</label>
                        <input type="text" name="course_code" class="form-control" placeholder="e.g., IT101" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Course Name *</label>
                        <input type="text" name="course_name" class="form-control" placeholder="e.g., Introduction to Programming" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Program *</label>
                        <select name="program" class="form-select" required>
                            <option value="">Select Program</option>
                            <option value="Bachelor in Information Technology">BIT</option>
                            <option value="Bachelor in Business Administration">BBA</option>
                            <option value="Bachelor in Computer Science">BSc CS</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Credit Hours *</label>
                        <input type="number" name="credit_hours" class="form-control" min="1" max="5" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Semester *</label>
                        <input type="number" name="semester" class="form-control" min="1" max="8" required>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Course
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-undo me-2"></i>Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
