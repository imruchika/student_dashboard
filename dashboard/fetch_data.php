<?php
include("../db/config.php");

// Count total students
$students_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM students");
$students_count = mysqli_fetch_assoc($students_query)['count'];

// Count total subjects
$subjects_query = mysqli_query($conn, "SELECT COUNT(DISTINCT subject) as count FROM marks");
$subjects_count = mysqli_fetch_assoc($subjects_query)['count'];

// Calculate pass rate (students with average >= 40)
$pass_query = mysqli_query($conn, "
    SELECT 
        COUNT(CASE WHEN avg_marks >= 40 THEN 1 END) as passed,
        COUNT(*) as total
    FROM (
        SELECT student_id, AVG(g3) as avg_marks
        FROM marks
        GROUP BY student_id
    ) as student_averages
");

$pass_data = mysqli_fetch_assoc($pass_query);
$total_with_marks = $pass_data['total'] > 0 ? $pass_data['total'] : 1;
$pass_rate = round(($pass_data['passed'] / $total_with_marks) * 100, 1);

// Return JSON
echo json_encode([
    'students' => $students_count,
    'subjects' => $subjects_count,
    'pass_rate' => $pass_rate
]);
?>