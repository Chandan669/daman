<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_login();

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

usort($memos, function($a, $b) {
    return strcmp($b['memo_no'], $a['memo_no']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
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
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="container pb-80">
        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Total Memos</h3>
                <div class="stat-value"><?php echo $total_memos; ?></div>
            </div>
            <div class="stat-card">
                <h3>Today's Memos</h3>
                <div class="stat-value"><?php echo $today_memos; ?></div>
            </div>
            <a href="create-memo.php" class="btn btn-primary text-center w-100 mt-10" style="padding:15px; font-size:1.2rem; font-weight:bold;">+ New Memo</a>
        </div>

        <div class="card">
            <form method="GET" action="index.php" class="search-form">
                <input type="text" name="search" placeholder="Search Memo No, Name, Mobile" value="<?php echo htmlspecialchars($search_query); ?>">
                <div class="form-row" style="flex-direction:row; gap:10px;">
                    <input type="date" name="from_date" title="From Date" value="<?php echo htmlspecialchars($from_date); ?>" style="flex:1;">
                    <input type="date" name="to_date" title="To Date" value="<?php echo htmlspecialchars($to_date); ?>" style="flex:1;">
                </div>
                <button type="submit" class="btn btn-secondary w-100">Search</button>
                <?php if($search_query): ?>
                    <a href="index.php" class="btn btn-danger w-100 text-center">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card" style="padding:10px;">
            <div class="flex-row mb-10" style="justify-content:space-between; padding:0 5px;">
                <h2 style="margin:0; font-size:1.2rem;">Recent Memos</h2>
                <div>
                    <button type="button" class="btn-sm btn-secondary" onclick="toggleSelectAll()">Select All</button>
                    <button type="button" class="btn-sm btn-secondary" onclick="clearSelection()">Clear</button>
                </div>
            </div>

            <form id="bulkActionForm" method="GET" action="export.php">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                <div class="memo-list">
                    <?php if (empty($memos)): ?>
                        <div class="text-center" style="padding:20px;">No memos found.</div>
                    <?php else: ?>
                        <?php foreach ($memos as $m): ?>
                        <div class="memo-list-item" onclick="toggleCheckbox('cb-<?php echo $m['memo_no']; ?>', event)">
                            <div class="memo-checkbox">
                                <input type="checkbox" name="id[]" value="<?php echo htmlspecialchars($m['memo_no']); ?>" id="cb-<?php echo $m['memo_no']; ?>" onchange="updateSelectionCount()">
                            </div>
                            <div class="memo-details">
                                <h4><?php echo htmlspecialchars($m['memo_no']); ?></h4>
                                <p><?php echo date('d-m-Y', strtotime($m['date'])); ?> &bull; <?php echo htmlspecialchars($m['customer_name'] ?: 'No Name'); ?></p>
                                <div class="memo-actions">
                                    <a href="edit-memo.php?id=<?php echo urlencode($m['memo_no']); ?>" class="btn-sm btn-secondary" onclick="event.stopPropagation()">Edit</a>
                                    <a href="export.php?id[]=<?php echo urlencode($m['memo_no']); ?>" class="btn-sm btn-primary" onclick="event.stopPropagation()">Export / Print</a>
                                    <a href="duplicate-memo.php?id=<?php echo urlencode($m['memo_no']); ?>" class="btn-sm btn-secondary" onclick="event.stopPropagation()">Dup</a>
                                </div>
                            </div>
                            <div class="memo-price">₹<?php echo number_format($m['total'] ?? 0, 0); ?></div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Bottom Action Bar for Bulk Select -->
                <div class="bottom-action-bar" id="bottomActionBar" style="display:none;">
                    <div class="selected-count">Selected: <span id="selCount">0</span></div>
                    <button type="submit" class="btn btn-primary btn-lg">Export / Print Selected</button>
                    <button type="submit" formaction="api/delete-memo.php" formmethod="POST" class="btn btn-danger btn-lg" style="margin-left:10px;" onclick="return confirm('Are you sure you want to delete selected memos?');">Delete</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updateSelectionCount() {
            const checkboxes = document.querySelectorAll('input[name="id[]"]');
            let count = 0;
            checkboxes.forEach(cb => {
                const item = cb.closest('.memo-list-item');
                if (cb.checked) {
                    count++;
                    item.classList.add('selected');
                } else {
                    item.classList.remove('selected');
                }
            });

            const bar = document.getElementById('bottomActionBar');
            const countSpan = document.getElementById('selCount');
            countSpan.textContent = count;

            if (count > 0) {
                bar.style.display = 'flex';
                document.body.style.paddingBottom = '80px';
            } else {
                bar.style.display = 'none';
                document.body.style.paddingBottom = '0';
            }
        }

        function toggleCheckbox(id, event) {
            // Prevent toggling if clicked on a button or link
            if (event.target.tagName === 'A' || event.target.tagName === 'BUTTON' || event.target.tagName === 'INPUT') {
                return;
            }
            const cb = document.getElementById(id);
            cb.checked = !cb.checked;
            updateSelectionCount();
        }

        function toggleSelectAll() {
            const checkboxes = document.querySelectorAll('input[name="id[]"]');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            checkboxes.forEach(cb => cb.checked = !allChecked);
            updateSelectionCount();
        }

        function clearSelection() {
            const checkboxes = document.querySelectorAll('input[name="id[]"]');
            checkboxes.forEach(cb => cb.checked = false);
            updateSelectionCount();
        }
    </script>
</body>
</html>
