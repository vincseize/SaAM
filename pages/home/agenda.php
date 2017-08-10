<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

	$userProjects = $_SESSION['user']->getUserProjects();
	$projList = array();
	try {
		if(is_array($userProjects)){
			$ixp = 0;
			foreach ($userProjects as $userProj) {
				try { $p = new Projects($userProj); }
				catch(Exception $e) { continue; }
				if (!$p->isVisible()) continue;
				$projList[$ixp]['id'] = $userProj;
				$projList[$ixp]['title'] = $p->getProjectInfos('title');
				$ixp++;
			}
		}
	}
	catch (Exception $e) { echo $e->getMessage(); }
?>

<link rel='stylesheet' type='text/css' href='css/jquery.weekcalendar.css' />
<script>
	$(function(){
		$('.bouton').button();
	});
</script>
<script src="js/jquery.weekcalendar.js"></script>
<script src="ajax/home_agenda.js"></script>


<div id="retourAjax" class="ui-state-highlight ui-corner-all"></div>

<div class="stageContent padH5">
	<div id="calendar"></div>

	<div class="hide" id="modal_eventEdit">
		<form>
			<div class="margeTop10 marge30l">
				<span class="inline mid date_holder"></span>
				<div class="inline mid">de </div>
				<div class="inline mid mini">
					<select name="start" class="w100 noBorder ui-corner-top fondSect3"></select>
				</div>

				<div class="inline mid">à </div>
				<div class="inline mid mini">
					<select name="end" class="w100 noBorder ui-corner-top fondSect3"></select>
				</div>
			</div>

			<div class="margeTop10">
				<div class="inline top w100 marge30l">Projet : </div>
				<select name="project" title="Projet" class="noBorder pad3 ui-corner-top w300 fondSect3" id="projSelect">
					<?php
						foreach($projList as $uProj) {
							echo '<option value="'.$uProj['id'].'">'.$uProj['title'].'</option>';
						}
					?>
	<!--				<option value="1">Demo</option>
					<option value="11">Project 1</option>
					<option value="12">Project 2</option>-->
				</select>
			</div>
			<div class="margeTop1">
				<div class="inline top w100 marge30l">Titre : </div>
				<input name="title" type="text" class="noBorder pad3 ui-corner-top w300 fondSect3" title="Titre" />
			</div>
			<div class="margeTop1">
				<div class="inline top w100 marge30l">Détail : </div>
				<textarea name="body" class="noBorder pad3 ui-corner-bottom w300 fondSect3 addProjDetail" rows="10"></textarea>
			</div>
		</form>
	</div>
</div>