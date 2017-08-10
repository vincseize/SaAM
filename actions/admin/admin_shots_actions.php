<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	if ($_SESSION['user']->isVisitor()) die('{"error":"error", "message":"actions shots : Access denied."}');

include('admin_shots_fcts.php');

extract($_POST);

$retour['error'] = 'error';
$retour['message'] = 'action undefined';

$userActifID	 = $_SESSION['user']->getUserInfos('id');
$userActifStatus = $_SESSION['user']->getUserInfos('status');
global $userActifID;
global $userActifStatus;


try {

	$ACL = new ACL($_SESSION['user']);

	// Traitement de l'ajout d'un shot
	if ($action == 'addShot') {
		if ($ACL->check('SHOTS_ADMIN')) {
		$valShots = json_decode(stripslashes(urldecode($values)), true);
		if (is_array($valShots)) {
			$nbShot = count($valShots);
			add_shots ($IDproj, $idSeq, $labelSeq, $nbShot, $valShots);
			$retour['error'] = 'OK';
			$retour['message'] = 'Plan(s) ajouté(s).';
		}
		else $retour['message'] = 'Action "addShot" : $values not an array !';
		}
		else $retour['message'] = 'Access denied.';
	}


	// Traitement de la modification d'un shot
	if ($action == 'modShot') {
		if ($ACL->check('SHOTS_ADMIN')) {
			$shotInfos = json_decode(stripslashes(urldecode($shotInfos)), true);
			modif_shot ($shotInfos, $IDshot);
			$state = 'modifié';
			if (@$shotInfos['hide']=='1') $state = 'caché';
			if (@$shotInfos['hide']=='0') $state = 'montré';
			if (@$shotInfos['lock']=='1') $state = 'bloqué';
			if (@$shotInfos['lock']=='0') $state = 'débloqué';
			$retour['error'] = 'OK';
			$retour['message'] = 'Plan '.$state.'.';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Modification de la position des shots dans la séquence
	if ($action == 'modShotPos') {
		if ($ACL->check('SHOTS_ADMIN')) {
			$newPositions = json_decode(stripslashes(urldecode($newPos)), true);
			reorganise_shots($newPositions);
			$retour['error'] = 'OK';
			$retour['message'] = 'Plans réorganisés';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Modification de l'équipe d'artistes assignée au shot
	if ($action == 'modShotTeam') {
		if ($ACL->check('SHOTS_ADMIN')) {
			rebuildTeam_shot($IDshot, stripslashes($newTeam));
			$retour['error'] = 'OK';
			$retour['message'] = 'Équipe du plan redéfinie.';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Modification des départements d'un shot
	if ($action == 'modShotDepts') {
		if ($ACL->check('SHOTS_ADMIN')) {
			$newDeptsList = json_decode(stripslashes(urldecode($newDepts)));
			if ($newDeptsList == null) $newDeptsList = Array();
			rebuildDepts_shot($shotID, $newDeptsList);
			$retour['error'] = 'OK';
			$retour['message'] = 'Départements du plan redéfinis.';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Traitement de l'archivage d'un shot
	if ($action == 'archiveShot') {
		if ($ACL->check('SHOTS_ADMIN')) {
			archive_shot($IDshot);
			$retour['error'] = 'OK';
			$retour['message'] = 'plan archivé.';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Traitement de la restauration d'un shot
	if ($action == 'restoreShot') {
		if ($ACL->check('SHOTS_ADMIN')) {
			restore_shot($IDshot);
			$retour['error'] = 'OK';
			$retour['message'] = 'plan restauré.';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Traitement de la modif de vignette
	if ($action == 'moveVignette') {
		if ($ACL->check('SHOTS_ADMIN')) {
			$sh = new Shots($idShot);
			if (!$sh->isLocked()) {
				$dir = $sh->getDirShot();
				move_vignette($dir, $dept, $vignetteName);
				$retour['error'] = 'OK';
				$retour['message'] = 'vignette sauvegardée.';
			}
			else $retour['message'] = 'This shot is locked.';
		}
		else $retour['message'] = 'Access denied.';
	}


	// Modification des infos du département d'un shot
	if ($action == 'modShotDeptInfos') {
		if ($ACL->check('SHOTS_DEPTS_INFOS', 'shot:'.$IDshot)) {
			$newDeptInfos = stripslashes(urldecode($shotDeptInfos));
			modShotDeptInfo($IDshot, $dept, $newDeptInfos);
			$retour['error'] = 'OK';
			$retour['message'] = 'Infos modifiées.';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Modification des tags du shot
	if ($action == 'modShotTags') {
		if ($ACL->check('SHOTS_TAGS', 'shot:'.$IDshot)) {
			$newTags = stripslashes(urldecode($tagName));
			modShotTags($IDshot, $newTags);
			$retour['error'] = 'OK';
			$retour['message'] = 'Tags modifiés.';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Traitement de la validation d'une retake uploadée
	if ($action == 'moveTempRetake') {
		if ($ACL->check('SHOTS_PUBLISH', 'shot:'.$idShot)) {
			$sh = new Shots($idShot);
			if (!$sh->isLocked()) {
				$dir = $sh->getDirShot();
				$projID = $sh->getShotInfos(Shots::SHOT_ID_PROJECT);
				move_retake($dir, $dept, $retakeName, @$modif);
				$sh->setValidRetake($dept, false);
				if (count($sh->getRetakesList($dept)) == 1)
					$sh->updateVignette($dept);
				try {
					$d = new Infos(TABLE_DEPTS);
					$d->loadInfos('id', $dept);
					$deptName = $d->getInfo('label');
				} catch (Exception $e) { $deptName = $dept; }
				$typeDailies = (@$modif) ? Dailies::TYPE_SHOT_MOD_PUBLISHED : Dailies::TYPE_SHOT_NEW_PUBLISHED;
				Dailies::add_dailies_entry($projID, Dailies::GROUP_SHOT, $typeDailies, '{"idShot":"'.$idShot.'","dept":"'.$deptName.'"}');
				$retour['error'] = 'OK';
				$retour['message'] = 'published sauvegardé.';
			}
			else $retour['message'] = 'This shot is locked.';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Traitement de la suppression de la retake Temp
	if ($action == 'deleteTempRetake') {
		$retakeTempFile = glob(INSTALL_PATH.'temp/uploads/retakes/'.$_SESSION['user']->getUserInfos('id').'_newRetakeTemp.*');
		if (unlink($retakeTempFile[0])) {
			$retour['error'] = 'OK';
			$retour['message'] = 'fichier temporaire supprimé.';
		}
		else $retour['message'] = 'deleteTempRetake : Unable to delete temp file.';
	}


	// Traitement de la validation de la retake actuelle
	if ($action == 'valideRetake') {
		if ($ACL->check('SHOTS_PUBLISH', 'shot:'.$idShot)) {
		$sh = new Shots($idShot);
		if (!$sh->isLocked()) {
			$sh->setValidRetake($dept, true);
			$sh->updateVignette($dept);
			try {
				$d = new Infos(TABLE_DEPTS);
				$d->loadInfos('id', $dept);
				$deptName = $d->getInfo('label');
			} catch (Exception $e) { $deptName = $dept; }
			Dailies::add_dailies_entry($projID, Dailies::GROUP_SHOT, Dailies::TYPE_SHOT_VALID_PUBLISHED, '{"idShot":"'.$idShot.'","dept":"'.$deptName.'"}');
			$retour['error'] = 'OK';
			$retour['message'] = 'Published validé.';
		}
		else $retour['message'] = 'This shot is locked.';
		}
		else $retour['message'] = 'Access denied.';
	}


	// Traitement de l'ajout de message
	if ($action == 'addMessage') {
		if ($ACL->check('SHOTS_MESSAGE', 'shot:'.$shotID)) {
			$sh = new Shots($shotID);
			if (!$sh->isLocked()) {
				$cm = new Comments('retake');
				$cm->initNewCommRetake($shotID, $dept, $reponse);
				$cm->addText(stripslashes(urldecode($texte)));
				$cm->save();
				try {
					$d = new Infos(TABLE_DEPTS);
					$d->loadInfos('id', $dept);
					$deptName = $d->getInfo('label');
				} catch (Exception $e) { $deptName = $dept; }
				Dailies::add_dailies_entry($projID, Dailies::GROUP_SHOT, Dailies::TYPE_SHOT_NEW_MESSAGE, '{"idShot":"'.$shotID.'","dept":"'.$deptName.'","txtMess":"'.urlencode($texte).'"}');
				$retour['error'] = 'OK';
				$retour['message'] = 'message sauvegardé.';
			}
			else $retour['message'] = 'This shot is locked.';
		}
		else $retour['message'] = 'Access denied.';
	}


	// Traitement de la suppression de message
	if ($action == 'deleteMessage') {
		if ($ACL->check('SHOTS_MESSAGE', 'shot:'.$shotID)) {
			$sh = new Shots($shotID);
			if (!$sh->isLocked()) {
				$cm = new Comments('retake', $idComm);
				$cm->delete();
				$retour['error'] = 'OK';
				$retour['message'] = 'message supprimé.';
				}
			else $retour['message'] = 'This shot is locked.';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Recalcul de la progression de TOUT les shots d'un coup
	if ($action == "recalcAllShotsProgress") {
		if (!$projID) throw new Exception("project ID is missing.");
		$p = new Projects($projID);
		$allShots = $p->getShots();
		foreach($allShots as $shot){
			$s = new Shots($shot[Shots::SHOT_ID_SHOT]);
			$s->recalc_shot_progress();
		}
		$retour['error'] = 'OK';
		$retour['message'] = 'Project progress recalculated.';
	}


	// Reconstruction des équipes de TOUT LES SHOTS dans TOUS LES PROJETS, avec leurs IDs !!
	if ($action == 'refactorShotsTeams') {
		if (!$_SESSION['user']->isDev()) die('{"error":"error","message":"Shots teams rebuilder: Access denied"}');
		$l = new Liste();
		$allShots = $l->getListe(TABLE_SHOTS, 'id');
		$retour['message'] = '<b>SHOTS TEAM rebuilder</b><br />';

		foreach ($allShots as $shotID) {
			try { $sh = new Shots($shotID); }
			catch (Exception $e) { $retour['message'] .= "shot #$shotID: <i>FAILED! (missing project)</i><br />"; continue; }
			$oldTeamShot = $sh->getShotTeam('arrayIDs');
			$newTeamShot = Array();
			if (is_array($oldTeamShot)) {
				foreach ($oldTeamShot as $usr) {
					if ((int)$usr === 0) {
						try { $u = new Users($usr, Users::USERS_PSEUDO); }
						catch (Exception $e) { continue; }
						$newTeamShot[] = (string)$u->getUserInfos(Users::USERS_ID);
					}
					else $newTeamShot[] = (string)$usr;
				}
			}
			$projShot = $sh->getShotInfos(Shots::SHOT_ID_PROJECT);
			$sh->setInfos($projShot, Array(Shots::SHOT_TEAM => json_encode($newTeamShot)));
			if (count($newTeamShot) > 0)
				$retour['message'] .= "shot #$shotID: ". json_encode($newTeamShot).'<br />';
		}
		$retour['message'] .= '<b>DONE.</b>';
		$retour['error'] = 'OK';
		$retour['persistant'] = 'persist';
	}


}
catch (Exception $e) {
	$retour['message'] = $e->getMessage();
}

echo json_encode($retour);

?>