<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	include('directories.php');

	$listeShotsDepts  = get_dpts();
	$listeScenesDepts = get_dpts(true, 'scenes');
	$listeAssetsDepts = get_dpts(true, 'assets');
	$userName   = $_SESSION['user']->getUserInfos('pseudo');
	$userIsDev  = $_SESSION['user']->isDev();
	$userIsDemo = $_SESSION['user']->isDemo();
?>

<script src="js/blueimp_uploader/jquery.iframe-transport.js"></script>
<script src="js/blueimp_uploader/jquery.fileupload.js"></script>
<script src="js/blueimp_uploader/jquery.fileupload-fp.js"></script>

<script>
	var deptsList = [];
	var default_ref = '<?php echo REF_PROJECT; ?>';

	$(function(){
		$('.bouton').button();
		$(".inputCal").datepicker({dateFormat: 'dd/mm/yy', firstDay: 1, changeMonth: true, changeYear: true, onSelect: function(){$(this).keyup().blur();}});		// Calendrier sur focus d'input

		// Empêche les drop de fichier dans le browser
		$(document).bind('drop dragover', function (e) {
			e.preventDefault();
		});

		if (localStorage['addProj_VignetteTempUrl']) {
			lastUploadedVignette = localStorage['addProj_VignetteTempName'];
			var t = new Date(); var tf = t.getTime();
			$('#microVignetteHead').attr("src", localStorage['addProj_VignetteTempUrl']+'?'+tf);
			$('#vignette_upload_Btn').children('span').html('CHANGER la vignette');
			$('#vignette_upload_img').parent('div').addClass('panavignette');
			$('#vignette_upload_img').attr("src", localStorage['addProj_VignetteTempUrl']).error(function() {
				console.log("Error loading vignette, reset localStorage and hide buttons.");
				localStorage.removeItem('addProj_VignetteTempUrl');
				localStorage.removeItem('addProj_VignetteTempName');
				$('#vignette_upload_titre').show();
				$('#vignette_upload_img').attr("src", "gfx/icones/menu/modele.gif").parent('div').removeClass('panavignette');
				$('#vignette_upload_Btn').children('span').html('ENVOYER une vignette');
			});
			$('#vignette_upload_titre').hide();
		}

		$('#vignette_upload').fileupload({
			url: "actions/upload_vignettes.php",
			dataType: 'json',
			dropZone: $('#proj_addVignette'),
			change: function (e, data) {
				$('#vignette_upload_msg').html('<span class="ui-state-disabled"><i>transfert</i></span> ' + data.files[0].name + '... <img src="gfx/ajax-loader-white.gif" />');
			},
			done: function (e, data) {
				var retour = data.result;
				if (retour[0].error) {
					$('#vignette_upload_msg').append('<span class="colorErreur gras">Failed : '+retour[0].error+'</span>').show().find('img').remove();
					lastUploadedVignette = '';
				}
				else {
					var d = new Date();
					$('#vignette_upload_img').attr("src", decodeURI(retour[0].url)+"?"+d.getTime()).parent('div').addClass('panavignette');
					$('#vignette_upload_msg').append('<span class="colorOk gras">OK</span>').hide(transition).find('img').remove();
					$('#vignette_upload_Btn').children('.ui-button-text').html('CHANGER la vignette');
					lastUploadedVignette = decodeURI(retour[0].name);
					localStorage.setItem('addProj_VignetteTempUrl',  decodeURI(retour[0].url));
					localStorage.setItem('addProj_VignetteTempName', decodeURI(retour[0].name));
					$('#vignette_upload_titre').hide();
					$('#microVignetteHead').attr("src", decodeURI(retour[0].url)+"?"+d.getTime());
				}
			}
		});

		$('#proj_depts').multiselect({height: '250px', selectedList: 4, noneSelectedText: 'Aucun', selectedText: '# depts', checkAllText: ' ', uncheckAllText: ' ',
			click: function(e, ui) {
				getDeptsList();
			},
			close: function(e, ui) {
				var checkedDepts = $('#proj_depts').multiselect('getChecked');
				if (checkedDepts.length != 0)
					$('#noticeDepts').removeClass('ui-state-error').children('span').addClass('ui-icon-check');
				if (checkAllFilled()) {
					$('.submitBtns').removeClass("ui-state-disabled");
					$('.deptBtn[content="proj_add_team"]').removeClass('ui-state-disabled inactiveBtn');
				}
				else {
					$('.submitBtns').addClass("ui-state-disabled");
					$('.deptBtn[content="proj_add_team"]').addClass('ui-state-disabled inactiveBtn');
				}
			}
		});

		if (localStorage['addProj_DeptsList']) {
			var listDepts = JSON.decode(localStorage['addProj_DeptsList']);
			deptsList = listDepts;
			$('.ui-multiselect-none').click();
			$.each(listDepts, function(i,dept){
				$('#proj_depts').multiselect("widget").find('input[value="'+dept+'"]').each(function(){this.click();});
			});
		}

		getDeptsList();

	});

	// récupère la liste des depts
	function getDeptsList() {
		deptsList = [];
		var selectedDepts = $('#proj_depts').multiselect('getChecked');
		$.each(selectedDepts, function(i, item){
			deptsList.push($(item).val());
			localStorage['addProj_DeptsList'] = JSON.encode(deptsList);
		});
		if (selectedDepts.length != 0)
			$('#noticeDepts').removeClass('ui-state-error').children('span').addClass('ui-icon-check');
		return deptsList;
	}

