<?php
@session_start(); // 2 lignes à placer toujours en haut du code des pages
require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );


extract($_POST);

if (!isset($action)) {
	$retour['error'] = 'ERROR';
	$retour['message'] = "pas d'action définie...";
	die(json_encode($retour));
}

// sauvegarde d'un évènement dans le fichier json
if ($action == 'save') {
	$retour['error'] = 'ERROR';
	if ($type == 'nouveau') {
		try {
			saveToJsonFile(json_decode(stripslashes(urldecode($event)), true), 'add');
			$retour['error'] = 'OK';
			$retour['message'] = "évènement ajouté";
		}
		catch(Exception $e) { $retour['message'] = "Impossible de sauver l'évènement !<br />$e"; }
	}
	elseif ($type == 'modif') {
		try {
			saveToJsonFile(json_decode(stripslashes(urldecode($event)), true), 'mod');
			$retour['error'] = 'OK';
			$retour['message'] = "évènement mis à jour";
		}
		catch(Exception $e) { $retour['message'] = "Impossible de sauver l'évènement !<br />$e"; }
	}
	elseif ($type == 'delete') {
		try {
			saveToJsonFile(json_decode(stripslashes(urldecode($event)), true), 'del');
			$retour['error'] = 'OK';
			$retour['message'] = "évènement supprimé";
		}
		catch(Exception $e) { $retour['message'] = "Impossible de supprimer l'évènement !<br />$e"; }
	}
	else {
		$retour['message'] = "pas de type défini !";
	}
}

if ($action == 'delete') {
	$retour['error'] = 'OK';
	$retour['message'] = "évènement supprimé !";
}

echo json_encode($retour);

// Sauvegarde les datas Json dans le fichier qui va bien.
function saveToJsonFile ($event, $type=false) {
	if (!is_array($event)) { throw new Exception('$event not an array !'); return; }
	$dataFile	= INSTALL_PATH.CALENDAR_JSON_DATAFILE;
	$backupFile = INSTALL_PATH.CALENDAR_JSON_DATAFILE.'.backup';
	// création du fichier de backup avant modif
	copy($dataFile, $backupFile);

	$calendar = json_decode(file_get_contents($dataFile), true);
	if		($type == 'add') { $calendar[]					= $event; }
	elseif  ($type == 'mod') { $calendar[(int)$event['id']] = $event; }
	elseif  ($type == 'del') { array_splice($calendar, $event['id'], 1); $calendar = refactorIds($calendar); }
	else	{ throw new Exception('$type unknown!'); return; }

	$calendarOK = array_filter($calendar, "vireNulls");

	if(!file_put_contents($dataFile, json_encode($calendarOK), LOCK_EX))
		throw new Exception("Impossible d'écrire dans le fichier ".$dataFile." !!");
}


function refactorIds ($cal) {
	$calendar = array();
	foreach ($cal as $k => $e) {
		$e['id'] = $k;
		$calendar[] = $e;
	}
	return $calendar;
}

function vireNulls ($val) {
	if ($val == null || $val =='null')
		return false;
	else return true;
}

?>