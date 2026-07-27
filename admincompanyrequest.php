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
        <title>Admin Dashboard - Registered Companies</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
            body { background-color: #f8f9fa; color: #333; display: flex; flex-direction: column; min-height: 100vh; }

            /* Modern Admin Navbar */
            .navbar { background: #ffffff; border-bottom: 1px solid #e1e4e8; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
            .logo-area h1 { font-size: 20px; color: #111; font-weight: 700; }
            .logo-area span { color: red; }
            
            .admin-profile-section { display: flex; align-items: center; gap: 15px; }
            .admin-badge { background: #eefbf7; color: #00b074; font-weight: 700; padding: 6px 14px; border-radius: 20px; font-size: 13.5px; border: 1px solid rgba(0, 176, 116, 0.2); }
            
            .btn-logout { background: #fff; border: 1px solid #de3e3e; color: #de3e3e; padding: 6px 16px; border-radius: 6px; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: 0.3s; }
            .btn-logout:hover { background: #de3e3e; color: #fff; }

            /* Dashboard Main Layout */
            .dashboard-layout { display: flex; flex: 1; }

            /* Sidebar Design */
            .sidebar { width: 260px; background: #ffffff; border-right: 1px solid #e2e8f0; padding: 30px 15px; }
            .nav-links { list-style: none; display: flex; flex-direction: column; gap: 8px; }
            .nav-links li a { display: block; padding: 12px 18px; color: #555; font-size: 14.5px; font-weight: 600; text-decoration: none; border-radius: 8px; transition: 0.3s; }
            .nav-links li a:hover { color: #00b074; background: #eefbf7; }
            .nav-links li a.active { background: #00b074; color: white !important; }

            /* Content Wrapper */
            .main-content { flex: 1; padding: 40px; background-color: #f8f9fa; }
            
            /* Welcome Area */
            .welcome-box { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px 25px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.01); }
            .welcome-box p { font-size: 15px; color: #555; }
            .welcome-box span { color: #00b074; font-weight: 700; }

            /* Main Header & Overview Text */
            .page-title { font-size: 24px; font-weight: 700; color: #1a1a1a; margin-bottom: 8px; }
            .page-subtitle { font-size: 14.5px; color: #666; margin-bottom: 30px; }

            /* Container for Modern Data Table */
            .table-container { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); overflow-x: auto; }
            .table-container h2 { font-size: 18px; font-weight: 700; margin-bottom: 20px; color: #222; border-bottom: 2px solid #f1f1f1; padding-bottom: 12px; }

            /* Table Design (Premium Layout) */
            .custom-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
            .custom-table th { background-color: #f8fafc; color: #475569; font-weight: 700; padding: 14px 16px; border-bottom: 2px solid #e2e8f0; }
            .custom-table td { padding: 14px 16px; border-bottom: 1px solid #edf2f7; color: #334155; }
            .custom-table tr:hover { background-color: #f8fafc; }
            
            /* Empty Data Row Row */
            .no-data { text-align: center; color: #777; padding: 30px !important; font-style: italic; }

            /* Footer */
            .footer { background: #111; color: #888; padding: 25px 20px; text-align: center; border-top: 1px solid #222; margin-top: auto; }
            .footer p { font-size: 13.5px; }
        </style>
    </head>

    <body>

        <div class="navbar">
            <div class="logo-area">
                <h1>Online <span>Job Find</span></h1>
            </div>
            <div class="admin-profile-section">
                <span class="admin-badge">🛡️ Admin Panel</span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>

        <div class="dashboard-layout">
            
            <div class="sidebar">   
                <ul class="nav-links">
                    <li><a href="adminhomepage.php">Home</a></li>
                    <li><a href="adminviewcompany.php" class="active">View Companies</a></li>
                    <li><a href="adminviewuser.php">View Users</a></li>
                    <li><a href="addcategory.php">Add Job Category</a></li>
                    <li><a href="admincompanyrequest.php">Company Requests</a></li>
                </ul>
            </div>

            <div class="main-content">
                
                <div class="welcome-box">
                    <p>Welcome back, <span><?php echo htmlspecialchars($_SESSION["username"]); ?></span>! You are currently logged in as Administrator.</p>
                </div>

                <h1 class="page-title">Dashboard Overview</h1>
                <p class="page-subtitle">Below is the complete overview of registered business details inside the platform.</p>

                <div class="table-container">
                    <h2>Registered Corporate Members</h2>
                    
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Company Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>PAN Number</th>
                                <th>License Code</th>
                                <th>Industry Category</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM companyregister";
                            $result = mysqli_query($con, $sql);

                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>
                                            <td><strong>#{$row['id']}</strong></td>
                                            <td>" . htmlspecialchars($row['company_name']) . "</td>
                                            <td>" . htmlspecialchars($row['username']) . "</td>
                                            <td>" . htmlspecialchars($row['email']) . "</td>
                                            <td><code style='background:#f1f3f5; padding:2px 6px; border-radius:4px; font-weight:600;'>" . htmlspecialchars($row['company_pan']) . "</code></td>
                                            <td>" . htmlspecialchars($row['company_license']) . "</td>
                                            <td><span style='background:#eefbf7; color:#00b074; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600;'>" . htmlspecialchars($row['company_category']) . "</span></td>
                                          </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='no-data'>No company accounts registered inside the system yet.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>&copy; 2026 Created by Maneesh and Pratik. Online Job Finding System. All Rights Reserved.</p>
        </div>

    </body>
    </html>
    <?php
} else {
    echo '<script>
            alert("Please login first");
            window.location.href = "index.php";
          </script>';
}
?>