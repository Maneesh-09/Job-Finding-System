<?php
include "database.php"; // DB context pipeline gateway

$error1 = $error2 = $error3 = $error4 = $error5 = $error6 = $error7 = $error8 = '';
$fname = $lname = $username = $email = $qualification = $gender = '';
$skills = [];
$errordb = '';

if (isset($_POST["register"])) {

    // Sanitize inbound matrix parameters securely against active strains
    $fname         = mysqli_real_escape_string($con, trim($_POST["fname"]));
    $lname         = mysqli_real_escape_string($con, trim($_POST["lname"]));
    $username      = mysqli_real_escape_string($con, trim($_POST["username"]));
    $password      = trim($_POST["password"]);
    $email         = mysqli_real_escape_string($con, trim($_POST["email"]));
    $qualification = mysqli_real_escape_string($con, $_POST["qualification"]);
    $gender        = isset($_POST["gender"]) ? mysqli_real_escape_string($con, $_POST["gender"]) : '';
    $skills        = isset($_POST["skills"]) ? $_POST["skills"] : [];

    // Detailed Verification Testing Suite
    if (empty($fname))         $error1 = "*First name is required";
    if (empty($lname))         $error2 = "*Last name is required";
    if (empty($username))      $error3 = "*Username registration string required";
    if (empty($password))      $error4 = "*Security key password required";
    if (empty($email))         $error5 = "*Personal email identity required";
    if (empty($qualification)) $error6 = "*Academic qualification tier must be chosen";
    if (empty($skills))        $error7 = "*Please select at least one competency skill";
    if (empty($gender))        $error8 = "*Please select your gender classification";

    // Validate dynamic uniqueness footprint inside registry cluster
    if (!empty($username)) {
        $check_user = mysqli_query($con, "SELECT * FROM user WHERE username = '$username'");
        if ($check_user && mysqli_num_rows($check_user) > 0) {
            $error3 = "*Username already reserved within system node";
        }
    }

    // Execute transaction only if error matrices evaluate empty
    if (empty($error1) && empty($error2) && empty($error3) && empty($error4) && empty($error5) && empty($error6) && empty($error7) && empty($error8)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        // Escape array mapping strings
        $sanitized_skills = array_map(function($item) use ($con) {
            return mysqli_real_escape_string($con, $item);
        }, $skills);
        $skills_str = implode(", ", $sanitized_skills);

        $sql = "INSERT INTO user (fname, lname, username, password, email, qualification, skills, gender, datecreated) 
                VALUES ('$fname', '$lname', '$username', '$hashed_password', '$email', '$qualification', '$skills_str', '$gender', CURRENT_TIMESTAMP())";
        $res = mysqli_query($con, $sql);

        if ($res) {
            echo "<script>alert('User Profile Registration Successful!'); window.location='index.php';</script>";
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
    <title>User Registration | Online Job Finding System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8f9fa; color: #333; display: flex; flex-direction: column; min-height: 100vh; }

        /* Modern Top Navigation Bar */
        .navbar { background: #ffffff; border-bottom: 1px solid #e1e4e8; padding: 18px 40px; display: flex; justify-content: space-between; align-items: center; }
        .logo-area h1 { font-size: 20px; color: black; font-weight: 700; }
        .logo-area span { color: red; }
        .nav-right-btn { background: #002fb0; border: 1px solid #cbd5e1; color: white; padding: 8px 18px; border-radius: 6px; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: 0.2s; }
        .nav-right-btn:hover { background: #b02900; color: white; }

        /* Main View Frame Architecture */
        .main-wrapper { flex: 1; display: flex; justify-content: center; align-items: center; padding: 40px 20px; }
        
        .form-container { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 40px; width: 100%; max-width: 700px; box-shadow: black; border-top: 5px solid #00b074; }
        
        .form-container h2 { font-size: 24px; font-weight: 700; color: #1e293b; margin-bottom: 6px; }
        .form-container p { font-size: 14px; color: #1e2227; margin-bottom: 30px; }

        .db-error-alert { background: #fff5f5; border: 1px solid #fee2e2; color: #de3e3e; border-radius: 6px; padding: 12px; font-size: 14px; font-weight: 500; margin-bottom: 20px; }

        /* Complex Grid Core Mapping Engine */
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 18px 20px; }
        .full-width-field { grid-column: span 2; }

        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-label { font-size: 13.5px; font-weight: 600; color: #475569; }
        
        .form-control { width: 100%; padding: 11px 14px; font-size: 14px; border: 1px solid #cbd5e1; border-radius: 6px; background-color: #fff; color: #334155; transition: border-color 0.2s; outline: none; }
        .form-control:focus { border-color: black; box-shadow: black; }
        
        

        /* Input Custom Matrices Layout Grid */
        .selection-matrix-group { display: flex; flex-wrap: wrap; gap: 14px; background: #f8fafc; border: 1px solid #e2e8f0; padding: 14px; border-radius: 8px; width: 100%; }
        .selection-label-item { display: inline-flex; align-items: center; gap: 8px; font-size: 13.5px; color: #334155; cursor: pointer; }
        .selection-label-item input { width: 16px; height: 16px; accent-color: red; cursor: pointer; }

        .error-msg { font-size: 12px; color: #de3e3e; font-weight: 500; margin-top: 2px; }

        /* Actions Elements Processing Deck */
        .form-actions { display: flex; flex-direction: column; gap: 12px; margin-top: 25px; padding-top: 20px; border-top: 1px solid #f1f5f9; grid-column: span 2; }
        .btn-submit { background: #002fb0; color: white; border: none; padding: 12px; border-radius: 6px; font-size: 14.5px; font-weight: 700; cursor: pointer; text-align: center; transition: 0.2s; width: 100%; }
        .btn-submit:hover { background: #b02900; }
        
        .switch-flow-text { font-size: 14px; color: #262b33; text-align: center; margin-top: 8px; }
        .switch-flow-text a { color: #002fb0; text-decoration: none; font-weight: 700; }
        .switch-flow-text a:hover { text-decoration: underline; }

        @media(max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
            .full-width-field { grid-column: span 1; }
        }

        /* Corporate Architecture System Footer Layout */
        .footer { background: #111; color: white; padding: 25px 20px; text-align: center; border-top: 1px solid #222; margin-top: auto; width: 100%; }
        .footer p { font-size: 13.5px; }
    </style>
</head>
<body>

    <!-- Corporate Navbar Module -->
    <div class="navbar">
        <div class="logo-area">
            <h1>Online <span>Job Find</span></h1>
        </div>
        <a href="index.php" class="nav-right-btn">Login</a>
    </div>

    <!-- Main Registration Matrix Framework -->
    <div class="main-wrapper">
        <div class="form-container">
            <h2>User Account Registration</h2>
            <p>Deploy your profile structural matrix variables parameters into the live recruitment registry indexes.</p>

            <?php if (!empty($errordb)): ?>
                <div class="db-error-alert"><?php echo $errordb; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-grid">
                    
                    <div class="form-group">
                        <label class="form-label">First Name</label>
                        <input type="text" name="fname" class="form-control" placeholder="first name" value="<?php echo htmlspecialchars($fname); ?>">
                        <?php if(!empty($error1)): ?> <span class="error-msg"><?php echo $error1; ?></span> <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="lname" class="form-control" placeholder="last name" value="<?php echo htmlspecialchars($lname); ?>">
                        <?php if(!empty($error2)): ?> <span class="error-msg"><?php echo $error2; ?></span> <?php endif; ?>
                    </div>

                    <div class="form-group full-width-field">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" placeholder="username" value="<?php echo htmlspecialchars($username); ?>">
                        <?php if(!empty($error3)): ?> <span class="error-msg"><?php echo $error3; ?></span> <?php endif; ?>
                    </div>

                    <div class="form-group full-width-field">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="passcode">
                        <?php if(!empty($error4)): ?> <span class="error-msg"><?php echo $error4; ?></span> <?php endif; ?>
                    </div>

                    <div class="form-group full-width-field">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control" placeholder="maneesh@example.com" value="<?php echo htmlspecialchars($email); ?>">
                        <?php if(!empty($error5)): ?> <span class="error-msg"><?php echo $error5; ?></span> <?php endif; ?>
                    </div>

                    <!-- Gender Selection Matrix Configured Correctly -->
                    <div class="form-group full-width-field">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-control">
                            <option value="">--select gender--</option>
                            <option value="male" <?php if ($gender == "male") echo "selected"; ?>>Male </option>
                            <option value="female" <?php if ($gender == "female") echo "selected"; ?>>Female</option>
                            <option value="other" <?php if ($gender == "other") echo "selected"; ?>>Other</option>
                        </select>
                        <?php if(!empty($error8)): ?> <span class="error-msg"><?php echo $error8; ?></span> <?php endif; ?>
                    </div>

                    <!-- Qualification Tier Options Framework -->
                    <div class="form-group full-width-field">
                        <label class="form-label">Qualification</label>
                        <select name="qualification" class="form-control">
                            <option value="">-- select qualification--</option>
                            <option value="High School" <?php if ($qualification == "High School") echo "selected"; ?>>High School </option>
                            <option value="+2" <?php if ($qualification == "+2") echo "selected"; ?>>Intermediate (+2 standard)</option>
                            <option value="Bachelor" <?php if ($qualification == "Bachelor") echo "selected"; ?>>Bachelor's Degree </option>
                            <option value="Master" <?php if ($qualification == "Master") echo "selected"; ?>>Master's Degree</option>
                            <option value="Other" <?php if ($qualification == "Other") echo "selected"; ?>>Other Specialized</option>
                        </select>
                        <?php if(!empty($error6)): ?> <span class="error-msg"><?php echo $error6; ?></span> <?php endif; ?>
                    </div>

                    <!-- Skills Inventory Node Repository -->
                    <div class="form-group full-width-field">
                        <label class="form-label">Technical Skills</label>
                        <div class="selection-matrix-group">
                            <?php
                            $available_skills = ["HTML", "CSS", "JavaScript", "PHP", "MySQL", "Python", "Java", "Ruby", "Kubernet", "WordPress", "C++", "Rust", "Dart", "TypeScript", "Other"];
                            foreach ($available_skills as $sk) {
                                $checked = (!empty($skills) && in_array($sk, $skills)) ? "checked" : "";
                                echo "<label class='selection-label-item'>
                                        <input type='checkbox' name='skills[]' value='$sk' $checked> $sk
                                      </label>";
                            }
                            ?>
                        </div>
                        <?php if(!empty($error7)): ?> <span class="error-msg"><?php echo $error7; ?></span> <?php endif; ?>
                    </div>

                    <!-- Administrative Submit Controls Deck -->
                    <div class="form-actions">
                        <button type="submit" name="register" class="btn-submit">Register Account</button>
                        <p class="switch-flow-text">Already possess operational parameters identity? <a href="index.php">Log In Here</a></p>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- Footer Segment Component Panel -->
    <div class="footer">
        <p>&copy; 2026 Created by Maneesh and Pratik. Online Job Finding System. All Rights Reserved.</p>
    </div>

</body>
</html>