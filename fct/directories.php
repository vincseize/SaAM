<?php
@session_start();

if(isset($_SESSION['INSTALL_PATH']))
	require_once ($_SESSION['INSTALL_PATH'].'inc/checkConnect.php' );
if(file_exists('inc/checkConnect.php'))
	require_once ('inc/checkConnect.php' );
if(file_exists('../inc/checkConnect.php'))
	require_once ('../inc/checkConnect.php' );
if(file_exists('../../inc/checkConnect.php'))
	require_once ('../../inc/checkConnect.php' );

require_once('users_fcts.php');
require_once('dates.php');
require_once('vignettes_fcts.php');

/**
 *
 * Crée un dossier au nom aléatoire
 * @param STRING $folder
 * @param STRING $extensions
 * @return STRING Un nom de dossier zarbi
 *
 */

function RandomFileFolder($folder='', $extensions='.*') {
    $folder = trim($folder);
    $folder = ($folder == '') ? './' : $folder;
    if (!is_dir($folder)) { die('invalid folder given!'); }
    $files = array();
    if ($dir = @opendir($folder)) {
        while($file = readdir($dir)) {
            if (!preg_match('/^\.+$/', $file) && preg_match('/\.('.$extensions.')$/', $file))
                $files[] = $file;
        }
        closedir($dir);
    }
    else die('Could not open the folder "'.$folder.'"');
    if (count($files) == 0) die('No files where found :-(');
    mt_srand((double)microtime()*1000000);		// seed random function
    $rand = mt_rand(0, count($files)-1);		// get an random index
    if (!isset($files[$rand])) die('Array index was not found! very strange!');
    return $folder . $files[$rand];				// return the random file:
}

/**
 *
 * Création d'un dossier dans les datas PROJECTS
 * @param STRING $dirName The directory name to create
 * @return BOOL True if no error
 *
 */
function makeDataDir($dirName) {
    $folderData = INSTALL_PATH . FOLDER_DATA_PROJ;
	return (@mkdir( $folderData . $dirName, 0755, true));
}

/**
 *
 * Création d'un sous-dossier dans le dossier TEMP
 * @param STRING $dirName The directory name to create
 * @return BOOL True if no error
 *
 */
function makeTempDir($dirName) {
    $folderTemp = INSTALL_PATH . FOLDER_TEMP;
	return (@mkdir( $folderTemp . $dirName, 0755, true));
}

/**
 *
 * Création d'un dossier dans les datas USERS
 * @param STRING $dirName The directory name to create
 * @return BOOL True if no error
 *
 */
function makeDataDir_user($dirName) {
	$folderUserData = INSTALL_PATH . FOLDER_DATA_USER;
	@chmod($folderUserData, 0755);
	return (@mkdir($folderUserData . $dirName, 0755, true));
}

/**
 * Recursive removal of a directory. !! BE CAREFUL !!
 *
 * Suppression de dossier de façon récursive. !! ATTENTION !!
 *
 * @param STRING $directory Path to the directory to delete.
 * @param BOOL $empty True if you want to check if the dir is empty before delete it. Default to FALSE !!
 * @return boolean True if no error
 *
 */

function rmDir_R ($directory, $empty = false) {
    //echo $directory; exit;
	if (substr($directory,-1) == '/')
		$directory = substr($directory,0,-1);
	if( !file_exists($directory) || !is_dir($directory))
		return false;
	elseif (is_readable($directory)) {
		$handle = opendir($directory);
		while (FALSE !== ($item = readdir($handle))) {
			if($item != '.' && $item != '..') {
				$path = $directory.'/'.$item;
				if(is_dir($path))
					rmDir_R($path);
				else
					unlink($path);
			}
		}
		closedir($handle);
		if($empty == false) {
			if(!rmdir($directory))
				return false;
		}
	}
	return true;
}

/**
 * List a directory content
 * @param STRING $dir The directory path
 * @param STRING $filter Can be "all", "files" to get only files, or "subdir" to get only sub-directories
 * @return Array List of files and / or sub-dirs in the specified dir
 *
 */

