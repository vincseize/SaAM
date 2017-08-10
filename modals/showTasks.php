<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH'].'/inc/checkConnect.php' );
	require_once ("users_fcts.php");

extract($_POST);

try {
	switch($section) {
		case Tasks::SECTION_ASSETS:
			$sectionL = L_ASSET;
			$a = new Assets((int)$projectID, (int)$idEntity);
			$entityName = $a->getName();
			break;
		case Tasks::SECTION_SCENES:
			$sectionL = L_SCENE;
			$c = new Scenes((int)$idEntity);
			$entityName = $c->getSceneInfos(Scenes::TITLE);
			break;
		case Tasks::SECTION_SHOTS:
			$sectionL = L_SHOT;
			$s = new Shots((int)$idEntity);
			$entityName = $s->getShotInfos(Shots::SHOT_TITLE);
			break;
		default:
			$sectionL = L_ROOT;
			$entityName = '';
			break;
	}
	$tasksShowed = 0;
	$usersProject = Users::getUsers_by_projects(Array((int)$projectID), 9);

	$l = new Liste();
	$allTypes = array_filter(array_unique($l->getListe(TABLE_TASKS, Tasks::TYPE_TASK)));
	sort($allTypes);
	$allTypes = implode(',', $allTypes);
}
catch (Exception $e) {
	die('<pre class="colorErreur">'.$e->getMessage().'</pre>');
} ?>

<script>
	$(function(){
		$('.bouton').button();

		var availableTypes = "<?php echo $allTypes; ?>";
		$('#type_task').autocomplete({
			source: availableTypes.split(',')
		});
	});
</script>
<script src="ajax/tasks.js"></script>


