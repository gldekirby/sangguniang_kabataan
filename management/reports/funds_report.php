<?php
require('C:\xampp\htdocs\sangguniang_kabataan\fpdf\fpdf.php');
include ('C:\xampp\htdocs\sangguniang_kabataan\config.php');

// Fetch fund sources data
$sql = "SELECT name, description, amount, created_at FROM fund_sources";
$result = $conn->query($sql);

// Create instance of FPDF with portrait orientation
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();

// Add centered background image
$pdf->Image('C:\xampp\htdocs\sangguniang_kabataan\management\bgi\fpdf.png', 
    ($pdf->GetPageWidth() - 100) / 2,  // X position (centered)
    ($pdf->GetPageHeight() - 100) / 2, // Y position (centered)
    100,  // Width
    100,  // Height
    'PNG' // Image format
);

$pdf->SetAutoPageBreak(true, 20);

// SK Branding Header
$pdf->SetFont('Arial', 'B', 16);
$pdf->Image('C:\xampp\htdocs\sangguniang_kabataan\management\bgi\sk_logo.png', 10, 10, 25);
$pdf->Image('C:\xampp\htdocs\sangguniang_kabataan\management\bgi\sk_logo.png', 175, 10, 25);
$pdf->SetY(15);
$pdf->Cell(0, 8, 'REPUBLIC OF THE PHILIPPINES', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 8, 'SANGGUNIANG KABATAAN', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 6, 'Barangay Prk. 2 Poblacion, Tupi, South Cotabato 9505', 0, 1, 'C');
$pdf->Ln(5);

// Official Report Header
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'FUND SOURCES REPORT', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Generated on: ' . date('F j, Y'), 0, 1, 'C');
$pdf->Ln(10);

// Report Metadata
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(40, 7, 'Prepared by:', 0, 0);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(60, 7, 'Kirby Jay S. Geldore', 0, 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(40, 7, 'Report Period:', 0, 0);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 7, date('F Y'), 0, 1);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(40, 7, 'Position:', 0, 0);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(60, 7, 'SK Chairman', 0, 1);
$pdf->Ln(8);

// Table Dimensions
$tableWidth = 190;
$columnWidths = array(
    'no' => 10,
    'name' => 35,
    'description' => 85,
    'date' => 30,
    'amount' => 30
);

// Table Header
$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($columnWidths['no'], 8, '#', 1, 0, 'C');
$pdf->Cell($columnWidths['name'], 8, 'FUND SOURCE', 1, 0, 'C');
$pdf->Cell($columnWidths['description'], 8, 'DESCRIPTION', 1, 0, 'C');
$pdf->Cell($columnWidths['date'], 8, 'DATE ADDED', 1, 0, 'C');
$pdf->Cell($columnWidths['amount'], 8, 'AMOUNT', 1, 1, 'C');

// Table Content
$pdf->SetFont('Arial', '', 9);
$fill = false;
$totalFunds = 0;
$counter = 1;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pdf->SetX(10);
        $pdf->Cell($columnWidths['no'], 7, $counter++, 1, 0, 'C');
        $pdf->Cell($columnWidths['name'], 7, substr($row['name'], 0, 30), 1, 0, 'L');
        $pdf->Cell($columnWidths['description'], 7, substr($row['description'], 0, 70), 1, 0, 'L');
        $pdf->Cell($columnWidths['date'], 7, date('M d, Y', strtotime($row['created_at'])), 1, 0, 'C');
        $pdf->Cell($columnWidths['amount'], 7, 'Php' . number_format($row['amount'], 2), 1, 1, 'R');
        $totalFunds += $row['amount'];
    }
} else {
    $pdf->SetX(10);
    $pdf->Cell($tableWidth, 10, 'No fund sources found', 1, 1, 'C');
}

// Total Row
$pdf->SetX(10);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($columnWidths['no'] + $columnWidths['name'] + $columnWidths['description'] + $columnWidths['date'], 8, 'TOTAL FUNDS:', 1, 0, 'R');
$pdf->Cell($columnWidths['amount'], 8, 'Php' . number_format($totalFunds, 2), 1, 1, 'R');
$pdf->Ln(15);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 5, 'KIRBY JAY S. GELDORE', 0, 1, 'R');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, 'SK Chairman', 0, 1, 'R');
$pdf->Cell(0, 5, 'Sangguniang Kabataan', 0, 1, 'R');
$pdf->Ln(10);

// Output PDF
$pdf->Output('I', 'SK_Fund_Sources_Report_' . date('Y-m-d') . '.pdf');
?>