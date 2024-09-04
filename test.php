<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "leave_golac";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get leave type and balances
$sql = "
SELECT 
    lt.type_name, 
    lt.total_leave, 
    SUM(lr.total_leave) AS leave_taken, 
    (lt.total_leave - SUM(lr.total_leave)) AS leave_balance
FROM 
    leave_type lt
LEFT JOIN 
    leave_request lr 
ON 
    lt.leave_type_id = lr.leave_type_id
GROUP BY 
    lt.leave_type_id";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Balance</title>
    <style>
        table {
            width: 50%;
            border-collapse: collapse;
            margin: 50px 0;
            font-size: 18px;
            text-align: left;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<h2>Leave Balance Summary</h2>

<table>
    <thead>
        <tr>
            <th>Leave Type</th>
            <th>Total Leave</th>
            <th>Leave Taken</th>
            <th>Leave Balance</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($result->num_rows > 0) {
            // Output data of each row
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . $row["type_name"] . "</td>
                        <td>" . $row["total_leave"] . "</td>
                        <td>" . $row["leave_taken"] . "</td>
                        <td>" . $row["leave_balance"] . "</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No data available</td></tr>";
        }
        $conn->close();
        ?>
    </tbody>
</table>

</body>
</html>
