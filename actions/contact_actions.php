<?php
@session_start();
@define("INSTALL_PATH", substr( dirname(__FILE__), 0, -7) );
require_once('../config/common.inc');
require_once('../config/config_mail.inc');

$retour['error'] = 'error';
$retour['message'] = 'action undefined';


$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=utf-8\r\n";


if (isset($_POST[@$_SESSION['qaptcha_key']]) && empty($_POST[@$_SESSION['qaptcha_key']]) ) {
	$retour['message'] = 'Captcha not valid.';
	if (isset($_POST['emailSender']) && isset($_POST['messageSender'])) {
		$fromForm = $_POST['from'];
		$host = $_SERVER['HTTP_HOST'];
		$MailExp  = $_POST['emailSender'];
		$MessageExp = nl2br(@$_POST['messageSender']);

		$headers .= "From: $MailExp";

		$MessageExp = stripslashes($MessageExp);
		$DateMail = date('d/m/Y');

		$subject = "SaAM : Message from $fromForm@$host";

		$mail_text='
		<body>
			<font color="#aaa">
				Nouveau message depuis le SaAM du serveur <b>'.$host.'</b>, formulaire <b>"'.$fromForm.'"</b>, le <b>'.$DateMail.'</b>
				<br /><br />
				Email de l\'expéditeur : <font color="#000000"><b>'.$MailExp.'</b></font>
				<br /><br />
				---------------------------------------------------------<br />
				<font color="#000000"><b>'.$MessageExp.'</b></font><br />
				---------------------------------------------------------<br />
				<br />
				++ <i>le SaAMbot</i>
			</font>
		</body>';

		if (mail(ROOTS_MAILS, $subject, $mail_text, $headers)) {
			$retour['error'] = 'OK';
			$retour['message'] = 'email sent.';
		}
		else $retour['message'] = 'Unable to send email.';
	}
	else {
		$retour['message'] = 'Missing some information.';
	}
}
elseif (isset($_POST['action'])) {
	extract($_POST);

	if ($action == 'newBug') {
		$headers .= "From: ".SAAM_MAILBOT;
		$subject = "SaAM : Nouveau BUG signalé";
		$mail_text='
			Yop mes ptits devs...<br><br>
			Désolé, mais un <b>bug</b> vient d\'être signalé <b>par '.$userPseudo.'</b> dans le bugHunter du SaAM:
			<br><br>
			<li>emplacement : <b>'.stripslashes($emplBug).'</b></li>
			<li>description : <b>'.nl2br(stripslashes($descrBug)).'</b></li>
			<br><br><br>
			++ ! <i>le SaAMbot</i>';

		// Suppression d'éventuels fichiers de screenshots (dossier temp/)
		$tempfiles = glob(INSTALL_PATH.'temp/uploads/bughunter/*.{jpg,JPG,jpeg,JPEG,png,PNG,gif,GIF}', GLOB_BRACE);
		if (is_array($tempfiles)) {
			foreach($tempfiles as $file)
				@unlink($file);
		}

		if (mail(ROOTS_MAILS, $subject, $mail_text, $headers)) {
			$retour['error'] = 'OK';
			$retour['message'] = 'The bug has been added to bugs list.';
		}
		else $retour['message'] = 'Unable to send email to devs.';
	}
}
else {
	$retour['message'] = 'Captcha have expired... Please try again.';
}


echo json_encode($retour);

?>
