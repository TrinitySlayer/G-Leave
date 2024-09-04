<?php include('includes/header.php'); ?>
<?php include('../includes/session.php'); ?>

<?php
// Check if user is logged in
if (!isset($_SESSION['alogin']) || trim($_SESSION['alogin']) == '') {
    header("Location: ../login.php");
    exit();
}

$session_id = $_SESSION['alogin'];

// Validate 'edit' parameter
if (!isset($_GET['edit']) || !preg_match('/^[\w-]{1,10}$/', $_GET['edit'])) {
    echo "<script>alert('Invalid Leave Request ID.'); window.location.href = 'HR_approval_leave.php';</script>";
    exit();
}

$leave_request_id = $_GET['edit'];

// Check if the leave request exists
$sql = "SELECT leave_request_id, employee_id FROM leave_request WHERE leave_request_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $leave_request_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows == 0) {
    echo "<script>alert('Leave request not found.'); window.location.href = 'HR_approval_leave.php';</script>";
    exit();
}
$stmt->bind_result($request_id, $employee_id);
$stmt->fetch();
$stmt->close();


// Function to update leave request
function update_leave_request($conn, $leave_request_id, $start_date, $end_date, $reason, $status_hr, $type_name) {
    $check_type_sql = "SELECT leave_type_id FROM leave_type WHERE type_name = ?";
    $stmt_check = $conn->prepare($check_type_sql);
    $stmt_check->bind_param('s', $type_name);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $leave_type = $result_check->fetch_assoc();
    
    if (!$leave_type) {
        error_log("Leave type '{$type_name}' not found.");
        return false; // Leave type is invalid
    }

    $leave_type_id = $leave_type['leave_type_id'];
    
    $update_sql = "
        UPDATE leave_request 
        SET
            start_date = ?, 
            end_date = ?, 
            reason = ?,
            status_hr = ?, 
            leave_type_id = ?
        WHERE leave_request_id = ?
    ";

    $stmt = $conn->prepare($update_sql);
    if (!$stmt) {
        error_log("Prepare Error: " . $conn->error);
        return false;
    }

    $stmt->bind_param('sssiii', $start_date, $end_date, $reason, $status_hr, $leave_type_id, $leave_request_id);
    if ($stmt->execute()) {
        return true;
    } else {
        error_log("SQL Error: " . $stmt->error);
        return false;
    }
}

// Handle the Update form submission
if (isset($_POST['update_leave'])) {
    if ($_POST['status_hr'] == 0) { // Only allow update if status is pending
        $leave_request_id = $_POST['leave_request_id'];
        $type_name = mysqli_real_escape_string($conn, $_POST['leave_type']);
        $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
        $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
        $reason = mysqli_real_escape_string($conn, $_POST['reason']);
        $status_hr = intval($_POST['update_status']); // Ensure status_hr is an integer

        // Handle file upload
        $document = null;
        if (isset($_FILES['medical_document']) && $_FILES['medical_document']['error'] == 0) {
            $upload_dir = '../uploads/';
            $document = basename($_FILES['medical_document']['name']);
            $upload_file = $upload_dir . $document;

            // Check file type and size if needed
            if (move_uploaded_file($_FILES['medical_document']['tmp_name'], $upload_file)) {
                // File uploaded successfully
            } else {
                echo "<script>alert('Failed to upload document. Please try again.'); window.location.href = 'view_leave.php?edit=$leave_request_id';</script>";
                exit();
            }
        }

        // Calculate applied leave days
        $start_date_obj = new DateTime($start_date);
        $end_date_obj = new DateTime($end_date);
        $interval = $start_date_obj->diff($end_date_obj);
        $applied_leave_days = $interval->days + 1; // +1 to include the start date


        // Update SQL query
        $update_sql = "
        UPDATE leave_request 
        SET
            start_date = ?, 
            end_date = ?, 
            reason = ?, 
            applied_leave = ?,  
            leave_type_id = (SELECT leave_type_id FROM leave_type WHERE type_name = ?),
            medical_document = COALESCE(?, medical_document),
            status_hr = ?
        WHERE leave_request_id = ? 
        AND employee_id = ?
    ";
    
    $stmt = mysqli_prepare($conn, $update_sql);
    
    // Use 's' for string types and 'i' for integer types
    mysqli_stmt_bind_param($stmt, 'sssissssi', $start_date, $end_date, $reason, $applied_leave_days, $type_name, $document, $status_hr, $leave_request_id, $employee_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Leave request updated successfully.'); window.location.href = 'view_leave.php?edit=$leave_request_id';</script>";
    } else {
        echo "<script>alert('Failed to update leave request. Please try again.'); window.location.href = 'view_leave.php?edit=$leave_request_id';</script>";
    }
    mysqli_stmt_close($stmt);
    
    } else {
        echo "<script>alert('Only pending leave requests can be updated.'); window.location.href = 'view_leave.php?edit=$leave_request_id';</script>";
    }
}


