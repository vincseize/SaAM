<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

require_once ('arrays_fcts.php');
extract($_GET);

try {
	// Export de langue en CSV
	if (!isset($lang))
		throw new Exception("Language name is missing !");

	$l = new Liste();
	$l->getListe(TABLE_LANGS, "id,constante,en,$lang", 'constante', 'ASC');
	$lang_constList = $l->simplifyList();

	$exportedCSV   = data_export_CSV("id;constante;en;$lang", $lang_constList);

	$mimeType = 'text/csv';
	$size = strlen($exportedCSV);

	header("Content-type: $mimeType" );
	header("Content-Disposition: attachment; filename=SaAM_export_language_$lang.csv");
	header("Content-Length: $size");
	header("Pragma: no-cache");
	header("Expires: 0");

	ob_clean();
	flush();
	echo $exportedCSV;
}
catch(Exception $e) {
	echo "An error occured while exporting this language : ".$e->getMessage();
}