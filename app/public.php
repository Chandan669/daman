<?php
$pdo=db();
if($path==='home') {
 $posts=$pdo->query("SELECT p.*,c.name category_name FROM posts p LEFT JOIN categories c ON c.id=p.category_id WHERE p.status='published' ORDER BY p.published_at DESC LIMIT 12")->fetchAll();
 render('public/home',['posts'=>$posts]); exit;
}
if(preg_match('#^article/([a-z0-9-]+)$#',$path,$m)){
 $st=$pdo->prepare("SELECT p.*,c.name category_name FROM posts p LEFT JOIN categories c ON c.id=p.category_id WHERE p.slug=? AND p.status='published' LIMIT 1");$st->execute([$m[1]]);$post=$st->fetch();
 if(!$post){http_response_code(404);render('public/404');exit;}
 $pdo->prepare("UPDATE posts SET views=views+1 WHERE id=?")->execute([$post['id']]);
 render('public/article',['post'=>$post]); exit;
}
if(preg_match('#^category/([a-z0-9-]+)$#',$path,$m)){
 $st=$pdo->prepare("SELECT id,name FROM categories WHERE slug=?");$st->execute([$m[1]]);$cat=$st->fetch();
 if(!$cat){http_response_code(404);render('public/404');exit;}
 $st=$pdo->prepare("SELECT p.*,c.name category_name FROM posts p JOIN categories c ON c.id=p.category_id WHERE p.category_id=? AND p.status='published' ORDER BY p.published_at DESC");$st->execute([$cat['id']]);$posts=$st->fetchAll();
 render('public/category',['cat'=>$cat,'posts'=>$posts]);exit;
}
if($path==='search'){
 $q=trim($_GET['q']??'');$st=$pdo->prepare("SELECT p.*,c.name category_name FROM posts p LEFT JOIN categories c ON c.id=p.category_id WHERE p.status='published' AND (p.title LIKE ? OR p.content LIKE ?) ORDER BY p.published_at DESC LIMIT 30");$st->execute(["%$q%","%$q%"]);$posts=$st->fetchAll();
 render('public/search',['posts'=>$posts,'q'=>$q]);exit;
}
http_response_code(404);render('public/404');
