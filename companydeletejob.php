<?php
session_start();
include "database.php"; // DB connection

if (!isset($_SESSION["username"])) {
    echo '<script>
            alert("Please login first");
            window.location.href = "index.php";
          </script>';
    exit;
}

$username = mysqli_real_escape_string($con, $_SESSION["username"]);

if (isset($_GET['id'])) {
    $jobid = intval($_GET['id']); 

    // ✅ Secure delete statement matching active authenticated account session
    $sql = "DELETE FROM jobs WHERE id = $jobid AND username = '$username'";

    if (mysqli_query($con, $sql)) {
        echo "<script>
                alert('Job circular deleted successfully from systems!');
                window.location.href = 'companyhomepage.php';
              </script>";
        exit;
    } else {
        $err = mysqli_error($con);
        echo "<script>
                alert('Error removing job records from database logs.');
                window.location.href = 'companyhomepage.php';
              </script>";
    }
} else {
    echo "<script>
            alert('Invalid parameter request identifier supplied.');
            window.location.href = 'companyhomepage.php';
          </script>";
}
?>