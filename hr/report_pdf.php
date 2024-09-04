<?php
include('../includes/session.php');
include('../includes/config.php');
require_once('../TCPDF-main/tcpdf.php');
require_once('../FPDI/src/autoload.php'); // Adjust path as necessary


if (!isset($_GET['edit']) || !preg_match('/^[\w-]{1,10}$/', $_GET['edit'])) {
    echo "<script>alert('Invalid Leave Request ID.'); window.location.href = 'view_leave.php';</script>";
    exit();
}

// Fetch the leave request details
$did = $_GET['edit'];
$sql = "SELECT 
            leave_request.leave_request_id AS lid, 
            employee.name, 
            employee.gender, 
            employee.position_name, 
            employee.employee_id,
            employee.dept_id, 
            leave_type.type_name, 
            leave_request.end_date, 
            leave_request.start_date, 
            leave_request.posting_date,
            leave_request.status_hr, 
            leave_request.reason, 
            leave_request.applied_leave,
            leave_request.medical_document
        FROM 
            leave_request 
        JOIN 
            employee ON leave_request.employee_id = employee.employee_id
        JOIN 
            leave_type ON leave_request.leave_type_id = leave_type.leave_type_id 
        WHERE 
            leave_request.leave_request_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $did); 
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($request_id, $name, $gender, $position, $employee_id, $dept_id, $leaveType, $end_date, $start_date, $posted, $status_hr, $reason, $applied_leave, $medical_document);

if (!$stmt->fetch()) {
    echo "<script>alert('Leave request not found.'); window.location.href = 'view_leave.php';</script>";
    exit();
}

// Fetch department name using prepared statements
$departmentQuery = "SELECT dept_name FROM department WHERE dept_id = ?";
$deptStmt = $conn->prepare($departmentQuery);
$deptStmt->bind_param('i', $dept_id); 
$deptStmt->execute();
$deptStmt->store_result();
$deptStmt->bind_result($department);
$deptStmt->fetch();
$deptStmt->close();

// Determine status text
$status_text = match($status_hr) {
    1 => 'Approved',
    2 => 'Rejected',
    default => 'Pending',
};

$stmt->close();

// Define PDF class
use setasign\Fpdi\Tcpdf\Fpdi;

class PDF extends Fpdi {
    public function Header() {
        if ($this->page == 1) {
            $logoPath = '../vendors/images/GOLAC COMMERCE-LOGO FA.jpg';
            $pageWidth = $this->getPageWidth();
            $logoWidth = 25; 
            $x = ($pageWidth - $logoWidth) / 2;

            $this->Image($logoPath, $x, 10, $logoWidth);
            $this->SetY(30); // Adjusted Y position for the header
            $this->SetFont('helvetica', 'B', 10); // Set font size to 10
            $this->Cell(0, 50, 'LEAVE APPLICATION FORM', 0, 1, 'C');
            $this->Ln(20); // Space after header
        }
    }

    public function Footer() {
        // You can add footer content here if needed
    }
}


// Create new PDF document
$pdf = new PDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('G-Leave System');
$pdf->SetTitle('Leave Application Form');
$pdf->SetSubject('Leave Application');

// Set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', 10));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', 10));

// Set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP + 35, PDF_MARGIN_RIGHT); // Increased top margin to accommodate header
$pdf->SetHeaderMargin(20); // Adjusted header margin
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Set font
$pdf->SetFont('dejavusans', '', 10, '', true);

// Add a page
$pdf->AddPage();

// Add content
$pdf->SetFont('dejavusans', '', 10);

// Employee Details
$pdf->Cell(20, 10, 'Name: ', 0, 0); 
$pdf->SetFont('dejavusans', 'U', 10);
$pdf->Cell(70, 10, $name, 0, 0); 
$pdf->SetFont('dejavusans', '', 10);
$pdf->Cell(30, 10, 'Department: ', 0, 0); 
$pdf->SetFont('dejavusans', 'U', 10);
$pdf->Cell(30, 10, $department, 0, 1); 

$pdf->SetFont('dejavusans', '', 10);
$pdf->Cell(30, 10, "Designation: ", 0, 0); 
$pdf->SetFont('dejavusans', 'U', 10);
$pdf->Cell(0, 10, $position, 0, 1); 
$pdf->SetFont('dejavusans', '', 10);

