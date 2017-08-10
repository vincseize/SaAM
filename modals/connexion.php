<?php

$OS = $_SERVER['HTTP_USER_AGENT'];
$langVersion = 'version ';

if (stripos($OS, "Linux")) {
	$OSshort = "Linux";
	$OStitleCompFF = $langVersion.'>= 14.1';
	$OStitleCompCH = $langVersion.'>= 22.0.1201.0';
	$OStitleCompSA = 'Not Available for linux';
}
elseif (stripos($OS, "Windows")) {
	$OSshort = "Windows";
	$OStitleCompFF = $langVersion.'>= 14.0';
	$OStitleCompCH = $langVersion.'>= 22.0.1201.0';
	$OStitleCompSA = $langVersion.'>= 5.1.7';
}
elseif (stripos($OS, "Mac")) {
	$OSshort = "Mac";
	$OStitleCompFF = 'Not Tested';
	$OStitleCompCH = 'Not Tested';
	$OStitleCompSA = '>= 5.1.7';
}
else {
	$OSshort = "";
	$OStitleCompFF = $langVersion.'>= 4.0';
	$OStitleCompCH = $langVersion.'>= 22.0.1201.0';
	$OStitleCompSA = $langVersion.'>= 5.1.7';
}

########## check sessions folder
$dir_sessions = INSTALL_PATH."sessions";
if(!file_exists($dir_sessions)) {
    @mkdir( $dir_sessions, 0755, true);
    @chmod($dir_sessions, 0755, true);
}

$hostNameArr = explode('.', $host);
$whichSaAM = $hostNameArr[0];

##########  REF OS/BROWSERS

//'Windows 3.11' => 'Win16',
//'Windows 95' => '(Windows 95)|(Win95)|(Windows_95)',
//'Windows 98' => '(Windows 98)|(Win98)',
//'Windows 2000' => '(Windows NT 5.0)|(Windows 2000)',
//'Windows XP' => '(Windows NT 5.1)|(Windows XP)',
//'Windows Server 2003' => '(Windows NT 5.2)',
//'Windows Vista' => '(Windows NT 6.0)',
//'Windows 7' => '(Windows NT 7.0)',
//'Windows NT 4.0' => '(Windows NT 4.0)|(WinNT4.0)|(WinNT)|(Windows NT)',
//'Windows ME' => 'Windows ME',
//'Open BSD' => 'OpenBSD',
//'Sun OS' => 'SunOS',
//'Linux' => '(Linux)|(X11)',
//'Mac OS' => '(Mac_PowerPC)|(Macintosh)',
//'QNX' => 'QNX',
//'BeOS' => 'BeOS',
//'OS/2' => 'OS/2'

?>

