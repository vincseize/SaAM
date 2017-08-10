<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

?>

<script>
$(function(){
	$('.bouton').button();

	$('#printLocalStorage').click(function(){
		console.log(localStorage);
	});
	$('#resetLocalStorage').click(function(){
		localStorage.clear();
		alert('done');
	});

	$('#rebuildProjectsTeams').click(function(){
		AjaxJson('action=rebuildMyProjects', 'admin/admin_users_actions', retourAjaxMsg);
	});
	$('#rebuildShotsTeams').click(function(){
		AjaxJson('action=rebuildMyShots', 'admin/admin_users_actions', retourAjaxMsg);
	});
	$('#rebuildAssetsTeams').click(function(){
		AjaxJson('action=rebuildMyAssets', 'admin/admin_users_actions', retourAjaxMsg);
	});
	$('#rebuildScenesTeams').click(function(){
		AjaxJson('action=rebuildMyScenes', 'admin/admin_users_actions', retourAjaxMsg);
	});

	$('#purgeMyItems').click(function(){
		AjaxJson('action=purgeUsersMyItems&fromDebug=true', 'admin/admin_users_actions', retourAjaxMsg);
	});
});
</script>

<div class="stageContent pad5">
	<div id="retourAjax"></div>

	<div class="padV5 petit" style="position: absolute; top: 10px; right: 10px; border-left: 2px dashed #2582AF;">
		<div class="colorBtnFake gros pad5 marge15bot">DEBUG TOOLS</div>
		<button class="bouton" id="printLocalStorage">PRINT localStorage</button><br /><br />
		<button class="bouton" id="resetLocalStorage">RESET localStorage</button><br /><br />
		<br />
		<button class="bouton ui-state-highlight" id="rebuildProjectsTeams">REBUILD PROJECTS TEAMS</button><br /><br />
		<button class="bouton ui-state-highlight" id="rebuildShotsTeams">REBUILD SHOTS TEAMS</button><br /><br />
		<button class="bouton ui-state-highlight" id="rebuildAssetsTeams">REBUILD ASSETS TEAMS</button><br /><br />
		<button class="bouton ui-state-highlight" id="rebuildScenesTeams">REBUILD SCENES TEAMS</button><br /><br />
		<br />
		<button class="bouton ui-state-error" id="purgeMyItems">PURGE ALL MY_ITEMS</button><br /><br />
		<button class="bouton ui-state-error" id="purgeTempFolders">PURGE ALL MY_ITEMS</button><br /><br />
		<br />
	</div>

	<div class="margeTop10">
		<h2>Tests divers</h2>
	</div>

	<div class="margeTop10">
		<pre><?php

		try {
//			$logPath = INSTALL_PATH.FOLDER_DATA_USER.'logs/failed.log';
//			$start = time() - 60 * 60 * 24 * 10;
//			$fakeLog = '';
//			for ($i=$start; $i<time(); $i += 3600 * 5) {
//				$fakeLog .= "$i | polosson | 127.0.0.1 | Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:33.0) Gecko/20100101 Firefox/33.0\n";
//				echo date('Y/m/d', $i)."\n";
//			}
//			file_put_contents($logPath, $fakeLog);
//			echo "Fichier de fake NON log créé. (".filesize($logPath)." o.)";

            // Classe Liste : Exemples
//			$l = new Liste();
//			$l->getListe(TABLE_TASKS, '*');
//			$all_shots = $l->simplifyList();
//			print_r($all_shots);
//
//			$l = new Liste();
//			$l ->addFiltre(Shots::SHOT_LOCK,'=',0);
//			// $l ->addFiltre('lock','=','0');
//			// $l->getListe(TABLE_SHOTS, '*', 'date', 'ASC', 'ID_project', '=', '1');
//			$l->getListe(TABLE_SHOTS, '*');
//			$all_shots = $l->simplifyList();
//			print_r($all_shots);

			// Classe Infos : Exemples
//			$i = new Infos(TABLE_SHOTS);
//			$i->loadInfos(Shots::SHOT_ID_SHOT, 2);
//			$result = $i->getInfo();
//			print_r($result);


			////////////////////////////////////////////////////////////////////
		}
		catch(Exception $e) {
			echo $e->getMessage();
		}
	?>
		</pre>
	</div>
</div>
