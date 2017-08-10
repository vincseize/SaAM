<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC'] . "/checkConnect.php" );
	if (!($_SESSION['user']->isSupervisor() || $_SESSION['user']->isDemo())) die('{"error":"error", "message":"actions projects : Access denied."}');

include('directories.php');
include('admin_projects_fcts.php');

extract($_POST);

$retour['error'] = 'error';
$retour['message'] = 'action undefined';



// Traitement de l'action 'Ajout de projet'
if ($action == 'addProject') {
	try {
		$values = json_decode(stripslashes(urldecode($values)), true);
		add_project($values);
		if (isset($team)) {
			$team = stripslashes(urldecode($team));
			add_team_project ($values['title'], $team);
		}
		if (isset($depts)) {
			$dpts = json_decode(stripslashes(urldecode($depts)), true);
			mod_depts_project ($dpts, false, $values['title'], true);
		}
		if (isset($struct)) {
			$struct = json_decode(stripslashes(urldecode($struct)), true);
			add_sequences ($struct, false, $values['title']);
		}
		$retour['error'] = $retour['message'] = 'OK';
	}
	catch (Exception $e) {						// Si erreur, on détruit en BDD tout ce qui vient d'être créé, pour éviter l'erreur de doublons, et les dossiers en trop dans datas
		$retour['message'] = $e->getMessage();
		try { delete_project(false, $values['title'], true); }
//		catch (Exception $e) { $retour['message'] .= '<br />'. $e->getMessage(); }
		catch (Exception $e) { $retour['message'] .= '...'; }
	}
}

// Traitement de l'action 'Modif Positions'
if ($action == 'modPos') {
	try {
		$newPositions = json_decode(stripslashes(urldecode($newPos)), true);
		reorganise_projects($newPositions);
		$retour['error'] = $retour['message'] = 'OK';
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

// Traitement de l'action 'Modif Hide'
if ($action == 'modHide') {
	try {
		$titleProj = mod_hide_project($idProj, $hide);
		$retour['error'] = $retour['message'] = 'OK';
		$retour['idProj'] = $idProj;
		$retour['titleProj'] = $titleProj;
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

// Traitement de l'action 'Modif Hide'
if ($action == 'modLock') {
	try {
		mod_lock_project($idProj, $lock);
		$retour['error'] = $retour['message'] = 'OK';
		$retour['idProj'] = $idProj;
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

// Traitement de l'action 'Modif Title'
if ($action == 'modTitle') {
	try {
		mod_title_project($idProj, $newTitle);
		$retour['error'] = $retour['message'] = 'OK';
		$retour['idProj'] = $idProj;
		$retour['newTitle'] = $newTitle;
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

// Traitement de l'action 'Archivage'
if ($action == 'archiveProj') {
	try {
		archive_project($idProj, $newState);
		$retour['error'] = $retour['message'] = 'OK';
		$retour['idProj'] = $idProj;
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

// Traitement de l'action 'Destruction définitive'
if ($action == 'destroyProj') {
	try {
		destroy_project($idProj);
		$retour['error'] = $retour['message'] = 'OK';
		$retour['idProj'] = $idProj;
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

// Traitement de l'action modify project details
if ($action == 'modProject') {
	try {
		$values = json_decode(stripslashes(urldecode($values)), true);
		modif_project($values, $IDproj);
		$retour['error'] = 'OK';
		$retour['message'] = 'Projet modifié.';
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

// Traitement de l'action move vignette (from temp upload dir)
if ($action == 'moveVignette') {
	try {
		$pr = new Projects($idProj);
		$dir = $pr->getDirProject();
		move_uploaded_vignette($dir, $vignetteName);
		$retour['error'] = 'OK';
		$retour['message'] = 'vignette sauvegardée.';
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}


// Traitement de la modif des départements
if ($action == 'modDepts') {
	try {
		mod_depts_project(explode(',', (string)$newDeptsList), $IDproj, false, false);
		$retour['error'] = 'OK';
		$retour['message'] = 'Départements modifiés.';
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

// Traitement de l'action 'GENERATE TABLEUR'
if ($action == 'genMasterTableur') {
    try {
        masterTABLEUR_project($idProj, $titleProj);
        $retour['error'] = 'OK';
        //$retour['message'] = 'tableur:'.$idProj.'|'.$titleProj;
        $retour['idProj'] = $idProj;
        $retour['titleProj'] = $titleProj;
    }
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

// Traitement de la sauvegarde (ZIP) d'un projet
if ($action == 'zipProj') {
    try {
        $retour['zip_url'] = createZip_project($idProj);
        $retour['error'] = 'OK';
		$retour['message'] = "Zip archive created: '".$retour['zip_url']."'.";
    }
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

echo json_encode($retour);