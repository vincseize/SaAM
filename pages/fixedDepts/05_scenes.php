<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC'].'/checkConnect.php' );
	require_once('dates.php');
	require_once('directories.php');

	// OBLIGATOIRE, id du projet à charger
	if (isset($_POST['projectID']))
		$idProj = $_POST['projectID'];
	else die('Pas de projet à charger...');

	$dept   = $_POST['dept'];
	$deptID = $_POST['deptID'];

    $p = new Projects($idProj);
    $projInfos = $p->getProjectInfos();
    $titleProj = $projInfos[Projects::PROJECT_TITLE];
	$vignette		= check_proj_vignette_ext($idProj, $projInfos[Projects::PROJECT_TITLE]);
	$dateStart		= SQLdateConvert($projInfos[Projects::PROJECT_DATE]);
	$dateEnd		= SQLdateConvert($projInfos[Projects::PROJECT_DEADLINE]);
	$dateEndTest	= SQLdateConvert($projInfos[Projects::PROJECT_DEADLINE], 'timeStamp');
	$classDeadLine	= ($dateEndTest < time()) ? "ui-state-error" : "";
	$nbDaysLeft		= (int)floor( - (time() - $dateEndTest) / (60*60*24));
	$daysLeftStr    = days_to_date($nbDaysLeft);
	$equipe			= $p->getEquipe('str');
	$equipeArr		= $p->getEquipe();
	if ($idProj == 1)
		$equipe = 'Demo, woman1, man1, man2, woman2, woman3, man3';
	$nbSeqs			= $p->getNbSequences();
	$nbShots		= $p->getNbShots();

//	$format			= $p->getFormat();
	$format			= '16/9';

	$l = new Liste();
	$l->addFiltre(Scenes::ID_PROJECT, '=', $idProj);
	$l->addFiltre(Scenes::MASTER, '=', '0');
	$scs = $l->getListe(TABLE_SCENES);
	$nbScenes = ($scs) ? count($scs) + 1 : 1;
	$nextLabel = NOMENCLATURE_SCENES.'_'.sprintf('%03d', $nbScenes);

	require('scenes_fcts.php');
?>

<script src="js/blueimp_uploader/jquery.iframe-transport.js"></script>
<script src="js/blueimp_uploader/jquery.fileupload.js"></script>
<script src="js/blueimp_uploader/jquery.fileupload-fp.js"></script>

