<?php
include "database.php"; 

$error1 = $error2 = $error3 = "";
$username = "";

if (isset($_POST["register"])) {

    // Sanitize input variables securely
    $username = mysqli_real_escape_string($con, trim($_POST["username"]));
    $password = trim($_POST["password"]);

    // Validation
    if (empty($username)) {
        $error1 = "*Username is required";
    }
    if (empty($password)) {
        $error2 = "*Password is required";
    }

    // Check if username exists inside administrative table securely
    if (!empty($username) && empty($error1)) {
        $check_user = mysqli_query($con, "SELECT * FROM admin WHERE username = '$username'");
        if ($check_user && mysqli_num_rows($check_user) > 0) {
            $error1 = "*Username already exists";
        }
    }

    // If no errors, insert into DB with secure hashing
    if (empty($error1) && empty($error2)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO admin (username, password) VALUES ('$username', '$hashed_password')";
        $res = mysqli_query($con, $sql);

        if ($res) {
            echo "<script>alert('Admin registered successfully!'); window.location='adminlogin.php';</script>";
            exit;
        } else {
            $error3 = "Database error structural failure: " . mysqli_error($con);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration | Online Job Finding System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8f9fa; color: #333; display: flex; flex-direction: column; min-height: 100vh; }

        /* Modern Administrative Top Navigation Bar */
        .navbar { background: #ffffff; border-bottom: 1px solid #e1e4e8; padding: 18px 40px; display: flex; justify-content: space-between; align-items: center; }
        .logo-area h1 { font-size: 20px; color: #111; font-weight: 700; }
        .logo-area span { color: #00b074; }
        .nav-right-btn { background: #fff; border: 1px solid #cbd5e1; color: #475569; padding: 8px 18px; border-radius: 6px; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: 0.2s; }
        .nav-right-btn:hover { background: #f1f5f9; color: #1e293b; }

        /* Center Control Core Wrapper */
        .main-wrapper { flex: 1; display: flex; justify-content: center; align-items: center; padding: 40px 20px; }
        
        .form-container { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 40px; width: 100%; max-width: 480px; box-shadow: 0 4px 25px rgba(0,0,0,0.02); border-top: 5px solid #1e293b; }
        
        .form-container h2 { font-size: 24px; font-weight: 700; color: #1e293b; margin-bottom: 6px; text-align: left; }
        .form-container p { font-size: 14px; color: #64748b; margin-bottom: 35px; text-align: left; }

        /* Database Execution Errors Banner */
        .db-error-alert { background: #fff5f5; border: 1px solid #fee2e2; color: #de3e3e; border-radius: 6px; padding: 12px; font-size: 14px; font-weight: 500; margin-bottom: 25px; }

        /* Input Group Stack Configuration Layout */
        .form-stack { display: flex; flex-direction: column; gap: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 13.5px; font-weight: 600; color: #475569; }
        
        .form-control { width: 100%; padding: 12px 14px; font-size: 14px; border: 1px solid #cbd5e1; border-radius: 6px; background-color: #fff; color: #334155; transition: border-color 0.2s; }
        .form-control:focus { outline: none; border-color: #1e293b; box-shadow: 0 0 0 3px rgba(30, 41, 59, 0.1); }
        .form-control::placeholder { color: #94a3b8; }

        .error-msg { font-size: 12px; color: #de3e3e; font-weight: 500; margin-top: 2px; }

        /* Action Buttons Grid Setup Panels */
        .form-actions { display: flex; flex-direction: column; gap: 12px; margin-top: 15px; padding-top: 25px; border-top: 1px solid #f1f5f9; }
        .btn-submit { background: #1e293b; color: white; border: none; padding: 12px; border-radius: 6px; font-size: 14.5px; font-weight: 700; cursor: pointer; text-align: center; transition: 0.2s; width: 100%; }
        .btn-submit:hover { background: #0f172a; }
        
        .btn-secondary { background: #f8fafc; color: #475569; border: 1px solid #cbd5e1; padding: 12px; border-radius: 6px; font-size: 14.5px; font-weight: 600; text-decoration: none; text-align: center; transition: 0.2s; width: 100%; cursor: pointer; }
        .btn-secondary:hover { background: #e2e8f0; color: #1e293b; }

        /* System Footer alignment */
        .footer { background: #111; color: #888; padding: 25px 20px; text-align: center; border-top: 1px solid #222; margin-top: auto; width: 100%; }
        .footer p { font-size: 13.5px; }
    </style>
</head>
<body>

    <!-- Corporate Navbar Frame Layout -->
    <div class="navbar">
        <div class="logo-area">
            <h1>Online <span>Job Find</span></h1>
        </div>
        <a href="adminlogin.php" class="nav-right-btn">Admin Login</a>
    </div>

    <!-- Main Registration Window Box Area -->
    <div class="main-wrapper">
        <div class="form-container">
            <h2>Register Administrative Account</h2>
            <p>Establish initial root administrative credentials to manage categories, platforms, companies, and global system configurations.</p>

            <?php if (!empty($error3)): ?>
                <div class="db-error-alert"><?php echo $error3; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-stack">
                    
                    <div class="form-group">
                        <label>Admin Username</label>
                        <input type="text" name="username" class="form-control" placeholder="Enter secure admin handle" value="<?php echo htmlspecialchars($username); ?>">
                        <?php if(!empty($error1)): ?> <span class="error-msg"><?php echo $error1; ?></span> <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Security Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Create high-entropy access key">
                        <?php if(!empty($error2)): ?> <span class="error-msg"><?php echo $error2; ?></span> <?php endif; ?>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="register" class="btn-submit">Initialize Admin Provisioning</button>
                        <button type="button" class="btn-secondary" onclick="window.location.href='adminlogin.php'">Already Have Management Account</button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- Administrative Footer Component Layout -->
    <div class="footer">
        <p>&copy; 2026 Created by Maneesh and Pratik. Online Job Finding System. All Rights Reserved.</p>
    </div>

</body>
</html>