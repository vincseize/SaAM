<?php

require_once ('directories.php' );


// Crée le fichier XML VIERGE formatté tout bien
function create_masterFile_assets ($idProj=false, $forceFormatXML=false) {
	if (!$idProj)	{ throw new Exception('create_masterFile_assets() : missing the project ID!'); }

	$p = new Projects($idProj);
	$dirProj	= $p->getDirProject();
	$assetsDir	= INSTALL_PATH . FOLDER_DATA_PROJ . $dirProj . '/assets/';
	$masterFile = $assetsDir.'masterFile_assets.xml';
	if (is_file($masterFile) && !$forceFormatXML)
		throw new Exception('create_masterFile_assets(): XML Masterfile already exists!');
	if (!is_dir($assetsDir))
		mkdir($assetsDir, 0755, true);

	$domXML = new DOMDocument('1.0', 'UTF-8');
	$domXML->preserveWhiteSpace = false;
	$domXML->formatOutput = true;
	$root = $domXML->createElement('root');
	$root->setAttribute('project_ID', $idProj);
	$root->setAttribute('project_name', $p->getTitleProject());
	$domXML->appendChild($root);
	return @$domXML->save($masterFile, LIBXML_NOEMPTYTAG);
}


// Crée un dossier dans le xml
function addFolderToXML ($idProj=false, $path=false) {
	if (!$idProj)	{ throw new Exception('addFolderToXML() : missing the project ID!'); }
	if (!$path)		{ throw new Exception('addFolderToXML() : missing folder path!'); }

	try { $xmlNotCreated = !create_masterFile_assets($idProj); }
	catch (Exception $e) { $xmlNotCreated = false; }
	if ($xmlNotCreated)
		throw new Exception ('addFolderToXML() : Unable to write XML file!');
	$p = new Projects($idProj);
	$dirProj	= $p->getDirProject();
	$assetsDir	= INSTALL_PATH . FOLDER_DATA_PROJ . $dirProj . '/assets/';
	$masterFile = $assetsDir.'masterFile_assets.xml';
	$path = preg_replace('#(^[\.]+[/]+)|([/]+$)#', '', $path);
	$path = preg_replace('#[/]{2,}#', '/', $path);
	$pathArr	= explode('/', $path);
	$parentxPath = '.'; $parentRealPath = '';
	$lastXp = $xml = simplexml_load_file($masterFile);
	foreach($pathArr as $fName) {
		$parentxPath .= '/dir[@name="'.$fName.'"]';
		$xph = $xml->xpath($parentxPath);
		if (count($xph) > 0) {
			$parentRealPath .= $fName.'/';
			$lastXp = $xph;
		}
		else {
			$newDir = $lastXp[0]->addChild('dir');
			$newDir->addAttribute('name', $fName);
			$pPath = ($parentRealPath == '') ? './' : $parentRealPath;
			$newDir->addAttribute('url', $pPath);
			$lastXp = $newDir;
		}
	}
	$dom = new DOMDocument('1.0', 'UTF-8');
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true;
	$dom->loadXML($xml->asXML());
	return $dom->save($masterFile);
}

// Supprime un dossier du xml
function removeFolderToXML ($idProj=false, $path=false) {
	if (!$idProj)	{ throw new Exception('removeFolderToXML() : missing the project ID!'); }
	if (!$path)		{ throw new Exception('removeFolderToXML() : missing folder path!'); }

	$p = new Projects($idProj);
	$dirProj	= $p->getDirProject();
	$assetsDir	= INSTALL_PATH . FOLDER_DATA_PROJ . $dirProj . '/assets/';
	$masterFile = $assetsDir.'masterFile_assets.xml';
	$path = preg_replace('#(^[\.]+[/]+)|([/]+$)#', '', $path);
	$path = preg_replace('#[/]{2,}#', '/', $path);
	$fDirName	= dirname($path);
	$folderName	= basename($path);

	$xml = simplexml_load_file($masterFile);
	$nodes = $xml->xpath('//dir[@name="'.$folderName.'"][@url="'.$fDirName.'/" or @url="./"]');
	if (count($nodes) < 1)
		throw new Exception('removeFolderToXML() : This folder ('.$path.') doesn\'t exists!');
	if (count($nodes) > 1)
		throw new Exception('removeFolderToXML() : There are multiple folders with this name ('.$path.')!!');
	if (isset($nodes[0]->dir))
		throw new Exception('removeFolderToXML() : This folder ('.$path.') contains a subfolder. You cannot remove it.');
	if (isset($nodes[0]->file))
		throw new Exception('removeFolderToXML() : This folder ('.$path.') contains some files. You cannot remove it.');

	@list($nodesXML) = $nodes;
	unset($nodesXML[0]);

	$dom = new DOMDocument('1.0', 'UTF-8');
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true;
	$dom->loadXML($xml->asXML());
	return $dom->save($masterFile);
}


