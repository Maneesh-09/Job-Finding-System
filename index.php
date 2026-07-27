<?php
include "database.php"; 
$sql = "SELECT * FROM jobs ORDER BY id DESC";
$result = mysqli_query($con, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Job Finding System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8f9fa; color: #000000; }

        /* Top Header & Navigation */
        .navbar { background: #ffffff; border-bottom: 1px solid #e1e4e8; padding: 15px 50px; display: flex; justify-content: space-between; align-items: center; sticky: top; z-index: 1000; }
        .logo-area h1 { font-size: 22px; color: #111; font-weight: 700; }
        .logo-area span { color: red; }
        
        .nav-container { display: flex; gap: 20px; align-items: center; }
        .nav-item { padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 15px; font-weight: 600; color: #555; transition: 0.3s; }
        .nav-item:hover { color: #b02900; background: #eefbf7; }
        .nav-item.active { background-color: #b02900; color: white !important; }

        /* Hero Search Section*/
        .hero-section { background: #ffffff; padding: 60px 20px; text-align: center; border-bottom: 1px solid #f1f1f1; position: relative; }
        .hero-section h2 { font-size: 40px; font-weight: 800; color: #111; margin-bottom: 30px; }
        .hero-section h2 span { color: red; }
        
        .search-bar-container { max-width: 800px; margin: 0 auto; background: #ffffff; border: 1px solid #ddd; border-radius: 50px; padding: 8px 8px 8px 25px; display: flex; align-items: center; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .search-input { border: none; outline: none; font-size: 15px; width: 45%; color: #333; }
        .search-separator { width: 1px; height: 30px; background: #ddd; margin: 0 20px; }
        .search-btn { background: #002fb0; color: white; border: none; padding: 12px 35px; border-radius: 30px; font-weight: bold; cursor: pointer; transition: 0.3s; font-size: 15px; margin-left: auto; }
        .search-btn:hover { background: #bc0909; }

        /* Discover Tags */
        .discover-container { margin-top: 25px; display: flex; justify-content: center; align-items: center; gap: 10px; flex-wrap: wrap; }
        .discover-title { font-size: 14px; color: #777; font-weight: 600; }
        .discover-tag { background: #fff; border: 1px solid #ddd; padding: 6px 16px; border-radius: 20px; font-size: 13px; color: #444; text-decoration: none; transition: 0.3s; }
        .discover-tag:hover { border-color: #002fb0; color: #002fb0; background: #eefbf7; }

        /* Active Companies Slider Section */
        .section-title { max-width: 1200px; margin: 40px auto 20px auto; padding: 0 20px; font-size: 24px; font-weight: 700; color: #222; text-align: left; }
        .companies-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 20px; max-width: 1200px; margin: 0 auto; padding: 0 20px 40px 20px; }
        .company-card { background: white; border: 1px solid #e1e4e8; border-radius: 8px; padding: 20px; text-align: center; transition: 0.3s; }
        .company-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .company-logo { width: 60px; height: 60px; object-fit: contain; margin-bottom: 12px; }
        .company-name { font-size: 14px; font-weight: 600; color: #333; margin-bottom: 12px; height: 36px; overflow: hidden; }
        .view-jobs-btn { display: inline-block; border: 1px solid #002fb0; color: #002fb0; text-decoration: none; padding: 6px 15px; border-radius: 20px; font-size: 13px; font-weight: 600; transition: 0.3s; }
        .view-jobs-btn:hover { background: #bc0909; color: white; }

        /* Trending Jobs Section */
        .trending-jobs-container { background: #fdfdfd; padding: 40px 0 60px 0; border-top: 1px solid #eee; }
        .jobs-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 25px; max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        
        .job-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 24px; transition: 0.3s; display: flex; flex-direction: column; position: relative; }
        .job-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.07); }
        
        /* Header inside job card */
        .job-header { display: flex; gap: 15px; align-items: flex-start; margin-bottom: 15px; }
        .job-comp-logo { width: 48px; height: 48px; border-radius: 6px; border: 1px solid #eee; padding: 4px; object-fit: contain; }
        .job-title-meta { flex: 1; }
        .job-title-meta h3 { font-size: 18px; color: #1a1a1a; font-weight: 700; margin-bottom: 2px; }
        .job-title-meta .company-lbl { font-size: 14px; color: #bc0909; font-weight: 600; margin-bottom: 2px; }
        .job-title-meta .location-lbl { font-size: 13px; color: #777; }

        /* Specifications (Salary, Experience) */
        .job-specs { display: flex; gap: 20px; margin-bottom: 15px; font-size: 13.5px; color: #555; }
        .spec-item { display: flex; align-items: center; gap: 5px; }
        
        /* Badges (Full Time, Mid Level, On-Site) */
        .job-badges { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px; }
        .badge { background: #f1f3f5; color: #495057; font-size: 12px; font-weight: 600; padding: 5px 12px; border-radius: 4px; }
        
        /* Action Buttons */
        .job-actions { display: flex; gap: 12px; margin-top: auto; }
        .btn-view-details { flex: 1; border: 1px solid #ccc; background: white; color: #333; text-align: center; padding: 10px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 600; transition: 0.3s; }
        .btn-view-details:hover { background: #f1f1f1; border-color: #bbb; }
        
        .btn-quick-apply { flex: 1; background: #002fb0; color: white; border: none; text-align: center; padding: 10px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; transition: 0.3s; }
        .btn-quick-apply:hover { background: #bc0909; }

        /* Remaining Days Limit */
        .expire-note { font-size: 12px; color: #888; margin-top: 12px; text-align: left; }

        /* Footer */
        .footer { background: #111; color: white; padding: 20px 20px; text-align: center; border-top: 1px solid #222; }
        .footer p { font-size: 14px; }
    </style>
</head>

<body>

    <!-- Header Navigation -->
    <div class="navbar">
        <div class="logo-area">
            <h1>Online <span>Job Find</span></h1>
        </div>
        <div class="nav-container">
            <a href="index.php" class="nav-item active">Home</a>
            <a href="jobs.php" class="nav-item">Jobs</a>
            <a href="admin.php" class="nav-item">Admin</a>
            <a href="user.php" class="nav-item">User</a>
            <a href="company.php" class="nav-item">Company</a>
        </div>
    </div>

    <div class="hero-section">
        <h2>Find Your <span>Dream Job</span> With Online Job Find</h2>
        
        <form action="jobs.php" method="GET">
            <div class="search-bar-container">
                <input type="text" name="search" class="search-input" placeholder="Search jobs by title, skill or company">
                <div class="search-separator"></div>
                <input type="text" name="location" class="search-input" placeholder="Kathmandu, Nepal" style="width: 30%;">
                <button type="submit" class="search-btn">Search</button>
            </div>
        </form>

        <div class="discover-container">
            <span class="discover-title">Discover:</span>
            <a href="jobs.php?search=Chief Operating Officer" class="discover-tag">Chief Operating Officer</a>
            <a href="jobs.php?search=Sales Lead" class="discover-tag">Sales Lead</a>
            <a href="jobs.php?search=Telemarketing Officer" class="discover-tag">Telemarketing Officer</a>
            <a href="jobs.php?search=Content Creator" class="discover-tag">Content Creator</a>
        </div>
    </div>

    <div class="section-title">Companies Actively Hiring</div>
    <div class="companies-grid">
        <div class="company-card">
            <img class="company-logo" src="https://cdn-icons-png.flaticon.com/512/3061/3061341.png" alt="Company 1">
            <div class="company-name">Dhuni Software</div>
            <a href="jobs.php" class="view-jobs-btn">View Jobs</a>
        </div>
        <div class="company-card">
            <img class="company-logo" src="https://cdn-icons-png.flaticon.com/512/4205/4205906.png" alt="Company 2">
            <div class="company-name">Hanu-uest Foods</div>
            <a href="jobs.php" class="view-jobs-btn">View Jobs</a>
        </div>
        <div class="company-card">
            <img class="company-logo" src="https://cdn-icons-png.flaticon.com/512/1063/1063196.png" alt="Company 3">
            <div class="company-name">Anytime Hygiene</div>
            <a href="jobs.php" class="view-jobs-btn">View Jobs</a>
        </div>
        <div class="company-card">
            <img class="company-logo" src="https://cdn-icons-png.flaticon.com/512/13716/13716757.png" alt="Company 4">
            <div class="company-name">Total Tools Nepal</div>
            <a href="jobs.php" class="view-jobs-btn">View Jobs</a>
        </div>
        <div class="company-card">
            <img class="company-logo" src="https://cdn-icons-png.flaticon.com/512/2942/2942789.png" alt="Company 5">
            <div class="company-name">Electra</div>
            <a href="jobs.php" class="view-jobs-btn">View Jobs</a>
        </div>
    </div>

    <!-- Trending Jobs Grid -->
    <div class="trending-jobs-container">
        <div class="section-title">🔥 Trending Jobs</div>
        
        <div class="jobs-grid">
            <?php
            $count = 0;
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    if ($count >= 6) break; 
                    $image_src = !empty($row['image']) ? "images/{$row['image']}" : "https://cdn-icons-png.flaticon.com/512/9374/9374944.png";
                    
                    // Expiry Date 
                    $expiry_date = strtotime($row['expirydate']);
                    $current_date = time();
                    $diff_seconds = $expiry_date - $current_date;
                    $remaining_days = floor($diff_seconds / (60 * 60 * 24));
                    
                    $remaining_text = ($remaining_days > 0) ? "Job Expire: {$remaining_days} days remaining" : "Application Closed";
                    ?>
                    
                    <div class="job-card">
                        <!-- Header Meta Info -->
                        <div class="job-header">
                            <img class="job-comp-logo" src="<?php echo $image_src; ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                            <div class="job-title-meta">
                                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                                <div class="company-lbl"><?php echo htmlspecialchars($row['category']); ?></div>
                                <div class="location-lbl">📍 <?php echo htmlspecialchars($row['location'] ?? 'Kathmandu, Nepal'); ?></div>
                            </div>
                        </div>

                        <!-- Salary & Experience Info -->
                        <div class="job-specs">
                            <div class="spec-item">💵 <strong><?php echo htmlspecialchars($row['salary']); ?></strong></div>
                            <div class="spec-item">🎓 <strong><?php echo htmlspecialchars($row['qualification'] ?? 'Graduate'); ?></strong></div>
                        </div>

                        <!-- Badges tags -->
                        <div class="job-badges">
                            <span class="badge">On-Site</span>
                            <span class="badge">Mid Level</span>
                            <span class="badge">Full Time</span>
                        </div>

                        <!-- Actions -->
                        <div class="job-actions">
                            <a href="jobs.php" class="btn-view-details">View Details</a>
                            <button class="btn-quick-apply" onclick="alert('Please login first to apply'); window.location.href='userregister.php';">Quick Apply</button>
                        </div>

                        <!-- Expiry Date Alert -->
                        <div class="expire-note">⏳ <?php echo $remaining_text; ?></div>
                    </div>

                    <?php
                    $count++;
                }
            } else {
                echo "<p style='grid-column: 1/-1; text-align: center; color: #777; padding: 40px;'>No jobs posted yet. Check back later!</p>";
            }
            ?>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; 2026 Created by Maneesh and Pratik. Online Job Finding System. All Rights Reserved.</p>
    </div>

</body>
</html>