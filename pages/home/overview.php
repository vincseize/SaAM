<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	require_once ('dates.php' );
	require_once ('vignettes_fcts.php' );

	$userProjects	= $_SESSION['user']->getUserProjects();
	$maxShotsVi		= 25;

	$l = new Liste();
/////////////////////////////////////////////////////////////////////////////// PROJECTS
	//$projects_list = array();
	$l->addFiltre(Projects::PROJECT_ARCHIVE, '=', 0, 'AND');
	$l->addFiltre(Projects::PROJECT_HIDE, '=', 0);
	$l->getListe(TABLE_PROJECTS, '*', 'position', 'ASC');
	$l->resetFiltre();
	$projects_list = $l->simplifyList(Projects::PROJECT_ID_PROJECT);

	if (is_array($userProjects)){
		$nbProjects = count($projects_list);
		$nbUserProjects = 0;

		$progressBars = '';
		$infosProjects = '';
		$caroussels = '';
		$IDsProjList = array();
		$JSprojList = $JSpreImgList = '{'; $JSImgList  = ''; $JSshotList = '{';

		foreach($projects_list as $idProj => $proj) {
			if (!in_array($idProj, $userProjects)) continue;
			$nbUserProjects ++;

			$p = new Projects($idProj);
			$nbSeqs   = $p->getNbSequences();
			$nbShots  = $p->getNbShots();
			$nbAssets = $p->getNbAssets();
			$fps	  = $p->getProjectInfos(Projects::PROJECT_FPS);
			$director = $proj[Projects::PROJECT_DIRECTOR];
			$shotsProj= $p->getShots('all', 'actifs', Shots::SHOT_UPDATE, 'DESC');

			$JSprojList		.= $idProj.': "'.$proj['title'].'", ';
			$JSpreImgList	.= $idProj.': new Image(), ';
			$JSImgList		.= "vignetList[".$idProj."].src = '". addslashes(check_proj_vignette_ext($idProj, $proj['title']))."';\n";
			$JSshotList		.= "$idProj:{"; $shCount = 0;
			if (is_array($shotsProj)) {
				foreach ($shotsProj as $ind => $shot) {
					if ($shCount == $maxShotsVi) break;
					$se = new Sequences($shot[Shots::SHOT_ID_SEQUENCE]);
					$labelSeq	= $se->getSequenceInfos(Sequences::SEQUENCE_LABEL);
					$titleSeq	= $se->getSequenceInfos(Sequences::SEQUENCE_TITLE);
					$viShot		= check_shot_vignette_ext($idProj, $labelSeq, $shot[Shots::SHOT_LABEL]);
					$JSshotList .= $ind.':["'.$shot[Shots::SHOT_ID_SHOT].'","'.$titleSeq.'/'.$shot[Shots::SHOT_TITLE].' : '.$shot[Shots::SHOT_PROGRESS].'%","'.$viShot.'"],';
					$shCount++;
					unset($se);
				}
			}
			$JSshotList		 = trim($JSshotList,',');
			$JSshotList		.= "},";


			$IDsProjList[] = $idProj;
			$progressBars .= '<div class="hide pBs" id="progBarsProj_'.$idProj.'">
								<div class="progBar" help="project_progressBar" idProj="'.$idProj.'" percent="'.$proj['progress'].'">
									<span class="floatL marge5 colorSoft hide">'.L_PROJECT.' : '.$proj['progress'].'%</span>
								</div>
							  </div>';

			$firstShotLabel = preg_replace('/###/', '001', $p->getNomenclature('shot'));
			$infosProjects .= '<div class="hide projInfos" help="project_infos" id="infosProj_'.$idProj.'">
								<div class="inline top w50">
									<span class="inline mid ui-icon ui-icon-video" title="'.L_SEQUENCES.'"></span> <span class="inline mid gras" title="'.L_SEQUENCES.'">'.$nbSeqs.'</span>
									<br />
									<span class="inline mid ui-icon ui-icon-copy" title="'.L_SHOTS.'"></span> <span class="inline mid gras" title="'.L_SHOTS.'">'.$nbShots.'</span>
									<br />
									<span class="inline mid ui-icon ui-icon-image" title="'.L_ASSETS.'"></span> <span class="inline mid gras" title="'.L_ASSETS.'">'.$nbAssets.'</span>
								</div>
								<div class="inline top w150">
									<span class="inline mid ui-icon ui-icon-extlink" title="'.L_FORMAT.'"></span> <span class="inline mid gras" title="'.L_FORMAT.'">16/9</span>
									<br />
									<span class="inline mid ui-icon ui-icon-signal" title="'.L_FPS.'"></span> <span class="inline mid gras" title="'.L_FPS.'">'.$fps.' '.L_FPS.'</span>
									<br />
									<span class="inline mid ui-icon ui-icon-person" title="'.L_DIRECTOR.'"></span> <span class="inline mid gras" title="'.L_DIRECTOR.'">'.$director.'</span>
								</div>
							</div>';
			$caroussels .= '<ul class="carousselContent hide" id="caroussel_'.$idProj.'">';
			if ($nbSeqs > 0) {
				foreach ($p->getSequences() as $seq) {
					if ($seq['hide'] == 1 || $seq['archive'] == 1) continue;
					$caroussels .= '<li style="background-image:url(\''.check_shot_vignette_ext($idProj, $seq['label'], $firstShotLabel).'\');background-size:contain;">'.$seq['title'].'</li>';
				}
			}
			else
				$caroussels .= '<li style="background-color:rgba(0,0,0,0.5); font-size:1.2em;"><br /><br />NO sequence!</li>';
			$caroussels .= '</ul>';
		}
		if ($nbUserProjects == 0)
			die('<div class="ui-state-error pad5 gros gras">You have no project to show !!</div>');

		$JSprojList		= substr($JSprojList, 0, -2);		$JSprojList		.= '}';
		$JSpreImgList	= substr($JSpreImgList, 0, -2);		$JSpreImgList	.= '}';
		$JSshotList		= trim($JSshotList,',');		$JSshotList 	.= '}';

		// Projet à montrer par défaut
		$keyActiveProj		= array_rand($IDsProjList, 1);
		$activeProjectID	= $IDsProjList[$keyActiveProj];
		$activeProjectTitle = $projects_list[$activeProjectID]['title'];

		if (count($IDsProjList) > 1) {
				$prevKey = $keyActiveProj-1;
				$nextKey = $keyActiveProj+1;
				if		($keyActiveProj == 0) $prevKey = count($IDsProjList)-1;
				elseif  ($keyActiveProj == (count($IDsProjList)-1)) $nextKey = 0;
		}
		else $prevKey = $nextKey = $keyActiveProj;

		$prevProjectID      = $IDsProjList[$prevKey];
		$prevProjectTitle   = $projects_list[$prevProjectID]['title'];
		$nextProjectID      = $IDsProjList[$nextKey];
		$nextProjectTitle   = $projects_list[$nextProjectID]['title'];

		$projOverviewed     = '<span class="fleche" id="projOverviewed">'.$activeProjectTitle.'</span>';
		$vignetteOverviewed = '<img id="vignetteP" src="'.check_proj_vignette_ext($activeProjectID, $activeProjectTitle).'" />';
	}
	else die('No Projects !!');


