<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	require_once ('vignettes_fcts.php');
	require_once ('theme_fcts.php');


	// Récupère les infos de l'utilisateur
	$infosUser = $_SESSION['user']->getUserInfos();
	Users::purge_user_projects((int)$infosUser['id']);
	Users::purge_user_scenes((int)$infosUser['id']);
	Users::purge_user_assets((int)$infosUser['id']);
	Users::purge_user_shots((int)$infosUser['id']);

	$vignetteUser = check_user_vignette_ext($infosUser['id'], $infosUser['login']);
	$compUser  = $_SESSION['user']->getCompetences();
	$projectsUser = json_decode($infosUser['my_projects']);
	$userShots  = $_SESSION['user']->getUserShots();
	$userScenes = $_SESSION['user']->getUserScenes();
	$userAssets = $_SESSION['user']->getUserAssets();
	$userPpath	= $_SESSION['user']->getUserProjectPath();
	$userPuri	= $_SESSION['user']->getUserProjectUrl();

	// Récupère la liste des compétences disponibles
	try {
		$inf = new Infos(TABLE_CONFIG);
		$inf->loadInfos('version', SAAM_VERSION);
		$avComp = json_decode($inf->getInfo('available_competences'));
	}
	catch (Exception $e) { $avComp = $_SESSION['CONFIG']['AV_COMPETENCES']; }

	// Récupère la liste des langues disponibles
	$langsList = Liste::getRows(TABLE_LANGS);
	array_splice($langsList, 0, 2); // suppr les champs 'id' et 'constante' de l'array

	// Récupère la liste des thèmes disponibles
	$themesList = list_themes();

	$disabledBtns = ($_SESSION['user']->isDemo()) ? 'ui-state-disabled' : '';
?>

<script src="js/blueimp_uploader/jquery.iframe-transport.js"></script>
<script src="js/blueimp_uploader/jquery.fileupload.js"></script>
<script src="js/blueimp_uploader/jquery.fileupload-fp.js"></script>

<script>
	var lastModifUndo = '';

	$(function(){
		$('.bouton').button();

		$('#mailReceive, #notifsReceive').buttonset();


		// init select multiple compétences
		$('#user_skills').multiselect({
			noneSelectedText: 'Aucune', selectedText: '# compétence(s)', selectedList: 3, checkAllText: ' ', uncheckAllText: ' ',
			create: function(e, ui) {
				lastModifUndo = $('#user_skills').val();
			},
			open: function(e, ui){
				hideAllBtns();
				lastModifUndo = $('#user_skills').val();
				$('.validSelect').parent('div').show();
			},
			close: function(e, ui) {
				if (lastModifUndo == $('#user_skills').val())
					$('.validSelect').parent('div').hide();
			}
		});
		// init select langue
		$('#user_lang').selectmenu({
			style: 'dropdown',
			open: function() {
				hideAllBtns();
				lastModifUndo = $('#user_lang option:selected').index();
				$('.validLang').parent('div').show();
			},
			change: function(e, ui) {
		//			$('.validLang').parent('div').show();
			},
			close: function(e, ui) {
				if (lastModifUndo == $('#user_lang option:selected').index())
					$('.validLang').parent('div').hide();
			}
		});
		// init select theme
		$('#user_theme').selectmenu({
			style: 'dropdown',
			open: function() {
				hideAllBtns();
				lastModifUndo = $('#user_theme option:selected').index();
				$('.validTheme').parent('div').show();
			},
			change: function(e, ui) {
		//			$('.validTheme').parent('div').show();
			},
			close: function(e, ui) {
				if (lastModifUndo == $('#user_theme option:selected').index())
					$('.validTheme').parent('div').hide();
			}
		});

		// Affiche les boutons de validation / annulation quand focus sur input
		$('.userMod').focus(function(){
			hideAllBtns();
			lastModifUndo = $(this).val();
			$(this).next('div').show();
		});
		// Pareil, pour receive mails
		$('#mailReceive').click(function(){
			hideAllBtns();
			lastModifUndo = $('.userModRM[checked]').attr('id');
			$(this).next().show();
		});
		// Pareil, pour receive notifications
		$('#notifsReceive').click(function(){
			hideAllBtns();
			lastModifUndo = $('.userModRN[checked]').attr('id');
			$(this).next().show();
		});



		// Empêche les drop de fichier dans le browser
		$(document).bind('drop dragover', function (e) {
			e.preventDefault();
		});
		<?php if (!$_SESSION['user']->isDemo()): ?>
			// Init de l'uploader
			$('#vignette_upload').fileupload({
				url: "actions/upload_vignettes.php?type=avatarUser",
				dataType: 'json',
				dropZone: $('#user_avatar'),
				change: function (e, data) {
					$('#vignette_upload_msg').html(data.files[0].name + '... <img src="gfx/ajax-loader-white.gif" />');
				},
				done: function (e, data) {
					var retour = data.result;
					if (retour[0].error) {
						$('#vignette_upload_msg').append('<span class="colorErreur gras">Failed : '+retour[0].error+'</span>').show().find('img').remove();
						lastUploadedVignette = '';
					}
					else {
						var d = new Date();
						$('#vignette_upload_img').attr("src", decodeURI(retour[0].url)+"?"+d.getTime()).parent('div').addClass('panavignette');
						$('#vignette_upload_msg').append('<span class="colorOk gras">OK</span>').hide(transition).find('img').remove();
						lastUploadedVignette = decodeURI(retour[0].name);
						localStorage.setItem('addProj_VignetteUserTempName', decodeURI(retour[0].name));
						$('#vignette_upload_msg').next('div').show();
					}
				}
			});

			// clic sur bouton "Envoyer une vignette"
			$('#vignette_upload_Btn').click(function(){
				$('#vignette_upload').click();
			});
		<?php endif; ?>
	});

	// cacher tous les boutons de valid / annul
	function hideAllBtns () {
		$('.userMod').next('div').hide();
		$('.validLang').parent('div').hide();
		$('.validTheme').parent('div').hide();
		$('.validSelect').parent('div').hide();
		$('.validAvatar').parent('div').hide();
		$('#mailReceive').next('div').hide();
		$('#notifsReceive').next('div').hide();
	}
