<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
if(($_SERVER['REQUEST_METHOD']??'GET')!=='POST'){http_response_code(405);echo json_encode(['valid'=>false,'message'=>'POST required']);exit;}
$code=trim($_POST['purchase_code']??'');$domain=strtolower(trim($_POST['domain']??''));$item=(int)($_POST['item_id']??0);
if(!preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i',$code)||$domain===''){http_response_code(422);echo json_encode(['valid'=>false,'message'=>'Invalid input']);exit;}
$expected=(int)getenv('GHOST_PRODUCT_ITEM_ID');$token=(string)getenv('GHOST_ENVATO_TOKEN');
if(!$token||!$expected){http_response_code(503);echo json_encode(['valid'=>false,'message'=>'License service is not configured']);exit;}
$ch=curl_init('https://api.envato.com/v3/market/author/sale?code='.rawurlencode($code));
curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>20,CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$token,'User-Agent: Ghost News purchase verification']]);
$raw=curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);$sale=json_decode($raw,true)?:[];
if($status!==200){http_response_code(403);echo json_encode(['valid'=>false,'message'=>'Purchase code could not be verified']);exit;}
if((int)($sale['item']['id']??0)!==$expected || ($item && $item!==$expected)){http_response_code(403);echo json_encode(['valid'=>false,'message'=>'Purchase code is not for this product']);exit;}
/* Replace the following in production with your license DB binding:
   one purchase code -> one licensed end product/domain. */
$dir=__DIR__.'/data';if(!is_dir($dir))mkdir($dir,0750,true);$file=$dir.'/licenses.json';$db=is_file($file)?(json_decode(file_get_contents($file),true)?:[]):[];
if(isset($db[$code]) && $db[$code]['domain']!==$domain){http_response_code(409);echo json_encode(['valid'=>false,'message'=>'This purchase code is already activated on another domain']);exit;}
$db[$code]=['domain'=>$domain,'buyer'=>$sale['buyer']??null,'item_id'=>$expected,'activated_at'=>$db[$code]['activated_at']??date('c')];file_put_contents($file,json_encode($db,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),LOCK_EX);
echo json_encode(['valid'=>true,'message'=>'Purchase verified','buyer'=>$sale['buyer']??null,'license'=>$sale['license']??null]);
