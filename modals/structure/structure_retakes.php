<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	require_once ('directories.php');
	require_once ('url_fcts.php');

	require_once('vignettes_fcts.php');

	if (isset($_POST['shotID']))
		$idShot = $_POST['shotID'];
	else die('Plan indéfini...');

	if (isset($_POST['dept']))
		$labelDept = $_POST['dept'];
	else die('Département indéfini...');

	$idDept = get_ID_dept($labelDept, 'shots');
	if ($idDept === false) $idDept = $labelDept;

	try {
		$shot = new Shots($idShot);
		$dirRetakes = $shot->getDirRetakes($idDept);
		$srclastRetake = FOLDER_DATA_PROJ.$shot->getLastRetake($idDept).'?_'.time();
        $lastRetake = $shot->getLastRetake($idDept);
		$validLastRetake = $shot->isValidLastRetake($idDept);
		$oldRetakesList  = $shot->getRetakesList($idDept);
		$nbRetakes	= count($oldRetakesList);
	}
	catch (Exception $e) {
		echo 'ERREUR : '.$e->getMessage();
		die();
	}

	// Si une retake est déjà en attente dans le dossier /temp
	$tempRetakeName = $_SESSION['user']->getUserInfos('id').'_newRetakeTemp';
	$testTempRetake = glob(INSTALL_PATH.'temp/uploads/retakes/'.$tempRetakeName.'.*');
	$newRetakeImg = 'gfx/novignette/novignette_retake.png'; $JSretakeName = '';
	if (count($testTempRetake) > 0) {
		$JSretakeName = basename($testTempRetake[0]);
		$newRetakeImg = 'temp/uploads/retakes/'.basename($testTempRetake[0]).'?'.time();
		if (preg_match('/(ogg|avi|mov|mp4)/i', $JSretakeName))
			$newRetakeImg = 'gfx/novignette/novignette_video_retake.png';
	}

	// Si la retake actuelle est validée
	$authAddRetake = ($validLastRetake || $nbRetakes == 0) ? 'true' : 'false';
	$hideRetakeActuelle = ($nbRetakes == 0) ? 'hide' : '';

	// Formattage du nom des fichiers retakes à télécharger
	$targetName = 'SaAMpub_'.preg_replace('/ /', '-', $titleProj).'_'.preg_replace('/ |_/', '-', $titleSeq).'_'.preg_replace('/ |_/', '-', $titleShot).'_'.$labelDept;
?>

