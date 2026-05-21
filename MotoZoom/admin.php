<?php
// Admin dashboard
include 'db.php';
session_start();

// Ensure only admins have access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit; // Stop further code execution
}

include 'sidebara.php'; // Since only admins have access

// Page Setup
$limit  = 5;
$page   = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$page   = max($page, 1);
$offset = ($page - 1) * $limit;

// Update booking status
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['booking_id'], $_POST['new_status'])
) {
    $stmt = $pdo2->prepare(
        "UPDATE pend_booking 
         SET status = :status 
         WHERE id = :id"
    );

    $stmt->execute([
        'status' => $_POST['new_status'],
        'id'     => $_POST['booking_id']
    ]);
}


$countStmt = $pdo2->prepare(
    "SELECT COUNT(*) 
     FROM pend_booking 
     WHERE status = :status"
);

$countStmt->execute(['status' => 'pending']);
$totalPending = (int) $countStmt->fetchColumn();

$countStmt->execute(['status' => 'active']);
$totalActive = (int) $countStmt->fetchColumn();

$countStmt->execute(['status' => 'overdue']);
$totalOverdue = (int) $countStmt->fetchColumn();

$totalRows  = max($totalPending, $totalActive, $totalOverdue);
$totalPages = max(1, ceil($totalRows / $limit));    // Limit per page

// Fetch bookings
function fetchBookings(
    PDO $pdo,
    string $status,
    int $limit,
    int $offset,
    string $orderKey
): array {
    $sql = "
        SELECT id, selected_details, status
        FROM pend_booking
        WHERE status = :status
        ORDER BY
            CASE
                WHEN JSON_UNQUOTE(JSON_EXTRACT(selected_details, :jsonPath)) = '1970-01-01 01:00:00'
                THEN 1
                ELSE 0
            END,
            STR_TO_DATE(
                JSON_UNQUOTE(JSON_EXTRACT(selected_details, :jsonPath)),
                '%Y-%m-%d %H:%i:%s'
            ) ASC
        LIMIT :limit OFFSET :offset
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':status', $status);
    $stmt->bindValue(':jsonPath', '$.' . $orderKey);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// Display data in each table, in the order mentioned last
$pendings = fetchBookings($pdo2, 'pending', $limit, $offset, 'start_date');
$actives  = fetchBookings($pdo2, 'active',  $limit, $offset, 'end_date');
$overdues = fetchBookings($pdo2, 'overdue', $limit, $offset, 'end_date');


?>
</div>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/stylesform.css">
    <style>
        /* Forced styling for this page to display 3 tables side by side*/
        .tables-wrapper {
            display: flex;
            gap: 20px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        table,
        th,
        td {
            border: 1px solid #000;
        }
        th,
        td {
            padding: 6px;
            vertical-align: top;
        }
        .page_change a {
            margin: 0 5px;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container">

    <h2>Admin Panel</h2>
    <div class="navbar navbar-admin">
        <p>
            <a href="mail.php">Debug mail</a>
            <a href="upload.php">Upload</a>
            <a href="login.php">Login</a>
            <a href="list.php">All Users</a>
            <a href="carlistallview.php">Car list</a>
            <a href="updatebooking.php">View returned bookings</a>
            <a href="change_pass.php">Change password</a>
            <a href="forgot_pass.php">Forgot password</a>
            <a href="admin_logs.php">Admin Log</a>
        </p>
    </div>
    <div class="tables-wrapper">

        <!-- Pending Booking -->
        <table>
            <thead>
                <tr>
                    <th>Pending Booking</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>

            <?php if ($pendings): ?>
                <?php foreach ($pendings as $b): ?>
                    <?php $d = json_decode($b['selected_details'], true); ?>
                    <tr>
                        <td>
                            Start: <?= htmlspecialchars($d['start_date'] ?? '') ?><br>
                            End: <?= htmlspecialchars($d['end_date'] ?? '') ?><br>
                            Name: <?= htmlspecialchars($d['customer_name'] ?? '') ?>
                        </td>
                        <td>
                            <form method="get" action="bookinginfo.php">
                                <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                <button type="submit">View</button>
                            </form>
                            <?php if (!empty($d['start_date']) && strtotime($d['start_date']) < time()): ?>
                                <form method="post">
                                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                    <input type="hidden" name="new_status" value="inactive">
                                    <button type="submit">Mark Inactive</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2">No pending bookings</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

        <!-- Active Bookings -->
        <table>
            <thead>
                <tr>
                    <th>Active Booking</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($actives): ?>
                <?php foreach ($actives as $b): ?>
                    <?php $d = json_decode($b['selected_details'], true); ?>
                    <tr>
                        <td>
                            Start: <?= htmlspecialchars($d['start_date'] ?? '') ?><br>
                            End: <?= htmlspecialchars($d['end_date'] ?? '') ?><br>
                            Name: <?= htmlspecialchars($d['customer_name'] ?? '') ?>
                        </td>
                        <td>
                            <form method="get" action="bookinginfo.php">
                                <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                <button type="submit">View</button>
                            </form>
                            <?php if (!empty($d['end_date']) && strtotime($d['end_date']) < time()): ?>
                                <form method="post">
                                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                    <input type="hidden" name="new_status" value="overdue">
                                    <button type="submit">Mark Overdue</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2">No active bookings</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

        <!-- Overdue bookings -->
        <table>
            <thead>
                <tr>
                    <th>Overdue Booking</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($overdues): ?>
                <?php foreach ($overdues as $b): ?>
                    <?php $d = json_decode($b['selected_details'], true); ?>
                    <tr>
                        <td>
                            Start: <?= htmlspecialchars($d['start_date'] ?? '') ?><br>
                            End: <?= htmlspecialchars($d['end_date'] ?? '') ?><br>
                            Name: <?= htmlspecialchars($d['customer_name'] ?? '') ?>
                        </td>
                        <td>
                            <form method="get" action="bookinginfo.php">
                                <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                <button type="submit">View</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2">No overdue bookings</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Page Change -->
    <div class="page_change" style="margin-top: 20px;">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>">&laquo; Prev</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>" <?= $i === $page ? 'style="font-weight:bold;"' : '' ?>>
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
