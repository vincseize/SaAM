<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC'].'/checkConnect.php' );
	require_once('directories.php');
	require_once('vignettes_fcts.php');

	// OBLIGATOIRE, id du projet à charger
	if (isset($_POST['projectID']))
		$idProj = $_POST['projectID'];
	else die('Pas de projet à charger...');


	// Chargement des infos du projet
	$p = new Projects($idProj);
	$projInfos	= $p->getProjectInfos();
	$vignette	= check_proj_vignette_ext($idProj, $projInfos[Projects::PROJECT_TITLE]);

	$projDepts			= $p->getDeptsProject();
	$listShotsDepts		= get_dpts(false, 'shots');
	$listScenesDepts	= get_dpts(false, 'scenes');
	$listAssetsDepts	= get_dpts(false, 'assets');
	$startDeptListJS = '';
	foreach ($projDepts as $dptid => $void)
		$startDeptListJS .= '"'.$dptid.'",';
	$startDeptListJS = substr($startDeptListJS, 0, -1);

	// récupère la liste des utilisateurs lead (et au dessus)
	$l = new Liste();
	$l->addFiltre('status', '>=', Users::USERS_STATUS_LEAD, 'AND');
	$l->addFiltre('my_projects', 'LIKE', '%'.$idProj.'%', 'AND');
	$l->getListe(TABLE_USERS, 'id,pseudo,status', 'pseudo', 'ASC');
	$leadsList = $l->simplifyList('id');
	$autocompleteLeads = $autocompleteSups = '[';
	foreach($leadsList as $user) {
		$autocompleteLeads .= '"'.$user['pseudo'].'",';
		if ($user['status'] >= Users::USERS_STATUS_SUPERVISOR)
			$autocompleteSups .= '"'.$user['pseudo'].'",';
	}
	$autocompleteLeads = substr($autocompleteLeads, 0, -1).']';
	$autocompleteSups = substr($autocompleteSups, 0, -1).']';

	// récupère la liste des utilisateurs qui sont assignés au projet (pour pouvoir les assigner au shot)
	$l->resetFiltre();
	$l->addFiltre('status', '>=', Users::USERS_STATUS_ARTIST, 'AND');
	$l->addFiltre('my_projects', 'LIKE', '%'.$idProj.'%');
	$l->getListe(TABLE_USERS, 'id,pseudo,status', 'pseudo', 'ASC');
	$artistsList = $l->simplifyList('id');
	$autocompleteArtists = '[';
	foreach($artistsList as $user) {
			$autocompleteArtists .= '"'.$user['pseudo'].'",';
	}
	$autocompleteArtists = substr($autocompleteArtists, 0, -1).']';

	// récupère la liste de tous les users (pour afficher l'info du last updater)
	$l->resetFiltre();
	$l->getListe(TABLE_USERS, 'id,pseudo,nom,prenom,mail', 'pseudo', 'ASC');
	$usersList = $l->simplifyList('id');

	// GET PROJECT SIZE
	$projectSize = get_project_size($idProj);
	$freeSpace = round(($_SESSION['CONFIG']['projects_size'] - $projectSize[3]) / 1024 ); // Espace libre (en Mega-octets)
