<?php
include('includes/header.php');
include('../includes/session.php');

if (!isset($_SESSION['alogin'])) {
    header("Location: ../login.php");
    exit();
} else {
    $session_id = $_SESSION['alogin'];
}

$get_id = $_GET['edit'];

// Handle new position addition
if (isset($_POST['add_position'])) {
    $new_position_name = $_POST['new_position_name'];

    // Check if the position already exists
    $check_position_query = mysqli_query($conn, "SELECT * FROM positions WHERE name = '$new_position_name'") or die(mysqli_error($conn));
    if (mysqli_num_rows($check_position_query) > 0) {
        echo "<script>alert('Position already exists.');</script>";
    } else {
        // Insert new position
        $insert_query = mysqli_query($conn, "INSERT INTO positions (name) VALUES ('$new_position_name')") or die(mysqli_error($conn));
        if ($insert_query) {
            echo "<script>alert('Position successfully added.');</script>";
        } else {
            die(mysqli_error($conn));
        }
    }
}

// Handle image deletion
if (isset($_POST['delete_image'])) {
    $employee_id = $_POST['employee_id'];

    // Get current picture path
    $query = mysqli_query($conn, "SELECT picture FROM employee WHERE employee_id = '$employee_id'") or die(mysqli_error($conn));
    $row = mysqli_fetch_array($query);
    $image_path = $row['picture'];

    if ($image_path && file_exists($image_path)) {
        unlink($image_path); // Delete the image from the server

        // Update the employee record to remove the image path
        $update_query = mysqli_query($conn, "UPDATE employee SET picture = '' WHERE employee_id = '$employee_id'") or die(mysqli_error($conn));
        if ($update_query) {
            echo "<script>alert('Image deleted successfully.');</script>";
            $image_path = 'uploads/Profile Icon.webp'; // Reset to default image
        } else {
            echo "<script>alert('Failed to delete image.');</script>";
        }
    } else {
        echo "<script>alert('Image not found.');</script>";
    }
}

// Update existing employee information, including the password
if (isset($_POST['update_staff'])) {
    $name = $_POST['name'];
    $staff_id = $_POST['employee_id'];
    $department = $_POST['department'];
    $position_name = $_POST['position_name'];
    $new_password = $_POST['new_password'];
    $gender = $_POST['gender'];
    $user_role = $_POST['role'];
    $join_date = $_POST['join_date'];

    // If a new password is provided, update it
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT); // Hash the new password
        $update_query = mysqli_query($conn, "UPDATE employee SET name='$name', gender='$gender', dept_id='$department', role='$user_role', position_name='$position_name', employee_id='$staff_id', join_date='$join_date', password='$hashed_password' WHERE employee_id='$get_id'") or die(mysqli_error($conn));
    } else {
        // Update employee record without changing the password
        $update_query = mysqli_query($conn, "UPDATE employee SET name='$name', gender='$gender', dept_id='$department', role='$user_role', position_name='$position_name', employee_id='$staff_id', join_date='$join_date' WHERE employee_id='$get_id'") or die(mysqli_error($conn));
    }

    if ($update_query) {
        echo "<script>alert('Record Successfully Updated');</script>";
        echo "<script type='text/javascript'> document.location = 'staff.php'; </script>";
    } else {
        die(mysqli_error($conn));
    }
}
?>

