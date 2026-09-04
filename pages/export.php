<?php
require_once '../config/database.php';
requireLogin();

$type = isset($_GET['type']) ? $_GET['type'] : 'students';
$format = isset($_GET['format']) ? $_GET['format'] : 'csv';

if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $type . '_export.csv"');
    
    $output = fopen('php://output', 'w');
    
    if ($type === 'students') {
        fputcsv($output, ['Student ID', 'Full Name', 'IC Number', 'Gender', 'Email', 'Phone', 'Program', 'Year', 'Status']);
        $data = $pdo->query("SELECT student_number, full_name, ic_number, gender, email, phone, program, year_of_study, status FROM students")->fetchAll();
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    } elseif ($type === 'payments') {
        fputcsv($output, ['Invoice', 'Student', 'Date', 'Total', 'Paid', 'Due', 'Status']);
        $data = $pdo->query("
            SELECT p.invoice_no, s.full_name, p.payment_date, p.total_amount, p.paid_amount, p.due_amount, p.status 
            FROM payments p JOIN students s ON p.student_id = s.student_id
        ")->fetchAll();
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    } elseif ($type === 'enrollments') {
        fputcsv($output, ['Student', 'Course', 'Semester', 'Year', 'Grade', 'Status']);
        $data = $pdo->query("
            SELECT s.full_name, c.course_code, e.semester, e.year, e.grade, e.status 
            FROM enrollments e 
            JOIN students s ON e.student_id = s.student_id 
            JOIN courses c ON e.course_id = c.course_id
        ")->fetchAll();
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    fclose($output);
    exit();
}
?>
