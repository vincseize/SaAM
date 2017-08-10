<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	if (!($_SESSION['user']->isSupervisor() || $_SESSION['user']->isDemo())) die('{"error":"error", "message":"function project : Access denied."}');

require_once ('dates.php' );
require_once ('directories.php' );
require_once ('admin_sequences_fcts.php' );
require_once ('admin_shots_fcts.php' );
require_once ('scenes_fcts.php' );
require_once ('assets_fcts.php' );

// Création rapide d'un projet (1ere étape)
function add_project ($values, $recette=false) {
	if (!is_array($values)) {
		throw new Exception('$values : not an array !');
		return;
	}
	$p = new Projects();
	foreach ($values as $valName => $val) {
		if ($valName == 'vignette') continue;
		if ($valName == 'date' || $valName == 'deadline') {
			$d = new DateTime($val);
			$val = $d->format('Y-m-d 00:00:00');
		}
		$p->setValue($valName, $val);
		if (($valName == 'project_type' && $val == 'demo') || $_SESSION["user"]->isDemo())
			$p->setDemo(1);																// si type démo ou créateur démo, on active le mode démo
	}
	$creator = $_SESSION["user"]->getUserInfos(Users::USERS_ID);
	$p->setHide(1);																		// hide par défaut
	$p->setCreator($creator);
	$p->setEquipe(json_encode(array($creator)));
	$p->setPosition(Liste::getMax(TABLE_PROJECTS, 'position')+1);
	$p->save();
	unset($p);
	$idProj = Liste::getMax(TABLE_PROJECTS, 'id');
	$_SESSION["user"]->addProjectToUser($idProj);
	$_SESSION["user"]->save();
																						// ajoute le projet aux users ROOT (pour omniprésence tout puissante :P) Sauf si creator déjà root bien sur
	if (!$_SESSION['user']->isRoot()) {
		$l = new Liste();
		$l->getListe(TABLE_USERS, 'id,login,status', 'id', 'ASC', Users::USERS_STATUS, "=", Users::USERS_STATUS_ROOT);
		$rootUsers = $l->simplifyList('id');
		$l->resetFiltre();
		foreach($rootUsers as $rootU) {
			$u = new Users($rootU['login']);
			$u->addProjectToUser($idProj);
			$u->save();
		}
	}

    if(!$recette) createProjectFolder($idProj,$values['title'], $values['vignette']);			// crée les dossiers (si pas recette)
}


// création du dossier du projet et des sous dossiers depts et assets (1ere étape)
function createProjectFolder ($idProj,$title, $vignetteTemp=false, $foldersassets='') {
	$p = new Projects();				// récup ID du projet selon son titre
	$p->loadFromBD('title', $title);
	$dirProject = $p->getDirProject();
	// crée le dossier du projet
	if (!makeDataDir($dirProject))
		throw new Exception('create project folder failed.');
	// crée dossier bank du projet
	if (!makeDataDir($dirProject.'/bank'))
		throw new Exception('create folder "bank" failed.');
	// crée dossier bank/thumbs du projet
	if (!makeDataDir($dirProject.'/bank/thumbs'))
		throw new Exception('create folder "bank thumbs" failed.');
	// crée dossier temp/bank du projet
	if (!makeTempDir('uploads/banks/'.$dirProject))
		throw new Exception('create folder "upload" failed.');
	// crée dossier assets du projet
	if (!makeDataDir($dirProject.'/assets'))
		throw new Exception('create folder "assets" failed.');
	// crée les sous dossiers des assets
	if ($foldersassets=='') $folders_assets = $_SESSION['CONFIG']['DEFAULT_ASSETS_DIRS'];
	else {
		$fA = stripslashes(urldecode($foldersassets));
		$folders_assets = json_decode($fA, true);
	}
	foreach ($folders_assets as $assetDir) {
		if (!makeDataDir($dirProject.'/assets/'.$assetDir))
			throw new Exception('create asset folder "'.$assetDir.'" failed.');
	}
	// crée dossier depts du projet
	if (!makeDataDir($dirProject.'/sequences'))
		throw new Exception('create sequences folder failed.');
	unset($p);

	if ($vignetteTemp)
		move_uploaded_vignette($dirProject, $vignetteTemp);	// ajoute la vignette
}



