<?php
@session_start(); // 2 lignes à placer toujours en haut du code des pages
require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
if ($_SESSION['user']->isVisitor()) die('{"error":"error", "message":"actions shots : Access denied."}');


extract($_POST);

$retour['error'] = 'error';
$retour['message'] = 'action undefined';

try {
	$userID = $_SESSION['user']->getUserInfos('id');

//	$ACL = new ACL($_SESSION['user']);

	if ($action == 'saveDrawing') {
		if (!is_file(urldecode(@$pubFile)))
			throw new Exception("The published filename is missing, or the file cannot be found.");
		if (urldecode(@$dataFond) == "")
			throw new Exception("The image data is missing, or corrupted. Please retry.");
		if (urldecode(@$dataDraw) == "")
			throw new Exception("The drawing data is missing, or corrupted. Please retry.");

		$fond = base64_decode(urldecode($dataFond), true);
		if (!$fond) throw new Exception("The image data is unreadable. Please retry.");
		$draw = base64_decode(preg_replace('#data:image/png;base64,#', '', urldecode($dataDraw)), true);
		if (!$draw) throw new Exception("The drawing data is unreadable. Please retry.");

		$sizeFond = getimagesizefromstring($fond);
		$sizeDraw = getimagesizefromstring($draw);

		$imgFond = imagecreatefromstring($fond);
		if (!$imgFond) throw new Exception("The image data is unreadable. Please retry.");
		$imgDraw = imagecreatefromstring($draw);
		if (!$imgDraw) throw new Exception("The drawing data is unreadable. Please retry.");

		imagesavealpha($imgDraw, true);
		imagealphablending($imgDraw, true);
		imagecopy($imgFond, $imgDraw, 0, 0, 0, 0, $sizeFond[0], $sizeFond[1]);
		$canvas  = imagecreatetruecolor($sizeDraw[0], $sizeDraw[1]);
		imagecopy($canvas, $imgFond, 0, 0, 0, 0, $sizeFond[0], $sizeFond[1]);

		$tmpName = $userID."_drawRetakeTemp.jpg";
		if (!imagejpeg($canvas, INSTALL_PATH.FOLDER_TEMP."uploads/retakes/$tmpName"))
			throw new Exception("Failed to save drawing image file to temp directory.");

		$d = new Infos(TABLE_DEPTS);
		$d->loadInfos('id', $dept);
		$deptName = $d->getInfo('label');

		$dir = preg_replace('#'.INSTALL_PATH.FOLDER_DATA_PROJ.'#', '', $pubFile);

		if (preg_match('#/sequences/#', $dir)) {
			require_once('admin_shots_fcts.php');
			$dir  = preg_replace('#[a-zA-Z0-9_]+/retakes/[a-zA-Z0-9_]+$#', '', $dir);
			move_retake($dir, $dept, $tmpName, true);
			Dailies::add_dailies_entry($idProj, Dailies::GROUP_SHOT, Dailies::TYPE_SHOT_MOD_PUBLISHED, '{"idShot":"'.$shotID.'","dept":"'.$deptName.'"}');
			if (isset($addMessage)) {
				$cm = new Comments('retake');
				$cm->initNewCommRetake($shotID, $dept);
				$cm->addText(stripslashes(urldecode($addMessage)));
				$cm->save();
				Dailies::add_dailies_entry($idProj, Dailies::GROUP_SHOT, Dailies::TYPE_SHOT_NEW_MESSAGE, '{"idShot":"'.$shotID.'","dept":"'.$deptName.'","txtMess":"'.urlencode($addMessage).'"}');
			}
		}
		elseif (preg_match('#/assets/#', $dir)) {
			require_once('assets_fcts.php');
			move_retake_asset($idProj, $dept, $nameAsset, $tmpName, true);
			Dailies::add_dailies_entry($idProj, Dailies::GROUP_ASSET, Dailies::TYPE_ASSET_MOD_PUBLISHED, '{"idAsset":"'.$idAsset.'","pathAsset":"'.$pathAsset.'","nameAsset":"'.$nameAsset.'","deptID":"'.$dept.'"}');
			if (isset($addMessage)) {
				$cm = new Comments('retake_asset');
				$cm->initNewCommRetake_asset($idAsset, $idProj, $dept);
				$cm->addText(stripslashes(urldecode($addMessage)));
				$cm->save();
				Dailies::add_dailies_entry($idProj, Dailies::GROUP_ASSET, Dailies::TYPE_ASSET_NEW_MESSAGE, '{"idAsset":"'.$idAsset.'","pathAsset":"'.$pathAsset.'","nameAsset":"'.$nameAsset.'","deptID":"'.$dept.'","txtMess":"'.urlencode($addMessage).'"}');
			}
			$retour['error'] = 'OK';
			$retour['message'] = 'message sauvegardé.';
		}
		elseif (preg_match('#/scenes/#', $dir)) {
			require_once('scenes_fcts.php');
			move_retake_scene($dept, $sceneID, $tmpName, true);
			Dailies::add_dailies_entry($idProj, Dailies::GROUP_SCENE, Dailies::TYPE_SCENE_MOD_PUBLISHED, '{"sceneID":"'.$sceneID.'","deptID":"'.$dept.'"}');
			if (isset($addMessage)) {
				$cm = new Comments('retake_scene');
				$cm->initNewCommRetake_scene($sceneID, $idProj, $dept);
				$cm->addText(stripslashes(urldecode($addMessage)));
				$cm->save();
				Dailies::add_dailies_entry($idProj, Dailies::GROUP_SCENE, Dailies::TYPE_SCENE_NEW_MESSAGE, '{"sceneID":"'.$sceneID.'","deptID":"'.$dept.'","txtMess":"'.urlencode($addMessage).'"}');
			}
		}
		else
			throw new Exception("The published type cannot be recognized.");

		$retour['error'] = "OK";
		$retour['message'] = "This drawing is saved, and replace the last published.";
	}

}
catch (Exception $e) {
	$retour['message'] = $e->getMessage();
}

echo json_encode($retour);