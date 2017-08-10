<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC'].'/checkConnect.php' );
	require_once('vignettes_fcts.php');

	extract($_POST);

	if (!isset($sceneID))
		die('<p class="marge10l gros leftText ui-state-disabled">Select a scene.</p>');
	if (!(isset($dept) && isset($deptID)))
		die('<div class="marge5 ui-state-error ui-corner-all pad5">department undefined !</div>');

try {

	$ACL = new ACL($_SESSION['user']);
	$sceneAdmin = $ACL->check('SCENES_ADMIN');

	$sc = new Scenes($sceneID);
	$idC	 = $sc->getSceneInfos(Scenes::ID_CREATOR);
	$creator = new Users((int)$idC);
	$pseudoCreator	 = $creator->getUserInfos(Users::USERS_PSEUDO);

	$filles = $sc->getDerivatives();
	$nbDeriv = count($sc->getDerivatives(true));

	$hungByID	= $sc->getHandler();
	$hungBy		= $sc->getHandler('pseudo');
	$review		= '';			// $sc->getReview();
	$styleHung	= ($hungBy) ? 'gras ui-state-error padV5' : '';
	$hungIcon	= ($hungBy) ? 'locked' : 'unlocked';
	$hungIconSt	= ($hungBy) ? 'error' : 'default';
	$hungByName	= ($hungBy) ? L_ASSET_HUNG_BY.' '.$hungBy : L_ASSET_FREE;


	$authBase	  = ($hungBy === false || $hungByID == $_SESSION['user']->getUserInfos(Users::USERS_ID) || $_SESSION['user']->isSupervisor()) ? true : false;
	$hungByList	= ($_SESSION['user']->isSupervisor()) ? 'liste' : 'onlyYou';
	$authHandling = false;
	$authMessage  = false;

	// recherche de la vignette de la scene
	$vignetteScene = check_scene_vignette($sceneID, $idProj);

	// Affichage de la description si existe
	$description = $sc->getSceneInfos(Scenes::DESCRIPTION);
	if ($description == '') $description = '<span class="ui-state-disabled"><i>No description</i></span>';

	// Récupère la liste des gens assignables au Hanging de la scene
	$p			= new Projects($idProj);
	$teamAll	= $p->getEquipe();

	// Récupère la liste de la team de la scene
	$strTeam = $sc->getSceneTeam('str');
	$sceneTeam = $sc->getSceneTeam('arrayIDs');

	// Récupère la liste des étapes dispo pour ce dept
	$deptInfos	  = new Infos(TABLE_DEPTS);
	$deptInfos->loadInfos('id', $deptID);
	$deptName = $deptInfos->getInfo('label');
	$sceneStatus = json_decode($deptInfos->getInfo('etapes'));
	$activeStatus = $sc->getStatus($deptID);
	$step = '<small><i>undefined status</i></small>';
	foreach ($sceneStatus as $idStatus => $stepName) {
		if ($activeStatus != $idStatus) continue;
		$step = $stepName;
	}
	// Récupère tous les départements actifs de la scene (pour griser les boutons depts)
	$sceneAllDepts = $sc->getDeptsInfos();
	$jsDeptsArray = "[";
	if (is_array($sceneAllDepts)) {
		foreach($sceneAllDepts as $deptLabel => $deptInfo) {
			if (isset($deptInfo['sceneStep'])) {
				$jsDeptsArray .= "'$deptLabel',";
				if ($deptLabel == $dept) { $authMessage = true; $authHandling = $authBase; }
			}
			else {
				if ($deptLabel == $dept) $authHandling = false;
			}
		}
		$jsDeptsArray = trim($jsDeptsArray, ',');
	}
	$jsDeptsArray .= "]";

	$nbRetakes = count($sc->getRetakesList($deptID));

	// Récupère la liste des assets assignés à la scène
	$sceneAssets = $sc->getSceneAssets();
	$assetsCategs = $_SESSION['CONFIG']['ASSETS_CATEGORIES'];
	$sortedAssetsList = Array(); $i = 0;
	foreach($sceneAssets as $assetID) {
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

	// Récupère le(s) shot(s) de la scène
	$sceneShots = $sc->getShots();
	if (!is_array($sceneShots) && $sceneShots != 0)
		$sceneShots = Array($sceneShots);

	$countShots = 0;
	if (is_array($sceneShots)) {
		foreach ($sceneShots as $shotScF) {
			if (!is_array($shotScF)) continue;
			foreach ($shotScF as $sScF)
				$countShots++;
		}
	}

	$scNameRacc = $sc->getSceneInfos(Scenes::TITLE);
	if (strlen($scNameRacc) >= 30)
		$scNameRacc = substr($scNameRacc, 0, 30) . '...';
?>
<script>
	$(function(){
		$('.bouton').button();
		$('.bouton[active]').addClass('ui-state-activeFake');
		$('#parentSceneName').html('<?php echo $scNameRacc; ?>').attr('title', '<?php echo $sc->getSceneInfos(Scenes::TITLE); ?>');

		if (localStorage['openScene_'+idProj])
			$('.sceneFilleItem[sceneID="'+localStorage['openScene_'+idProj]+'"]').addClass('colorHard');

		// Ouvre la dernière section visitée
		if (localStorage['disp_scenes_parent']) {
			$('#'+localStorage['disp_scenes_parent']).show();
			$('.menuSceneBtn[type="parent"]').removeClass('shadowOut fondHigh');
			$('.menuSceneBtn[show="'+localStorage['disp_scenes_parent']+'"]').addClass('shadowOut fondHigh');
		}
		else {
			$('#iScParent_deriv').show();
			localStorage['disp_scenes_parent'] = 'iScParent_deriv';
		}

		$('#addDerivBtnAux').click(function(){
			if (localStorage['typeScene_'+idProj] != 'master') {
				openSceneRight(localStorage['typeScene_'+idProj]);
				setTimeout(function(){
					$('.sceneFilleItem').removeClass('colorHard');
					$('.menuSceneBtn[show="iScSelected_deriv"]').click();
					$('#addDerivBtn').click();
				}, 800);
				return;
			}
			$('.menuSceneBtn[show="iScSelected_deriv"]').click();
			$('#addDerivBtn').click();
		});

		// Ouverture des catégories assets
		$('#sectionSceneParent .assetCategHead').click(function(){
			$('#sectionSceneParent .assetCategList').hide(150);
			$(this).next('.assetCategList').show(150);
		});
	});
</script>


<div class="petit center doigt colorSoft marge5">
	<span class="fondPage ui-corner-bottom pad5 menuSceneBtn shadowOut fondHigh" type="parent" show="iScParent_deriv" help="scenes_master_infos_derivates"><?php echo L_DERIVATIVES; ?></span>
	<span class="fondPage ui-corner-bottom pad5 menuSceneBtn" type="parent" show="iScParent_infos" help="scenes_master_scene_infos"><?php echo L_INFOS; ?></span>
	<span class="fondPage ui-corner-bottom pad5 menuSceneBtn" type="parent" show="iScParent_assets" help="scenes_master_infos_assets"><?php echo L_ASSETS; ?></span>
	<span class="fondPage ui-corner-bottom pad5 menuSceneBtn" type="parent" show="iScParent_shots" help="scenes_master_infos_shots"><?php echo L_SHOTS; ?></span>
</div>

<div class="iScParentDiv pad5 hide" id="iScParent_infos" help="scenes_master_scene_infos">
	<?php if ($sceneAdmin): ?>
	<div class="margeTop5" title="ID">
		<span class="inline mid">&nbsp#&nbsp</span>
		<span class="inline mid colorSoft margeTop1"><?php echo $sc->getSceneInfos(Scenes::ID_SCENE); ?></span>
	</div>
	<?php endif; ?>
	<div class="margeTop5" title="Label">
		<span class="inline mid ui-icon ui-icon-folder-collapsed"></span>
		<span class="inline mid colorSoft margeTop1"><?php echo $sc->getSceneInfos(Scenes::LABEL); ?></span>
	</div>
	<div class="margeTop5" title="<?php echo L_SUPERVISOR; ?>">
		<span class="inline mid ui-icon ui-icon-lightbulb"></span>
		<span class="inline mid colorSoft margeTop1"><?php echo $sc->getSceneSupervisor(); ?></span>
	</div>
	<div class="margeTop5" title="<?php echo L_LEAD; ?>">
		<span class="inline mid ui-icon ui-icon-person"></span>
		<span class="inline mid colorSoft margeTop1"><?php echo $sc->getSceneLead(); ?></span>
	</div>
	<div class="margeTop5" title="<?php echo L_TEAM; ?>">
		<span class="inline mid ui-icon ui-icon-person"></span>
		<span class="inline mid colorSoft margeTop1"><?php echo $strTeam; ?></span>
	</div>
	<?php if ($hungBy): ?>
		<div class="margeTop10" title="<?php echo L_ASSET_HANDLE; ?>">
			<div class="inline ui-state-error ui-corner-all">
				<span class="inline mid ui-icon ui-icon-person"></span>
				<span class="inline mid margeTop1"><?php echo $hungByName; ?></span>&nbsp;&nbsp;
			</div>
		</div>
	<?php endif; ?>
<!--	<div class="margeTop10" title="<?php echo L_FRAMES; ?>">
		<span class="inline mid ui-icon ui-icon-image"></span>
		<span class="inline mid colorSoft margeTop1"><?php echo $sc->getSceneInfos(Scenes::NB_FRAMES) . ' ' . L_FRAMES; ?></span>
	</div>
	<div class="margeTop5" title="<?php echo L_FPS; ?>">
		<span class="inline mid ui-icon ui-icon-signal"></span>
		<span class="inline mid colorSoft margeTop1"><?php echo $sc->getSceneFPS() . ' ' . L_FPS; ?></span>
	</div>-->
	<div class="margeTop5" title="<?php echo L_START; ?>">
		<span class="inline mid ui-icon ui-icon-clock"></span>
		<span class="inline mid colorSoft margeTop1"><?php echo SQLdateConvert($sc->getSceneInfos(Scenes::DATE)); ?></span>
	</div>
	<div class="margeTop5" title="<?php echo L_END; ?>">
		<div class="inline mid ui-state-error-text">
			<span class="inline mid ui-icon ui-icon-clock"></span>
			<span class="inline mid colorSoft margeTop1"><?php echo SQLdateConvert($sc->getSceneInfos(Scenes::DEADLINE)); ?></span>
		</div>
	</div>
	<div class="margeTop5" title="<?php echo L_STATUS; ?>">
		<span class="inline mid ui-icon ui-icon-arrowthickstop-1-e"></span>
		<span class="inline mid colorSoft margeTop1"><?php echo $step; ?></span>
	</div>
	<div class="margeTop5 marge15bot" title="<?php echo L_RETAKES; ?>">
		<span class="inline mid ui-icon ui-icon-clipboard"></span>
		<span class="inline mid colorSoft margeTop1"><?php echo strtoupper($deptName); ?> : <?php echo $nbRetakes .' '. L_RETAKES; ?></span>
	</div>

	<div class="colorDiscret sceneDescriptionDiv" title="<?php echo L_DESCRIPTION; ?>">
		<?php echo nl2br($description); ?>
	</div>

	<div class="center margeTop10">
		<img src="<?php echo $vignetteScene; ?>" height="75" />
	</div>

	<div class="enorme colorDark marge30l">
		<div class="marge30l"><?php echo $nbDeriv.' '.L_DERIVATIVES; ?></div>
		<div class="marge30l"><?php echo count($sceneAssets); ?> <?php echo L_ASSETS; ?></div>
		<div class="marge30l">0 <?php echo L_SHOTS; ?></div>
	</div>
</div>

<div class="iScParentDiv pad5 hide" id="iScParent_assets" help="scenes_master_infos_assets">
	<div class="floatR margeTop10 colorMid"><?php echo count($sceneAssets); ?> <?php echo L_ASSETS; ?></div>
	<div class="margeTop5 nano">
		<button class="bouton marge10r addAssetsBtn" sceneID="<?php echo $sceneID; ?>" sceneTitle="<?php echo $sc->getSceneInfos(Scenes::TITLE); ?>" title="Manage assets (MASTER)">
			<span class="inline mid terra">Manage assets (MASTER)</span>
			<span class="inline mid ui-icon ui-icon-plusthick"></span>
		</button>
	</div>
	<?php if(count($sortedAssetsList) >= 1): ?>
	<div class="margeTop5">
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
			<div class='ui-state-default doigt assetItemScene' assetID='<?php echo $item['id']; ?>' filename='<?php echo $item['name']; ?>' filePath='<?php echo $item['path']; ?>'>
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
		<div class="marge5 ui-state-disabled pad5">No asset in this scene yet.</div>
	<?php endif; ?>
</div>

<div class="iScParentDiv pad5 hide" id="iScParent_deriv" help="scenes_master_infos_derivates">
	<div class="floatR margeTop10 colorMid">
		<?php echo $nbDeriv.' '.L_DERIVATIVES; ?>
	</div>
	<div class="margeTop5 nano">
		<button class="bouton" title="Add a Derivative" id="addDerivBtnAux">
			<span class="inline mid terra">Create derivative</span>
			<span class="inline mid ui-icon ui-icon-plusthick doigt"></span>
		</button>
	</div>
	<div class="pad5 margeTop5 colorBtnFake">
	<?php if (is_array($filles) && count($filles) > 0):
			foreach($filles as $filleID => $fille):
				$colorF = '';
				if ($fille[Scenes::ARCHIVE] == '1') {
					if ($_SESSION['user']->isSupervisor())
						$colorF = 'gray-layer';
					else continue;
				} ?>
			<div class="<?php echo $colorF; ?> margeTop5 doigt sceneFilleItem" title="<?php echo $fille[Scenes::TITLE]; ?>" sceneID="<?php echo $filleID; ?>">
				<?php echo $fille[Scenes::TITLE]; ?>
			</div>
		<?php endforeach;
		else: ?>
			<i class="colorDark">Aucune scène fille</i>
	<?php endif; ?>
	</div>
</div>

<div class="iScParentDiv hide" id="iScParent_shots" help="scenes_master_infos_shots">
	<div class="floatR margeTop5 pad5 colorMid">
		<?php echo $countShots.' '.L_SHOTS; ?>
	</div>
	<div class="margeTop10 pad5 nano">
		<button class="bouton marge10r assignMasterShotsBtn" sceneID="<?php echo $sceneID; ?>" sceneTitle="<?php echo $sc->getSceneInfos(Scenes::TITLE); ?>" title="Assign shots to derivatives...">
			<span class="inline mid terra"><?php echo L_ASSIGN.' '.L_SHOTS; ?> (MASTER)</span>
			<span class="inline mid ui-icon ui-icon-copy"></span>
		</button>
	</div>
	<div class=""><?php
		if (is_array($sceneShots)):
			foreach($sceneShots as $filleID=>$shots):
				$scF = new Scenes((int)$filleID);
				$filleTitle = $scF->getSceneInfos(Scenes::TITLE); ?>
				<div class="margeTop5 pad5 colorBtnFake"><?php echo $filleTitle; ?></div>
			<?php foreach($shots as $shotID):
					$sh = new Shots($shotID);
					$shot = $sh->getShotInfos();
					$seqID = (int)$shot[Shots::SHOT_ID_SEQUENCE];
					$se = new Sequences($seqID);
					$seq = $se->getSequenceInfos();
					$vignette = check_shot_vignette_ext($idProj, $seq[Sequences::SEQUENCE_LABEL], $shot[Shots::SHOT_LABEL]);
					?>
					<div class="ui-state-default doigt sceneShotOpener" seqID="<?php echo $seqID; ?>" shotID="<?php echo $shotID; ?>" title="<?php echo $filleTitle; ?>">
						<div class="inline mid w50 pad3"><img src="<?php echo $vignette; ?>" width="45" height="25" /></div>
						<div class="inline mid w150 colorHi" title="Shot title">
							<?php echo $shot[Shots::SHOT_TITLE]; ?>
							<span class="colorDiscret petit" title="Sequence title">(<?php echo $seq[Sequences::SEQUENCE_TITLE]; ?>)</span>
						</div>
					</div>
		<?php endforeach;
		endforeach;
		else: ?>
			<div class="pad5 ui-state-disabled">No shot assigned to this scene yet.</div>
		<?php endif; ?>
	</div>
</div>


<?php
}
catch(Exception $e) { echo('<div class="marge5 ui-state-error ui-corner-all pad5">'.$e->getMessage().'</div>'); }
?>