// récupère la dernière vignette uploadée dans /temp, pour la mettre à la racine du dossier du projet
function move_uploaded_vignette ($dirProj, $vignetteName) {
	if (!$dirProj && $dirProj == '')			{ throw new Exception('missing project directory !'); return; }
	if (!$vignetteName && $vignetteName == '')	{ throw new Exception('missing vignette name !'); return; }
	$tempVignette = INSTALL_PATH .'temp/uploads/vignettes/'.$vignetteName;
	$vNameArr = explode('.', $vignetteName);
	$vExt = $vNameArr[count($vNameArr)-1];
	$destVignette = INSTALL_PATH . FOLDER_DATA_PROJ . $dirProj . '/vignette.' ;
	@unlink ($destVignette.'gif');
	@unlink ($destVignette.'jpg');
	@unlink ($destVignette.'png');
	if (!is_file($tempVignette)) { throw new Exception('missing temp vignette file !'); return; }
	if (!copy($tempVignette, $destVignette.$vExt)) { throw new Exception('unable to copy vignette file to : '.$destVignette.$vExt.' !'); return; }
	if (!unlink($tempVignette)) { throw new Exception('unable to delete temp vignette !'); }
}


// ajoute les artistes à l'équipe du projet créé (2eme étape)
function add_team_project($title, $team) {
	$p = new Projects();				// récup ID du projet selon son titre
	$p->loadFromBD('title', $title);
	$idProj = $p->getProjectInfos('id');
	if ($idProj == 1 && !$_SESSION['user']->isRoot()) { throw new Exception('the DEMO project can\'t be modified. Please make your own.'); return; }
	$p->setEquipe($team);
	$p->save();
	unset($p);
	foreach(json_decode($team) as $usr) {
		$u = new Users((int)$usr);
		$u->addProjectToUser($idProj);
		$u->save();
		unset($u);
	}
}


// réorganise les positions des projets en BDD (2eme étape)
function reorganise_projects ($newPositions) {
	if (!is_array($newPositions)) {
		throw new Exception('$newPositions : not an array !');
		return;
	}
	foreach ($newPositions as $idProj => $posProj) {
		$p = new Projects($idProj);
		$p->setPosition($posProj);
		$p->save();
	}
}

// modif de la liste des départements
function mod_depts_project ($deptsList, $idProj=false, $title=false, $newProj=true) {
	if (!$idProj && !$title) { throw new Exception('mod_depts : missing $idProj AND $title !'); return; }
	$p = new Projects();					// chargement du projet
	if ($idProj)							// selon son titre
		$p->loadFromBD('id', $idProj);
	if ($title)								// ou selon son ID
		$p->loadFromBD('title', $title);
	if ($idProj == 1 && !$_SESSION['user']->isRoot()) { throw new Exception('the DEMO project can\'t be modified. Please make your own.'); return; }
	if ($newProj)
		$p->setDpts(json_encode($deptsList));
	else
		$p->modDpts($deptsList);
	$p->save();
}

// modif du champ "hide" d'un projet
function mod_hide_project($idProj=false, $hide=false) {
	if (!$idProj || $hide === false) { throw new Exception('$idProj or $hide undefined !'); return; }
//	if ($idProj == 1) { throw new Exception('the DEMO project can\'t be hidden. Please make your own.'); return; }
	$p = new Projects($idProj);
	$title = $p->getTitleProject();
	$p->setHide($hide);
	$p->save();
	return $title;
}

// modif du champ "lock" d'un projet
function mod_lock_project($idProj=false, $lock=false) {
	if (!$idProj || $lock === false) { throw new Exception('$idProj or $lock undefined !'); return; }
	if ($idProj == 1) { throw new Exception('the DEMO project can\'t be locked. Please make your own.'); return; }
	$p = new Projects($idProj);
	$p->setLock($lock);
	$p->save();
}

// modif du champ "lock" d'un projet
function mod_title_project($idProj=false, $newTitle=false, $saveBDD=true) {
	if (!$idProj || $newTitle === false) { throw new Exception('$idProj or $newTitle undefined !'); return; }
	if ($idProj == 1 && !$_SESSION['user']->isRoot()) { throw new Exception('the DEMO project can\'t be modified. Please make your own.'); return; }
	$zp = new Projects($idProj);
	$oldName = $idProj.'_'.$zp->getProjectInfos('title');
	if ($saveBDD) {
		$zp->setTitle($newTitle);
		$zp->save();
	}

	if (!@rename(INSTALL_PATH . FOLDER_DATA_PROJ . $oldName, INSTALL_PATH . FOLDER_DATA_PROJ . $idProj.'_'.$newTitle))
		throw new Exception('impossible de renommer le dossier data du projet !');
}

