<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "teacher"){
    header("Location: auth/index.php");
    exit();
}

include("db/config.php");

$student_id = $_GET['id'] ?? null;

if (!$student_id) {
    header("Location: view_students.php");
    exit();
}

// Get student name for confirmation
$student_query = mysqli_query($conn, "SELECT name FROM students WHERE student_id='$student_id'");
$student = mysqli_fetch_assoc($student_query);

if (!$student) {
    header("Location: view_students.php");
    exit();
}

// Delete student marks first (foreign key constraint)
mysqli_query($conn, "DELETE FROM marks WHERE student_id='$student_id'");

// Delete student record
$delete_query = "DELETE FROM students WHERE student_id='$student_id'";

if (mysqli_query($conn, $delete_query)) {
    // Success - redirect with message
    header("Location: view_students.php?deleted=1&name=" . urlencode($student['name']));
    exit();
} else {
    // Error - redirect with error
    header("Location: view_students.php?error=1");
    exit();
}
?>