<script>

	var idProj = project_ID = '<?php echo $idProj; ?>';
	var titleProj	= '<?php echo $titleProj; ?>';
	var dept		= '<?php echo $dept; ?>';
	var deptID = id_dept	= '<?php echo $deptID; ?>';
	var addSceneAuth= true;
	var timerSearch;
	localStorage['lastDeptMyScene'] = dept;

	$(function() {

		$('.bouton').button();
		$('.inputCal').datepicker({dateFormat: 'dd/mm/yy', firstDay: 1, changeMonth: true, changeYear: true});
		$('.addScene_set').selectmenu({style: 'dropdown'});
		$('.addScene_supervisor').selectmenu({style: 'dropdown'});
		$('.addScene_lead').selectmenu({style: 'dropdown'});
		$('.addScene_team').multiselect({height: '200px', minWidth: 206, selectedList: 2, noneSelectedText: '<?php echo L_NOBODY; ?>', selectedText: '# artists', checkAllText: ' ', uncheckAllText: ' '});

		// Ouvre le dernier view mode ou celui par défaut
		var mode = (localStorage['lastSceneViewMode']) ? localStorage['lastSceneViewMode'] : 'list';	// @TODO : définir un "scene view mode" par pref user
		openViewMode(mode);

		// Permet l'ouverture d'une scène sélectionnée (besoin de l'ID et du TYPE)
		if (localStorage['openScene_'+idProj]) {
			var isFille = localStorage['typeScene_'+idProj] != 'master';
			if (isFille) {
				openSceneCenter(localStorage['typeScene_'+idProj]);
			}
			else
				openSceneCenter(localStorage['openScene_'+idProj]);
			openSceneRight(localStorage['openScene_'+idProj]);
		}

		// toggle du topInfos
		$(document).off('click', '.projTitle');
		$(document).on('click', '.projTitle', function(){
			if (topInfosHidden == false) hideTopInfosStage(transition);
			else showTopInfosStage(transition);
		});

		// init des progressBars
		$('.progBar').each(function() {
			var percent = parseInt($(this).attr('percent'));
			$(this).progressbar("destroy");
			$(this).progressbar({value: percent});
		});

		// Sélection du mode de vue
		$('.displaySceneMode').click(function(){
			var mode = $(this).attr('mode');
			openViewMode(mode);
		});

		// Bouton refresh arbo
		$('#refreshArbo').click(function(){
			$('#scenes_depts').find('.deptBtn[label="'+dept+'"]').click();
		});

		// Sélection du mode de vue
		$('.displaySceneMode').click(function(){
			var mode = $(this).attr('mode');
			if (!addSceneAuth) {
				$('#addSceneDiv').hide();
				$('.pageContent').append($('#addSceneDiv'));
				addSceneAuth = true;
				$('#addSceneBtn').show();
			}
			openViewMode(mode);
		});

		// Bouton ajouter une scène
		$('#addSceneBtn').click(function(){
			if (!addSceneAuth) return;
			$(this).hide();
			$('#addSceneDiv').show(transition);
			$('#addSceneDiv').find('.addScene_title').focus();
			addSceneAuth = false;
		});
		// Bouton validation d'ajout de scene
		$('#addSceneValid').click(function(){
			var infos = {
				<?php echo Scenes::MASTER; ?>		: 0,
				<?php echo Scenes::TITLE; ?>		: $('#addSceneDiv').find('.addScene_title').val(),
				<?php echo Scenes::LABEL; ?>			: $('#addSceneDiv').find('.addScene_label').html(),
				<?php echo Scenes::SUPERVISOR; ?>	: $('#addSceneDiv').find('.addScene_supervisor').val(),
				<?php echo Scenes::LEAD; ?>			: $('#addSceneDiv').find('.addScene_lead').val(),
				<?php echo Scenes::TEAM; ?>			: $('#addSceneDiv').find('.addScene_team').val(),
				<?php echo Scenes::DATE; ?>			: $('#addSceneDiv').find('.addScene_date').datepicker("getDate"),
				<?php echo Scenes::DEADLINE; ?>		: $('#addSceneDiv').find('.addScene_deadline').datepicker("getDate"),
				<?php echo Scenes::DESCRIPTION; ?>	: $('#addSceneDiv').find('.addScene_description').val()
			};
			var ajaxStr = 'action=addScene&projID='+idProj+'&infos='+encodeURIComponent(JSON.encode(infos));
//			console.log(infos);
			AjaxJson(ajaxStr, 'depts/scenes_actions', retourAjaxMsg, true);
			$('#addSceneDiv').hide(transition);
			$('#addSceneBtn').show(transition);
			addSceneAuth = true;
		});
		// Bouton annulation d'ajout de scene
		$('#addSceneCancel').click(function(){
			$('#addSceneDiv').hide(transition);
			$('#addSceneBtn').show(transition);
			addSceneAuth = true;
		});


		// Click sur scene MASTER
		$('.stageContent').off('click', '.sceneMasterItem');
		$('.stageContent').on('click', '.sceneMasterItem', function() {
			var theScene = $(this).attr('sceneID');
			$('.sceneFilleItem').removeClass('colorHard');
			if ($(this).attr('opened') == 'opened' && localStorage['typeScene_'+idProj] == 'master') {
				$('.sceneMasterContent').hide(150);
				$('.sceneMasterItem').removeAttr('opened').removeClass('colorHard colorErrText').addClass('colorBtnFake');
				$(this).addClass('colorErrText');
			}
			else {
				localStorage['openScene_'+idProj] = theScene;
				localStorage['typeScene_'+idProj] = 'master';
				$('.sceneMasterContent[sceneID!="'+theScene+'"]').hide(150);
				$('.sceneMasterItem').removeAttr('opened').removeClass('colorHard colorErrText').addClass('colorBtnFake');
				$(this).attr('opened', 'opened').removeClass('colorBtnFake').addClass('colorErrText').next('.sceneMasterContent').show(150);
				openSceneCenter(theScene);
				openSceneRight(theScene);
			}
		});

		// Click sur scene FILLE
		$('.stageContent').off('click', '.sceneFilleItem');
		$('.stageContent').on('click', '.sceneFilleItem', function() {
			$('.sceneFilleItem').removeClass('colorHard');
			var theScene = $(this).attr('sceneID');
			$('.sceneFilleItem[sceneID="'+theScene+'"]').addClass('colorHard');
			openSceneRight(theScene);
		});

		// Fonction de recherche SCENES
		$('#sceneSearchSubmit').click(function(){
			var term = $('#sceneSearchInput').val();
			if (term == '') {
				$('.sceneItem').show().next('.sceneMasterContent').hide();
				$('.sceneFolderContent').hide();
				$(this).parent().removeClass('ui-state-error noBG');
				openArboScenes();
				return;
			}
			$('.sceneFolderContent').show();
			$('.sceneItem').hide().next('.sceneMasterContent').hide();
			var terme = term.replace(/([.?*+^$[\]\\(){}|-])/g, "\\$1");
			var re = new RegExp(terme, "i");
			$('.sceneItem').filter(function() {
				return re.test($(this).attr('title'));
			}).show().next('.sceneMasterContent').show();
			$(this).parent().addClass('ui-state-error noBG');
			$('#sceneSearchInput').focus();
		});

	});

	// Ouvre un mode d'affichage donné
	function openViewMode(mode) {
		$('#sceneNavContent').load('modals/scenes_specific/scenes_'+mode+'_view.php', {idProj:idProj, titleProj:titleProj, dept: dept, deptID: deptID}, function(){
			recalcScrollScenes();
		});
		$('.displaySceneMode').removeClass('ui-state-active');
		$('.displaySceneMode[mode="'+mode+'"]').addClass('ui-state-active');
		localStorage['lastSceneViewMode'] = mode;
	}

	// Ouvre l'arbo vers une scène
	function openArboScenes() {
		if (localStorage['openScene_'+idProj]) {		// Si une scene est définie en mémoire
			var isFille = localStorage['typeScene_'+idProj] != 'master';
			var theScene = localStorage['openScene_'+idProj];
			if (isFille) {
				$('.sceneFilleItem[sceneID="'+theScene+'"]').addClass('colorHard');
				theScene = localStorage['typeScene_'+idProj];
			}
			$('.sceneItem[sceneID="'+theScene+'"]').attr('opened','opened').removeClass('colorBtnFake').addClass('colorErrText').next('.sceneMasterContent').show().parent('.sceneFolderContent').show();
		}
		// Si moins de 10 scenes, on ouvre l'arbo par défaut
		if (nbScenes <= 10 ) {
			$('.sceneFolderContent').show();
		}
	}

	// Ouvre une scène dans la partie centrale
	function openSceneCenter (sceneID) {
		$('#noSceneSel').hide();
		var params = {sceneID : sceneID, idProj: idProj, titleProj: titleProj, dept: dept, deptID: deptID};
		$('#sectionSceneParent').load('modals/scenes_specific/showScene_parent.php', params, function(){
			setTimeout(recalcScrollScenes, 1000);
		});
	}

	// Ouvre une scene  dans la partie de droite
	function openSceneRight (sceneID) {
		var params = {sceneID : sceneID, idProj: idProj, titleProj: titleProj, dept: dept, deptID: deptID};
		$('#sectionSceneSelect').load('modals/scenes_specific/showScene_selected.php', params, function(){
			addSceneAuth = true;
			setTimeout(recalcScrollScenes, 1000);
		});
	}

	// Recalcul du scroll liste des scènes
	function recalcScrollScenes() {
		var maxTreeHeight = stageHeight - 60;
		$('.stageContent').height(maxTreeHeight);
		$('#scenesList').slimScroll({
			position: 'right',
			height: (maxTreeHeight-10)+'px',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		});
	}
