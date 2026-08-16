<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_login();

$ids = $_GET['id'] ?? [];
if (!is_array($ids)) {
    $ids = [$ids];
}

if (empty($ids)) {
    header("Location: index.php");
    die();
}

$def_layout = get_setting('default_print_layout') ?: 4;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Export & Print - SWAGAT XEROX CENTER</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/lib/html2canvas.min.js"></script>
    <script src="assets/js/lib/jspdf.umd.min.js"></script>
    <style>
        .export-container { padding-bottom: 250px; }
        .render-container { position: absolute; left: -9999px; top: 0; }
        .preview-box { border: 1px solid var(--border); padding: 10px; background: #fff; min-height: 200px; display: flex; justify-content: center; align-items: center; background: #eee;}
        .preview-page { background: white; width: 100%; max-width: 300px; aspect-ratio: 1 / 1.414; box-shadow: 0 0 5px rgba(0,0,0,0.2); position: relative; display: grid;}

        .preview-page.grid-1 { grid-template-columns: 1fr; grid-template-rows: 1fr; }
        .preview-page.grid-2 { grid-template-columns: 1fr; grid-template-rows: 1fr 1fr; }
        .preview-page.grid-3 { grid-template-columns: 1fr; grid-template-rows: 1fr 1fr 1fr; }
        .preview-page.grid-4 { grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; }
        .preview-page.grid-6 { grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr 1fr; }

        .preview-cell { border: 1px dashed #ccc; display: flex; justify-content: center; align-items: center; font-size: 0.8rem; color: #666; overflow: hidden; padding: 2px;}
        .preview-cell-inner { width: 100%; height: 100%; background: #f9f9f9; text-align: center; display: flex; flex-direction: column; justify-content: center;}
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">SWAGAT XEROX CENTER</div>
        <div class="nav-links">
            <a href="index.php">Dashboard</a>
            <a href="export.php" class="active">Export</a>
        </div>
    </nav>

    <div class="container export-container">
        <div class="card text-center">
            <h3>Selected: <?php echo count($ids); ?> Memo(s)</h3>
            <p style="color:#666; font-size:0.9rem; margin:0;">
                <?php echo implode(', ', array_map('htmlspecialchars', $ids)); ?>
            </p>
        </div>

        <div class="card">
            <h3>Memos Per A4</h3>
            <div class="export-layout-selector" id="layoutSelector">
                <div class="layout-btn" data-layout="1">
                    <div class="icon-1up"></div> 1
                </div>
                <div class="layout-btn" data-layout="2">
                    <div class="icon-2up"></div> 2
                </div>
                <div class="layout-btn" data-layout="3">
                    <div class="icon-3up"></div> 3
                </div>
                <div class="layout-btn" data-layout="4">
                    <div class="icon-4up"></div> 4
                </div>
                <div class="layout-btn" data-layout="6">
                    <div class="icon-6up"></div> 6
                </div>
            </div>

            <div class="form-group">
                <label>Copies</label>
                <input type="number" id="inputCopies" value="1" min="1" max="100" inputmode="numeric">
            </div>
        </div>

        <div class="card">
            <h3>A4 Preview <span id="previewPageInfo" style="font-size:0.8rem; font-weight:normal; float:right;">Page 1 of 1</span></h3>
            <div class="preview-box">
                <div class="preview-page grid-4" id="previewPage">
                    <!-- JS preview -->
                </div>
            </div>
        </div>

        <!-- Render container for actual PDF/JPG generation (hidden) -->
        <div id="renderContainer" class="render-container"></div>

        <div class="card" style="position: fixed; bottom: 0; left: 0; right: 0; z-index: 100; box-shadow: 0 -2px 10px rgba(0,0,0,0.2); padding: 15px; border-radius: 0; margin: 0;">
            <div class="form-row mb-10">
                <button type="button" class="btn btn-primary btn-lg col-half" id="btnExportPDF">PDF</button>
                <button type="button" class="btn btn-primary btn-lg col-half" id="btnExportJPG">JPG</button>
                <button type="button" class="btn btn-secondary btn-lg col-half" id="btnPrintNative">PRINT</button>
            </div>
            <button type="button" class="btn btn-success btn-lg w-100" id="btnShare">Share File (Mobile)</button>
            <div id="loadingIndicator" style="display:none; text-align:center; padding-top:10px; font-weight:bold; color:var(--primary);">Processing... Please wait.</div>
        </div>
    </div>

    <script>
        window.exportData = {
            ids: <?php echo json_encode($ids); ?>,
            defaultLayout: <?php echo $def_layout; ?>
        };
    </script>
    <script src="assets/js/export.js"></script>
</body>
</html>
