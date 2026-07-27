<?php
include "database.php"; // DB connection

// Job categories array
$job_categories = [
    "IT & Software", "Marketing & Sales", "Finance & Accounting",
    "Healthcare", "Education & Training", "Engineering",
    "Hospitality & Tourism", "Customer Service", "Human Resources",
    "Legal", "Construction", "Transport & Logistics",
    "Design & Creative", "Manufacturing", "Retail"
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
    <title>Available Jobs - Online Job Finding System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8f9fa; color: #333; }

        /* Navigation Bar */
        .navbar { background: #ffffff; border-bottom: 1px solid #e1e4e8; padding: 15px 50px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
        .logo-area h1 { font-size: 22px; color: #111; font-weight: 700; }
        .logo-area span { color:red; }
        
        .nav-container { display: flex; gap: 20px; align-items: center; }
        .nav-item { padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 15px; font-weight: 600; color: #555; transition: 0.3s; }
        .nav-item:hover { color: #b00000; background: #eefbf7; }
        .nav-item.active { background-color: #b00000; color: white !important; }

        /* Page Banner */
        .page-banner { background: #ffffff; padding: 40px 20px; text-align: center; border-bottom: 1px solid #eee; }
        .page-banner h2 { font-size: 32px; font-weight: 800; color: #111; }
        .page-banner h2 span { color: red; }
        .page-banner p { color: #000000; font-size: 15px; margin-top: 5px; }

        /* Main Container Layout (Filter Left, Jobs Right) */
        .main-layout { max-width: 1200px; margin: 40px auto; padding: 0 20px; display: grid; grid-template-columns: 300px 1fr; gap: 30px; }

        /* Filter Box (Sidebar style) */
        .filter-container { background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 25px; height: fit-content; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }
        .filter-container h3 { font-size: 18px; margin-bottom: 15px; font-weight: 700; color: black; }
        .filter-container label { font-size: 14px; color: black; display: block; margin-bottom: 8px; font-weight: 600; }
        
        .filter-select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; outline: none; margin-bottom: 15px; background-color: #fff; transition: 0.3s; }
        .filter-select:focus { border-color: black; }
        
        .submitbtn { width: 100%; background: #002fb0; color: white; border: none; padding: 12px; border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.3s; font-size: 15px; }
        .submitbtn:hover { background: #b02900; }

        /* Jobs Grid Column */
        .jobs-column { display: flex; flex-direction: column; gap: 20px; }
        
        /* Job Card - Horizontal & Modern View */
        .job-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 24px; transition: 0.3s; display: flex; flex-direction: column; position: relative; }
        .job-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.06); }
        
        /* Card Top Info */
        .job-header { display: flex; gap: 15px; align-items: flex-start; margin-bottom: 15px; }
        .job-comp-logo { width: 50px; height: 50px; border-radius: 6px; border: 1px solid #eee; padding: 4px; object-fit: contain; background: #fff; }
        .job-title-meta { flex: 1; }
        .job-title-meta h3 { font-size: 19px; color: #1a1a1a; font-weight: 700; margin-bottom: 3px; }
        .job-title-meta .category-lbl { font-size: 14px; color: #b02900; font-weight: 600; margin-bottom: 3px; }
        .job-title-meta .location-lbl { font-size: 13px; color: #777; }

        /* Card Specifications */
        .job-specs { display: flex; gap: 20px; margin-bottom: 15px; font-size: 13.5px; color: #555; border-bottom: 1px dashed #eee; padding-bottom: 15px; }
        .spec-item { display: flex; align-items: center; gap: 5px; }
        
        /* Description Text Minimalist */
        .job-desc { font-size: 14px; color: #666; line-height: 1.6; margin-bottom: 15px; }

        /* Badges & Actions Container */
        .job-footer { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-top: auto; }
        .job-badges { display: flex; gap: 8px; }
        .badge { background: #f1f3f5; color: #495057; font-size: 12px; font-weight: 600; padding: 5px 12px; border-radius: 4px; }
        
        /* Buttons */
        .job-actions { display: flex; gap: 10px; }
        .btn-view-details { border: 1px solid #ccc; background: white; color: #333; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 13.5px; font-weight: 600; transition: 0.3s; }
        .btn-view-details:hover { background: #f1f1f1; }
        
        .btn-quick-apply { background: #002fb0; color: white; border: none; padding: 8px 20px; border-radius: 6px; cursor: pointer; font-size: 13.5px; font-weight: 600; transition: 0.3s; }
        .btn-quick-apply:hover { background: #b02900; }

        /* Expiry Info */
        .expire-note { font-size: 12px; color: #888; margin-top: 12px; border-top: 1px solid #f9f9f9; padding-top: 8px; }

        /* Footer */
        .footer { background: #111; color: white; padding: 20px 20px; text-align: center; margin-top: 60px; }
        .footer p { font-size: 14px; }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="logo-area">
            <h1>Online <span>Job Find</span></h1>
        </div>
        <div class="nav-container">
            <a href="index.php" class="nav-item">Home</a>
            <a href="jobs.php" class="nav-item active">Jobs</a>
            <a href="admin.php" class="nav-item">Admin</a>
            <a href="user.php" class="nav-item">User</a>
            <a href="company.php" class="nav-item">Company</a>
        </div>
    </div>

    <div class="page-banner">
        <h2>Explore Available <span>Jobs</span></h2>
        <p>Find the best career opportunity matching your expertise and interest.</p>
    </div>

    <div class="main-layout">
        
        <div class="filter-container">
            <h3>Filters</h3>
            <form method="post">
                <label for="category">Job Category</label>
                <select name="category" id="category" class="filter-select">
                    <option value="all">-- All Categories --</option>
                    <?php foreach($job_categories as $cat): ?>
                        <option value="<?php echo $cat; ?>" <?php if($selectedCategory==$cat) echo "selected"; ?>>
                            <?php echo $cat; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="submit" name="Filter" value="Apply Filter" class="submitbtn">
            </form>
        </div>

        <div class="jobs-column">
            <?php
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    
                    $image_src = !empty($row['image']) ? "images/{$row['image']}" : "https://cdn-icons-png.flaticon.com/512/9374/9374944.png";
                    
                    // Expiry Days Calculation
                    $expiry_date = strtotime($row['expirydate']);
                    $current_date = time();
                    $diff_seconds = $expiry_date - $current_date;
                    $remaining_days = floor($diff_seconds / (60 * 60 * 24));
                    $remaining_text = ($remaining_days > 0) ? "{$remaining_days} days remaining to apply" : "Application Closed";
                    ?>
                    
                    <div class="job-card">
                        <div class="job-header">
                            <img class="job-comp-logo" src="<?php echo $image_src; ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                            <div class="job-title-meta">
                                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                                <div class="category-lbl"><?php echo htmlspecialchars($row['category']); ?></div>
                                <div class="location-lbl">📍 <?php echo htmlspecialchars($row['location'] ?? 'Kathmandu, Nepal'); ?></div>
                            </div>
                        </div>

                        <div class="job-desc">
                            <strong>Description:</strong> <?php echo htmlspecialchars(substr($row['description'], 0, 150)) . '...'; ?>
                        </div>

                        <div class="job-specs">
                            <div class="spec-item">💵 <strong>Salary:</strong> <?php echo htmlspecialchars($row['salary']); ?></div>
                            <div class="spec-item">🎓 <strong>Req:</strong> <?php echo htmlspecialchars($row['qualification'] ?? 'Graduate'); ?></div>
                        </div>

                        <div class="job-footer">
                            <div class="job-badges">
                                <span class="badge">Full Time</span>
                                <span class="badge">On-Site</span>
                            </div>
                            
                            <div class="job-actions">
                                <a href="#" class="btn-view-details" onclick="alert('Details page is under development'); return false;">View Details</a>
                                <button class="btn-quick-apply" onclick="alert('Please login first to apply'); window.location.href='userregister.php';">Apply Now</button>
                            </div>
                        </div>

                        <div class="expire-note">⏳ <strong>Status:</strong> <?php echo $remaining_text; ?> (Posted on: <?php echo date("d-m-Y", strtotime($row['openeddate'])); ?>)</div>
                    </div>

                    <?php
                }
            } else {
                echo "<div style='background: white; border: 1px solid #e2e8f0; padding: 40px; text-align: center; border-radius: 10px; color: #777;'>
                        <p style='font-size: 1.2rem; font-weight: 600;'>No jobs found!</p>
                        <p style='font-size: 0.95rem; margin-top: 5px;'>There are no active jobs available under this category at the moment.</p>
                      </div>";
            }
            ?>
        </div>

    </div>

    <div class="footer">
        <p>&copy; 2026 Created by Maneesh and Pratik. Online Job Finding System. All Rights Reserved.</p>
    </div>

</body>
</html>