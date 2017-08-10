<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

	extract($_GET);

	// Export de shot list selon tag
	if (!isset($type)) die('Please choose a type of export !');
	if (!isset($tagName)) die('Tag name is missing !');

	require_once('xml_fcts.php');

	$exportedList = export_shotList_tag($tagName, $type);
	if ($exportedList == false) die('Unable to export list to '.$type.'... Sorry !') ;

	$mimeType = 'text/plain';
	if ($type == 'xml') $mimeType = 'text/xml';
	$size = strlen($exportedList);

	$tagStr = preg_replace('/ /', '_', $tagName);

	header("Content-type: $mimeType" );
	header("Content-Disposition: attachment; filename=SaAM_export_from_Tag_$tagStr.$type");
	header("Content-Length: $size");
	header("Pragma: no-cache");
	header("Expires: 0");

	ob_clean();
	flush();
	echo $exportedList;
?>
