<?php
	@session_start();
	require_once ("../inc/checkConnect.php" );
?>
<script src="js/blueimp_uploader/jquery.iframe-transport.js"></script>
<script src="js/blueimp_uploader/jquery.fileupload.js"></script>
<script src="js/blueimp_uploader/jquery.fileupload-fp.js"></script>

<script>
	$(function(){
		localStorage['activeContent'] = 'bughunter';

		$('#showAddBug').click(function(){
			$('#thanksBug, #errorBug').hide();
			$('#formAddBug').show(transition);
		});

		$('#validAddBug').click(function(){
			var emplBug  = $('#emplBug').val();
			var descrBug = $('#descrBug').val();
			if (emplBug.length < 3){
				$('#validMsg').html('Location is too short. (3 char. min.)').show(); return;
			}
			if (emplBug.length > 90){
				$('#validMsg').html('Location is too long. (90 char. max.)').show(); return;
			}
			if (descrBug.length < 3){
				$('#validMsg').html('Description is too short. (3 char. min.)').show(); return;
			}
			$('#validMsg').hide();
			$('#thanksBug, #errorBug').hide();
			$.post('actions/proxy_bughunter.php', { action:"insert_bug", title:emplBug, description:descrBug, priority:4 }, function(data){
				console.log(data);
				if (!data) return;
				if (data.error == "OK") {
					var ajaxReq = "action=newBug&emplBug="+emplBug+"&descrBug="+descrBug+"&userPseudo=<?php echo $_SESSION['user']->getUserInfos(Users::USERS_PSEUDO); ?>";
					AjaxJson(ajaxReq, "contact_actions", retourAjaxMsg);
					$('#formAddBug').hide(transition);
					$('#thanksBug').show(transition);
					$('#listScreens').html("");
					$('#emplBug').val('');
					$('#descrBug').val('');
				}
				else
					$('#errorBug').html(data.message).show(transition);
			}, "json");
		});

		// Upload de screenshot (bouton)
		$("#screenUploadBtn").click(function(){
			$('#screen_upload').click();
		});

		// Progress bar upload de screenshot
		$('#screen_upload').off('fileuploadprogress');
		$('#screen_upload').on('fileuploadprogress', function (e, data) {
			var filename = data.files[0].name;
			var percent =  data.loaded / data.total * 100;
			$('#retourAjax').find('.uploadProg[filename="'+filename+'"]')
							.progressbar({value: percent})
							.children('span').html('speed : '+Math.round(data.bitrate / 10000)+'Kb/s => '+Math.round(percent)+' %...');
		});

		// Upload de screenshot
		$('#screen_upload').fileupload({
			url: "actions/upload_screenBH.php",
			dataType: 'json',
			sequentialUploads: false,
			start: function(){ $('#validAddBug').addClass('ui-state-disabled'); },
			change: function (e, data) {
				var strMsgTransfert = 'Sending screens...';
				$.each(data.files, function(ix,newFile){
					strMsgTransfert += '<br /><div class="uploadProg mini" filename="'+newFile.name+'"><span class="floatL marge5 colorMid"></span></div>';
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
						.append('<br /><span class="colorErreur gras">Failed : '+retour[0].error+'</span>')
						.addClass('ui-state-error')
						.show(transition);
					setTimeout(function(){$('#retourAjax').fadeOut(transition*10, function(){$('#retourAjax').html('').removeClass('ui-state-error');});},4000);
				}
				else {
					console.log(retour);
					$("#listScreens").append('<img src="temp/uploads/bughunter/'+retour[0].name+'" style="width: 150px;" /> ');
				}
			},
			stop: function(e){
				$('#retourAjax').html("Done ! Screenshots uploaded.<br />You can submit your bug now.");
				$('#validAddBug').removeClass('ui-state-disabled');
				setTimeout(function(){$('#retourAjax').fadeOut(transition*10, function(){$('#retourAjax').html('').removeClass('ui-state-error');});},3000);
			}
		});
	});
</script>

<div class="ui-corner-all" id="retourAjax"></div>

<div class="pageContent">
	<div class="stageContent pad10">
		<div class="inline mid"><img src="gfx/icones/bugs.png" height="80" /></div> <h2 class="inline mid center">BUG Hunter</h2>
		<br />
		<div class="margeTop5 marge15bot gros" style="margin-left: 80px;">
			<a href="http://bughunter.saamanager.net/" target="_blank">
				<button class="bouton"><?php echo L_TRACKBACK_LINK; ?>...</button>
			</a>
			<button class="bouton marge30l" id="showAddBug"><?php echo L_BTN_ADD_BUG; ?></button>
		</div>
		<br />
		<div class="margeTop10 gros hide"  style="margin-left: 80px;" id="formAddBug">
			<div class="inline top">
				<div class="inline top w150">Location (page) :</div>
				<div class="inline top w400"><input type="text" class="fondPage noBorder ui-corner-all pad3" style="width: 100%" id="emplBug" maxlength="90" /></div>
				<br /><br />
				<div class="inline top w150">Detailed description :</div>
				<div class="inline top w400"><textarea class="fondPage noBorder ui-corner-all pad3" style="width: 100%" rows="10" id="descrBug"></textarea></div>
				<br /><br />
				<div class="inline top w150"></div>
				<div class="inline top w400 rightText">
					<span class="ui-state-error ui-corner-all pad3 hide" id="validMsg"></span>
					<button class="bouton" id="validAddBug"><?php echo L_BTN_VALID; ?></button>
				</div>
			</div>
			<div class="inline top" style="margin: 0 20px;">
				<button class="bouton" id="screenUploadBtn">ADD SCREENSHOTS</button>
				<input type="file" name="files[]" class="hide" id="screen_upload" multiple />
				<div class="margeTop10" id="listScreens"></div>
			</div>
		</div>
		<div class="inline enorme ui-state-active ui-corner-all pad5" style="display:none; margin-left: 80px;" id="thanksBug">
			<?php echo L_THANKS_BUG; ?>
		</div>
		<div class="enorme hide colorErrText" style="margin-left: 80px;" id="errorBug"></div>
	</div>
</div>
