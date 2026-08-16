<?php
declare(strict_types=1);
define('GHOST_ROOT',dirname(__DIR__));
session_start();
$config=require GHOST_ROOT.'/config/config.php';
function ie($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function dbtest($h,$p,$d,$u,$pw){$dsn="mysql:host=$h;port=$p;dbname=$d;charset=utf8mb4";return new PDO($dsn,$u,$pw,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);}
$msg='';$step=(int)($_GET['step']??1);
if($_SERVER['REQUEST_METHOD']==='POST'){
 if(($_POST['action']??'')==='verify'){
   $code=trim($_POST['purchase_code']??''); if(!preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i',$code)){$msg='Purchase code format is invalid.';}
   else { $server=$config['envato']['license_server']; $ok=false;$data=[];
     if($server){$ch=curl_init(rtrim($server,'/').'/v1/activate');curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query(['purchase_code'=>$code,'domain'=>$_SERVER['HTTP_HOST']??'','item_id'=>$config['envato']['item_id']]),CURLOPT_TIMEOUT=>20,CURLOPT_HTTPHEADER=>['User-Agent: Ghost-News-Installer/1.0']]);$raw=curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);$data=json_decode($raw,true)?:[];$ok=$status===200 && !empty($data['valid']);}
     else {$msg='License server is not configured. Set GHOST_LICENSE_SERVER in config/config.php before distribution.';}
     if($ok){$_SESSION['license']=$data;$_SESSION['purchase_code']=$code;$step=2;}elseif(!$msg)$msg=$data['message']??'Purchase verification failed.';
   }
 } elseif(($_POST['action']??'')==='install'){
   if(empty($_SESSION['license']['valid'])){$msg='Verify the purchase code first.';}else{
    try{$pdo=dbtest($_POST['db_host'],$_POST['db_port'],$_POST['db_name'],$_POST['db_user'],$_POST['db_pass']);$sql=file_get_contents(GHOST_ROOT.'/database/schema.sql');$pdo->exec($sql);
     $hash=password_hash($_POST['admin_pass'],PASSWORD_DEFAULT);$pdo->prepare("INSERT INTO admins(email,password_hash) VALUES(?,?)")->execute([$_POST['admin_email'],$hash]);
     foreach([['site_name','Ghost News & Magazine'],['tagline','A modern, SEO-first, AI-ready publishing platform.'],['meta_description','Ghost News & Magazine — modern digital publishing.'],['primary_color','#7c3aed'],['license_status','1'],['license_domain',$_SERVER['HTTP_HOST']??'']] as $x)$pdo->prepare("INSERT INTO settings(`key`,`value`) VALUES(?,?) ON DUPLICATE KEY UPDATE value=VALUES(value)")->execute($x);
     foreach([['World','world'],['Technology','technology'],['Business','business'],['Lifestyle','lifestyle'],['Entertainment','entertainment']] as $x)$pdo->prepare("INSERT INTO categories(name,slug) VALUES(?,?)")->execute($x);
     $cat=(int)$pdo->lastInsertId();$pdo->prepare("INSERT INTO posts(title,slug,content,category_id,status,published_at,created_at,updated_at) VALUES(?,?,?,?, 'published',NOW(),NOW(),NOW())")->execute(['Welcome to Ghost News & Magazine','welcome-to-ghost-news','<p>Your newsroom is ready. Create your first story from the admin panel.</p>',$cat]);
     file_put_contents(GHOST_ROOT.'/storage/.installed',date('c'));@unlink(__FILE__);$step=3;
    }catch(Throwable $e){$msg='Installation failed: '.ie($e->getMessage());}
   }
 }
}
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title>Ghost Installer</title><link rel="stylesheet" href="../assets/css/ghost.css"></head><body class="center"><main class="login" style="width:min(650px,94vw)"><h1>GHOST <span>INSTALLER</span></h1><?php if($msg):?><div class="alert"><?=ie($msg)?></div><?php endif;?>
<?php if($step===1):?><p>Step 1 — Verify your CodeCanyon purchase.</p><form method="post"><input type="hidden" name="action" value="verify"><input name="purchase_code" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" required><button>Verify Purchase & Continue</button></form>
<?php elseif($step===2):?><p>Step 2 — Database & administrator.</p><form method="post"><input type="hidden" name="action" value="install"><input name="db_host" value="localhost" placeholder="DB host" required><input name="db_port" value="3306" placeholder="DB port" required><input name="db_name" placeholder="Database name" required><input name="db_user" placeholder="Database user" required><input name="db_pass" type="password" placeholder="Database password"><input name="admin_email" type="email" placeholder="Admin email" required><input name="admin_pass" type="password" placeholder="Admin password" required><button>Install Ghost</button></form>
<?php else:?><h2>Installation complete</h2><p>Installer has been removed. Your site is ready.</p><a class="button" href="../">Open Website</a> <a class="button" href="../admin">Admin Panel</a><?php endif;?></main></body></html>
