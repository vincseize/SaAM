<?php
	@session_start(); // lignes à placer toujours en haut du code des pages

	if(isset($_SESSION['INSTALL_PATH_INC'])){
		require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
        require_once($_SESSION['INSTALL_PATH'].'/classes/GarageImg.php');
	}
	if(file_exists("inc/checkConnect.php")) {
		require_once ("inc/checkConnect.php" );
        require_once("classes/GarageImg.php");
	}
	if(file_exists("../inc/checkConnect.php")) {
		require_once ("../inc/checkConnect.php" );
        require_once("../classes/GarageImg.php");
	}
	if(file_exists("../../inc/checkConnect.php")) {
		require_once ("../../inc/checkConnect.php" );
        require_once("../../classes/GarageImg.php");
	}
////////////////////////////////// RECUPERATION DES VIGNETTES SELON LEUR EXTENSION /////////////////////////////

// retourne le nom de la vignette d'un USER
function check_user_vignette_ext($id,$login) {
	$vignetteTest = FOLDER_DATA_USER . $id.'_'.$login.'/vignette';
	$vignette = check_ext($vignetteTest);
	return $vignette;
}

// retourne le nom de la vignette d'un PROJET
function check_proj_vignette_ext($id, $title, $withCache=false) {
	$vignetteTest = FOLDER_DATA_PROJ . $id.'_'.$title.'/vignette';
	$vignette = check_ext($vignetteTest);
	return ($withCache) ? $vignette : $vignette.'?_'.time();
}

// retourne le nom de la vignette d'une SÉQUENCE
function check_sequence_vignette_ext($idProj, $titleProj, $labelSeq) {
	$vignetteTest = FOLDER_DATA_PROJ . $idProj.'_'.$titleProj.'/sequences/'.$labelSeq.'/vignette';
	$vignette = check_ext($vignetteTest);
	return $vignette.'?_'.time();
}

// retourne le nom de la vignette d'un SHOT
function check_shot_vignette_ext($idProj, $labelSeq, $labelShot, $deptSpecified=false) {
	$p = new Projects($idProj);
	$dirProj = $p->getDirProject();
	if ($deptSpecified == false || $deptSpecified == 'structure') {			// recherche dans tous les depts, pour récup celle du dernier dept
		$depts = array_reverse($p->getDeptsProject(false, 'id'));
		$depts[] = 'storyboard';											// On ajoute le dept storyboard à la liste, qui n'est pas compris dans les depts du projet (BDD) mais qui est fixed
		foreach ($depts as $dept) {
			$vignetteTest = FOLDER_DATA_PROJ . $dirProj.'/sequences/'.$labelSeq.'/'.$labelShot.'/'.$dept.'/vignette';
			$vignette = check_ext($vignetteTest);
			if ($vignette !== 'gfx/novignette/novignette_seq.png') break;	// Si on en trouve une, on arrête de chercher
		}
	}
	else {																	// OU recherche dans un dept spécifique
		$vignetteTest = FOLDER_DATA_PROJ . $dirProj.'/sequences/'.$labelSeq.'/'.$labelShot.'/'.$deptSpecified.'/vignette';
		$vignette = check_ext($vignetteTest);
	}
	return $vignette.'?_'.time();
}

// retourne le nom de la vignette ASSET
function check_asset_vignette($pathAsset=false, $nameAsset=false, $idProj=false, $noCache=false) {
	if (!$pathAsset && !$nameAsset && !$idProj)
		return 'gfx/novignette/novignette_image.png';
	$p = new Projects($idProj);
	$dirProj = $p->getDirProject();
	$pathAsset = preg_replace('#\./#', '', $pathAsset);
	$vignetteTest = FOLDER_DATA_PROJ . $dirProj.'/assets/'.$pathAsset.'vignette_'.$nameAsset;
	$vignette = check_ext($vignetteTest, 'image');
	if ($noCache)
		return $vignette;
	return $vignette.'?_'.time();
}


