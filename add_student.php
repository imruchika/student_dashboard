<?php
session_start();
if (
  empty($_SESSION['user_id']) ||
    empty($_SESSION['role']) ||
    $_SESSION['role'] !== 'teacher'
) {
    header("Location: /std_dashboard/page1/index.php");
    exit();
}
include("db/config.php");

//include("db/config.php");
$romanToInt = [
    'I' => 1,
    'II' => 2,
    'III' => 3,
    'IV' => 4,
    'V' => 5,
    'VI' => 6,
    'VII' => 7,
    'VIII' => 8
];

$semRoman = trim($_POST['semester'] ?? '');
$sem = $romanToInt[$semRoman] ?? 0;


$success = $error = "";

// Subjects you want in dropdown
$subjects_list = [
    'Python','Java',
    'C++','DSA','DBMS'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $name   = mysqli_real_escape_string($conn, trim($_POST['name'] ?? ''));
    $course = mysqli_real_escape_string($conn, trim($_POST['course'] ?? ''));
    $section= mysqli_real_escape_string($conn, trim($_POST['section'] ?? ''));
    $year   = mysqli_real_escape_string($conn, trim($_POST['year'] ?? ''));
    // semester as plain number string "1".."8"
    $sem    = mysqli_real_escape_string($conn, trim($_POST['semester'] ?? ''));
    $extra  = $_POST['extracurricular'] ?? 'no';
    $extra_field = mysqli_real_escape_string($conn, trim($_POST['extra_field'] ?? ''));
    $attendance_input = trim($_POST['attendance'] ?? '');
    $attendance_percentage = ($attendance_input === '' ? 0 : (float)$attendance_input);
$added_by = $_SESSION['username'];
    if ($name === '' || $course === '' || $section === '') {
        $error = "Name, Course and Section are required.";
    } elseif ($attendance_percentage < 0 || $attendance_percentage > 100) {
        $error = "Attendance must be between 0 and 100.";
    } elseif ($sem ===0){
        $error = "Please select a valid semester.";
    } else {
            $role = "student";
            $prn = date('YmdHis');  
            $hashedPassword = password_hash("stud123", PASSWORD_DEFAULT);//creting default password for student
            $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO users (username, password, role) VALUES (?, ?, ?)"
        );
                
            mysqli_stmt_bind_param($stmt, "sss", $prn, $hashedPassword, $role);
           mysqli_stmt_execute($stmt);

            // get last inserted id
            $user_id = mysqli_insert_id($conn);
              
                $q = "INSERT INTO students ( prn, user_id, name, class, section, year, semester, attendance_percentage, extracurricular,added_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $q);
                mysqli_stmt_bind_param(
                    $stmt,
                    "sdsssssdss",
                    $prn,
                    $user_id,
                    $name,
                    $course,
                    $section,
                    $year,
                    $sem,
                    $attendance_percentage,
                    $extra,
                    $added_by
                );

                if (mysqli_stmt_execute($stmt)) {
                    $student_id = mysqli_insert_id($conn);
                    
                    for ($i = 1; $i <= 5; $i++) {
                        $subject = mysqli_real_escape_string($conn, trim($_POST["subject$i"] ?? ''));
                        $marks   = (int)($_POST["marks$i"] ?? 0);

                        if ($subject !== '' && $marks >= 0 && $marks <= 100) {
                            $mq = "INSERT INTO marks (student_id, subject, g3) VALUES (?, ?, ?)";
                            $mstmt = mysqli_prepare($conn, $mq);
                            mysqli_stmt_bind_param($mstmt, "isi", $student_id, $subject, $marks);
                            mysqli_stmt_execute($mstmt);
                            mysqli_stmt_close($mstmt);
                        }
                    }

                    if ($extra === 'yes') {
                        $bonus_q = "INSERT INTO marks (student_id, subject, g3) VALUES (?, 'Activity Bonus', 5)";
                        $bstmt = mysqli_prepare($conn, $bonus_q);
                        mysqli_stmt_bind_param($bstmt, "i", $student_id);
                        mysqli_stmt_execute($bstmt);
                        mysqli_stmt_close($bstmt);
                    }

                    $success = "Student '$name' added successfully.";
                } else {
                    $error = "Error inserting student: " . mysqli_error($conn);
                }

         }
        mysqli_stmt_close($stmt);
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Student</title>
<link rel=stylesheet href="style.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="add-std-body">
    <div style="display:flex; gap:10px; margin-top:10px;">
    <button type="button" class="back-btn" onclick="history.back()">Back</button>
