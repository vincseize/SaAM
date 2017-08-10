<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

if (isset($_POST['projectID'])) {
	$idProj		= $_POST['projectID'];
	$dept		= $_POST['dept'];
	$deptID		= $_POST['deptID'];
	$deptFile	= $_POST['template'];
	$type		= $_POST['typeArbo'];

	try {
		$p = new Projects($idProj);
		$titleProj = $p->getTitleProject();
	}
	catch (Exception $e) { echo $e->getMessage(); }
}
else {
	$idProj		= 0;
	$titleProj	= '<div class="inline"><span class="ui-icon ui-icon-home"></span></div>';
	$dept		= "";
	$deptID		= 0;
	$deptFile	= "";
	$type		= 'racine';
}


include('menu_arbo_specific/menu_arbo_'.$type.'.php');

?>


<script>
	var departement = '<?php echo $dept; ?>';
	var deptID		= '<?php echo $deptID; ?>';
	var deptFile    = '<?php echo $deptFile; ?>';
	var idProj		= <?php echo $idProj; ?>;

	$(function() {
		// init de l'arbo
		$('.arboShots, .arboAssets, .arboScenes, .arboTasks').hide();

		var maxArboHeight = $('#arboMenu').height();

		$('#arboSeq').slimScroll({
			position: 'right',
			height: maxArboHeight+'px',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		});

		// hover seq
		$('#arboMenu').on('mouseenter', '.arboItem', function(){
			$('.arboItem').removeClass('ui-state-hover');
			$(this).addClass('ui-state-hover');
		});
		$('#arboMenu').on('mouseleave', '.arboItem', function(){
			$(this).removeClass('ui-state-hover');
		});

		// click sur un shot
		$('#arboMenu').off('click', '.arboItem');
		$('#arboMenu').on('click', '.arboItem', function() {
			if ($(this).attr('idSeq')) {
				$('#arboHeadProj').hide();
				$('#arboSeq').hide().parent('.slimScrollDiv').hide();
				$('#arboHeadSeq').show();
				var idSeq = $(this).attr('idSeq');
				var nameSeq = $(this).html();
				$('#titleSeq').html(nameSeq);
				$('.arboShots[idSeq="'+idSeq+'"], .arboScenes[idSeq="'+idSeq+'"]').show().parent('.slimScrollDiv').show();
				$('.arboShots[idSeq="'+idSeq+'"], .arboScenes[idSeq="'+idSeq+'"]').slimScroll({
					position: 'right',
					height: maxArboHeight+'px',
					size: '10px',
					wheelStep: 10,
					railVisible: true
				});
				$('li[idSeq="'+idSeq+'"]').find('.seqTable td:not(.nowrap)').first().click();
			}
			else if ($(this).attr('idShot')) {
				var idSeq  = $(this).parent('.arboShots').attr('idSeq');
				var idShot = $(this).attr('idShot');
				var gotoP  = 'structure/structure_shots';
				var params = {dept: departement, deptID: deptID, template: deptFile, projectID: idProj, sequenceID: idSeq, shotID: idShot};

				if (departement == 'structure')
					gotoP = 'depts_shot_specific/dept_structure_shots';
				else if (departement == 'scenario' || departement == 'sound' || departement == 'final' || departement == 'storyboard') {
					params['dept'] = 'storyboard';
					params['template'] = 'dept_storyboard';
				}
				else if (departement == 'dectech')
					gotoP = 'depts_shot_specific/dept_dectech_shots';

				loadPageContentModal(gotoP, params);

				$('.arboItem').removeClass('ui-state-focusFake noBG');
				$(this).addClass('ui-state-focusFake noBG');
			}
			else if ($(this).attr('idScene')) {
				var sceneID = $(this).attr('idScene');
				var seqID   = $(this).attr('sequenceID');
				$('.sceneFolder').removeAttr('opened').next('.sceneFolderContent').hide(150);
				$('.sceneFolderContent').has('.sceneMasterItem[sceneID="'+sceneID+'"]').each(function(){
					var theFolder = $(this).prev('.sceneFolder');
					if (theFolder.attr('idSeq')) {
						$('.sceneFolder[idSeq="'+seqID+'"]').next('.sceneFolderContent').find('.sceneMasterItem[sceneID="'+sceneID+'"]').click();
						$('.sceneFolder[idSeq="'+seqID+'"]').attr('opened', 'opened').next('.sceneFolderContent').show(150);
						return false;
					}
					else {
						$('.sceneMasterItem[sceneID="'+sceneID+'"]').click();
						theFolder.attr('opened', 'opened').next('.sceneFolderContent').show(150);
						return false;
					}
				});
//					$(this).prev('.sceneFolder').attr('opened', 'opened').click();
//				});
			}
			else if ($(this).attr('rootPathAsset')) {
				$('#arboHeadProj').hide();
				$('#arboSeq').hide().parent('.slimScrollDiv').hide();
				$('#arboHeadSeq').show();
				var rootPathAsset = $(this).attr('rootPathAsset');
				$('#titleSeq').html(rootPathAsset);
				$('.arboAssets[rootPathAsset="'+rootPathAsset+'"]').show().parent('.slimScrollDiv').show();
				$('.arboAssets[rootPathAsset="'+rootPathAsset+'"]').slimScroll({
					position: 'right',
					height: maxArboHeight+'px',
					size: '10px',
					wheelStep: 10,
					railVisible: true
				});
				closeArbo();
				$('.assetFolder[level="0"]:contains('+rootPathAsset+')').click();
			}
			else if ($(this).attr('nameAsset')) {
				closeArbo();
				var found = false;
				var nameAsset = $(this).attr('nameAsset');
				$('.assetItem[filename="'+nameAsset+'"]').click();
				$('.assetFolderContent').has('.assetItem[filename="'+nameAsset+'"]').each(function(){
					$(this).prev('.assetFolder').click();
					found = true;
				});
				if (!found) {
					$('.assetItemTag[filename="'+nameAsset+'"]').click();

					$('.assetCategList').hide();
					$('.assetItemCateg[filename="'+nameAsset+'"]').click().parent('.assetCategList').show(150);
				}
			}
			else if ($(this).attr('prodCat')) {
				var table = $(this).attr('prodCat');
				var params = {projectID:idProj, table:table};
				$('.pageContent').load('pages/fixedDepts/03_prod.php', params);
			}
		});

		$('#arboMenu').off('click', '#arboHeadProj');
		$('#arboMenu').on('click', '#arboHeadProj', function() {
			delete window.shot_ID;
			delete window.seq_ID;
			localStorage.removeItem('openShot_'+project_ID);
			var fromSection = $('#selectDeptsList').val();
			var fromDept = '<?php echo $dept; ?>';
			$('#'+fromSection+'_depts').find('.deptBtn[label="'+fromDept+'"]').click();
		});

		$('#arboMenu').off('click', '#arboHeadSeq');
		$('#arboMenu').on('click', '#arboHeadSeq', function() {
			$('#arboHeadSeq').hide();
			$('.arboShots, .arboAssets, .arboScenes').hide().parent('.slimScrollDiv').hide();
			$('#arboHeadProj').show();
			$('#arboSeq').show().parent('.slimScrollDiv').show();
		});


		<?php
			// Si une séquence est spécifiée on prépare son ouverture
			if (isset($_POST['sequenceID'])) {
				echo '$(\'.arboItem[idSeq="'.$_POST['sequenceID'].'"]\').click();';
			}
			// Si un shot est spécifié on le surligne
			if (isset($_POST['shotID'])) {
				echo '$(\'.arboItem[idShot="'.$_POST['shotID'].'"]\').addClass(\'ui-state-focusFake noBG\');';
			}
		?>

	});
</script>
