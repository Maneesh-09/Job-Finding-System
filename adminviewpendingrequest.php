<?php
session_start();
if (isset($_SESSION["username"])) {
    include "database.php"; // DB connection

    // Fetch all applications
    $sql = "SELECT * FROM jobapplication ORDER BY id DESC";
    $result = mysqli_query($con, $sql);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Dashboard - Job Applications</title>
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
            
            table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14.5px; min-width: 1200px; }
            th { background-color: #f8fafc; color: #475569; font-weight: 700; padding: 14px 18px; border-bottom: 2px solid #e2e8f0; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
            td { padding: 14px 18px; border-bottom: 1px solid #e2e8f0; color: #334155; vertical-align: middle; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            tr:hover { background-color: #f8fafc; }
            
            /* UI Enhancements */
            .cv-link { display: inline-block; background: #f0fdf4; color: #166534; font-weight: 600; font-size: 13px; padding: 5px 10px; border-radius: 6px; text-decoration: none; border: 1px solid rgba(22, 101, 52, 0.15); transition: 0.2s; }
            .cv-link:hover { background: #00b074; color: #fff; }
            .date-text { color: #64748b; font-size: 13.5px; font-family: monospace; }
            .job-accent { font-weight: 600; color: #00b074; }

            /* Action Buttons Interaction styling */
            .action-holder { display: flex; gap: 8px; }
            .action-btn { border: none; padding: 7px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; transition: 0.2s; text-decoration: none; display: inline-block; }
            
            .delete-btn { background-color: #fdf2f2; color: #de3e3e; border: 1px solid rgba(222, 62, 62, 0.15); }
            .delete-btn:hover { background-color: #de3e3e; color: #fff; }

            .no-data-msg { background: #ffffff; border: 1px solid #e2e8f0; padding: 40px; border-radius: 12px; text-align: center; color: #64748b; font-size: 16px; font-weight: 600; box-shadow: 0 4px 20px rgba(0,0,0,0.02); }

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
                    <li><a href="adminviewuser.php">View Users</a></li>
                    <li><a href="adminviewjobs.php">View Jobs</a></li>
                    <li><a href="adminviewpendingrequest.php" class="active">All Pending Applications</a></li>
                    <li><a href="allacceptedapplicant.php">All Accepted Applicants</a></li>
                    <li><a href="allrejectedapplicant.php">All Rejected Applicants</a></li>
                </ul>
            </div>

            <!-- Right Content Management Area -->
            <div class="main-content">
                
                <div class="page-title-section">
                    <h2>Application Registries</h2>
                    <p>Review candidate portfolios, screen core technical skills, track timeline indices, and moderate submissions.</p>
                </div>

                <!-- Applications Data Grid -->
                <?php if (mysqli_num_rows($result) > 0) { ?>
                    <div class="table-card">
                        <h3 style="font-size: 17px; font-weight: 700; color: #1e293b; margin-bottom: 5px;">Submitted Applications</h3>
                        
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>App ID</th>
                                        <th>Job ID</th>
                                        <th>Company</th>
                                        <th>Job Title</th>
                                        <th>Applicant Name</th>
                                        <th>Email Address</th>
                                        <th>Phone</th>
                                        <th>Current Address</th>
                                        <th>Skills Summary</th>
                                        <th>Experience</th>
                                        <th>Applied Date</th>
                                        <th>Document</th>
                                        <th style="text-align: center;">Operations</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $job_id = intval($row['job_id']);

                                        // Fetch job details
                                        $job_title = "Unknown";
                                        $company_name = "Unknown Enterprise";
                                        $job_query = "SELECT title, company_id FROM jobs WHERE id = $job_id";
                                        $job_result = mysqli_query($con, $job_query);
                                        if ($job_result && mysqli_num_rows($job_result) > 0) {
                                            $job_data = mysqli_fetch_assoc($job_result);
                                            $job_title = $job_data['title'];
                                            $company_id = intval($job_data['company_id']);

                                            // Fetch company name
                                            $company_query = "SELECT company_name FROM company WHERE cid = $company_id";
                                            $company_result = mysqli_query($con, $company_query);
                                            if ($company_result && mysqli_num_rows($company_result) > 0) {
                                                $company_data = mysqli_fetch_assoc($company_result);
                                                $company_name = $company_data['company_name'];
                                            }
                                        }
                                        ?>
                                        <tr>
                                            <td><strong>#<?php echo $row['id']; ?></strong></td>
                                            <td><code><?php echo $job_id; ?></code></td>
                                            <td style="font-weight: 600; color: #111;"><?php echo htmlspecialchars($company_name); ?></td>
                                            <td><span class="job-accent"><?php echo htmlspecialchars($job_title); ?></span></td>
                                            <td style="font-weight: 600; color: #111;"><?php echo htmlspecialchars($row['fullname']); ?></td>
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                                            <td title="<?php echo htmlspecialchars($row['skills']); ?>"><?php echo htmlspecialchars($row['skills']); ?></td>
                                            <td title="<?php echo htmlspecialchars($row['experiences']); ?>"><?php echo htmlspecialchars($row['experiences']); ?></td>
                                            <td><span class="date-text"><?php echo date("d-m-Y", strtotime($row['applied_date'])); ?></span></td>
                                            <td><a href="uploads/<?php echo urlencode($row['cv']); ?>" target="_blank" class="cv-link">📄 View CV</a></td>
                                            <td align="center">
                                                <div class="action-holder">
                                                    <button class="action-btn delete-btn" onclick="if(confirm('Are you sure you want to permanently delete this job application dossier?')) window.location.href='admindeleteapplication.php?id=<?php echo $row['id']; ?>';">Delete</button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="no-data-msg">
                        <p>🔍 No candidate job applications have been submitted to the platform yet.</p>
                    </div>
                <?php } ?>

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
    echo '<script>alert("Please login first"); window.location.href = "index.php";</script>';
}
?>