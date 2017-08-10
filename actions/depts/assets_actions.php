<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC'].'/checkConnect.php' );
	if ($_SESSION['user']->isVisitor()) die('{"error":"error", "message":"actions assets : Access denied."}');

require_once ('xml_fcts.php');
require_once ('assets_fcts.php');

extract($_POST);
extract($_GET);

$retour['error']	= 'error';
$retour['message']	= 'action undefined';

try {

	$ACL = new ACL($_SESSION['user']);

	// Création d'asset en BDD
	if ($action == 'addAsset') {
		if ($ACL->check('ASSETS_CREATE')) {
			$infos = json_decode($infos, true);
			if (!preg_match('/^[a-z0-9_\-\.]{2,}\.[a-z]{3,}$/i', $infos[Assets::ASSET_NAME]))
				throw new Exception('addAsset: invalid filename!');
			if (isset($addExtTodefault))
				add_default_extension($addExtTodefault);
			$path = $infos[Assets::ASSET_PATH_REL];
			$path = preg_replace('#(^[\.]+[/]+)|(^[\.]+)|(^[/]+)#', '', $path);
			$path = (!preg_match('#/$#', $path)) ? './'.$path.'/' : './'.$path;
			$a = new Assets($projID, $infos[Assets::ASSET_NAME], $path);
			$a->setInfos($infos);
			$retour['error'] = 'OK';
			$retour['message'] = 'Asset added.';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Renommage d'un asset non présent dans le XML
	if ($action == 'renameAsset') {
		if ($ACL->check('ASSETS_CREATE')) {
			$a = new Assets($idProj, (int)$idAsset);
			$a->renameAsset($newName);
			$a->save();
			$retour['error'] = 'OK';
			$retour['message'] = 'Asset renamed.';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Changement du path d'un asset non présent dans le XML
	if ($action == 'changePathAsset') {
		if ($ACL->check('ASSETS_CREATE')) {
			if (strlen($newPath) < 2)
				throw new Exception('changePathAsset: Path too short.');
			$a = new Assets($idProj, (int)$idAsset);
			$a->changePath($newPath);
			$a->save();
			$retour['error'] = 'OK';
			$retour['message'] = "Asset's path modified.";
		}
		else $retour['message'] = 'Access denied.';
	}

	// Ajout d'un dossier dans le XML
	if ($action == 'addPathFolder') {
		if ($ACL->check('ASSETS_CREATE')) {
			addFolderToXML($projID, $folderPath);
			$retour['error'] = 'OK';
			$retour['message'] = 'Folder created.';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Suppression d'un dossier du XML
	if ($action == 'deletePathFolder') {
		if ($ACL->check('ASSETS_CREATE')) {
			removeFolderToXML($projID, $folderPath);
			$retour['error'] = 'OK';
			$retour['message'] = 'Folder deleted.';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Récupère tout les chemins possibles depuis le XML
	if ($action == 'getAllXMLpaths') {
		$retour['paths'] = getAllXMLpaths($projID);
		$retour['error'] = 'OK';
		$retour['message'] = 'Paths retreived.';
	}

	// Ajout d'un asset dans le XML
	if ($action == 'addAssetToXML') {
		if ($ACL->check('ASSETS_CREATE')) {
			addAssetToXML($idProj, $idAsset);
			$retour['error'] = 'OK';
			$retour['message'] = 'Asset inserted into XML masterFile.';
		}
		else $retour['message'] = 'Access denied.';
	}


	// Modification de la description
	if ($action == 'modifAssetDescr') {
		if ($ACL->check('ASSETS_ADMIN')) {
			if (isset($nameAsset) && $nameAsset !='undefined') {
				if ( isset($newDescr) && $newDescr !='undefined') {
					$a = new Assets(false, $nameAsset);
					$a->setDescription(urldecode($newDescr));
					$a->save();
					$retour['error'] = 'OK';
					$retour['message'] = 'Description modified.';
				}
				else $retour['message'] = 'New description is missing !';
			}
			else $retour['message'] = 'Asset name is missing !';
		}
		else $retour['message'] = 'Access denied.';
	}


	// Modification de la description
	if ($action == 'modifAssetHide') {
		if ($ACL->check('ASSETS_ADMIN')) {
			if (isset($nameAsset) && $nameAsset !='undefined') {
				if ( isset($hidden) && $hidden !='undefined') {
					$a = new Assets(false, $nameAsset);
					if ($hidden == 'hide')
						$a->setHide('1');
					else
						$a->setHide('0');
					$a->save();
					$retour['error'] = 'OK';
					$retour['message'] = 'Asset visibility modified.';
				}
				else $retour['message'] = 'hidden state is missing !';
			}
			else $retour['message'] = 'Asset name is missing !';
		}
		else $retour['message'] = 'Access denied.';
	}


	// Modification de la catégorie
	if ($action == 'modifAssetCateg') {
		if ($ACL->check('ASSETS_ADMIN')) {
			if (isset($nameAsset) && $nameAsset !='undefined') {
				if ( isset($idNewCat) && $idNewCat !='undefined') {
					$a = new Assets(false, $nameAsset);
					$a->setCategory(urldecode($idNewCat));
					$a->save();
					$retour['error'] = 'OK';
					$retour['message'] = 'Category modified.';
				}
				else $retour['message'] = 'Category ID is missing !';
			}
			else $retour['message'] = 'Asset name is missing !';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Modification de l'équipe de l'asset
	if ($action == 'modAssetTeam') {
		if ($ACL->check('ASSETS_ADMIN')) {
			$asset = new Assets(false, $nameAsset);
			$asset->setTeam(json_decode(urldecode($newTeam)));
			$asset->save();
			$retour['error'] = 'OK';
			$retour['message'] = 'Asset team modified.';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Move temp masterFile_assets.xml
	if ($action == 'moveTempMasterFile') {
		if ($ACL->check('ASSETS_ADMIN')) {
			move_masterfile_asset($idProj, $masterFileTempName);
//			Dailies::add_dailies_entry($idProj, Dailies::GROUP_ASSET, Dailies::TYPE_ASSET_NEW_MASTERFILE, '{"idProject":"'.$idProj.'"}');				// @TODO : Dailies quand nouveau masterfile
			$retour['error'] = 'OK';
			$retour['message'] = 'Masterfile uploaded.';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Placement de la vignette dans le bon rep
	if ($action == 'moveVignette') {
		if ($ACL->check('ASSETS_ADMIN')) {
			move_asset_vignette($idProj, $nameAsset, $pathAsset, $vignetteName);
			$retour['error'] = 'OK';
			$retour['message'] = 'Vignette uploaded.';
		}
		else $retour['message'] = 'Access denied.';
	}


	// Modification de l'étape
	if ($action == 'modifAssetStatus') {
		if ($ACL->check('ASSETS_PUBLISH', 'asset:'.$nameAsset)) {
			if (isset($nameAsset) && $nameAsset !='undefined') {
				if ( isset($idNewStatus) && $idNewStatus !='undefined') {
					$a = new Assets(false, $nameAsset);
					$a->setStatus($idProj, $idDept, (int)$idNewStatus);
					$a->save();
					$retour['error'] = 'OK';
					$retour['message'] = 'Status modified.';
				}
				else $retour['message'] = 'Status ID is missing !';
			}
			else $retour['message'] = 'Asset name is missing !';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Placement de la nouvelle retake
	if ($action == 'moveTempRetake') {
		if ($ACL->check('ASSETS_PUBLISH', 'asset:'.$nameAsset)) {
			$replace = (isset($modif) && @$modif == 'true') ? true : false;
			move_retake_asset($idProj, $deptID, $nameAsset, $retakeTempName, $replace);
			$asset = new Assets($idProj, $nameAsset);
			$asset->setValidRetake($idProj, $deptID, false);
			$asset->save();
			$pathAsset = preg_replace('#\./#', '', $asset->getPath());
			$idAsset   = $asset->getIDasset();
			$typeDailies = ($replace) ? Dailies::TYPE_ASSET_MOD_PUBLISHED : Dailies::TYPE_ASSET_NEW_PUBLISHED;
			Dailies::add_dailies_entry($idProj, Dailies::GROUP_ASSET, $typeDailies, '{"idAsset":"'.$idAsset.'","pathAsset":"'.$pathAsset.'","nameAsset":"'.$nameAsset.'","deptID":"'.$deptID.'"}');
			$retour['error'] = 'OK';
			$retour['message'] = 'Published uploaded.';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Validation de la dernière retake
	if ($action == 'valideRetake') {
		if ($ACL->check('ASSETS_PUBLISH', 'asset:'.$nameAsset)) {
			$asset = new Assets($idProj, $nameAsset);
			$asset->setValidRetake($idProj, $deptID, true);
//			$asset->updateVignette($idProj);					// @TODO : permettre le choix de MAJ auto vignette dans les settings
			$asset->save();
			$pathAsset = preg_replace('#\./#', '', $asset->getPath());
			$idAsset   = $asset->getIDasset();
			Dailies::add_dailies_entry($idProj, Dailies::GROUP_ASSET, Dailies::TYPE_ASSET_VALID_PUBLISHED, '{"idAsset":"'.$idAsset.'","pathAsset":"'.$pathAsset.'","nameAsset":"'.$nameAsset.'","deptID":"'.$deptID.'"}');
			$retour['error'] = 'OK';
			$retour['message'] = 'Published validated.';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Suppression de dernière retake temporaire
	if ($action == 'deleteTempRetake') {
		$retakeTempFile = glob(INSTALL_PATH.'temp/uploads/retakes/'.$_SESSION['user']->getUserInfos('id').'_newRetakeTemp.*');
		if (unlink($retakeTempFile[0])) {
			$retour['error'] = 'OK';
			$retour['message'] = 'Temp file deleted.';
		}
		else $retour['message'] = 'deleteTempRetake : Unable to delete temp file.';
	}

	// Traitement de l'ajout de message
	if ($action == 'addMessage') {
		if ($ACL->check('ASSETS_MESSAGE', 'asset:'.$idAsset)) {
			$cm = new Comments('retake_asset');
			$cm->initNewCommRetake_asset($idAsset, $idProj, $deptID, $reponse);
			$cm->addText(stripslashes(urldecode($texte)));
			$cm->save();
			$asset = new Assets($idProj, (int)$idAsset);
			$pathAsset = preg_replace('#\./#', '', $asset->getPath());
			Dailies::add_dailies_entry($idProj, Dailies::GROUP_ASSET, Dailies::TYPE_ASSET_NEW_MESSAGE, '{"idAsset":"'.$idAsset.'","pathAsset":"'.$pathAsset.'","nameAsset":"'.$asset->getName().'","deptID":"'.$deptID.'","txtMess":"'.urlencode($texte).'"}');
			$asset->save();
			$retour['error'] = 'OK';
			$retour['message'] = 'message sauvegardé.';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Traitement de la suppression de message
	if ($action == 'deleteMessage') {
		if ($ACL->check('ASSETS_MESSAGE', 'asset:'.$idAsset)) {
			$cm = new Comments('retake_asset', $idComm);
			$cm->delete();
			$retour['error'] = 'OK';
			$retour['message'] = 'message supprimé.';
		}
		else $retour['message'] = 'Access denied.';
	}


	// Modification du Handler de l'asset (celui qui "prend la main")
	if ($action == 'modifAssetHandler') {
		if ($ACL->check('ASSETS_HANDLE', 'asset:'.$nameAsset)) {
			if (isset($nameAsset) && $nameAsset !='undefined') {
				if ( isset($idNewHandler) && $idNewHandler !='undefined') {
					$a = new Assets($idProj, $nameAsset);
					$a->setHandler((int)urldecode($idNewHandler));
					$a->save();
					$pathAsset = preg_replace('#\./#', '', $a->getPath());
					$idAsset   = $a->getIDasset();
					Dailies::add_dailies_entry($idProj, Dailies::GROUP_ASSET, Dailies::TYPE_ASSET_HANDLED, '{"idAsset":"'.$idAsset.'","pathAsset":"'.$pathAsset.'","nameAsset":"'.$a->getName().'","idNewHandler":"'.(int)urldecode($idNewHandler).'"}');
					$retour['error'] = 'OK';
					$mlock = ((int)urldecode($idNewHandler) == 0) ? 'UNLOCKED' : 'LOCKED';
					$retour['message'] = 'Assignation modified. Asset is now '.$mlock.'.';
				}
				else $retour['message'] = 'User ID is missing !';
			}
			else $retour['message'] = 'Asset name is missing !';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Suppression d'un asset en BDD
	if ($action == 'deleteAsset') {
		if ($ACL->check('ASSETS_ADMIN')) {
			$a = new Assets($idProj, (int)$idAsset);
			$usrs = $a->getTeamAsset();
			$retour['message'] = 'Asset deleted.';
			foreach($usrs as $usrId) {
				$u = new Users((int)$usrId);
				$u->removeAssetToUser($idAsset);
				$u->save();
			}
			delete_asset($idProj, $idAsset);
			$retour['error'] = 'OK';
		}
		else $retour['message'] = 'Access denied.';
	}

}
catch(Exception $e) { $retour['message'] = "Action error : ".$e->getMessage(); }

echo json_encode($retour);

?>
