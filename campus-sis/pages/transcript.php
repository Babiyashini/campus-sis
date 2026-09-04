<?php
require_once '../config/database.php';
requireLogin();

$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    header('Location: students.php');
    exit();
}

$transcript = $pdo->prepare("
    SELECT sar.*, m.module_code, m.module_name, m.credit_hours, 
           s.semester_name, s.semester_number, ay.year_name
    FROM student_academic_records sar
    JOIN modules m ON sar.module_id = m.module_id
    JOIN semesters s ON sar.semester_id = s.semester_id
    JOIN academic_years ay ON sar.academic_year_id = ay.year_id
    WHERE sar.student_id = ?
    ORDER BY ay.year_name ASC, s.semester_number ASC
");
$transcript->execute([$student_id]);
$transcriptData = $transcript->fetchAll();

$totalCredits = 0;
$totalGradePoints = 0;
$totalModules = 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Transcript - <?php echo htmlspecialchars($student['full_name']); ?></title>
    <style>
        body { font-family: "Times New Roman", serif; padding: 40px; }
        .header { text-align: center; border-bottom: 3px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { font-size: 24px; margin: 0; }
        .header p { margin: 5px 0; color: #666; }
        .student-info { margin-bottom: 20px; }
        .student-info table { width: 100%; }
        .student-info td { padding: 5px 10px; }
        .student-info .label { font-weight: bold; width: 150px; }
        table.transcript { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table.transcript th { background: #333; color: #fff; padding: 10px; text-align: left; }
        table.transcript td { padding: 8px 10px; border-bottom: 1px solid #ddd; }
        table.transcript tfoot { font-weight: bold; background: #f5f5f5; }
        .footer { text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>American College of Higher Education</h1>
        <p>Student Academic Transcript</p>
    </div>

    <div class="student-info">
        <table>
            <tr>
                <td class="label">Student Name:</td>
                <td><strong><?php echo htmlspecialchars($student['full_name']); ?></strong></td>
                <td class="label">Student ID:</td>
                <td><?php echo htmlspecialchars($student['student_number']); ?></td>
            </tr>
            <tr>
                <td class="label">Program:</td>
                <td><?php echo htmlspecialchars($student['program']); ?></td>
                <td class="label">Year of Study:</td>
                <td><?php echo $student['year_of_study']; ?></td>
            </tr>
            <tr>
                <td class="label">Date of Birth:</td>
                <td><?php echo date('d M Y', strtotime($student['dob'])); ?></td>
                <td class="label">Status:</td>
                <td><?php echo ucfirst($student['status']); ?></td>
            </tr>
        </table>
    </div>

    <?php if (!empty($transcriptData)): ?>
    <table class="transcript">
        <thead>
            <tr>
                <th>Year</th>
                <th>Semester</th>
                <th>Module Code</th>
                <th>Module Name</th>
                <th style="text-align:center;">Credits</th>
                <th style="text-align:center;">Grade</th>
                <th style="text-align:center;">Grade Points</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transcriptData as $record): 
                $totalCredits += $record['credit_hours'];
                $totalModules++;
                if ($record['grade_points']) {
                    $totalGradePoints += $record['grade_points'];
                }
            ?>
            <tr>
                <td><?php echo htmlspecialchars($record['year_name']); ?></td>
                <td><?php echo htmlspecialchars($record['semester_name']); ?></td>
                <td><strong><?php echo htmlspecialchars($record['module_code']); ?></strong></td>
                <td><?php echo htmlspecialchars($record['module_name']); ?></td>
                <td style="text-align:center;"><?php echo $record['credit_hours']; ?></td>
                <td style="text-align:center;"><?php echo $record['grade'] ?: '-'; ?></td>
                <td style="text-align:center;"><?php echo $record['grade_points'] ? number_format($record['grade_points'], 2) : '-'; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align:right;"><strong>Totals:</strong></td>
                <td style="text-align:center;"><strong><?php echo $totalCredits; ?> Credits</strong></td>
                <td colspan="2" style="text-align:center;">
                    <strong>GPA: <?php echo $totalGradePoints > 0 ? number_format($totalGradePoints / $totalModules, 2) : 'N/A'; ?></strong>
                </td>
            </tr>
        </tfoot>
    </table>
    <?php else: ?>
        <p style="text-align:center; color:#999; padding:40px;">No academic records found for this student.</p>
    <?php endif; ?>

    <div class="footer">
        <p>This is a system-generated transcript. For official use, please contact the Registrar's Office.</p>
        <p>Generated on: <?php echo date('d M Y H:i:s'); ?></p>
    </div>

    <div class="no-print" style="text-align:center; margin-top:20px;">
        <button onclick="window.print()" style="padding:12px 30px; background:#4361ee; color:#fff; border:none; border-radius:8px; font-size:16px; cursor:pointer;">
            Print Transcript
        </button>
        <button onclick="window.close()" style="padding:12px 30px; background:#6c757d; color:#fff; border:none; border-radius:8px; font-size:16px; cursor:pointer; margin-left:10px;">
            Close
        </button>
    </div>
</body>
</html>
