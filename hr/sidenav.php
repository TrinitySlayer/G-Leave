<?php
require_once '../config.php';

// Check if the user is logged in
if (!isset($_SESSION['employee_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch user details
$employee_id = $_SESSION['employee_id'];
$query = "SELECT name FROM employee WHERE employee_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <!--<title> Drop Down Sidebar Menu | CodingLab </title>-->
    <link rel="stylesheet" href="style.css">
    <!-- Boxicons CDN Link -->
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" type="image/x-icon" href="/images/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<div class="sidebar close">
    <!---<div class="logo-details">
        <img src="./bg/GolacFav.ico" type="image/x-icon" alt="Golac Commerce" id="logo" height="80" width="80">
        <span class="logo_name">e-LeaveGolac</span>
    </div>--->
    <ul class="nav-links">
        <li>
            <a href="#">
                <i class='bx bx-grid-alt'></i>
                <span class="link_name">Employee List</span>
            </a>
            <ul class="sub-menu blank">
                <li><a class="link_name" href="employee_list.php">Employee List</a></li>
            </ul>
        </li>
        <li>
            <a href="#">
                <i class='bx bx-log-out'></i>
                <span class="link_name">Logout</span>
            </a>
            <ul class="sub-menu blank">
                <li><a class="link_name" href="../logout.php">Logout</a></li>
            </ul>
        </li>
    </ul>
</div>

<script>
let arrow = document.querySelectorAll(".arrow");
for (var i = 0; i < arrow.length; i++) {
    arrow[i].addEventListener("click", (e) => {
        let arrowParent = e.target.parentElement.parentElement; // selecting main parent of arrow
        arrowParent.classList.toggle("showMenu");
    });
}
let sidebar = document.querySelector(".sidebar");
let sidebarBtn = document.querySelector(".bx-menu");
console.log(sidebarBtn);
sidebarBtn.addEventListener("click", () => {
    sidebar.classList.toggle("close");
});
</script>
</body>
</html>
