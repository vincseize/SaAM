<?php
	require_once ('inc/checkConnect.php' );
	include('head_html.php');
?>
<body>

	<div id="bigDiv" class="fondPage ui-widget">

		<!-- Si pas de connexion, on charge la modal de connexion -->
		<?php
			if ( !isset($_SESSION['user']) ) {
				include ('modals/connexion.php');
				die();
			}
		?>

		<div id="headerPage" class="fondPage bordBottom bordSection petit" help="raccourcis">
			<?php include('menuHeader.php'); ?>
			<div id="loadingIcon"><img src="gfx/ajax-loader-big.gif" /></div>
		</div>

		<div id="Page" class="ui-widget">

			<!-- Affichage menu d'aide global (touche "h") -->
			<div class="hide" id="globalHelp">
				<div id="globalHelpContent">
					<?php include('help.php'); ?>
				</div>
			</div>
			<!-- Affichage menu de last news (touche "n") -->
			<div class="hide" id="globalNews">
				<?php include('news.php'); ?>
			</div>

			<!-- COLONNE DE GAUCHE (affichage menu)	-->
            <div class="colonne L center" id="leftCol">
				<?php include('menuShortcuts.php'); ?>
			</div>

			<div id="btnLeftPanelToggle" title="expand my shots" help="menu_shortcuts">
				<span class="ui-icon ui-icon-grip-dotted-vertical"></span>
			</div>

			<!-- COLONNE DU CENTRE (affichage contenus)	-->
			<div class="colonne C" id="centerCol">
				<?php include('tabsProjects.php'); ?>
			</div>


			<div id="btnRightPanelToggle" title="expand tools menu" help="tools_panel">
				<span class="ui-icon ui-icon-grip-dotted-vertical"></span>
			</div>

			<!-- COLONNE DE DROITE (affichage outils)	-->
			<div class="colonne R ui-corner-left center" id="rightCol">
				<?php include('menuTools.php'); ?>
			</div>

			<div id="footerPage" class="fondSect1" help="messages_panel">
				<?php include('menuFooter.php'); ?>
			</div>

			<div id="btnFooterToggle" title="expand bottom menu"  help="messages_panel">
				<span class="ui-icon ui-icon-grip-dotted-horizontal"></span>
			</div>

			<div id="dialog-tasks"></div>

		</div>

	</div>

	<div id="version">
		<p class='version-vertical-text colorDiscret'>
		<b>SaAM v<?php echo SAAM_VERSION; ?></b> | LRDS &copy; 2012-<?php echo date('Y'); ?>
		</p>
	</div>

</body>
</html>