function listDir($dir, $filter='all'){
	$dirList = array();
    $parentDir = opendir(INSTALL_PATH . $dir);
    while($item = readdir($parentDir)) {
		if ($filter=='files') {
			if ($item == '.gitignore') continue;
			if (is_file(INSTALL_PATH . $dir.'/'.$item))
				$dirList[] = $item;
		}
		elseif ($filter=='subdir') {
			if ($item =='.' || $item =='..') continue;
			if (is_dir(INSTALL_PATH . $dir.'/'.$item))
				$dirList[] = $item;
		}
		else $dirList[] = $item;
    }
    closedir($parentDir);
    return $dirList;
}


/**
 *
 * Récupération de la liste des départements
 * @param BOOL $withHidden TRUE to show hidden departments
 * @param STRING $type The department category (shots, assets, or scenes)
 * @return Array List of departments for the specified category.
 *
 */

function get_dpts($withHidden=true, $type="shots") {
	$departments = array();
	$dl = new Liste();
	$dl->addFiltre('type', '=', $type, 'AND');
	$dl->getListe(TABLE_DEPTS);
	$depList = $dl->simplifyList('position');
	ksort($depList);
	foreach ($depList as $dept) {
		if ($dept['hide'] == 1 && $withHidden === false) continue;
		$departments[$dept['id']] = strtoupper($dept['label']);
	}
	return $departments;
}


/**
 * Récupération de l'ID d'un dept en fonction de son label
 * @param STRING $dept LABEL du département
 * @param STRING $type Le type de département ('shots', 'assets', 'scenes')
 * @return int ID du département
 */
function get_ID_dept($dept, $type) {
	$di = new Infos(TABLE_DEPTS);
	try {
		$di->loadInfos('label', $dept);
		$idDept = $di->getInfo('id');
		if ($di->nbInfos() > 1) {
			foreach ($di->getInfo() as $deptInfs) {
				if (@$deptInfs['type'] == $type)
					$idDept = $deptInfs['id'];
			}
		}
		return $idDept ;
	}
	catch (Exception $e) { return false; }
}


/**
 * Récupération d'un label de département en fonction de son ID
 * @param INT $dept ID du département à récupérer
 * @return string label du département
 */
function get_label_dept($dept) {
	try {
		$deptLabel = $dept;
		if (is_int($dept)) {
			$di = new Infos(TABLE_DEPTS);
			$di->loadInfos('id', $dept);
			$deptLabel = $di->getInfo('label');
		}
		return $deptLabel;
	}
	catch (Exception $e) { return false; }
}


/**
 *
 * @param INT $idProj The project's ID
 * @param STRING $titleProj The project's title
 * @param STRING $folder The folder you want
 * @return Array List of the files into the specified folder
 *
 *
 */
function listDirBank_Project($idProj, $titleProj, $folder='root'){
	if ($folder == 'root') {
		$bankRoot = glob(INSTALL_PATH.FOLDER_DATA_PROJ.$idProj.'_'.$titleProj.'/bank/*');
		sort($bankRoot);
		$bankRootOK = array();
		foreach($bankRoot as $bankFolder) {
			if (is_dir($bankFolder))
				$bankRootOK[] = basename($bankFolder);
		}
		return $bankRootOK;
	}
	else {
		$bank = glob(INSTALL_PATH.FOLDER_DATA_PROJ.$idProj.'_'.$titleProj.'/bank/'.$folder.'/*');
		usort($bank, "sort_by_mtime");
		$bankOK = array();
		foreach($bank as $bankItem) {
			if (is_file($bankItem))
				$bankOK[] = basename($bankItem);
		}
		return $bankOK;
	}
}


/**
 *
 * @param INT $idProject Project ID
 * @return array the project's size in KB, MB or GB, and other informations :
 * 0 => Project size,
 * 1 => max size,
 * 2 => percent used,
 * 3 => project size in KB
 *
 */
