<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC'].'/checkConnect.php' );
	require_once('vignettes_fcts.php');

	extract($_POST);

	$assetsList = Assets::getProjectAssets($idProj, true);
	$assetsListByUser = Array();
	foreach($assetsList as $assetName => $asset)
		$assetsListByUser[$asset[Assets::ASSET_ID_HANDLER]][$assetName] = $asset;
	krsort($assetsListByUser);
	if (!is_array($assetsList))
		die('<div class="pad5 ui-state-disabled" id="assetsTagsList">No asset for this project.</div>');
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
	<div><?php
//	echo '<pre>'; var_dump($assetsListByUser); echo '</pre>';
	foreach($assetsListByUser as $idH => $assets) :
		$handler = L_ASSET_FREE;
		$classHandler = '';
		if ($idH != 0) {
			$uH = new Users((int)$idH, Users::USERS_ID);
			$handler = $uH->getUserInfos(Users::USERS_PSEUDO);
			$classHandler = 'ui-state-error';
		}
		?>
		</div>
		<div class="marge5bot assetCategDiv petit">
			<div class="padH5 gros assetCategHead">
				<div class="inline mid doigt"><span class="ui-icon ui-icon-triangle-1-e"></span></div>
				<div class="inline mid doigt ui-state-disabled"><?php echo strtoupper($handler); ?></div>
			</div>
			<div class="hide assetCategList"><?php
			foreach($assets as $assetName => $asset):
				$vignette	= check_asset_vignette($asset[Assets::ASSET_PATH_REL], $assetName, $idProj, true);
				$a			= new Assets($idProj, (int)$asset[Assets::ASSET_ID]);
				try { $assetInXML = asset_exists_xml($idProj, $assetName); }
				catch (Exception $e) { $assetInXML = false; }
				$dateModif	= $a->getLastModifDate();
				$classAsset = ($assetInXML) ? 'ui-state-default' : 'ui-state-disabled';
				$warnAsset  = ($assetInXML) ? '' : 'WARNING: this asset has been removed from XML masterfile. You should consider remove it from DB too.'; ?>
				<div class='<?php echo $classAsset; ?> doigt assetItemCateg' title="<?php echo $warnAsset; ?>" filename='<?php echo $assetName; ?>' filePath='<?php echo $asset[Assets::ASSET_PATH_REL]; ?>'>
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
				</div><?php
			endforeach; ?>
			</div><?php
	endforeach; ?>
	<p>&nbsp;</p>
</div>