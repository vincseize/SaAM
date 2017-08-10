<?php
/*
 * CONFIG POUR LES FONCIONS DE MAILING
 */
define ("ROOTS_MAILS", 'vincseize@gmail.com, polossson@gmail.com');		// emails du noyaux créateur de SaAM (pour contact page de connexion)

define ('ALERT_LOGS', true);											// true pour envoyer un mail aux DEVS quand quelqu'un se connecte
define ('ALERT_SEND_TO', 'vincseize@gmail.com, polossson@gmail.com');	// emails à utiliser pour l'envoi des message de log

define ('DONT_SENDMAIL_LOCAL', true);									// true pour désactiver l'envoi de mail si on est en local

?>