<script>
	var retakeName = '<?php echo $JSretakeName; ?>';
	var authAddRetake = <?php echo $authAddRetake; ?>;

	$(function(){
		$('.bouton').button();

		if (isLocked)
			$('#validRetake, #modifRetake, #drawOnRetake').addClass('ui-state-disabled');

		if (!authAddRetake)
			$('#btn_addRetake').hide();
		else $('#btn_addRetake').show();

		$(".fancybox").fancybox({'type' : 'image'});
		$(".fancyboxVideo").fancybox();

		// PLUGIN DRAWTOOL
		<?php if (@(bool)$_SESSION['CONFIG']['plugins_enabled']['drawtool']): ?>
			$(".fancyboxDrawtool").fancybox({
				type: 'ajax', autoSize: false, width: winWidth, height: winHeight,
				beforeClose: function(){
					return confirm("Close this drawing? Everything will be lost.\n\nAre you sure?");
				}
			});
		<?php endif; ?>

		// Clic sur une div de old retake
		$('.oldRetakeHead').click(function(){
			if ($(this).attr('opened') != 'opened') {
				var retakeFilename = $(this).attr('vName');
				var rNum = $(this).attr('rNum');
				$('.oldVignette').hide(transition);
				$(this).parent('.retakeVign').find('.oldVignette').attr('src', retakeFilename).show(transition);
				$('#shotViewRight').load('modals/structure/structure_messages.php', {shotID:shot_ID, dept:id_dept, retakeNum:rNum, authAdd: false});
				$('.oldRetakeHead').removeAttr('opened');
				$(this).attr('opened','opened');
			}
			else {
				$(this).parent('.retakeVign').find('.oldVignette').hide(transition);
				if ($(this).attr('isVideo') == 'true')
					$(this).parent('.retakeVign').find('.oldVignette').attr('src', '');
				$('#shotViewRight').load('modals/structure/structure_messages.php', {shotID:shot_ID, dept:id_dept}, function(){
					if (!authAddRetake)
						$(this).removeClass('ui-state-disabled');
				});
				$(this).removeAttr('opened');
			}
		});

		var shotViewH = $('#shotViewLeft').height();
		$('#retakesList').slimScroll({
			position: 'left',
			height: shotViewH+'px',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		});

		$('#activeRetakeVignette').hover(
			function(){
				$('#panelRetake').show();
			},
			function(){
				$('#panelRetake').hide();
			}
		);
		$('.oldVignette, .panelsOldRetakes').hover(
			function(){
				$(this).parents('.retakeVign').find('.panelsOldRetakes').show();
			},
			function(){
				$(this).parents('.retakeVign').find('.panelsOldRetakes').hide();
			}
		);

		// Upload progress bar
		$('#newRetake, #changeRetake').bind('fileuploadprogress', function (e, data) {
			var filename = data.files[0].name;
			var percent =  data.loaded / data.total * 100;
			if (percent < 98) {
				$('#retourAjax').find('.uploadProg[filename="'+filename+'"]')
								.progressbar({value: percent})
								.children('span').html('speed : '+Math.round(data.bitrate / 10000)+'Kb/s => '+Math.round(percent)+' %...');
			}
			else {
				$('#retourAjax').find('.uploadProg[filename="'+filename+'"]')
								.progressbar({value: 100})
								.children('span').html('DONE. Encoding (if video file)...');
			}
		});

		// Met le numéro de la dernière retake tout en haut de la liste
		<?php if ($nbRetakes == 0): ?>
		$('#activeRetakeNumber').html('<?php echo L_NO_RETAKE; ?>');
		<?php else: ?>
		$('#activeRetakeNumber').html('PUBLISHED <?php echo sprintf('%03d',$nbRetakes); ?>');
		<?php endif; ?>

		// Bouton ajout de retake
		$('#btn_addRetake').click(function(){
			if (authAddRetake==true) {
				if (isLocked) {
					$('#retourAjax').html('This shot is locked.').addClass('ui-state-error').show(transition);
					setTimeout(function(){$('#retourAjax').fadeOut(transition, function(){$('#retourAjax').html('');});}, 1000);
					return;
				}
				$('#activeRetakeNumber').html('Nouveau Published');
				$('#newRetake').show(transition);
				$(this).addClass('ui-state-activeFake');
			}
			else {
				$('#retourAjax').html('Le dernier published doit être <img src="gfx/icones/icone_valid.png" width="14" /> (validé) pour pouvoir en ajouter un nouveau !')
								.addClass('ui-state-error').show(transition);
				setTimeout(function(){$('#retourAjax').fadeOut(transition*6);},8000);
			}
		});

		// Init de l'uploader de RETAKE
		$('#newRetake').fileupload({
			url: "actions/upload_retake.php",
			dataType: 'json',
			dropZone: $('#newRetakeUpload'),
			drop: function (e, data) {
				if (isLocked) {
					$('#retourAjax').html('This shot is locked.').addClass('ui-state-error').show(transition);
					setTimeout(function(){$('#retourAjax').fadeOut(transition, function(){$('#retourAjax').html('');});}, 1000);
					return;
				}
				$('#retourAjax')
					.html('Sending published...<br /><div class="uploadProg mini" filename="'+data.files[0].name+'"><span class="floatL marge5 colorMid"></span></div>')
					.removeClass('ui-state-error').addClass('ui-state-highlight')
					.show(transition);
			},
			done: function (e, data) {
				var retour = data.result;
				if (retour[0].error) {
					$('#retourAjax')
						.html('<span class="colorErreur gras">Failed : '+retour[0].error+'</span>')
						.addClass('ui-state-error')
						.show(transition);
				}
				else {
					retakeName = decodeURI(retour[0].name);
					var timeStamp = Math.round((new Date()).getTime()/1000); // pour anti-cache
					$('#retourAjax').removeClass('ui-state-error').addClass('ui-state-highlight').html('Published uploaded.').show(transition);
					if (retour[0].type.indexOf('image') == -1) {
						$('#newRetakeUpload').html('<iframe src="'+retour[0].url+'" width="270" height="150" frameborder="0" scrolling="no"></iframe>');
					}
					else $('#newRetakeUpload').html('<img src="'+retour[0].url+'?_'+timeStamp+'" width="270" height="150" />');
					setTimeout(function(){$('#retourAjax').fadeOut(transition);},1500);
				}
			}
		});

		// Validation upload nouvelle retake
		$('#commitNewRetake').click(function(){
			if(retakeName == '') { alert('<?php echo L_ADD_RETAKE_ERROR; ?>'); return; }
			var ajaxReq = 'action=moveTempRetake&idShot='+shot_ID+'&dept='+id_dept+'&retakeName='+retakeName;
			AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure, 'reloadShot');
		});

		// Annulation nouvelle retake
		$('#cancelNewRetake').click(function(){
			$('#activeRetakeNumber').html('PUBLISHED <?php echo sprintf('%03d',$nbRetakes); ?>');
			$('#newRetake').hide(transition);
			$('#btn_addRetake').removeClass('ui-state-activeFake');
		});


		// Valide la retake actuelle
		$('#validRetake').click(function(){
			if (isLocked) {
				$('#retourAjax').html('This shot is locked.').addClass('ui-state-error').show(transition);
				setTimeout(function(){$('#retourAjax').fadeOut(transition, function(){$('#retourAjax').html('');});}, 1000);
				return;
			}
			if (!confirm('Valider ce published ? Sûr ?')) return;
			var ajaxReq = 'action=valideRetake&projID='+project_ID+'&idShot='+shot_ID+'&dept='+departement;
			AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure, 'reloadShot');
		});


		// Permet la modif de la retake actuelle
		$('#modifRetake').click(function(){
			if (isLocked) {
				$('#retourAjax').html('This shot is locked.').addClass('ui-state-error').show(transition);
				setTimeout(function(){$('#retourAjax').fadeOut(transition, function(){$('#retourAjax').html('');});}, 1000);
				return;
			}
			$('#changeRetake').click();
		});

		// Init de l'uploader de RETAKE QUAND on clique sur le bouton "modifier la retake" (petit crayon)
		$('#changeRetake').fileupload({
			url: "actions/upload_retake.php",
			dataType: 'json',
			dropZone: null,
			change: function (e, data) {
				$('#retourAjax')
					.html('Sending published...<br /><div class="uploadProg mini" filename="'+data.files[0].name+'"><span class="floatL marge5 colorMid"></span></div>')
					.addClass('ui-state-highlight')
					.show(transition);
			},
			done: function (e, data) {
				var retour = data.result;
				if (retour[0].error) {
					$('#retourAjax')
						.html('<span class="colorErreur gras">Failed : '+retour[0].error+'</span>')
						.addClass('ui-state-error')
						.show(transition);
				}
				else {
					retakeName = decodeURI(retour[0].name);
					var timeStamp = Math.round((new Date()).getTime()/1000); // pour anti-cache
					$('#retourAjax').removeClass('ui-state-error').addClass('ui-state-highlight').html('Published uploaded. N\'oubliez pas de VALIDER !').show(transition);
					console.log($('#vi').attr('src'));
					var srcVignette = retour[0].url+'?_'+timeStamp;
					if (retour[0].type.indexOf('image') == -1)
						srcVignette = 'gfx/novignette/novignette_video_retake.png';
					$('#vi').attr('src', srcVignette);
					console.log($('#vi').attr('src'));
					$('#panelRetake').html(
							'<div class="ui-corner-all ui-state-error inline btnR" id="commitModRetake"><span class="ui-icon ui-icon-check" title="Valider l\'upload"></span></div>'
						+   '<div class="inline btnR" id="cancelModRetake"><span class="ui-icon ui-icon-cancel" title="Annuler l\'upload"></span></div>')
							.show();
					setTimeout(function(){$('#retourAjax').fadeOut(transition);},3000);
				}
			}
		});

		// valide une modif de retake
		$('#retakesList').off('click', '#commitModRetake');
		$('#retakesList').on('click', '#commitModRetake', function(){
			var ajaxReq = 'action=moveTempRetake&idShot='+shot_ID+'&dept='+id_dept+'&retakeName='+retakeName+'&modif=true';
			AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure, 'reloadShot');
		});

		// annule une modif de retake
		$('#retakesList').off('click', '#cancelModRetake');
		$('#retakesList').on('click', '#cancelModRetake', function(){
			var ajaxReq = 'action=deleteTempRetake';
			AjaxJson(ajaxReq, 'admin/admin_shots_actions', retourAjaxStructure, 'reloadShot');
		});
	});

