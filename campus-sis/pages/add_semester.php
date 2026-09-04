<?php
require_once '../config/database.php';
requireLogin();
$page_title = 'Add Semester';
include '../includes/header.php';
include '../includes/sidebar.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO semesters (semester_number, semester_name, description)
            VALUES (?, ?, ?)
        ");
        $result = $stmt->execute([
            (int)$_POST['semester_number'],
            sanitize($_POST['semester_name']),
            sanitize($_POST['description'])
        ]);

        if ($result) {
            $message = '<div class="alert alert-success">Semester added successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Failed to add semester.</div>';
        }
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Add Semester</h1>
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
                        <label class="form-label">Semester Number *</label>
                        <input type="number" name="semester_number" class="form-control" min="1" max="8" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Semester Name *</label>
                        <input type="text" name="semester_name" class="form-control" placeholder="e.g., Semester 1" required>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>Save Semester
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
