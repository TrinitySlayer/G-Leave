<?php
session_start();

include('includes/config.php');


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id = isset($_POST['employee_id']) ? $_POST['employee_id'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Prepare the SQL query to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM employee WHERE employee_id = ?");
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Check if the provided password matches the hashed password
        if (password_verify($password, $row['password'])) {
            // Set session variables
            $_SESSION['alogin'] = $row['employee_id'];
            $_SESSION['arole'] = $row['role'];
            $_SESSION['adeptID'] = $row['dept_id'];

            // Update login status to 'Online'
            $session_id = $_SESSION['alogin'];
            $status_update = mysqli_query($conn, "UPDATE employee SET status='Online' WHERE employee_id='$session_id'");

            // Redirect based on the role
            if ($row['role'] == 'HR') {
                header('Location: hr/HR_approval_leave.php');
                exit;
            } elseif ($row['role'] == 'Staff') {
                header('Location: staff/dashboard.php');
                exit;
            } 
        } else {
            echo "<script>alert('Invalid employee ID or password');</script>";
        }
    } else {
        echo "<script>alert('Invalid employee ID or password');</script>";
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="framework/login/images/icons/GolacFav.ico"/>
    <link rel="stylesheet" type="text/css" href="framework/login/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="framework/login/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="framework/login/fonts/Linearicons-Free-v1.0.0/icon-font.min.css">
    <link rel="stylesheet" type="text/css" href="framework/login/vendor/animate/animate.css">
    <link rel="stylesheet" type="text/css" href="framework/login/vendor/css-hamburgers/hamburgers.min.css">
    <link rel="stylesheet" type="text/css" href="framework/login/vendor/animsition/css/animsition.min.css">
    <link rel="stylesheet" type="text/css" href="framework/login/vendor/select2/select2.min.css">
    <link rel="stylesheet" type="text/css" href="framework/login/vendor/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" type="text/css" href="framework/login/css/util.css">
    <link rel="stylesheet" type="text/css" href="framework/login/css/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
</head>
<style>
    .wrap-input100 {
    position: relative;
}

.wrap-input100 .toggle-password {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
}

</style>
<body>
    <div id="login" class="limiter">
        <div class="container-login100">
            <div class="wrap-login100">
                <div class="login100-form-title" style="background-image: url(framework/login/images/e-leaveGolac.png);">
                    <span class="login100-form-title-1"></span>
                </div>
                <form action="login.php" method="post" class="login100-form validate-form">
                    <div class="wrap-input100 validate-input m-b-26" data-validate="Employee ID is required">
                        <span class="label-input100"><i class='bx bx-user'></i></span>
                        <input class="input100" type="text" name="employee_id" id="employee_id" placeholder="Enter Employee ID">
                        <span class="focus-input100"></span>
                    </div>
                    <div class="wrap-input100 validate-input m-b-18" data-validate="Password is required">
                        <span class="label-input100"><i class='bx bx-lock-alt'></i></span>
                        <input class="input100" type="password" name="password" id="password" placeholder="Enter Password">
                        <span class="focus-input100"></span>
                        <span class="toggle-password">
                            <i class="bx bx-show" id="togglePassword"></i>
                        </span>
                    </div>
                    <div class="container-login100-form-btn">
                        <button type="submit" class="login100-form-btn" name="submit">
                            Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="framework/login/vendor/jquery/jquery-3.2.1.min.js"></script>
    <script src="framework/login/vendor/animsition/js/animsition.min.js"></script>
    <script src="framework/login/vendor/bootstrap/js/popper.js"></script>
    <script src="framework/login/vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="framework/login/vendor/select2/select2.min.js"></script>
    <script src="framework/login/vendor/daterangepicker/moment.min.js"></script>
    <script src="framework/login/vendor/daterangepicker/daterangepicker.js"></script>
    <script src="framework/login/vendor/countdowntime/countdowntime.js"></script>
    <script src="framework/login/js/main.js"></script>
    <script>
        // Toggle Password Visibility
        const togglePassword = document.querySelector('#togglePassword');
        const passwordField = document.querySelector('#password');

        togglePassword.addEventListener('click', function () {
            // Toggle the type attribute
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);

            // Toggle the icon
            this.classList.toggle('bx-show');
            this.classList.toggle('bx-hide');
        });

    </script>
</body>
</html>
