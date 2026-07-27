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

// Get user ID securely from URL
$uid = isset($_GET["uid"]) ? mysqli_real_escape_string($con, $_GET["uid"]) : '';

if ($uid == '') {
    echo '<script>
            alert("Invalid User ID");
            window.location.href = "userprofile.php";
          </script>';
    exit();
}

// Fetch user data securely
$sql = "SELECT * FROM user WHERE uid='$uid'";
$result = mysqli_query($con, $sql);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo '<script>
            alert("User Profile Not Found");
            window.location.href = "userprofile.php";
          </script>';
    exit();
}

$fname         = $user['fname'];
$lname         = $user['lname'];
$username      = $user['username'];
$email         = $user['email'];
$qualification = $user['qualification'];
$gender        = $user['gender'];
$skills        = explode(", ", $user['skills']); // Convert string sequence to array

$error1 = $error2 = $error3 = $error4 = $error5 = $error6 = $error7 = $error8 = '';
$errordb = '';

if (isset($_POST["update"])) {
    $fname         = mysqli_real_escape_string($con, trim($_POST["fname"]));
    $lname         = mysqli_real_escape_string($con, trim($_POST["lname"]));
    $username      = mysqli_real_escape_string($con, trim($_POST["username"]));
    $password      = trim($_POST["password"]);
    $email         = mysqli_real_escape_string($con, trim($_POST["email"]));
    $qualification = mysqli_real_escape_string($con, $_POST["qualification"]);
    $gender        = isset($_POST["gender"]) ? mysqli_real_escape_string($con, $_POST["gender"]) : '';
    $skills        = isset($_POST["skills"]) ? $_POST["skills"] : [];

    // Check unique record constraints across other instances
    $check_user = mysqli_query($con, "SELECT * FROM user WHERE username='$username' AND uid != '$uid'");
    if ($check_user && mysqli_num_rows($check_user) > 0) {
        $error3 = "*Username already reserved by another user node";
    }

    // Explicit validation rules
    if (empty($fname))         $error1 = "*First name parameter required";
    if (empty($lname))         $error2 = "*Last name parameter required";
    if (empty($username))      $error3 = "*Profile entry username required";
    if (empty($email))         $error5 = "*Valid electronic mail identity required";
    if (empty($qualification)) $error6 = "*Academic qualification must be chosen";
    if (empty($skills))        $error7 = "*Select at least one active profile skill";
    if (empty($gender))        $error8 = "*Gender classification node required";

    if (empty($error1) && empty($error2) && empty($error3) && empty($error4) && empty($error5) && empty($error6) && empty($error7) && empty($error8)) {

        $sanitized_skills = array_map(function($item) use ($con) {
            return mysqli_real_escape_string($con, $item);
        }, $skills);
        $skills_str = implode(", ", $sanitized_skills);

        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE user 
                    SET fname='$fname', lname='$lname', username='$username', password='$hashed_password',
                        email='$email', qualification='$qualification', gender='$gender', skills='$skills_str'
                    WHERE uid='$uid'";
        } else {
            $sql = "UPDATE user 
                    SET fname='$fname', lname='$lname', username='$username',
                        email='$email', qualification='$qualification', gender='$gender', skills='$skills_str'
                    WHERE uid='$uid'";
        }

        $res = mysqli_query($con, $sql);

        if ($res) {
            echo '<script>
                    alert("User Profile Parameters Updated Successfully");
                    window.location.href="userprofile.php";
                  </script>';
            exit;
        } else {
            $errordb = "Transaction Fail: " . mysqli_error($con);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile | Candidate Console</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8f9fa; color: #333; display: flex; min-height: 100vh; }

        /* Left Sidebar Management Matrix */
        .sidebar { width: 260px; background: #1e293b; color: white; display: flex; flex-direction: column; position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; }
        .sidebar-brand { padding: 24px; border-bottom: 1px solid #334155; }
        .sidebar-brand h2 { font-size: 19px; font-weight: 700; color: #ffffff; }
        .sidebar-brand h2 span { color: #00b074; }
        
        .nav-links { list-style: none; padding: 20px 0; display: flex; flex-direction: column; gap: 4px; }
        .nav-links li a { display: block; padding: 12px 24px; color: #94a3b8; text-decoration: none; font-size: 14.5px; font-weight: 500; transition: 0.2s; border-left: 4px solid transparent; }
        .nav-links li a:hover { background: #334155; color: #ffffff; }
        .nav-links li a.active { background: #0f172a; color: #00b074; border-left-color: #00b074; font-weight: 600; }

        /* Core Application Container */
        .app-container { flex: 1; margin-left: 260px; display: flex; flex-direction: column; min-height: 100vh; }

        /* Header Navigation Console */
        .topbar { background: #ffffff; border-bottom: 1px solid #e2e8f0; height: 70px; padding: 0 40px; display: flex; justify-content: space-between; align-items: center; }
        .topbar-title { font-size: 16px; font-weight: 600; color: #64748b; }
        .btn-logout { background: #ef4444; color: white; padding: 8px 16px; border-radius: 6px; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: 0.2s; border: none; cursor: pointer; }
        .btn-logout:hover { background: #dc2626; }

        /* Workspace Form Processing Arena */
        .workspace { padding: 40px; flex: 1; display: flex; justify-content: center; align-items: flex-start; }
        
        .form-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 40px; width: 100%; max-width: 750px; box-shadow: 0 4px 20px rgba(0,0,0,0.01); border-top: 5px solid #00b074; }
        .form-card h2 { font-size: 24px; font-weight: 700; color: #1e293b; margin-bottom: 6px; }
        .form-card p { font-size: 14px; color: #64748b; margin-bottom: 30px; }

        .db-error-alert { background: #fff5f5; border: 1px solid #fee2e2; color: #de3e3e; border-radius: 6px; padding: 12px; font-size: 14px; font-weight: 500; margin-bottom: 20px; }

        /* Two Column Dynamic Alignment Grid */
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .full-width-node { grid-column: span 2; }

        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-label { font-size: 13.5px; font-weight: 600; color: #475569; }
        
        .form-control { width: 100%; padding: 11px 14px; font-size: 14px; border: 1px solid #cbd5e1; border-radius: 6px; background-color: #fff; color: #334155; transition: border-color 0.2s; outline: none; }
        .form-control:focus { border-color: #00b074; box-shadow: 0 0 0 3px rgba(0, 176, 116, 0.1); }
        
        select.form-control { cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23475569'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; background-size: 16px; padding-right: 40px; }

        /* Radio & Checkbox Container Nodes */
        .selection-panel { display: flex; flex-wrap: wrap; gap: 16px; background: #f8fafc; border: 1px solid #e2e8f0; padding: 14px; border-radius: 8px; width: 100%; }
        .selection-item { display: inline-flex; align-items: center; gap: 8px; font-size: 13.5px; color: #334155; cursor: pointer; }
        .selection-item input { width: 16px; height: 16px; accent-color: #00b074; cursor: pointer; }

        .error-msg { font-size: 12px; color: #de3e3e; font-weight: 500; margin-top: 2px; }

        /* Action Controls Layout Section */
        .form-actions { display: flex; align-items: center; gap: 15px; margin-top: 25px; padding-top: 20px; border-top: 1px solid #f1f5f9; grid-column: span 2; }
        .btn-update { background: #00b074; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-size: 14px; font-weight: 700; cursor: pointer; transition: 0.2s; flex: 1; }
        .btn-update:hover { background: #009460; }
        .btn-back { background: #ffffff; color: #475569; border: 1px solid #cbd5e1; padding: 12px 24px; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; text-align: center; transition: 0.2s; }
        .btn-back:hover { background: #f1f5f9; color: #1e293b; }

        @media(max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
            .full-width-node { grid-column: span 1; }
            .form-actions { flex-direction: column; }
            .btn-back { width: 100%; }
        }

        /* Footer Element */
        .footer { background: #ffffff; border-top: 1px solid #e2e8f0; padding: 20px; text-align: center; width: 100%; margin-top: auto; }
        .footer p { font-size: 13.5px; color: #64748b; }
    </style>
</head>
<body>

    <!-- Left Navigation Dashboard Panel -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <h2>Online <span>Job Find</span></h2>
        </div>
        <ul class="nav-links">
            <li><a href="userhomepage.php">Home Dashboard</a></li>
            <li><a href="userviewjobs.php">View Vacant Jobs</a></li>
            <li><a href="userjobrequeststatus.php">Job Request Status</a></li>
            <li><a href="userrejected.php">View Declined Jobs</a></li>
            <li><a href="userprofile.php">Candidate Profile</a></li>
            <li><a href="#" class="active">Updating Profile</a></li>
        </ul>
    </div>

    <!-- Right Component Flow Frame -->
    <div class="app-container">
        
        <!-- Header Top Control Desk -->
        <div class="topbar">
            <div class="topbar-title">Account Modification Node</div>
            <a href="logout.php" class="btn-logout">Secure Logout</a>
        </div>

        <!-- Working Core Application Arena -->
        <div class="workspace">
            <div class="form-card">
                <h2>Modify Profile Parameters</h2>
                <p>Edit your verified system configuration attributes inside the production directory node database.</p>

                <?php if (!empty($errordb)): ?>
                    <div class="db-error-alert"><?php echo $errordb; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-grid">
                        
                        <div class="form-group">
                            <label class="form-label">First Name</label>
                            <input type="text" name="fname" class="form-control" value="<?php echo htmlspecialchars($fname); ?>">
                            <?php if(!empty($error1)): ?> <span class="error-msg"><?php echo $error1; ?></span> <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="lname" class="form-control" value="<?php echo htmlspecialchars($lname); ?>">
                            <?php if(!empty($error2)): ?> <span class="error-msg"><?php echo $error2; ?></span> <?php endif; ?>
                        </div>

                        <div class="form-group full-width-node">
                            <label class="form-label">Profile Identity Username</label>
                            <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>">
                            <?php if(!empty($error3)): ?> <span class="error-msg"><?php echo $error3; ?></span> <?php endif; ?>
                        </div>

                        <div class="form-group full-width-node">
                            <label class="form-label">Account Passphrase (Leave blank to preserve present token)</label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••">
                            <?php if(!empty($error4)): ?> <span class="error-msg"><?php echo $error4; ?></span> <?php endif; ?>
                        </div>

                        <div class="form-group full-width-node">
                            <label class="form-label">Electronic Mail Communication Endpoint</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>">
                            <?php if(!empty($error5)): ?> <span class="error-msg"><?php echo $error5; ?></span> <?php endif; ?>
                        </div>

                        <!-- Gender Options Matrix Configured Correctly -->
                        <div class="form-group full-width-node">
                            <label class="form-label">Gender Identification Map</label>
                            <div class="selection-panel">
                                <label class="selection-item">
                                    <input type="radio" name="gender" value="Male" <?php if (strcasecmp($gender, "Male") == 0) echo "checked"; ?>> Male
                                </label>
                                <label class="selection-item">
                                    <input type="radio" name="gender" value="Female" <?php if (strcasecmp($gender, "Female") == 0) echo "checked"; ?>> Female
                                </label>
                                <label class="selection-item">
                                    <input type="radio" name="gender" value="Other" <?php if (strcasecmp($gender, "Other") == 0) echo "checked"; ?>> Other
                                </label>
                            </div>
                            <?php if(!empty($error8)): ?> <span class="error-msg"><?php echo $error8; ?></span> <?php endif; ?>
                        </div>

                        <!-- Academic Record Node Tier -->
                        <div class="form-group full-width-node">
                            <label class="form-label">Academic Qualification Level</label>
                            <select name="qualification" class="form-control">
                                <option value="">-- Select Valid Education Node --</option>
                                <option value="High School" <?php if ($qualification == "High School") echo "selected"; ?>>High School Standard</option>
                                <option value="+2" <?php if ($qualification == "+2") echo "selected"; ?>>Intermediate (+2 Tier)</option>
                                <option value="Bachelor" <?php if ($qualification == "Bachelor") echo "selected"; ?>>Bachelor Degree Matrix</option>
                                <option value="Master" <?php if ($qualification == "Master") echo "selected"; ?>>Master Graduate Framework</option>
                                <option value="Other" <?php if ($qualification == "Other") echo "selected"; ?>>Other Specialized Form</option>
                            </select>
                            <?php if(!empty($error6)): ?> <span class="error-msg"><?php echo $error6; ?></span> <?php endif; ?>
                        </div>

                        <!-- Technical Competencies Checkbox Engine -->
                        <div class="form-group full-width-node">
                            <label class="form-label">Technical Skills Inventory Mapping (Select Multiple)</label>
                            <div class="selection-panel">
                                <?php
                                $all_skills = ["HTML", "CSS", "JavaScript", "PHP", "MySQL", "Python", "Java", "C++", "Other"];
                                foreach ($all_skills as $skill) {
                                    $checked = in_array($skill, $skills) ? "checked" : "";
                                    echo "<label class='selection-item'>
                                            <input type='checkbox' name='skills[]' value='$skill' $checked> $skill
                                          </label>";
                                }
                                ?>
                            </div>
                            <?php if(!empty($error7)): ?> <span class="error-msg"><?php echo $error7; ?></span> <?php endif; ?>
                        </div>

                        <!-- System Configuration Control Actions Panel -->
                        <div class="form-actions">
                            <button type="submit" name="update" class="btn-update">Commit Profile Modification</button>
                            <a href="userprofile.php" class="btn-back">Cancel & Go Back</a>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        <!-- System Architecture Footnotes Layout -->
        <div class="footer">
            <p>&copy; 2026 Created by Maneesh and Pratik. Online Job Finding System. All Rights Reserved.</p>
        </div>
    </div>

</body>
</html>