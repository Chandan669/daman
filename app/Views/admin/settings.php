<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - <?= e(app_name()) ?> Admin</title>
    <link rel="stylesheet" href="<?= e(url('assets/css/ghost.css')) ?>">
    <style>
        .admin-layout { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: #0f172a; color: #fff; padding: 1rem; }
        .sidebar a { color: #cbd5e1; text-decoration: none; display: block; padding: 0.5rem; margin-bottom: 0.25rem; border-radius: 4px; }
        .sidebar a:hover { background: #1e293b; color: #fff; }
        .main-content { flex: 1; padding: 2rem; background: #f8fafc; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 4px; }
        .btn { padding: 0.5rem 1rem; background: #3b82f6; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #2563eb; }
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
            <h1>Settings</h1>
            <?php if ($msg = flash('ok')): ?>
                <div class="alert"><?= e($msg) ?></div>
            <?php endif; ?>
            <form method="post" action="<?= e(url('admin/settings')) ?>">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="site_name">Site Name</label>
                    <input type="text" id="site_name" name="site_name" value="<?= e(setting('site_name')) ?>" required>
                </div>

                <div class="form-group">
                    <label for="tagline">Tagline</label>
                    <input type="text" id="tagline" name="tagline" value="<?= e(setting('tagline')) ?>">
                </div>

                <div class="form-group">
                    <label for="meta_description">SEO Meta Description</label>
                    <textarea id="meta_description" name="meta_description" rows="3"><?= e(setting('meta_description')) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="primary_color">Primary Brand Color</label>
                    <input type="color" id="primary_color" name="primary_color" value="<?= e(setting('primary_color', '#7c3aed')) ?>" style="width: 100px; padding: 0; height: 40px;">
                </div>

                <h3>AI Integration</h3>
                <div class="form-group">
                    <label for="ai_provider">AI Provider</label>
                    <select id="ai_provider" name="ai_provider">
                        <option value="">None</option>
                        <option value="openai" <?= setting('ai_provider') === 'openai' ? 'selected' : '' ?>>OpenAI</option>
                        <option value="gemini" <?= setting('ai_provider') === 'gemini' ? 'selected' : '' ?>>Google Gemini</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="ai_api_key">AI API Key</label>
                    <input type="password" id="ai_api_key" name="ai_api_key" value="<?= e(setting('ai_api_key')) ?>">
                </div>

                <button type="submit" class="btn">Save Settings</button>
            </form>
        </main>
    </div>
</body>
</html>
