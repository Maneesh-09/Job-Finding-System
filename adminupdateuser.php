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

// Get user ID from URL
$uid = $_GET["uid"] ?? '';

if ($uid == '') {
    echo '<script>
            alert("Invalid User ID");
            window.location.href = "adminviewuser.php";
          </script>';
    exit();
}

$uid = intval($uid); // Prevent SQL Injection
// Fetch user data
$sql = "SELECT * FROM user WHERE uid='$uid'";
$result = mysqli_query($con, $sql);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo '<script>
            alert("User not found!");
            window.location.href = "adminviewuser.php";
          </script>';
    exit();
}

$fname = $user['fname'];
$lname = $user['lname'];
$username = $user['username'];
$email = $user['email'];
$qualification = $user['qualification'];
$gender = $user['gender'];
$skills = !empty($user['skills']) ? explode(", ", $user['skills']) : []; // convert string to array

$error1 = $error2 = $error3 = $error4 = $error5 = $error6 = $error7 = $error8 = '';
$errordb = '';

if (isset($_POST["update"])) {
    $fname = mysqli_real_escape_string($con, $_POST["fname"]);
    $lname = mysqli_real_escape_string($con, $_POST["lname"]);
    $username = mysqli_real_escape_string($con, $_POST["username"]);
    $password = $_POST["password"];
    $email = mysqli_real_escape_string($con, $_POST["email"]);
    $qualification = mysqli_real_escape_string($con, $_POST["qualification"]);
    $gender = mysqli_real_escape_string($con, $_POST["gender"] ?? '');
    $skills = isset($_POST["skills"]) ? $_POST["skills"] : [];

    // Check uniqueness
    $check_user = mysqli_query($con, "SELECT * FROM user WHERE username='$username' AND uid != '$uid'");
    if ($check_user && mysqli_num_rows($check_user) > 0)
        $error3 = "*Username already taken";

    // Validation
    if (empty($fname)) $error1 = "*First name is required";
    if (empty($lname)) $error2 = "*Last name is required";
    if (empty($username)) $error3 = "*Username is required";
    if (empty($email)) $error5 = "*Email is required";
    if (empty($qualification)) $error6 = "*Qualification must be chosen";
    if (empty($skills)) $error7 = "*Please select at least one skill";
    if (empty($gender)) $error8 = "*Please select your gender";

    if (
        empty($error1) && empty($error2) && empty($error3) && empty($error4) && empty($error5) &&
        empty($error6) && empty($error7) && empty($error8)
    ) {

        $skills_str = implode(", ", $skills);

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
                    alert("User updated successfully");
                    window.location.href="adminviewuser.php";
                  </script>';
            exit;
        } else {
            $errordb = "Update failed: " . mysqli_error($con);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Edit User</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8f9fa; color: #333; display: flex; flex-direction: column; min-height: 100vh; }

        /* Modern Admin Navbar */
        .navbar { background: #ffffff; border-bottom: 1px solid #e1e4e8; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
        .logo-area h1 { font-size: 20px; color: #111; font-weight: 700; }
        .logo-area span { color: #00b074; }
        
        .admin-profile-section { display: flex; align-items: center; gap: 15px; }
        .admin-badge { background: #eefbf7; color: #00b074; font-weight: 700; padding: 6px 14px; border-radius: 20px; font-size: 13.5px; border: 1px solid rgba(0, 176, 116, 0.2); }
        
        .btn-logout { background: #fff; border: 1px solid #de3e3e; color: #de3e3e; padding: 6px 16px; border-radius: 6px; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: 0.3s; }
        .btn-logout:hover { background: #de3e3e; color: #fff; }

        /* Dashboard Main Layout */
        .dashboard-layout { display: flex; flex: 1; }

        /* Sidebar Design */
        .sidebar { width: 280px; background: #ffffff; border-right: 1px solid #e2e8f0; padding: 30px 15px; }
        .nav-links { list-style: none; display: flex; flex-direction: column; gap: 6px; }
        .nav-links li a { display: block; padding: 12px 16px; color: #555; font-size: 14.5px; font-weight: 600; text-decoration: none; border-radius: 8px; transition: 0.3s; }
        .nav-links li a:hover { color: #00b074; background: #eefbf7; }
        .nav-links li a.active { background: #00b074; color: white !important; }

        /* Content Wrapper */
        .main-content { flex: 1; padding: 40px; background-color: #f8f9fa; }

        /* Form Card Styling */
        .form-container { max-width: 850px; width: 100%; margin: 0 auto; }
        .form-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); }
        .form-card h2 { font-size: 22px; font-weight: 700; margin-bottom: 25px; color: #1a1a1a; border-bottom: 2px solid #f1f1f1; padding-bottom: 12px; }
        .form-card h2 span { color: #00b074; }

        /* 2-Column Responsive Form Grid */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .full-width { grid-column: span 2; }

        /* Form Elements */
        .form-group { margin-bottom: 5px; }
        .form-label { display: block; font-size: 14px; font-weight: 600; color: #4a5568; margin-bottom: 8px; }
        
        .form-input { width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; outline: none; transition: 0.3s; background-color: #f8fafc; }
        .form-input:focus { border-color: #00b074; background-color: #fff; box-shadow: 0 0 0 3px rgba(0, 176, 116, 0.1); }

        /* Checkbox & Radio Alignment UI */
        .selection-container { display: flex; flex-wrap: wrap; gap: 15px; background: #f8fafc; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; align-items: center; }
        .selection-container label { display: flex; align-items: center; gap: 6px; font-size: 14.5px; color: #334155; cursor: pointer; }
        .selection-container input { width: 16px; height: 16px; accent-color: #00b074; cursor: pointer; }

        /* Error Text */
        .error { color: #de3e3e; font-size: 12.5px; font-weight: 600; margin-top: 5px; }
        .db-error { background: #fdf2f2; border: 1px solid #f8b4b4; color: #9b1c1c; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; font-weight: 600; }

        /* Action Buttons Row */
        .form-actions { display: flex; gap: 15px; margin-top: 20px; border-top: 1px solid #eee; padding-top: 25px; grid-column: span 2; }
        
        .btn-submit { background: #00b074; color: white; border: none; padding: 13px 28px; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-submit:hover { background: #009460; }
        
        .btn-cancel { background: white; border: 1px solid #cbd5e1; color: #4a5568; padding: 13px 28px; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; transition: 0.3s; text-decoration: none; text-align: center; }
        .btn-cancel:hover { background: #f8fafc; border-color: #b8c5d6; }

        /* Footer */
        .footer { background: #111; color: #888; padding: 25px 20px; text-align: center; border-top: 1px solid #222; margin-top: auto; }
        .footer p { font-size: 13.5px; }
    </style>
</head>

<body>

    <!-- Admin Top Navbar -->
    <div class="navbar">
        <div class="logo-area">
            <h1>Online <span>Job Find</span></h1>
        </div>
        <div class="admin-profile-section">
            <span class="admin-badge">🛡️ Admin Panel</span>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </div>

    <!-- Dashboard Body Container -->
    <div class="dashboard-layout">
        
        <!-- Left Sidebar Navigation -->
        <div class="sidebar">
            <ul class="nav-links">
                <li><a href="adminhomepage.php">Home</a></li>
                <li><a href="adminviewcompany.php">View Companies</a></li>
                <li><a href="adminviewuser.php">View Users</a></li>
                <li><a href="adminviewjobs.php">View Jobs</a></li>
                <li><a href="adminviewpendingrequest.php">All Pending Applications</a></li>
                <li><a href="allacceptedapplicant.php">All Accepted Applicants</a></li>
                <li><a href="allrejectedapplicant.php">All Rejected Applicants</a></li>
            </ul>
        </div>

        <!-- Right Content Management Area -->
        <div class="main-content">
            <div class="form-container">
                <div class="form-card">
                    <h2>Update Candidate <span>Profile</span></h2>

                    <?php if (!empty($errordb)) { ?>
                        <div class="db-error"><?php echo $errordb; ?></div>
                    <?php } ?>

                    <form method="post">
                        <div class="form-grid">
                            
                            <!-- First Name -->
                            <div class="form-group">
                                <label class="form-label">First Name</label>
                                <input type="text" name="fname" class="form-input" value="<?php echo htmlspecialchars($fname); ?>">
                                <div class="error"><?php echo $error1; ?></div>
                            </div>

                            <!-- Last Name -->
                            <div class="form-group">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="lname" class="form-input" value="<?php echo htmlspecialchars($lname); ?>">
                                <div class="error"><?php echo $error2; ?></div>
                            </div>

                            <!-- Username -->
                            <div class="form-group">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-input" value="<?php echo htmlspecialchars($username); ?>">
                                <div class="error"><?php echo $error3; ?></div>
                            </div>

                            <!-- Password -->
                            <div class="form-group">
                                <label class="form-label">Password (Leave blank to keep old)</label>
                                <input type="password" name="password" class="form-input" placeholder="••••••••">
                                <div class="error"><?php echo $error4; ?></div>
                            </div>

                            <!-- Email -->
                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($email); ?>">
                                <div class="error"><?php echo $error5; ?></div>
                            </div>

                            <!-- Qualification -->
                            <div class="form-group">
                                <label class="form-label">Qualification</label>
                                <select name="qualification" class="form-input">
                                    <option value="">-- Select Qualification --</option>
                                    <option value="High School" <?php if ($qualification == "High School") echo "selected"; ?>>High School</option>
                                    <option value="+2" <?php if ($qualification == "+2") echo "selected"; ?>>+2</option>
                                    <option value="Bachelor" <?php if ($qualification == "Bachelor") echo "selected"; ?>>Bachelor Degree</option>
                                    <option value="Master" <?php if ($qualification == "Master") echo "selected"; ?>>Master Degree</option>
                                    <option value="Other" <?php if ($qualification == "Other") echo "selected"; ?>>Other</option>
                                </select>
                                <div class="error"><?php echo $error6; ?></div>
                            </div>

                            <!-- Gender Select (Full Width Layout) -->
                            <div class="form-group full-width">
                                <label class="form-label">Gender Identity</label>
                                <div class="selection-container">
                                    <label><input type="radio" name="gender" value="Male" <?php if ($gender == "Male") echo "checked"; ?>> Male</label>
                                    <label><input type="radio" name="gender" value="Female" <?php if ($gender == "Female") echo "checked"; ?>> Female</label>
                                    <label><input type="radio" name="gender" value="Other" <?php if ($gender == "Other") echo "checked"; ?>> Other</label>
                                </div>
                                <div class="error"><?php echo $error8; ?></div>
                            </div>

                            <!-- Skills Checkbox Selection (Full Width Layout) -->
                            <div class="form-group full-width">
                                <label class="form-label">Core Technical Skills</label>
                                <div class="selection-container">
                                    <?php
                                    $all_skills = ["HTML", "CSS", "JavaScript", "PHP", "MySQL", "Python", "Java", "C++", "Other"];
                                    foreach ($all_skills as $skill) {
                                        $checked = in_array($skill, $skills) ? "checked" : "";
                                        echo "<label><input type='checkbox' name='skills[]' value='" . htmlspecialchars($skill) . "' $checked> " . htmlspecialchars($skill) . "</label>";
                                    }
                                    ?>
                                </div>
                                <div class="error"><?php echo $error7; ?></div>
                            </div>

                            <!-- Action Buttons inside grid -->
                            <div class="form-actions">
                                <button type="submit" name="update" class="btn-submit">Update User</button>
                                <a href="adminviewuser.php" class="btn-cancel">Go Back</a>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Footer -->
    <div class="footer">
        <p>&copy; 2026 Created by Maneesh and Pratik. Online Job Finding System. All Rights Reserved.</p>
    </div>

</body>
</html>