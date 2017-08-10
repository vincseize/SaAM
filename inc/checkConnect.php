<?php
@session_start();
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date dans le passé

@define("INSTALL_PATH", substr( dirname(__FILE__), 0, -3) );
mb_internal_encoding('UTF-8');
$errAuthMsg = $lastLoginUsed = '';

require_once (INSTALL_PATH.'inc/initInclude.php');
require_once ('common.inc');
require_once ('autoload.php');
require_once ('PDOinit.php');
require_once ('mails_fcts.php');
require_once ('dates.php');
require_once ('logs_fcts.php');

// Vérification si la session est toujours active.
$Auth = new Connecting($bdd);

// CONNEXION
if ( isset ($_POST['conx']) && isset ($_POST['login']) && isset ($_POST['password']) ) {
    if (!$Auth->connect($_POST['login'], $_POST['password'])) $errAuth = true;
	else $errAuth = false;
	add_conx_log($errAuth, $_POST['login'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);		// add line in log file
	send_alertmailConx($_POST['login'], $errAuth);	// send mail to Devs
}
else $errAuth = false;

$configForUserConx = true;
// Chargement de la config !!! Don't move !!!
require_once ('config.inc');						// Don't move !!!!!!!
$_SESSION['INSTALL_PATH'] = INSTALL_PATH;				// Don't move !!!!!!!
$_SESSION['INSTALL_PATH_INC'] = INSTALL_PATH.'inc';			// Don't move !!!!!!!
$_SESSION['INSTALL_PATH_CONF'] = INSTALL_PATH.'config';			// Don't move !!!!!!!
$_SESSION['INSTALL_PATH_PLUGINS'] = INSTALL_PATH.'plugins';		// Don't move !!!!!!!


// DECONNEXION
if (isset($_POST['action']) && isset($_POST['x'])) {
	if ($_POST['action'] == 'deconx') {
		$userLogin = $_POST['x'];
		global $bdd;
		$q = $bdd->prepare("SELECT id,login FROM saam_users WHERE login = '".$userLogin."'");
		try { $q->execute(array()); }
		catch (Exception $e) { die('ERROR SQL (pdo) : '.$e->getMessage()); }

		if ($q->rowCount() == 1) {
			$q->setFetchMode(PDO::FETCH_OBJ);
			while( $l = $q->fetch() ) {
				$userID = $l->id;
			}
			$q->closeCursor();
			// on detruit le fichier de session
			$log = INSTALL_PATH . FOLDER_SESSIONS . $userID . ".ssid";
			if(file_exists($log)) {
				chmod($log, 0755);
				unlink($log);
			}
		}
		// on deconnect et vide toutes les sessions
		$Auth->disconnect();
		unset ($_SESSION);
		$logged = false ;
	}
}
// VÉRIF SI TOUJOURS CONNECTÉ
else {
	$isStillConnect = $Auth->is_connected();
	if ($isStillConnect !== false) {
		// Crée une instance User à chaque rechargement de page si connecté
		try {
			$_SESSION["user"] = new Users($isStillConnect);
			$logged			  = true ;
			$userTheme				= $_SESSION["user"]->getUserInfos('theme');
			$_SESSION['theme']		= $userTheme;
			$_SESSION['username']	= $_SESSION['user']->getUserInfos('login');
			$userID			= $_SESSION["user"]->getUserInfos('id');		// mise en mémoire pour Javascript
			$userLogin		= $_SESSION["user"]->getUserInfos('login');		// mise en mémoire pour Javascript
			$userStatus		= $_SESSION["user"]->getUserInfos('status');	// mise en mémoire pour Javascript
			$usrProjs		= $_SESSION['user']->getUserProjects();
			$userProjectsJS = '[';
			foreach($usrProjs as $proj)
				$userProjectsJS .= '"'.$proj.'",';
			$userProjectsJS .= ']';
			if (@$dontTouchSSID !== true)
				touch(INSTALL_PATH . FOLDER_SESSIONS . $userID . ".ssid");			// "touch" sur le fichier SSID pour éviter déconnexion auto intempestive
		}
		catch (Exception $e) {
			echo $e->getMessage() ;
			$logged = false;
			unset ($_SESSION["user"]);
		}
	}
	else $logged = false ;
}




// CHARGEMENT DE LA LANGUE
if (isset($_SESSION['user']))
	define('LANG', $_SESSION['user']->getUserInfos('lang'));
else
	define('LANG', LANG_DEFAULT);


// Définition des constantes de langue (récup en BDD)
if (defined('LANG')) {
	$l = new Liste();
	$l->getListe(TABLE_LANGS);
	$langConsts = $l->simplifyList('constante');
	foreach($langConsts as $constName => $constVal) {
		if ($constVal[LANG] != '')
			define($constName, $constVal[LANG]);
		else
			define($constName, $constVal[LANG_DEFAULT]);
	}
}





?>
