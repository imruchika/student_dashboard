<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: page1/index.php");
    exit();
}
$firstName = explode(" ", $_SESSION['username'])[0];

include("db/config.php");

/*
   Performance summary for dashboard KPIs and charts.
   Subjects: C++, Java, DBMS, Python, DSA
   Bonus 5 only if extracurricular = "yes"
*/

/* ---------- MAIN PERFORMANCE SUMMARY (for KPIs & charts) ---------- */
$q = "
SELECT
    s.student_id,
    s.name,
    s.year,
    s.semester,
    s.attendance_percentage,
    s.extracurricular,
    SUM(CASE WHEN m.subject = 'C++' THEN m.g3 ELSE 0 END)    AS cpp_marks,
    SUM(CASE WHEN m.subject = 'Java' THEN m.g3 ELSE 0 END)   AS java_marks,
    SUM(CASE WHEN m.subject = 'DBMS' THEN m.g3 ELSE 0 END)   AS dbms_marks,
    SUM(CASE WHEN m.subject = 'Python' THEN m.g3 ELSE 0 END) AS python_marks,
    SUM(CASE WHEN m.subject = 'DSA' THEN m.g3 ELSE 0 END)    AS dsa_marks
FROM students s
LEFT JOIN marks m ON s.student_id = m.student_id
GROUP BY
    s.student_id,
    s.name,
    s.year,
    s.semester,
    s.attendance_percentage,
    s.extracurricular
ORDER BY s.student_id ASC
";

$res = mysqli_query($conn, $q);
if (!$res) {
    die("Query Error: " . mysqli_error($conn));
}

$total_students = 0;
$sum_percentage = 0;
$pass_count = 0;

$yearCounts = [];
$statusCounts = [
    'Pass'    => 0,
    'Fail'    => 0,
    'No Data' => 0
];
$gradeCounts = [
    'A+' => 0,
    'A'  => 0,
    'B'  => 0,
    'C'  => 0,
    'D'  => 0,
    'F'  => 0
];

$max_total = 5 * 100;

while ($r = mysqli_fetch_assoc($res)) {
    $total_students++;

    $base_total =
        (int)$r['cpp_marks'] +
        (int)$r['java_marks'] +
        (int)$r['dbms_marks'] +
        (int)$r['python_marks'] +
        (int)$r['dsa_marks'];

    $extra_value      = strtolower(trim((string)$r['extracurricular']));
    $has_activity     = ($extra_value === 'yes');
    $bonus            = $has_activity ? 5 : 0;
    $total_with_bonus = $base_total + $bonus;

    $percentage = $max_total > 0 ? (($total_with_bonus / $max_total) * 100) : 0;

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

    $sum_percentage += $percentage;
    if ($status === 'Pass') {
        $pass_count++;
    }

    $year = $r['year'] ?: 'Unknown';
    if (!isset($yearCounts[$year])) {
        $yearCounts[$year] = 0;
    }
    $yearCounts[$year]++;

    if (isset($statusCounts[$status])) {
        $statusCounts[$status]++;
    } else {
        $statusCounts[$status] = 1;
    }

    if (isset($gradeCounts[$grade])) {
        $gradeCounts[$grade]++;
    } else {
        $gradeCounts[$grade] = 1;
    }
}

$avg_percentage = $total_students > 0 ? ($sum_percentage / $total_students) : 0;
$pass_rate      = $total_students > 0 ? (($pass_count / $total_students) * 100) : 0;

$year_labels   = array_keys($yearCounts);
$year_values   = array_values($yearCounts);
$status_labels = array_keys($statusCounts);
$status_values = array_values($statusCounts);
$grade_labels  = array_keys($gradeCounts);
$grade_values  = array_values($gradeCounts);

/* ---------- SUBJECT-WISE TOPPERS ---------- */
$subjects = ['C++','Java','DBMS','Python','DSA'];
$subject_toppers = [];

foreach ($subjects as $subj) {
    $subjEsc = mysqli_real_escape_string($conn, $subj);
    $topQuery = "
        SELECT s.name, m.g3
        FROM marks m
        JOIN students s ON s.student_id = m.student_id
        WHERE m.subject = '{$subjEsc}'
        AND m.g3 = (
            SELECT MAX(m2.g3)
            FROM marks m2
            WHERE m2.subject = '{$subjEsc}'
        )
        LIMIT 1
    ";
    $topRes = mysqli_query($conn, $topQuery);
    if ($topRes && mysqli_num_rows($topRes) > 0) {
        $row = mysqli_fetch_assoc($topRes);
        $subject_toppers[$subj] = [
            'name'  => $row['name'],
            'marks' => (int)$row['g3']
        ];
    } else {
        $subject_toppers[$subj] = [
            'name'  => 'No data',
            'marks' => 0
        ];
    }
}

