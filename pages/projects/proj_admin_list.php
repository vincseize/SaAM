<?php
@session_start();
require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
require_once ('dates.php' );
require_once ('vignettes_fcts.php');

$l = new Liste();
if ($_SESSION['user']->isDemo()) {
	$l->addFiltre(Projects::PROJECT_TYPE, "=", 'demo');
}
$project_list = $l->getListe(TABLE_PROJECTS,'*', 'position');

try {
	$acl = new ACL(@$_SESSION['user']); ?>

<script>
	$(function(){
		$('.bouton').button();
		// init des progressBars
		$('.progBar').each(function() {
			var percent = parseInt($(this).attr('percent'));
			$(this).progressbar("destroy");
			$(this).progressbar({ value: percent });
		});
		$('.greyButton').addClass('ui-state-disabled doigt');

		// Réarrangement de la position des projets
		$('#liste').sortable({
			placeholder: 'ui-state-highlight',
			forcePlaceholderSize: true,
			axis: 'y',
			update: function(e, ui) {
				var posArr = {}; var i = 1;
				$('li[idProj]').each(function(){
					posArr[$(this).attr('idProj')] = i;
					i++;
				});
				<?php if (!$_SESSION['user']->isDemo()): ?>
					ajaxUpdateProjPos(posArr);
				<?php endif; ?>
			}
		});
	});
</script>

<script src="ajax/admin_projects.js"></script>

<div class="stageContent">
	<h2 class="marge10l">Liste des projets</h2>
	<table class="leftText w100p">
		<tr>
			<th style="width: 250px;"><span style="margin-left:55px;"><?php echo L_TITLE; ?></span></th>
			<th class="w150">Seq. Shots FPS</th>
			<th class="center"><?php echo L_PROGRESS; ?></th>
			<th class="w100"><span class="marge20l"><?php echo L_START; ?></span></th>
			<th class="w100"><span class="marge20l"><?php echo L_END; ?></span></th>
			<th class="w100"><span class="marge10l">Proj. Size</span></th>
			<th class="center w180">Actions</th>
		</tr>
	</table>
	<ul id="liste"><?php
		foreach ($project_list as $proj) :
			$ACLcheckView = $acl->check("ADMIN_PROJECTS_VIEW", "proj:".$proj['id']);
			$ACLcheckMod  = $acl->check("ADMIN_PROJECTS_MODIFY", "projcreated:".$proj['id']);
			if (!$ACLcheckView)
				continue;
			$dateDebut = new DateTime($proj['date']);
			$dateFin   = new DateTime($proj['deadline']);
			$classHiddenProj = ($proj['hide'] == 1) ? 'greyButton' : '';
			$classLockedProj = ($proj['lock'] == 1) ? 'greyButton' : '';
			$iconLockedProj = ($proj['lock'] == 1) ? 'ui-icon-locked' : 'ui-icon-unlocked';

			$vignette = check_proj_vignette_ext($proj['id'], $proj['title']);
			$P = new Projects($proj['id']);
			$projectNbSeq  = $P->getNbSequences();
			$projectNbShot = $P->getNbShots();

			$projSize = get_project_size($proj['id']);
			$projSizeRatio = round($projSize[3] / ($_SESSION['CONFIG']['projects_size']) * 100);
			$projDiskSpace = $projSize[0]." ($projSizeRatio%)";

			$disabledBtns = ($proj['id'] == 1) ? 'ui-state-disabled' : ''; ?>

			<li class="ui-state-default" idProj="<?php echo $proj['id']; ?>">
				<table class="seqTable">
					<tr class="seqLine">
						<td style="width: 250px;">
							<img class="mid" src="<?php echo $vignette; ?>" height="30" class="padV5" />
							<span class="mid proj_title" title="<?php echo L_TITLE; ?>"><?php echo $proj['title']; ?></span>
							<span class="mid ui-state-disabled" title="type">(<?php echo $proj['project_type']; ?>)</span>
						</td>
						<td class="w150">
							<span class="inline mid ui-icon ui-icon-video" title="<?php echo L_SEQUENCES; ?>"></span> <?php echo $projectNbSeq; ?>
							<span class="inline mid ui-icon ui-icon-copy" title="<?php echo L_SHOTS; ?>"></span> <?php echo $projectNbShot; ?>
							<span class="inline mid ui-icon ui-icon-signal" title="<?php echo L_FPS; ?>"></span> <?php echo $proj['fps']; ?>
						</td>
						<td>
							<div class="progBar miniProgBar" percent="<?php echo $proj['progress']; ?>" title="<?php echo L_PROGRESS; ?>">
								<span class="floatL marge10l margeTop1 petit colorMid"><?php echo $proj['progress']; ?>%</span>
							</div>
						</td>
						<td class="w100" title="<?php echo L_START; ?>">
							<span class="inline mid ui-icon ui-icon-clock"></span> <span class="inline mid proj_start"><?php echo $dateDebut->format('d/m/Y'); ?></span>
						</td>
						<td class="w100 ui-state-error-text colorHard" title="<?php echo L_END; ?>">
							<span class="inline mid ui-icon ui-icon-clock"></span> <span class="inline mid proj_end"><?php echo $dateFin->format('d/m/Y'); ?></span>
						</td>
						<td class="w100" title="<?php echo L_CREATOR; ?>">
							<span class="inline mid ui-icon ui-icon-disk"></span> <span class="inline mid proj_crea" title="Project size (% of total server alloed size)"><?php echo $projDiskSpace; ?></span>
						</td>
						<td class="w180 rightText micro actionBtns <?php echo $disabledBtns; ?>"><?php
							if ($ACLcheckMod || ($_SESSION['user']->isDemo() && $proj[Projects::PROJECT_ID_PROJECT] == 1)) :
								if ($proj['archive'] == 1) : ?>
									<button class="bouton proj_restore ui-state-highlight" title="<?php echo L_RESTORE; ?>"><span class="ui-icon ui-icon-refresh"></span></button>
									<button class="bouton proj_destroy ui-state-error marge10r" title="<?php echo L_DESTRUCT; ?>"><span class="ui-icon ui-icon-trash"></span></button><?php
								else : ?>
									<button class="bouton <?php echo $classHiddenProj; ?> proj_hide" title="<?php echo L_SHOW; ?>/<?php echo L_HIDE; ?>"><span class="ui-icon ui-icon-lightbulb"></span></button>
									<button class="bouton <?php echo $classLockedProj; ?> proj_lock" title="<?php echo L_LOCK; ?>"><span class="ui-icon <?php echo $iconLockedProj; ?>"></span></button>
									<button class="bouton marge10l proj_mod" title="<?php echo L_RENAME; ?>"><span class="ui-icon ui-icon-pencil"></span></button>
									<button class="bouton proj_zip" title="<?php echo L_BACKUP; ?>"><span class="ui-icon ui-icon-suitcase"></span></button>
									<button class="bouton marge10l proj_del" title="<?php echo L_ARCHIVE; ?>"><span class="ui-icon ui-icon-trash"></span></button><?php
								endif;
							endif; ?>
						</td>
					</tr>
				</table>
			</li><?php
		endforeach; ?>
	</ul>
</div><?php
}
catch (Exception $e) { die('<span class="colorErreur">'. $e->getMessage().'</span>'); }
?>