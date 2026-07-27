<?php
session_start();
include "database.php";

if (!isset($_SESSION["username"])) {
    echo '<script>
            alert("Please login first");
            window.location.href = "index.php";
          </script>';
    exit;
}

$username = mysqli_real_escape_string($con, $_SESSION["username"]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Home Dashboard | Online Job Finding System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8f9fa; color: #333; display: flex; min-height: 100vh; }

        /* Modern Sidebar Component Layout */
        .sidebar { width: 260px; background: #1e293b; color: white; display: flex; flex-direction: column; position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; }
        .sidebar-brand { padding: 24px; border-bottom: 1px solid #ffffff; }
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
        .btn-profile { background: #b02900 ; color: white; padding: 8px 16px; border-radius: 6px; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: 0.2s; }
        .btn-profile:hover { background: #002fb0; }
        .btn-logout { background: #002fb0; color: white; padding: 8px 16px; border-radius: 6px; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: 0.2s; border: none; cursor: pointer; }
        .btn-logout:hover { background: #b02900; }

        /* Workspace Main Layout Engine */
        .workspace { padding: 40px; flex: 1; }
        
        .welcome-alert { background: #ede6f7; border-left: 4px solid #b00000; padding: 16px 20px; border-radius: 0 8px 8px 0; margin-bottom: 35px; }
        .welcome-alert p { font-size: 15px; color: black; }
        .welcome-alert span { font-weight: 700; color: #b00000; }

        .page-heading { font-size: 24px; font-weight: 700; color: black; margin-bottom: 25px; border-bottom: 2px solid #eefbf7; padding-bottom: 12px; }

        /* Premium Job Grid Layout Matrix */
        .jobs-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px; }
        
        /* Modern Job Card Architecture */
        .job-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.01); display: flex; flex-direction: column; gap: 12px; transition: 0.2s; position: relative; }
        .job-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.03); }
        
        .job-banner { width: 100%; height: 160px; border-radius: 8px; overflow: hidden; background: #f8fafc; border: 1px solid #f1f5f9; }
        .job-banner img { width: 100%; height: 100%; object-fit: cover; }

        .job-title { font-size: 18px; font-weight: 700; color: #1e293b; margin-top: 5px; }
        .job-desc { font-size: 13.5px; color: #64748b; line-height: 1.5; height: 60px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; }

        .job-meta-list { display: flex; flex-direction: column; gap: 6px; margin: 8px 0; padding: 10px 0; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; }
        .meta-item { font-size: 13px; color: #475569; display: flex; justify-content: space-between; }
        .meta-item strong { color: #1e293b; font-weight: 600; }
        
        .badge-category { background: #e2e8f0; color: #b02900; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; text-transform: uppercase; }

        .btn-apply { background: #002fb0; color: white; border: none; padding: 10px; border-radius: 6px; font-size: 14px; font-weight: 700; cursor: pointer; text-align: center; transition: 0.2s; text-decoration: none; display: block; width: 100%; margin-top: auto; }
        .btn-apply:hover { background: #b02900; }

        .no-data { background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 40px; text-align: center; color: #64748b; font-size: 15px; font-weight: 500; grid-column: 1 / -1; }

        /* Footer Frame */
        .footer { background: #ffffff; border-top: 1px solid #e2e8f0; padding: 20px; text-align: center; width: 100%; }
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
            <li><a href="userhomepage.php" class="active">Home Dashboard</a></li>
            <li><a href="userviewjobs.php">View Vacant Jobs</a></li>
            <li><a href="userjobrequeststatus.php">Job Request Status</a></li>
            <li><a href="useraccepted.php">View Accepted Jobs</a></li>
            <li><a href="userrejected.php">View Rejected Jobs</a></li>
            <li><a href="userprofile.php">Candidate Profile</a></li>
        </ul>
    </div>

    <!-- Right Presentation Grid Wrapper -->
    <div class="app-container">
        
        <!-- Header Top Console Module -->
        <div class="topbar">
            <div class="topbar-title">Candidate Tracking Environment</div>
            <div class="user-action-area">
                <a href="userprofile.php" class="btn-profile">View Profile </a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>

        <!-- Main Workspace Flow Arena -->
        <div class="workspace">
            <div class="welcome-alert">
                <p>Welcome, <span><?php echo htmlspecialchars($_SESSION["username"]); ?></span>! System authentication logs successfully mapped to the live dashboard gateway.</p>
            </div>

            <h1 class="page-heading">Recent, Vacancies</h1>

            <div class="jobs-grid">
                <?php
                // Fetch 6 most recent jobs by openeddate safely
                $query = "SELECT * FROM jobs ORDER BY openeddate DESC LIMIT 6";
                $result = mysqli_query($con, $query);

                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $job_id = intval($row['id']);
                        $title = htmlspecialchars($row['title']);
                        $desc = htmlspecialchars($row['description']);
                        $loc = htmlspecialchars($row['location']);
                        $qual = htmlspecialchars($row['qualification']);
                        $salary = htmlspecialchars($row['salary']);
                        $category = htmlspecialchars($row['category']);
                        $img = htmlspecialchars($row['image']);
                        $posted = date("d-M-Y", strtotime($row['openeddate']));
                        $expiry = date("d-M-Y", strtotime($row['expirydate']));

                        $imgSrc = "images/" . $img;
                        if(empty($img) || !file_exists($imgSrc)) {
                            $imgSrc = "images/default-job.png"; // Dynamic image fallback
                        }
                        ?>
                        
                        <!-- Premium Dynamic Job Card Element -->
                        <div class="job-card">
                            <div class="job-banner">
                                <img src="<?php echo $imgSrc; ?>" alt="<?php echo $title; ?>">
                            </div>
                            <h3 class="job-title"><?php echo $title; ?></h3>
                            <p class="job-desc"><?php echo $desc; ?></p>
                            
                            <div class="job-meta-list">
                                <div class="meta-item"><strong>Location:</strong> <span><?php echo $loc; ?></span></div>
                                <div class="meta-item"><strong>Requirement:</strong> <span><?php echo $qual; ?></span></div>
                                <div class="meta-item"><strong>Compensation:</strong> <span><?php echo $salary; ?></span></div>
                                <div class="meta-item"><strong>Sector Type:</strong> <span class="badge-category"><?php echo $category; ?></span></div>
                                <div class="meta-item"><strong>Posted Date:</strong> <span><?php echo $posted; ?></span></div>
                                <div class="meta-item"><strong>Expiry Date:</strong> <span style="color: #010101; font-weight:600;"><?php echo $expiry; ?></span></div>
                            </div>
                            
                            <button class="btn-apply" onclick="window.location.href='applyjob.php?job_id=<?php echo $job_id; ?>'">Apply Job</button>
                        </div>

                        <?php
                    }
                } else {
                    echo "<div class='no-data'>No operational vacancy circulars located in current registry database.</div>";
                }
                ?>
            </div>
        </div>

        <!-- Corporate System Footer -->
        <div class="footer">
            <p>&copy; 2026 Created by Maneesh and Pratik. Online Job Finding System. All Rights Reserved.</p>
        </div>
    </div>

</body>
</html>