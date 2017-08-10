<?php

/*
 * CONFIG GÉNÉRALE du SaAM
 */

// Versions du SaAM
define ("SAAM_PREV_VERSION", '1.1');
define ("SAAM_VERSION", '1.2');
define ("SAAM_NEXT_VERSION", '1.3');
define ("SAAM_NEXT_VERSION_DATE", '2015, 09');					// -> annnée, mois
define ("SAAM_NEXT_VERSION_DAY", '13');							// -> jour du mois

define ("SAAM_API_VERSION", '0.1');

// SIZE MAX EN POIDS d'un projet
define('SIZE_MAX_PROJECT',  1.5		* 1024 * 1024);		// 1.5 Giga-octets
define('SIZE_SECU_PROJECT', 50		* 1024);			// moins 50 Mo pour sécu

// Définition des domaines considérés comme des SaAM DEMOS
$_SESSION['isDemoSaAM'] =
	(	$_SERVER['HTTP_HOST'] == 'saamanager.com'
	 || $_SERVER['HTTP_HOST'] == 'saamanager.eu'
	 || $_SERVER['HTTP_HOST'] == 'saamanager.fr'
	 || $_SERVER['HTTP_HOST'] == 'saamanager.org'
	 || $_SERVER['HTTP_HOST'] == 'demo.saamanager.net'
//	 || $_SERVER['HTTP_HOST'] == 'etoilenoire'
	) ? true : false;

// Config defaults

$_SESSION['CONFIG']['projects_size']		= SIZE_MAX_PROJECT - SIZE_SECU_PROJECT; // Taille maxi des projets par défaut

$_SESSION['CONFIG']['DEFAULT_STATUS']		= array(			// Status généraux par défaut (prod / tasks)
	'--', 'WIP', 'OnHold', 'VALID', 'Disabled'
);
$_SESSION['CONFIG']['DEFAULT_DEPTS']		= array(			// départements par défaut pour l'ajout de projet
	'1'
);
$_SESSION['CONFIG']['PROJ_TYPES_LIST']		= array(			// Types disponibles pour les projets
	'demo',		'short',	'feature',	'commercial',
	'effects',	'game',		'web',		'test'
);
$_SESSION['CONFIG']['ASSETS_CATEGORIES']	= array(			// Catégories des assets par défaut
	"1"=>"characters",	"2"=>"sets",	"3"=>"props",
	"4"=>"textures",	"5"=>"mattes"
);
$_SESSION['CONFIG']['DEFAULT_ASSETS_DIRS']	= array(			// Dossiers des assets par défaut
	'characters',	'props',	'sets',
	'vehicles',		'terrains'
);
$_SESSION['CONFIG']['asset_exclude_dirs']	= array(			// Exclusion de dossiers assets (pendant le parsing du masterfile XML -> tree view)
	'refs', 'bank', 'stills'
);
$_SESSION['CONFIG']['dataShotsFolders']		= array(			// Dossiers data shots/assets obligatoires
	'refs', 'bank', 'stills'
);
$_SESSION['CONFIG']['SOFTS']				= array(			// Logiciels disponibles dans la prod
	'blender',		'Zbrush',	'AfterEffect',	'Maya',
	'MotionBuilder', 'Ardour'
);
$_SESSION['CONFIG']['AV_ASSETS_EXTS']		= array(			// Extensions de fichier dispos pour les assets
	"blend", "ma", "jpg", "png",
	"wav", "aif", "psd", "ae", "obj"
);
$_SESSION['CONFIG']['AV_COMPETENCES']		= array(			// Compétences disponibles pour les utilisateurs
	"producer",	"assistantProd","director",	"scenarist",
	"cameraman","lightTech",	"soundTech","artist2D",
	"artist3D",	"editor",		"coder",	"VFX",
	"soundMix"
);
$_SESSION['CONFIG']['decTech_DEFAULT']		= array(			// Données disponibles dans le département Dec.Tech
	'action' => array(
		1 => array('Lieu', ''),
		2 => array('Int-Ext', ''),
		3 => array('day-night', ''),
		4 => array('Action', '')
	),
	'camera' => array(
		1 => array('Valeur_Plan', ''),
		2 => array('Mvt_Cam', ''),
		3 => array('Angle', ''),
		4 => array('Raccord', '')
	),
	'son' => array(
		1 => array('Son_Amb', ''),
		2 => array('Dialogue', ''),
		3 => array('In-Off', ''),
		4 => array('Musique', '')
	)
);
$_SESSION['CONFIG']['plugins_enabled']		= array(
	'chat' => true
);

