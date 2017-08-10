<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC'].'/checkConnect.php' );
	require_once('directories.php');

	// OBLIGATOIRE, id du projet à charger
	if (isset($_POST['projectID']))
		$idProj = $_POST['projectID'];
	else die('Pas de projet à charger...');

    $p = new Projects($idProj);
    $projInfos = $p->getProjectInfos();
	$dirProj   = $p->getDirProject();
	$dirBank   = FOLDER_DATA_PROJ.$dirProj.'/bank';
    $titleProj = $projInfos[Projects::PROJECT_TITLE];

	// list bank folders
	$bankFolders = listDirBank_Project($idProj, $titleProj);

	// GET SIZE OF PROJECT
	$projectSize = get_project_size($idProj);
	$freeSpace = round(($_SESSION['CONFIG']['projects_size'] - $projectSize[3]) * 1024 ); // Espace libre (en octets)

	$colorEmpty	  = "#999";
	$colorFull	  = "#FECF5B";
?>

<script src="js/blueimp_uploader/jquery.iframe-transport.js"></script>
<script src="js/blueimp_uploader/jquery.fileupload.js"></script>
<script src="js/blueimp_uploader/jquery.fileupload-fp.js"></script>

<script>
	var dir = "<?php echo $dirBank; ?>";
	var projID = <?php echo $idProj; ?>;
	var activeBankFolder = false;
	var nbUploadedFiles = 0; var nbSendFiles = 0;

	$(function() {
		$('.bouton').button();

		// init de la barre de visu espace disque
		$('#diskBar').progressbar({value: <?php echo $projectSize[2]; ?>});

		var view = stageHeight - 85;
		$('#foldersList').slimScroll({
			position: 'left',
			height: view+'px',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		});
		$('#contentSection').slimScroll({
			position: 'right',
			height: view+'px',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		});

		// Hover et click sur dossier bank
		$('.icon-folder').hover(
			function(){ $(this).find('.nbFileFolder').show(); $(this).find('.supprFolder, .renameFolder').show(); },
			function(){ $(this).find('.nbFileFolder').hide(); $(this).find('.supprFolder, .renameFolder').hide(); }
		).click(function(){
			$('.icon-folder').removeClass('icon-folder-open').find('.nameFolder').removeClass('colorHard');
			$(this).addClass('icon-folder-open').find('.nameFolder').addClass('colorHard');
			var folder = $(this).attr('folder');
			activeBankFolder = folder;
			$('#folderMsg').html('<b>'+folder+'/</b>').addClass('colorBtnFake').css('font-size', '2.2em');
			$('#contentSection').load('modals/showBankFolder.php', {idProj: projID, bankFolder: folder});
		});

		// Empêche les drop de fichier dans le browser
		$(document).bind('drop dragover', function (e) {
			e.preventDefault();
		});

		// init de l'uploader
		$('#bank_upload').fileupload({
			url: "actions/upload_bank.php?folderTempProj="+encodeURIComponent('<?php echo $dirProj; ?>'),
			dataType: 'json',
			dropZone: $('#contentSection'),
			sequentialUploads: false,
			drop: function (e, data) {
				nbUploadedFiles = 0;
				nbSendFiles = 0;
				if (activeBankFolder === false) {
					$('#retourAjax').html('You must choose a folder before.').addClass('ui-state-error').show(transition);
					setTimeout(function(){$('#retourAjax').fadeOut(transition*10);},4000);
					return false;
				}
				var strMsgTransfert = 'Sending files <img src="gfx/ajax-loader.gif" />';
				$.each(data.files, function(ix,newFile){
					strMsgTransfert += '<br /><div class="uploadProg mini" filename="'+newFile.name+'"><span class="floatL marge5 colorMid"></span></div>';
					nbSendFiles ++ ;
				});
				$('#retourAjax').append(strMsgTransfert).addClass('ui-state-highlight').show(transition);
			},
			submit: function (e, data) {
				var totalSize = 0;
				$.each(data.originalFiles, function(ix,fichier){
					totalSize += fichier.size;
				});
				if (totalSize >= <?php echo $freeSpace; ?>) {
					$('#retourAjax')
						.html('Your upload is too large for the server. Please check free disk space available.')
						.addClass('ui-state-error')
						.show(transition);
					setTimeout(function(){$('#retourAjax').fadeOut(transition*10);},4000);
					return false;
				}
			},
			done: function (e, data) {
				var retour = data.result;
				nbUploadedFiles += data.result.length;
				if (retour[0].error) {
					$('#retourAjax')
						.html('<span class="colorErreur gras">Failed : '+retour[0].error+'</span>')
						.addClass('ui-state-error')
						.show(transition);
					setTimeout(function(){$('#retourAjax').fadeOut(transition*10);},4000);
				}
				else {
					if(nbSendFiles == nbUploadedFiles) {
						$('#retourAjax').removeClass('ui-state-error').addClass('ui-state-highlight').html('Terminé.').show(transition);
						var ajaxReq = 'action=moveUploadedRef&tempDirProj='+encodeURIComponent('<?php echo $dirProj; ?>')+'&destDir='+dir+'/'+activeBankFolder;
						AjaxJson(ajaxReq, 'bank_actions', retourAjaxBank, 'add');
						setTimeout(function(){$('#retourAjax').fadeOut(transition);},1500);
						nbUploadedFiles = 0;
					}
				}
			}
		});
		// Upload progress bar
		$('#bank_upload').bind('fileuploadprogress', function (e, data) {
			var filename = data.files[0].name;
			var percent =  data.loaded / data.total * 100;
			if (percent < 98) {
				$('#retourAjax').find('.uploadProg[filename="'+filename+'"]')
								.progressbar({value: percent})
								.children('span').html('<b>'+ filename + '</b> : speed : '+Math.round(data.bitrate / 10000)+'Kb/s => '+Math.round(percent)+' %...');
			}
			else {
				$('#retourAjax').find('.uploadProg[filename="'+filename+'"]')
								.progressbar({value: 100})
								.children('span').html('<b>'+ filename + '</b> : DONE. Encoding (if video file)...');
			}
		});

		// Ajout de dossier
		$('#bankAddFolder').click(function(){
			var newFname	= prompt('New folder name:');
			if (!newFname) return;
			var ajaxReq		= 'action=addbankFolder&destDir='+encodeURIComponent(dir)+'&newFolderName='+newFname;
			AjaxJson(ajaxReq, 'admin/admin_banks_actions', retourAjaxMsg, true);
		});

		// Suppression de dossier
		$('.supprFolder').click(function(e){
			e.stopPropagation();
			var folderName	= $(this).attr('name');
			if (!confirm('Delete folder "'+folderName+'/" ?')) return;
			var ajaxReq		= 'action=deleteBankFolder&destDir='+encodeURIComponent(dir)+'&folderName='+folderName;
			AjaxJson(ajaxReq, 'admin/admin_banks_actions', retourAjaxMsg, true);
		});

		// Rename de dossier
		$('.renameFolder').click(function(e){
			e.stopPropagation();
			var folderName  = $(this).attr('name');
			var newFname	= prompt('Rename folder with:', folderName);
			if (!newFname) return;
			var ajaxReq		= 'action=renameBankFolder&destDir='+encodeURIComponent(dir)+'&folderName='+folderName+'&newName='+newFname;
			AjaxJson(ajaxReq, 'admin/admin_banks_actions', retourAjaxMsg, true);
		});

		// Clic sur les filtres
		$('.bankFilter').click(function(){
			var itemClass = $(this).attr('filterType');
			if ($(this).hasClass('ui-state-error')) {
				$('.bankItem, .bankItemVideo, .bankItemZip, .bankItemOther').show();
				$('.bankFilter').removeClass('ui-state-error');
			}
			else {
				$('.bankFilter').removeClass('ui-state-error');
				$(this).addClass('ui-state-error');
				$('.bankItem, .bankItemVideo, .bankItemZip, .bankItemOther').hide();
				$('.'+itemClass).show();
			}
		});

		// Creation de planche contact pour le dossier en cours
		$('#bankCS').click(function(){
			if (activeBankFolder === false) {
				retourAjaxMsg({error:'error', message:'Create contact sheet: You must choose a folder!'});
				return false;
			}
			var params = 'proj_ID='+projID+'&folderPath=<?php echo urlencode($dirBank); ?>/'+activeBankFolder+'/';
			window.open('modals/sheets/folder_sheet.php?'+params, "ContactSheet", "menubar=no, scrollbars=yes, width=1024");
		});

		// Download de dossier en ZIP
		$('#bankDL').click(function(){
			if (activeBankFolder === false) {
				retourAjaxMsg({error:'error', message:'Download zip: You must choose a folder!'});
				return false;
			}
			var ajaxReq	= 'action=zipDLbankFolder&projTitle=<?php echo urlencode($titleProj); ?>&bankDir='+encodeURIComponent(dir)+'&folder='+activeBankFolder;
			AjaxJson(ajaxReq, 'bank_actions', retourAjaxMsg);
		});

	});

	// message de retour
	function retourAjaxBank (datas, type) {
		if (datas.error == 'OK') {
			$('#retourAjax').append((datas.message)+'<br />').addClass('ui-state-highlight').show(transition);
			var oldNbFiles = parseInt($('.icon-folder[folder="'+datas.folder+'"]').find('.nbFileFolder').html());
			if (type=='remove') {
				$('.icon-folder[folder="'+datas.folder+'"]').find('.nbFileFolder').html(''+(oldNbFiles - 1));
				$('#'+datas.img).remove();
			}
			else if (type == 'add') {
				if (datas.imgs == undefined) return false;
				var nextThumbID = parseInt($('#lastThumbID').html());
				var section = 'imagesSection';
				var newNbFiles = oldNbFiles;
				$.each(datas.imgs, function(ix, image) {
					if (isNaN(nextThumbID)) return true;
					nextThumbID ++;
					newNbFiles++;
					var content = '<div class="inline mid center marge10l margeTop10 bankItem" id="'+nextThumbID+'">'
									+'<a class="fancybox" rel="bank" href="'+datas.dir + image+'"><img src="'+datas.dir +'thumbs/thumb_'+ image+'" alt="NEW" style="max-width:80px; max-height:80px;" /></a>'
								 +'</div>';
					if (image == 'other') {
						content = '<div class="inline mid center doigt marge10l margeTop10 bankItemVideo" id="'+nextThumbID+'" title="click to refresh the page">'
									+'<img src="gfx/icones/archive.png" height="40" onClick="window.location=\'index.php\'" /><br />'
								 +'</div>';
					}
					$('#contentSection').prepend(content);
				});
				$('.icon-folder[folder="'+datas.folder+'"]').find('.nbFileFolder').html(''+(newNbFiles));
				$('#'+section).prepend('<div class="center big colorDark">Last uploaded</div>');
			}
			setTimeout(function(){$('#retourAjax').fadeOut(transition);}, 2000);
		}
		else {
			$('#retourAjax').html('<b>'+datas.message+'</b>').addClass('ui-state-error').show(transition);
			setTimeout(function(){$('#retourAjax').fadeOut(transition*10);}, 4000);
		}
	}
