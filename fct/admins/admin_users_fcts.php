<?php
require_once ('directories.php' );


function add_user_recette ($loginUser) {
	$userID = $_SESSION["user"]->getUserInfos('id');
	$now = time();
	$array_status = array("1", "2", "3","4","5","6","7");
	$status = $array_status[array_rand($array_status)];

	$competences = array();
	$competences_list = $_SESSION['CONFIG']['AV_COMPETENCES'];
	$n_rand = rand(1,count($competences_list)-1);
	$comp_rand_array = array_slice($competences_list, 1, $n_rand);
	foreach ($comp_rand_array as $dd)
		array_push ($competences,$dd);

	$mail = 'john.doe@lrds.net';
	$randPseudo = rand(0, 99);
	$pseudo = 'ZJunior 0'.$randPseudo;

	$user = new Users();
	$user->setCreator( $userID );
	$user->setLogin( $loginUser );
	$user->setPassword( 'jdoe' );
	$user->setStatus( $status );
	$user->setPseudo( $pseudo );
	$user->setNom( 'DOE' );
	$user->setPrenom( 'John' );
	$user->setMail( $mail );
	$user->setCompetences( json_encode($competences) );
	$user->setLang( 'fr' );
	$user->setTheme( 'dark' );
	$user->setLastCon( $now );
	$user->setLastAction( $now );
	$user->addProjectToUser(1);
	$user->save();
}

function createUserFolder_recette ($loginUser) {
	if ($loginUser=='') { throw new Exception('create user folder failed (missing $loginUser)'); return; }
	// crée dossier du projet
	$ID = Liste::getMAx(TABLE_USERS,'id');
	$dirUser = $ID.'_'.$loginUser;
	if (!makeDataDir_user($dirUser))
		throw new Exception('create user folder failed.');
	add_vignette_user($dirUser);
}



///////////////////////////////////////////////////////////////////////////////////////////////////////


function add_user ($datas) {
	if (!is_array($datas)) { throw new Exception('create user failed ($datas not an array)'); return false; }
	if (!isset($datas[Users::USERS_LOGIN])	  || $datas[Users::USERS_LOGIN]	   == '') { throw new Exception('create user failed (missing login)');	  return false; }
	if (!isset($datas[Users::USERS_PASSWORD]) || $datas[Users::USERS_PASSWORD] == '') { throw new Exception('create user failed (missing password)'); return false; }
	if (!isset($datas[Users::USERS_MAIL])	  || $datas[Users::USERS_MAIL]	   == '') { throw new Exception('create user failed (missing email)');	  return false; }
	if (!isset($datas[Users::USERS_PSEUDO])   || $datas[Users::USERS_PSEUDO]   == '') { throw new Exception('create user failed (missing pseudo)');	  return false; }

	$IDCreator = $_SESSION["user"]->getUserInfos('id');
	$u = new Users();
	foreach($datas as $row => $val) {
		$u->setValue($row, stripslashes(urldecode($val)));
	}
	$u->setLang(LANG_DEFAULT);
	$u->setTheme(THEME_DEFAULT);
	$u->setUserInfos(Users::USERS_DECONX_TIME, DECONX_TIME_DEFAULT);
	$u->addProjectToUser(1);
	$u->setCreator($IDCreator);
	$u->save();
	return true;
}

// Crée le dossier d'un user et récupère la vignette uploadée
function createUserFolder ($loginUser, $vignetteName=false) {
	if ($loginUser=='') { throw new Exception('create user folder failed (missing $loginUser).'); return; }
	// crée dossier du projet
	$ID = Liste::getMAx(TABLE_USERS,'id');
	$dirUser = $ID.'_'.$loginUser;
	if (!makeDataDir_user($dirUser))
		throw new Exception('create user folder failed.');
	if ($vignetteName)
		move_uploaded_vignette($dirUser, $vignetteName);
}


