<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_login(); // Commented out for easier testing initially

$memos = [];
$total_memos = 0;
$today_memos = 0;
$today_date = date('Y-m-d');

$search_query = $_GET['search'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

foreach (glob(MEMOS_DIR . '/*.json') as $file) {
    $total_memos++;
    $json = file_get_contents($file);
    $memo = json_decode($json, true);
    if ($memo) {
        if (isset($memo['date']) && $memo['date'] === $today_date) {
            $today_memos++;
        }

        $match = true;

        if ($search_query) {
            $q = strtolower($search_query);
            $match = (
                strpos(strtolower($memo['memo_no']), $q) !== false ||
                strpos(strtolower($memo['customer_name'] ?? ''), $q) !== false ||
                strpos(strtolower($memo['mobile_number'] ?? ''), $q) !== false
            );
        }

        if ($match && $from_date && isset($memo['date']) && $memo['date'] < $from_date) {
            $match = false;
        }

        if ($match && $to_date && isset($memo['date']) && $memo['date'] > $to_date) {
            $match = false;
        }

        if ($match) {
            $memos[] = $memo;
        }
    }
}

// Sort by memo_no descending (newest first)
usort($memos, function($a, $b) {
    return strcmp($b['memo_no'], $a['memo_no']);
});

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SWAGAT XEROX CENTER</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">SWAGAT XEROX CENTER</div>
        <div class="nav-links">
            <a href="index.php" class="active">Dashboard</a>
            <a href="create-memo.php">New Memo</a>
            <a href="print-blank.php">Blank Memo</a>
            <a href="settings.php">Settings</a>
            <?php if (is_logged_in()): ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Total Memos</h3>
                <div class="stat-value"><?php echo $total_memos; ?></div>
            </div>
            <div class="stat-card">
                <h3>Today's Memos</h3>
                <div class="stat-value"><?php echo $today_memos; ?></div>
            </div>
            <div class="stat-card">
                <h3>Quick Action</h3>
                <a href="create-memo.php" class="btn btn-primary" style="margin-top:10px; display:inline-block;">+ Create New Memo</a>
            </div>
        </div>

        <div class="search-section card">
            <form method="GET" action="index.php" class="search-form">
                <input type="text" name="search" placeholder="Search by Memo No, Name, Mobile" value="<?php echo htmlspecialchars($search_query); ?>">
                <input type="date" name="from_date" title="From Date" value="<?php echo htmlspecialchars($from_date); ?>">
                <input type="date" name="to_date" title="To Date" value="<?php echo htmlspecialchars($to_date); ?>">
                <button type="submit" class="btn btn-secondary">Search</button>
                <a href="index.php" class="btn">Clear</a>
            </form>
        </div>

        <div class="memos-list card">
            <h2>Recent Memos</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Memo No.</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Total (₹)</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($memos)): ?>
                        <tr><td colspan="6" style="text-align:center;">No memos found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($memos as $m): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($m['memo_no']); ?></td>
                                <td><?php echo htmlspecialchars($m['date'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($m['customer_name'] ?? ''); ?></td>
                                <td>₹<?php echo number_format($m['total'] ?? 0, 2); ?></td>
                                <td><span class="badge paid">PAID</span></td>
                                <td class="actions">
                                    <a href="edit-memo.php?id=<?php echo urlencode($m['memo_no']); ?>" class="btn-sm btn-secondary">Edit</a>
                                    <a href="print.php?id=<?php echo urlencode($m['memo_no']); ?>" class="btn-sm btn-primary">Print</a>
                                    <a href="duplicate-memo.php?id=<?php echo urlencode($m['memo_no']); ?>" class="btn-sm btn-secondary">Duplicate</a>
                                    <form method="POST" action="api/delete-memo.php" onsubmit="return confirm('Are you sure you want to delete this memo?');" style="display:inline;">
                                        <input type="hidden" name="memo_no" value="<?php echo htmlspecialchars($m['memo_no']); ?>">
                                        <button type="submit" class="btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
