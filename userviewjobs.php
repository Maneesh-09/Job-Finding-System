<?php
session_start();
if (isset($_SESSION["username"])) {
    include "database.php"; // DB connection gateway

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
        <title>Explore Jobs | Candidate Dashboard</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
            body { background-color: #f8f9fa; color: #333; display: flex; min-height: 100vh; }

            /* Modern Left Navigation Sidebar Layout */
            .sidebar { width: 260px; background: #1e293b; color: white; display: flex; flex-direction: column; position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; }
            .sidebar-brand { padding: 24px; border-bottom: 1px solid #ffffff; }
            .sidebar-brand h2 { font-size: 19px; font-weight: 700; color: #ffffff; }
            .sidebar-brand h2 span { color: red; }
            
            .nav-links { list-style: none; padding: 20px 0; display: flex; flex-direction: column; gap: 4px; }
            .nav-links li a { display: block; padding: 12px 24px; color: #94a3b8; text-decoration: none; font-size: 14.5px; font-weight: 500; transition: 0.2s; border-left: 4px solid transparent; }
            .nav-links li a:hover { background: #334155; color: #ffffff; }
            .nav-links li a.active { background: #0f172a; color: white; border-left-color: #002fb0; font-weight: 600; }

            /* Right Application Core Workspace */
            .app-container { flex: 1; margin-left: 260px; display: flex; flex-direction: column; min-height: 100vh; }

            /* Upper Console Header Panel */
            .topbar { background: #ffffff; border-bottom: 1px solid #e2e8f0; height: 70px; padding: 0 40px; display: flex; justify-content: space-between; align-items: center; }
            .topbar-title { font-size: 16px; font-weight: 600; color: #64748b; }
            
            .user-action-deck { display: flex; align-items: center; gap: 12px; }
            .btn-profile { background: #b02900; color: white; padding: 8px 16px; border-radius: 6px; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: 0.2s; }
            .btn-profile:hover { background: #002fb0; }
            .btn-logout { background: #002fb0; color: white; padding: 8px 16px; border-radius: 6px; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: 0.2s; border: none; cursor: pointer; }
            .btn-logout:hover { background: #b02900; }

            /* Main Processing View Arena */
            .workspace { padding: 40px; flex: 1; }

            /* Premium Integrated Filtering Matrix Bar */
            .filter-container { background: #ffffff; border: 1px solid #e2e8f0; padding: 20px 25px; border-radius: 10px; margin-bottom: 35px; box-shadow: 0 4px 12px rgba(0,0,0,0.01); }
            .filter-form { display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
            .filter-form label { font-size: 14px; font-weight: 600; color: #475569; }
            
            .filter-select { padding: 10px 16px; font-size: 14px; border: 1px solid #cbd5e1; border-radius: 6px; background-color: #fff; min-width: 250px; outline: none; cursor: pointer; color: #334155; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23475569'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; background-size: 16px; }
            .filter-select:focus { border-color: #b02900; }
            
            .btn-filter { background: #b02900; color: white; border: none; padding: 10px 22px; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; transition: 0.2s; }
            .btn-filter:hover { background: #002fb0; }

            /* Clean Multi-Column Job Grid Core Engine */
            .jobs-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 30px; }
            
            /* Premium Corporate Job Presentation Card */
            .job-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 4px 15px rgba(0,0,0,0.01); transition: transform 0.2s, box-shadow 0.2s; }
            .job-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.04); }
            
            .job-image-frame { width: 100%; height: 180px; background: #f1f5f9; position: relative; overflow: hidden; border-bottom: 1px solid #f1f5f9; }
            .job-image-frame img { width: 100%; height: 100%; object-fit: cover; }
            
            .job-body { padding: 22px; flex: 1; display: flex; flex-direction: column; gap: 12px; }
            .job-title { font-size: 18.5px; font-weight: 700; color: #1e293b; margin-bottom: 2px; }
            
            .job-desc { font-size: 13.5px; color: #64748b; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 4px; }
            
            /* Structured Parameter Pairs */
            .meta-list { display: flex; flex-direction: column; gap: 6px; }
            .meta-item { font-size: 13px; color: #475569; display: flex; align-items: center; }
            .meta-item strong { width: 100px; color: #64748b; font-weight: 600; flex-shrink: 0; }
            
            .badge-category { background: #e8f7f2; color: #b02900; padding: 3px 8px; border-radius: 4px; font-weight: 600; font-size: 12px; }

            /* Action Buttons Footer Matrix inside Cards */
            .job-card-footer { padding: 18px 22px; border-top: 1px solid #f1f5f9; background: #fafafa; }
            .btn-apply { background: #002fb0; color: white; border: none; padding: 10px; border-radius: 6px; font-size: 13.5px; font-weight: 700; cursor: pointer; text-align: center; transition: 0.2s; width: 100%; display: block; text-decoration: none; }
            .btn-apply:hover { background: #b02900; }

            .no-jobs { background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 50px; text-align: center; color: #64748b; font-size: 15px; font-weight: 500; grid-column: 1 / -1; }

            /* Structural System Footer Positioning */
            .footer { background: #ffffff; border-top: 1px solid #e2e8f0; padding: 20px; text-align: center; width: 100%; margin-top: auto; }
            .footer p { font-size: 13.5px; color: #000000; }

            @media(max-width: 768px) {
                .filter-form { flex-direction: column; align-items: flex-start; }
                .filter-select { width: 100%; }
                .btn-filter { width: 100%; }
            }
        </style>
    </head>

    <body>

        <!-- Left Column Sidebar System Menu -->
        <div class="sidebar">
            <div class="sidebar-brand">
                <h2>Online <span>Job Find</span></h2>
            </div>
            <ul class="nav-links">
                <li><a href="userhomepage.php">Home Dashboard</a></li>
                <li><a href="userviewjobs.php" class="active">View Vacant Jobs</a></li>
                <li><a href="userjobrequeststatus.php">Job Request Status</a></li>
                <li><a href="useraccepted.php">View Accepted Jobs</a></li>
                <li><a href="userrejected.php">View Declined Jobs</a></li>
                <li><a href="userprofile.php">Candidate Profile</a></li>
            </ul>
        </div>

        <!-- Right Side Platform Main Block Container -->
        <div class="app-container">
            
            <!-- Upper Horizontal Interface Dashboard Bar -->
            <div class="topbar">
                <div class="topbar-title">Employment Opportunity Index</div>
                <div class="user-action-deck">
                    <a href="userprofile.php" class="btn-profile">View Profile</a>
                    <a href="logout.php" class="btn-logout">Logout</a>
                </div>
            </div>

            <!-- Workspace Viewport Platform -->
            <div class="workspace">

                <!-- Advanced Refined Selection Filter Hub -->
                <div class="filter-container">
                    <form method="POST" class="filter-form">
                        <label for="category">Select Job Category</label>
                        <select name="category" id="category" class="filter-select">
                            <option value="all">--Career Domains --</option>
                            <?php foreach ($job_categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" <?php if ($selectedCategory == $cat) echo "selected"; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="Filter" class="btn-filter">Filter</button>
                    </form>
                </div>

                <!-- Live Dynamic Job Listings Matrix Grid -->
                <div class="jobs-grid">
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $job_img = !empty($row['image']) ? htmlspecialchars($row['image']) : 'default-job.jpg';
                            ?>
                            
                            <!-- Dynamic Placed Corporate Card Architecture -->
                            <div class="job-card">
                                <div class="job-image-frame">
                                    <img src="images/<?php echo $job_img; ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                                </div>
                                <div class="job-body">
                                    <h3 class="job-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                                    <p class="job-desc"><?php echo htmlspecialchars($row['description']); ?></p>
                                    
                                    <div class="meta-list">
                                        <div class="meta-item"><strong>Location:</strong> <span><?php echo htmlspecialchars($row['location']); ?></span></div>
                                        <div class="meta-item"><strong>Education:</strong> <span><?php echo htmlspecialchars($row['qualification']); ?></span></div>
                                        <div class="meta-item"><strong>Compensation:</strong> <span><?php echo htmlspecialchars($row['salary']); ?></span></div>
                                        <div class="meta-item"><strong>Category:</strong> <span class="badge-category"><?php echo htmlspecialchars($row['category']); ?></span></div>
                                        <div class="meta-item"><strong>Posted Date:</strong> <span><?php echo date("d M Y", strtotime($row['openeddate'])); ?></span></div>
                                        <div class="meta-item"><strong>Expiry Frame:</strong> <span style="color: #010101; font-weight:600;"><?php echo date("d M Y", strtotime($row['expirydate'])); ?></span></div>
                                    </div>
                                </div>
                                <div class="job-card-footer">
                                    <a href="applyjob.php?job_id=<?php echo urlencode($row['id']); ?>" class="btn-apply">Initialize Application</a>
                                </div>
                            </div>

                            <?php
                        }
                    } else {
                        echo "<div class='no-jobs'>No active corporate employment records matching this category criteria node.</div>";
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
    <?php
} else {
    echo '<script>
            alert("Please login first");
            window.location.href = "index.php";
          </script>';
}
?>