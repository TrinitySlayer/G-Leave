<?php include('includes/header.php');?>
<?php include('../includes/session.php'); ?>

<?php 

if (isset($_GET['delete'])) {
    $delete = $_GET['delete'];

    // First, delete related records from leave_request
    $delete_related = "DELETE FROM leave_request WHERE employee_id = '$delete'";
    $result_related = mysqli_query($conn, $delete_related);
    
    if ($result_related) {
        // Now delete the employee record
        $sql = "DELETE FROM employee WHERE employee_id = '$delete'";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            echo "<script>alert('Staff deleted Successfully');</script>";
            echo "<script type='text/javascript'> document.location = 'staff.php'; </script>";
        } else {
            echo "<script>alert('Error deleting staff.');</script>";
        }
    } else {
        echo "<script>alert('Error deleting related records.');</script>";
    }
}

?>


<body>

    <?php include('includes/navbar.php')?>
    <?php include('includes/right_sidebar.php')?>
    <?php include('includes/left_sidebar.php')?>

    <div class="mobile-menu-overlay"></div>

    <div class="main-container">
        <div class="pd-ltr-5">
            <div class="title pb-20">
                <h2 class="h3 mb-0">Administrative Breakdown</h2>
            </div>
            <div class="row pb-10">
                <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
                    <div class="card-box height-100-p widget-style3">
                        <?php
                        $sql = "SELECT COUNT(employee_id) AS empcount FROM employee";
                        $result = $conn->query($sql);
                        $row = $result->fetch_assoc();
                        $empcount = $row['empcount'];
                        ?>

                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark"><?php echo $empcount; ?></div>
                                <div class="font-14 text-secondary weight-500">Total Employees</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon" data-color="#00eccf"><i class="icon-copy dw dw-user-2"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
                    <div class="card-box height-100-p widget-style3">
                        <?php 
                        $query_reg_staff = "SELECT COUNT(employee_id) AS staffcount FROM employee WHERE role = 'Staff'";
                        $result_staff = $conn->query($query_reg_staff);
                        $row_staff = $result_staff->fetch_assoc();
                        $count_reg_staff = $row_staff['staffcount'];
                        ?>

                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark"><?php echo $count_reg_staff; ?></div>
                                <div class="font-14 text-secondary weight-500">Staffs</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon" data-color="#09cc06"><span class="icon-copy fa fa-hourglass"></span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
                    <div class="card-box height-100-p widget-style3">
                        <?php 
                        $query_reg_admin = "SELECT COUNT(employee_id) AS admincount FROM employee WHERE role = 'HR'";
                        $result_admin = $conn->query($query_reg_admin);
                        $row_admin = $result_admin->fetch_assoc();
                        $count_reg_admin = $row_admin['admincount'];
                        ?>

                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark"><?php echo $count_reg_admin; ?></div>
                                <div class="font-14 text-secondary weight-500">Administrators</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon" data-color="#ff5b5b"><i class="icon-copy fa fa-hourglass-o" aria-hidden="true"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-box mb-30">
                <div class="pd-20">
                    <h2 class="text-blue h4">ALL EMPLOYEES</h2>
                </div>
                <div class="pb-20">
                    <table class="data-table table stripe hover nowrap">
                        <thead>
                            <tr>
                                <th class="table-plus">FULL NAME</th>
                                <th>POSITION</th>
                                <th class="datatable-nosort">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            $employee_query = "
                            SELECT employee.*, position_name, department.dept_name 
                            FROM employee 
                            LEFT JOIN department ON employee.dept_id = department.dept_id";
                            
                            // If department session is set, filter employees by department
                            if (!empty($_SESSION['dept_id'])) {
                                $employee_query .= " AND employee.dept_id = '".$_SESSION['dept_id']."'";
                            }

                            $employee_query .= " ORDER BY employee.employee_id";
                            $result_employee = $conn->query($employee_query);
                        
                            while ($row = $result_employee->fetch_assoc()) {
                                $id = $row['employee_id'];
                            ?>
                            <tr>
                                <td class="table-plus">
                                    <div class="name-avatar d-flex align-items-center">
                                        <div class="txt">
                                            <div class="weight-600"><?php echo $row['name']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo $row['position_name']; ?></td>
                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                                            <i class="dw dw-more"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                            <a class="dropdown-item" href="edit_staff.php?edit=<?php echo $row['employee_id'];?>"><i class="dw dw-edit2"></i> Edit</a>
                                            <a class="dropdown-item" href="staff.php?delete=<?php echo $row['employee_id'] ?>"><i class="dw dw-delete-3"></i> Delete</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php }
                            ?>
                        </tbody>
                    </table>
               </div>
            </div>

            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <?php include('includes/scripts.php')?>
</body>
</html>
