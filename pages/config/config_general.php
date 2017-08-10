<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

	if (!$_SESSION['user']->isDev()) die();		// secu status

try {
	$saamInfo = new Infos(TABLE_CONFIG);
	$saamInfo->loadInfos('version', SAAM_VERSION);

	$strings_config = Array(
		'default_seqLabel'	=> Array("Sequences nomenclatura",	$saamInfo->getInfo('default_seqLabel')),
		'default_shotLabel'	=> Array("Shots nomenclatura",		$saamInfo->getInfo('default_shotLabel')),
		'default_scenesLabel'=> Array("Master scenes nomenclatura",	$saamInfo->getInfo('default_scenesLabel')),
		'default_fps'		=> Array("Default framerate",		$saamInfo->getInfo('default_fps')),
		'sep',
		'projects_size'		=> Array("Projects max size (GB)",	$saamInfo->getInfo('projects_size')),
		'date_format'		=> Array("Date format to use",		$saamInfo->getInfo('date_format')),
		'calendar_file'		=> Array("Filepath for calendar",	$saamInfo->getInfo('calendar_file')),
		'sep',
		'fps_list'			=> Array("Framerates available",	$saamInfo->getInfo('fps_list')),
		'ratio_list'		=> Array("Image-ratios available",	$saamInfo->getInfo('ratio_list'))
	);

	$arrays_config = Array(
		'default_depts'					=> Array("Default departments for project creation",	json_decode($saamInfo->getInfo('default_depts'))),
		'default_project_types'			=> Array("Available project's types",					json_decode($saamInfo->getInfo('default_project_types'))),
		'default_status'				=> Array("Available status",							json_decode($saamInfo->getInfo('default_status'), true)),
		'sep',
		'assets_categories'				=> Array("Available assets categories",					json_decode($saamInfo->getInfo('assets_categories'), true)),
		'default_assets_dirs'			=> Array("Default assets directories",					json_decode($saamInfo->getInfo('default_assets_dirs'))),
		'default_assets_exclude_dirs'	=> Array("Exclude list for directories in assets tree",	json_decode($saamInfo->getInfo('default_assets_exclude_dirs'))),
		'sep',
		'default_data_folders'			=> Array("Default data folders for departments",		json_decode($saamInfo->getInfo('default_data_folders'))),
		'available_softs'				=> Array("Available softwares",							json_decode($saamInfo->getInfo('available_softs'))),
		'available_competences'			=> Array("Available competences",						json_decode($saamInfo->getInfo('available_competences')))
	);

	$dectechInfo = json_decode($saamInfo->getInfo('dectech_infos'), true);
}
catch (Exception $e) { die('<div class="inline ui-state-error ui-corner-all pad3">WARNING : version problem ! Config not found.</div>'); }
?>

<script>
$(function(){
	$('.bouton').button();

	// Sauvegarde de la config
	$('#config_save').click(function(){
		var config		= {};
		$('.stringConfigValues').each(function(idx,elem){
			var champ = $(elem).attr('id');
			config[champ] = $(elem).val();
		});
		$('.arrayConfigValues').each(function(idx,elem){
			var champ = $(elem).attr('id');
			var values = $(elem).val();
			var vals = values.split(', ');
			if (champ == "assets_categories") {
				var valsOK = {};
				$.each(vals, function(i,val){ valsOK[(i+1).toString()] = val; });
			}
			else
				var valsOK = vals;
			config[champ] = valsOK;
		});
		config['dectech_infos'] = {};
		$('.dectechCategValues').each(function(idx,elem){
			var categ = $(elem).attr('id');
			var values = $(elem).val();
			var vals = values.split(', ');
			var valsOK = {};
			$.each(vals, function(i,v){ valsOK[(i+1).toString()] = [v, ''] });
			config['dectech_infos'][categ] = valsOK;
		});
		var config = encodeURIComponent(JSON.encode(config));
		var ajaxReq = 'action=updateConfig&newConfig='+config;
		AjaxJson(ajaxReq, 'admin/admin_config_actions', retourAjaxConfig);
	});

	// Restauration de la config
	$('#config_restore').click(function(){
		$('.deptBtn[content="config_general"]').click();
	});

	// Vidage des dossiers TEMP
	$('#purgeTempFolders').click(function(){
		var ajaxReq = 'action=purgeTempFolders';
		AjaxJson(ajaxReq, 'admin/admin_config_actions', retourAjaxConfig);
	});

	// Vidage du dossier BF_logs
	$('#purgeBFlogs').click(function(){
		var ajaxReq = 'action=purgeBFlogs';
		AjaxJson(ajaxReq, 'admin/admin_config_actions', retourAjaxConfig);
	});

});

