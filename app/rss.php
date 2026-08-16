<?php
header('Content-Type: application/rss+xml; charset=utf-8');$posts=db()->query("SELECT p.*,c.name category_name FROM posts p LEFT JOIN categories c ON c.id=p.category_id WHERE p.status='published' ORDER BY p.published_at DESC LIMIT 30")->fetchAll();
echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<rss version="2.0"><channel>
<title><?=e(app_name())?></title><link><?=e(url())?></link><description><?=e(setting('tagline','Independent digital news & magazine platform'))?></description>
<?php foreach($posts as $p): ?><item><title><?=e($p['title'])?></title><link><?=e(url('article/'.$p['slug']))?></link><guid><?=e(url('article/'.$p['slug']))?></guid><pubDate><?=date(DATE_RSS,strtotime($p['published_at']))?></pubDate><description><?=e(excerpt($p['content'],300))?></description></item><?php endforeach; ?>
</channel></rss>
