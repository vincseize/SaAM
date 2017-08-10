<?php
@session_start();

if (!isset($_SESSION["user"])) die('No user connected.');

$dontTouchSSID = true;

require_once ($_SESSION['INSTALL_PATH'].'inc/checkConnect.php' );
require_once ('vignettes_fcts.php');
require_once ('dates.php');

$userID = $_SESSION['user']->getUserInfos(Users::USERS_ID);

$tl = new Liste();
if (isset($_SESSION['active_project_id']))
	$tl->addFiltre(Tasks::ID_PROJECT_TASK, '=', $_SESSION['active_project_id']);
$tl->addFiltre(Tasks::STATUS_TASK, '!=', 3);
$tl->addFiltre(Tasks::HIDE_TASK, '=', 0);
$tl->getListe(TABLE_TASKS, '*', 'end', 'DESC');
$listeTasks = $tl->simplifyList('id');

if (is_array($listeTasks)) :
	foreach ($listeTasks as $task) :
//		if (@$_SESSION['active_project_id'] != $task['ID_project'])
//			continue;
		if ($task[Tasks::STATUS_TASK] == 3)
			continue;
		switch($task['section']) {
			case Tasks::SECTION_ROOT:
				$section = L_ROOT; break;
			case Tasks::SECTION_ASSETS:
				$section = L_ASSETS; break;
			case Tasks::SECTION_SCENES:
				$section = L_SCENES; break;
			case Tasks::SECTION_SHOTS:
				$section = L_SHOTS; break;
			default: continue;
		}
		$assigned_to = json_decode($task['assigned_to']);
		$classTask = 'ui-state-default';
		if (is_array($assigned_to)) {
			if (in_array($userID, $assigned_to))			// If user assigned to the task
				$classTask = 'ui-state-focus';
			elseif ($userID != $task['ID_creator'])									// If user not assigned and not the creator
				continue;
		}
		if ($userID == $task['ID_creator'])					// If user is the creator of the task
			$classTask .= ' bordTaskOwn'; ?>
		<div class="inline gray-layer noBorder ui-corner-all w9p margeTop5 leftText pad3 doigt myTask <?php echo $classTask; ?>" help="vos_taches"
			 taskID="<?php echo $task['id']; ?>"
			 sectionTask="<?php echo $task['section']; ?>"
			 projectID="<?php echo $task['ID_project']; ?>">
			<span class="floatR colorErrText gras" title="Task type"><?php echo $task['type']; ?></span>
			<div class="colorErrText gras" title="Task section"><?php echo mb_convert_case($section, MB_CASE_UPPER); ?></div>
			<div class="fixFloat" style="padding: 2px 0px;" title="Task title"><?php echo $task['title']; ?></div>
			<?php
			$end  = SQLdateConvert($task[Tasks::END_TASK]);
			$endT = SQLdateConvert($task[Tasks::END_TASK], 'timeStamp');
			$classDate = 'ui-state-default';
			if ($end != '?' && $endT < time())
				$classDate = 'ui-state-error'; ?>
			<div class="floatR noBorder noBG <?php echo $classDate; ?>"><span class="ui-icon ui-icon-clock"></span></div>
			<span class="<?php echo $classDate; ?>" title="Task end date (deadline)"><?php echo $end; ?></span>
		</div> <?php
	endforeach;
else: ?>
	<br /><span class="ui-state-disabled"><?php echo L_NOTHING.' '.L_ASSIGNED_TO.' '.$_SESSION['user']->getUserInfos(Users::USERS_PSEUDO); ?>.</span>
<?php
endif; ?>


<script>
	$(function(){
		reCalcScrollMyMenu();
		$('.myTask').hover(
			function(){$(this).addClass('ui-state-active').removeClass('gray-layer');},
			function(){$(this).removeClass('ui-state-active').addClass('gray-layer');}
		);

		// Clic sur un MyTask
        $('#myMenu').off('click', '.myTask');
		$('#myMenu').on('click', '.myTask', function() {
			var idProj = $(this).attr("projectID");
			var section = $(this).attr("sectionTask");
			var taskID = $(this).attr("taskID");

			localStorage['lastGroupDepts_'+idProj] = "tasks";
			localStorage['lastDept_'+idProj+'_GRP_tasks'] = section;
			localStorage['activeBtn_'+idProj]	= '30_tasks';
			localStorage['lastDept_'+idProj]	= section;
			localStorage['openTask_'+idProj]	= taskID;

			if (localStorage['activeContent'] == idProj) {
				$('#selectDeptsList').val('tasks').change();
				$('#tasks_depts').find('.deptBtn[label="'+section+'"]').click();
			}
			else
				openProjectTab (idProj);
		});

	});
</script>