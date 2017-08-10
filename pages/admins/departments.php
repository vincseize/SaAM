<?php
@session_start();
require_once ("../../inc/checkConnect.php" );				// Ligne à placer toujours en haut du code des pages

$l = new Liste();
$deptList = $l->getListe(TABLE_DEPTS, '*', 'position', 'ASC');

try { $acl = new ACL(@$_SESSION['user']);
?>

<script>
$(function(){
	$('.bouton').button();

	// système de repositionnage des depts
	$('.liste').sortable({
		placeholder: 'ui-state-highlight',
		forcePlaceholderSize: true,
		axis: 'y',
		update: function(e, ui) {
			var posArr = {}; var i = 1;
			var listType = $(this).attr('type');
			$('li[idDept]').each(function(){
				if ($(this).parents('.liste').attr('type') == listType) {
					posArr[$(this).attr('idDept')] = i;
					i++;
				}
			});
			updateDeptPos(posArr);
		}
	});

	// Click sur ajouter un dept
	$('.btnAddDept').click(function(){
		var type = $(this).attr('type');
		$('.liste').sortable("disable");
		$('li').removeClass('curMove');
		var toAppend = $('#addDeptModel').children('li');
		$('.liste[type='+type+']').append(toAppend);
		if (type == 'scenes')
			$('#templateNewDept').val('05_scenes').hide();
		else if (type == 'assets')
			$('#templateNewDept').val('06_assets').hide();
		else $('#templateNewDept').val('template_1');
		$("#labelNewDept").focus();
	});

	// bouton validation d'ajout de dept
	$('.liste').off('click', '.btnValidNewDept');
	$('.liste').on('click', '.btnValidNewDept', function(){
		var type  = $(this).parents('.liste').attr('type');
		var label = $('#labelNewDept').val();
		if (label == 'storyboard' || label == 'sound' || label == 'final') { alert('This name is reserved. Please choose another name.'); return; }
		var template = $('#templateNewDept').val();
		var etapes = encodeURIComponent($('#etapesNewDept').val());
		if (etapes == '') etapes = 'WIP';
		label	 = label.toLowerCase();
		template = template.replace(/ /g, "_");
		var strAjax = 'action=addNewDept&type='+type+'&labelDept='+label+'&templateDept='+template+'&etapesDept='+etapes;
		AjaxJson(strAjax, 'admin/admin_config_actions', retourAjaxMsg, true);
	});

	// bouton d'annulation d'ajout de dept
	$('.liste').off('click', '.btnAnnulNewDept')
	$('.liste').on('click', '.btnAnnulNewDept', function(){
		$('.deptBtn[active]').click();
	});

	// bouton show / hide dept
	$('.btnHideDept').click(function(){
		var idDept = $(this).parents('li').attr('idDept');
		var strAjax = 'action=modHideDept&idDept='+idDept;
		AjaxJson(strAjax, 'admin/admin_config_actions', retourAjaxMsg, true);
	});

	// bouton modif de dept
	$('.btnModDept').click(function(){
		var type  = $(this).parents('.liste').attr('type');
		var lineDept	= $(this).parents('li');
		var oldLabel	= lineDept.children('.labelDeptLine').html();
		var oldTemplate = lineDept.children('.templateDeptLine').html();
		var oldEtapes	= lineDept.children('.etapesDeptLine').html();
		lineDept.children('.labelDeptLine').html('<input class="noBorder pad3 ui-corner-all fondSect3 modLabelDept" type="text" size="15" value="'+oldLabel+'" />');
		var showTemplate = '';
		if (type == "assets")
			showTemplate = 'hide';
		lineDept.children('.templateDeptLine').html('<input class="'+showTemplate+' noBorder pad3 ui-corner-all fondSect3 modTemplateDept" type="text" size="11" value="'+oldTemplate+'" />');
		lineDept.children('.etapesDeptLine').html('<input class="noBorder pad3 ui-corner-all fondSect3 modEtapesDept" type="text" size="40" value="'+oldEtapes+'" title="Exemple : etape1, etape2, etape3" />');
		lineDept.children('.btnsDeptLine').html(
			'<div class="inline mid ui-corner-all doigt pad3 ui-state-highlight btnValidModDept" title="<?php echo L_BTN_VALID; ?>"><span class="ui-icon ui-icon-check"></span></div> '+
			'<div class="inline mid ui-corner-all doigt pad3 ui-state-error btnAnnulModDept" title="<?php echo L_BTN_CANCEL; ?>"><span class="ui-icon ui-icon-cancel"></span></div>');
	});

	// bouton validation de modif dept
	$('.liste').off('click', '.btnValidModDept');
	$('.liste').on('click', '.btnValidModDept', function(){
		var idDept		= $(this).parents('li').attr('idDept');
		var newLabel	= encodeURIComponent($('.modLabelDept').val());
		var newTemplate = $('.modTemplateDept').val();
		var newEtapes	= encodeURIComponent($('.modEtapesDept').val());
		if (newEtapes == '') newEtapes = 'WIP';
		newLabel	= newLabel.toLowerCase();
		newTemplate = newTemplate.replace(/ /g, "_");
		var strAjax = 'action=modValDept&idDept='+idDept+'&newLabel='+newLabel+'&newTemplate='+newTemplate+'&newEtapes='+newEtapes;
		AjaxJson(strAjax, 'admin/admin_config_actions', retourAjaxMsg, true);
	});

	// bouton annulation de modif dept
	$('.liste').off('click', '.btnAnnulModDept');
	$('.liste').on('click', '.btnAnnulModDept', function(){
		$('.deptBtn[active]').click();
	});
});

function updateDeptPos (newPosArr) {
	var newPosJson = encodeURIComponent(JSON.encode(newPosArr));
	var strAjax = 'action=modPosDepts&newPos='+newPosJson;
	AjaxJson(strAjax, 'admin/admin_config_actions', retourAjaxMsg, true);
}
</script>

<div class="stageContent pad5">

	<div class="inline top" style="width:660px;">

		<p class="gros gras colorBtnFake"><?php echo L_SHOTS; ?></p>

		<table class="leftText">
			<tr>
				<th class="w50 center"></th>
				<th class="w150">LABEL</th>
				<th class="w100">TEMPLATE</th>
				<th class="w300"><?php echo L_ETAPES; ?></th>
				<?php if ($acl->check('ADMIN_DEPTS')): ?>
				<th class="padH5"><button class="bouton btnAddDept" title="<?php echo L_BTN_ADD_DEPT; ?>" type="shots"><span class="ui-icon ui-icon-plusthick"></span></button></th>
				<?php endif; ?>
			</tr>
		</table>
		<ul class="liste" type="shots">
			<?php foreach($deptList as $dept) :
					if ($dept['type'] != "shots") continue;
					($dept['hide'] == 0) ? $hideClass = '' : $hideClass = 'ui-state-error-text';
					($dept['hide'] == 0) ? $hideTitle = L_HIDE : $hideTitle = L_SHOW ;
					$deptStepsArr = json_decode($dept['etapes']);
					$deptSteps = '';
					foreach($deptStepsArr as $dpS) $deptSteps .= $dpS.', ';
					$deptSteps = trim($deptSteps,', ');
			?>
			<li class="ui-state-default curMove" style="width:680px;" idDept="<?php echo $dept['id']; ?>">
				<div class="inline mid w50 center ui-state-disabled" title="ID: #<?php echo $dept['id']; ?>">#<?php echo $dept['position']; ?></div>
				<div class="inline mid w150 labelDeptLine"><?php echo strtoupper($dept['label']); ?></div>
				<div class="inline mid w100 templateDeptLine"><?php echo $dept['template_name']; ?></div>
				<div class="inline mid w300 etapesDeptLine"><?php echo $deptSteps; ?></div>
				<div class="inline mid padH5 nano btnsDeptLine" style="height:21px;">
				<?php if ($acl->check('ADMIN_DEPTS')): ?>
					<button class="bouton btnModDept" title="<?php echo L_MODIFY; ?>"><span class="ui-icon ui-icon-pencil"></span></button>
					<button class="bouton <?php echo $hideClass; ?> btnHideDept" title="<?php echo $hideTitle; ?>"><span class="ui-icon ui-icon-lightbulb"></span></button>
				<?php endif; ?>
				</div>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<?php if ($acl->check('ADMIN_DEPTS')): ?>
	<div class="inline top marge30l w300">
		<p class="gros gras"><?php echo L_ADMIN_DEPT; ?></p>
		<div class="ui-corner-all fondSect3 pad5" style="margin-top:54px;">
			<p class="colorErreur">ATTENTION !</p>
			<p>Si vous créez un département, vous ne pourrez plus le supprimer !</p>
			<p class="ui-state-disabled">Vous pouvez modifier l'ordre d'apparition des département en les faisant glisser de haut en bas.</p>
			<p>Pour modifier ou créer les étapes d'un département, attention de bien séparer chaque étape avec une <b>virgule</b> ET <b>un espace</b>.</p>
			<p>Exemple : "etape1, etape2, etape3"</p>
		</div>
	</div>
	<?php endif; ?>


	<div class="" style="width:660px;">
		<p class="gros gras colorBtnFake"><?php echo L_SCENES; ?></p>
		<table class="leftText">
			<tr>
				<th class="w50 center"></th>
				<th class="w150">LABEL</th>
				<th class="w100"> </th>
				<th class="w300"><?php echo L_ETAPES; ?></th>
				<?php if ($acl->check('ADMIN_DEPTS')): ?>
					<th class="padH5"><button class="bouton btnAddDept" title="<?php echo L_BTN_ADD_DEPT; ?>" type="scenes"><span class="ui-icon ui-icon-plusthick"></span></button></th>
				<?php endif; ?>
			</tr>
		</table>
		<ul class="liste" type="scenes">
			<?php foreach($deptList as $dept) :
					if ($dept['type'] != "scenes") continue;
					($dept['hide'] == 0) ? $hideClass = '' : $hideClass = 'ui-state-error-text';
					($dept['hide'] == 0) ? $hideTitle = L_HIDE : $hideTitle = L_SHOW ;
					$deptStepsArr = json_decode($dept['etapes']);
					$deptSteps = '';
					foreach($deptStepsArr as $dpS) $deptSteps .= $dpS.', ';
					$deptSteps = trim($deptSteps,', ');
			?>
			<li class="ui-state-default curMove" style="width:680px;" idDept="<?php echo $dept['id']; ?>">
				<div class="inline mid w50 center ui-state-disabled" title="ID: #<?php echo $dept['id']; ?>">#<?php echo $dept['position']; ?></div>
				<div class="inline mid w150 labelDeptLine"><?php echo strtoupper($dept['label']); ?></div>
				<div class="inline mid w100 ui-state-disabled templateDeptLine"><?php echo $dept['template_name']; ?></div>
				<div class="inline mid w300 etapesDeptLine"><?php echo $deptSteps; ?></div>
				<div class="inline mid padH5 nano btnsDeptLine" style="height:21px;">
				<?php if ($acl->check('ADMIN_DEPTS')): ?>
					<button class="bouton btnModDept" title="<?php echo L_MODIFY; ?>"><span class="ui-icon ui-icon-pencil"></span></button>
					<button class="bouton <?php echo $hideClass; ?> btnHideDept" title="<?php echo $hideTitle; ?>"><span class="ui-icon ui-icon-lightbulb"></span></button>
				<?php endif; ?>
				</div>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>


	<div class="" style="width:660px;">

		<p class="gros gras colorBtnFake"><?php echo L_ASSETS; ?></p>

		<table class="leftText">
			<tr>
				<th class="w50 center"></th>
				<th class="w150">LABEL</th>
				<th class="w100"> </th>
				<th class="w300"><?php echo L_ETAPES; ?></th>
				<?php if ($acl->check('ADMIN_DEPTS')): ?>
					<th class="padH5"><button class="bouton btnAddDept" title="<?php echo L_BTN_ADD_DEPT; ?>" type="assets"><span class="ui-icon ui-icon-plusthick"></span></button></th>
				<?php endif; ?>
			</tr>
		</table>
		<ul class="liste" type="assets">
			<?php foreach($deptList as $dept) :
					if ($dept['type'] != "assets") continue;
					($dept['hide'] == 0) ? $hideClass = '' : $hideClass = 'ui-state-error-text';
					($dept['hide'] == 0) ? $hideTitle = L_HIDE : $hideTitle = L_SHOW ;
					$deptStepsArr = json_decode($dept['etapes']);
					$deptSteps = '';
					foreach($deptStepsArr as $dpS) $deptSteps .= $dpS.', ';
					$deptSteps = trim($deptSteps,', ');
			?>
			<li class="ui-state-default curMove" style="width:680px;" idDept="<?php echo $dept['id']; ?>">
				<div class="inline mid w50 center ui-state-disabled" title="ID: #<?php echo $dept['id']; ?>">#<?php echo $dept['position']; ?></div>
				<div class="inline mid w150 labelDeptLine"><?php echo strtoupper($dept['label']); ?></div>
				<div class="inline mid w100 ui-state-disabled templateDeptLine"><?php echo $dept['template_name']; ?></div>
				<div class="inline mid w300 etapesDeptLine"><?php echo $deptSteps; ?></div>
				<div class="inline mid padH5 nano btnsDeptLine" style="height:21px;">
				<?php if ($acl->check('ADMIN_DEPTS')): ?>
					<button class="bouton btnModDept" title="<?php echo L_MODIFY; ?>"><span class="ui-icon ui-icon-pencil"></span></button>
					<button class="bouton <?php echo $hideClass; ?> btnHideDept" title="<?php echo $hideTitle; ?>"><span class="ui-icon ui-icon-lightbulb"></span></button>
				<?php endif; ?>
				</div>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>


<?php if ($acl->check('ADMIN_DEPTS')): ?>
<div class="hide" id="addDeptModel">
	<li class="ui-state-default" style="width:680px;">
		<div class="inline mid w50"></div>
		<div class="inline mid w150"><input class="noBorder pad3 ui-corner-all fondSect3" type="text" size="15" id="labelNewDept" /></div>
		<div class="inline mid w100"><input class="noBorder pad3 ui-corner-all fondSect3" type="text" size="11" id="templateNewDept" /></div>
		<div class="inline mid w300"><input class="noBorder pad3 ui-corner-all fondSect3" type="text" size="40" id="etapesNewDept" /></div>
		<div class="inline mid padH5 nano">
			<button class="bouton ui-state-highlight btnValidNewDept" title="<?php echo L_BTN_VALID; ?>"><span class="ui-icon ui-icon-check"></span></button>
			<button class="bouton ui-state-error btnAnnulNewDept" title="<?php echo L_BTN_CANCEL; ?>"><span class="ui-icon ui-icon-cancel"></span></button>
		</div>
	</li>
</div><?php
endif;
}
catch (Exception $e) { die('<span class="colorErreur">'. $e->getMessage().'</span>'); }
?>