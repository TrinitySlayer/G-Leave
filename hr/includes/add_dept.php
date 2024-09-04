<?php include('includes/header.php')?>
<?php include('../includes/session.php')?>

<?php
if (!isset($_SESSION['alogin']) || trim($_SESSION['alogin']) == '') {
    header("Location: ../login.php");
    exit();
}
$session_id = $_SESSION['alogin'];

// Fetch departments for the dropdown
$dept_type = "SELECT dept_name FROM department";
$dept_type_result = mysqli_query($conn, $dept_type); 

if (isset($_POST['add_staff'])) {
    // Form data processing
    $name = $_POST['name'];
    $staff_id = $_POST['employee_id'];   
    $department = $_POST['department']; 
    $position_name = $_POST['position_id']; 
    $password = $_POST['password']; 
    $gender = $_POST['gender'];
    $user_role = $_POST['role']; 
    $join_date = $_POST['join_date']; 
    $status = "Offline";

    // Convert department name to dept_id
    $stmt = $conn->prepare("SELECT dept_id FROM department WHERE dept_name = ?");
    $stmt->bind_param("s", $department);
    $stmt->execute();
    $dept_result = $stmt->get_result();
    $dept_row = $dept_result->fetch_assoc();
    $dept_id = $dept_row['dept_id'];

    // Check if employee already exists
    $stmt = $conn->prepare("SELECT * FROM employee WHERE employee_id = ?");
    $stmt->bind_param("s", $staff_id);
    $stmt->execute();
    $query = $stmt->get_result();
    $count = $query->num_rows;

    if ($count > 0) {
        echo "<script>alert('Data Already Exist');</script>";
    } else {
        // Insert into the database without image path
        $stmt = $conn->prepare("INSERT INTO employee (name, employee_id, dept_id, position_name, password, gender, role, status, join_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssissssss", $name, $staff_id, $dept_id, $position_name, $password, $gender, $user_role, $status, $join_date);
        $stmt->execute();
        echo "<script>alert('Staff Records Successfully Added');</script>";
        echo "<script>window.location = 'staff.php';</script>";
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
                                    <li class="breadcrumb-item"><a href="index.html">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Staff Module</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <div class="pd-20 card-box mb-30">
                    <div class="clearfix">
                        <div class="pull-left">
                            <h4 class="text-blue h4">Department Form</h4>
                            <p class="mb-20"></p>
                        </div>
                    </div>
                    <div class="wizard-content">
                        <form method="post" action="" onsubmit="return checkPasswordMatch();" enctype="multipart/form-data">
                            <section>
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="form-group">
                                            <label>Department Name :</label>
                                            <input name="name" type="text" class="form-control wizard-required" required="true" autocomplete="off">
                                        </div>
                                    </div>
                                </div>        
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label>Staff ID :</label>
                                            <input name="employee_id" type="text" class="form-control" required="true" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label>Gender :</label>
                                            <select name="gender" class="custom-select form-control" required="true" autocomplete="off">
                                                <option value="">Select Gender</option>
                                                <option value="male">Male</option>
                                                <option value="female">Female</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label>Password :</label>
                                            <input id="password" name="password" type="password" placeholder="**********" class="form-control" required="true" autocomplete="off">
                                            <span class="toggle-password">
                                                <i class="bx bx-show" id="togglePassword"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label>Confirm Password :</label>
                                            <input id="confirm_password" type="password" placeholder="**********" class="form-control" required="true" autocomplete="off">
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
                                                <option value="">Select Department</option>
                                                <?php while ($row = mysqli_fetch_assoc($dept_type_result)) { ?>
                                                    <option value="<?php echo $row['dept_name']; ?>"><?php echo $row['dept_name']; ?></option>
                                                <?php } ?>                                                
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label>Staff Position :</label>
                                            <input name="position_id" type="text" class="form-control wizard-required" required="true" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label>User Role :</label>
                                            <select name="role" class="custom-select form-control" required="true" autocomplete="off">
                                                <option value="">Select Role</option>
                                                <option value="HR">HR</option>
                                                <option value="Superior">Superior</option>
                                                <option value="Staff">Staff</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="form-group">
                                            <label>Join Date :</label>
                                            <input name="join_date" type="text" class="form-control date-picker" required="true" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">                            
                                    <div class="col-md-12 col-sm-12">
                                        <div class="form-group">
                                            <label style="font-size:16px;"><b></b></label>
                                            <div class="modal-footer justify-content-center">
                                                <button class="btn btn-primary" name="add_staff" id="add_staff" data-toggle="modal">Add&nbsp;Staff</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </form>
                    </div>
                </div>
            </div>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <!-- JavaScript to check if passwords match and toggle visibility -->
    <script>
        function checkPasswordMatch() {
            var password = document.getElementById("password").value;
            var confirmPassword = document.getElementById("confirm_password").value;

            if (password !== confirmPassword) {
                alert("Passwords do not match.");
                return false;
            }
            return true;
        }

        // Toggle Password Visibility
        const togglePassword = document.querySelector('#togglePassword');
        const passwordField = document.querySelector('#password');
        const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
        const confirmPasswordField = document.querySelector('#confirm_password');

        togglePassword.addEventListener('click', function () {
            // Toggle the type attribute
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);

            // Toggle the icon
            this.classList.toggle('bx-show');
            this.classList.toggle('bx-hide');
        });

        toggleConfirmPassword.addEventListener('click', function () {
            // Toggle the type attribute
            const type = confirmPasswordField.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordField.setAttribute('type', type);

            // Toggle the icon
            this.classList.toggle('bx-show');
            this.classList.toggle('bx-hide');
        });
    </script>
    
    <!-- js -->
    <?php include('includes/scripts.php')?>
</body>
</html>
