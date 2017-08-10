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
	if ($idProj == 1)
		$equipe = 'Demo, woman1, man1, man2, woman2, woman3, man3';
	$nbSeqs			= $p->getNbSequences();
	$nbShots		= $p->getNbShots();
	$format			= '16/9';

	$availExt = $_SESSION['CONFIG']['AV_ASSETS_EXTS'];
	sort($availExt);

try {
	$ACL = new ACL($_SESSION['user']);
	$assetCreate = $ACL->check('ASSETS_CREATE');
	$assetAdmin  = $ACL->check('ASSETS_ADMIN');
}
catch(Exception $e) {  }
?>

<script src="js/blueimp_uploader/jquery.iframe-transport.js"></script>
<script src="js/blueimp_uploader/jquery.fileupload.js"></script>
<script src="js/blueimp_uploader/jquery.fileupload-fp.js"></script>

<script>

	var idProj = project_ID = '<?php echo $idProj; ?>';
	var titleProj	= '<?php echo $titleProj; ?>';
	var dept		= '<?php echo $dept; ?>';
	var deptID = id_dept	= '<?php echo $deptID; ?>';
	var extsAC		= <?php echo json_encode($availExt); ?>;
	localStorage['lastDeptMyAsset'] = dept;

	$(function() {

		$('.bouton').button();

		// Ouvre le dernier view mode ou celui par défaut
		var mode = (localStorage['lastAssetViewMode']) ? localStorage['lastAssetViewMode'] : 'tree';	// @TODO : définir un "asset view mode" par pref user
		openViewMode(mode);

		// Permet l'ouverture d'un asset sélectionné (besoin du nom ET du path)
		if (localStorage['openAsset_'+idProj] && localStorage['openAssetPath_'+idProj])
			openAsset(localStorage['openAsset_'+idProj], localStorage['openAssetPath_'+idProj]);

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
		$('.displayAssetMode').click(function(){
			var mode = $(this).attr('mode');
			openViewMode(mode);
		});

		// Bouton refresh arbo
		$('#refreshArbo').click(function(){
			$('#assets_depts').find('.deptBtn[label="'+dept+'"]').click();
		});

		////////////////////////////////////////////////////////////////////////

		// Upload progress bar
		$('#uploader_Masterfile').bind('fileuploadprogress', function (e, data) {
			var filename = data.files[0].name;
			var percent =  data.loaded / data.total * 100;
			$('#retourAjax').find('.uploadProg[filename="'+filename+'"]')
							.progressbar({value: percent})
							.children('span').html('speed : '+Math.round(data.bitrate / 10000)+'Kb/s => '+Math.round(percent)+' %...');
		});

		// Upload de MasterFile_assets.xml
		$('#uploadMasterFile_Btn').click(function(){
			$('#uploader_Masterfile').click();
		});

		// Init de l'uploader du masterfile
		$('#uploader_Masterfile').fileupload({
			url: "actions/upload_masterFile.php",
			dataType: 'json',
			dropZone: null,
			change: function (e, data) {
				$('#retourAjax')
					.html('Sending masterFile...<br /><div class="uploadProg mini" filename="'+data.files[0].name+'"><span class="floatL marge5 colorMid"></span></div>')
					.addClass('ui-state-highlight')
					.show(transition);
			},
			done: function (e, data) {
				var retour = data.result;
				if (retour[0].error) {
					$('#retourAjax')
						.html('<span class="colorErreur gras">Failed : '+retour[0].error+'</span>')
						.addClass('ui-state-error')
						.show(transition);
				}
				else {
					var ajaxReq = 'action=moveTempMasterFile&idProj='+idProj+'&masterFileTempName='+retour[0].name;
					AjaxJson(ajaxReq, 'depts/assets_actions', retourAjaxAssets, 'reloadSansAsset');
				}
			}
		});

	});

	// Ouvre un mode d'affichage donné
	function openViewMode(mode) {
		$('#assetNavContent').load('modals/assets_specific/assets_'+mode+'_view.php', {idProj:idProj, titleProj:titleProj, dept: dept, deptID: deptID}, function(){
			recalcScrollAssets();
		});
		$('.displayAssetMode').removeClass('ui-state-activeFake');
		$('.displayAssetMode[mode="'+mode+'"]').addClass('ui-state-activeFake');
		localStorage['lastAssetViewMode'] = mode;
	}

	// Ouvre un asset donné
	function openAsset (assetName, pathAsset) {
		var params	  = {nameAsset : assetName, pathAsset : pathAsset, idProj: idProj, titleProj: titleProj, dept: dept, deptID: deptID};
		$('#assetDetails').load('modals/showAsset.php', params, function(){
			setTimeout(recalcScrollAssets, 1000);
		});
	}

	// Recalcule le scroll de la vue de gauche (liste des assets)
	function recalcScrollAssets() {
		var maxTreeHeight = stageHeight - 60;
		$('.stageContent').height(maxTreeHeight);
		$('#assetsList').slimScroll({
			position: 'right',
			height: (maxTreeHeight-50)+'px',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		});
	}

	// Retour ajax sur opéations assets
	var timerAlert;
	function retourAjaxAssets (datas, reload) {
		clearTimeout(timerAlert);
		if (datas.error == 'OK') {
			$('#retourAjax').html('<b>'+datas.message+'</b>').removeClass('ui-state-error').addClass('ui-state-highlight').show(transition);
			if (reload == 'reloadFolderAsset') {
				$('#listFolderContent').load('actions/display_folder_shot.php', {path: datas.rep}, function(){
					$('.fancybox-bankShot').fancybox();
				});
			}
			else if (reload == 'reloadSansAsset') {
				localStorage.removeItem('openAsset_'+idProj);
				localStorage.removeItem('openAssetPath_'+idProj);
				delete(window['nameAsset']);
				$('.deptBtn[content="06_assets"]').first().click();
			}
			else if (reload == 'reloadTreeView') {
				openViewMode('tree');
			}
			else if (reload == 'reloadCategView') {
				openViewMode('categ');
			}
			else if(reload === true) {
				var lastDpt = localStorage['lastDept_'+idProj+'_GRP_assets'];
				$('#assets_depts').find('.deptBtn[label="'+lastDpt+'"]').click();
			}
			timerAlert = setTimeout(function(){ closeRetourAjax(); }, 4000);
		}
		else {
			$('#retourAjax').html('<b>'+datas.message+'</b>').addClass('ui-state-error').show(transition);
			$('#retourAjax').prepend('<div class="floatR doigt" onClick="closeRetourAjax()"><span class="ui-icon ui-icon-close"></span></div>');
			timerAlert = setTimeout(function(){ closeRetourAjax(); }, 60000);
		}
	}

