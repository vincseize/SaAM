<?php
session_start();
	
if (isset($_POST['action']) && isset($_POST['qaptcha_key'])) {
	$_SESSION['qaptcha_key'] = false;	
	
	if (htmlentities($_POST['action'], ENT_QUOTES, 'UTF-8') == 'qaptcha') {
		$_SESSION['qaptcha_key'] = $_POST['qaptcha_key'];
		if ($_POST['userMail'] != '' && $_POST['userMail'] != 'Your email' && $_POST['userMess'] != '' && $_POST['userMess'] != 'Your message' && validEmail($_POST['userMail']))
			$aResponse['error'] = false;
		else $aResponse['error'] = true;
	}
	else $aResponse['error'] = true;
}
else $aResponse['error'] = true;

echo json_encode($aResponse);




// Fonction de check si un email donné est valide
function validEmail ($email) {								// @TODO : faire un vrai check (pas internet donc les regexp... lol)
	if (preg_match('/@/i', $email))
		return true;
	else return false;
}
?>