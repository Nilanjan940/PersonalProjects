<?php
session_start();
require_once "config.php";

if (isset($_POST['addorder'])) {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $quantity = $_POST['quantity'];
    $unit_price = $_POST['unit_price'];
    $location = $_POST['location'];

    // Validate input if needed

    // Check if the entered quantity is valid
    $checkQuantitySql = "SELECT quantity FROM inventory WHERE id = ?";
    $checkQuantityStmt = $conn->prepare($checkQuantitySql);

    if (!$checkQuantityStmt) {
        die("Error in prepare statement: " . $conn->error);
    }

    // Bind parameters for the select statement
    $checkQuantityStmt->bind_param("s", $product_id);

    // Execute the select statement
    if ($checkQuantityStmt->execute()) {
        $checkQuantityStmt->bind_result($availableQuantity);
        $checkQuantityStmt->fetch();

        if ($quantity == 0) {
            // Show an error message if the entered quantity is 0
            echo '<script>alert("Error: Quantity cannot be 0. Please enter a valid quantity."); window.location.href = "Deliver_order.php";</script>';
        } elseif ($quantity > $availableQuantity) {
            // Show an error message if the entered quantity exceeds available quantity
            echo '<script>alert("Error: Quantity entered exceeds available quantity in inventory."); window.location.href = "Deliver_order.php";</script>';
        } else {
            // Close the check quantity statement
            $checkQuantityStmt->close();

            // Update the inventory table
            $updateSql = "UPDATE inventory SET quantity = quantity - ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);

            if (!$updateStmt) {
                die("Error in prepare statement: " . $conn->error);
            }

            // Bind parameters for the update statement
            $updateStmt->bind_param("is", $quantity, $product_id);

            // Execute the update statement
            if ($updateStmt->execute()) {
                // Insert into the deliverorder table
                $insertSql = "INSERT INTO deliverorder (productid, name, quantity, unitprice, location) VALUES (?, ?, ?, ?, ?)";
                $insertStmt = $conn->prepare($insertSql);

                if (!$insertStmt) {
                    die("Error in prepare statement: " . $conn->error);
                }

                // Bind parameters for the insert statement
                $insertStmt->bind_param("ssids", $product_id, $product_name, $quantity, $unit_price, $location);

                // Execute the insert statement
                if ($insertStmt->execute()) {
                    // Insert into the report table
                    $actionDescription = "Transfer product to " . $location;
                    $status = "Active";

                    $stmtInsertReport = $conn->prepare("INSERT INTO report (id, name, quantity, unitprice, description, category, variant, status, image, Action, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");

                    if (!$stmtInsertReport) {
                        die("Error in prepare statement: " . $conn->error);
                    }

                    $stmtInsertReport->bind_param("ssdsbsssss", $product_id, $product_name, $quantity, $unit_price, $actionDescription, $actionDescription, $actionDescription, $status, $actionDescription, $actionDescription);

                    if ($stmtInsertReport->execute()) {
                        // Insert successful
                        $username = $_SESSION['username'];
                        $addAction = "Transfer " . $quantity . " of " . $product_name . " to " . $location;
                        $auditTrailQuery = "INSERT INTO audittrails (datetime, username, action) VALUES (CURRENT_TIMESTAMP, ?, ?)";
                        $stmtAuditTrail = $conn->prepare($auditTrailQuery);

                        if ($stmtAuditTrail) {
                            $stmtAuditTrail->bind_param("ss", $username, $addAction);

                            if ($stmtAuditTrail->execute()) {
                                echo '<script>alert("Transfered product successfully!"); window.location.href = "Deliver_order.php";</script>';
                            } else {
                                echo "Error adding order to audittrails table: " . $stmtAuditTrail->error;
                            }

                            // Close the statement for audittrails table
                            $stmtAuditTrail->close();
                        } else {
                            echo "Error in prepare statement: " . $conn->error;
                        }
                    } else {
                        echo "Error adding order to report table: " . $stmtInsertReport->error;
                    }

                    // Close the insert statement for report table
                    $stmtInsertReport->close();
                } else {
                    echo "Error adding order to deliverorder table: " . $insertStmt->error;
                }

                // Close the insert statement for deliverorder table
                $insertStmt->close();
            } else {
                echo "Error updating inventory: " . $updateStmt->error;
            }

            // Close the update statement for inventory table
            $updateStmt->close();
        }
    } else {
        // Select statement execution failed
        echo "Error checking quantity: " . $conn->error;
    }

    // Close the check quantity statement
    $checkQuantityStmt->close();
}
?>






