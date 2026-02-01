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
        $q = "INSERT INTO students (name, class, section, year, semester, attendance_percentage, extracurricular,added_by) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $q);
        mysqli_stmt_bind_param(
            $stmt,
            "sssssdss",
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

        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Student</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
    *{
        box-sizing:border-box;
        font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
    }
    body{
        margin:0;
        padding:24px;
        min-height:100vh;
         background: linear-gradient(to right, #DDD0C8, #bbb4b4);
        display:flex;
        align-items:center;
        justify-content:center;
    }
    .wrapper{
        width:100%;
        max-width:1050px;
    }
    .card{
        background: #ffffff;
        border-radius:20px;
        padding:0;
        box-shadow:0 20px 50px rgba(0,0,0,.25);
        max-height:90vh;
        overflow:hidden;
        display:flex;
        flex-direction:column;
        animation:fadeIn 0.5s ease-out;
    }
    .card-header{
        padding:18px 26px;    
        background-color: #413f3f;
        color: #DDD0C8;
        display:flex;
        align-items:center;
        justify-content:space-between;
    }
    .card-header h2{
        margin:0;
        font-size:22px;
        font-weight:650;
    }
    .card-header span{
        font-size:13px;
        opacity:0.9;
    }
    .card-body{
        padding:22px 24px 24px;
        overflow-y:auto;
    }
    h3{
        margin:18px 0 10px;
        font-size:17px;
        color: #222;
    }
    .row{
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
        gap:16px;
        margin-bottom:18px;
    }
    label{
        font-weight:600;
        font-size:13px;
        display:block;
        margin-bottom:6px;
        color: #374151;
    }
    input,select{
        width:100%;
        padding:10px 12px;
        border-radius:10px;
        border:1px solid #d1d5db;
        font-size:14px;
        background: #f9fafb;
        transition:all 0.2s ease;
    }
    input:focus,select:focus{
        outline:none;
        border-color: #3d3d48;
        box-shadow:0 0 0 3px rgba(93, 93, 97, 0.2);
        background:#ffffff;
    }
    .subjects{
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
        gap:14px;
    }
    .sub-card{
        border-radius:14px;
        padding:12px 12px 14px;
        background: #f9fafb;
        border:1px solid #e5e7eb;
        transition:box-shadow 0.2s ease, transform 0.2s ease, border-color 0.2s ease;
    }
    .sub-card:hover{
        box-shadow:0 6px 18px rgba(148,163,184,0.35);
        transform:translateY(-2px);
        border-color: #59595d;
    }
    .sub-flex{
        display:flex;
        gap:10px;
        align-items:center;
        margin-top:6px;
    }
    .sub-flex input[type=number]{
        max-width:90px;
    }
    .radio-group{
        display:flex;
        gap:10px;
        flex-wrap:wrap;
        margin-top:6px;
    }
    .radio-pill{
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding:6px 14px;
        border-radius:999px;
        background: #eef2ff;
        border:1px solid #c7d2fe;
        font-size:13px;
        cursor:pointer;
        transition:all 0.2s ease;
    }
    .radio-pill:hover{
        background: #e0e7ff;
    }
    .radio-pill input{
        width:14px;
        height:14px;
    }
    #extraFieldWrapper{
        margin-top:8px;
        padding:10px 12px;
        border-radius:12px;
        background: #f0fdf4;
        border:1px dashed #6ee7b7;
        transition:all 0.2s ease;
    }
    #extraFieldWrapper.hidden{
        max-height:0;
        padding-top:0;
        padding-bottom:0;
        opacity:0;
        overflow:hidden;
        border-width:0;
        margin-top:0;
    }
    #extraFieldWrapper small{
        display:block;
        margin-top:4px;
        color: #047857;
        font-size:11px;
    }
    .message{
        padding:10px 12px;
        border-radius:10px;
        margin-bottom:14px;
        font-size:13px;
    }
    .success{
        background:#dcfce7;
        color: #166534;
        border:1px solid #86efac;
    }
    .error{
        background:#fee2e2;
        color: #b91c1c;
        border:1px solid #fecaca;
    }
    .btn{
        width:100%;
        padding:12px;
        border:none;
        border-radius:999px;
        background-color: #413f3f;
        color: #DDD0C8;
        font-weight:700;
        font-size:15px;
        cursor:pointer;
        margin-top:10px;
        box-shadow:0 12px 30px rgba(22, 22, 24, 0.45);
        transition:transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
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
    .btn:hover{
        transform:translateY(-2px);
        box-shadow:0 16px 36px rgba(43, 43, 46, 0.55);
        opacity:0.97;
    }
    .btn:active{
        transform:translateY(0);
        box-shadow:0 10px 24px rgba(46, 46, 51, 0.45);
    }
    @keyframes fadeIn{
        from{opacity:0;transform:translateY(15px);}
        to{opacity:1;transform:translateY(0);}
    }
    @media(max-width:768px){
        body{padding:16px;}
        .card-header{flex-direction:column;align-items:flex-start;gap:4px;}
    }
</style>
</head>
<body>
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
    <!-- <button type="button" class="btn btn-primary" onclick="history.back()">Back</button> -->
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
