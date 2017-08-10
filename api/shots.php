<?php

/*	SaAM A.P.I.
 *	lrds © 2013
 *
 *********************** API SHOTS **************************
 *
 * Retour JSON avec au minimum :
 *		"error"			=> (int) 1 si une erreur est survenue, 0 si tout s'est bien passé
 *		"message"		=> (string) Message de bienvenue, contenant le nom prénom de l'user pour vérif si c'est le bon, et retour "verbose" de l'action
 *		"action"		=> (string) Action demandée pour retour d'info (debug ou display)
 *
 * Liste des requêtes possibles :
 *
 *		@action "get_shots_by_project"
 *			@param needed : "id_project"		=> (int) ID de projet
 *			@return :		"id_project"			=> (int) ID du projet demandé pour retour d'info (debug ou display)
 *							"data[]"				=> (array) données
 *								"data['nbResult']"		=> (int) nombre de résulat
 *								"data['shots']"			=> (array) liste des shots (si au moins un shot)
 *
 *
 *		@action "get_shots_by_tag"
 *			@param needed : "tag_name"			=> (string) NOM de Tag
 *			@return :		"tag_name"				=> (string) nom du tag demandé pour retour d'info (debug ou display)
 *							"data[]"				=> (array) données
 *								"data['nbResult']"		=> (int) nombre de résulat
 *								"data['shots']"			=> (array) liste des shots (si au moins un shot)
 *
 *		@action "get_shot_by_id"
 *			@param needed : "id_shot"			=> (int) ID de shot
 *			@return :		"id_shot"				=> (int) ID du shot demandé pour retour d'info (debug ou display)
 *							"data[]"				=> (array) données
 *								"data['nbResult']"		=> (int) nombre de résulat
 *								"data['shot']"			=> (array) liste des infos du shot (si trouvé)
 *
 *		@action "get_shots_by_user"
 *			@param needed : "from_user"			=> (string or int) LOGIN ou ID de l'user
 *			@return :		"from_user"				=> (string or int) LOGIN ou ID de l'user demandé pour retour d'info (debug ou display)
 *							"data[]"				=> (array) données
 *								"data['nbResult']"		=> (int) nombre de résulat
 *								"data['shots']"			=> (array) liste des shots (si au moins un shot)
 *
 *
 *		@TODO : continuer la liste des requêtes à utiliser
 *
 */

require_once ('initAPI.php');
require_once ('directories.php');

$reponse["message"] .= '"'.basename(__FILE__) . '", ' ;


if (!isset($action)) {
	$reponse["message"] .= "but you should choose an action!";
	die (json_encode($reponse));
}

$reponse['action'] = $action;
$messageAdd = "but this action ('$action') is unknown! ";


try {
	$ACL = new ACL($USER);

	if ($action == 'get_shots_by_project') {
		$messageAdd = "with action '".strtoupper($action)."', ";
		if (isset($id_project)) {
			try {
				if ($ACL->check('SHOTS_ADMIN')) {
					$p = new Projects((int)$id_project);
					$shotList = $p->getShots();
					$reponse['id_project'] = $id_project;
					$reponse['data']['nbResult'] = count($shotList);
					$reponse['data']['shots'] = $shotList;
					$reponse['error']	= 0;
					$messageAdd  .= "and everything went fine! The result is available into: response['data']";
				}
				else $messageAdd .= 'but you don\'t have access to the project\'s shots list.';
			}
			catch(Exception $e) { $messageAdd .= "and something's wrong:\n\n".$e->getMessage(); }
		}
		else $messageAdd .= "but the project ID is missing! ";
	}


	if ($action == 'get_shots_by_tag') {
		$messageAdd = "with action '".strtoupper($action)."', ";
		if (isset($tag_name)) {
			try {
				$shotList = Shots::getShotsByTag($tag_name);
				$reponse['tag_name'] = $tag_name;
				if (!is_array($shotList))
					$reponse['data']['nbResult'] = 0;
				else {
					$reponse['data']['nbResult'] = count($shotList);
					$reponse['data']['shots'] = $shotList;
				}
				$reponse['error']	= 0;
				$messageAdd  .= "and everything went fine! The result is available into: response['data']";
			}
			catch(Exception $e) { $messageAdd .= "and something's wrong:\n\n".$e->getMessage(); }
		}
		else $messageAdd .= "but the tag name is missing! ";
	}


	if ($action == 'get_shot_by_id') {
		$messageAdd = "with action '".strtoupper($action)."', ";
		if (isset($id_shot)) {
			try {
				if ($ACL->check('SHOTS_MESSAGE', 'shot:'.$id_shot)) {
					$shot = new Shots($id_shot);
					$reponse['id_shot'] = $id_shot;
					$shotInfo = $shot->getShotInfos();
					$reponse['data']['nbResult'] = 1;
					$reponse['data']['shot'] = $shotInfo;
					$reponse['error']	= 0;
					$messageAdd  .= "and everything went fine! The result is available into: response['data']";
				}
				else $messageAdd .= 'but you don\'t have access to this shot.';
			}
			catch(Exception $e) { $messageAdd .= "and something's wrong:\n\n".$e->getMessage(); }
		}
		else $messageAdd .= "but the shot ID is missing! ";
	}


	if ($action == 'get_shots_by_user') {
		$messageAdd = "with action '".strtoupper($action)."', ";
		if (isset($from_user)) {
			try {
				if ((int)$from_user != 0)
					$from_user = (int)$from_user;
				$fromU = new Users($from_user);
				$reponse['from_user'] = $from_user;
				$UshotList = $fromU->getUserShots();
				if (!is_array($UshotList))
					$reponse['data']['nbResult'] = 0;
				else {
					$shotList = array();
					foreach ($UshotList as $shId) {
						$shot = new Shots($shId);
						$shotList[] = $shot->getShotInfos();
					}
					$reponse['data']['nbResult'] = count($shotList);
					$reponse['data']['shots'] = $shotList;
				}
				$reponse['error']	= 0;
				$messageAdd  .= "and everything went fine! The result is available into: response['data']";
			}
			catch(Exception $e) { $messageAdd .= "and something's wrong:\n\n".$e->getMessage(); }
		}
		else $messageAdd .= "but the user login or ID is missing! ";
	}

}
catch(Exception $e) {
	$messageAdd = 'ERROR: '.$e->getMessage();
}


$reponse['message'] .= $messageAdd ;
echo json_encode($reponse);

?>