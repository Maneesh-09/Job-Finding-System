<?php
session_start();
include "database.php"; // डेटाबेस कनेक्सन थपिएको (Stats काउन्ट गर्नका लागि)

if (isset($_SESSION["username"])) {
    // ड्यासबोर्डमा टोटल डाटाहरूको संख्या देखाउनका लागि क्वैरीहरू
    $company_count = mysqli_num_rows(mysqli_query($con, "SELECT id FROM companyregister"));
    $user_count = mysqli_num_rows(mysqli_query($con, "SELECT uid FROM user"));
    $job_count = mysqli_num_rows(mysqli_query($con, "SELECT id FROM jobs"));
    $pending_count = mysqli_num_rows(mysqli_query($con, "SELECT id FROM jobapplication WHERE status = 'Pending'"));
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Dashboard - Home</title>
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
            
            /* Welcome Area */
            .welcome-box { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; margin-bottom: 35px; box-shadow: 0 4px 20px rgba(0,0,0,0.01); }
            .welcome-box h2 { font-size: 24px; font-weight: 700; color: #1a1a1a; margin-bottom: 6px; }
            .welcome-box h2 span { color: #00b074; }
            .welcome-box p { font-size: 14.5px; color: #666; }

            /* Grid Layout for Stats Cards */
            .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px; }
            .card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; display: flex; flex-direction: column; gap: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.01); transition: 0.3s; }
            .card:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.03); }
            .card-title { font-size: 14px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
            .card-value { font-size: 28px; font-weight: 700; color: #1e293b; }
            .card-link { font-size: 13px; color: #00b074; font-weight: 600; text-decoration: none; margin-top: 5px; display: inline-block; }
            .card-link:hover { text-decoration: underline; }

            /* Info Section Inside Dashboard */
            .info-panel { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 30px; }
            .info-panel h3 { font-size: 18px; font-weight: 700; color: #222; margin-bottom: 12px; }
            .info-panel p { color: #555; font-size: 15px; line-height: 1.6; }

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
                    <li><a href="adminhomepage.php" class="active">Home</a></li>
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
                
                <!-- Welcome Banner Card -->
                <div class="welcome-box">
                    <h2>Welcome, <span><?php echo htmlspecialchars($_SESSION["username"]); ?></span>!</h2>
                    <p>You are safely logged in to the Administrative control panel of Online Job Finding System.</p>
                </div>

                <!-- Live Dynamic Overview Stats Grid -->
                <div class="stats-grid">
                    <div class="card">
                        <span class="card-title">Total Companies</span>
                        <span class="card-value"><?php echo $company_count; ?></span>
                        <a href="adminviewcompany.php" class="card-link">Manage Companies &rarr;</a>
                    </div>
                    <div class="card">
                        <span class="card-title">Registered Users</span>
                        <span class="card-value"><?php echo $user_count; ?></span>
                        <a href="adminviewuser.php" class="card-link">Manage Users &rarr;</a>
                    </div>
                    <div class="card">
                        <span class="card-title">Active Job Posts</span>
                        <span class="card-value"><?php echo $job_count; ?></span>
                        <a href="adminviewjobs.php" class="card-link">View Vacancies &rarr;</a>
                    </div>
                    <div class="card" style="border-left: 3px solid #ff9800;">
                        <span class="card-title" style="color: #ff9800;">Pending Applications</span>
                        <span class="card-value"><?php echo $pending_count; ?></span>
                        <a href="adminviewpendingrequest.php" class="card-link" style="color: #ff9800;">Review Requests &rarr;</a>
                    </div>
                </div>

                <!-- Guide Panel -->
                <div class="info-panel">
                    <h3>Dashboard Overview & Instructions</h3>
                    <p>Select any command or module from the left sidebar navigation menu to track corporate members, look up candidate records, filter job listings, or instantly manage open vacancy hiring processes (Approve or Reject candidate application forms).</p>
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
            window.location.href = "index.php";
          </script>';
}
?>