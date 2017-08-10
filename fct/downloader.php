<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	require_once('directories.php');

	if ( ! isset($_GET['type']) || ! isset ( $_GET['file']) ) die('Il manque le type, ou nom du fichier à télécharger !') ;
	extract ($_GET) ;
	$file = urldecode($file);

	if ( $type == 'sql' ){
		$dir = FOLDER_CONFIG . "dumpSQL";
	}
	elseif ( $type == 'retake' ) {
		if (empty($dirRetake))  die ('Missing path to published!');
		if (empty($targetName)) die ('Missing target filename!');
		$dir = FOLDER_DATA_PROJ . $dirRetake;
	}
	elseif ( $type == 'scenario') {
		$content = file_get_contents(INSTALL_PATH.$file);
		$pdf = new html2pdf('P','A4','fr');
		$pdf->WriteHTML($content);
		$pdf->Output(basename($file, '.htm').'.pdf', 'D');
		die();
	}
	elseif ( $type == 'masterFileAssets' ) {
		if (empty($idProj)) die ('Missing project ID!');
		if (empty($titleProj)) die ('Missing project title!');
		$dir = FOLDER_DATA_PROJ . $idProj.'_'.urldecode($titleProj).'/assets';
	}
	elseif ( $type == 'script' ){
		$dir = FOLDER_DATA . "scripts";
	}
	elseif ( $type == 'prod_export' ){
		$dir = FOLDER_TEMP . "exports";
	}
	elseif ( $type == 'bank_zip') {
		$dir = FOLDER_TEMP . "exports";
	}
	else {
		$dir = 'NO FILES HERE';
		die('Type of data not downloadable!');
	}

	$filename = INSTALL_PATH."$dir/$file" ;

	if ( !file_exists( $filename ) ){
		die( '<b>File "'.basename($filename).'" NOT FOUND!</b><br />Redirecting... <script>setTimeout(function(){window.location.href = "../index.php"},1200);</script>');
	}

	$size = filesize($filename);
	$newFileName = preg_replace('/ /', '_', $file);

	$mime = check_mime_type($filename);

	if ( $type == 'retake' ) {
		$newFileName = $targetName.'_'.date('Y-m-d_H:i:s', filemtime($filename));
		if (preg_match('/ogg/i', $mime))
			$newFileName .= '.ogv';
		elseif(preg_match('/png/i', $mime))
			$newFileName .= '.png';
		elseif(preg_match('/gif/i', $mime))
			$newFileName .= '.gif';
		else
			$newFileName .= '.jpg';
	}


	header("Content-type: " . $mime );
	header("Content-Disposition: attachment; filename=$newFileName");
	header("Content-Length: $size");
	header("Pragma: no-cache");
	header("Expires: 0");

	ob_clean();
	flush();
	readfile($filename);
?>