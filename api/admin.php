<?php

/*	SaAM A.P.I.
 *	lrds © 2013
 *
 *********************** API PUBLISHED **************************
 *
 *  Liste des requêtes possibles :
 *
 *		@action "purge_sessions"
 *			@param needed : None
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

if (!$USER->isRoot()){
	$reponse['message'] .= "but you are not allowed to access this API file (Root users only).";
	die(json_encode($reponse));
}

if (!isset($action)) {
	$reponse["message"] .= "but you should choose an action!";
	die (json_encode($reponse));
}

$reponse['action'] = $action;
$messageAdd = "but this action ('$action') is unknown! ";


if ($action == 'purge_sessions') {
	$messageAdd = "with action : '".strtoupper($action)."', ";
	$sessFiles = glob(INSTALL_PATH.FOLDER_SESSIONS.'*');
	$cntSF = count($sessFiles);
	foreach($sessFiles as $sessFile)
		@unlink($sessFile);
	$reponse['error'] = 0;
	$messageAdd .= "and everything went fine ! ($cntSF session files deleted)";

}

$reponse['message'] .= $messageAdd ;
echo json_encode($reponse);

?>