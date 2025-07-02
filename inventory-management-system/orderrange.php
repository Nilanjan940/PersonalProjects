<?php
require_once "config.php";


if (isset($_POST['search'])) {
    $date1 = date("Y-m-d", strtotime($_POST['date1']));
    $date2 = date("Y-m-d", strtotime($_POST['date2']));

    $reportQuery = mysqli_query($conn, "SELECT * FROM deliverorder WHERE STR_TO_DATE(`addeddate`, '%Y-%m-%d') BETWEEN '$date1' AND '$date2' ORDER BY `id` DESC") or die(mysqli_error($conn));
    $reportRow = mysqli_num_rows($reportQuery);

    if ($reportRow > 0) {
        while ($reportFetch = mysqli_fetch_array($reportQuery)) {
            $pdfData[] = $reportFetch;
            ?>
            <tr>
                <td><?php echo $reportFetch['id'] ?></td>
                <td><?php echo $reportFetch['productid'] ?></td>
                <td><?php echo $reportFetch['name'] ?></td>
                <td><?php echo $reportFetch['quantity'] ?></td>
                <td><?php echo $reportFetch['unitprice'] ?></td>
                <td><?php echo $reportFetch['location'] ?></td>
                <td><?php echo $reportFetch['addeddate'] ?></td>
            </tr>
            <?php
        }
    } else {
        echo '
        <tr>
            <td colspan="7"><center>Record Not Found</center></td>
        </tr>';
    }
} else {
    // Fetch products from the report table, ordered by tracking ID in descending order
    $reportQuery = mysqli_query($conn, "SELECT * FROM deliverorder ORDER BY `id` DESC") or die(mysqli_error($conn));

    if (mysqli_num_rows($reportQuery) > 0) {
        while ($reportFetch = mysqli_fetch_array($reportQuery)) {
            $pdfData[] = $reportFetch;
            ?>
            <tr>
                <td><?php echo $reportFetch['id'] ?></td>
                <td><?php echo $reportFetch['productid'] ?></td>
                <td><?php echo $reportFetch['name'] ?></td>
                <td><?php echo $reportFetch['quantity'] ?></td>
                <td><?php echo $reportFetch['unitprice'] ?></td>
                <td><?php echo $reportFetch['location'] ?></td>
                <td><?php echo $reportFetch['addeddate'] ?></td>
            </tr>
            <?php
        }
    } else {
        echo '
        <tr>
            <td colspan="7"><center>Record Not Found</center></td>
        </tr>';
    }
}

?>

