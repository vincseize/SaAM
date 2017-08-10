<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

    // includes /fcts
	require_once ('dates.php' );
	require_once ('directories.php' );
	require_once ('vignettes_fcts.php' );
	require_once ('arrays_fcts.php' );
	require_once ('admin_projects_fcts.php' );
	require_once ('admin_sequences_fcts.php' );
	require_once ('admin_shots_fcts.php' );
	require_once ('pdf_fcts.php' );

	if (!$_SESSION['user']->isDev()) die();

extract($_POST);

$retour['error'] = 'error';
$retour['message'] = 'action undefined';


// Traitement de l'action 'ADD'
if ($action == 'add') {
	try {
		prepare_add_project_recette($titleProj);
		$retour['error'] = $retour['message'] = 'OK';
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

// Traitement de l'action 'MOD'
elseif ($action == 'mod') {
	try {
		$retour['titleBack'] = update_project_recette($titleProj);
		$retour['error'] = $retour['message'] = 'OK';

	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

// Traitement de l'action 'FOLDER'
elseif ($action == 'folder') {
	try {
		createProjectFolder_recette($titleProj,$foldersassets);
		$retour['error'] = $retour['message'] = 'OK';
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

// Traitement de l'action 'DEL'
elseif ($action == 'del') {
	try {
		delete_project($idProj, $titleProj);
		$retour['error'] = $retour['message'] = 'OK';
		$retour['idProj'] = $idProj;
		$retour['titleProj'] = $titleProj;
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

// Traitement de l'action 'masterfile'
elseif ($action == 'masterfile') {
	try {
		masterXML_project($idProj, $titleProj);
		$retour['error'] = $retour['message'] = 'OK';
		$retour['idProj'] = $idProj;
		$retour['titleProj'] = $titleProj;
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

// Traitement de l'action 'pdf'
elseif ($action == 'pdf') {
	try {
		pdf_project($idProj,$titleProj);
		$retour['error'] = $retour['message'] = 'OK';
		$retour['idProj'] = $idProj;
		$retour['titleProj'] = $titleProj;
	}
	catch (Exception $e) {
		$retour['message'] = $e->getMessage();
	}
}

echo json_encode($retour);


////////////////////////////////////////////////////////////////////////////////////////////////////

function prepare_add_project_recette ($title) {

        $my_rand = rand(1, 32); // randomize value
        $array_directors = array("HEWING Bobby","VADOR Dark","VADOR Etoameme","RABBIT Roger","MALTESE Corto","HADDOCK Cpt","LOVELACE Ada","TURING Iain","BABBAGE Charles","CASIMIR Julie","SCRAT","BIP Bip","COYOTTE Will","JUNG-IL Kim","FREUD Simon");
        $array_users_tot = array();
		$l = new Liste();
		$users_list = $l->getListe(TABLE_USERS,'id');   // recup values
        foreach ($users_list as $u) {
            array_push ($array_users_tot,$u);
        }


        $ID_creator = $_SESSION["user"]->getUserInfos('id');
        $array_fps = recup_fps();
        $fps = $array_fps[array_rand($array_fps)];
        $nomenclature = NOMENCLATURE_SEQ.'###'.NOMENCLATURE_SEPARATOR.NOMENCLATURE_SHOT.'###';
        $project_type = PROJECT_TEST_TYPE;
        $departments = array();
        $departments_list = get_dpts();
        $n_rand = rand(1,count($departments_list)-1);
        $dpt_rand_array = array_slice($departments_list, 1, $n_rand);
        foreach ($dpt_rand_array as $dd) {
                array_push ($departments,$dd);
        }
        $departments = json_encode($departments);



        $supervisor = $ID_creator;
        $title = $title;
        $description = 'des'.$my_rand;
        $director = $array_directors[array_rand($array_directors)];
        $equipe = array();
        $equipe_list = $array_users_tot;
        $n_rand = rand(1,count($equipe_list)-1);
        $equipe_rand_array = array_slice($equipe_list, 1, $n_rand);
        foreach ($equipe_rand_array as $ee) {
                array_push ($equipe,$ee);
        }
        $equipe = json_encode($equipe);
        $company = 'comp'.$my_rand;
        $date = date("Y-m-d G:i:s");    // today date format : '2012-05-21 00:00:00';
        $update = date("Y-m-d G:i:s");  // today date format : '2012-05-21 00:00:00';
	$my_rand_deadline = rand(14, 25);
        $deadline = '20'.$my_rand_deadline.'-05-21 00:00:00';
	$progress = rand(0, 100);
        $demo = '0';
        $hide = '0';
        $lock = '0';
        $archive = '0';
        $deleted = '0';
        $reference = 'REF_'.strtoupper($title);
        $softwares = array();
        $softwares_list = $_SESSION['CONFIG']['SOFTS'];
        $n_rand = rand(1,count($softwares_list)-1);
        $softwares_rand_array = array_slice($softwares_list, 1, $n_rand);
        foreach ($softwares_rand_array as $dd) {
        array_push ($softwares,$dd);
        }
        //$softwares = ''; // @todo : list reelle softs

        //////////////

        $values = array(
        //"ID_creator" => $ID_creator,
        "fps" => $fps,
        "nomenclature" => $nomenclature,
        "project_type" => $project_type,
        "dpts" => $departments,
        "title" => $title,
        "description" => $description,
        "director" => $director,
        "equipe" => $equipe,
        "company" => $company,
        "supervisor" => $supervisor,
        "date" => $date,
        "update" => $update,
        "deadline" => $deadline,
        "progress" => $progress,
        "demo" => $demo,
        "lock" => $lock,
        "hide" => $hide,
        "archive" => $archive,
        "deleted" => $deleted,
        "reference" => $reference,
        "softwares" => $softwares,
        "vignette"  => '../../_RECETTE/vignettes/projects/random.png' // le nom n'a aucune importance
        );

        add_project ($values,TRUE);

}


function createProjectFolder_recette ($title,$foldersassets='') {

        if($foldersassets==''){
            throw new Exception('create folder failed, no assets folders array');
            return;
        }

        $fA = stripslashes(urldecode($foldersassets));
        $folders_assets = json_decode($fA, true);

        // crée dossier du projet
        $ID_project = Liste::getMAx(TABLE_PROJECTS,'id');
        $dirProject = $ID_project.'_'.$title;

        if (!makeDataDir($dirProject))
            throw new Exception('create folder failed.');

        // add vignette
        add_vignette_project($dirProject);


        if (!makeDataDir($dirProject.'/assets'))
                throw new Exception('create folder failed.');
        foreach($folders_assets as $row){
            if (!makeDataDir($dirProject.'/assets/'.$row))
                    throw new Exception('create asset '.$row.' failed.');
        }
        // création dossier sequences du projet
        if (!makeDataDir($dirProject.'/sequences'))
                throw new Exception('create sequence folder failed.');

        // crée dossier bank du projet
        if (!makeDataDir($dirProject.'/bank'))
            throw new Exception('create folder "assets" failed.');

        // crée dossier temp/bank du projet
        if (!makeTempDir('uploads/banks/'.$dirProject))
            throw new Exception('create folder "assets" failed.');

        // création  sequences du projet
        $n_seqs = rand(1,4);
        prepare_add_sequence_recette($dirProject,$n_seqs,$ID_project);

}


function update_project_recette ($title) {
	// genere nom aléatoire
	$arr = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ');
	shuffle($arr);
	$arr = array_slice($arr, 0, 4);
	$str = implode('', $arr);
	$title =$title.$str;
	// check dernière ID en BDD et save nouveau title
	$ID = Liste::getMAx(TABLE_PROJECTS,'id');
	$project = new Projects($ID);
	$project->setTitle( $title );
	$project->save();
	return $title;
}


////////////////////////////////// SEQUENCES


function createSequenceFolder_recette ($title,$foldersassets='') {



}



function prepare_add_sequence_recette($dirProject,$n_seqs,$ID_project) {


        $my_ref = Liste::getMAx(TABLE_SEQUENCES,'position')+1;
        //$my_ref = Liste::getMAx(TABLE_SEQUENCES,'position')+1;
        //$my_ref = $ID_sequence+1;

        $start_seq = 001;
        // création  sequences du projet
        for($i=0;$i<=$n_seqs;$i++){
            $number_seq = sprintf("%03d", $start_seq++);
            $name_seq = NOMENCLATURE_SEQ.$number_seq;
            if (!makeDataDir($dirProject.'/sequences/'.$name_seq))
                    throw new Exception('create folder '.$name_seq.' failed.');

            // insert in bdd
            $ID_creator = $_SESSION["user"]->getUserInfos('id');

            $date = date("Y-m-d G:i:s");    // today date format : '2012-05-21 00:00:00';
            $update = date("Y-m-d G:i:s");  // today date format : '2012-05-21 00:00:00';
            $my_rand_deadline = rand(14, 25);
            $deadline = '20'.$my_rand_deadline.'-05-21 00:00:00';
            $progress = rand(0, 100);
            $hide = '0';
            $lock = '0';
            $title = 'TitleSeq'.rand(0, 1000);
            $reference = 'REF_S'.$my_ref;
            $supervisor = '';
            $lead = '';

            $description = "I've seen things you people
                wouldn't believe. Attack ships on fire off the shoulder of Orion.
                I watched C-beams glitter in the dark near the Tannhäuser gate.
                All those moments will be lost in time, like tears in rain. Time";

            // add sequences in BDD
            add_sequence_recette($ID_creator,$ID_project,$title,$supervisor,$lead,$description,$date,$update,$deadline,$progress,$hide,$lock,$reference);




// add shots
            $ID_sequence = Liste::getMAx(TABLE_SEQUENCES,'id');
            $n_shots = rand(1,3);
            prepare_add_shot_recette($ID_project,$ID_creator,$ID_sequence,$dirProject,$name_seq,$n_shots);
//            if (!add_shot($dirProject,$name_seq,$n_shots))
//                    throw new Exception('create folder shots failed.');
        }



}



////////////////////////////////// SHOTS



function prepare_add_shot_recette($ID_project,$ID_creator,$ID_sequence,$dirProject,$name_seq,$n_shots) {
        $start_shot = 001;
		$p = new Projects();
		$p->loadFromBD('id', $ID_project);
		$deptList = $p->getDeptsProject();
        // création  shots de la sequence
        for($j=0;$j<=$n_shots;$j++){
            $number_shot = sprintf("%03d", $start_shot++);
            $name_shot = NOMENCLATURE_SHOT.$number_shot;
            if (!makeDataDir($dirProject.'/sequences/'.$name_seq.'/'.$name_shot))
                    throw new Exception('create folder '.$name_shot.' failed.');

            // sous dossiers
			foreach($deptList as $dept) {
				if (!makeDataDir($dirProject.'/sequences/'.$name_seq.'/'.$name_shot.'/'.$dept.'/retakes'))
					throw new Exception('create shot datashot failed.');
				foreach($_SESSION['CONFIG']['dataShotsFolders'] as $row){
					if (!makeDataDir($dirProject.'/sequences/'.$name_seq.'/'.$name_shot.'/'.$dept.'/datashot/'.$row))
						throw new Exception('create shot '.$row.' failed.');
				}
				// creation vignette shot
				$dirShot = $dirProject.'/sequences/'.$name_seq.'/'.$name_shot;
				add_vignette_shot ($dirShot);
			}
            // add shots in BDD
            $title = $name_shot;
            add_shot_recette($ID_project,$ID_creator,$ID_sequence,$title);

        }
}




?>
