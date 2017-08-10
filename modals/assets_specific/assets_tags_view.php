<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC'].'/checkConnect.php' );
	require_once('vignettes_fcts.php');

	extract($_POST);

	$assetsList = Assets::getProjectAssets($idProj, true);

	if (!is_array($assetsList))
		die('<div class="pad5 ui-state-disabled" id="assetsTagsList">No asset for this project.</div>');
?>

<script>
	$(function() {
		// Si un asset est défini en mémoire
		if (window['nameAsset']) {
			$('.assetItemTag').removeClass('ui-state-activeFake');
			$('.assetItemTag[filename="'+nameAsset+'"]').addClass('ui-state-activeFake');
		}

		// Click sur un asset (item)
		$('.assetItemTag').click(function() {
			$('.assetItemTag').removeClass('ui-state-activeFake');
			$(this).addClass('ui-state-activeFake');
			openAsset($(this).attr('filename'), $(this).attr('filePath'));
		}).hover(
			function(){
				$(this).addClass('ui-state-hover');
			},
			function(){
				$(this).removeClass('ui-state-hover');
			}
		);

	});
</script>

<div class="pad5 ui-state-disabled" id="assetsTagsList">No tags (still in developement). Showing all assets:</div>

<div id="assetsList">
	<?php foreach($assetsList as $assetName => $asset) :
		$vignette	= check_asset_vignette($asset[Assets::ASSET_PATH_REL], $assetName, $idProj, true);
		$a			= new Assets($idProj, (int)$asset[Assets::ASSET_ID]);
		try { $assetInXML = asset_exists_xml($idProj, $assetName); }
		catch (Exception $e) { $assetInXML = false; }
		$handler	= $a->getHandler('pseudo');
		$classHandler = 'ui-state-error';
		if ($handler == false) {
			$handler = L_ASSET_FREE;
			$classHandler = '';
		}
		$dateModif	= $a->getLastModifDate();
		$classAsset = ($assetInXML) ? 'ui-state-default' : 'ui-state-disabled';
		$warnAsset  = ($assetInXML) ? '' : 'WARNING: this asset has been removed from XML masterfile. You should consider remove it from DB too.'; ?>
		<div class='<?php echo $classAsset; ?> doigt assetItemTag' title="<?php echo $warnAsset; ?>" filename='<?php echo $assetName; ?>' filePath='<?php echo $asset[Assets::ASSET_PATH_REL]; ?>'>
			<div class='floatR leftText marge10r'>
				<div class='margeTop1' title='Asset <?php echo L_ASSET_HUNG_BY; ?>'>
					<div class='inline mid <?php echo $classHandler; ?> noBG noBorder'>
						<span class='ui-icon ui-icon-person'></span>
					</div>
					<div class='inline mid <?php echo $classHandler; ?>'>
						<?php echo $handler; ?>
					</div>
				</div>
				<div class='margeTop1 ui-state-disabled' title='Asset last modif'>
					<small><?php echo $dateModif; ?></small>
				</div>
			</div>
			<div class='inline mid center w80' title='<?php echo $asset[Assets::ASSET_PATH_REL]; ?>'><img src='<?php echo $vignette; ?>' height='40' /></div>
			<div class='inline mid' title='<?php echo $asset[Assets::ASSET_PATH_REL]; ?>'><?php echo $assetName; ?></div>
		</div>
	<?php endforeach; ?>
	<p>&nbsp;</p>
</div>