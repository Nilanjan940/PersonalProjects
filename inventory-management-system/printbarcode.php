<?php
session_start();
require_once "config.php";

isset($_SESSION['login']) && $_SESSION['login']===true ? '' : header("Location:Login.php");

$companyId = 1; 
$query = "SELECT * FROM companyprofile WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $companyId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$companyProfile = mysqli_fetch_assoc($result);
$companyProfileImagePath = $companyProfile['profilepicture'];
$companyName = $companyProfile['name'];

// Get product details
$eid = isset($_GET['barcodeid']) ? $_GET['barcodeid'] : '';
$product = [];
if($eid) {
    $sql = mysqli_query($conn, "SELECT * FROM inventory WHERE id='$eid'");
    $product = mysqli_fetch_array($sql);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Barcode - <?php echo $companyName; ?></title>
    <link rel="stylesheet" href="testin3.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .barcode-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .barcode-preview {
            text-align: center;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
            margin: 20px auto;
            max-width: 300px;
        }
        .barcode-title {
            font-size: 16px;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .barcode-id {
            font-size: 14px;
            margin-top: 5px;
        }
        .barcode-price {
            font-size: 18px;
            font-weight: bold;
            margin-top: 5px;
        }
        #print-area {
            display: none;
        }
        .print-barcode {
            width: 2in;
            height: 1in;
            padding: 5px;
            margin: 5px;
            display: inline-block;
            page-break-inside: avoid;
        }
    </style>
</head>

<body>
<nav>
    <div class="logo">
        <div class="logo-image">
            <?php if (!empty($companyProfileImagePath)): ?>
                <img src="<?php echo $companyProfileImagePath; ?>" alt="Company Logo" class="logoimage">
            <?php else: ?>
                <img src="path/to/placeholder/image.jpg" alt="No Image">
            <?php endif; ?>
        </div>
        <div class="logo-name"><?php echo $companyName; ?></div>
    </div>

    <div class="menu-items">
        <ul class="navLinks">
            <li class="navList">
                <a href="Dashboard.php">
                    <ion-icon name="stats-chart"></ion-icon>
                    <span class="links">Dashboard</span>
                </a>
            </li>
            <li class="navList active">
                <a href="Inventory.php">
                    <ion-icon name="file-tray-full"></ion-icon> 
                    <span class="links">Inventory</span>
                </a>
            </li>
            <li class="navList">
                <a href="Product.php">
                    <ion-icon name="add-circle"></ion-icon> 
                    <span class="links">Add Product</span>
                </a>
            </li>
            <li class="navList">
                <a href="Category.php">
                    <ion-icon name="grid"></ion-icon> 
                    <span class="links">Category</span>
                </a>
            </li>  
            <li class="navList">
                <a href="Order.php">
                    <ion-icon name="swap-horizontal"></ion-icon>
                    <span class="links">Product Transfer</span>
                </a>
            </li> 
            <li class="navList">
                <a href="Reports.php">
                    <ion-icon name="reader"></ion-icon>
                    <span class="links">Inventory Journal</span>
                </a>
            </li> 
            <li class="navList">
                <a href="audittrails.php">
                    <ion-icon name="receipt"></ion-icon>
                    <span class="links">Audit trails</span>
                </a>
            </li> 
            <li class="navList">
                <a href="Settings.php">
                    <ion-icon name="cog"></ion-icon> 
                    <span class="links">Settings</span>
                </a>
            </li>          
        </ul>
        <ul class="bottom-link">
            <li>
                <a href="logout.php">
                    <ion-icon name="log-out"></ion-icon>
                    <span class="links">Logout</span>
                </a>
            </li>
        </ul>
    </div>
</nav>

