<?php
session_start();
include "database.php";

if (isset($_SESSION["username"])) {
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - View Users</title>
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
        
        /* Page Header Title */
        .page-title-section { margin-bottom: 30px; }
        .page-title-section h2 { font-size: 24px; font-weight: 700; color: #1e293b; }
        .page-title-section p { font-size: 14.5px; color: #64748b; margin-top: 4px; }

        /* Premium Table Container & Wrap Styling */
        .table-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); }
        .table-container { width: 100%; overflow-x: auto; margin-top: 15px; border-radius: 8px; border: 1px solid #e2e8f0; }
        
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14.5px; min-width: 950px; }
        th { background-color: #f8fafc; color: #475569; font-weight: 700; padding: 14px 18px; border-bottom: 2px solid #e2e8f0; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; }
        td { padding: 14px 18px; border-bottom: 1px solid #e2e8f0; color: #334155; vertical-align: middle; }
        tr:hover { background-color: #f8fafc; }
        
        /* Badges for Meta Data */
        .gender-badge { background: #f1f5f9; color: #475569; font-size: 12.5px; font-weight: 600; padding: 4px 10px; border-radius: 6px; display: inline-block; }
        .skills-text { font-size: 13.5px; color: #555; font-weight: 500; }

        /* Action Buttons Interaction styling */
        .action-holder { display: flex; gap: 8px; }
        .action-btn { border: none; padding: 7px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; transition: 0.2s; text-decoration: none; display: inline-block; }
        
        .edit-btn { background-color: #eefbf7; color: #00b074; border: 1px solid rgba(0, 176, 116, 0.2); }
        .edit-btn:hover { background-color: #00b074; color: #fff; }
        
        .delete-btn { background-color: #fdf2f2; color: #de3e3e; border: 1px solid rgba(222, 62, 62, 0.15); }
        .delete-btn:hover { background-color: #de3e3e; color: #fff; }

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
                <li><a href="adminviewuser.php" class="active">View Users</a></li>
                <li><a href="adminviewjobs.php">View Jobs</a></li>
                <li><a href="adminviewpendingrequest.php">All Pending Applications</a></li>
                <li><a href="allacceptedapplicant.php">All Accepted Applicants</a></li>
                <li><a href="allrejectedapplicant.php">All Rejected Applicants</a></li>
            </ul>
        </div>

        <!-- Right Content Management Area -->
        <div class="main-content">
            
            <div class="page-title-section">
                <h2>Candidate Directories</h2>
                <p>Manage candidate profiles, view technical qualifications, and handle system user access controls.</p>
            </div>

            <!-- Table Block Card -->
            <div class="table-card">
                <h3 style="font-size: 17px; font-weight: 700; color: #1e293b; margin-bottom: 5px;">Registered Job Seekers</h3>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>UID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Username</th>
                                <th>Email Address</th>
                                <th>Gender</th>
                                <th>Qualification</th>
                                <th>Technical Skills</th>
                                <th style="text-align: center;">Operations</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM user";
                            $result = mysqli_query($con, $sql);

                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>
                                        <td><strong>#{$row['uid']}</strong></td>
                                        <td style='font-weight: 600; color: #111;'>" . htmlspecialchars($row['fname']) . "</td>
                                        <td style='font-weight: 600; color: #111;'>" . htmlspecialchars($row['lname']) . "</td>
                                        <td>" . htmlspecialchars($row['username']) . "</td>
                                        <td>" . htmlspecialchars($row['email']) . "</td>
                                        <td><span class='gender-badge'>" . htmlspecialchars($row['gender']) . "</span></td>
                                        <td>" . htmlspecialchars($row['qualification']) . "</td>
                                        <td><span class='skills-text'>" . htmlspecialchars($row['skills']) . "</span></td>
                                        <td align='center'>
                                            <div class='action-holder'>
                                                <button class='action-btn edit-btn' onclick=\"window.location.href='adminupdateuser.php?uid={$row['uid']}'\">Edit</button>
                                                <button class='action-btn delete-btn' onclick=\"if(confirm('Are you sure you want to permanently delete this user account?')) window.location.href='adminedeleteuser.php?uid={$row['uid']}';\">Delete</button>
                                            </div>
                                        </td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='9' style='text-align:center; color:#888; padding: 30px;'>No registered candidates found inside the system database.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
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
<?php
} else {
    echo '<script>
            alert("Please login first");
            window.location.href = "admin.php";
          </script>';
}
?>