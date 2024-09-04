<?php
session_start();
include '../config.php';

if (!isset($_SESSION['employee_id'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = $_POST['request_id'];
    $status_hr = $_POST['status_hr'];

    // Validate inputs
    if (empty($request_id) || empty($status_hr)) {
        echo "Invalid input.";
        exit;
    }

    // Update the leave request status in the database
    $sql = "UPDATE leave_request SET status_hr = ? WHERE leave_request_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'si', $status_hr, $request_id);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script> alert('Leave request updated successfully.')</script>";
    } else {
        echo "Error updating leave request.";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>