</script>

<?php if (!$_SESSION['user']->isDemo()): ?>
	<script src="ajax/prefs_infos.js"></script>
<?php endif; ?>

<div class="inline bot big gras pad5">Préférences de <?php echo $infosUser['pseudo']; ?></div>
<div class="inline bot petit pad5 colorMid"><i>inscrit le <?php echo date(DATE_FORMAT, $infosUser['date_inscription']); ?></i></div>
<br />
<br />
<div class="inline top gros w600 marge15bot" userID="<?php echo $infosUser['id']; ?>">
	<div class="marge15bot">
		<div style="position:absolute; margin:14px 0px 0px 32px;" class="nano">
			<button class="bouton" title="Changer l'avatar" id="vignette_upload_Btn"><span class="ui-icon ui-icon-pencil"></span></button>
		</div>
		<div class="inline mid w150 marge30l panavignette" id="user_avatar"><img src="<?php echo $vignetteUser.'?_'.time(); ?>" width="150" id="vignette_upload_img" /></div>
		<input class="hide" type="file" name="files[]" id="vignette_upload" />
		<div class="inline top mini" style="width: 380px;" id="vignette_upload_msg">

		</div>
		<div class="inline top nano marge30l <?php echo $disabledBtns; ?>" style="display:none;">
			<button class="bouton validAvatar" title="valider" what="login"><span class="ui-icon ui-icon-check"></span></button>
			<button class="bouton ui-state-error annulAvatar" title="annuler"><span class="ui-icon ui-icon-close"></span></button>
		</div>
	</div>
	<div class="margeTop1">
		<div class="inline top w150 marge30l " title="ATTENTION ! souvenez-vous de votre modification !"><span class="floatR ui-icon ui-icon-alert"></span>Login : </div>
		<input type="text" class="noBorder pad3 ui-corner-top w300 fondSect3 userMod" title="Login" value="<?php echo $infosUser['login']; ?>" id="user_login" onkeypress="return checkChar(event,true,true,true)" />
		<div class="inline top nano <?php echo $disabledBtns; ?>" style="display:none;">
			<button class="bouton validModUser" title="valider" what="login"><span class="ui-icon ui-icon-check"></span></button>
			<button class="bouton ui-state-error annulModUser" title="annuler"><span class="ui-icon ui-icon-close"></span></button>
		</div>
	</div>
	<div class="margeTop1">
		<div class="inline top w150 marge30l" title="ATTENTION ! souvenez-vous de votre modification !"><span class="floatR ui-icon ui-icon-alert"></span>Mot de passe : </div>
		<input type="password" class="noBorder pad3 w300 fondSect3 userMod" title="Mot de Passe" onkeypress="return checkChar(event,true,true,true)" id="user_MDP" />
		<div class="inline top nano <?php echo $disabledBtns; ?>" style="display:none;">
			<button class="bouton validModUser" title="valider" what="passwd"><span class="ui-icon ui-icon-check"></span></button>
			<button class="bouton ui-state-error annulModUser" title="annuler"><span class="ui-icon ui-icon-close"></span></button>
		</div>
	</div>
	<div class="margeTop1">
		<div class="inline top w150 marge30l">Adresse e-mail : </div>
		<input type="text" class="noBorder pad3 w300 fondSect3 userMod" title="Email" value="<?php echo $infosUser['mail']; ?>" id="user_mail" />
		<div class="inline top nano <?php echo $disabledBtns; ?>" style="display:none;">
			<button class="bouton validModUser" title="valider" what="mail"><span class="ui-icon ui-icon-check"></span></button>
			<button class="bouton ui-state-error annulModUser" title="annuler"><span class="ui-icon ui-icon-close"></span></button>
		</div>
	</div>
	<div class="margeTop1">
		<div class="inline top w150 marge30l">Pseudo : </div>
		<input type="text" class="noBorder pad3 w300 fondSect3 userMod" title="Pseudo" value="<?php echo $infosUser['pseudo']; ?>" onkeypress="return checkChar(event,null,true,null)" id="user_pseudo" />
		<div class="inline top nano <?php echo $disabledBtns; ?>" style="display:none;">
			<button class="bouton validModUser" title="valider" what="pseudo"><span class="ui-icon ui-icon-check"></span></button>
			<button class="bouton ui-state-error annulModUser" title="annuler"><span class="ui-icon ui-icon-close"></span></button>
		</div>
	</div>
	<div class="margeTop1">
		<div class="inline top w150 marge30l">Prénom : </div>
		<input type="text" class="noBorder pad3 w300 fondSect3 userMod" title="Prénom" value="<?php echo $infosUser['prenom']; ?>" onkeypress="return checkChar(event,null,true,null)" id="user_prenom" />
		<div class="inline top nano <?php echo $disabledBtns; ?>" style="display:none;">
			<button class="bouton validModUser" title="valider" what="prenom"><span class="ui-icon ui-icon-check"></span></button>
			<button class="bouton ui-state-error annulModUser" title="annuler"><span class="ui-icon ui-icon-close"></span></button>
		</div>
	</div>
	<div class="margeTop1">
		<div class="inline top w150 marge30l">Nom : </div>
		<input type="text" class="noBorder pad3 w300 fondSect3 ui-corner-bottom userMod" title="Nom" value="<?php echo $infosUser['nom']; ?>" onkeypress="return checkChar(event,null,true,null)" id="user_nom" />
		<div class="inline top nano <?php echo $disabledBtns; ?>" style="display:none;">
			<button class="bouton validModUser" title="valider" what="nom"><span class="ui-icon ui-icon-check"></span></button>
			<button class="bouton ui-state-error annulModUser" title="annuler"><span class="ui-icon ui-icon-close"></span></button>
		</div>
	</div>
	<div class="margeTop10">
		<div class="inline top margeTop5 w150 marge30l">Compétences : </div>
		<div class="inline mid mini pad3">
			<select multiple="multiple" class="w300" id="user_skills">
			<?php
			foreach ($avComp as $comp) {
				(in_array($comp, $compUser)) ? $select = ' selected="selected"' : $select = '';
				echo '<option class="mini" value="'.$comp.'"'.$select.'>'.strtoupper($comp).'</option>';
			}
			?>
			</select>
		</div>
		<div class="inline top nano <?php echo $disabledBtns; ?>" style="display:none;">
			<button class="bouton validSelect" title="valider" what="competences"><span class="ui-icon ui-icon-check"></span></button>
			<button class="bouton ui-state-error annulSelect" title="annuler"><span class="ui-icon ui-icon-close"></span></button>
		</div>
	</div>
	<div class="">
		<div class="inline mid w150 marge30l">Langue : </div>
		<div class="inline mid mini pad3">
			<select class="w300" id="user_lang">
				<?php
					foreach($langsList as $lang) {
						$selected = ($lang==LANG) ? 'selected="selected"' : '' ;
						echo '<option value="'.$lang.'" '.$selected.'>'.strtoupper($lang).'</option>';
					}
				?>
			</select>
		</div>
		<div class="inline top nano <?php echo $disabledBtns; ?>" style="display:none;">
			<button class="bouton validLang" title="valider" what="lang"><span class="ui-icon ui-icon-check"></span></button>
			<button class="bouton ui-state-error annulLang" title="annuler"><span class="ui-icon ui-icon-close"></span></button>
		</div>
	</div>
	<div class="">
		<div class="inline mid w150 marge30l">Thème : </div>
		<div class="inline mid mini pad3">
			<select class="w300" id="user_theme">
				<?php
					$userTheme = $infosUser['theme'];
					if ($userTheme == '') $userTheme = THEME_DEFAULT;
					foreach($themesList as $theme) {
						$selected = ($theme == $userTheme) ? 'selected="selected"' : '' ;
						echo '<option value="'.$theme.'" '.$selected.'>'.strtoupper($theme).'</option>';
					}
				?>
			</select>
		</div>
		<div class="inline top nano <?php echo $disabledBtns; ?>" style="display:none;">
			<button class="bouton validTheme" title="valider" what="theme"><span class="ui-icon ui-icon-check"></span></button>
			<button class="bouton ui-state-error annulTheme" title="annuler"><span class="ui-icon ui-icon-close"></span></button>
		</div>
	</div>

	<div class="margeTop10 marge30l colorDark">Préférences de réception d'emails:</div>

	<div class="margeTop5">
		<div class="inline mid w150 marge30l">Recevoir Dailies: </div>
		<div class="inline mid w300 micro pad3" id="mailReceive">
			<?php
				if (!isset($infosUser['receiveMails']))
					$selectYes = 'checked="checked"';
				else
					$selectYes = (@$infosUser['receiveMails'] == '1') ? 'checked="checked"' : '';
				$selectNo  = (@$infosUser['receiveMails'] == '0') ? 'checked="checked"' : '';
			?>
			<input type="radio" value="1" name="mailReceive" class="userModRM" id="mailYes" <?php echo @$selectYes; ?> /><label class="big" for="mailYes"><?php echo L_BTN_YES; ?></label>
			<input type="radio" value="0" name="mailReceive" class="userModRM" id="mailNo"  <?php echo @$selectNo; ?>  /><label class="big" for="mailNo"><?php echo L_BTN_NO; ?></label>
			<span class="gros colorDark marge10l">(1 mail par jour, à 20h00)</span>
		</div>
		<div class="inline top nano <?php echo $disabledBtns; ?>" style="display:none;">
			<button class="bouton validMailR" title="valider" what="receiveMails"><span class="ui-icon ui-icon-check"></span></button>
			<button class="bouton ui-state-error annulMailR" title="annuler"><span class="ui-icon ui-icon-close"></span></button>
		</div>
	</div>

	<div class="margeTop1">
		<div class="inline mid w150 marge30l">Recevoir Notifications: </div>
		<div class="inline mid w300 micro pad3" id="notifsReceive">
			<?php
				if (!isset($infosUser['receiveNotifs']))
					$selectYes = 'checked="checked"';
				else
					$selectYes = (@$infosUser['receiveNotifs'] == '1') ? 'checked="checked"' : '';
				$selectNo  = (@$infosUser['receiveNotifs'] == '0') ? 'checked="checked"' : '';
			?>
			<input type="radio" value="1" name="notifsReceive" class="userModRN" id="notifsYes" <?php echo @$selectYes; ?> /><label class="big" for="notifsYes"><?php echo L_BTN_YES; ?></label>
			<input type="radio" value="0" name="notifsReceive" class="userModRN" id="notifsNo"  <?php echo @$selectNo; ?>  /><label class="big" for="notifsNo"><?php echo L_BTN_NO; ?></label>
			<span class="gros colorDark marge10l">(1 mail par évènement)</span>
		</div>
		<div class="inline top nano <?php echo $disabledBtns; ?>" style="display:none;">
			<button class="bouton validNotifsR" title="valider" what="receiveNotifs"><span class="ui-icon ui-icon-check"></span></button>
			<button class="bouton ui-state-error annulNotifsR" title="annuler"><span class="ui-icon ui-icon-close"></span></button>
		</div>
	</div>

