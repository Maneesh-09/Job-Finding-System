<?php
include "database.php";

$error1 = $error2 = $error3 = $error4 = $error5 = $error6 = $error7 = $error8 = '';
$cname = $username = $email = $address = $pan = $license = $category = '';
$errordb = '';

if (isset($_POST["register"])) {

    // Sanitize input variables securely
    $cname    = mysqli_real_escape_string($con, $_POST["cname"]);
    $username = mysqli_real_escape_string($con, $_POST["username"]);
    $password = $_POST["password"]; 
    $email    = mysqli_real_escape_string($con, $_POST["email"]);
    $address  = mysqli_real_escape_string($con, $_POST["address"] ?? '');
    $pan      = mysqli_real_escape_string($con, $_POST["pan"]);
    $license  = mysqli_real_escape_string($con, $_POST["license"]);
    $category = mysqli_real_escape_string($con, $_POST["category"]);

    // Validation checks inside database indices
    $check_user    = mysqli_query($con, "SELECT * FROM company WHERE username = '$username'");
    $check_pan     = mysqli_query($con, "SELECT * FROM company WHERE company_pan = '$pan'");
    $check_license = mysqli_query($con, "SELECT * FROM company WHERE company_license = '$license'");

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

    if ($check_license === false) {
        $error6 = "*Database error: " . mysqli_error($con);
    } else if (mysqli_num_rows($check_license) > 0) {
        $error6 = "*Company License already exists";
    }

    // Structural input constraint evaluations
    if (empty($cname))    $error1 = "*Company Name is required";
    if (empty($username)) $error2 = "*Username is required";
    if (empty($password)) $error3 = "*Password is required";
    if (empty($email))    $error4 = "*Email is required";
    if (empty($address))  $error8 = "*Operational Address is required";
    if (empty($pan))      $error5 = "*Company PAN is required";
    if (empty($license))  $error6 = "*Company License Number is required";
    if (empty($category)) $error7 = "*Please select Company Category";

    if (empty($error1) && empty($error2) && empty($error3) && empty($error4) && 
        empty($error5) && empty($error6) && empty($error7) && empty($error8)) {
        
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO `company`(`cid`, `company_name`, `username`, `password`, `email`, `address`, `company_pan`, `company_license`, `company_type`, datecreated) 
                VALUES (NULL, '$cname', '$username', '$hashed_password', '$email', '$address', '$pan', '$license', '$category', CURRENT_TIMESTAMP())";

        if (mysqli_query($con, $sql)) {
            echo "<script>alert('Company Registration successful!'); window.location='company.php';</script>";
            exit;
        } else {
            $errordb = "Registration failed: " . mysqli_error($con);
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

        /* Modern Corporate Header */
        .navbar { background: #ffffff; border-bottom: 1px solid #e1e4e8; padding: 18px 40px; display: flex; justify-content: space-between; align-items: center; }
        .logo-area h1 { font-size: 20px; color: #111; font-weight: 700; }
        .logo-area span { color: red; }
        .nav-right-btn { background: #002fb0; border: 1px solid #cbd5e1; color: white; padding: 8px 18px; border-radius: 6px; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: 0.2s; }
        .nav-right-btn:hover { background: #b02900; color: white; }

        /* Main Center Layout Wrapper */
        .main-wrapper { flex: 1; display: flex; justify-content: center; align-items: center; padding: 40px 20px; }
        
        .form-container { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 40px; width: 100%; max-width: 680px; box-shadow: 0 4px 25px rgba(0,0,0,0.02); border-top: 5px solid #00b074; }
        
        .form-container h2 { font-size: 24px; font-weight: 700; color: #1e293b; margin-bottom: 6px; text-align: left; }
        .form-container p { font-size: 14px; color: #64748b; margin-bottom: 30px; text-align: left; }

        /* DB Level Error Alert block */
        .db-error-alert { background: #fff5f5; border: 1px solid #fee2e2; color: #de3e3e; border-radius: 6px; padding: 12px; font-size: 14px; font-weight: 500; margin-bottom: 20px; }

        /* Form Controls Setup Grid Layout styling */
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px 24px; }
        .full-width-field { grid-column: span 2; }

        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 13.5px; font-weight: 600; color: #475569; }
        
        .form-control { width: 100%; padding: 11px 14px; font-size: 14px; border: 1px solid #cbd5e1; border-radius: 6px; background-color: #fff; color: #334155; transition: border-color 0.2s; }
        .form-control:focus { outline: none; border-color: #00b074; box-shadow: 0 0 0 3px rgba(0, 176, 116, 0.1); }
        .form-control::placeholder { color: #94a3b8; }

        select.form-control { height: 44px; cursor: pointer; }

        .error-msg { font-size: 12px; color: #de3e3e; font-weight: 500; margin-top: 2px; }

        /* Button configurations layout setup */
        .form-actions { display: flex; gap: 14px; margin-top: 35px; padding-top: 20px; border-top: 1px solid #f1f5f9; grid-column: span 2; }
        .btn-submit { background: #002fb0; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-size: 14.5px; font-weight: 700; cursor: pointer; flex: 2; text-align: center; transition: 0.2s; }
        .btn-submit:hover { background: #b02900; }
        
        .btn-cancel { background: #f8fafc; color: #475569; border: 1px solid #cbd5e1; padding: 12px 24px; border-radius: 6px; font-size: 14.5px; font-weight: 600; text-decoration: none; text-align: center; flex: 1; transition: 0.2s; }
        .btn-cancel:hover { background: #e2e8f0; color: #1e293b; }

        /* Footer Element Frame System UI */
        .footer { background: #111; color: white; padding: 25px 20px; text-align: center; border-top: 1px solid #222; margin-top: auto; width: 100%; }
        .footer p { font-size: 13.5px; }
    </style>
</head>
<body>

    <!-- Corporate Upper Top Menu Grid Structure -->
    <div class="navbar">
        <div class="logo-area">
            <h1>Online <span>Job Find</span></h1>
        </div>
        <a href="index.php" class="nav-right-btn">Login</a>
    </div>

    <!-- Core Dynamic Form Frame System Panel Container -->
    <div class="main-wrapper">
        <div class="form-container">
            <h2>Create Company Account</h2>
            <p>Register your Company identity to manage vacancy parameters, screen candidate applications, and track requests.</p>

            <?php if (!empty($errordb)): ?>
                <div class="db-error-alert"><?php echo $errordb; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-grid">
                    
                    <div class="form-group full-width-field">
                        <label>Company Name</label>
                        <input type="text" name="cname" class="form-control" placeholder="enter company title" value="<?php echo htmlspecialchars($cname); ?>">
                        <?php if(!empty($error1)): ?> <span class="error-msg"><?php echo $error1; ?></span> <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" placeholder="username" value="<?php echo htmlspecialchars($username); ?>">
                        <?php if(!empty($error2)): ?> <span class="error-msg"><?php echo $error2; ?></span> <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" placeholder="passcode">
                        <?php if(!empty($error3)): ?> <span class="error-msg"><?php echo $error3; ?></span> <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>E-mail</label>
                        <input type="email" name="email" class="form-control" placeholder="example@company.com" value="<?php echo htmlspecialchars($email); ?>">
                        <?php if(!empty($error4)): ?> <span class="error-msg"><?php echo $error4; ?></span> <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="address" class="form-control" placeholder="location" value="<?php echo htmlspecialchars($address); ?>">
                        <?php if(!empty($error8)): ?> <span class="error-msg"><?php echo $error8; ?></span> <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Permanent Account Number (PAN)</label>
                        <input type="text" name="pan" class="form-control" placeholder="9-Digit Registry Code" value="<?php echo htmlspecialchars($pan); ?>">
                        <?php if(!empty($error5)): ?> <span class="error-msg"><?php echo $error5; ?></span> <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Operating License Registration No.</label>
                        <input type="text" name="license" class="form-control" placeholder="Legal operational reference code" value="<?php echo htmlspecialchars($license); ?>">
                        <?php if(!empty($error6)): ?> <span class="error-msg"><?php echo $error6; ?></span> <?php endif; ?>
                    </div>

                    <div class="form-group full-width-field">
                        <label>Industry Operation Classification</label>
                        <select name="category" class="form-control">
                            <option value="">-- Choose Category --</option>
                            <option value="Manufacturing" <?php if($category == "Manufacturing") echo "selected"; ?>>Manufacturing Sector</option>
                            <option value="IT" <?php if($category == "IT") echo "selected"; ?>>Information Technology (IT)</option>
                            <option value="Service" <?php if($category == "Service") echo "selected"; ?>>Service / Logistics Providers</option>
                            <option value="Other" <?php if($category == "Other") echo "selected"; ?>>Other Miscellaneous Channels</option>
                        </select>
                        <?php if(!empty($error7)): ?> <span class="error-msg"><?php echo $error7; ?></span> <?php endif; ?>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="register" class="btn-submit">Register Account</button>
                        <a href="index.php" class="btn-cancel">Return Back</a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- Corporate Footer Module Setup -->
    <div class="footer">
        <p>&copy; 2026 Created by Maneesh and Pratik. Online Job Finding System. All Rights Reserved.</p>
    </div>

</body>
</html>