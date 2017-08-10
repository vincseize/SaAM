<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC'].'/checkConnect.php' );
	require_once('dates.php');
	require_once('directories.php');
	require_once('vignettes_fcts.php');

extract($_POST);	// @NEED : idProj, bankFolder

// Construction de la bank
try {
    $p = new Projects($idProj);
    $projInfos = $p->getProjectInfos();
	$dirProj   = $p->getDirProject();
	$dirBank   = FOLDER_DATA_PROJ.$dirProj.'/bank/'.$bankFolder;
    $titleProj = $projInfos[Projects::PROJECT_TITLE];

	$gallery = '';
	$bank = listDirBank_Project($idProj, $titleProj, $bankFolder);
	if (count($bank) == 0)
		$gallery = '<div class="margeTop10 colorSoft gros center"><br /><br />No file yet!<br /><br />Drag & Drop files on this page to upload some refs!</div>';

	$i = count($bank);
	foreach ($bank as $value) {
		$id_thumb = "thumb".$i;
		$fileBank = $dirBank.'/'.$value;
		$src_thumb = $dirBank."/thumbs/thumb_".$value;
		$src_vthumb = $dirBank."/thumbs/vthumb_".$value.'.gif';
		$mimeTypeFile = check_mime_type(INSTALL_PATH.$fileBank);

		// check si a le droit de supprimer une ref
		$ACL = new ACL($_SESSION['user']);				// DROIT ACL UI root dev supervisor du projet
		$btnDelete = '<div class="divBtnDelBank hide">
						<div class="inline bot ui-corner-all fondPage pad3">'.$i.'</div>';
		if ($ACL->check('VIEW_BANK_BTN_DEL')) {
			$btnDelete .= '<button class="btnDelBank" idThumb="'.$id_thumb.'" imgName="'.$value.'">
							<span class="ui-icon ui-icon-trash"></span>
						</button>';
		}
		$btnDelete .= '</div>';

		// Si c'est un fichier de type image
		if (preg_match('/image/i', $mimeTypeFile)) {
			if(!file_exists(INSTALL_PATH.$src_thumb)){
				create_bankProj_thumb($dirBank, $value);			// Si le thumb n'existe pas, on le crée
				$src_thumb = FOLDER_GFX.'novignette/novignette_point.png';
			}
			$gallery .= '<div class="inline mid marge10l margeTop10 bankItem" id="'.$id_thumb.'">
					'.$btnDelete.'
					<a class="fancybox-bank" rel="bank" href="'.$fileBank.'" title="'.$value.'">
						<img src="'.$src_thumb.'" alt="'.$value.'" />
					</a>
				  </div>';
		}
		// Si c'est un fichier de type vidéo
		elseif (preg_match('/video/i', $mimeTypeFile)) {
			$movie = $dirBank."/".$value;
			if(!file_exists(INSTALL_PATH.$src_vthumb)){
				create_bankProj_thumb($dirBank, $value);			// Si le thumb n'existe pas, on le crée
				$src_vthumb = FOLDER_GFX.'novignette/novignette_video.png';
			}
			$gallery .= '<div class="inline mid marge10l margeTop10 bankItemVideo" id="'.$id_thumb.'">
					'.$btnDelete.'
					<a class="fancybox-bank" href="#video'.$i.'">
						<img width="80px" src="'.$src_vthumb.'" alt="'.$value.'" title="'.$value.'" />
					</a>
					<div class="hide center" id="video'.$i.'">
						<video src="'.$movie.'" width="960" heigth="540" preload="none" controls="controls" loop="loop" poster="'.$src_vthumb.'" /><br />
						'.$value.'
					</div>
				</div>';
		}
		// Si c'est un fichier de type audio
		elseif (preg_match('/audio/i', $mimeTypeFile)) {
			$gallery .= '<div class="inline mid center marge10l margeTop10 bankItemOther" id="'.$id_thumb.'">
					'.$btnDelete.'
					<a class="fancybox-bank" href="#audio'.$i.'">
						<img src="gfx/icones/audio.png" height="40" /><br />
						'.$value.'
					</a>
					<div class="hide center pad20" id="audio'.$i.'">
						<img src="gfx/icones/audio.png" /><br />
						'.$value.'<br /><br />
						<audio src="'.$fileBank.'" width="500" preload="none" controls="controls" loop="loop" />
					</div>
				</div>';
		}
		// Si c'est un fichier de type PDF
		elseif (preg_match('/PDF/i', $mimeTypeFile)) {
			$gallery .= '<div class="inline mid center marge10l margeTop10 bankItemOther" id="'.$id_thumb.'">
					'.$btnDelete.'
					<a href="'.$fileBank.'" target="_blank">
						<img src="gfx/icones/pdf_file.png" height="40" /><br />
						'.$value.'
					</a>
				</div>';
		}
		// Si c'est un fichier de type archive
		elseif (preg_match('/zip/i', $mimeTypeFile)) {
			$gallery .= '<div class="inline mid center marge10l margeTop10 bankItemZip" id="'.$id_thumb.'">
					'.$btnDelete.'
					<a href="'.$fileBank.'" target="_blank">
						<img src="gfx/icones/archive.png" height="40" /><br />
						'.$value.'
					</a>
				</div>';
		}
		// Si c'est un autre fichier quelconque
		else {
			$gallery .= '<div class="inline mid center marge10l margeTop10 bankItemOther" id="'.$id_thumb.'">
					'.$btnDelete.'
					<a href="'.$fileBank.'" target="_blank">
						<img src="gfx/icones/fichier.png" height="40" /><br />
						'.$value.'
					</a>
				</div>';
		}
		$i--;
	}
}
catch (Exception $e) { die($e->getMessage()); }

echo $gallery;

?>

<div class="hide" id="lastThumbID"><?php echo $i; ?></div>

<script>
	var folder = "<?php echo $dirBank; ?>";

	$(function(){
		$('.btnDelBank').button();
		$('.fancybox-bank').fancybox();

        // Hover des fichiers pour numéro & bouton delete
		$('.bankItem, .bankItemVideo, .bankItemZip, .bankItemOther').hover(
			function(){ $(this).children('.divBtnDelBank').show(); },
			function(){ $(this).children('.divBtnDelBank').hide(); }
		);

		// Suppression d'item bank
		$('.btnDelBank').click(function(){
			var img = $(this).attr('imgName');
			var idT = $(this).attr('idThumb');
			if (!confirm('Supprimer '+img+' ?')) return;
			var ajaxReq = 'action=deleteBankRef&directory='+folder+'&filename='+img+'&idThumb='+idT;
			$('#retourAjax').html('');
			AjaxJson(ajaxReq, 'admin/admin_banks_actions', retourAjaxBank, 'remove');
		});
	});
</script>