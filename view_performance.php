<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "teacher") {
    header("Location: page1/index.php");
    exit();
}

include("db/config.php");

// ULTIMATE QUERY - Get ALL student information
$query = "
SELECT 
    s.student_id,
    s.name,
    s.class,
    s.section,
    s.attendance_percentage,
    s.extracurricular,
    SUM(CASE WHEN m.subject='DBMS' THEN m.g3 END) AS DBMS,
    SUM(CASE WHEN m.subject='English' THEN m.g3 END) AS English,
    SUM(CASE WHEN m.subject='DSA' THEN m.g3 END) AS DSA,
    SUM(CASE WHEN m.subject='Python' THEN m.g3 END) AS Python,
    SUM(CASE WHEN m.subject='Java' THEN m.g3 END) AS Java,
    SUM(CASE WHEN m.subject='C++' THEN m.g3 END) AS CPlusPlus,
    SUM(m.g3) AS total_marks,
    COUNT(DISTINCT m.subject) AS subject_count,
    ROUND(AVG(m.g3), 2) AS average_marks,
    CASE 
        WHEN AVG(m.g3) >= 40 THEN 'PASS'
        WHEN AVG(m.g3) IS NULL THEN 'NO DATA'
        ELSE 'FAIL'
    END AS pass_status,
    GROUP_CONCAT(DISTINCT ea.activity_name SEPARATOR ', ') AS activities
