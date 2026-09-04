<?php
require_once '../config/database.php';
requireLogin();
$page_title = 'Add Program';
include '../includes/header.php';
include '../includes/sidebar.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO programs (program_code, program_name, total_semesters, description)
            VALUES (?, ?, ?, ?)
        ");
        $result = $stmt->execute([
            sanitize($_POST['program_code']),
            sanitize($_POST['program_name']),
            (int)$_POST['total_semesters'],
            sanitize($_POST['description'])
        ]);

        if ($result) {
            $message = '<div class="alert alert-success">Program added successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Failed to add program.</div>';
        }
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Add Program</h1>
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
                        <label class="form-label">Program Code *</label>
                        <input type="text" name="program_code" class="form-control" placeholder="e.g., BIT" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Program Name *</label>
                        <input type="text" name="program_name" class="form-control" placeholder="e.g., Bachelors in Information Technology" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Total Semesters *</label>
                        <select name="total_semesters" class="form-select" required>
                            <option value="">Select</option>
                            <option value="6">6 Semesters (Foundation)</option>
                            <option value="8">8 Semesters (Bachelor)</option>
                            <option value="10">10 Semesters (Professional)</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Program
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
