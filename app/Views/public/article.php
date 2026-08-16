<?php
// Basic Article schema
$schema = [
    '@context' => 'https://schema.org',
    '@type' => $post['schema_type'] ?? 'NewsArticle',
    'headline' => $post['title'],
    'datePublished' => date('c', strtotime($post['published_at'])),
    'dateModified' => date('c', strtotime($post['updated_at'])),
    'author' => [
        '@type' => 'Person',
        'name' => $post['author_name'] ?? 'Editorial Team'
    ]
];
if ($post['featured_image']) {
    $schema['image'] = [url('storage/' . $post['featured_image'])];
}
$seo = [
    'title' => ($post['seo_title'] ?: $post['title']) . ' - ' . app_name(),
    'description' => $post['seo_description'] ?: excerpt($post['content'], 160),
    'canonical' => $post['canonical_url'] ?: url('article/'.$post['slug']),
    'schema' => json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
];
render('public/layout', array_merge($seo, ['content' => function() use ($post, $related) {
?>
    <main class="single-article">
        <div class="container container-sm">
            <header class="article-header">
                <div class="breadcrumbs">
                    <a href="<?= e(url()) ?>">Home</a> &raquo;
                    <a href="<?= e(url('category/'.$post['category_slug'])) ?>"><?= e($post['category_name']) ?></a>
                </div>
                <h1><?= e($post['title']) ?></h1>

                <?php if($post['summary']): ?>
                    <p class="summary"><?= e($post['summary']) ?></p>
                <?php endif; ?>

                <div class="article-meta">
                    <span class="author">By <?= e($post['author_name'] ?? 'Editorial Team') ?></span>
                    <span class="date">Published <?= date('F j, Y', strtotime($post['published_at'])) ?></span>
                    <span class="views"><?= number_format((float)$post['views']) ?> views</span>
                </div>
            </header>

            <?php if($post['featured_image']): ?>
                <figure class="featured-media">
                    <img src="<?= e(url('storage/' . $post['featured_image'])) ?>" alt="<?= e($post['title']) ?>">
                </figure>
            <?php endif; ?>

            <div class="article-content">
                <?= $post['content'] /* Content should be pre-sanitized before DB insert */ ?>
            </div>

            <?php if(!empty($related)): ?>
                <section class="related-posts">
                    <h3>Related Stories</h3>
                    <div class="post-grid" style="grid-template-columns: repeat(3, 1fr);">
                        <?php foreach($related as $r): ?>
                            <article class="post-card">
                                <h4><a href="<?= e(url('article/'.$r['slug'])) ?>"><?= e($r['title']) ?></a></h4>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </main>
<?php }])); ?>
