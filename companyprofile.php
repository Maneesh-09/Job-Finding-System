<?php
session_start();
include "database.php"; // DB connection

// Check if company is logged in
if (!isset($_SESSION['username'])) {
    echo "<script>alert('Please login first!'); window.location='index.php';</script>";
    exit;
}

$username = mysqli_real_escape_string($con, $_SESSION['username']);

// Fetch company details securely
$query = "SELECT * FROM company WHERE username = '$username'";
$result = mysqli_query($con, $query);
$company = mysqli_fetch_assoc($result);

if (!$company) {
    echo "<script>alert('Corporate data not found!'); window.location='index.php';</script>";
    exit;
}

$error1 = $error4 = $error5 = $error6 = $error7 = $error8 = '';

if (isset($_POST['update'])) {
    $cname    = mysqli_real_escape_string($con, $_POST['cname']);
    $password = $_POST['password']; // Will be hashed, escaping not strictly needed before check
    $email    = mysqli_real_escape_string($con, $_POST['email']);
    $address  = mysqli_real_escape_string($con, $_POST['address']);
    $category = mysqli_real_escape_string($con, $_POST['category']);
    
    // PAN and License are readonly in UI, but kept secure for structural validation
    $pan      = mysqli_real_escape_string($con, $company['company_pan']);
    $license  = mysqli_real_escape_string($con, $company['company_license']);

    // Validation Layout Constraints
    if (empty($cname))    $error1 = "*Company Name is required";
    if (empty($email))    $error4 = "*Corporate Email is required";
    if (empty($address))  $error8 = "*Operational Address is required";
    if (empty($category)) $error7 = "*Please select Business Category";

    if (empty($error1) && empty($error4) && empty($error7) && empty($error8)) {
        
        // Optional password update tracking logic
        $password_sql = '';
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $password_sql = ", password = '$hashed_password'";
        }

        $update_sql = "UPDATE company SET 
            company_name = '$cname',
            email = '$email',
            address = '$address',
            company_type = '$category'
            $password_sql
            WHERE username = '$username'";

        if (mysqli_query($con, $update_sql)) {
            echo "<script>alert('Profile metrics updated successfully!'); window.location.href='companyprofile.php';</script>";
            exit;
        } else {
            $err = mysqli_error($con);
            echo "<script>alert('Error sync updates: $err');</script>";
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

        /* Modern Corporate Top Navbar */
        .navbar { background: #ffffff; border-bottom: 1px solid #e1e4e8; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
        .logo-area h1 { font-size: 20px; color: #111; font-weight: 700; }
        .logo-area span { color: red; }
        
        .profile-section { display: flex; align-items: center; gap: 15px; }
        .company-badge { background: #002fb0; color: white; font-weight: 700; padding: 6px 14px; border-radius: 10px; font-size: 13.5px; }
        
        .btn-logout { background: #b02900; border: 0px solid #de3e3e; color: white; padding: 8px 16px; border-radius: 10px; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: 0.3s; }
        .btn-logout:hover { background: #b02900; color: white; }

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
        
        .form-container { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 35px; width: 100%; max-width: 650px; box-shadow: 0 4px 20px rgba(0,0,0,0.01); border-top: 4px solid #00b074; }
        
        .form-container h2 { font-size: 22px; font-weight: 700; color: #1e293b; margin-bottom: 6px; text-align: left; }
        .form-container p { font-size: 14px; color: #64748b; margin-bottom: 25px; text-align: left; }

        /* Form Logic Element Groups */
        .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 18px; }
        .form-group label { font-size: 14px; font-weight: 600; color: #475569; }
        
        .form-control { width: 100%; padding: 10px 14px; font-size: 14px; border: 1px solid #cbd5e1; border-radius: 6px; background-color: #fff; color: #334155; transition: border-color 0.2s; }
        .form-control:focus { outline: none; border-color: red; box-shadow: 0 0 0 3px rgba(0, 176, 116, 0.1); }
        .form-control:disabled, .form-control[readonly] { background-color: #f1f5f9; color: #64748b; cursor: not-allowed; border-color: #e2e8f0; }

        /* Custom Dropdown select structural tweaks */
        select.form-control { height: 42px; cursor: pointer; }

        .error-msg { font-size: 12px; color: #de3e3e; font-weight: 500; margin-top: 2px; }

        /* Actions Buttons Grid block setup */
        .form-actions { display: flex; gap: 12px; margin-top: 25px; padding-top: 15px; border-top: 1px solid #f1f5f9; }
        .btn-submit { background: #b02900; color: white; border: none; padding: 11px 20px; border-radius: 6px; font-size: 14.5px; font-weight: 700; cursor: pointer; flex: 2; text-align: center; transition: 0.2s; }
        .btn-submit:hover { background: #002fb0; }
        
        .btn-cancel { background: #002fb0; color: white; border: 1px solid #cbd5e1; padding: 11px 20px; border-radius: 6px; font-size: 14.5px; font-weight: 600; text-decoration: none; text-align: center; flex: 1; transition: 0.2s; }
        .btn-cancel:hover { background: #b02900; color: #1e293b; }

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
            <span class="company-badge">🏢 Company Profile</span>
            <a href="logout.php" class="btn-logout">Log Out</a>
        </div>
    </div>

    <!-- Dashboard Core Layout Structure -->
    <div class="dashboard-layout">
        
        <!-- Left Side Menu Navigation Elements -->
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

        <!-- Right Main Dashboard View Content Frame -->
        <div class="main-content">
            
            <div class="form-container">
                <h2>Company Identity Settings</h2>
                <p>Modify organizational operational parameters, updates verification and security credentials below.</p>

                <form method="POST" action="">
                    
                    <div class="form-group">
                        <label>Company Name</label>
                        <input type="text" name="cname" class="form-control" value="<?php echo htmlspecialchars($company['company_name']); ?>">
                        <?php if(!empty($error1)): ?> <span class="error-msg"><?php echo $error1; ?></span> <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Account Username</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($company['username']); ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Leave completely blank to retain current system key hash">
                    </div>

                    <div class="form-group">
                        <label>Company E-mail</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($company['email']); ?>">
                        <?php if(!empty($error4)): ?> <span class="error-msg"><?php echo $error4; ?></span> <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Location Address</label>
                        <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($company['address']); ?>">
                        <?php if(!empty($error8)): ?> <span class="error-msg"><?php echo $error8; ?></span> <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Permanent Account Number (PAN)</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($company['company_pan']); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>Company Operating License No.</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($company['company_license']); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>Core Industry Operation Category</label>
                        <select name="category" class="form-control">
                            <option value="">--- Choose Category Paradigm ---</option>
                            <option value="Manufacturing" <?php if($company['company_type'] == "Manufacturing") echo "selected"; ?>>IT & Software</option>
                            <option value="IT" <?php if($company['company_type'] == "IT") echo "selected"; ?>>Marketing & Sales</option>
                            <option value="Service" <?php if($company['company_type'] == "Service") echo "selected"; ?>>Finance & Accounting</option>
                            <option value="Other" <?php if($company['company_type'] == "Other") echo "selected"; ?>>Healthcare & Medicine</option>
                            <option value="Other" <?php if($company['company_type'] == "Other") echo "selected"; ?>>Education & Training</option>
                            <option value="Other" <?php if($company['company_type'] == "Other") echo "selected"; ?>>Engineering & Others</option>
                            <option value="Other" <?php if($company['company_type'] == "Other") echo "selected"; ?>>Hospitality & Tourism</option>
                            <option value="Other" <?php if($company['company_type'] == "Other") echo "selected"; ?>>Customer Services & Others</option>
                            <option value="Other" <?php if($company['company_type'] == "Other") echo "selected"; ?>>Legal & Registration</option>
                            <option value="Other" <?php if($company['company_type'] == "Other") echo "selected"; ?>>Construction & Works</option>
                            <option value="Other" <?php if($company['company_type'] == "Other") echo "selected"; ?>>Design & Creative</option>
                            <option value="Other" <?php if($company['company_type'] == "Other") echo "selected"; ?>>Manufacturing & Retail</option>
                            <option value="Other" <?php if($company['company_type'] == "Other") echo "selected"; ?>>Transport & Logistics</option>
                            <option value="Other" <?php if($company['company_type'] == "Other") echo "selected"; ?>>Other Miscellaneous Channels</option>
                        </select>
                        <?php if(!empty($error7)): ?> <span class="error-msg"><?php echo $error7; ?></span> <?php endif; ?>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="update" class="btn-submit"> Modify Profile</button>
                        <a href="companyhomepage.php" class="btn-cancel">Cancel</a>
                    </div>

                </form>
            </div>

        </div>
    </div>

    <!-- Footer Structural System Design Unit -->
    <div class="footer">
        <p>&copy; 2026 Created by Maneesh and Pratik. Online Job Finding System. All Rights Reserved.</p>
    </div>

</body>
</html>