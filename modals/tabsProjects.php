<?php
@session_start();
if (isset($_SESSION['INSTALL_PATH_INC']))
	require_once ($_SESSION['INSTALL_PATH_INC'].'/checkConnect.php' );
else
	require_once ('inc/checkConnect.php' );

try {
	$l = new Liste();
	$projectsList = $l->getListe(TABLE_PROJECTS, '*', Projects::PROJECT_POSITION, 'ASC');
	$userProjects = $_SESSION['user']->getUserProjects();

	$acl = new ACL($_SESSION['user']);
	$allowAddProj = $acl->check('ADMIN_PROJECTS_ADD');
	if ($_SESSION['user']->isDemo() && $_SESSION['isDemoSaAM'])
		$allowAddProj = true;
?>

<div id="ongletsProjets" class="noBG noBorder">
	<ul class="petit">
		<li><a href="pages/home.php" title="stage" onglet="home" idProj="0"><span help="SaAM_home_navigation">Home</span></a></li>
		<li style="display:none;"><a href="#stage"><span>nothing</span></a></li> <!-- IMPORTANT, à laisser ! (petit trick pour déselectionner un onglet quand on est sur une autre page genre préférences)--> <?php
		foreach ($projectsList as $proj) :
			if (!is_array($userProjects)) continue;
			if (in_array($proj['id'], $userProjects) && $proj['archive'] != 1 && $proj['deleted'] != 1) :
				$styleOnglet = '';
				if ($proj['hide'] == 1) {
					if ($proj['ID_creator'] == $_SESSION['user']->getUserInfos('id')) $styleOnglet = 'class="colorMid"';
					else continue;
				} ?>
				<li>
					<a href="pages/showProject.php?proj=<?php echo $proj['id']; ?>" title="<?php echo L_OPEN_PROJECT." '".$proj['title']."'"; ?>" onglet="<?php echo $proj['title']; ?>" idProj="<?php echo $proj['id']; ?>">
						<span <?php echo $styleOnglet; ?> help="projects_navigation"><?php echo $proj['title']; ?></span>
					</a>
				</li><?php
			endif;
		endforeach;

		if ($allowAddProj) : ?>
			<li><a href="pages/addProject.php" help="SaAM_add_project" title="<?php echo L_ADD_PROJECT; ?>" onglet="addProject"><span class="ui-icon ui-icon-plusthick"></span></a></li><?php
		endif; ?>
	</ul>
	<!-- EMPLACEMENT DE LA DIV STAGE, MAIS CONSTRUITE PAR UI-TABS !!!!!
	<div id="stage"></div> -->
</div><?php
}
catch (Exception $e) {
	echo $e->getMessage();
}