</script>

<div class="stageContent pad5">
	<div class="ui-corner-all" id="retourAjax"></div>

	<div class="colorSoft" id="deptNameBGBank">BANK</div>

	<div class="inline mid mini">
		<div class="w300" percent="<?php echo $projectSize[2]; ?>" id="diskBar" title="Free space : <?php echo round($freeSpace / 1024 / 1024) . ' MB'; ?>">
			<span class="floatL marge5 colorMid"><?php echo $projectSize[0]." / ".(number_format($projectSize[1]/1024, 1))." GB (".$projectSize[2]." %)"; ?></span>
		</div>
	</div>
	<div class="inline mid petit ui-state-disabled">
		<?php $extsAllowed = preg_replace('/\(|\)|\$/', '', BANK_EXTENSIONS_ALLOWED);
			echo L_FILETYPE_ALLOW.' : '.preg_replace('/\|/', ', ', $extsAllowed); ?>
	</div>


	<div id="bank_project">
		<input id="bank_upload" type="file" name="files[]" style="display:none;" multiple />

		<div class="inline top margeTop10 w300 bordBankSection" id="folderSection">
			<?php if ($_SESSION['user']->isSupervisor()): ?>
			<div class="floatR margeTop5 icon-folder-add" title="ADD a folder" id="bankAddFolder"></div>
			<?php endif; ?>
			<div id="foldersList">
			<?php foreach ($bankFolders as $folder):
					$countFolder = count(glob(INSTALL_PATH.$dirBank.'/'.$folder.'/*')) -1;
					$countFolder = ($countFolder < 0) ? 0 : $countFolder;
					$colorDir	 = ($countFolder > 0) ? $colorFull : $colorEmpty; ?>
					<div class="icon-folder marge5bot" title="Open" folder="<?php echo $folder; ?>">
						<?php if ($countFolder == 0 && $_SESSION['user']->isSupervisor()): ?>
						<div class="supprFolder hide" title="Delete folder" name="<?php echo $folder; ?>">
							<button class="bouton"><span class="ui-icon ui-icon-trash"></span></button>
						</div>
						<?php endif; ?>
						<?php if ($_SESSION['user']->isSupervisor()): ?>
						<div class="renameFolder hide" title="Rename folder" name="<?php echo $folder; ?>">
							<button class="bouton"><span class="ui-icon ui-icon-pencil"></span></button>
						</div>
						<?php endif; ?>
						<div class="nbFileFolder hide" title="files"><?php echo $countFolder; ?></div>
						<div class="nameFolder" style="margin-top:46px; color:<?php echo $colorDir; ?> !important;"><?php echo $folder; ?></div>
					</div>
			<?php endforeach; ?>
			</div>
		</div><div class="inline top margeTop10" style="width:calc(100% - 310px);">
			<div class="fondSect4 pad3 ui-corner-right rightText nano" id="headerBank">
				<div class="floatL colorDiscret terra margeTop5" id="folderMsg">< Choose a folder.</div>
				<button class="bouton ui-state-highlight bankFilter" filterType="bankItem" title="Filter images"><span class="ui-icon ui-icon-image"></span></button> &nbsp;&nbsp;&nbsp;
				<button class="bouton ui-state-highlight bankFilter" filterType="bankItemVideo" title="Filter videos"><span class="ui-icon ui-icon-video"></span></button> &nbsp;&nbsp;&nbsp;
				<button class="bouton ui-state-highlight bankFilter" filterType="bankItemZip" title="Filter zip archives"><span class="ui-icon ui-icon-suitcase"></span></button> &nbsp;&nbsp;&nbsp;
				<button class="bouton ui-state-highlight bankFilter" filterType="bankItemOther" title="Filter other documents"><span class="ui-icon ui-icon-document"></span></button> &nbsp;&nbsp;&nbsp;
				<span class="colorDiscret" style="font-size:4em;">|</span> &nbsp;&nbsp;&nbsp;
				<button class="bouton" id="bankCS" title="Create contact sheet"><span class="ui-icon ui-icon-clipboard"></span></button> &nbsp;&nbsp;&nbsp;
				<button class="bouton" id="bankDL" title="Download this folder as ZIP archive"><span class="ui-icon ui-icon-suitcase"></span></button> &nbsp;&nbsp;&nbsp;
			</div>
			<div class="padH5" id="contentSection"></div>
		</div>

	</div>

</div>
