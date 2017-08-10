<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC'].'/checkConnect.php' );
	require_once('vignettes_fcts.php');
	require_once('xml_fcts.php');

	extract($_POST);

	if (!isset($nameAsset))
		die('<p class="marge10l gros leftText ui-state-disabled">Select an asset.</p>');
	if (!isset($pathAsset))
		die('<div class="ui-state-error ui-corner-all pad5">asset path undefined !</div>');
	if (!(isset($dept) && isset($deptID)))
		die('<div class="ui-state-error ui-corner-all pad5">department undefined !</div>');

	$assetsCategs = $_SESSION['CONFIG']['ASSETS_CATEGORIES'];

try {

	$ACL = new ACL($_SESSION['user']);
	$assetAdmin = $ACL->check('ASSETS_ADMIN');

	$asset	 = new Assets($idProj, $nameAsset, $pathAsset);
	$IDasset = $asset->getIDasset(Assets::ASSET_ID);
	$vAsset	 = $asset->getVersion();
	$categId = $asset->getInfo(Assets::ASSET_CATEGORY);
	$idC	 = $asset->getInfo(Assets::ASSET_ID_CREATOR);
	$isActive= $asset->isActive();
	$creator = new Users((int)$idC);
	$pseudoCreator	 = $creator->getUserInfos(Users::USERS_PSEUDO);
	$dependancesList = $asset->getDependencies();
	$countDep		 = count($dependancesList);
	$countScenes	 = $asset->getNbScenes();
	$assetInXML		 = asset_exists_xml($idProj, $nameAsset);

	if (!$assetAdmin && !$isActive)
		die('<p class="marge5 pad10 gros colorErreur">THIS ASSET IS INACTIVE. Please contact the supervisor.</p>');

	// Gestion des Hung By
	$hungByID	= $asset->getHandler();
	$hungBy		= $asset->getHandler('pseudo');
	$review		= $asset->getReview();
	$styleHung	= ($hungBy) ? 'gras ui-state-error padV5' : '';
	$hungIcon	= ($hungBy) ? 'locked' : 'unlocked';
	$hungIconSt	= ($hungBy) ? 'error' : 'default';
	$hungByName	= ($hungBy) ? L_ASSET_HUNG_BY.' '.$hungBy : L_ASSET_FREE;
	$hungByList	= ($_SESSION['user']->isSupervisor()) ? 'liste' : 'onlyYou';

	// recherche de la vignette de l'asset
	$vignetteAsset = check_asset_vignette($pathAsset, $nameAsset, $idProj);

	// Affichage de la description si existe
	$description = $asset->getInfo(Assets::ASSET_DESCRIPTION);
	if ($description == '') $description = '<span class="ui-state-disabled"><i>No description</i></span>';

	// Récupère la liste des gens assignables au Hanging de l'asset
	$p			= new Projects($idProj);
	$teamAll	= $p->getEquipe();

	// Récupère la liste de la team de l'asset
	$assetTeam	= $asset->getTeamAsset();
	$strTeam	= (count($assetTeam) == 0) ? L_NOBODY : '';
	foreach ($assetTeam as $idUteam) {
		try {
			$u = new Users((int)$idUteam);
			$strTeam .= $u->getUserInfos(Users::USERS_PSEUDO) . ', ';
		}
		catch(Exception $e) {  }
	}
	$strTeam = trim($strTeam, ', ');

	// Récupère la liste des étapes dispo pour ce dept
	$deptInfos	  = new Infos(TABLE_DEPTS);
	$deptInfos->loadInfos('id', $deptID);
	$assetsStatus = json_decode($deptInfos->getInfo('etapes'));
	$activeStatus = $asset->getStatus($idProj, $deptID);

	// Récupère tous les départements actifs de l'asset (pour griser les boutons depts)
	$assetAllDepts = $asset->getDeptsInfos($idProj);
	$deptFound = false;
	$jsDeptsArray = "[";
	if (is_array($assetAllDepts)) {
		foreach($assetAllDepts as $deptLabel => $deptInfo) {
			if (isset($deptInfo['assetStep'])) {
				$jsDeptsArray .= "'$deptLabel',";
				if ($deptLabel == $dept)
					$deptFound = true;
			}
		}
		$jsDeptsArray = trim($jsDeptsArray, ',');
	}
	$jsDeptsArray .= "]";

	// Définition de la condition pour le bloquage de l'asset
	$authMessage = $authPublish = $authHandling = false;
	if ($deptFound && $assetInXML) {
		$authMessage  = $ACL->check('ASSETS_MESSAGE', 'asset:'.$IDasset);
		$authPublish  = $ACL->check('ASSETS_PUBLISH', 'asset:'.$IDasset);
		$authHandling = ($hungBy === false || $hungByID == $_SESSION['user']->getUserInfos(Users::USERS_ID) || $_SESSION['user']->isSupervisor());
	}

	//	Liste des dépendances
	$sortedDepList = Array();
	if ($countDep > 0):
		foreach($dependancesList as $i => $dependName):
			try {
				$a = new Assets($idProj, $dependName);
				$depCategId = $a->getInfo(Assets::ASSET_CATEGORY);
				$depCateg	= ($depCategId != 0) ? $assetsCategs[$depCategId] : 'uncategorized';
				$sortedDepList[$depCateg][$i]['name']		= $dependName;
				$sortedDepList[$depCateg][$i]['path']		= $a->getPath($idProj);
				$sortedDepList[$depCateg][$i]['handler']	= $a->getHandler('pseudo');
				$sortedDepList[$depCateg][$i]['dateModif']	= $a->getLastModifDate();
				$sortedDepList[$depCateg][$i]['vignette']	= check_asset_vignette($a->getPath($idProj), $dependName, $idProj);
			}
			catch(Exception $e) { continue; }
		endforeach;
		ksort($sortedDepList);
	endif;
}
catch (Exception $e) { die('<div class="ui-state-error ui-corner-all pad5">'.$e->getMessage().'</div>'); }

