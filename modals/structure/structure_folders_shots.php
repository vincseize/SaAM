
<script>
	var nbFileUploaded = 0; nbFileUploadedOK = 0;
	var shotInfos = '<?php echo @$idProj.';'.@$titleProj.';'.@$idSeq.';'.@$titleSeq.';'.@$idShot.';'.@$titleShot.';'.@$idDept ?>';
	var activeFolder  = '';

	$(function(){

		var footHeigth = $('#shotViewFooter').height() - 15;
		$('#listFolders').slimScroll({
			position: 'right',
			height: footHeigth+'px',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		});
		$('#listFolderContent').slimScroll({
			position: 'right',
			height: footHeigth+'px',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		});

		// Upload de fichiers dans un dossier de shot
		$('#foldershot_upload').fileupload({
			url: "actions/upload_bankshot.php",
			dataType: 'json',
			dropZone: $('#listFolderContent'),
			sequentialUploads: false,
			drop: function (e, data) {
				$('#listFolderContent').removeClass('fondHigh shadowIn');
				var strMsgTransfert = 'Sending files <img src="gfx/ajax-loader.gif" />';
				$.each(data.files, function(ix,newFile){
					strMsgTransfert += '<br /><div class="uploadProg mini" filename="'+newFile.name+'"><span class="floatL marge5 colorMid"></span></div>';
					nbFileUploaded ++;
				});
				$('#retourAjax')
					.append(strMsgTransfert)
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
					setTimeout(function(){$('#retourAjax').fadeOut(transition*10, function(){$('#retourAjax').html('').removeClass('ui-state-error');});},4000);
				}
				else {
					var rep = $('.icon-folder-open').attr('path');
					$('#listFolderContent').load('actions/display_folder_shot.php', {path: rep}, function(){
						$('.fancybox-bankShot').fancybox();
					});
					nbFileUploadedOK ++;
					if (nbFileUploadedOK == nbFileUploaded) {
						AjaxJson('action=sendMailUpload&nbFiles='+nbFileUploaded+'&shotInfos='+shotInfos+';'+activeFolder, 'admin/admin_folder_shots_actions', retourAjaxStructure);
						nbFileUploadedOK = 0;
					}
				}
			}
		});

		// Upload progress bar
		$('#foldershot_upload').bind('fileuploadprogress', function (e, data) {
			var filename = data.files[0].name;
			var percent =  data.loaded / data.total * 100;
			$('#retourAjax').find('.uploadProg[filename="'+filename+'"]')
							.progressbar({value: percent})
							.children('span').html('<b>'+ filename + '</b> : speed : '+Math.round(data.bitrate / 10000)+'Kb/s => '+Math.round(percent)+' %...');
		});


		// Affichage du nombre de fichier contenu dans les folder_shot
		$('.icon-folder').hover(
			function(){ $(this).find('.nbFileFolder').show(); $(this).find('.supprFolder').show(); },
			function(){ $(this).find('.nbFileFolder').hide(); $(this).find('.supprFolder').hide(); }
		);

		// Clic sur un dossier dans la liste en bas
		$('.icon-folder').click(function(){
			$('.icon-folder').removeClass('icon-folder-open colorActiveFolder');
			var rep = $(this).attr('path');
			$(this).addClass('icon-folder-open colorActiveFolder');
			activeFolder = rep;
			$('#listFolderContent').load('actions/display_folder_shot.php', {path: rep}, function(){
				$('.fancybox-bankShot').fancybox();
			});
			$('#foldershot_upload').fileupload(
				'option',
				'url',
				'actions/upload_bankshot.php?path='+encodeURIComponent(rep)+'&IDshot='+shot_ID
			);
		});

		// Clic sur bouton ajout de dossier
		$('.icon-folder-add').click(function(){
			var newFolderName = prompt('Enter folder name:');
			if (newFolderName.length < 3) { alert('This name is too short. It must be at least 3 characters.'); return; }
			var path = $(this).attr('path') + 'custom/' + newFolderName;
			var ajaxReq = 'action=addShotFolder&IDshot='+shot_ID+'&path='+path;
			AjaxJson(ajaxReq, 'admin/admin_folder_shots_actions', retourAjaxStructure, 'reloadShot');
		});

		// Clic sur suppression de dossier
		$('.supprFolder').click(function(e){
			e.stopPropagation();
			var folderName = $(this).attr('name');
			if (!confirm('Delete folder "/'+folderName+'" ?')) return;
			var path = $(this).parent('.icon-folder').attr('path');
			var ajaxReq = 'action=deleteShotFolder&IDshot='+shot_ID+'&path='+path;
			AjaxJson(ajaxReq, 'admin/admin_folder_shots_actions', retourAjaxStructure, 'reloadShot');
		});

		// Suppression d'un fichier d'un folder shot
		$('#listFolderContent').off('click', '.deleteFileShot');
		$('#listFolderContent').on('click', '.deleteFileShot', function(){
			var filePath = $(this).attr('filePath');
			var fileName = $(this).attr('file');
			if (!confirm('Supprimer '+fileName+' ?')) return;
			var ajaxReq = 'action=deleteShotBankRef&IDshot='+shot_ID+'&filePath='+filePath+fileName;
			AjaxJson(ajaxReq, 'admin/admin_folder_shots_actions', retourAjaxStructure, 'reloadFolderShot');
		});

	});

