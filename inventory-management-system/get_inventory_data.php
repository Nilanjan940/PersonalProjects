<?php
require_once "config.php"; // Include your database connection file

$query = "SELECT id, name FROM inventory"; // Add more columns as needed
$result = mysqli_query($conn, $query);

$data = array();

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);

mysqli_close($conn);
?>