<style>
	#menuConx			 { position:absolute; top: 15px; left: 20px; font-size: 1.1em; }
	#versionCountdown	 { position:absolute; top: 10px; right: 10px; }
	#browsers_OS		 { position:absolute; bottom: 5px; left: 10px;opacity: 0.12; filter: alpha(opacity=12); }
	#browsers_comp		 { position:absolute; bottom: 18px; left: 10px; }
	.icone_browser		 { opacity: 0.16; filter: alpha(opacity=16);  }
	.icone_browser:hover { opacity: 1;    filter: alpha(opacity=100); }

	#social_network		 { position:absolute; bottom: 10px; right: 10px; }

	a:link, a:visited, a:active { color: #bbb; }
	a:hover						{ color: #fff; text-decoration: underline; }
</style>

<script src="js/QapTcha.jquery.js">// JQUERY plugin : Captcha</script>

<script>
	$(function(){

		// Annulation des refresh du head_html.php pour éviter de surcharger la mémoire
		clearInterval(refreshWip);
		clearInterval(refreshMyShots);
		clearInterval(refreshMessages);

		// Vidage du localStorage si besoin
		<?php if ($resetLocalStorage) : ?>
			localStorage.clear();
			console.log('LOCALSTORAGE RESETED !! Cache version = <?php echo $cache_version; ?>');
		<?php endif; ?>

		// TEST acceptation des cookies
		var cookieEnabled = (navigator.cookieEnabled) ? true : false;
		if (typeof navigator.cookieEnabled == "undefined" && !cookieEnabled) {
			document.cookie="testcookie";
			cookieEnabled = (document.cookie.indexOf("testcookie") != -1) ? true : false;
		}
		if (!cookieEnabled) {
			$('input').remove();
			$('#logoConx').append('<div class="margeTop10 enorme red center"><p>WARNING !!</p></div>'
								+ '<div class="margeTop10 gros red center">'
									+ 'You can\'t use SaAM without accepting cookies.<br />'
									+ 'Please enable cookies in your browser settings, and reload the page.<br /><br />'
									+ '<a href="index.php">-- RELOAD --</a>'
								+ '</div>');
		}

		// Bouton de contact
		$('#homeContactBtn').toggle(
			function() { $('#homeContactDiv').load('modals/contact.php').show(transition); },
			function() { $('#homeContactDiv').hide(transition); }
		);

		$('input[name="login"]').focus();
	});
</script>


<div class="ui-widget">

	<div class="w100p margeTop5" id="menuConx">
		<span class="inline mid ui-icon ui-icon-link"></span> <a href="<?php echo URL_TRACKBACKS;?>" target="_blank">Issues tracker</a>
		<br />
		<br />
		<span class="inline mid ui-icon ui-icon-contact"></span> <a href="#" id="homeContactBtn">Contact</a>
		<br />
		<div class="pad5 ui-corner-all petit hide" id="homeContactDiv"></div>
	</div>

	<?php include('modals/countdown.php'); ?>

</div>


<div class="ui-widget" id="conxPage">

	<div class="leftText" id="logoConx">
		<img src="gfx/logoBig.png" alt="SAAM"/>
		<div class="inline bot marge15bot colorSoft"><a href="http://saamanager.net/logs.php" target="blank">release <?php echo SAAM_VERSION ?></a></div>
		<?php if (@$_SESSION['isDemoSaAM']) : ?>
			<div class="floatR ui-state-error ui-corner-all gros gras pad3">DEMO</div>
		<?php else: ?>
			<div class="floatR gros gras pad3 colorSoft"><?php echo $whichSaAM; ?>&nbsp;</div>
		<?php endif; ?>
		<div class="colorActiveFolder marge10l gros" style="margin-top:-16px;font-weight: bold;">Shots and Assets Management</div>
	</div>

	<br /><br />

	<div class="center gros margeTop10">
		<form action="index.php" method="post">
			<input type="hidden" name="conx" value="SaAM" />
			<input class="noBorder pad3 ui-corner-all fondSect2" type="text" name="login" placeholder="login" <?php echo (@$_SESSION['isDemoSaAM']) ? 'value="demo"' : ''; ?> size="20" />
			<input class="noBorder pad3 ui-corner-all fondSect2" type="password" name="password" placeholder="password" <?php echo (@$_SESSION['isDemoSaAM']) ? 'value="demo"' : ''; ?> size="20" />
			<span class="mini"><input type="submit" class="bouton boutonMenu" value="GO" /></span>
		</form>
	</div>

	<?php if (@$_SESSION['isDemoSaAM']) : ?>
		<div class="center margeTop10 colorDiscret">
			Demo user :
			<pre class="big"><?php echo "login : demo\npassw : demo"; ?></pre>
			enjoy !
		</div>
	<?php endif; ?>

	<br /><br />

	<div class="center gros"><?php
		if ($errAuth === true): ?>
			<script>
				$('input[name="login"]').val('<?php echo @$lastLoginUsed; ?>');
				$('input[name="password"]').focus().focus();
			</script>
			<div class="inline ui-state-error ui-corner-all pad3 gros center red">
				<b><?php echo $errAuthMsg; ?></b>
			</div><?php
		endif; ?>
	</div>

</div>

<div id='browsers_OS'><?php echo $OSshort; ?> browsers compatibility</div>

<div id='browsers_comp'>
	<span class='icone_browser'><img src="gfx/icones/firefox.png" title="<?php echo $OStitleCompFF; ?>" /></span>
	<span class='icone_browser'><img src="gfx/icones/chrome.png"  title="<?php echo $OStitleCompCH; ?>" /></span>
	<span class='icone_browser'><img src="gfx/icones/safari.png"  title="<?php echo $OStitleCompSA; ?>" /></span>
</div>
