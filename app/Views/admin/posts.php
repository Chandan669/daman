<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts - <?= e(app_name()) ?> Admin</title>
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
        .badge { padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; text-transform: uppercase; }
        .badge.published { background: #d1fae5; color: #065f46; }
        .badge.draft { background: #fef3c7; color: #92400e; }
        .btn { padding: 0.5rem 1rem; background: #3b82f6; color: #fff; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background: #2563eb; }
        .editor-form { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 4px; }
        .alert { padding: 1rem; margin-bottom: 1rem; border-radius: 4px; background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
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
            <h1>Manage Posts</h1>

            <?php if ($msg = flash('ok')): ?>
                <div class="alert"><?= e($msg) ?></div>
            <?php endif; ?>

            <div class="editor-form">
                <h2>Create New Post</h2>
                <form method="post" action="<?= e(url('admin/posts')) ?>">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="slug">Slug (optional)</label>
                        <input type="text" id="slug" name="slug">
                    </div>
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select id="category_id" name="category_id">
                            <option value="">No Category</option>
                            <?php foreach ($cats as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="content">Content</label>
                        <!-- Basic textarea. In production, load TinyMCE or similar here -->
                        <textarea id="content" name="content" rows="10" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                    <button type="submit" class="btn">Save Post</button>
                </form>
            </div>

            <h2>All Posts</h2>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $p): ?>
                        <tr>
                            <td><?= e($p['title']) ?></td>
                            <td><?= e($p['category_name'] ?? 'Uncategorized') ?></td>
                            <td><span class="badge <?= e($p['status']) ?>"><?= e($p['status']) ?></span></td>
                            <td><?= (int)$p['views'] ?></td>
                            <td><?= date('Y-m-d H:i', strtotime($p['created_at'])) ?></td>
                            <td>
                                <a href="<?= e(url('article/' . $p['slug'])) ?>" target="_blank" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">View</a>
                                <!-- Edit btn would typically load the post into the form above via JS or a separate route -->
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
