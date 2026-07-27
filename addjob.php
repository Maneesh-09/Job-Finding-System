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

$error1 = $error2 = $error3 = $error4 = $error5 = $error6 = $error7 = $error8 = '';
$title = $description = $location = $qualification = $salary = $expirydate = $category = '';
$image = '';

if (isset($_POST["addjob"])) {
    $title = mysqli_real_escape_string($con, $_POST["title"]);
    $description = mysqli_real_escape_string($con, $_POST["description"]);
    $location = mysqli_real_escape_string($con, $_POST["location"]);
    $qualification = mysqli_real_escape_string($con, $_POST["qualification"]);
    $salary = mysqli_real_escape_string($con, $_POST["salary"]);
    $expirydate = mysqli_real_escape_string($con, $_POST["expirydate"]);
    $category = mysqli_real_escape_string($con, $_POST["category"]);

    // Validation
    if (empty($title)) $error1 = "*Job title is required";
    if (empty($description)) $error2 = "*Description is required";
    if (empty($location)) $error3 = "*Location is required";
    if (empty($qualification)) $error4 = "*Qualification is required";
    if (empty($salary)) $error5 = "*Salary is required";
    if (empty($category) || $category == "") $error8 = "*Job Category is required";
    if (empty($_FILES["file"]["name"])) $error6 = "*Job image is required";
    if (empty($expirydate)) $error7 = "*Expiry date is required";

    if (empty($error1) && empty($error2) && empty($error3) && empty($error4) && empty($error5) && empty($error6) && empty($error7) && empty($error8)) {
        $imagedirectory = "images/";
        
        // Image name sanitize र अनौठो क्यारेक्टर हटाउन
        $image = time() . '_' . basename($_FILES["file"]["name"]); 
        $target_file = $imagedirectory . $image;

        if(move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            // SQL मा category र openeddate (स्वत: आजको मिति) थपिएको छ
            $cid = $_SESSION['cid'];
            $sql = "INSERT INTO jobs (title, description, location, qualification, salary, image, username, openeddate, expirydate, category, company_id) 
             VALUES ('$title', '$description', '$location', '$qualification', '$salary', '$image', '$username', NOW(), '$expirydate', '$category', '$cid')";

            if (mysqli_query($con, $sql)) {
                echo "<script>alert('Job added successfully!'); window.location='jobs.php';</script>";
                exit;
            } else {
                echo "<script>alert('Error adding job: " . mysqli_error($con) . "');</script>";
            }
        } else {
            $error6 = "*Failed to upload image. Check directory permissions.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Job - Online Job Finding System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8f9fa; color: #333; display: flex; flex-direction: column; min-height: 100vh; }

        /* Navigation Bar */
        .navbar { background: #ffffff; border-bottom: 1px solid #e1e4e8; padding: 15px 50px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
        .logo-area h1 { font-size: 22px; color: #111; font-weight: 700; }
        .logo-area span { color: red; }
        
        .nav-container { display: flex; gap: 20px; align-items: center; }
        .nav-item { padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 15px; font-weight: 600; color: #555; transition: 0.3s; }
        .nav-item:hover { color: #b02900; background: #eefbf7; }
        
        .user-badge { background: #eefbf7; color: #0041b0; font-weight: 700; padding: 8px 16px; border-radius: 20px; font-size: 14px; border: 1px solid rgba(0, 176, 116, 0.2); }

        /* Page Banner */
        .page-banner { background: #ffffff; padding: 40px 20px; text-align: center; border-bottom: 1px solid #eee; }
        .page-banner h2 { font-size: 32px; font-weight: 800; color: #111; }
        .page-banner h2 span { color: red; }
        .page-banner p { color: #666; font-size: 15px; margin-top: 5px; }

        /* Main Form Container */
        .form-container { flex: 1; max-width: 900px; width: 100%; margin: 40px auto; padding: 0 20px; }
        .form-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
        .full-width { grid-column: span 2; }

        /* Form Controls */
        .form-group { margin-bottom: 5px; }
        .form-label { display: block; font-size: 14px; font-weight: 600; color: black; margin-bottom: 8px; }
        
        .form-input { width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; outline: none; transition: 0.3s; background-color: #f8fafc; }
        .form-input:focus { border-color: black; background-color: #fff; box-shadow: black; }
        
        textarea.form-input { resize: vertical; font-family: inherit; }
        
        /* File Upload custom look styling */
        input[type="file"].form-input { padding: 9px 12px; background: #fff; cursor: pointer; }

        /* Error Styling */
        .error { color: #de3e3e; font-size: 13px; font-weight: 600; margin-top: 6px; }

        /* Actions Button Row */
        .form-actions { display: flex; gap: 15px; margin-top: 20px; border-top: 1px solid #eee; padding-top: 25px; }
        
        .btn-submit { background: #002fb0; color: white; border: none; padding: 14px 30px; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-submit:hover { background: #b02900; }
        
        .btn-cancel { background: white; border: 1px solid #cbd5e1; color: #4a5568; padding: 14px 30px; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; transition: 0.3s; text-decoration: none; text-align: center; }
        .btn-cancel:hover { background: #f8fafc; border-color: #b8c5d6; }

        /* Footer */
        .footer { background: #111; color: white; padding: 40px 20px; text-align: center; margin-top: auto; border-top: 1px solid #222; }
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
            <a href="jobs.php" class="nav-item">Jobs</a>
            <span class="user-badge">💼 Company: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        </div>
    </div>

    <div class="page-banner">
        <h2>Post a New <span>Vacancy</span></h2>
        <p>Fill out the details below to publish your opening position to thousands of job seekers.</p>
    </div>

    <div class="form-container">
        <div class="form-card">
            
            <form method="post" enctype="multipart/form-data">
                <div class="form-grid">
                    
                    <div class="form-group">
                        <label class="form-label">Job Title</label>
                        <input type="text" name="title" placeholder="e.g. Senior PHP Developer" class="form-input" value="<?php echo htmlspecialchars($title); ?>">
                        <div class="error"><?php echo $error1; ?></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Job Category</label>
                        <select name="category" class="form-input">
                            <option value="">-- Select Category --</option>
                            <option value="IT & Software" <?php if($category=="IT & Software") echo "selected"; ?>>IT & Software</option>
                            <option value="Marketing & Sales" <?php if($category=="Marketing & Sales") echo "selected"; ?>>Marketing & Sales</option>
                            <option value="Finance & Accounting" <?php if($category=="Finance & Accounting") echo "selected"; ?>>Finance & Accounting</option>
                            <option value="Healthcare" <?php if($category=="Healthcare") echo "selected"; ?>>Healthcare</option>
                            <option value="Education & Training" <?php if($category=="Education & Training") echo "selected"; ?>>Education & Training</option>
                            <option value="Engineering" <?php if($category=="Engineering") echo "selected"; ?>>Engineering</option>
                            <option value="Hospitality & Tourism" <?php if($category=="Hospitality & Tourism") echo "selected"; ?>>Hospitality & Tourism</option>
                            <option value="Customer Service" <?php if($category=="Customer Service") echo "selected"; ?>>Customer Service</option>
                            <option value="Human Resources" <?php if($category=="Human Resources") echo "selected"; ?>>Human Resources</option>
                            <option value="Design & Creative" <?php if($category=="Design & Creative") echo "selected"; ?>>Design & Creative</option>
                        </select>
                        <div class="error"><?php echo $error8; ?></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Job Location</label>
                        <input type="text" name="location" placeholder="e.g. Tinkune, Kathmandu" class="form-input" value="<?php echo htmlspecialchars($location); ?>">
                        <div class="error"><?php echo $error3; ?></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Salary Range</label>
                        <input type="text" name="salary" placeholder="e.g. Rs. 40,000 - Rs. 60,000" class="form-input" value="<?php echo htmlspecialchars($salary); ?>">
                        <div class="error"><?php echo $error5; ?></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Required Qualification</label>
                        <input type="text" name="qualification" placeholder="e.g. Bachelor in BCA / CSIT" class="form-input" value="<?php echo htmlspecialchars($qualification); ?>">
                        <div class="error"><?php echo $error4; ?></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Application Expiry Date</label>
                        <input type="date" name="expirydate" class="form-input" value="<?php echo htmlspecialchars($expirydate); ?>">
                        <div class="error"><?php echo $error7; ?></div>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Upload Job Banner / Logo Image</label>
                        <input type="file" name="file" class="form-input">
                        <div class="error"><?php echo $error6; ?></div>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Detailed Job Description</label>
                        <textarea name="description" placeholder="Specify job responsibilities, required skills, and benefits..." class="form-input" rows="6"><?php echo htmlspecialchars($description); ?></textarea>
                        <div class="error"><?php echo $error2; ?></div>
                    </div>
                    
                </div>

                <div class="form-actions">
                    <button type="submit" name="addjob" class="btn-submit">Publish Vacancy</button>
                    <a href="adminhomepage.php" class="btn-cancel">Cancel & Back</a>
                </div>
            </form>

        </div>
    </div>

    <div class="footer">
        <p>&copy; 2026 Created by Maneesh and Pratik. Online Job Finding System. All Rights Reserved.</p>
    </div>

</body>
</html>