<?php
include "database.php"; // DB Connection log route

$error1 =$error2 = $error3 =$error4 = $error5 = '';$fname = $lname =$username = $email = '';$errordb = '';

if (isset($_POST["register"])) {

    // Sanitize input variables safely against injection strains
    $fname    = mysqli_real_escape_string($con, trim($_POST["fname"]));
    $lname    = mysqli_real_escape_string($con, trim($_POST["lname"]));
    $username = mysqli_real_escape_string($con, trim($_POST["username"]));
    $password = trim($_POST["password"]);
    $email    = mysqli_real_escape_string($con, trim($_POST["email"]));

    // Check if candidate username is already mapped inside DB
    if (!empty($username)) {
        $check_user = mysqli_query($con, "SELECT * FROM register WHERE username = '$username'");
        if ($check_user && mysqli_num_rows($check_user) > 0) {$error3 = "*Username already taken by another seeker";
        }
    }

    // Input validation metrics
    if (empty($fname))$error1 = "*First name is required";
    if (empty($lname))$error2 = "*Last name is required";
    if (empty($username))$error3 = "*Username is required";
    if (empty($password))$error4 = "*Password is required";
    if (empty($email))$error5 = "*Email address is required";

    // Commit records if error array logs are empty
    if (empty($error1) && empty($error2) && empty($error3) && empty($error4) && empty($error5)) {$hashed_password = password_hash($password, PASSWORD_BCRYPT);$sql = "INSERT INTO register (fname, lname, username, password, email) 
                VALUES ('$fname', '$lname', '$username', '$hashed_password', '$email')";
        $res = mysqli_query($con,$sql);

        if ($res) {
            echo "<script>alert('Candidate Profile Registration successful!'); window.location='index.php';</script>";
            exit;
        } else {
            $errordb = "System Transaction Failure: " . mysqli_error($con);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Registration | Online Job Finding System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8f9fa; color: #333; display: flex; flex-direction: column; min-height: 100vh; }

        /* Modern Top Navigation Bar */
        .navbar { background: #ffffff; border-bottom: 1px solid #e1e4e8; padding: 18px 40px; display: flex; justify-content: space-between; align-items: center; }
        .logo-area h1 { font-size: 20px; color: #111; font-weight: 700; }
        .logo-area span { color: #00b074; }
        .nav-right-btn { background: #fff; border: 1px solid #cbd5e1; color: #475569; padding: 8px 18px; border-radius: 6px; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: 0.2s; }
        .nav-right-btn:hover { background: #f1f5f9; color: #1e293b; }

        /* Main View Wrapper */
        .main-wrapper { flex: 1; display: flex; justify-content: center; align-items: center; padding: 40px 20px; }
        
        .form-container { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 40px; width: 100%; max-width: 600px; box-shadow: 0 4px 25 rgba(0,0,0,0.02); border-top: 5px solid #00b074; }
        
        .form-container h2 { font-size: 24px; font-weight: 700; color: #1e293b; margin-bottom: 6px; text-align: left; }
        .form-container p { font-size: 14px; color: #64748b; margin-bottom: 30px; text-align: left; }

        /* DB Level Error Alert Box */
        .db-error-alert { background: #fff5f5; border: 1px solid #fee2e2; color: #de3e3e; border-radius: 6px; padding: 12px; font-size: 14px; font-weight: 500; margin-bottom: 20px; }

        /* Form Components Layout Engine */
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px 20px; }
        .full-width-field { grid-column: span 2; }

        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 13.5px; font-weight: 600; color: #475569; }
        
        .form-control { width: 100%; padding: 11px 14px; font-size: 14px; border: 1px solid #cbd5e1; border-radius: 6px; background-color: #fff; color: #334155; transition: border-color 0.2s; }
        .form-control:focus { outline: none; border-color: #00b074; box-shadow: 0 0 0 3px rgba(0, 176, 116, 0.1); }
        .form-control::placeholder { color: #94a3b8; }

        .error-msg { font-size: 12px; color: #de3e3e; font-weight: 500; margin-top: 2px; }

        /* Control Action Buttons Wrapper */
        .form-actions { display: flex; flex-direction: column; gap: 12px; margin-top: 25px; padding-top: 20px; border-top: 1px solid #f1f5f9; grid-column: span 2; }
        .btn-submit { background: #00b074; color: white; border: none; padding: 12px; border-radius: 6px; font-size: 14.5px; font-weight: 700; cursor: pointer; text-align: center; transition: 0.2s; width: 100%; }
        .btn-submit:hover { background: #009460; }
        
        .switch-flow-text { font-size: 14px; color: #64748b; text-align: center; margin-top: 8px; }
        .switch-flow-text a { color: #00b074; text-decoration: none; font-weight: 700; }
        .switch-flow-text a:hover { text-decoration: underline; }

        /* Premium System Footer Layout */
        .footer { background: #111; color: #888; padding: 25px 20px; text-align: center; border-top: 1px solid #222; margin-top: auto; width: 100%; }
        .footer p { font-size: 13.5px; }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="logo-area">
            <h1>Online <span>Job Find</span></h1>
        </div>
        <a href="index.php" class="nav-right-btn">Login System</a>
    </div>

    <div class="main-wrapper">
        <div class="form-container">
            <h2>Create Candidate Account</h2>
            <p>Register your job seeker parameters to search, discover, and instantly apply for dynamic corporate vacant circulars.</p>

            <?php if (!empty($errordb)): ?>
                <div class="db-error-alert"><?php echo $errordb; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-grid">
                    
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="fname" class="form-control" placeholder="John" value="<?php echo htmlspecialchars($fname); ?>">
                        <?php if(!empty($error1)): ?> <span class="error-msg"><?php echo $error1; ?></span> <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="lname" class="form-control