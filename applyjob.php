<?php
session_start(); // Start session to get logged-in user
include "database.php"; // Database connection

if (!isset($_SESSION['username'])) {
    echo "<script>alert('Please login first!'); window.location='index.php';</script>";
    exit;
}

$username = $_SESSION['username']; // Current logged-in username

// Initialize variables
$error1 = $error2 = $error3 = $error4 = $error5 = $error6 = $error7 = $error8 = '';
$fullname = $email = $phone = $address = $skills = $experiences = '';
$job_id = $_GET['job_id'] ?? ''; // Get job ID from URL

if (isset($_POST["apply"])) {
    $job_id = mysqli_real_escape_string($con, $_POST["job_id"]);
    $fullname = trim(mysqli_real_escape_string($con, $_POST["fullname"]));
    $email = trim(mysqli_real_escape_string($con, $_POST["email"]));
    $phone = trim(mysqli_real_escape_string($con, $_POST["phone"]));
    $address = trim(mysqli_real_escape_string($con, $_POST["address"]));
    $skills = trim(mysqli_real_escape_string($con, $_POST["skills"]));
    $experiences = trim(mysqli_real_escape_string($con, $_POST["experiences"]));

    // File upload
    $cv = $_FILES["cv"]["name"];
    $photo = $_FILES["photo"]["name"];
    $cv_tmp = $_FILES["cv"]["tmp_name"];
    $photo_tmp = $_FILES["photo"]["tmp_name"];

    $upload_dir_cv = "cvs/";
    $upload_dir_photo = "photos/";

    if (!is_dir($upload_dir_cv)) mkdir($upload_dir_cv, 0777, true);
    if (!is_dir($upload_dir_photo)) mkdir($upload_dir_photo, 0777, true);

    $cv_path = $upload_dir_cv . basename($cv);
    $photo_path = $upload_dir_photo . basename($photo);

    // Validation
    if (empty($fullname)) $error1 = "*Full name is required";
    if (empty($email)) $error2 = "*Email is required";
    if (empty($phone)) $error3 = "*Phone number is required";
    if (empty($address)) $error4 = "*Address is required";
    if (empty($skills)) $error5 = "*Skills field is required";
    if (empty($experiences)) $error6 = "*Experience field is required";
    if (empty($cv)) $error7 = "*Please upload your CV";
    if (empty($photo)) $error8 = "*Please upload your photo";

    // Proceed if no errors
    if (empty($error1) && empty($error2) && empty($error3) && empty($error4) &&
        empty($error5) && empty($error6) && empty($error7) && empty($error8)) {

        // Upload files
        if (!move_uploaded_file($cv_tmp, $cv_path)) {
            echo "<script>alert('Failed to upload CV.');</script>";
        }
        if (!move_uploaded_file($photo_tmp, $photo_path)) {
            echo "<script>alert('Failed to upload photo.');</script>";
        }

        $sql = "INSERT INTO jobapplication
                (job_id, fullname, email, phone, address, skills, experiences, cv, photo, applied_date, username)
                VALUES
                ('$job_id', '$fullname', '$email', '$phone', '$address', '$skills', '$experiences', '$cv', '$photo', NOW(), '$username')";

        $res = mysqli_query($con, $sql);

        if ($res) {
            echo "<script>alert('Job Application Submitted Successfully!'); window.location='userviewjobs.php';</script>";
            exit;
        } else {
            echo "<script>alert('Database Error: " . mysqli_error($con) . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Apply Job</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8f9fa; color: #333; display: flex; flex-direction: column; min-height: 100vh; }

        /* Modern Top Navbar */
        .navbar { background: #ffffff; border-bottom: 1px solid #e1e4e8; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
        .logo-area h1 { font-size: 20px; color: #111; font-weight: 700; }
        .logo-area span { color: #00b074; }
        
        .profile-section { display: flex; align-items: center; gap: 15px; }
        .user-badge { background: #eefbf7; color: #00b074; font-weight: 700; padding: 6px 14px; border-radius: 20px; font-size: 13.5px; border: 1px solid rgba(0, 176, 116, 0.2); }
        
        .btn-logout { background: #fff; border: 1px solid #de3e3e; color: #de3e3e; padding: 6px 16px; border-radius: 6px; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: 0.3s; }
        .btn-logout:hover { background: #de3e3e; color: #fff; }

        /* Dashboard Main Layout */
        .dashboard-layout { display: flex; flex: 1; }

        /* Sidebar Design */
        .sidebar { width: 280px; background: #ffffff; border-right: 1px solid #e2e8f0; padding: 30px 15px; }
        .nav-links { list-style: none; display: flex; flex-direction: column; gap: 6px; }
        .nav-links li a { display: block; padding: 12px 16px; color: #555; font-size: 14.5px; font-weight: 600; text-decoration: none; border-radius: 8px; transition: 0.3s; }
        .nav-links li a:hover { color: #00b074; background: #eefbf7; }
        .nav-links li a.active { background: #00b074; color: white !important; }

        /* Content Wrapper */
        .main-content { flex: 1; padding: 40px; background-color: #f8f9fa; display: flex; flex-direction: column; align-items: center; }
        
        /* Form Card Design */
        .form-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 35px; width: 100%; max-width: 800px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); }
        .form-card h2 { font-size: 22px; font-weight: 700; color: #1e293b; margin-bottom: 8px; border-bottom: 2px solid #eefbf7; padding-bottom: 12px; }
        .form-card p { font-size: 14px; color: #64748b; margin-bottom: 25px; }

        /* Form Layout Grid */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .full-width { grid-column: span 2; }

        /* Input Elements */
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-label { font-size: 14px; font-weight: 600; color: #475569; }
        
        .form-input { padding: 11px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14.5px; outline: none; background-color: #f8fafc; transition: 0.3s; width: 100%; }
        .form-input:focus { border-color: #00b074; background-color: #fff; box-shadow: 0 0 0 3px rgba(0, 176, 116, 0.1); }
        .form-input[readonly] { background-color: #e2e8f0; color: #64748b; cursor: not-allowed; }
        
        textarea.form-input { resize: vertical; font-family: inherit; }

        /* File Upload Inputs */
        input[type="file"].form-input { padding: 8px 12px; background: #fff; cursor: pointer; }

        /* Errors styling */
        .error { color: #de3e3e; font-size: 12.5px; font-weight: 500; margin-top: 2px; }

        /* Form Footer Buttons */
        .form-actions { display: flex; gap: 12px; margin-top: 25px; border-top: 1px solid #e2e8f0; padding-top: 20px; }
        .btn-submit { background: #00b074; color: white; border: none; padding: 12px 30px; border-radius: 8px; font-size: 14.5px; font-weight: 700; cursor: pointer; transition: 0.3s; flex: 1; }
        .btn-submit:hover { background: #009460; }
        
        .btn-back { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 12px 24px; border-radius: 8px; font-size: 14.5px; font-weight: 600; cursor: pointer; text-decoration: none; text-align: center; transition: 0.2s; }
        .btn-back:hover { background: #e2e8f0; color: #1e293b; }

        /* Footer */
        .footer { background: #111; color: #888; padding: 25px 20px; text-align: center; border-top: 1px solid #222; margin-top: auto; width: 100%; }
        .footer p { font-size: 13.5px; }
    </style>
</head>

<body>

    <!-- User Top Navbar -->
    <div class="navbar">
        <div class="logo-area">
            <h1>Online <span>Job Find</span></h1>
        </div>
        <div class="profile-section">
            <span class="user-badge">👤 <?php echo htmlspecialchars($username); ?></span>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </div>

    <!-- Dashboard Body Container -->
    <div class="dashboard-layout">
        
        <!-- Left Sidebar Navigation -->
        <div class="sidebar">
            <ul class="nav-links">
                <li><a href="userhomepage.php">Home</a></li>
                <li><a href="userviewjobs.php">View Jobs</a></li>
                <li><a href="userjobrequeststatus.php">Job Request Status</a></li>
                <li><a href="userprofile.php">Profile</a></li>
                <li><a href="applyjob.php" class="active">Apply Job</a></li>
            </ul>
        </div>

        <!-- Right Form Area -->
        <div class="main-content">
            
            <div class="form-card">
                <h2>Job Application Form</h2>
                <p>Please supply your authentic contact coordinates, key corporate skillsets, and professional validation files.</p>

                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($job_id); ?>">

                    <div class="form-grid">
                        
                        <div class="form-group full-width">
                            <label class="form-label">Account Username</label>
                            <input type="text" class="form-input" value="<?php echo htmlspecialchars($username); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="fullname" class="form-input" value="<?php echo htmlspecialchars($fullname); ?>" placeholder="Enter your full name">
                            <div class="error"><?php echo $error1; ?></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($email); ?>" placeholder="name@example.com">
                            <div class="error"><?php echo $error2; ?></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-input" value="<?php echo htmlspecialchars($phone); ?>" placeholder="98XXXXXXXX">
                            <div class="error"><?php echo $error3; ?></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Technical Core Skills</label>
                            <input type="text" name="skills" class="form-input" value="<?php echo htmlspecialchars($skills); ?>" placeholder="e.g., PHP, Java, Content Writing">
                            <div class="error"><?php echo $error5; ?></div>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Current Address</label>
                            <textarea name="address" class="form-input" rows="2" placeholder="Your permanent or current residency location"><?php echo htmlspecialchars($address); ?></textarea>
                            <div class="error"><?php echo $error4; ?></div>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Work Experience Summary</label>
                            <textarea name="experiences" class="form-input" rows="3" placeholder="Outline your corporate tenure, past projects, or active roles"><?php echo htmlspecialchars($experiences); ?></textarea>
                            <div class="error"><?php echo $error6; ?></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Upload CV Dossier (PDF Format Only)</label>
                            <input type="file" name="cv" class="form-input" accept=".pdf">
                            <div class="error"><?php echo $error7; ?></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Upload Profile Photograph (JPG / PNG)</label>
                            <input type="file" name="photo" class="form-input" accept="image/*">
                            <div class="error"><?php echo $error8; ?></div>
                        </div>

                    </div>

                    <div class="form-actions">
                        <button type="submit" name="apply" class="btn-submit">Submit Application</button>
                        <a href="userviewjobs.php" class="btn-back">Cancel & Return</a>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <!-- Dashboard Footer -->
    <div class="footer">
        <p>&copy; 2026 Created by Maneesh and Pratik. Online Job Finding System. All Rights Reserved.</p>
    </div>

</body>
</html>