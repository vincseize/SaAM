<?php

require_once (INSTALL_PATH . FOLDER_CLASSES . 'Infos.class.php' );

class Sequences implements Iterator {

	const UPDATE_ERROR_DATA = 'donnee invalide' ;						// erreur si pseudo ou autre donnée ne correspond pas
	const UPDATE_OK			= 'donnée modifiée, OK !' ;				// message si une modif BDD a réussi
	const INFO_ERREUR		= 'Sequences:: Impossible de lire les infos en BDD';    // erreur de la methode Infos::loadInfos()
	const INFO_DONT_EXIST   = 'donnee inexistante' ;					// erreur si champs inexistant dans BDD lors de récup d'info
	const INFO_FORBIDDEN    = 'donnee interdite' ;						// erreur si info est une donnée sensible (ici, password)
	const SAVE_LOSS			= 'Champs manquants';						// erreur si il manques des données essentielles à sauvegarder dans la BDD

	const SEQUENCE_OK		= true ;						// retour general, si la fonction a marché
	const SEQUENCE_ERROR		= false ;						// retour general, si la fonction n'a pas marché

	const SEQUENCE_ID_SEQUENCE	= 'id' ;
	const SEQUENCE_ID_CREATOR	= 'ID_creator' ;
	const SEQUENCE_ID_PROJECT	= 'ID_project' ;
	const SEQUENCE_POSITION		= 'position' ;
	const SEQUENCE_TITLE		= 'title' ;
	const SEQUENCE_LABEL			= 'label' ;
	const SEQUENCE_DESCRIPTION	= 'description' ;
	const SEQUENCE_SUPERVISOR	= 'supervisor' ;
	const SEQUENCE_LEAD			= 'lead' ;
	const SEQUENCE_DATE			= 'date' ;
	const SEQUENCE_UPDATE		= 'update' ;
	const SEQUENCE_DEADLINE		= 'deadline' ;
	const SEQUENCE_PROGRESS		= 'progress' ;
	const SEQUENCE_HIDE			= 'hide' ;
	const SEQUENCE_LOCK			= 'lock' ;
	const SEQUENCE_ARCHIVE		= 'archive' ;
	const SEQUENCE_REFERENCE	= 'reference' ;


	private $ID_sequence;
	private $infos;
	private $nbShots;


	public function __construct ($ID_sequence = 'new') {
		$this->infos = new Infos( TABLE_SEQUENCES ) ;
		if ( $ID_sequence == 'new' ) return ;
		$this->ID_sequence = $ID_sequence;
		try { $this->loadFromBD( Sequences::SEQUENCE_ID_SEQUENCE , $this->ID_sequence ); }
		catch (Exception $e) { throw new Exception ($e->getMessage()); }
	}

	// Charge les infos
	public function loadFromBD ( $keyFilter , $value ) {
		try { $this->infos->loadInfos( $keyFilter, $value ); }
		catch (Exception $e) { throw new Exception(Sequences::INFO_ERREUR.' : '.$e->getMessage().' pour : '.$keyFilter.' = '.$value); }
	}



	// Retourne une valeur de l'objet Infos
	public function getSequenceInfos ($what='') {
		if ($what == '') {
			try { $info = $this->infos->getInfo(); }							// RÃ©cup toutes les infos dans la BDD
			catch (Exception $e) { throw new Exception ($e->getMessage()); }
		}
		else {
			try { $info = $this->infos->getInfo($what); }						// RÃ©cup une seule info
			catch (Exception $e) { throw new Exception ($e->getMessage()); }
		}
		return $info;
	}

	public function is_active() {
		$hide = $this->infos->getInfo(Sequences::SEQUENCE_HIDE);
		$arch = $this->infos->getInfo(Sequences::SEQUENCE_ARCHIVE);
		return ($hide == '0' && $arch == '0');
	}

	// Retourne un array des shots de la séquence
	public function getSequenceShots ($withHidden=true, $withArchive=false) {
		$l = new Liste();
		if (!$withHidden)
			$l->addFiltre(Shots::SHOT_HIDE, '=', '0');
		if (!$withArchive)
			$l->addFiltre(Shots::SHOT_ARCHIVE, '=', '0');
		$l->addFiltre(Shots::SHOT_ID_SEQUENCE, '=', $this->ID_sequence);
		$l->getListe(TABLE_SHOTS, '*', Shots::SHOT_POSITION, 'ASC');
		$shotsList = $l->simplifyList(Shots::SHOT_ID_SHOT);
		if (is_array($shotsList)) {
			$this->nbShots = count($shotsList);
			return $shotsList;
		}
		else return false;
	}

	// Nb de valeurs ds l'objet Infos
	public function nbElements () {
		return $this->infos->nbInfos() ;
	}

	// ajoute / modifie une info puis SAVE
	public function updateInfo ($typeInfo, $newInfo) {
		$this->infos->addInfo( $typeInfo, $newInfo );
		$this->save();
		return true;
	}


