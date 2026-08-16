<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - <?= e(app_name()) ?> Admin</title>
    <link rel="stylesheet" href="<?= e(url('assets/css/ghost.css')) ?>">
    <style>
        .admin-layout { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: #0f172a; color: #fff; padding: 1rem; }
        .sidebar a { color: #cbd5e1; text-decoration: none; display: block; padding: 0.5rem; margin-bottom: 0.25rem; border-radius: 4px; }
        .sidebar a:hover { background: #1e293b; color: #fff; }
        .main-content { flex: 1; padding: 2rem; background: #f8fafc; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f1f5f9; font-weight: bold; }
        .btn { padding: 0.5rem 1rem; background: #3b82f6; color: #fff; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background: #2563eb; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        .form-group input { width: max-content; min-width: 300px; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 4px; }
        .alert { padding: 1rem; margin-bottom: 1rem; border-radius: 4px; background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .creation-form { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem; }
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
            <h1>Manage Categories</h1>

            <?php if ($msg = flash('ok')): ?>
                <div class="alert"><?= e($msg) ?></div>
            <?php endif; ?>

            <div class="creation-form">
                <h2>Create New Category</h2>
                <form method="post" action="<?= e(url('admin/categories')) ?>">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label for="name">Category Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <button type="submit" class="btn">Add Category</button>
                </form>
            </div>

            <h2>All Categories</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Slug</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cats as $c): ?>
                        <tr>
                            <td><?= (int)$c['id'] ?></td>
                            <td><?= e($c['name']) ?></td>
                            <td><?= e($c['slug']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
