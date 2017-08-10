<?php
session_start();
define("INSTALL_PATH", substr( dirname(__FILE__), 0, -5) );

// POST avec MD5 pour sécu
if (!isset($_POST['authSendmail']) || $_POST['authSendmail'] !== md5('Si7uVeuxmP4rler3nv01es-M0i128Faxx')) {
	echo date('d/m/Y - H:i:s'). ' - ';
	die("Access Denied.\n");
}

require_once (INSTALL_PATH.'inc/initInclude.php');
require_once ('config_mail.inc');
require_once ('common.inc');
require_once ('autoload.php');
require_once ('PDOinit.php');
require_once ('dates.php');
require_once ('vignettes_fcts.php');
require_once ('url_fcts.php');

$date = date('d M Y', time() - 24 * 3600);
$dateTime = date('d/m/Y - H:i:s');

$l = new Liste();
$l->addFiltreRaw(Dailies::DAILIES_DATE, '>=', 'NOW() - INTERVAL 24 HOUR');
$l->getListe(TABLE_DAILIES, '*', Dailies::DAILIES_DATE, 'DESC');
$dailies = $l->simplifyList();
if ($dailies == false) die('No dailies to send.');
$l->resetFiltre();
$l->addFiltre(Projects::PROJECT_DELETED, '=', '0');
$l->addFiltre(Projects::PROJECT_ARCHIVE, '=', '0');
$l->addFiltre(Projects::PROJECT_HIDE,    '=', '0');
$l->getListe(TABLE_PROJECTS);
$projects = $l->simplifyList();

$hostNameArr = explode('.', $_SERVER['HTTP_HOST']);
$whichSaAM = strtoupper($hostNameArr[0]);

$retour = "\n------------- $dateTime -----------------\n";

$nbMailSent = 0;
foreach ($projects as $pID => $proj) {
	$mails = Users::getUsersMails($pID);
	if ($mails == false) continue;
	$nbDailies = 0;
	$body = '<html>
	<body style="background-color: #333; color: #ccc; padding: 5px;">
		<h3>Last <a style="color:#006E9E; text-decoration:none; outline: none;" href="'.getSaamURL().'">SaAM '.$whichSaAM.'</a> dailies <span style="font-size:0.8em; color:#aaa;">('.$date.')</span></h3>';
	$retour .= "Check dailies of '".$proj['title']."'...\n";
	foreach ($dailies as $daily) {
		if ($daily[Dailies::DAILIES_PROJECT_ID] != $proj[Projects::PROJECT_ID_PROJECT]) continue;
		$nbDailies += 1;
		$dailyFormated = Dailies::getFormatedDaily($daily);
		$body .= '
		<div style="background-color: #555; margin: 5px 0px;">
			<div style="float: left; margin: 5px 10px 5px 5px;">
				<img src="'. preg_replace('/ /', '%20',$dailyFormated['vignette']) .'" height="75" width="133" />
			</div>
			<div style="float: right; margin: 7px 10px;">
				'. strtoupper($dailyFormated['project']). ' | ' .$dailyFormated['group'] .'
			</div>
			<div style="padding: 3px;">
				<p style="background-color: #333; margin: 2px 0px; padding: 2px 0px;">
					<span style="color: #999;">'. $dailyFormated['date'] .' | by</span> '. $dailyFormated['user'] .'
				</p>
				<p style="margin: 3px 0px;">'. $dailyFormated['message'] .'</p>
				<p style="background-color:#4b4b4b; color:#fff; padding: 2px 5px; margin: 3px 0px;">'. $dailyFormated['details'] .'</p>
			</div>
			<div style="clear: both;"></div>
		</div>';
	}
	if ($nbDailies == 0) {
		$retour .= "No daily.\n";
		continue;
	}
	$body .= '
	</body>
	</html>';

	$to = implode(', ', $mails);
	$retour .= "$nbDailies dailies found. Sending mail to: $to.\n";

	$headers  = "MIME-Version: 1.0\r\n";
	$headers .= "From: ".SAAM_MAILBOT."\r\n";
	$headers .= "Content-type: text/html; charset=utf-8";

	$subject = 'SaAM DAILY: '.$nbDailies . ' new event'. (($nbDailies > 1) ? 's' : '').' in '.strtoupper($proj['title']).'! ';
//	echo $body;

	foreach($mails as $toSolo) {
		if (mail($toSolo, $subject, $body, $headers))
			$nbMailSent += 1;
	}
}

$retour .= "DONE. $nbMailSent mail(s) sent\n";

//echo nl2br($retour);	// for debug (affichage in browser)
echo $retour;