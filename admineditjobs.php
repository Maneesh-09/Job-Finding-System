<?php
session_start();
include "database.php";

if (!isset($_SESSION["username"])) {
    echo '<script>
            alert("Please login first");
            window.location.href = "index.php";
          </script>';
    exit;
}

$username = $_SESSION["username"];

// ---------------------
// Initialize Variables
// ---------------------
$error1 = $error2 = $error3 = $error4 = $error5 = $error6 = $error7 = $error8 = '';
$title = $description = $location = $qualification = $salary = $category = $expirydate = $image = '';
$job_id = $_GET['job_id'] ?? '';

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

// ---------------------
// Step 1: Fetch Job Details
// ---------------------
if (!empty($job_id)) {
    $job_id = intval($job_id); // Prevent SQL Injection
    $sql = "SELECT * FROM jobs WHERE id = '$job_id'";
    $result = mysqli_query($con, $sql);

    if ($result && mysqli_num_rows($result) == 1) {
        $job = mysqli_fetch_assoc($result);
        $title = $job['title'];
        $description = $job['description'];
        $location = $job['location'];
        $qualification = $job['qualification'];
        $salary = $job['salary'];
        $category = $job['category'];
        $expirydate = $job['expirydate'];
        $image = $job['image'];
    } else {
        echo "<script>alert('Job not found!'); window.location='adminviewjobs.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('Invalid request!'); window.location='adminviewjobs.php';</script>";
    exit;
}

// ---------------------
// Step 2: Handle Update
// ---------------------
if (isset($_POST["updatejob"])) {
    $title = mysqli_real_escape_string($con, $_POST["title"]);
    $description = mysqli_real_escape_string($con, $_POST["description"]);
    $location = mysqli_real_escape_string($con, $_POST["location"]);
    $qualification = mysqli_real_escape_string($con, $_POST["qualification"]);
    $salary = mysqli_real_escape_string($con, $_POST["salary"]);
    $category = mysqli_real_escape_string($con, $_POST["category"]);
    $expirydate = mysqli_real_escape_string($con, $_POST["expirydate"]);

    // Validation
    if (empty($title)) $error1 = "*Job title is required";
    if (empty($description)) $error2 = "*Description is required";
    if (empty($location)) $error3 = "*Location is required";
    if (empty($qualification)) $error4 = "*Qualification is required";
    if (empty($salary)) $error5 = "*Salary is required";
    if (empty($category)) $error7 = "*Please select a category";
    if (empty($expirydate)) $error8 = "*Please select expiry date";

    // Image Handling
    if (!empty($_FILES["file"]["name"])) {
        $target_dir = "images/";
        $new_image = time() . '_' . basename($_FILES["file"]["name"]); // Add timestamp to avoid conflicts
        $target_file = $target_dir . $new_image;

        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            $image = $new_image;
        }
    }

    // If no errors, update job
    if (
        empty($error1) && empty($error2) && empty($error3) &&
        empty($error4) && empty($error5) && empty($error7) && empty($error8)
    ) {
        $update_sql = "UPDATE jobs SET 
                        title = '$title',
                        description = '$description',
                        location = '$location',
                        qualification = '$qualification',
                        salary = '$salary',
                        category = '$category',
                        expirydate = '$expirydate',
                        image = '$image'
                      WHERE id = '$job_id'";

        if (mysqli_query($con, $update_sql)) {
            echo "<script>alert('Job updated successfully!'); window.location='adminviewjobs.php';</script>";
            exit;
        } else {
            echo "<script>alert('Error updating job: " . mysqli_error($con) . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Edit Job</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8f9fa; color: #333; display: flex; flex-direction: column; min-height: 100vh; }

        /* Modern Admin Navbar */
        .navbar { background: #ffffff; border-bottom: 1px solid #e1e4e8; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
        .logo-area h1 { font-size: 20px; color: #111; font-weight: 700; }
        .logo-area span { color: red; }
        
        .admin-profile-section { display: flex; align-items: center; gap: 15px; }
        .admin-badge { background: #eefbf7; color: #00b074; font-weight: 700; padding: 6px 14px; border-radius: 20px; font-size: 13.5px; border: 1px solid rgba(0, 176, 116, 0.2); }
        
        .btn-logout { background: #fff; border: 1px solid #de3e3e; color: #de3e3e; padding: 6px 16px; border-radius: 6px; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: 0.3s; }
        .btn-logout:hover { background: #de3e3e; color: #fff; }

        /* Dashboard Main Layout */
        .dashboard-layout { display: flex; flex: 1; }

        /* Sidebar Design */
        .sidebar { width: 260px; background: #ffffff; border-right: 1px solid #e2e8f0; padding: 30px 15px; }
        .nav-links { list-style: none; display: flex; flex-direction: column; gap: 8px; }
        .nav-links li a { display: block; padding: 12px 18px; color: #555; font-size: 14.5px; font-weight: 600; text-decoration: none; border-radius: 8px; transition: 0.3s; }
        .nav-links li a:hover { color: #00b074; background: #eefbf7; }
        .nav-links li a.active { background: #00b074; color: white !important; }

        /* Content Wrapper */
        .main-content { flex: 1; padding: 40px; background-color: #f8f9fa; }

        /* Form Card Styling */
        .form-container { max-width: 850px; width: 100%; margin: 0 auto; }
        .form-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); }
        .form-card h2 { font-size: 22px; font-weight: 700; margin-bottom: 25px; color: #1a1a1a; border-bottom: 2px solid #f1f1f1; padding-bottom: 12px; }
        .form-card h2 span { color: #00b074; }

        /* 2-Column Responsive Form Grid */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .full-width { grid-column: span 2; }

        /* Form Elements */
        .form-group { margin-bottom: 5px; }
        .form-label { display: block; font-size: 14px; font-weight: 600; color: #4a5568; margin-bottom: 8px; }
        
        .form-input { width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; outline: none; transition: 0.3s; background-color: #f8fafc; }
        .form-input:focus { border-color: #00b074; background-color: #fff; box-shadow: 0 0 0 3px rgba(0, 176, 116, 0.1); }
        
        textarea.form-input { resize: vertical; font-family: inherit; }
        input[type="file"].form-input { padding: 9px 12px; background: #fff; cursor: pointer; }

        /* Error Text */
        .error { color: #de3e3e; font-size: 12.5px; font-weight: 600; margin-top: 5px; }

        /* Image Preview Box */
        .preview-box { background: #f8fafc; border: 1px dashed #cbd5e1; padding: 15px; border-radius: 8px; display: flex; align-items: center; gap: 15px; }
        .preview-box img { width: 90px; height: 90px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0; }
        .preview-info { font-size: 13.5px; color: #666; }

        /* Action Buttons Row */
        .form-actions { display: flex; gap: 15px; margin-top: 20px; border-top: 1px solid #eee; padding-top: 25px; grid-column: span 2; }
        
        .btn-submit { background: #00b074; color: white; border: none; padding: 13px 28px; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-submit:hover { background: #009460; }
        
        .btn-cancel { background: white; border: 1px solid #cbd5e1; color: #4a5568; padding: 13px 28px; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; transition: 0.3s; text-decoration: none; text-align: center; }
        .btn-cancel:hover { background: #f8fafc; border-color: #b8c5d6; }

        /* Footer */
        .footer { background: #111; color: #888; padding: 25px 20px; text-align: center; border-top: 1px solid #222; margin-top: auto; }
        .footer p { font-size: 13.5px; }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="logo-area">
            <h1>Online <span>Job Find</span></h1>
        </div>
        <div class="admin-profile-section">
            <span class="admin-badge">🛡️ Admin Panel</span>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </div>

    <div class="dashboard-layout">
        
        <div class="sidebar">
            <ul class="nav-links">
                <li><a href="adminhomepage.php">Home</a></li>
                <li><a href="adminviewcompany.php">View Companies</a></li>
                <li><a href="adminviewuser.php">View Users</a></li>
                <li><a href="addcategory.php">Add Job Category</a></li>
                <li><a href="admincompanyrequest.php">Company Requests</a></li>
                <li><a href="adminviewjobs.php">Jobs List</a></li>
                <li><a href="adminviewjobs.php" class="active">Editing Job</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="form-container">
                <div class="form-card">
                    <h2>Edit Job <span>Details</span></h2>

                    <form method="post" enctype="multipart/form-data">
                        <div class="form-grid">
                            
                            <div class="form-group">
                                <label class="form-label">Job Title</label>
                                <input type="text" name="title" placeholder="Enter job title" class="form-input" value="<?php echo htmlspecialchars($title); ?>">
                                <div class="error"><?php echo $error1; ?></div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-input">
                                    <option value="">-- Select Category --</option>
                                    <?php
                                    foreach ($job_categories as $cat) {
                                        $selected = ($cat == $category) ? "selected" : "";
                                        echo "<option value='$cat' $selected>" . htmlspecialchars($cat) . "</option>";
                                    }
                                    ?>
                                </select>
                                <div class="error"><?php echo $error7; ?></div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" placeholder="Enter job location" class="form-input" value="<?php echo htmlspecialchars($location); ?>">
                                <div class="error"><?php echo $error3; ?></div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Salary Range</label>
                                <input type="text" name="salary" placeholder="Enter salary range" class="form-input" value="<?php echo htmlspecialchars($salary); ?>">
                                <div class="error"><?php echo $error5; ?></div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Required Qualification</label>
                                <input type="text" name="qualification" placeholder="Enter qualification required" class="form-input" value="<?php echo htmlspecialchars($qualification); ?>">
                                <div class="error"><?php echo $error4; ?></div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Expiry Date</label>
                                <input type="date" name="expirydate" class="form-input" value="<?php echo htmlspecialchars($expirydate); ?>">
                                <div class="error"><?php echo $error8; ?></div>
                            </div>

                            <?php if (!empty($image)) { ?>
                                <div class="form-group full-width">
                                    <label class="form-label">Current Job Banner</label>
                                    <div class="preview-box">
                                        <img src="images/<?php echo htmlspecialchars($image); ?>" alt="Job Image">
                                        <div class="preview-info">
                                            <strong>File name:</strong> <?php echo htmlspecialchars($image); ?><br>
                                            <span style="color: #666;">If you don't upload a new file, this banner will remain active.</span>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="form-group full-width">
                                <label class="form-label">Upload New Banner / Logo (Optional)</label>
                                <input type="file" name="file" class="form-input">
                            </div>

                            <div class="form-group full-width">
                                <label class="form-label">Detailed Job Description</label>
                                <textarea name="description" placeholder="Specify job responsibilities, skills..." class="form-input" rows="6"><?php echo htmlspecialchars($description); ?></textarea>
                                <div class="error"><?php echo $error2; ?></div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="updatejob" class="btn-submit">Save & Update</button>
                                <a href="adminviewjobs.php" class="btn-cancel">Cancel & Back</a>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2026 Created by Maneesh and Pratik. Online Job Finding System. All Rights Reserved.</p>
    </div>

</body>
</html>