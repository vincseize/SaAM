<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	require_once ('dates.php');

	if (isset($_POST['sceneID']))
		$sceneID = $_POST['sceneID'];
	else die('Scène indéfinie...');

	if (isset($_POST['idProj']))
		$idProj = $_POST['idProj'];
	else die('Projet indéfini...');

	if (isset($_POST['deptID']))
		$deptID = $_POST['deptID'];
	else die('Département indéfini...');

	extract($_POST);

try {
	$ACL = new ACL($_SESSION['user']);
	if (!$ACL->check('SCENES_ADMIN')) die('<div class="marge5 ui-state-error ui-corner-all pad5">Acces denied.</div>');

	$sc = new Scenes($sceneID);
	if ($sc->getMaster() == 0) die('<div class="marge5 ui-state-error ui-corner-all pad5">Assets in master scenes must be ADDED.</div>');

	// Récupère la liste des assets assignés à la scène FILLE (exclus)
	$sceneExcludedAssets = $sc->getSceneAssets(true);
	$jsSceneExcludedAssets = '[';
	foreach($sceneExcludedAssets as $assetID) {
		if ((int)$assetID == 0) continue;
		$jsSceneExcludedAssets .= $assetID.',';
	}
	$jsSceneExcludedAssets .= ']';

	// Récupère la liste des assets assignés à la scène MASTER
	$mSc = new Scenes($sc->getMaster());
	$masterSceneAssets = $mSc->getSceneAssets();
	$jsSceneIncludedAssets = '[';
	foreach($masterSceneAssets as $assetID) {
		$jsSceneIncludedAssets .= $assetID.',';
	}
	$jsSceneIncludedAssets .= ']';

	$assetsCategs = $_SESSION['CONFIG']['ASSETS_CATEGORIES'];
	$sortedAssetsList = Array(); $i = 0;
	foreach($masterSceneAssets as $assetID) {
		try {
			$a = new Assets($idProj, (int)$assetID);
			$assetCategId = $a->getInfo(Assets::ASSET_CATEGORY);
			$assetCateg	  = ($assetCategId != 0) ? $assetsCategs[$assetCategId] : 'uncategorized';
			$sortedAssetsList[$assetCateg][$i]['id']		= $assetID;
			$sortedAssetsList[$assetCateg][$i]['name']		= $a->getInfo(Assets::ASSET_NAME);
			$sortedAssetsList[$assetCateg][$i]['path']		= $a->getInfo(Assets::ASSET_PATH_REL);
			$sortedAssetsList[$assetCateg][$i]['handler']	= $a->getHandler('pseudo');
			$sortedAssetsList[$assetCateg][$i]['dateModif']	= $a->getLastModifDate();
			$sortedAssetsList[$assetCateg][$i]['vignette']	= check_asset_vignette($a->getInfo(Assets::ASSET_PATH_REL), $a->getInfo(Assets::ASSET_NAME), $idProj, true);
			$i++;
		}
		catch(Exception $e) { continue; }
	}
	ksort($sortedAssetsList);

?>
<script>

	assetSceneListExclude = [];
	assetsInitListInclude = <?php echo $jsSceneIncludedAssets; ?>;
	assetsInitListExclude = <?php echo $jsSceneExcludedAssets; ?>;

	$(function(){

		// Ajout des assets de la scène MASTER dans la div de droite
		if(assetsInitListInclude.length >= 1) {
			$('#projectAssetsList .assetItemCateg').each(function(i,idAsset) {
				$(this).clone().appendTo('#sceneAssetsList');
			});
		}

		// Suppression des assets déjà présents dans la div de droite (à exclure)
		if(assetsInitListExclude.length >= 1) {
			$.each(assetsInitListExclude, function(i,idAsset) {
				var item = $('#projectAssetsList .assetItemCateg[assetID="'+idAsset+'"]');
				addAssetToExclude(item);
			});
		}

		// Ouverture des catégories
		$('#projectAssetsList .assetCategHead').click(function(){
			$('#projectAssetsList .assetCategList').hide(150);
			$(this).next('.assetCategList').show(150);
		});

		// Click sur un asset dans la liste de la Master (pour ajouter / enlever)
		$('#projectAssetsList .assetItemCateg').click(function() {
			if ($(this).hasClass('ui-state-error'))
				removeAssetToExclude($(this));
			else
				addAssetToExclude($(this));
		}).hover(
			function(){ $(this).addClass('ui-state-hover'); },
			function(){ $(this).removeClass('ui-state-hover'); }
		);

		// click sur un asset dans la liste de la FILLE (pour enlever)
		$('#sceneAssetsList').off('click','.assetItemCateg');
		$('#sceneAssetsList').on('click','.assetItemCateg', function(){
			addAssetToExclude($(this));
		});

	});

	// add asset in the scene's list
	function addAssetToExclude (assetDiv) {
		var idAsset = assetDiv.attr('assetID');
		assetSceneListExclude.push(idAsset);
		$('#projectAssetsList .assetItemCateg[assetID="'+idAsset+'"]').addClass('ui-state-error');
		$('#sceneAssetsList .assetItemCateg[assetID="'+idAsset+'"]').remove();
	}
	// remove asset from the scene's list
	function removeAssetToExclude (assetDiv) {
		var idAsset = assetDiv.attr('assetID');
		var index = assetSceneListExclude.indexOf(idAsset);
		if (index > -1)
			assetSceneListExclude.splice(index, 1);
		$('#projectAssetsList .assetItemCateg[assetID="'+idAsset+'"]').removeClass('ui-state-error');
		$('#projectAssetsList .assetItemCateg[assetID="'+idAsset+'"]').clone().removeClass('ui-state-hover').appendTo('#sceneAssetsList');
	}
</script>


<div class="inline top demi bordBankSection">
	<b class="colorBtnFake"><?php echo $mSc->getSceneInfos(Scenes::TITLE); ?></b>
</div>
<div class="inline top demi">
	<?php echo $sc->getSceneInfos(Scenes::TITLE); ?>
</div>

<div class="inline top demi bordBankSection margeTop5">
	<div class="ui-state-default pad3 gras center">
		<span class="floatL ui-state-error ui-corner-all mini marge10l" style="padding: 1px 4px;"><?php echo L_MASTER; ?></span>
		<span class="ui-state-disabled">Master scene's assets</span>
	</div>
	<div class="pad5" id="projectAssetsList">
		<?php if(count($sortedAssetsList) >= 1): ?>
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
				} ?>
				<div class='ui-state-default doigt assetItemCateg' assetID='<?php echo $item['id']; ?>' filename='<?php echo $item['name']; ?>' filePath='<?php echo $item['path']; ?>'>
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
			<?php endforeach; ?>
				</div>
		<?php endforeach; ?>
		</div>
		<?php else: ?>
			No asset in this project yet.
		<?php endif; ?>
	</div>
</div><div class="inline top demi margeTop5">
	<div class="ui-state-default pad3 gras center">
		<span class="floatL ui-state-highlight ui-corner-all mini marge10l" style="padding: 1px 4px;"><?php echo L_DERIVATIVE; ?></span>
		<span class="ui-state-disabled">Derivative's assets</span>
	</div>
	<div class="pad5" id="sceneAssetsList">

	</div>
</div>

<?php
}
catch(Exception $e) { echo('<div class="marge5 ui-state-error ui-corner-all pad5">'.$e->getMessage().'</div>'); }
?>