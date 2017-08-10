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
	$sceneTitle = $sc->getSceneInfos(Scenes::TITLE);
	$vScene		= sprintf('%03d', $sc->getSceneInfos(Scenes::VERSION));
	$sceneLabel = $sc->getSceneInfos(Scenes::LABEL);
	$idC	 = $sc->getSceneInfos(Scenes::ID_CREATOR);
	$creator = new Users((int)$idC);
	$pseudoCreator	 = $creator->getUserInfos(Users::USERS_PSEUDO);

	// Type de scène : Master, ou fille -> de quelle scène
	$typeScene = L_DERIVATIVE; $master = false;
	$masterScene = $sc->getMaster();
	if ($masterScene == 0) {
		$master = true;
		$typeScene = L_MASTER;
	}

	// Numeros de scènes
	$labelInfos = explode('_', $sceneLabel);
	$masterNum  = $labelInfos[2];
	if (!$master)
		$derivNum   = $labelInfos[4];

	// Récupère les scènes filles
	$filles = $sc->getDerivatives();
	$nbDeriv = count($sc->getDerivatives(true));
	$nextDerivL	= '#'.substr($sceneLabel, 1).'_D_'.sprintf('%03d', count($sc->getDerivatives(false))+1);
	$authDeriv = ($master && $ACL->check('SCENES_ADMIN'));
	$authAssetsAdd  = ($master && $ACL->check('SCENES_ADMIN'));
	$authAssetsExcl = (!$master && $ACL->check('SCENES_ADMIN'));
	$authShotAssign = ($master && $ACL->check('SCENES_ADMIN'));

	// Style de la deadline (rouge si dépassée)
	$styleDeadline = 'colorSoft';
	if (compare_dates(SQLdateConvert($sc->getSceneInfos(Scenes::DEADLINE), 'format', DATE_ATOM), date(DATE_ATOM), 'date2SUPdate1'))
		$styleDeadline = 'ui-state-error';

	// Récupère l'user qui a pris la main
	$hungByID	= $sc->getHandler();
	$hungBy		= $sc->getHandler('pseudo');
	$review		= '';			// $sc->getReview();
	$styleHung	= ($hungBy) ? 'gras ui-state-error padV5' : '';
	$hungIcon	= ($hungBy) ? 'locked' : 'unlocked';
	$hungIconSt	= ($hungBy) ? 'error' : 'default';
	$hungByName	= ($hungBy) ? L_ASSET_HUNG_BY.' '.$hungBy : L_ASSET_FREE;

	// Autorisations pour prendre la main / ajouter des retakes / messages
	$authBase	  = ($hungBy === false || $hungByID == $_SESSION['user']->getUserInfos(Users::USERS_ID) || $_SESSION['user']->isSupervisor()) ? true : false;
	$hungByList	  = ($_SESSION['user']->isSupervisor()) ? 'liste' : 'onlyYou';
	$authHandling = false;
	$authMessage  = false;

	// recherche de la vignette de la scene
	$vignetteScene = check_scene_vignette($sceneID);

	// Affichage de la description si existe
	$description = $sc->getSceneInfos(Scenes::DESCRIPTION);
	if ($description == '') $description = '<span class="ui-state-disabled"><i>No description</i></span>';

	// Récupère la liste des gens assignables au Hanging de la scene
	$p			= new Projects($idProj);
	$teamAll	= $p->getEquipe();
	$titleProj	= $p->getTitleProject();

	// Récupère la liste de la team de la scene
	$strTeam = $sc->getSceneTeam('str');
	$sceneTeam = $sc->getSceneTeam('arrayIDs');

	// Récupère la liste des étapes dispo pour ce dept
	$deptInfos	  = new Infos(TABLE_DEPTS);
	$deptInfos->loadInfos('id', $deptID);
	$sceneStatus = json_decode($deptInfos->getInfo('etapes'));
	$activeStatus = $sc->getStatus($deptID);

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

	// Récupère la liste des séquences et des shots du projet
	$projSeqs	= $p->getSequences(true);
	$projShots	= $p->getShots('all', 'actifs');

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

	// Récupère les cams
	$sceneCams = $sc->getCameras();
	$countCams = 0; $strCountCam = '001';
	if (is_array($sceneCams)) {
		$countCams = count($sceneCams);
		$strCountCam = sprintf('%03d', $countCams+1);
	}


