<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC'].'/checkConnect.php' );
	if ($_SESSION['user']->isVisitor()) die('{"error":"error", "message":"actions scenes : Access denied."}');

require_once ('scenes_fcts.php');

extract($_POST);

$retour['error']	= 'error';
$retour['message']	= 'action undefined';

try {

	$ACL = new ACL($_SESSION['user']);

	// Ajout de scène mère
	if ($action == 'addScene') {
		if ($ACL->check('SCENES_ADMIN')) {
			$newID = Liste::getAIval(TABLE_SCENES);
			$infos = json_decode($infos, true);
			$sc = new Scenes();
			$sc->setInfos($projID, $infos);
			if ($infos[Scenes::MASTER] != "0") {
				$scM = new Scenes((int)$infos[Scenes::MASTER]);
				$scM->addDerivative((int)$newID);
			}
			$retour['error'] = 'OK';
			$retour['message'] = 'Scene added.';
			$retour['sceneID'] = $newID;
			$retour['sceneTitle'] = $infos[Scenes::TITLE];
		}
		else $retour['message'] = 'Access denied.';
	}

	// Modif du status par dept.
	if ($action == 'modifSceneStatus') {
		if ($ACL->check('SCENES_ADMIN')) {
			if (isset($sceneID) && $sceneID != 'undefined') {
				if ( isset($idNewStatus) && $idNewStatus != 'undefined') {
					$sc = new Scenes($sceneID);
					$sc->setStatus($idProj, $idDept, (int)$idNewStatus);
					$sc->save();
					$retour['error'] = 'OK';
					$retour['message'] = 'Status modified.';
				}
				else $retour['message'] = 'Status ID is missing!';
			}
			else $retour['message'] = 'Scene ID is missing!';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Placement de la vignette dans le bon rep
	if ($action == 'moveVignette') {
		if ($ACL->check('SCENES_ADMIN')) {
			move_scene_vignette($idProj, $sceneID, $vignetteName);
			$retour['error'] = 'OK';
			$retour['message'] = 'Vignette uploaded.';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Modif d'une valeur "simple"
	if ($action == 'modSceneInfo') {
		if ($ACL->check('SCENES_ADMIN')) {
			if (isset($sceneID) && $sceneID !='undefined') {
				if ( isset($keyMod) && $keyMod !='undefined') {
					if ($keyMod == Scenes::DATE || $keyMod == Scenes::DEADLINE) {
						$newDate = DateTime::createFromFormat(DATE_FORMAT, $valMod);
						if ($newDate) $valMod = $newDate->format(DATE_ATOM);
					}
					$sc = new Scenes($sceneID);
					$sc->updateInfo($keyMod, $valMod);
					$sc->save();
					$retour['error'] = 'OK';
					$retour['message'] = 'Value modified.';
				}
				else $retour['message'] = 'Value of type "'.$keyMod.'" unknown!';
			}
			else $retour['message'] = 'Scene ID is missing!';
		}
		else $retour['message'] = 'Access denied.';
	}

	// LOCK / UNLOCK de scène
	if ($action == 'modifSceneLock') {
		if ($ACL->check('SCENES_ADMIN')) {
			if (isset($sceneID) && $sceneID !='undefined') {
				$sc = new Scenes($sceneID);
				$sc->setLock($lock);
				$sc->save();
				$retour['error'] = 'OK';
				if ($lock == '1')
					$retour['message'] = 'Scene locked.';
				else
					$retour['message'] = 'Scene unlocked.';
			}
			else $retour['message'] = 'Scene ID is missing!';
		}
		else $retour['message'] = 'Access denied.';
	}

	// ARCHIVE / RESTORE de scène
	if ($action == 'modifSceneArchive') {
		if ($ACL->check('SCENES_ADMIN')) {
			if (isset($sceneID) && $sceneID !='undefined') {
				$sc = new Scenes($sceneID);
				if ($archive == '1') {
					$sc->archiveScene();
					$retour['message'] = 'Scene archived.';
				}
				elseif ($archive == '0') {
					$sc ->restoreScene();
					$retour['message'] = 'Scene restored.';
				}
				$sc->save();
				$retour['error'] = 'OK';
			}
			else $retour['message'] = 'Scene ID is missing!';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Modif de la description
	if ($action == 'modifSceneDescr') {
		if ($ACL->check('SCENES_ADMIN')) {
			if (isset($sceneID) && $sceneID !='undefined') {
				if ( isset($newDescr) && $newDescr !='undefined') {
					$sc = new Scenes($sceneID);
					$sc->setDescription(urldecode($newDescr));
					$sc->save();
					$retour['error'] = 'OK';
					$retour['message'] = 'Description modified.';
				}
				else $retour['message'] = 'New description is missing!';
			}
			else $retour['message'] = 'Scene ID is missing!';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Modification du Handler de l'asset (celui qui "prend la main")
	if ($action == 'modifSceneHandler') {
		if ($ACL->check('SCENES_HANDLE', 'scene:'.$sceneID)) {
			if (isset($sceneID) && $sceneID !='undefined') {
				if ( isset($idNewHandler) && $idNewHandler !='undefined') {
					$sc = new Scenes($sceneID);
					$sc->setHandler((int)urldecode($idNewHandler));
					$sc->save();
					Dailies::add_dailies_entry($idProj, Dailies::GROUP_SCENE, Dailies::TYPE_SCENE_HANDLED, '{"sceneID":"'.$sceneID.'","idNewHandler":"'.(int)urldecode($idNewHandler).'"}');
					$retour['error'] = 'OK';
					$mlock = ((int)urldecode($idNewHandler) == 0) ? 'UNLOCKED' : 'LOCKED';
					$retour['message'] = 'Assignation modified. Scene is now '.$mlock.'.';
				}
				else $retour['message'] = 'User ID is missing!';
			}
			else $retour['message'] = 'Scene ID is missing!';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Modification de l'équipe de la scene
	if ($action == 'modSceneTeam') {
		if ($ACL->check('SCENES_ADMIN')) {
			$sc = new Scenes($sceneID);
			$sc->setTeam(json_decode(urldecode($newTeam)));
			$sc->save();
			$retour['error'] = 'OK';
			$retour['message'] = 'Scene team modified.';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Placement de la nouvelle retake
	if ($action == 'moveTempRetake') {
		if ($ACL->check('SCENES_PUBLISH', 'scene:'.$sceneID)) {
			$replace = (isset($modif) && @$modif == 'true') ? true : false;
			move_retake_scene($deptID, $sceneID, $retakeTempName, $replace);
			$sc = new Scenes($sceneID);
			$sc->setValidRetake($deptID, false);
			$sc->save();
			$typeDailies = ($replace) ? Dailies::TYPE_SCENE_MOD_PUBLISHED : Dailies::TYPE_SCENE_NEW_PUBLISHED;
			Dailies::add_dailies_entry($idProj, Dailies::GROUP_SCENE, $typeDailies, '{"sceneID":"'.$sceneID.'","deptID":"'.$deptID.'"}');
			$retour['error'] = 'OK';
			$retour['message'] = 'Published uploaded.';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Validation de la dernière retake
	if ($action == 'valideRetake') {
		if ($ACL->check('SCENES_PUBLISH', 'scene:'.$sceneID)) {
			$sc = new Scenes($sceneID);
			$sc->setValidRetake($deptID, true);
//			$asset->updateVignette($idProj);									// @TODO : permettre le choix de MAJ auto vignette dans les settings
			$sc->save();
			Dailies::add_dailies_entry($idProj, Dailies::GROUP_SCENE, Dailies::TYPE_SCENE_VALID_PUBLISHED, '{"sceneID":"'.$sceneID.'","deptID":"'.$deptID.'"}');
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
		if ($ACL->check('SCENES_MESSAGE', 'scene:'.$sceneID)) {
			$cm = new Comments('retake_scene');
			$cm->initNewCommRetake_scene($sceneID, $idProj, $deptID, $reponse);
			$cm->addText(stripslashes(urldecode($texte)));
			$cm->save();
			Dailies::add_dailies_entry($idProj, Dailies::GROUP_SCENE, Dailies::TYPE_SCENE_NEW_MESSAGE, '{"sceneID":"'.$sceneID.'","deptID":"'.$deptID.'","txtMess":"'.urlencode($texte).'"}');
			$retour['error'] = 'OK';
			$retour['message'] = 'message sauvegardé.';
		}
		else $retour['message'] = 'Access denied.';
	}

	// Traitement de la suppression de message
	if ($action == 'deleteMessage') {
		if ($ACL->check('SCENES_MESSAGE', 'scene:'.$sceneID)) {
			$cm = new Comments('retake_scene', $idComm);
			$cm->delete();
			$retour['error'] = 'OK';
			$retour['message'] = 'message supprimé.';
		}
		else $retour['message'] = 'Access denied.';
	}


	// Modif de la liste des assets
	if ($action == 'modSceneAssets') {
		if ($ACL->check('SCENES_ADMIN')) {
			$sc = new Scenes($sceneID);
			$sc->updateInfo(Scenes::ASSETS, urldecode($newAssetsList));
			$sc->save();
			$retour['error'] = 'OK';
			$retour['message'] = 'Scene assets list modified.';
		}
		else $retour['message'] = 'Access denied.';
	}


	// Assignation à un shot
	if ($action === 'assignShot') {
		if ($ACL->check('SCENES_ADMIN')) {
			$sc = new Scenes($sceneID);
			$sc->assignShot((int)$seqID, (int)$shotID);
			$sc->save();
			$p = new Projects((int)$projID);
			$se = new Sequences((int)$seqID);
			$sh = new Shots((int)$shotID);
			$retour['error']	 = 'OK';
			$retour['message']	 = 'Shot "'.$sh->getShotInfos(Shots::SHOT_TITLE).'" assigned to scene "'.$sc->getSceneInfos(Scenes::TITLE).'".';
			$retour['filleID']   = $sceneID;
			$retour['seqTitle']  = $se->getSequenceInfos(Sequences::SEQUENCE_TITLE);
			$retour['shotInfos'] = $sh->getShotInfos();
			$retour['vignetteShot'] = check_shot_vignette_ext($projID, $se->getSequenceInfos(Sequences::SEQUENCE_LABEL), $sh->getShotInfos(Shots::SHOT_LABEL)) ;
		}
		else $retour['message'] = 'Access denied.';
	}

	// Désassignation d'un shot
	if ($action === 'removeShot') {
		if ($ACL->check('SCENES_ADMIN')) {
			$sc = new Scenes($sceneID);
			$sc->removeShot((int)$seqID, (int)$shotID);
			$sc->save();
			$retour['error'] = 'OK';
			$retour['message'] = "Shot #$shotID removed from scene.";
			$retour['shotID'] = $shotID;
		}
		else $retour['message'] = 'Access denied.';
	}

	// Ajout de camera
	if ($action == 'addCamera') {
		if ($ACL->check('SCENES_ADMIN')) {
			$cam = new Cameras();
			$cam->setProject($projID);
			$cam->setScene($sceneID);
			$cam->setName($camName);
			$cam->setCreator($_SESSION['user']->getUserInfos(Users::USERS_ID));
			$cam->save();
			$retour['error'] = 'OK';
			$retour['message'] = "Camera added.";
			$retour['newCamName'] = $camName;
		}
		else $retour['message'] = 'Access denied.';
	}

	// Assignation de camera à un shot de scène fille
	if ($action == 'assignCamera') {
		if ($ACL->check('SCENES_ADMIN')) {
			$sh = new Shots((int)$shotID);
			$sh->setCamera((int)$camID);
			$shotTitle = $sh->getShotInfos(Shots::SHOT_TITLE);
			$retour['error'] = 'OK';
			if ($camID == '0')
				$retour['message'] = "Camera unassigned to shot '$shotTitle'.";
			else
				$retour['message'] = "Camera assigned to shot '$shotTitle'.";
		}
		else $retour['message'] = 'Access denied.';
	}

}
catch(Exception $e) { $retour['message'] = $e->getMessage(); }

echo json_encode($retour);
?>