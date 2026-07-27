<?php
session_start();
include "database.php"; // DB connection

// Check if admin is logged in
if (!isset($_SESSION["username"])) {
    echo "<script>
            alert('Please login first!');
            window.location.href = 'index.php';
          </script>";
    exit;
}

// Check if job_id is provided
if (isset($_GET['job_id'])) {
    $job_id = intval($_GET['job_id']); // prevent SQL injection

    // Check if the job exists
    $check_sql = "SELECT * FROM jobs WHERE id = $job_id";
    $check_result = mysqli_query($con, $check_sql);

    if ($check_result && mysqli_num_rows($check_result) > 0) {
        
        // १. यो जबसँग सम्बन्धित सबै आवेदनहरू (Job Applications) लाई पहिले हटाउने
        $delete_apps_sql = "DELETE FROM jobapplication WHERE job_id = $job_id";
        mysqli_query($con, $delete_apps_sql);

        // २. मुख्य jobs टेबलबाट जब डिलिट गर्ने
        $delete_job_sql = "DELETE FROM jobs WHERE id = $job_id";
        
        if (mysqli_query($con, $delete_job_sql)) {
            echo "<script>
                    alert('Job post and all associated applications deleted successfully.');
                    window.location.href = 'adminviewjobs.php';
                  </script>";
            exit;
        } else {
            echo "<script>
                    alert('Error executing delete logic: " . mysqli_error($con) . "');
                    window.location.href = 'adminviewjobs.php';
                  </script>";
            exit;
        }
    } else {
        echo "<script>
                alert('Target job posting not found inside database.');
                window.location.href = 'adminviewjobs.php';
              </script>";
        exit;
    }
} else {
    echo "<script>
            alert('Invalid request parameters.');
            window.location.href = 'adminviewjobs.php';
          </script>";
    exit;
}
?>