function get_project_size ($idProject) {
    $pp = new Projects($idProject);
    $titleProj = $pp->getTitleProject();
    $path_project	= escapeshellarg(INSTALL_PATH.FOLDER_DATA_PROJ.$idProject."_".$titleProj);
	$project_size_R	= exec("du -s $path_project | grep -o '^[0-9]*'");
    $project_size_H = exec("du -sh $path_project | grep -o '^[0-9\.]*[K|M|G]'");
	$project_size	= preg_replace('/(K|M|G)/', ' $1B', $project_size_H);

    $max_size_real	= $_SESSION['CONFIG']['projects_size'] + SIZE_SECU_PROJECT;
    $max_size		= $_SESSION['CONFIG']['projects_size'];
    $pourcent		= number_format($project_size_R/$max_size * 100, 1);

    return array(
		$project_size,
		$max_size_real/1024,
		$pourcent,
		$project_size_R
	);
}

/**
 * Récupère la taille d'un dossier d'un projet
 * @param INT $idProject ID du projet à charger
 * @param STRING $folder NOM du sous-dossier à analyser
 * @return INT La taille du sous-dossier
 */
function get_project_folder_size ($idProject, $folder) {
	$pp = new Projects($idProject);
    $titleProj = $pp->getTitleProject();
    $path = escapeshellarg(INSTALL_PATH.FOLDER_DATA_PROJ.$idProject."_".$titleProj.'/'.$folder);
	$folder_size	= exec("du -s $path | grep -o '^[0-9]*'");
	return (int)$folder_size;
}


/**
 *
 * @param STRING $filePath File path
 * @return string The mime type of the file
 *
 */
function check_mime_type ($filePath) {
	exec("file -b '$filePath'", $output);
	return $output[0];
}

/**
 *
 * @param STRING $filePath File path
 * @return string The mime type of the file with other informations
 *
 */
function check_mime_type_info ($filePath) {
	exec("file -bi '$filePath'", $output);
	return $output[0];
}

/**
 *
 * @param STRING $contentType The mime type information returned by check_mime_type_info()
 * @return string The right file extension (suffix) for its mime type
 *
 */
function recup_file_ext ($contentType) {
	$map = array(
		'application/pdf'   => '.pdf',
		'application/zip'   => '.zip',
		'image/gif'         => '.gif',
		'image/jpeg'        => '.jpg',
		'image/png'         => '.png',
		'text/plain'        => '.txt',
		'text/xml'          => '.xml',
		'video/ogg'			=> '.ogv'
	);
	if (isset($map[$contentType]))
		return $map[$contentType];
	$pieces = explode('/', $contentType);
	return '.' . array_pop($pieces);
}


/**
 * Tri des fichiers d'un array selon sa date de dernière modification
 * @param STRING $file1 One file to compare
 * @param STRING $file2 Another file to compare
 * @return INT 0, 1, -1 as sorting spec.
 *
 */
function sort_by_mtime($file1, $file2) {
	$time1 = filemtime($file1);
	$time2 = filemtime($file2);
	if ($time1 == $time2) return 0;
	return ($time1 < $time2) ? 1 : -1;
}

/////////////////////////////////////////////////////////////////////////////// ARCHIVAGE ZIP D'UN DOSSIER

/**
 *
 * @param STRING $source The source file path
 * @param STRING $destination The destination zip file path
 * @return void
 *
 */
function Zip ($source, $destination) {
    if (!extension_loaded('zip'))	{ throw new Exception('Zip extension is missing!'); }
	if (!file_exists($source))		{ throw new Exception('Source dir (or file) does not exists!'); }
    $zip = new ZipArchive();
    if (!$zip->open($destination, ZIPARCHIVE::CREATE))	{ throw new Exception('Unable to open ZIP instance!'); }

    $source = str_replace('\\', '/', realpath($source));
    if (is_dir($source) === true) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($files as $file) {
            $file = str_replace('\\', '/', $file);
            // Ignore "." and ".." folders
            if ( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
                continue;

            $file = realpath($file);
            if (is_dir($file) === true) {
                $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
            }
            else if (is_file($file) === true) {
                $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
            }
        }
    }
    else if (is_file($source) === true) {
        $zip->addFromString(basename($source), file_get_contents($source));
    }

    $zip->close();
}


?>