// Handle the Delete request
if (isset($_POST['delete_leave']) && isset($_POST['leave_request_id'])) {
    if ($_POST['status_hr'] == 0) { // Only allow delete if status is pending
        $leave_request_id = $_POST['leave_request_id'];

        // SQL query to delete the leave request
        $delete_sql = "DELETE FROM leave_request WHERE leave_request_id = ? AND employee_id = ?";
        $stmt = mysqli_prepare($conn, $delete_sql);
        mysqli_stmt_bind_param($stmt, 'ss', $leave_request_id, $employee_id);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Leave request deleted successfully.'); window.location.href = 'HR_approval_leave.php';</script>";
        } else {
            echo "<script>alert('Failed to delete leave request. Please try again.'); window.location.href = 'HR_approval_leave.php';</script>";
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "<script>alert('Only pending leave requests can be deleted.'); window.location.href = 'view_leave.php?edit=$leave_request_id';</script>";
    }
}

// Fetch employee and leave request data
$sql = "SELECT employee_id, name, gender FROM employee WHERE employee_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 's', $employee_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if ($user) {
    $name = $user['name'];
    $gender = $user['gender'] ?? 'Not Available';
} else {
    echo "<script>alert('User not found.'); window.location.href = '../login.php';</script>";
    exit();
}
mysqli_stmt_close($stmt);
// Initialize the variable to avoid undefined variable warning
$status_hr = '';

// Fetch leave request data
$sql_requests = "
    SELECT 
        lr.leave_request_id,
        lt.type_name, 
        lr.start_date, 
        lr.end_date,
        lr.reason,
        lr.status_hr,
        lr.medical_document
    FROM 
        leave_request lr
    JOIN 
        leave_type lt 
    ON 
        lr.leave_type_id = lt.leave_type_id
    WHERE 
        lr.leave_request_id = ?
    AND
        lr.employee_id = ?
";

$stmt = mysqli_prepare($conn, $sql_requests);
mysqli_stmt_bind_param($stmt, 'ss', $leave_request_id, $employee_id);
mysqli_stmt_execute($stmt);
$result_requests = mysqli_stmt_get_result($stmt);

if ($leave_request = mysqli_fetch_assoc($result_requests)) {
    $type_name = $leave_request['type_name'] ?? 'Not Available';
    $start_date = $leave_request['start_date'] ?? 'Not Available';
    $end_date = $leave_request['end_date'] ?? 'Not Available';
    $reason = $leave_request['reason'] ?? 'Not Available';
    $status_hr = $leave_request['status_hr'] ?? 0; // Use 0 as default if not set
    $status_text = $status_hr == 1 ? 'Approved' : ($status_hr == 2 ? 'Rejected' : 'Pending');
} else {
    echo "<script>alert('Leave request not found.'); window.location.href = 'view_leave.php';</script>";
    exit();
}
mysqli_stmt_close($stmt);

// Determine if the request is approved or rejected
$is_approved_or_rejected = $status_hr != 0;

?>

<!-- HTML and JavaScript for the Leave Details Form -->

