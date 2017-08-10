<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC'] . "/checkConnect.php" );
	require_once ('directories.php');
	
	function list_themes () {
		$listThemes = array();
		$listThemes = listDir(FOLDER_CSS, 'subdir');
		sort($listThemes, SORT_STRING);
		return $listThemes;
	}
	
?>