// Récupère le nom de la catégorie de l'asset courant
$categName = ($categId != 0) ? $assetsCategs[$categId] : '<span class="ui-state-disabled"><i>No category</i></span>';


?>
<script>
	var idAsset		  = <?php echo $IDasset; ?>;
	var nameAsset	  = '<?php echo $nameAsset; ?>';
	var pathAsset	  = '<?php echo $pathAsset; ?>';
	var creatorAsset  = '<?php echo $pseudoCreator; ?>';
	var authHandling  = <?php echo ($authHandling) ? 'true' : 'false'; ?>;
	var hungByList	  = '<?php echo $hungByList; ?>';
	var disabledDepts = <?php echo $jsDeptsArray; ?>;
	var hasStep		  = <?php echo ($deptFound) ? 'true' : 'false'; ?>;
	localStorage['openAsset_'+idProj] = nameAsset;
	localStorage['openAssetPath_'+idProj] = pathAsset;

	$(function(){
		$('.bouton').button();
		$('.bouton[active]').addClass('ui-state-activeFake');
		$('#displayPathAsset').html(pathAsset+nameAsset+'<span class="marge30l ui-state-default noBG noBorder">v#<?php echo sprintf('%03d',$vAsset); ?></span>');
	});
</script>
<script src="ajax/depts/dept_assets.js"></script>


<div id="asset_review">
	<?php if ($review != ''): ?>
	<div class="ui-state-error gras padV5 margeTop1 fleche" title="<?php echo $review; ?>">
		<div class="inline mid"><span class="ui-icon ui-icon-alert"></span></div>
		<div class="inline mid">New version TO REVIEW</div>
	</div>
	<?php endif; ?>
</div>

