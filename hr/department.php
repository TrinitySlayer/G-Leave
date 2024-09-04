<?php include('includes/header.php')?>
<?php include('../includes/session.php')?>

<?php 
    if (isset($_GET['delete'])) {
        $department_id = $_GET['delete'];
        $sql = "DELETE FROM department WHERE dept_id = ".$department_id;
        $result = mysqli_query($conn, $sql);
        if ($result) {
            echo "<script>alert('Department deleted Successfully');</script>";
            echo "<script type='text/javascript'> document.location = 'department.php'; </script>";
        }
    }
?>

<?php
 if(isset($_POST['add'])) {
    $deptname = $_POST['dept_name'];

    $query = mysqli_query($conn, "SELECT * FROM department WHERE dept_name = '$deptname'") or die(mysqli_error($conn));
    $count = mysqli_num_rows($query);

    if ($count > 0) { 
        echo "<script>alert('Department Already exists');</script>";
    } else {
        $query = mysqli_query($conn, "INSERT INTO department (dept_name) VALUES ('$deptname')") or die(mysqli_error($conn)); 

        if ($query) {
            echo "<script>alert('Department Added Successfully');</script>";
            echo "<script type='text/javascript'> document.location = 'department.php'; </script>";
        }
    }
}
?>

<body>
    <?php include('includes/navbar.php')?>
    <?php include('includes/right_sidebar.php')?>
    <?php include('includes/left_sidebar.php')?>

    <div class="mobile-menu-overlay"></div>

    <div class="main-container">
        <div class="pd-ltr-20 xs-pd-20-10">
            <div class="min-height-200px">
                <div class="page-header">
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="title">
                                <h4>Department List</h4>
                            </div>
                            <nav aria-label="breadcrumb" role="navigation">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="HR_approval_leave.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Department Module</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-30">
                        <div class="card-box pd-30 pt-10 height-100-p">
                            <h2 class="mb-30 h4">New Department</h2>
                            <section>
                                <form name="save" method="post">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Department Name</label>
                                                <input name="dept_name" type="text" class="form-control" required="true" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 text-right">
                                        <div class="dropdown">
                                            <input class="btn btn-primary" type="submit" value="REGISTER" name="add" id="add">
                                        </div>
                                    </div>
                                </form>
                            </section>
                        </div>
                    </div>
                    
                    <div class="col-lg-8 col-md-6 col-sm-12 mb-30">
                        <div class="card-box pd-30 pt-10 height-100-p">
                            <h2 class="mb-30 h4">Department List</h2>
                            <div class="pb-20">
                                <table class="data-table table stripe hover nowrap">
                                    <thead>
                                        <tr>
                                            <th>SR NO.</th>
                                            <th class="table-plus">DEPARTMENT</th>
                                            <th class="datatable-nosort">ACTION</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $sql = "SELECT * FROM department";
                                        $query = mysqli_query($conn, $sql);
                                        if(mysqli_num_rows($query) > 0) {
                                            $cnt = 1;
                                            while($result = mysqli_fetch_assoc($query)) {
                                                echo '<tr>
                                                        <td>' . htmlentities($cnt) . '</td>
                                                        <td>' . htmlentities($result['dept_name']) . '</td>
                                                        <td>
                                                            <div class="table-actions">
                                                                <a href="edit_department.php?edit=' . htmlentities($result['dept_id']) . '" data-color="#265ed7"><i class="icon-copy dw dw-edit2"></i></a>
                                                                <a href="department.php?delete=' . htmlentities($result['dept_id']) . '" data-color="#e95959"><i class="icon-copy dw dw-delete-3"></i></a>
                                                            </div>
                                                        </td>
                                                    </tr>';
                                                $cnt++;
                                            }
                                        }
                                        ?>  
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <?php include('includes/scripts.php')?>
</body>
</html>