?>
<script>
	var project_ID = '<?php echo $idProj; ?>';
	var autocompleteLeads = <?php echo $autocompleteLeads; ?>;
	var autocompleteSups = <?php echo $autocompleteSups; ?>;
	var autocompleteArtists = <?php echo $autocompleteArtists; ?>;
	var startDeptList		  = [<?php echo $startDeptListJS; ?>];

	$(function(){
		$('.bouton').button();
		$(".inputCal").datepicker({dateFormat: 'dd/mm/yy', firstDay: 1, changeMonth: true, changeYear: true, onSelect: function(){$(this).keyup().blur();}});		// Calendrier sur focus d'input

		$(".inputDate").attr("id", "date").removeClass('hasDatepicker').removeData('datepicker').unbind()
			   .datepicker({dateFormat: 'dd/mm/yy', firstDay: 1, changeMonth: true, changeYear: true, onSelect: function(){checkAllFilled();}});		// Calendrier sur focus d'input
		$(".inputDeadline").attr("id", "deadline").removeClass('hasDatepicker').removeData('datepicker').unbind()
			   .datepicker({dateFormat: 'dd/mm/yy', firstDay: 1, changeMonth: true, changeYear: true, onSelect: function(){checkAllFilled();}});		// Calendrier sur focus d'input
		$('#project_type').selectmenu({style: 'dropdown'});
		$('#fps').selectmenu({style: 'dropdown'});
		$('#softwares').buttonset();
		$('#director').autocomplete({source: autocompleteLeads});
		$('#supervisor').autocomplete({source: autocompleteSups});

		$('#modifProjInfos').click(function(){
			if ($('#modifProjForm').find('#title').val().length < 4) { alert('Project\'s name can\'t be empty ! (4 characters minimum)'); return; }
			if ($('#modifProjForm').find('#date').datepicker("getDate") > $('#modifProjForm').find('#deadline').datepicker("getDate")) { alert('Dates are reversed'); return; }
			var valModProj = JSON.encode(getModValues());
			var ajaxReq = 'action=modProject&IDproj='+project_ID+'&values='+valModProj;
			console.log(ajaxReq);
			AjaxJson(ajaxReq, 'admin/admin_projects_actions', retourAjaxStructure, 'totalReload');
		});

		$('#changeProjVignette').click(function(){
			$('#vignetteProj_upload').click();
		});

		// Empêche les drop de fichier dans le browser
		$(document).bind('drop dragover', function (e) {
			e.preventDefault();
		});

		$('.modDepts_select').multiselect({height: '180px', selectedList: 5, noneSelectedText: '<?php echo L_NOTHING; ?>', selectedText: '# depts', checkAllText: ' ', uncheckAllText: ' ',
			close: function(e, ui) { redefine_project_depts() }
		});

	});

	// Envoie la requête pour modification des départements du projet
	function redefine_project_depts() {
		var newShotsDeptsList = $('#modDepts_select_shots').multiselect("getChecked").map(function(){return this.value;}).get();
		var newScenesDeptsList = $('#modDepts_select_scenes').multiselect("getChecked").map(function(){return this.value;}).get();
		var newAssetsDeptsList = $('#modDepts_select_assets').multiselect("getChecked").map(function(){return this.value;}).get();
		var newDeptsList = newShotsDeptsList.concat(newScenesDeptsList);
		newDeptsList = newDeptsList.concat(newAssetsDeptsList);
		if (newDeptsList.toString() == startDeptList.toString())
			return true;
		var ajaxReq	= 'action=modDepts&IDproj='+project_ID+'&newDeptsList='+newDeptsList;
		AjaxJson(ajaxReq, 'admin/admin_projects_actions', retourAjaxStructure, 'totalReload');
	}


	// Construit l'array des valeurs de modif project
	function getModValues() {
		var arrModVals = {};
		$('.modProjDetail').each(function(){
			var valName = $(this).attr('id');
			if (valName == 'softwares' || valName == 'date' || valName == 'deadline') return true;	// skip les softs et les dates pour traitement à part
			var newValue = $(this).val();
			if (valName != '' && newValue != '' && newValue != null)
				arrModVals[valName] = encodeURIComponent(newValue);
		});
		// dates
		arrModVals['date']	 = $('.modProjDetail#date').datepicker("getDate");
		arrModVals['deadline'] = $('.modProjDetail#deadline').datepicker("getDate");
		// softs
		arrModVals['softwares'] = [];
		$('.modProjDetail#softwares').children('input').each(function(){
			if ($(this).attr('checked'))
				arrModVals['softwares'].push($(this).val());
		});
		return arrModVals;
	}
</script>
<script src="js/blueimp_uploader/jquery.iframe-transport.js"></script>
<script src="js/blueimp_uploader/jquery.fileupload.js"></script>
<script src="js/blueimp_uploader/jquery.fileupload-fp.js"></script>
<script src="ajax/depts/dept_common.js"></script>
<script>
	$(function(){
		$('.requiredField').keyup();

		var view = stageHeight - 28;
		$('.stageContent').slimScroll({
			position: 'right',
			height: view+'px',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		});

		// Init de l'uploader de vignette PROJET
		$('#vignetteProj_upload').fileupload({
			url: "actions/upload_vignettes.php",
			dataType: 'json',
			dropZone: $('.vignetteTopInfos'),
			drop: function (e, data) {
				$('#retourAjax')
					.html('<span class="ui-state-disabled"><i>transfert</i></span> ' + data.files[0].name + '... <img src="gfx/ajax-loader.gif" />')
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
					var ajaxReq = "action=moveVignette&idProj="+project_ID+"&vignetteName="+decodeURI(retour[0].name);
					AjaxJson(ajaxReq, "admin/admin_projects_actions", retourAjaxStructure, 'totalReload');
				}
			}
		});
	});
