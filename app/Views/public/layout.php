<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? app_name()) ?></title>
    <meta name="description" content="<?= e($description ?? setting('meta_description')) ?>">
    <?php if(isset($canonical)): ?>
        <link rel="canonical" href="<?= e($canonical) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= e(url('assets/css/ghost.css')) ?>">
    <link rel="manifest" href="<?= e(url('manifest.webmanifest')) ?>">
    <meta name="theme-color" content="<?= e(setting('primary_color', '#7c3aed')) ?>">
    <style>
        :root { --primary-color: <?= e(setting('primary_color', '#7c3aed')) ?>; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; margin: 0; padding: 0; line-height: 1.6; color: #333; background: #f8fafc; }
        a { color: var(--primary-color); text-decoration: none; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 1rem; }
        .container-sm { max-width: 800px; }
        .site-header { background: #fff; border-bottom: 1px solid #e2e8f0; padding: 1rem 0; }
        .site-header .container { display: flex; justify-content: space-between; align-items: center; }
        .brand { font-size: 1.5rem; font-weight: bold; font-family: serif; letter-spacing: -0.05em; color: #000; }
        .nav-menu { display: flex; gap: 1rem; }
        .nav-menu a { color: #475569; font-weight: 500; }
        .site-footer { background: #0f172a; color: #cbd5e1; padding: 3rem 0; margin-top: 4rem; }

        /* Grid */
        .post-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem; }
        .post-card { background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; flex-direction: column;}
        .post-card img { width: 100%; height: 200px; object-fit: cover; }
        .card-content { padding: 1.5rem; flex: 1; display: flex; flex-direction: column;}
        .card-content h3 { margin: 0 0 1rem 0; font-size: 1.25rem; line-height: 1.3;}
        .category { font-size: 0.75rem; text-transform: uppercase; color: var(--primary-color); font-weight: bold; margin-bottom: 0.5rem; display: block;}

        /* Article */
        .single-article { margin-top: 2rem; background: #fff; padding: 3rem 0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .article-header { text-align: center; margin-bottom: 2rem; }
        .article-header h1 { font-size: 2.5rem; margin-bottom: 1rem; line-height: 1.2;}
        .article-meta { color: #64748b; font-size: 0.875rem; display: flex; justify-content: center; gap: 1rem;}
        .featured-media img { width: 100%; height: auto; border-radius: 8px; margin-bottom: 2rem;}
        .article-content { font-size: 1.125rem; line-height: 1.8; }
        .article-content p { margin-bottom: 1.5rem; }

        /* Featured Layout */
        .featured-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-bottom: 3rem;}
        @media (max-width: 768px) { .featured-grid { grid-template-columns: 1fr; } }
        .featured-main { background: #fff; border-radius: 8px; overflow: hidden; position: relative;}
        .featured-main img { width: 100%; height: 400px; object-fit: cover; }
        .featured-main .meta { padding: 2rem; }
        .featured-side { display: flex; flex-direction: column; gap: 1rem; }
        .featured-small { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);}
    </style>
    <?php if(isset($schema)): ?>
        <script type="application/ld+json"><?= $schema ?></script>
    <?php endif; ?>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <a href="<?= e(url()) ?>" class="brand"><?= e(app_name()) ?></a>
            <nav class="nav-menu">
                <a href="<?= e(url()) ?>">Home</a>
                <!-- Dynamic categories would go here -->
                <form action="<?= e(url('search')) ?>" method="get">
                    <input type="search" name="q" placeholder="Search..." style="padding: 0.25rem; border-radius:4px; border:1px solid #ccc;">
                </form>
            </nav>
        </div>
    </header>

    <?php $content(); ?>

    <footer class="site-footer">
        <div class="container">
            <div style="display: flex; justify-content: space-between;">
                <div>
                    <h3 style="color:#fff; margin-top:0;"><?= e(app_name()) ?></h3>
                    <p><?= e(setting('tagline')) ?></p>
                </div>
                <div>
                    <h4>Newsletter</h4>
                    <form action="<?= e(url('newsletter/subscribe')) ?>" method="post">
                        <?= csrf_field() ?>
                        <input type="email" name="email" placeholder="Your email" required style="padding: 0.5rem; border:none; border-radius:4px;">
                        <button style="padding: 0.5rem 1rem; background:var(--primary-color); color:#fff; border:none; border-radius:4px;">Subscribe</button>
                    </form>
                </div>
            </div>
            <div style="margin-top: 2rem; border-top: 1px solid #334155; padding-top: 1rem; text-align: center; font-size: 0.875rem;">
                &copy; <?= date('Y') ?> <?= e(app_name()) ?>. All rights reserved.
            </div>
        </div>
    </footer>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?= e(url('sw.js')) ?>');
            });
        }
    </script>
</body>
</html>
