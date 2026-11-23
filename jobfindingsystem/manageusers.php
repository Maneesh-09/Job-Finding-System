<?php
require_once __DIR__ . '/auth.php';
require_role('admin');
include "database.php";

if (!isset($con) && isset($conn)) { $con = $conn; }

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin.php");
    exit();
}

// User Delete Action
require_once __DIR__ . '/records.php';
if (isset($_GET['delete_id'])) {
    $delete_id = filter_var($_GET['delete_id'], FILTER_VALIDATE_INT);
    if (!$delete_id || $delete_id < 1) { http_response_code(400); exit('Invalid record ID.'); }
    $con->begin_transaction();
    try {
        delete_account_records($con, 'user', $delete_id);
        $con->commit();
    } catch (Throwable $exception) {
        $con->rollback();
        throw $exception;
    }
    header('Location: manageusers.php');
    exit;
}

$users = mysqli_query($con, "SELECT * FROM user ORDER BY uid DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8f9fa; color: #333; display: flex; flex-direction: column; min-height: 100vh; }

        .navbar { background: #ffffff; border-bottom: 1px solid #e1e4e8; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
        .logo-area h1 { font-size: 22px; color: #111; font-weight: 700; }
        .logo-area span { color: red; }
        .admin-badge { background: #b02900; color: white; padding: 8px 16px; border-radius: 20px; font-size: 13.5px; font-weight: 600; }
        .btn-logout { background: #002fb0; color: white; padding: 8px 18px; border-radius: 8px; font-size: 13.5px; font-weight: 600; text-decoration: none; margin-left: 10px; }
        .btn-logout:hover { background: #b02900; }

        .dashboard-layout { display: flex; flex: 1; }
        .sidebar { width: 260px; background: #ffffff; border-right: 1px solid #e2e8f0; padding: 30px 15px; }
        .nav-links { list-style: none; display: flex; flex-direction: column; gap: 8px; }
        .nav-links li a { display: block; padding: 12px 18px; color: #475569; font-size: 14.5px; font-weight: 600; text-decoration: none; border-radius: 8px; }
        .nav-links li a:hover { color: white; background: #002fb0; }
        .nav-links li a.active { background: #b02900; color: white !important; }

        .main-content { flex: 1; padding: 40px; background-color: #f8f9fa; }
        .page-title { font-size: 24px; font-weight: 700; color: #1e293b; margin-bottom: 20px; }

        .data-table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; }
        .data-table th, .data-table td { padding: 14px 18px; text-align: left; font-size: 14px; border-bottom: 1px solid #e2e8f0; }
        .data-table th { background: #f1f5f9; font-weight: 700; color: #475569; }
        .btn-delete { background: #de3e3e; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12.5px; font-weight: 600; }
        .btn-delete:hover { background: #b91c1c; }

        .footer { background: #0f172a; color: white; padding: 18px; text-align: center; margin-top: auto; }
        .footer p { font-size: 13.5px; color: white; }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="logo-area"><h1>Online <span>Job Find</span></h1></div>
        <div>
            <span class="admin-badge">🛡️ Admin Panel</span>
            <a href="logout.php" class="btn-logout">Log Out</a>
        </div>
    </div>

    <div class="dashboard-layout">
        <div class="sidebar">
            <ul class="nav-links">
                <li><a href="adminhomepage.php">Dashboard</a></li>
                <li><a href="manageusers.php" class="active">Manage Users</a></li>
                <li><a href="managecompanies.php">Manage Companies</a></li>
                <li><a href="managejobs.php">Manage Jobs</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h2 class="page-title">Registered Job Seekers / Users</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name / Username</th>
                        <th>Email</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users && mysqli_num_rows($users) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($users)): ?>
                            <tr>
                                <td><?php echo $row['uid'] ?? 'N/A'; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['name'] ?? $row['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['email'] ?? 'N/A'); ?></td>
                                <td>
                                    <a href="manageusers.php?delete_id=<?php echo $row['uid']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align:center;">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer"><p>&copy; 2026 Created by Maneesh and Pratik. Online Job Finding System. All Rights Reserved.</p></div>
</body>
</html>