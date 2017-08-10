<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC'] . "/checkConnect.php" );

	$userID = $_SESSION['user']->getUserInfos('id');

	if (isset($_GET['type']))
		$tmpVignetteSuffix = $_GET['type'];
	else $tmpVignetteSuffix = 'addProj';

	include('url_fcts.php');

header('Pragma: no-cache');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Content-Disposition: inline; filename="files.json"');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size');

if (!is_dir(INSTALL_PATH.'temp/uploads/vignettes/'))
	mkdir(INSTALL_PATH.'temp/uploads/vignettes/', 0755, true);


$upload_options = array(
	'upload_dir' => INSTALL_PATH.'temp/uploads/vignettes/',
	'upload_url' => getSaamURL().'/temp/uploads/vignettes/',
	'is_single_file' => true,
	'rename_single_file' => $userID.'_vignetteTemp_'.$tmpVignetteSuffix,
	'accept_file_types' => '/gif|jpg|png|jpeg/i'
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