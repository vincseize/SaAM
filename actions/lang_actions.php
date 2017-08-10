<?php
@session_start();
require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

if (!$_SESSION['user']->isDev()) die('{"error":"error","message":"save lang : Access denied."}');

extract($_POST);
unset($_POST['action']);

$retour['error']	= 'error';
$retour['message']	= 'No action selected !';

try {

	// Traitement de l'action de modif d'une langue (peu importe le nombre de langue)
	if (@$action == 'modif') {
		$i = new Infos(TABLE_LANGS);
		$i->loadInfos('id', $id);
		unset($_POST['id']);
		foreach($_POST as $row => $val)
			$i->addInfo($row, $val);
		$i->save();
		$retour['error'] = 'OK';
		$retour['message'] = 'mise à jour effectuée.';
	}

	// Traitement de l'ajout de constante dans la base
	if ($action == 'addConst') {
		$i = new Infos(TABLE_LANGS);
		foreach($_POST as $row => $val)
			$i->addInfo($row, $val);
		$i->save();
		$retour['error'] = 'OK';
		$retour['message'] = 'Language entry (constante) "'.$constante.'" added.';
	}

	// Traitement de la suppression de constante dans la base
	if ($action == 'delConst') {
		$i = new Infos(TABLE_LANGS);
		$i->loadInfos('id', $id);
		$i->delete();
		$retour['error'] = 'OK';
		$retour['message'] = 'Language entry (constante) deleted.';
	}

	// Traitement de l'ajout d'une langue
	if ($action == 'addLang') {
		if (!Infos::addNewChamp(TABLE_LANGS, $newLang))
			throw new Exception("Adding language in database failed.");
		$retour['error']= 'OK';
		$retour['message'] = 'Language added.';
	}

	// Traitement de l'ajout d'une langue
	if ($action == 'delLang') {
		if (!Infos::removeChamp(TABLE_LANGS, $langName))
			throw new Exception("Deleting language from database failed.");
		$retour['error']= 'OK';
		$retour['message'] = 'Language deleted.';
	}
}
catch (Exception $e) {
	$retour['error'] = $e->getMessage();
}

echo json_encode($retour);