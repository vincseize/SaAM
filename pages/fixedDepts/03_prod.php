<?php
@session_start();
require_once ($_SESSION['INSTALL_PATH_INC'].'/checkConnect.php' );
require_once('dates.php');

// OBLIGATOIRE, id du projet à charger
if (isset($_POST['projectID']))
	$idProj = $_POST['projectID'];
else die('Pas de projet à charger...');

// OBLIGATOIRE, table à charger
if (isset($_POST['table']))
	$table = $_POST['table'];
else {
	if (!isset($_SESSION['lastVisitedTable'][$idProj]))
		$table = 'saam_sequences';
	else $table = $_SESSION['lastVisitedTable'][$idProj];
}
$_SESSION['lastVisitedTable'][$idProj] = $table;

if (!isset($_SESSION['prodHideArchived'][$idProj]))
	$_SESSION['prodHideArchived'][$idProj] = true;
?>

<script>
	var project_ID	= <?php echo $idProj; ?>;

	$(function(){

		$('.bouton').button();
		$('#prod_searchTable_row').selectmenu({width:85});
		$('#prod_searchTable_row-button').removeClass('ui-state-default').addClass('ui-state-highlight').attr('title', 'Filter by');
		$('#prod_searchTable_row-menu').css('font-size', '0.72em');

		$('#pageSizeSel').selectmenu({width:45});
		$('#pageSizeSel-button').removeClass('ui-state-default').addClass('ui-state-highlight').attr('title', 'Number of lines to display');
		$('#pageSizeSel-menu').css('font-size', '0.72em');

		$('#arboMenu').load('modals/menuArbo.php', {projectID: project_ID, dept: 'prod', deptID: '0', template: '<?php echo $table; ?>', typeArbo: 'prod'}).show();

	});

</script>
<script src="js/blueimp_uploader/jquery.iframe-transport.js"></script>
<script src="js/blueimp_uploader/jquery.fileupload.js"></script>
<script src="js/blueimp_uploader/jquery.fileupload-fp.js"></script>

<div class="fondProd noMarge" id="stageProd">
	<?php include('prod_specific/show_prod_table.php') ?>
</div>