function retourAjaxConfig (retour) {
	if (retour.error == 'OK') {
		$('#retourAjax').html(retour.message).removeClass('ui-state-error').addClass('ui-state-highlight').show(transition);
		$('.deptBtn[content="config_general"]').click();
		setTimeout(function() {$('#retourAjax').fadeOut(transition);}, 1000);
	}
	else {
		$('#retourAjax').html(retour.error+' : '+retour.message).addClass('ui-state-error').removeClass('ui-state-highlight').show(transition);
		setTimeout(function() {$('#retourAjax').fadeOut(transition);}, 3000);
	}
}

</script>


<div class="stageContent pad5">

	<h2>SaAM CONFIGURATION</h2>

	<div class="inline top margeTop10 marge10l marge15bot">
	<?php foreach ($strings_config as $col => $entry):
			 if ($entry == 'sep'): ?>
				<div class="margeTop10 marge15bot"></div>
		<?php else: ?>
			<div class="margeTop1 margeTop1">
				<div class="marge10l gros gras colorBtnFake"><?php echo $entry[0]; ?>:</div>
				<div class="marge10l">
					<input type="text" class="marge10l margeTop5 noBorder pad3 fondSect3 w200 ui-corner-all stringConfigValues" value="<?php echo $entry[1]; ?>" id="<?php echo $col; ?>" />
				</div>
			</div>
		<?php endif;
		endforeach; ?>
	</div>


	<div class="inline top marge10l margeTop10 marge15bot bordBankAsset">
	<?php foreach ($arrays_config as $col => $entry):
			 if ($entry == 'sep'): ?>
				<div class="margeTop10 marge15bot"></div>
		<?php else:
				$strArr = @implode(', ', $entry[1]) ?>
				<div class="margeTop1 marge10l">
					<div class="marge10l w300 gros gras colorBtnFake"><?php echo $entry[0]; ?>:</div>
					<div class="marge10l">
						<input type="text" class="marge10l margeTop5 w600 noBorder pad3 fondSect3 ui-corner-all arrayConfigValues" value="<?php echo $strArr; ?>" id="<?php echo $col; ?>" />
					</div>
				</div>
		<?php endif;
		endforeach; ?>
	</div>

	<div></div>

	<div class="inline mid marge10l margeTop10 marge15bot">
		<div class="marge10l w300 gros gras colorBtnFake"><?php echo L_DECTECH_LONG; ?> structure</div>
		<?php foreach ($dectechInfo as $categ => $infos):
				$dectinfs = '';
				foreach ($infos as $dectinf) $dectinfs .= $dectinf[0].', '; ?>
				<div class=" marge10l margeTop5">
					<div class="inline midgros gros gras colorPage w100"><?php echo $categ; ?></div>
					<input type="text" class="marge10l w400 noBorder pad3 fondSect3 ui-corner-all dectechCategValues" value="<?php echo substr($dectinfs,0, -2); ?>" id="<?php echo $categ; ?>" />
				</div>
		<?php endforeach; ?>
		<div class="margeTop1 marge15bot"></div>
	</div>

	<div class="inline mid marge10l margeTop10 marge15bot w300 rightText">
		<div class="margeTop5 marge10l">
			<button class="bouton" id="config_save"><?php echo strtoupper(L_BTN_SAVE); ?></button>
			<button class="bouton" id="config_restore"><?php echo L_RESTORE; ?></button>
		</div>
	</div>

	<div class="marge10l marge15bot">
		<div class="marge10l gros gras colorBtnFake">Admin operations</div>
		<div class="inline mid marge10l margeTop5">
			<button class="bouton" id="purgeTempFolders" title="Empty all the temporary subfolders">Purge temp folders</button>
		</div>
		<div class="inline mid marge10l margeTop5">
			<button class="bouton" id="purgeBFlogs" title="Empty the 'brute force logs' folder">Purge BFlogs</button>
		</div>
		<div class="inline mid marge10l margeTop5 giant ui-state-disabled"> |
		</div>
		<div class="inline mid marge10l margeTop5">
			<button class="bouton ui-state-error" id="resetSessions" title="This will kick everybody currently logged in">Reset all sessions</button>
		</div>
		<div class="inline mid marge10l margeTop5 giant ui-state-disabled"> |
		</div>
		<div class="inline mid marge10l margeTop5">
			<button class="bouton" id="resetCalendar" title="Empty and re-format the calendar's json file">Reset calendar file</button>
		</div>
	</div>
</div>