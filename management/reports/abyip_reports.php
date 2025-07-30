<?php
require('C:\xampp\htdocs\sangguniang_kabataan\fpdf\fpdf.php');
include('C:\xampp\htdocs\sangguniang_kabataan\config.php');

// Fetch all projects data
$sql = "SELECT 
            reference_code,
            project_name,
            implementing_office,
            CONCAT(start_date, ' to ', end_date) AS duration,
            expected_output,
            funding_source,
            personal_services,
            mooe,
            capital_outlay,
            total_cost,
            sector
        FROM projects
        ORDER BY sector, project_name";
$result = $conn->query($sql);

// Create PDF in landscape orientation
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();

// Calculate dynamic image sizes based on page dimensions
$pageWidth = $pdf->GetPageWidth();
$pageHeight = $pdf->GetPageHeight();

// Add resizable background image (scaled to 30% of page width)
$bgWidth = $pageWidth * 0.3;
$bgHeight = $pageHeight * 0.3;
$pdf->Image('C:\xampp\htdocs\sangguniang_kabataan\management\bgi\fpdf.png', 
    ($pageWidth - $bgWidth) / 2,
    ($pageHeight - $bgHeight) / 2,
    $bgWidth, $bgHeight, 'PNG'
);

$pdf->SetAutoPageBreak(true, 20);

// SK Branding Header with resizable logos
$logoWidth = $pageWidth * 0.08; // 8% of page width
$logoHeight = $logoWidth * 1.1; // Maintain aspect ratio

$pdf->SetFont('Arial', 'B', 16);
$pdf->Image('C:\xampp\htdocs\sangguniang_kabataan\management\bgi\1952.png', 
    10, 10, $logoWidth, $logoHeight);
$pdf->Image('C:\xampp\htdocs\sangguniang_kabataan\management\bgi\sk_logo.png', 
    $pageWidth - 10 - $logoWidth, 10, $logoWidth, $logoHeight);
    
$pdf->SetY(15 + $logoHeight/5);
$pdf->Cell(0, 8, 'REPUBLIC OF THE PHILIPPINES', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 8, 'SANGGUNIANG KABATAAN', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 6, 'Barangay Prk. 2 Poblacion, Tupi, South Cotabato 9505', 0, 1, 'C');
$pdf->Ln(5);

// Official Report Header
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'COMPREHENSIVE PROJECTS REPORT', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Generated on: ' . date('F j, Y'), 0, 1, 'C');
$pdf->Ln(5);

// Report Metadata
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(40, 7, 'Prepared by:', 0, 0);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(60, 7, 'Nimzeal N. Solomon', 0, 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(40, 7, 'Fiscal Year:', 0, 0);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 7, date('Y'), 0, 1);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(40, 7, 'Position:', 0, 0);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(60, 7, 'SK Treasurer', 0, 1);
$pdf->Ln(8);

// Calculate dynamic column widths based on content importance
$colWidths = [
    'ref' => 15,    // Reference code
    'name' => $pageWidth * 0.15,   // Project name (15% of page width)
    'sector' => $pageWidth * 0.07, // Sector
    'office' => $pageWidth * 0.08, // Implementing office
    'duration' => $pageWidth * 0.07, // Duration
    'output' => $pageWidth * 0.13, // Expected output
    'funding' => $pageWidth * 0.08, // Funding source
    'ps' => $pageWidth * 0.07,    // Personal Services
    'mooe' => $pageWidth * 0.07,  // MOOE
    'co' => $pageWidth * 0.07,     // Capital Outlay
    'total' => $pageWidth * 0.08  // Total Cost
];

// Table Header
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell($colWidths['ref'], 10, 'Ref Code', 1, 0, 'C');
$pdf->Cell($colWidths['name'], 10, 'Project Name', 1, 0, 'C');
$pdf->Cell($colWidths['sector'], 10, 'Sector', 1, 0, 'C');
$pdf->Cell($colWidths['office'], 10, 'Office', 1, 0, 'C');
$pdf->Cell($colWidths['duration'], 10, 'Duration', 1, 0, 'C');
$pdf->Cell($colWidths['output'], 10, 'Expected Output', 1, 0, 'C');
$pdf->Cell($colWidths['funding'], 10, 'Funding', 1, 0, 'C');
$pdf->Cell($colWidths['ps'], 10, 'P.S.', 1, 0, 'C');
$pdf->Cell($colWidths['mooe'], 10, 'MOOE', 1, 0, 'C');
$pdf->Cell($colWidths['co'], 10, 'C.O.', 1, 0, 'C');
$pdf->Cell($colWidths['total'], 10, 'Total Cost', 1, 1, 'C');

$pdf->SetFont('Arial', '', 7); // Smaller font for data

// Function to handle text overflow with multi-line support
function fitText($pdf, $text, $width, $height = 5) {
    $words = explode(' ', $text);
    $lines = array();
    $currentLine = '';
    
    foreach ($words as $word) {
        $testLine = $currentLine . ' ' . $word;
        if ($pdf->GetStringWidth($testLine) < $width) {
            $currentLine = $testLine;
        } else {
            $lines[] = trim($currentLine);
            $currentLine = $word;
        }
    }
    $lines[] = trim($currentLine);
    
    // If single line fits, return it
    if (count($lines) == 1 && $pdf->GetStringWidth($lines[0]) < $width) {
        return $lines[0];
    }
    
    // Otherwise return first line with ellipsis
    while ($pdf->GetStringWidth($currentLine . '...') > $width && strlen($currentLine) > 0) {
        $currentLine = substr($currentLine, 0, -1);
    }
    return $currentLine . '...';
}

