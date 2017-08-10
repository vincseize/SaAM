<?php

$host = $_SERVER['HTTP_HOST'];
$serverName = php_uname('n');

//echo 'host = '.$host.'<br>serverName = '.$serverName.'<br>Remote_addr = '.$_SERVER['REMOTE_ADDR']; exit;

define ("SOCKET_CHAT_URL", false);
define ("IS_LOCAL", true);
define ("HOST", "127.0.0.1");
define ("USER", "root");
define ("PASS", "zz161169");
define ("BASE", "saam");
define ('SAAM_MAILBOT', 'saamLocal@saamanager.net');

define ("DSN", 'mysql:dbname='.BASE.';host='.HOST);								// données de connexion à la BDD via PDO

// Définition URL TRACKBACKS SAAM
define ("URL_TRACKBACKS",   "http://bughunter.saamanager.net/");

// Définition des dossiers du site
define ("FOLDER_ADMIN",		"admin/");
define ("FOLDER_CLASSES",	"classes/");
define ("FOLDER_CONFIG",	"config/");
define ("FOLDER_CSS",		"css/");
define ("FOLDER_FCT",		"fct/");
define ("FOLDER_GFX",		"gfx/");
define ("FOLDER_JS",		"js/");
define ("FOLDER_INC",		"inc/");
define ("FOLDER_MAILS",		"mails/");
define ("FOLDER_PAGES",		"pages/");
define ("FOLDER_RECETTE",	"_RECETTE/");
define ("FOLDER_DATA",		"datas/");
define ("FOLDER_DATA_PROJ",	"datas/projects/");
define ("FOLDER_DATA_USER",	"datas/users/");
define ("FOLDER_TEMP",      "temp/");
define ("FOLDER_PLUGINS",	"plugins/");
define ("FOLDER_SESSIONS",	"sessions/");

// Définition des tables de la base de données
define ("TABLE_ACL", "saam_acl");												// table des ACL
define ("TABLE_LANGS", "saam_all_langs");										// table des langues
define ("TABLE_ASSETS", "saam_assets");											// table des assets
define ("TABLE_CAMERAS", "saam_cameras");										// table des cameras
define ("TABLE_COMM_ASSET", "saam_comments_asset");								// table des commentaires d'assets
define ("TABLE_COMM_SCENES", "saam_comments_scenes");							// table des commentaires de scenes
define ("TABLE_COMM_FINAL", "saam_comments_final");								// table des commentaires de final
define ("TABLE_COMM_SHOT", "saam_comments_shots");								// table des commentaires de shots
define ("TABLE_COMM_TASKS", "saam_comments_tasks");								// table des commentaires de shots
define ("TABLE_DAILIES", "saam_dailies");										// table des dailies
define ("TABLE_DAILIES_SUMMARY", "saam_dailies_summary");						// table des résumés de dailies
define ("TABLE_DEPTS", "saam_depts");											// table des départements
define ("TABLE_CONFIG", "saam_config");											// table des infos de config
define ("TABLE_CHAT", "saam_jchat");											// table des messages Jchat
define ("TABLE_NEWS", "saam_news");												// table des news
define ("TABLE_NOTES", "saam_notes");											// table des notes perso
define ("TABLE_PROD_CUSTOM", "saam_prod_custom");								// table des custom "meta tables" (prod)
define ("TABLE_PROJECTS", "saam_projects");										// table des projets
define ("TABLE_RELATIONS", "saam_relations");									// table de descriptions des relations entre les tables
define ("TABLE_SEQUENCES", "saam_sequences");									// table des séquences
define ("TABLE_SCENES", "saam_scenes");											// table des scenes
define ("TABLE_SHOTS", "saam_shots");											// table des plans
define ("TABLE_SHOTS_DEPTS", "saam_shots_depts_infos");							// table des infos spécifiques aux depts pour chaque shot
define ("TABLE_SCENES_DEPTS", "saam_scenes_depts_infos");						// table des infos spécifiques aux depts pour chaque scene
define ("TABLE_ASSETS_DEPTS", "saam_assets_depts_infos");						// table des infos spécifiques aux depts pour chaque asset
define ("TABLE_TASKS", "saam_tasks");											// table des taches
define ("TABLE_USERS", "saam_users");											// table des utilisateurs

// Définition RECETTE
define ("PROJECT_TEST_TYPE", "test");						// type des projets de test
define ("TITLE_PROJECT_DEV", "DEV_DEV");					// nom des projets de test
define ("LOGIN_USER_DEV", "jdoe");							// nom des users de test

//
// Définition des constantes servant à la connexion
define ("SALT_PASS",		'G:niUk5!1|WQ');				// Grain de sel (seed) pour la création / récup de password in BDD
define ("COOKIE_NAME_LOG",	'saam_auth_login');				// nom du cookie gestion login
define ("COOKIE_NAME_PASS", 'saam_auth_password');			// nom du cookie gestion password
define ("COOKIE_PEREMPTION", time() + (3600 * 24 * 2));		// péremption des cookies : 2 jours.
define ('BF_TIME_LAPS', (60*5)); // 5 minutes				// laps de temps à attendre après avoir fait trop de tentatives de connexion (en secondes)
define ('BF_NB_TENTATIVE', 5);	// 5 tentatives				// Nbre de tentatives maxi, tous les TIME_LAPS
define ('BF_DIR', INSTALL_PATH.'BFlogs/');					// répertoire de stockage des logs


// Supprime un fichier de session
function rmSessionFile ($userID) {
	$log = INSTALL_PATH . FOLDER_SESSIONS . $userID . ".ssid";
	if(file_exists($log)) {
		chmod($log, 0755);
		unlink($log);
	}
}

// Recherche dans un tableau de façon récursive
function in_array_r($needle, $haystack, $strict=false) {
	foreach ($haystack as $item) {
		if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict)))
			return true;
	}
	return false;
}
?>