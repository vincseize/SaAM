<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );


extract($_POST);

$retour['error'] = 'error';
$retour['message'] = 'action undefined';

try {

	// Ajout de groupe d'ACL
	if ($action == 'addNote') {
		$note = new Infos(TABLE_NOTES);
		$note->addInfo('note', urlencode($textNote));
		$note->addInfo('ID_user', $_SESSION['user']->getUserInfos(Users::USERS_ID));
		$note->save();
		$retour['error'] = 'OK';
		$retour['message'] = 'Note added.';
	}

	// Modification de note
	if ($action == 'modNote') {
		$note = new Infos(TABLE_NOTES);
		$note->loadInfos('id', $idNote);
		$note->addInfo('note', urlencode($textNote));
		$note->save();
		$retour['error'] = 'OK';
		$retour['message'] = 'Note modified.';
	}

	// Suppression de note
	if ($action == 'deleteNote') {
		$note = new Infos(TABLE_NOTES);
		$note->loadInfos('id', $idNote);
		$note->delete();
		$retour['error'] = 'OK';
		$retour['message'] = 'Note deleted.';
	}

	// Marquage de couleur de note (position)
	if ($action == 'markPosNote') {
		$note = new Infos(TABLE_NOTES);
		$note->loadInfos('id', $idNote);
		$note->addInfo('position', (int)$position);
		$note->save();
		$retour['error'] = 'OK';
		$retour['message'] = 'Note color updated.';
	}
}
catch(Exception $e) { $retour['message'] = $e->getMessage(); }

echo json_encode($retour);
?>