// modif de valeurs de projet (array des valeurs attendu)
function modif_project( $values, $ID_project=false ) {
	if (!$ID_project) { throw new Exception('modif_project: $idProj undefined !'); return; }
	if ($ID_project == 1 && !$_SESSION['user']->isRoot()) { throw new Exception('the DEMO project can\'t be modified. Please make your own.'); return; }
	if (!is_array($values)) { throw new Exception('modif_project: $values not an array !'); return; }
	$p = new Projects($ID_project);
	foreach($values as $row => $val) {
		if ($row == '') continue;
		if ($ID_project == 1 && $row == Projects::PROJECT_TITLE) continue;
		if ($ID_project == 1 && $row == Projects::PROJECT_COMPANY) continue;
		if ($ID_project == 1 && $row == Projects::PROJECT_TYPE) continue;
		if ($row == Projects::PROJECT_TITLE) {
			mod_title_project($ID_project, $val, false);
		}
		$p->setValue($row, $val);
	}
	$p->save();
}


// modif du champ "archive" d'un projet
function archive_project($idProj=false, $newState=false) {
	if (!$idProj || $newState === false) { throw new Exception('$idProj or $newState undefined !'); return; }
	if ($idProj == 1) { throw new Exception('the DEMO project can\'t be archived. Please make your own.'); return; }
	$p = new Projects($idProj);
	$p->setArchive($newState);
	if ($newState == 0){
		$p->setHide(1);
		$p->setLock(0);
	}
	$p->save();
}


// Destruction d'un projet, dans les règles ! (archivage zip et backup SQL avant delete folders et BDD)
function destroy_project ($idProj=false) {
	if ($idProj === false) { throw new Exception('$idProj undefined !'); }
	if ($idProj == 1) { throw new Exception('the DEMO project can\'t be destroyed. Please make your own.'); }

	createZip_project($idProj);

	// suppression des dailies du projet en BDD
	try { Dailies::delete_all_dailies_project($idProj); }
	catch (Exception $e) { if($e->getCode() != 404) throw new Exception('Some dailies could not be removed. Detail: '.$e->getMessage()); }
	// suppression des sequences et shots du projet en BDD
	try { delete_all_sequences_project($idProj); }
	catch (Exception $e) { if($e->getCode() != 404) throw new Exception('Some shots and sequences could not be removed. Detail: '.$e->getMessage()); }
	// suppression des scenes du projet en BDD
	try { delete_all_scenes_project($idProj); }
	catch (Exception $e) { if($e->getCode() != 404) throw new Exception('Some scenes could not be removed. Detail: '.$e->getMessage()); }
	// suppression des assets du projet en BDD
	try { delete_all_assets_project($idProj); }
	catch (Exception $e) { if($e->getCode() != 404) throw new Exception('Some assets could not be removed. Detail: '.$e->getMessage()); }
	// Suppression du projet en BDD
	try {
		$p = new Projects($idProj);
		$title  = $p->getTitleProject();
		$folder[0] = FOLDER_DATA_PROJ . $p->getDirProject();	// dossiers à supprimer : data/projects/projName, temp/upload/banks/projName
		$folder[1] = FOLDER_TEMP . 'uploads/banks/'.$idProj."_".$title;
		$p->delete();
		unset($p);
	}
	catch (Exception $e) { throw new Exception('The project could not be removed. Detail: '.$e->getMessage()); }
	// Suppression des dossiers du projet
	$error = "";
	foreach ($folder as $directory) {
		if (!file_exists(INSTALL_PATH.$directory)) continue;
		if (!rmDir_R(INSTALL_PATH.$directory)) { $error .= "Removing $directory : failed! Please check tree.\n"; continue; }
	}
	if ($error != "") { throw new Exception ($error); }
}


// Création d'un ZIP avec les données du projet, et un dump SQL
// @returns : STRING Le nom du fichier qui vient d'être créé.
function createZip_project ($idProj=false) {
	if ($idProj === false) { throw new Exception('createZip_project : $idProj undefined !'); }
	if ($idProj == 1) { throw new Exception('the DEMO project can\'t be zipped and downloaded. Please make your own.'); }
	$p = new Projects($idProj);
	$title  = $p->getTitleProject();
	$zipFile = INSTALL_PATH . FOLDER_DATA_PROJ . "Backup_SaAM_".preg_replace('/ /', '_', $title)."_".date('Y-m-d').".zip";
	// Création d'un fichier SQL d'export des datas du projet
	require_once('sql_utils_fct.php');
	try { backup_SQL_project($idProj, FOLDER_DATA_PROJ.$p->getDirProject().'/SaAM-DB_backup_'.preg_replace('/ /', '_', $title).'_'.date('Y-m-d').'.sql'); }
	catch(Exception $e) { throw new Exception('The database cannot be saved to file! '.$e->getMessage()); }
	// Backup du dossier du projet dans un ZIP
	try { Zip(INSTALL_PATH.FOLDER_DATA_PROJ.$p->getDirProject(), $zipFile); }
	catch(Exception $e)	{ throw new Exception ('Archiving datas into zip file failed! '.$e->getMessage()); }
	return basename($zipFile);
}