// Ajoute un asset au XML
function addAssetToXML ($idProj, $idAsset) {
	if (!$idProj)	{ throw new Exception('addAssetToXML() : missing the project ID!'); }
	if (!$idAsset)	{ throw new Exception('addAssetToXML() : missing folder path!'); }

	$p = new Projects($idProj);
	$dirProj	= $p->getDirProject();
	$assetsDir	= INSTALL_PATH . FOLDER_DATA_PROJ . $dirProj . '/assets/';
	$masterFile = $assetsDir.'masterFile_assets.xml';

	$a = new Assets($idProj, (int)$idAsset);
	$aPath = $a->getPath();
	$path = preg_replace('#(^[\.]+[/]+)|([/]+$)#', '', $aPath).'/';
	$fDirName	= dirname($path);
	$folderName = basename($path);

	$xml = simplexml_load_file($masterFile);
	$dir = $xml->xpath('//dir[@name="'.$folderName.'"][@url="'.$fDirName.'/" or @url="./"]');
	if (count($dir) < 1)
		throw new Exception("The path '$path' doesn't exists in XML!<br />Check if folders exists. If not, you create it in tree view.");
	if (count($dir) > 1)
		throw new Exception("There are multiple folders with this name ($path)!!");
	$checkExists = (count($dir[0]->xpath('//file[@name="'.$a->getName().'"]')) > 0);
	if ($checkExists)
		throw new Exception("This folder '$path' already contains a file named '".$a->getName()."'!");

	$newFile = $dir[0]->addChild('file');
	$newFile->addAttribute('name', $a->getName());

	$dom = new DOMDocument('1.0', 'UTF-8');
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true;
	$dom->loadXML($xml->asXML());
	return $dom->save($masterFile);
}


// récupère la vignette d'asset du dossier temp (après upload)
function move_asset_vignette ($idProj=false, $nameAsset=false, $pathAsset=false, $vignetteName=false) {
	if (!$idProj)		{ throw new Exception('move_asset_vignette() : missing the project ID !'); }
	if (!$nameAsset)	{ throw new Exception('move_asset_vignette() : missing the name of the asset !'); }
	if (!$pathAsset)	{ throw new Exception('move_asset_vignette() : missing the path of the asset !'); }
	if (!$vignetteName)	{ throw new Exception('move_asset_vignette() : missing vignette name !'); }

	$pathAsset	  = preg_replace('#\./#', '', $pathAsset);
	$p = new Projects($idProj);
	$dirProj = $p->getDirProject();
	$destDir = INSTALL_PATH . FOLDER_DATA_PROJ . $dirProj . '/assets/'. $pathAsset;
	$tempVignette = INSTALL_PATH .'temp/uploads/vignettes/'.$vignetteName;
	$vNameArr	  = explode('.', $vignetteName);
	$vExt		  = $vNameArr[count($vNameArr)-1];
	if (!is_dir($destDir))
		mkdir($destDir, 0755, true);
	$destVignette = $destDir . 'vignette_' . $nameAsset . '.' ;
	@unlink ($destVignette.'gif');
	@unlink ($destVignette.'jpg');
	@unlink ($destVignette.'png');
	if (!is_file($tempVignette))
		{ throw new Exception('move_asset_vignette() : temp vignette file not found !'); }
	if (!@copy($tempVignette, $destVignette.$vExt))
		{ throw new Exception('move_asset_vignette() : unable to copy vignette file to : '.$destVignette.$vExt.' !'); }
	if (!@unlink($tempVignette))
		{ throw new Exception('move_asset_vignette() : unable to delete temp vignette file !'); }
}


