<?php
session_start();
include "database.php";

// Check if admin is logged in
if (!isset($_SESSION["username"])) {
    echo '<script>
            alert("Please login first");
            window.location.href = "index.php";
          </script>';
    exit;
}

// Check if company ID is provided
if (isset($_GET['cid'])) {
    $cid = intval($_GET['cid']); // prevent SQL injection

    // First, check if the company exists inside the database
    $check_sql = "SELECT * FROM company WHERE cid = $cid";
    $check_result = mysqli_query($con, $check_sql);

    if ($check_result && mysqli_num_rows($check_result) > 0) {
        $company_data = mysqli_fetch_assoc($check_result);
        $company_username = mysqli_real_escape_string($con, $company_data['username']);

        // 1. Delete job applications for jobs posted by this company
        $delete_apps = "DELETE FROM jobapplication WHERE job_id IN (SELECT id FROM jobs WHERE company_id = $cid)";
        mysqli_query($con, $delete_apps);

        // 2. Delete jobs posted by this company
        $delete_jobs = "DELETE FROM jobs WHERE company_id = $cid";
        mysqli_query($con, $delete_jobs);

        // 3. Delete from the main registration profile (companyregister) to prevent duplicate errors later
        if (!empty($company_username)) {
            $delete_register = "DELETE FROM companyregister WHERE username = '$company_username'";
            mysqli_query($con, $delete_register);
        }

        // 4. Finally, delete the company credentials from login table
        $delete_sql = "DELETE FROM company WHERE cid = $cid";
        
        if (mysqli_query($con, $delete_sql)) {
            echo '<script>
                    alert("Company and all associated records deleted successfully!");
                    window.location.href = "adminviewcompany.php";
                  </script>';
            exit;
        } else {
            echo '<script>
                    alert("Error executing database logic: ' . mysqli_error($con) . '");
                    window.location.href = "adminviewcompany.php";
                  </script>';
            exit;
        }
    } else {
        echo '<script>
                alert("Target company records not found!");
                window.location.href = "adminviewcompany.php";
              </script>';
        exit;
    }
} else {
    echo '<script>
            alert("Invalid request context or missing parameters!");
            window.location.href = "adminviewcompany.php";
          </script>';
    exit;
}
?>