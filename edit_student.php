<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "teacher"){
    header("Location: index.php");
    exit();
}

include("db/config.php");

// Subjects list for dropdowns
$subjects_list = [
    'Python','Java',
    'C++','DSA','DBMS'
];
$semester_list = [
   'I','II','III','IV','V','VI','VII','VIII'
];

$success = $error = "";

// Get student id
$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($student_id <= 0) {
    die("Invalid student ID.");
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = mysqli_real_escape_string($conn, trim($_POST['name'] ?? ''));
    $course = mysqli_real_escape_string($conn, trim($_POST['course'] ?? ''));
    $section= mysqli_real_escape_string($conn, trim($_POST['section'] ?? ''));
    $year   = mysqli_real_escape_string($conn, trim($_POST['year'] ?? ''));
    $sem    = mysqli_real_escape_string($conn, trim($_POST['semester'] ?? ''));
    $attendance_input = trim($_POST['attendance'] ?? '');
    $attendance_percentage = ($attendance_input === '' ? 0 : (float)$attendance_input);

    if ($name === '' || $course === '' || $section === '') {
        $error = "Name, Course and Section are required.";
    } elseif ($attendance_percentage < 0 || $attendance_percentage > 100) {
        $error = "Attendance must be between 0 and 100.";
    }  elseif ($sem !== '' && !in_array($sem, $semester_list, true)) {
    $error = "Please select a valid semester.";
    }else {
        // Update students table
        $uq = "UPDATE students 
               SET name = ?, class = ?, section = ?, year = ?, semester = ?, attendance_percentage = ? 
               WHERE student_id = ?";
        $ustmt = mysqli_prepare($conn, $uq);
        // name (s), class (s), section (s), year (s), semester (s), attendance (d), id (i)
        mysqli_stmt_bind_param(
            $ustmt,
            "sssssdi",
            $name,
            $course,
            $section,
            $year,
            $sem,
            $attendance_percentage,
            $student_id
        );
        if (!mysqli_stmt_execute($ustmt)) {
            $error = "Error updating student: " . mysqli_error($conn);
        }
        mysqli_stmt_close($ustmt);

        if ($error === "") {
            // Delete old marks for this student
            $dq = "DELETE FROM marks WHERE student_id = ?";
            $dstmt = mysqli_prepare($conn, $dq);
            mysqli_stmt_bind_param($dstmt, "i", $student_id);
            mysqli_stmt_execute($dstmt);
            mysqli_stmt_close($dstmt);

            // Re-insert marks from form
            if (isset($_POST['subjects']) && is_array($_POST['subjects'])) {
                foreach ($_POST['subjects'] as $idx => $subj) {
                    $subject = mysqli_real_escape_string($conn, trim($subj ?? ''));
                    $marks   = (int)($_POST['marks'][$idx] ?? 0);

                    if ($subject !== '' && $marks >= 0 && $marks <= 100) {
                        $mq = "INSERT INTO marks (student_id, subject, g3) VALUES (?, ?, ?)";
                        $mstmt = mysqli_prepare($conn, $mq);
                        mysqli_stmt_bind_param($mstmt, "isi", $student_id, $subject, $marks);
                        mysqli_stmt_execute($mstmt);
                        mysqli_stmt_close($mstmt);
                    }
                }
            }

            $success = "Student updated successfully.";
             header("Location: view_students.php");
            exit();
        }
    }
}

// Load student data
$sq = "SELECT * FROM students WHERE student_id = ?";
$sstmt = mysqli_prepare($conn, $sq);
mysqli_stmt_bind_param($sstmt, "i", $student_id);
mysqli_stmt_execute($sstmt);
$sres = mysqli_stmt_get_result($sstmt);
$student = mysqli_fetch_assoc($sres);
mysqli_stmt_close($sstmt);

if (!$student) {
    die("Student not found.");
}

