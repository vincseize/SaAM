<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	if ($_SESSION['user']->isVisitor()) die('{"error":"error", "message":"actions shots : Access denied."}');

require_once ('dates.php' );
require_once ('directories.php' );


/////////////////////////////////////////////////////////////////////////////////////////////////////// FONCTIONS RECETTES

function add_shot_recette ($ID_project,$ID_creator,$ID_sequence,$title){

	$new_position = Liste::getMAx(TABLE_SHOTS,'position')+1;
	$position = $new_position;
	$number = sprintf("%03d", $position);
	$label = NOMENCLATURE_SHOT.$number;

	$shot = new Shots();

	$shot->setcreator( $ID_creator );
	$shot->setIDproject($ID_project);
	$shot->setIDsequence( $ID_sequence );
	$shot->setTitle( $title );
	$shot->setLabel( $label );

	$shot->save();
}

// Supprime tout les shots d'une séquence
function delete_all_shots_sequence($ID_sequence) {
	$sL = new Liste();
	$shots_list = $sL->getListe(TABLE_SHOTS,'id', 'id', 'ASC', Shots::SHOT_ID_SEQUENCE, '=', $ID_sequence);
	if (is_array($shots_list)) {
		foreach($shots_list as $shId)
			delete_shot($shId);
	}
}

// Supprime un shot
function delete_shot($ID_shot) {
	$l = new Liste();
	$shComList = $l->getListe(TABLE_COMM_SHOT, 'id', 'id', 'ASC', Comments::COMM_ID_SHOT, '=', $ID_shot);
	if (is_array($shComList)) {
		foreach($shComList as $shComId) {
			$shCom = new Comments('retake', (int)$shComId);
			$shCom->delete();
		}
	}
	$shDptList = $l->getListe(TABLE_SHOTS_DEPTS, 'id', 'id', 'ASC', Comments::COMM_ID_SHOT, '=', $ID_shot);
	if (is_array($shDptList)) {
		foreach($shDptList as $shDptId) {
			$i = new Infos(TABLE_SHOTS_DEPTS);
			$i->loadInfos('id', $shDptId);
			$i->delete();
		}
	}
	$sh = new Shots((int)$ID_shot);
	$sh->delete();
}



//////////////////////////////////////////////////////////////////////////////////////////////////////// FONCTIONS UI

// Ajoute un ou plusieurs shot(s) à une séquence d'un projet
function add_shots ($ID_project, $ID_sequence, $labelSeq, $nbShot, $valShots=false) {
	if (empty($ID_project))  { throw new Exception('add_shots : missing $ID_project !'); return; }
	if (empty($ID_sequence)) { throw new Exception('add_shots : missing $ID_sequence !'); return; }
	if (empty($labelSeq))	 { throw new Exception('add_shots : missing $labelSeq !'); return; }
	if (empty($nbShot))		 { throw new Exception('add_shots : missing $nbShot, or $nbShot = 0 !'); return; }
	if ($ID_project == 1)    { throw new Exception('the DEMO project can\'t be modified. Please make your own.'); return; }

	$ID_creator = $_SESSION["user"]->getUserInfos(Users::USERS_ID);
	$p = new Projects();
	$p->loadFromBD(Projects::PROJECT_ID_PROJECT, $ID_project);
	$dirProject = $p->getDirProject();
	$listDepts  = $p->getDeptsProject(true, 'id');
	$fpsProj	= $p->getProjectInfos(Projects::PROJECT_FPS);
	$nomencShot = $p->getNomenclature('shot');
	$date = date('Y-m-d 00:00:00');
	$deadline = $p->getDeadline();

	if (($p->isDemo() && !$_SESSION['user']->isRoot()) && ($nbShot > MAX_DEMO_SHOTS)) { throw new Exception('the DEMO project can\'t have more than '.MAX_DEMO_SHOTS.' shots per sequence.'); return; }

	$lastNbShots = $p->getNbShots($ID_sequence);
	if (is_array($valShots)) {
		foreach($valShots as $ind=>$valShot) {
			$lastNbShots++;
			$labelShot = preg_replace('/###/', sprintf('%03d', $lastNbShots), $nomencShot);
			$titleShot = ($valShot['title'] && $valShot['title'] != '') ? $valShot['title'] : $labelShot;
			$dateShot = ($valShot['date'] && $valShot['date'] != '') ? $valShot['date'] : $date;
			$deadShot = ($valShot['deadline'] && $valShot['deadline'] != '') ? $valShot['deadline'] : $deadline;
			$shot = new Shots();
			$shot->setcreator($ID_creator);
			$shot->setIDproject($ID_project);
			$shot->setIDsequence($ID_sequence);
			$shot->setPosition($lastNbShots);
			$shot->setLabel($valShot['label']);
			$shot->setTitle($titleShot);
			$shot->setDate($dateShot);
			$shot->setDeadline($deadShot);
			$shot->setValue(Shots::SHOT_FPS, $fpsProj);
			$shot->save();
			createShotFolders ($dirProject, $labelSeq, $labelShot, $listDepts);
		}
	}
	else {
		for ($i=1; $i<=$nbShot; $i++) {
			$labelShot = preg_replace('/###/', sprintf('%03d', $i), $nomencShot);
			$shot = new Shots();
			$shot->setcreator($ID_creator);
			$shot->setIDproject($ID_project);
			$shot->setIDsequence($ID_sequence);
			$shot->setPosition($i);
			$shot->setLabel($labelShot);
			$shot->setTitle('title '.$labelShot);
			$shot->setDate($date);
			$shot->setDeadline($deadline);
			$shot->save();
			createShotFolders ($dirProject, $labelSeq, $labelShot, $listDepts);
		}
	}
}

