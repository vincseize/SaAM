<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC'] . "/checkConnect.php" );
	if (!($_SESSION['user']->isSupervisor() || $_SESSION['user']->isDemo())) die('{"error":"error", "message":"actions sequences : Access denied."}');

include('admin_sequences_fcts.php');

extract($_POST);

$retour['error'] = 'error';
$retour['message'] = 'action undefined';

$userActifID	 = $_SESSION['user']->getUserInfos('id');
$userActifStatus = $_SESSION['user']->getUserInfos('status');
global $userActifID;
global $userActifStatus;



// Traitement de l'ajout d'une séquence
if ($action == 'addSeq') {
	try {
		$seqInfos = json_decode(stripslashes(urldecode($seqInfos)), true);
		add_one_sequence ($seqInfos, $projID);
		$retour['error'] = 'OK';
		$retour['message'] = 'Sequence pour le projet '.$projID.' ajoutée.';
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

// Traitement de la modification d'une séquence
if ($action == 'modSeq') {
	try {
		$seqInfos = json_decode(stripslashes(urldecode($seqInfos)), true);
		modif_sequence ($seqInfos, $seqID);
		$state = 'modifiée.';
		if (@$seqInfos['hide']=='1') $state = 'cachée';
		if (@$seqInfos['hide']=='0') $state = 'montrée';
		if (@$seqInfos['lock']=='1') $state = 'bloquée';
		if (@$seqInfos['lock']=='0') $state = 'débloquée';
		$retour['error'] = 'OK';
		$retour['message'] = 'Sequence '.$state.'.';
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

// Traitement de la modification de l'ordre des séquences
if ($action == 'modPos') {
	try {
		$newPositions = json_decode(stripslashes(urldecode($newPos)), true);
		reorganise_sequences($newPositions);
		$retour['error'] = 'OK';
		$retour['message'] = 'séquences réorganisées';
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}


// Traitement de l'archivage d'une séquence
if ($action == 'archiveSeq') {
	try {
		archive_sequence($idSeq);
		$retour['error'] = 'OK';
		$retour['message'] = 'séquences archivée';
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

// Traitement de la restauration d'une séquence
if ($action == 'restoreSeq') {
	try {
		restore_sequence($idSeq);
		$retour['error'] = 'OK';
		$retour['message'] = 'séquences restaurée';
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

echo json_encode($retour);

?>
