<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../page1/index.php");
    exit();
}
include("../db/config.php");

/*
  Performance data:
  - Sum of 5 subjects: C++, Java, DBMS, Python, DSA
  - Bonus 5 marks if extracurricular not empty
  - Overall percentage = (total_with_bonus / (5 * 100)) * 100
  - Grade & status based on percentage
*/

$query = "
SELECT
    s.student_id,
    s.name,
    s.class,
    s.section,
    s.year,
    s.semester,
    s.attendance_percentage,
    s.extracurricular,
    SUM(CASE WHEN m.subject = 'C++' THEN m.g3 ELSE 0 END) AS cpp_marks,
    SUM(CASE WHEN m.subject = 'Java' THEN m.g3 ELSE 0 END) AS java_marks,
    SUM(CASE WHEN m.subject = 'DBMS' THEN m.g3 ELSE 0 END) AS dbms_marks,
    SUM(CASE WHEN m.subject = 'Python' THEN m.g3 ELSE 0 END) AS python_marks,
    SUM(CASE WHEN m.subject = 'DSA' THEN m.g3 ELSE 0 END) AS dsa_marks
FROM students s
LEFT JOIN marks m ON s.student_id = m.student_id
GROUP BY
    s.student_id,
    s.name,
    s.class,
    s.section,
    s.year,
    s.semester,
    s.attendance_percentage,
    s.extracurricular
ORDER BY s.student_id DESC
";

$result = mysqli_query($conn, $query);
if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}

$rows = [];
while ($r = mysqli_fetch_assoc($result)) {
    $base_total =
        (int)$r['cpp_marks'] +
        (int)$r['java_marks'] +
        (int)$r['dbms_marks'] +
        (int)$r['python_marks'] +
        (int)$r['dsa_marks'];
    // Bonus 5 marks only if extracurricular = "yes" (case-insensitive)
$extra_value = strtolower(trim((string)$r['extracurricular']));
$has_activity = ($extra_value === 'yes');
   
    $bonus = $has_activity ? 5 : 0;
    $total_with_bonus = $base_total + $bonus;

    // Avoid divide by zero; if no marks at all, treat total_marks as 0 / 500
    $max_total = 5 * 100;
    $percentage = $max_total > 0 ? (($total_with_bonus / $max_total) * 100) : 0;

    // Grade & status from overall percentage
    if ($percentage >= 90) {
        $grade = 'A+';
    } elseif ($percentage >= 80) {
        $grade = 'A';
    } elseif ($percentage >= 70) {
        $grade = 'B';
    } elseif ($percentage >= 60) {
        $grade = 'C';
    } elseif ($percentage >= 40) {
        $grade = 'D';
    } else {
        $grade = 'F';
    }

    if ($base_total == 0 && !$has_activity) {
        $status = 'No Data';
    } elseif ($percentage >= 40) {
        $status = 'Pass';
    } else {
        $status = 'Fail';
    }

    $r['base_total'] = $base_total;
    $r['bonus'] = $bonus;
    $r['total_with_bonus'] = $total_with_bonus;
    $r['percentage'] = $percentage;
    $r['grade'] = $grade;
    $r['status'] = $status;
    $rows[] = $r;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Performance</title>
    <link rel="stylesheet" href="../style.css">
    
</head>
<body class="stdperform-body">
    <a href="../dashboard.php" class="back-btn"> Back</a>
<div class="page-shape s1"></div>
<div class="page-shape s2"></div>
<div class="page-shape s3"></div>
<div class="page-shape s4"></div>

<div class="wrapper">
    <header class="top">
        <h2>📊 Student Performance</h2>
        
    </header>


    <div class="p-card">
        <h3>Overall Performance (with Bonus)</h3>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Extra‑curricular</th>
                        <th>Base Total (5 subjects)</th>
                        <th>Bonus Marks</th>
                        <th>Total with Bonus</th>
                        <th>Overall %</th>
                        <th>Attendance %</th>
                        <th>Grade</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($rows) === 0): ?>
                    <tr>
                        <td colspan="10" style="padding:24px; color:#6b7280;">
                            No students found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): 
                        $grade_class = 'grade-' . strtolower(str_replace('+', '-plus', $row['grade']));
                        $status_class = $row['status'] === 'Pass'
                            ? 'status-pass'
                            : ($row['status'] === 'Fail' ? 'status-fail' : 'status-nodata');
                    ?>
                    <tr>
                        <td><strong><?php echo $row['student_id']; ?></strong></td>
                        <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['extracurricular']); ?></td>
                        <td><?php echo $row['base_total']; ?></td>
                        <td><?php echo $row['bonus']; ?></td>
                        <td><strong><?php echo $row['total_with_bonus']; ?></strong></td>
                        <td><?php echo number_format($row['percentage'], 2); ?>%</td>
                        <td><?php echo number_format((float)$row['attendance_percentage'], 2); ?>%</td>
                        <td class="<?php echo $grade_class; ?>"><?php echo $row['grade']; ?></td>
                        <td>
                            <span class="status-badge <?php echo $status_class; ?>">
                                <?php echo $row['status']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
   <div class="bottom-actions">
    <button class="btn-print" onclick="window.print()">🖨️ Print</button>
    <!-- <a href="../dashboard.php" class="btn-back">⬅ Back</a> -->
   </div>

        
    </div>
</div>
</body>
</html>
