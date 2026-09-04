<?php
require_once '../config/database.php';
requireLogin();
$page_title = 'Academic Years';
include '../includes/header.php';
include '../includes/sidebar.php';

$years = $pdo->query("SELECT * FROM academic_years ORDER BY year_name DESC")->fetchAll();
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Academic Years</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addYearModal">
            <i class="fas fa-plus me-2"></i>Add Academic Year
        </button>
    </div>

    <div class="row">
        <?php foreach ($years as $year): ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <h2 class="display-4 fw-bold text-primary"><?php echo htmlspecialchars($year['year_name']); ?></h2>
                    <?php if ($year['is_current']): ?>
                        <span class="badge bg-success mb-2">Current Year</span>
                    <?php endif; ?>
                    <p class="text-muted small">
                        <?php echo date('d M Y', strtotime($year['start_date'])); ?> - 
                        <?php echo date('d M Y', strtotime($year['end_date'])); ?>
                    </p>
                    <a href="view_academic_year.php?id=<?php echo $year['year_id']; ?>" class="btn btn-primary">
                        <i class="fas fa-eye me-1"></i>View Students
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="modal fade" id="addYearModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-calendar-plus me-2"></i>Add Academic Year</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="add_academic_year.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Year Name *</label>
                        <input type="text" name="year_name" class="form-control" placeholder="e.g., 2023" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Start Date *</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">End Date *</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_current" class="form-check-input" id="isCurrent">
                        <label class="form-check-label" for="isCurrent">Set as Current Academic Year</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Year</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
