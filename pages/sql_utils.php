<?php
	@session_start();
	require_once ("../inc/checkConnect.php" );

    if ( !isset($_SESSION["user"]) || !$_SESSION['user']->isDev() ) die('SQL Utils : ACCESS DENIED !');

	require_once('sql_utils_fct.php');
?>

<script type="text/javascript" src="./ajax/sql_utils_ajax.js"></script>

<div class="pageContent">

	<div class="stageContent pad5">
		<h3 class="floatR colorBtnFake">Database name: "<?php echo BASE; ?>"</h3>
		<h2><?php echo L_BTN_SQL; ?></h2>
		<div class="">
			<br />
			<div class="inline top marge30r">
				<div class="center padV10" id="saveBDD">
					<div class="pad5">Tables in database</div><br />
					<select id="tableList" multiple="multiple" size="28" class="moyen pad5 fondSect4 noBorder" style="min-width:250px;">
						<option value="all" selected><?php echo mb_convert_case(L_EVERYTHING, MB_CASE_UPPER); ?></option>
						<?php foreach (getTableList() as $name) {echo '<option value="'.$name.'">'.$name.'</option>';} ?>
					</select>
					<br />
					<button class="bouton margeTop10" id="dumpSQL" >SAVE SELECTION</button>
				</div>
			</div>

			<div class="inline top">
				<div class="center padV10" id="restoreBDD">
					<div class="pad5">SQL file to restore</div><br />
					<select id="dumpList" size="28" class="moyen pad5 fondSect4 noBorder" style="min-width:250px;">
						<option disabled selected>----</option>
						<?php foreach (getDumpList() as $name) {echo '<option value="'.$name.'">'.$name.'</option>';} ?>
					</select>
					<br />
					<button class="bouton margeTop10" id="restoreSQL" >RESTORE SQL FILE TO DB</button>
					<button class="bouton margeTop5" id="downloadSQL" >DOWNLOAD SQL FILE</button>
				</div>
			</div>

			<div class="ui-state-error ui-corner-all margeTop10 pad10 hide" id="retourAjaxSQL"></div>

		</div>
	</div>
</div>