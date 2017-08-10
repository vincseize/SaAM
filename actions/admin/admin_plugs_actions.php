<?php
@session_start(); // 2 lignes à placer toujours en haut du code des pages
require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
if (!$_SESSION['user']->isMagic()) die('{"error":"error", "message":"admin actions : Access denied."}');

extract($_POST);

$retour['error'] = 'error';
$retour['message'] = 'action undefined';

try {

	// Enregistrement de l'ativation ou désactivation du plugin dans la config BDD
	if ($action == 'disenplug') {
		if (!isset($plugName))
			throw new Exception("disenplug action : missing plugin name!");
		if (!isset($state))
			throw new Exception("disenplug action : missing plugin state!");
		$stateBool = ($state == 'enable');
		$newPlugStates = $_SESSION['CONFIG']['plugins_enabled'];
		$newPlugStates[$plugName] = $stateBool;

		$saamInf = new Infos(TABLE_CONFIG);
		$saamInf->loadInfos('version', SAAM_VERSION);
		$saamInf->addInfo('plugins_enabled', json_encode($newPlugStates));
		$saamInf->save();
		$retour['error'] = 'OK';
		$retour['message'] = "Plugin '$plugName' is now $state"."d.";
	}
}
catch (Exception $e) {
	$retour['message'] = $e->getMessage();
}

echo json_encode($retour);

?>