$DEF_LANG_DEFAULT			= "en";								// Langue par défaut
$DEF_THEME_DEFAULT			= "dark";							// Thème par défaut
$DEF_LIST_FPS				= "16|18|23.97|24|25|30|60|100";	// Cadences d'image disponibles
$DEF_DEFAULT_FPS			= 25;								// Cadence d'image par défaut pour l'ajout de projet
$DEF_LIST_RATIOS			= "16:9|4:3";						// Rapports d'image (ratio) disponibles
$DEF_DATE_FORMAT			= "d/m/Y";							// Format de date par défaut
$DEF_URL_INTRANET			= "http://saamanager.net";			// URL de l'intranet de la boîte
$DEF_HOME_MAX_NEWS			= 4 ;								// Nombre maximum de news affichées en home
$DAILIES_MAX_WEEKS			= 8 ;								// Nombre maximum de semaine à récupérer pour les dailies
$DEF_CALENDAR_FILE			= "datas/calendar/events.json";		// emplacement et nom du fichier des données de l'agenda Home
$DEF_NOMENCLATURE_SEQ		= "SEQ";							// Nomenclature séquence par défaut
$DEF_NOMENCLATURE_SHOT		= "SHOT";							// Nomenclature shot par défaut
$DEF_NOMENCLATURE_SCENES	= "M_SC";							// Nomenclature scènes par défaut
$DEF_ALERT_UPLOADS			= true;								// true pour envoyer un mail aux gens, quand un upload est en cours
$DEF_ALERT_RETAKES			= true;								// true pour envoyer un mail aux gens, quand une retake est ajoutée ou validée
$DEF_ALERT_MESSAGES			= true;								// true pour envoyer un mail aux gens, quand un nouveau message est posté sur une retake
$DEF_ALERT_TASKS			= true;								// true pour envoyer un mail aux gens, quand une tache leur est attribuée
$DEF_DECONX_TIME			= 30;								//	Temps pour le check de déconnexion automatique (en minutes, 30 minutes)


