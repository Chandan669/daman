<?php
header('Content-Type: application/xml; charset=utf-8');
$base=rtrim(url(),'/');$posts=db()->query("SELECT slug,updated_at FROM posts WHERE status='published' ORDER BY updated_at DESC")->fetchAll();$cats=db()->query("SELECT slug FROM categories")->fetchAll();
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";
echo "<url><loc>".e($base.'/')."</loc></url>";
foreach($cats as $c) echo "<url><loc>".e($base.'/category/'.$c['slug'])."</loc></url>";
foreach($posts as $p) echo "<url><loc>".e($base.'/article/'.$p['slug'])."</loc><lastmod>".date('c',strtotime($p['updated_at']))."</lastmod></url>";
echo "</urlset>";
