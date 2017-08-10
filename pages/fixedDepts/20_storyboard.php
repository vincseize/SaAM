<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

	// IMPORTANT !!!!!!!!!!!
	$dept = 'storyboard';
	// nom du fichier réel du dept:
	$deptFile = 'template_1';
	// !!!!!!!!!!!!!!!!!!!!!

	if (isset($_POST['shotID']))
		require_once('structure/structure_shots.php');
	else
		require_once('structure/structure_project.php');
?>
