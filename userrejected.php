<?php
session_start();
include "database.php";

if (!isset($_SESSION["username"])) {
    echo "<script>alert('Please login first'); window.location='index.php';</script>";
    exit;
}

$username = mysqli_real_escape_string($con, $_SESSION["username"]);

// Step 1: Fetch declined applications for this user securely
$sql_dec = "SELECT * FROM declined_application WHERE username = '$username' ORDER BY rejected_at DESC";
$res_dec = mysqli_query($con, $sql_dec);
if (!$res_dec) {
    echo "Error fetching declined applications: " . mysqli_error($con);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Declined Jobs | Candidate Dashboard</title>
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
        .nav-links li a.active { background: #0f172a; color: white; border-left-color:  #002fb0; font-weight: 600; }

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

        .page-heading { font-size: 24px; font-weight: 700; color: #1e293b; margin-bottom: 25px; }

        /* Corporate Listings Grid Control */
        .jobs-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(450px, 1fr)); gap: 25px; }
        
        /* Modernized Declined Card Structure */
        .job-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.01); display: flex; flex-direction: column; gap: 18px; border-top: 4px solid #ef4444; }
        
        .job-card-header { display: flex; justify-content: space-between; align-items: flex-start; }
        .job-title { font-size: 20px; font-weight: 700; color: #1e293b; }
        .badge-declined { background: #fef2f2; color: #ef4444; font-size: 12px; font-weight: 700; padding: 5px 12px; border-radius: 20px; text-transform: uppercase; border: 1px solid #fee2e2; }

        .job-desc { font-size: 14px; color: #475569; line-height: 1.5; background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid #f1f5f9; }
        .declined-date { font-size: 13px; color: #64748b; font-weight: 500; }

        .section-divider { border: 0; border-top: 1px solid #f1f5f9; margin: 5px 0; }

        .info-block-title { font-size: 13.5px; font-weight: 700; color: #ef4444; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }

        /* Data Alignment Lists inside Cards */
        .details-list { display: flex; flex-direction: column; gap: 6px; }
        .details-item { font-size: 13.5px; color: #334155; display: flex; }
        .details-item strong { width: 120px; color: #64748b; flex-shrink: 0; font-weight: 600; }
        .details-item span { color: #1e293b; }
        
        /* Document download anchors layout */
        .doc-link { color: #b00000; text-decoration: none; font-weight: 600; }
        .doc-link:hover { text-decoration: underline; }

        .no-data { background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 40px; text-align: center; color: #64748b; font-size: 15px; font-weight: 500; grid-column: 1 / -1; }

        /* Footer Element Alignment */
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
            <li><a href="userrejected.php" class="active">View Declined Jobs</a></li>
            <li><a href="userprofile.php">Candidate Profile</a></li>
        </ul>
    </div>

    <!-- Right Presentation Grid Wrapper -->
    <div class="app-container">
        
        <!-- Header Top Console Module -->
        <div class="topbar">
            <div class="topbar-title">Candidate Evaluation History</div>
            <div class="user-action-area">
                <a href="userprofile.php" class="btn-profile">View Profile</a>
                <a href="logout.php" class="btn-logout"> Logout</a>
            </div>
        </div>

        <!-- Main Workspace Flow Arena -->
        <div class="workspace">
            <div class="welcome-alert">
                <p>Welcome, <span><?php echo htmlspecialchars($username); ?></span>! Reviewing archived closed application logs below.</p>
            </div>

            <h1 class="page-heading">Declined Job Listings</h1>

            <div class="jobs-grid">
                <?php
                if (mysqli_num_rows($res_dec) > 0) {
                    while ($dec = mysqli_fetch_assoc($res_dec)) {
                        $job_id = mysqli_real_escape_string($con, $dec['job_id']);
                        $company_id = mysqli_real_escape_string($con, $dec['cid']);

                        // Fetch job specifications securely
                        $sql_job = "SELECT * FROM jobs WHERE id = '$job_id'";
                        $res_job = mysqli_query($con, $sql_job);
                        $job = mysqli_fetch_assoc($res_job);

                        // Fetch company organizational credentials safely
                        $sql_cmp = "SELECT * FROM company WHERE cid = '$company_id'";
                        $res_cmp = mysqli_query($con, $sql_cmp);
                        $company = mysqli_fetch_assoc($res_cmp);
                        ?>
                        
                        <!-- Individual Corporate Placement Card -->
                        <div class="job-card">
                            <div class="job-card-header">
                                <div>
                                    <h3 class="job-title"><?php echo $job ? htmlspecialchars($job['title']) : "Job Position ID #".$job_id; ?></h3>
                                    <span class="declined-date">Declined on: <?php echo date("d M Y", strtotime($dec['rejected_at'])); ?></span>
                                </div>
                                <span class="badge-declined">Declined</span>
                            </div>

                            <?php if ($job): ?>
                                <p class="job-desc"><?php echo htmlspecialchars($job['description']); ?></p>
                            <?php endif; ?>

                            <hr class="section-divider">

                            <!-- Company Matrix block configuration -->
                            <div>
                                <h4 class="info-block-title">Employer Information</h4>
                                <?php if ($company): ?>
                                    <div class="details-list">
                                        <div class="details-item"><strong>Company:</strong> <span><?php echo htmlspecialchars($company['company_name']); ?></span></div>
                                        <div class="details-item"><strong>Email Address:</strong> <span><?php echo htmlspecialchars($company['email']); ?></span></div>
                                        <div class="details-item"><strong>Corporate Hub:</strong> <span><?php echo htmlspecialchars($company['address']); ?></span></div>
                                        <div class="details-item"><strong>Govt PAN:</strong> <span><?php echo htmlspecialchars($company['company_pan']); ?></span></div>
                                        <div class="details-item"><strong>License Key:</strong> <span><?php echo htmlspecialchars($company['company_license']); ?></span></div>
                                        <div class="details-item"><strong>Industry Type:</strong> <span><?php echo htmlspecialchars($company['company_type']); ?></span></div>
                                    </div>
                                <?php else: ?>
                                    <p style="font-size: 13px; color: #94a3b8;">Corporate information node offline.</p>
                                <?php endif; ?>
                            </div>

                            <hr class="section-divider">

                            <!-- Submitted Application Artifact Tracking -->
                            <div>
                                <h4 class="info-block-title">Your Submitted Application Info</h4>
                                <div class="details-list">
                                    <div class="details-item"><strong>Full Name:</strong> <span><?php echo htmlspecialchars($dec['fullname']); ?></span></div>
                                    <div class="details-item"><strong>Contact Phone:</strong> <span><?php echo htmlspecialchars($dec['phone']); ?></span></div>
                                    <div class="details-item"><strong>Core Skills:</strong> <span><?php echo htmlspecialchars($dec['skills']); ?></span></div>
                                    <div class="details-item"><strong>Experience:</strong> <span><?php echo htmlspecialchars($dec['experiences']); ?></span></div>
                                    <div class="details-item"><strong>Curriculum Vitae:</strong> <span><a href="cvs/<?php echo urlencode($dec['cv']); ?>" class="doc-link" target="_blank">Review CV Artifact</a></span></div>
                                    <div class="details-item"><strong>Verification Pix:</strong> <span><a href="photos/<?php echo urlencode($dec['photo']); ?>" class="doc-link" target="_blank">View Portrait</a></span></div>
                                </div>
                            </div>
                        </div>

                        <?php
                    }
                } else {
                    echo "<div class='no-data'>No declined job records logged in your tracking profile indexes.</div>";
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