<?php
function e(?string $v): string { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
function url(string $path=''): string {
    $base = rtrim((string)(getenv('APP_URL') ?: ''), '/');
    return $base . '/' . ltrim($path, '/');
}
function redirect(string $path): never { header('Location: '.url($path)); exit; }
function csrf_token(): string { if(empty($_SESSION['_csrf'])) $_SESSION['_csrf']=bin2hex(random_bytes(24)); return $_SESSION['_csrf']; }
function csrf_field(): string { return '<input type="hidden" name="_csrf" value="'.e(csrf_token()).'">'; }
function csrf_check(): void { if(!hash_equals($_SESSION['_csrf'] ?? '', $_POST['_csrf'] ?? '')) { http_response_code(419); exit('Invalid CSRF token'); } }
function flash(string $key, ?string $value=null): ?string {
    if($value!==null){$_SESSION['_flash'][$key]=$value; return null;}
    $v=$_SESSION['_flash'][$key]??null; unset($_SESSION['_flash'][$key]); return $v;
}
function db(): PDO {
    static $pdo;
    if($pdo) return $pdo;
    $c=require GHOST_ROOT.'/config/database.php';
    $dsn="mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset={$c['charset']}";
    $pdo=new PDO($dsn,$c['username'],$c['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    return $pdo;
}
function auth(): bool { return !empty($_SESSION['admin_id']); }
function require_auth(): void { if(!auth()) redirect('admin/login'); }
function slugify(string $s): string { $s=trim(mb_strtolower($s)); $s=preg_replace('/[^\pL\pN]+/u','-',$s); return trim($s,'-') ?: bin2hex(random_bytes(4)); }
function setting(string $key, ?string $default=null): ?string {
    static $cache=[];
    if(array_key_exists($key,$cache)) return $cache[$key];
    $st=db()->prepare("SELECT value FROM settings WHERE `key`=? LIMIT 1"); $st->execute([$key]);
    return $cache[$key]=$st->fetchColumn() ?: $default;
}
function set_setting(string $key,string $value): void {
    db()->prepare("INSERT INTO settings(`key`,`value`) VALUES(?,?) ON DUPLICATE KEY UPDATE value=VALUES(value)")->execute([$key,$value]);
}
function excerpt(string $html,int $len=150): string { $t=trim(preg_replace('/\s+/',' ',strip_tags($html))); return mb_strimwidth($t,0,$len,'…'); }
function license_ok(): bool { return (bool) setting('license_status','0') && setting('license_domain') === ($_SERVER['HTTP_HOST'] ?? ''); }
function app_name(): string { return setting('site_name','Ghost News & Magazine'); }
function render(string $view,array $data=[]): void { extract($data); require GHOST_ROOT.'/app/Views/'.$view.'.php'; }
