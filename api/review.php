<?php

/*	SaAM A.P.I.
 *	lrds © 2013
 *
 *********************** API REVIEWS **************************
 *
 *  Liste des requêtes possibles :
 *
 *		@action "add_asset_review_request"
 *			@param needed : "id_project"		=> (int) Project ID for the asset
 *			@param needed : "name_asset"		=> (string) filename de l'asset
 *			@param needed : "date"				=> (int) timeStamp pour deadline de review
 *			@param needed : "comment"			=> (string) Commentaire de la review pour le superviseur
 *
 *		@action "valid_asset_review"
 *			@param needed : "id_project"		=> (int) Project ID for the asset
 *			@param needed : "name_asset"		=> (string) filename de l'asset
 *
 *		@action "add_scene_review_request"
 *			@param needed : "id_scene"			=> (int) ID de scene
 *			@param needed : "date"				=> (int) timeStamp pour deadline de review
 *			@param needed : "comment"			=> (string) Commentaire de la review pour le superviseur
 *
 *		@action "valid_scene_review"
 *			@param needed : "id_scene"			=> (int) Project ID for the asset
 *
 *
 * Retour JSON attendu :
 *		"error"			=> (int) 1 si une erreur est survenue, 0 si tout s'est bien passé
 *		"message"		=> (string) Message de bienvenue, contenant le nom prénom de l'user pour vérif si c'est le bon, et retour "verbose" de l'action
 *		"action"		=> (string) Action demandée pour retour d'info (debug ou display)
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
$messageAdd = "but this action is unknown! ";


try {
	$ACL = new ACL($USER);

	if ($action == 'add_asset_review_request') {
		$messageAdd = "with action '".strtoupper($action)."', ";
		if (!isset($id_project))
			$messageAdd .= "but the project ID is missing! ";
		if (!isset($name_asset))
			$messageAdd .= "but the asset's name is missing! ";
		elseif (!isset($comment))
			$messageAdd .= "but the comment is missing! ";
		elseif (strlen($comment) < 5)
			$messageAdd .= "but your comment too short! ";
		else {
			$asset		= new Assets($id_project, (string)$name_asset);
			$pathAsset  = preg_replace('#\./#', '', $asset->getPath());
			$idAsset	= $asset->getIDasset();
			$review		= $asset->getReview();
			if ($ACL->check('ASSETS_REVIEW_ASK', 'asset:'.$idAsset)) {
				if ($review == '') {
					$asset->setReview($comment);
					$asset->save();
					Dailies::add_dailies_entry($id_project, Dailies::GROUP_ASSET, Dailies::TYPE_ASSET_REVIEW_REQUEST, '{"pathAsset":"'.$pathAsset.'","nameAsset":"'.$name_asset.'","comment":"'.$comment.'"}');
					$reponse['error'] = 0;
					$messageAdd .= "and everything went fine ! (asset '$name_asset', review set with comment: '$comment')";
				}
				else $messageAdd .= 'but there is already a review requested for this asset.';
			}
			else $messageAdd .= 'but you don\'t have access to this asset.';
		}
	}

	if ($action == 'valid_asset_review') {
		$messageAdd = "with action '".strtoupper($action)."', ";
		if (!isset($id_project))
			$messageAdd .= "but the project ID is missing! ";
		if (!isset($name_asset))
			$messageAdd .= "but the asset's name is missing! ";
		else {
			$asset		= new Assets($id_project, (string)$name_asset);
			$pathAsset  = preg_replace('#\./#', '', $asset->getPath());
			$idAsset	= $asset->getIDasset();
			$review		= $asset->getReview();
			if ($ACL->check('ASSETS_REVIEW_VALID', 'asset:'.$idAsset)) {
				if ($review != '') {
					$asset->setReview(true);
					$asset->save();
					Dailies::add_dailies_entry($id_project, Dailies::GROUP_ASSET, Dailies::TYPE_ASSET_REVIEW_VALID, '{"pathAsset":"'.$pathAsset.'","nameAsset":"'.$name_asset.'"}');
					$reponse['error'] = 0;
					$messageAdd .= "and everything went fine ! (asset '$name_asset', review validated)";
				}
				else $messageAdd .= 'but there is no review to validate, for this asset.';
			}
			else $messageAdd .= 'but you don\'t have access to this asset.';
		}
	}

	if ($action == 'add_scene_review_request') {
		$messageAdd = "with action '".strtoupper($action)."', ";
		if (!isset($id_scene))
			$messageAdd .= "but the scene ID is missing! ";
		elseif (!isset($comment))
			$messageAdd .= "but the comment is missing! ";
		elseif (strlen($comment) < 5)
			$messageAdd .= "but your comment too short! ";
		else {
			$scene	= new Scenes((int)$id_scene);
			$scene_title = $scene->getSceneInfos(Scenes::TITLE);
			$review	= $scene->getReview();
			if ($ACL->check('SCENES_REVIEW_ASK', 'scene:'.$id_scene)) {
				if ($review == '') {
					$scene->setReview($comment);
					$scene->save();
					Dailies::add_dailies_entry($id_project, Dailies::GROUP_SCENE, Dailies::TYPE_SCENE_REVIEW_REQUEST, '{"sceneID":"'.$id_scene.'","comment":"'.$comment.'"}');
					$reponse['error'] = 0;
					$messageAdd .= "and everything went fine ! (scene '$scene_title', review set with comment: '$comment')";
				}
				else $messageAdd .= 'but there is already a review requested for this scene.';
			}
			else $messageAdd .= 'but you don\'t have access to this scene.';
		}
	}

	if ($action == 'valid_scene_review') {
		$messageAdd = "with action '".strtoupper($action)."', ";
		if (!isset($id_scene))
			$messageAdd .= "but the scene ID is missing! ";
		else {
			$scene	= new Scenes((int)$id_scene);
			$scene_title = $scene->getSceneInfos(Scenes::TITLE);
			$review	= $scene->getReview();
			if ($ACL->check('SCENES_VALID_REVIEW', 'scene:'.$id_scene)) {
				if ($review != '') {
					$scene->setReview(true);
					$scene->save();
					Dailies::add_dailies_entry($id_project, Dailies::GROUP_SCENE, Dailies::TYPE_SCENE_REVIEW_VALID, '{"sceneID":"'.$id_scene.'"}');
					$reponse['error'] = 0;
					$messageAdd .= "and everything went fine ! (scene '$scene_title', review validated)";
				}
				else $messageAdd .= 'but there is no review to validate, for this scene.';
			}
			else $messageAdd .= 'but you don\'t have access to this scene.';
		}
	}

}
catch(Exception $e) {
	$messageAdd = 'ERROR: '.$e->getMessage();
}


$reponse['message'] .= $messageAdd ;
echo json_encode($reponse);

?>