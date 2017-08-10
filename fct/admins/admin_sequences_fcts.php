<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	if (!($_SESSION['user']->isSupervisor() || $_SESSION['user']->isDemo())) die('{"error":"error", "message":"function sequence : Access denied."}');

require_once ('dates.php' );
require_once ('directories.php' );
require_once ('admin_shots_fcts.php' );


//////////////////////////////////////////////////////////////////////////////// FONCTIONS RECETTES

function add_sequence_recette($ID_creator,$ID_project,$title,$supervisor,$lead,$description,$date,$update,$deadline,$progress,$hide,$lock,$reference){
	$new_position = Liste::getMAx(TABLE_SEQUENCES,'position')+1;
	$position = $new_position;
	$number = sprintf("%03d", $position);
	$label = NOMENCLATURE_SEQ.$number;

	$sequence = new Sequences();

	$sequence->setcreator( $ID_creator );
	$sequence->setIDproject( $ID_project );
	$sequence->setTitle( $title );
	$sequence->setLabel( $label );
	$sequence->setSupervisor( $supervisor );
	$sequence->setLead( $lead );
	$sequence->setPosition( $position );
	$sequence->setDescription( $description );
	$sequence->setDate( $date );
	$sequence->setUpdate( $update );
	$sequence->setDeadline( $deadline );
	$sequence->setProgress( $progress );
	$sequence->setHide( $hide );
	$sequence->setLock( $lock );
	$sequence->setReference( $reference );

	$sequence->save();
}

// Supprime toutes les séquence d'un projet
function delete_all_sequences_project($ID_project) {
	$lS = new Liste();
	$sequences_list = $lS->getListe(TABLE_SEQUENCES,'id', 'id', 'ASC', Sequences::SEQUENCE_ID_PROJECT, '=', $ID_project);
	if (is_array($sequences_list)){
		foreach ($sequences_list as $seqID) {
			delete_sequence($seqID);
		}
	}
}

// Supprime une séquence
function delete_sequence($ID_sequence) {
	delete_all_shots_sequence($ID_sequence);
	$sequences = new Sequences((int)$ID_sequence);
	$sequences->delete();
}

//////////////////////////////////////////////////////////////////////////////// FONCTIONS UI

// Création des séquences en une fois (3eme étape de add project)
function add_sequences ($sequencesList, $idProj=false, $title=false) {
	if (!$idProj && !$title) { throw new Exception('add_sequences : missing $idProj AND $title !'); }
	if (!is_array($sequencesList)) { throw new Exception('add_sequences : $sequencesList must be an array ! ex: $sequencesList[i]=array(label, title, nbShots)'); }
	$p = new Projects();					// chargement du projet
	if ($idProj)							// selon son titre
		$p->loadFromBD('id', $idProj);
	if ($title)								// ou selon son ID
		$p->loadFromBD('title', $title);
	$ID_project = $p->getIDproject();
	if ($ID_project == 1 && !$_SESSION['user']->isRoot()) { throw new Exception('the DEMO project can\'t be modified. Please make your own.'); }
	$dirProject = $p->getDirProject();
	$date = date('Y-m-d 00:00:00');
	$deadline = $p->getDeadline();
	// Crée les séquences en base de données
	foreach($sequencesList as $i => $seqInfos) {		// Ici, que 6 infos : label[0], titre[1], nbShots[2] et récup de ID_project, ID_creator et position
		if ($p->isDemo() && ($i+1 > MAX_DEMO_SEQUENCES)) { return; }
		$s = new Sequences();
		$s->setcreator( $_SESSION["user"]->getUserInfos(Users::USERS_ID) );
		$s->setIDproject( $ID_project );
		$s->setTitle( $seqInfos[1] );
		$s->setLabel( $seqInfos[0] );
		$s->setPosition( $i+1 );
		$s->setDate( $date );
		$s->setDeadline( $deadline );
		$s->setUpdate( $date );
		$s->save();
		unset($s);
		$ID_sequence = Liste::getMax(TABLE_SEQUENCES, Sequences::SEQUENCE_ID_SEQUENCE);
		// Crée les dossiers des séquences
		if (!makeDataDir($dirProject.'/sequences/'.$seqInfos[0]))
			throw new Exception('create sequence folder: '.$seqInfos[0].' :failed.');
		// Crée les shots rapidos (avec leurs dossiers) si nbShots != 0
		if ((int)$seqInfos[2] > 0)
			add_shots($ID_project, $ID_sequence, $seqInfos[0], $seqInfos[2]);
	}
}

