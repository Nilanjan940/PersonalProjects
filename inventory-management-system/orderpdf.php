<?php
session_start();
require_once "config.php";
require_once "fpdf/fpdf186/fpdf.php";

// Get company profile
$companyId = 1; 
$query = "SELECT * FROM companyprofile WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $companyId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$companyProfile = mysqli_fetch_assoc($result);
$companyProfileImagePath = $companyProfile['profilepicture'] ?? '';
$companyName = $companyProfile['name'] ?? '';

// Validate and sanitize inputs
$selectedLocation = $_POST['locationFilter'] ?? '';
if ($selectedLocation !== '') {
    $selectedLocation = mysqli_real_escape_string($conn, $selectedLocation);
}

$date1 = $_POST['date1'] ?? '';
$date2 = $_POST['date2'] ?? '';
if ($date1 !== '' && $date2 !== '') {
    $date1 = date("Y-m-d", strtotime($date1));
    $date2 = date("Y-m-d", strtotime($date2));
} else {
    $date1 = '';
    $date2 = '';
}

// Create PDF
$pdf = new FPDF('P','mm',"A4");
$pdf->AddPage();
$pdf->SetFont('Arial','B', 20);

// Add company logo if exists
if (!empty($companyProfileImagePath) && file_exists($companyProfileImagePath)) {
    $imageX = $pdf->GetPageWidth() - 40;
    $pdf->Image($companyProfileImagePath, $imageX, 10, 30);
}

$pdf->Cell(59 ,20,'',0,1);
$pdf->Cell(190, 10, 'Product Transfer', 1, 0, 'C');
$pdf->Cell(71,5,'',0,0);
$pdf->Cell(59 ,10,'',0,1);

$pdf->SetFont('Arial','', 13);
$pdf->Cell(59 ,5,'',0,1);

$pdf->Cell(59, 5, 'Company Name : ' . $companyName, 0, 0);
$pdf->Cell(75 ,7,'',0,);
$pdf->Cell(10, 5, 'Received by: ', 0, 0);
$pdf->Cell(59 ,7,'',0,1);

$pdf->Cell(59 ,5,'Location             : ' . $selectedLocation,0,0);
$pdf->Cell(75 ,5,'',0,0);
$pdf->Cell(150, 5, 'Received date: ', 0, 0);
$pdf->Cell(59 ,7,'',0,1);

$pdf->Cell(59 ,5,'Tel No                : 016-7195325' ,0,0);
$pdf->Cell(59 ,7,'',0,1);

// Date range text
if (!empty($date1) && !empty($date2)) {  // Removed extra parenthesis
    $dateText = 'Date                   : ' . $date1 . ' to ' . $date2;
} else {
    // If date1 and date2 are not selected, retrieve oldest and newest dates
    $dateRangeQuery = "SELECT MIN(DATE_FORMAT(addeddate, '%Y-%m-%d')) AS oldestDate, 
                              MAX(DATE_FORMAT(addeddate, '%Y-%m-%d')) AS newestDate 
                       FROM deliverorder";
    $dateRangeResult = mysqli_query($conn, $dateRangeQuery);
    
    if ($dateRangeResult && mysqli_num_rows($dateRangeResult) > 0) {
        $dateRangeData = mysqli_fetch_assoc($dateRangeResult);  // This is line 75
        $oldestDate = $dateRangeData['oldestDate'] ?? 'N/A';
        $newestDate = $dateRangeData['newestDate'] ?? 'N/A';
        $dateText = 'Date                   : ' . $oldestDate . ' to ' . $newestDate;
    } else {
        $dateText = 'Date                   : N/A';
    }
}

$pdf->Cell(59, 5, $dateText, 0, 0);
$pdf->Cell(59, 5, '', 0, 1);

$pdf->SetFont('Arial','B', 11);
$pdf->Cell(59 ,5,'',0,1);

// Table headers
$pdf->Cell(30 ,6,'Tracking ID' ,1,0,'C');
$pdf->Cell(25 ,6,'Product ID' ,1,0,'C');
$pdf->Cell(35 ,6,'Product Name' ,1,0,'C');
$pdf->Cell(20 ,6,'Quantity' ,1,0,'C');
$pdf->Cell(20 ,6,'Unit Price' ,1,0,'C');
$pdf->Cell(30 ,6,'Location' ,1,0,'C');
$pdf->Cell(30 ,6,'Transfered Date' ,1,1,'C');

$pdf->SetFont('Arial','', 10);

// Build query based on filters
$whereClause = [];
$params = [];
$types = '';

if (!empty($date1) && !empty($date2)) {
    $whereClause[] = "STR_TO_DATE(`addeddate`, '%Y-%m-%d') BETWEEN ? AND ?";
    $params[] = $date1;
    $params[] = $date2;
    $types .= 'ss';
}

if (!empty($selectedLocation)) {
    $whereClause[] = "location = ?";
    $params[] = $selectedLocation;
    $types .= 's';
}

$query = "SELECT * FROM deliverorder";
if (!empty($whereClause)) {
    $query .= " WHERE " . implode(" AND ", $whereClause);
}
$query .= " ORDER BY `id` DESC";

// Execute query with prepared statement
$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($reportRow = mysqli_fetch_assoc($result)) {
    $pdf->Cell(30, 6, $reportRow['id'], 1, 0, 'C');
    $pdf->Cell(25, 6, $reportRow['productid'], 1, 0, 'C');
    $pdf->Cell(35, 6, $reportRow['name'], 1, 0, 'C');
    $pdf->Cell(20, 6, $reportRow['quantity'], 1, 0, 'C');
    $pdf->Cell(20, 6, $reportRow['unitprice'], 1, 0, 'C');
    $pdf->Cell(30, 6, $reportRow['location'], 1, 0, 'C');
    $pdf->Cell(30, 6, date('Y-m-d', strtotime($reportRow['addeddate'])), 1, 0, 'C');
    $pdf->Ln();
}

$pdf->SetFont('Arial','B', 14);
$pdf->Ln(30);
$pdf->Cell(50, 5, 'Authorised Signature(s)', 0, 0);
$pdf->SetFont('Arial','', 12);
$pdf->Ln(20);

$pdf->Line(10, $pdf->GetY(), 60, $pdf->GetY());
$pdf->Line(150, $pdf->GetY(), $pdf->GetPageWidth() - 10, $pdf->GetY());
$pdf->Ln();

$pdf->Cell(59, 5, 'HQ', 0, 0);
$pdf->Cell(80, 5, '', 0, 0);
$pdf->Cell(50, 5, 'Received by', 0, 1);

// Output the PDF directly to browser
$pdf->Output('D', 'Product_Transfer_Report.pdf');
exit();