</script>
<script src="ajax/depts/dept_scenes.js"></script>


<div class="colorSoft hide" id="deptNameBG"><?php echo L_SCENES; ?></div>

<div class="topInfosStage" style="height: 34px;">

	<div class="projTitle fondSemiTransp doigt noBG" help="project_infos" id="proj_title" title="click to show/hide infos"><?php echo $projInfos[Projects::PROJECT_TITLE]; ?></div>

	<div class="vignetteTopInfos" help="project_infos">
		<img class="toHide hide" src="<?php echo $vignette; ?>" />
		<div class="leftText fondSemiTransp toHide hide colorMid" style="position: absolute; top:134px; width:270px;">
			<span class="inline mid marge10l ui-icon ui-icon-video" title="<?php echo L_SEQUENCES;?>"></span>
			<span class="inline mid gras colorHard"><?php echo $nbSeqs; ?></span>
			<span class="inline mid marge10l ui-icon ui-icon-copy" title="<?php echo L_SHOTS;?>"></span>
			<span class="inline mid gras colorHard marge10r"><?php echo $nbShots; ?></span>
			<span class="inline mid marge10l ui-icon ui-icon-signal" title="<?php echo L_FPS;?>"></span>
			<span class="inline mid gras colorHard"><?php echo $projInfos[Projects::PROJECT_FPS].' fps'; ?></span>
			<span class="inline mid marge10l ui-icon ui-icon-extlink" title="<?php echo L_FORMAT;?>"></span>
			<span class="inline mid gras colorHard marge10r"><?php echo $format; ?></span>
		</div>
	</div>

	<div class="fleche" help="project_infos" id="detailsInfosCenter">
		<div class="progBar" help="project_progressBar" percent="<?php echo $projInfos[Projects::PROJECT_PROGRESS]; ?>">
			<span class="floatL marge5 colorMid"><?php echo L_PROJECT;?> : <?php echo $projInfos[Projects::PROJECT_PROGRESS]; ?>%</span>
		</div>

		<div class="inline top colorHard toHide" style="display:none;" id="proj_InfosPeople">
			<div>
				<span class="inline mid ui-icon ui-icon-home" title="production"></span>
				<span class="inline mid gras" title="production"><?php echo $projInfos[Projects::PROJECT_COMPANY]; ?></span>
			</div>
			<div>
				<span class="inline mid ui-icon ui-icon-volume-off" title="réalisateur"></span>
				<span class="inline mid gras" title="réalisateur"><?php echo $projInfos[Projects::PROJECT_DIRECTOR]; ?></span>
			</div>
			<div>
				<span class="inline mid ui-icon ui-icon-person" title="<?php echo L_SUPERVISOR; ?>"></span>
				<span class="inline mid gras" title="<?php echo L_SUPERVISOR; ?>"><?php echo $projInfos[Projects::PROJECT_SUPERVISOR]; ?></span>
			</div>
		</div>
		<div class="inline top marge30l colorHard toHide" style="display:none;" id="proj_InfosDates">
			<div>
				<span class="inline mid ui-icon ui-icon-clock" title="<?php echo L_START; ?>"></span>
				<span class="inline mid gras" title="<?php echo L_START; ?>"><?php echo $dateStart; ?></span>
			</div>
			<div class="ui-state-error-text colorHard">
				<span class="inline mid ui-icon ui-icon-clock" title="<?php echo L_END; ?>"></span>
				<span class="inline mid gras <?php echo $classDeadLine; ?>" title="<?php echo L_END; ?>"><?php echo $dateEnd; ?></span>
			</div>
		</div>

		<div class="ui-state-disabled toHide hide" id="proj_Team">
			<div class="inline top ui-icon ui-icon-person" title="<?php echo L_TEAM; ?>"></div>
			<div class="inline top w9p padH5" title="<?php echo L_TEAM; ?>"><?php echo $equipe; ?></div>
		</div>
	</div>

	<div class="fleche" id="detailsInfosRight">
		<div class="pad5 ui-state-error-text colorHard">
			<span class="inline mid ui-icon ui-icon-arrowthickstop-1-e" title="end until"></span>
			<span class="inline mid" title="<?php echo L_REM_TIME; ?>"><?php echo $daysLeftStr; ?></span>
		</div>

		<div class="margeTop5 padH5 padV5 colorHard toHide hide" id="proj_Descr" title="description du projet">
			<?php echo stripslashes($projInfos[Projects::PROJECT_DESCRIPTION]); ?>
			<br />
			<br />
		</div>
	</div>

	<div class="topActionStage" idProj="<?php echo $idProj; ?>">
	</div>

	<div class="bottomActionStage nano" style="bottom:5px;" idProj="<?php echo $idProj; ?>">
		<button class="bouton" title="refresh arbo" id="refreshArbo"><span class="ui-icon ui-icon-arrowrefresh-1-e"></span></button>
		&nbsp;&nbsp;<button class="bouton" title="Show global dependencies tree (dev. WiP.)" id="showDepTree_Btn"><span class="ui-icon ui-icon-shuffle"></span></button>
	</div>