</script>

<div class="stageContent">
	<div class=" bordBottom bordColInv2">
		<div class="inline top margeTop10 gros" id="modifProjForm">
			<div class="margeTop1 marge30l colorBtnFake gras">
				Modify project informations
			</div>
			<div class="margeTop10">
				<div class="inline mid w150 marge30l">Project name : </div>
				<?php if ($idProj != 1): ?>
				<input type="text" class="noBorder pad3 ui-corner-top w300 fondSect3 modProjDetail requiredField" value="<?php echo $projInfos[Projects::PROJECT_TITLE]; ?>"  title="Project name" onkeypress="return checkChar(event,null,true,null)" id="title" />
				<div class="inline mid ui-state-error noBG noBorder"><span class="ui-icon ui-icon-notice"></span></div>
				<?php else :?>
				<div class="inline mid w300 fondSect3 ui-corner-top pad3 colorMid"><?php echo $projInfos[Projects::PROJECT_TITLE]; ?></div>
				<div class="inline mid noBG noBorder"><span class="ui-icon ui-icon-check"></span></div>
				<?php endif; ?>
			</div>
			<div class="margeTop1">
				<div class="inline mid w150 marge30l">Dates start, end : </div>
				<input type="text" class="noBorder pad3 fondSect3 inputDate modProjDetail" style="width: 134px;" value="<?php echo SQLdateConvert($projInfos[Projects::PROJECT_DATE]); ?>" title="start" id="" />
				<div class="inline mid noBG noBorder"><span class="ui-icon ui-icon-check"></span></div>
				<input type="text" class="noBorder pad3 fondSect3 inputDeadline modProjDetail" style="width: 134px;" value="<?php echo SQLdateConvert($projInfos[Projects::PROJECT_DEADLINE]); ?>" title="end" id="" />
				<div class="inline mid ui-state-error mini noBG noBorder noticeDates"><span class="ui-icon ui-icon-notice"></span></div>
			</div>
			<div class="margeTop1">
				<div class="inline mid w150 marge30l">Production : </div>
				<?php if ($idProj != 1): ?>
				<input type="text" class="noBorder pad3 w300 fondSect3 modProjDetail" value="<?php echo $projInfos[Projects::PROJECT_COMPANY]; ?>" title="Production" id="company" />
				<?php else :?>
				<div class="inline mid w300 fondSect3 pad3 colorMid"><?php echo $projInfos[Projects::PROJECT_COMPANY]; ?></div>
				<?php endif; ?>
			</div>
			<div class="margeTop1">
				<div class="inline mid w150 marge30l">Director : </div>
				<input type="text" class="noBorder pad3 w300 fondSect3 modProjDetail" value="<?php echo $projInfos[Projects::PROJECT_DIRECTOR]; ?>" title="Director" id="director" />
			</div>
			<div class="margeTop1">
				<div class="inline mid w150 marge30l">Supervisor : </div>
				<input type="text" class="noBorder pad3 w300 fondSect3 modProjDetail" value="<?php echo $projInfos[Projects::PROJECT_SUPERVISOR]; ?>" title="Supervisor" id="supervisor" />
			</div>
			<div class="margeTop1 mini">
				<div class="inline mid gros w150 marge30l">Frame rate : </div>
				<select class="w300 noPad modProjDetail" title="FPS (Frames per Second)" id="fps">
					<?php
						$FPSlist = explode('|', LIST_FPS);
						foreach($FPSlist as $fpsVal) {
							$selected = ($fpsVal == $projInfos[Projects::PROJECT_FPS]) ? 'selected="selected"' : '';
							echo "<option class='mini' value='$fpsVal' $selected>$fpsVal</option>";
						}
					?>
				</select>
			</div>
			<div class="margeTop1 mini">
				<div class="inline mid gros w150 marge30l">Project type : </div>
				<select class="w300 noPad modProjDetail" title="Project type" id="project_type">
					<?php
						if (isset($_SESSION['CONFIG'])) {
							if ($idProj == 1) echo '<option class="mini" disabled>demo</option>';
							else {
								foreach($_SESSION['CONFIG']['PROJ_TYPES_LIST'] as $projType) {
									$selected = ($projType == $projInfos[Projects::PROJECT_TYPE]) ? 'selected="selected"' : '';
									echo "<option class='mini' value='$projType' $selected>$projType</option>";
								}
							}
						}
					?>
				</select>
			</div>
			<div class="">
				<div class="inline top margeTop5 w150 marge30l">Description : </div>
				<textarea rows="3" class="noBorder pad3 ui-corner-bottom w300 fondSect3 modProjDetail"  title="Description" id="description"><?php
					echo $projInfos[Projects::PROJECT_DESCRIPTION];
				?></textarea>
			</div>
			<div class="margeTop10">
				<div class="inline top margeTop5 w150 marge30l">Softwares : </div>
				<div class="inline modProjDetail" style="font-size:0.7em;" id="softwares">
					<?php
						if (isset($_SESSION['CONFIG']['SOFTS'])) {
							foreach($_SESSION['CONFIG']['SOFTS'] as $i => $soft) {
								$selected = (in_array($soft, json_decode($projInfos[Projects::PROJECT_SOFTWARES]))) ? 'checked="checked"' : '';
								echo "<input type='checkbox' value='$soft' id='soft$i' $selected /><label for='soft$i'>$soft</label> ";
							}
						}
						?>
				</div>
			</div>
			<div class="margeTop10">
				<div class="inline mid w150 marge30l"></div>
				<div class="margeTop10 marge15bot rightText inline mid w300">
					<button class="bouton" id="modifProjInfos"><?php echo L_MODIFY; ?></button>
					<button class="bouton" id="cancelProjInfos"><?php echo L_BTN_CANCEL; ?></button>
				</div>
			</div>
		</div>


		<div class="inline top margeTop10 marge30l gros w300">
			<div class="margeTop1 marge30l colorBtnFake gras">
				Project's vignette
			</div>
			<div class="margeTop10 marge30l center">
				<img src="<?php echo $vignette; ?>" />
				<input class="hide" type="file" name="files[]" id="vignetteProj_upload" />
			</div>
			<div class="margeTop10 marge30l center mini">
				<button class="bouton" id="changeProjVignette">Change vignette</button><br />
				<i class="petit colorDiscret">(or drag & drop new vignette on old one)</i>
			</div>
		</div>

	</div>



	<div class="margeTop10 marge10l bordBottom bordColInv2">

		<div class="inline top gros margeTop10">
			<div class="margeTop1 marge10r colorBtnFake gras">
				Shots departments assignation
			</div>
			<div class="margeTop10">
				<div class="inline mid marge10r marge15bot">
					<select class="mini noPad w300 modDepts_select" id="modDepts_select_shots" title="Départements des plans" multiple>
						<?php
						foreach ($listShotsDepts as $idSDdept => $DSdept) {
							$selected = '';
							if (in_array(strtolower($DSdept), $projDepts))
								$selected = 'selected disabled';
							echo '<option class="mini" value="'.$idSDdept.'" '.$selected.'>'.$DSdept.'</option>';
						}
						?>
					</select>
				</div>
			</div>
		</div>

		<div class="inline top gros margeTop10" style="margin-left:110px;">
			<div class="margeTop1 marge10r colorBtnFake gras">
				Scenes departments assignation
			</div>
			<div class="margeTop10">
				<div class="inline mid marge10r marge15bot">
					<select class="mini noPad w300 modDepts_select" id="modDepts_select_scenes" title="Départements des scenes" multiple>
						<?php
						foreach ($listScenesDepts as $idEDdept => $DEdept) {
							$selected = '';
							if (in_array(strtolower($DEdept), $projDepts))
								$selected = 'selected disabled';
							echo '<option class="mini" value="'.$idEDdept.'" '.$selected.'>'.$DEdept.'</option>';
						}
						?>
					</select>
				</div>
			</div>
		</div>

		<div class="inline top gros margeTop10" style="margin-left:110px;">
			<div class="margeTop1 marge10r colorBtnFake gras">
				Assets departments assignation
			</div>
			<div class="margeTop10">
				<div class="inline mid marge10r  marge15bot">
					<select class="mini noPad w300 modDepts_select" id="modDepts_select_assets" title="Départements des assets" multiple>
						<?php
						foreach ($listAssetsDepts as $idADdept => $DAdept) {
							$selected = '';
							if (in_array(strtolower($DAdept), $projDepts))
								$selected = 'selected disabled';
							echo '<option class="mini" value="'.$idADdept.'" '.$selected.'>'.$DAdept.'</option>';
						}
						?>
					</select>
				</div>
			</div>
		</div>

	</div>



	<div class="margeTop10">

		<div class="inline top gros margeTop10 marge15bot w450">
			<div class="margeTop1 marge30l colorBtnFake gras">
				Additional informations
			</div>
			<div class="margeTop10">
				<div class="inline mid w150 marge30l colorSoft">Global progression : </div>
				<div class="inline mid w200">
					<div class="progBar miniProgBar" percent="<?php echo $projInfos['progress']; ?>">
						<span class="floatL marge5 colorSoft micro"><?php echo $projInfos['progress']." %"; ?></span>
					</div>
				</div>
			</div>
			<div class="margeTop10">
				<div class="inline mid w150 marge30l colorSoft">Server disk space :</div>
				<div class="inline mid w200">
					<div class="progBar miniProgBar" percent="<?php echo $projectSize[2]; ?>">
						<span class="floatL marge5 colorSoft micro">
							<?php echo round($projectSize[0])." MB / ".($projectSize[1]/1024)." GB (".$projectSize[2]." %) | Free : $freeSpace MB"; ?>
						</span>
					</div>
				</div>
			</div>
			<div class="margeTop10">
				<div class="inline mid w150 marge30l colorSoft">Last update : </div>
				<div class="inline mid w200 colorBtnFake"><?php echo SQLdateConvert($projInfos['update']); ?></div>
			</div>
			<div class="margeTop10">
				<div class="inline mid w150 marge30l colorSoft">Last update by : </div>
				<div class="inline mid w200 colorBtnFake">
					<?php echo @$usersList[$projInfos['updated_by']]['pseudo']; ?>
					(<?php echo @$usersList[$projInfos['updated_by']]['prenom'] . ' ' . @$usersList[$projInfos['updated_by']]['nom'] ; ?>)
					<a href="mailto:<?php echo @$usersList[$projInfos['updated_by']]['mail']; ?>" class="pico">
						<button class="bouton"><span class="ui-icon ui-icon-mail-closed"></span></button>
					</a>
				</div>
			</div>
			<div class="margeTop10">
				<div class="inline mid w150 marge30l colorSoft">Nomenclatura : </div>
				<div class="inline mid w200 colorBtnFake"><?php echo $projInfos['nomenclature']; ?></div>
			</div>
			<div class="margeTop10">
				<div class="inline mid w150 marge30l colorSoft">Reference : </div>
				<div class="inline mid w200 colorBtnFake"><?php echo $projInfos['reference']; ?></div>
			</div>
			<div class="margeTop10">
				<div class="inline mid w150 marge30l colorSoft">ID (in DB) : </div>
				<div class="inline mid w200 colorSoft">#<?php echo $projInfos['id']; ?></div>
			</div>
			<div class="margeTop10 marge30l">
				<p>&nbsp;</p>
			</div>
		</div>

		<div class="inline top gros margeTop10 marge15bot" style="margin-left:160px;">
			<div class="marge30l colorBtnFake gras">
				Project's visibility
			</div>
			<div class="margeTop10">
				<div class="inline mid w150 marge30l colorSoft">Visible ?</div>
				<div class="inline mid w200 colorBtnFake"><?php echo ((string)$projInfos['hide'] == '1') ? 'No, HIDDEN!' : 'Yes, visible'; ?></div>
			</div>
			<div class="margeTop10">
				<div class="inline mid w150 marge30l colorSoft">Locked ?</div>
				<div class="inline mid w200 colorBtnFake"><?php echo ((string)$projInfos['lock'] == '1') ? 'Yes, LOCKED!' : 'No, unlocked'; ?></div>
			</div>
			<div class="margeTop10">
				<div class="inline mid w150 marge30l colorSoft">Archived ?</div>
				<div class="inline mid w200 colorBtnFake"><?php echo ((string)$projInfos['archive'] == '1') ? 'Yes, ARCHIVED!' : 'No, live'; ?></div>
			</div>
			<div class="margeTop10">
				<div class="inline mid w150 marge30l colorSoft">Position in tabs :</div>
				<div class="inline mid w200 colorBtnFake">N° <?php echo $projInfos['position']; ?></div>
			</div>
			<div class="margeTop10 marge30l colorDiscret">
				<p class="w300">
					Note : to modify the position, the visibility or lock of a project,
					rendez-vous in the <b>tools panel</b> (right-hand), button <b>"Manage Projects"</b>.
				</p>
			</div>
		</div>

	</div>
</div>