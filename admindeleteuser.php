<?php
session_start();
include "database.php";

// Check if admin is logged in
if (!isset($_SESSION["username"])) {
    echo "<script>
            alert('Please login first!');
            window.location.href = 'index.php';
          </script>";
    exit;
}

// Check if user ID is provided
if (isset($_GET['uid'])) {
    $uid = intval($_GET['uid']); // prevent SQL injection

    // First, check if the user exists inside the database
    $check_sql = "SELECT * FROM user WHERE uid = $uid";
    $check_result = mysqli_query($con, $check_sql);

    if ($check_result && mysqli_num_rows($check_result) > 0) {
        $user_data = mysqli_fetch_assoc($check_result);
        $user_username = mysqli_real_escape_string($con, $user_data['username']);

        $delete_apps = "DELETE FROM jobapplication WHERE user_id = $uid";
        mysqli_query($con, $delete_apps);

        if (!empty($user_username)) {
            $delete_register = "DELETE FROM userregister WHERE username = '$user_username'";
            mysqli_query($con, $delete_register);
        }

        $delete_sql = "DELETE FROM user WHERE uid = $uid";
        
        if (mysqli_query($con, $delete_sql)) {
            echo "<script>
                    alert('User and all associated application records deleted successfully.');
                    window.location.href = 'adminviewuser.php';
                  </script>";
            exit;
        } else {
            echo "<script>
                    alert('Error executing delete logic: " . mysqli_error($con) . "');
                    window.location.href = 'adminviewuser.php';
                  </script>";
            exit;
        }
    } else {
        echo "<script>
                alert('Target user records not found inside database.');
                window.location.href = 'adminviewuser.php';
              </script>";
        exit;
    }
} else {
    echo "<script>
            alert('Invalid request parameters.');
            window.location.href = 'adminviewuser.php';
          </script>";
    exit;
}
?>