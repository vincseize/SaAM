<?php

header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

define("INSTALL_PATH", substr( dirname(__FILE__), 0, -3) );
require_once (INSTALL_PATH.'inc/initInclude.php');
require_once ('common.inc');
require_once ('autoload.php');
require_once ('config.inc');

$api_infos = Array(
	'Name'			=> 'SaAM server API',
	'SaAM_version'	=> 'SaAM v'.SAAM_VERSION,
	'API_version'	=> 'SaAM API v'.SAAM_API_VERSION,
	'API_devs'		=> 'SaAM devs: Karlova, Polosson',
	'API_docs'		=> 'http://saamanager.net/api_docs',
	'action_list'	=> Array(
			'published' => Array('add_shot_published','add_asset_published','add_scene_published'),
			'review'	=> Array('add_asset_review_request','valid_asset_review','add_scene_review_request','valid_scene_review'),
			'message'	=> Array('add_shot_message','add_asset_message','add_scene_message'),
			'shots'		=> Array('get_shots_by_project','get_shots_by_tag','get_shot_by_id','get_shots_by_user')
	)
);

echo json_encode($api_infos);
?>
