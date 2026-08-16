<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_login();

$ids = $_GET['id'] ?? [];
if (!is_array($ids)) {
    $ids = [$ids];
}

if (empty($ids)) {
    die("Memo IDs required.");
}

$layout = isset($_GET['layout']) ? (int)$_GET['layout'] : get_setting('default_print_layout');
$copies = isset($_GET['copies']) ? (int)$_GET['copies'] : 1;

if (!in_array($layout, [1, 2, 3, 4, 6])) {
    $layout = 4;
}

$memos = [];
foreach ($ids as $id) {
    if (preg_match('/^[a-zA-Z0-9]+$/', $id)) {
        $file_path = MEMOS_DIR . '/' . $id . '.json';
        if (file_exists($file_path)) {
            $json = file_get_contents($file_path);
            $memo = json_decode($json, true);
            if ($memo) $memos[] = $memo;
        }
    }
}

$render_list = [];
for ($c = 0; $c < $copies; $c++) {
    $render_list = array_merge($render_list, $memos);
}

$total_items = count($render_list);
$total_pages = ceil($total_items / $layout);

$logo_url = get_logo_url();
$signature_url = get_signature_url();

$l_align = strtolower(get_setting('logo_alignment') ?: 'right');
$s_align = strtolower(get_setting('signature_alignment') ?: 'right');
$s_width = get_setting('signature_width') ?: 120;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Memos</title>
    <link rel="stylesheet" href="assets/css/print.css">
    <style>
        body { background: #f0f0f0; margin: 0; font-family: sans-serif; }
        .print-controls { background: #fff; padding: 15px; text-align: center; border-bottom: 1px solid #ccc; position: fixed; top:0; width:100%; z-index: 1000; box-shadow: 0 2px 5px rgba(0,0,0,0.1);}
        .print-container { margin-top: 80px; padding: 20px; display: flex; flex-direction: column; align-items: center; gap: 20px;}
        .page { background: white; width: 210mm; height: 297mm; box-shadow: 0 0 10px rgba(0,0,0,0.1); position: relative; box-sizing: border-box;}

        .grid-1 { display: grid; grid-template-columns: 1fr; grid-template-rows: 1fr; height: 100%; padding: 10mm; }
        .grid-2 { display: grid; grid-template-columns: 1fr; grid-template-rows: 1fr 1fr; height: 100%; border-bottom: 1px dashed #ccc;}
        .grid-3 { display: grid; grid-template-columns: 1fr; grid-template-rows: 1fr 1fr 1fr; height: 100%; border-bottom: 1px dashed #ccc;}
        .grid-4 { display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; height: 100%; }
        .grid-6 { display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr 1fr; height: 100%; }

        .memo-wrapper { border: 1px dashed #ccc; padding: 5mm; box-sizing: border-box; overflow: hidden; display: flex; flex-direction: column;}
        .memo-wrapper.no-border-top { border-top: none; }
        .memo-wrapper.no-border-left { border-left: none; }

        .memo-inner { font-size: 12px; line-height: 1.4; display:flex; flex-direction: column; height: 100%; }
        .grid-1 .memo-inner { font-size: 16px; }
        .grid-2 .memo-inner { font-size: 14px; }
        .grid-6 .memo-inner { font-size: 10px; }

        .header { margin-bottom: 5px; border-bottom: 1px solid #000; padding-bottom: 5px; }
        .header img { max-height: 40px; margin-bottom: 5px; }
        .grid-6 .header img { max-height: 30px; }
        .header h1 { margin: 0; font-size: 1.5em; }
        .header p { margin: 0; font-size: 0.8em; }

        .title { text-align: center; font-weight: bold; margin: 5px 0; border: 1px solid #000; padding: 2px; }

        .meta { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 0.9em;}
        .meta div { margin-bottom: 2px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 5px; flex-grow: 1; font-size: 0.9em;}
        th, td { border: 1px solid #000; padding: 2px 4px; text-align: left; }
        th { font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .totals { border-top: 2px solid #000; padding-top: 5px; font-size: 0.9em;}
        .totals-row { display: flex; justify-content: space-between; margin-bottom: 2px;}
        .final-total { font-weight: bold; font-size: 1.1em; border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 2px 0;}

        .footer { display: flex; justify-content: space-between; align-items: flex-end; margin-top: auto; padding-top: 10px; }
        .paid-stamp { border: 2px solid #000; padding: 5px 10px; font-weight: bold; font-size: 1.2em; transform: rotate(-5deg); display: inline-block;}
        .signature-box { text-align: center; font-size: 0.8em; }

        button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 6px; font-size: 1.1rem; font-weight: bold;}
        .btn-secondary { background: #6c757d; }

        .align-left { text-align: left; }
        .align-center { text-align: center; }
        .align-right { text-align: right; }
        .pos-left { margin-right: auto; margin-left: 0; }
        .pos-center { margin-left: auto; margin-right: auto; }
        .pos-right { margin-left: auto; margin-right: 0; }

        @media print {
            body { background: none; margin: 0; padding: 0; }
            .print-controls { display: none; }
            .print-container { margin-top: 0; padding: 0; display: block; gap: 0;}
            .page { box-shadow: none; width: 100%; height: 100vh; page-break-after: always; margin: 0;}
            .page:last-child { page-break-after: auto; }
            .memo-wrapper { border-color: #000; }
        }
    </style>
</head>
<!-- Auto trigger print dialog on load for convenience since they already selected export-->
<body onload="setTimeout(()=>window.print(), 500)">
    <div class="print-controls">
        <button type="button" onclick="window.print()">Print Now</button>
        <button type="button" class="btn-secondary" onclick="window.history.back()" style="margin-left: 15px;">Go Back</button>
    </div>

    <div class="print-container">
        <?php
        $printed = 0;
        for ($p = 0; $p < $total_pages; $p++):
        ?>
            <div class="page grid-<?php echo $layout; ?>">
                <?php for ($i = 0; $i < $layout; $i++):
                    if ($printed >= $total_items) {
                        echo '<div class="memo-wrapper" style="border:none;"></div>'; // Empty space
                        continue;
                    }
                    $memo = $render_list[$printed];
                    $printed++;
                ?>
                    <div class="memo-wrapper">
                        <div class="memo-inner">
                            <div class="header align-<?php echo $l_align; ?>">
                                <?php if ($logo_url): ?>
                                    <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="Logo">
                                <?php endif; ?>
                                <h1><?php echo htmlspecialchars(get_setting('company_name')); ?></h1>
                                <p><?php echo htmlspecialchars(get_setting('company_subtitle')); ?></p>
                            </div>

                            <div class="title">CASH MEMO / PAID</div>

                            <div class="meta">
                                <div style="flex:1;">
                                    <div><b>Memo No:</b> <?php echo htmlspecialchars($memo['memo_no']); ?></div>
                                    <div><b>Customer:</b> <?php echo htmlspecialchars($memo['customer_name'] ?? ''); ?></div>
                                    <div><b>Address:</b> <?php echo htmlspecialchars($memo['address'] ?? ''); ?></div>
                                    <div><b>Mobile:</b> <?php echo htmlspecialchars($memo['mobile_number'] ?? ''); ?></div>
                                </div>
                                <div>
                                    <div><b>Date:</b> <?php echo date('d-m-Y', strtotime($memo['date'])); ?></div>
                                    <div><b>Payment:</b> <?php echo htmlspecialchars($memo['payment_method'] ?? 'Cash'); ?></div>
                                </div>
                            </div>

                            <table>
                                <thead>
                                    <tr>
                                        <th width="5%">No.</th>
                                        <th width="55%">Item / Particulars</th>
                                        <th width="10%" class="text-right">Qty</th>
                                        <th width="15%" class="text-right">Rate</th>
                                        <th width="15%" class="text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $item_count = 0;
                                    foreach ($memo['items'] as $index => $item):
                                        $item_count++;
                                    ?>
                                        <tr>
                                            <td class="text-center"><?php echo $index + 1; ?></td>
                                            <td><?php echo htmlspecialchars($item['desc']); ?></td>
                                            <td class="text-right"><?php echo $item['qty']; ?></td>
                                            <td class="text-right"><?php echo number_format($item['rate'], 2); ?></td>
                                            <td class="text-right"><?php echo number_format($item['amount'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php
                                    $min_rows = ($layout >= 6) ? 2 : 5;
                                    while ($item_count < $min_rows) {
                                        echo '<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td></tr>';
                                        $item_count++;
                                    }
                                    ?>
                                </tbody>
                            </table>

                            <div class="totals">
                                <div class="totals-row">
                                    <span>Subtotal:</span>
                                    <span>₹<?php echo number_format($memo['subtotal'], 2); ?></span>
                                </div>
                                <?php if ($memo['discount'] > 0): ?>
                                <div class="totals-row">
                                    <span>Discount:</span>
                                    <span>- ₹<?php echo number_format($memo['discount'], 2); ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="totals-row final-total">
                                    <span>TOTAL:</span>
                                    <span>₹<?php echo number_format($memo['total'], 2); ?></span>
                                </div>
                            </div>

                            <div class="footer">
                                <div class="paid-stamp">PAID</div>
                                <div class="signature-box pos-<?php echo $s_align; ?>">
                                    <?php if ($signature_url): ?>
                                        <img src="<?php echo htmlspecialchars($signature_url); ?>" alt="Signature" style="max-width:<?php echo $s_width; ?>px;"><br>
                                    <?php else: ?>
                                        <br><br><br>
                                    <?php endif; ?>
                                    <b>Authorized Signature</b>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        <?php endfor; ?>
    </div>
</body>
</html>
