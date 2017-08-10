<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	if (!$_SESSION['user']->isArtist()) die('{"error":"error", "message":"actions final : Access denied."}');

	require_once('directories.php');

extract($_POST);

$retour['error'] = 'error';
$retour['message'] = 'action undefined';


////////////////////////////////////////////////////////////////// VIDEOS //////////////////////////////////////////////////////////////

// Récupération des dernières vidéos selon la sélection
if ($action == 'getVideosUrls') {
	$sel = json_decode(urldecode($selection), true);
	$seqList = Array();
	foreach($sel['seq'] as $seqID=>$dept) {
		$se = new Sequences($seqID);
		$shotSeq = $se->getSequenceShots(false);
		$seqPos  = $se->getSequenceInfos(Sequences::SEQUENCE_POSITION);
		$seqTitle= $se->getSequenceInfos(Sequences::SEQUENCE_TITLE);
		foreach($shotSeq as $shot) {
			$shotID = $shot[Shots::SHOT_ID_SHOT];
			$shotPos = $shot[Shots::SHOT_POSITION];
			$shotTitle = $shot[Shots::SHOT_TITLE];
			$shotVideo = getLastVideoRetake($shotID, $dept);
			if ($shotVideo)
				$seqList[(int)$seqPos][(int)$shotPos] = Array('labels'=>$seqTitle.'/'.$shotTitle, 'dept'=>$shotVideo[0], 'video'=>$shotVideo[1]);
		}
	}
	foreach($sel['shot'] as $shotID=>$dept) {
		$sh = new Shots($shotID);
		$shotSeq = $sh->getShotInfos(Shots::SHOT_ID_SEQUENCE);
		$shotTitle = $sh->getShotInfos(Shots::SHOT_TITLE);
		$s = new Sequences($shotSeq);
		$seqPos  = $s->getSequenceInfos(Sequences::SEQUENCE_POSITION);
		$seqTitle= $s->getSequenceInfos(Sequences::SEQUENCE_TITLE);
		$shotPos = $sh->getShotInfos(Shots::SHOT_POSITION);
		$shotVideo = getLastVideoRetake($shotID, $dept);
		if ($shotVideo)
			$seqList[(int)$seqPos][(int)$shotPos] =  Array('labels'=>$seqTitle.'/'.$shotTitle, 'dept'=>$shotVideo[0], 'video'=>$shotVideo[1]);
	}
	ksort($seqList);
	$retour['selection'] = Array();
	$retour['newQueue']  = Array();
	foreach($seqList as $shotsList) {
		ksort($shotsList);
		foreach($shotsList as $shot) {
			$retour['selection'][] = Array($shot['labels'], $shot['dept']);
			$retour['newQueue'][]  = $shot['video'];
		}
	}
	$retour['error'] = $retour['message'] = 'OK';
}

// récupération de la retake vidéo la + récente
function getLastVideoRetake ($shotID, $forceDept=0) {
	$retakesList = Array();
	if ($forceDept == 0) {			// récup pour tous les depts.
		$l = new Liste();
		$all = $l->getListe(TABLE_DEPTS, 'id', 'position', 'ASC', 'type', '=', 'shots');
		foreach($all as $deptID) {
			$retakesDept = getVideoRetake($shotID, $deptID);
			if (count($retakesDept) > 0)
				$retakesList[key($retakesDept)] = reset($retakesDept);
		}
	}								// Récup pour le dept. défini
	else $retakesList = getVideoRetake($shotID, $forceDept);
	if (count($retakesList) == 0 ) return false;			// Si pas de retake vidéo on retourne
	$lastRetake = end($retakesList);						// récup la dernière retake de la liste (dept le plus en avant dans la progression)
	$deptChoose = key($retakesList);						// récup le nom du dept pour cette retake
	return Array($deptChoose, $lastRetake);					// on retourne la liste
}

