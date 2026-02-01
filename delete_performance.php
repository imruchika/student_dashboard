<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "teacher") {
    header("Location: page1/index.php");
    exit();
}

include("db/config.php");

if (isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];

    // STEP 1: Delete all marks of the student
    mysqli_query($conn, "DELETE FROM marks WHERE student_id = '$student_id'");

    // STEP 2: Delete student record (name, class, section)
    mysqli_query($conn, "DELETE FROM students WHERE student_id = '$student_id'");
}

// Redirect back to performance table
header("Location: view_performance.php");
exit();