<?php
session_start();
require_once('DBconnect.php');
require('fpdf.php'); // FPDF library

if (!isset($_SESSION['UserId']) || $_SESSION['Role'] !== 'general') {
    die("Access denied.");
}

if (!isset($_GET['childId'])) die("Child not selected.");
$childId = intval($_GET['childId']);

$child = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM child_profiles WHERE ChildId=$childId"));
if (!$child) die("Invalid child.");

$dob = new DateTime($child['DateOfBirth']);
$today = new DateTime();
$ageMonths = ($today->diff($dob)->y) * 12 + $today->diff($dob)->m;

$vaccines = mysqli_query($conn, "SELECT v.VaccineId, v.Name, v.Dose_Number, v.Recommended_Age 
    FROM vaccine v ORDER BY v.VaccineId");

$records = [];
$cvRes = mysqli_query($conn, "SELECT * FROM childvaccination WHERE ChildId=$childId");
while ($row = mysqli_fetch_assoc($cvRes)) {
    $records[$row['VaccineId']] = $row;
}

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, "Vaccination Status for ".$child['Name'], 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, "Date of Birth: ".$child['DateOfBirth'], 0, 1);
$pdf->Cell(0, 8, "Gender: ".$child['Gender'], 0, 1);
$pdf->Cell(0, 8, "Age: ".$ageMonths." months", 0, 1);
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(50, 10, 'Vaccine', 1);
$pdf->Cell(10, 10, 'Dose', 1);
$pdf->Cell(45, 10, 'Status', 1);
$pdf->Cell(20, 10, 'Date', 1);
$pdf->Cell(50, 10, 'Center', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 10);
while ($v = mysqli_fetch_assoc($vaccines)) {
    $vid = $v['VaccineId'];
    $rec = isset($records[$vid]) ? $records[$vid] : null;

    if ($rec) {
        if ($rec['Status'] == 'Completed') {
            $pdf->SetTextColor(0, 128, 0); // Green ✅
            $statusText = "✅ Completed";
        } else {
            $pdf->SetTextColor(255, 0, 0); // Red ⚠
            if (!empty($rec['ScheduledDate'])) {
                $dueDate = new DateTime($rec['ScheduledDate']);
                $statusText = "⚠ Missed (Give within ".$dueDate->modify('+4 days')->format('Y-m-d').")";
            } else {
                $statusText = "⚠ Missed - Consult doctor";
            }
        }
    } else {
        $pdf->SetTextColor(0, 0, 255); // Blue 🕒
        $statusText = "🕒 Upcoming";
    }

    $pdf->Cell(50, 8, $v['Name'], 1);
    $pdf->Cell(10, 8, $v['Dose_Number'], 1, 0, 'C');
    $pdf->Cell(45, 8, $statusText, 1, 0, 'C');

    $scheduled = $rec && !empty($rec['ScheduledDate']) ? $rec['ScheduledDate'] : '-';
    $actual = $rec && !empty($rec['ActualDate']) ? $rec['ActualDate'] : '-';
    $center = $rec && !empty($rec['CenterId']) ? "Center ID ".$rec['CenterId'] : '-';
    $pdf->Cell(20, 8, $actual, 1, 0, 'C');
    $pdf->Cell(50, 8, $center, 1, 1, 'C');
}

$pdf->SetTextColor(0, 0, 0);

$pdf->Output("D", "full_vaccination_report_".$child['Name'].".pdf");


