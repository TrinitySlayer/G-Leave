<?php
include('includes/header.php');
include('../includes/session.php');


// Check if the employee is logged in and retrieve the employee ID
if (!isset($_SESSION['alogin']) || trim($_SESSION['alogin']) == '') {
    header("Location: ../login.php");
    exit();
}
$session_id = $_SESSION['alogin'];

// Handle delete request if applicable
if (isset($_GET['delete'])) {
    $delete = $_GET['delete'];
    $delete = mysqli_real_escape_string($conn, $delete);
    $sql = "DELETE FROM employee WHERE employee_id = '$delete'";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        echo "<script>alert('Staff deleted Successfully');</script>";
        echo "<script type='text/javascript'> document.location = 'staff.php'; </script>";
    }
}

// Query to get leave type and balances for the cards
$sql_balance = "
SELECT 
    lt.type_name, 
    COALESCE(SUM(lr.applied_leave), 0) AS leave_taken, 
    (lt.total_leave - COALESCE(SUM(lr.applied_leave), 0)) AS leave_balance
FROM 
    leave_type lt
LEFT JOIN 
    leave_request lr 
ON 
    lt.leave_type_id = lr.leave_type_id
AND 
    lr.employee_id = '$session_id'
GROUP BY 
    lt.leave_type_id";

$result_balance = $conn->query($sql_balance);

// Query to get leave requests for the table
$sql_requests = "
SELECT 
    lr.leave_request_id,
    lt.type_name, 
    lr.start_date, 
    lr.end_date,
    lr.applied_leave, 
    lr.status_hr
FROM 
    leave_request lr
JOIN 
    leave_type lt 
ON 
    lr.leave_type_id = lt.leave_type_id
WHERE 
    lr.employee_id = '". $_SESSION['alogin']. "'";

$result_requests = $conn->query($sql_requests);

// Query for "All Applied Leave" by the current user
// Query to count the number of leave requests made by the current user
$sql_leave_request_count = "
    SELECT COUNT(leave_request_id) AS leave_request_count
    FROM leave_request 
    WHERE employee_id = '$session_id'";

$query_leave_request_count = mysqli_query($conn, $sql_leave_request_count);
$result_leave_request_count = mysqli_fetch_assoc($query_leave_request_count);
$leave_request_count = $result_leave_request_count['leave_request_count'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Balance Summary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include('includes/navbar.php')?>
    <?php include('includes/right_sidebar.php')?>
    <?php include('includes/left_sidebar.php')?>
    <div class="mobile-menu-overlay"></div>
    <div class="main-container">
        <div class="pd-ltr-20">
            <div class="title pb-20">
                <h2 class="h3 mb-0">Leave Breakdown</h2>
            </div>
            <div class="row pb-10">
                <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
                    <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark"><?php echo htmlentities($leave_request_count); ?></div>
                                <div class="font-14 text-secondary weight-500">Total Applied Requests</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon" data-color="#00eccf"><i class="icon-copy dw dw-file"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <!-- Repeat similar blocks for other leave statistics -->
                <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
                    <div class="card-box height-100-p widget-style3">
                        <?php 
                        $status = 1;
                        $query = mysqli_query($conn, "SELECT * FROM leave_request WHERE employee_id = '$session_id' AND status_hr = '$status'");
                        $count_reg_staff = mysqli_num_rows($query);
                        ?>
                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark"><?php echo htmlentities($count_reg_staff); ?></div>
                                <div class="font-14 text-secondary weight-500">Approved</div>
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
                        $status = 0;
                        $query_pend = mysqli_query($conn, "SELECT * FROM leave_request WHERE employee_id = '$session_id' AND status_hr = '$status'");
                        $count_pending = mysqli_num_rows($query_pend);
                        ?>
                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark"><?php echo htmlentities($count_pending); ?></div>
                                <div class="font-14 text-secondary weight-500">Pending</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon"><i class="icon-copy fa fa-hourglass-end" aria-hidden="true"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
                    <div class="card-box height-100-p widget-style3">
                        <?php 
                        $status = 2;
                        $query_reject = mysqli_query($conn, "SELECT * FROM leave_request WHERE employee_id = '$session_id' AND status_hr = '$status'");
                        $count_reject = mysqli_num_rows($query_reject);
                        ?>
                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark"><?php echo htmlentities($count_reject); ?></div>
                                <div class="font-14 text-secondary weight-500">Rejected</div>
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
                    <h2 class="text-blue h4">ALL MY LEAVE</h2>
                </div>
                <div class="pb-20">
                    <table class="data-table table stripe hover nowrap">
                        <thead class="thead-light">
                            <tr>
                                <th style="text-align: center;" class="table-plus">LEAVE TYPE</th>
                                <th style="text-align: center;">DATE FROM</th>
                                <th style="text-align: center;">DATE TO</th>
                                <th style="text-align: center;">NO. OF DAYS</th>
                                <th style="text-align: center;">CURRENT STATUS</th>
                                <th style="text-align: center;" class="datatable-nosort">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                          <?php while ($result = mysqli_fetch_assoc($result_requests)) { ?>
                                <tr>
                                    <td style="text-align: center;"><?php echo isset($result['type_name']) ? htmlentities($result['type_name']) : 'N/A'; ?></td>
                                    <td style="text-align: center;"><?php echo isset($result['start_date']) ? htmlentities($result['start_date']) : 'N/A'; ?></td>
                                    <td style="text-align: center;"><?php echo isset($result['end_date']) ? htmlentities($result['end_date']) : 'N/A'; ?></td>
                                    <td style="text-align: center;"><?php echo isset($result['applied_leave']) ? htmlentities($result['applied_leave']) : 'N/A'; ?></td>
                                    <td style="text-align: center;">
                                        <?php
                                        $status_text = '';
                                        switch ($result['status_hr']) {
                                            case 1:
                                                $status_text = '<span style="color: green">Approved</span>';
                                                break;
                                            case 2:
                                                $status_text = '<span style="color: red">Rejected</span>';
                                                break;
                                            default:
                                                $status_text = '<span style="color: orange">Pending</span>';
                                        }
                                        echo $status_text;
                                        ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <div class="table-actions">
                                            <a title="VIEW" href="view_leave.php?edit=<?php echo htmlentities($result['leave_request_id']); ?>" data-color="#265ed7"><i class="icon-copy dw dw-eye"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php include('includes/footer.php'); ?>
        </div>
    </div>
    <!-- js -->
    <?php include('includes/scripts.php')?>
</body>
</html>
