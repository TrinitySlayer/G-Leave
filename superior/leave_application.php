<?php
require_once '../config.php'; // Connect to database
session_start();

if (!isset($_SESSION['employee_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch employee details
$employee_id = $_SESSION['employee_id'];
$sql = "SELECT name, position_id, dept_id, phone_no, join_date FROM employee WHERE employee_id = '$employee_id'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

if ($user) {
    $name = $user['name'];
    $position_id = $user['position_id'];
    $dept_id = $user['dept_id'];
    $phone_no = isset($user['phone_no']) ? $user['phone_no'] : 'Not Available'; // Handle missing phone_no
    $joinDate = new DateTime($user['join_date']);
    $today = new DateTime();

    // Fetch position name
    $positionQuery = "SELECT name FROM positions WHERE position_id = '$position_id'";
    $positionResult = mysqli_query($conn, $positionQuery);
    $positionData = mysqli_fetch_assoc($positionResult);
    $position = $positionData['name'];

    // Fetch department name
    $departmentQuery = "SELECT dept_name FROM department WHERE dept_id = '$dept_id'";
    $departmentResult = mysqli_query($conn, $departmentQuery);
    $departmentData = mysqli_fetch_assoc($departmentResult);
    $department = $departmentData['dept_name'];
} else {
    // Handle the case where user data is not found
    echo "<script>alert('User not found.'); window.location.href = '../login.php';</script>";
    exit();
}

if (isset($_POST['submit'])) {
    $leaveType = $_POST["leave_type"];
    $reason = $_POST["reason"];
    $start_date = $_POST["start_date"];
    $end_date = $_POST["end_date"];
    $contactNo = $_POST["contact_no"];
    $comments = $_POST["comments"];
    $targetFile = null;
    $error = "";

    // Handle medical document upload if necessary
    if ($leaveType == "Medical Leave" && !empty($_FILES["medicalDocument"]["name"])) {
        $targetDir = "uploads/";
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

        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "pdf") {
            $error = "Sorry, only JPG, JPEG, PNG & PDF files are allowed.";
            $uploadOk = 0;
        }

        if ($uploadOk == 0) {
            // Handle upload error
        } else {
            if (move_uploaded_file($_FILES["medicalDocument"]["tmp_name"], $targetFile)) {
                echo "<script>alert('The file " . basename($_FILES["medicalDocument"]["name"]) . " has been uploaded.')</script>";
            } else {
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
        $workingDays = $interval->days;

        // Fetch years of service
        $yearsOfService = $today->diff($joinDate)->y;

        // Calculate leave days based on leave type
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
        } elseif ($leaveType == "Halfday Leave") {
            $leaveDays = 0.5; // Halfday Leave is defined as half a day
        } elseif ($leaveType == "Unpaid Leave") {
            $applyDeadline = (clone $today)->modify('+5 days');
            if ($today > $applyDeadline) {
                $error = "Unpaid Leave must be applied 5 days before the leave day.";
            }
            $leaveDays = $workingDays; // No specific max days for Leave Without Pay
        } elseif ($leaveType == "Maternity Leave") {
            $maxLeaveDays = 98; // Maternity leave is fixed at 98 days
            $leaveDays = min($workingDays, $maxLeaveDays);
        } elseif ($leaveType == "Paternity Leave") {
            $maxLeaveDays = 7; // Paternity leave is fixed at 7 days
            $leaveDays = min($workingDays, $maxLeaveDays);
        } else {
            $leaveDays = $workingDays;
        }
    }

    // Get leave_type_id from leave_type table
    $leaveTypeQuery = "SELECT leave_type_id FROM leave_type WHERE type_name = '$leaveType'";
    $leaveTypeResult = mysqli_query($conn, $leaveTypeQuery);

    if (!$leaveTypeResult) {
        $error = "SQL Error: " . mysqli_error($conn);
    } else {
        $leaveTypeData = mysqli_fetch_assoc($leaveTypeResult);

        if ($leaveTypeData) {
            $leaveTypeId = $leaveTypeData['leave_type_id'];
            
            // Begin transaction
            mysqli_begin_transaction($conn);

            // Insert into leave_request table
            $query = "INSERT INTO leave_request (employee_id, start_date, end_date, leave_type_id, status_superior, reason,comments, total_leave) 
                       VALUES ('$employee_id', '$start_date', '$end_date', '$leaveTypeId', 'Pending', '$reason','$comments', '$leaveDays')";

            if (mysqli_query($conn, $query)) {
                // Commit transaction
                mysqli_commit($conn);
                header("Location: thanks.php?message=Leave request submitted successfully");
                exit();
            } else {
                $error = "Error: " . mysqli_error($conn);
                mysqli_rollback($conn);
            }
        } else {
            $error = "Invalid leave type selected.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Leave Application Form</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<style>
body {
  font-family: sans-serif;
}
.container {
  width: 600px;
  margin: 0 auto;
  padding: 20px;
  border: 1px solid #ccc;
}
h2 {
  text-align: center;
}
label {
  display: block;
  margin-bottom: 5px;
}
input[type="text"],
input[type="date"],
textarea, select {
  width: 100%;
  padding: 10px;
  margin-bottom: 15px;
  border: 1px solid #ccc;
  box-sizing: border-box;
}
input[type="radio"] {
  margin-right: 5px;
}
.submit-btn {
  background-color: #4CAF50;
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
.submit-btn:hover {
  background-color: #45a049;
}
.error {
  color: red;
  margin-bottom: 10px;
}
.upload-file {
  display: none;
}
</style>
</head>
<body>

<div class="container">

  <h2>Leave Application Form</h2>
  <form method="post" enctype="multipart/form-data">
    <div>
      <label for="name">Name:</label>
      <input type="text" id="name" value="<?php echo $name; ?>" readonly>
    </div>
    <div>
      <label for="position">Position:</label>
      <input type="text" id="position" value="<?php echo $position; ?>" readonly>
    </div>
    <div>
      <label for="department">Department:</label>
      <input type="text" id="department" value="<?php echo $department; ?>" readonly>
    </div>
    <div>
      <label for="contact_no">Contact No:</label>
      <input type="text" id="contact_no" name="contact_no" value="<?php echo $phone_no; ?>" required>
    </div>
    <div>
      <label for="leave_type">Type of Leave:</label>
      <select id="leave_type" name="leave_type" onchange="toggleFileUpload(this)">
        <!--- Type of leave --->
        <option value="Annual Leave">Annual Leave</option>
        <option value="Medical Leave">Medical Leave</option>
        <option value="Unpaid Leave">Unpaid Leave</option>
        <option value="Halfday Leave">Halfday Leave</option>
        <option value="Maternity Leave">Maternity Leave</option>
        <option value="Paternity Leave">Paternity Leave</option>
      </select>
    </div>
    <div>
      <label for="start_date">Start Date:</label>
      <input type="date" id="start_date" name="start_date" required>
    </div>
    <div>
      <label for="end_date">End Date:</label>
      <input type="date" id="end_date" name="end_date" required>
    </div>
    <div>
      <label for="reason">Reason:</label>
      <textarea id="reason" name="reason" rows="4" required></textarea>
    </div>
    <div id="file-upload" class="upload-file">
      <label for="medicalDocument">Medical Document (only for Medical Leave):</label>
      <input type="file" name="medicalDocument" id="medicalDocument">
    </div>
    <div>
      <label for="comments">Comments (Optional):</label>
      <textarea id="comments" name="comments" rows="4"></textarea>
    </div>
    <button type="submit" name="submit" class="submit-btn">Submit</button>
  </form>
  <?php if (!empty($error)) { echo "<p class='error'>$error</p>"; } ?>
</div>

<script>
// Get the alert element
var alertElement = document.createElement("div");
alertElement.className = "alert alert-info";
alertElement.textContent = "Selamat Datang ke Sistem E-leave Golac, " + "<?php echo strtoupper($name); ?>";

// Get the container element
var containerElement = document.querySelector(".container");

// Insert the alert element at the beginning of the container
containerElement.insertBefore(alertElement, containerElement.firstChild);

// Remove the alert element after 5 seconds
setTimeout(function() {
  alertElement.parentNode.removeChild(alertElement);
}, 5000);

function toggleFileUpload(selectElement) {
  var fileUploadDiv = document.getElementById("file-upload");
  if (selectElement.value == "Medical Leave") {
    fileUploadDiv.style.display = "block";
  } else {
    fileUploadDiv.style.display = "none";
  }
}
</script>
</body>
</html>
