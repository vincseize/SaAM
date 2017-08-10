<?php

// CLASSE DE SÉCU ANTI FORCE BRUTE
class NoBF {

    public function  __construct() { }

    // Teste si le nbre de tentative n'exède pas BF_NB_TENTATIVE dans le laps de temps défini avec BF_TIME_LAPS
    public static function bruteCheck($pseudo) {
        $filename = BF_DIR . $pseudo . '.tmp';
        $deny_access = false;

        if (file_exists($filename)) {
            $infos = NoBF::fileToArray($filename);
            $nb_tentatives = count($infos);
            $premiere_tentative = @$infos[0];
            if ($nb_tentatives > BF_NB_TENTATIVE && (BF_TIME_LAPS + $premiere_tentative) > time())
                $deny_access = true;
        }
        return $deny_access;
    }

    public static function addTentative($pseudo) {
        $filename = BF_DIR . $pseudo . '.tmp';
        $date = time();

        if(file_exists($filename))
            $infos = NoBF::fileToArray($filename);
        else $infos = array();

        $infos[] = $date;
        NoBF::arrayToFile($filename, $infos);
    }

    // Permet de supprimer les enregistrements trop anciens
    public static function cleanUp($infos) {
        foreach($infos as $n => $date) {
            if((BF_TIME_LAPS + $date) < time())
                unset($infos[$n]);
        }
        return array_values($infos);
    }

    // Récupère les infos du fichier et les retourne unserialisé
    public static function fileToArray($filename) {
        $infos = unserialize( file_get_contents($filename) );
        $infos = NoBF::cleanUp($infos);
        return $infos;
    }

    // Enregistre les infos dans le fichier de log serialisé
    public static function arrayToFile($filename, $data) {
        $file = fopen ($filename, "w");
        fwrite($file, serialize($data) );
        fclose ($file);
        return true;
    }
}


// CLASSE DE CONNEXION D'UN UTILISATEUR
class Connecting {

	private $db;			// Instance de PDO
	private $connected;
	private $user			 = array();
	private $user_table_name = TABLE_USERS;
	private $login_cookie	 = COOKIE_NAME_LOG ;
	private $password_cookie = COOKIE_NAME_PASS;
	private $salt			 = SALT_PASS ;

	public function __construct($db) {
		$this->db = $db;
		if($this->testConnexion() == false) {
			$this->connected = false;
		}
	}

	// Retourne si cette personne est connectée ou pas
	public function is_connected() {
		if ($this->connected)
			return $_SESSION[ $this->login_cookie ];
		else return false;
	}

