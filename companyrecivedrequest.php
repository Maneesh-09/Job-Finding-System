<?php
session_start();
include "database.php"; // DB connection

if (!isset($_SESSION["username"])) {
    echo '<script>
            alert("Please login first");
            window.location.href = "index.php";
          </script>';
    exit;
}

$company_username = mysqli_real_escape_string($con, $_SESSION['username']);

// Fetch company details securely
$sql_company = "SELECT * FROM company WHERE username='$company_username'";
$res_company = mysqli_query($con, $sql_company);

if (!$res_company || mysqli_num_rows($res_company) !== 1) {
    echo "<script>alert('Company profiles not mapped inside logs.'); window.location='logout.php';</script>";
    exit;
}

$company = mysqli_fetch_assoc($res_company);
$company_id = intval($company['cid']);

// Fetch jobs posted by this company identity
$sql_jobs = "SELECT * FROM jobs WHERE username='$company_username' ORDER BY id DESC";
$res_jobs = mysqli_query($con, $sql_jobs);
if (!$res_jobs) {
    echo "<script>alert('Error fetching jobs matrix logs: " . mysqli_error($con) . "');</script>";
    $res_jobs = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Panel - Received Applications</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8f9fa; color: #333; display: flex; flex-direction: column; min-height: 100vh; }

        /* Corporate Navbar Elements */
        .navbar { background: #ffffff; border-bottom: 1px solid #e1e4e8; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
        .logo-area h1 { font-size: 20px; color: #111; font-weight: 700; }
        .logo-area span { color: red; }
        
        .profile-section { display: flex; align-items: center; gap: 15px; }
        .company-badge { background: #002fb0; color: white; font-weight: 700; padding: 6px 14px; border-radius: 10px; font-size: 13.5px; }
        
        .btn-logout { background: #b02900; border: 0px solid white; color: white; padding: 8px 16px; border-radius: 10px; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: 0.3s; }
        .btn-logout:hover { background: #de3e3e; color: #fff; }

        /* Core Panel Layout Architecture */
        .dashboard-layout { display: flex; flex: 1; }

        /* Sidebar Navigation Controls */
        .sidebar { width: 280px; background: #ffffff; border-right: 1px solid #e2e8f0; padding: 30px 15px; }
        .nav-links { list-style: none; display: flex; flex-direction: column; gap: 6px; }
        .nav-links li a { display: block; padding: 12px 16px; color: #555; font-size: 14.5px; font-weight: 600; text-decoration: none; border-radius: 8px; transition: 0.3s; }
        .nav-links li a:hover { color: black; background: white; }
        .nav-links li a.active { background: #334155; color: white !important; }

        /* Main Content Stream Area */
        .main-content { flex: 1; padding: 40px; background-color: #f8f9fa; }
        
        .welcome-banner { background: #ede6f7; border: 1px; border-radius: 8px; padding: 14px 20px; margin-bottom: 30px; font-size: 14.5px; color: #334155; }
        .welcome-banner span { color: #b02900; font-weight: 700; }

        .page-title { font-size: 24px; font-weight: 700; color: #1e293b; margin-bottom: 25px; border-bottom: 2px solid #eefbf7; padding-bottom: 12px; }

        /* Container Stack Matrix */
        .applications-stack { display: flex; flex-direction: column; gap: 30px; }

        /* Corporate Job Wrapper Box Layout */
        .job-group-box { background: transparent; display: flex; flex-direction: column; gap: 15px; }
        .job-group-header { background: #1e293b; color: #ffffff; padding: 12px 20px; border-radius: 8px; font-size: 16px; font-weight: 600; box-shadow: 0 4px 10px rgba(0,0,0,0.02); }

        /* Applicant Card Profile UI Architecture */
        .applicant-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.01); display: flex; gap: 25px; align-items: flex-start; transition: 0.2s; }
        .applicant-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.03); }

        /* Photo Component */
        .avatar-area { width: 120px; height: 120px; border-radius: 8px; overflow: hidden; border: 2px solid #e2e8f0; background: #f8fafc; flex-shrink: 0; }
        .avatar-area img { width: 100%; height: 100%; object-fit: cover; }

        /* Core profile data layout streams */
        .profile-details { flex: 1; display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px 20px; }
        .detail-item { font-size: 14px; line-height: 1.5; color: #475569; }
        .detail-item strong { color: #1e293b; display: inline-block; width: 110px; }
        .detail-item.full-width { grid-column: span 2; }

        /* Link adjustments */
        .cv-link { color: #de3e3e; text-decoration: none; font-weight: 700; border-bottom: 1px dashed #00b074; transition: 0.2s; }
        .cv-link:hover { color: #002fb0; border-bottom-style: solid; }

        /* Row Configuration Panel for Control Buttons */
        .action-panel { display: flex; flex-direction: column; gap: 10px; justify-content: center; width: 180px; flex-shrink: 0; padding-left: 20px; border-left: 1px solid #f1f5f9; }
        .action-panel form { width: 100%; }
        
        .btn-action { display: block; width: 100%; border: none; padding: 10px 14px; border-radius: 6px; font-size: 13.5px; font-weight: 700; cursor: pointer; text-align: center; transition: 0.2s; text-decoration: none; }
        .btn-accept { background: #de3e3e; color: white; }
        .btn-accept:hover { background: #002fb0; }
        .btn-decline { background: #fff5f5; color: #de3e3e; border: 1px solid #fee2e2; }
        .btn-decline:hover { background: #de3e3e; color: white; border-color: #de3e3e; }

        /* System Messages Alerts Elements */
        .no-records { text-align: center; padding: 50px; background: white; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 15px; color: #64748b; }

        /* Footer Element Frame UI */
        .footer { background: #111; color: white; padding: 20px 20px; text-align: center; border-top: 1px solid #222; margin-top: auto; width: 100%; }
        .footer p { font-size: 13.5px; }
    </style>
</head>
<body>

    <!-- Corporate Navbar Module Layout -->
    <div class="navbar">
        <div class="logo-area">
            <h1>Online <span>Job Find</span></h1>
        </div>
        <div class="profile-section">
            <span class="company-badge">🏢 Company Profile</span>
            <a href="logout.php" class="btn-logout">Log Out</a>
        </div>
    </div>

    <!-- Dashboard Core Layout Container Grid Frame -->
    <div class="dashboard-layout">
        
        <!-- Navigation Links Sidebar Panel Grid Menu -->
        <div class="sidebar">
            <ul class="nav-links">
                <li><a href="companyhomepage.php">Home</a></li>
                <li><a href="companyaddjobs.php">Add Jobs</a></li>
                <li><a href="companyrecivedrequest.php" class="active">Received Requests</a></li>
                <li><a href="companyaccepteduser.php">Accepted User</a></li>
                <li><a href="companydeclinerequest.php">Declined User</a></li>
                <li><a href="companyprofile.php">Profile</a></li>
            </ul>
        </div>

        <!-- System Active Main UI Panel Dashboard View Space -->
        <div class="main-content">
            
            <div class="welcome-banner">
                <p>Welcome, <span><?php echo htmlspecialchars($company_username); ?></span>! Operational logs are actively connected to the secure routing channels.</p>
            </div>

            <h2 class="page-title">Incoming Candidate Applications</h2>

            <div class="applications-stack">
                <?php
                $hasApplicants = false;

                if ($res_jobs && mysqli_num_rows($res_jobs) > 0) {
                    while ($job = mysqli_fetch_assoc($res_jobs)) {
                        $job_id = intval($job['id']);
                        $job_title = htmlspecialchars($job['title']);
                        
                        // Fetch specific pending user application data matching active index
                        $sql_applications = "SELECT * FROM jobapplication WHERE job_id='$job_id' ORDER BY id DESC";
                        $res_applications = mysqli_query($con, $sql_applications);

                        if ($res_applications && mysqli_num_rows($res_applications) > 0) {
                            $hasApplicants = true;
                            ?>
                            
                            <!-- Job Category Wrapper Header Context -->
                            <div class="job-group-box">
                                <div class="job-group-header">
                                    Target Position Slot: <?php echo $job_title; ?>
                                </div>

                                <?php
                                while ($app = mysqli_fetch_assoc($res_applications)) {
                                    $app_id    = intval($app['id']);
                                    $fullname  = htmlspecialchars($app['fullname']);
                                    $email     = htmlspecialchars($app['email']);
                                    $phone     = htmlspecialchars($app['phone']);
                                    $address   = htmlspecialchars($app['address']);
                                    $skills    = htmlspecialchars($app['skills']);
                                    $experience= htmlspecialchars($app['experiences']);
                                    $photo     = htmlspecialchars($app['photo']);
                                    $cv        = htmlspecialchars($app['cv']);
                                    $appliedOn = date("d-M-Y", strtotime($app['applied_date']));

                                    // Fallback context checks for dynamic profile avatars
                                    $avatarSrc = "photos/" . $photo;
                                    if(empty($photo) || !file_exists($avatarSrc)) {
                                        $avatarSrc = "photos/default-user.png"; // Dynamic fallback image asset allocation logs
                                    }
                                    ?>
                                    
                                    <!-- Dynamic Candidate Row UI Unit element structure -->
                                    <div class="applicant-card">
                                        <div class="avatar-area">
                                            <img src="<?php echo $avatarSrc; ?>" alt="<?php echo $fullname; ?>">
                                        </div>

                                        <div class="profile-details">
                                            <div class="detail-item"><strong>Candidate:</strong> <?php echo $fullname; ?></div>
                                            <div class="detail-item"><strong>Applied:</strong> <?php echo $appliedOn; ?></div>
                                            <div class="detail-item"><strong>Email:</strong> <?php echo $email; ?></div>
                                            <div class="detail-item"><strong>Contact No:</strong> <?php echo $phone; ?></div>
                                            <div class="detail-item full-width"><strong>Location:</strong> <?php echo $address; ?></div>
                                            <div class="detail-item full-width"><strong>Competencies:</strong> <?php echo $skills; ?></div>
                                            <div class="detail-item full-width"><strong>Background:</strong> <?php echo $experience; ?></div>
                                            <div class="detail-item">
                                                <strong>Documents:</strong> 
                                                <a href="cvs/<?php echo $cv; ?>" target="_blank" class="cv-link">Review Professional CV File</a>
                                            </div>
                                        </div>

                                        <div class="action-panel">
                                            <form action="companyacceptrequest.php" method="POST">
                                                <input type="hidden" name="application_id" value="<?php echo $app_id; ?>">
                                                <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
                                                <button type="submit" name="accept" class="btn-action btn-accept" onclick="return confirm('Do you want to accept this candidate\'s submission requests?');">Accept Candidate</button>
                                            </form>

                                            <form action="companydeclineuser.php" method="POST">
                                                <input type="hidden" name="application_id" value="<?php echo $app_id; ?>">
                                                <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
                                                <button type="submit" name="decline" class="btn-action btn-decline" onclick="return confirm('Are you sure you want to decline this applicant? This file will be archived.');">Decline Entry</button>
                                            </form>
                                        </div>
                                    </div>

                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                        }
                    }

                    if (!$hasApplicants) {
                        echo "<div class='no-records'><h3>No pending applicant screening transactions located at the moment.</h3></div>";
                    }
                } else {
                    echo "<div class='no-records'><h3>No active vacancies published under this profile session workspace logs.</h3></div>";
                }
                ?>
            </div>

        </div>
    </div>

    <!-- Footer Structural Alignment component panel -->
    <div class="footer">
        <p>&copy; 2026 Created by Maneesh and Pratik. Online Job Finding System. All Rights Reserved.</p>
    </div>

</body>
</html>