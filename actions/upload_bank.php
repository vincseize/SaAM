<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	if (!isset($_GET['folderTempProj']))
		die('{"error":"error","message":"folderTempProj undefined !"}');

	$dirProj = urldecode($_GET['folderTempProj']);

	include('url_fcts.php');
	require_once('directories.php');

header('Pragma: no-cache');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Content-Disposition: inline; filename="files.json"');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size');


$upload_options = array(
	'upload_dir' => INSTALL_PATH.'temp/uploads/banks/'.$dirProj.'/',
	'upload_url' => getSaamURL().'/temp/uploads/banks/'.$dirProj.'/',
	'is_single_file' => false,
	'rename_single_file' => false,
	'orient_image' => true,
	'accept_file_types' => '/'.BANK_EXTENSIONS_ALLOWED.'/i'
);

if (!is_dir(INSTALL_PATH.'temp/uploads/banks/'.$dirProj))
	makeTempDir('uploads/banks/'.$dirProj);

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