<section class="dashboard">
    <div class="top">
        <ion-icon class="navToggle" name="menu-outline"></ion-icon>
    </div>

    <div class="content-wrapper">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <a href="Inventory.php" class="btn btn-light mb-3">
                        <ion-icon name="arrow-back-outline"></ion-icon> Back to Inventory
                    </a>
                    
                    <div class="barcode-card">
                        <h2><ion-icon name="barcode-outline"></ion-icon> Print Barcode</h2>
                        
                        <?php if(!empty($product)): ?>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Product ID:</label>
                                    <div class="form-control p-2"><?php echo htmlspecialchars($product['id']); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Product Name:</label>
                                    <div class="form-control p-2"><?php echo htmlspecialchars($product['name']); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Unit Price:</label>
                                    <div class="form-control p-2">RM <?php echo number_format($product['unitprice'], 2); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Quantity to Print:</label>
                                    <input type="number" id="quantity" class="form-control" min="1" value="1" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="barcode-preview">
                            <div class="barcode-title"><?php echo htmlspecialchars($product['name']); ?></div>
                            <svg id="barcode-preview-svg"></svg>
                            <div class="barcode-id"><?php echo htmlspecialchars($product['id']); ?></div>
                            <div class="barcode-price">RM <?php echo number_format($product['unitprice'], 2); ?></div>
                        </div>
                        
                        <button id="print-btn" class="btn btn-primary mt-3">
                            <ion-icon name="print-outline"></ion-icon> Print Barcode
                        </button>
                        <?php else: ?>
                        <div class="alert alert-danger">Product not found!</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Hidden print area -->
<div id="print-area"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Generate initial barcode preview
    generateBarcode('barcode-preview-svg', '<?php echo $product['id'] ?? ''; ?>');
    
    // Print button click handler
    document.getElementById('print-btn').addEventListener('click', function() {
        const quantity = parseInt(document.getElementById('quantity').value);
        
        if(isNaN(quantity) || quantity <= 0) {
            alert('Please enter a valid quantity greater than 0.');
            return;
        }
        
        const productId = '<?php echo $product['id'] ?? ''; ?>';
        const productName = '<?php echo $product['name'] ?? ''; ?>';
        const productPrice = '<?php echo isset($product['unitprice']) ? number_format($product['unitprice'], 2) : '0.00'; ?>';
        
        // Clear previous print area
        const printArea = document.getElementById('print-area');
        printArea.innerHTML = '';
        
        // Generate barcodes for printing
        for(let i = 0; i < quantity; i++) {
            const barcodeDiv = document.createElement('div');
            barcodeDiv.className = 'print-barcode';
            barcodeDiv.innerHTML = `
                <div style="text-align: center; font-family: Arial, sans-serif;">
                    <div style="font-size: 10px; margin-bottom: 2px; font-weight: bold;">${productName}</div>
                    <svg id="barcode-${i}" style="margin: 0 auto;"></svg>
                    <div style="font-size: 8px; margin-top: 2px;">${productId}</div>
                    <div style="font-size: 12px; font-weight: bold; margin-top: 2px;">RM ${productPrice}</div>
                </div>
            `;
            printArea.appendChild(barcodeDiv);
            
            // Generate barcode for this item
            generateBarcode(`barcode-${i}`, productId);
        }
        
        // Generate PDF
        const options = {
            margin: 10,
            filename: `barcode_${productId}.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { 
                scale: 2,
                logging: false,
                useCORS: true,
                allowTaint: true
            },
            jsPDF: { 
                unit: 'mm', 
                format: 'a4', 
                orientation: 'portrait',
                compress: true
            }
        };
        
        // Show loading indicator
        const printBtn = document.getElementById('print-btn');
        const originalText = printBtn.innerHTML;
        printBtn.innerHTML = '<ion-icon name="hourglass-outline"></ion-icon> Generating...';
        printBtn.disabled = true;
        
        // Generate PDF
        html2pdf().from(printArea).set(options).save().then(() => {
            // Restore button state
            printBtn.innerHTML = originalText;
            printBtn.disabled = false;
        });
    });
});

function generateBarcode(elementId, barcodeValue) {
    if(!barcodeValue) return;
    
    JsBarcode(`#${elementId}`, barcodeValue, {
        format: "CODE128",
        lineColor: "#000",
        width: 1.5,
        height: 40,
        displayValue: false,
        margin: 5
    });
}
</script>
</body>
</html>