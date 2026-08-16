<?php
require __DIR__.'/app/bootstrap.php';
$path=trim(parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH),'/');
$path=preg_replace('#^index\.php/?#','',$path);
if($path==='') $path='home';
if(str_starts_with($path,'installer')) { require __DIR__.'/installer/index.php'; exit; }
if(str_starts_with($path,'admin')) { require __DIR__.'/app/admin.php'; exit; }
if($path==='sitemap.xml') { require __DIR__.'/app/sitemap.php'; exit; }
if($path==='rss.xml') { require __DIR__.'/app/rss.php'; exit; }
if($path==='robots.txt') { header('Content-Type:text/plain'); echo "User-agent: *\nAllow: /\nSitemap: ".url('sitemap.xml'); exit; }
require __DIR__.'/app/public.php';
