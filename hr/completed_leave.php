<?php
include('includes/header.php');
include('../includes/session.php'); 

if (!isset($_SESSION['alogin']) || trim($_SESSION['alogin']) == '') {
    header("Location: ../login.php");
    exit();
}

$session_id = $_SESSION['alogin'];

// Fetch the current user's details
$sql = "SELECT * FROM employee WHERE employee_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $session_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "<script>alert('User not found.'); window.location.href = '../login.php';</script>";
    exit();
}

$dept_id = $user['dept_id'];

// Handle the search query
$searchTerm = '';
if (isset($_GET['search'])) {
    $searchTerm = trim($_GET['search']);
}

// Filter based on the selected status
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$filter_condition = "";
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

 if ($filter == 'dates' && $start_date && $end_date) {
    $filter_condition = "AND lr.start_date >= ? AND lr.end_date <= ?";
}

// Prepare the SQL query to fetch only approved leave requests with search filtering
// Determine the SQL query and the parameters based on the filter condition
if ($filter == 'dates' && $start_date && $end_date) {
    $sql = "
    SELECT 
        lr.leave_request_id,
        e.employee_id,
        e.name,
        lt.type_name as leave_type,
        lr.start_date,
        lr.end_date,
        lr.status_hr 
    FROM 
        leave_request lr 
    JOIN 
        employee e ON lr.employee_id = e.employee_id
    JOIN 
        leave_type lt ON lr.leave_type_id = lt.leave_type_id
    WHERE 
        e.role = 'Staff'
        AND lr.start_date >= ?
        AND lr.end_date <= ?
        AND lr.status_hr = 1
        AND (
            e.name LIKE ? 
            OR lt.type_name LIKE ? 
            OR CASE WHEN lr.status_hr = 1 THEN 'Approved' END LIKE ?
        )
    ORDER BY 
        lr.leave_request_id DESC
    LIMIT 5";

    $searchParam = '%' . $searchTerm . '%';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssss', $start_date, $end_date, $searchParam, $searchParam, $searchParam);
} else {
    $sql = "
    SELECT 
        lr.leave_request_id,
        e.employee_id,
        e.name,
        lt.type_name as leave_type,
        lr.start_date,
        lr.end_date,
        lr.posting_date,
        lr.status_hr 
    FROM 
        leave_request lr 
    JOIN 
        employee e ON lr.employee_id = e.employee_id
    JOIN 
        leave_type lt ON lr.leave_type_id = lt.leave_type_id
    WHERE 
        e.role IN ('Staff', 'HR')
        AND lr.status_hr = 1
        AND (
            e.name LIKE ? 
            OR lt.type_name LIKE ? 
            OR CASE WHEN lr.status_hr = 1 THEN 'Approved' END LIKE ?
        )
    ORDER BY 
        lr.leave_request_id DESC";

    $searchParam = '%' . $searchTerm . '%';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $searchParam, $searchParam, $searchParam);
}

$stmt->execute();
$result = $stmt->get_result();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Head contents here -->
</head>
<body>
    <?php include('includes/navbar.php')?>
    <?php include('includes/right_sidebar.php')?>
    <?php include('includes/left_sidebar.php')?>

    <div class="main-container">
        <div class="pd-ltr-20">
            <div class="page-header">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="title">
                            <h4>Leave Portal</h4>
                        </div>
                        <nav aria-label="breadcrumb" role="navigation">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">All Leave</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            <div class="card-box mb-30">
                <div class="row justify-content-between align-items-center">
                    <div class="col-md-12">
                        <div class="pd-20 d-flex align-items-center">
                            <h2 class="text-blue h4 mb-0 d-inline-block">COMPLETED LEAVE APPLICATIONS</h2>
                            <div class="ml-auto text-right d-inline-block">
                                <form method="GET" action="completed_leave.php" class="d-inline-block">
                                    <input type="text" class="search-input" placeholder="Search..." name="search" value="<?php echo htmlspecialchars($searchTerm); ?>">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                                </form>
                                <a class="btn btn-outline-primary " type="button" id="filterDropdown" href="#" data-toggle="modal" data-target="#dateFilterModal">
                                <i class="icon-copy fa fa-filter" aria-hidden="true"></i></a>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="pb-20">
                    <table class="data-table table stripe hover nowrap">
                        <thead>
                            <tr>
                                <th class="table-plus">STAFF</th>
                                <th>LEAVE TYPE</th>
                                <th>APPLIED DATE</th>
                                <th>DATE CREATED</th>
                                <th>MY REMARKS</th>
                                <th class="datatable-nosort">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)) { 
                            $hr_status = $row['status_hr'] == 1 ? 'Approved' : ($row['status_hr'] == 2 ? 'Rejected' : 'Pending');
                        ?>
                            <tr class="alert" role="alert">
                                <td><?php echo htmlspecialchars($row['employee_id']);?> <br>
                                <?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['leave_request_id']); ?><br>
                                <?php echo htmlspecialchars($row['leave_type']); ?></td>
                                <td><?php echo htmlspecialchars($row['start_date'] . " - "); ?> <br>
                                <?php echo htmlspecialchars($row['end_date']); ?></td>
                                <td><?php echo htmlspecialchars($row['posting_date']); ?></td>
                                <td>
                                    <?php 
                                        $status = $row['status_hr']; 
                                        if ($status == 1) {
                                            echo "<span style='color: green'>Approved</span>";
                                        } elseif ($status == 2) { 
                                            echo "<span style='color: red'>Rejected</span>";
                                        } else {
                                            echo "<span style='color: blue'>Pending</span>";
                                        } 
                                    ?>
                                </td>
                                <td>
                                    <a title='VIEW' href='view_leave.php?edit=<?php echo urlencode(htmlspecialchars($row['leave_request_id'])); ?>' data-color='#265ed7'><i class='icon-copy dw dw-eye'></i></a>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- Date Filter Modal -->
                <div class="modal fade" id="dateFilterModal" tabindex="-1" role="dialog" aria-labelledby="dateFilterModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="dateFilterModalLabel">Filter by Dates</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form method="GET" action="">
                                    <div class="form-group">
                                        <label for="start_date">Start Date</label>
                                        <input type="text" class="form-control date-picker" id="start_date" name="start_date" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="end_date">End Date</label>
                                        <input type="text" class="form-control date-picker" id="end_date" name="end_date" required>
                                    </div>
                                    <input type="hidden" name="filter" value="dates">
                                    <button type="submit" class="btn btn-primary">Apply</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <!-- JS Scripts -->
    <?php include('includes/scripts.php')?>
</body>
</html>