	// ajoute / modifie des infos
	public function setInfos ($projID=false, $infos=false) {
		try {
			if (!$projID)
				throw new Exception ('$projID is missing !');
			if (!is_array($infos))
				throw new Exception ('$infos is not an array !');
			foreach($infos as $typeInfo => $newInfo) {
				$this->infos->addInfo( $typeInfo, $newInfo );
			}
			if ($this->infos->is_loaded() == false) {
				$this->infos->addInfo(Sequences::SEQUENCE_ID_CREATOR, $_SESSION['user']->getUserInfos(Users::USERS_ID));
				$this->infos->addInfo(Sequences::SEQUENCE_ID_PROJECT, $projID);
				$pMax = Liste::getMax(TABLE_SEQUENCES, Sequences::SEQUENCE_POSITION);
				$this->infos->addInfo(Sequences::SEQUENCE_POSITION, $pMax);
			}
			$this->save();
			return true;
		}
		catch (Exception $e) { throw new Exception ('Sequences::SetInfos() error: '.$e->getMessage()); return false;}
	}

	// sauvegarde les données en BDD
	public function save() {
        // @TODO : verif doublons
		try { $this->infos->save(); }
		catch (Exception $e) { throw new Exception ($e->getMessage()); }
		return Sequences::SEQUENCE_OK ;
	}

    public function delete() {
		try { $this->infos->delete(); }
		catch (Exception $e) { throw new Exception ($e->getMessage()); }
		return Sequences::SEQUENCE_OK ;
	}

	// setters
 	public function setCreator($value) {
		$this->infos->addInfo ( Sequences::SEQUENCE_ID_CREATOR, $value ) ;
	}
 	public function setIDproject($value) {
		$this->infos->addInfo ( Sequences::SEQUENCE_ID_PROJECT, $value ) ;
	}
 	public function setTitle($value) {
		$this->infos->addInfo ( Sequences::SEQUENCE_TITLE, $value ) ;
	}
	public function setLabel($value) {
		$this->infos->addInfo ( Sequences::SEQUENCE_LABEL, $value ) ;
	}
	public function setPosition($value) {
		$this->infos->addInfo ( Sequences::SEQUENCE_POSITION, $value ) ;
	}
	public function setDescription($value) {
		$this->infos->addInfo ( Sequences::SEQUENCE_DESCRIPTION, $value ) ;
	}
	public function setSupervisor($value) {
		$this->infos->addInfo ( Sequences::SEQUENCE_SUPERVISOR, $value ) ;
	}
 	public function setLead($value) {
		$this->infos->addInfo ( Sequences::SEQUENCE_LEAD, $value ) ;
	}
 	public function setDate($value) {
		$this->infos->addInfo ( Sequences::SEQUENCE_DATE, $value ) ;
	}
 	public function setUpdate($value) {
		$this->infos->addInfo ( Sequences::SEQUENCE_UPDATE, $value ) ;
	}
	public function setDeadline($value) {
		$this->infos->addInfo ( Sequences::SEQUENCE_DEADLINE, $value ) ;
	}
	public function setProgress($value) {
		$this->infos->addInfo ( Sequences::SEQUENCE_PROGRESS, $value ) ;
	}
  	public function setLock($value) {
		$this->infos->addInfo ( Sequences::SEQUENCE_LOCK, $value ) ;
	}
  	public function setHide($value) {
		$this->infos->addInfo ( Sequences::SEQUENCE_HIDE, $value ) ;
	}
	public function setReference($value) {
		$this->infos->addInfo ( Sequences::SEQUENCE_REFERENCE, $value ) ;
	}

	// setter de valeur à déterminer
	public function setValue($champ, $value) {
		if (is_array($value)) $value = json_encode($value);
		$this->infos->addInfo ( $champ, $value ) ;
	}

	public function archiveSequence () {
		$this->infos->addInfo ( Sequences::SEQUENCE_ARCHIVE, '1' ) ;
		$shotList = $this->getSequenceShots();
		if ($shotList) {
			foreach($shotList as $Id_shot => $shotInfos) {
				$s = new Shots($Id_shot);
				$s->archiveShot();
				$s->save();
			}
		}
	}

	public function restoreSequence () {
		$this->infos->addInfo ( Sequences::SEQUENCE_ARCHIVE, '0' ) ;
		$shotList = $this->getSequenceShots();
		if ($shotList) {
			foreach($shotList as $Id_shot => $shotInfos) {
				$s = new Shots($Id_shot);
				$s->restoreShot();
				$s->save();
			}
		}
	}


	public function recalc_sequence_progress () {
		$shots = $this->getSequenceShots();
		if (!is_array($shots)) return;
		$totalProgress = $this->nbShots * 100;
		$seqProgCount = 0;
		foreach ($shots as $shot) {
			$seqProgCount += $shot[Shots::SHOT_PROGRESS];
		}
		$seqProgress = (int)($seqProgCount / $totalProgress * 100);
		$this->updateInfo(Sequences::SEQUENCE_PROGRESS, $seqProgress);
	}

	public static function getLabelSequence ($idSeq) {
		$s = new Sequences($idSeq);
		return $s->getSequenceInfos(Sequences::SEQUENCE_LABEL);
	}

	public function key()     { return $this->infos->key(); }
	public function current() { return $this->infos->current(); }
	public function next()	  { $this->infos->next() ; }
	public function rewind()  { $this->infos->rewind() ; }
	public function valid()   {
		while ( $this->infos->valid() ){
			if ( in_array(  $this->infos->key() , $this->hide_datas) )
				$this->infos->next() ;
			else
				return true ;
		}
		return false ;
	}






}
?>
