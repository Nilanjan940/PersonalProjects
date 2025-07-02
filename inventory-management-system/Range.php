<?php
require 'config.php';



if (isset($_POST['search'])) {
    $date1 = date("Y-m-d", strtotime($_POST['date1']));
    $date2 = date("Y-m-d", strtotime($_POST['date2']));

    $reportQuery = mysqli_query($conn, "SELECT * FROM report WHERE STR_TO_DATE(`date`, '%Y-%m-%d') BETWEEN '$date1' AND '$date2' ORDER BY `mainid` ASC") or die(mysqli_error());
    $reportRow = mysqli_num_rows($reportQuery);

    if ($reportRow > 0) {
        while ($reportFetch = mysqli_fetch_array($reportQuery)) {
            ?>
            <tr>
                <td><?php echo $reportFetch['id'] ?></td>
                <td><?php echo $reportFetch['name'] ?></td>
                <td><?php echo $reportFetch['quantity'] ?></td>
                <td><?php echo $reportFetch['Action'] ?></td>
                <td><?php echo $reportFetch['status'] ?></td>
                <td><?php echo $reportFetch['date'] ?></td>
            </tr>
            <?php
        }
    } else {
        echo '
        <tr>
            <td colspan="6"><center>Record Not Found</center></td>
        </tr>';
    }
} else {
    // Fetch products from the report table, ordered by date in descending order
    $reportQuery = mysqli_query($conn, "SELECT * FROM report ORDER BY mainid DESC") or die(mysqli_error($conn));
    $reportRow = mysqli_num_rows($reportQuery);

    if ($reportRow > 0) {
        while ($reportFetch = mysqli_fetch_array($reportQuery)) {
            ?>
            <tr>
                <td><?php echo $reportFetch['id'] ?></td>
                <td><?php echo $reportFetch['name'] ?></td>
                <td><?php echo $reportFetch['quantity'] ?></td>
                <td><?php echo $reportFetch['Action'] ?></td>
                <td><?php echo $reportFetch['status'] ?></td>
                <td><?php echo $reportFetch['date'] ?></td>
            </tr>
            <?php
        }
    } else {
        echo '
        <tr>
            <td colspan="6"><center>Record Not Found</center></td>
        </tr>';
    }
}
?>