<!--	<div class="margeTop1">
		<div class="inline mid w150 marge30l">VCard : </div>
		<div class="inline mid w300 nano pad3 <?php echo $disabledBtns; ?>" Vcard="<?php echo $infosUser['vcard']; ?>">
			<button class="bouton VcardDownload" title="télécharger"><span class="ui-icon ui-icon-extlink"></span></button>
			<button class="bouton VcardUpload"  title="remplacer"><span class="ui-icon ui-icon-arrow-2-n-s"></span></button>
			<button class="bouton Vcardrefresh" title="rfraîchir"><span class="ui-icon ui-icon-refresh"></span></button>
		</div>
	</div>-->

</div>


<div class="inline top gros">
	<div class="marge15bot">
		<div class="pad10 fondPage ui-corner-all colorErrText">
			Niveau d'habilitation <?php echo strtoupper($_SESSION['STATUS_LIST'][$infosUser['status']]); ?>
		</div>
	</div>
	<div class="margeTop10">
		<h3><?php echo mb_strtoupper(L_PROJECTS); ?></h3>
		<table class="tableListe" style="line-height: 25px; box-shadow: none;">
			<tr>
				<th style="padding-right: 30px;"><?php echo L_TITLE; ?></th>
				<th class="center" title="<?php echo L_MY_ASSETS; ?>"><span class="ui-icon ui-icon-image"></span></th>
				<th class="center" title="<?php echo L_MY_SCENES; ?>"><span class="ui-icon ui-icon-link"></span></th>
				<th class="center" title="<?php echo L_MY_SHOTS; ?>"><span class="ui-icon ui-icon-copy"></span></th>
				<th class="w300"><?php echo L_PATH; ?> (local)</th>
				<th class="w300"><?php echo L_PATH; ?>/URL (cloud)</th>
			</tr><?php
			foreach($projectsUser as $pId) :
				try {
					$p = new Projects((int)$pId);
					if (!$p->isVisible()) continue;
					$pShots  = $p->getShots(); $countMyshots = 0;
					if (is_array($pShots)) {
						foreach ($pShots as $sh) if (in_array($sh[Shots::SHOT_ID_SHOT], $userShots)) $countMyshots++;
					}
					$pScenes = $p->getScenes(); $countMyscenes = 0;
					if (is_array($pScenes)) {
						foreach ($pScenes as $sc) if (in_array($sc[Scenes::ID_SCENE], $userScenes)) $countMyscenes++;
					}
					$pAssets = $p->getAssets(); $countMyassets = 0;
					if (is_array($pAssets)) {
						foreach ($pAssets as $as) if (in_array($as[Assets::ASSET_ID], $userAssets)) $countMyassets++;
					}
				}
				catch (Exception $e) { echo $e->getMessage(); continue; } ?>
				<tr idProj="<?php echo $pId; ?>">
					<td class="top colorBtnFake gras doigt" onclick="openProjectTab(<?php echo $pId; ?>);" title="click to open project"><?php echo $p->getTitleProject(); ?></td>
					<td class="top center <?php echo ($countMyassets == 0) ? 'colorDiscret' : ''; ?>"><?php echo $countMyassets; ?></td>
					<td class="top center <?php echo ($countMyscenes == 0) ? 'colorDiscret' : ''; ?>"><?php echo $countMyscenes; ?></td>
					<td class="top center <?php echo ($countMyshots == 0) ? 'colorDiscret' : ''; ?>"><?php echo $countMyshots; ?></td>
					<td class="top">
						<span class="currPath colorDiscret<?php echo (isset($userPpath[$pId])) ? '2' : '' ?>"><?php
							echo (isset($userPpath[$pId])) ? preg_replace('/ /', '&nbsp;', $userPpath[$pId]) : 'Unknown yet.'
						?></span>
						<div class="pico">
							<button class="bouton modProjUserPath"><span class="ui-icon ui-icon-pencil"></span></button>
							<span class="confirmBtns hide">
								<button class="bouton confirmProjUserPath"><span class="ui-icon ui-icon-check"></span></button>
								<button class="bouton ui-state-error annuleProjUserPath"><span class="ui-icon ui-icon-cancel"></span></button>
							</span>
						</div>
					</td>
					<td class="top">
						<span class="currPath <?php echo (isset($userPuri[$pId])) ? 'colorBtnFake' : 'colorDiscret' ?>"><?php
							echo (isset($userPuri[$pId])) ? preg_replace('/ /', '&nbsp;', $userPuri[$pId]) : 'Unknown yet.'
						?></span>
						<div class="pico">
							<button class="bouton modProjUserUrl"><span class="ui-icon ui-icon-pencil"></span></button>
							<span class="confirmBtns hide">
								<button class="bouton confirmProjUserUrl"><span class="ui-icon ui-icon-check"></span></button>
								<button class="bouton ui-state-error annuleProjUserPath"><span class="ui-icon ui-icon-cancel"></span></button>
							</span>
						</div>
					</td>
				</tr>
		<?php endforeach; ?>
		</table>
	</div>
</div>