// retourne le nom de la vignette SCENE
function check_scene_vignette($sceneID=false, $noCache=false) {
	if (!$sceneID)
		return 'gfx/novignette/novignette_image.png';
	$sc = new Scenes($sceneID);
	$vignetteTest = FOLDER_DATA_PROJ . $sc->getDirScene().'/scene_vignette';
	$vignette = preg_replace('/#/', '%23', check_ext($vignetteTest, 'image'));
	if ($noCache)
		return $vignette;
	return $vignette.'?_'.time();
}



// fonction générique de test de l'extension d'une vignette
function check_ext($vignetteTest, $type='seq') {
	$noVignette = 'gfx/novignette/novignette_'.$type.'.png';
	if (is_file(INSTALL_PATH.$vignetteTest.'.png')) return $vignetteTest.'.png';
	if (is_file(INSTALL_PATH.$vignetteTest.'.jpg')) return $vignetteTest.'.jpg';
	if (is_file(INSTALL_PATH.$vignetteTest.'.gif')) return $vignetteTest.'.gif';
	return $noVignette;
}


////////////////////////////////// CHECK RETAKES FILES /////////////////////////////


// trouve le fichier d'une retake
function check_retake_vignette_ext ($dirRetake, $vignetteName) {						// @TODO : récup de tous type de fichiers retake (même vidéos)
	$vignette = 'gfx/novignette/novignette_seq.png';
	$vignetteTest = FOLDER_DATA_PROJ . $dirRetake.'/'.$vignetteName;
	if (is_file(INSTALL_PATH.$vignetteTest.'.png')) $vignette = $vignetteTest.'.png';
	if (is_file(INSTALL_PATH.$vignetteTest.'.jpg')) $vignette = $vignetteTest.'.jpg';
	if (is_file(INSTALL_PATH.$vignetteTest.'.gif')) $vignette = $vignetteTest.'.gif';
	return $vignette.'?_'.time();
}


////////////////////////////////// ADD VIGNETTES POUR RECETTES /////////////////////////////

// add_vignette project
function add_vignette_user ($dirUser){
	$folderThumb = INSTALL_PATH.'/_RECETTE/vignettes/users/'; // random vignette plus sympa
	$file = RandomFileFolder($folderThumb,'jpg|png|gif');
	$newfile = INSTALL_PATH . FOLDER_DATA_USER .$dirUser.'/vignette.png';
	if(file_exists($file)){
			copy($file, $newfile);
			//chmod($path,0755);
			chmod($newfile,0755);
			return TRUE;
	}
	return FALSE;
}

// add_vignette project
function add_vignette_project ($dirProject){
	$folderThumb = INSTALL_PATH.'/_RECETTE/vignettes/projects/'; // random vignette plus sympa
	$file = RandomFileFolder($folderThumb,'jpg|png|gif');
	$newfile = INSTALL_PATH . FOLDER_DATA_PROJ .$dirProject.'/vignette.png';
	if(file_exists($file)){
			copy($file, $newfile);
			chmod($newfile,0755);
			return true;
	}
	return false;
}

// add_vignette shots
function add_vignette_shot ($dirShot){
	$folderThumb = INSTALL_PATH.'/_RECETTE/vignettes/shots/'; // random vignette plus sympa
	$file = RandomFileFolder($folderThumb,'jpg|png|gif');
	$path = INSTALL_PATH . FOLDER_DATA_PROJ .$dirShot;
	$newfile = $path.'/vignette.png';
	if(file_exists($file)){
			copy($file, $newfile);
	}
	return false;
}

////////////////////////////////// RATIO VIGNETTES /////////////////////////////

function ratio_vignettes($ratio,$size,$w,$h){

    switch ($ratio) {
        case '16:9':
            switch ($size){
                case 'medium':
                    $sizes = array(VIGNETTE_MEDIUM_W,VIGNETTE_MEDIUM_H);
                    return $sizes;
                    break;
            }

        case '4:3':

            break;
    }

}

////////////////////////////////// BANK VIGNETTES /////////////////////////////

