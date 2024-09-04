<?php
session_start(); 
include('includes/config.php');
 if(isset($_SESSION['alogin'])){
  $emp_id = $_SESSION['alogin'];
 }

 //login active status
$result = mysqli_query($conn, "UPDATE employee SET status='Offline' WHERE employee_id='$employee_id'");

$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 60*60,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
unset($_SESSION['alogin']);
session_destroy(); // destroy session

header("location:login.php"); 
?>

