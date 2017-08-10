<?php
@session_start();
if(isset($_SESSION['INSTALL_PATH']))
	require_once ($_SESSION['INSTALL_PATH'].'inc/checkConnect.php' );
else require_once ('../inc/checkConnect.php' );

if (!$_SESSION['user']->isDev()) { die('{"error":"actions SQL Utils : ACCESS DENIED !"}'); }

extract($_POST);
$retour['error'] = "Erreur : " ;
$retour['message'] = "aucune action sélectionnée...";
$retour['type']  = 'message';
$retour['reload']  = 'sql_utils';
$retour['divID'] = 'retourAjaxSQL';

require_once(INSTALL_PATH.'fct/admins/sql_utils_fct.php');

// ACTION DUMP : enregistre la structure et le contenu de la base MySQL dans un fichier SQL
if ($action == 'dump') {
	if(($fileSaved = backup_SQL($_POST['table']))) {
		$retour['error']   = 'OK';
		$retour['message'] = "File <b>$fileSaved</b> Saved!";
	}
	else $retour['error'] = "Impossible to save DB.";
}

// ACTION RESTORE : récupère le contenu d'un fichier SQL et éxécute son contenu dans MySQL
if ($action == 'restore') {
	if (($fileLoaded = retore_SQL($_POST['fileBackup']))) {
		$retour['error']   = 'OK';
		$retour['message'] = "SQL file <b>$fileLoaded</b> restored to database!";
	}
	else $retour['error'] = "Impossible to restore file to DB.";
}

if ($action == 'setWIP') {
	try {
		setWIP($wip);
		$retour['error'] = 'OK';
		$retour['message'] = ($wip==1) ? 'mode WIP enabled' : 'mode WIP disabled';
	}
	catch(Exception $e) {
		$retour['error'] = 'Erreur : ';
		$retour['message'] = 'set WIP : '.$e->getMessage();
	}
}

echo json_encode($retour); ?>