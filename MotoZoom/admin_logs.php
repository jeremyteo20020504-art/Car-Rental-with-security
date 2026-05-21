<?php
include 'db.php';
session_start();

// Check if the user is an admin, as only admins should access the logs page
if ($_SESSION['role'] != 'admin') {
    header('Location: index.php'); // Redirect to home if not an admin
    exit;
}

// Set the number of entries per page
$entriesPerPage = 4;

// Get the current page from the URL, default to page 1 if not set
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $entriesPerPage;

// Fetch Admin Login Logs (with pagination)
$adminLogQuery = $pdo->prepare("SELECT * FROM admin_login_logs ORDER BY login_time DESC LIMIT :limit OFFSET :offset");
$adminLogQuery->bindValue(':limit', $entriesPerPage, PDO::PARAM_INT);
$adminLogQuery->bindValue(':offset', $offset, PDO::PARAM_INT);
$adminLogQuery->execute();
$adminLogs = $adminLogQuery->fetchAll(PDO::FETCH_ASSOC);


// Count total records for pagination
$totalAdminLogsQuery = $pdo->query("SELECT COUNT(*) FROM admin_login_logs");
$totalAdminLogs = $totalAdminLogsQuery->fetchColumn();


// Calculate total pages
$totalAdminPages = ceil($totalAdminLogs / $entriesPerPage);

include "sidebara.php"; //only admin can access, no need to customise sidebar
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Log</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Admin Login Logs</h1>
        <table border="1">
            <thead>
                <tr>
                    <th>Entry Number (#)</th> <!-- Number column to clearly mark entries -->
                    <th>Login Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($adminLogs): ?>
                    <?php 
                    $serial = $offset + 1; // Start numbering based on the page offset
                    foreach ($adminLogs as $log): 
                    ?>
                        <tr>
                            <td><?php echo $serial++; ?></td> <!-- Increment for each row -->
                            <td><?php echo htmlspecialchars($log['login_time']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="2">No admin logs available.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>


        <!-- Page Count -->

        <div>
            <p>Page <?php echo $page; ?> of <?php echo $totalAdminPages; ?></p>

            <nav>
                <div class="page_change">
                    <?php if ($page > 1): ?>
                        <a href="admin_logs.php?page=<?php echo $page - 1; ?>">Back</a>
                    <?php endif; ?>
                    <?php if ($page < $totalAdminPages): ?>
                        <a href="admin_logs.php?page=<?php echo $page + 1; ?>">Next</a>			
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <div class="navbar navbar-admin">
            <p><a href="admin.php">← Back to Admin Panel</a></p>
        </div>  
</body>
</html>
