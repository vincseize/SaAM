<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

	require_once('directories.php');

extract($_POST);

$retour['error'] = 'error';
$retour['message'] = 'action undefined';

try {
	$ACL = new ACL($_SESSION['user']);

	// Rapatriement de la ref uploadée
	if ($action == 'moveUploadedRef') {
		if (!$ACL->check('BANK_UPLOAD')) { $retour['message'] = 'Access denied!'; die(json_encode($retour)); }
		if (empty($destDir)) { $retour['message'] = 'moveUploadedRef action : missing $destDir !'; die(json_encode($retour)); }
		if (empty($tempDirProj)) { $retour['message'] = 'moveUploadedRef action : missing $tempDirProj !'; die(json_encode($retour)); }

		$thumbDir = $destDir.'/thumbs/';
		$tempFiles = glob(INSTALL_PATH.'temp/uploads/banks/'.$tempDirProj.'/*');
		$retour['message'] = '';
		if (is_array($tempFiles)) {
			$ir = 0;
			foreach($tempFiles as $tempFile) {
				if (is_file($tempFile)) {
					if (!@copy($tempFile, INSTALL_PATH.$destDir.'/'.basename($tempFile)))
						{ $retour['message'] .= 'unable to copy '.$tempFile.' to '.$destDir.'/'.basename($tempFile).' ! '; }
					else {
						if (preg_match('/(jpg|png|jpeg|gif)$/i', basename($tempFile))) {
							create_bankProj_thumb($destDir, basename($tempFile));
							$retour['imgs'][] = basename($tempFile);
						}
						elseif (preg_match('/(ogg|ogv|avi|quicktime|mp4)$/i', basename($tempFile))) {
							create_bankProj_thumb($destDir, basename($tempFile));
							$retour['imgs'][] = '/thumbs/vthumb_'.basename($tempFile);
						}
						else $retour['imgs'][] = 'other';
						$retour['dir'] = $destDir.'/';
					}
					if (!unlink($tempFile))
						{ $retour['message'] .= 'unable to delete temp file !';  }
					$ir++;
				}
			}
			$retour['error'] = 'OK';
			$retour['folder'] = basename($destDir);
			// envoi de mail alert upload
			if ($ir > 0) {
				$project = explode('_', $tempDirProj);
				Dailies::add_dailies_entry($project[0], Dailies::GROUP_ROOT, Dailies::TYPE_BANK_UPLOAD, '{"idProject":"'.$project[0].'","nbF":"'.$ir.'","folder":"'.basename($destDir).'"}');
				$retour['message'] .= $ir.' file(s) added.';
			}
		}
		else {
			$retour['message'] = '$tempFiles not an array !';
		}
	}

	// Création du zip de dossier à télécharger
	if ($action == 'zipDLbankFolder') {
		$zipFileName = 'bank_'.$projTitle.'_'.$folder.'.zip';
		$zipPath	 = INSTALL_PATH.FOLDER_TEMP."exports/".$zipFileName;
		touch($zipPath);
		$zip = new ZipArchive();
		if ($zip->open($zipPath, ZipArchive::OVERWRITE) !== true) {
			$retour['message'] = "Unable to create zip file!";
		}
		else {
			$directory = INSTALL_PATH . $bankDir.'/'.$folder;
			$options = array('add_path' => $folder.'/', 'remove_all_path' => true);
			$zip->addPattern('/\.*/', $directory, $options);
			$retour['error'] = 'OK';
			$retour['message'] = 'Zip file created ('.$zip->numFiles.' files). <b><a href="fct/downloader.php?type=bank_zip&file='.$zipFileName.'">Click here to download it.</a></b>';
			$retour['persistant'] = 'persist';
			$zip->close();
		}
	}

	// Création du zip de dossier à télécharger
	if ($action == 'zipDLshotFolder') {
		$itemInfos = explode(';', $item);
		$itemInfo  = "";
		if ($type == 'shots')
			$itemInfo = $itemInfos[5];
		if ($type == 'assets')
			$itemInfo = $itemInfos[1];
		$zipFileName = $proj_title.'_'.$type.'_'.preg_replace('/ /', '_', $itemInfo).'_'.basename($folder).'-folder.zip';
		$zipPath	 = INSTALL_PATH.FOLDER_TEMP."exports/".$zipFileName;
		touch($zipPath);
		$zip = new ZipArchive();
		$errZip = $zip->open($zipPath, ZipArchive::OVERWRITE);
		if ($errZip !== true)
			throw new Exception("Unable to create zip file at '$zipPath' (code $errZip) !");
		$directory = INSTALL_PATH . $folderPath;
		$options = array('add_path' => basename($folder).'/', 'remove_all_path' => true);
		$zip->addPattern('/\.*/', $directory, $options);
		$retour['error'] = 'OK';
		$retour['message'] = 'Zip file created ('.$zip->numFiles.' files). <b><a href="fct/downloader.php?type=bank_zip&file='.$zipFileName.'">Click here to download it.</a></b>';
		$retour['persistant'] = 'persist';
		$zip->close();
	}
}
catch(Exception $e) { $retour['message'] = $e->getMessage(); }

echo json_encode($retour); ?>