function createShotFolders ($dirProject, $labelSeq, $labelShot, $listDepts) {
	if (!makeDataDir($dirProject.'/sequences/'.$labelSeq.'/'.$labelShot))
		throw new Exception('create shot folder: '.$labelSeq.'/'.$labelShot.' :failed.');
	foreach ($listDepts as $dept) {
		if ($dept == 'scenario') continue;
		foreach($_SESSION['CONFIG']['dataShotsFolders'] as $subFloder){
			if (!makeDataDir($dirProject.'/sequences/'.$labelSeq.'/'.$labelShot.'/'.$dept.'/datashot/'.$subFloder))
				throw new Exception('create shot folder '.$labelSeq.'/'.$labelShot.'/'.$dept.'/datashot/'.$subFloder.' failed.');
		}
		if (!makeDataDir($dirProject.'/sequences/'.$labelSeq.'/'.$labelShot.'/'.$dept.'/retakes'))
			throw new Exception('create shot folder: '.$labelSeq.'/'.$labelShot.'/'.$dept.'/retakes :failed.');
	}
}


// modifie les infos d'un shot particulier
function modif_shot ($values, $IDshot=false) {
	if (!is_array($values)) { throw new Exception('modif_shot : $values not an array : '.$values.'!'); return; }
	if (!$IDshot) { throw new Exception('modif_shot : missing $IDshot !'); return; }

	$sh = new Shots($IDshot);
	if ($sh->isLocked() && !isset($values[SHOTS::SHOT_LOCK])) { throw new Exception('This shot is locked.'); return; }
	$idProj = $sh->getShotInfos(Shots::SHOT_ID_PROJECT);
	if ($idProj == 1 && !$_SESSION['user']->isRoot()) { throw new Exception('The DEMO project can\'t be modified. Please make your own.'); return; }
	foreach($values as $row => $val) {
		$sh->setValue($row, $val);
	}
	$sh->save();
	unset($sh);
}

// Réagencement des shots dans la séquence
function reorganise_shots ($newPositions) {
	if (!is_array($newPositions)) { throw new Exception('$newPositions : not an array !'); return; }
	foreach ($newPositions as $idShot => $posShot) {
		$s = new Shots($idShot);
		$idProj = $s->getShotInfos(Shots::SHOT_ID_PROJECT);
		if ($idProj == 1 && !$_SESSION['user']->isRoot()) { throw new Exception('the DEMO project can\'t be modified. Please make your own.'); return; }
		$s->setPosition($posShot);
		$s->save();
	}
}


// modifie l'équipe d'un shot
function rebuildTeam_shot ($IDshot=false, $newTeam=false) {
	if (!$IDshot)  { throw new Exception('rebuildTeam_shot : missing $IDshot !'); return; }
	if (!$newTeam) { throw new Exception('rebuildTeam_shot : missing $newTeam !'); return; }

	$sh = new Shots($IDshot);
	$idProj = $sh->getShotInfos(Shots::SHOT_ID_PROJECT);
	if ($idProj == 1 && !$_SESSION['user']->isRoot()) { throw new Exception('the DEMO project can\'t be modified. Please make your own.'); return; }
	$sh->setTeam(json_decode($newTeam));
	$sh->save();
	unset($sh);
}

// modifie les départements définis pour un shot
function rebuildDepts_shot ($IDshot=false, $newDeptsList=false) {
	if (!$IDshot)		{ throw new Exception('rebuildDepts_shot : missing $IDshot !'); return; }
	if (!is_array($newDeptsList)) { throw new Exception('rebuildDepts_shot : $newDeptsList not an array !'); return; }

	$sh = new Shots($IDshot);
	$sh->setShotDepts($newDeptsList);
	$sh->save();
}

function modShotDeptInfo ($IDshot=false, $dept=false, $newDeptInfos=false) {
	if (!$IDshot)		{ throw new Exception('modShotDeptInfo : missing $IDshot !'); return; }
	if (!$dept)			{ throw new Exception('modShotDeptInfo : missing $dept !'); return; }
	if (!$newDeptInfos)	{ throw new Exception('modShotDeptInfo : missing $newDeptInfos !'); return; }

	$sh = new Shots($IDshot);
	$sh->setShotDeptsInfos($dept, $newDeptInfos);
}

function modShotTags ($IDshot=false, $newTags=false) {
	if (!$IDshot)		{ throw new Exception('modShotTags : missing $IDshot !'); return; }
	if (!$newTags)	{ throw new Exception('modShotTags : missing $newTags !'); return; }

	$sh = new Shots($IDshot);
	$sh->setValue(Shots::SHOT_TAGS, $newTags);
	$sh->save();
}