// Define the leave types and cell width
$leave_types = [
    "Annual Leave",
    "Unpaid Leave",
    "Emergency Leave",
    "Medical Leave",
    "Maternity Leave",
    "Hospitalization Leave",
    "Paternity Leave",
    "Others"
];

$cell_width = 45; // Adjust based on the page width and number of types per row

// Set font for leave type section
$pdf->Ln(10);
$pdf->SetFont('dejavusans', 'B', 10);
$pdf->Cell(0, 10, "TYPE OF LEAVE (Please tick √)", 0, 1, 'L');
$pdf->SetFont('dejavusans', '', 10);

// Loop through leave types and display side by side
$x_start = $pdf->GetX();
$y_start = $pdf->GetY();
$cols_per_row = 4;

foreach ($leave_types as $index => $type) {
    if ($index % $cols_per_row == 0 && $index != 0) {
        $pdf->Ln(); // Move to next line
        $pdf->SetX($x_start); // Reset X position to the start
    }

    $pdf->Cell(10, 10, ($leaveType == $type) ? '√' : ' ', 1, 0, 'C');
    $pdf->Cell($cell_width - 5, 10, $type, 0, 0, 'L');
}

$pdf->Ln(); // Ensure a line break after the last row

// Note Section
$pdf->Ln(10);
$pdf->SetFont('dejavusans', 'B', 10);
$pdf->MultiCell(0, 10, "Note:\n\n1) All Annual Leave applications must be submitted five (5) working days before commencement of leave. If the leave is applied less than five (5) working days, it shall be treated as Emergency Leave (EL).", 0, 'L');

// Reason for Leave
$pdf->Ln(10);
$pdf->Cell(20, 10, "Reason: ", 0, 0, 'L');
$pdf->SetFont('dejavusans', 'U', 10);
$pdf->MultiCell(0, 10, $reason, 0, 'L');
$pdf->SetFont('dejavusans', '', 10);

// Dates and Total Leave
$pdf->Ln(10);
$pdf->Cell(50, 10, "Total No. of leave: ", 0, 0, 'L');
$pdf->SetFont('dejavusans', 'U', 10);
$pdf->Cell(20, 10, $applied_leave, 0, 0, 'L');
$pdf->SetFont('dejavusans', '', 10);
$pdf->Cell(10, 10, "From: ", 0, 0, 'L');
$pdf->SetFont('dejavusans', 'U', 10);
$pdf->Cell(50, 10, $start_date, 0, 0, 'L');
$pdf->SetFont('dejavusans', '', 10);
$pdf->Cell(10, 10, "To: ", 0, 0, 'L');
$pdf->SetFont('dejavusans', 'U', 10);
$pdf->Cell(50, 10, $end_date, 0, 1, 'L');

// Signature and Date Section
$pdf->Ln(20);
$pdf->SetFont('dejavusans', '', 10);
$pdf->Cell(30, 10, "Status: ", 0, 0, 'L');
$pdf->SetFont('dejavusans', 'U', 10);
$pdf->Cell(50, 10, $status_text, 0, 1, 'L');

if (!empty($medical_document)) {
    $pdf->AddPage();
    $pdf->setPrintHeader(false);
    $file_path = '../uploads/' . $medical_document;
    $file_extension = pathinfo($file_path, PATHINFO_EXTENSION);

    if (in_array(strtolower($file_extension), ['jpg', 'jpeg', 'png'])) {
        $pdf->Image($file_path, 10, 10, 270, 297, strtoupper($file_extension), '', '', true, 300, '', false, false, 0);
    } elseif (strtolower($file_extension) == 'pdf') {
        // Embed the PDF document
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(0, 10, '', 0, 1, 'L');
        $pdf->Ln(10);

        // Use FPDI for full-page PDF embedding
        $pdf->setSourceFile($file_path);
        $tplId = $pdf->importPage(1);
        $pdf->useTemplate($tplId, ['adjustPageSize' => true]); // Adjust page size to fit the PDF
    } else {
        // Handle other file types or display an error message
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(0, 10, 'Unsupported file type.', 0, 1, 'L');
    }
}


// Close and output PDF document
$pdf->Output($name . '_leave_application.pdf', 'I');
?>