</div>

<div class="stageContent">

	<div class="ui-widget-content noBorder">
		<table class="seqTableHead colorDiscret">
			<tr>
				<th style="padding: 3px 0px 0px 0px; width:29.5%;" help="scenes_list_tabs">
					<div class="ui-corner-top ui-state-default displaySceneMode" mode="list" style="border:none !important; margin-left: 2px;" title="Display All scenes (tree view)">List all</div>
					<div class="ui-corner-top ui-state-default displaySceneMode" mode="seqs" style="border:none !important;" title="Display scenes by sequences">By Seq.</div>
					<div class="ui-corner-top ui-state-default displaySceneMode" mode="tags" style="border:none !important;" title="Display scenes by tags">By Tag</div>
					<div class="ui-widget-content noBorder" style="position:absolute; top:3px; left:163px;">
						<span class="inline top ui-icon ui-icon-search doigt showSearch" id="sceneSearchBtn" title="Search within scenes"></span>
						<input type="text" class="inline top noBorder ui-corner-all fondSect3 w150 searchInput" style="display:none;" id="sceneSearchInput" />
						<span class="inline top ui-icon ui-icon-circle-zoomin doigt submitSearch" style="display:none;" id="sceneSearchSubmit"></span>
					</div>
				</th>
				<th class="center" style="width: 270px;" help="scenes_center_view">
					<span class="marge10l colorErrText" id="parentSceneName"></span>
				</th>
				<th class="center" help="scenes_info_bar">
					<span class="floatL ui-state-highlight ui-corner-all mini marge10l" style="padding: 1px 4px; display:none;" id="selectedSceneType">MASTER</span>
					<span class="marge10l" id="selectedSceneName"></span>
				</th>
			</tr>
		</table>
	</div>

	<div class="bordBankSection" id="sceneNav">
		<div class="hide fondSect4 pad5 gros" id="addSceneDiv">
			<div class="inline mid w80"></div>
			<div class="inline mid colorMid marge5bot">Create a master scene</div>
			<br />
			<div class="inline mid w80 colorSoft margeTop1">Scene title</div>
			<div class="inline mid w200 margeTop1" title="Scene title">
				<input type="text" class="noBorder pad3 ui-corner-all fondSect3 w100p addScene_title" value="<?php echo $nextLabel.'_'; ?>" />
			</div>
			<br />
			<div class="inline mid w80 colorSoft margeTop5">Scene label</div>
			<div class="inline mid w200 margeTop5" title="Scene label">
				&nbsp;<span class="colorSoft addScene_label"><?php echo $nextLabel; ?></span>
			</div>
			<br />
			<div class="inline mid w80 colorSoft margeTop1">Supervisor</div>
			<div class="inline mid w200 margeTop1 mini" title="Scene supervisor">
				<select class="addScene_supervisor" style="width:195px;">
					<option disabled selected>none</option>
					<?php foreach($equipeArr as $idM=>$nameM):
						$usr = new Users($idM);
						if(!$usr->isSupervisor()) continue; ?>
						<option value="<?php echo $idM; ?>"><?php echo $nameM; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<br />
			<div class="inline mid w80 colorSoft margeTop1">Lead</div>
			<div class="inline mid w200 margeTop1 mini" title="Scene lead">
				<select class="addScene_lead" style="width:195px;">
					<option disabled selected>none</option>
					<?php foreach($equipeArr as $idM=>$nameM):
						$usr = new Users($idM);
						if(!$usr->isLead()) continue; ?>
						<option value="<?php echo $idM; ?>"><?php echo $nameM; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<br />
			<div class="inline mid w80 colorSoft margeTop1">Team</div>
			<div class="inline mid w200 margeTop1 mini" title="Scene team">
				<select class="addScene_team" multiple="multiple">
					<?php foreach($equipeArr as $idM=>$nameM): ?>
						<option value="<?php echo $idM; ?>"><?php echo $nameM; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<br />
			<div class="inline mid w80 colorSoft margeTop1">Dates start</div>
			<div class="inline mid w200 margeTop1" title="Scene dates (start, end)">
				<input type="text" class="noBorder pad3 ui-corner-all fondSect3 inputCal addScene_date" style="width:76px;" value="<?php echo date(DATE_FORMAT); ?>" /> <span class="colorSoft">end</span>
				<input type="text" class="noBorder pad3 ui-corner-all fondSect3 inputCal addScene_deadline" style="width:76px;" value="<?php echo $dateEnd; ?>" />
			</div>
			<br />
			<div class="inline top w80 colorSoft margeTop5">Description</div>
			<div class="inline top w200 margeTop1" title="Scene description">
				<textarea class="noBorder pad3 ui-corner-all fondSect3 w100p addScene_description" rows="4"></textarea>
			</div>
			<br />
			<div class="inline mid w80"></div>
			<div class="inline mid w200 margeTop5 nano rightText">
				<button class="bouton" id="addSceneValid"><span class="ui-icon ui-icon-check"></span></button>
				<button class="ui-state-error bouton" id="addSceneCancel"><span class="ui-icon ui-icon-cancel"></span></button>
			</div>
		</div>
		<div class="marge5 nano" help="create_master_scene">
			<button class="bouton" title="Add a Master Scene" id="addSceneBtn">
				<span class="inline mid terra">Create Master scene</span>
				<span class="inline mid ui-icon ui-icon-plusthick doigt"></span>
			</button>
		</div>
		<div class="petit" id="sceneNavContent"></div>
	</div>
	<div id="sceneDetails">
		<div id="noSceneSel">
			<p class="marge10l gros leftText ui-state-disabled">Select a scene.</p>
		</div>
		<div class="bordBankSection" id="sectionSceneParent" help="scenes_center_view"></div>
		<div class="fondSect2" id="sectionSceneSelect" help="scenes_right_view"></div>
		<div class="hide" id="managersModal"></div>
	</div>
</div>