// Crée UNE séquence avec les infos (array) qu'on lui passe
function add_one_sequence ($seqInfos, $idProj=false, $titleProj=false) {
	if (!isset($seqInfos)) { throw new Exception('add_one_sequence : $seqInfos is NULL !'); }
	if (!$idProj && !$titleProj) { throw new Exception('add_one_sequence : missing $idProj AND $title !'); }
	$p = new Projects();					// chargement du projet
	if ($idProj)							// selon son titre
		$p->loadFromBD('id', $idProj);
	if ($titleProj)								// ou selon son ID
		$p->loadFromBD('title', $titleProj);
	// Récup données essentielles
	$ID_project = $p->getIDproject();
	if ($ID_project == 1 && !$_SESSION['user']->isRoot()) { throw new Exception('the DEMO project can\'t be modified. Please make your own.'); }
	$nbSeqExist = $p->getNbSequences();
	if ($p->isDemo() && ($nbSeqExist >= MAX_DEMO_SEQUENCES)) { throw new Exception('a DEMO project can\'t have more than '.MAX_DEMO_SEQUENCES.' sequences.'); }
	$nomencSeq  = $p->getNomenclature('seq');
	$labelSeq   = preg_replace('/###/', sprintf('%03d', $nbSeqExist+1), $nomencSeq);
	// Crée le dossier de la séquence
	$dirProject = $p->getDirProject();
	if (!makeDataDir($dirProject.'/sequences/'.$labelSeq)) {
		throw new Exception('create sequence folder: '.$labelSeq.' :failed.');
		return;
	}
	$seqTitle = ($seqInfos['title'] == '') ? $labelSeq : $seqInfos['title'];

	$s = new Sequences();
	$s->setcreator( $_SESSION["user"]->getUserInfos(Users::USERS_ID) );
	$s->setIDproject( $ID_project );
	$s->setTitle( $seqTitle );
	$s->setLabel( $labelSeq );
	$s->setPosition( $nbSeqExist+1 );
	$s->setDate( $seqInfos['date'] );
	$s->setUpdate(date('Y-m-d 00:00:00'));
	$s->setDeadline( $seqInfos['deadline'] );
	$s->setLead( $seqInfos['lead'] );
	$s->save();
	unset($s);
	// Création des shots de la séquence
	$ID_sequence = Liste::getMax(TABLE_SEQUENCES, Sequences::SEQUENCE_ID_SEQUENCE);
	if ((int)$seqInfos['nbShots'] > 0)
		add_shots($ID_project, $ID_sequence, $labelSeq, $seqInfos['nbShots']);
}


function modif_sequence ($values, $idSeq=false) {
	if (!is_array($values)) { throw new Exception('modif_sequence : $values not an array : '.$values.'!'); }
	if (!$idSeq) { throw new Exception('modif_sequence : missing $idSeq !'); }

	$s = new Sequences($idSeq);
	$idProj = $s->getSequenceInfos(Sequences::SEQUENCE_ID_PROJECT);
	if ($idProj == 1 && !$_SESSION['user']->isRoot()) { throw new Exception('the DEMO project can\'t be modified. Please make your own.'); }
	foreach($values as $row => $val) {
		$s->setValue($row, $val);
	}
	$s->setUpdate(date('Y-m-d 00:00:00'));
	$s->save();
	unset($s);
}


function reorganise_sequences ($newPositions) {
	if (!is_array($newPositions)) { throw new Exception('$newPositions : not an array !'); }
	foreach ($newPositions as $idSeq => $posSeq) {
		$s = new Sequences($idSeq);
		$idProj = $s->getSequenceInfos(Sequences::SEQUENCE_ID_PROJECT);
		if ($idProj == 1 && !$_SESSION['user']->isRoot()) { throw new Exception('the DEMO project can\'t be modified. Please make your own.'); }
		$s->setPosition($posSeq);
		$s->save();
	}
}


function archive_sequence($idSeq=false) {
	if (!$idSeq) { throw new Exception('archive_sequence : missing $idSeq !'); }
	$s = new Sequences($idSeq);
	$idProj = $s->getSequenceInfos(Sequences::SEQUENCE_ID_PROJECT);
	if ($idProj == 1 && !$_SESSION['user']->isRoot()) { throw new Exception('the DEMO project can\'t be modified. Please make your own.'); }
	$s->archiveSequence();
	$s->save();
}

function restore_sequence($idSeq=false) {
	if (!$idSeq) { throw new Exception('restore_sequence : missing $idSeq !'); }
	$s = new Sequences($idSeq);
	$idProj = $s->getSequenceInfos(Sequences::SEQUENCE_ID_PROJECT);
	if ($idProj == 1 && !$_SESSION['user']->isRoot()) { throw new Exception('the DEMO project can\'t be modified. Please make your own.'); }
	$s->restoreSequence();
	$s->save();
}
?>
