<?php 
include('includes/header.php');
include('../includes/session.php');

if (!isset($_SESSION['alogin']) || trim($_SESSION['alogin']) == '') {
    header("Location: ../login.php");
    exit();
}

$session_id = $_SESSION['alogin'];

// Fetch employee details
$sql = "SELECT name, employee_id, dept_id, position_name, join_date FROM employee WHERE employee_id = '$session_id'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

if ($user) {
    $name = $user['name'];
    $employee_id = $user['employee_id'];
    $dept_id = $user['dept_id'];
    $position_name = $user['position_name'];

    $joinDate = new DateTime($user['join_date']);
    $today = new DateTime();

    // Fetch department name
    $departmentQuery = "SELECT dept_name FROM department WHERE dept_id = '$dept_id'";
    $departmentResult = mysqli_query($conn, $departmentQuery);
    $departmentData = mysqli_fetch_assoc($departmentResult);
    $department = $departmentData['dept_name'];
} else {
    echo "<script>alert('User not found.'); window.location.href = '../login.php';</script>";
    exit();
}

if (isset($_POST['submit'])) {
  $leaveType = $_POST["leave_type"];
  $reason = $_POST["reason"];
  $start_date = $_POST["start_date"];
  $end_date = $_POST["end_date"];
  $PostingDate = date("Y-m-d");
  $targetFile = null;
  $error = "";

  // Handle document upload if necessary and not Annual Leave
  if ($leaveType != "Annual Leave" && !empty($_FILES["medicalDocument"]["name"])) {
      $targetDir = "../uploads/"; // Ensure correct directory
      $targetFile = $targetDir . basename($_FILES["medicalDocument"]["name"]);
      $uploadOk = 1;
      $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

      if (file_exists($targetFile)) {
          $error = "Sorry, file already exists.";
          $uploadOk = 0;
      }

      if ($_FILES["medicalDocument"]["size"] > 500000) {
          $error = "Sorry, your file is too large.";
          $uploadOk = 0;
      }

      if (!in_array($imageFileType, ["jpg", "jpeg", "png", "pdf"])) {
          $error = "Sorry, only JPG, JPEG, PNG & PDF files are allowed.";
          $uploadOk = 0;
      }

      if ($uploadOk == 0) {
          // Handle upload error
          $error .= " Your file was not uploaded.";
      } else {
          if (!move_uploaded_file($_FILES["medicalDocument"]["tmp_name"], $targetFile)) {
              $error = "Sorry, there was an error uploading your file.";
          }
      }
  }

  // Calculate leave days and validate dates
  $startDate = new DateTime($start_date);
  $endDate = new DateTime($end_date);

  if ($endDate < $startDate) {
      $error = "End date cannot be before start date.";
  } else {
      $interval = $startDate->diff($endDate);
      $workingDays = $interval->days + 1; // Include both start and end dates

      // Fetch years of service
      $yearsOfService = $today->diff($joinDate)->y;

      // Default leaveDays calculation based on leave type
      if ($leaveType == "Halfday Leave") {
          $leaveDays = 0.5;
      } else {
          // Calculate leave days based on leave type
          $leaveDays = $workingDays; // Default to workingDays

          if ($leaveType == "Annual Leave") {
              if ($yearsOfService < 2) {
                  $maxLeaveDays = 8;
              } elseif ($yearsOfService >= 2 && $yearsOfService <= 5) {
                  $maxLeaveDays = 12;
              } else {
                  $maxLeaveDays = 16;
              }
              $leaveDays = min($workingDays, $maxLeaveDays);
          } elseif ($leaveType == "Medical Leave") {
              if ($yearsOfService < 2) {
                  $maxLeaveDays = 14;
              } elseif ($yearsOfService >= 2 && $yearsOfService <= 5) {
                  $maxLeaveDays = 18;
              } else {
                  $maxLeaveDays = 22;
              }
              $leaveDays = min($workingDays, $maxLeaveDays);
          } elseif ($leaveType == "Unpaid Leave") {
              $applyDeadline = (clone $today)->modify('+5 days');
              if ($today > $applyDeadline) {
                  $error = "Unpaid Leave must be applied 5 days before the leave day.";
              }
          } elseif ($leaveType == "Maternity Leave") {
              $maxLeaveDays = 98; // Maternity leave is fixed at 98 days
              $leaveDays = min($workingDays, $maxLeaveDays);
          } elseif ($leaveType == "Paternity Leave") {
              $maxLeaveDays = 7; // Paternity leave is fixed at 7 days
              $leaveDays = min($workingDays, $maxLeaveDays);
          }
      }
  }

  if (empty($error)) {
      // Begin transaction
      mysqli_begin_transaction($conn);

      // Check and fetch leave_type_id and current new_balance from leave_type table
      $leaveTypeQuery = "SELECT leave_type_id, new_balance FROM leave_type WHERE type_name = '$leaveType'";
      $leaveTypeResult = mysqli_query($conn, $leaveTypeQuery);

      if ($leaveTypeResult && mysqli_num_rows($leaveTypeResult) > 0) {
          $leaveTypeData = mysqli_fetch_assoc($leaveTypeResult);
          $leaveTypeId = $leaveTypeData['leave_type_id'];
          $leaveTypeBalance = $leaveTypeData['new_balance'];

          // Adjust Annual Leave balance if Halfday Leave is selected
          if ($leaveType == "Halfday Leave") {
              $annualLeaveQuery = "SELECT leave_type_id, new_balance FROM leave_type WHERE type_name = 'Annual Leave'";
              $annualLeaveResult = mysqli_query($conn, $annualLeaveQuery);

              if ($annualLeaveResult && mysqli_num_rows($annualLeaveResult) > 0) {
                  $annualLeaveData = mysqli_fetch_assoc($annualLeaveResult);
                  $annualLeaveTypeId = $annualLeaveData['leave_type_id'];
                  $annualLeaveBalance = $annualLeaveData['new_balance'];

                  if ($annualLeaveBalance >= 0.5) {
                      $newAnnualLeaveBalance = $annualLeaveBalance - 0.5;
                      $updateBalanceQuery = "UPDATE leave_type SET new_balance = $newAnnualLeaveBalance WHERE leave_type_id = $annualLeaveTypeId";

                      if (!mysqli_query($conn, $updateBalanceQuery)) {
                          $error = "Error updating Annual Leave balance: " . mysqli_error($conn);
                          mysqli_rollback($conn);
                      }
                  } else {
                      $error = "Insufficient Annual Leave balance for a half-day leave.";
                      mysqli_rollback($conn);
                  }
              }
          }

          // Generate new leave_request_id
          $lastIDQuery = "SELECT leave_request_id FROM leave_request ORDER BY leave_request_id DESC LIMIT 1";
          $lastIDResult = mysqli_query($conn, $lastIDQuery);

          if ($lastIDResult && mysqli_num_rows($lastIDResult) > 0) {
              $lastIDRow = mysqli_fetch_assoc($lastIDResult);
              $lastID = $lastIDRow['leave_request_id'];
              $numericPart = (int)substr($lastID, 2); // Extract numeric part
              $newID = 'LR' . str_pad($numericPart + 1, 3, '0', STR_PAD_LEFT);
          } else {
              $newID = 'LR001'; // First entry case
          }

          // Insert into leave_request table
          $leaveRequestQuery = "INSERT INTO leave_request (
            leave_request_id, 
            leave_type_id, 
            employee_id, 
            reason, 
            start_date, 
            end_date, 
            applied_leave,
            posting_date,
            medical_document) VALUES ('$newID', '$leaveTypeId', '$employee_id', '$reason', '$start_date', '$end_date', $leaveDays, '$PostingDate', '$targetFile')";

          if (mysqli_query($conn, $leaveRequestQuery)) {
              // Commit transaction
              mysqli_commit($conn);
              echo "<script>alert('Leave request submitted successfully.'); window.location.href = 'HR_approval_leave.php';</script>";
          } else {
              $error = "Error inserting leave request: " . mysqli_error($conn);
              mysqli_rollback($conn);
          }
      } else {
          $error = "Invalid leave type.";
          mysqli_rollback($conn);
      }
  }

  // Display errors if any
  if (!empty($error)) {
      echo "<script>alert('$error');</script>";
  }
}