/////////////////////////////////////////////////////////////////////////////// NEWS
    $news_list = $l->getListe(TABLE_NEWS,'*', 'new_date', 'DESC');
    $my_lang = $_SESSION['user']->getUserInfos(Users::USERS_LANG);;

?>


<script type="text/javascript" src="js/Snake.js"></script>
<script type="text/javascript" src="js/jquery.roundabout.min.js"></script>

<script type="text/javascript">

	var nbProjects  = <?php echo $nbProjects; ?>;
	var projList	= <?php echo $JSprojList; ?>;
	var vignetList	= <?php echo $JSpreImgList; ?>;
	var shotsList	= <?php echo $JSshotList; ?>;
	<?php echo $JSImgList; ?>

	// id du projet à montrer par défaut
	var activeProj = <?php echo $activeProjectID; ?>;

</script>
<script type="text/javascript" src="js/home_overview.js"></script>


<div class="stageContent padH5">
	<div class="topInfosHome" style="margin-top: -15px; padding-right: 20px;" id="projInfos">

		<div class="projTitles fondSemiTransp" help="home_projects_vignette">
			<?php
			if (count($IDsProjList) > 1) : ?>
				<div class="floatL doigt navigOverview" id="prevProj" idProj="<?php echo $prevProjectID; ?>" title="<?php echo $prevProjectTitle; ?>">
					<span class="inline mid ui-icon ui-icon-seek-prev"></span>
				</div>
				<div class="floatR doigt navigOverview" id="nextProj" idProj="<?php echo $nextProjectID; ?>" title="<?php echo $nextProjectTitle; ?>">
					<span class="inline mid ui-icon ui-icon-seek-next"></span>
				</div>
			<?php endif;
				echo $projOverviewed; ?>
		</div>

		<div id="vignettesProjects" help="home_projects_vignette">
			<?php echo $vignetteOverviewed; ?>
		</div>

		<div id="progressBarsHome" style="top:5px;">

			<?php echo $progressBars; ?>

			<?php echo $infosProjects; ?>

		</div>

		<div class="center" id="caroussel" help="caroussel">
			<?php echo $caroussels; ?>
		</div>

		<div id="pelliculeShots" help="home_shots_list">

		</div>

	</div>