function reorganise_departements($newPositions) {
	if (!is_array($newPositions))
		throw new Exception('reorganise_departements :: $newPositions is not an array !');
	foreach ($newPositions as $idDept => $posDept) {
		$d = new Infos(TABLE_DEPTS);
		$d->loadInfos('id', $idDept);
		$d->addInfo('position', $posDept);
		$d->save();
		unset($d);
	}
}


// !!!!!!!!!!!!!!!!!!!!!! RESTRICTED TO RECETTE ONLY !!!!!!!!!!!!!!!!!!!!!!!!!!!
// Supprime un projet en BDD et ses dossiers
function delete_project ($ID_project=false, $title=false, $forceToClean=false) {
	if (!$_SESSION['user']->isDev() && !$forceToClean) throw new Exception('delete_project : Access denied.');
    if (!$ID_project && !$title) { throw new Exception('delete_project : missing $ID_project AND $title (at least ONE should be defined) !');}
	$p = new Projects();								// chargement du projet
	if ($ID_project)										// selon son ID
		$p->loadFromBD(Projects::PROJECT_ID_PROJECT, (int)$ID_project);
	elseif ($title)											// ou selon son titre
		$p->loadFromBD(Projects::PROJECT_TITLE, $title);
	$projID = $p->getIDproject();
	if ($projID == 1) { throw new Exception('the DEMO project can\'t be deleted. Please make your own.'); return; }
	$folder = $p->getDirProject();
    $p->delete();

    // delete temp/banks/project
    $folderBank = 'uploads/banks/'.$projID."_".$title;
	$directory = INSTALL_PATH . FOLDER_TEMP . $folderBank ;
    if(file_exists($directory)) {
        if (!rmDir_R($directory))
            throw new Exception('suppr folder failed, check arbo.');
	}
    // delete datas
	$directory = INSTALL_PATH . FOLDER_DATA_PROJ . $folder ;
    if(file_exists($directory)) {
        if (!rmDir_R($directory))
            throw new Exception('suppr folder failed, check arbo.');
	}

    //delete sequences
	delete_all_sequences_project($projID);
}
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!





// Export du MASTERFILE XML
function masterXML_project($idProj, $titleProj){

		global $bdd;
		$q = $bdd->prepare("SELECT * FROM ".TABLE_PROJECTS." WHERE id='".$idProj."'");
		$q->execute();
		if ($q->rowCount() >= 1) {
			$result = $q->fetch(PDO::FETCH_ASSOC);
		}

		// XML prépa
		$xml          = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";

		if(!empty($result)){
			$xml .= "<".TABLE_PROJECTS.">\n\n";

			//loop through each key,value pair in row
			foreach($result as $key => $value)
			{
					//if array: transform array to new node
					if($key=='dpts' || $key=='equipe' || $key=='softwares'){
						$node_title = substr($key, 0, -1);
						$array_depts = json_decode($value);

						$xml .= "<".$key.">\n\n";
						foreach ($array_depts as $k => $v) {
									$xml .= "<$node_title>";
										$xml .= $v;
									$xml .= "</$node_title>\n";
						}
						$xml .= "</".$key.">\n\n";
					}
					//
					else{
						$xml .= "<$key>";
							$xml .= $value;
						$xml .= "</$key>\n";
					}
			}
			$xml.="\n\n</".TABLE_PROJECTS.">";
		}

		///////////////////// WRITE FILE
			$master_xml = $_SESSION['INSTALL_PATH']."/datas/projects/".$idProj."_".$titleProj."/".$idProj."_".$titleProj."_master.xml";

			$file_handle = fopen($master_xml, "w"); //open file for writing
			if(file_exists($master_xml)){
				chmod($master_xml, 0755);
				fwrite($file_handle,$xml); //write XML content to file
				fclose($file_handle); //close file
				chmod($master_xml, 0644);
			}
}



// Export du MASTERFILE TABLEUR
function masterTABLEUR_project($idProj,$titleProj) {
    global $bdd;
    $filename = $idProj."_".$titleProj."_master.csv";
    $master_csv = $_SESSION['INSTALL_PATH']."/datas/projects/".$idProj."_".$titleProj."/".$filename;

    $sql = "SELECT * FROM ".TABLE_PROJECTS." WHERE id='".$idProj."'";			// !!!! ??? A RÉÉCRIRE !! berk (lol)
    $result = $bdd->query($sql);
    $handle = fopen($master_csv, 'w');
    if(file_exists($master_csv)){
        chmod($master_csv, 0755);
        fputcsv($handle, array('id','title'));
        if(!empty($result) && $result!=''){
            foreach($result as $row){
                    fputcsv($handle, array($row['id'], $row['title']));
            }
        }
        fclose($handle);
        chmod($master_csv, 0755);
    }


}

?>
