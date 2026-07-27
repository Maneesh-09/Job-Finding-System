<?php
session_start();
if (isset($_SESSION["username"])) {
    include "database.php"; // DB connection

    // Job categories array
    $job_categories = [
        "IT & Software",
        "Marketing & Sales",
        "Finance & Accounting",
        "Healthcare",
        "Education & Training",
        "Engineering",
        "Hospitality & Tourism",
        "Customer Service",
        "Human Resources",
        "Legal",
        "Construction",
        "Transport & Logistics",
        "Design & Creative",
        "Manufacturing",
        "Retail"
    ];

    // Initialize category filter
    $selectedCategory = '';
    if (isset($_POST['Filter'])) {
        $selectedCategory = mysqli_real_escape_string($con, $_POST['category']);
        if ($selectedCategory != 'all') {
            $sql = "SELECT * FROM jobs WHERE category='$selectedCategory' ORDER BY id DESC";
        } else {
            $sql = "SELECT * FROM jobs ORDER BY id DESC";
        }
    } else {
        $sql = "SELECT * FROM jobs ORDER BY id DESC";
    }

    $result = mysqli_query($con, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Manage Jobs</title>
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

        /* Modern Filter Container UI */
        .filter-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 25px; margin-bottom: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.01); }
        .filter-form { display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
        .filter-form label { font-size: 14.5px; font-weight: 600; color: #475569; }
        
        .filter-select { padding: 10px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14.5px; outline: none; background-color: #f8fafc; min-width: 240px; transition: 0.3s; }
        .filter-select:focus { border-color: #00b074; background-color: #fff; }
        
        .btn-filter { background: #00b074; color: white; border: none; padding: 10px 24px; border-radius: 8px; font-size: 14.5px; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-filter:hover { background: #009460; }

        /* Premium Table Container & Wrap Styling */
        .table-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); }
        .table-container { width: 100%; overflow-x: auto; margin-top: 15px; border-radius: 8px; border: 1px solid #e2e8f0; }
        
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14.5px; min-width: 1100px; }
        th { background-color: #f8fafc; color: #475569; font-weight: 700; padding: 14px 18px; border-bottom: 2px solid #e2e8f0; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; }
        td { padding: 14px 18px; border-bottom: 1px solid #e2e8f0; color: #334155; vertical-align: middle; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        tr:hover { background-color: #f8fafc; }
        
        /* Badges for Meta Data */
        .category-badge { background: #eefbf7; color: #00b074; font-size: 12.5px; font-weight: 600; padding: 4px 10px; border-radius: 6px; display: inline-block; }
        .salary-tag { font-weight: 600; color: #0f172a; }
        .date-text { color: #64748b; font-size: 13.5px; font-family: monospace; }

        /* Action Buttons Interaction styling */
        .action-holder { display: flex; gap: 8px; }
        .action-btn { border: none; padding: 7px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; transition: 0.2s; text-decoration: none; display: inline-block; }
        
        .edit-btn { background-color: #eefbf7; color: #00b074; border: 1px solid rgba(0, 176, 116, 0.2); }
        .edit-btn:hover { background-color: #00b074; color: #fff; }
        
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
                <li><a href="adminviewjobs.php" class="active">View Jobs</a></li>
                <li><a href="adminviewpendingrequest.php">All Pending Applications</a></li>
                <li><a href="allacceptedapplicant.php">All Accepted Applicants</a></li>
                <li><a href="allrejectedapplicant.php">All Rejected Applicants</a></li>
            </ul>
        </div>

        <!-- Right Content Management Area -->
        <div class="main-content">
            
            <div class="page-title-section">
                <h2>Vacancy Control Board</h2>
                <p>Monitor employment listings, filter postings by corporate industry sectors, or perform listing operations.</p>
            </div>

            <!-- Filter Card Wrapper -->
            <div class="filter-card">
                <form method="post" class="filter-form">
                    <label for="category">Select Job Sector:</label>
                    <select name="category" id="category" class="filter-select">
                        <option value="all">-- All Categories --</option>
                        <?php foreach ($job_categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php if ($selectedCategory == $cat) echo "selected"; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="Filter" class="btn-filter">Filter Grid</button>
                </form>
            </div>

            <!-- Jobs Display Grid -->
            <?php if (mysqli_num_rows($result) > 0) { ?>
                <div class="table-card">
                    <h3 style="font-size: 17px; font-weight: 700; color: #1e293b; margin-bottom: 5px;">Active Opportunities</h3>
                    
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Corporate Entity</th>
                                    <th>Job Title</th>
                                    <th>Description Summary</th>
                                    <th>Location</th>
                                    <th>Min. Qualification</th>
                                    <th>Offered Salary</th>
                                    <th>Category</th>
                                    <th>Opened On</th>
                                    <th>Expiry Target</th>
                                    <th style="text-align: center;">Operations</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $company_id = intval($row['company_id']);
                                    $company_name = "Unknown Enterprise";

                                    // Fetch company name separately
                                    $company_query = "SELECT company_name FROM company WHERE cid = $company_id";
                                    $company_result = mysqli_query($con, $company_query);
                                    if ($company_result && mysqli_num_rows($company_result) > 0) {
                                        $company_data = mysqli_fetch_assoc($company_result);
                                        $company_name = $company_data['company_name'];
                                    }
                                    ?>
                                    <tr>
                                        <td><strong>#<?php echo $row['id']; ?></strong></td>
                                        <td style="font-weight: 600; color: #111;"><?php echo htmlspecialchars($company_name); ?></td>
                                        <td style="font-weight: 600; color: #00b074;"><?php echo htmlspecialchars($row['title']); ?></td>
                                        <td title="<?php echo htmlspecialchars($row['description']); ?>"><?php echo htmlspecialchars($row['description']); ?></td>
                                        <td><?php echo htmlspecialchars($row['location']); ?></td>
                                        <td><?php echo htmlspecialchars($row['qualification']); ?></td>
                                        <td><span class="salary-tag"><?php echo htmlspecialchars($row['salary']); ?></span></td>
                                        <td><span class="category-badge"><?php echo htmlspecialchars($row['category']); ?></span></td>
                                        <td><span class="date-text"><?php echo date("d-m-Y", strtotime($row['openeddate'])); ?></span></td>
                                        <td><span class="date-text" style="color:#de3e3e;"><?php echo date("d-m-Y", strtotime($row['expirydate'])); ?></span></td>
                                        <td align="center">
                                            <div class="action-holder">
                                                <button class="action-btn edit-btn" onclick="window.location.href='admineditjobs.php?job_id=<?php echo $row['id']; ?>'">Edit</button>
                                                <button class="action-btn delete-btn" onclick="if(confirm('Are you sure you want to permanently delete this job post vacancy?')) window.location.href='admindeletejobs.php?job_id=<?php echo $row['id']; ?>';">Delete</button>
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
                    <p>🔍 No active career employment listings found matching the selected parameters.</p>
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
    echo '<script>
            alert("Please login first");
            window.location.href = "index.php";
          </script>';
}
?>