FROM students s
LEFT JOIN marks m ON s.student_id = m.student_id
LEFT JOIN extracurricular_activities ea ON s.student_id = ea.student_id
GROUP BY s.student_id, s.name, s.class, s.section, s.attendance_percentage, s.extracurricular
ORDER BY s.student_id";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Student Performance Report</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .table-container {
            width: 98%;
            max-width: 1600px;
            background: #fff;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.25);
            margin: 40px auto;
            overflow-x: auto;
        }
        
        .page-header {
            /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
             background: linear-gradient(to right, #DDD0C8, #bbb4b4);
              /* color: #413f3f; */
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .page-header h2 {
            margin: 0;
            font-size: clamp(24px, 5vw, 32px);
        }
        
        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1400px;
            font-size: 13px;
        }
        
        thead {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: white;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        th {
            padding: 12px 8px;
            text-align: center;
            font-weight: bold;
            white-space: nowrap;
            border-right: 1px solid rgba(255,255,255,0.2);
            font-size: 12px;
        }
        
        th:last-child {
            border-right: none;
        }
        
        td {
            padding: 10px 8px;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
            font-size: 13px;
        }
        
        tbody tr:hover {
            background: #f0f7ff;
        }
        
        .pass {
            background: #d4edda;
            color: #155724;
            padding: 4px 10px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 11px;
        }
        
        .fail {
            background: #f8d7da;
            color: #721c24;
            padding: 4px 10px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 11px;
        }
        
        .no-data {
            background: #fff3cd;
            color: #856404;
            padding: 4px 10px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 11px;
        }
        
        .marks-good { color: #28a745; font-weight: bold; }
        .marks-average { color: #ffc107; font-weight: bold; }
        .marks-poor { color: #dc3545; font-weight: bold; }
        
        .attendance-good { color: #28a745; font-weight: bold; }
        .attendance-average { color: #ffc107; font-weight: bold; }
        .attendance-poor { color: #dc3545; font-weight: bold; }
        
        .action-buttons {
            display: flex;
            gap: 4px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }
        
        .stat-card h3 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        
        .stat-card p {
            margin: 5px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        
        .activities-cell {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 11px;
        }
        
        .activities-cell:hover {
            white-space: normal;
            overflow: visible;
        }
        
        @media print {
            .back-link, .action-buttons {
                display: none !important;
            }
        }
        
        @media (max-width: 768px) {
            .table-container {
                padding: 15px;
                margin: 30px auto;
            }
            
            table {
                font-size: 11px;
                min-width: 1200px;
            }
            
            th, td {
                padding: 6px 4px;
            }
            
            .stats-summary {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn-edit, .btn-delete {
                width: 100%;
                font-size: 11px;
                padding: 4px 8px;
            }
        }
    </style>
</head>
<body>

<div class="table-container">

    <div class="page-header">
        <h2>📊 Complete Student Performance Report</h2>
        <p style="margin: 5px 0 0; opacity: 0.9;">Comprehensive Academic & Extracurricular Analysis</p>
    </div>
    
    <?php
    // Calculate summary statistics
    $total_students = mysqli_num_rows($result);
    $pass_count = 0;
    $fail_count = 0;
    $total_attendance = 0;
    
    $students_data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $students_data[] = $row;
        if ($row['pass_status'] == 'PASS') $pass_count++;
        if ($row['pass_status'] == 'FAIL') $fail_count++;
        $total_attendance += $row['attendance_percentage'] ?? 0;
    }
    
    $pass_rate = $total_students > 0 ? round(($pass_count / $total_students) * 100, 1) : 0;
    $avg_attendance = $total_students > 0 ? round($total_attendance / $total_students, 1) : 0;
    ?>
    
    <!-- Summary Statistics -->
    <div class="stats-summary">
        <div class="stat-card">
            <h3><?php echo $total_students; ?></h3>
            <p>Total Students</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $pass_count; ?></h3>
            <p>Students Passed</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $fail_count; ?></h3>
            <p>Students Failed</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $pass_rate; ?>%</h3>
            <p>Pass Rate</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $avg_attendance; ?>%</h3>
            <p>Avg Attendance</p>
        </div>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th rowspan="2">ID</th>
                    <th rowspan="2">Student Name</th>
                    <th rowspan="2">Class</th>
                    <th rowspan="2">Section</th>
                    <th colspan="6" style="background: rgba(255,255,255,0.1);">Subject Marks</th>
                    <th rowspan="2">Total</th>
                    <th rowspan="2">Average</th>
                    <th rowspan="2">Attendance</th>
                    <th rowspan="2">Activities</th>
                    <th rowspan="2">Status</th>
                    <th rowspan="2">Actions</th>
                </tr>
                <tr>
                    <th style="background: rgba(255,255,255,0.1);">DBMS</th>
                    <th style="background: rgba(255,255,255,0.1);">English</th>
                    <th style="background: rgba(255,255,255,0.1);">DSA</th>
                    <th style="background: rgba(255,255,255,0.1);">Python</th>
                    <th style="background: rgba(255,255,255,0.1);">Java</th>
                    <th style="background: rgba(255,255,255,0.1);">C++</th>
                </tr>
            </thead>

            <tbody>
            <?php 
            if (count($students_data) > 0) {
                foreach ($students_data as $row) { 
                    $avg = $row['average_marks'] ?? 0;
                    $mark_class = $avg >= 60 ? 'marks-good' : ($avg >= 40 ? 'marks-average' : 'marks-poor');
                    
                    $attendance = $row['attendance_percentage'] ?? 0;
                    $attendance_class = $attendance >= 75 ? 'attendance-good' : ($attendance >= 50 ? 'attendance-average' : 'attendance-poor');
                    
                    $activities_display = $row['activities'] ?? ($row['extracurricular'] ?? 'None');
            ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($row['student_id']); ?></strong></td>
                    <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['class'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($row['section'] ?? '-'); ?></td>
                    <td class="<?php echo $mark_class; ?>"><?php echo $row['DBMS'] ?? '-'; ?></td>
                    <td class="<?php echo $mark_class; ?>"><?php echo $row['English'] ?? '-'; ?></td>
                    <td class="<?php echo $mark_class; ?>"><?php echo $row['DSA'] ?? '-'; ?></td>
                    <td class="<?php echo $mark_class; ?>"><?php echo $row['Python'] ?? '-'; ?></td>
                    <td class="<?php echo $mark_class; ?>"><?php echo $row['Java'] ?? '-'; ?></td>
                    <td class="<?php echo $mark_class; ?>"><?php echo $row['CPlusPlus'] ?? '-'; ?></td>
                    <td><strong><?php echo $row['total_marks'] ?? 0; ?></strong></td>
                    <td class="<?php echo $mark_class; ?>">
                        <strong><?php echo $row['average_marks'] ?? 0; ?></strong>
                    </td>
                    <td class="<?php echo $attendance_class; ?>">
                        <strong><?php echo number_format($attendance, 1); ?>%</strong>
                    </td>
                    <td class="activities-cell" title="<?php echo htmlspecialchars($activities_display); ?>">
                        <?php echo htmlspecialchars($activities_display); ?>
                    </td>
                    <td>
                        <span class="<?php echo strtolower($row['pass_status']); ?>">
                            <?php echo $row['pass_status']; ?>
                        </span>
                    </td>
                    <td class="action-buttons">
                        <a href="edit_performance.php?student_id=<?php echo $row['student_id']; ?>" 
                           class="btn-edit" title="Edit marks and details">
                           ✏️ Edit
                        </a>
                        <a href="delete_performance.php?student_id=<?php echo $row['student_id']; ?>"
                           class="btn-delete"
                           title="Delete all records"
                           onclick="return confirm('⚠️ Delete all records for <?php echo htmlspecialchars($row['name']); ?>?')">
                           🗑️ Delete
                        </a>
                    </td>
                </tr>
            <?php 
                }
            } else {
            ?>
                <tr>
                    <td colspan="15" style="text-align:center; padding:40px; color:#999; font-size:16px;">
                        📋 No student performance data available yet.<br>
                        <small>Add students and marks to see comprehensive performance records.</small>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 25px; display: flex; gap: 10px; flex-wrap: wrap; justify-content: space-between;">
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="dashboard.php" class="back-link">⬅ Back to Dashboard</a>
           
            </a>
        </div>
        <button onclick="window.print()" class="back-link" style="background: rgba(33, 150, 243, 0.2); border-color: rgba(33, 150, 243, 0.6); cursor: pointer;">
            🖨️ Print Report
        </button>
    </div>

</div>

</body>
</html>