</script>

<?php
	$dirDataShot = FOLDER_DATA_PROJ.$shot->getDirShot().'/'.$idDept.'/datashot/';
	$customDirs  = glob(INSTALL_PATH.$dirDataShot.'custom/*');
	$colorEmpty	  = "#666";
	$colorFull	  = "#FECF5B";
?>

<div class="inline top tiers bordBankSection">
	<div id="listFolders"><?php
		$countDir = count(glob(INSTALL_PATH.$dirDataShot.'bank/*.*'));
		$colorDir = ($countDir > 0) ? $colorFull : $colorEmpty; ?>
		<div class="floatR icon-folder-add" title="ADD a folder" path="<?php echo $dirDataShot;?>"></div>
		<div class="icon-folder" title="Open" path="<?php echo $dirDataShot.'bank/'; ?>">
			<div class="nbFileFolder hide" title="files"><?php echo $countDir; ?></div>
			<div style="margin-top:46px; color: <?php echo $colorDir; ?>;">Bank Shot</div>
		</div><?php
		$countDir = count(glob(INSTALL_PATH.$dirDataShot.'stills/*.*'));
		$colorDir = ($countDir > 0) ? $colorFull : $colorEmpty; ?>
		<div class="icon-folder" title="Open" path="<?php echo $dirDataShot.'stills/'; ?>">
			<div class="nbFileFolder hide" title="files"><?php echo $countDir; ?></div>
			<div style="margin-top:46px; color: <?php echo $colorDir; ?>;">Work in Progress</div>
		</div>
		<?php
		foreach ($customDirs as $cDir):
			$dirName = basename($cDir);
			$countDir = count(glob($cDir.'/*.*'));
			$colorDir = ($countDir > 0) ? $colorFull : $colorEmpty;
			if (!is_dir($cDir) || $dirName == '.' || $dirName == '..') continue;?>
			<div class="icon-folder" title="Open" path="<?php echo $dirDataShot.'custom/'.$dirName.'/'; ?>">
				<?php if ($countDir == 0): ?>
				<div class="supprFolder hide" title="Delete folder" name="<?php echo $dirName; ?>"><button class="bouton"><span class="ui-icon ui-icon-trash"></span></button></div>
				<?php endif; ?>
				<div class="nbFileFolder hide" title="files"><?php echo $countDir; ?></div>
				<div style="margin-top:46px; color: <?php echo $colorDir; ?>;"><?php echo $dirName; ?></div>
			</div>
		<?php endforeach; ?>
		<p>&nbsp;<br />&nbsp;</p>
	</div>
</div>

<div class="inline top deuxTiers">
	<input id="foldershot_upload" type="file" name="files[]" path="" style="display:none;" multiple />
	<div id="listFolderContent"></div>
</div>