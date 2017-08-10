<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC'].'/checkConnect.php' );
	require_once ("users_fcts.php");

	// OBLIGATOIRE, id du projet à charger
	if (isset($_POST['projectID']))
		$idProj = $_POST['projectID'];
	else die('Pas de projet à charger...');

try {
	$section = $_POST['dept'];
	$p = new Projects($idProj);
	switch($section) {
		case Tasks::SECTION_ASSETS:
			$sectionL = L_ASSETS;
			$entities = $p->getAssets('actifs');
			$entNameKey = Assets::ASSET_NAME;
			$showAddTaskBtn = false;
			break;
		case Tasks::SECTION_SCENES:
			$sectionL = L_SCENES;
			$entities = $p->getScenes('all', 'actifs');
			$entNameKey = Scenes::TITLE;
			$showAddTaskBtn = false;
			break;
		case Tasks::SECTION_SHOTS:
			$sectionL = L_SHOTS;
			$entities = $p->getShots('all', 'actifs');
			$entNameKey = Shots::SHOT_TITLE;
			$showAddTaskBtn = false;
			break;
		default:
			$sectionL = L_ROOT;
			$entities = false;
			$showAddTaskBtn = true;
			break;
	}
	$tasksShowed = 0;
	$usersProject = Users::getUsers_by_projects(Array((int)$idProj), 9);

	$allTypes = '';
	if ($showAddTaskBtn) {
		$l = new Liste();
		$allTypes = array_filter(array_unique($l->getListe(TABLE_TASKS, Tasks::TYPE_TASK)));
		sort($allTypes);
		$allTypes = implode(',', $allTypes);
	}
//	echo '<pre>'; var_dump($allTypes); echo '</pre>';
}
catch (Exception $e) {
	die($e->getMessage());
}
?>

<script>
	var project_ID = '<?php echo $idProj; ?>';

	$(function(){
		$('.bouton').button();

		var availableTypes = "<?php echo $allTypes; ?>";
		$('#type_task').autocomplete({
			source: availableTypes.split(',')
		});

		var view = stageHeight - 28;
		$('.stageContent').slimScroll({
			position: 'right',
			height: view+'px',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		});
	});
</script>
<script src="ajax/tasks.js"></script>

<div class="stageContent fondSect1 pad5">
	<table class="tasksUI">
		<tr>
			<th class="tasksUI-list mini">
				<?php if ($showAddTaskBtn): ?>
					<button class="bouton floatR marge30r" title="<?php echo L_ADD_TASK; ?>" id="showAddTask"><span class="ui-icon ui-icon-plusthick"></span></button>
				<?php endif; ?>
				<span class="enorme colorHard gras">
					<?php echo L_TASKS.' '.$sectionL; ?>
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
					<input type="text" class="noBorder ui-corner-all pad3 w100p fondSect1" id="title_task" placeholder="<?php echo L_TITLE; ?>" /><br />
					<textarea class="noBorder ui-corner-all pad3 w100p fondSect1" rows="5" id="descr_task" placeholder="<?php echo L_DESCRIPTION; ?>"></textarea><br /><br />
					<input type="text" class="noBorder ui-corner-all pad3 w100p fondSect1" id="type_task"  placeholder="<?php echo L_TYPE; ?>" /><br /><br />
					<input type="text" class="noBorder ui-corner-all pad3 fondSect1 inputCal" id="start_task" value="<?php echo Date(DATE_FORMAT); ?>" placeholder="<?php echo L_START; ?>" />
					<input type="text" class="noBorder ui-corner-all pad3 fondSect1 inputCal" id="end_task" placeholder="<?php echo L_END; ?>" /><br />
					<input type="hidden" id="project_task" value="<?php echo $idProj; ?>" />
					<input type="hidden" id="section_task" value="<?php echo $section; ?>" /><br />
					<div class="inline top">
						<?php echo L_ASSIGNMENTS.' '.L_USERS; ?><br />
						<select id="assignee_task" multiple="multiple"><?php
							foreach($usersProject as $usr) : ?>
							<option value="<?php echo $usr[Users::USERS_ID]; ?>"><?php echo $usr[Users::USERS_PSEUDO]; ?></option><?php
							endforeach; ?>
						</select>
					</div>
					<br />
					<div class="rightText micro marge15bot">
						<button class="bouton ui-state-error" id="cancelAddTask"><span class="ui-icon ui-icon-cancel"></span></button>
						<button class="bouton" id="confirmAddTask"><span class="ui-icon ui-icon-check"></span></button>
					</div>
				</div>

				<?php
				foreach($_SESSION['CONFIG']['DEFAULT_STATUS'] as $stID => $status):
					$tasks	 = Tasks::getProjectTasks((int)$idProj, true, $section, (int)$stID);
					$status  = ($stID == 0) ? 'TODO' : $status;
					$lineCol = ($stID == 0) ? 'focus' : 'default';
					$headCol = ($stID == 0) ? 'colorErrText' : '';
					$nbTasks = count($tasks);
					$showTR	 = ($nbTasks < 1) ? 'hide' : '';
					if ($nbTasks > 0) $tasksShowed++; ?>
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
								<td class="w80 colorHard triTasks selTri" tri="start" sens="ASC">
									&nbsp;<div class="inline mid"><?php echo L_START; ?></div>
									<div class="inline mid iconTri"><span class="ui-icon ui-icon-triangle-1-s"></span></div>
								</td>
								<td class="w80 colorSoft triTasks" tri="end" sens="ASC">
									&nbsp;<div class="inline mid"><?php echo L_END; ?></div>
								</td>
							</tr>
						</thead>
						<tbody><?php
							foreach($tasks as $tID => $task):
								$uF = new Users((int)$task[Tasks::CREATOR_TASK]);
								$from = $uF->getUserInfos(Users::USERS_PSEUDO);
								$classFrom = ($task[Tasks::CREATOR_TASK] == $_SESSION['user']->getUserInfos('id')) ? '' : 'colorSoft';
								$team = formatTeam($task[Tasks::ASSIGNEE_TASK]); ?>
								<tr class="ui-state-<?php echo $lineCol; ?> doigt taskLine" taskID="<?php echo $tID; ?>">
									<td class="pad5 petit" col="title"><?php echo $task[Tasks::TITLE_TASK]; ?></td>
									<td class="pad5 petit" col="team"><?php echo $team; ?></td>
                                    <td class="pad5 petit <?php echo $classFrom; ?>" col="from"><?php echo $from; ?></td>
									<td class="pad5 petit" col="type"><?php echo $task[Tasks::TYPE_TASK]; ?></td>
									<td class="pad5 colorSoft petit" col="start" timeStamp="<?php echo SQLdateConvert($task[Tasks::START_TASK], 'timeStamp'); ?>"><?php echo SQLdateConvert($task[Tasks::START_TASK]); ?></td>
									<td class="pad5 colorErrText petit" col="end" timeStamp="<?php echo SQLdateConvert($task[Tasks::END_TASK], 'timeStamp'); ?>"><?php echo SQLdateConvert($task[Tasks::END_TASK]); ?></td>
								</tr><?php
							endforeach; ?>
						</tbody>
					</table><?php
				endforeach;
				if ($tasksShowed == 0) : ?>
					<div class="colorDiscret noTaskMsg">No task here.<?php if($showAddTaskBtn): ?> Click the + button to create one.<?php endif; ?></div>
				<?php endif; ?>
			</td>
			<td class="tasksUI-content fondSect2 ui-corner-tl">
				<p class="colorDiscret marge30l">Select a task to show.</p>
			</td>
		</tr>
	</table>
</div>