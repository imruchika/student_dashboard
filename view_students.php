<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}
?>

<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}
include("db/config.php");

function intToRoman($num) {
    $map = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII'];
    return $map[$num] ?? '-';
}


// Get all students with their marks data, including year & semester
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
    s.added_by,
    SUM(m.g3) AS total_marks,
    SUM(CASE WHEN m.subject = 'C++' THEN m.g3 ELSE 0 END) AS cpp_marks,
    SUM(CASE WHEN m.subject = 'Java' THEN m.g3 ELSE 0 END) AS java_marks,
    SUM(CASE WHEN m.subject = 'DBMS' THEN m.g3 ELSE 0 END) AS dbms_marks,
    SUM(CASE WHEN m.subject = 'Python' THEN m.g3 ELSE 0 END) AS python_marks,
    SUM(CASE WHEN m.subject = 'DSA' THEN m.g3 ELSE 0 END) AS dsa_marks
   
FROM students s
LEFT JOIN marks m ON s.student_id = m.student_id
GROUP BY s.student_id, s.name, s.class, s.section, s.year, s.semester, s.attendance_percentage, s.extracurricular, s.added_by
ORDER BY s.student_id DESC
";


$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List</title>
    <!-- <link rel="stylesheet" href="style.css"> -->
    <style>
        body {
           
              background: linear-gradient(to right, #DDD0C8, #bbb4b4);
              color: #413f3f;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        
        .table-container {
            width: 98%;
            max-width: 1400px;
            background: #fff;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.25);
            margin: 40px auto;
            overflow-x: auto;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .page-header h2 {
        color: #413f3f;
            font-size: clamp(24px, 5vw, 32px);
            margin: 0 0 10px 0;
        }
        
        .filter-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
        }
        
        .search-box {
            display: flex;
            gap: 10px;
            flex: 1;
            min-width: 250px;
        }
        
        .search-box input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
        }
        
        .search-box input:focus {
            /* border-color: #667eea; */
             border-color: #413f3f;
  
        }
        
        .btn-search,
        .btn-reset {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-search {
            /* background: linear-gradient(135deg, #667eea, #764ba2); */
            /* color: white; */
             background-color: #413f3f;
  color: #DDD0C8;
        }
        
        .btn-search:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .btn-reset {
            /* background: #e0e0e0;
            color: #333; */
             background-color: #413f3f;
  color: #DDD0C8;
        }
        
        .btn-reset:hover {
            background: #737070;
        }
        
        .sort-section {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .sort-section select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            cursor: pointer;
        }
        
        .btn-apply {
            padding: 10px 20px;
            /* background: linear-gradient(135deg, #667eea, #764ba2); */
            /* color: white; */
             background-color: #413f3f;
            color: #DDD0C8;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-apply:hover{
              background: #737070;
        }
        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1200px;
        }
        
        thead {
            /* background: linear-gradient(135deg, #667eea, #764ba2);
            color: white; */
             background-color: #413f3f;
             color: #DDD0C8;
        }
        
        th {
            padding: 14px 10px;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            white-space: nowrap;
        }
        
        td {
            padding: 12px 10px;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
        }
        
        tbody tr:hover {
            background: #f0f7ff;
        }
        
        .status-pass {
            background: #28a745;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 12px;
            display: inline-block;
        }
        
        .status-fail {
            background: #dc3545;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 12px;
            display: inline-block;
        }
        
        .status-nodata {
            background: #ffc107;
            color: #333;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 12px;
            display: inline-block;
        }
        
        .grade-a-plus { color: #28a745; font-weight: bold; }
        .grade-a { color: #5cb85c; font-weight: bold; }
        .grade-b { color: #5bc0de; font-weight: bold; }
        .grade-c { color: #f0ad4e; font-weight: bold; }
        .grade-d { color: #ff8c00; font-weight: bold; }
        .grade-f { color: #d9534f; font-weight: bold; }
        
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-action {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }
        
        .btn-edit {
             background-color: #413f3f;
  color: #DDD0C8;
        }
        
        .btn-delete {
                 background-color: #413f3f;
  color: #DDD0C8;

    }

        
        .btn-add-marks {
            /* background: linear-gradient(135deg, #667eea, #764ba2);
            color: white; */
             background-color: #413f3f;
  color: #DDD0C8;
        }
        
        .btn-action:hover {
            opacity: 0.85;
            transform: translateY(-2px);
        }
        
        .print-section {
            margin-top: 20px;
            text-align: center;
        }
        
        .btn-print {
            padding: 10px 20px;
             background-color: #413f3f;
            color: #DDD0C8;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align:center;
            align-item:center;
        }
        
        .btn-print:hover {
           
             background: #737070;
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
     /* background: #737070; */
    transform: translateY(-50%) translateX(-4px);
    box-shadow: 0 16px 40px rgba(0,0,0,0.5);
}

        @media print {
            .filter-section,
            .action-buttons,
            .back-link,
            .print-section {
                display: none !important;
            }
        }
        
        @media (max-width: 768px) {
            .table-container {
                padding: 15px;
                margin: 20px auto;
            }
            .filter-section {
                flex-direction: column;
                align-items: stretch;
            }
            .search-box {
                min-width: 100%;
            }
            .sort-section {
                width: 100%;
                flex-wrap: wrap;
            }
            table {
                font-size: 12px;
                min-width: 1000px;
            }
            th, td {
                padding: 8px 5px;
            }
            .action-buttons {
                flex-direction: column;
            }
            .btn-action {
                width: 100%;
                justify-content: center;
            }
        }

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

body{
    position:relative;
}
.dashboard-main,
.main{
    position:relative;
    z-index:1;
}

    </style>
</head>
<body>

 <a href="dashboard.php" class="back-btn" style="margin-left:10px;">Back</a>
<div class="page-shape s1"></div>
<div class="page-shape s2"></div>
<div class="page-shape s3"></div>
<div class="page-shape s4"></div>

<div class="table-container">
    <div class="page-header">
        <h2>👥 Students Data</h2>
    </div>

    
    <div class="filter-section">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search by name, course or class...">
            <button class="btn-search" onclick="searchTable()">Search</button>
            <button class="btn-reset" onclick="resetSearch()">Reset</button>
        </div>
        
        <div class="sort-section">
            <label style="font-weight: bold;">Sort By</label>
            <select id="sortBy">
                <option value="id">ID</option>
                <option value="name">Name</option>
                <option value="class">Class</option>
                <option value="marks">Total Marks</option>
              
            </select>
            
            <select id="sortOrder">
                <option value="asc">Ascending</option>
                <option value="desc">Descending</option>
            </select>
            
            <button class="btn-apply" onclick="sortTable()">Apply</button>
        </div>
    </div>

    <div class="table-wrapper">
        <table id="studentTable">
            <thead>
          <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Class</th>
        <th>Section</th>
        <th>Year</th>
        <th>Semester</th>
        <th>C++</th>
        <th>Java</th>
        <th>DBMS</th>
        <th>Python</th>
        <th>DSA</th>
        <th>Total Marks</th>
        <!-- <th>Added By</th> -->
          <?php if ($_SESSION['role'] === 'teacher') { ?>
        <th>Added By</th>
    <?php } ?>
        <th>Action</th>
        </tr>
            </thead>

            <tbody>
            <?php 
            if (mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) { 
                   
            ?>
                 <tr>
    <td><strong><?php echo $row['student_id']; ?></strong></td>
    <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
    <td><?php echo htmlspecialchars($row['class']); ?></td>
    <td><?php echo htmlspecialchars($row['section']); ?></td>
    <td><?php echo htmlspecialchars($row['year']); ?></td>
    
 <td><?= intToRoman($row['semester']) ?></td>


    <td><?php echo $row['cpp_marks'] ?? 0; ?></td>
    <td><?php echo $row['java_marks'] ?? 0; ?></td>
    <td><?php echo $row['dbms_marks'] ?? 0; ?></td>
    <td><?php echo $row['python_marks'] ?? 0; ?></td>
    <td><?php echo $row['dsa_marks'] ?? 0; ?></td>

    <td><strong><?php echo $row['total_marks'] ?? 0; ?></strong></td>
    <?php if ($_SESSION['role'] === 'teacher') { ?>
        <td><?= htmlspecialchars($row['added_by'] ?? '—'); ?></td>

    <?php } ?>

    <td>
        <div class="action-buttons">
            <a href="edit_student.php?id=<?php echo $row['student_id']; ?>" 
               class="btn-action btn-edit">
               ✏️ Edit
            </a>
            <a href="delete_student.php?id=<?php echo $row['student_id']; ?>" 
               class="btn-action btn-delete"
               onclick="return confirm('Delete <?php echo htmlspecialchars($row['name']); ?>?')">
               🗑️ Delete
            </a>
        </div>
    </td>
</tr> 
            <?php 
                }
            } else {
            ?>
                <tr>
                    <td colspan="11" style="text-align:center; padding:40px; color:#999; font-size:16px;">
                        📋 No students found.<br>
                        <small>Add students to see them here.</small>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    
    <div class="print-section">
        <button class="btn-print" onclick="window.print()">🖨️ Print</button>
        <!-- <a href="dashboard.php" class="back-link" style="margin-left:10px;">⬅ Back</a> -->
    </div>
</div>

<script>
function searchTable() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const table = document.getElementById('studentTable');
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const text = row.textContent.toLowerCase();
        
        if (text.includes(input)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
}

function resetSearch() {
    document.getElementById('searchInput').value = '';
    searchTable();
}

function sortTable() {
    const sortBy = document.getElementById('sortBy').value;
    const order = document.getElementById('sortOrder').value;
    const table = document.getElementById('studentTable');
    const tbody = table.getElementsByTagName('tbody')[0];
    const rows = Array.from(tbody.getElementsByTagName('tr'));
    
    rows.sort((a, b) => {
        let aVal, bVal;
        
        switch(sortBy) {
            case 'id':
                aVal = parseInt(a.cells[0].textContent);
                bVal = parseInt(b.cells[0].textContent);
                break;
            case 'name':
                aVal = a.cells[1].textContent.toLowerCase();
                bVal = b.cells[1].textContent.toLowerCase();
                break;
            case 'class':
                aVal = a.cells[2].textContent.toLowerCase();
                bVal = b.cells[2].textContent.toLowerCase();
                break;
            case 'marks':
                 aVal = parseInt(a.cells.textContent) || 0; // Total Marks column
                 bVal = parseInt(b.cells.textContent) || 0;
                break;
            
        }
        
        if (order === 'asc') {
            return aVal > bVal ? 1 : -1;
        } else {
            return aVal < bVal ? 1 : -1;
        }
    });
    
    rows.forEach(row => tbody.appendChild(row));
}

document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchTable();
    }
});
</script>
</body>
</html>
