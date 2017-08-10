<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC'] . "/checkConnect.php" );

	// Récupère la liste des projets existants
	$l = new Liste();
	if ($_SESSION['user']->isDemo())
		$l->addFiltre(Projects::PROJECT_TYPE, "=", "demo");
	$l->getListe(TABLE_PROJECTS, 'id,title');
	$l->resetFiltre();
	$projectsList = $l->simplifyList();

	// Récupère la liste des projets de l'utilisateur
	$userProjects = $_SESSION['user']->getUserProjects();

	// Récupère la liste des status users
	$statusList = $_SESSION['STATUS_LIST'];

	// Récupère la liste des compétences disponibles
	try {
		$inf = new Infos(TABLE_CONFIG);
		$inf->loadInfos('version', SAAM_VERSION);
		$compList = json_decode($inf->getInfo('available_competences'));
	}
	catch (Exception $e) { $compList = $_SESSION['CONFIG']['AV_COMPETENCES']; }

?>

<script>
	$(function(){
		$('.bouton').button();
		$('#filtres').hide();
		$('#status').selectmenu({style: 'dropdown'});
		$('#competences').multiselect({noneSelectedText: 'Aucun', selectedText: '# competence(s)', selectedList: 3, checkAllText: ' ', uncheckAllText: ' '});
	});
</script>

<script src="js/blueimp_uploader/jquery.iframe-transport.js"></script>
<script src="js/blueimp_uploader/jquery.fileupload.js"></script>
<script src="js/blueimp_uploader/jquery.fileupload-fp.js"></script>

<script>
	$(function(){

		if (localStorage.getItem('addProj_VignetteAddUserTempName')) {
			var d = new Date();
			$('#vignette_upload_img').attr('src', 'temp/uploads/vignettes/'+localStorage.getItem('addProj_VignetteAddUserTempName')+"?_"+d.getTime())
		}

		// Empêche les drop de fichier dans le browser
		$(document).bind('drop dragover', function (e) {
			e.preventDefault();
		});

		// Init de l'uploader
		$('#vignette_upload').fileupload({
			url: "actions/upload_vignettes.php?type=avatarAddUser",
			dataType: 'json',
			dropZone: $('#user_avatar'),
			change: function (e, data) {
				$('#vignette_upload_msg').html('<span class="ui-state-disabled"><i>sending</i></span> '+data.files[0].name + '... <img src="gfx/ajax-loader.gif" />');
			},
			done: function (e, data) {
				var retour = data.result;
				if (retour[0].error) {
					$('#vignette_upload_msg').append('<br /><span class="colorErreur gras">Failed : '+retour[0].error+'</span>').show().find('img').remove();
					lastUploadedVignette = '';
				}
				else {
					var d = new Date();
					$('#vignette_upload_img').attr("src", decodeURI(retour[0].url)+"?_"+d.getTime()).parent('div').addClass('panavignette');
					$('#vignette_upload_msg').append('<span class="colorOk gras">OK</span>').hide(transition).find('img').remove();
					lastUploadedVignette = decodeURI(retour[0].name);
					localStorage.setItem('addProj_VignetteAddUserTempName', decodeURI(retour[0].name));
					$('#vignette_upload_msg').next('div').show();
				}
			}
		});

		// clic sur bouton "Envoyer une vignette"
		$('#vignette_upload_Btn').click(function(){
			$('#vignette_upload').click();
		});

	});

</script>

<script src="ajax/add_user.js"></script>