</script>


<div class="colorSoft hide" id="deptNameBG"><?php echo $dept; ?></div>

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

	<div class="bottomActionStage nano" style="bottom:7px;" idProj="<?php echo $idProj; ?>">
		<button class="bouton" title="Refresh assets view" id="refreshArbo">
			<span class="inline mid ui-icon ui-icon-arrowrefresh-1-e"></span>
		</button>
		<?php if ($_SESSION['user']->isArtist()): ?>
		<a href="fct/downloader.php?type=masterFileAssets&idProj=<?php echo $idProj; ?>&titleProj=<?php echo urlencode($titleProj); ?>&file=masterFile_assets.xml" target="new">
			<button class="bouton marge20l" title="Download current XML Assets MasterFile">
				<span class="inline mid ui-icon ui-icon-arrowthickstop-1-s"></span><span class="inline mid terra">XML</span>
			</button>
		</a><?php
		endif;
		if ($assetAdmin): ?>
			<button class="bouton marge10l ui-state-highlight" title="Upload new XML Assets MasterFile" id="uploadMasterFile_Btn">
				<span class="inline mid ui-icon ui-icon-arrowthickstop-1-n"></span><span class="inline mid terra">XML</span>
			</button>
			<input type="file" name="files[]" class="hide" id="uploader_Masterfile"/>
		<?php endif; ?>
	</div>

</div>

<div class="stageContent noPad">

	<div class="ui-widget-content noBorder">
		<table class="seqTableHead colorDiscret">
			<tr>
				<th style="padding: 3px 0px 0px 0px; width:30%;">
					<div class="ui-corner-top ui-state-default displayAssetMode" mode="tree"   style="border:none !important; margin-left: 2px;" title="Display assets tree view">Tree View</div>
					<div class="ui-corner-top ui-state-default displayAssetMode" mode="categ"  style="border:none !important;" title="Display assets by categories">By Categ.</div>
					<div class="ui-corner-top ui-state-default displayAssetMode" mode="tags"   style="border:none !important;" title="Display assets by tags">By Tags</div>
					<div class="ui-corner-top ui-state-default displayAssetMode" mode="users"  style="border:none !important;" title="Display assets by users">By Users</div>
					<div class="ui-corner-top ui-state-default displayAssetMode" mode="search" style="border:none !important; padding: 0px 5px 1px 4px;" title="Search within assets">
						<span class="ui-icon ui-icon-search"></span>
					</div>
				</th>
				<th style="padding: 1px 0px 2px 0px;">
					<span title="dept ID: #<?php echo $deptID; ?>"><?php echo $dept; ?> :</span>
					<span class="marge10l activeShotCenter" id="displayPathAsset"></span>
				</th>
			</tr>
		</table>
	</div>

	<div class="bordBankSection" id="assetNav">
		<div id="assetNavContent"></div>
	</div>
	<div id="assetDetails">
		<p class="marge10l gros leftText ui-state-disabled">Select an asset.</p>
	</div>

</div>
