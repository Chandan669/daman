<?php render('public/layout', ['title' => 'Home - ' . app_name(), 'content' => function() use ($posts, $featured) { ?>
    <section class="featured-section">
        <div class="container">
            <?php if(!empty($featured)): $main = array_shift($featured); ?>
                <div class="featured-grid">
                    <article class="featured-main">
                        <?php if($main['featured_image']): ?>
                            <img src="<?= e(url('storage/'. $main['featured_image'])) ?>" alt="<?= e($main['title']) ?>">
                        <?php endif; ?>
                        <div class="meta">
                            <span class="category"><?= e($main['category_name']) ?></span>
                            <h1><a href="<?= e(url('article/'.$main['slug'])) ?>"><?= e($main['title']) ?></a></h1>
                            <p><?= e(excerpt($main['content'], 120)) ?></p>
                        </div>
                    </article>
                    <div class="featured-side">
                        <?php foreach($featured as $f): ?>
                            <article class="featured-small">
                                <h3><a href="<?= e(url('article/'.$f['slug'])) ?>"><?= e($f['title']) ?></a></h3>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="latest-news">
        <div class="container">
            <h2 class="section-title">Latest News</h2>
            <div class="post-grid">
                <?php foreach($posts as $p): ?>
                    <article class="post-card">
                        <?php if($p['featured_image']): ?>
                            <a href="<?= e(url('article/'.$p['slug'])) ?>" class="thumb">
                                <img src="<?= e(url('storage/'. $p['featured_image'])) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
                            </a>
                        <?php endif; ?>
                        <div class="card-content">
                            <span class="category"><?= e($p['category_name']) ?></span>
                            <h3><a href="<?= e(url('article/'.$p['slug'])) ?>"><?= e($p['title']) ?></a></h3>
                            <div class="meta-info">
                                <span class="date"><?= date('M j, Y', strtotime($p['published_at'])) ?></span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php }]); ?>