<div id="asset_hungBy">

	<div class="inline mid <?php echo $styleHung; ?>">
		<?php if (!$authHandling): ?>
		<div title="The asset must be validated in tree, and a step must be specified in order to handle it." class="inline mid ui-state-error noBg noBorder margeTop1">
			<span class="ui-icon ui-icon-locked"></span>
		</div>
		<?php endif; ?>
		<div class="inline mid noMarge"><?php echo $hungByName; ?></div>
	</div>
	<?php if ($authHandling): ?>
		<div class="inline mid ui-state-<?php echo $hungIconSt; ?> ui-corner-all doigt margeTop1" id="btn_HandleAsset" title="Change <?php echo L_ASSIGNMENTS; ?>">
			<span class="ui-icon ui-icon-<?php echo $hungIcon; ?>"></span>
		</div>

		<div class="ui-corner-left fondSect1 shadowOut pad10 leftText modifBox hide" id="handlerModifBox">
			<div class="floatR doigt closeModifBox"><span class="ui-icon ui-icon-closethick"></span></div>
			<div class="inline colorActiveFolder marge15bot gros gras"><?php echo L_ASSIGNMENTS; ?></div>
			<div class="handlersList"><?php
				if ($hungBy): ?>
					<button class="bouton margeTop1 handlerModifLine ui-state-focusFake" idHandler="0" title="<?php echo L_NOBODY; ?>"><?php echo L_FREE_ASSET; ?></button><br /><br />
				<?php endif;
				if ($hungByList == 'liste'):
					foreach ($teamAll as $idU => $pseudoU):
						$state = ($idU == $hungByID) ? 'ui-state-highlight' : ''; ?>
					<button class="bouton margeTop1 <?php echo $state; ?> handlerModifLine" idHandler="<?php echo $idU; ?>"><?php echo $pseudoU; ?></button><br />
				<?php
					endforeach;
				elseif (!$hungBy): ?>
					<button class="bouton margeTop1 handlerModifLine" idHandler="<?php echo $_SESSION['user']->getUserInfos(Users::USERS_ID); ?>"><?php echo L_ASSET_HANDLE; ?></button><br />
				<?php endif;?>
			</div>
		</div>
	<?php endif; ?>

</div>

<div id="vignetteAsset">
	<img src="<?php echo $vignetteAsset; ?>" width="270" height="150" />
	<input class="hide" type="file" name="files[]" id="vignetteAsset_upload" />
</div>