function create_bankProj_thumb($dirBank, $file){
	$dir_thumbs = INSTALL_PATH.$dirBank."/thumbs";
	if(!is_dir($dir_thumbs)){
		mkdir($dir_thumbs);
	}

	$bankFile = INSTALL_PATH.$dirBank.'/'.$file;
	$mimeTypeFile = check_mime_type($bankFile);

	// Si fichier de type image
	if (preg_match('/image/i', $mimeTypeFile)) {
		$thumb = $dir_thumbs.'/thumb_'.$file;
		if(!is_file($thumb)){
			$image = new GarageImg();
			$image->load($bankFile);
			$image->resizeToThumb(80);
			$image->save($thumb);
			chmod($thumb, 0644);
		}
	}
	// Si fichier de type vidéo
	if (preg_match('/video/i', $mimeTypeFile)) {
		$vthumb = $dir_thumbs.'/vthumb_'.$file.'.gif';
		check_create_video_thumb($bankFile, $vthumb);
    }
}


function check_thumbDir ($path) {
	if (!is_dir(INSTALL_PATH.$path.'/thumbs'))											// Si le dossier des thumbs n'existe pas, on le crée
		mkdir(INSTALL_PATH.$path.'/thumbs', 0755, true);
}


// Vérification de l'existence d'un thumb pour l'image, et création si il existe pas
function check_create_thumb($imagePath=false) {
	if (!$imagePath) { return false; }
	if (!is_file($imagePath)) { return false; }									// Si l'image existe,
	$image_info = getimagesize($imagePath);
	if (!preg_match('/image/i', $image_info['mime'])) { return false; }			// et que c'est bien un fichier de mimetype image
	$photoName = basename($imagePath);
	$photoDir  = dirname($imagePath);
	if (!is_file($photoDir.'/thumbs/t_'.$photoName)) {							// Création du thumb, si il n'existe pas
		try {
			if (!is_dir($photoDir.'/thumbs'))											// Si le dossier des thumbs n'existe pas, on le crée
				mkdir($photoDir.'/thumbs');
			$image = new GarageImg();
			$image->load($imagePath);
			$image->resizeToThumb(60);
			$image->save($photoDir.'/thumbs/t_'.$photoName);
			return true;
		}
		catch (Exception $e) { return false; }
	}
	return true;
}


// Vérification de l'existence d'un thumb pour la vidéo, et création si il existe pas
function check_create_video_thumb($videoPath=false, $thumbPath=false, $duplicateOGV=false, $rate=1000, $width=80, $height=44, $debug=false) {
	if (!$videoPath) { return false; }
	if (!$thumbPath) { return false; }
	if (is_file($videoPath) && $duplicateOGV == true) {							// Copie du fichier avec l'extension OGV, si demandé (et si pas encore existant)
		$path = dirname($videoPath);
		$name = basename($videoPath);
		if (!preg_match('/\.ogv$/', $videoPath))
			copy($videoPath, $path.'/video_'.$name.'.ogv');
	}
	if (is_file($thumbPath)) {													// Si le thumb existe déjà on retourne TRUE pour passer la fonction tranquille
		if (@filesize($thumbPath) > 50)
			return true;
	}
	if (!is_file($videoPath)) { echo 'La vidéo n\'existe pas.'; return false; }									// Si la vidéo existe pas on quitte
	if (!is_int($rate) && !is_int($width) && is_int($height)) { echo 'argument invalide !'; return false; }		// Si les arguments ne sont pas des nombres entiers on arrête tout
	$vP = escapeshellarg($videoPath);																			// on échappe les caractères spéciaux qu'on envoie au shell

//	echo '*';				// Pour debug (si on a éxécuté le script, une étoile appparaît à côté de la thumb)
	exec('cd '.INSTALL_PATH."/fct; /bin/bash videoThumb.sh $vP $rate $width $height", $retourTableau, $errorStatus);			// Éxécution du script de conversion (bash)
	if ($debug === true) {
		foreach($retourTableau as $retourLine) echo $retourLine.'<br />';											// Affichage du résultat (débug seulement)
		echo "DONE.<br />Erreurs : $errorStatus";
	}

	if ($errorStatus == 0) {
		if (@filesize($thumbPath) > 50)				// Vérification que le gif ne soit pas vide (> 50 octets)
			return true;
	}
	return false;

}

?>
