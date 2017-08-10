<?php
@session_start();

/*********************************  LOGS ************************************/

/**
 * Ajout de ligne dans les fichiers de LOG connexion utilisateurs
 *
 * @param BOOL $failed TRUE si la connexion a échoué, FALSE si elle a réussi
 * @param STRING $login Le login de l'utilisateur qui se connecte
 * @param STRING $ip L'adresse IP du client
 * @param STRING $agent La valeur User_Agent du client
 *
 */
function add_conx_log($failed, $login, $ip, $agent){
	$logFile = ($failed) ? 'failed.log' : 'success.log';
	$logPath = INSTALL_PATH.FOLDER_DATA_USER.'logs/';
	if (!is_dir($logPath))
		mkdir($logPath, 0755, true);
	if (filesize($logPath.$logFile) >= 110000) {
		try{
			$pathName = preg_replace('/\.log$/', '', $logPath.$logFile);
			$nbA = count(glob($pathName.'*'));
			$pathName .= (string)$nbA;
			$a = new PharData($pathName.'.tar');
			$dateComp = date('Y_m_d');
			$a->addFromString(preg_replace('/\.log$/', '-'.$dateComp.'.log', $logFile), file_get_contents($logPath.$logFile));
			$a->compress(Phar::GZ);
			@unlink($pathName.'.tar');
			@unlink($logPath.$logFile);
		}
		catch (Exception $e) { /* SILENT ERROR */ }
	}
	$logLine = time()." | $login | $ip | $agent\n";
	file_put_contents($logPath.$logFile, $logLine, FILE_APPEND);
}
?>
