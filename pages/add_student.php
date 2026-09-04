<?php
require_once '../config/database.php';
requireLogin();
$page_title = 'Add Student';
include '../includes/header.php';
include '../includes/sidebar.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $student_number = generateStudentNumber();
        $stmt = $pdo->prepare("
            INSERT INTO students (
                student_number, full_name, ic_number, gender, dob, email, phone,
                address, program, year_of_study, enrollment_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $result = $stmt->execute([
            $student_number,
            sanitize($_POST['full_name']),
            sanitize($_POST['ic_number']),
            sanitize($_POST['gender']),
            $_POST['dob'],
            sanitize($_POST['email']),
            sanitize($_POST['phone']),
            sanitize($_POST['address']),
            sanitize($_POST['program']),
            (int)$_POST['year_of_study']
        ]);

        if ($result) {
            $message = '<div class="alert alert-success">Student added successfully! Student ID: ' . $student_number . '</div>';
        } else {
            $message = '<div class="alert alert-danger">Failed to add student.</div>';
        }
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Add Student</h1>
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
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">IC Number *</label>
                        <input type="text" name="ic_number" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone *</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Gender *</label>
                        <select name="gender" class="form-select" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Date of Birth *</label>
                        <input type="date" name="dob" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Program *</label>
                        <input type="text" name="program" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Year of Study *</label>
                        <input type="number" name="year_of_study" class="form-control" min="1" max="4" required>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Address *</label>
                        <textarea name="address" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Add Student
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
