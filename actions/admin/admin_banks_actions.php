<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	if (!($_SESSION['user']->isSupervisor() || $_SESSION['user']->isDemo())) die('{"error":"error", "message":"actions bank : Access denied."}');

	require_once('directories.php');

extract($_POST);

$retour['error'] = 'error';
$retour['message'] = 'action undefined';

if ($action == 'deleteBankRef') {
	if (empty($directory) || empty($filename)) {
		$retour['message'] = 'missing some infos !';
		die(json_encode($retour));
	}
	$img = INSTALL_PATH.$directory.'/'.$filename;
	$img_thumb = INSTALL_PATH.$directory.'/thumbs/thumb_'.$filename;
	$img_vthumb = INSTALL_PATH.$directory.'/thumbs/vthumb_'.$filename.'.gif';
	if(file_exists($img))
		unlink($img);
	if (file_exists($img_thumb))
		unlink($img_thumb);
	if (file_exists($img_vthumb))
		unlink($img_vthumb);
	$retour['error'] = 'OK';
	$retour['message'] = $filename.' deleted.';
	$retour['folder'] = basename($directory);
	$retour['img'] = $idThumb;
}


// Ajout de sous dossier bank
if ($action == 'addbankFolder') {
	$newFolder = INSTALL_PATH.$destDir.'/'.$newFolderName;
	if (is_dir($newFolder))
		$retour['message'] = "This folder already exists.";
	else {
		if (mkdir($newFolder)) {
			mkdir($newFolder.'/thumbs');
			$retour['error'] = 'OK';
			$retour['message'] = "Folder <b>$newFolderName</b> added.";
		}
		else
			$retour['message'] = "Unable to create this folder.";
	}
}

// Suppression de dossier bank
if ($action == 'deleteBankFolder') {
	$delFolder = INSTALL_PATH.$destDir.'/'.$folderName;
	if (!is_dir($delFolder))
		$retour['message'] = "This folder doesn't exists, or it's a file.";
	else {
		if (rmDir_R($delFolder)) {
			$retour['error']   = 'OK';
			$retour['message'] = "Folder <b>$folderName</b> deleted.";
		}
		else
			$retour['message'] = "Unable to delete this folder.";
	}
}

// Rename de dossier bank
if ($action == 'renameBankFolder') {
	$folderOldName = INSTALL_PATH.$destDir.'/'.$folderName;
	$folderNewName = INSTALL_PATH.$destDir.'/'.$newName;
	if (file_exists($folderNewName))
		$retour['message'] = "This folder already exists.";
	else {
		if (rename($folderOldName, $folderNewName)) {
			$retour['error'] = 'OK';
			$retour['message'] = "Folder <b>$newName</b> renamed.";
		}
		else
			$retour['message'] = "Unable to rename this folder.";
	}
}

echo json_encode($retour); ?>