</script>

<script src="ajax/add_project.js"></script>

<div class="stageContent pad5">

	<h2><?php echo mb_convert_case(L_ADD_PROJECT, MB_CASE_UPPER); ?> - <?php echo L_BASE_INFOS; ?></h2>

	<div class="inline top gros marge30r">
		<div class="margeTop1">
			<div class="inline mid w150 marge30l"><?php echo L_PROJECT_NAME; ?></div>
			<input type="text" class="noBorder pad3 ui-corner-top w300 fondSect3 addProjDetail requiredField" title="<?php echo L_PROJECT_NAME; ?>" id="proj_title" onKeypress="return checkChar(event,null,true,null)" />
			<div class="inline mid ui-state-error noBG noBorder"><span class="ui-icon ui-icon-notice"></span></div>
		</div>
		<div class="margeTop1">
			<div class="inline mid w150 marge30l"><?php echo L_DATES.' '.L_START.' '.L_END; ?></div>
			<input type="text" class="noBorder pad3 fondSect3 inputCal addProjDetail requiredField" style="width: 134px;" value="<?php echo date('d/m/Y'); ?>" title="<?php echo L_START; ?>" id="proj_date" />
			<div class="inline mid noBG noBorder"><span class="ui-icon ui-icon-check"></span></div>
			<input type="text" class="noBorder pad3 fondSect3 inputCal addProjDetail requiredField" style="width: 134px;" title="<?php echo L_END; ?>" id="proj_deadline" />
			<div class="inline mid ui-state-error mini noBG noBorder" id="noticeDates"><span class="ui-icon ui-icon-notice"></span></div>
		</div>
		<div class="margeTop1">
			<div class="inline mid w150 marge30l"><?php echo L_PRODUCTION; ?></div>
			<input type="text" class="noBorder pad3 w300 fondSect3 addProjDetail" title="<?php echo L_PRODUCTION; ?>" id="proj_company" />
		</div>
		<div class="margeTop1">
			<div class="inline mid w150 marge30l"><?php echo L_DIRECTOR; ?></div>
			<input type="text" class="noBorder pad3 w300 fondSect3 addProjDetail" title="<?php echo L_DIRECTOR; ?>" id="proj_director" />
		</div>
		<div class="margeTop1">
			<div class="inline mid w150 marge30l"><?php echo L_SUPERVISOR; ?></div>
			<input type="text" class="noBorder pad3 w300 fondSect3 addProjDetail" title="<?php echo L_SUPERVISOR; ?>" value="<?php echo @$userName ?>" id="proj_supervisor" />
		</div>
		<div class="margeTop1">
			<div class="inline mid w150 marge30l"><?php echo L_NOMENCLATURA; ?></div>
			<input type="text" class="noBorder pad3 w300 fondSect3 addProjDetail" title="<?php echo L_NOMENCLATURA; ?>" value="<?php echo NOMENCLATURE_SEQ .'###'.NOMENCLATURE_SEPARATOR . NOMENCLATURE_SHOT.'###' ?>"  id="proj_nomenclature"/>
		</div>
		<div class="margeTop1 marge15bot">
			<div class="inline mid w150 marge30l"><?php echo L_REF; ?></div>
			<div class="inline mid pad3 w300 fondSect3 colorMid" style="min-height:20px;" title="<?php echo L_REF; ?>" id="proj_reference"></div>
		</div>

		<div class="margeTop10">
			<div class="inline top margeTop5 w150 marge30l"><?php echo L_DEPTS; ?></div>
			<select id="proj_depts" multiple="multiple" class="w300">
				<option class="mini" disabled="disabled">------- Shots -------</option>
				<?php
					foreach ($listeShotsDepts as $id => $dept) {
						$sel = (in_array($id, $_SESSION['CONFIG']['DEFAULT_DEPTS'])) ? 'selected="selected"' : '';
						echo '<option class="mini" value="'.$id.'" '.$sel.'>'.$dept.'</option>';
					}
				?>
				<option class="mini" disabled="disabled">------- Scenes -------</option>
				<?php
					foreach ($listeScenesDepts as $id => $dept) {
						 $sel = (in_array($id, $_SESSION['CONFIG']['DEFAULT_DEPTS'])) ?'selected="selected"' : '';
						echo '<option class="mini" value="'.$id.'" '.$sel.'>'.$dept.'</option>';
					}
				?>
				<option class="mini" disabled="disabled">------- Assets -------</option>
				<?php
					foreach ($listeAssetsDepts as $id => $dept) {
						 $sel = (in_array($id, $_SESSION['CONFIG']['DEFAULT_DEPTS'])) ?'selected="selected"' : '';
						echo '<option class="mini" value="'.$id.'" '.$sel.'>'.$dept.'</option>';
					}
				?>
			</select>
			<div class="inline mid ui-state-error noBG noBorder" id="noticeDepts"><span class="ui-icon ui-icon-notice"></span></div>
		</div>

		<div class="margeTop10">
			<div class="inline mid w150 marge30l"><?php echo L_FRAMERATE; ?></div>
			<select class="w300 noPad addProjDetail" title="FPS (Frames per Second)" id="proj_fps">
				<?php
					$FPSlist = explode('|', LIST_FPS);
					foreach($FPSlist as $fpsVal) {
						($fpsVal == DEFAULT_FPS) ? $sel = 'selected="selected"' : $sel = '';
						echo "<option class='mini' value='$fpsVal' $sel>$fpsVal ".L_FPS."</option>";
					}
				?>
			</select>
		</div>
		<div class="margeTop10">
			<div class="inline mid w150 marge30l"><?php echo L_PROJECT_TYPE; ?></div>
			<select class="w300 noPad addProjDetail" title="<?php echo L_PROJECT_TYPE; ?>" id="proj_project_type">
				<?php
					if (isset($_SESSION['CONFIG'])) {
						foreach($_SESSION['CONFIG']['PROJ_TYPES_LIST'] as $projType) {
							if ($projType == 'test' && !$userIsDev) continue;
							$disabled = ($projType != 'demo' && $userIsDemo) ? 'disabled' : '';
							$selected = ($projType == 'short' && !$userIsDemo) ? 'selected' : '';
							echo "<option class='mini' value='$projType' $disabled $selected>$projType</option>";
						}
					}
				?>
			</select>
		</div>
		<div class="">
			<div class="inline top margeTop5 w150 marge30l"><?php echo L_DESCRIPTION; ?></div>
			<textarea rows="5" class="noBorder pad3 ui-corner-bottom w300 fondSect3 addProjDetail"  title="<?php echo L_DESCRIPTION; ?>" id="proj_description"></textarea>
		</div>

		<div class="margeTop10">
			<div class="inline mid w150 marge30l"><?php echo L_SOFTWARE_USED; ?></div>
			<div class="inline mid petit addProjDetail" id="proj_softwares">
				<?php if (isset($_SESSION['CONFIG'])) {
						foreach($_SESSION['CONFIG']['SOFTS'] as $i => $soft)
							echo "<input type='checkbox' value='$soft' id='soft$i' /><label for='soft$i'>$soft</label> ";
					} ?>
			</div>
		</div>
	</div>


	<div class="inline top  center marge30l">
		<div class="gros marge15bot" id="vignette_upload_titre">
			<?php echo L_VIGNETTE; ?>
			<div class="mini"><i class="ui-state-disabled">(ratio 16:9)</i>&nbsp;&nbsp;&nbsp;</div>
		</div>
		<div class="center" id="proj_addVignette">
			<div><img src="gfx/icones/menu/dodecaedre.png" id="vignette_upload_img" /></div>
			<br />
			<button class="bouton" id="vignette_upload_Btn" title="or drag and drop a .gif, .jpg or .png file here"><?php echo L_SEND.' '.L_VIGNETTE; ?></button>
			<br /><br />
			<input class="hide" type="file" name="files[]" id="vignette_upload" />
			<div id="vignette_upload_msg"></div>
		</div>
	</div>


	<div class="margeTop10 rightText troisQuart ui-state-disabled submitBtns">
		<br /><br />
		<button class="bouton" id="proj_NEXT_team"><?php echo mb_convert_case(L_NEXT, MB_CASE_UPPER); ?></button>
		<button class="bouton marge30l" id="proj_DONE"><?php echo mb_convert_case(L_DONE, MB_CASE_UPPER); ?></button>
	</div>
</div>