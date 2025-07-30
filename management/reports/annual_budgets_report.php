<?php
require('C:\xampp\htdocs\sangguniang_kabataan\fpdf\fpdf.php');
include('C:\xampp\htdocs\sangguniang_kabataan\config.php');

// Fetch only categories that have programs
$sql = "SELECT 
            bc.id as category_id,
            bc.category_name,
            ab.id,
            ab.program_name, 
            ab.allocated_amount
        FROM budget_categories bc
        INNER JOIN annual_budget ab ON bc.id = ab.category_id
        ORDER BY bc.category_name, ab.program_name";
$result = $conn->query($sql);

// Create instance of FPDF with portrait orientation
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();

// Add centered background image
$pdf->Image('C:\xampp\htdocs\sangguniang_kabataan\management\bgi\fpdf.png', 
    ($pdf->GetPageWidth() - 100) / 2,
    ($pdf->GetPageHeight() - 100) / 2,
    100, 100, 'PNG'
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
$pdf->Cell(0, 10, 'ANNUAL BUDGET ALLOCATION REPORT', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Generated on: ' . date('F j, Y'), 0, 1, 'C');
$pdf->Ln(10);

// Report Metadata
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(40, 7, 'Prepared by:', 0, 0);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(60, 7, 'Kirby Jay S. Geldore', 0, 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(40, 7, 'Fiscal Year:', 0, 0);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 7, date('Y'), 0, 1);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(40, 7, 'Position:', 0, 0);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(60, 7, 'SK Chairman', 0, 1);
$pdf->Ln(8);

// Table Header
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(140, 8, 'PROGRAM / ACTIVITY', 1, 0, 'L');
$pdf->Cell(50, 8, 'AMOUNT', 1, 1, 'R');

$pdf->SetFont('Arial', '', 10);

$grand_total = 0;
$current_category = null;
$category_total = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // If this is a new category, print the category header
        if ($current_category != $row['category_id']) {
            // Print the previous category's total if it exists
            if ($current_category !== null) {
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(140, 7, 'TOTAL ' . strtoupper($current_category_name), 1, 0, 'R');
                $pdf->Cell(50, 7, 'Php' . number_format($category_total, 2), 1, 1, 'R');
                $grand_total += $category_total;
                $category_total = 0;
            }
            
            // Start new category
            $current_category = $row['category_id'];
            $current_category_name = $row['category_name'];
            
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->SetFillColor(230, 230, 230);
            $pdf->Cell(190, 8, strtoupper($current_category_name), 1, 1, 'L', true);
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetFillColor(255, 255, 255);
        }
        
        // Print program details
        $pdf->Cell(140, 7, '   ' . $row['program_name'], 1, 0, 'L');
        $pdf->Cell(50, 7, 'Php' . number_format($row['allocated_amount'], 2), 1, 1, 'R');
        $category_total += $row['allocated_amount'];
    }
    
    // Print the last category's total
    if ($current_category !== null) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(140, 7, 'TOTAL ' . strtoupper($current_category_name), 1, 0, 'R');
        $pdf->Cell(50, 7, 'Php' . number_format($category_total, 2), 1, 1, 'R');
        $grand_total += $category_total;
    }
    
    // Print grand total
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(140, 10, 'GRAND TOTAL', 1, 0, 'R');
    $pdf->Cell(50, 10, 'Php' . number_format($grand_total, 2), 1, 1, 'R');
} else {
    $pdf->Cell(190, 10, 'No budget records found', 1, 1, 'C');
}

$pdf->Ln(15);

// Approval Section
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 5, 'Haizel Jane Mary G. Uy', 0, 1, 'R');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, 'SK Chairman', 0, 1, 'R');
$pdf->Cell(0, 5, 'Sangguniang Kabataan', 0, 1, 'R');
$pdf->Ln(10);


// Output the PDF
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="SK_Budget_Allocation_Report_' . date('Y-m-d') . '.pdf"');
header('Content-Transfer-Encoding: binary');

$pdfContent = $pdf->Output('S');
header('Content-Length: ' . strlen($pdfContent));
echo $pdfContent;
?>