<?php

/*	SaAM A.P.I.
 *	lrds © 2013
 *
 *********************** API MESSAGES **************************
 *
 *  Liste des requêtes possibles :
 *
 *		@action "add_shot_message"
 *			@param needed : "id_shot"			=> (int) ID de shot
 *			@param needed : "dept"				=> (int) ID de département
 *			@param needed : "comment"			=> (string) Commentaire à envoyer
 *
 *		@action "add_asset_message"
 *			@param needed : "id_project"		=> (int) Project ID for the asset
 *			@param needed : "name_asset"		=> (string) filename de l'asset
 *			@param needed : "dept"				=> (int) ID de département
 *			@param needed : "comment"			=> (string) Commentaire à envoyer
 *
 *		@action "add_scene_message"
 *			@param needed : "id_scene"			=> (int) ID de scene
 *			@param needed : "dept"				=> (int) ID de département
 *			@param needed : "comment"			=> (string) Commentaire à envoyer
 *
 * Retour JSON attendu :
 *		"error"			=> (int) 1 si une erreur est survenue, 0 si tout s'est bien passé
 *		"message"		=> (string) Message de bienvenue, contenant le nom prénom de l'user pour vérif si c'est le bon, et retour "verbose" de l'action
 *		"action"		=> (string) Action demandée pour retour d'info (debug ou display)
 *
 */

require_once ('initAPI.php');
require_once ('directories.php');
require_once ('mails_fcts.php');

$reponse["message"] .= '"'.basename(__FILE__) . '", ' ;


if (!isset($action)) {
	$reponse["message"] .= "but you should choose an action!";
	die (json_encode($reponse));
}

$reponse['action'] = $action;
$messageAdd = "but this action ('$action') is unknown! ";

try {
	$ACL = new ACL($USER);

	if ($action == 'add_shot_message') {
		$messageAdd = "with action : '".strtoupper($action)."', ";
		if (!isset($id_shot))
			$messageAdd .= "but the shot ID is missing! ";
		elseif (!isset($dept))
			$messageAdd .= "but the department ID is missing! ";
		elseif (!isset($comment))
			$messageAdd .= "but your comment is missing! ";
		elseif (strlen($comment) < 5)
			$messageAdd .= "but your comment too short! ";
		else {
			if ($ACL->check('SHOTS_MESSAGE', 'shot:'.$id_shot)) {
				$sh = new Shots($id_shot);
				if ($sh->getLastRetake($dept) == false)
					$messageAdd .= 'but this shot doesn\'t have an active published for this dept!';
				elseif ($sh->isValidLastRetake($dept))
					$messageAdd .= 'but the last published is validated. Please send a new publish before comment it.';
				elseif ($sh->isLocked())
					$messageAdd .= 'but this shot is locked.';
				else {
					$projID = $sh->getShotInfos(Shots::SHOT_ID_PROJECT);
					$cm = new Comments('retake');
					$cm->initNewCommRetake($id_shot, $dept);
					$cm->addText(stripslashes(urldecode($comment)));
					$cm->save();
					try {
						$d = new Infos(TABLE_DEPTS);
						$d->loadInfos('id', $dept);
						$deptName = $d->getInfo('label');
					} catch (Exception $e) { $deptName = $dept; }
					Dailies::add_dailies_entry($projID, Dailies::GROUP_SHOT, Dailies::TYPE_SHOT_NEW_MESSAGE, '{"idShot":"'.$id_shot.'","dept":"'.$deptName.'","txtMess":"'.urlencode($comment).'"}');
					$reponse['error'] = 0;
					$messageAdd .= "and everything went fine ! (shot #$id_shot, dept #$dept : comment added.)";
				}
			}
			else $messageAdd .= 'but you don\'t have access to this shot.';
		}
	}

	if ($action == 'add_asset_message') {
		$messageAdd = "with action '".strtoupper($action)."', ";
		if (!isset($id_project))
			$messageAdd .= "but the project ID is missing! ";
		if (!isset($name_asset))
			$messageAdd .= "but the asset's name is missing! ";
		elseif (!isset($dept))
			$messageAdd .= "but the department ID is missing! ";
		elseif (!isset($comment))
			$messageAdd .= "but your comment is missing! ";
		elseif (strlen($comment) < 5)
			$messageAdd .= "but your comment too short! ";
		else {
			$asset = new Assets($id_project, (string)$name_asset);
			$asset->getDirRetakes($id_project, $dept);
			$idAsset = $asset->getIDasset();
			if ($ACL->check('ASSETS_MESSAGE', 'asset:'.$idAsset)) {
				if ($asset->getLastRetake() == false)
					$messageAdd .= 'but this asset doesn\'t have an active published for this dept!';
				elseif ($asset->isValidLastRetake($id_project, $dept))
					$messageAdd .= 'but the last published is validated. Please send a new publish before comment it.';
				else {
					$cm = new Comments('retake_asset');
					$cm->initNewCommRetake_asset($idAsset, $id_project, $dept, $reponse);
					$cm->addText(stripslashes(urldecode($comment)));
					$cm->save();
					$pathAsset = preg_replace('#\./#', '', $asset->getPath());
					Dailies::add_dailies_entry($id_project, Dailies::GROUP_ASSET, Dailies::TYPE_ASSET_NEW_MESSAGE, '{"pathAsset":"'.$pathAsset.'","nameAsset":"'.$name_asset.'","deptID":"'.$dept.'","txtMess":"'.urlencode($comment).'"}');
					$asset->save();
					$reponse['error'] = 0;
					$messageAdd .= "and everything went fine ! (asset '$name_asset', dept #$dept : comment added.)";
				}
			}
			else $messageAdd .= 'but you don\'t have access to this asset.';
		}
	}

	if ($action == 'add_scene_message') {
		$messageAdd = "with action '".strtoupper($action)."', ";
		if (!isset($id_scene))
			$messageAdd .= "but the scene ID is missing! ";
		elseif (!isset($dept))
			$messageAdd .= "but the department ID is missing! ";
		elseif (!isset($comment))
			$messageAdd .= "but your comment is missing! ";
		elseif (strlen($comment) < 5)
			$messageAdd .= "but your comment too short! ";
		else {
			// @TODO : API "message" -> add_scene_message
			$reponse['error'] = 0;
			$messageAdd .= "and everything went fine ! (scene #$id_scene, dept #$dept : NOT YET SUPPORTED -TODO-)";
		}
	}

}
catch(Exception $e) {
	$messageAdd = 'ERROR: '.$e->getMessage();
}

$reponse['message'] .= $messageAdd ;
echo json_encode($reponse);

?>