<body>
    <?php include('includes/navbar.php')?>
    <?php include('includes/right_sidebar.php')?>
    <?php include('includes/left_sidebar.php')?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

    <div class="mobile-menu-overlay"></div>

    <div class="main-container">
        <div class="pd-ltr-20 xs-pd-20-10">
            <div class="min-height-200px">
                <div class="page-header">
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="title">
                                <h4>Staff Portal</h4>
                            </div>
                            <nav aria-label="breadcrumb" role="navigation">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="HR_approval_leave.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Staff Edit</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <div class="pd-20 card-box mb-30">
                    <div class="clearfix">
                        <div class="pull-left">
                            <h4 class="text-blue h4">Edit Staff</h4>
                            <p class="mb-20"></p>
                        </div>
                    </div>
                    <div class="wizard-content">
                        <!-- Form to Edit Staff -->
                        <form method="post" action="" onsubmit="return checkPasswordMatch();">
                            <section>
                                <?php
                                $query = mysqli_query($conn, "SELECT * FROM employee WHERE employee_id = '$get_id'") or die(mysqli_error($conn));
                                $row = mysqli_fetch_array($query);
                                ?>

                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="form-group">
                                            <label>Name :</label>
                                            <input name="name" type="text" class="form-control wizard-required" required="true" autocomplete="off" value="<?php echo htmlspecialchars($row['name']); ?>">
                                        </div>
                                    </div>
                                </div>        
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label>Staff ID :</label>
                                            <input name="employee_id" type="text" class="form-control" required="true" autocomplete="off" value="<?php echo htmlspecialchars($row['employee_id']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label>Gender :</label>
                                            <select name="gender" class="custom-select form-control" required="true" autocomplete="off">
                                                <option value="Male" <?php echo $row['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                                <option value="Female" <?php echo $row['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class = "row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label>New Password :</label>
                                            <input id="new_password" name="new_password" type="password" placeholder="**********" class="form-control" autocomplete="off">
                                            <span class="focus-input100"></span>
                                            <span class="toggle-password">
                                                <i class="bx bx-show" id="togglePassword"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label>Confirm Password :</label>
                                            <input id="confirm_password" type="password" placeholder="**********" class="form-control" autocomplete="off">
                                            <span class="focus-input100"></span>
                                            <span class="toggle-password">
                                                <i class="bx bx-show" id="toggleConfirmPassword"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label>Department :</label>
                                            <select name="department" class="custom-select form-control" required="true" autocomplete="off">
                                                <?php
                                                $query_staff = mysqli_query($conn, "SELECT * FROM department") or die(mysqli_error($conn));
                                                while ($department_row = mysqli_fetch_array($query_staff)) {
                                                    $selected = $department_row['dept_id'] == $row['dept_id'] ? 'selected' : '';
                                                    echo "<option value='{$department_row['dept_id']}' $selected>{$department_row['dept_name']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label>Staff Position :</label>
                                            <input name="position_name" type="text" class="form-control" required="true" autocomplete="off" value="<?php echo htmlspecialchars($row['position_name']); ?>">
                                        </div>
                                    </div>
                                
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label>User Role :</label>
                                            <select name="role" class="custom-select form-control" required="true" autocomplete="off">
                                                <option value="HR" <?php echo $row['role'] == 'HR' ? 'selected' : ''; ?>>HR</option>
                                                <option value="Staff" <?php echo $row['role'] == 'Staff' ? 'selected' : ''; ?>>Staff</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label>Joining Date :</label>
                                            <input name="join_date" type="text" class="form-control date-picker" required="true" autocomplete="off" value="<?php echo htmlspecialchars($row['join_date']); ?>">
                                        </div>
                                    </div>
                                </div>
                                <button class="btn btn-primary" name="update_staff" type="submit">Update Staff</button>
                            </section>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('includes/footer.php')?>
    <?php include('includes/scripts.php')?>
    <script>
        function checkPasswordMatch() {
            var password = document.getElementById("new_password").value;
            var confirmPassword = document.getElementById("confirm_password").value;

            if (password !== confirmPassword) {
                alert("Passwords do not match.");
                return false;
            }
            return true;
        }

        // Toggle Password Visibility
        const togglePassword = document.querySelector('#togglePassword');
        const passwordField = document.querySelector('#new_password');
        const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
        const confirmPasswordField = document.querySelector('#confirm_password');

        togglePassword.addEventListener('click', function () {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.classList.toggle('bx-show');
            this.classList.toggle('bx-hide');
        });

        toggleConfirmPassword.addEventListener('click', function () {
            const type = confirmPasswordField.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordField.setAttribute('type', type);
            this.classList.toggle('bx-show');
            this.classList.toggle('bx-hide');
        });
    </script>
</body>
