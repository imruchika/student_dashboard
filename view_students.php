<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'teacher') {
    // header("Location: ../login.php");
    header("Location:auth/index.php");
    exit();
}
?>

<?php

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}
include("db/config.php");
$yearMap = [
    1 => 'First Year',
    2 => 'Second Year',
    3 => 'Third Year',
    4 => 'Fourth Year'
];

$romanSemester = [
    1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
    5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII'
];


// Get all students with their marks data, including year & semester
$query = "
SELECT 
    s.student_id,
    s.prn,
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
GROUP BY s.student_id, s.prn,  s.name, s.class, s.section, s.year, s.semester, s.attendance_percentage, s.extracurricular, s.added_by
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
    <link rel="stylesheet" href="style.css">
    </head>
<body class ="view-std-body">

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
                <option value="prn">PRN</option>
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
        <th>PRN</th>
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
    <td><strong><?php echo $row['prn']; ?></strong></td>
    <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
    <td><?php echo htmlspecialchars($row['class']); ?></td>
    <td><?php echo htmlspecialchars($row['section']); ?></td>
    <!-- <td><?php echo htmlspecialchars($row['year']); ?></td> -->
    <td><?= $yearMap[$row['year']] ?? '—'; ?></td>

 <!-- <td><?php echo $row['semester'] ?></td> -->
  <td><?= $romanSemester[$row['semester']] ?? '—'; ?></td>



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
