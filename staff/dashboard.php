<?php include('../includes/session.php'); ?>
<?php
// Start session and check if the user is logged in
if (!isset($_SESSION['alogin']) || trim($_SESSION['alogin']) == '') {
    header("Location: ../login.php");
    exit();
}
// Assuming $session_id is the employee's ID stored in the session
$session_id = $_SESSION['alogin']; // Use the session variable storing the employee's ID

include('includes/header.php');

// Fetch the user's gender
$sql_gender = $conn->prepare("SELECT gender FROM employee WHERE employee_id = ?");
$sql_gender->bind_param("s", $session_id);
$sql_gender->execute();
$result_gender = $sql_gender->get_result();

if ($result_gender->num_rows > 0) {
    $user_gender = $result_gender->fetch_assoc()['gender'];
} else {
    echo "Error: Unable to fetch gender.";
    exit();
}

// Query to get leave type and balances for the cards, considering only approved leaves
$sql_balance = $conn->prepare("
SELECT 
    lt.type_name, 
    lt.total_leave, 
    COALESCE(SUM(CASE WHEN lr.status_hr = 1 THEN lr.applied_leave ELSE 0 END), 0) AS leave_taken, 
    (lt.total_leave - COALESCE(SUM(CASE WHEN lr.status_hr = 1 THEN lr.applied_leave ELSE 0 END), 0)) AS leave_balance
FROM 
    leave_type lt
LEFT JOIN 
    leave_request lr 
ON 
    lt.leave_type_id = lr.leave_type_id
AND 
    lr.employee_id = ?
GROUP BY 
    lt.type_name, lt.total_leave, lt.leave_type_id");
$sql_balance->bind_param("s", $session_id);
$sql_balance->execute();
$result_balance = $sql_balance->get_result();

// Query to get leave requests for the table
$sql_requests = $conn->prepare("
SELECT 
    lr.leave_request_id,
    lt.type_name, 
    lr.start_date, 
    lr.end_date,
    lr.applied_leave, 
    lr.posting_date, 
    lr.status_hr
FROM 
    leave_request lr
JOIN 
    leave_type lt 
ON 
    lr.leave_type_id = lt.leave_type_id
WHERE 
    lr.employee_id = ?
ORDER BY 
    lr.leave_request_id DESC
LIMIT 5");
$sql_requests->bind_param("s", $session_id);
$sql_requests->execute();
$result_requests = $sql_requests->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Balance Summary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            padding: 20px;
            background-color: #f4f4f4;
            margin-top: 5px;
            margin-bottom: 5px;
            margin-right: 30%;
        }
        .card-container {
            margin-top: 20px;
        }
        .card {
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 15px;
            text-align: center;
        }
        .card p {
            font-size: 16px;
            color: #555;
            margin: 10px 0;
        }
        .card .balance {
            font-size: 20px;
            font-weight: bold;
            color: #007bff;
        }
        .table th, .table td {
            text-align: center;
        }
    </style>
</head>
<body>
<?php include('includes/navbar.php') ?>
<?php include('includes/right_sidebar.php') ?>
<?php include('includes/left_sidebar.php') ?>
<div class="mobile-menu-overlay"></div>

<div class="main-container">
    <div class="pd-ltr-20">
        <div class="card-box pd-20 height-100-p mb-30">
            <div class="row align-items-center">
                <div class="col-md-4 user-icon">
                    <img src="../vendors/images/banner-img.png" alt="">
                </div>
                <div class="col-md-8">
                    <?php 
                    $query= mysqli_query($conn,"SELECT * FROM employee WHERE employee_id = '$session_id'")or die(mysqli_error($conn));
                    $row = mysqli_fetch_array($query);
                    ?>
                    <h4 class="font-20 weight-500 mb-10 text-capitalize">
                        Welcome back, <div class="weight-600 font-30 text-blue"><?php echo htmlspecialchars($row['name']); ?>,</div>
                    </h4>
                    <p class="font-18 max-width-600"></p>
                </div>
            </div>
        </div>

        <div class="container">
            <h2 class="my-4">Leave Balance Summary</h2>

            <div class="row card-container">
                <?php
                if ($result_balance->num_rows > 0) {
                    while($row = $result_balance->fetch_assoc()) {
                        if (($user_gender == 'Male' && $row["type_name"] == 'Maternity Leave') ||
                            ($user_gender == 'Female' && $row["type_name"] == 'Paternity Leave')) {
                            continue;
                        }

                        if (in_array($row["type_name"], ['Unpaid Leave', 'Other'])) {
                            echo "<div class='col-md-3'>
                                    <div class='card'>
                                        <h5>" . htmlspecialchars($row["type_name"]) . "</h5>
                                        <p class='balance'>" . htmlspecialchars($row["leave_taken"]) . "</p>
                                    </div>
                                  </div>";
                        } else {
                            echo "<div class='col-md-3'>
                                    <div class='card'>
                                        <h5>" . htmlspecialchars($row["type_name"]) . "</h5>
                                        <p class='balance'>" . htmlspecialchars($row["leave_balance"]) . " / " . htmlspecialchars($row["total_leave"]) . "</p>
                                    </div>
                                  </div>";
                        }
                    }
                } else {
                    echo "<p>No data available</p>";
                }
                ?>
            </div>

            <div class="card-box mb-30">
                <div class="pd-20">
                    <h2 class="text-blue h4">LEAVE HISTORY</h2>
                </div>
                <div class="pb-20">
                    <table class="data-table table stripe hover nowrap">
                        <thead class="thead-light">
                            <tr>
                                <th class="table-plus">LEAVE TYPE</th>
                                <th>DATE FROM</th>
                                <th>NO. OF DAYS</th>
                                <th>Date Created</th>
                                <th>Current Status</th>
                                <th class="datatable-nosort">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($result_requests->num_rows > 0) {
                                while($row = $result_requests->fetch_assoc()) {
                                    $hr_status = $row['status_hr'] == 1 ? 'Approved' : ($row['status_hr'] == 2 ? 'Rejected' : 'Pending');
                                    echo "<tr>
                                            <td>" . htmlspecialchars($row['leave_request_id']) . "<br>" . htmlspecialchars($row['type_name']) . "</td>
                                            <td>" . htmlspecialchars($row['start_date']) . " -" . "<br>" . htmlspecialchars($row['end_date']) . "</td>
                                            <td>" . htmlspecialchars($row['applied_leave']) . "</td>
                                            <td>" . htmlspecialchars($row['posting_date']) . "</td>
                                            <td><span style='color: " . ($row['status_hr'] == 1 ? 'green' : ($row['status_hr'] == 2 ? 'red' : 'orange')) . "'>$hr_status</span></td>
                                            <td>
                                                <a title='VIEW' href='view_leaves.php?edit=" . htmlentities($row['leave_request_id']) . "' data-color='#265ed7'><i class='icon-copy dw dw-eye'></i></a>
                                            </td>
                                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6'>No leave requests found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>
</div>

<?php include('includes/scripts.php');?>
</body>
</html>