<div id="infosAsset">
	<div id="showAssetTags">
        <button class="bouton showTasks" section="<?php echo Tasks::SECTION_ASSETS; ?>" entity="<?php echo $IDasset; ?>" title="<?php echo L_TASKS; ?>"><span class="ui-icon ui-icon-bookmark"></span></button>
		<button class="bouton" disabled title="<?php echo L_TAGS; ?>" id="showAssetTagsBtn"><span class="ui-icon ui-icon-tag"></span></button>
	</div>

	<div id="divAssetStatus">
		<?php if (!$isActive): ?>
			<div class="inline top pad5" style="width:calc(100% - 175px);">
				<div class="inline mid colorErreur">
					<?php if (LANG == 'fr'): ?>
					<b class="colorErrText">ATTENTION !</b><br />
					Cet asset est inactif (caché ou archivé).
					<?php else: ?>
					<b class="colorErrText">WARNING!</b><br />
					This asset is inactive (hidden or archived).
					<?php endif; ?>
				</div>
				<div class="inline mid mini">
					<button class="bouton setHideAssetOff"><span class="inline bot ui-icon ui-icon-lightbulb"></span> <?php echo L_SHOW; ?></button>
				</div>
			</div>
		<?php else: ?>
			<?php if ($assetAdmin && $assetInXML):
				$warnStep = ($deptFound) ? '' : 'bordHi'; ?>
				<div class="mini padB5 ui-corner-all <?php echo $warnStep; ?>">
					<?php foreach ($assetsStatus as $idStatus => $step):
						$active = ($activeStatus == $idStatus) ? 'active' : ''; ?>
						<button class="bouton margeTop1 modifAssetStatus" <?php echo $active; ?> title="STEP: <?php echo $step; ?>" idStatus="<?php echo $idStatus; ?>"><?php echo $step; ?></button>
					<?php endforeach;
					$activeDone = ($activeStatus == 99) ? 'active' : ''; ?>
					<button class="bouton margeTop1 modifAssetStatus" <?php echo $activeDone; ?> title="STEP: <?php echo L_APPROVED; ?>" idStatus="99"><?php echo L_APPROVED; ?></button>
				</div>
			<?php elseif($assetInXML): ?>
				<div class="mini pad3 margeTop5 fleche">
					<?php foreach ($assetsStatus as $idStatus => $step):
						$active = ($activeStatus == $idStatus) ? 'ui-state-active' : 'fondSect4'; ?>
						<span class="pad5 ui-corner-all <?php echo $active; ?>" idStatus="<?php echo $idStatus; ?>"><?php echo $step; ?></span>
					<?php endforeach;
					$activeDone = ($activeStatus == 99) ? 'ui-state-active' : 'fondSect4'; ?>
					<span class="pad5 ui-corner-all <?php echo $activeDone; ?>" idStatus="99"><?php echo L_APPROVED; ?></span>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>

	<?php if (!$assetInXML): ?>
		<div class="inline top pad10" style="width:calc(100% - 175px);">
			<div class="">
				<?php if (LANG == 'fr'): ?>
				<b class="colorErrText">ATTENTION !</b><br />
				Cet asset a été créé dans la base, mais n'est pas encore pris en <br />
				compte dans l'arborescence.	Vous pouvez encore le renommer, changer <br />
				son chemin, le supprimer, ou bien le valider.
				<?php elseif (LANG == 'de'): ?>
				<b class="colorErrText">ACHTUNG!</b><br />
				Diese Asset ist angelegt, aber ist noch nicht in Foldern<br />
				Tree vorliegenden. Sie können immer noch umbenennen,<br />
				ändern ordenen, löschen, oder bestätigen.
				<?php else: ?>
				<b class="colorErrText">WARNING!</b><br />
				This asset have been created, but isn't present in assets tree yet.<br />
				You can still rename, change path, delete, or validate it.
				<?php endif; ?>
			</div>
			<?php if ($assetAdmin): ?>
			<div class="nano margeTop10">
				<button class="bouton" title="<?php echo L_RENAME.' '.L_ASSET; ?>" style="padding:1px;" id="renameAssetDB" idAsset="<?php echo $IDasset; ?>">
					<span class="inline mid ui-icon ui-icon-pencil"></span>
				</button>
				<button class="bouton marge10l" title="<?php echo L_MODIFY.' '.L_PATH; ?>" style="padding:1px;" id="repathAssetDB" idAsset="<?php echo $IDasset; ?>">
					<span class="inline mid ui-icon ui-icon-folder-collapsed"></span>
				</button>
				<button class="bouton ui-state-error marge10l" title="<?php echo L_DELETE.' '.L_ASSET; ?>" id="deleteAssetDB" idAsset="<?php echo $IDasset; ?>">
					<span class="inline mid ui-icon ui-icon-trash"></span>
				</button>
				<button class="bouton ui-state-highlight marge30l" title="<?php echo L_BTN_VALID; ?>" id="addAssetToXML" idAsset="<?php echo $IDasset; ?>">
					<span class="inline mid ui-icon ui-icon-check"></span> <span class="inline mid terra"><?php echo L_BTN_VALID; ?></span>
				</button>
			</div>
		<?php endif; ?>
		</div>
	<?php else: ?>
	<div class="colorSoft" title="Description" id="divAssetDescr">
		<?php if ($assetAdmin && $assetInXML): ?>
		<div class="floatL pico" style="margin: 10px 5px 0 0;" title="Modify description">
			<button class="bouton" id="modifDescrBtn"><span class="ui-icon ui-icon-pencil"></span></button>
			<div class="hide modifBtns">
				<button class="bouton ui-state-highlight" id="modifDescrOK"><span class="ui-icon ui-icon-check"></span></button><br />
				<button class="bouton ui-state-error" id="modifDescrANNUL"><span class="ui-icon ui-icon-cancel"></span></button>
			</div>
		</div>
		<?php endif; ?>
		<p id="assetDescription"><?php echo nl2br($description); ?></p>
	</div>
	<?php endif; ?>

	<div class="noMarge" id="divAssetDetail" nameAsset="<?php echo $nameAsset; ?>" pathAsset="<?php echo urlencode($pathAsset); ?>">
	<?php if (isset($errCateg)) : echo $errCateg;
		   else:
			   if ($assetAdmin): ?>
			<div class="ui-corner-all fondSect1 shadowOut pad10 leftText modifBox hide" id="categModifBox">
				<div class="floatR doigt closeModifBox"><span class="ui-icon ui-icon-closethick"></span></div>
				<?php foreach($assetsCategs as $idCat => $categ):
						$state = ($categId == $idCat) ? 'ui-state-highlight' : '';?>
						<button class="bouton <?php echo $state; ?> margeTop1 categModifLine" idCat="<?php echo $idCat; ?>"><?php echo $categ; ?></button><br />
				<?php endforeach; ?>
			</div>
	<?php endif; endif; ?>
		<div class="inline top marge10l margeTop5 colorHard">
			<div class="inline mid w150 leftText">
				<?php if ($assetAdmin): ?>
					<?php if ($isActive): ?>
					<div class="inline mid pico" title="Hide asset">
						<button class="bouton setHideAssetOn"><span class="ui-icon ui-icon-lightbulb"></span></button>
					</div>
					<?php endif; ?>
				<div class="inline mid pico" title="Modify category">
					<button class="bouton" id="modifCategBtn" idCateg="<?php echo $categId; ?>"><span class="ui-icon ui-icon-pencil"></span></button>
				</div>
				<?php else: ?>
				<span class="inline mid pad3">&nbsp;&nbsp;&nbsp;&nbsp;</span>
				<?php endif; ?>
				<div class="inline mid" title="Category">
					&nbsp;<span class="inline mid gras"><?php echo @$categName; ?></span>
				</div>
			</div>
			<br />
			<div class="inline mid w150 leftText margeTop1">
				<div class="inline mid pico" title="<?php echo L_ASSIGNMENTS . ' ' . L_SCENES; ?>">
					<button class="bouton" id="showAssetScenes"><span class="ui-icon ui-icon-shuffle"></span></button>
				</div>
				<div class="inline mid" title="<?php echo L_IN_SCENES; ?>">
					<span class="inline mid ui-icon ui-icon-copy"></span>
					<span class="inline mid gras"><?php echo $countScenes; ?></span>
					<span class="inline mid colorDiscret"><?php echo L_SCENES; ?></span>
				</div>
			</div>
			<br />
			<div class="inline mid w150 leftText margeTop1">
				<div class="inline mid pico" title="<?php echo L_ASSETS.' '.L_DEPENDENCIES; ?>">
					<button class="bouton" id="showAssetDependencies"><span class="ui-icon ui-icon-search"></span></button>
				</div>
				<div class="inline mid" title="<?php echo L_ASSETS.' inter-'.L_DEPENDENCIES; ?>">
					<span class="inline mid ui-icon ui-icon-script"></span>
					<span class="inline mid gras"><?php echo $countDep; ?></span>
					<span class="inline mid colorDiscret"><?php echo L_DEPENDENCIES; ?></span>
				</div>
			</div>
			<br />
			<div class="inline mid w150 leftText margeTop1">
				<?php if ($assetAdmin): ?>
				<div class="inline top pico" title="Modify team">
					<button class="bouton" id="showAddArtistToAsset"><span class="ui-icon ui-icon-plus"></span></button>
				</div>
				<?php else: ?>
				<span class="inline mid">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
				<?php endif; ?>
				<span class="inline top ui-icon ui-icon-person" title="<?php echo L_TEAM; ?>"></span>
				<span class="inline top colorDiscret margeTop1" style="width:90px;"><?php echo $strTeam; ?></span>
			</div>
			<div class="inline top" style="display:none; margin-left:-130px;" id="addArtistToAssetDiv">
				<div class="inline mid">
					<select class="mini noPad" title="Artists" id="addArtistToAssetInput" multiple>
						<?php
						foreach ($teamAll as $idA => $pseudoA) {
							$selected = '';
							if (in_array($idA, $assetTeam))
								$selected = 'selected';
							echo '<option class="mini" value="'.$idA.'" '.$selected.'>'.$pseudoA.'</option>';
						}
						?>
					</select>
				</div>
				<div class="inline mid nano">
					<button class="bouton" id="addArtistToAssetBtn"><span class="ui-icon ui-icon-check"></span></button>
				</div>
			</div>
		</div>
	</div>