// Config récup BDD
try {
	$conf = new Infos(TABLE_CONFIG);
	$conf->loadInfos('version', SAAM_VERSION);

	// ÉCRASEMENT DES DEFAULTS EN DUR PAR LES INFOS DEFAULTS EN BDD SI DISPO (table saam_config)
	$inf = $conf->getInfo('default_lang');
	if ($inf)  $DEF_LANG_DEFAULT				  = $inf;

	$inf = $conf->getInfo('default_theme');
	if ($inf)  $DEF_THEME_DEFAULT				  = $inf;

	$inf = $conf->getInfo('fps_list');
	if ($inf)  $DEF_LIST_FPS					  = $inf;

	$inf = $conf->getInfo('default_fps');
	if ($inf)  $DEF_DEFAULT_FPS				 = (int)$inf;

	$inf = $conf->getInfo('ratio_list');
	if ($inf)  $DEF_LIST_RATIOS					  = $inf;

	$inf = $conf->getInfo('date_format');
	if ($inf)  $DEF_DATE_FORMAT					  = $inf;

	$inf = $conf->getInfo('url_intranet');
	if ($inf)  $DEF_URL_INTRANET				  = $inf;

	$inf = $conf->getInfo('home_max_news');
	if ($inf)  $DEF_HOME_MAX_NEWS			 = (int)$inf;

	$inf = $conf->getInfo('dailies_max_weeks');
	if ($inf)  $DAILIES_MAX_WEEKS			 = (int)$inf;

	$inf = $conf->getInfo('calendar_file');
	if ($inf)  $DEF_CALENDAR_FILE				  = $inf;

	$inf = $conf->getInfo('default_seqLabel');
	if ($inf)  $DEF_NOMENCLATURE_SEQ			  = $inf;

	$inf = $conf->getInfo('default_shotLabel');
	if ($inf)  $DEF_NOMENCLATURE_SHOT			  = $inf;

	$inf = $conf->getInfo('default_sceneLabel');
	if ($inf)  $DEF_NOMENCLATURE_SCENES			  = $inf;

	$inf = $conf->getInfo('alert_uploads');
	if ($inf)  $DEF_ALERT_UPLOADS			= (bool)$inf;

	$inf = $conf->getInfo('alert_retakes');
	if ($inf)  $DEF_ALERT_RETAKES			= (bool)$inf;

	$inf = $conf->getInfo('alert_messages');
	if ($inf)  $DEF_ALERT_MESSAGES			= (bool)$inf;

	$inf = $conf->getInfo('alert_tasks');
	if ($inf)  $DEF_ALERT_TASKS				= (bool)$inf;

	$inf = json_decode($conf->getInfo('default_status'));
	if ($inf)  $DEF_GENERAL_STATUS				  = $inf;

	$inf = json_decode($conf->getInfo('projects_size'), true);
	if ($inf != NULL && $inf > 0)  $_SESSION['CONFIG']['projects_size'] = (float)$inf * 1024 * 1024;

	$inf = json_decode($conf->getInfo('default_depts'));
	if ($inf != NULL)  $_SESSION['CONFIG']['DEFAULT_DEPTS'] = $inf;

	$inf = json_decode($conf->getInfo('default_project_types'));
	if ($inf != NULL)  $_SESSION['CONFIG']['PROJ_TYPES_LIST'] = $inf;

	$inf = json_decode($conf->getInfo('assets_categories'), true);
	if ($inf != NULL)  $_SESSION['CONFIG']['ASSETS_CATEGORIES'] = $inf;

	$inf = json_decode($conf->getInfo('default_assets_dirs'));
	if ($inf != NULL)  $_SESSION['CONFIG']['DEFAULT_ASSETS_DIRS'] = $inf;

	$inf = json_decode($conf->getInfo('default_assets_exclude_dirs'));
	if ($inf != NULL)  $_SESSION['CONFIG']['asset_exclude_dirs'] = $inf;

	$inf = json_decode($conf->getInfo('default_data_folders'));
	if ($inf != NULL)  $_SESSION['CONFIG']['dataShotsFolders'] = $inf;

	$inf = json_decode($conf->getInfo('available_softs'));
	if ($inf != NULL)  $_SESSION['CONFIG']['SOFTS'] = $inf;

	$inf = json_decode($conf->getInfo('available_assets_extensions'));
	if ($inf != NULL)  $_SESSION['CONFIG']['AV_ASSETS_EXTS'] = $inf;

	$inf = json_decode($conf->getInfo('available_competences'));
	if ($inf != NULL)  $_SESSION['CONFIG']['AV_COMPETENCES'] = $inf;

	$inf = json_decode($conf->getInfo('dectech_infos'), true);
	if ($inf != NULL)  $_SESSION['CONFIG']['decTech_DEFAULT'] = $inf;

	$inf = json_decode($conf->getInfo('plugins_enabled'), true);
	if ($inf != NULL)  $_SESSION['CONFIG']['plugins_enabled'] = $inf;

}
catch (Exception $e) {
	define ('GLOBAL_ERROR', 'VERSION ERROR : no config for version '.SAAM_VERSION.' !');
}


define ('DECONX_TIME_DEFAULT',	$DEF_DECONX_TIME);

if ($configForUserConx === true) {
	try {
		$usrID = $Auth->is_connected();
		if ($usrID === false)
			throw new Exception('User inaccessible!');
		$usr = new Users($usrID);

		$info = (int)$usr->getUserInfos(Users::USERS_DECONX_TIME);
		if (is_int(@$info))
			$DEF_DECONX_TIME = $info;
	}
	catch (Exception $e) {
		if (!defined('GLOBAL_ERROR'))
			define ('GLOBAL_ERROR', 'PREFERENCES ERROR: missing data: '.$e->getMessage());
	}
}