<style>
    input[type="text"], input[type="date"], textarea {
        font-size: 16px;
        color: #0f0d1b;
        font-family: Verdana, Helvetica;
    }

    .btn-outline:hover {
        color: #fff;
        background-color: #524d7d;
        border-color: #524d7d;
    }

    textarea.text_area {
        height: 8em;
        font-size: 16px;
        color: #0f0d1b;
        font-family: Verdana, Helvetica;
    }
</style>

<body>
    <?php include('includes/navbar.php'); ?>
    <?php include('includes/right_sidebar.php'); ?>
    <?php include('includes/left_sidebar.php'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

    <div class="mobile-menu-overlay"></div>

    <div class="main-container">
        <div class="pd-ltr-20">
            <div class="min-height-200px">
                <div class="page-header">
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="title">
                                <h4>LEAVE DETAILS</h4>
                            </div>
                            <nav aria-label="breadcrumb" role="navigation">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="HR_approval_leave.php">Home</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Leave</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="col-md-6 col-sm-12 text-right">
                            <div class="dropdown show">
                                <a class="btn btn-primary" href="report_pdf.php?edit=<?php echo urlencode($leave_request_id); ?>">
                                    Generate Report
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pd-20 card-box mb-30">
                    <div class="clearfix">
                        <div class="pull-left">
                            <h4 class="text-blue h4">Leave Details</h4>
                            <p class="mb-20"></p>
                        </div>
                    </div>
                    <form method="post" action="" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label style="font-size:16px;"><b>Full Name</b></label>
                                    <input name="name" type="text" class="form-control" readonly value="<?php echo htmlspecialchars($name); ?>">
                                </div>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label style="font-size:16px;"><b>Staff ID</b></label>
                                    <input name="id" type="text" class="form-control wizard-required" required="true" readonly value="<?php echo htmlspecialchars($employee_id); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label style="font-size:16px;"><b>Leave Type</b></label>
                                    <input type="text" id="type_name_text" class="form-control" name="type_name_text" readonly value="<?php echo htmlspecialchars($type_name); ?>">
                                    <select id="leave_type_select" name="leave_type" class="custom-select form-control" style="display:none;">
                                        <option value="Annual Leave" <?php if($type_name == 'Annual Leave') echo 'selected'; ?>>Annual Leave</option>
                                        <option value="Medical Leave" <?php if($type_name == 'Medical Leave') echo 'selected'; ?>>Medical Leave</option>
                                        <option value="Unpaid Leave" <?php if($type_name == 'Unpaid Leave') echo 'selected'; ?>>Unpaid Leave</option>
                                        <option value="Paternity Leave" <?php if($type_name == 'Paternity Leave') echo 'selected'; ?>>Paternity Leave</option>
                                        <option value="Maternity Leave" <?php if($type_name == 'Maternity Leave') echo 'selected'; ?>>Maternity Leave</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label style="font-size:16px;"><b>Leave Status</b></label>
                                    <input name="status_hr" type="text" class="form-control" style="color: <?php echo $status_color; ?>" readonly id="status_text" value="<?php echo htmlspecialchars($status_text); ?>">
                                    <select name="update_status" id="status_select" class="custom-select form-control" style="display:none;" <?php echo $is_approved_or_rejected ? 'disabled' : ''; ?>>
                                    <option value="1" style="color: green" <?php echo ($status_hr == 1) ? 'selected' : ''; ?>>Approved</option>
                                    <option value="2" style="color: red" <?php echo ($status_hr == 2) ? 'selected' : ''; ?>>Rejected</option>
                                    </select>
                                </div>
                            </div>
                        </div>    
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label style="font-size:16px;"><b>Start Date</b></label>
                                    <input name="start_date" type="text" class="form-control date-picker" readonly value="<?php echo htmlspecialchars($start_date); ?>">
                                    </div>
                            </div>

                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label style="font-size:16px;"><b>End Date</b></label>
                                    <input name="end_date" type="text" class="form-control date-picker" readonly value="<?php echo htmlspecialchars($end_date); ?>">
                                    </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label style="font-size:16px;"><b>Reason</b></label>
                                    <textarea name="reason" class="text_area form-control" readonly><?php echo htmlspecialchars($reason); ?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label style="font-size:16px;"><b>Upload Document</b></label>
                                    <input type="file" name="medical_document" class="form-control" disabled <?php echo $is_approved_or_rejected ? 'disabled' : ''; ?>>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label style="font-size:16px;"><b>Uploaded Document</b></label>
                                    <?php if (isset($leave_request['medical_document']) && $leave_request['medical_document']): ?>
                                        <p><a href="javascript:void(0);" data-toggle="modal" data-target="#documentModal">View Your Uploded Document</a></p>
                                    <?php else: ?>
                                        <p>No attachment document</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>


                        <!-- Hidden input to store leave request ID -->
                        <input type="hidden" name="leave_request_id" value="<?php echo htmlspecialchars($leave_request_id); ?>">
                        <input type="hidden" name="status_hr" value="<?php echo htmlspecialchars($leave_request['status_hr']); ?>">

                        <div class="form-group row">
                            <div class="col-md-12 col-sm-12 text-right">
                                <!-- Form for Edit/Update -->
                                <form method="post" action="" class="d-inline-block">
                                    <button class="btn btn-outline-primary" id="edit_button" name="edit_leave" type="button" onclick="toggleEdit()" <?php echo $is_approved_or_rejected ? 'disabled' : ''; ?>>Edit</button>
                                    <button type="submit" name="update_leave" id="save_button" style="display:none;" class="btn btn-outline-primary">Save</button>
                                    <button type="button" id="cancel_button" style="display:none;" class="btn btn-outline-primary">Cancel</button>
                                </form>

                                <!-- Form for Delete -->
                                <form method="post" action="" class="d-inline-block">
                                    <input type="hidden" name="leave_request_id" value="<?php echo htmlspecialchars($leave_request_id); ?>">
                                    <button type="submit" name="delete_leave" onclick="return confirm('Are you sure you want to delete this leave request?');" class="btn btn-outline-danger" <?php echo $is_approved_or_rejected ? 'disabled' : ''; ?>>Delete</button>
                                </form>
                            </div>
                        </div>                        
                    </form>
                </div>
            </div>

            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="documentModal" tabindex="-1" role="dialog" aria-labelledby="documentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="documentModalLabel">Uploaded Document</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php if ($leave_request['medical_document']): ?>
                        <?php
                        $file_extension = strtolower(pathinfo($leave_request['medical_document'], PATHINFO_EXTENSION));
                        if ($file_extension == 'pdf') {
                            echo '<iframe src="../uploads/' . htmlspecialchars($leave_request['medical_document']) . '" width="100%" height="500px"></iframe>';
                        } elseif (in_array($file_extension, ['jpg', 'jpeg', 'png'])) {
                            echo '<img src="../uploads/' . htmlspecialchars($leave_request['medical_document']) . '" alt="Document Image" style="width:100%; height:auto;">';
                        } else {
                            echo '<p>Unsupported document format.</p>';
                        }
                        ?>
                    <?php else: ?>
                        <p>No document uploaded.</p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <script>
    // Function to toggle only the Leave Status field
    function toggleEdit() {
        // Toggle Leave Status field
        document.getElementById("status_text").style.display = "none";
        document.getElementById("status_select").style.display = "block";

        // Hide the Edit button and show Save and Cancel buttons
        document.getElementById("edit_button").style.display = "none";
        document.getElementById("save_button").style.display = "inline-block";
        document.getElementById("cancel_button").style.display = "inline-block";
    }

    // Event listener for the Cancel button to reload the page
    document.getElementById("cancel_button").addEventListener("click", function () {
        window.location.reload();
    });

    // Optionally, you can attach the toggleEdit function directly to the Edit button
    // if you prefer not to use the onclick attribute in the HTML
    /*
    document.getElementById("edit_button").addEventListener("click", toggleEdit);
    */
</script>


    <?php include('includes/scripts.php') ?>
</body>
</html>