/* ---------- SUBJECT-WISE PASS COUNTS ---------- */
$subject_pass_counts = [];
foreach ($subjects as $subj) {
    $subjEsc = mysqli_real_escape_string($conn, $subj);
    $passQuery = "
        SELECT COUNT(*) AS pass_count
        FROM marks
        WHERE subject = '{$subjEsc}'
        AND g3 >= 40
    ";
    $passRes = mysqli_query($conn, $passQuery);
    if ($passRes) {
        $row = mysqli_fetch_assoc($passRes);
        $subject_pass_counts[$subj] = (int)$row['pass_count'];
    } else {
        $subject_pass_counts[$subj] = 0;
    }
}

/* ---------- INDIVIDUAL PERFORMANCE DATA (for dropdown chart) ---------- */
$studentsRes = mysqli_query($conn, "SELECT student_id, name FROM students ORDER BY name");
$studentsList = [];
while ($row = mysqli_fetch_assoc($studentsRes)) {
    $studentsList[] = $row;
}

    $marksRes = mysqli_query(
    $conn,
    "SELECT s.student_id, s.name, m.subject, m.g3, s.extracurricular
     FROM students s
     LEFT JOIN marks m ON s.student_id = m.student_id
     ORDER BY s.name, m.subject"
);

$studentMarks = [];
while ($row = mysqli_fetch_assoc($marksRes)) {
    $sid = $row['student_id'];

    // Initialize fixed structure for each student
    if (!isset($studentMarks[$sid])) {
        $studentMarks[$sid] = [
            'name'        => $row['name'],
            'extracurricular' => strtolower(trim((string)$row['extracurricular'])),
            'subjects'    => ['C++','Java','DBMS','Python','DSA','Activity'],
            'marks'       => [0, 0, 0, 0, 0, 0] // default 0 for all, Activity will become 5 if yes
        ];
    }

    $subject = $row['subject'];
    $mark    = isset($row['g3']) ? (int)$row['g3'] : 0;

    // Map each subject to a fixed index
    if ($subject === 'C++') {
        $studentMarks[$sid]['marks'][0] = $mark;
    } elseif ($subject === 'Java') {
        $studentMarks[$sid]['marks'][1] = $mark;
    } elseif ($subject === 'DBMS') {
        $studentMarks[$sid]['marks'][2] = $mark;
    } elseif ($subject === 'Python') {
        $studentMarks[$sid]['marks'][3] = $mark;
    } elseif ($subject === 'DSA') {
        $studentMarks[$sid]['marks'][4] = $mark;
    }

    // Activity bar: 5 if extracurricular = yes, otherwise 0
    $hasActivity = ($studentMarks[$sid]['extracurricular'] === 'yes');
    $studentMarks[$sid]['marks'][5] = $hasActivity ? 5 : 0;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="Style1.css">
    <!-- <link rel="stylesheet" href="style.css"> -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Chart.js main CDN script [web:565][web:686] -->
    
</head>
<body>
<div class="page-shape s1"></div>
<div class="page-shape s2"></div>
<div class="page-shape s3"></div>
<div class="page-shape s4"></div>

<!-- <div class="dashboard-main"> -->
<div id="menu-btn" class="menu-btn openbtn" onclick="openNav()">☰</div>
<div class="dashboard-main">

    <div class="sidebar" id="sidebar">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
        <a href="add_student.php" class="links">➕ Add Student</a>
        <a href="view_students.php" class="links">👥 Students Data</a>
        <a href="dashboard.php" class="active " >📊 Performance Dashboard</a>
        <a href="dashboard/index.php" class="links">📋 Performance Table</a>
        <form method="post" action="logout.php">
            <button class="logout-side">Logout</button>
        </form>
    </div>

    <div class="main" id="main" >
        <!-- <button class="openbtn" onclick="openNav()">&#9776; Menu</button> -->
        <h1 class="welcome-text">Welcome, <?php echo htmlspecialchars($firstName); ?> 🎉</h1>
        <h2 class="dashboard-title">Student Performance Dashboard</h2>

        <div class="layout-grid">

            <!-- LEFT: KPIs + CHARTS -->
            <div>
                <div class="kpi-row">
                    <div class="kpi-card">
                        <div class="kpi-label">Total Students</div>
                        <div class="kpi-value"><?php echo $total_students; ?></div>
                        <div class="kpi-sub">Active in system</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label">Average Percentage</div>
                        <div class="kpi-value"><?php echo number_format($avg_percentage, 2); ?>%</div>
                        <div class="kpi-sub">With bonus marks applied</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label">Pass Rate</div>
                        <div class="kpi-value"><?php echo number_format($pass_rate, 2); ?>%</div>
                        <div class="kpi-sub">Students with status Pass</div>
                    </div>
                </div>

                <!-- CHARTS GRID WITH SWAPPED FULL-WIDTH CARDS -->
                <div class="charts-grid">
                    <!-- Students by Year FIRST (full width at top) -->
                    <div class="chart-card full-width">
                        <h3><span>🎓</span> Students by Year</h3>
                        <div class="chart-container">
                            <canvas id="yearChart"></canvas>
                        </div>
                    </div>

                    <!-- Two small charts in the row -->
                    <div class="chart-card">
                        <h3><span>📊</span> Pass / Fail / No Data</h3>
                        <div class="chart-container">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                    <div class="chart-card">
                        <h3><span>⭐</span> Grade Distribution</h3>
                        <div class="chart-container">
                            <canvas id="gradeChart"></canvas>
                        </div>
                    </div>

                    <!-- Individual Performance LAST (full width at bottom) -->
                    <div class="chart-card full-width">
                        <h3>
                            <span>📌</span> Individual Performance
                        </h3>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                            <span style="font-size:13px; color:#6b7280;">Select a student to view subject-wise marks</span>
                            <select id="studentSelect" class="form-control"
                                    style="max-width:200px; padding:6px 8px; border-radius:8px;">
                                <?php foreach ($studentsList as $s): ?>
                                    <option value="<?php echo $s['student_id']; ?>">
                                        <?php echo htmlspecialchars($s['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="chart-container" style="height:220px;">
                            <canvas id="individualChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: TOPPERS + PERFORMANCE SUMMARY -->
            <div class="right-column">
                <div class="toppers-card">
                    <h3><span>🏅</span> Subject-wise Toppers</h3>
                    <div class="toppers-list">
                        <?php foreach ($subjects as $subj):
                            $top = $subject_toppers[$subj];
                        ?>
                        <div class="topper-item">
                            <div>
                                <div class="topper-subject"><?php echo htmlspecialchars($subj); ?></div>
                                <div class="topper-name">
                                    <?php echo htmlspecialchars($top['name']); ?>
                                </div>
                            </div>
                            <div class="topper-marks">
                                <?php echo $top['marks']; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="summary-card">
                    <h3><span>📌</span> Subject-wise Pass Summary</h3>
                    <div class="subject-summary-grid">
                        <?php foreach ($subjects as $subj):
                            $cnt = $subject_pass_counts[$subj];
                        ?>
                        <div class="subject-summary-item">
                            <div class="subject-name"><?php echo htmlspecialchars($subj); ?></div>
                            <div class="subject-pass">
                                <?php echo $cnt; ?> students passed
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>

// Year bar chart
const yearCtx = document.getElementById('yearChart').getContext('2d');
new Chart(yearCtx, {
    type: 'bar',
    data: {
        labels: yearLabels,
        datasets: [{
            label: 'Students',
            data: yearValues,
            backgroundColor: ['#494542',' #9c7268','#68899c','rgb(94, 143, 96)'],
            borderRadius: 6
        }]
    },
    options: {
        responsive:true,
        maintainAspectRatio:false,
        plugins:{ legend:{ display:false } },
        scales:{ y:{ beginAtZero:true, ticks:{ precision:0 } } }
    }
});

// Status doughnut chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: statusLabels,
        datasets: [{
            data: statusValues,
            backgroundColor: ['#80d09c','#d76262','#bda662']
        }]
    },
    options: {
        responsive:true,
        maintainAspectRatio:false,
        plugins:{ legend:{ position:'bottom' } }
    }
});