<table class="tasksUI">
	<tr>
		<th class="tasksUI-list mini">
			<button class="bouton floatR marge30r" title="<?php echo L_ADD_TASK; ?>" id="showAddTask"><span class="ui-icon ui-icon-plusthick"></span></button>
			<span class="enorme colorActiveFolder">
				<b class="colorHard"><?php echo L_TASKS; ?> :</b> <?php echo $sectionL; ?> <b class="colorHard"><?php echo $entityName; ?></b>
			</span>
		</th>
		<th class="">
			<div class="inline mid"><span class="gros colorActiveFolder">Selected Task</span></div>
			<div class="inline mid ui-corner-all marge10l pad5 hide gras" id="retourAjaxTasks"></div>
		</th>
	</tr>
	<tr>
		<td class="tasksUI-list">
			<div class="fondSect2 padV10 padH5 hide" id="addTaskDiv">
				<p>TASK CREATION FORM</p>
				<input type="text" class="noBorder ui-corner-all pad3 fondSect1 w100p" id="title_task" placeholder="<?php echo L_TITLE; ?>" /><br />
				<textarea class="noBorder ui-corner-all pad3 fondSect1 w100p" rows="5" id="descr_task" placeholder="<?php echo L_DESCRIPTION; ?>"></textarea><br /><br />
				<input type="text" class="noBorder ui-corner-all pad3 fondSect1 w100p" id="type_task"  placeholder="<?php echo L_TYPE; ?>" /><br /><br />
				<input type="text" class="noBorder ui-corner-all pad3 fondSect1 inputCal" id="start_task" placeholder="<?php echo L_START; ?>" value="<?php echo Date(DATE_FORMAT); ?>" />
				<input type="text" class="noBorder ui-corner-all pad3 fondSect1 inputCal" id="end_task"  placeholder="<?php echo L_END; ?>" /><br />
				<input type="hidden" id="project_task" value="<?php echo $projectID; ?>" />
				<input type="hidden" id="section_task" value="<?php echo $section; ?>" />
				<input type="hidden" id="entity_task"  value="<?php echo $idEntity; ?>" /><br />
				<?php echo L_ASSIGNMENTS; ?><br />
				<select id="assignee_task" multiple="multiple"><?php
					foreach($usersProject as $usr) : ?>
					<option value="<?php echo $usr[Users::USERS_ID]; ?>"><?php echo $usr[Users::USERS_PSEUDO]; ?></option><?php
					endforeach; ?>
				</select>
				<br />
				<div class="rightText micro marge15bot">
					<button class="bouton ui-state-error" id="cancelAddTask"><span class="ui-icon ui-icon-cancel"></span></button>
					<button class="bouton" id="confirmAddTask"><span class="ui-icon ui-icon-check"></span></button>
				</div>
			</div>

			<?php
			foreach($_SESSION['CONFIG']['DEFAULT_STATUS'] as $stID => $status):
				$tasks   = Tasks::getEntityTasks((int)$projectID, $section, (int)$idEntity, (int)$stID);
				$status  = ($stID == 0) ? 'TODO' : $status;
				$lineCol = ($stID == 0) ? 'focus' : 'default';
				$headCol = ($stID == 0) ? 'colorErrText' : '';
				$nbTasks = count($tasks);
				$showTR	 = ($nbTasks < 1) ? 'hide' : '';
				if ($nbTasks > 0)
					$tasksShowed++; ?>
				<table class="tasksUI-listTable <?php echo $showTR; ?>" id="taskTable-s<?php echo $stID; ?>">
					<thead>
						<tr class="colorDiscret petit" style="height: 30px;">
							<td class="w200 triTasks" tri="title" sens="ASC">
								<div class="inline mid">
									<span class="<?php echo $headCol; ?> gras"><?php echo $status; ?></span> <i>(</i><i class="nbTask"><?php echo $nbTasks; ?></i><i> <?php echo ($nbTasks > 1) ? L_TASKS : L_TASK; ?>)</i>
								</div>
							</td>
							<td class="colorSoft triTasks" tri="team" sens="ASC">
								&nbsp;<div class="inline mid"><?php echo L_ASSIGNMENTS; ?></div>
							</td>
							<td class="w100 colorSoft triTasks" tri="from" sens="ASC">
								&nbsp;<div class="inline mid"><?php echo L_FROM; ?></div>
							</td>
							<td class="w100 colorSoft triTasks" tri="type" sens="ASC">
								&nbsp;<div class="inline mid"><?php echo L_TYPE; ?></div>
							</td>
							<td class="w100 colorHard triTasks selTri" tri="start" sens="ASC">
								&nbsp;<div class="inline mid"><?php echo L_START; ?></div>
								<div class="inline mid iconTri"><span class="ui-icon ui-icon-triangle-1-s"></span></div>
							</td>
							<td class="w100 colorSoft triTasks" tri="end" sens="ASC">
								&nbsp;<div class="inline mid"><?php echo L_END; ?></div>
							</td>
						</tr>
					</thead>
					<tbody><?php
						foreach($tasks as $tID => $task):
							$uF = new Users((int)$task[Tasks::CREATOR_TASK]);
							$from = $uF->getUserInfos(Users::USERS_PSEUDO);
							$team = formatTeam($task[Tasks::ASSIGNEE_TASK]); ?>
							<tr class="ui-state-<?php echo $lineCol; ?> doigt taskLine" taskID="<?php echo $tID; ?>">
								<td class="pad5 petit" col="title"><?php echo $task[Tasks::TITLE_TASK]; ?></td>
								<td class="pad5 petit" col="team"><?php echo $team; ?></td>
								<td class="pad5 petit" col="from"><?php echo $from; ?></td>
								<td class="pad5 petit" col="type"><?php echo $task[Tasks::TYPE_TASK]; ?></td>
								<td class="pad5 colorSoft petit" col="start" timeStamp="<?php echo SQLdateConvert($task[Tasks::START_TASK], 'timeStamp'); ?>"><?php echo SQLdateConvert($task[Tasks::START_TASK]); ?></td>
								<td class="pad5 colorErrText petit" col="end" timeStamp="<?php echo SQLdateConvert($task[Tasks::END_TASK], 'timeStamp'); ?>"><?php echo SQLdateConvert($task[Tasks::END_TASK]); ?></td>
							</tr><?php
						endforeach; ?>
					</tbody>
				</table><?php
			endforeach;
			if ($tasksShowed == 0) : ?>
				<div class="colorDiscret noTaskMsg">No task here. Click the + button to create one.</div>
			<?php endif; ?>
		</td>
		<td class="tasksUI-content fondSect2 ui-corner-tl">
			<p class="colorDiscret marge30l">Select a task to show.</p>
		</td>
	</tr>
</table>
