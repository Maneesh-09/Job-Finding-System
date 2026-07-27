<?php
session_start();   

include "database.php";

$error1 = $error2 = '';
$username = '';
if (isset($_POST["login"])) {
    $username = mysqli_real_escape_string($con, $_POST["username"]);
    $password = $_POST["password"];
    if (empty($username)) {
        $error1 = "*Username is required";
    }
    if (empty($password)) {
        $error2 = "*Password is required";
    }

    if (empty($error1) && empty($error2)) {
        $sql = "SELECT * FROM user WHERE username = '$username'";
        $result = mysqli_query($con, $sql);

        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            if (password_verify($password, $user['password'])) {
                $_SESSION['username'] = $username;
                header("Location:userhomepage.php");
                exit();
            } else {
                $error2 = " *Invalid password";
            }
        } else {
            $error1 = "*Username not found";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login - Online Job Finding System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8f9fa; color: #333; display: flex; flex-direction: column; min-height: 100vh; }

        /* Navigation Bar */
        .navbar { background: #ffffff; border-bottom: 1px solid #e1e4e8; padding: 15px 50px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
        .logo-area h1 { font-size: 22px; color: #111; font-weight: 700; }
        .logo-area span { color: red; }
        
        .nav-container { display: flex; gap: 20px; align-items: center; }
        .nav-item { padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 15px; font-weight: 600; color: #555; transition: 0.3s; }
        .nav-item:hover { color: #b02900; background: #eefbf7; }
        .nav-item.active { background-color: #b02900; color: white !important; }

        /* Page Banner */
        .page-banner { background: #ffffff; padding: 40px 20px; text-align: center; border-bottom: 1px solid #eee; }
        .page-banner h2 { font-size: 32px; font-weight: 800; color: #111; }
        .page-banner h2 span { color: red; }
        .page-banner p { color: #666; font-size: 15px; margin-top: 5px; }

        /* Main Container for Login */
        .login-container { flex: 1; display: flex; justify-content: center; align-items: center; padding: 60px 20px; }
        
        .login-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 40px; width: 100%; max-width: 450px; box-shadow: black; }
        .login-card h3 { font-size: 24px; font-weight: 700; color: black; margin-bottom: 25px; text-align: center; }
        .login-card h3 span { color: #b02900; }

        /* Form Controls */
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: 14px; font-weight: 600; color: #4a5568; margin-bottom: 8px; }
        
        .form-input { width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; outline: none; transition: 0.3s; background-color: #f8fafc; }
        .form-input:focus { border-color: black; background-color: #fff; box-shadow: black; }
        
        /* Error Styling */
        .error { color: #de3e3e; font-size: 12.5px; font-weight: 600; margin-top: 5px; }

        /* Form Button */
        .btn-submit { width: 100%; background: #002fb0; color: white; border: none; padding: 14px; border-radius: 8px; font-size: 16px; font-weight: 700; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .btn-submit:hover { background: #b02900; }

        /* Link Section */
        .register-link { text-align: center; margin-top: 20px; font-size: 14.5px; color: black; }
        .register-link a { color: #002fb0; text-decoration: none; font-weight: 600; transition: 0.2s; }
        .register-link a:hover { text-decoration: underline; }

        /* Footer */
        .footer { background: #111; color: white; padding: 20px 20px; text-align: center; margin-top: auto; border-top: 1px solid black; }
        .footer p { font-size: 14px; }
    </style>
</head>

<body>

    <!-- Header Navigation -->
    <div class="navbar">
        <div class="logo-area">
            <h1>Online <span>Job Find</span></h1>
        </div>
        <div class="nav-container">
            <a href="index.php" class="nav-item">Home</a>
            <a href="jobs.php" class="nav-item">Jobs</a>
            <a href="admin.php" class="nav-item">Admin</a>
            <a href="user.php" class="nav-item active">User</a>
            <a href="company.php" class="nav-item">Company</a>
        </div>
    </div>

    <!-- Page Title Banner -->
    <div class="page-banner">
        <h2>Job Seeker <span>Portal</span></h2>
        <p>Log in to apply for your dream jobs and keep track of your applications.</p>
    </div>

    <!-- Main Content Area -->
    <div class="login-container">
        <div class="login-card">
            <h3>User <span>Login</span></h3>
            
            <form action="" method="post">
                <!-- Username Input -->
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" class="form-input" value="<?php echo htmlspecialchars($username); ?>">
                    <div class="error"><?php echo $error1; ?></div>
                </div>

                <!-- Password Input -->
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" class="form-input">
                    <div class="error"><?php echo $error2; ?></div>
                </div>

                <!-- Submit Button -->
                <button type="submit" name="login" class="btn-submit">Sign In</button>
            </form>

            <!-- Bottom Link -->
            <div class="register-link">
                Don't have an Account? <a href="userregister.php">Register Here</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; 2026 Created by Maneesh and Pratik. Online Job Finding System. All Rights Reserved.</p>
    </div>

</body>
</html>