// récupère la retake du dossier temp (après upload)
function move_retake_asset ($idProj=false, $deptID=false, $nameAsset=false, $retakeTempName=false, $isReplace=false) {
	if (!$idProj)			{ throw new Exception('move_retake_asset() : missing project ID !'); }
	if (!$deptID)			{ throw new Exception('move_retake_asset() : missing department ID !'); }
	if (!$nameAsset)		{ throw new Exception('move_retake_asset() : missing the asset name !'); }
	if (!$retakeTempName)	{ throw new Exception('move_retake_asset() : missing $retakeTempName !'); }

	$a = new Assets($idProj, $nameAsset);
	$destAssetDir	= INSTALL_PATH . FOLDER_DATA_PROJ . $a->getDirRetakes($idProj, $deptID);
	$wipAssetDir	= INSTALL_PATH . FOLDER_DATA_PROJ . $a->getDirAssetDatas($idProj, $deptID) . 'wip/';
	$retakeTempFile = INSTALL_PATH .'temp/uploads/retakes/'.$retakeTempName;
	$retakeName		= 'retake_0';
	if (!is_dir($destAssetDir))
		mkdir($destAssetDir, 0755, true);
	if (!is_dir($wipAssetDir))
		mkdir($wipAssetDir, 0755, true);

	$nbRetake = count(glob($destAssetDir.'retake*'));
	$nbWips	  = count(glob($wipAssetDir.'*'));
	if (!is_file($retakeTempFile))
		{ throw new Exception('move_retake() : temp retake file not found!'); }
	if (is_file($destAssetDir.$retakeName)) {
		if ($isReplace) {
			$mimeOldRetake = explode(';',check_mime_type_info($destAssetDir.$retakeName));
			$typeOldRetake = recup_file_ext($mimeOldRetake[0]);
			if (!copy($destAssetDir.$retakeName, $wipAssetDir.'backup_retake_'.$nbWips.$typeOldRetake))
				{ throw new Exception('move_retake() : unable to move old retake to WIP dir!'); }
		}
		else {
			if (!rename($destAssetDir.$retakeName, $destAssetDir.'retake_'.$nbRetake))
				{ throw new Exception('move_retake() : unable to rename old retake !'); }
		}
		@unlink($destAssetDir.'thumbs/vthumb_retake_0.gif');
	}
	if (!copy($retakeTempFile, $destAssetDir.$retakeName))
		{ throw new Exception('move_retake() : unable to copy new retake file to : '.$destAssetDir.'!'); }
	if (!unlink($retakeTempFile))
		{ throw new Exception('move_retake() : unable to delete temp retake file...'); }
}


// récupère le fichier temp du masterfile uploadé pour le mettre au bon endroit avec le bon nom
function move_masterfile_asset ($idProj, $tempMFname) {
	if (!$idProj)		{ throw new Exception('move_masterfile_asset() : missing project ID !'); }
	if (!$tempMFname)	{ throw new Exception('move_masterfile_asset() : missing temp name !'); }

	$p = new Projects($idProj);
	$dirProj = $p->getDirProject();
	$destAssetDir	= INSTALL_PATH . FOLDER_DATA_PROJ . $dirProj . '/assets/';
	$MFtempFile		= INSTALL_PATH .'temp/uploads/'.$tempMFname;

	if (!is_dir($destAssetDir))
		mkdir($destAssetDir, 0755, true);

	if (!is_file($MFtempFile))
		{ throw new Exception('move_masterfile_asset() : temp masterFile not found !'); }
	if (!copy($MFtempFile, $destAssetDir.'masterFile_assets.xml'))
		{ throw new Exception('move_masterfile_asset() : unable to copy masterFile to : '.$destAssetDir.' !'); }
	if (!unlink($MFtempFile))
		{ throw new Exception('move_masterfile_asset() : unable to delete temp masterFile !'); }
}


// Fonction de destruction en BDD de tout les assets d'un projet ( !!!!!! BEWARE !!!!!! )
function delete_all_assets_project ($idProj) {
	$l = new Liste();
	$assList = $l->getListe(TABLE_ASSETS, 'id', 'id', 'ASC', Assets::ASSET_ID_PROJECTS, '=', '["'.$idProj.'"]');
	if (is_array($assList)) {
		foreach($assList as $assId)
			delete_asset($idProj, $assId);
	}
}

// Supprime un asset
function delete_asset ($idProj, $assetID) {
	$l = new Liste();
	$assComList = $l->getListe(TABLE_COMM_ASSET, 'id', 'id', 'ASC', Comments::COMM_ID_ASSET, '=', $assetID);
	if (is_array($assComList)) {
		foreach($assComList as $assComId) {
			$assCom = new Comments('retake_asset', (int)$assComId);
			$assCom->delete();
		}
	}
	$assDptList = $l->getListe(TABLE_ASSETS_DEPTS, 'id', 'id', 'ASC', Comments::COMM_ID_ASSET, '=', $assetID);
	if (is_array($assDptList)) {
		foreach($assDptList as $assDptId) {
			$i = new Infos(TABLE_ASSETS_DEPTS);
			$i->loadInfos('id', $assDptId);
			$i->delete();
		}
	}
	$ass = new Assets($idProj, (int)$assetID);
	$ass->delete();
}


// Ajouter une extension aux SaAM defaults (config)
function add_default_extension ($ext) {
	if (strlen($ext) < 2)
		throw new Exception('add_default_extension() : $ext too short.');

	$conf = new Infos(TABLE_CONFIG);
	$conf->loadInfos('version', SAAM_VERSION);
	$oldExts = json_decode($conf->getInfo('available_assets_extensions'));
	$oldExts[] = $ext;
	$conf->addInfo('available_assets_extensions', json_encode($oldExts));
	$conf->save();
}

?>
