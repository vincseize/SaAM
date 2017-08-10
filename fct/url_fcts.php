<?php

function getSaamURL () {
	$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
	$url = ($https) ? 'https://' : 'http://' ;
	$url.= (!empty($_SERVER['REMOTE_USER'])) ? $_SERVER['REMOTE_USER'].'@' : '';
	if (!isset($_SERVER['HTTP_HOST'])) {
		$url.= ($_SERVER['SERVER_NAME']. ($https && $_SERVER['SERVER_PORT'] === 443 || $_SERVER['SERVER_PORT'] === 80) ? '' : ':'.$_SERVER['SERVER_PORT']);
	}
	else $url.= $_SERVER['HTTP_HOST'];
	$url.= (preg_match('/^\/SaAM/', $_SERVER['PHP_SELF'])) ? '/SaAM' : '';
	return $url;
}

?>