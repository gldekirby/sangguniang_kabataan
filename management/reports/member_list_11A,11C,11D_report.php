<?php
require('C:\xampp\htdocs\sangguniang_kabataan\fpdf\fpdf.php');
include('C:\xampp\htdocs\sangguniang_kabataan\config.php');

function extractSpecificPurok($street) {
    if (preg_match('/(Purok|Prk|Purok|Zone|Sitio)\s*(11A|11C|11D)/i', $street, $matches)) {
        return 'Purok ' . $matches[2];
    }
    return null;
}

$sql = "SELECT member_id, last_name, first_name, middle_name, gender, street 
        FROM members 
        WHERE status1 = 'approved'
        ORDER BY street, last_name, first_name";
$result = $conn->query($sql);

$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();

// Add branding header
$logoWidth = 25;
$logoHeight = 25;
$pdf->SetFont('Arial', 'B', 16);
$pdf->Image('C:\xampp\htdocs\sangguniang_kabataan\management\bgi\1952.png', 10, 10, $logoWidth, $logoHeight);
$pdf->Image('C:\xampp\htdocs\sangguniang_kabataan\management\bgi\sk_logo.png', 180, 10, $logoWidth, $logoHeight);
$pdf->SetY(5 + $logoHeight/2);
$pdf->Cell(0, 8, 'REPUBLIC OF THE PHILIPPINES', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 8, 'SANGGUNIANG KABATAAN', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Barangay Prk. 2 Poblacion, Tupi, South Cotabato 9505', 0, 1, 'C');
$pdf->Ln(5);

// Report Header
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'MEMBERS LIST', 0, 1, 'C');
$pdf->Cell(0, 10, 'District 4 - Purok 11A, 11C, 11D', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Generated on: ' . date('F j, Y'), 0, 1, 'C');

$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 5, 'Prepared by: Nimzeal N. Solomon', 0, 1, 'L');
$pdf->Cell(0, 5, 'Position: SK Treasurer', 0, 1, 'L');
$pdf->Ln(5);

if ($result && $result->num_rows > 0) {
    $membersByPurok = [
        'Purok 11A' => [],
        'Purok 11C' => [],
        'Purok 11D' => []
    ];
    
    while ($row = $result->fetch_assoc()) {
        $purok = extractSpecificPurok($row['street']);
        if ($purok && isset($membersByPurok[$purok])) {
            $membersByPurok[$purok][] = $row;
        }
    }
    
    $totalApproved = 0;
    $hasContent = false;
    
    foreach ($membersByPurok as $purok => $members) {
        $memberCount = count($members);
        
        if ($memberCount === 0) {
            continue;
        }
        
        $hasContent = true;
        $totalApproved += $memberCount;
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, $purok, 0, 1, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        
        // Table header
        $pdf->Cell(15, 8, 'No.', 1, 0, 'C');  // Changed from 'ID' to 'No.'
        $pdf->Cell(150, 8, 'Name', 1, 0, 'L');
        $pdf->Cell(25, 8, 'Gender', 1, 1, 'C');
        $pdf->SetFont('Arial', '', 10);
        
        $counter = 1; // Initialize counter for this purok
        
        foreach ($members as $row) {
            $middleInitial = !empty($row['middle_name']) ? substr($row['middle_name'], 0, 1) . '.' : '';
            $name = $row['last_name'] . ', ' . $row['first_name'] . ' ' . $middleInitial;
            
            $pdf->Cell(15, 8, $counter, 1, 0, 'C'); // Use counter instead of member_id
            $pdf->Cell(150, 8, $name, 1, 0, 'L');
            $pdf->Cell(25, 8, $row['gender'], 1, 1, 'C');
            
            $counter++; // Increment counter for next member
            
            if ($pdf->GetY() > 250) {
                $pdf->AddPage();
                $pdf->SetY(20);
            }
        }
    }
    
    if ($hasContent) {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'Total Members (Purok 11A, 11C, 11D): ' . $totalApproved, 0, 1, 'L');
        $pdf->Ln(5);
    } else {
        $pdf->Cell(0, 10, 'No members found in Purok 11A, 11C, and 11D', 1, 1, 'C');
    }
} else {
    $pdf->Cell(0, 10, 'No members found in Purok 11A, 11C, and 11D', 1, 1, 'C');
}

// Signatures section
if ($pdf->GetY() > 220) {
    $pdf->AddPage();
    $pdf->SetY(20);
}


$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 5, 'Haizel Jane Mary G. Uy', 0, 1, 'R');
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 5, 'SK Chairman', 0, 1, 'R');
$pdf->Cell(0, 5, 'Sangguniang Kabataan', 0, 1, 'R');

$pdf->Output('I', 'SK_Approved_Members_Purok_11A_11C_11D_' . date('Y-m-d') . '.pdf');
?>