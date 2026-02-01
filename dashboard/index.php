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
    <!-- <style>
        * {
            box-sizing: border-box;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        body {
            /* background: linear-gradient(135deg,#5f2dbd,#8a2be2,#c026d3); */
             background: linear-gradient(to right, #DDD0C8, #bbb4b4);
              color: #413f3f;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .wrapper {
            max-width: 1200px;
            margin: 0 auto;
        }
        .top {
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }
        .top h2 {
        color: #413f3f;
          margin: 0;
            font-size: clamp(20px, 4vw, 26px);
        }
        .back-btn {
            padding: 8px 16px;
           
             background-color: #413f3f;
  color: #DDD0C8;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            border-radius: 20px;
            border: none;
            display: inline-block;
            transition: all 0.2s ease;
        }
        .back-btn:hover {
            background: #138496;
            transform: translateY(-1px);
        }

        .card {
            background: #fff;
            border-radius: 14px;
            padding: 18px 20px 20px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.25);
            margin-top: 10px;
        }
        .card h3 {
            margin: 0 0 12px;
            font-size: 18px;
            color: #333;
        }

        .table-wrapper {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }
        thead {
            
            background-color: #413f3f;
            color: #DDD0C8;
        }
        th, td {
            padding: 10px 8px;
            text-align: center;
            font-size: 13px;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            font-weight: 600;
            white-space: nowrap;
        }
        tbody tr:hover {
            background: #f0f7ff;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-pass {
            background: #22c55e33;
            color: #15803d;
        }
        .status-fail {
            background: #ef444433;
            color: #b91c1c;
        }
        .status-nodata {
            background: #eab30833;
            color: #92400e;
        }
        .grade-a-plus { color: #16a34a; font-weight: 600; }
        .grade-a { color: #22c55e; font-weight: 600; }
        .grade-b { color: #0ea5e9; font-weight: 600; }
        .grade-c { color: #f97316; font-weight: 600; }
        .grade-d { color: #facc15; font-weight: 600; }
        .grade-f { color: #dc2626; font-weight: 600; }

        .bottom-actions {
            margin-top: 12px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .btn-print {
            padding: 8px 16px;
           background-color: #413f3f;
           color: #DDD0C8;
            border-radius: 6px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-print:hover {
            background: #138496;
            transform: translateY(-1px);
        }

      .back-btn{
    position: fixed;
    left: 30px;
    top:30px;
    transform: translateY(-50%);
    padding: 12px 22px;
    border-radius: 999px;
    background: linear-gradient(135deg, #413f3f, #111827);
    color: #ffffff;
    font-weight: 600;
    text-decoration: none;
    box-shadow: 0 12px 30px rgba(0,0,0,0.4);
    transition: all 0.3s ease;
    z-index: 999;
}

.back-btn:hover{
    transform: translateY(-50%) translateX(-4px);
    box-shadow: 0 16px 40px rgba(0,0,0,0.5);
}


        @media (max-width: 768px) {
            body {
                padding: 12px;
            }
            .card {
                padding: 14px;
            }
            th, td {
                font-size: 12px;
                padding: 8px 6px;
            }
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .top, .bottom-actions {
                display: none !important;
            }
            .card {
                box-shadow: none;
                margin: 0;
                border-radius: 0;
            }
        }

        /* background shapes for inner pages */
.page-shape{
    position:fixed;
    border-radius:50%;
    background:rgba(255,255,255,0.04);
    z-index:0;
    pointer-events:none;
}
.page-shape.s1{width:200px;height:200px;top:-60px;left:-60px;}
.page-shape.s2{width:160px;height:160px;top:18%;right:-40px;}
.page-shape.s3{width:140px;height:140px;bottom:-60px;left:15%;}
.page-shape.s4{width:260px;height:260px;bottom:-120px;right:20%;border-radius:28px;}

/* keep gradient, only add position:relative */
body{
    position:relative;
}

/* make main content above shapes */
.dashboard-main,
.main,
.wrapper{
    position:relative;
    z-index:1;
}

    </style> -->
</head>
<body class="edit-std-body">
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
