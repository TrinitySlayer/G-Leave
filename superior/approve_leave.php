<?php
session_start();
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['employee_id'])) {
        header("Location: ../login.php");
        exit;
    }

    $request_id = $_POST['request_id'];
    $status = $_POST['status_superior'];

    // Update the leave request status in the database
    $sql = "UPDATE leave_request SET status_superior = ? WHERE leave_request_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status, $request_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: leaveRequest_list.php");
}
?>
