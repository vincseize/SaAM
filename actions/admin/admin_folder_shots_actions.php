<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	if ($_SESSION['user']->isVisitor()) die('{"error":"error", "message":"actions bank shots : Access denied."}');

	require_once('directories.php');

extract($_POST);

$retour['error'] = 'error';
$retour['message'] = 'action undefined';


try {
	$ACL = new ACL($_SESSION['user']);

	// Traitement de la suppression de fichier dans un dossier de dataShot
	if ($action == 'deleteShotBankRef') {
		if ($ACL->check('SHOTS_UPLOAD', 'shot:'.$IDshot)) {
			if (empty($filePath)) {
				$retour['message'] = 'missing file path!';
				die(json_encode($retour));
			}
			$path = dirname($filePath);
			$file = basename($filePath);
			$img_thumb = INSTALL_PATH.$path.'/thumbs/t_'.$file;
			if(file_exists(INSTALL_PATH.$filePath)) {
				if (unlink(INSTALL_PATH.$filePath))
					$retour['message'] = $file.' supprimé';
			}
			if (file_exists($img_thumb)) {
				if (unlink($img_thumb))
					$retour['message'] .= ' (+ thumb)';
			}
			$retour['error'] = 'OK';
			$retour['rep']   = $path.'/';
		}
		else  $retour['message'] = 'Access denied.';
	}

	// Ajout de dossier custom Shot
	if ($action == 'addShotFolder') {
		if ($ACL->check('SHOTS_UPLOAD', 'shot:'.$IDshot)) {
			if (empty($path)) {
				$retour['message'] = 'missing path where to create folder!';
				die(json_encode($retour));
			}
			if (!is_dir(INSTALL_PATH.$path)) {
				if (mkdir(INSTALL_PATH.$path, 0755, true)) {
					$retour['error'] = 'OK';
					$retour['message'] = 'New folder added.';
				}
			}
		}
		else  $retour['message'] = 'Access denied.';
	}

	// Suppression de dossier custom Shot
	if ($action == 'deleteShotFolder') {
		if ($ACL->check('SHOTS_UPLOAD', 'shot:'.$IDshot)) {
			if (empty($path)) {
				$retour['message'] = 'missing path where to create folder!';
				die(json_encode($retour));
			}
			if (is_dir(INSTALL_PATH.$path)) {
				if (rmDir_R(INSTALL_PATH.$path)) {
					$retour['error'] = 'OK';
					$retour['message'] = 'Folder deleted.';
				}
			}
		}
		else  $retour['message'] = 'Access denied.';
	}

	// Traitement de la suppression de fichier dans un dossier de dataAsset
	if ($action == 'deleteAssetBankRef') {
		if ($ACL->check('ASSETS_UPLOAD', 'asset:'.$IDasset)) {
			if (empty($filePath)) {
				$retour['message'] = 'missing file path!';
				die(json_encode($retour));
			}
			$path = dirname($filePath);
			$file = basename($filePath);
			$img_thumb = INSTALL_PATH.$path.'/thumbs/t_'.$file;
			if(file_exists(INSTALL_PATH.$filePath)) {
				if (unlink(INSTALL_PATH.$filePath))
					$retour['message'] = $file.' supprimé';
			}
			if (file_exists($img_thumb)) {
				if (unlink($img_thumb))
					$retour['message'] .= ' (+ thumb)';
			}
			$retour['error'] = 'OK';
			$retour['rep']   = $path.'/';
		}
		else  $retour['message'] = 'Access denied.';
	}

	// Ajout de dossier custom Asset
	if ($action == 'addAssetFolder') {
		if ($ACL->check('ASSETS_UPLOAD', 'asset:'.$IDasset)) {
			if (empty($path)) {
				$retour['message'] = 'missing path where to create folder!';
				die(json_encode($retour));
			}
			if (!is_dir(INSTALL_PATH.$path)) {
				if (mkdir(INSTALL_PATH.$path, 0755, true)) {
					$retour['error'] = 'OK';
					$retour['message'] = 'New folder added.';
				}
			}
		}
		else  $retour['message'] = 'Access denied.';
	}

	// Suppression de dossier custom Asset
	if ($action == 'deleteAssetFolder') {
		if ($ACL->check('ASSETS_UPLOAD', 'asset:'.$IDasset)) {
			if (empty($path)) {
				$retour['message'] = 'missing path where to create folder!';
				die(json_encode($retour));
			}
			if (is_dir(INSTALL_PATH.$path)) {
				if (rmDir_R(INSTALL_PATH.$path)) {
					$retour['error'] = 'OK';
					$retour['message'] = 'Folder deleted.';
				}
			}
		}
		else  $retour['message'] = 'Access denied.';
	}


	// Envoi des mails d'infos quand upload
	if ($action == 'sendMailUpload') {
		$retour['error'] = 'OK';
		$retour['message'] = $nbFiles.' fichier(s) uploadé(s).';
		if (isset($shotInfos)) {
			$shotInf = explode(';', $shotInfos);
			$folder	 = explode('/', $shotInf[7]);
			end($folder);
			try {
				$d = new Infos(TABLE_DEPTS);
				$d->loadInfos('id', $shotInf[6]);
				$deptName = $d->getInfo('label');
			} catch (Exception $e) { $deptName = $shotInf[6]; }
			$retour['rep'] = $shotInf[7];
			Dailies::add_dailies_entry((int)$shotInf[0], Dailies::GROUP_SHOT, Dailies::TYPE_SHOT_BANK_UPLOAD, '{"idShot":"'.$shotInf[4].'","dept":"'.$deptName.'","nbF":"'.$nbFiles.'","folder":"'.prev($folder).'"}');
		}
		elseif (isset($assetInfos)) {
			$assetInf = explode(';', $assetInfos);
			$folder	 = explode('/', $assetInf[4]);
			end($folder);
			$retour['rep'] = $assetInf[4];
			Dailies::add_dailies_entry((int)$assetInf[0], Dailies::GROUP_ASSET, Dailies::TYPE_ASSET_BANK_UPLOAD, '{"pathAsset":"'.$assetInf[2].'","nameAsset":"'.$assetInf[1].'","deptID":"'.$assetInf[3].'","nbF":"'.$nbFiles.'","folder":"'.prev($folder).'"}');
		}
		else {
			$retour['error'] = 'error';
			$retour['message'] = 'some infos are missing.';
		}
	}

}
catch (Exception $e) { $retour['message'] = $e->getMessage(); }

echo json_encode($retour);

?>