// récupère la dernière vignette uploadée dans /temp, pour la mettre à la racine du dossier de l'user
function move_uploaded_vignette ($dirUser, $vignetteName) {
	if (!$dirUser && $dirUser == '')			{ throw new Exception('missing project title!'); return; }
	if (!$vignetteName && $vignetteName == '')	{ throw new Exception('missing vignette name!'); return; }
	if (!is_dir(INSTALL_PATH . FOLDER_DATA_USER . $dirUser))
		mkdir(INSTALL_PATH . FOLDER_DATA_USER . $dirUser);
	@unlink(INSTALL_PATH . FOLDER_DATA_USER . $dirUser . '/vignette.jpg');
	@unlink(INSTALL_PATH . FOLDER_DATA_USER . $dirUser . '/vignette.png');
	@unlink(INSTALL_PATH . FOLDER_DATA_USER . $dirUser . '/vignette.gif');
	$tempVignette = INSTALL_PATH .'temp/uploads/vignettes/'.$vignetteName;
	$vNameArr = explode('.', $vignetteName);
	$vExt = $vNameArr[count($vNameArr)-1];
	$destVignette = INSTALL_PATH . FOLDER_DATA_USER . $dirUser . '/vignette.'.$vExt ;
	if (!is_file($tempVignette)) { throw new Exception('missing temp vignette file !'); return; }
	if (!copy($tempVignette, $destVignette)) { throw new Exception('unable to copy vignette file to: '.$destVignette.'!'); return; }
	if (!unlink($tempVignette)) { throw new Exception('unable to delete temp vignette!'); }
}


// Modifie les infos d'un utilisateur
function update_user($IDuser=false, $arrValues=false) {
	if (!$IDuser && $IDuser == '')	{ throw new Exception('Update_user: missing ID of the user to update!'); return; }
	if (!is_array($arrValues))		{ throw new Exception('Update_user: missing values! (or not an array)'); return; }
	try {
		$u = new Users((int)$IDuser);
		$oldDir = INSTALL_PATH.FOLDER_DATA_USER . $IDuser.'_'.$u->getUserInfos(Users::USERS_LOGIN);
		foreach($arrValues as $row => $val) {
			if ($u->row_must_be_array($row))
				$val = json_decode (stripslashes(urldecode($val)));
			$u->setValue($row, $val);
			if ($row == Users::USERS_LOGIN) {
				$newDir = INSTALL_PATH.FOLDER_DATA_USER . $IDuser.'_'.$val;
				@rename($oldDir, $newDir);
			}
		}
		$u->save();
	}
	catch (Exception $e) { throw new Exception('Update_user : '.$e->getMessage()); return; }
}

// Modofie les assignations PROJECTS d'un user
function assign_user_to_projects ($idUser=false, $projects=false) {
	if (!$idUser)				{ throw new Exception('assign_user_to_projects: missing $idUser!'); return; }
	if (!is_array($projects))	{ throw new Exception('assign_user_to_projects: $projects not an array!'); return; }
	try {
		$u = new Users((int)$idUser);
		$u->setMyProjects($projects);
		$u->save();
		foreach($projects as $pID) {
			$p = new Projects((int)$pID);
			$p->purgeEquipe();
			$p->save();
		}
	}
	catch (Exception $e) { throw new Exception('assign_user_to_projects: '.$e->getMessage()); return; }
}

// Supprime un utilisateur
function delete_user ($id, $userlogin) {
	$adminLogin = $_SESSION['user']->getUserInfos('login');
	$user = new Users($adminLogin);		// Pour connecter en tant qu'admin (vérif, sinon erreur, pas le droit)
	$user->delete($id);					// id de l'user à supprimer !!!
	$directory = INSTALL_PATH . FOLDER_DATA_USER . $id.'_'.$userlogin ;
	if (file_exists($directory)) {
		if (!rmDir_R($directory))
			throw new Exception('delete folder '.$id.'_'.$userlogin.' failed, check arbo.');
	}
}


?>