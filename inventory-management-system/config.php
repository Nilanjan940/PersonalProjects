<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventorymanagementsystem";
$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    echo "connection failed!";
}

// Check if the function exists before defining it
if (!function_exists('count_by_id')) {
    function count_by_id($table)
    {
        global $conn;

        $query = "SELECT COUNT(*) AS count FROM $table";
        $result = mysqli_query($conn, $query);

        if (!$result) {
            die("Query failed: " . mysqli_error($conn));
        }

        $row = mysqli_fetch_assoc($result);
        return $row['count'];
    }
}

?>
