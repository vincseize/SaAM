<?php
@session_start(); // 2 lignes à placer toujours en haut du code des pages
require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

extract($_POST);

$retour['error'] = 'error';
$retour['message'] = 'action undefined';

try {
	$acl = new ACL(@$_SESSION['user']);

	// Ajout de global tag
	if ($action == 'addGlobalTag') {
		try {
			if (!$acl->check('VIEW_TOOLS_BTNS_ADMIN')) { $retour['message'] = 'Permission denied.'; }
			else {
				if (strlen($tagName) > 3) {
					$SaAMinfo = new Infos(TABLE_CONFIG);
					$SaAMinfo->loadInfos('version', SAAM_VERSION);
					$tags = json_decode($SaAMinfo->getInfo('global_tags'));
					if (!is_array($tags)) $tags = Array();
					$tags[] = trim(urldecode($tagName));
					sort($tags);
					$SaAMinfo->addInfo('global_tags', json_encode($tags));
					$SaAMinfo->save();
					$retour['error']	= 'OK';
					$retour['message']	= 'Tag "'.urldecode($tagName).'" added.' ;
				}
				else $retour['message'] = 'Tag name too short.';
			}
		}
		catch (Exception $e) {
			$retour['message'] = $e->getMessage();
		}
	}
	// Suppression de global tag
	if ($action == 'delGlobalTag') {
		try {
			if (!$acl->check('VIEW_TOOLS_BTNS_ADMIN')) { $retour['message'] = 'Permission denied.'; }
			else {
				$SaAMinfo = new Infos(TABLE_CONFIG);
				$SaAMinfo->loadInfos('version', SAAM_VERSION);
				$tags = json_decode($SaAMinfo->getInfo('global_tags'));
				if(($key = array_search(urldecode($tagName), $tags)) !== false)
					unset($tags[$key]);
				sort($tags);
				$SaAMinfo->addInfo('global_tags', json_encode($tags));
				$SaAMinfo->save();
				$retour['error']	= 'OK';
				$retour['message']	= 'Tag "'.urldecode($tagName).'" removed.';
			}
		}
		catch (Exception $e) {
			$retour['message'] = $e->getMessage();
		}
	}


	// Ajout de user tag
	if ($action == 'addUserTag') {
		try {
			if (strlen($tagName) > 3) {
				$userTags = $_SESSION['user']->getUserTags();
				if (!is_array($userTags)) $userTags = Array();
				$userTags[] = trim(urldecode($tagName));
				sort($userTags);
				$_SESSION['user']->setUserInfos(Users::USERS_MY_TAGS, json_encode($userTags));
				$_SESSION['user']->save();
				$retour['error']	= 'OK';
				$retour['message']	= 'Tag "'.urldecode($tagName).'" added.' ;
			}
			else $retour['message'] = 'Tag name too short.';
		}
		catch (Exception $e) {
			$retour['message'] = $e->getMessage();
		}
	}
	// Suppression de user tag
	if ($action == 'delUserTag') {
		try {
			$userTags = $_SESSION['user']->getUserTags();
			if (!is_array($userTags)) $userTags = Array();
			if(($key = array_search(urldecode($tagName), $userTags)) !== false)
				unset($userTags[$key]);
			sort($userTags);
			$_SESSION['user']->setUserInfos(Users::USERS_MY_TAGS, json_encode($userTags));
			$_SESSION['user']->save();
			$retour['error']	= 'OK';
			$retour['message']	= 'Tag "'.urldecode($tagName).'" removed.';
		}
		catch (Exception $e) {
			$retour['message'] = $e->getMessage();
		}
	}

	// Partage de user tag
	if ($action == 'shareUserTag') {
		try {
			if (strlen($tagName) > 3) {
				$uTs = new Users((int)$userIdToShare);
				$userTags = $uTs->getUserTags();
				if (!is_array($userTags)) $userTags = Array();
				if (!in_array(urldecode($tagName), $userTags)) {
					$userTags[] = trim(urldecode($tagName));
					sort($userTags);
					$uTs->setUserInfos(Users::USERS_MY_TAGS, json_encode($userTags));
					$uTs->save();
					$retour['error']	= 'OK';
					$retour['message']	= 'Tag "'.urldecode($tagName).'" shared with '.$userToShare.'.';
				}
				else $retour['message'] = 'Tag already shared with '.$userToShare.'.';
			}
			else $retour['message'] = 'Tag name too short.';
		}
		catch (Exception $e) {
			$retour['message'] = $e->getMessage();
		}
	}
}
catch (Exception $e) { $retour['message'] = $e->getMessage(); }

echo json_encode($retour);


?>
