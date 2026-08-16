<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= e(app_name()) ?> Admin</title>
    <link rel="stylesheet" href="<?= e(url('assets/css/ghost.css')) ?>">
    <style>
        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem; }
        .stat-card { background: var(--bg-card, #fff); padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid var(--border-color, #e2e8f0); }
        .stat-card h3 { margin: 0 0 0.5rem 0; font-size: 0.875rem; color: var(--text-muted, #64748b); text-transform: uppercase; letter-spacing: 0.05em; }
        .stat-card .value { font-size: 2rem; font-weight: bold; margin: 0; color: var(--primary-color, #0f172a); }
        .admin-layout { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: #0f172a; color: #fff; padding: 1rem; }
        .sidebar a { color: #cbd5e1; text-decoration: none; display: block; padding: 0.5rem; margin-bottom: 0.25rem; border-radius: 4px; }
        .sidebar a:hover { background: #1e293b; color: #fff; }
        .main-content { flex: 1; padding: 2rem; background: #f8fafc; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar">
            <h2 style="color:#fff; margin-top:0;">Ghost Admin</h2>
            <nav>
                <a href="<?= e(url('admin')) ?>">Dashboard</a>
                <a href="<?= e(url('admin/posts')) ?>">Posts</a>
                <a href="<?= e(url('admin/categories')) ?>">Categories</a>
                <a href="<?= e(url('admin/settings')) ?>">Settings</a>
                <a href="<?= e(url('admin/logout')) ?>">Logout</a>
            </nav>
        </aside>
        <main class="main-content">
            <h1>Dashboard</h1>
            <div class="dashboard-grid">
                <div class="stat-card">
                    <h3>Total Posts</h3>
                    <p class="value"><?= number_format((float)$stats['posts']) ?></p>
                </div>
                <div class="stat-card">
                    <h3>Published</h3>
                    <p class="value"><?= number_format((float)$stats['published']) ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <p class="value"><?= number_format((float)$stats['users']) ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Views</h3>
                    <p class="value"><?= number_format((float)$stats['views']) ?></p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
