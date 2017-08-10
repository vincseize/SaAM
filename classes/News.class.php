<?php
	
require_once (INSTALL_PATH . FOLDER_CLASSES . 'Infos.class.php' );
	
class News implements Iterator {
	
	const NEWS_OK		= true ;			// retour général, si la fonction a marché
	const NEWS_ERROR	= false ;			// retour général, si la fonction n'a pas marché
	
	const NEWS_UPDATE_ERROR	= 'Aucune donnée à modifier !' ;	// message d'erreur pour l'update
	
	const NEWS_ID		= 'id' ;
	const NEWS_CREATOR	= 'ID_creator' ;
	const NEWS_VISIBLE	= 'visible' ;
	const NEWS_DATE		= 'new_date' ;
	const NEWS_TITLE	= 'new_title' ;
	const NEWS_TEXT		= 'new_text' ;
	
	private $ID_new;
	private $infos;
	
	
	public function __construct ($idNew = 'new') {
		$this->infos = new Infos( TABLE_NEWS ) ;
		if ( $idNew == 'new' ) return ;
		$this->ID_new = $idNew;
		try { $this->loadFromBD( News::NEWS_ID, $this->ID_new ); }
		catch (Exception $e) { throw new Exception ($e->getMessage()); }
	}
	
	// Charge les infos 
	public function loadFromBD ( $keyFilter , $value ) {
		try { $this->infos->loadInfos( $keyFilter, $value ); }
		catch (Exception $e) { throw new Exception(Projects::INFO_ERREUR.' : '.$e->getMessage().' pour : '.$keyFilter.' = '.$value); }
	}
	
	// Retourne les (ou une) valeur(s) de l'objet Infos
	public function getProjectInfos ($what='') {
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
	
	// retourne true si la news est visible
	public function is_visible () {
		if ($this->infos->getInfo(News::NEWS_VISIBLE) == 1) {
			return true;
		}
		else return false;
	}
	
	// setters
 	public function setCreator ($value)	{ $this->infos->addInfo ( News::NEWS_CREATOR, $value ) ; }
 	public function setVisibility ($v)	{ $this->infos->addInfo ( News::NEWS_VISIBLE, $v ) ; }
 	public function setVisible   ()		{ $this->infos->addInfo ( News::NEWS_VISIBLE, 1 ) ; }
 	public function setInvisible ()		{ $this->infos->addInfo ( News::NEWS_VISIBLE, 0 ) ; }
 	public function setDate ($date)		{ if ((string)$this->ID_new == '1') {return;} $this->infos->addInfo ( News::NEWS_DATE, $date ) ; }
 	public function setTitle($title)	{ $this->infos->addInfo ( News::NEWS_TITLE,	  $title ) ; }
 	public function setText ($text)		{ $this->infos->addInfo ( News::NEWS_TEXT,    $text ) ; }
	
	
	// sauvegarde les données en BDD
	public function save() {             
		try { $this->infos->save(); }
		catch (Exception $e) { throw new Exception ($e->getMessage()); }
		return News::NEWS_OK ; 
	}
	
	// delete un enregistrement en BDD
    public function delete() {
		try { $this->infos->delete(); }			
		catch (Exception $e) { throw new Exception ($e->getMessage()); }			
		return News::NEWS_OK ; 
	}
	
	// iterator
	public function key()     { return $this->infos->key(); }
	public function current() { return $this->infos->current(); }
	public function next()	  {	$this->infos->next() ;  	}
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