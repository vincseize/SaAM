<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	require_once('directories.php');
	//@TODO : check ACL pour l'accès

	if (isset($_POST['path']))
		$path = $_POST['path'];
	else die('Chemin indéfini...');

	require_once('vignettes_fcts.php');

	$htmlList = '';
	check_thumbDir($path);
	$listFiles = glob(INSTALL_PATH.$path.'*');
	usort($listFiles, "sort_by_mtime");
	$i = count($listFiles) - count(glob(INSTALL_PATH.$path.'*', GLOB_ONLYDIR));
	foreach($listFiles as $file) {
		if (!is_file($file)) continue;
		$filename = basename($file);
		$mimeTypeFile = check_mime_type($file);
		$btnDelete = '<div class="divBtnDelShotBank hide">
						<span style="font-size:8em;">'.$i.'</span>
						<button class="deleteFileShot floatR" filePath="'.$path.'" file="'.$filename.'">
							<span class="ui-icon ui-icon-trash"></span>
						</button>
					  </div>';
		// Si c'est un fichier de type image
		if (preg_match('/image/i', $mimeTypeFile)) {
			$srcThumb = $path.'thumbs/t_'.$filename;
			if (!check_create_thumb($file))
				$srcThumb = 'gfx/novignette/novignette_image.png" width="60';
			$htmlList .= '<div class="inline mid marge10l margeTop10 bankItem">
							'.$btnDelete.'
							<a class="fancybox-bankShot" href="'.$path.$filename.'" rel="bankshot" title="'.$filename.'">
								<img src="'.$srcThumb.'" alt="'.$filename.'">
							</a>
						  </div>';
		}
		// Si c'est un fichier de type vidéo
		elseif (preg_match('/video/i', $mimeTypeFile)) {
			$srcThumb = $path.'thumbs/vthumb_'.$filename.'.gif';
			if (!check_create_video_thumb($file, INSTALL_PATH.$srcThumb))
				$srcThumb = 'gfx/novignette/novignette_video.png" width="60';
			$htmlList .= '<div class="inline mid marge10l margeTop10 bankItemVideo">
							'.$btnDelete.'
							<a class="fancybox-bankShot" href="#video'.$i.'" >
								<img src="'.$srcThumb.'" alt="'.$filename.'">
							</a>
						  </div>
						  <div class="hide center" id="video'.$i.'">
							<video src="'.$path.$filename.'" width="960" heigth="540" preload="none" controls="controls" loop="loop" poster="'.$srcThumb.'" /><br />
							'.$filename.'
						  </div>';
		}
		// Si c'est un fichier de type audio
		elseif (preg_match('/audio/i', $mimeTypeFile)) {
			$htmlList .= '<div class="inline mid center marge10l margeTop10 bankItemOther">
							'.$btnDelete.'
							<a class="fancybox-bankShot" href="#audio'.$i.'">
								<img src="gfx/icones/audio.png" height="40" /><br />
								'.$filename.'
							</a>
							<div class="hide center pad20" id="audio'.$i.'">
								<img src="gfx/icones/audio.png" /><br />
								'.$filename.'<br /><br />
								<audio src="'.$path.$filename.'" width="500" preload="auto" controls="controls" loop="loop" />
							</div>
						</div>';
		}
		// Si c'est un fichier de type PDF
		elseif (preg_match('/PDF/i', $mimeTypeFile)) {
			$htmlList .= '<div class="inline mid center marge10l margeTop10 bankItemOther">
							'.$btnDelete.'
							<a href="'.$path.$filename.'" target="_blank">
								<img src="gfx/icones/pdf_file.png" height="40" /><br />
								'.$value.'
							</a>
						</div>';
		}
		// Si c'est un fichier de type archive
		elseif (preg_match('/zip/i', $mimeTypeFile)) {
			$htmlList .= '<div class="inline mid center marge10l margeTop10 bankItemOther">
							'.$btnDelete.'
							<a href="'.$path.$filename.'" target="_blank">
								<img src="gfx/icones/archive.png" height="40" /><br />
								'.$filename.'
							</a>
						</div>';
		}
		$i--;
	}

	if ($htmlList != ''): ?>

		<div class="floatR marge10r pico" help="shot_folders_btns">
			<button class="bouton" id="folderContactSheet" title="Print a contact sheet of this folder"><span class="ui-icon ui-icon-clipboard"></span></button>
			<button class="bouton marge10r" id="zipFolder" title="Download this folder as ZIP archive"><span class="ui-icon ui-icon-suitcase"></span></button>
		</div>

<?php endif;
	echo ($htmlList == '') ? '<i class="ui-state-disabled">Dossier vide.<br />Drag & drop files here.</i>' : $htmlList;
?>

<script>
	$(function(){
		$('.bouton').button();
		$('.deleteFileShot').button();

		$('#listFolderContent').off('dragenter dragleave');
		$('#listFolderContent').on('dragenter', function(){ $(this).addClass('fondHigh shadowIn'); })
							   .on('dragleave', function(){ $(this).removeClass('fondHigh shadowIn'); });

		$('#folderContactSheet').click(function(){
			var params = 'proj_ID='+project_ID+'&folderPath=<?php echo urlencode($path); ?>';
			window.open('modals/sheets/folder_sheet?'+params, "ContactSheet", "menubar=no, scrollbars=yes, width=1024");
		});

		// Download de dossier en ZIP
		$('#zipFolder').click(function(){
			var item = false;
			var type = localStorage['lastGroupDepts_'+project_ID];
			if (type == 'shots')  item = shotInfos;
			if (type == "assets") item = assetInfos;
			if (!item) {
				retourAjaxMsg({"error":"error", "message":"Can't get the item infos."});
				return;
			}
			var ajaxReq	= 'action=zipDLshotFolder&proj_title='+titleProj+'&type='+type+'&item='+item+'&folder='+activeFolder+'&folderPath=<?php echo urlencode($path); ?>';
			AjaxJson(ajaxReq, 'bank_actions', retourAjaxMsg);
		});

		$('.bankItem, .bankItemVideo, .bankItemOther').hover(
			function(){
				$(this).find('.divBtnDelShotBank').show();
			},
			function(){
				$(this).find('.divBtnDelShotBank').hide();
			}
		);
	});
</script>

<br /><br /><br /><br />
