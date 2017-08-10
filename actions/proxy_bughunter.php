<?php
session_start();
error_reporting(E_ALL & ~E_NOTICE);
require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
require_once('directories.php');

$BHurl = 'http://bughunter.saamanager.net/api/index.php';
//$BHurl = 'http://etoilenoire/Bughunter/api/index.php';

$postdata = $_POST;
$postdata['password']	 = "462adc7f9e51ad62f62ff914a61d6a01e0c8c484bc56321227a37924f3a56aa0";
$postdata['app_version'] = SAAM_VERSION;
$postdata['app_url']	 = "http://".$_SERVER['HTTP_HOST'];
$postdata['author']		 = $_SESSION['user']->getUserInfos(Users::USERS_PSEUDO);


$tempPath = INSTALL_PATH.'temp/uploads/bughunter/';

$files = Array();
foreach(glob($tempPath.'*.{jpg,JPG,jpeg,JPEG,png,PNG,gif,GIF}', GLOB_BRACE) as $file) {
	$fname = basename($file);
	$ftype = check_mime_type($file);
	$files[$fname] = Array("name"=>$fname, "type"=>$ftype, "content"=>base64_encode(file_get_contents($file)));
}

$postdata['files'] = json_encode($files);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $BHurl);
curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

header('Content-type: text/json');
echo curl_exec($ch);
curl_close($ch);