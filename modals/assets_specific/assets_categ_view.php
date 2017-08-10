<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC'].'/checkConnect.php' );
	require_once('vignettes_fcts.php');

	extract($_POST);

	$assetsList = Assets::getProjectAssets($idProj, true);

	$assetsCategs = $_SESSION['CONFIG']['ASSETS_CATEGORIES'];

	if (is_array($assetsList)) {
		$sortedAssetsList = Array(); $i = 0;
		foreach($assetsList as $assetName => $asset) {
			try {
				$a = new Assets($idProj, (int)$asset[Assets::ASSET_ID]);
				$assetCategId = $asset[Assets::ASSET_CATEGORY];
				$assetCateg	  = ($assetCategId != 0) ? $assetsCategs[$assetCategId] : 'uncategorized';
				$sortedAssetsList[$assetCateg][$i]['name']		= $assetName;
				$sortedAssetsList[$assetCateg][$i]['path']		= $asset[Assets::ASSET_PATH_REL];
				$sortedAssetsList[$assetCateg][$i]['handler']	= $a->getHandler('pseudo');
				$sortedAssetsList[$assetCateg][$i]['dateModif']	= $a->getLastModifDate();
				$sortedAssetsList[$assetCateg][$i]['vignette']	= check_asset_vignette($asset[Assets::ASSET_PATH_REL], $assetName, $idProj, true);
				try { $sortedAssetsList[$assetCateg][$i]['XMLexists']	= asset_exists_xml($idProj, $assetName); }
				catch (Exception $e) { $sortedAssetsList[$assetCateg][$i]['XMLexists'] = false; }
				$i++;
			}
			catch(Exception $e) { continue; }
		}
		ksort($sortedAssetsList);
	}
	else die('<div class="pad5 ui-state-disabled" id="assetsTagsList">No asset for this project.</div>');
?>

<script>
	$(function() {
		// Si un asset est défini en mémoire
		if (window['nameAsset']) {
			$('.assetCategList').hide();
			$('.assetItemCateg').removeClass('ui-state-activeFake');
			$('.assetItemCateg[filename="'+nameAsset+'"]').addClass('ui-state-activeFake').parent('.assetCategList').show(150).attr('showed', 'showed');
		}

		// Ouverture des catégories
		$('.assetCategHead').click(function(){
			if ($(this).next('.assetCategList').attr('showed') == 'showed')
				$('.assetCategList').hide(150).removeAttr('showed');
			else
				$(this).next('.assetCategList').show(150).attr('showed', 'showed');
		});

		// Click sur un asset (item)
		$('.assetItemCateg').click(function() {
			$('.assetItemCateg').removeClass('ui-state-activeFake');
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

<div id="assetsList">
	<div>
	<?php foreach($sortedAssetsList as $category => $items): ?>
			</div>
			<div class="marge5bot assetCategDiv petit">
				<div class="padH5 gros assetCategHead">
					<div class="inline mid doigt"><span class="ui-icon ui-icon-triangle-1-e"></span></div>
					<div class="inline mid doigt ui-state-disabled"><?php echo strtoupper($category); ?></div>
				</div>
				<div class="hide assetCategList">
		<?php foreach ($items as $i => $item):
				$classHandler = 'ui-state-error';
				if ($item['handler'] == false) {
					$item['handler'] = L_ASSET_FREE;
					$classHandler = '';
				}
				$classAsset = ($item['XMLexists']) ? 'ui-state-default' : 'ui-state-disabled';
				$warnAsset  = ($item['XMLexists']) ? '' : 'WARNING: this asset has been removed from XML masterfile. You should consider remove it from DB too.'; ?>
				<div class='<?php echo $classAsset; ?> doigt assetItemCateg' title="<?php echo $warnAsset; ?>" filename='<?php echo $item['name']; ?>' filePath='<?php echo $item['path']; ?>'>
					<div class='floatR leftText marge10r'>
						<div class='margeTop1' title='Asset <?php echo L_ASSET_HUNG_BY; ?>'>
							<div class='inline mid <?php echo $classHandler; ?> noBG noBorder'>
								<span class='ui-icon ui-icon-person'></span>
							</div>
							<div class='inline mid <?php echo $classHandler; ?>'>
								<?php echo $item['handler']; ?>
							</div>
						</div>
						<div class='margeTop1 ui-state-disabled' title='Asset last modif'>
							<small><?php echo $item['dateModif']; ?></small>
						</div>
					</div>
					<div class='inline mid center w80' title='<?php echo $item['path']; ?>'><img src='<?php echo $item['vignette']; ?>' height='40' /></div>
					<div class='inline mid' title='<?php echo $item['path']; ?>'><?php echo $item['name']; ?></div>
				</div>
	<?php	endforeach; ?>
				</div>
<?php endforeach; ?>
	</div>
	<p>&nbsp;</p>
</div>