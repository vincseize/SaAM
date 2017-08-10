<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

	require_once ('theme_fcts.php');

	if (!$_SESSION['user']->isSupervisor()) die();		// secu status


try {
	$saamInfo = new Infos(TABLE_CONFIG);
	$saamInfo->loadInfos('version', SAAM_VERSION);

	$acl = new ACL(@$_SESSION['user']);
	$ACLcheck = $acl->check('ADMIN_UI');

	// Récupère la liste des langues disponibles
	$langsList = Liste::getRows(TABLE_LANGS);
	array_splice($langsList, 0, 2); // suppr les champs 'id' et 'constante' de l'array

	// Récupère la liste des thèmes disponibles
	$themesList = list_themes();

	// Affiche les status en chaine
	$default_status = '';
	foreach(json_decode($saamInfo->getInfo('default_status')) as $status) {
		$default_status .= $status.', ';
	}
	$default_status = preg_replace('/, $/', '', $default_status);
}
catch (Exception $e) {
	die('<div class="inline ui-state-error ui-corner-all pad3 gras margeTop10">WARNING : version problem ! Config not found or ACL check failed (group "ADMIN_UI").</div>');
}
?>
<script>
	var config = {
		'default_lang'		: '<?php echo $saamInfo->getInfo('default_lang'); ?>',
		'default_theme'		: '<?php echo $saamInfo->getInfo('default_theme'); ?>',
		'url_intranet'		: '<?php echo $saamInfo->getInfo('url_intranet'); ?>',
		'home_max_news'		:  <?php echo (int)$saamInfo->getInfo('home_max_news'); ?>,
		'date_format'		: '<?php echo $saamInfo->getInfo('date_format'); ?>',
		'default_status'	: '<?php echo $default_status; ?>',
		'dailies_max_weeks'	: <?php echo (int)$saamInfo->getInfo('dailies_max_weeks'); ?>,
		'alert_uploads'		: <?php echo ((bool)$saamInfo->getInfo('alert_uploads')) ? 'true' : 'false'; ?>,
		'alert_retakes'		: <?php echo ((bool)$saamInfo->getInfo('alert_retakes')) ? 'true' : 'false'; ?>,
		'alert_messages'	: <?php echo ((bool)$saamInfo->getInfo('alert_messages')) ? 'true' : 'false'; ?>,
		'alert_tasks'		: <?php echo ((bool)$saamInfo->getInfo('alert_tasks')) ? 'true' : 'false'; ?>
	};

