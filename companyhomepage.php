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

// Fetch jobs posted by this specific company corporate identity account logs
$jobQuery = "SELECT * FROM jobs WHERE username='$username' ORDER BY id DESC";
$result = mysqli_query($con, $jobQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard - Home</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8f9fa; color: #333; display: flex; flex-direction: column; min-height: 100vh; }

        /* Modern Corporate Top Navbar */
        .navbar { background: #ffffff; border-bottom: 1px solid #e1e4e8; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
        .logo-area h1 { font-size: 20px; color: #111; font-weight: 700; }
        .logo-area span { color: red; }
        
        .profile-section { display: flex; align-items: center; gap: 15px; }
        .company-badge { background: #002fb0; color: white; font-weight: 700; padding: 6px 14px; border-radius: 10px; font-size: 13.5px;}
        
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
        
        .page-header { margin-bottom: 30px; border-bottom: 2px solid #eefbf7; padding-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
        .page-header h2 { font-size: 24px; font-weight: 700; color: #1e293b; }
        .page-header p { font-size: 14.5px; color: #64748b; margin-top: 4px; }
        
        .btn-add-new { background: #002fb0; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 700; text-decoration: none; transition: 0.3s; }
        .btn-add-new:hover { background: #b02900; }

        /* Grid System for Job Circular Cards */
        .jobs-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px; }

        /* Premium Job Card Architecture */
        .job-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.01); display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s; }
        .job-card:hover { transform: translateY(-4px); box-shadow: 0 6px 25px rgba(0,0,0,0.04); }
        
        /* Banner Graphics Elements */
        .job-banner { width: 100%; height: 160px; position: relative; background: #f1f5f9; overflow: hidden; }
        .job-banner img { width: 100%; height: 100%; object-fit: cover; }
        
        .category-badge { position: absolute; top: 12px; right: 12px; background: rgba(0, 0, 0, 0.6); color: #fff; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 4px; backdrop-filter: blur(4px); }

        .card-body { padding: 20px; display: flex; flex-direction: column; gap: 12px; flex: 1; }
        .card-body h3 { font-size: 18px; font-weight: 700; color: #1e293b; line-height: 1.3; }
        
        .description-text { font-size: 13.5px; color: #64748b; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 4px; }

        .info-row { display: flex; justify-content: space-between; border-bottom: 1px dashed #f1f5f9; padding-bottom: 6px; font-size: 13.5px; }
        .info-row strong { color: #475569; }
        .info-row span { color: #334155; }

        /* Active Timeline Logs styling */
        .timeline-box { background: #f8fafc; border-radius: 8px; padding: 10px; border: 1px solid #e2e8f0; margin-top: 5px; font-size: 12.5px; color: #64748b; }
        .timeline-box div { display: flex; justify-content: space-between; margin-bottom: 2px; }

        /* Action Buttons Grid */
        .card-actions { padding: 0 20px 20px 20px; background: #fff; border-top: 1px solid white; margin-top: auto; }
        .btn-delete { display: block; width: 100%; background: #b02900 ; color: white; border: 1px solid black; padding: 10px; border-radius: 6px; font-size: 14px; font-weight: 700; text-align: center; text-decoration: none; cursor: pointer; transition: 0.2s; margin-top: 15px; }
        .btn-delete:hover { background: #002fb0; color: #white; color: #fff; }

        /* Empty State */
        .no-data { text-align: center; padding: 60px 20px; background: white; border: 1px solid #e2e8f0; border-radius: 12px; width: 100%; max-width: 600px; margin: 40px auto; grid-column: 1 / -1; }
        .no-data h3 { font-size: 18px; color: #475569; margin-bottom: 8px; }
        .no-data p { font-size: 14px; color: #94a3b8; }

        /* Footer */
        .footer { background: #111; color: #888; padding: 25px 20px; text-align: center; border-top: 1px solid #222; margin-top: auto; width: 100%; }
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
                <li><a href="companyhomepage.php" class="active">Home</a></li>
                <li><a href="companyaddjobs.php">Add Jobs</a></li>
                <li><a href="companyrecivedrequest.php">Received Requests</a></li>
                <li><a href="companyaccepteduser.php">Accepted User</a></li>
                <li><a href="companydeclinerequest.php">Declined User</a></li>
                <li><a href="companyprofile.php">Profile</a></li>
            </ul>
        </div>

        <!-- Right Core Main Content Context -->
        <div class="main-content">
            
            <div class="page-header">
                <div>
                    <h2>My Published Jobs</h2>
                    <p>Manage, track, and monitor active job postings published by your corporate recruitment system account.</p>
                </div>
                <a href="companyaddjobs.php" class="btn-add-new">+ Add New Job</a>
            </div>

            <div class="jobs-grid">
                <?php
                if ($result) {
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $jobId       = intval($row['id']);
                            $title       = htmlspecialchars($row['title']);
                            $description = htmlspecialchars($row['description']);
                            $location    = htmlspecialchars($row['location']);
                            $qualification= htmlspecialchars($row['qualification']);
                            $salary      = htmlspecialchars($row['salary']);
                            $category    = !empty($row['category']) ? htmlspecialchars($row['category']) : 'General';
                            $openeddate  = date("d-M-Y", strtotime($row['openeddate']));
                            $expirydate  = date("d-M-Y", strtotime($row['expirydate']));
                            $image       = htmlspecialchars($row['image']);

                            // Graphic Display Fallback Validation
                            $imageSrc = "images/" . $image;
                            if (empty($image) || !file_exists($imageSrc)) {
                                $imageSrc = "images/default-job.png"; // Fallback image asset placeholder context
                            }
                            ?>

                            <div class="job-card">
                                <div class="job-banner">
                                    <img src="<?php echo $imageSrc; ?>" alt="<?php echo $title; ?>">
                                    <span class="category-badge"><?php echo $category; ?></span>
                                </div>

                                <div class="card-body">
                                    <h3><?php echo $title; ?></h3>
                                    <p class="description-text"><?php echo $description; ?></p>
                                    
                                    <div class="info-row">
                                        <strong>Location:</strong>
                                        <span><?php echo $location; ?></span>
                                    </div>
                                    <div class="info-row">
                                        <strong>Salary:</strong>
                                        <span><?php echo $salary; ?></span>
                                    </div>
                                    <div class="info-row">
                                        <strong>Min Eligibility:</strong>
                                        <span><?php echo $qualification; ?></span>
                                    </div>

                                    <div class="timeline-box">
                                        <div>
                                            <strong>Published:</strong>
                                            <span><?php echo $openeddate; ?></span>
                                        </div>
                                        <div>
                                            <strong>Deadline:</strong>
                                            <span style="color: black; font-weight: 600;"><?php echo $expirydate; ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-actions">
                                    <a href="companydeletejob.php?id=<?php echo $jobId; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to permanently delete this job circular listing? This action cannot be reverted.');">
                                        Remove Listing
                                    </a>
                                </div>
                            </div>

                            <?php
                        }
                    } else {
                        ?>
                        <div class="no-data">
                            <h3>No Active Circulars Located</h3>
                            <p>You haven't posted any jobs yet. Click on '+ Add New Circular' above to hire tech talent.</p>
                        </div>
                        <?php
                    }
                } else {
                    echo "<div class='no-data'><h3 style='color:red;'>System Fetching Error:</h3><p>".mysqli_error($con)."</p></div>";
                }
                ?>
            </div>

        </div>
    </div>

    <!-- Footer System Component UI Element -->
    <div class="footer">
        <p>&copy; 2026 Created by Maneesh and Pratik. Online Job Finding System. All Rights Reserved.</p>
    </div>

</body>
</html>