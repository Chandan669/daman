<?php
require_once 'includes/config.php';

$logo_url = get_logo_url();
$layout = isset($_GET['layout']) ? (int)$_GET['layout'] : get_setting('default_print_layout');
$copies = isset($_GET['copies']) ? (int)$_GET['copies'] : $layout;

if (!in_array($layout, [1, 2, 4, 6, 9])) {
    $layout = 4;
}

$total_pages = ceil($copies / $layout);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Blank Memos</title>
    <link rel="stylesheet" href="assets/css/print.css">
    <style>
        /* Re-use exact same print styles as print.php */
        body { background: #f0f0f0; margin: 0; font-family: sans-serif; }
        .print-controls { background: #fff; padding: 20px; text-align: center; border-bottom: 1px solid #ccc; position: fixed; top:0; width:100%; z-index: 1000; box-shadow: 0 2px 5px rgba(0,0,0,0.1);}
        .print-container { margin-top: 100px; padding: 20px; display: flex; flex-direction: column; align-items: center; gap: 20px;}
        .page { background: white; width: 210mm; height: 297mm; box-shadow: 0 0 10px rgba(0,0,0,0.1); position: relative; box-sizing: border-box;}

        .grid-1 { display: grid; grid-template-columns: 1fr; grid-template-rows: 1fr; height: 100%; padding: 10mm; }
        .grid-2 { display: grid; grid-template-columns: 1fr; grid-template-rows: 1fr 1fr; height: 100%; border-bottom: 1px dashed #ccc;}
        .grid-4 { display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; height: 100%; }
        .grid-6 { display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr 1fr; height: 100%; }
        .grid-9 { display: grid; grid-template-columns: 1fr 1fr 1fr; grid-template-rows: 1fr 1fr 1fr; height: 100%; }

        .memo-wrapper { border: 1px dashed #ccc; padding: 5mm; box-sizing: border-box; overflow: hidden; display: flex; flex-direction: column;}

        .memo-inner { font-size: 12px; line-height: 1.4; display:flex; flex-direction: column; height: 100%; }
        .grid-1 .memo-inner { font-size: 16px; }
        .grid-2 .memo-inner { font-size: 14px; }
        .grid-6 .memo-inner, .grid-9 .memo-inner { font-size: 10px; }
        .grid-9 .memo-inner { font-size: 9px; line-height: 1.2; }

        .header { text-align: center; margin-bottom: 5px; border-bottom: 1px solid #000; padding-bottom: 5px; }
        .header img { max-height: 40px; margin-bottom: 5px; }
        .grid-6 .header img, .grid-9 .header img { max-height: 30px; }
        .header h1 { margin: 0; font-size: 1.5em; }
        .header p { margin: 0; font-size: 0.8em; }

        .title { text-align: center; font-weight: bold; margin: 5px 0; border: 1px solid #000; padding: 2px; }

        .meta { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 0.9em; line-height: 2;}
        .line { border-bottom: 1px dotted #666; display: inline-block; min-width: 100px; margin-left: 5px;}

        table { width: 100%; border-collapse: collapse; margin-bottom: 5px; flex-grow: 1; font-size: 0.9em;}
        th, td { border: 1px solid #000; padding: 2px 4px; text-align: left; }
        th { font-weight: bold; }

        .totals { border-top: 2px solid #000; padding-top: 5px; font-size: 0.9em; line-height: 2;}
        .totals-row { display: flex; justify-content: flex-end; margin-bottom: 2px;}
        .totals-row span:first-child { margin-right: 10px; }

        .footer { display: flex; justify-content: space-between; align-items: flex-end; margin-top: auto; padding-top: 10px; }
        .signature-area { text-align: center; font-size: 0.8em; margin-left: auto; }

        button { padding: 8px 16px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 4px; font-size: 16px;}
        select, input { padding: 6px; margin: 0 5px;}

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
<body>
    <div class="print-controls">
        <form method="GET">
            <label>Blank Layout (Memos per Page):</label>
            <select name="layout" onchange="this.form.submit()">
                <option value="1" <?php echo $layout == 1 ? 'selected' : ''; ?>>1</option>
                <option value="2" <?php echo $layout == 2 ? 'selected' : ''; ?>>2</option>
                <option value="4" <?php echo $layout == 4 ? 'selected' : ''; ?>>4</option>
                <option value="6" <?php echo $layout == 6 ? 'selected' : ''; ?>>6</option>
                <option value="9" <?php echo $layout == 9 ? 'selected' : ''; ?>>9</option>
            </select>

            <label>Total Copies:</label>
            <input type="number" name="copies" value="<?php echo $copies; ?>" min="1" max="100" style="width:60px;">

            <button type="submit" style="background:#6c757d; margin-right: 15px;">Update</button>
            <button type="button" onclick="window.print()">Print Blank Memos</button>
            <button type="button" onclick="window.location.href='index.php'" style="background:#6c757d; margin-left: 15px;">Back</button>
        </form>
    </div>

    <div class="print-container">
        <?php
        $printed = 0;
        for ($p = 0; $p < $total_pages; $p++):
        ?>
            <div class="page grid-<?php echo $layout; ?>">
                <?php for ($i = 0; $i < $layout; $i++):
                    if ($printed >= $copies) {
                        echo '<div class="memo-wrapper" style="border:none;"></div>';
                        continue;
                    }
                    $printed++;
                ?>
                    <div class="memo-wrapper">
                        <div class="memo-inner">
                            <div class="header">
                                <?php if ($logo_url): ?>
                                    <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="Logo">
                                <?php endif; ?>
                                <h1><?php echo htmlspecialchars(get_setting('company_name')); ?></h1>
                                <p><?php echo htmlspecialchars(get_setting('company_subtitle')); ?></p>
                            </div>

                            <div class="title">CASH MEMO / PAID</div>

                            <div class="meta">
                                <div style="flex:1;">
                                    <div><b>Memo No:</b> <span class="line" style="width:80px;"></span></div>
                                    <div><b>Customer:</b> <span class="line" style="width:70%;"></span></div>
                                    <div><b>Address:</b> <span class="line" style="width:70%;"></span></div>
                                </div>
                                <div>
                                    <div><b>Date:</b> <span class="line" style="width:80px;"></span></div>
                                    <div><b>Mobile:</b> <span class="line" style="width:100px;"></span></div>
                                </div>
                            </div>

                            <table>
                                <thead>
                                    <tr>
                                        <th width="5%">No.</th>
                                        <th width="55%">Item / Particulars</th>
                                        <th width="10%">Qty</th>
                                        <th width="15%">Rate</th>
                                        <th width="15%">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $num_rows = ($layout >= 6) ? 4 : 8;
                                    for ($r = 0; $r < $num_rows; $r++):
                                    ?>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    <?php endfor; ?>
                                </tbody>
                            </table>

                            <div class="totals">
                                <div class="totals-row">
                                    <span>Subtotal:</span>
                                    <span class="line" style="width:100px;"></span>
                                </div>
                                <div class="totals-row">
                                    <span>Discount:</span>
                                    <span class="line" style="width:100px;"></span>
                                </div>
                                <div class="totals-row">
                                    <span><b>TOTAL:</b></span>
                                    <span class="line" style="width:100px;"></span>
                                </div>
                            </div>

                            <div class="footer">
                                <div class="signature-area">
                                    <br><br><br>
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