define ('LANG_DEFAULT',			$DEF_LANG_DEFAULT);
define ('THEME_DEFAULT',		$DEF_THEME_DEFAULT);
define ("LIST_FPS",				$DEF_LIST_FPS);
define ("DEFAULT_FPS",			$DEF_DEFAULT_FPS);
define ("LIST_RATIOS",			$DEF_LIST_RATIOS);
define ('DATE_FORMAT',			$DEF_DATE_FORMAT);
define ('INTRANET_URL',			$DEF_URL_INTRANET);
define ('HOME_MAX_NEWS',		$DEF_HOME_MAX_NEWS);
define ('DAILIES_MAX_WEEKS',	$DAILIES_MAX_WEEKS);
define ('CALENDAR_JSON_DATAFILE', $DEF_CALENDAR_FILE);
define ('NOMENCLATURE_SEQ',		$DEF_NOMENCLATURE_SEQ);
define ('NOMENCLATURE_SHOT',	$DEF_NOMENCLATURE_SHOT);
define ('NOMENCLATURE_SCENES',	$DEF_NOMENCLATURE_SCENES);
define ('ALERT_UPLOADS',		$DEF_ALERT_UPLOADS);
define ('ALERT_RETAKES',		$DEF_ALERT_RETAKES);
define ('ALERT_MESSAGES',		$DEF_ALERT_MESSAGES);
define ('ALERT_TASKS',			$DEF_ALERT_TASKS);

define('ASSETS_CATEG_SETS', 2); // ID de la catéorie "SETS" pour les assets -> cf. config panel

// CONFIG IMMUABLE

$_SESSION['STATUS_LIST'] = array(
	1=>'visitor', 2=>'demo', 3=>'artist', 4=>'lead', 5=>'supervisor', 6=>'prod dir', 7=>'magic', 8=>'dev', 9=>'root'
);

// Définition des valeurs temporelles
define ('AGE_NEWS_MAX',		  3600 * 24 *		7  );					//	JOURS		Age maximum des news affichées en home					(7 jours)
define ('DECONNEXION_AUTO_TIME',	 60 *		$DEF_DECONX_TIME );		//	MINUTES		Temps pour le check de déconnexion automatique			(30 minutes)
define ('MESSAGES_REFRESH_INTERVAL', 60 *		5  );					//	MINUTES		Intervale de rafraichissement des messages				(5 minutes)
define ('MY_SHOTS_REFRESH_INTERVAL', 60 *		5  );					//	MINUTES		Intervale de rafraichissement des my shots				(5 minutes)
define ('WIP_REFRESH_INTERVAL',		 60 *		10 );					//	MINUTES		Intervale de rafraichissement indicateur WIP			(10 minutes)
define ('REMAIN_REFRESH_INTERVAL',	 1 *		30 );					//	SECONDES	Intervale de rafraichissement du check activité			(30 secondes)

// Obsolete, mais gardé pour JChat (SaAM démo)
define ('AVATAR_VISIBLE_TIME',		 60 *		5  );//	MINUTES			Temps d'affichage des avatars selon dernière action		(5 minutes)
define ('AVATAR_REFRESH_INTERVAL',	  1 *		35 );// SECONDES		Intervale de rafraichissement des avatars				(35 secondes)

// Nomenclature
define('NOMENCLATURE_SEPARATOR','_');				// Séparateur entre label seq. et label shot, pour nomenclature
define('REF_PROJECT', 'REF_');						// Utilisé pour les références intra-SaAM

// Listes diverses
define ("BANK_EXTENSIONS_ALLOWED", "(gif|jpg|png|jpeg|avi|mp3|ogg|ogv|mp4|mov|pdf|doc|odt|xls|ods|zip)$");
define ("FINAL_EXTENSIONS_ALLOWED", "(avi|ogg|ogv|mp4|mov)$");
define ("RETAKE_EXTENSIONS_ALLOWED", "(gif|jpg|png|jpeg|avi|ogg|ogv|mov|mp4)$");

// VIGNETTES
define ("JPG_QUALITY_H", 90);
define ("JPG_QUALITY_M", 75);
define ("PNG_QUALITY_H", 9);
define ("PNG_QUALITY_M", 7);

define ("VIGNETTE_LARGE_W", 640);
define ("VIGNETTE_LARGE_H", 360);
define ("VIGNETTE_MEDIUM_W", 270);
define ("VIGNETTE_MEDIUM_H", 150);

// JCHAT
define ("MAX_MESSAGES_JCHAT", 50);

// maximums pour les projets demo
define('MAX_DEMO_PROJECTS', 3);
define('MAX_DEMO_SEQUENCES', 5);
define('MAX_DEMO_SHOTS', 3);

?>
