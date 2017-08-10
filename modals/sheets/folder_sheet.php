<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	require_once ('vignettes_fcts.php');

	extract($_GET);

	if ((!isset($proj_ID) || empty($proj_ID) || $proj_ID == '') && (!isset($tagName) || empty($tagName) || $tagName == ''))
		die('Missing project ID, or TAG name.');

	$p = new Projects($proj_ID);
	$titleProj = $p->getTitleProject();
	$prod = $p->getProjectInfos(Projects::PROJECT_COMPANY);

	$vignettes = Array();
	$listFiles = glob(INSTALL_PATH.$folderPath.'*');
//	usort($listFiles, "sort_by_mtime");
	foreach($listFiles as $file) {
		if (!is_file($file)) continue;
		$filename = basename($file);
		$mimeTypeFile = check_mime_type($file);
		if (preg_match('/image/i', $mimeTypeFile)) {
			$vignettes[] = Array($folderPath, $filename);
		}
	}
	if (preg_match('/sequences/', $folderPath))
		$f = explode('sequences', $folderPath);
	if (preg_match('/assets/', $folderPath))
		$f = explode('assets', $folderPath);
	if (preg_match('/\/bank\//', $folderPath))
		$f = Array('bank', L_BANK.' / '.basename($folderPath));
	$dispFolder = preg_replace('/^\/|\/$/', '', $f[1]);

//	echo '<pre>'; print_r($vignettes); echo '</pre>';
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
	<meta charset="utf-8" />
	<meta name="keywords" content="SaAM, shots, assets, management, CG, post-production" />
	<meta name="description" content="Shots and Assets Manager" />
	<meta name="robots" content="noindex,nofollow" />
	<title>SaAM - Folder Contact-Sheet</title>
	<link type="image/x-icon" href="../../gfx/favicon.ico" rel="shortcut icon" />
	<link type="text/css" href="../../css/contact_sheets.css?v=1" rel="stylesheet" media="screen, print" />
	<style type="text/css" media="print">
		@page {
		  size: A4 portrait;
		  margin: 3%;
		}
		body { font-size: 8pt;}
		.printer { display: none; }
	</style>
</head>
<body>

	<div class="headPage">
		<div class="headBtn" style="position: absolute; margin:5px 10px;">
			<img src="../../gfx/logoH.png" height="50" />
		</div>
		<div class="headTitle">
			<?php echo strtoupper(@$titleProj); ?> - <?php echo $dispFolder; ?>
		</div>
		<div class="headInfo">
			&COPY; <?php echo @$prod; ?> | <?php echo date($DEF_DATE_FORMAT); ?> <span class="printer" onClick="window.print()" title="print this document (landscape mode recommended)">| <img src="../../gfx/printer.png" /></span>
		</div>
	</div>

	<div style="text-align:center;">
		<?php foreach ($vignettes as $item): ?>
			<img src="<?php echo '../../'.$item[0].$item[1]; ?>" width="950" /><br />
			<?php echo $item[1]; ?><br />
			<br />
		<?php endforeach; ?>
	</div>
</body>
</html>