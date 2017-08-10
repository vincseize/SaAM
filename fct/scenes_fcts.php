<?php

require_once ('directories.php' );

// récupère la vignette de scene du dossier temp (après upload)
function move_scene_vignette ($idProj=false, $sceneID=false, $vignetteName=false) {
	if (!$idProj)		{ throw new Exception('move_scene_vignette() : missing the project ID!'); return; }
	if (!$sceneID)		{ throw new Exception('move_scene_vignette() : missing the scene ID!'); return; }
	if (!$vignetteName)	{ throw new Exception('move_scene_vignette() : missing vignette name!');  return; }

	$sc = new Scenes($sceneID);
	$destDir = INSTALL_PATH . FOLDER_DATA_PROJ . $sc->getDirScene();
	$tempVignette = INSTALL_PATH .'temp/uploads/vignettes/'.$vignetteName;
	$vNameArr	  = explode('.', $vignetteName);
	$vExt		  = $vNameArr[count($vNameArr)-1];
	if (!is_dir($destDir))
		mkdir($destDir, 0755, true);
	$destVignette = $destDir . '/scene_vignette.' ;
	@unlink ($destVignette.'gif');
	@unlink ($destVignette.'jpg');
	@unlink ($destVignette.'png');
	if (!is_file($tempVignette))
		{ throw new Exception('move_scene_vignette() : temp vignette file not found !'); return; }
	if (!@copy($tempVignette, $destVignette.$vExt))
		{ throw new Exception('move_scene_vignette() : unable to copy vignette file to : '.$destVignette.$vExt.' !'); return; }
	if (!@unlink($tempVignette))
		{ throw new Exception('move_scene_vignette() : unable to delete temp vignette file !'); }
}

// récupère la retake du dossier temp (après upload)
function move_retake_scene ($deptID=false, $sceneID=false, $retakeTempName=false, $isReplace=false) {
	if (!$deptID)	{ throw new Exception('move_retake_asset() : missing department ID!'); return; }
	if (!$sceneID)	{ throw new Exception('move_retake_asset() : missing the scene ID!');  return; }
	if (!$retakeTempName)	{ throw new Exception('move_retake_asset() : missing $retakeTempName !'); return; }

	$sc = new Scenes($sceneID);
	$destSceneDir	= INSTALL_PATH . FOLDER_DATA_PROJ . $sc->getDirRetakes($deptID).'/';
	$wipSceneDir	= INSTALL_PATH . FOLDER_DATA_PROJ . $sc->getDirSceneDatas($deptID).'/wip/';
	$retakeTempFile = INSTALL_PATH .'temp/uploads/retakes/'.$retakeTempName;
	$retakeName		= 'retake_0';
	if (!is_dir($destSceneDir))
		mkdir($destSceneDir, 0755, true);
	if (!is_dir($wipSceneDir))
		mkdir($wipSceneDir, 0755, true);

	$nbRetake = count(glob($destSceneDir.'retake*'));
	$nbWips	  = count(glob($wipSceneDir.'*'));
	if (!is_file($retakeTempFile))
		{ throw new Exception('move_retake_scene() : temp retake file not found!'); return; }
	if (is_file($destSceneDir.$retakeName)) {
		if ($isReplace) {
			$mimeOldRetake = explode(';',check_mime_type_info($destSceneDir.$retakeName));
			$typeOldRetake = recup_file_ext($mimeOldRetake[0]);
			if (!copy($destSceneDir.$retakeName, $wipSceneDir.'backup_retake_'.$nbWips.$typeOldRetake))
				{ throw new Exception('move_retake() : unable to move old retake to WIP dir!'); return; }
		}
		else {
			if (!rename($destSceneDir.$retakeName, $destSceneDir.'retake_'.$nbRetake))
				{ throw new Exception('move_retake() : unable to rename old retake !'); return; }
		}
		@unlink($destSceneDir.'thumbs/vthumb_retake_0.gif');
	}
	if (!copy($retakeTempFile, $destSceneDir.$retakeName))
		{ throw new Exception('move_retake() : unable to copy new retake file to : '.$destSceneDir.'!'); return; }
	if (!unlink($retakeTempFile))
		{ throw new Exception('move_retake() : unable to delete temp retake file...'); }
}


// Fonction de destruction en BDD de toutes les scènes d'un projet ( !!!!!! BEWARE !!!!!! )
function delete_all_scenes_project($idProj) {
	$l = new Liste();
	$scList = $l->getListe(TABLE_SCENES, 'id', 'id', 'ASC', Scenes::ID_PROJECT, '=', $idProj);
	if (is_array($scList)) {
		foreach($scList as $scId)
			delete_scene($scId);
	}
}

// Supprime une scène
function delete_scene ($sceneID) {
	$l = new Liste();
	$scComList = $l->getListe(TABLE_COMM_SCENES, 'id', 'id', 'ASC', Comments::COMM_ID_SCENE, '=', $sceneID);
	if (is_array($scComList)) {
		foreach($scComList as $scComId) {
			$scCom = new Comments('retake_scene', (int)$scComId);
			$scCom->delete();
		}
	}
	$scDptList = $l->getListe(TABLE_SCENES_DEPTS, 'id', 'id', 'ASC', Comments::COMM_ID_SCENE, '=', $sceneID);
	if (is_array($scDptList)) {
		foreach($scDptList as $scDptId) {
			$i = new Infos(TABLE_SCENES_DEPTS);
			$i->loadInfos('id', $scDptId);
			$i->delete();
		}
	}
	$sc = new Scenes((int)$sceneID);
	$sc->delete();
}

?>
