<?php requireLogin(); ?>
<nav id="sidebar" class="sidebar">
    <div class="sidebar-header">
        <span class="logo-icon">🎓</span>
        <h4><?php echo APP_NAME; ?></h4>
        <div class="subtitle">American College of Higher Education</div>
    </div>

    <ul class="nav flex-column">
        <div class="nav-label">Main</div>
        <li class="nav-item">
            <a href="<?php echo APP_URL; ?>pages/dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a>
        </li>

        <div class="nav-label">Student Management</div>
        <li class="nav-item">
            <a href="<?php echo APP_URL; ?>pages/students.php" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['students.php', 'add_student.php', 'edit_student.php', 'view_student.php']) ? 'active' : ''; ?>">
                <i class="fas fa-user-graduate"></i> Students
                <span class="badge"><?php echo $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn(); ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?php echo APP_URL; ?>pages/academic_years.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'academic_years.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i> Academic Years
            </a>
        </li>

        <div class="nav-label">Academic</div>
        <li class="nav-item">
            <a href="<?php echo APP_URL; ?>pages/courses.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'courses.php' ? 'active' : ''; ?>">
                <i class="fas fa-book-open"></i> Programs & Courses
            </a>
        </li>
        <li class="nav-item">
            <a href="<?php echo APP_URL; ?>pages/enrollments.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'enrollments.php' ? 'active' : ''; ?>">
                <i class="fas fa-clipboard-list"></i> Enrollments
            </a>
        </li>

        <div class="nav-label">Finance</div>
        <li class="nav-item">
            <a href="<?php echo APP_URL; ?>pages/payments.php" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['payments.php', 'make_payment.php']) ? 'active' : ''; ?>">
                <i class="fas fa-credit-card"></i> Payments
                <span class="badge"><?php echo $pdo->query("SELECT COUNT(*) FROM payments WHERE status != 'paid'")->fetchColumn(); ?></span>
            </a>
        </li>

        <div class="nav-label">System</div>
        <li class="nav-item">
            <a href="<?php echo APP_URL; ?>pages/settings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i> Settings
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 2)); ?></div>
            <div>
                <div class="user-name"><?php echo sanitize($_SESSION['full_name'] ?? 'User'); ?></div>
                <div class="user-role"><?php echo ucfirst($_SESSION['role'] ?? 'staff'); ?></div>
            </div>
            <a href="<?php echo APP_URL; ?>auth/logout.php" class="ms-auto text-white-50" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</nav>
<div id="content" class="content">
    <nav class="top-navbar navbar navbar-expand-lg">
        <div class="container-fluid">
            <button type="button" id="sidebarCollapse" class="btn btn-outline-secondary border-0">
                <i class="fas fa-bars fs-5"></i>
            </button>
            <div class="ms-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>pages/dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo $page_title ?? 'Dashboard'; ?></li>
                    </ol>
                </nav>
            </div>
            <div class="user-menu ms-auto">
                <span class="user-name-sm"><?php echo sanitize($_SESSION['full_name'] ?? 'User'); ?></span>
                <div class="user-avatar-sm"><?php echo strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 2)); ?></div>
            </div>
        </div>
    </nav>
    <div class="content-body">
