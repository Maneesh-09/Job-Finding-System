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

// Initialize form variables and error messages
$error1 = $error2 = $error3 = $error4 = $error5 = $error6 = $error7 = $error8 = '';
$title = $description = $location = $qualification = $salary = $category = $expirydate = '';
$image = '';

// Job categories
$job_categories = [
    "IT & Software",
    "Marketing & Sales",
    "Finance & Accounting",
    "Healthcare",
    "Education & Training",
    "Engineering",
    "Hospitality & Tourism",
    "Customer Service",
    "Human Resources",
    "Legal",
    "Construction",
    "Transport & Logistics",
    "Design & Creative",
    "Manufacturing",
    "Retail"
];

// Get company_id safely
$cid = null;
$sql_company = "SELECT cid FROM company WHERE username = '$username'";
$result_company = mysqli_query($con, $sql_company);

if ($result_company && mysqli_num_rows($result_company) === 1) {
    $company_data = mysqli_fetch_assoc($result_company);
    $cid = intval($company_data['cid']);
} else {
    echo "<script>alert('Corporate identity not found!'); window.location='logout.php';</script>";
    exit;
}

if (isset($_POST["addjob"])) {
    // Sanitizing inputs against SQL Injection
    $title = trim(mysqli_real_escape_string($con, $_POST["title"]));
    $description = trim(mysqli_real_escape_string($con, $_POST["description"]));
    $location = trim(mysqli_real_escape_string($con, $_POST["location"]));
    $qualification = trim(mysqli_real_escape_string($con, $_POST["qualification"]));
    $salary = trim(mysqli_real_escape_string($con, $_POST["salary"]));
    $category = trim(mysqli_real_escape_string($con, $_POST["category"]));
    $expirydate = trim(mysqli_real_escape_string($con, $_POST["expirydate"]));

    // Validation
    if (empty($title)) $error1 = "*Job title is required";
    if (empty($description)) $error2 = "*Description is required";
    if (empty($location)) $error3 = "*Location is required";
    if (empty($qualification)) $error4 = "*Qualification is required";
    if (empty($salary)) $error5 = "*Salary range is required";
    if (empty($_FILES["file"]["name"])) $error6 = "*Job branding image is required";
    if (empty($category)) $error7 = "*Please select a job category";
    if (empty($expirydate)) $error8 = "*Please select application expiry date";

    if (
        empty($error1) && empty($error2) && empty($error3) &&
        empty($error4) && empty($error5) && empty($error6) &&
        empty($error7) && empty($error8)
    ) {
        $target_dir = "images/";
        $image = basename($_FILES["file"]["name"]);
        $target_file = $target_dir . $image;

        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        move_uploaded_file($_FILES["file"]["tmp_name"], $target_file);

        $sql = "INSERT INTO jobs 
                (title, description, location, qualification, salary, image, username, openeddate, expirydate, category, company_id)
                VALUES 
                ('$title', '$description', '$location', '$qualification', '$salary', '$image', '$username', CURRENT_TIMESTAMP(), '$expirydate', '$category', '$cid')";

        if (mysqli_query($con, $sql)) {
            echo "<script>alert('New job circular published successfully!'); window.location='companyhomepage.php';</script>";
            exit;
        } else {
            echo "<script>alert('Database Error publishing job: " . mysqli_error($con) . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard - Add Job</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8f9fa; color: #333; display: flex; flex-direction: column; min-height: 100vh; }

        /* Modern Corporate Top Navbar */
        .navbar { background: #ffffff; border-bottom: 1px solid #e1e4e8; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
        .logo-area h1 { font-size: 20px; color: #111; font-weight: 700; }
        .logo-area span { color: red; }
        
        .profile-section { display: flex; align-items: center; gap: 15px; }
        .company-badge { background:  #002fb0; color: white; font-weight: 700; padding: 6px 14px; border-radius: 10px; font-size: 13.5px; }
        
        .btn-logout { background: #b02900; border: 0px solid white; color: white; padding: 8px 16px; border-radius: 10px; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: 0.3s; }
        .btn-logout:hover { background: #b02900; color: #fff; }

        /* Dashboard Main Layout */
        .dashboard-layout { display: flex; flex: 1; }

        /* Sidebar Design */
        .sidebar { width: 280px; background: #ffffff; border-right: 1px solid #e2e8f0; padding: 30px 15px; }
        .nav-links { list-style: none; display: flex; flex-direction: column; gap: 6px; }
        .nav-links li a { display: block; padding: 12px 16px; color: #555; font-size: 14.5px; font-weight: 600; text-decoration: none; border-radius: 8px; transition: 0.3s; }
        .nav-links li a:hover { color: black; background: white; }
        .nav-links li a.active { background: #334155; color: white !important; }

        /* Content Wrapper */
        .main-content { flex: 1; padding: 40px; background-color: #f8f9fa; display: flex; flex-direction: column; align-items: center; }
        
        /* Premium Form Card Layout */
        .form-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 35px; width: 100%; max-width: 850px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); }
        .form-card h2 { font-size: 25px; font-weight: 700; color: #0661f2; margin-bottom: 5px; border-bottom: 4px solid #eefbf7; padding-bottom: 8px; }
        .form-card p { font-size: 15px; color: #64748b; margin-bottom: 30px; }

        /* Balanced Grid Field Distribution */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .full-width { grid-column: span 2; }

        /* Input Controls Elements styling */
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-label { font-size: 14px; font-weight: 600; color: #475569; }
        
        .form-input { padding: 11px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14.5px; outline: none; background-color: #f8fafc; transition: 0.3s; width: 100%; }
        .form-input:focus { border-color: black; background-color: #fff;  }
        .form-input[readonly] { background-color: #e2e8f0; color: #64748b; cursor: not-allowed; }
        
        textarea.form-input { resize: vertical; font-family: inherit; }
        select.form-input { cursor: pointer; }
        input[type="file"].form-input { padding: 8px 12px; background: #fff; cursor: pointer; }

        /* Errors messaging */
        .error { color: #de3e3e; font-size: 12.5px; font-weight: 500; margin-top: 2px; }

        /* Actions Form Layout Group */
        .form-actions { display: flex; gap: 12px; margin-top: 25px; border-top: 1px solid #e2e8f0; padding-top: 20px; }
        .btn-submit { background: #b02900; color: white; border: none; padding: 12px 30px; border-radius: 8px; font-size: 14.5px; font-weight: 700; cursor: pointer; transition: 0.3s; flex: 1; }
        .btn-submit:hover { background: #002fb0; }
        
        .btn-cancel { background: #002fb0; color: white; border: 1px solid #cbd5e1; padding: 12px 24px; border-radius: 8px; font-size: 14.5px; font-weight: 600; cursor: pointer; text-decoration: none; text-align: center; transition: 0.2s; }
        .btn-cancel:hover { background: #b02900; color: white; }

        /* Footer */
        .footer { background: #111; color: white; padding: 20px 20px; text-align: center; border-top: 1px solid #222; margin-top: auto; width: 100%; }
        .footer p { font-size: 13.5px; }
    </style>
</head>
<body>

    <!-- Corporate Header Navbar -->
    <div class="navbar">
        <div class="logo-area">
            <h1>Online <span>Job Find</span></h1>
        </div>
        <div class="profile-section">
            <span class="company-badge">🏢  Company Profile</span>
            <a href="logout.php" class="btn-logout">Log Out</a>
        </div>
    </div>

    <!-- Dashboard Core Container -->
    <div class="dashboard-layout">
        
        <!-- Left Sidebar Navigation Link Elements -->
        <div class="sidebar">
            <ul class="nav-links">
                <li><a href="companyhomepage.php">Home</a></li>
                <li><a href="companyaddjobs.php" class="active">Add Jobs</a></li>
                <li><a href="companyrecivedrequest.php">Received Requests</a></li>
                <li><a href="companyaccepteduser.php">Accepted User</a></li>
                <li><a href="companydeclinerequest.php">Declined User</a></li>
                <li><a href="companyprofile.php">Profile</a></li>
            </ul>
        </div>

        <!-- Right Core Form Area -->
        <div class="main-content">
            
            <div class="form-card">
                <h2> Add New Job </h2>
                <p>Provide specific corporate descriptions, qualification parameters, and deadline logs to attract talent pools.</p>

                <form method="post" enctype="multipart/form-data">
                    <div class="form-grid">
                        
                        <div class="form-group">
                            <label class="form-label">Job Title</label>
                            <input type="text" name="title" placeholder="e.g., Senior PHP Developer" class="form-input" value="<?php echo htmlspecialchars($title); ?>">
                            <div class="error"><?php echo $error1; ?></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Job Category</label>
                            <select name="category" class="form-input">
                                <option value="">-- Select Industry --</option>
                                <?php
                                foreach ($job_categories as $cat) {
                                    $selected = ($cat == $category) ? "selected" : "";
                                    echo "<option value='".htmlspecialchars($cat)."' $selected>".htmlspecialchars($cat)."</option>";
                                }
                                ?>
                            </select>
                            <div class="error"><?php echo $error7; ?></div>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Job Description</label>
                            <textarea name="description" placeholder="Specify roles, technical stacks, everyday workflows, and expectations..." class="form-input" rows="4"><?php echo htmlspecialchars($description); ?></textarea>
                            <div class="error"><?php echo $error2; ?></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label"> Location</label>
                            <input type="text" name="location" placeholder="e.g., Kathmandu, Nepal (Or Remote)" class="form-input" value="<?php echo htmlspecialchars($location); ?>">
                            <div class="error"><?php echo $error3; ?></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Salary</label>
                            <input type="text" name="salary" placeholder="e.g., Rs. 60,000 - 90,000 / month" class="form-input" value="<?php echo htmlspecialchars($salary); ?>">
                            <div class="error"><?php echo $error5; ?></div>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Academic Qualification</label>
                            <input type="text" name="qualification" placeholder="e.g., Bachelor in Computer Application (BCA) or equivalent experience" class="form-input" value="<?php echo htmlspecialchars($qualification); ?>">
                            <div class="error"><?php echo $error4; ?></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Upload Job Banners / Image</label>
                            <input type="file" name="file" class="form-input" accept="image/*">
                            <div class="error"><?php echo $error6; ?></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Expiry Date</label>
                            <input type="date" name="expirydate" class="form-input" value="<?php echo htmlspecialchars($expirydate); ?>">
                            <div class="error"><?php echo $error8; ?></div>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Authorized Recruiter Account</label>
                            <input type="text" class="form-input" value="<?php echo htmlspecialchars($_SESSION["username"]); ?>" readonly>
                        </div>

                    </div>

                    <div class="form-actions">
                        <button type="submit" name="addjob" class="btn-submit">Publish Job </button>
                        <a href="companyhomepage.php" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <!-- Footer System component Layout -->
    <div class="footer">
        <p>&copy; 2026 Created by Maneesh and Pratik. Online Job Finding System. All Rights Reserved.</p>
    </div>

</body>
</html>