<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	require_once('directories.php');
?>

<script>
	$(function(){
		$('.bouton').button();
	});
</script>


<div class="stageContent pad5">
	<h3>Liste des scripts disponibles au téléchargement :</h3>

	<?php
	$scriptsFiles = listDir('datas/scripts/', 'files');
	sort($scriptsFiles);
	foreach($scriptsFiles as $sFile) :
		$dateF = filemtime(INSTALL_PATH.'datas/scripts/'.$sFile); ?>
		<div class="inline w500 ui-state-default ui-corner-all pad3">
			<div class="floatR micro">
				<span class="mid big ui-state-disabled" title="last modified"><?php echo date('d/m/Y, H\hi', $dateF); ?></span>
				<a href="fct/downloader.php?type=script&file=<?php echo urlencode($sFile); ?>" target="new">
					<button class="mid bouton marge10l DLscript" title="download"><span class="ui-icon ui-icon-document"></span></button>
				</a>
			</div>
			<span class="gros gras"><?php echo $sFile; ?></span>
		</div><br />
	<?php endforeach; ?>
</div>