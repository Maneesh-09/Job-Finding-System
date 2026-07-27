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

$username = mysqli_real_escape_string($con, $_SESSION["username"]);

// ✅ Step 1: Get company ID safely
$companyresult = mysqli_query($con, "SELECT cid FROM company WHERE username = '$username'");
$cid = null;

if ($companyresult && mysqli_num_rows($companyresult) === 1) {
    $companydata = mysqli_fetch_assoc($companyresult);
    $cid = intval($companydata['cid']);
} else {
    echo "<script>alert('Corporate identity not found!'); window.location='logout.php';</script>";
    exit;
}

$declinedApplicants = [];

if ($cid !== null) {
    // ✅ Step 2: Get job IDs and titles for jobs posted by this company
    $jobs = []; // key = job_id, value = title
    $jobresult = mysqli_query($con, "SELECT id, title FROM jobs WHERE company_id = '$cid'");

    if ($jobresult && mysqli_num_rows($jobresult) > 0) {
        $jobid = [];
        while ($job = mysqli_fetch_assoc($jobresult)) {
            $jobid[] = $job['id'];
            $jobs[$job['id']] = $job['title'];
        }

        // ✅ Step 3: Fetch declined applicants for these jobs
        if (!empty($jobid)) {
            $jobidstr = implode(",", array_map('intval', $jobid)); // safe for SQL
            $applicantquery = "SELECT * FROM declined_application WHERE job_id IN ($jobidstr) ORDER BY rejected_at DESC";
            $applicantresult = mysqli_query($con, $applicantquery);

            if ($applicantresult && mysqli_num_rows($applicantresult) > 0) {
                while ($row = mysqli_fetch_assoc($applicantresult)) {
                    $job_id = $row['job_id'];
                    $row['job_title'] = isset($jobs[$job_id]) ? $jobs[$job_id] : 'Unknown';
                    $declinedApplicants[] = $row;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard - Declined Applicants</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8f9fa; color: #333; display: flex; flex-direction: column; min-height: 100vh; }

        /* Modern Corporate Top Navbar */
        .navbar { background: #ffffff; border-bottom: 1px solid #e1e4e8; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
        .logo-area h1 { font-size: 20px; color: #111; font-weight: 700; }
        .logo-area span { color: red; }
        
        .profile-section { display: flex; align-items: center; gap: 15px; }
        .company-badge { background:  #002fb0; color: white; font-weight: 700; padding: 6px 14px; border-radius: 10px; font-size: 13.5px; }
        
        .btn-logout { background: #b02900; border: 0px solid white; color: white; padding: 8px 16px; border-radius: 10px; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: 0.3s; }
        .btn-logout:hover { background: #b02900; color: #fff; }

        /* Dashboard Main Layout */
        .dashboard-layout { display: flex; flex: 1; }

        /* Sidebar Design */
        .sidebar { width: 280px; background: #ffffff; border-right: 1px solid #e2e8f0; padding: 30px 15px; }
        .nav-links { list-style: none; display: flex; flex-direction: column; gap: 6px; }
        .nav-links li a { display: block; padding: 12px 16px; color: #555; font-size: 14.5px; font-weight: 600; text-decoration: none; border-radius: 8px; transition: 0.3s; }
        .nav-links li a:hover { color: black; background: white; }
        .nav-links li a.active { background: #334155; color: white !important; }

        /* Content Wrapper */
        .main-content { flex: 1; padding: 40px; background-color: #f8f9fa; }
        
        .page-header { margin-bottom: 30px; border-bottom: 2px solid #eefbf7; padding-bottom: 15px; }
        .page-header h2 { font-size: 24px; font-weight: 700; color: #1e293b; }
        .page-header p { font-size: 14.5px; color: #64748b; margin-top: 4px; }

        /* Grid System for Applicant Cards */
        .applicants-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px; }

        /* Premium Applicant Card Design */
        .applicant-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.01); display: flex; flex-direction: column; position: relative; border-top: 4px solid #de3e3e; }
        
        .card-header { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; }
        
        /* Modern Profile Circle Avatar */
        .avatar-container { width: 70px; height: 70px; border-radius: 50%; overflow: hidden; border: 2px solid #e2e8f0; background: #f1f5f9; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .avatar-container img { width: 100%; height: 100%; object-fit: cover; }
        .avatar-placeholder { font-size: 11px; color: #94a3b8; font-weight: 600; text-align: center; }

        .applicant-meta h3 { font-size: 17px; font-weight: 700; color: #1e293b; line-height: 1.2; }
        .job-tag { display: inline-block; background: #f8fafc; color: #475569; font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 4px; border: 1px solid #e2e8f0; margin-top: 6px; }

        .card-body { display: flex; flex-direction: column; gap: 10px; font-size: 14px; margin-bottom: 20px; flex: 1; }
        .info-row { display: flex; justify-content: space-between; border-bottom: 1px dashed #f1f5f9; padding-bottom: 6px; }
        .info-row strong { color: #475569; }
        .info-row span { color: #334155; text-align: right; max-width: 65%; word-break: break-word; }

        .skills-section, .exp-section { background: #fff5f5; border-radius: 8px; padding: 10px 12px; border: 1px solid #fee2e2; margin-top: 5px; }
        .skills-section p, .exp-section p { font-size: 13px; color: #991b1b; line-height: 1.4; }

        /* Action Buttons Grid */
        .card-actions { display: flex; gap: 10px; margin-top: auto; padding-top: 15px; border-top: 1px solid #f1f5f9; }
        .btn-cv { background: #f8fafc; color: #475569; border: 1px solid #cbd5e1; padding: 9px 16px; border-radius: 6px; font-size: 13.5px; font-weight: 600; text-decoration: none; text-align: center; flex: 1; transition: 0.2s; }
        .btn-cv:hover { background: #e2e8f0; color: #1e293b; }
        
        .btn-status { background: #fef2f2; color: #de3e3e; border: 1px solid #fee2e2; padding: 9px 16px; border-radius: 6px; font-size: 13.5px; font-weight: 700; cursor: not-allowed; text-align: center; flex: 1; }

        /* Empty State */
        .no-data { text-align: center; padding: 60px 20px; background: white; border: 1px solid #e2e8f0; border-radius: 12px; width: 100%; max-width: 600px; margin: 40px auto; }
        .no-data h3 { font-size: 18px; color: #475569; margin-bottom: 8px; }
        .no-data p { font-size: 14px; color: #94a3b8; }

        /* Footer */
        .footer { background: #111; color: white; padding: 25px 20px; text-align: center; border-top: 1px solid #222; margin-top: auto; width: 100%; }
        .footer p { font-size: 13.5px; }
    </style>
</head>
<body>

    <!-- Corporate Header Navbar -->
    <div class="navbar">
        <div class="logo-area">
            <h1>Online <span>Job Find</span></h1>
        </div>
        <div class="profile-section">
            <span class="company-badge">🏢 Company Profile</span>
            <a href="logout.php" class="btn-logout">Log Out</a>
        </div>
    </div>

    <!-- Dashboard Core Container -->
    <div class="dashboard-layout">
        
        <!-- Left Sidebar Navigation Layout -->
        <div class="sidebar">
            <ul class="nav-links">
                <li><a href="companyhomepage.php">Home</a></li>
                <li><a href="companyaddjobs.php">Add Jobs</a></li>
                <li><a href="companyrecivedrequest.php">Received Requests</a></li>
                <li><a href="companyaccepteduser.php">Accepted User</a></li>
                <li><a href="companydeclinerequest.php" class="active">Declined User</a></li>
                <li><a href="companyprofile.php">Profile</a></li>
            </ul>
        </div>

        <!-- Right Core Main Content Context -->
        <div class="main-content">
            
            <div class="page-header">
                <h2>Declined Applicants</h2>
                <p>Archive of talents and candidate applications that did not align with your structural job criteria constraints.</p>
            </div>

            <div class="applicants-grid">
                <?php
                if (!empty($declinedApplicants)) {
                    foreach ($declinedApplicants as $row) {
                        $photo = $row['photo'];
                        $cv = $row['cv'];
                        $fullname = htmlspecialchars($row['fullname']);
                        $email = htmlspecialchars($row['email']);
                        $phone = htmlspecialchars($row['phone']);
                        $address = htmlspecialchars($row['address']);
                        $skills = htmlspecialchars($row['skills']);
                        $experience = htmlspecialchars($row['experiences']);
                        $jobTitle = htmlspecialchars($row['job_title']);
                        $rejectedAt = date("d-M-Y", strtotime($row['rejected_at']));

                        // Photo Avatar Evaluation
                        $avatar = '<div class="avatar-placeholder">No Photo</div>';
                        if (!empty($photo) && file_exists("photos/" . $photo)) {
                            $avatar = '<img src="photos/' . htmlspecialchars($photo) . '" alt="' . $fullname . '">';
                        }
                        
                        // CV Links Layout
                        $cvLink = !empty($cv) 
                            ? '<a href="cvs/' . htmlspecialchars($cv) . '" target="_blank" class="btn-cv">View CV File</a>' 
                            : '<a href="#" class="btn-cv" style="pointer-events:none; opacity:0.6;">No CV</a>';
                        ?>

                        <div class="applicant-card">
                            <div class="card-header">
                                <div class="avatar-container">
                                    <?php echo $avatar; ?>
                                </div>
                                <div class="applicant-meta">
                                    <h3><?php echo $fullname; ?></h3>
                                    <span class="job-tag">Target: <?php echo $jobTitle; ?></span>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="info-row">
                                    <strong>Email:</strong>
                                    <span><?php echo $email; ?></span>
                                </div>
                                <div class="info-row">
                                    <strong>Phone:</strong>
                                    <span><?php echo $phone; ?></span>
                                </div>
                                <div class="info-row">
                                    <strong>Address:</strong>
                                    <span><?php echo $address; ?></span>
                                </div>
                                <div class="info-row">
                                    <strong>Declined Date:</strong>
                                    <span><?php echo $rejectedAt; ?></span>
                                </div>

                                <div class="skills-section">
                                    <p><strong>Skills Logged:</strong> <?php echo $skills; ?></p>
                                </div>

                                <div class="exp-section">
                                    <p><strong>Experience Metrics:</strong> <?php echo $experience; ?></p>
                                </div>
                            </div>

                            <div class="card-actions">
                                <?php echo $cvLink; ?>
                                <button type="button" class="btn-status" disabled>Archived / Rejected</button>
                            </div>
                        </div>

                        <?php
                    }
                } else {
                    ?>
                    </div><!-- Close grid safely before injecting empty state block layout -->
                    <div class="no-data">
                        <h3>No Rejected Candidates Found</h3>
                        <p>No system logs discovered matching active job exclusions currently.</p>
                    </div>
                    <div>
                    <?php
                }
                ?>
            </div>

        </div>
    </div>

    <!-- Footer System UI Element component -->
    <div class="footer">
        <p>&copy; 2026 Created by Maneesh and Pratik. Online Job Finding System. All Rights Reserved.</p>
    </div>

</body>
</html>