<?php
// Assuming you have a database connection named $conn
require 'config.php';
// Check if the connection is successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Query to select products added in the last 5 days
$sql = "SELECT id, name, quantity FROM inventory WHERE DATE(addeddate) >= CURDATE() - INTERVAL 5 DAY";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['quantity'] . "</td>";
        echo "</tr>";
    }
} else {
    echo '
    <tr>
        <td colspan="5"><center>No recently added product for past 5 day</center></td>
    </tr>';
}

// Close the database connection
mysqli_close($conn);
?>