</div>


<div class="fondSect4" id="assetViewHead">
	<table class="seqTableHead">
		<tr>
			<th class="w20">
				<?php if ($authMessage): ?>
					<div class="marge10l doigt ui-corner-all hide" title="<?php echo L_ADD_MESSAGE; ?>" id="btn_addMessage"><span class="ui-icon ui-icon-mail-closed"></span></div>
				<?php endif; ?>
			</th>
			<th class="colorDiscret center">
				&nbsp;&nbsp;&nbsp;<?php echo L_RETAKE_MESSAGES; ?> <span class="activeShotCenter" id="numRetakeMessages">en cours</span>
			</th>
			<th class="w50">Publish</th>
			<th class="w20">
				<?php if ($authPublish): ?>
					<div class="marge10r ui-corner-all doigt" title="<?php echo L_ADD_RETAKE; ?>" id="btn_addRetake"><span class="ui-icon ui-icon-plusthick"></span></div>
				<?php else: ?>
					<div class="marge10r ui-corner-all ui-state-disabled doigt" title="The asset must be validated in tree, and a step must be specified in order to add a publish." id="btn_addRetakeError"><span class="ui-icon ui-icon-plusthick"></span></div>
				<?php endif; ?>
			</th>
			<th class="activeShotCenter center" style="width:270px;" id="activeRetakeNumber"><?php echo L_NO_RETAKE; ?></th>
		</tr>
	</table>
