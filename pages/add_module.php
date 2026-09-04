<?php
require_once '../config/database.php';
requireLogin();
$page_title = 'Add Module';
include '../includes/header.php';
include '../includes/sidebar.php';

$semesters = $pdo->query("SELECT * FROM semesters ORDER BY semester_number")->fetchAll();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO modules (module_code, module_name, semester_id, credit_hours, description)
            VALUES (?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([
            sanitize($_POST['module_code']),
            sanitize($_POST['module_name']),
            (int)$_POST['semester_id'],
            (int)$_POST['credit_hours'],
            sanitize($_POST['description'])
        ]);

        if ($result) {
            $message = '<div class="alert alert-success">Module added successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Failed to add module.</div>';
        }
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Add Module</h1>
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
                        <label class="form-label">Module Code *</label>
                        <input type="text" name="module_code" class="form-control" placeholder="e.g., IT101" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Module Name *</label>
                        <input type="text" name="module_name" class="form-control" placeholder="e.g., Introduction to Programming" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Semester *</label>
                        <select name="semester_id" class="form-select" required>
                            <option value="">Select Semester</option>
                            <?php foreach ($semesters as $semester): ?>
                            <option value="<?php echo $semester['semester_id']; ?>">
                                <?php echo htmlspecialchars($semester['semester_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Credit Hours *</label>
                        <select name="credit_hours" class="form-select" required>
                            <option value="">Select Credits</option>
                            <option value="1">1 Credit</option>
                            <option value="2">2 Credits</option>
                            <option value="3">3 Credits</option>
                            <option value="4">4 Credits</option>
                            <option value="5">5 Credits</option>
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Module
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