// Load marks data
$marks_data = [];
$mq = "SELECT id, subject, g3 FROM marks WHERE student_id = ? ORDER BY id ASC";
$mstmt = mysqli_prepare($conn, $mq);
mysqli_stmt_bind_param($mstmt, "i", $student_id);
mysqli_stmt_execute($mstmt);
$mres = mysqli_stmt_get_result($mstmt);
while ($row = mysqli_fetch_assoc($mres)) {
    $marks_data[] = $row;
}
mysqli_stmt_close($mstmt);

// If no marks, create 5 empty rows
if (count($marks_data) === 0) {
    for ($i = 0; $i < 5; $i++) {
        $marks_data[] = ['id' => 0, 'subject' => '', 'g3' => ''];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Student</title>
<link rel=stylesheet href="style.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body class="edit-std-body">
    <button type="button" class="back-btn" onclick="history.back()">Back</button>
<div class="wrapper">
    <div class="card">
        <div class="card-header">
            <div>
                <h2>Edit Student</h2>
                <span>Update student details, attendance and subject marks</span>
            </div>
        </div>
        <div class="card-body">
            <?php if ($success): ?>
                <div class="message success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="post">
                <h3>📋 Basic Information</h3>
                <div class="row">
                    <div>
                        <label>Student ID</label>
                        <input type="text" value="<?php echo $student['student_id']; ?>" disabled>
                    </div>
                    <div>
                        <label>Student Name *</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required>
                    </div>
                    <div>
                        <label>Class *</label>
                        <input type="text" name="course" value="<?php echo htmlspecialchars($student['class']); ?>" required>
                    </div>
                    <div>
                        <label>Section *</label>
                        <input type="text" name="section" value="<?php echo htmlspecialchars($student['section']); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div>
                        <label>Year</label>
                        <select name="year" required>
                            <option value="">Select Year</option>
                            <option value="First" <?php echo $student['year']=='First'?'selected':''; ?>>First Year</option>
                            <option value="Second" <?php echo $student['year']=='Second'?'selected':''; ?>>Second Year</option>
                            <option value="Third" <?php echo $student['year']=='Third'?'selected':''; ?>>Third Year</option>
                            <option value="Four" <?php echo $student['year']=='Four'?'selected':''; ?>>Four Year</option>
                        </select>
                    </div>
                    <div>
                        <label>Semester</label>
                        <select name="semester" required>
                            <option value="">Select Semester</option>
                                    <?php foreach ($semester_list as $sem): ?>
                                        <option value="<?php echo $sem; ?>" <?php echo ($student['semester']==$sem?'selected':''); ?>>
                                            <?php echo $sem; ?>
                                        </option>
                                    <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label>Attendance (%)</label>
                        <input type="number" name="attendance" min="0" max="100" step="0.1"
                               value="<?php echo htmlspecialchars($student['attendance_percentage']); ?>">
                    </div>
                </div>

                <h3>📊 Subject Marks (0–100)</h3>
                <div class="subjects">
                    <?php foreach ($marks_data as $md): ?>
                        <div class="sub-card">
                            <label>Subject</label>
                            <div class="sub-flex">
                                <select name="subjects[]">
                                    <option value="">Select Subject</option>
                                    <?php foreach ($subjects_list as $s): ?>
                                        <option value="<?php echo $s; ?>" <?php echo ($md['subject']==$s?'selected':''); ?>>
                                            <?php echo $s; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="number" name="marks[]" min="0" max="100"
                                       value="<?php echo htmlspecialchars($md['g3']); ?>" placeholder="Marks">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

<div style="display:flex; gap:10px; margin-top:10px;">
    <button class="btn" type="submit">💾 Update Student</button>

    <a href="view_students.php" class="btn" style="display:inline-block; text-decoration:none;">
        ❌ Cancel
    </a>
</div>

</form>
</div>
                
                
        </div>
    </div>
</div>
</body>
</html>
