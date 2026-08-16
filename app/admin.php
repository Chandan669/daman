<?php
if($path==='admin/login'){
 if($_SERVER['REQUEST_METHOD']==='POST'){csrf_check();$st=db()->prepare("SELECT * FROM admins WHERE email=? LIMIT 1");$st->execute([trim($_POST['email']??'')]);$u=$st->fetch();
  if($u && password_verify($_POST['password']??'',$u['password_hash'])){$_SESSION['admin_id']=$u['id'];redirect('admin');}
  flash('error','Invalid credentials.');
 }
 render('auth/login');exit;
}
require_auth();
if($path==='admin/logout'){session_destroy();redirect('admin/login');}
if($path==='admin'){
 $stats=['posts'=>db()->query("SELECT COUNT(*) FROM posts")->fetchColumn(),'published'=>db()->query("SELECT COUNT(*) FROM posts WHERE status='published'")->fetchColumn(),'users'=>db()->query("SELECT COUNT(*) FROM admins")->fetchColumn(),'views'=>db()->query("SELECT COALESCE(SUM(views),0) FROM posts")->fetchColumn()];
 render('admin/dashboard',['stats'=>$stats]);exit;
}
if($path==='admin/posts'){
 if($_SERVER['REQUEST_METHOD']==='POST'){csrf_check();$title=trim($_POST['title']);$slug=slugify($_POST['slug']?:$title);$content=$_POST['content']??'';$status=$_POST['status']??'draft';$cat=(int)$_POST['category_id'];$id=(int)($_POST['id']??0);
  if($id){db()->prepare("UPDATE posts SET title=?,slug=?,content=?,category_id=?,status=?,published_at=CASE WHEN ?='published' AND published_at IS NULL THEN NOW() ELSE published_at END,updated_at=NOW() WHERE id=?")->execute([$title,$slug,$content,$cat,$status,$status,$id]);}
  else {db()->prepare("INSERT INTO posts(title,slug,content,category_id,status,published_at,created_at,updated_at) VALUES(?,?,?,?,?,CASE WHEN ?='published' THEN NOW() END,NOW(),NOW())")->execute([$title,$slug,$content,$cat,$status,$status]);}
  redirect('admin/posts');
 }
 $posts=db()->query("SELECT p.*,c.name category_name FROM posts p LEFT JOIN categories c ON c.id=p.category_id ORDER BY p.created_at DESC")->fetchAll();$cats=db()->query("SELECT * FROM categories ORDER BY name")->fetchAll();
 render('admin/posts',['posts'=>$posts,'cats'=>$cats]);exit;
}
if($path==='admin/categories'){
 if($_SERVER['REQUEST_METHOD']==='POST'){csrf_check();$name=trim($_POST['name']);db()->prepare("INSERT INTO categories(name,slug) VALUES(?,?)")->execute([$name,slugify($name)]);redirect('admin/categories');}
 $cats=db()->query("SELECT * FROM categories ORDER BY name")->fetchAll();render('admin/categories',['cats'=>$cats]);exit;
}
if($path==='admin/settings'){
 if($_SERVER['REQUEST_METHOD']==='POST'){csrf_check();foreach(['site_name','tagline','primary_color','meta_description','ai_provider','ai_api_key'] as $k) set_setting($k,trim($_POST[$k]??''));flash('ok','Settings saved.');}
 render('admin/settings');exit;
}
http_response_code(404);echo 'Not found';
