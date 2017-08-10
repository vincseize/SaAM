<?php
session_start();

/*	SaAM A.P.I.
 *	lrds © 2013
 *
 ****************************************** AUTHENTIFICATION API *************************************************
 *
 * Paramètres attendus : (POST)
 * -----type--------requestName---------------------description--------------------------------------------------
 *		(string)	'user'						=>  Simplement : urlEncoded
 *		(string)	'pass'						=>  Encodé en  : MD5
 *
 *
 * Réponses possibles de l'API : (JSON)
 * -----type--------example--------------------------------------------------------------------------------------
 *		(string)	{"error":0,"message":"Welcome message","data":[an array of datas]}
 * or
 *		(string)	{"error":1,"message":"Access denied.","data":[]}
 *
 *
 *************************************** POUR ENCODER LE PASSWORD ***********************************************
 * Vous aurez besoin deu grain de sel suivant :		G:niUk5!1|WQ
 ****************************************************************************************************************/

header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

define("INSTALL_PATH", substr( dirname(__FILE__), 0, -3) );

$dontTouchSSID = true;

require_once (INSTALL_PATH.'inc/initInclude.php');
require_once ('common.inc');
require_once ('autoload.php');
require_once ('PDOinit.php');

$reponse = Array(
	"error" => 1,
	"message" => "Access denied."
);

extract($_POST);

$Auth = new Connecting($bdd);

if (isset($user) && $user!='' && isset($pass) && $pass!='') {							// Si les POST "user" et "pass" sont présent
	try {
		if ($Auth->connect($user, $pass, true) == false) {
			$reponse['message'] .= " User '$user' unknown, or password not correct. Please try again.";
			die(json_encode($reponse));
		}
		else $USER = new Users($user);
	}
	catch(Exception $e) {
		$reponse['message'] .= "ERROR: ".$e->getMessage();
		die(json_encode($reponse));
	}
}
else {
	$reponse['message'] .= " Not enought information to be auth.";
	die(json_encode($reponse));
}


$u = new Users((string)$user);
$userNames	= $u->getUserInfos(Users::USERS_PRENOM) . ' ' . $u->getUserInfos(Users::USERS_NOM);

$reponse["message"] = "API : welcome, $userNames ! You requested ";

?>