$grand_total = 0;
$current_sector = null;
$sector_total = 0;
$sector_ps = 0;
$sector_mooe = 0;
$sector_co = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // New sector handling
        if ($current_sector != $row['sector']) {
            // Print previous sector total if exists
            if ($current_sector !== null) {
                $pdf->SetFont('Arial', 'B', 7);
                $pdf->Cell(array_sum([
                    $colWidths['ref'], $colWidths['name'], $colWidths['sector'], 
                    $colWidths['office'], $colWidths['duration'], 
                    $colWidths['output'], $colWidths['funding']
                ]), 7, 'TOTAL ' . strtoupper($current_sector), 1, 0, 'R');
                $pdf->Cell($colWidths['ps'], 7, number_format($sector_ps, 2), 1, 0, 'R');
                $pdf->Cell($colWidths['mooe'], 7, number_format($sector_mooe, 2), 1, 0, 'R');
                $pdf->Cell($colWidths['co'], 7, number_format($sector_co, 2), 1, 0, 'R');
                $pdf->Cell($colWidths['total'], 7, number_format($sector_total, 2), 1, 1, 'R');
                $grand_total += $sector_total;
                $sector_total = 0;
                $sector_ps = 0;
                $sector_mooe = 0;
                $sector_co = 0;
            }
            
            // New sector header
            $current_sector = $row['sector'];
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetFillColor(230, 230, 230);
            $pdf->Cell(array_sum($colWidths), 7, strtoupper($current_sector), 1, 1, 'L', true);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('Arial', '', 7);
        }
        
        // Project data row with dynamic text fitting
        $pdf->Cell($colWidths['ref'], 7, fitText($pdf, $row['reference_code'] ?: '-', $colWidths['ref']), 1, 0, 'C');
        $pdf->Cell($colWidths['name'], 7, fitText($pdf, $row['project_name'], $colWidths['name']), 1, 0, 'L');
        $pdf->Cell($colWidths['sector'], 7, fitText($pdf, $row['sector'], $colWidths['sector']), 1, 0, 'C');
        $pdf->Cell($colWidths['office'], 7, fitText($pdf, $row['implementing_office'], $colWidths['office']), 1, 0, 'C');
        $pdf->Cell($colWidths['duration'], 7, fitText($pdf, $row['duration'], $colWidths['duration']), 1, 0, 'C');
        $pdf->Cell($colWidths['output'], 7, fitText($pdf, $row['expected_output'], $colWidths['output']), 1, 0, 'L');
        $pdf->Cell($colWidths['funding'], 7, fitText($pdf, $row['funding_source'], $colWidths['funding']), 1, 0, 'C');
        $pdf->Cell($colWidths['ps'], 7, number_format($row['personal_services'], 2), 1, 0, 'R');
        $pdf->Cell($colWidths['mooe'], 7, number_format($row['mooe'], 2), 1, 0, 'R');
        $pdf->Cell($colWidths['co'], 7, number_format($row['capital_outlay'], 2), 1, 0, 'R');
        $pdf->Cell($colWidths['total'], 7, number_format($row['total_cost'], 2), 1, 1, 'R');
        
        // Accumulate totals
        $sector_total += $row['total_cost'];
        $sector_ps += $row['personal_services'];
        $sector_mooe += $row['mooe'];
        $sector_co += $row['capital_outlay'];
    }
    
    // Final sector total
    if ($current_sector !== null) {
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(array_sum([
            $colWidths['ref'], $colWidths['name'], $colWidths['sector'], 
            $colWidths['office'], $colWidths['duration'], 
            $colWidths['output'], $colWidths['funding']
        ]), 7, 'TOTAL ' . strtoupper($current_sector), 1, 0, 'R');
        $pdf->Cell($colWidths['ps'], 7, number_format($sector_ps, 2), 1, 0, 'R');
        $pdf->Cell($colWidths['mooe'], 7, number_format($sector_mooe, 2), 1, 0, 'R');
        $pdf->Cell($colWidths['co'], 7, number_format($sector_co, 2), 1, 0, 'R');
        $pdf->Cell($colWidths['total'], 7, number_format($sector_total, 2), 1, 1, 'R');
        $grand_total += $sector_total;
    }
    
    // Grand total
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(array_sum([
        $colWidths['ref'], $colWidths['name'], $colWidths['sector'], 
        $colWidths['office'], $colWidths['duration'], 
        $colWidths['output'], $colWidths['funding']
    ]), 8, 'GRAND TOTAL', 1, 0, 'R');
    $pdf->Cell($colWidths['ps'], 8, '', 1, 0, 'R');
    $pdf->Cell($colWidths['mooe'], 8, '', 1, 0, 'R');
    $pdf->Cell($colWidths['co'], 8, '', 1, 0, 'R');
    $pdf->Cell($colWidths['total'], 8, number_format($grand_total, 2), 1, 1, 'R');
} else {
    $pdf->Cell(array_sum($colWidths), 10, 'No projects found', 1, 1, 'C');
}

$pdf->Ln(15);

// Approval Section
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 1, 'Haizel Jane Mary G. Uy', 0, 1, 'R');
$pdf->SetFont('Arial', '', 10);
$pdf->Ln(5);
$pdf->Cell(0, 5, 'SK Chairman', 0, 1, 'R');
$pdf->Cell(0, 5, 'Sangguniang Kabataan', 0, 1, 'R');
$pdf->Ln(10);

// Output the PDF
$pdf->Output('I', 'SK_Projects_Report_' . date('Y-m-d') . '.pdf');
?>