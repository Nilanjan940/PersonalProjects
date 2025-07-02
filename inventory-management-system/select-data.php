<?php
require_once "config.php";

$catname = $_POST['cat_name'];

if ($catname != 'All') {
    $cond = mysqli_real_escape_string($conn, $catname); // Prevent SQL injection
    $query = "SELECT * FROM inventory WHERE category = $cond";
} else {
    $query = "SELECT * FROM inventory";
}

$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Output your product details here
        echo "ID: " . $row['id'] . "<br>";
        echo "Name: " . $row['name'] . "<br>";
        // Add more fields as needed
        echo "<hr>";
    }

    // Close the result set
    mysqli_free_result($result);
} else {
    // Handle the case when there is an error in the query
    echo "Error: " . mysqli_error($conn);
}

// Close the database connection
mysqli_close($conn);
?>