</div>

<div class="shadowOut" id="assetViewRight">
	<?php include('structure/structure_retakes_assets.php'); ?>
</div>
<div class="pad5" id="assetViewLeft">
	<?php include('structure/structure_messages_assets.php'); ?>
</div>
<div class="fondSect1" id="assetViewFooter">
	<?php include('structure/structure_folders_assets.php'); ?>
</div>




<div class="hide" title="<span class='colorBtnFake'>Change asset's path</span>" id="chPathModele">
	<div class="inline mid w80 colorSoft margeTop5">New Path</div>
	<div class="inline mid margeTop5" title="Enter new Path">
		<input type="text" class="noBorder pad3 ui-corner-all fondSect3 w100p addPathFolder_name" id="changePathInput" size="35" />
	</div>
	<div class="inline mid colorSoft marge5">/</div><div class="inline mid colorSoft margeTop5" id="changePathAssName"></div>
	<p class="colorDiscret" style="margin-left:85px;">Be careful, you can only choose an existing path.<br />The autocompletion tool can help you.</p>
</div>


<div class="hide" title="<span class='colorBtnFake'><?php echo $nameAsset.'</span> : '.$countScenes.' '.strtolower(L_SCENES); ?>" id="assetScenesModal">
	<?php
	if ($countScenes > 0):
		foreach($asset->getScenes() as $sceneID):
			$sc = new Scenes((int)$sceneID);
			$sceneTitle = $sc->getSceneInfos(Scenes::TITLE);
			$sceneVignette = check_scene_vignette($sceneID);
			?>
			<div class="ui-state-default">
				<div class="inline mid"><img src="<?php echo $sceneVignette; ?>" width="60" height="34" /></div>
				<div class="inline mid colorErrText"><?php echo $sceneTitle; ?></div>
			</div><?php
		endforeach;
	else :
		echo L_NOTHING . ' '. L_SCENE.'.';
	endif; ?>

</div>

<div class="hide" title="<span class='colorBtnFake'><?php echo $nameAsset.'</span> : '.$countDep.' '.strtolower(L_DEPENDENCIES); ?>" id="assetDepModal">
	<div>
	<?php
	if ($countDep > 0):
		foreach($sortedDepList as $category => $items): ?>
			</div>
			<div class="marge5bot depCategDiv petit">
				<div class="padH5 gros depCategHead">
					<div class="inline mid doigt"><span class="ui-icon ui-icon-triangle-1-e"></span></div>
					<div class="inline mid doigt ui-state-disabled"><?php echo strtoupper($category); ?></div>
				</div>
				<div class="hide depCategList">
		<?php foreach ($items as $i => $dep):
				$classHandler = 'ui-state-error';
				if ($dep['handler'] == false) {
					$dep['handler'] = L_ASSET_FREE;
					$classHandler = '';
				} ?>
				<div class='ui-state-default doigt assetItemDep marge30r' filename='<?php echo $dep['name']; ?>' filePath='<?php echo $dep['path']; ?>'>
					<div class='floatR leftText marge10r'>
						<div class='margeTop1' title='Asset <?php echo L_ASSET_HUNG_BY; ?>'>
							<div class='inline mid <?php echo $classHandler; ?> noBG noBorder'>
								<span class='ui-icon ui-icon-person'></span>
							</div>
							<div class='inline mid <?php echo $classHandler; ?>'>
								<?php echo $dep['handler']; ?>
							</div>
						</div>
						<div class='margeTop1 ui-state-disabled' title='Asset last modif'>
							<small><?php echo $dep['dateModif']; ?></small>
						</div>
					</div>
					<div class='inline mid center w80' title='<?php echo $dep['path']; ?>'><img src='<?php echo $dep['vignette']; ?>' height='40' /></div>
					<div class='inline mid' title='<?php echo $dep['path']; ?>'><?php echo $dep['name']; ?></div>
				</div>
	<?php	endforeach; ?>
				</div>
<?php endforeach;
	else: ?>
		<div class="center ui-state-disabled"><?php echo '0 '.L_DEPENDENCIES; ?></div>
	<?php endif; ?>
	<div class="margeTop10"><p>&nbsp;</p></div>
</div>