</script>


<div id="retakesList" help="shot_published">

	<div class="hide" id="newRetake" help="shot_add_published">
		<div class="noMarge" id="newRetakeUpload" title="drag and drop a file here, and click validate yellow icon">
			<img src="<?php echo $newRetakeImg; ?>" width="270" height="150" />
		</div>
		<div class="fondSect3 colorSoft center noMarge pad3">PUBLISHED <?php echo sprintf('%03d',$nbRetakes); ?></div>
		<div id="panelNewRetake">
			<div class="ui-corner-all ui-state-error inline btnR" id="commitNewRetake"><span class="ui-icon ui-icon-check" title="Valider l'upload"></span></div>
			<div class="inline btnR" id="cancelNewRetake"><span class="ui-icon ui-icon-cancel" title="Annuler l'ajout"></span></div>
		</div>
	</div>


	<div class="fondSect3 colorSoft retakeVignActive <?php echo $hideRetakeActuelle; ?>" id="activeRetakeVignette"><?php
		$typeLastRetake = check_mime_type(INSTALL_PATH.FOLDER_DATA_PROJ.$lastRetake);
		$pathRetake = FOLDER_DATA_PROJ. dirname($lastRetake);
		$retakeName = basename($lastRetake);
		if (preg_match('/video/i', $typeLastRetake)) :
			$absVignetteRetakeVideo = INSTALL_PATH.$pathRetake.'/thumbs/vthumb_'.$retakeName.'.gif';
			$cts = time();
			$vignetteRetakeVideo	= $pathRetake.'/thumbs/vthumb_'.$retakeName.'.gif?_'.$cts;
			if (!check_create_video_thumb(INSTALL_PATH.$pathRetake.'/'.$retakeName, $absVignetteRetakeVideo, true, 1000, 270, 150))
				$vignetteRetakeVideo = 'gfx/novignette/novignette_video_retake.png' ?>
			<a class="fancyboxVideo" href="#videoLastRetake_<?php echo $idShot.'_'.$idDept; ?>">
				<img src="<?php echo $vignetteRetakeVideo; ?>" width="270" height="150" class="noMarge" id="vi" />
			</a>
			<div class="hide" id="videoLastRetake_<?php echo $idShot.'_'.$idDept; ?>" style="max-height: 600px;">
				<video src="<?php echo "$pathRetake/video_$retakeName.ogv?_$cts"; ?>" width="960" heigth="540" preload="none" controls="controls" loop="loop" poster="<?php echo $vignetteRetakeVideo; ?>" />
			</div>
		<?php else: ?>
			<a class="fancybox" rel="lastRetake" href="<?php echo $srclastRetake; ?>">
				<img src="<?php echo $srclastRetake; ?>" width="270" height="150" class="noMarge" id="vi"/>
			</a>
		<?php endif; ?>
		<input class="hide" type="file" name="files[]" id="changeRetake" />
		<?php if ($validLastRetake) : ?>
			<div id="retakeState"><img src="gfx/icones/icone_valid.png" width="18" /></div>
		<?php endif; ?>
		<div id="panelRetake">
			<div class="inline btnR">
				<a href="fct/downloader.php?type=retake&file=<?php echo $retakeName; ?>&dirRetake=<?php echo dirname($lastRetake); ?>&targetName=<?php echo $targetName; ?>" target="new">
					<span class="ui-icon ui-icon-arrowthickstop-1-s" title="Download this published"></span>
				</a>
			</div><?php
			if (!$validLastRetake) :
				if (@(bool)$_SESSION['CONFIG']['plugins_enabled']['drawtool']): ?>
					<a class="fancyboxDrawtool" href="<?php echo getSaamURL(); ?>/plugins/drawtool/drawtool.php?Wmax=1450&Hmax=850&pubFile=<?php echo urlencode($lastRetake); ?>">
						<div class="inline btnR" id="drawOnRetake"><span class="ui-icon ui-icon-pencil" title="Draw on published"></span></div>
					</a><?php
				endif; ?>
				<div class="inline btnR" id="modifRetake"><span class="ui-icon ui-icon-wrench" title="Change published"></span></div><?php
				if ($_SESSION['user']->isLead()) : ?>
					<div class="inline btnR" id="validRetake"><span class="ui-icon ui-icon-disk" title="Validate published"></span></div><?php
				endif;
			endif; ?>
		</div>
	</div>

	<?php
	if (is_array($oldRetakesList)) :
		foreach ($oldRetakesList as $nR => $retakeName) :
			if ($nR==0) continue;
			$nRStr = sprintf('%03d', $nR);
			$pathRetakes	= INSTALL_PATH.FOLDER_DATA_PROJ.$dirRetakes.'/';
			$typeOldRetake	= check_mime_type($pathRetakes.$retakeName);
			$typeOldRetakeA = explode(';', $typeOldRetake);
			$isVideo = 'false';
			$retakeVignette = FOLDER_DATA_PROJ.$dirRetakes.'/'.$retakeName;
			if (preg_match('/video/i', $typeOldRetake)) {
				$isVideo = 'true';
				$retakeVignetteABS	= $pathRetakes.'thumbs/vthumb_'.$retakeName.'.gif';
				$retakeVignette		= FOLDER_DATA_PROJ.$dirRetakes.'/thumbs/vthumb_'.$retakeName.'.gif';
				if (!check_create_video_thumb($pathRetakes.$retakeName, $retakeVignetteABS, true, 1000, 270, 150))
					$retakeVignette = 'gfx/novignette/novignette_video_retake.png';
			} ?>

			<div class="fondSect3 colorSoft retakeVign">
				<div class="oldRetakeHead" vName="<?php echo $retakeVignette; ?>" rNum="<?php echo $nR; ?>" isVideo="<?php echo $isVideo; ?>" title="click to open/close">
					PUBLISHED <?php echo $nRStr; ?>
				</div><?php
				if ($isVideo == 'true') : ?>
					<a class="fancyboxVideo" href="#videoRetake<?php echo $nR; ?>">
						<img src="" width="270" height="150" class="margeTop5 oldVignette hide" />
					</a>
					<div class="hide" id="videoRetake<?php echo $nR; ?>">
						<video src="<?php echo FOLDER_DATA_PROJ.$dirRetakes.'/video_'.$retakeName.'.ogv'; ?>" width="960" heigth="540" preload="none" loop="loop" poster="<?php echo $retakeVignette; ?>" controls="controls" />
					</div><?php
				else : ?>
					<a class="fancybox" rel="oldRetake" href="<?php echo FOLDER_DATA_PROJ.$dirRetakes.'/'.$retakeName; ?>">
						<img src="" width="270" height="150" class="margeTop5 oldVignette hide" />
					</a><?php
				endif; ?>
				<div class="panelsOldRetakes">
					<div class="inline btnR">
						<a href="fct/downloader.php?type=retake&file=<?php echo $retakeName; ?>&dirRetake=<?php echo $dirRetakes; ?>&targetName=<?php echo $targetName; ?>" target="new">
							<span class="ui-icon ui-icon-arrowthickstop-1-s" title="Download this published"></span>
						</a>
					</div>
				</div>
			</div><?php
		endforeach;
	endif; ?>

</div>

