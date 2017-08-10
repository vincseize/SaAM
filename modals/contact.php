<link type="text/css" href="css/QapTcha.jquery.css" rel="stylesheet" />

<script>
	mailOK	  = false;
	messageOK = false;

	$(function(){
		$('.bouton').button();

		// INIT du Captcha
		$('.QapTcha').QapTcha({
			autoSubmit : false,
			autoRevert : true,
			PHPfile : 'actions/qapTcha.php',
			txtLock : 'Slide to unlock',
			txtLoss : 'Missing some information !',
			txtUnlock : ''
		});

		// Check infos message de contact
		$('.contactField')
			.focus(function(){
				if ($(this).val()=='Your email' || $(this).val()=='Your message') {
					$(this).val('').removeClass('colorMid');
				}
			})
			.keyup(function(){
				var val = $(this).val();
				$(this).parent().next('div').addClass('ui-state-error-text').children('span').removeClass('ui-icon-check');
				if (val.length >= 5) {
					if ($(this).attr('id')=='contactMailSender') {
						if (verifyEmail(val)) {
							$(this).parent().next('div').removeClass('ui-state-error-text').children('span').addClass('ui-icon-check');
							mailOK = true;
						}
					}
					else {
						$(this).parent().next('div').removeClass('ui-state-error-text').children('span').addClass('ui-icon-check');
						messageOK = true;
					}
				}
			});

		// Envoi du message de contact
		$('#contactSendBtn').click(function(e){
			e.preventDefault();
			var valsToPost = {from: 'home'};
			$('.contactField').each(function(){
				valsToPost[$(this).attr('name')] = $(this).val();
			});
			$.post(
				'actions/contact_actions.php',
				valsToPost,
				function(data) {
					if (data.error == 'OK')
						$('#homeContactDiv').html('<div class="marge10l gros">Your message has been sent!<br />Thank you for your interrest.</div>');
					else
						$('#homeContactDiv').append('<div class="marge10l colorErreur">An error occured while sending message! <br />'+data.message+'</div>');
				},
				'json'
			);
		});

	});

</script>

<div class="margeTop10 marge15bot marge10l">
	<a href="http://webchat.freenode.net?channels=saamanager&uio=OT10cnVlJjEwPXRydWUmMTE9MTEz95" target="_blank">
		<button class="bouton" title="Open new tab, to chat with devs on channel #saamanager (Freenode)">Open chat IRC <small>(#saamanager)</small></button>
	</a>
</div>

<form method="post">
	<div class="marge10l">
		<div class="inline mid">
			<input type="text" class="noBorder ui-corner-top fondSect2 w200 pad3 colorMid contactField" value="Your email" name="emailSender" id="contactMailSender" />
		</div>
		<div class="inline mid ui-state-error-text"><span class="ui-icon ui-icon-notice"></span></div>
	</div>
	<div class="marge10l">
		<div class="inline top">
			<textarea class="noBorder ui-corner-bottom fondSect2 w200 pad3 colorMid contactField" rows="10" name="messageSender" id="contactMessageSender">Your message</textarea>
		</div>
		<div class="inline top ui-state-error-text"><span class="ui-icon ui-icon-notice"></span></div>
	</div>
	<div class="marge10l">
		<div class="inline top w200">
			<div class="QapTcha"></div>
		</div>
		<div class="inline top marge10l margeTop10">
			<button type="submit" class="bouton hide" id="contactSendBtn">Send</button>
		</div>
	</div>
</form>