?>


<!DOCTYPE html>
<html>
<head>
<title>Leave Application Form</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php include('includes/navbar.php')?>
<?php include('includes/right_sidebar.php')?>
<?php include('includes/left_sidebar.php')?>

<style>
body {
  font-family: sans-serif;
  background-color: #f2f2f2;
}
</style>
</head>
<body>
  

<div class="container">
<div class="mobile-menu-overlay"></div>

    <div class="main-container">
        <div class="pd-ltr-20 xs-pd-20-10">
            <div class="min-height-200px">

                <div class="page-header">
                    <div class="row">
                        <div class="title">
                            <h2>Leave Application Form</h2>
                        </div>
                    </div>

                    <form method="post" enctype="multipart/form-data">
                        <section>

                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label> Name </label>
                                    <input name="name" type="text" class="form-control" readonly required value="<?php echo $name; ?>">
                                </div>
                            </div>
                        </div>  
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label>ID</label>
                                    <input name="id" type="text" class="form-control" required readonly value="<?php echo $employee_id; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label for="department">Department:</label>
                                    <input type="text" id="department" class="form-control" value="<?php echo $department; ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label for="position">Position:</label>
                                    <input type="text" id="position" class="form-control" value="<?php echo $position_name; ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label for="leave_type">Type of Leave:</label>
                                    <select id="leave_type" name="leave_type" class="custom-select form-control" onchange="toggleFileUpload(this)">
                                      <option value="Annual Leave">Annual Leave</option>
                                      <option value="Medical Leave">Medical Leave</option>
                                      <option value="Unpaid Leave">Unpaid Leave</option>
                                      <option value="Maternity Leave">Maternity Leave</option>
                                      <option value="Paternity Leave">Paternity Leave</option>
                                      <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                         
                        <div class="row">      
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label for="start_date">Start Date:</label>
                                    <input type="text" id="start_date" class="form-control date-picker" name="start_date" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label for="end_date">End Date:</label>
                                    <input type="text" id="end_date" class="form-control date-picker" name="end_date" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label>Reason:</label>
                                    <textarea id="reason" name="reason" class="form-control" rows="4" required></textarea>
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-12">
                                <div class="form-group" id="file-upload" style="display:none;">
                                    <label for="medicalDocument">Support Document (if applicable):</label>
                                    <input type="file" name="medicalDocument" id="medicalDocument" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-2 col-sm-12">
                                <div class="form-group">
                                    <div class="modal-footer justify-content-center">                                  
                                        <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                                    </div>
                                </div>
                            </div>

                        </div>
                        </section>
                    </form>
                    <?php if (!empty($error)) { echo "<p class='error'>$error</p>"; } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFileUpload(selectElement) {
  var fileUploadDiv = document.getElementById("file-upload");
  if (selectElement.value == "Annual Leave") {
    fileUploadDiv.style.display = "none";
  } else {
    fileUploadDiv.style.display = "block";
  }
}

// Initialize the file upload visibility based on the selected leave type
toggleFileUpload(document.getElementById("leave_type"));
</script>

<script src="../vendors/scripts/core.js"></script>
<script src="../vendors/scripts/script.min.js"></script>
<script src="../vendors/scripts/process.js"></script>
<script src="../vendors/scripts/layout-settings.js"></script>
</body>
</html>
