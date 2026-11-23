<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/database.php';
$error = '';
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = post_string('username');
    $password = post_string('password', false);
    if ($username === '' || $password === '') {
        $error = 'Please fill in all required fields.';
    } elseif (login_account($con, 'admin', $username, $password)) {
        header('Location: adminhomepage.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Online Job Finding System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8f9fa; color: #333; display: flex; flex-direction: column; min-height: 100vh; }

        /* User Header / Navbar Style */
        .navbar { background: #ffffff; border-bottom: 1px solid #e1e4e8; padding: 15px 50px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
        .logo-area h1 { font-size: 22px; color: #111; font-weight: 700; }
        .logo-area span { color: red; }
        
        .nav-container { display: flex; gap: 20px; align-items: center; }
        .nav-item { padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 15px; font-weight: 600; color: black; transition: 0.2s; }
        .nav-item:hover { color: #b02900; background: #eefbf7; }
        .nav-item.active { background-color: #b02900; color: white !important; }

        

        /* Banner Header */
        .page-banner { background: #ffffff; padding: 35px 20px; text-align: center; border-bottom: 1px solid #edf2f7; }
        .page-banner h2 { font-size: 32px; font-weight: 800; color: #0f172a; }
        .page-banner h2 span { color: red; }
        .page-banner p { color: #64748b; font-size: 15px; margin-top: 5px; }

        /* User Platform Card Form Style */
        .login-container { flex: 1; display: flex; justify-content: center; align-items: center; padding: 60px 20px; }
        .login-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 40px; width: 100%; max-width: 450px; box-shadow: black; }
        .login-card h3 { font-size: 24px; font-weight: 700; color: #1e293b; margin-bottom: 25px; text-align: center; }
        .login-card h3 span { color: #b02900; }

        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: 14px; font-weight: 600; color: #1e293b; margin-bottom: 8px; }
        
        .form-input { width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; outline: none; background-color: #f8fafc; transition: 0.2s; }
        .form-input:focus { border-color: #b02900; background-color: #ffffff; box-shadow: 0 0 0 3px rgba(0, 47, 176, 0.1); }
        
        .error-alert { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 12px; border-radius: 8px; font-size: 13.5px; font-weight: 600; margin-bottom: 20px; text-align: center; }

        .btn-submit { width: 100%; background: #002fb0; color: white; border: none; padding: 13px; border-radius: 8px; font-size: 15.5px; font-weight: 700; cursor: pointer; transition: 0.2s; margin-top: 5px; }
        .btn-submit:hover { background: #b02900; }

        .register-link { text-align: center; margin-top: 20px; font-size: 14.5px; color: black; }
        .register-link a { color: #002fb0; text-decoration: none; font-weight: 600; transition: 0.2s; }
        .register-link a:hover { text-decoration: underline; }
        
        .footer { background: black; color: white; padding: 20px 20px; text-align: center; margin-top: auto; border-top: 1px solid black; }
        .footer p { font-size: 14px; color: white; }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="logo-area">
            <h1>Online <span>Job Find</span></h1>
        </div>
        <div class="nav-container">
            <a href="index.php" class="nav-item">Home</a>
            <a href="jobs.php" class="nav-item">Jobs</a>
            <a href="admin.php" class="nav-item active">Admin</a>
            <a href="user.php" class="nav-item ">User</a>
            <a href="company.php" class="nav-item">Company</a>
        </div>
    </div>

    <div class="page-banner">
        <h2>Admin <span>Portal</span></h2>
        <p>Sign in with your administrative credentials to manage system settings.</p>
    </div>

    <div class="login-container">
        <div class="login-card">
            <h3>Admin <span>Login</span></h3>
            
            <?php if (!empty($error)): ?>
                <div class="error-alert"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="admin.php" method="post">
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter username" class="form-input" value="<?php echo htmlspecialchars($username); ?>" required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password" class="form-input" required>
                </div>

                <button type="submit" name="login" class="btn-submit">Login to Admin Dashboard</button>
                <div class="register-link">
                Don't have an Account? <a href="userregister.php">Register Here</a>
            </div>
            </form>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2026 Created by Maneesh and Pratik. Online Job Finding System. All Rights Reserved.</p>
    </div>

</body>
</html>