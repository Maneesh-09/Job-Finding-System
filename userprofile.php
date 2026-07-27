<?php
session_start();
include "database.php";

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo "<script>alert('Please login first!'); window.location='index.php';</script>";
    exit;
}

$username = mysqli_real_escape_string($con, $_SESSION['username']);

// Fetch user details safely
$query = "SELECT * FROM user WHERE username = '$username'";
$result = mysqli_query($con, $query);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo "<script>alert('User profile record not located!'); window.location='index.php';</script>";
    exit;
}

$error1 = $error2 = $error3 = $error5 = $error6 = $error7 = $error8 = '';

if (isset($_POST['update'])) {
    $fname = mysqli_real_escape_string($con, $_POST['fname']);
    $lname = mysqli_real_escape_string($con, $_POST['lname']);
    $new_username = mysqli_real_escape_string($con, $_POST['username']);
    $password = $_POST['password'];
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $qualification = mysqli_real_escape_string($con, $_POST['qualification']);
    $gender = isset($_POST['gender']) ? mysqli_real_escape_string($con, $_POST['gender']) : '';
    $skills = isset($_POST['skills']) ? mysqli_real_escape_string($con, implode(", ", $_POST['skills'])) : '';

    // Complete Server-Side Input Validation Matrix
    if (empty($fname)) $error1 = "*First name is required";
    if (empty($lname)) $error2 = "*Last name is required";
    if (empty($new_username)) $error3 = "*Username is required";
    if (empty($email)) $error5 = "*Email identity is required";
    if (empty($qualification)) $error6 = "*Academic qualification is required";
    if (empty($skills)) $error7 = "*Select at least one applicable skill";
    if (empty($gender)) $error8 = "*Gender classification is required";

    // Check username uniqueness within scope identity mapping
    $check_user = mysqli_query($con, "SELECT * FROM user WHERE username='$new_username' AND uid != '{$user['uid']}'");
    if ($check_user && mysqli_num_rows($check_user) > 0) {
        $error3 = "*Username already reserved by another account";
    }

    if (
        empty($error1) && empty($error2) && empty($error3) &&
        empty($error5) && empty($error6) && empty($error7) && empty($error8)
    ) {
        // Optional password cryptographic rotation hashing
        $password_sql = '';
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $password_sql = ", password = '$hashed_password'";
        }

        $update_sql = "UPDATE user SET 
            fname = '$fname',
            lname = '$lname',
            username = '$new_username',
            email = '$email',
            qualification = '$qualification',
            gender = '$gender',
            skills = '$skills'
            $password_sql
            WHERE username = '$username'";

        if (mysqli_query($con, $update_sql)) {
            // Hot-swap ongoing active session profile to match state changes
            $_SESSION['username'] = $new_username;
            echo "<script>alert('Candidate Profile records synchronized successfully!'); window.location.href='userprofile.php';</script>";
            exit;
        } else {
            echo "<script>alert('Database pipeline mutation error: " . mysqli_error($con) . "');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Candidate Profile | Online Job Finding System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8f9fa; color: #333; display: flex; min-height: 100vh; }

        /* Modern Sidebar Component Layout */
        .sidebar { width: 260px; background: #1e293b; color: white; display: flex; flex-direction: column; position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; }
        .sidebar-brand { padding: 24px; border-bottom: 1px solid #334155; }
        .sidebar-brand h2 { font-size: 19px; font-weight: 700; color: #ffffff; }
        .sidebar-brand h2 span { color: red; }
        
        .nav-links { list-style: none; padding: 20px 0; display: flex; flex-direction: column; gap: 4px; }
        .nav-links li a { display: block; padding: 12px 24px; color: #94a3b8; text-decoration: none; font-size: 14.5px; font-weight: 500; transition: 0.2s; border-left: 4px solid transparent; }
        .nav-links li a:hover { background: #334155; color: #ffffff; }
        .nav-links li a.active { background: #0f172a; color: white; border-left-color: #002fb0; font-weight: 600; }

        /* Core Application Right Panel Framework */
        .app-container { flex: 1; margin-left: 260px; display: flex; flex-direction: column; min-height: 100vh; }

        /* Top Bar Component Navigation Grid */
        .topbar { background: #ffffff; border-bottom: 1px solid #e2e8f0; height: 70px; padding: 0 40px; display: flex; justify-content: space-between; align-items: center; }
        .topbar-title { font-size: 16px; font-weight: 600; color: #64748b; }
        
        .user-action-area { display: flex; align-items: center; gap: 12px; }
        .btn-profile { background:  #b02900; color: white; padding: 8px 16px; border-radius: 6px; font-size: 13.5px; font-weight: 600; text-decoration: none; border: 1px solid #334155; }
        .btn-profile:hover { background: #002fb0; }
        .btn-logout { background: #002fb0; color: white; padding: 8px 16px; border-radius: 6px; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: 0.2s; border: none; cursor: pointer; }
        .btn-logout:hover { background:  #b02900; }

        /* Workspace Main Layout Engine */
        .workspace { padding: 40px; flex: 1; display: flex; flex-direction: column; align-items: center; }

        .page-heading { font-size: 24px; font-weight: 700; color: #1e293b; margin-bottom: 25px; border-bottom: 2px solid #eefbf7; padding-bottom: 12px; width: 100%; max-width: 700px; text-align: left; }

        /* Modernized Premium Form Card Panel Architecture */
        .form-container-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 35px; width: 100%; max-width: 700px; box-shadow: 0 4px 20px rgba(0,0,0,0.01); display: flex; flex-direction: column; gap: 20px; }
        
        .form-row-split { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group.full-width { grid-column: span 2; }
        
        .form-label { font-size: 14px; font-weight: 600; color: #1e293b; }
        .form-input { width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14.5px; transition: 0.2s; outline: none; background: #ffffff; }
        .form-input:focus { border-color: black; box-shadow: 0 0 0 3px rgba(0, 176, 116, 0.1); }
        
        /* Modern Select Box Override */
        select.form-input { cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23475569'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; background-size: 16px; padding-right: 40px; }

        /* Custom Structured Checkbox/Radio Grid Framework */
        .selection-matrix-group { display: flex; flex-wrap: wrap; gap: 14px; background: #f8fafc; border: 1px solid #e2e8f0; padding: 14px; border-radius: 8px; }
        .selection-label-item { display: inline-flex; align-items: center; gap: 8px; font-size: 14px; color: #334155; cursor: pointer; user-select: none; }
        .selection-label-item input[type="radio"], .selection-label-item input[type="checkbox"] { width: 16px; height: 16px;  cursor: pointer; }

        .error-message-feedback { color: #ef4444; font-size: 12.5px; font-weight: 500; margin-top: 2px; }

        /* Operational Dynamic Action Elements */
        .form-actions-wrapper { display: flex; gap: 15px; margin-top: 15px; border-top: 1px solid #f1f5f9; padding-top: 20px; }
        .btn-action-submit { flex: 2; background: #b02900; color: white; border: none; padding: 12px; border-radius: 6px; font-size: 14.5px; font-weight: 700; cursor: pointer; text-align: center; transition: 0.2s; }
        .btn-action-submit:hover { background: #002fb0; }
        .btn-action-cancel { flex: 1; background: #002fb0; color: white; border: none; padding: 12px; border-radius: 6px; font-size: 14.5px; font-weight: 600; cursor: pointer; text-align: center; transition: 0.2s; text-decoration: none; }
        .btn-action-cancel:hover { background:  #b02900; }

        /* Responsive Breakpoint Logic */
        @media(max-width: 768px) {
            .form-row-split { grid-template-columns: 1fr; gap: 20px; }
            .form-group.full-width { grid-column: span 1; }
        }

        /* Footer Frame */
        .footer { background: #ffffff; border-top: 1px solid #e2e8f0; padding: 20px; text-align: center; width: 100%; margin-top: auto; }
        .footer p { font-size: 13.5px; color: black; }
    </style>
</head>
<body>

    <!-- Left Structural Navigation Menu Frame -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <h2>Online <span>Job Find</span></h2>
        </div>
        <ul class="nav-links">
            <li><a href="userhomepage.php">Home Dashboard</a></li>
            <li><a href="userviewjobs.php">View Vacant Jobs</a></li>
            <li><a href="userjobrequeststatus.php">Job Request Status</a></li>
            <li><a href="useraccepted.php">View Accepted Jobs</a></li>
            <li><a href="userrejected.php">View Rejected Jobs</a></li>
            <li><a href="userprofile.php" class="active">Candidate Profile</a></li>
        </ul>
    </div>

    <!-- Right Presentation Grid Wrapper -->
    <div class="app-container">
        
        <!-- Header Top Console Module -->
        <div class="topbar">
            <div class="topbar-title">Account Security & Authentication Engine</div>
            <div class="user-action-area">
                <a href="userprofile.php" class="btn-profile">View Profile</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>

        <!-- Main Workspace Flow Arena -->
        <div class="workspace">
            <h1 class="page-heading">Modify Profile Identity</h1>

            <form method="post" class="form-container-card">
                
                <!-- Fullname Combined Matrix Rows -->
                <div class="form-row-split">
                    <div class="form-group">
                        <label class="form-label">First Name</label>
                        <input type="text" name="fname" class="form-input" value="<?php echo htmlspecialchars($user['fname']); ?>">
                        <?php if(!empty($error1)) echo "<div class='error-message-feedback'>$error1</div>"; ?>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="lname" class="form-input" value="<?php echo htmlspecialchars($user['lname']); ?>">
                        <?php if(!empty($error2)) echo "<div class='error-message-feedback'>$error2</div>"; ?>
                    </div>
                </div>

                <!-- Account Credentials Fields Mapping -->
                <div class="form-row-split">
                    <div class="form-group">
                        <label class="form-label">System Username</label>
                        <input type="text" name="username" class="form-input" value="<?php echo htmlspecialchars($user['username']); ?>">
                        <?php if(!empty($error3)) echo "<div class='error-message-feedback'>$error3</div>"; ?>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($user['email']); ?>">
                        <?php if(!empty($error5)) echo "<div class='error-message-feedback'>$error5</div>"; ?>
                    </div>
                </div>

                <!-- Safe Cryptographic Rotation Layer -->
                <div class="form-group full-width">
                    <label class="form-label">New Account Password</label>
                    <input type="password" name="password" class="form-input" placeholder="Leave blank completely to preserve existing credentials hierarchy">
                </div>

                <!-- Gender Grid Configuration System -->
                <div class="form-group full-width">
                    <label class="form-label">Gender Classification</label>
                    <div class="selection-matrix-group">
                        <label class="selection-label-item">
                            <input type="radio" name="gender" value="Male" <?php if ($user['gender'] == "Male") echo "checked"; ?>> Male
                        </label>
                        <label class="selection-label-item">
                            <input type="radio" name="gender" value="Female" <?php if ($user['gender'] == "Female") echo "checked"; ?>> Female
                        </label>
                        <label class="selection-label-item">
                            <input type="radio" name="gender" value="Other" <?php if ($user['gender'] == "Other") echo "checked"; ?>> Other
                        </label>
                    </div>
                    <?php if(!empty($error8)) echo "<div class='error-message-feedback'>$error8</div>"; ?>
                </div>

                <!-- Qualification Tier Array Dropdown -->
                <div class="form-group full-width">
                    <label class="form-label">Highest Qualification Level</label>
                    <select name="qualification" class="form-input">
                        <option value="">-- Select Valid Academic Tier --</option>
                        <option value="High School" <?php if ($user['qualification'] == "High School") echo "selected"; ?>>High School</option>
                        <option value="+2" <?php if ($user['qualification'] == "+2") echo "selected"; ?>>Intermediate (+2 Tier)</option>
                        <option value="Bachelor" <?php if ($user['qualification'] == "Bachelor") echo "selected"; ?>>Bachelor Degree Program</option>
                        <option value="Master" <?php if ($user['qualification'] == "Master") echo "selected"; ?>>Master Graduate Infrastructure</option>
                        <option value="Other" <?php if ($user['qualification'] == "Other") echo "selected"; ?>>Other Specialized Stream</option>
                    </select>
                    <?php if(!empty($error6)) echo "<div class='error-message-feedback'>$error6</div>"; ?>
                </div>

                <!-- Technical Skills Processing Repository Checkboxes -->
                <div class="form-group full-width">
                    <label class="form-label">Technical Competencies & Skills Inventory</label>
                    <div class="selection-matrix-group">
                        <?php
                        $all_skills = ["HTML", "CSS", "JavaScript", "PHP", "MySQL", "Python", "Java", "C++", "Dart", "Ruby", "Word Press", "Flutter", "Kubernet", "IBM SPSS", "Other"];
                        $user_skills = explode(", ", $user['skills']);
                        foreach ($all_skills as $skill) {
                            $checked = in_array($skill, $user_skills) ? "checked" : "";
                            echo "<label class='selection-label-item'>
                                    <input type='checkbox' name='skills[]' value='$skill' $checked> $skill
                                  </label>";
                        }
                        ?>
                    </div>
                    <?php if(!empty($error7)) echo "<div class='error-message-feedback'>$error7</div>"; ?>
                </div>

                <!-- Form Controls Triggers Frame -->
                <div class="form-actions-wrapper">
                    <button type="submit" name="update" class="btn-action-submit">Update Profile</button>
                    <a href="userhomepage.php" class="btn-action-cancel">Discard Change</a>
                </div>
            </form>
        </div>

        <!-- Corporate System Footer -->
        <div class="footer">
            <p>&copy; 2026 Created by Maneesh and Pratik. Online Job Finding System. All Rights Reserved.</p>
        </div>
    </div>

</body>
</html>