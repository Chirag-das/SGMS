<?php
require_once 'core/init.php';
require_once 'lib/fpdf/fpdf.php';

// Check login (Admin or Guard)
if (!$auth->isLoggedIn()) {
    die("Unauthorized access.");
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) die("Invalid request.");

$salaryModel = new Salary($conn);
$salary = $salaryModel->getById($id);

if (!$salary) die("Salary record not found.");

// If guard is logged in, they can only view their own payslip
if (isset($_SESSION['guard_id']) && $_SESSION['guard_id'] != $salary['guard_id']) {
    die("Unauthorized access to this payslip.");
}

// PDF Generation
class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, 'SECURITY GUARD MANAGEMENT SYSTEM', 0, 1, 'C');
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 5, 'Monthly Pay Slip', 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 11);

// Employee Details
$pdf->Cell(40, 7, 'Employee Name:', 0);
$pdf->Cell(60, 7, $salary['full_name'], 0);
$pdf->Cell(40, 7, 'Employee ID:', 0);
$pdf->Cell(0, 7, $salary['employee_id'], 0, 1);

$pdf->Cell(40, 7, 'Month/Year:', 0);
$pdf->Cell(60, 7, date('F Y', strtotime($salary['year_month'] . '-01')), 0);
$pdf->Cell(40, 7, 'Date Generated:', 0);
$pdf->Cell(0, 7, date('d-m-Y'), 0, 1);

$pdf->Ln(10);

// Table Header
$pdf->SetFillColor(230, 230, 230);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(95, 8, 'Earnings', 1, 0, 'C', true);
$pdf->Cell(95, 8, 'Deductions / Stats', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 11);

// Row 1
$pdf->Cell(60, 8, 'Basic Salary', 1);
$pdf->Cell(35, 8, number_format($salary['basic_salary'], 2), 1, 0, 'R');
$pdf->Cell(60, 8, 'Present Days', 1);
$pdf->Cell(35, 8, $salary['present_days'], 1, 1, 'R');

// Row 2
$pdf->Cell(60, 8, 'Overtime Allowance', 1);
$pdf->Cell(35, 8, number_format($salary['overtime_allowance'], 2), 1, 0, 'R');
$pdf->Cell(60, 8, 'Absent Days', 1);
$pdf->Cell(35, 8, $salary['absent_days'], 1, 1, 'R');

// Row 3 (Bonus & Leave)
$pdf->Cell(60, 8, 'Bonus', 1);
$pdf->Cell(35, 8, number_format($salary['bonus'], 2), 1, 0, 'R');
$pdf->Cell(60, 8, 'Leave Days', 1);
$pdf->Cell(35, 8, $salary['leave_days'], 1, 1, 'R');

// Row 4 (Other Allowances & Deductions)
$pdf->Cell(60, 8, 'Other Allowances', 1);
$pdf->Cell(35, 8, '0.00', 1, 0, 'R');
$pdf->Cell(60, 8, 'Deductions', 1);
$pdf->Cell(35, 8, number_format($salary['deductions'], 2), 1, 1, 'R');

// Totals
$pdf->SetFont('Arial', 'B', 11);
$gross = $salary['basic_salary'] + $salary['overtime_allowance'] + $salary['bonus'];
$pdf->Cell(60, 8, 'Gross Earnings', 1);
$pdf->Cell(35, 8, number_format($gross, 2), 1, 0, 'R');
$pdf->Cell(60, 8, 'Total Deductions', 1);
$pdf->Cell(35, 8, number_format($salary['deductions'], 2), 1, 1, 'R');

$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(155, 10, 'Net Salary Payable', 1, 0, 'R');
$pdf->Cell(35, 10, number_format($salary['net_salary'], 2), 1, 1, 'R');

$pdf->Ln(20);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(95, 5, '__________________________', 0, 0, 'C');
$pdf->Cell(95, 5, '__________________________', 0, 1, 'C');
$pdf->Cell(95, 5, "Employee's Signature", 0, 0, 'C');
$pdf->Cell(95, 5, "Admin's Signature", 0, 1, 'C');

$pdf->Output('D', 'PaySlip_' . $salary['employee_id'] . '_' . $salary['year_month'] . '.pdf');
?>
