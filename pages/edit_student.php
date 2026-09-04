<?php
require_once '../config/database.php';
requireLogin();
$page_title = 'Edit Student';
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

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("
            UPDATE students SET
                full_name = ?, ic_number = ?, gender = ?, dob = ?,
                email = ?, phone = ?, address = ?, program = ?,
                year_of_study = ?, status = ?
            WHERE student_id = ?
        ");
        $result = $stmt->execute([
            sanitize($_POST['full_name']),
            sanitize($_POST['ic_number']),
            sanitize($_POST['gender']),
            $_POST['dob'],
            sanitize($_POST['email']),
            sanitize($_POST['phone']),
            sanitize($_POST['address']),
            sanitize($_POST['program']),
            (int)$_POST['year_of_study'],
            sanitize($_POST['status']),
            $student_id
        ]);

        if ($result) {
            $message = '<div class="alert alert-success">Student updated successfully!</div>';
            $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
            $stmt->execute([$student_id]);
            $student = $stmt->fetch();
        }
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Edit Student</h1>
        <a href="students.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>

    <?php echo $message; ?>

    <div class="card shadow">
        <div class="card-body">
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">IC Number *</label>
                        <input type="text" name="ic_number" class="form-control" value="<?php echo htmlspecialchars($student['ic_number']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone *</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($student['phone']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Gender *</label>
                        <select name="gender" class="form-select" required>
                            <option value="Male" <?php echo $student['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $student['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Date of Birth *</label>
                        <input type="date" name="dob" class="form-control" value="<?php echo $student['dob']; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Program *</label>
                        <input type="text" name="program" class="form-control" value="<?php echo htmlspecialchars($student['program']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Year of Study *</label>
                        <input type="number" name="year_of_study" class="form-control" min="1" max="4" value="<?php echo $student['year_of_study']; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?php echo $student['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="graduated" <?php echo $student['status'] == 'graduated' ? 'selected' : ''; ?>>Graduated</option>
                            <option value="suspended" <?php echo $student['status'] == 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Address *</label>
                        <textarea name="address" class="form-control" rows="2" required><?php echo htmlspecialchars($student['address']); ?></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Student
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
