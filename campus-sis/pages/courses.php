<?php
require_once '../config/database.php';
requireLogin();
$page_title = 'Course Management';
include '../includes/header.php';
include '../includes/sidebar.php';

// Get all programs
$programs = $pdo->query("
    SELECT p.*, COUNT(DISTINCT s.semester_id) as semester_count 
    FROM programs p
    LEFT JOIN semesters s ON p.program_id = s.program_id
    GROUP BY p.program_id
    ORDER BY p.program_code
")->fetchAll();

// Get all semesters with program info
$semesters = $pdo->query("
    SELECT s.*, p.program_code, p.program_name 
    FROM semesters s
    JOIN programs p ON s.program_id = p.program_id
    ORDER BY p.program_code, s.semester_number
")->fetchAll();

// Get all modules with semester and program info
$modules = $pdo->query("
    SELECT m.*, s.semester_number, s.semester_name, p.program_code, p.program_name
    FROM modules m
    JOIN semesters s ON m.semester_id = s.semester_id
    JOIN programs p ON s.program_id = p.program_id
    ORDER BY p.program_code, s.semester_number, m.module_code
")->fetchAll();

// Group modules by semester
$modulesBySemester = [];
foreach ($modules as $module) {
    $key = $module['program_code'] . '_' . $module['semester_id'];
    $modulesBySemester[$key][] = $module;
}
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Academic Structure</h1>
        <div>
            <a href="add_program.php" class="btn btn-primary me-2">
                <i class="fas fa-plus me-2"></i>Add Program
            </a>
            <a href="add_course.php" class="btn btn-success me-2">
                <i class="fas fa-plus me-2"></i>Add Course
            </a>
            <a href="add_module.php" class="btn btn-info">
                <i class="fas fa-plus me-2"></i>Add Module
            </a>
        </div>
    </div>

    <!-- Programs Overview -->
    <div class="row mb-4">
        <?php foreach ($programs as $program): ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 border-left-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                <?php echo htmlspecialchars($program['program_code']); ?>
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                <?php echo htmlspecialchars($program['program_name']); ?>
                            </div>
                            <div class="mt-2">
                                <span class="badge bg-info"><?php echo $program['total_semesters']; ?> Semesters</span>
                                <span class="badge bg-success"><?php echo $program['semester_count']; ?> Active</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-graduation-cap fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Full Structure -->
    <div class="card shadow">
        <div class="card-header">
            <h6 class="m-0"><i class="fas fa-sitemap me-2"></i>Complete Academic Structure</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($programs)): ?>
                <div class="accordion" id="programAccordion">
                    <?php foreach ($programs as $pIndex => $program): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button <?php echo $pIndex > 0 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#program<?php echo $program['program_id']; ?>">
                                <strong><?php echo htmlspecialchars($program['program_code']); ?></strong>
                                <span class="ms-3"><?php echo htmlspecialchars($program['program_name']); ?></span>
                                <span class="badge bg-primary ms-3"><?php echo $program['total_semesters']; ?> Semesters</span>
                            </button>
                        </h2>
                        <div id="program<?php echo $program['program_id']; ?>" class="accordion-collapse collapse <?php echo $pIndex === 0 ? 'show' : ''; ?>" data-bs-parent="#programAccordion">
                            <div class="accordion-body">
                                <?php
                                $programSemesters = array_filter($semesters, function($s) use ($program) {
                                    return $s['program_id'] == $program['program_id'];
                                });
                                ?>
                                <?php foreach ($programSemesters as $sIndex => $semester): ?>
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">
                                            <strong><?php echo htmlspecialchars($semester['semester_name']); ?></strong>
                                            <span class="badge bg-secondary ms-2">Semester <?php echo $semester['semester_number']; ?></span>
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Module Code</th>
                                                        <th>Module Name</th>
                                                        <th>Credits</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $key = $program['program_code'] . '_' . $semester['semester_id'];
                                                    if (isset($modulesBySemester[$key])):
                                                    ?>
                                                        <?php foreach ($modulesBySemester[$key] as $module): ?>
                                                        <tr>
                                                            <td><strong><?php echo htmlspecialchars($module['module_code']); ?></strong></td>
                                                            <td><?php echo htmlspecialchars($module['module_name']); ?></td>
                                                            <td>
                                                                <span class="badge bg-<?php echo $module['credit_hours'] >= 4 ? 'warning' : 'info'; ?>">
                                                                    <?php echo $module['credit_hours']; ?> Credits
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-<?php echo $module['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                                    <?php echo ucfirst($module['status']); ?>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr><td colspan="4" class="text-center py-2 text-muted">No modules added yet.</td></tr>
                                                    <?php endif; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr class="table-light">
                                                        <td colspan="2"><strong>Total Credits:</strong></td>
                                                        <td>
                                                            <?php
                                                            $totalCredits = 0;
                                                            if (isset($modulesBySemester[$key])) {
                                                                foreach ($modulesBySemester[$key] as $m) {
                                                                    $totalCredits += $m['credit_hours'];
                                                                }
                                                            }
                                                            ?>
                                                            <strong><?php echo $totalCredits; ?> Credits</strong>
                                                        </td>
                                                        <td>
                                                            <a href="add_module.php?semester_id=<?php echo $semester['semester_id']; ?>" class="btn btn-sm btn-primary">
                                                                <i class="fas fa-plus me-1"></i>Add Module
                                                            </a>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center py-4">No programs found. <a href="add_program.php">Add one now</a>.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