<div class="stageContent pad5">
	<h2><?php echo L_ADD.' '.L_USER; ?></h2>

	<div class="inline top gros">
		<div class="margeTop1">
			<div class="inline mid w150 marge30l">Login: </div>
			<input type="text" id="login" class="noBorder pad3 ui-corner-top w300 fondSect3 addUser requiredField" title="Login" onkeypress="return checkChar(event,true,true,null)" />
			<div class="inline mid ui-state-error noBG noBorder"><span class="ui-icon ui-icon-notice"></span></div>
		</div>
		<div class="margeTop1">
			<div class="inline mid w150 marge30l"><?php echo L_PASSWORD; ?> : </div>
			<input type="password" id="passwd" class="noBorder pad3 w300 fondSect3 addUser requiredField" title="Mot de Passe" onkeypress="return checkChar(event,true,true,null)" />
			<div class="inline mid ui-state-error noBG noBorder"><span class="ui-icon ui-icon-notice"></span></div>
		</div>
		<div class="margeTop1">
			<div class="inline mid w150 marge30l">E-mail address: </div>
			<input type="text" id="mail" class="noBorder pad3 w300 fondSect3 addUser requiredField" title="Email" />
			<div class="inline mid ui-state-error mini noBG noBorder" id="noticeMail"><span class="ui-icon ui-icon-notice"></span></div>
		</div>
		<div class="margeTop1">
			<div class="inline mid w150 marge30l">Pseudo: </div>
			<input type="text" id="pseudo" class="noBorder pad3 w300 fondSect3 addUser requiredField" title="Pseudo" onkeypress="return checkChar(event,true,true,null)" />
			<div class="inline mid ui-state-error noBG noBorder"><span class="ui-icon ui-icon-notice"></span></div>
		</div>
		<div class="margeTop1">
			<div class="inline mid w150 marge30l"><?php echo L_FIRST_NAME; ?>: </div>
			<input type="text" id="prenom" class="noBorder pad3 w300 fondSect3 addUser" title="Prénom" onkeypress="return checkChar(event,null,true,null)" />
		</div>
		<div class="margeTop1 marge15bot">
			<div class="inline mid w150 marge30l"><?php echo L_NAME; ?>: </div>
			<input type="text" id="nom" class="noBorder pad3 ui-corner-bottom w300 fondSect3 addUser" title="Nom" onkeypress="return checkChar(event,null,true,null)" />
		</div>
		<div class="margeTop10">
			<div class="inline mid w150 marge30l"><?php echo L_LEVEL; ?>: </div>
			<div class="inline mid mini">
				<select id="status" class="w300 addUser">
					<?php
					foreach ($statusList as $status => $statusName) {
						if ($status < $_SESSION['user']->getUserInfos(Users::USERS_STATUS))
							echo '<option class="mini" value="'.$status.'">'.$statusName.'</option>';
					}
					?>
				</select>
			</div>
		</div>
		<div class="margeTop10 marge15bot">
			<div class="inline top margeTop5 w150 marge30l"><?php echo L_SKILLS; ?>: </div>
			<div class="inline mid mini">
				<select id="competences" multiple="multiple" class="w300 addUser">
					<?php
					foreach ($compList as $comp)
						echo '<option class="mini" value="'.$comp.'">'.strtoupper($comp).'</option>';
					?>
				</select>
			</div>
		</div>

	</div>


	<div class="inline top center w300 marge30l">
		<div class="inline" id="user_avatar"><img src="gfx/novignette/novignette_user.png" width="150" id="vignette_upload_img" /></div>
		<input class="hide" type="file" name="files[]" id="vignette_upload" />
		<br />
		<button class="bouton" title="or drag and drop a .jpg ou .png file here" id="vignette_upload_Btn"><?php echo mb_convert_case(L_SEND, MB_CASE_UPPER); ?> avatar</button>
		<div class="petit margeTop10" id="vignette_upload_msg"></div>
	</div>


	<div class="margeTop10 center hide" id="submitBtns">
		<br /><br />
		<?php if (!$_SESSION['user']->isDemo()): ?>
			<button class="bouton marge30l" id="add_user_Done"><?php echo L_DONE ?></button>
		<?php else: ?>
			<div class="marge30r"><button class="bouton marge30r"><span class="colorErreur"><?php echo @L_DEMO_MODE; ?></span></button></div>
		<?php endif; ?>
	</div>
</div>