	// Connexion : $pseudo : string (pseudo) / $password : string non crypté (mot de passe)
	public function connect($pseudo, $password, $isMD5=false) {
		global $errAuthMsg, $lastLoginUsed;
		$this->disconnect();

		if (NoBF::bruteCheck($pseudo)) {
			$errAuthMsg = "Too many connexion attemps for user '$pseudo'!<br />Please retry later (".(BF_TIME_LAPS/60)." minutes).";
			$lastLoginUsed = $pseudo;
			return false;
		}

		$pseudo = preg_replace('/\\\'/', '', $pseudo);		// Empêcher les injections SQL en virant les '
		$passw = ($isMD5) ? $password : md5($this->salt.$password);
		$q = $this->db->prepare("SELECT `".Users::USERS_ID."`, `".Users::USERS_LOGIN."`, `".Users::USERS_PASSWORD."`, `".Users::USERS_ACTIVE."`
								FROM `".$this->user_table_name."`
								WHERE `login` = '$pseudo'
								AND `passwd` = '".$passw."' ");
		try {
			$q->execute();
		}
		catch (Exception $e) {
			$errAuthMsg = 'SQL ERROR:<br />'.$e->getMessage();
			return false;
		}

		if ($q->rowCount() == 1) {
			$this->connected = true;
			$this->user = $q->fetch(PDO::FETCH_ASSOC);
			// Check utilisateur actif / inactif
			if (@$this->user[Users::USERS_ACTIVE] == '0') {
				$errAuthMsg = "User '$pseudo' is inactive.";
				$lastLoginUsed = $pseudo;
				return false;
			}
			$this->setSecuredData();
			$this->updateUser($this->user['id'], 1);
			// creation du fichier de log /sessions/ID.ssid
			$filename = FOLDER_SESSIONS.$this->user['id'].".ssid";
			@file_put_contents($filename, date('\l\e d/m/Y à H:i'));
			return true;
		}
		else {
			NoBF::addTentative($pseudo);
			$q = $this->db->prepare("SELECT `id`, `login`, `passwd`, `".Users::USERS_ACTIVE."`
									FROM `".$this->user_table_name."`
									WHERE `login` = '$pseudo' ");
			$q->execute();
			if ($q->rowCount() == 1) {
				$errAuthMsg = "Wrong password for user '$pseudo'.";
				$lastLoginUsed = $pseudo;
			}
			else
				$errAuthMsg = "User '$pseudo' not found.";
			return false;
		}

	}


	// Déconnexion
	public function disconnect() {
		$this->resetSessionData();
		session_unset();
	}

	// Teste la connexion en cours
	private function testConnexion() {
		global $errAuthMsg;
		// def des vars à tester
		$toTestToken = '';
		$toTestPassword = '';
		$toTestLogin = '';

		// Conservation d'une connexion via cookie (seulement si $_SESSION est vide)
		if (!empty($_COOKIE[ $this->login_cookie ]) && !empty($_COOKIE[ $this->password_cookie ]) && empty($_SESSION[ $this->password_cookie ])) {
			$toTestLogin	= $_COOKIE[ $this->login_cookie ];
			$toTestPassword = $_COOKIE[ $this->password_cookie ];
			$toTestToken	= $_COOKIE[ 'token' ];
		}
		// Conservation d'une connexion via Session
		elseif (!empty($_SESSION[ $this->password_cookie ]) && !empty($_SESSION[ $this->login_cookie ])) {
			$toTestLogin	= $_SESSION[ $this->login_cookie ];
			$toTestPassword = $_SESSION[ $this->password_cookie ];
			$toTestToken	= $_SESSION[ 'token' ];
		}

		// Si le token n'est pas identique au fingerprint du navigateur, on reset tout
		if ($toTestToken != $this->fingerprint()) {
			$this->resetSessionData();
			$errAuthMsg = "Your session was corrupted.";
			return false;
		}

		if (!empty($toTestLogin) && !empty($toTestPassword)) {
			// teste si l'utilisateur existe bel et bien
			$q  =   $this->db->prepare("SELECT `".Users::USERS_ID."`, `".Users::USERS_LOGIN."`, `".Users::USERS_PASSWORD."`, `".Users::USERS_ACTIVE."`
										FROM `".$this->user_table_name."`
										WHERE `login`='$toTestLogin'
										AND `passwd`='$toTestPassword'");
			$q->execute();

			if ($q->rowCount() == 1) {
				$this->connected = true;
				$this->user = $q->fetch(PDO::FETCH_ASSOC);

				// Check utilisateur actif / inactif
				if (@$this->user[Users::USERS_ACTIVE] == '0') {
					$this->resetSessionData();
					$errAuthMsg = "User became inactive.";
					return false;
				}

				// Si connexion depuis cookie : on remet en place les sessions + cookies
				if (empty($_SESSION[ $this->password_cookie ]) || !empty($_SESSION[ $this->login_cookie ]))
					$this->setSecuredData();

				$this->updateUser($this->user['id']);
				return true;
			}
			else {
				$this->resetSessionData();
				$errAuthMsg = "Your session has expired.";
				return false;
			}
		}
		else return false;
	}

	// Génère le token (jeton) du navigateur en cours
	private function fingerprint() {
		$fingerprint = $this->salt . $_SERVER['HTTP_USER_AGENT'];
		$token = md5($fingerprint . session_id());

		return $token;
	}

	// On défini les variables d'identifications (token, login et mot de passe) !! obligation d'avoir défini $this->user avant de l'utiliser !!
	private function setSecuredData() {
		// declaration des sessions
		$_SESSION[ $this->password_cookie ] = $this->user['passwd'];
		$_SESSION[ $this->login_cookie ] = $this->user['login'];
		$_SESSION['token'] = $this->fingerprint();

		// déclaration des cookies
		setcookie( $this->login_cookie , $this->user['login'], COOKIE_PEREMPTION, "/");
		setcookie( $this->password_cookie , $this->user['passwd'], COOKIE_PEREMPTION, "/");
		setcookie( 'token' , $_SESSION['token'], COOKIE_PEREMPTION, "/");
	}

	// Reset complet des variables d'identification... C'est une déconnexion !
	private function resetSessionData() {
		// declaration des sessions
		$_SESSION[ $this->password_cookie ] = '';
		$_SESSION[ $this->login_cookie ] = '';
		$_SESSION['token'] = '';

		// destruction des cookies en leur mettant une expiration dans le passé
		$peremptionCookies = time() - (3600 * 24 * 31 * 365); // - 1 an
		setcookie( $this->login_cookie , '', $peremptionCookies, "/");
		setcookie( $this->password_cookie , '', $peremptionCookies, "/");
		setcookie( 'token' , '', $peremptionCookies, "/");

		$this->connected = false;
		$this->user = array();
		session_unset();
	}

	// Mise à jour de divers infos de connexion dans la BDD ($id : int du user) ($connexion : 0 ou 1)
	//     => 0 : test de connexion
	//     => 1 : connexion
	private function updateUser($id, $connexion = 0) {
		$date = time();
		$addReq = ($connexion == 1) ? ", `date_last_connexion` = '$date'" : "";
		$q = $this->db->prepare("UPDATE ".$this->user_table_name." SET `date_last_action` = '$date'$addReq WHERE `id` = '$id'");
		$q->execute();
	}
}

?>
