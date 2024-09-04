<?php 
include('includes/header.php');
include('../includes/session.php'); // Check this file for session management

if (!isset($_SESSION['alogin']) || trim($_SESSION['alogin']) == '') {
    header("Location: ../login.php");
    exit();
}

$session_id = $_SESSION['alogin']; // Use the session variable storing the employee's ID

// Fetch employee details
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

$name = $user['name'];
$position_name = $user['position_name'];
$dept_id = $user['dept_id'];
$phone_no = $user['phone_no'] ?? 'Not Available';
$joinDate = new DateTime($user['join_date']);
$today = new DateTime();

// Handle form submission for updating leave request status
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['leave_request_id']) && isset($_POST['status_hr'])) {
        $leave_request_id = $_POST['leave_request_id'];
        $status_hr = (int)$_POST['status_hr'];

        if (!empty($leave_request_id) && !empty($status_hr)) {
            $sql = "UPDATE leave_request SET status_hr = ? WHERE leave_request_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $status_hr, $leave_request_id);
            if ($stmt->execute()) {
                echo "<script>alert('Leave request updated successfully.');</script>";
            } else {
                echo "<script>alert('Error updating leave request: " . $stmt->error . "');</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Invalid input.');</script>";
        }
    } else {
        echo "<script>alert('Form data not set.');</script>";
    }
}

// Filter based on the selected status
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$filter_condition = "";

if ($filter == 'approved') {
    $filter_condition = "AND lr.status_hr = 1";
} elseif ($filter == 'pending') {
    $filter_condition = "AND lr.status_hr = 0";
} elseif ($filter == 'rejected') {
    $filter_condition = "AND lr.status_hr = 2";
}

// Pagination setup
$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch leave requests from the database with pagination
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
    $filter_condition
    AND lr.posting_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
ORDER BY 
    lr.posting_date DESC, lr.leave_request_id DESC
LIMIT $limit OFFSET $offset";  // Append LIMIT and OFFSET directly

$result = $conn->query($sql);

// Fetch total number of records for pagination
$sql_count = "SELECT COUNT(*) AS total FROM leave_request lr JOIN employee e ON lr.employee_id = e.employee_id WHERE e.role IN ('Staff', 'HR') $filter_condition AND lr.posting_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
$result_count = $conn->query($sql_count);
$total_records = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,100,300,700' rel='stylesheet'>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="framework/table-02/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include('includes/navbar.php')?>
    <?php include('includes/right_sidebar.php')?>
    <?php include('includes/left_sidebar.php')?>
</head>
<body>

<div class="main-container">
    <div class="pd-20">
        <div class="card-box pd-20 height-100-p mb-30">
            <div class="row align-items-center">
                <div class="col-md-4 user-icon">
                    <img src="../vendors/images/banner-img.png" alt="">
                </div>
                <div class="col-md-8">
                    <h4 class="font-20 weight-500 mb-10 text-capitalize">
                        Welcome back <div class="weight-600 font-30 text-blue"><?php echo htmlspecialchars($user['name']); ?>,</div>
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card-box mb-30">
        <div class="row justify-content-between align-items-center">
            <div class="col-md-12">
                <div class="pd-20 d-flex align-items-center">
                    <h2 class="text-blue h4 mb-0 d-inline-block">LATEST LEAVE APPLICATIONS</h2>
                    <div class="ml-auto text-right d-inline-block">
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" id="filterDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Filter by Status
                            </button>
                            <div class="dropdown-menu" aria-labelledby="filterDropdown">
                                <a class="dropdown-item" href="?filter=all">All</a>
                                <a class="dropdown-item" href="?filter=approved">Approved</a>
                                <a class="dropdown-item" href="?filter=pending">Pending</a>
                                <a class="dropdown-item" href="?filter=rejected">Rejected</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="pb-20">
            <table class="data-table table stripe hover nowrap">
                <thead class="thead-light">
                    <tr>
                        <th class="table-plus">Staff</th>
                        <th>Leave Type</th>
                        <th>Start Date - End Date</th>
                        <th>Date Created</th>
                        <th>HR Status</th>
                        <th class="datatable-nosort">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr class="alert" role="alert">
                        <td><?php echo htmlspecialchars($row['employee_id']);?><br><?php echo htmlspecialchars ($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['leave_request_id']); ?><br><?php echo htmlspecialchars($row['leave_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['start_date']);?> - <br><?php echo htmlspecialchars ($row['end_date']); ?></td>
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
    </div>
    <?php include('includes/footer.php');?>
</div>

<?php include('includes/scripts.php');?>

</body>
</html>
