<?php
session_start();
include "database.php"; // DB connection

if (isset($_SESSION["username"])) {
    $username = mysqli_real_escape_string($con, $_SESSION["username"]);

    // ✅ Step 1: Get company ID
    $companyresult = mysqli_query($con, "SELECT cid FROM company WHERE username = '$username'");
    $cid = null;

    if ($companyresult && mysqli_num_rows($companyresult) === 1) {
        $companydata = mysqli_fetch_assoc($companyresult);
        $cid = $companydata['cid'];
    }

    $acceptedapplicant = [];

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

            // ✅ Step 3: Fetch acceptedapplicant for these jobs
            if (!empty($jobid)) {
                $jobidstr = implode(",", array_map('intval', $jobid)); // safe for SQL
                $applicantquery = "SELECT * FROM accepted_application WHERE job_id IN ($jobidstr) ORDER BY accepted_at DESC";
                $applicantresult = mysqli_query($con, $applicantquery);

                if ($applicantresult && mysqli_num_rows($applicantresult) > 0) {
                    while ($row = mysqli_fetch_assoc($applicantresult)) {
                        $job_id = $row['job_id'];
                        $row['job_title'] = isset($jobs[$job_id]) ? $jobs[$job_id] : 'Unknown';
                        $acceptedapplicant[] = $row;
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
    <title>Company Dashboard - Accepted Users</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8f9fa; color: #333; display: flex; flex-direction: column; min-height: 100vh; }

        /* Modern Company Top Navbar */
        .navbar { background: #ffffff; border-bottom: 1px solid #e1e4e8; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
        .logo-area h1 { font-size: 20px; color: #111; font-weight: 700; }
        .logo-area span { color: red; }
        
        .profile-section { display: flex; align-items: center; gap: 15px; }
        .company-badge { background: #002fb0; color: white; font-weight: 700; padding: 6px 14px; border-radius: 10px; font-size: 13.5px;}
        
        .btn-logout { background: #b02900; border: 0px solid #de3e3e; color: white; padding: 8px 16px; border-radius: 10px; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: 0.3s; }
        .btn-logout:hover { background: #de3e3e; color: #fff; }

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
        
        /* Page Header Title */
        .page-title-section { margin-bottom: 30px; }
        .page-title-section h2 { font-size: 24px; font-weight: 700; color: #1e293b; }
        .page-title-section p { font-size: 14.5px; color: #64748b; margin-top: 4px; }

        /* Grid Layout for Accepted Profiles */
        .applicant-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 25px; }

        /* Premium Applicant Card Design */
        .talent-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; display: flex; flex-direction: column; gap: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.015); position: relative; }
        
        .card-header { display: flex; align-items: center; gap: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; }
        .avatar-box { width: 65px; height: 65px; border-radius: 50%; overflow: hidden; border: 2px solid #b02900; background: #f8fafc; display: flex; align-items: center; justify-content: center; }
        .avatar-box img { width: 100%; height: 100%; object-fit: cover; }
        .avatar-box span { font-size: 11px; color: #64748b; text-align: center; font-weight: 600; }
        
        .header-details h3 { font-size: 17px; font-weight: 700; color: #1e293b; }
        .job-tag { display: inline-block; background: #eefbf7; color: #b02900; font-size: 12px; font-weight: 700; padding: 3px 10px; border-radius: 4px; margin-top: 4px; }

        .card-body { display: flex; flex-direction: column; gap: 8px; font-size: 14px; color: #475569; }
        .info-row { display: flex; justify-content: space-between; border-bottom: 1px dashed #f1f5f9; padding-bottom: 6px; }
        .info-row strong { color: #1e293b; }
        
        .text-block { display: flex; flex-direction: column; gap: 2px; margin-top: 4px; }
        .text-block span { font-size: 13.5px; color: #334155; background: #f8fafc; padding: 6px 10px; border-radius: 6px; border: 1px solid #e2e8f0; }

        .card-footer { margin-top: auto; display: flex; flex-direction: column; gap: 10px; pt: 10px; }
        
        .cv-link { display: block; text-align: center; background: #f0fdf4; color: #166534; font-weight: 600; font-size: 13.5px; padding: 9px; border-radius: 6px; text-decoration: none; border: 1px solid rgba(22, 101, 52, 0.15); transition: 0.2s; }
        .cv-link:hover { background: #b02900; color: #fff; }

        /* Badge Status Button */
        .status-badge { width: 100%; border: none; padding: 10px; border-radius: 6px; font-size: 13.5px; font-weight: 700; background-color: #0084b0; color: white; cursor: default; text-align: center; }

        .no-data-msg { background: #ffffff; border: 1px solid #e2e8f0; padding: 40px; border-radius: 12px; text-align: center; color: #64748b; font-size: 16px; font-weight: 600; box-shadow: 0 4px 20px rgba(0,0,0,0.02); width: 100%; }

        /* Footer */
        .footer { background: #111; color: white; padding: 20px 20px; text-align: center; border-top: 1px solid #222; margin-top: auto; width: 100%; }
        .footer p { font-size: 13.5px; }
    </style>
</head>
<body>

    <!-- Top Corporate Navbar -->
    <div class="navbar">
        <div class="logo-area">
            <h1>Online <span>Job Find</span></h1>
        </div>
        <div class="profile-section">
            <span class="company-badge">🏢 Company Profile</span>
            <a href="logout.php" class="btn-logout">Log Out</a>
        </div>
    </div>

    <!-- Dashboard Body Container -->
    <div class="dashboard-layout">
        
        <!-- Left Sidebar Navigation -->
        <div class="sidebar">
            <ul class="nav-links">
                <li><a href="companyhomepage.php">Home</a></li>
                <li><a href="companyaddjobs.php">Add Jobs</a></li>
                <li><a href="companyrecivedrequest.php">Received Requests</a></li>
                <li><a href="companyaccepteduser.php" class="active">Accepted User</a></li>
                <li><a href="companydeclinerequest.php">Declined User</a></li>
                <li><a href="companyprofile.php">Profile</a></li>
            </ul>
        </div>

        <!-- Right Content Management Area -->
        <div class="main-content">
            
            <div class="page-title-section">
                <h2>Accepted Talent Profiles</h2>
                <p>Review contact credentials and technical background parameters for applicants accepted into your organization.</p>
            </div>

            <!-- Accepted Applicants Grid -->
            <div class="applicant-grid">
                <?php
                if (!empty($acceptedapplicant)) {
                    foreach ($acceptedapplicant as $row) {
                        $photo = $row['photo'];
                        $cv = $row['cv'];
                        $fullname = $row['fullname'];
                        $email = $row['email'];
                        $phone = $row['phone'];
                        $address = $row['address'];
                        $skills = $row['skills'];
                        $experience = $row['experiences'];
                        $jobTitle = $row['job_title'];
                        $acceptedAt = date("d-m-Y", strtotime($row['accepted_at']));

                        $cvLink = !empty($cv) ? "<a href='cvs/".urlencode($cv)."' target='_blank' class='cv-link'>📄 View Professional CV</a>" : "<span class='cv-link' style='background:#f1f5f9;color:#64748b;border-color:#cbd5e1;'>No CV Provided</span>";
                        
                        $photoTag = (!empty($photo) && file_exists("photos/" . $photo)) 
                                    ? "<img src='photos/".htmlspecialchars($photo)."' alt='".htmlspecialchars($fullname)."'>" 
                                    : "<span>No Photo</span>";
                        ?>
                        
                        <div class="talent-card">
                            <div class="card-header">
                                <div class="avatar-box">
                                    <?php echo $photoTag; ?>
                                </div>
                                <div class="header-details">
                                    <h3><?php echo htmlspecialchars($fullname); ?></h3>
                                    <span class="job-tag"><?php echo htmlspecialchars($jobTitle); ?></span>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="info-row">
                                    <strong>Email:</strong>
                                    <span><?php echo htmlspecialchars($email); ?></span>
                                </div>
                                <div class="info-row">
                                    <strong>Phone:</strong>
                                    <span><?php echo htmlspecialchars($phone); ?></span>
                                </div>
                                <div class="info-row">
                                    <strong>Location:</strong>
                                    <span><?php echo htmlspecialchars($address); ?></span>
                                </div>
                                <div class="info-row">
                                    <strong>Approved On:</strong>
                                    <span style="font-family: monospace; color: #64748b;"><?php echo $acceptedAt; ?></span>
                                </div>

                                <div class="text-block">
                                    <strong>Skills Architecture:</strong>
                                    <span><?php echo htmlspecialchars($skills); ?></span>
                                </div>

                                <div class="text-block">
                                    <strong>Experience Logs:</strong>
                                    <span><?php echo htmlspecialchars($experience); ?></span>
                                </div>
                            </div>

                            <div class="card-footer">
                                <?php echo $cvLink; ?>
                                <button type="button" class="status-badge">✓ Approved / Hired</button>
                            </div>
                        </div>

                    <?php
                    }
                } else {
                    echo "<div class='no-data-msg'><p>🔍 No accepted applicant records found inside your corporate dashboard archive.</p></div>";
                }
                ?>
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