$(function(){
	$('.bouton').button();
	$('#alert_uploads').buttonset();
	$('#alert_retakes').buttonset();
	$('#alert_messages').buttonset();
	$('#alert_tasks').buttonset();
	$('#default_lang').selectmenu({style: 'dropdown'});
	$('#default_theme').selectmenu({style: 'dropdown'});


	// Sauvegarde de la config
	$('#configUI_save').click(function(){
		config['default_lang']		= $('#default_lang').val();
		config['default_theme']		= $('#default_theme').val();
		config['url_intranet']		= $('#url_intranet').val();
		config['home_max_news']		= $('#home_max_news').val();
		config['dailies_max_weeks']	= $('#dailies_max_weeks').val();
		config['date_format']		= $('#date_format').val();
		config['alert_uploads']		= $('#alert_uploads :radio:checked').val();
		config['alert_retakes']		= $('#alert_retakes :radio:checked').val();
		config['alert_messages']	= $('#alert_messages :radio:checked').val();
		config['alert_tasks']		= $('#alert_tasks :radio:checked').val();
		var statusSTR = $('#default_status').val();
		config['default_status']		= statusSTR.replace(/ /g, '').split(',');

		var configOK = encodeURIComponent(JSON.encode(config));
		var ajaxReq = 'action=updateConfig&newConfig='+configOK;
		AjaxJson(ajaxReq, 'admin/admin_config_actions', retourAjaxConfig);
	});

	// Restauration de la config
	$('#configUI_restore').click(function(){
		$('.deptBtn[content="interface"]').click();
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

	<h2>INTERFACE CONFIGURATION</h2>

	<div>
		<div class="inline mid w200 marge30l gros gras colorBtnFake">Default language :</div>
		<div class="inline mid pad3">
			<?php if ($ACLcheck): ?>
				<select class="w300" id="default_lang"><?php
					foreach($langsList as $lang) {
						$selected = ($lang==$saamInfo->getInfo('default_lang')) ? 'selected="selected"' : '' ;
						echo '<option value="'.$lang.'" '.$selected.'>'.strtoupper($lang).'</option>';
					}?>
				</select>
			<?php else:
				echo strtoupper($saamInfo->getInfo('default_lang'));
			endif; ?>
		</div>
	</div>
	<div class="">
		<div class="inline mid w200 marge30l gros gras colorBtnFake">Default theme : </div>
		<div class="inline mid pad3">
			<?php if ($ACLcheck): ?>
			<select class="w300" id="default_theme"><?php
				foreach($themesList as $theme) {
					$selected = ($theme == $saamInfo->getInfo('default_theme')) ? 'selected="selected"' : '' ;
					echo '<option value="'.$theme.'" '.$selected.'>'.strtoupper($theme).'</option>';
				}?>
			</select>
			<?php else:
				echo strtoupper($saamInfo->getInfo('default_theme'));
			endif; ?>
		</div>
	</div>

	<div class="">
		<div class="inline mid w200 marge30l gros gras colorBtnFake">Your own website : </div>
		<div class="inline mid pad3">
			<?php if ($ACLcheck): ?>
				<input type="text" class="noBorder pad3 w300 fondSect3 ui-corner-all" value="<?php echo $saamInfo->getInfo('url_intranet'); ?>" id="url_intranet" />
			<?php else:
				echo $saamInfo->getInfo('url_intranet');
			endif; ?>
		</div>
	</div>

	<div class="">
		<div class="inline mid w200 marge30l gros gras colorBtnFake">Max. homepage news : </div>
		<div class="inline mid pad3">
			<?php if ($ACLcheck): ?>
				<input type="text" class="noBorder pad3 w300 fondSect3 ui-corner-all" value="<?php echo $saamInfo->getInfo('home_max_news'); ?>" id="home_max_news" />
			<?php else:
				echo $saamInfo->getInfo('home_max_news');
			endif; ?>
		</div>
	</div>

	<div class="">
		<div class="inline mid w200 marge30l gros gras colorBtnFake">Max. Dailies weeks: </div>
		<div class="inline mid pad3">
			<?php if ($ACLcheck): ?>
				<input type="text" class="noBorder pad3 w300 fondSect3 ui-corner-all" value="<?php echo $saamInfo->getInfo('dailies_max_weeks'); ?>" id="dailies_max_weeks" />
			<?php else:
				echo $saamInfo->getInfo('dailies_max_weeks');
			endif; ?>
		</div>
	</div>

	<div class="">
		<div class="inline mid w200 marge30l gros gras colorBtnFake">Default date format: </div>
		<div class="inline mid pad3">
			<?php if ($ACLcheck): ?>
				<input type="text" class="noBorder pad3 w300 fondSect3 ui-corner-all" value="<?php echo $saamInfo->getInfo('date_format'); ?>" id="date_format" />
			<?php else:
				echo $saamInfo->getInfo('date_format');
			endif; ?>
		</div>
	</div>

	<div class="">
		<div class="inline mid w200 marge30l gros gras colorBtnFake">Available status: </div>
		<div class="inline mid pad3">
			<?php if ($ACLcheck): ?>
				<input type="text" class="noBorder pad3 w300 fondSect3 ui-corner-all" value="<?php echo $default_status; ?>" id="default_status" />
			<?php else:
				echo $default_status;
			endif; ?>
		</div>
	</div>

	<div class="margeTop10">
		<div class="inline mid w200 marge30l gros gras colorBtnFake">Send alert for uploads : </div>
		<div class="inline mid w300 mini pad3" id="alert_uploads">
			<?php if ($ACLcheck):
				$selectYes = ((bool)$saamInfo->getInfo('alert_uploads')) ? 'checked="checked"' : '';
				$selectNo  = (!(bool)$saamInfo->getInfo('alert_uploads')) ? 'checked="checked"' : ''; ?>
				<input type="radio" value="1" name="alert_uploads" id="alertUpYes" <?php echo @$selectYes; ?> /><label class="big" for="alertUpYes"><?php echo L_BTN_YES; ?></label>
				<input type="radio" value="0" name="alert_uploads" id="alertUpNo"  <?php echo @$selectNo; ?>  /><label class="big" for="alertUpNo"><?php echo L_BTN_NO; ?></label>
			<?php else:
				echo ((bool)$saamInfo->getInfo('alert_uploads')) ? L_BTN_YES : L_BTN_NO;
			endif; ?>
		</div>
	</div>

	<div class="">
		<div class="inline mid w200 marge30l gros gras colorBtnFake">Send alert for published : </div>
		<div class="inline mid w300 mini pad3" id="alert_retakes">
			<?php if ($ACLcheck):
				$selectYes = ((bool)$saamInfo->getInfo('alert_retakes')) ? 'checked="checked"' : '';
				$selectNo  = (!(bool)$saamInfo->getInfo('alert_retakes')) ? 'checked="checked"' : ''; ?>
				<input type="radio" value="1" name="alert_retakes" id="alertRetYes" <?php echo @$selectYes; ?> /><label class="big" for="alertRetYes"><?php echo L_BTN_YES; ?></label>
				<input type="radio" value="0" name="alert_retakes" id="alertRetNo"  <?php echo @$selectNo; ?>  /><label class="big" for="alertRetNo"><?php echo L_BTN_NO; ?></label>
			<?php else:
				echo ((bool)$saamInfo->getInfo('alert_retakes')) ? L_BTN_YES : L_BTN_NO;
			endif; ?>
		</div>
	</div>

	<div class="">
		<div class="inline mid w200 marge30l gros gras colorBtnFake">Send alert for messages : </div>
		<div class="inline mid w300 mini pad3" id="alert_messages">
			<?php if ($ACLcheck):
				$selectYes = ((bool)$saamInfo->getInfo('alert_messages')) ? 'checked="checked"' : '';
				$selectNo  = (!(bool)$saamInfo->getInfo('alert_messages')) ? 'checked="checked"' : ''; ?>
				<input type="radio" value="1" name="alert_messages" id="alertMessYes" <?php echo @$selectYes; ?> /><label class="big" for="alertMessYes"><?php echo L_BTN_YES; ?></label>
				<input type="radio" value="0" name="alert_messages" id="alertMessNo"  <?php echo @$selectNo; ?>  /><label class="big" for="alertMessNo"><?php echo L_BTN_NO; ?></label>
			<?php else:
				echo ((bool)$saamInfo->getInfo('alert_messages')) ? L_BTN_YES : L_BTN_NO;
			endif; ?>
		</div>
	</div>

	<div class="">
		<div class="inline mid w200 marge30l gros gras colorBtnFake">Send alert for tasks : </div>
		<div class="inline mid w300 mini pad3" id="alert_tasks">
			<?php if ($ACLcheck):
				$selectYes = ((bool)$saamInfo->getInfo('alert_tasks')) ? 'checked="checked"' : '';
				$selectNo  = (!(bool)$saamInfo->getInfo('alert_tasks')) ? 'checked="checked"' : ''; ?>
				<input type="radio" value="1" name="alert_tasks" id="alertTaskYes" <?php echo @$selectYes; ?> /><label class="big" for="alertTaskYes"><?php echo L_BTN_YES; ?></label>
				<input type="radio" value="0" name="alert_tasks" id="alertTaskNo"  <?php echo @$selectNo; ?>  /><label class="big" for="alertTaskNo"><?php echo L_BTN_NO; ?></label>
			<?php else:
				echo ((bool)$saamInfo->getInfo('alert_tasks')) ? L_BTN_YES : L_BTN_NO;
			endif; ?>
		</div>
	</div>


	<div class="inline w500 margeTop10 marge30l rightText">
		<?php if ($ACLcheck): ?>
			<button class="bouton" id="configUI_save"><?php echo strtoupper(L_BTN_SAVE); ?></button>
			<button class="bouton" id="configUI_restore"><?php echo L_RESTORE; ?></button>
		<?php else: ?>
			<span class='ui-state-error pad5 ui-corner-all'>Access restricted, no modification allowed.</span><?php
		endif; ?>
	</div>

</div>