// Grade bar chart
const gradeCtx = document.getElementById('gradeChart').getContext('2d');
new Chart(gradeCtx, {
    type: 'bar',
    data: {
        labels: gradeLabels,
        datasets: [{
            label:'Students',
            data: gradeValues,
            backgroundColor:'#85d8ff',
            borderRadius:6
        }]
    },
    options: {
        responsive:true,
        maintainAspectRatio:false,
        plugins:{ legend:{ display:false } },
        scales:{ y:{ beginAtZero:true, ticks:{ precision:0 } } }
    }
});


function getDataForStudent(studentId) {
    const data = allStudentMarks[studentId];
    if (!data) return { labels: [], marks: [] };
    return {
        labels: data.subjects,
        marks: data.marks
    };
}

const firstId = studentSelect.value;
let initial = getDataForStudent(firstId);

let individualChart = new Chart(ctxInd, {
    type: 'bar',
    data: {
        labels: initial.labels,
        datasets: [{
            label: 'Marks',
            data: initial.marks,
            backgroundColor: 'rgba(129, 210, 245, 0.85)',
            borderColor: 'rgb(6, 149, 185)',
            borderWidth: 1,
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio:false,
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            }
        },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#111827',
                titleColor: '#e5e7eb',
                bodyColor: '#e5e7eb'
            }
        }
    }
});

studentSelect.addEventListener('change', function () {
    const sid = this.value;
    const d = getDataForStudent(sid);

    individualChart.data.labels = d.labels;
    individualChart.data.datasets[0].data = d.marks;
    individualChart.update();
});
</script>

 <script>
        /* Set the width of the sidebar to 250px and the left margin of the page content to 250px */
function openNav() {
  document.getElementById("sidebar").style.width = "270px";
  document.getElementById("main").style.marginLeft = "270px";
   document.getElementById("menuBtn").style.left = "280px";
}

/* Set the width of the sidebar to 0 and the left margin of the page content to 0 */
function closeNav() {
  document.getElementById("sidebar").style.width = "0";
  document.getElementById("main").style.marginLeft = "0";
   document.getElementById("menuBtn").style.left = "12px";
}


            
      </script>
</body>
</html>
