<?php
require_once '../config/database.php';
requireLogin();
$page_title = 'Settings';
include '../includes/header.php';
include '../includes/sidebar.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$message = '';
$messageType = '';

// Update Profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    try {
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE user_id = ?");
        $result = $stmt->execute([
            sanitize($_POST['full_name']),
            sanitize($_POST['email']),
            sanitize($_POST['phone']),
            $user_id
        ]);
        if ($result) {
            $_SESSION['full_name'] = sanitize($_POST['full_name']);
            $_SESSION['email'] = sanitize($_POST['email']);
            $message = 'Profile updated successfully!';
            $messageType = 'success';
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        }
    } catch (PDOException $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Change Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if (password_verify($current, $user['password'])) {
        if ($new === $confirm && strlen($new) >= 6) {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $result = $stmt->execute([$hash, $user_id]);
            if ($result) {
                $message = 'Password changed successfully!';
                $messageType = 'success';
            }
        } else {
            $message = 'Passwords do not match or are too short (min 6 characters).';
            $messageType = 'danger';
        }
    } else {
        $message = 'Current password is incorrect.';
        $messageType = 'danger';
    }
}

// System Statistics
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalPayments = $pdo->query("SELECT COUNT(*) FROM payments")->fetchColumn();
$totalEnrollments = $pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();
?>
<div class="container-fluid fade-in">
    <h1 class="h3 text-gray-800 mb-4"><i class="fas fa-cog me-2 text-primary"></i>System Settings</h1>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Profile Settings -->
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header">
                    <i class="fas fa-user me-2 text-primary"></i> Profile Settings
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Profile
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Change Password -->
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header">
                    <i class="fas fa-key me-2 text-warning"></i> Change Password
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required minlength="6">
                            <small class="text-muted">Minimum 6 characters</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-warning">
                            <i class="fas fa-key me-2"></i>Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header">
                    <i class="fas fa-info-circle me-2 text-info"></i> System Information
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="display-4"><?php echo $totalUsers; ?></div>
                                <div class="text-muted">Users</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="display-4"><?php echo $totalStudents; ?></div>
                                <div class="text-muted">Students</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="display-4"><?php echo $totalEnrollments; ?></div>
                                <div class="text-muted">Enrollments</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="display-4"><?php echo $totalPayments; ?></div>
                                <div class="text-muted">Payments</div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-muted">
                        <div class="col-md-6">
                            <strong>System Name:</strong> <?php echo APP_NAME; ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Version:</strong> 2.0 Professional
                        </div>
                        <div class="col-md-6">
                            <strong>Institution:</strong> American College of Higher Education
                        </div>
                        <div class="col-md-6">
                            <strong>Last Login:</strong> <?php echo $user['last_login'] ? date('d M Y H:i', strtotime($user['last_login'])) : 'Never'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