?>
<script>
	var sceneID		  = '<?php echo $sceneID; ?>';
	var sceneTitle	  = '<?php echo $sceneTitle; ?>';
	var creatorScene  = '<?php echo $pseudoCreator; ?>';
	var authHandling  = <?php echo ($authHandling) ? 'true' : 'false'; ?>;
	var hungByList	  = '<?php echo $hungByList; ?>';
	var disabledDepts = <?php echo $jsDeptsArray; ?>;
	var masterScene   = <?php echo ($master) ? 'true' : 'false'; ?>;
	localStorage['openScene_'+idProj] = sceneID;
	localStorage['typeScene_'+idProj] = '<?php echo ($master) ? 'master' : $masterScene ?>';

	$(function(){
		$('.bouton').button();
		var iScdivHeight = $('#sectionSceneSelect').height() - 202;
		$('.showSceneShots, .showAssetsScene').slimScroll({
			position: 'right',
			height: iScdivHeight+'px',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		});
		$('.bouton[active]').addClass('ui-state-activeFake');
		$('#selectedSceneName').html('<?php echo $sc->getSceneInfos(Scenes::TITLE).' <span class="marge30l ui-state-default noBG noBorder">v#'.$vScene.'</span>'; ?>');
		$('#selectedSceneType').html('<?php echo mb_convert_case($typeScene, MB_CASE_UPPER); ?>').show();
		<?php if($master): ?>
			$('#selectedSceneType').removeClass('ui-state-highlight').addClass('ui-state-error');
			$('#selectedSceneName').removeClass('colorHard').addClass('colorErrText');
		<?php else: ?>
			$('#selectedSceneType').removeClass('ui-state-error').addClass('ui-state-highlight');
			$('#selectedSceneName').removeClass('colorErrText').addClass('colorHard');
		<?php endif; ?>
		$(".miniCal" ).datepicker({dateFormat: 'dd/mm/yy', firstDay: 1, changeMonth: false, changeYear: false});
		$('.modifSelect').selectmenu({style: 'dropdown'});
		$('.inputCal').datepicker({dateFormat: 'dd/mm/yy', firstDay: 1, changeMonth: true, changeYear: true});
		$('.addDeriv_supervisor').selectmenu({style: 'dropdown'});
		$('.addDeriv_lead').selectmenu({style: 'dropdown'});
		$('.addDeriv_team').multiselect({height: '200px', minWidth: 206, selectedList: 2, noneSelectedText: '<?php echo L_NOBODY; ?>', selectedText: '# artists', checkAllText: ' ', uncheckAllText: ' '});
		$('#addArtistToSceneInput').multiselect({height: '340px', selectedList: 4, noneSelectedText: 'Aucun', selectedText: '# artists', checkAllText: ' ', uncheckAllText: ' '});

		$('#assignSceneSeq, .assignSceneShot, .assignCamShot').selectmenu({style: 'dropdown'});

		// Ouvre la dernière section visitée
		if (localStorage['disp_scenes_selected']) {
			if (!masterScene && localStorage['disp_scenes_selected'] == 'iScSelected_deriv')
				localStorage['disp_scenes_selected'] = 'iScSelected_published';
			$('#'+localStorage['disp_scenes_selected']).show();
			$('.menuSceneBtn[type="selected"]').removeClass('fondHigh');
			$('.menuSceneBtn[show="'+localStorage['disp_scenes_selected']+'"]').addClass('fondHigh');
		}
		else {
			$('#iScSelected_published').show();
			localStorage['disp_scenes_selected'] = 'iScSelected_published';
		}

		// Ouverture des catégories assets
		$('#sectionSceneSelect .assetCategHead').click(function(){
			$('#sectionSceneSelect .assetCategList').hide(150);
			$(this).next('.assetCategList').show(150);
		});

		// Empêche les drop de fichier dans le browser
		$(document).bind('drop dragover', function (e) {
			e.preventDefault();
		});

		// Upload progress bar
		$('#vignetteScene_upload').bind('fileuploadprogress', function (e, data) {
			var filename = data.files[0].name;
			var percent =  data.loaded / data.total * 100;
			$('#retourAjax').find('.uploadProg[filename="'+filename+'"]')
							.progressbar({value: percent})
							.children('span').html('speed : '+Math.round(data.bitrate / 10000)+'Kb/s => '+Math.round(percent)+' %...');
		});

		// Init de l'uploader de vignette SCENE
		$('#vignetteScene_upload').fileupload({
			url: "actions/upload_vignettes.php?type=scene",
			dataType: 'json',
			dropZone: $('#vignetteScene'),
			drop: function (e, data) {
				$('#retourAjax')
					.html('Sending vignette...<br /><div class="uploadProg mini" filename="'+data.files[0].name+'"><span class="floatL marge5 colorMid"></span></div>')
					.addClass('ui-state-highlight')
					.show(transition);
			},
			done: function (e, data) {
				var retour = data.result;
				if (retour[0].error) {
					$('#retourAjax')
						.append('<br /><span class="colorErreur gras">Failed : '+retour[0].error+'</span>')
						.addClass('ui-state-error')
						.show(transition);
				}
				else {
					var ajaxReq = "action=moveVignette&idProj="+idProj+"&sceneID="+sceneID+"&vignetteName="+decodeURI(retour[0].name);
					AjaxJson(ajaxReq, "depts/scenes_actions", retourAjaxScenes, true);
				}
			}
		});
	});

	// Récupère les valeurs lors de l'ajout de scène fille
	function getAddDerivValues () {
		return {
			<?php echo Scenes::MASTER; ?>		: sceneID,
			<?php echo Scenes::TITLE; ?>		: $('#addDerivDiv').find('.addDeriv_title').val(),
			<?php echo Scenes::LABEL; ?>			: $('#addDerivDiv').find('.addDeriv_label').html(),
			<?php echo Scenes::SUPERVISOR; ?>	: $('#addDerivDiv').find('.addDeriv_supervisor').val(),
			<?php echo Scenes::LEAD; ?>			: $('#addDerivDiv').find('.addDeriv_lead').val(),
			<?php echo Scenes::TEAM; ?>			: $('#addDerivDiv').find('.addDeriv_team').val(),
			<?php echo Scenes::DATE; ?>			: $('#addDerivDiv').find('.addDeriv_date').datepicker("getDate"),
			<?php echo Scenes::DEADLINE; ?>		: $('#addDerivDiv').find('.addDeriv_deadline').datepicker("getDate"),
			<?php echo Scenes::DESCRIPTION; ?>	: $('#addDerivDiv').find('.addDeriv_description').val()
		};
	}
</script>

<div id="scene_hungBy" help="scenes_hung_by">

	<div class="inline mid <?php echo $styleHung; ?>">
		<?php if (!$authHandling): ?><div class="inline mid ui-state-error noBg noBorder margeTop1"><span class="ui-icon ui-icon-locked"></span></div><?php endif; ?>
		<div class="inline mid noMarge"><?php echo $hungByName; ?></div>
	</div>
	<?php if ($authHandling): ?>
		<div class="inline mid ui-state-<?php echo $hungIconSt; ?> ui-corner-all doigt margeTop1" id="btn_HandleScene" title="Change <?php echo L_ASSIGNMENTS; ?>">
			<span class="ui-icon ui-icon-<?php echo $hungIcon; ?>"></span>
		</div>

		<div class="ui-corner-left fondSect1 shadowOut pad10 leftText modifBox hide" id="handlerModifBox">
			<div class="floatR doigt closeModifBox"><span class="ui-icon ui-icon-closethick"></span></div>
			<div class="inline colorActiveFolder marge15bot gros gras"><?php echo L_ASSIGNMENTS; ?></div>
			<div class="handlersList"><?php
				if ($hungBy): ?>
					<button class="bouton margeTop1 handlerModifLine" idHandler="0" title="<?php echo L_NOBODY; ?>"><?php echo L_FREE_SCENE; ?></button><br /><br />
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

<div class="iScSelectedHeader" help="scenes_top_right_view">

	<div class="floatR marge10l rightText micro" id="vignetteScene" help="scenes_vignette">
		<img src="<?php echo $vignetteScene; ?>" />
		<input class="hide" type="file" name="files[]" id="vignetteScene_upload" />
	</div>
	<div class="floatR micro" style="margin-top: 5px;">
		<button class="bouton showTasks" section="<?php echo Tasks::SECTION_SCENES; ?>" entity="<?php echo $sceneID; ?>" title="<?php echo L_TASKS; ?>"><span class="ui-icon ui-icon-bookmark"></span></button>
	</div>


	<div class="inline top" style="margin-left: 1px; max-height: 30px;" id="divSceneStatus" help="scenes_department_steps">
		<?php
		if ($sceneAdmin):
			$warnStep = ($authMessage) ? '' : 'bordHi'; ?>
			<div class="mini padB5 ui-corner-all <?php echo $warnStep; ?>">
				<?php foreach ($sceneStatus as $idStatus => $step):
					$active = ($activeStatus == $idStatus) ? 'active' : ''; ?>
					<button class="bouton margeTop1 modifSceneStatus" <?php echo $active; ?> idStatus="<?php echo $idStatus; ?>"><?php echo $step; ?></button>
				<?php endforeach;
				$activeDone = ($activeStatus == 99) ? 'active' : ''; ?>
				<button class="bouton margeTop1 modifSceneStatus" <?php echo $activeDone; ?> idStatus="99"><?php echo L_APPROVED; ?></button>
			</div>
		<?php else: ?>
			<div class="mini pad5 margeTop5 fleche">
				<?php foreach ($sceneStatus as $idStatus => $step):
					$active = ($activeStatus == $idStatus) ? 'ui-state-active' : 'fondSect4'; ?>
					<span class="pad5 ui-corner-all <?php echo $active; ?>" idStatus="<?php echo $idStatus; ?>"><?php echo $step; ?></span>
				<?php endforeach;
				$activeDone = ($activeStatus == 99) ? 'ui-state-active' : 'fondSect4'; ?>
				<span class="pad5 ui-corner-all <?php echo $activeDone; ?>" idStatus="99"><?php echo L_APPROVED; ?></span>
			</div>
		<?php endif; ?>
	</div>
	<br />

	<div class="inline top marge5" help="scenes_informations_top_right">
		<div class="" title="<?php echo L_SUPERVISOR; ?>">
			<span class="inline mid ui-icon ui-icon-lightbulb"></span>
			<span class="inline mid colorSoft margeTop1"><?php echo $sc->getSceneSupervisor(); ?></span>
		</div>

		<div class="margeTop5" title="<?php echo L_LEAD; ?>">
			<span class="inline mid ui-icon ui-icon-person"></span>
			<span class="inline mid colorSoft margeTop1"><?php echo $sc->getSceneLead(); ?></span>
		</div>

		<div class="margeTop5" title="<?php echo L_TEAM; ?>">
			<div class="inline mid ui-state-disabled"><span class="ui-icon ui-icon-person"></span></div>
			<span class="inline mid colorSoft margeTop1"><?php echo $strTeam; ?></span>
		</div>
	</div>


	<div class="inline top margeTop5 marge10l" help="scenes_informations_top_right">
		<div class="" title="<?php echo L_START; ?>">
			<span class="inline mid ui-icon ui-icon-clock"></span>
			<span class="inline mid colorSoft margeTop1"><?php echo SQLdateConvert($sc->getSceneInfos(Scenes::DATE)); ?></span>
		</div>

		<div class="margeTop5" title="<?php echo L_END; ?>">
			<div class="inline mid ui-state-error-text">
				<span class="inline mid ui-icon ui-icon-clock"></span>
				<span class="inline mid <?php echo $styleDeadline; ?> margeTop1"><?php echo SQLdateConvert($sc->getSceneInfos(Scenes::DEADLINE)); ?></span>
			</div>
		</div>
	</div>

	<div class="marge5" help="scenes_informations_top_right">
		<span class="inline mid ui-icon ui-icon-link"></span>
		<span class="inline mid colorSoft margeTop1"><?php echo $nbDeriv.' '.L_DERIVATIVES; ?></span>
		<span class="inline mid ui-icon ui-icon-image"></span>
		<span class="inline mid colorSoft margeTop1"><?php echo count($sceneAssets); ?> <?php echo L_ASSETS; ?></span>
		<?php if($master): ?>
		<span class="inline mid ui-icon ui-icon-copy"></span>
		<span class="inline mid colorSoft margeTop1"><?php echo $countShots; ?> <?php echo L_SHOTS; ?></span>
		<?php else: ?>
		<span class="inline mid ui-icon ui-icon-video"></span>
		<span class="inline mid colorSoft margeTop1"><?php echo $countCams; ?> <?php echo L_CAMERAS; ?></span>
		<?php endif; ?>
	</div>

	<div class="inline margeTop1" id="scene_review">
		<?php if ($review != ''): ?>
		<div class="ui-state-error gras padV5 margeTop1 fleche" title="<?php echo $review; ?>">
			<div class="inline mid"><span class="ui-icon ui-icon-alert"></span></div>
			<div class="inline mid">New version TO REVIEW</div>
		</div>
		<?php endif; ?>
	</div>

	<div class="fixFloat"></div>
</div>

<div class="fondPage" help="scenes_right_view_tabs">
	<div class="doigt colorSoft" style="padding: 3px 0px;">
		<span class="fondPage pad3 padV5 menuSceneBtn fondHigh" type="selected" show="iScSelected_published"><?php echo L_RETAKES; ?></span>
		<?php if($master): ?>
		<span class="fondPage pad3 padV5 menuSceneBtn" type="selected" show="iScSelected_deriv"><?php echo L_DERIVATIVES; ?></span>
		<?php endif; ?>
		<span class="fondPage pad3 padV5 menuSceneBtn" type="selected" show="iScSelected_infos"><?php echo L_INFOS; ?></span>
		<span class="fondPage pad3 padV5 menuSceneBtn" type="selected" show="iScSelected_assets"><?php echo L_ASSETS; ?></span>
		<span class="fondPage pad3 padV5 menuSceneBtn" type="selected" show="iScSelected_shots"><?php echo L_SHOTS; ?></span>
	</div>
</div>


<div class="iScSelectedDiv hide" id="iScSelected_infos" help="scenes_informations_panel">

	<?php if ($sceneAdmin): ?>
	<div class="floatR nano">
		<?php if ($sc->getSceneInfos(Scenes::LOCK) == '1'): ?>
			<button class="bouton ui-state-error" id="unlockScene"><span class="ui-icon ui-icon-locked"></span></button>
		<?php else: ?>
			<button class="bouton" id="lockScene"><span class="ui-icon ui-icon-unlocked"></span></button>
		<?php endif; ?>
		<?php if ($sc->getSceneInfos(Scenes::ARCHIVE) == '1'): ?>
			<button class="bouton ui-state-error" id="restoreScene"><span class="ui-icon ui-icon-refresh"></span></button>
		<?php else: ?>
			<button class="bouton" id="archiveScene"><span class="ui-icon ui-icon-trash"></span></button>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<div class="marge5 nano">
		<button class="bouton marge10r" title="Export to file...">
			<span class="inline mid terra">Export</span>
			<span class="inline mid ui-icon ui-icon-extlink"></span>
		</button>
		<?php if (!$master): ?>
		<button class="bouton marge10r" title="Clone derivative">
			<span class="inline mid terra">Clone derivative</span>
			<span class="inline mid ui-icon ui-icon-seek-next"></span>
		</button>
		<?php endif; ?>
	</div>

	<div class="padV5">
		<div class="margeTop5 marge5bot" title="path/LABEL (sc. id #<?php echo $sc->getSceneInfos(Scenes::ID_SCENE); ?>)">
			<span class="inline mid ui-icon ui-icon-folder-collapsed"></span>
			<span class="inline mid colorDark"><?php echo dirname($sc->getDirScene()); ?>/</span><span class="inline bot colorMid gros"><?php echo $sc->getSceneInfos(Scenes::LABEL); ?></span>
		</div>

		<div class="inline top demi margeTop5">
			<div class="" title="<?php echo L_SUPERVISOR; ?>">
				<?php if ($sceneAdmin): ?>
				<div class="inline mid pico" title="Change supervisor">
					<button class="bouton showModif" dataMod="<?php echo Scenes::SUPERVISOR; ?>"><span class="ui-icon ui-icon-pencil"></span></button>
				</div>
				<div class="inline mid modifDiv" style="display:none;" dataMod="<?php echo Scenes::SUPERVISOR; ?>" title="Change supervisor">
					<div class="inline mid pico">
						<button class="bouton ui-state-error annulModif"><span class="ui-icon ui-icon-cancel"></span></button>
						<button class="bouton validModif" dataMod="<?php echo Scenes::SUPERVISOR; ?>"> <span class="ui-icon ui-icon-check"></span></button>
					</div>
					<div class="inline mid">
						<select class="mini noPad modifSelect w100p" dataMod="<?php echo Scenes::SUPERVISOR; ?>" title="Change Supervidor">
							<?php foreach ($teamAll as $idA => $pseudoA) {
							$selected = ($pseudoA == $sc->getSceneSupervisor()) ? 'selected' : '' ;
							echo '<option class="mini" value="'.$idA.'" '.$selected.'>'.$pseudoA.'</option>'; } ?>
						</select>
					</div>
				</div>
				<?php endif; ?>
				<div class="inline infoDiv" infoDiv="<?php echo Scenes::SUPERVISOR; ?>">
					<span class="inline mid ui-icon ui-icon-lightbulb"></span>
					<span class="inline mid colorSoft margeTop1"><?php echo $sc->getSceneSupervisor(); ?></span>
				</div>
			</div>

			<div class="margeTop5" title="<?php echo L_LEAD; ?>">
				<?php if ($sceneAdmin): ?>
				<div class="inline mid pico" title="Change lead">
					<button class="bouton showModif" dataMod="<?php echo Scenes::LEAD; ?>"><span class="ui-icon ui-icon-pencil"></span></button>
				</div>
				<div class="inline mid modifDiv" style="display:none;" dataMod="<?php echo Scenes::LEAD; ?>" title="Change lead">
					<div class="inline mid pico">
						<button class="bouton ui-state-error annulModif"><span class="ui-icon ui-icon-cancel"></span></button>
						<button class="bouton validModif" dataMod="<?php echo Scenes::LEAD; ?>"> <span class="ui-icon ui-icon-check"></span></button>
					</div>
					<div class="inline mid">
						<select class="mini noPad modifSelect w100p" dataMod="<?php echo Scenes::LEAD; ?>" title="Change Lead">
							<?php foreach ($teamAll as $idA => $pseudoA) {
							$selected = ($pseudoA == $sc->getSceneLead()) ? 'selected' : '' ;
							echo '<option class="mini" value="'.$idA.'" '.$selected.'>'.$pseudoA.'</option>'; } ?>
						</select>
					</div>
				</div>
				<?php endif; ?>
				<div class="inline infoDiv" infoDiv="<?php echo Scenes::LEAD; ?>">
					<span class="inline mid ui-icon ui-icon-person"></span>
					<span class="inline mid colorSoft margeTop1"><?php echo $sc->getSceneLead(); ?></span>
				</div>
			</div>

			<div class="margeTop5" title="<?php echo L_TEAM; ?>">
				<?php if ($sceneAdmin): ?>
				<div class="inline mid pico" title="Modify team">
					<button class="bouton" id="showAddArtistToScene"><span class="ui-icon ui-icon-plus"></span></button>
				</div>
				<?php endif; ?>
				<div class="inline mid ui-state-disabled"><span class="ui-icon ui-icon-person"></span></div>
				<span class="inline mid colorSoft margeTop1"><?php echo $strTeam; ?></span>
				<?php if ($sceneAdmin): ?>
				<div class="hide" id="addArtistToSceneDiv">
					<div class="inline mid">
						<select class="mini noPad" title="Artists" id="addArtistToSceneInput" multiple>
							<?php foreach ($teamAll as $idA => $pseudoA) {
							$selected = ''; if (in_array($idA, $sceneTeam)) $selected = 'selected';
							echo '<option class="mini" value="'.$idA.'" '.$selected.'>'.$pseudoA.'</option>'; } ?>
						</select>
					</div>
					<div class="inline mid nano"><button class="bouton" id="addArtistToSceneBtn"><span class="ui-icon ui-icon-check"></span></button></div>
				</div>
				<?php endif; ?>
			</div>
		</div>


		<div class="inline top demi margeTop5">

			<div class="" title="<?php echo L_START; ?>">
				<?php if ($sceneAdmin): ?>
				<div class="inline mid pico" title="Modify start date">
					<button class="bouton showModif" dataMod="<?php echo Scenes::DATE; ?>"><span class="ui-icon ui-icon-calendar"></span></button>
				</div>
				<div class="inline mid modifDiv" style="display:none;" dataMod="<?php echo Scenes::DATE; ?>" title="Change start date">
					<div class="inline mid pico">
						<button class="bouton ui-state-error annulModif"><span class="ui-icon ui-icon-cancel"></span></button>
						<button class="bouton validModif" dataMod="<?php echo Scenes::DATE; ?>"> <span class="ui-icon ui-icon-check"></span></button>
					</div>
					<div class="inline mid"> <input type="text" class="noBorder ui-corner-all pad3 fondSect3 miniCal" value="<?php echo SQLdateConvert($sc->getSceneInfos(Scenes::DATE)); ?>" /> </div>
				</div>
				<?php endif; ?>
				<div class="inline infoDiv" infoDiv="<?php echo Scenes::DATE; ?>">
					<span class="inline mid ui-icon ui-icon-clock"></span>
					<span class="inline mid colorSoft margeTop1"><?php echo SQLdateConvert($sc->getSceneInfos(Scenes::DATE)); ?></span>
				</div>
			</div>

			<div class="margeTop5" title="<?php echo L_END; ?>">
				<?php if ($sceneAdmin): ?>
				<div class="inline mid pico" title="Modify deadline">
					<button class="bouton showModif" dataMod="<?php echo Scenes::DEADLINE; ?>"><span class="ui-icon ui-icon-calendar"></span></button>
				</div>
				<div class="inline mid modifDiv" style="display:none;" dataMod="<?php echo Scenes::DEADLINE; ?>" title="Change deadline (end date)">
					<div class="inline mid pico">
						<button class="bouton ui-state-error annulModif"><span class="ui-icon ui-icon-cancel"></span></button>
						<button class="bouton validModif" dataMod="<?php echo Scenes::DEADLINE; ?>"> <span class="ui-icon ui-icon-check"></span></button>
					</div>
					<div class="inline mid"> <input type="text" class="noBorder ui-corner-all pad3 fondSect3 miniCal" value="<?php echo SQLdateConvert($sc->getSceneInfos(Scenes::DEADLINE)); ?>" /> </div>
				</div>
				<?php endif; ?>
				<div class="inline infoDiv" infoDiv="<?php echo Scenes::DEADLINE; ?>">
					<div class="inline mid ui-state-error-text">
						<span class="inline mid ui-icon ui-icon-clock"></span>
						<span class="inline mid <?php echo $styleDeadline; ?> margeTop1"><?php echo SQLdateConvert($sc->getSceneInfos(Scenes::DEADLINE)); ?></span>
					</div>
				</div>
			</div>

		</div>


		<div class="margeTop10" title="<?php echo L_DESCRIPTION; ?>">
			<?php if ($sceneAdmin): ?>
			<div class="floatL pico marge10r" title="Modify description">
				<button class="bouton" id="modifDescrBtn"><span class="ui-icon ui-icon-pencil"></span></button>
				<div class="hide modifBtns">
					<button class="bouton ui-state-highlight" id="modifDescrOK"><span class="ui-icon ui-icon-check"></span></button><br />
					<button class="bouton ui-state-error" id="modifDescrANNUL"><span class="ui-icon ui-icon-cancel"></span></button>
				</div>
			</div>
			<?php endif; ?>
			<div class="colorSoft sceneDescriptionDiv" id="sceneDescription"><?php echo nl2br($description); ?></div>
		</div>
	</div>

</div>

<div class="iScSelectedDiv hide" id="iScSelected_published" help="scenes_published_panel">
	<table class="fondPage w100p">
		<tr>
			<th class="w20 top">
				<?php if ($authMessage): ?>
					<div class="marge10l doigt ui-corner-all hide" title="<?php echo L_ADD_MESSAGE; ?>" id="btn_addMessage"><span class="ui-icon ui-icon-mail-closed"></span></div>
				<?php endif; ?>
			</th>
			<th class="colorDiscret center top" help="scenes_messages">
				<?php echo L_RETAKE_MESSAGES; ?> <span class="activeShotCenter" id="numRetakeMessages"></span>
			</th>
			<th class="w20 top" help="scenes_add_published">
				<?php if ($authHandling && $authMessage): ?>
					<div class="marge10r ui-corner-all doigt" title="<?php echo L_ADD_RETAKE; ?>" id="btn_addRetake"><span class="ui-icon ui-icon-plusthick"></span></div>
				<?php endif; ?>
			</th>
			<th class="activeShotCenter center top" style="width:270px;" id="activeRetakeNumber" help="scenes_published"><?php echo L_NO_RETAKE; ?></th>
		</tr>
	</table>
	<div class="shadowOut scenePublishedDiv" help="scenes_published">
		<?php include('structure/structure_retakes_scenes.php'); ?>
	</div>
	<div class="sceneMessagesDiv" help="scenes_messages">
		<?php include('structure/structure_messages_scenes.php'); ?>
	</div>
</div>

<?php if($master):																// DÉRIVÉES	?>
<div class="iScSelectedDiv hide" id="iScSelected_deriv" help="scenes_master_infos_derivates">
	<div class="hide fondSect4 pad5 gros" id="addDerivDiv" help="scenes_create_derivative">
		<div class="inline mid w80"></div>
		<div class="inline mid colorMid marge5bot">Create a derivative</div>
		<br />
		<div class="inline mid w80 colorSoft margeTop1">Scene title</div>
		<div class="inline mid w300 margeTop1" title="Scene title">
			<input type="text" class="noBorder pad3 ui-corner-all fondSect3 w100p addDeriv_title"
				   value="#<?php echo substr($sceneTitle, 1, strlen(NOMENCLATURE_SCENES)+3); ?>_D_<?php printf('%03d', count($sc->getDerivatives())+1); ?>_<?php echo substr($sceneTitle, strlen(NOMENCLATURE_SCENES)+5); ?>" />
		</div>
			<br />
			<div class="inline mid w80 colorSoft margeTop5">Scene label</div>
			<div class="inline mid w200 margeTop5" title="Scene label">
				&nbsp;<span class="colorSoft addDeriv_label"><?php echo $nextDerivL; ?></span>
			</div>
			<br />
			<div class="inline mid w80 colorSoft margeTop5">Supervisor</div>
			<div class="inline mid w300 margeTop5 mini" title="Scene supervisor">
				<select class="addDeriv_supervisor" style="width:297px;">
					<option disabled selected>none</option>
					<?php foreach($teamAll as $idM=>$nameM):
						$usr = new Users($idM);
						if(!$usr->isSupervisor()) continue; ?>
						<option value="<?php echo $idM; ?>"><?php echo $nameM; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<br />
			<div class="inline mid w80 colorSoft margeTop1">Lead</div>
			<div class="inline mid w300 margeTop1 mini" title="Scene lead">
				<select class="addDeriv_lead" style="width:297px;">
					<option disabled selected>none</option>
					<?php foreach($teamAll as $idM=>$nameM):
						$usr = new Users($idM);
						if(!$usr->isLead()) continue; ?>
						<option value="<?php echo $idM; ?>"><?php echo $nameM; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<br />
			<div class="inline mid w80 colorSoft margeTop1">Team</div>
			<div class="inline mid w300 margeTop1 mini" title="Scene team">
				<select class="addDeriv_team w300" multiple="multiple">
					<?php foreach($teamAll as $idM=>$nameM): ?>
						<option value="<?php echo $idM; ?>"><?php echo $nameM; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<br />
			<div class="inline mid w80 colorSoft margeTop1">Dates start</div>
			<div class="inline mid w300 margeTop1" title="Scene dates (start, end)">
				<input type="text" class="noBorder pad3 ui-corner-all fondSect3 inputCal addDeriv_date" style="width:126px;" value="<?php echo date(DATE_FORMAT); ?>" /> <span class="colorSoft">end</span>
				<input type="text" class="noBorder pad3 ui-corner-all fondSect3 inputCal addDeriv_deadline" style="width:126px;" value="<?php echo SQLdateConvert($sc->getSceneInfos(Scenes::DEADLINE)); ?>" />
			</div>
			<br />
			<div class="inline top w80 colorSoft margeTop5">Description</div>
			<div class="inline top w300 margeTop1" title="Scene description">
				<textarea class="noBorder pad3 ui-corner-all fondSect3 w100p addDeriv_description" rows="4"></textarea>
			</div>
			<br />
		<div class="inline mid w80"></div>
		<div class="inline mid w300 margeTop5 nano rightText">
			<button class="bouton" id="addDerivValid"><span class="ui-icon ui-icon-check"></span></button>
			<button class="ui-state-error bouton" id="addDerivCancel"><span class="ui-icon ui-icon-cancel"></span></button>
		</div>
	</div>
	<div class="floatR margeTop1 pad5 colorMid">
		<?php echo $nbDeriv.' '.L_DERIVATIVES; ?>
	</div>
	<?php if ($authDeriv): ?>
	<div class="marge5 nano" help="scenes_create_derivative">
		<button class="bouton" title="Add a Derivative" id="addDerivBtn">
			<span class="inline mid terra">Create derivative</span>
			<span class="inline mid ui-icon ui-icon-plusthick doigt"></span>
		</button>
	</div>
	<?php endif; ?>
	<div class="pad5 margeTop5 colorBtnFake">
	<?php if (is_array($filles) && count($filles) > 0):
			foreach($filles as $filleID => $fille):
				$colorF = '';
				if ($fille[Scenes::ARCHIVE] == '1') {
					if ($_SESSION['user']->isSupervisor())
						$colorF = 'gray-layer';
					else continue;
				}  ?>
			<div class="<?php echo $colorF; ?> margeTop5 doigt sceneFilleItem" sceneID="<?php echo $filleID; ?>">
				<?php echo $fille[Scenes::TITLE]; ?>
			</div>
		<?php endforeach;
		else: ?>
			<i class="colorDark">Aucune scène fille</i>
	<?php endif; ?>
	</div>
</div>
<?php endif; ?>


<div class="iScSelectedDiv hide" id="iScSelected_assets" help="scenes_assets_panel">
	<div class="floatR margeTop1 pad5 colorMid">
		<?php echo count($sceneAssets).' '.L_ASSETS;							// ASSETS ?>
	</div>
	<div class="marge5 nano">
	<?php if ($authAssetsAdd): ?>
		<button class="bouton marge10r addAssetsBtn" sceneID="<?php echo $sceneID; ?>" sceneTitle="<?php echo $sc->getSceneInfos(Scenes::TITLE); ?>" title="Manage assets">
			<span class="inline mid terra">Manage assets (MASTER)</span>
			<span class="inline mid ui-icon ui-icon-plusthick"></span>
		</button>
	<?php elseif ($authAssetsExcl): ?>
		<button class="bouton marge10r" title="Manage assets" id="exclAssetsBtn">
			<span class="inline mid terra">Manage assets</span>
			<span class="inline mid ui-icon ui-icon-plusthick"></span>
		</button>
	<?php endif; ?>
	</div>
	<div class="showAssetsScene">
		<div class="margeTop1 nano">&nbsp;</div>
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
</div>

<div class="iScSelectedDiv hide" id="iScSelected_shots" help="scenes_shots_cameras_panel">
	<?php if ($authShotAssign):													// ASSIGNATIONS SHOTS SCÈNES MASTER	?>
	<div class="marge5 nano">
		<button class="bouton marge10r assignMasterShotsBtn" sceneID="<?php echo $sceneID; ?>" sceneTitle="<?php echo $sc->getSceneInfos(Scenes::TITLE); ?>" title="Assign shots to derivatives...">
			<span class="inline mid terra"><?php echo L_ASSIGN.' '.L_SHOTS; ?> (MASTER)</span>
			<span class="inline mid ui-icon ui-icon-copy"></span>
		</button>
	</div>

	<?php elseif($ACL->check('SCENES_ADMIN')):									// ASSIGNATIONS SHOTS SCÈNES FILLES	 ?>
	<div class="fondSect4 pad5 gros hide" id="assignShotDiv">
		<div class="inline mid w80"></div>
		<div class="inline mid colorMid marge5bot"><?php echo L_ASSIGNMENTS.' '.L_SHOT; ?></div>
		<br />
		<div class="inline mid w80 colorSoft margeTop1"><?php echo L_SEQUENCE; ?></div>
		<div class="inline mid margeTop1" title="<?php echo L_SEQUENCE; ?>">
			<select class="w200" id="assignSceneSeq">
				<option value="0" disabled selected>Choose a sequence</option>
				<?php
				if (is_array($projSeqs)):
					foreach($projSeqs as $seq): ?>
						<option value="<?php echo $seq[Sequences::SEQUENCE_ID_SEQUENCE]; ?>"><?php echo $seq[Sequences::SEQUENCE_TITLE]; ?></option> <?php
					endforeach;
				endif; ?>
			</select>
		</div>
		<br />
		<div class="inline mid w80 colorSoft margeTop1"><?php echo L_SHOT; ?></div>
		<div class="inline mid margeTop1" title="<?php echo L_SHOT; ?>">
			<span class="ui-state-disabled" id="noSeqMsg">Choose a sequence before</span>
			<?php
			if (is_array($projSeqs)):
				foreach($projSeqs as $seqID => $seq): ?>
				<div class="assignSceneShotDiv hide" seqID="<?php echo $seqID; ?>">
					<select class="w200 assignSceneShot">
						<option value="0" disabled selected>Choose a shot (<?php echo $seq[Sequences::SEQUENCE_TITLE]; ?>)</option><?php
						if (is_array($projSeqs)):
							foreach($projShots as $shot):
								if($shot[Shots::SHOT_ID_SEQUENCE] != $seqID) continue; ?>
							<option value="<?php echo $shot[Shots::SHOT_ID_SHOT]; ?>"><?php echo $shot[Shots::SHOT_TITLE]; ?></option>
							<?php endforeach;
						endif; ?>
					</select>
				</div>
			<?php endforeach;
			endif; ?>
		</div>
		<br />
		<div class="inline mid w80"></div>
		<div class="inline mid w200 margeTop5"></div>
		<div class="inline mid margeTop5 nano rightText">
			<button class="bouton hide" id="assignShotValid"><span class="ui-icon ui-icon-check"></span></button>
			<button class="ui-state-error bouton" id="assignShotCancel"><span class="ui-icon ui-icon-cancel"></span></button>
		</div>
	</div>

	<div class="pad5 marge5bot nano filleBoutonsDiv">
		<button class="bouton marge10r" title="Assign to shot..." id="assignShotBtn">
			<span class="inline mid terra"><?php echo L_ASSIGN.' '.L_SHOTS; ?></span>
			<span class="inline mid ui-icon ui-icon-copy"></span>
		</button>
		<button class="bouton marge10r" title="Manage derivative's cameras..." id="manageCamsBtn">
			<span class="inline mid terra"><?php echo L_MANAGE.' '.L_CAMERAS; ?></span>
			<span class="inline mid ui-icon ui-icon-video"></span>
		</button>
	</div>
	<?php endif; ?>

	<?php if ($master):															// AFFICHAGE des shots de MASTER ?>
	<div class="showSceneShots"><?php
		if (is_array($sceneShots)):
			foreach($sceneShots as $filleID=>$shots):
				$scF = new Scenes((int)$filleID);
				$filleCams = $scF->getCameras();
				$filleTitle = $scF->getSceneInfos(Scenes::TITLE); ?>
				<div class="margeTop5 pad5 colorBtnFake"><?php echo $filleTitle; ?></div>
			<?php foreach($shots as $shotID):
					$sh = new Shots($shotID);
					$shot = $sh->getShotInfos();
					$camShot = $sh->getCamera('id');
					$seqID = (int)$shot[Shots::SHOT_ID_SEQUENCE];
					$se = new Sequences($seqID);
					$seq = $se->getSequenceInfos();
					$vignette = check_shot_vignette_ext($idProj, $seq[Sequences::SEQUENCE_LABEL], $shot[Shots::SHOT_LABEL]);
					?>
					<div class="ui-state-default sceneShotOpener" sceneID="<?php echo $filleID; ?>" seqID="<?php echo $seqID; ?>" shotID="<?php echo $shotID; ?>">
						<?php if ($sceneAdmin): ?>
						<div class="floatR pad10 margeTop1 nano" title="Remove assignation">
							<button class="bouton sceneRemoveAssignedShot"><span class="ui-icon ui-icon-trash"></span></button>
						</div>
						<?php endif; ?>
						<div class="inline mid w80 doigt pad3"><img src="<?php echo $vignette; ?>" width="75" height="41" /></div>
						<div class="inline mid w150 colorHi">
							<span class="colorMid petit" title="Sequence title"><?php echo $seq[Sequences::SEQUENCE_TITLE]; ?></span><br />
							<span class="colorHard gras" title="Shot title"><?php echo $shot[Shots::SHOT_TITLE]; ?></span><br />
							<span class="colorDiscret petit" title="Labels">(<?php echo $seq[Sequences::SEQUENCE_LABEL].'|'.$shot[Shots::SHOT_LABEL]; ?>)</span>
						</div>
						<div class="inline mid marge5bot" title="Assign camera">
							<?php if ($sceneAdmin): ?>
							<select class="assignCamShot" style="width: 250px;">
								<?php $noCam = ($camShot == false) ? 'selected' : '' ; ?>
								<option value="0" <?php echo $noCam; ?> disabled>Select camera</option><?php
								if (is_array($filleCams)) :
									foreach ($filleCams as $camID=>$camInfos):
									$selected = ($camShot == $camID) ? 'selected' : ''; ?>
									<option <?php echo $selected; ?> value="<?php echo $camID; ?>"><?php echo $camInfos[Cameras::NAME]; ?></option>
								<?php endforeach;
								endif; ?>
								<option value="0">No camera</option>
							</select>
							<?php else:
								echo $sh->getCamera(Cameras::NAME);
							endif; ?>
						</div>
					</div>
		<?php endforeach;
		endforeach;
		else: ?>
			<div class="pad5 ui-state-disabled">No shot assigned to this scene's derivatives yet.</div>
		<?php endif; ?>
	</div>

	<?php else:																	// AFFICHAGE des shots de FILLES  ?>
	<div class="showSceneShots"><?php
		if (is_array($sceneShots)):
			foreach($sceneShots as $shotID):
				$sh = new Shots($shotID);
				$shot = $sh->getShotInfos();
				$camShot = $sh->getCamera('id');
				$se = new Sequences((int)$shot[Shots::SHOT_ID_SEQUENCE]);
				$seq = $se->getSequenceInfos();
				$vignette = check_shot_vignette_ext($idProj, $seq[Sequences::SEQUENCE_LABEL], $shot[Shots::SHOT_LABEL]);
				?>
				<div class="ui-state-default doigt sceneShotOpener" sceneID="<?php echo $sceneID; ?>" seqID="<?php echo $seq[Sequences::SEQUENCE_ID_SEQUENCE]; ?>" shotID="<?php echo $shotID; ?>">
					<div class="floatR pad10 margeTop1 nano" title="Remove assignation">
						<button class="bouton sceneRemoveAssignedShot"><span class="ui-icon ui-icon-trash"></span></button>
					</div>
					<div class="inline mid w80 pad3"><img src="<?php echo $vignette; ?>" width="75" height="41" /></div>
					<div class="inline top w150 colorHi">
						<span class="colorMid petit" title="Sequence title"><?php echo $seq[Sequences::SEQUENCE_TITLE]; ?></span><br />
						<span class="colorHard gras" title="Shot title"><?php echo $shot[Shots::SHOT_TITLE]; ?></span><br />
						<span class="colorDiscret petit" title="Shot label">(<?php echo $shot[Shots::SHOT_LABEL]; ?>)</span>
					</div>
					<div class="inline mid marge5bot" title="Assign camera">
						<select class="assignCamShot" style="width: 250px;">
							<?php $noCam = ($camShot == false) ? 'selected' : '' ; ?>
							<option value="0" <?php echo $noCam; ?> disabled>Select camera</option><?php
							if (is_array($sceneCams)) :
								foreach ($sceneCams as $camID=>$camInfos):
								$selected = ($camShot == $camID) ? 'selected' : ''; ?>
								<option <?php echo $selected; ?> value="<?php echo $camID; ?>"><?php echo $camInfos[Cameras::NAME]; ?></option>
							<?php endforeach;
							endif; ?>
							<option value="0">No camera</option>
						</select>
					</div>
				</div>
		<?php endforeach;
		else: ?>
			<div class="pad5 ui-state-disabled">No shot assigned to this derivative yet.</div>
		<?php endif; ?>
	</div>

	<div class="hide" id="camerasManager">
		<div class="fondSect4 pad5 hide" id="addCameraDiv">
			<div class="inline mid w150">Camera name</div>
			<div class="inline mid">
				<input type="text" class="ui-corner-all fondSect2 noBorder pad3 w300" id="addCameraName" value="CAM_<?php echo $strCountCam; ?>_M_<?php echo $masterNum;  ?>_D_<?php echo $derivNum; ?>_" />
			</div>
			<div class="inline mid nano">
				<button class="bouton" id="addCameraValide"><span class="ui-icon ui-icon-check"></span></button>
				<button class="bouton ui-state-error" id="addCameraCancel"><span class="ui-icon ui-icon-cancel"></span></button>
			</div>
		</div>
		<div class="nano">
			<button class="bouton" id="addCameraBtn">
				<span class="inline mid giant">Add camera</span>
				<span class="inline mid ui-icon ui-icon-plusthick"></span>
			</button>
		</div>
		<div class="margeTop10" id="modalCamerasList">
		<?php if (is_array($sceneCams)) :
			foreach ($sceneCams as $camID=>$camInfos):
				$cam = new Cameras((int)$camID);
				$shotCam = $cam->getSequence(Sequences::SEQUENCE_TITLE) . ' - ' . $cam->getShot(Shots::SHOT_TITLE); ?>
				<div class="ui-state-default pad5">
					<div class="floatR nano">
						<span class="inline mid terra"><?php echo $shotCam; ?></span>
						<button class="inline mid marge10l bouton"><span class="ui-icon ui-icon-trash"></span></button>
					</div>
					<div class="gras"><?php echo $camInfos[Cameras::NAME]; ?></div>
				</div>
		<?php endforeach;
		endif;?>
		</div>
	</div>

	<?php endif; ?>
</div>




<?php
}
catch(Exception $e) { echo('<div class="marge5 ui-state-error ui-corner-all pad5">'.$e->getMessage().'</div>'); }
?>