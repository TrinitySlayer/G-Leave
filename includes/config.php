<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'leave_golac');

// Establish MySQLi connection
$conn = mysqli_connect('localhost:3306', DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set the charset to UTF-8
mysqli_set_charset($conn, 'utf8');

?>
