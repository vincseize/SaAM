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
	if ($sc->getMaster() > 0) die('<div class="marge5 ui-state-error ui-corner-all pad5">Assets in derivatives scenes must be EXCLUDED.</div>');
	$sceneAssets = $sc->getSceneAssets();
	$jsSceneAssets = '[';
	foreach($sceneAssets as $assetID) {
		if ((int)$assetID == 0) continue;
		$jsSceneAssets .= $assetID.',';
	}
	$jsSceneAssets .= ']';

	$assetsList = Assets::getProjectAssets($idProj);
	$assetsCategs = $_SESSION['CONFIG']['ASSETS_CATEGORIES'];
	$sortedAssetsList = Array(); $i = 0;
	foreach($assetsList as $assetName => $asset) {
		try {
			$a = new Assets($idProj, (int)$asset[Assets::ASSET_ID]);
			$assetCategId = $asset[Assets::ASSET_CATEGORY];
			$assetCateg	  = ($assetCategId != 0) ? $assetsCategs[$assetCategId] : 'uncategorized';
			$sortedAssetsList[$assetCateg][$i]['id']		= $asset[Assets::ASSET_ID];
			$sortedAssetsList[$assetCateg][$i]['name']		= $assetName;
			$sortedAssetsList[$assetCateg][$i]['path']		= $asset[Assets::ASSET_PATH_REL];
			$sortedAssetsList[$assetCateg][$i]['handler']	= $a->getHandler('pseudo');
			$sortedAssetsList[$assetCateg][$i]['dateModif']	= $a->getLastModifDate();
			$sortedAssetsList[$assetCateg][$i]['vignette']	= check_asset_vignette($asset[Assets::ASSET_PATH_REL], $assetName, $idProj, true);
			$i++;
		}
		catch(Exception $e) { continue; }
	}
	ksort($sortedAssetsList);

?>
<script>

	assetSceneList = [];
	assetsInitList = <?php echo $jsSceneAssets; ?>;

	$(function(){

		// Ajout des assets déjà présents dans la div de droite
		if(assetsInitList.length >= 1) {
			$.each(assetsInitList, function(i,idAsset) {
				var item = $('#projectAssetsList .assetItemCateg[assetID="'+idAsset+'"]');
				addAssetToScene(item);
			});
		}

		// Ouverture des catégories
		$('#projectAssetsList .assetCategHead').click(function(){
			$('#projectAssetsList .assetCategList').hide(150);
			$(this).next('.assetCategList').show(150);
		});

		// Click sur un asset dans la liste du projet (pour ajouter / enlever)
		$('#projectAssetsList .assetItemCateg').click(function() {
			if ($(this).hasClass('ui-state-activeFake'))
				removeAssetToScene($(this));
			else
				addAssetToScene($(this));
		}).hover(
			function(){ $(this).addClass('ui-state-hover'); },
			function(){ $(this).removeClass('ui-state-hover'); }
		);

		// click sur un asset dans la liste de la scène (pour enlever)
		$('#sceneAssetsList').off('click','.assetItemCateg');
		$('#sceneAssetsList').on('click','.assetItemCateg', function(){
			removeAssetToScene($(this));
		});

	});

	// add asset in the scene's list
	function addAssetToScene (assetDiv) {
		var idAsset = assetDiv.attr('assetID');
		assetSceneList.push(idAsset);
		assetDiv.addClass('ui-state-activeFake').clone().appendTo('#sceneAssetsList');
	}
	// remove asset from the scene's list
	function removeAssetToScene (assetDiv) {
		var idAsset = assetDiv.attr('assetID');
		var index = assetSceneList.indexOf(idAsset);
		if (index > -1)
			assetSceneList.splice(index, 1);
		$('#projectAssetsList .assetItemCateg[assetID="'+idAsset+'"]').removeClass('ui-state-activeFake');
		$('#sceneAssetsList .assetItemCateg[assetID="'+idAsset+'"]').remove();
	}
</script>


<div class="inline top demi bordBankSection">
	<div class="ui-state-default pad3 gras center">Project's assets</div>
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
</div><div class="inline top demi">
	<div class="ui-state-default pad3 gras center">Master scene's assets</div>
	<div class="pad5" id="sceneAssetsList">

	</div>
</div>

<?php
}
catch(Exception $e) { echo('<div class="marge5 ui-state-error ui-corner-all pad5">'.$e->getMessage().'</div>'); }
?>