// Archive un shot
function archive_shot($IDshot=false) {
	if (!$IDshot) { throw new Exception('archive_shot : missing $idSeq !'); return; }
	$s = new Shots($IDshot);
	$idProj = $s->getShotInfos(Shots::SHOT_ID_PROJECT);
	if ($idProj == 1 && !$_SESSION['user']->isRoot()) { throw new Exception('the DEMO project can\'t be modified. Please make your own.'); return; }
	$s->archiveShot();
	$s->save();
}

// restaure un shot
function restore_shot($IDshot=false) {
	if (!$IDshot) { throw new Exception('restore_shot : missing $idSeq !'); return; }
	$s = new Shots($IDshot);
	$idProj = $s->getShotInfos(Shots::SHOT_ID_PROJECT);
	if ($idProj == 1 && !$_SESSION['user']->isRoot()) { throw new Exception('the DEMO project can\'t be modified. Please make your own.'); return; }
	$s->restoreShot();
	$s->save();
}


// récupère la vignette du dossier temp (après upload)
function move_vignette ($dir=false, $toDept=false, $vignetteName=false) {
	if (!$dir)			{ throw new Exception('move_vignette() : missing shot directory !'); return; }
	if (!$toDept)		{ throw new Exception('move_vignette() : missing the dept !');		 return; }
	if (!$vignetteName)	{ throw new Exception('move_vignette() : missing vignette name !');  return; }

	$tempVignette = INSTALL_PATH .'temp/uploads/vignettes/'.$vignetteName;
	$vNameArr = explode('.', $vignetteName);
	$vExt = $vNameArr[count($vNameArr)-1];
	if (!is_dir(INSTALL_PATH . FOLDER_DATA_PROJ . $dir .'/'. $toDept)) {
		makeDataDir($dir .'/'. $toDept);
		makeDataDir($dir .'/'. $toDept .'/datashot');
		makeDataDir($dir .'/'. $toDept .'/retakes');
	}
	$destVignette = INSTALL_PATH . FOLDER_DATA_PROJ . $dir .'/'. $toDept . '/vignette.' ;
	@unlink ($destVignette.'gif');
	@unlink ($destVignette.'jpg');
	@unlink ($destVignette.'png');
	if (!is_file($tempVignette))
		{ throw new Exception('move_vignette() : temp vignette file not found !'); return; }
	if (!@copy($tempVignette, $destVignette.$vExt))
		{ throw new Exception('move_vignette() : unable to copy vignette file to : '.$destVignette.$vExt.' !'); return; }
	if (!@unlink($tempVignette))
		{ throw new Exception('move_vignette() : unable to delete temp vignette file !'); }
}


// récupère la retake du dossier temp (après upload)
function move_retake ($dir=false, $toDept=false, $retakeTempName=false, $isReplace=false) {
	if (!$dir)				{ throw new Exception('move_retake() : missing shot directory !');	return; }
	if (!$toDept)			{ throw new Exception('move_retake() : missing the dept !');		return; }
	if (!$retakeTempName)	{ throw new Exception('move_retake() : missing $retakeTempName !'); return; }

	$destShotDir	= INSTALL_PATH . FOLDER_DATA_PROJ . $dir .'/'. $toDept .'/retakes/' ;
	$wipShotDir		= INSTALL_PATH . FOLDER_DATA_PROJ . $dir .'/'. $toDept .'/datashot/stills/';
	$retakeTempFile = INSTALL_PATH .'temp/uploads/retakes/'.$retakeTempName;
	$retakeName		= 'retake_0';
	if (!is_dir($destShotDir))
		mkdir($destShotDir, 0755, true);
	if (!is_dir($wipShotDir))
		mkdir($wipShotDir, 0755, true);

	$nbRetake = count(glob($destShotDir.'retake*'));
	$nbWips	  = count(glob($wipShotDir.'*'));
	if (!is_file($retakeTempFile))
		{ throw new Exception('move_retake() : temp retake file not found !'); return; }
	if (is_file($destShotDir.$retakeName)) {
		if ($isReplace) {
			$mimeOldRetake = explode(';',check_mime_type_info($destShotDir.$retakeName));
			$typeOldRetake = recup_file_ext($mimeOldRetake[0]);
			if (!copy($destShotDir.$retakeName, $wipShotDir.'backup_retake_'.$nbWips.$typeOldRetake))
				{ throw new Exception('move_retake() : unable to copy last retake to WIP dir!'); return; }
		}
		else {
			if (!rename($destShotDir.$retakeName, $destShotDir.'retake_'.$nbRetake))
				{ throw new Exception('move_retake() : unable to rename last retake !'); return; }
		}
		@unlink($destShotDir.'thumbs/vthumb_retake_0.gif');
	}
	if (!copy($retakeTempFile, $destShotDir.$retakeName))
		{ throw new Exception('move_retake() : unable to copy retake file to : '.$destShotDir.' !'); return; }
	if (!unlink($retakeTempFile))
		{ throw new Exception('move_retake() : unable to delete temp retake file !'); }
}


?>
