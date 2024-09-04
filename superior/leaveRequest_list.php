<?php
session_start();
require_once '../config.php';
require_once 'sidenav.php';


if (!isset($_SESSION['employee_id'])) {
    header("Location: ../login.php");
    exit;
}

// Fetch leave requests from the database, including leave type names
$sql = "SELECT lr.leave_request_id, e.employee_id, e.name, lt.type_name as leave_type, lr.start_date, lr.end_date, lr.status_superior 
        FROM leave_request lr 
        JOIN employee e ON lr.employee_id = e.employee_id
        JOIN leave_type lt ON lr.leave_type_id = lt.leave_type_id";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,100,300,700' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="framework/table-02/css/style.css">
    <script src="https://cdn.jsdelivr.net/momentjs/2.12.0/moment.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

</head>
<body>
<section class="table__body">
<div class="container">
    <div class="row justify-content-center">
    <div class="col-md-6 text-center mb-5">
    <h2>Leave Requests</h2>
    </div>
    </div>
    <div class="row">
    <div class="col-lg-12">
    <div class="panel panel-primary">

        <div class="panel-heading">
            <h3 class="panel-title">Current Employees</h3>
        </div>
    <div class="panel-body">          
    <table class="table table-hover">
        <thead>
            <tr>
                <th class="align-middle">Employee ID</th>
                <th class="align-middle">Name</th>
                <th class="align-middle">Leave Type</th>
                <th class="align-middle">Start Date - End Date</th>
                <th class="align-middle">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) { 
                $statusClass = strtolower($row['status_superior']);
                $statusClass = "btn-" . ($statusClass ? $statusClass : "pending");    
                ?>
                <tr class="alert" role="alert">
                    <td><?php echo htmlspecialchars($row['employee_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['leave_type']); ?></td>
                    <td><?php echo htmlspecialchars($row['start_date'] . " - " . $row['end_date']); ?></td>
                    <!--<td><span class="status <?php //echo $statusClass; ?>"><?php //echo htmlspecialchars($row['status_superior']); ?></span></td>-->
                    <td>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#approvalModal" 
                                data-id="<?php echo htmlspecialchars($row['leave_request_id']); ?>"
                                data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                data-leavetype="<?php echo htmlspecialchars($row['leave_type']); ?>"
                                data-startdate="<?php echo htmlspecialchars($row['start_date']); ?>"
                                data-enddate="<?php echo htmlspecialchars($row['end_date']); ?>">
                            <?php echo htmlspecialchars($row['status_superior']); ?>
                        </button>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
        </div>
</section>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approvalModalLabel">Leave Request Approval</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="approvalForm" method="post" action="approve_leave.php">
                    <input type="hidden" id="request_id" name="request_id">
                    <div class="mb-3">
                        <label for="employee_name" class="form-label">Employee Name:</label>
                        <input type="text" class="form-control" id="employee_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="leave_type" class="form-label">Leave Type:</label>
                        <input type="text" class="form-control" id="leave_type" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="leave_dates" class="form-label">Leave Dates:</label>
                        <input type="text" class="form-control" id="leave_dates" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="status_superior" class="form-label">Status:</label>
                        <select class="form-control" id="status_superior" name="status_superior">
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>          
</section>
<script src="framework/table-02/js/jquery.min.js"></script>
  <script src="framework/table-02/js/popper.js"></script>
  <script src="framework/table-02/js/bootstrap.min.js"></script>
  <script src="framework/table-02/js/main.js"></script>

<script>
    // Get the alert element
var alertElement = document.createElement("div");
alertElement.className = "alert alert-info";
alertElement.textContent = "Selamat Datang ke Sistem E-leave Golac, " + "<?php echo strtoupper($name); ?>";

document.addEventListener('DOMContentLoaded', function () {
    var approvalModal = document.getElementById('approvalModal');
    approvalModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var name = button.getAttribute('data-name');
        var leaveType = button.getAttribute('data-leavetype');
        var startDate = button.getAttribute('data-startdate');
        var endDate = button.getAttribute('data-enddate');

        var requestIdInput = approvalModal.querySelector('#request_id');
        var employeeNameInput = approvalModal.querySelector('#employee_name');
        var leaveTypeInput = approvalModal.querySelector('#leave_type');
        var leaveDatesInput = approvalModal.querySelector('#leave_dates');

        requestIdInput.value = id;
        employeeNameInput.value = name;
        leaveTypeInput.value = leaveType;
        leaveDatesInput.value = startDate + " - " + endDate;

        // Set the initial color of the submit button based on the current status
        var initialStatus = button.textContent.trim().toLowerCase();
        submitButton.classList.remove('btn-pending', 'btn-approved', 'btn-rejected');
        submitButton.classList.add('btn-' + initialStatus);

        // Add change event to status select to change button color
        statusInput.addEventListener('change', function() {
            var selectedStatus = statusInput.value.toLowerCase();
            submitButton.classList.remove('btn-pending', 'btn-approved', 'btn-rejected');
            submitButton.classList.add('btn-' + selectedStatus);
        });
    });
});
</script>
</body>
</html>
