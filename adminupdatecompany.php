<?php
session_start();
include "database.php";

if (!isset($_SESSION["username"])) {
    echo '<script>
            alert("Please login first");
            window.location.href = "index.php";
          </script>';
    exit();
}

$cid = $_GET["cid"] ?? '';

if ($cid == '') {
    echo '<script>
            alert("Invalid Company ID");
            window.location.href = "adminviewcompany.php";
          </script>';
    exit();
}

$cid = intval($cid); // Prevent SQL Injection
$sql = "SELECT * FROM company WHERE cid='$cid'";
$result = mysqli_query($con, $sql);
$company = mysqli_fetch_assoc($result);

if (!$company) {
    echo '<script>
            alert("Company not found!");
            window.location.href = "adminviewcompany.php";
          </script>';
    exit();
}

$cname = $company['company_name'];
$username = $company['username'];
$email = $company['email'];
$address = $company['address'] ?? '';
$pan = $company['company_pan'];
$license = $company['company_license'];
$category = $company['company_type'];

$error1 = $error2 = $error3 = $error4 = $error5 = $error6 = $error7 = $error8 = '';
$errordb = '';

if (isset($_POST["update"])) {
    $cname = mysqli_real_escape_string($con, $_POST["cname"]);
    $username = mysqli_real_escape_string($con, $_POST["username"]);
    $password = $_POST["password"];
    $email = mysqli_real_escape_string($con, $_POST["email"]);
    $address = mysqli_real_escape_string($con, $_POST["address"] ?? '');
    $pan = mysqli_real_escape_string($con, $_POST["pan"]);
    $license = mysqli_real_escape_string($con, $_POST["license"]);
    $category = mysqli_real_escape_string($con, $_POST["category"]);

    // Check uniqueness
    $check_user = mysqli_query($con, "SELECT * FROM company WHERE username = '$username' AND cid != '$cid'");
    $check_pan = mysqli_query($con, "SELECT * FROM company WHERE company_pan = '$pan' AND cid != '$cid'");
    $check_license = mysqli_query($con, "SELECT * FROM company WHERE company_license = '$license' AND cid != '$cid'");

    if ($check_user && mysqli_num_rows($check_user) > 0) $error2 = "*Username already taken";
    if ($check_pan && mysqli_num_rows($check_pan) > 0) $error5 = "*Company PAN already exists";
    if ($check_license && mysqli_num_rows($check_license) > 0) $error6 = "*Company License already exists";
    
    if (empty($cname)) $error1 = "*Company Name is required";
    if (empty($username)) $error2 = "*Username is required";
    if (empty($email)) $error4 = "*Email is required";
    if (empty($address)) $error8 = "*Address is required";
    if (empty($pan)) $error5 = "*Company PAN is required";
    if (empty($license)) $error6 = "*Company License Number is required";
    if (empty($category)) $error7 = "*Please select Company Category";

    if (empty($error1) && empty($error2) && empty($error3) && empty($error4) &&
        empty($error5) && empty($error6) && empty($error7) && empty($error8)) {
        
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE company 
                    SET company_name='$cname', username='$username', password='$hashed_password',
                        email='$email', address='$address', company_pan='$pan', 
                        company_license='$license', company_type='$category'
                    WHERE cid='$cid'";
        } else {
            $sql = "UPDATE company 
                    SET company_name='$cname', username='$username',
                        email='$email', address='$address', company_pan='$pan', 
                        company_license='$license', company_type='$category'
                    WHERE cid='$cid'";
        }

        $res = mysqli_query($con, $sql);

        if ($res) {
            echo '<script>
                    alert("Company updated successfully");
                    window.location.href="adminviewcompany.php";
                  </script>';
            exit();
        } else {
            $errordb = "Update failed: " . mysqli_error($con);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Edit Company</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8f9fa; color: #333; display: flex; flex-direction: column; min-height: 100vh; }

        /* Modern Admin Navbar */
        .navbar { background: #ffffff; border-bottom: 1px solid #e1e4e8; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
        .logo-area h1 { font-size: 20px; color: #111; font-weight: 700; }
        .logo-area span { color: #00b074; }
        
        .admin-profile-section { display: flex; align-items: center; gap: 15px; }
        .admin-badge { background: #eefbf7; color: #00b074; font-weight: 700; padding: 6px 14px; border-radius: 20px; font-size: 13.5px; border: 1px solid rgba(0, 176, 116, 0.2); }
        
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
        .main-content { flex: 1; padding: 40px; background-color: #f8f9fa; }

        /* Form Card Styling */
        .form-container { max-width: 850px; width: 100%; margin: 0 auto; }
        .form-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); }
        .form-card h2 { font-size: 22px; font-weight: 700; margin-bottom: 25px; color: #1a1a1a; border-bottom: 2px solid #f1f1f1; padding-bottom: 12px; }
        .form-card h2 span { color: #00b074; }

        /* 2-Column Responsive Form Grid */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .full-width { grid-column: span 2; }

        /* Form Elements */
        .form-group { margin-bottom: 5px; }
        .form-label { display: block; font-size: 14px; font-weight: 600; color: #4a5568; margin-bottom: 8px; }
        
        .form-input { width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; outline: none; transition: 0.3s; background-color: #f8fafc; }
        .form-input:focus { border-color: #00b074; background-color: #fff; box-shadow: 0 0 0 3px rgba(0, 176, 116, 0.1); }

        /* Error Text */
        .error { color: #de3e3e; font-size: 12.5px; font-weight: 600; margin-top: 5px; }
        .db-error { background: #fdf2f2; border: 1px solid #f8b4b4; color: #9b1c1c; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; font-weight: 600; }

        /* Action Buttons Row */
        .form-actions { display: flex; gap: 15px; margin-top: 20px; border-top: 1px solid #eee; padding-top: 25px; grid-column: span 2; }
        
        .btn-submit { background: #00b074; color: white; border: none; padding: 13px 28px; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-submit:hover { background: #009460; }
        
        .btn-cancel { background: white; border: 1px solid #cbd5e1; color: #4a5568; padding: 13px 28px; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; transition: 0.3s; text-decoration: none; text-align: center; }
        .btn-cancel:hover { background: #f8fafc; border-color: #b8c5d6; }

        /* Footer */
        .footer { background: #111; color: #888; padding: 25px 20px; text-align: center; border-top: 1px solid #222; margin-top: auto; }
        .footer p { font-size: 13.5px; }
    </style>
</head>

<body>

    <!-- Admin Top Navbar -->
    <div class="navbar">
        <div class="logo-area">
            <h1>Online <span>Job Find</span></h1>
        </div>
        <div class="admin-profile-section">
            <span class="admin-badge">🛡️ Admin Panel</span>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </div>

    <!-- Dashboard Body Container -->
    <div class="dashboard-layout">
        
        <!-- Left Sidebar Navigation -->
        <div class="sidebar">
            <ul class="nav-links">
                <li><a href="adminhomepage.php">Home</a></li>
                <li><a href="adminviewcompany.php">View Companies</a></li>
                <li><a href="adminviewuser.php">View Users</a></li>
                <li><a href="adminviewjobs.php">View Jobs</a></li>
                <li><a href="adminviewpendingrequest.php">All Pending Applications</a></li>
                <li><a href="allacceptedapplicant.php">All Accepted Applicants</a></li>
                <li><a href="allrejectedapplicant.php">All Rejected Applicants</a></li>
            </ul>
        </div>

        <!-- Right Content Management Area -->
        <div class="main-content">
            <div class="form-container">
                <div class="form-card">
                    <h2>Update Corporate <span>Profile</span></h2>

                    <?php if (!empty($errordb)) { ?>
                        <div class="db-error"><?php echo $errordb; ?></div>
                    <?php } ?>

                    <form method="POST">
                        <div class="form-grid">
                            
                            <!-- Company Name -->
                            <div class="form-group">
                                <label class="form-label">Company Name</label>
                                <input class="form-input" type="text" name="cname" value="<?php echo htmlspecialchars($cname); ?>">
                                <div class="error"><?php echo $error1; ?></div>
                            </div>

                            <!-- Username -->
                            <div class="form-group">
                                <label class="form-label">Username</label>
                                <input class="form-input" type="text" name="username" value="<?php echo htmlspecialchars($username); ?>">
                                <div class="error"><?php echo $error2; ?></div>
                            </div>

                            <!-- Password -->
                            <div class="form-group">
                                <label class="form-label">Password (Leave blank to keep old)</label>
                                <input class="form-input" type="password" name="password" placeholder="••••••••">
                                <div class="error"><?php echo $error3; ?></div>
                            </div>

                            <!-- Email -->
                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <input class="form-input" type="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                                <div class="error"><?php echo $error4; ?></div>
                            </div>

                            <!-- Address -->
                            <div class="form-group">
                                <label class="form-label">Office Address</label>
                                <input class="form-input" type="text" name="address" value="<?php echo htmlspecialchars($address); ?>">
                                <div class="error"><?php echo $error8; ?></div>
                            </div>

                            <!-- Category Selection -->
                            <div class="form-group">
                                <label class="form-label">Company Category</label>
                                <select class="form-input" name="category">
                                    <option value="">--Select--</option>
                                    <option value="Manufacturing" <?php if($category=="Manufacturing") echo "selected"; ?>>Manufacturing</option>
                                    <option value="IT" <?php if($category=="IT") echo "selected"; ?>>IT</option>
                                    <option value="Service" <?php if($category=="Service") echo "selected"; ?>>Service</option>
                                    <option value="Other" <?php if($category=="Other") echo "selected"; ?>>Other</option>
                                </select>
                                <div class="error"><?php echo $error7; ?></div>
                            </div>

                            <!-- Company PAN -->
                            <div class="form-group">
                                <label class="form-label">Company PAN</label>
                                <input class="form-input" type="text" name="pan" value="<?php echo htmlspecialchars($pan); ?>">
                                <div class="error"><?php echo $error5; ?></div>
                            </div>

                            <!-- Company License -->
                            <div class="form-group">
                                <label class="form-label">Company Registration License</label>
                                <input class="form-input" type="text" name="license" value="<?php echo htmlspecialchars($license); ?>">
                                <div class="error"><?php echo $error6; ?></div>
                            </div>

                            <!-- Action Buttons inside grid -->
                            <div class="form-actions">
                                <button type="submit" name="update" class="btn-submit">Update Company</button>
                                <a href="adminviewcompany.php" class="btn-cancel">Go Back</a>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Footer -->
    <div class="footer">
        <p>&copy; 2026 Created by Maneesh and Pratik. Online Job Finding System. All Rights Reserved.</p>
    </div>

</body>
</html>