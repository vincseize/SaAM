<?php

require_once (INSTALL_PATH . FOLDER_CLASSES . 'Infos.class.php' );
require_once ('directories.php');

class Cameras implements Iterator {

	const OK	= true;
	const ERROR	= false;

	const ID_CAMERA			= 'id' ;
	const NAME				= 'name' ;
	const ID_PROJECT		= 'ID_project' ;
	const SCENE				= 'ID_scene' ;
	const SEQUENCE			= 'ID_sequence' ;
	const SHOT				= 'ID_shot' ;
	const ID_CREATOR			= 'ID_creator' ;
	const UPDATE			= 'update' ;
	const UPDATED_BY		= 'updated_by' ;
	const TAGS				= 'tags' ;
	const HIDE				= 'hide' ;
	const ARCHIVE			= 'archive' ;


	private $ID_camera;
	private $ID_scene;
	private $ID_sequence;
	private $ID_shot;
	private $ID_project;
	private $infos;


	public function __construct ($ID_camera = 'new') {
		$this->infos = new Infos( TABLE_CAMERAS ) ;
		if ( $ID_camera == 'new' ) return ;
		$this->ID_camera = $ID_camera;
		try { $this->loadFromBD( Cameras::ID_CAMERA , $this->ID_camera ); }
		catch (Exception $e) { throw new Exception ($e->getMessage()); }
	}


	// Charge les infos
	public function loadFromBD ( $keyFilter , $value ) {
		try {
			$this->infos->loadInfos( $keyFilter, $value );
			$idProj = $this->infos->getInfo(Cameras::ID_PROJECT);
			$this->ID_project = $idProj;
		}
		catch (Exception $e) { throw new Exception('Erreur: '.$e->getMessage().' pour : '.$keyFilter.' = '.$value); }
	}


	// Retourne une valeur de l'objet Infos
	public function getCameraInfos ($what='') {
		if ($what == '') {
			try { $info = $this->infos->getInfo(); }							// Récup toutes les infos dans la BDD
			catch (Exception $e) { throw new Exception ($e->getMessage()); }
		}
		else {
			try { $info = $this->infos->getInfo($what); }						// Récup une seule info
			catch (Exception $e) { throw new Exception ($e->getMessage()); }
		}
		return $info;
	}

	// Retourne TRUE si la scène est "active" (si pas hide, ni archive)
	public function isActive() {
		if ($this->getCameraInfos(Cameras::HIDE) || $this->getCameraInfos(Cameras::ARCHIVE))
			return false;
		return true;
	}

	// Retourne la scene associée à la cam
	public function getScene () {
		return (int)$this->infos->getInfo(Cameras::SCENE);
	}

	// Retourne la sequence assignée à la cam
	public function getSequence ($what='id') {
		$this->ID_sequence = (int)$this->infos->getInfo(Cameras::SEQUENCE);
		if ($what == 'id')
			return $this->ID_sequence;
		else {
			if ($this->ID_sequence == 0) return '<i class="ui-state-disabled">No Sequence</i>';
			$se = new Sequences($this->ID_sequence);
			return $se->getSequenceInfos($what);
		}
	}

	// Retourne le plan assigné à la cam
	public function getShot ($what='id') {
		$this->ID_shot = (int)$this->infos->getInfo(Cameras::SHOT);
		if ($what == 'id')
			return $this->ID_shot;
		else {
			if ($this->ID_shot == 0) return '<i class="ui-state-disabled">No Shot</i>';
			$sh = new Shots($this->ID_shot);
			return $sh->getShotInfos($what);
		}
	}



	// ajoute / modifie une info
	public function updateInfo ($typeInfo, $newInfo) {
		$this->infos->addInfo( $typeInfo, $newInfo );
		$this->save();
		return true;
	}

	// ajoute / modifie des infos
	public function setInfos ($infos=false) {
		try {
			if (!is_array($infos))	throw new Exception ('$infos is not an array !');
			foreach($infos as $typeInfo => $newInfo) {
				if (is_array($newInfo))
					$newInfo = json_encode($newInfo);
				$this->infos->addInfo( $typeInfo, $newInfo );
			}
			if ($this->infos->is_loaded() == false)
				$this->setCreator($_SESSION['user']->getUserInfos(Users::USERS_ID));
			$this->save();
			return true;
		}
		catch (Exception $e) { throw new Exception ('Scenes::SetInfos() error: '.$e->getMessage()); return false; }
	}

	// sauvegarde les données en BDD
	public function save($forceNoUp = 'avoid') {
		try {
			if ($forceNoUp != 'noUpdate')
				$this->setUpdate(date('Y-m-d H:i:s'));
			$this->infos->save();
		}
		catch (Exception $e) { throw new Exception ($e->getMessage()); }
		return Cameras::OK ;
	}

	// DELETE de scene... Beware
    public function delete() {
		//@TODO : dump bdd, move data to tmp
		try { $this->infos->delete(); }
		catch (Exception $e) { throw new Exception ($e->getMessage()); }
		return Cameras::OK ;
	}

	// setters
 	public function setCreator($value) {
		$this->infos->addInfo ( Cameras::ID_CREATOR, (int)$value ) ;
	}
 	public function setProject($value) {
		$this->infos->addInfo ( Cameras::ID_PROJECT, $value ) ;
	}
 	public function setScene($value) {
		$this->infos->addInfo ( Cameras::SCENE, $value ) ;
	}
 	public function setSequence($value) {
		$this->infos->addInfo ( Cameras::SEQUENCE, $value ) ;
	}
 	public function setShot($value) {
		$this->infos->addInfo ( Cameras::SHOT, $value ) ;
	}
 	public function setName($value) {
		$this->infos->addInfo ( Cameras::NAME, $value ) ;
	}
 	public function setUpdate($value) {
		$this->infos->addInfo ( Cameras::UPDATE, $value ) ;
		$this->infos->addInfo ( Cameras::UPDATED_BY, $_SESSION['user']->getUserInfos(Users::USERS_ID) ) ;
	}
  	public function setHide($value) {
		$this->infos->addInfo ( Cameras::HIDE, $value ) ;
	}
	public function archiveCamera () {
		$this->infos->addInfo ( Cameras::ARCHIVE, '1' ) ;
	}
	public function restoreCamera () {
		$this->infos->addInfo ( Cameras::ARCHIVE, '0' ) ;
	}

	// Retourne une liste de camera et leurs infos en fonction d'un tag
	public static function getCamerasByTag ($tagName=false) {			// @TODO : getCamerasByTag()
		if (!$tagName) return false;
		$l = new Liste();
		$l->getListe(TABLE_CAMERAS, '*', Cameras::ID_PROJECT.'`, `'.Cameras::SCENE.'`, `'.Cameras::SEQUENCE.'`, `'.Cameras::SHOT, 'ASC', Cameras::TAGS, 'LIKE', '%'.$tagName.'%');
		$listeScenes = $l->simplifyList(Cameras::ID_CAMERA);

		return (count($listeScenes) > 0) ? $listeScenes : false;
	}


	public function key()		{ return $this->infos->key(); }
	public function current()	{ return $this->infos->current(); }
	public function next()		{ $this->infos->next() ; }
	public function rewind()	{ $this->infos->rewind() ; }
	public function valid()		{
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
