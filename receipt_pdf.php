<?php
session_start();
require_once('DBconnect.php');
require('fpdf.php');

// Only general users
if (!isset($_SESSION['UserId']) || $_SESSION['Role'] !== 'general') {
    die("Access denied.");
}

if (!isset($_GET['id'])) {
    die("No Payment ID provided.");
}

$paymentId = intval($_GET['id']);

// Fetch payment details along with Child, Center, and User info
$sql = "SELECT 
            p.PaymentId, p.PaymentDate, p.Amount, p.PaymentMethod,
            c.Name as ChildName,
            ce.Name as CenterName, ce.Contact as CenterContact, ce.Address as CenterAddress,
            u.Name as UserName, u.Phone as UserPhone
        FROM payment p
        JOIN child_profiles c ON p.ChildId = c.ChildId
        JOIN centers ce ON p.CenterId = ce.CenterId
        JOIN users u ON p.UserId = u.UserId
        WHERE p.PaymentId = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $paymentId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    // Generate PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0, 10, 'Cash Memo Receipt', 0, 1, 'C');
    $pdf->Ln(10);

    $pdf->SetFont('Arial','',12);
    $pdf->Cell(0,10,'Transaction ID: TXN'.$row['PaymentId'],0,1);
    $pdf->Cell(0,10,'Payment ID: '.$row['PaymentId'],0,1);
    
    // User Info
    $pdf->Cell(0,10,'User: '.$row['UserName'],0,1);
    $pdf->Cell(0,10,'Phone: '.$row['UserPhone'],0,1);

    // Child Info
    $pdf->Cell(0,10,'Child: '.$row['ChildName'],0,1);

    // Center Info
    $pdf->Cell(0,10,'Center: '.$row['CenterName'],0,1);
    $pdf->Cell(0,10,'Contact: '.$row['CenterContact'],0,1);
    $pdf->Cell(0,10,'Address: '.$row['CenterAddress'],0,1);

    // Payment Info
    $pdf->Cell(0,10,'Amount: '.$row['Amount'].' BDT',0,1);
    $pdf->Cell(0,10,'Method: '.$row['PaymentMethod'],0,1);
    $pdf->Cell(0,10,'Date: '.$row['PaymentDate'],0,1);

    $pdf->Output();
} else {
    echo "Payment not found.";
}
?>
