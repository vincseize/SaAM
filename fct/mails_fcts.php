<?php

require_once ('config_mail.inc');

$headers  = "MIME-Version: 1.0\r\n";
$headers .= "From: ".SAAM_MAILBOT."\r\n";
$headers .= "Content-type: text/html; charset=utf-8";
global $headers;

// Envoi de mini email d'alerte pour prévenir les devs quand quelqu'un se connecte
function send_alertmailConx($loginConx, $failed) {
	global $headers;
    $local = '';
    if (IS_LOCAL === true)
		$local = ' (local)';
	if (DONT_SENDMAIL_LOCAL === true && IS_LOCAL === true)
		return true;
	if (!empty($loginConx)) {
		if (ALERT_LOGS) {
			$date = date("d/m/Y - H:i");
			$IPconx = $_SERVER['REMOTE_ADDR'];
			$OSconx = $_SERVER['HTTP_USER_AGENT'];
			$logged = ($failed === false) ? 'is connected' : 'tried and failed to connect';
			$to = ALERT_SEND_TO;
			$subject = "$loginConx $logged on SaAM !$local";
			$message = "Hi!<br><br><b>$loginConx</b> $logged on SaAM ($date)<br><br>";
			$message .= "IP : <b>$IPconx</b><br>";
			$message .= "OS : <b>$OSconx</b><br><br>See ya!";
			return mail($to, $subject, $message, $headers);
		}
	}
}
?>
