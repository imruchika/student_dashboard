<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "teacher") {
    header("Location: page1/index.php");
    exit();
}

include("db/config.php");

$student_id = $_GET['student_id'] ?? null;
if (!$student_id) {
    header("Location: view_performance.php");
    exit();
}

/* Fetch student name */
$student = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT name FROM students WHERE student_id='$student_id'")
);

/* Fetch all subjects */
$subjects = mysqli_query($conn, "SELECT * FROM subjects");

/* Fetch existing marks */
$marks_data = [];
$marks_query = mysqli_query($conn, "SELECT * FROM marks WHERE student_id='$student_id'");
while ($m = mysqli_fetch_assoc($marks_query)) {
    $marks_data[$m['subject_id']] = $m['marks'];
}

/* Update marks */
if (isset($_POST['update_marks'])) {
    foreach ($_POST['marks'] as $subject_id => $marks) {

        if ($marks === "") continue;

        $check = mysqli_query(
            $conn,
            "SELECT * FROM marks WHERE student_id='$student_id' AND subject_id='$subject_id'"
        );

        if (mysqli_num_rows($check) > 0) {
            mysqli_query(
                $conn,
                "UPDATE marks SET marks='$marks'
                 WHERE student_id='$student_id' AND subject_id='$subject_id'"
            );
        } else {
            mysqli_query(
                $conn,
                "INSERT INTO marks (student_id, subject_id, marks)
                 VALUES ('$student_id', '$subject_id', '$marks')"
            );
        }
    }

    header("Location: view_performance.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Performance</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="form-container">

    <h2>Edit Performance</h2>
    <!-- <p style="text-align:center; font-weight:bold;">
        Student: <?php echo htmlspecialchars($student['name']); ?>
    </p> -->

    <form method="POST">

        <?php while ($sub = mysqli_fetch_assoc($subjects)) { ?>
            <div class="input-group">
                <label><?php echo $sub['subject_name']; ?></label>
                <input
                    type="number"
                    name="marks[<?php echo $sub['subject_id']; ?>]"
                    min="0"
                    max="100"
                    value="<?php echo $marks_data[$sub['subject_id']] ?? ''; ?>"
                    placeholder="Enter marks"
                >
            </div>
        <?php } ?>

        <button type="submit" name="update_marks">Update Marks</button>
    </form>

    <a href="view_performance.php" class="back-link">← Back to Performance</a>

</div>

</body>
</html>