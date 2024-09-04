<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['employee_id'])) {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id'])) {
    $request_id = $_GET['id'];

    // Validate input
    if (empty($request_id)) {
        echo "Invalid request ID.";
        exit;
    }

    // Delete the leave request from the database
    $sql = "DELETE FROM leave_request WHERE leave_request_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $request_id);

    if (mysqli_stmt_execute($stmt)) {
        echo "Leave request deleted successfully.";
    } else {
        echo "Error deleting leave request.";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    // Redirect back to the leave requests page
    header("Location: ./../HR_approval_leave.php");
    exit;
} else {
    echo "No request ID provided.";
}
?>
