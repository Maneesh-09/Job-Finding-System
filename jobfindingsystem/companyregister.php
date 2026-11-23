<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/uploads.php';
include "database.php";

$error1 = $error2 = $error3 = $error4 = $error5 = $error6 = $error7 = $error8 = $error9 = $error10 = $error11 = '';
$cname = $username = $email = $address = $pan = $category = $est_year = $employees = '';
$errordb = '';

if (isset($_POST["register"])) {

    // Sanitize input variables
    $cname     = mysqli_real_escape_string($con, post_string('cname'));
    $username  = mysqli_real_escape_string($con, post_string('username'));
    $password = post_string('password', false);
    if (strlen($password) > 72) $error3 = '*Password must be 72 bytes or fewer'; 
    $email     = mysqli_real_escape_string($con, post_string('email'));
    $address   = mysqli_real_escape_string($con, post_string('address'));
    $pan       = mysqli_real_escape_string($con, post_string('pan'));
    $category  = mysqli_real_escape_string($con, post_string('category'));
    $est_year  = mysqli_real_escape_string($con, post_string('est_year'));
    $employees = mysqli_real_escape_string($con, post_string('employees'));

    // Upload Directory Check
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // PAN Image Check
    $pan_image = $_FILES['pan_image']['name'] ?? '';
    $pan_tmp   = $_FILES['pan_image']['tmp_name'] ?? '';

    // Company Logo Check
    $logo_name = $_FILES['company_logo']['name'] ?? '';
    $logo_tmp  = $_FILES['company_logo']['tmp_name'] ?? '';

    // Database Duplicate Checks
    $check_user = mysqli_query($con, "SELECT * FROM company WHERE username = '$username'");
    $check_pan  = mysqli_query($con, "SELECT * FROM company WHERE company_pan = '$pan'");

    if ($check_user === false) {
        $error2 = "*Database error: " . mysqli_error($con);
    } else if (mysqli_num_rows($check_user) > 0) {
        $error2 = "*Username already taken";
    }

    if ($check_pan === false) {
        $error5 = "*Database error: " . mysqli_error($con);
    } else if (mysqli_num_rows($check_pan) > 0) {
        $error5 = "*Company PAN already registered";
    }

    // Required Field Validations
    if (empty($cname))     $error1 = "*Company Name is required";
    if (empty($username))  $error2 = "*Username is required";
    if (empty($password))  $error3 = "*Password is required";
    if (empty($email))     $error4 = "*Email is required";
    if (empty($address))   $error8 = "*Operational Address is required";
    if (empty($pan))       $error5 = "*Company PAN/VAT Number is required";
    if (empty($category))  $error7 = "*Please select Company Category";
    if (empty($est_year))  $error9 = "*Established year is required";
    if (empty($employees)) $error10 = "*Number of employees is required";

    if (strlen($username) > 20) $error2 = '*Username must be 20 characters or fewer';
    if (!filter_var(post_string('email'), FILTER_VALIDATE_EMAIL)) $error4 = '*A valid email is required';
    if (!preg_match('/^[0-9]{4}$/', post_string('est_year'))) $error9 = '*Enter a four-digit year';

    // Form Processing if no validation errors
    if (empty($error1) && empty($error2) && empty($error3) && empty($error4) && 
        empty($error3) && empty($error5) && empty($error7) && empty($error8) && 
        empty($error9) && empty($error10)) {
        
        $new_pan_name = "";
        $new_logo_name = "";

        try {
            if ($pan_image !== '') $new_pan_name = save_upload('pan_image', 'uploads', image_upload_types());
            if ($logo_name !== '') $new_logo_name = save_upload('company_logo', 'uploads', image_upload_types());
        } catch (RuntimeException $exception) {
            if ($new_pan_name !== '') unlink(__DIR__ . '/uploads/' . $new_pan_name);
            $errordb = $exception->getMessage();
        }
        if ($errordb === '') {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // SQL Insertion
        $sql = "INSERT INTO `company` (`company_name`, `username`, `password`, `email`, `address`, `company_pan`, `pan_image`, `company_logo`, `company_type`, `est_year`, `employees`, `datecreated`) 
                VALUES ('$cname', '$username', '$hashed_password', '$email', '$address', '$pan', '$new_pan_name', '$new_logo_name', '$category', '$est_year', '$employees', CURRENT_TIMESTAMP())";

        if (mysqli_query($con, $sql)) {
            echo "<script>alert('Company Registration successful!'); window.location='company.php';</script>";
            exit;
        } else {
            $errordb = "Registration failed: " . mysqli_error($con);
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
    <title>Company Registration | Online Job Finding System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8f9fa; color: #333; display: flex; flex-direction: column; min-height: 100vh; }

        .navbar { background: #ffffff; border-bottom: 1px solid #e1e4e8; padding: 18px 40px; display: flex; justify-content: space-between; align-items: center; }
        .logo-area h1 { font-size: 20px; color: #111; font-weight: 700; }
        .logo-area span { color: red; }
        .nav-right-btn { background: #002fb0; border: 1px solid #cbd5e1; color: white; padding: 8px 18px; border-radius: 6px; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: 0.2s; }
        .nav-right-btn:hover { background: #001f78; color: white; }

        .main-wrapper { flex: 1; display: flex; justify-content: center; align-items: center; padding: 40px 20px; }
        .form-container { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 40px; width: 100%; max-width: 680px; box-shadow: 0 4px 25px rgba(0,0,0,0.02); }
        
        .form-container h2 { font-size: 24px; font-weight: 700; color: #1e293b; margin-bottom: 6px; text-align: left; }
        .form-container p { font-size: 14px; color: #64748b; margin-bottom: 30px; text-align: left; }

        .db-error-alert { background: #fff5f5; border: 1px solid #fee2e2; color: #de3e3e; border-radius: 6px; padding: 12px; font-size: 14px; font-weight: 500; margin-bottom: 20px; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .full-width { grid-column: span 2; }

        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 13.5px; font-weight: 600; color: #475569; }
        
        .form-input, .form-control { padding: 11px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; background-color: #f8fafc; transition: 0.3s; width: 100%; }
        .form-input:focus, .form-control:focus { border-color: #002fb0; background-color: #fff; box-shadow: 0 0 0 3px rgba(0, 47, 176, 0.1); }
        
        input[type="file"].form-input { padding: 8px 12px; background: #fff; cursor: pointer; }
        .error-msg { font-size: 12px; color: #de3e3e; font-weight: 500; }

        .form-actions { display: flex; gap: 12px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #f1f5f9; grid-column: span 2; }
        .btn-submit { background: #002fb0; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-size: 14.5px; font-weight: 700; cursor: pointer; flex: 2; text-align: center; transition: 0.2s; }
        .btn-submit:hover { background: #001f78; }
        
        .btn-cancel { background: #f8fafc; color: #475569; border: 1px solid #cbd5e1; padding: 12px 24px; border-radius: 6px; font-size: 14.5px; font-weight: 600; text-decoration: none; text-align: center; flex: 1; transition: 0.2s; }
        .btn-cancel:hover { background: #e2e8f0; color: #1e293b; }

        .footer { background: #111; color: white; padding: 20px; text-align: center; margin-top: auto; width: 100%; }
        .footer p { font-size: 13px; }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="logo-area">
            <h1>Online <span>Job Find</span></h1>
        </div>
        <a href="index.php" class="nav-right-btn">Login</a>
    </div>

    <div class="main-wrapper">
        <div class="form-container">
            <h2>Create Company Account</h2>
            <p>Register your Company identity to manage job vacancies and applications.</p>

            <?php if (!empty($errordb)): ?>
                <div class="db-error-alert"><?php echo $errordb; ?></div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-grid">
                    
                    <div class="form-group full-width">
                        <label>Company Name</label>
                        <input type="text" name="cname" class="form-input" value="<?php echo htmlspecialchars($cname); ?>" placeholder="Enter Company Name">
                        <?php if(!empty($error1)): ?> <span class="error-msg"><?php echo $error1; ?></span> <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-input" value="<?php echo htmlspecialchars($username); ?>" placeholder="Username">
                        <?php if(!empty($error2)): ?> <span class="error-msg"><?php echo $error2; ?></span> <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-input" placeholder="Password">
                        <?php if(!empty($error3)): ?> <span class="error-msg"><?php echo $error3; ?></span> <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>E-mail Address</label>
                        <input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($email); ?>" placeholder="example@company.com">
                        <?php if(!empty($error4)): ?> <span class="error-msg"><?php echo $error4; ?></span> <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Operational Address</label>
                        <input type="text" name="address" class="form-input" value="<?php echo htmlspecialchars($address); ?>" placeholder="Enter Address">
                        <?php if(!empty($error8)): ?> <span class="error-msg"><?php echo $error8; ?></span> <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>PAN/VAT Number</label>
                        <input type="text" name="pan" class="form-input" value="<?php echo htmlspecialchars($pan); ?>" placeholder="PAN/VAT Number">
                        <?php if(!empty($error5)): ?> <span class="error-msg"><?php echo $error5; ?></span> <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Upload PAN/VAT Image (Optional)</label>
                        <input type="file" name="pan_image" class="form-input" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label>Established Year</label>
                        <input type="text" name="est_year" class="form-input" value="<?php echo htmlspecialchars($est_year); ?>" placeholder="e.g. 2020">
                        <?php if(!empty($error9)): ?> <span class="error-msg"><?php echo $error9; ?></span> <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Number of Employees</label>
                        <input type="text" name="employees" class="form-input" value="<?php echo htmlspecialchars($employees); ?>" placeholder="e.g. 10-50">
                        <?php if(!empty($error10)): ?> <span class="error-msg"><?php echo $error10; ?></span> <?php endif; ?>
                    </div>

                    <div class="form-group full-width">
                        <label>Industry Classification</label>
                        <select name="category" class="form-control">
                            <option value="">-- Choose Category --</option>
                            <option value="Manufacturing" <?php if($category == "Manufacturing") echo "selected"; ?>>Manufacturing Sector</option>
                            <option value="IT" <?php if($category == "IT") echo "selected"; ?>>Information Technology (IT)</option>
                            <option value="Service" <?php if($category == "Service") echo "selected"; ?>>Service / Logistics Providers</option>
                            <option value="Other" <?php if($category == "Other") echo "selected"; ?>>Other Miscellaneous Channels</option>
                        </select>
                        <?php if(!empty($error7)): ?> <span class="error-msg"><?php echo $error7; ?></span> <?php endif; ?>
                    </div>

                    <div class="form-group full-width">
                        <label>Upload Company Logo (Optional)</label>
                        <input type="file" name="company_logo" class="form-input" accept="image/*">
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="register" class="btn-submit">Register Account</button>
                        <a href="index.php" class="btn-cancel">Return Back</a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2026 Created by Maneesh and Pratik. Online Job Finding System. All Rights Reserved.</p>
    </div>

</body>
</html>