// récupération de la retake la + récente d'un dept donné pour un shot
function getVideoRetake($shotID, $deptID) {
	$shot = new Shots($shotID);
	$retakes = $shot->getRetakesList($deptID);				// récup les retakes du dept
	if (count($retakes) == 0) return Array();				// Si pas de retake on retourne
	$dirRetakes = $shot->getDirRetakes($deptID);			// récup retakes base path pour ce dept
	$retakesPaths = Array();
	foreach($retakes as $retakeNumber => $retakeName) {		// Pour chaque retake de ce dept
		$retakePath = FOLDER_DATA_PROJ.$dirRetakes.'/video_'.$retakeName.'.ogv';
		if (file_exists(INSTALL_PATH.$retakePath))			// Si retake vidéo
			$retakesPaths[$retakeNumber] = $retakePath;		// on l'ajoute à la liste des paths
	}
	if (count($retakesPaths) == 0) return Array();			// Si pas de retake vidéo dans ce dept, on retourne
	ksort($retakesPaths);									// on trie les retakes dans l'ordre
	$deptName   = get_label_dept((int)$deptID);				// récup dept name
	$lastRetake = reset($retakesPaths).'?'.time();			// récup retake la + récente
	return Array($deptName => $lastRetake);
}

////////////////////////////////////////////////////////////////// SELECTION MANAGER ///////////////////////////////////////////////////

if ($action == 'saveSelection') {
	$p = new Projects($projID);
	$finalDir = INSTALL_PATH.FOLDER_DATA_PROJ.$p->getDirProject().'/final';
	if (!is_dir($finalDir))
		mkdir($finalDir, 0755, true);
	$file = "$finalDir/$saveName.json";
	if (!file_exists($file)) {
		$data = Array(
			"selection" => json_decode(urldecode($selection), true),
			"openSeq" => json_decode(urldecode($openedSeq))
		);
		if (file_put_contents($file, json_encode($data))) {
			$retour['error'] = 'OK';
			$retour['message'] = 'Selection saved.';
			$retour['saveName'] = $saveName;
		}
		else
			$retour['message'] = "Impossible to write $saveName.json!";
	}
	else {
		$retour['message'] = 'This file already exists.<br />Please choose another name.';
	}
}

if ($action == 'deleteSelection') {
	$p = new Projects($projID);
	$file = INSTALL_PATH.FOLDER_DATA_PROJ.$p->getDirProject().'/final/'.$delName.'.json';
	if (file_exists($file)) {
		if (unlink($file)) {
			$retour['error'] = 'OK';
			$retour['message'] = 'Selection deleted.';
			$retour['delName'] = $delName;
		}
		else
			$retour['message'] = "Impossible to delete $delName.json!";
	}
	else {
		$retour['message'] = 'This file does not exists.';
	}
}

if ($action == 'loadSelection') {
	$p = new Projects($projID);
	$file = INSTALL_PATH.FOLDER_DATA_PROJ.$p->getDirProject().'/final/'.$selName.'.json';
	$data = file_get_contents($file);
	if ($data && strlen($data) > 2) {
		$retour['error'] = 'OK';
		$retour['message'] = 'Selection loaded.';
		$retour['selName'] = $selName;
		$retour['selectionData'] = $data;
	}
	else
		$retour['message'] = "Impossible to load $selName.json!";
}

////////////////////////////////////////////////////////////////// MESSAGES ////////////////////////////////////////////////////////////

// Traitement de l'ajout de message
if ($action == 'addMessage') {
	try {
		$cm = new Comments('final');
		$cm->initNewComm($projID, $reponse);
		$cm->addText(stripslashes(urldecode($texte)));
		$cm->save();
		Dailies::add_dailies_entry($projID, Dailies::GROUP_ROOT, Dailies::TYPE_FINAL_NEW_MESSAGE, stripslashes(urldecode($texte)));
		$retour['error'] = 'OK';
		$retour['message'] = 'message sauvegardé.';
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}


// Traitement de la suppression de message
if ($action == 'deleteMessage') {
	try {
		$cm = new Comments('final', $idComm);
		$cm->delete();
		$retour['error'] = 'OK';
		$retour['message'] = 'message supprimé.';
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

echo json_encode($retour); ?>