<?php
@session_start();
require_once ("../inc/checkConnect.php" );

require_once ('directories.php');

$id_project = $_SESSION['active_project_id'] = $_GET['proj'];

$p = new Projects($id_project);
$proj_isHidden = ($p->getProjectInfos(Projects::PROJECT_HIDE) == 1) ? true : false;
$titleProj = $p->getProjectInfos(Projects::PROJECT_TITLE);
$projShotsDepts  = $p->getDeptsProjectWithInfos('shots');
$projScenesDepts = $p->getDeptsProjectWithInfos('scenes');
$projAssetsDepts = $p->getDeptsProjectWithInfos('assets');

try { $acl = new ACL(@$_SESSION['user']);

?>
<script>

var titleProj	= "<?php echo $titleProj; ?>";
var idProj		= "<?php echo $id_project; ?>";
localStorage['lastViewedProject'] = <?php echo $id_project; ?>;

$(function(){

	var lastGrp = 'racine';
	// Si c'est un shot qui est défini dans l'URL GET, on affiche le groupe "shots"
	if ($(document).getUrlParam('shot') != null)
		lastGrp = 'shots';
	// Si c'est une scene qui est définie dans l'URL GET, on affiche le groupe "scenes"
	else if ($(document).getUrlParam('scene') != null)
		lastGrp = 'scenes';
	// Si c'est un asset qui est défini dans l'URL GET, on affiche le groupe "assets"
	else if ($(document).getUrlParam('asset') != null)
		lastGrp = 'assets';
 	// Si c'est une task qui est défini dans l'URL GET, on affiche le groupe "tasks"
	else if ($(document).getUrlParam('task') != null)
		lastGrp = 'tasks';
	// Sinon, et si un groupe est défini en mémoire, ou ouvre celui-ci
	else if (localStorage['lastGroupDepts_'+idProj])
		 lastGrp = localStorage['lastGroupDepts_'+idProj];

	// Affichage des bons départements
	$('#selectDeptsList').val(lastGrp);
	$('.deptsList').hide();
	$('#'+lastGrp+'_depts').show();

	// Affichage du bon my_truc
	var openMy = (lastGrp == 'racine' || lastGrp == 'scenes') ? 'notes' : lastGrp;
	$('.myMenuHeadEntry[menuLoad="my_'+openMy+'"]').click();

	// Si un département est défini dans l'URL GET, on force son ouverture
	if ($(document).getUrlParam('dept') != null)
		$('#'+lastGrp+'_depts').find('.deptBtn[label="'+$(document).getUrlParam('dept')+'"]').click();

	// Init du sélect de groupe de depts
	$('#selectDeptsList').selectmenu({style: 'dropdown'}).on('change', function(){
		var groupDepts = $(this).val();								// Récup du groupe de depts
		var affGrpDept = $(this).find('option[value="'+groupDepts+'"]').html();	// Pareil mais dans la langue
		$('#selectDeptsList-button').find('.ui-selectmenu-status').html(affGrpDept); // Remplace le contenu du bouton par la sélection
		$('.deptsList').hide();										// cache tout les depts
		$('#'+groupDepts+'_depts').show();							// montre le groupe de depts sélectionné
		localStorage['lastGroupDepts_'+idProj] = groupDepts;		// stocke en mémoire le groupe pour s'en souvenir

		// On ouvre le dernier dept visité dans ce groupe (si existant)
		if (localStorage['lastDept_'+idProj+'_GRP_'+groupDepts])
			$('#'+groupDepts+'_depts').find('.deptBtn[label="'+localStorage['lastDept_'+idProj+'_GRP_'+groupDepts]+'"]').click();
		// Sinon on ouvre le premier dept du groupe
		else
			$('#'+groupDepts+'_depts').find('.deptBtn').first().click();

		// Ouvre les "my_trucs" correspondants, dans panel de gauche
		switch(groupDepts){
			case 'scenes':
				$('.myMenuHeadEntry[menuLoad="my_scenes"]').click();
				hideTopInfosStage(0);
				break;
			case 'assets':
				$('.myMenuHeadEntry[menuLoad="my_assets"]').click();
				hideTopInfosStage(0);
				break;
			case 'shots':
				$('.myMenuHeadEntry[menuLoad="my_shots"]').click();
				showTopInfosStage(0);
				break;
			case 'tasks':
				$('.myMenuHeadEntry[menuLoad="my_tasks"]').click();
				showTopInfosStage(0);
				break;
			default:
				$('.myMenuHeadEntry[menuLoad="my_notes"]').click();
				break;
		}

	});
});
</script>

<div class="headerStage">
	<?php if ($proj_isHidden): ?>
		<div class="floatL ui-state-error ui-corner-all margeTop5" title="WARNING! This project is hidden. You can see it because you are its creator.">
			<span class="ui-icon ui-icon-alert"></span>
		</div>
	<?php endif; ?>

	<div class="floatR">
		<div class="inline deptBtn colorMid" type="fixedDepts" content="99_final" label="final" help="dept_final"><small><?php echo mb_strtoupper(L_FINAL); ?></small></div>
	</div>

	<div class="inline" help="depts_group_selector">
		<select class="fondSect4 noBorder" title="Select departments group" id="selectDeptsList">
			<option value="racine"><?php echo preg_replace('/ /', '&nbsp;', mb_strtoupper(L_BTN_SECTION_ROOT, 'UTF-8')); ?></option>
			<option value="shots"><?php echo preg_replace('/ /', '&nbsp;', mb_strtoupper(L_BTN_SECTION_SHOTS, 'UTF-8')); ?>&nbsp;</option>
			<?php $disable = (count($projScenesDepts) == 0) ? 'disabled' : '' ?>
			<option value="scenes" <?php echo $disable; ?>><?php echo preg_replace('/ /', '&nbsp;', mb_strtoupper(L_BTN_SECTION_SCENES, 'UTF-8')); ?></option>
			<?php $disable = (count($projAssetsDepts) == 0) ? 'disabled' : '' ?>
			<option value="assets" <?php echo $disable; ?>><?php echo preg_replace('/ /', '&nbsp;', mb_strtoupper(L_BTN_SECTION_ASSETS, 'UTF-8')); ?></option>
			<option value="tasks"><?php echo preg_replace('/ /', '&nbsp;', mb_strtoupper(L_BTN_SECTION_TASKS, 'UTF-8')); ?></option>
		</select>
	</div>
	<div class="Vseparator fondSect3"></div>

	<div class="inline deptsList" id="racine_depts">
		<?php if ($acl->check('VIEW_DEPT_CONFIG')) : ?>
			<div class="inline deptBtn colorSoft" type="fixedDepts" content="02_config" label="infos"><small><?php echo mb_strtoupper(L_BTN_CONFIG); ?></small></div>
			<div class="Vseparator fondSect3"></div>
		<?php endif; ?>
		<?php if ($acl->check('VIEW_DEPT_OVERVIEW')) : ?>
			<div class="inline deptBtn colorSoft" type="fixedDepts" content="05_overview" label="overview"><small><?php echo mb_strtoupper(L_BTN_OVERVIEW); ?></small></div>
		<?php endif; ?>
		<?php if ($acl->check('VIEW_DEPT_PROD')) : ?>
			<div class="inline deptBtn colorSoft" type="fixedDepts" content="03_prod" label="prod"><small><?php echo mb_strtoupper(L_PROD); ?></small></div>
			<div class="Vseparator fondSect3"></div>
		<?php endif; ?>

		<div class="inline deptBtn colorSoft" type="fixedDepts" content="01_daylies" label="daylies" active><small><?php echo mb_strtoupper(L_DAILIES); ?></small></div>
		<?php if ($acl->check('VIEW_DEPT_STRUCTURE', "proj:".$id_project)) : ?>
			<div class="inline deptBtn colorSoft" type="fixedDepts" content="03_gantt" label="gantt"><small><?php echo mb_strtoupper(L_SCHEDULE); ?></small></div>
		<?php endif; ?>
		<div class="inline deptBtn colorSoft" type="fixedDepts" content="04_bank" label="bank"><small><?php echo mb_strtoupper(L_BANK); ?></small></div>
	</div>

	<div class="inline deptsList" style="display:none;" id="shots_depts">
		<?php if ($acl->check('VIEW_DEPT_STRUCTURE', "proj:".$id_project)) : ?>
			<div class="inline deptBtn colorMid" type="fixedDepts" content="00_structure" label="structure" help="dept_shots_structure"><small><?php echo mb_strtoupper(L_STRUCTURE); ?></small></div>
			<div class="Vseparator fondSect3"></div>
		<?php endif; ?>
		<div class="inline deptBtn colorMid" type="fixedDepts" content="10_scenario" label="scenario" help="dept_scenario"><?php echo mb_strtoupper(L_SCENARIO); ?></div>
		<div class="inline deptBtn colorMid" type="fixedDepts" content="15_dectech" label="dectech" help="dept_tech_script"><?php echo mb_strtoupper(L_DECTECH); ?></div>
		<div class="inline deptBtn colorMid" type="fixedDepts" content="20_storyboard" label="storyboard" help="dept_storyboard" active><?php echo mb_strtoupper(L_STORYBOARD); ?></div>
		<div class="Vseparator fondSect3"></div>
		<?php foreach($projShotsDepts as $shotsDept) : ?>
			<div class="inline deptBtn colorMid" type="depts" help="shots_departments"
				 content="<?php echo $shotsDept['template']; ?>"
				 idDept="<?php echo $shotsDept['id']; ?>"
				 label="<?php echo $shotsDept['label']; ?>">
			<?php echo mb_strtoupper($shotsDept['label']); ?>
			</div>
		<?php endforeach; ?>
		<!--<div class="inline deptBtn colorMid" type="fixedDepts" content="90_sound" label="sound"><?php echo mb_strtoupper(L_SOUND); ?></div>-->
		<div class="Vseparator fondSect3"></div>
	</div>

	<div class="inline deptsList" style="display:none;" id="scenes_depts">
		<?php foreach($projScenesDepts as $scenesDept) : ?>
			<div class="inline deptBtn colorMid" type="fixedDepts"
				 content="<?php echo $scenesDept['template']; ?>"
				 idDept="<?php echo $scenesDept['id']; ?>"
				 label="<?php echo $scenesDept['label']; ?>">
			<?php echo mb_strtoupper($scenesDept['label']); ?>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="inline deptsList" style="display:none;" id="assets_depts">
		<?php if ($acl->check('VIEW_DEPT_STRUCTURE', "proj:".$id_project)): ?>
		<div class="inline deptBtn" type="fixedDepts" content="98_overviewEntity" label="overviewentity" title="<?php echo mb_strtoupper(L_ASSETS.' '.L_BTN_OVERVIEW); ?>">
			O
		</div>
		<?php endif;
		foreach($projAssetsDepts as $assetsDept) : ?>
			<div class="inline deptBtn colorMid" type="fixedDepts"
				 content="<?php echo $assetsDept['template']; ?>"
				 idDept="<?php echo $assetsDept['id']; ?>"
				 label="<?php echo $assetsDept['label']; ?>">
			<?php echo mb_strtoupper($assetsDept['label']); ?>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="inline deptsList" style="display:none;" id="tasks_depts">
		<div class="inline deptBtn colorMid" type="fixedDepts" content="30_tasks" label="<?php echo Tasks::SECTION_ROOT; ?>"><?php echo mb_strtoupper(L_ROOT); ?></div>
		<div class="inline deptBtn colorMid" type="fixedDepts" content="30_tasks" label="<?php echo Tasks::SECTION_ASSETS; ?>"><?php echo mb_strtoupper(L_ASSETS); ?></div>
		<div class="inline deptBtn colorMid" type="fixedDepts" content="30_tasks" label="<?php echo Tasks::SECTION_SCENES; ?>"><?php echo mb_strtoupper(L_SCENES); ?></div>
		<div class="inline deptBtn colorMid" type="fixedDepts" content="30_tasks" label="<?php echo Tasks::SECTION_SHOTS; ?>"><?php echo mb_strtoupper(L_SHOTS); ?></div>
	</div>
</div>

<div projectID="<?php echo $id_project; ?>" class="projIdentity hide"></div>

<div class="ui-corner-all" id="retourAjax"></div>

<div class="pageContent noscroll">
	<div class="inline mid margeTop10 big gras pad10 ui-state-disabled"><?php echo stripslashes(L_FIRST_TIME); ?></div>
	<div class="inline mid margeTop10 ui-state-processing ui-corner-all"><span class="ui-icon ui-icon-arrowthickstop-1-n"</span></div>
</div>


<?php
	}
	catch (Exception $e) { die('<span class="colorErreur">'. $e->getMessage().'</span>'); }
?>
