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

// Optimized Single Query using JOIN to fetch application and job details together
$sql_applications = "SELECT ja.*, j.title, j.description, j.location, j.qualification, j.salary, j.category, j.image, j.openeddate, j.expirydate 
                     FROM jobapplication ja 
                     JOIN jobs j ON ja.job_id = j.id 
                     WHERE ja.username='$username' 
                     ORDER BY ja.applied_date DESC";
$res_applications = mysqli_query($con, $sql_applications);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Application Status | Online Job Finding System</title>
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
        .btn-profile { background: #b02900; color: white; padding: 8px 16px; border-radius: 6px; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: 0.2s; }
        .btn-profile:hover { background: #002fb0; }
        .btn-logout { background: #002fb0; color: white; padding: 8px 16px; border-radius: 6px; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: 0.2s; border: none; cursor: pointer; }
        .btn-logout:hover { background: #b02900; }

        /* Workspace Main Layout Engine */
        .workspace { padding: 40px; flex: 1; }
        
        .welcome-alert { background: #ede6f7; border-left: 4px solid #b00000; padding: 16px 20px; border-radius: 0 8px 8px 0; margin-bottom: 35px; }
        .welcome-alert p { font-size: 15px; color: black; }
        .welcome-alert span { font-weight: 700; color: #b00000; }

        .page-heading { font-size: 24px; font-weight: 700; color: #1e293b; margin-bottom: 25px; border-bottom: 2px solid #eefbf7; padding-bottom: 12px; }

        /* Premium Application Grid Layout Matrix */
        .status-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 25px; }
        
        /* Modern Status Card Architecture */
        .status-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.01); display: flex; flex-direction: column; gap: 12px; transition: 0.2s; }
        .status-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.03); }
        
        .job-banner { width: 100%; height: 150px; border-radius: 8px; overflow: hidden; background: #f8fafc; border: 1px solid #f1f5f9; }
        .job-banner img { width: 100%; height: 100%; object-fit: cover; }

        .job-title { font-size: 18px; font-weight: 700; color: #1e293b; }
        
        .section-title { font-size: 12px; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-top: 5px; border-bottom: 1px dashed #e2e8f0; padding-bottom: 4px; }

        .meta-list { display: flex; flex-direction: column; gap: 6px; }
        .meta-item { font-size: 13px; color: #475569; display: flex; justify-content: space-between; align-items: center; }
        .meta-item strong { color: #1e293b; font-weight: 600; }
        .meta-item a { color: #00b074; text-decoration: none; font-weight: 600; }
        .meta-item a:hover { text-decoration: underline; }
        
        .badge-category { background: #e2e8f0; color: #475569; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .badge-status-pending { background: #fef3c7; color: #d97706; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; border: 1px solid #fde68a; }

        .card-actions { display: flex; flex-direction: column; gap: 8px; margin-top: auto; padding-top: 10px; }
        .btn-view { background: #1e293b; color: white; border: none; padding: 10px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; text-align: center; transition: 0.2s; text-decoration: none; }
        .btn-view:hover { background: #0f172a; }

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
            <li><a href="userhomepage.php">Home Dashboard</a></li>
            <li><a href="userviewjobs.php">View Vacant Jobs</a></li>
            <li><a href="userjobrequeststatus.php" class="active">Job Request Status</a></li>
            <li><a href="useraccepted.php">View Accepted Jobs</a></li>
            <li><a href="userrejected.php">View Rejected Jobs</a></li>
            <li><a href="userprofile.php">Candidate Profile</a></li>
        </ul>
    </div>

    <!-- Right Presentation Grid Wrapper -->
    <div class="app-container">
        
        <!-- Header Top Console Module -->
        <div class="topbar">
            <div class="topbar-title">Application Status Tracking Console</div>
            <div class="user-action-area">
                <a href="userprofile.php" class="btn-profile">View Profile</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>

        <!-- Main Workspace Flow Arena -->
        <div class="workspace">
            <div class="welcome-alert">
                <p>Welcome, <span><?php echo htmlspecialchars($_SESSION["username"]); ?></span>! Real-time synchronization active for all submitted job applications.</p>
            </div>

            <h1 class="page-heading">Job Request Status Check</h1>

            <div class="status-grid">
                <?php
                if ($res_applications && mysqli_num_rows($res_applications) > 0) {
                    while ($row = mysqli_fetch_assoc($res_applications)) {
                        $app_id = intval($row['id']);
                        $title = htmlspecialchars($row['title']);
                        $desc = htmlspecialchars($row['description']);
                        $loc = htmlspecialchars($row['location']);
                        $qual = htmlspecialchars($row['qualification']);
                        $salary = htmlspecialchars($row['salary']);
                        $category = htmlspecialchars($row['category']);
                        $img = htmlspecialchars($row['image']);
                        
                        $fullname = htmlspecialchars($row['fullname']);
                        $email = htmlspecialchars($row['email']);
                        $phone = htmlspecialchars($row['phone']);
                        $address = htmlspecialchars($row['address']);
                        $skills = htmlspecialchars($row['skills']);
                        $exp = htmlspecialchars($row['experiences']);
                        $cv = htmlspecialchars($row['cv']);
                        $photo = htmlspecialchars($row['photo']);

                        $posted = date("d-M-Y", strtotime($row['openeddate']));
                        $expiry = date("d-M-Y", strtotime($row['expirydate']));
                        $applied_date = date("d-M-Y", strtotime($row['applied_date']));

                        $imgSrc = "images/" . $img;
                        if(empty($img) || !file_exists($imgSrc)) {
                            $imgSrc = "images/default-job.png";
                        }
                        ?>
                        
                        <!-- Premium Status Card Element -->
                        <div class="status-card">
                            <div class="job-banner">
                                <img src="<?php echo $imgSrc; ?>" alt="<?php echo $title; ?>">
                            </div>
                            <h3 class="job-title"><?php echo $title; ?></h3>
                            
                            <div class="section-title">Job Details</div>
                            <div class="meta-list">
                                <div class="meta-item"><strong>Location:</strong> <span><?php echo $loc; ?></span></div>
                                <div class="meta-item"><strong>Category:</strong> <span class="badge-category"><?php echo $category; ?></span></div>
                                <div class="meta-item"><strong>Salary:</strong> <span><?php echo $salary; ?></span></div>
                                <div class="meta-item"><strong>Deadline:</strong> <span style="color:#ef4444; font-weight:600;"><?php echo $expiry; ?></span></div>
                            </div>

                            <div class="section-title">Your Submitted Profile</div>
                            <div class="meta-list">
                                <div class="meta-item"><strong>Applicant:</strong> <span><?php echo $fullname; ?></span></div>
                                <div class="meta-item"><strong>Phone:</strong> <span><?php echo $phone; ?></span></div>
                                <div class="meta-item"><strong>Skills:</strong> <span><?php echo $skills; ?></span></div>
                                <div class="meta-item"><strong>Documents:</strong> 
                                    <span>
                                        <a href="cvs/<?php echo $cv; ?>" target="_blank">CV</a> | 
                                        <a href="photos/<?php echo $photo; ?>" target="_blank">Photo</a>
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Application Status Badges and Actions -->
                            <div class="card-actions">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                    <span style="font-size: 13px; color:#64748b; font-weight:500;">Current Review State:</span>
                                    <span class="badge-status-pending">Pending</span>
                                </div>
                                <button class="btn-view" onclick="window.location.href='viewapplication.php?app_id=<?php echo $app_id; ?>'">
                                    Submitted On: <?php echo $applied_date; ?>
                                </button>
                            </div>
                        </div>

                        <?php
                    }
                } else {
                    echo "<div class='no-data'> You have not submitted any job applications to the registry system yet. </div>";
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