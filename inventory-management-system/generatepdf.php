<?php
session_start();
require_once "config.php";
require_once "fpdf/fpdf186/fpdf.php";



$companyId = 1; 
$query = "SELECT * FROM companyprofile WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $companyId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$companyProfile = mysqli_fetch_assoc($result);
$companyProfileImagePath = $companyProfile['profilepicture'];
$companyName = $companyProfile['name'];


$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 20);

if (!empty($companyProfileImagePath)) {
    // Set the position for the logo
    $pdf->SetXY(10, $pdf->GetY());
    
    // Add the logo to the PDF
    $imageX = $pdf->GetPageWidth() - 120; // Adjust as needed

    // Set the position for the logo
    $pdf->SetXY($imageX, $pdf->GetY());
    
    // Add the logo to the PDF
    $pdf->Image($companyProfileImagePath, $pdf->GetX(), $pdf->GetY(), 30);
    
    // Move the position after adding the logo
    $pdf->SetXY($pdf->GetX() + 30, $pdf->GetY());
    
    // Set the position for the text
    $pdf->SetXY($imageX, $pdf->GetY());
    
    // Move the position after adding the logo
    $pdf->SetXY($pdf->GetX() + 30, $pdf->GetY());
} else {
    // If the logo path is empty, you can display a placeholder image
    $pdf->Cell(30, 0, ''); // Empty cell as a placeholder
}
$pdf->Cell(59 ,20,'',0,1);

$pdf->Cell(190, 10, 'Inventory', 1, 0, 'C');
$pdf->Cell(71,5,'',0,0);
$pdf->Cell(59 ,10,'',0,1);

$pdf->SetFont('Arial','', 11);

$pdf->Cell(59 ,5,'',0,1);

$pdf->Cell(59, 5, 'Company Name : ' . $companyName, 0, 0);
$pdf->Cell(75 ,7,'',0,);


$pdf->Cell(59 ,7,'',0,1);
$pdf->Cell(59, 5, 'Date                   : ' . date('d-m-Y'), 0, 0);
$pdf->Cell(75 ,5,'',0,0);



$pdf->Cell(59 ,7,'',0,1);

$pdf->Cell(59 ,5,'Tel No                : 016-7195325' ,0,0);
$pdf->Cell(59 ,7,'',0,1);

$result = "SELECT * FROM inventory ORDER by id";
$sql = $conn->query($result);




$pdf->SetFont('Arial','B', 11);
// Table Header
$pdf->Cell(20, 10, 'ID', 1);
$pdf->Cell(50, 10, 'Name', 1);
$pdf->Cell(20, 10, 'Quantity', 1);
$pdf->Cell(30, 10, 'UnitPrice(RM)', 1);
$pdf->Cell(20, 10, 'Variant', 1);
$pdf->Cell(30, 10, 'Category', 1);
$pdf->Cell(20, 10, 'Status', 1);
$pdf->Ln(); // Move to the next line after the table header

$pdf->SetFont('Arial','', 11);
while ($row = $sql->fetch_object()) {
    $id = $row->id;
    $name = $row->name;
    $quantity = $row->quantity;
    $unitprice = $row->unitprice;
    $variant = $row->variant;
    $description = $row->description;
    $category = $row->category;
    $status = $row->status;
    $image = $row->image;

    $pdf->Cell(20, 10, $id, 1);
    $pdf->Cell(50, 10, $name, 1);
    $pdf->Cell(20, 10, $quantity, 1);
    $pdf->Cell(30, 10, $unitprice, 1);
    $pdf->Cell(20, 10, $variant, 1);
    $pdf->Cell(30, 10, $category, 1);
    $pdf->Cell(20, 10, $status, 1);
    
    $pdf->Ln();
}

$pdf->Output();
?>
