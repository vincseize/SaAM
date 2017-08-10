<?php

/*	SaAM A.P.I.
 *	lrds © 2013
 *
 *********************** API PUBLISHED **************************
 *
 *  Liste des requêtes possibles :
 *
 *		@action "add_shot_published"
 *			@param needed : "id_shot"			=> (int) ID de shot
 *			@param needed : "dept"				=> (int) ID de département
 *			@param needed : "file_published"	=> (file) Fichier à uploader
 *
 *		@action "add_asset_published"
 *			@param needed : "name_asset"		=> (string) filename de l'asset
 *			@param needed : "dept"				=> (int) ID de département
 *			@param needed : "file_published"	=> (file) Fichier à uploader
 *
 *		@action "add_scene_published"
 *			@param needed : "id_scene"			=> (int) ID de scene
 *			@param needed : "dept"				=> (int) ID de département
 *			@param needed : "file_published"	=> (file) Fichier à uploader
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
$messageAdd = "but this action ('$action') is unknown! ";


if ($action == 'add_shot_published') {
	$messageAdd = "with action : '".strtoupper($action)."', ";
	if (!isset($id_shot))
		$messageAdd .= "but the shot ID is missing! ";
	elseif (!isset($dept))
		$messageAdd .= "but the department ID is missing! ";
	elseif (!isset($file_published))
		$messageAdd .= "but the file is missing!";
	else {
		// @TODO : API "published" -> add_shot_published
		$reponse['error'] = 0;
		$messageAdd .= "and everything went fine ! (shot #$id_shot, dept #$dept : TODO)";
	}
}

if ($action == 'add_asset_published') {
	$messageAdd = "with action '".strtoupper($action)."', ";
	if (!isset($name_asset))
		$messageAdd .= "but the asset's name is missing! ";
	elseif (!isset($dept))
		$messageAdd .= "but the department ID is missing! ";
	elseif (!isset($file_published))
		$messageAdd .= "but the file is missing!";
	else {
		// @TODO : API "published" -> add_asset_published
		$reponse['error'] = 0;
		$messageAdd .= "and everything went fine ! (asset '$name_asset', dept #$dept : TODO)";
	}
}

if ($action == 'add_scene_published') {
	$messageAdd = "with action '".strtoupper($action)."', ";
	if (!isset($id_scene))
		$messageAdd .= "but the scene ID is missing! ";
	elseif (!isset($dept))
		$messageAdd .= "but the department ID is missing! ";
	elseif (!isset($file_published))
		$messageAdd .= "but the file is missing!";
	else {
		// @TODO : API "published" -> add_scene_published
		$reponse['error'] = 0;
		$messageAdd .= "and everything went fine ! (scene #$id_scene, dept #$dept : TODO)";
	}
}

$reponse['message'] .= $messageAdd ;
echo json_encode($reponse);

?>