<?php
// Assuming you have a database connection named $conn
require 'config.php';
// Query to select products with quantity less than 10
$sql = "SELECT id, name, quantity FROM inventory WHERE quantity < 10";
$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        $quantityColor = ($row['quantity'] < 10) ? 'color: red;' : '';
        echo "<td style='$quantityColor'>" . $row['quantity'] . "</td>";
        echo "</tr>";
    }
} else {
    echo '
    <tr>
        <td colspan="5"><center>Record Not Found</center></td>
    </tr>';
}

// Close the database connection
mysqli_close($conn);


?>