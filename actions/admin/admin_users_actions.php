<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC'] . "/checkConnect.php" );
	require_once ('admin_users_fcts.php' );
	require_once ('vignettes_fcts.php' );

	if (!$_SESSION['user']->isSupervisor()) die('{"error":"error","message":"actions users : Access denied"}');

extract($_POST);

$retour['error'] = 'error';
$retour['message'] = 'no action selected !';


// Traitement de l'action 'ADD'
if ($action == 'add') {
	try {
		unset($_POST['action']);
		if (isset($_POST['avatar'])) unset($_POST['avatar']);
		add_user($_POST);
		if (isset($avatar))
			createUserFolder($login, $avatar);
		else
			createUserFolder($login);
		$retour['error'] = 'OK';
		$retour['message'] = 'Utilisateur ajouté.';
		$retour['loginnewUser'] = $login;
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

// Traitement de l'action 'MOD'
elseif ($action == 'mod') {
	try {
		unset($_POST['action'], $_POST['id']);
		update_user($id, $_POST);
		$retour['error'] = 'OK';
		$retour['message'] = "Utilisateur ".@$login." modifié.";
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

// Traitement de l'action 'activate'
elseif ($action == 'activate') {
	try {
		$u = new Users($loginUser);
		$u->setValue(Users::USERS_ACTIVE, $active);
		$u->save();
		$retour['error'] = 'OK';
		$retour['message'] = "User '$loginUser' enabled.";
		if ($active == '0')
			$retour['message'] = "User '$loginUser' disabled.";
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

// Traitement de l'action 'MODIF ASSIGNATIONS PROJETS'
elseif ($action == 'modAssigns') {
	try {
		assign_user_to_projects($idUser, json_decode($projects));
		$retour['error'] = 'OK';
		$retour['message'] = "Assignations modifiées.";
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

// Traitement de l'action 'DEL'
elseif ($action == 'del') {
	try {
		delete_user($idUser, $loginUser);
		$retour['error'] = 'OK';
		$retour['message'] = "Utilisateur $loginUser supprimé.";
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

elseif ($action == 'load') {
	try {
		$u = new Users($loginUser);
		$datasUser = $u->getUserInfos();
		$datasUser['vignetteUser'] = check_user_vignette_ext($datasUser['id'], $loginUser);
		$retour['error'] = $retour['message'] = 'OK';
		$retour['userDatas'] = $datasUser;
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

elseif ($action == 'loadAssigns') {
	try {
		$u = new Users($loginUser);
		$retour['idUser'] = $u->getUserInfos('id');
		$retour['projectsUser'] = $u->getUserProjects();
		$retour['error'] = $retour['message'] = 'OK';
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

//////////////////////////////////////// REBUILDER DES MY_TRUCS (DEV ONLY - from DEBUG PANEL) ////////////////////////////////

// Reconstruction de la liste des my_projects pour TOUS LES USERS dans TOUS LES PROJETS !!
elseif ($action == 'rebuildMyProjects') {
	if (!$_SESSION['user']->isDev()) die('{"error":"error","message":"Users teams rebuilder: Access denied"}');
	$l = new Liste();
	$l->getListe(TABLE_PROJECTS);
	$allProjs = $l->simplifyList('id');
	$l->getListe(TABLE_USERS);
	$allUsers = $l->simplifyList('id');
	$retour['message'] = '<b>MY_PROJECTS rebuilder</b><br />';

	foreach ($allUsers as $usrID=>$usr) {
		$u = new Users((int)$usrID);
		$usrProjs = Array();
		foreach ($allProjs as $proj) {
			$projTeam = json_decode($proj[Projects::PROJECT_EQUIPE]);
			if (!is_array($projTeam)) continue;
			if (!in_array($usrID, $projTeam)) continue;
			$usrProjs[] = (string)$proj[Projects::PROJECT_ID_PROJECT];
		}
		$u->setUserInfos(Users::USERS_MY_PROJECTS, json_encode($usrProjs));
		$u->save();
		$retour['message'] .= $u->getUserInfos('pseudo') .': '. json_encode($usrProjs).'<br />';
	}
	$retour['message'] .= '<b>DONE.</b>';
	$retour['error'] = 'OK';
	$retour['persistant'] = 'persist';
}

// Reconstruction de la liste des my_shots pour TOUS LES USERS dans TOUS LES PROJETS !!
elseif ($action == 'rebuildMyShots') {
	if (!$_SESSION['user']->isDev()) die('{"error":"error","message":"Users teams rebuilder: Access denied"}');
	$l = new Liste();
	$l->getListe(TABLE_SHOTS);
	$allShots = $l->simplifyList('id');
	$l->getListe(TABLE_USERS);
	$allUsers = $l->simplifyList('id');
	$retour['message'] = '<b>MY_SHOTS rebuilder</b><br />';

	foreach ($allUsers as $usrID=>$usr) {
		$u = new Users((int)$usrID);
		$usrShots = Array();
		foreach ($allShots as $shot) {
			$shotTeam = json_decode($shot[Shots::SHOT_TEAM]);
			if (!is_array($shotTeam)) continue;
			if (!in_array($usrID, $shotTeam)) continue;
			$usrShots[] = (string)$shot[Shots::SHOT_ID_SHOT];
		}
		$u->setUserInfos(Users::USERS_MY_SHOTS, json_encode($usrShots));
		$u->save();
		$retour['message'] .= $u->getUserInfos('pseudo') .': '. json_encode($usrShots).'<br />';
	}
	$retour['message'] .= '<b>DONE.</b>';
	$retour['error'] = 'OK';
	$retour['persistant'] = 'persist';
}

// Reconstruction de la liste des my_assets pour TOUS LES USERS dans TOUS LES PROJETS !!
elseif ($action == 'rebuildMyAssets') {
	if (!$_SESSION['user']->isDev()) die('{"error":"error","message":"Users teams rebuilder: Access denied"}');
	$l = new Liste();
	$l->getListe(TABLE_ASSETS);
	$allAssets = $l->simplifyList('id');
	$l->getListe(TABLE_USERS);
	$allUsers  = $l->simplifyList('id');
	$retour['message'] = '<b>MY_ASSETS rebuilder</b><br />';

	foreach ($allUsers as $usrID=>$usr) {
		$u = new Users((int)$usrID);
		$usrAssets = Array();
		foreach ($allAssets as $asset) {
			$assetTeam = json_decode($asset[Assets::ASSET_TEAM]);
			if (!is_array($assetTeam)) continue;
			if (!in_array($usrID, $assetTeam)) continue;
			$usrAssets[] = (string)$asset[Assets::ASSET_ID];
		}
		$u->setUserInfos(Users::USERS_MY_ASSETS, json_encode($usrAssets));
		$u->save();
		$retour['message'] .= $u->getUserInfos('pseudo') .': '. json_encode($usrAssets).'<br />';
	}
	$retour['message'] .= '<b>DONE.</b>';
	$retour['error'] = 'OK';
	$retour['persistant'] = 'persist';
}

// Reconstruction de la liste des my_tasks pour TOUS LES USERS dans TOUS LES PROJETS !!
//elseif ($action == 'rebuildMyTasks') {
//    echo 'my tasks ok';
//	if (!$_SESSION['user']->isDev()) die('{"error":"error","message":"Users teams rebuilder: Access denied"}');
//	$l = new Liste();
//	$l->getListe(TABLE_TASKS);
//	$allScenes = $l->simplifyList('id');
//	$l->getListe(TABLE_USERS);
//	$allUsers  = $l->simplifyList('id');
//	$retour['message'] = '<b>MY_TASKS rebuilder</b><br />';
//
//	foreach ($allUsers as $usrID=>$usr) {
//		$u = new Users((int)$usrID);
//		$usrScenes = Array();
//		foreach ($allScenes as $scene) {
//			$sceneTeam = json_decode($scene[Scenes::TEAM]);
//			if (!is_array($sceneTeam)) continue;
//			if (!in_array($usrID, $sceneTeam)) continue;
//			$usrScenes[] = (string)$scene[Scenes::ID_SCENE];
//		}
//		$u->setUserInfos(Users::USERS_MY_TASKS, json_encode($usrScenes));
//		$u->save();
//		$retour['message'] .= $u->getUserInfos('pseudo') .': '. json_encode($usrScenes).'<br />';
//	}
//	$retour['message'] .= '<b>DONE.</b>';
//	$retour['error'] = 'OK';
//	$retour['persistant'] = 'persist';
//}

// Purge de tous les my_trucs pour TOUS LES USERS dans TOUS LES PROJETS !!
elseif ($action == "purgeUsersMyItems") {
	if (!$_SESSION['user']->isSupervisor()) die('{"error":"error","message":"Users teams rebuilder: Access denied"}');
	try {
		$l = new Liste();
		$allUsers = $l->getListe(TABLE_USERS, 'id');
		$retour['message'] = '<b>PURGE USERS all My_ITEMS</b><br />';

		foreach($allUsers as $userID) {
			$removedProjects = Users::purge_user_projects($userID);
			$removedShots	 = Users::purge_user_shots($userID);
			$removedScenes	 = Users::purge_user_scenes($userID);
			$removedAssets	 = Users::purge_user_assets($userID);
			$u = new Users((int)$userID);
			$retour['message'] .= $u->getUserInfos(Users::USERS_PSEUDO).": removed $removedProjects projects, $removedShots shots, $removedScenes scenes, $removedAssets assets.<br />";
		}
		$retour['message'] .= '<b>DONE.</b>';
		$retour['error'] = 'OK';
		if (@$fromDebug == 'true')
			$retour['persistant'] = 'persist';
	}
	catch(Exception $e) {$retour['error'] = 'error'; $retour['message'] = $e->getMessage(); }
}

echo json_encode($retour);
?>