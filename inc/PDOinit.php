<?php

try {
	$dsn = 'mysql:dbname='.BASE.';host='.HOST ;
	$bdd = new PDO($dsn, USER, PASS, array(PDO::ATTR_PERSISTENT => true));
	$bdd->query("SET NAMES 'utf8'");
	$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	global $bdd;
}
catch (Exception $e) {
	die('Erreur de connexion PDO : '.$e->getMessage());
	//echo 'Erreur de connexion PDO : '.$e->getMessage();
}

?>