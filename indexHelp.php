<?php
	require_once ('inc/checkConnect.php' );					// Ligne à placer toujours en haut du code des pages
	$titrePageBar = 'HELP SaAM v'.SAAM_VERSION;
	if (isset($_SESSION['user']))
		$langue = $_SESSION['user']->getUserInfos('lang');
	else $langue = LANG_DEFAULT;
	$helpTitle = ($langue == 'fr') ? 'AIDE ' : 'HELP ' ;
	include('head_html.php');
?>

<script>
	$(function(){
		// Init de la scroll
		var stageHeight = $('#stage').height() - 26;
		$('.pageContent').slimScroll({
			position: 'right',
			height: stageHeight+'px',
			size: '10px',
			wheelStep: 8,
			railVisible: true
		});

		$(document).keydown(function(e) {
			var lock = $('input').is(":focus") || $('textarea').is(":focus");
			switch( e.keyCode || e.which ) {
				case 116:				// touche F5
					if (!isCtrl) {e.preventDefault(); window.location = 'indexHelp.php';}			// rafraichi la page (soft)
			}
		});
		// positions des ancres onload
		var aPositions = {};
		setTimeout(function(){
			$('a').each(function(){
				var id = $(this).attr('id');
				var thisPos = $(this).position();
				aPositions[id] = thisPos.top;
			});
		}, 1000);

		// boutons du header stage
		$('.helpBtn').hover(
			function() {
				$(this).addClass('ui-state-hover');
			},
			function() {
				$(this).removeClass('ui-state-hover');
			}
		);
		$('.helpBtn').click(function() {
			var gotoAnchor = $(this).attr('content');
			$('.helpBtn').removeClass('ui-state-active colorHard');
			$('.inline[content="'+gotoAnchor+'"]').addClass('ui-state-active colorHard');
			$('.pageContent').slimScroll({ scrollTo: aPositions[gotoAnchor]+'px' });
		});

		// Si une catégorie ET un paragraphe sont définis
		var categ = $(document).getUrlParam('c');
		var parag = $(document).getUrlParam('p');
		if (categ != undefined && parag != undefined) {
			$('.pageContent').slimScroll({ scrollTo: aPositions[parag]-120+'px' });
			$('.helpBtn').removeClass('ui-state-active colorHard');
			$('.inline[content="'+categ+'"]').addClass('ui-state-active colorHard');
		}

		// Clic sur les boutons "scroll to Top"
		$('.btnTop').click(function() {
			$('.pageContent').slimScroll({ scrollTo: '0px' });
			$('.helpBtn').removeClass('ui-state-active colorHard');
			$('.inline[content="sommaire"]').addClass('ui-state-active colorHard');
		});

	});
</script>
<style>
	li[content]:hover {
		text-decoration: underline;
	}
	.bouton {
		font-size: 0.8em !important;
	}
</style>

<body>

	<div id="bigDiv" class="fondPage ui-widget">

		<div id="headerPage" class="bordBottom bordSection gros center" style="padding: 3px 10px 10px 5px;">
			<div class="enorme">SaAM <?php echo L_BTN_HELP; ?></div>
		</div>

		<div id="Page" class="ui-widget">

            <div class="colonne L center" id="leftCol">
				<img src="gfx/logoV.png" />
			</div>

			<div class="colonne C" id="centerCol">
				<div id="stage" class="fondSect2">
					<div class="headerStage">
						<div class="inline helpBtn colorSoft ui-state-active colorHard" content="sommaire" active>Sommaire</div>
						<div class="inline helpBtn colorSoft" content="navigation">Navigation</div>
						<div class="inline helpBtn colorSoft" content="homepage">Home</div>
						<div class="inline helpBtn colorSoft" content="projects"><?php echo L_PROJECTS; ?></div>
						<div class="inline helpBtn colorSoft" content="departments"><?php echo L_DEPTS; ?></div>
						<div class="inline helpBtn colorSoft" content="sequences"><?php echo L_SEQUENCES; ?></div>
						<div class="inline helpBtn colorSoft" content="shots"><?php echo L_SHOTS; ?></div>
						<div class="inline helpBtn colorSoft" content="scenes"><?php echo L_SCENES; ?></div>
						<div class="inline helpBtn colorSoft" content="assets"><?php echo L_ASSETS; ?></div>
						<div class="inline helpBtn colorSoft" content="tasks"><?php echo L_TASKS; ?></div>
						<div class="inline helpBtn colorSoft" content="tags"><?php echo L_TAGS; ?></div>
						<div class="inline helpBtn colorSoft" content="news"><?php echo L_NEWS; ?></div>
						<div class="inline helpBtn colorSoft" content="users"><?php echo L_USERS; ?></div>
						<div class="inline helpBtn colorSoft" content="notes"><?php echo L_NOTES; ?></div>
					</div>

					<div class="pageContent big"><?php
						if (!@include('help/help_'.$langue.'.php'))
							include('help/help_'.LANG_DEFAULT.'.php'); ?>
					</div>
				</div>
			</div>

		</div>

	</div>

</body>
</html>
