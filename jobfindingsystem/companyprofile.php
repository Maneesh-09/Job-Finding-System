<?php
require_once __DIR__ . '/uploads.php';
require_once __DIR__ . '/auth.php';
require_role('company');
include "database.php"; // DB connection

// Check if company is logged in
if (!isset($_SESSION['username'])) {
    echo "<script>alert('Please login first!'); window.location='index.php';</script>";
    exit;
}

$username = mysqli_real_escape_string($con, $_SESSION['username']);

// Fetch company details
$query = "SELECT * FROM company WHERE username = '$username'";
$result = mysqli_query($con, $query);
$company = mysqli_fetch_assoc($result);

if (!$company) {
    echo "<script>alert('Corporate data not found!'); window.location='index.php';</script>";
    exit;
}

$error1 = $error4 = $error7 = $error8 = '';

if (isset($_POST['update'])) {
    $cname     = mysqli_real_escape_string($con, post_string('cname'));
    $password = post_string('password', false);
    if (strlen($password) > 72) $error1 = '*Password must be 72 bytes or fewer';
    $email     = mysqli_real_escape_string($con, post_string('email'));
    $address   = mysqli_real_escape_string($con, post_string('address'));
    $category  = mysqli_real_escape_string($con, post_string('category'));
    $est_year  = mysqli_real_escape_string($con, post_string('est_year'));
    $employees = mysqli_real_escape_string($con, post_string('employees'));

    // Validation
    if (empty($cname))    $error1 = "*Company Name is required";
    if (empty($email))    $error4 = "*Corporate Email is required";
    if (empty($address))  $error8 = "*Operational Address is required";
    if (empty($category)) $error7 = "*Please select Business Category";

    if (empty($error1) && empty($error4) && empty($error7) && empty($error8)) {
        
        // Target Directory for Uploads
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $pan_sql = $logo_sql = '';
        try {
            if (!empty($_FILES['pan_image']['name'])) {
                $name = save_upload('pan_image', 'uploads', image_upload_types());
                $pan_sql = ", pan_image = '$name'";
            }
            if (!empty($_FILES['company_logo']['name'])) {
                $name = save_upload('company_logo', 'uploads', image_upload_types());
                $logo_sql = ", company_logo = '$name'";
            }
        } catch (RuntimeException $exception) {
            $error1 = $exception->getMessage();
        }
        if ($error1 === '') {
        // Password Hash Check
        $password_sql = '';
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $password_sql = ", password = '$hashed_password'";
        }

        // Database Update Query
        $update_sql = "UPDATE company SET 
            company_name = '$cname',
            email = '$email',
            address = '$address',
            company_type = '$category',
            est_year = '$est_year',
            employees = '$employees'
            $password_sql
            $pan_sql
            $logo_sql
            WHERE username = '$username'";

        if (mysqli_query($con, $update_sql)) {
            echo "<script>alert('Profile updated successfully!'); window.location.href='companyprofile.php';</script>";
            exit;
        } else {
            $err = mysqli_error($con);
            echo "<script>alert('Error updating profile: $err');</script>";
        }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Profile - Settings</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8f9fa; color: #333; display: flex; flex-direction: column; min-height: 100vh; }

        /* Corporate Navbar */
        .navbar { background: #ffffff; border-bottom: 1px solid #e1e4e8; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
        .logo-area h1 { font-size: 20px; color: #111; font-weight: 700; }
        .logo-area span { color: red; }
        
        .profile-section { display: flex; align-items: center; gap: 15px; }
        .company-badge { background: #002fb0; color: white; font-weight: 600; padding: 6px 14px; border-radius: 8px; font-size: 13.5px; }
        
        .btn-logout { background: #de3e3e; color: white; padding: 8px 16px; border-radius: 8px; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: 0.3s; }
        .btn-logout:hover { background: #b91c1c; }

        /* Dashboard Layout */
        .dashboard-layout { display: flex; flex: 1; }

        /* Sidebar Design */
        .sidebar { width: 260px; background: #ffffff; border-right: 1px solid #e2e8f0; padding: 30px 15px; }
        .nav-links { list-style: none; display: flex; flex-direction: column; gap: 6px; }
        .nav-links li a { display: block; padding: 12px 16px; color: #475569; font-size: 14.5px; font-weight: 600; text-decoration: none; border-radius: 8px; transition: 0.3s; }
        .nav-links li a:hover { color: black; background: white; }
        .nav-links li a.active { background: #334155; color: white !important; }

        /* Main View */
        .main-content { flex: 1; padding: 40px; background-color: #f8f9fa; display: flex; justify-content: center; align-items: flex-start; }
        
        .form-container { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 35px; width: 100%; max-width: 700px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); }
        
        .form-container h2 { font-size: 22px; font-weight: 700; color: #1e293b; margin-bottom: 6px; }
        .form-container p { font-size: 14px; color: #64748b; margin-bottom: 25px; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .full-width { grid-column: span 2; }

        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 13.5px; font-weight: 600; color: #475569; }
        
        .form-control { width: 100%; padding: 10px 14px; font-size: 14px; border: 1px solid #cbd5e1; border-radius: 8px; background-color: #fff; color: #334155; transition: border-color 0.2s; }
        .form-control:focus { outline: none; border-color: #002fb0; box-shadow: 0 0 0 3px rgba(0, 47, 176, 0.1); }
        .form-control:disabled, .form-control[readonly] { background-color: #f1f5f9; color: #64748b; cursor: not-allowed; border-color: #e2e8f0; }

        input[type="file"].form-control { padding: 7px 12px; background: #fff; cursor: pointer; }
        
        .preview-img { width: 60px; height: 60px; border-radius: 8px; object-fit: cover; border: 1px solid #cbd5e1; margin-top: 5px; }
        .error-msg { font-size: 12px; color: #de3e3e; font-weight: 500; }

        .form-actions { display: flex; gap: 12px; margin-top: 25px; padding-top: 15px; border-top: 1px solid #f1f5f9; grid-column: span 2; }
        .btn-submit { background: #002fb0; color: white; border: none; padding: 11px 20px; border-radius: 8px; font-size: 14.5px; font-weight: 700; cursor: pointer; flex: 2; text-align: center; transition: 0.2s; }
        .btn-submit:hover { background: #001f78; }
        
        .btn-cancel { background: #f8fafc; color: #475569; border: 1px solid #cbd5e1; padding: 11px 20px; border-radius: 8px; font-size: 14.5px; font-weight: 600; text-decoration: none; text-align: center; flex: 1; transition: 0.2s; }
        .btn-cancel:hover { background: #e2e8f0; color: #1e293b; }

        .footer { background: #111; color: white; padding: 18px; text-align: center; border-top: 1px solid #222; margin-top: auto; width: 100%; }
        .footer p { font-size: 13px; }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="logo-area">
            <h1>Online <span>Job Find</span></h1>
        </div>
        <div class="profile-section">
            <span class="company-badge">🏢 <?php echo htmlspecialchars($company['company_name']); ?></span>
            <a href="logout.php" class="btn-logout">Log Out</a>
        </div>
    </div>

    <div class="dashboard-layout">
        
        <div class="sidebar">
            <ul class="nav-links">
                <li><a href="companyhomepage.php">Home</a></li>
                <li><a href="companyaddjobs.php">Add Jobs</a></li>
                <li><a href="companyrecivedrequest.php">Received Requests</a></li>
                <li><a href="companyaccepteduser.php">Accepted User</a></li>
                <li><a href="companydeclinerequest.php">Declined User</a></li>
                <li><a href="companyprofile.php" class="active">Profile</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="form-container">
                <h2>Company Identity Settings</h2>
                <p>Modify corporate metrics, update credentials, and manage organizational identity.</p>

                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-grid">
                        
                        <div class="form-group full-width">
                            <label>Company Name</label>
                            <input type="text" name="cname" class="form-control" value="<?php echo htmlspecialchars($company['company_name'] ?? ''); ?>">
                            <?php if(!empty($error1)): ?> <span class="error-msg"><?php echo $error1; ?></span> <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label>Account Username</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($company['username'] ?? ''); ?>" disabled>
                        </div>

                        <div class="form-group">
                            <label>New Password (Optional)</label>
                            <input type="password" name="password" class="form-control" placeholder="Leave empty to keep current password">
                        </div>

                        <div class="form-group">
                            <label>Company E-mail</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($company['email'] ?? ''); ?>">
                            <?php if(!empty($error4)): ?> <span class="error-msg"><?php echo $error4; ?></span> <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label>Location Address</label>
                            <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($company['address'] ?? ''); ?>">
                            <?php if(!empty($error8)): ?> <span class="error-msg"><?php echo $error8; ?></span> <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label>PAN / VAT Number</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($company['company_pan'] ?? ''); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label>Established Year</label>
                            <input type="text" name="est_year" class="form-control" value="<?php echo htmlspecialchars($company['est_year'] ?? ''); ?>" placeholder="e.g. 2020">
                        </div>

                        <div class="form-group">
                            <label>Number of Employees</label>
                            <input type="text" name="employees" class="form-control" value="<?php echo htmlspecialchars($company['employees'] ?? ''); ?>" placeholder="e.g. 23">
                        </div>

                        <div class="form-group full-width">
                            <label>Core Industry Category</label>
                            <select name="category" class="form-control">
                                <option value="">-- Choose Category --</option>
                                <option value="IT" <?php if(($company['company_type'] ?? '') == "IT") echo "selected"; ?>>Information Technology (IT)</option>
                                <option value="Manufacturing" <?php if(($company['company_type'] ?? '') == "Manufacturing") echo "selected"; ?>>Manufacturing Sector</option>
                                <option value="Service" <?php if(($company['company_type'] ?? '') == "Service") echo "selected"; ?>>Service / Logistics Providers</option>
                                <option value="Other" <?php if(($company['company_type'] ?? '') == "Other") echo "selected"; ?>>Other Miscellaneous Channels</option>
                            </select>
                            <?php if(!empty($error7)): ?> <span class="error-msg"><?php echo $error7; ?></span> <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label>Update PAN/VAT Document Image</label>
                            <input type="file" name="pan_image" class="form-control" accept="image/*">
                            <?php if(!empty($company['pan_image'])): ?>
                                <a href="uploads/<?php echo htmlspecialchars($company['pan_image']); ?>" target="_blank" style="font-size:12px; color:#002fb0; margin-top:4px; text-decoration:none;">📄 View Uploaded PAN Image</a>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label>Update Company Logo</label>
                            <input type="file" name="company_logo" class="form-control" accept="image/*">
                            <?php if(!empty($company['company_logo'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($company['company_logo']); ?>" class="preview-img" alt="Logo Preview">
                            <?php endif; ?>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="update" class="btn-submit">Modify Profile</button>
                            <a href="companyhomepage.php" class="btn-cancel">Cancel</a>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2026 Created by Maneesh and Pratik. Online Job Finding System. All Rights Reserved.</p>
    </div>

</body>
</html>