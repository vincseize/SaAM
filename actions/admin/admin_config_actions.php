<?php
@session_start(); // 2 lignes à placer toujours en haut du code des pages
require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
if (!$_SESSION['user']->isSupervisor()) die('{"error":"error", "message":"admin actions : Access denied."}');

include('directories.php');
include('admin_projects_fcts.php');

extract($_POST);

$retour['error'] = 'error';
$retour['message'] = 'action undefined';

try {
// Ajout de groupe d'ACL
	if ($action == 'ACL_addGrp') {
		unset($_POST['action']);
		$i = new Infos(TABLE_ACL);
		foreach($_POST as $key => $val)
			$i->addInfo ($key, $val);
		$i->save();
		$retour['error'] = 'OK';
		$retour['message'] = 'ACL group added.';
	}

// Ajout de groupe d'ACL
	if ($action == 'ACL_modGrp') {
		unset($_POST['action']);
		$i = new Infos(TABLE_ACL);
		$i->loadInfos('id', $group);
		unset($_POST['group']);
		foreach($_POST as $key => $val)
			$i->addInfo ($key, $val);
		$i->save('id', 'this', false);
		$retour['error'] = 'OK';
		$retour['message'] = 'ACL group modified.';
}

// Ajout de nouveau departement
	if ($action == 'addNewDept') {
		$d = new Infos(TABLE_DEPTS);
		$d->addInfo('type', $type);
		$d->addInfo('label', $labelDept);
		$d->addInfo('template_name', $templateDept);
		$etapes = json_encode(explode(', ', urldecode($etapesDept)));
		$d->addInfo('etapes', $etapes);
		$max = Liste::getMax(TABLE_DEPTS, 'position');
		$d->addInfo('position', $max+1);
		$d->save();
		$retour['error'] = 'OK';
		$retour['message'] = 'Departement "'.strtoupper($labelDept).'" added.';
	}


// Modification de la position des départements
	if ($action == 'modPosDepts') {
		$newPositions = json_decode(stripslashes(urldecode($newPos)), true);
		reorganise_departements($newPositions);
		$retour['error'] = 'OK';
		$retour['message'] = 'Departements 	reordered';
	}


// Show / Hide departement
	if ($action == 'modHideDept') {
		$d = new Infos(TABLE_DEPTS);
		$d->loadInfos('id', $idDept);
		$state = $d->getInfo('hide');
		($state == 0) ? $newState = 1 : $newState = 0 ;
		$d->addInfo('hide', $newState);
		$d->save();
		$retour['error'] = 'OK';
		$retour['message'] = 'Departement modified.';
	}

// Modif de departement (label, template, ou etapes)
	if ($action == 'modValDept') {
		$d = new Infos(TABLE_DEPTS);
		$d->loadInfos('id', $idDept);
		$d->addInfo('label', urldecode($newLabel));
		$d->addInfo('template_name', $newTemplate);
		$etapes = json_encode(explode(', ', urldecode($newEtapes)));
		$d->addInfo('etapes', $etapes);
		$d->save();
		$retour['error'] = 'OK';
		$retour['message'] = 'Departement modified.';
	}


// Copie un enregistrement de version pour la version actuelle
	if ($action == 'copySaAMVersion') {
		$SIold = new Infos(TABLE_CONFIG);
		$SIold->loadInfos('version', $lastV);
		$SInew = new Infos(TABLE_CONFIG);
		foreach ($SIold as $champ => $value) {
			if ($champ == 'id' || $champ == 'version' || $champ == 'oldversion') continue;
			$SInew->addInfo($champ, $value);
		}
		$SInew->addInfo('version', SAAM_VERSION);
		$SInew->addInfo('oldversion', $lastV);
		$SInew->save();
		$retour['error'] = 'OK';
		$retour['message'] = 'Version created in DB.';
	}

// Écriture des infos de conig dans la base
	if ($action == 'updateConfig') {
		$newConf = json_decode($newConfig, true);
		$saamInf = new Infos(TABLE_CONFIG);
		$saamInf->loadInfos('version', SAAM_VERSION);
		if (is_array($newConf)) {
			foreach ($newConf as $champ => $value) {
				if (is_array($value))
					$value = json_encode($value);
				$saamInf->addInfo($champ, $value);
			}
			$saamInf->save();
			$retour['error'] = 'OK';
			$retour['message'] = 'SaAM configuration saved.';
		}
		else $retour['message'] = 'newConfig not an array !';
	}

// Vidage des dossiers TEMP
	if ($action == 'purgeTempFolders') {
		if (!$_SESSION['user']->isMagic())
			throw new Exception("This operation is reserved to 'Magic' user status. Access denied.");
		$tf = INSTALL_PATH.FOLDER_TEMP;
		$tempFolders = Array(
			$tf.'exports/',
			$tf.'uploads/banks/',
			$tf.'uploads/bughunter/',
			$tf.'uploads/prod/',
			$tf.'uploads/retakes/',
			$tf.'uploads/vignettes/'
		);
		foreach($tempFolders as $F) {
			foreach(glob($F.'*') as $file)
				@unlink($file);
		}
		$retour["error"] = "OK";
		$retour["message"] = "Temp folders emptied.";
	}

// Vidage des dossiers TEMP
	if ($action == 'purgeBFlogs') {
		if (!$_SESSION['user']->isDev())
			throw new Exception("This operation is reserved to 'Dev' user status. Access denied.");
		$logPath = INSTALL_PATH."BFlogs/";
		foreach(glob($logPath.'*') as $file)
			@unlink($file);
		$retour["error"] = "OK";
		$retour["message"] = "Brute force logs emptied.";
	}
}
catch (Exception $e) {
	$retour['message'] = $e->getMessage();
}

echo json_encode($retour);

?>
