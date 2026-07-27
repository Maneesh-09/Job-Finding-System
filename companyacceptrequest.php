<?php
session_start();
include "database.php"; // DB connection

if (!isset($_SESSION['username'])) {
    echo "<script>
            alert('Please login first!');
            window.location.href = 'index.php';
          </script>";
    exit;
}

$username = mysqli_real_escape_string($con, $_SESSION['username']);

// ✅ Step 1: Get the current company's cid using the session username safely
$cid = null;
$getCompanyQuery = "SELECT cid FROM company WHERE username = '$username' LIMIT 1";
$getCompanyResult = mysqli_query($con, $getCompanyQuery);

if ($getCompanyResult && mysqli_num_rows($getCompanyResult) === 1) {
    $companyData = mysqli_fetch_assoc($getCompanyResult);
    $cid = intval($companyData['cid']);
} else {
    echo "<script>
            alert('Corporate identity not found inside database logs.');
            window.location.href = 'companyrecivedrequest.php';
          </script>";
    exit;
}

if (isset($_POST['accept'])) {
    // Sanitizing the input parameters
    $application_id = isset($_POST['application_id']) ? mysqli_real_escape_string($con, $_POST['application_id']) : '';
    $job_id         = isset($_POST['job_id']) ? mysqli_real_escape_string($con, $_POST['job_id']) : '';

    if (empty($application_id) || empty($job_id)) {
        echo "<script>
                alert('Invalid parameters supplied for transaction requests.');
                window.location.href = 'companyrecivedrequest.php';
              </script>";
        exit;
    }

    // ✅ Step 2: Avoid duplicates - check if candidate already moved to approved table
    $check_sql = "SELECT * FROM accepted_application WHERE application_id = '$application_id'";
    $check_result = mysqli_query($con, $check_sql);
    if ($check_result && mysqli_num_rows($check_result) > 0) {
        echo "<script>
                alert('This candidate registry has already been accepted.');
                window.location.href = 'companyrecivedrequest.php';
              </script>";
        exit;
    }

    // ✅ Step 3: Fetch active job application parameters safely
    $app_sql = "SELECT * FROM jobapplication WHERE id = '$application_id' AND job_id = '$job_id'";
    $app_result = mysqli_query($con, $app_sql);

    if ($app_result && mysqli_num_rows($app_result) > 0) {
        $application = mysqli_fetch_assoc($app_result);

        $app_username = mysqli_real_escape_string($con, $application['username'] ?? '');
        $fullname     = mysqli_real_escape_string($con, $application['fullname']);
        $email        = mysqli_real_escape_string($con, $application['email']);
        $phone        = mysqli_real_escape_string($con, $application['phone']);
        $address      = mysqli_real_escape_string($con, $application['address']);
        $skills       = mysqli_real_escape_string($con, $application['skills']);
        $experiences  = mysqli_real_escape_string($con, $application['experiences']);
        $photo        = mysqli_real_escape_string($con, $application['photo']);
        $cv           = mysqli_real_escape_string($con, $application['cv']);

        // ✅ Step 4: Map parameters to modern corporate talent pool
        $insert_sql = "INSERT INTO accepted_application 
            (application_id, job_id, fullname, email, phone, address, skills, experiences, photo, cv, accepted_at, username, cid)
            VALUES 
            ('$application_id', '$job_id', '$fullname', '$email', '$phone', '$address', '$skills', '$experiences', '$photo', '$cv', NOW(), '$app_username', '$cid')";

        if (mysqli_query($con, $insert_sql)) {
            
            // ✅ Step 5: Cleanse the entry from pending queue logs
            $delete_sql = "DELETE FROM jobapplication WHERE id = '$application_id'";
            if (mysqli_query($con, $delete_sql)) {
                echo "<script>
                        alert('Candidate application successfully approved and hired.');
                        window.location.href = 'companyrecivedrequest.php';
                      </script>";
            } else {
                echo "<script>
                        alert('Candidate approved, but failed to clean queue records.');
                        window.location.href = 'companyrecivedrequest.php';
                      </script>";
            }
        } else {
            $err = mysqli_error($con);
            echo "<script>
                    alert('System Error mapping application data: $err');
                    window.history.back();
                  </script>";
        }
    } else {
        echo "<script>
                alert('Targeted job seeker profile details not located.');
                window.location.href = 'companyrecivedrequest.php';
              </script>";
    }
} else {
    header("Location: companyrecivedrequest.php");
    exit;
}
?>