</div>


<div id="newsHome" help="projects_tree">
	<div class="demi ui-corner-all fondSect1 colorHard welcomeHome">
		<span class="inline mid doigt ui-icon ui-icon-newwin"></span>
		<span class="inline mid pad5 gros gras"><?php echo L_WELCOME; ?></span>
		<div class="hide"><?php
			$welcomeFile = INSTALL_PATH . 'help/'.$my_lang.'/welcome_'.$my_lang.'.php';
			if (file_exists($welcomeFile))
				include ($welcomeFile);
			else include INSTALL_PATH . 'help/en/welcome_en.php';
		?></div>
	</div>

	<?php
	if (!is_array($news_list)) echo 'Pas de nouvelle, bonne nouvelle !';
	else {
		$n = 1;
		foreach ($news_list as $new) {
			if (!$new['visible'] || $new['visible'] == 0) continue;
			$dateNew = SQLdateConvert($new['new_date'], 'timeStamp');
			if ($new['id'] != 1) {
				if ($dateNew <= time()-AGE_NEWS_MAX || $n > HOME_MAX_NEWS)  continue;
			}
			$classNew = 'ui-corner-all fondPage colorDiscret homeNew';
			if ($new['id'] == 1) $classNew .= ' sticky bordHi newShowed';

			$affDateNew = ($new['id'] == 1) ? '' : date('d/m/Y', $dateNew);
			$hideContent = ($new['id'] != 1) ? 'hide' : 'show';

			echo '
				<div class="'.$classNew.'" pos="'.$n.'" help="sticky_new">
					<span class="inline mid doigt ui-icon ui-icon-newwin"></span>
					<span class="inline mid pad5 gros gras">'.stripslashes($new['new_title']).'</span>
					<span class="inline mid mini colorDiscret dateNew" timeNew="'.$dateNew.'"><i>'.$affDateNew.'</i></span>
					<div class="'.$hideContent.'">
						'.stripslashes($new['new_text']).'
					</div>
				</div>
			';
			$n++;
		}
	}
	?>
	<br /><br />
</div>



<canvas id="tree"></canvas>
