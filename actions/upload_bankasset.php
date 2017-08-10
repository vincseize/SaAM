<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	if (!isset($_GET['path']))
		die('{"error":"path undefined !"}');

	$dirAsset = urldecode($_GET['path']);
	$IDasset  = $_GET['IDasset'];

	try {
		$ACL = new ACL($_SESSION['user']);
		if (!$ACL->check('ASSETS_UPLOAD', 'asset:'.$IDasset))
			die('[{"error":"Access denied."}]');
	}
	catch(Exception $e) { die('[{"error":"'.$e->getMessage().'"}]'); }

	include('url_fcts.php');

header('Pragma: no-cache');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Content-Disposition: inline; filename="files.json"');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size');


$upload_options = array(
	'upload_dir' => INSTALL_PATH.$dirAsset,
	'upload_url' => getSaamURL().'/'.$dirAsset,
	'is_single_file' => false,
	'rename_single_file' => false,
	'orient_image' => true,
	'accept_file_types' => '/'.BANK_EXTENSIONS_ALLOWED.'/i'
);

$upload_handler = new UploadHandler($upload_options);

switch ($_SERVER['REQUEST_METHOD']) {
	case 'OPTIONS':
		break;
	case 'HEAD':
	case 'GET':
		$upload_handler->get();
		break;
	case 'POST':
		if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
			$upload_handler->delete();
		} else {
			$upload_handler->post();
		}
		break;
	case 'DELETE':
		$upload_handler->delete();
		break;
	default:
		header('HTTP/1.1 405 Method Not Allowed');
}

?>
