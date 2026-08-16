<?php
$seo = [
    'title' => e($cat['name']) . ' News - ' . app_name(),
    'description' => $cat['description'] ?: 'Latest news and updates in ' . $cat['name'],
    'canonical' => url('category/'.$cat['slug'])
];
render('public/layout', array_merge($seo, ['content' => function() use ($cat, $posts) {
?>
    <section class="category-header" style="background: #fff; padding: 3rem 0; border-bottom: 1px solid #e2e8f0; margin-bottom: 2rem; text-align: center;">
        <div class="container container-sm">
            <h1><?= e($cat['name']) ?></h1>
            <?php if($cat['description']): ?>
                <p style="color: #64748b; font-size: 1.125rem;"><?= e($cat['description']) ?></p>
            <?php endif; ?>
        </div>
    </section>

    <div class="container">
        <div class="post-grid">
            <?php foreach($posts as $p): ?>
                <article class="post-card">
                    <?php if($p['featured_image']): ?>
                        <a href="<?= e(url('article/'.$p['slug'])) ?>" class="thumb">
                            <img src="<?= e(url('storage/'. $p['featured_image'])) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
                        </a>
                    <?php endif; ?>
                    <div class="card-content">
                        <h3><a href="<?= e(url('article/'.$p['slug'])) ?>"><?= e($p['title']) ?></a></h3>
                        <p><?= e(excerpt($p['content'], 100)) ?></p>
                        <div class="meta-info" style="margin-top: auto; padding-top: 1rem; font-size: 0.875rem; color:#64748b;">
                            <span class="date"><?= date('M j, Y', strtotime($p['published_at'])) ?></span>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if(empty($posts)): ?>
            <p style="text-align:center; color:#64748b; padding: 3rem 0;">No articles found in this category yet.</p>
        <?php endif; ?>
    </div>
<?php }])); ?>
