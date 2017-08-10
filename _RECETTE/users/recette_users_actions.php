<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	require_once ('dates.php' );
	require_once ('vignettes_fcts.php' );
	require_once ('admin_users_fcts.php' );
	
	if (!$_SESSION['user']->isDev()) die();
	
extract($_POST);

$retour['error'] = 'error';
$retour['message'] = 'action undefined';


// Traitement de l'action 'ADD'
if ($action == 'add') {
	try {
		add_user_recette($loginUser);
		$retour['error'] = $retour['message'] = 'OK';
		$retour['loginnewUser'] = $loginUser;
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

// Traitement de l'action 'MOD'
elseif ($action == 'mod') {
	try {
		$retour['titleBack'] = update_user($loginUser);
		$retour['error'] = $retour['message'] = 'OK';
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

// Traitement de l'action 'FOLDER'
elseif ($action == 'folder') {
	try {
		createUserFolder_recette($loginUser);
		$retour['error'] = $retour['message'] = 'OK';
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

// Traitement de l'action 'DEL'
elseif ($action == 'del') {
	try {
		delete_user($idUser, $loginUser, $_SESSION['user']->getUserInfos('login'));
		$retour['error'] = $retour['message'] = 'OK';
		$retour['loginUser'] = $loginUser;
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

echo json_encode($retour);

?>