</div>
<div class="wrapper">
    <div class="card">
        <div class="card-header">
            <div>
                <h2>Add New Student</h2>
                <span>Enter student details, subjects, attendance, year/semester and extra‑curricular info</span>
            </div>
        </div>
        <div class="card-body">
            <?php if ($success): ?>
                <div class="message success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="post" action="add_student.php">
                <div class="row">
                    <div>
                        <label>Name *</label>
                        <input type="text" name="name" required>
                    </div>
                    <div>
                        <label>Course *</label>
                        <input type="text" name="course" required>
                    </div>
                    <div>
                        <label>Section *</label>
                        <input type="text" name="section" required>
                    </div>
                    <div>
                        <label>Attendance (%)</label>
                        <input type="number" name="attendance" min="0" max="100" step="0.1" placeholder="e.g. 85.5">
                    </div>
                </div>

                <div class="row">
                    <div>
                        <label>Year</label>
                        <select name="year" required>
                            <option value="">Select Year</option>
                            <option value="first">First Year</option>
                            <option value="second">Second Year</option>
                            <option value="third">Third Year</option>
                            <option value="four">Fourth Year</option>
                        </select>
                    </div>
                    <div>
                        <label>Semester</label>
                        <select name="semester" required>
                            <option value="">Select Semester</option>
                            <option value="I">I</option>
                            <option value="II">II</option>
                            <option value="III">III</option>
                            <option value="IV">IV</option>
                            <option value="V">V</option>
                            <option value="VI">VI</option>
                            <option value="VII">VII</option>
                            <option value="VIII">VIII</option>
                        </select>
                    </div>
                </div>

                <h3>📚 Subjects & Marks (5)</h3>
                <div class="subjects">
                    <?php for ($i=1;$i<=5;$i++): ?>
                        <div class="sub-card">
                            <label>Subject <?php echo $i; ?></label>
                            <div class="sub-flex">
                                <select name="subject<?php echo $i; ?>" required>
                                    <option value="">Select Subject</option>
                                    <?php foreach ($subjects_list as $s): ?>
                                        <option value="<?php echo $s; ?>"><?php echo $s; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="number" name="marks<?php echo $i; ?>" min="0" max="100" required placeholder="Marks">
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>

                <h3>🎭 Extra‑curricular Activities</h3>
                <div>
                    <label>Participated?</label>
                    <div class="radio-group">
                        <label class="radio-pill">
                            <input type="radio" name="extracurricular" value="yes">
                            <span>Yes</span>
                        </label>
                        <label class="radio-pill">
                            <input type="radio" name="extracurricular" value="no" checked>
                            <span>No</span>
                        </label>
                    </div>
                </div>
                <div id="extraFieldWrapper" class="hidden">
                    <label>In which field?</label>
                    <select name="extra_field">
                        <option value="">Select activity</option>
                        <option value="Yoga">Yoga</option>
                        <option value="Dance">Dance</option>
                        <option value="Sports">Sports</option>
                        <option value="Drama">Drama</option>
                        <option value="Singing">Singing</option>
                        <option value="Other">Other</option>
                    </select>
                    <small>If "Yes" is selected, 5 bonus marks are added as "Activity Bonus" in total marks.</small>
                </div>
                   <div style="display:flex; gap:10px; margin-top:10px;">
    
    <button type="submit" class="btn btn-primary">Save Student</button>
           </div>



               
            </form>
        </div>
    </div>
</div>

<script>
    const radios = document.querySelectorAll('input[name="extracurricular"]');
    const extraWrap = document.getElementById('extraFieldWrapper');

    radios.forEach(r => {
        r.addEventListener('change', () => {
            if (r.value === 'yes' && r.checked) {
                extraWrap.classList.remove('hidden');
            } else if (r.value === 'no' && r.checked) {
                extraWrap.classList.add('hidden');
            }
        });
    });
</script>
</body>
</html>
