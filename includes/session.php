<?php
// Check whether the session variable SESS_MEMBER_ID is present or not
session_start();
if (!isset($_SESSION['alogin']) || (trim($_SESSION['alogin']) == '')) { 
    echo "<script>window.location = '../login.php';</script>";
}
$session_id = $_SESSION['alogin'];
$session_role = $_SESSION['arole'];
$session_dept = $_SESSION['adeptID'];
?>
