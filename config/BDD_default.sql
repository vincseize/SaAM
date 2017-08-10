-- phpMyAdmin SQL Dump
-- version 3.3.2deb1ubuntu1
-- http://www.phpmyadmin.net
--
-- Serveur: localhost
-- Généré le : Ven 18 Janvier 2013 à 11:19
-- Version du serveur: 5.1.66
-- Version de PHP: 5.3.2-1ubuntu4.18

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Base de données: `saam`
--

-- --------------------------------------------------------

--
-- Structure de la table `saam_acl`
--

DROP TABLE IF EXISTS `saam_acl`;
CREATE TABLE IF NOT EXISTS `saam_acl` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `grp_name` varchar(64) NOT NULL,
  `1` varchar(8) NOT NULL,
  `2` varchar(8) NOT NULL,
  `3` varchar(8) NOT NULL,
  `4` varchar(8) NOT NULL,
  `5` varchar(8) NOT NULL,
  `6` varchar(8) NOT NULL,
  `7` varchar(8) NOT NULL,
  `8` varchar(8) NOT NULL,
  `9` varchar(8) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `grp_name` (`grp_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=49 ;

--
-- Contenu de la table `saam_acl`
--

INSERT INTO `saam_acl` (`id`, `grp_name`, `1`, `2`, `3`, `4`, `5`, `6`, `7`, `8`, `9`) VALUES
(1, 'ADMIN_DEPTS', 'N', 'N', 'N', 'N', 'N', 'A', 'A', 'A', 'A'),
(2, 'ADMIN_PROJECTS_ADD', 'N', 'N', 'N', 'N', 'A', 'A', 'A', 'A', 'A'),
(3, 'ADMIN_PROJECTS_MODIFY', 'N', 'N', 'N', 'N', 'O', 'O', 'O', 'A', 'A'),
(4, 'ADMIN_PROJECTS_VIEW', 'N', 'O', 'N', 'N', 'O', 'O', 'A', 'A', 'A'),
(5, 'ADMIN_UI', 'N', 'N', 'N', 'N', 'N', 'N', 'A', 'A', 'A'),
(6, 'ADMIN_USERS_ADD', 'N', 'N', 'N', 'N', 'A', 'A', 'A', 'A', 'A'),
(7, 'ADMIN_USERS_ASSIGN', 'N', 'N', 'N', 'N', 'O', 'O', 'O', 'A', 'A'),
(8, 'ADMIN_USERS_MODIFY', 'N', 'N', 'N', 'N', 'O', 'O', 'O', 'A', 'A'),
(9, 'ADMIN_USERS_VIEW', 'N', 'O', 'N', 'N', 'O', 'A', 'O', 'A', 'A'),
(10, 'ASSETS_ADMIN', 'N', 'N', 'N', 'N', 'A', 'N', 'A', 'A', 'A'),
(11, 'ASSETS_CREATE', 'N', 'N', 'N', 'N', 'A', 'A', 'A', 'A', 'A'),
(12, 'ASSETS_HANDLE', 'N', 'N', 'O', 'O', 'A', 'N', 'A', 'A', 'A'),
(13, 'ASSETS_MESSAGE', 'N', 'A', 'O', 'O', 'A', 'N', 'A', 'A', 'A'),
(14, 'ASSETS_PUBLISH', 'N', 'A', 'O', 'O', 'A', 'N', 'A', 'A', 'A'),
(15, 'ASSETS_REVIEW_ASK', 'N', 'N', 'O', 'O', 'A', 'N', 'A', 'A', 'A'),
(16, 'ASSETS_REVIEW_VALID', 'N', 'N', 'N', 'N', 'O', 'N', 'A', 'A', 'A'),
(17, 'ASSETS_TAGS', 'N', 'A', 'O', 'O', 'A', 'N', 'A', 'A', 'A'),
(18, 'ASSETS_UPLOAD', 'N', 'A', 'O', 'O', 'A', 'N', 'A', 'A', 'A'),
(19, 'BANK_UPLOAD', 'N', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A'),
(20, 'SCENES_ADMIN', 'N', 'N', 'N', 'N', 'A', 'A', 'A', 'A', 'A'),
(21, 'SCENES_HANDLE', 'N', 'N', 'O', 'O', 'A', 'N', 'A', 'A', 'A'),
(22, 'SCENES_MESSAGE', 'N', 'A', 'O', 'O', 'A', 'N', 'A', 'A', 'A'),
(23, 'SCENES_PUBLISH', 'N', 'A', 'O', 'O', 'A', 'N', 'A', 'A', 'A'),
(24, 'SCENES_REVIEW_ASK', 'N', 'N', 'O', 'O', 'A', 'N', 'A', 'A', 'A'),
(25, 'SCENES_REVIEW_VALID', 'N', 'N', 'N', 'N', 'O', 'N', 'A', 'A', 'A'),
(26, 'SHOTS_ADMIN', 'N', 'N', 'N', 'N', 'A', 'N', 'A', 'A', 'A'),
(27, 'SHOTS_DEPTS_INFOS', 'N', 'A', 'O', 'O', 'A', 'N', 'A', 'A', 'A'),
(28, 'SHOTS_MESSAGE', 'N', 'A', 'O', 'O', 'A', 'N', 'A', 'A', 'A'),
(29, 'SHOTS_PUBLISH', 'N', 'A', 'O', 'O', 'A', 'N', 'A', 'A', 'A'),
(30, 'SHOTS_TAGS', 'N', 'A', 'O', 'O', 'A', 'N', 'A', 'A', 'A'),
(31, 'SHOTS_UPLOAD', 'N', 'A', 'O', 'O', 'A', 'N', 'A', 'A', 'A'),
(32, 'VIEW_BANK_BTN_DEL', 'N', 'N', 'N', 'A', 'A', 'N', 'A', 'A', 'A'),
(33, 'VIEW_DEPT_CONFIG', 'N', 'N', 'N', 'N', 'N', 'A', 'A', 'A', 'A'),
(34, 'VIEW_DEPT_OVERVIEW', 'N', 'N', 'N', 'N', 'A', 'A', 'A', 'A', 'A'),
(35, 'VIEW_DEPT_PROD', 'N', 'N', 'N', 'N', 'N', 'A', 'A', 'A', 'A'),
(36, 'VIEW_DEPT_STRUCTURE', 'N', 'O', 'N', 'N', 'A', 'A', 'A', 'A', 'A'),
(37, 'VIEW_HEADER_BTN_PREF', 'N', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A'),
(38, 'VIEW_HEADER_BTN_WIP', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'A', 'A'),
(39, 'VIEW_HEADER_JCHAT', 'N', 'N', 'A', 'A', 'A', 'N', 'A', 'A', 'A'),
(40, 'VIEW_HEADER_SEARCH', 'N', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A'),
(41, 'VIEW_TOOLS_BTNS_ADMIN', 'N', 'A', 'N', 'N', 'A', 'A', 'A', 'A', 'A'),
(42, 'VIEW_TOOLS_BTNS_ADMIN_NEWS', 'N', 'N', 'N', 'N', 'N', 'A', 'A', 'A', 'A'),
(43, 'VIEW_TOOLS_BTNS_BUGHUNTER', 'N', 'N', 'A', 'A', 'A', 'A', 'A', 'A', 'A'),
(44, 'VIEW_TOOLS_BTNS_DEV', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'A', 'A'),
(45, 'VIEW_TOOLS_BTNS_NOTES', 'N', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A'),
(46, 'VIEW_TOOLS_BTNS_PLUGINS', 'N', 'N', 'A', 'A', 'A', 'A', 'A', 'A', 'A'),
(47, 'VIEW_TOOLS_BTNS_SCRIPT', 'N', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A'),
(48, 'VIEW_TOOLS_CAL', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A');

-- --------------------------------------------------------

--
-- Structure de la table `saam_all_langs`
--

DROP TABLE IF EXISTS `saam_all_langs`;
CREATE TABLE IF NOT EXISTS `saam_all_langs` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `constante` varchar(25) NOT NULL,
  `fr` tinytext NOT NULL,
  `en` tinytext NOT NULL,
  `ar` tinytext NOT NULL,
  `de` tinytext NOT NULL,
  `sp` tinytext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `constante` (`constante`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `saam_all_langs`
--

INSERT INTO `saam_all_langs` (`constante`, `fr`, `en`, `ar`, `de`, `sp`) VALUES
('L_VERSION', 'Version', 'Version', '', 'Version', 'Versión'),
('L_VERSIONS', 'Versions', 'Versions', '', 'Versionen', 'Versiónes'),
('L_PROJECT', 'Projet', 'Project', 'مخطط', 'Projekt', 'Proyecto'),
('L_CONX_ASK', 'Merci d''entrer vos identifiants', 'Please enter your ID''s', '', 'Bitte geben Sie Ihre IDs', ''),
('L_CONX_BYE', 'À bientot', 'Be seeing you', '', 'Tchüss', 'Hasta luego'),
('L_SEQUENCE', 'Séquence', 'Sequence', '', 'Sequence', 'Secuencias'),
('L_SHOT', 'Plan', 'Shot', '', 'Shot', 'Shot'),
('L_BTN_PREFS', 'PRÉFÉRENCES', 'PREFERENCES', 'بالأفضليات', 'WAHL', 'PREFERENCIA'),
('L_BTN_HELP', 'AIDE', 'HELP', 'مساعدة', 'HILFE', 'AYUDA'),
('L_BTN_ADMIN_NEWS', 'Gestion Nouvelles', 'Admin News', '', 'Narrichten Administration', 'Administraton noticias'),
('L_BTN_ADMIN_USERS', 'Gestion Utilisateurs', 'Admin Users', '', 'Users Administration', 'Administraton Usuarios'),
('L_BTN_ADMIN_PROJECTS', 'Gestion Projets', 'Admin Projects', '', 'Projekten Administration', 'Administraton Proyectos'),
('L_BTN_ADMIN', 'Administration', 'Administration', '', 'Administration', 'Administration'),
('L_BTN_NOTES', 'NOTES', 'NOTES', '', 'NOTEN', 'APUNTES'),
('L_BTN_SCRIPT', 'SCRIPTS', 'SCRIPTS', '', 'SCRIPTS', 'SCRIPTS'),
('L_BTN_CONFIG', 'CONFIG', 'CONFIG', '', 'KONFIGURATION', ''),
('L_BTN_UPDATE', 'MISE À JOUR', 'UPDATE', '', 'AKTUALIZIERUNG', 'ACTUALIZAR'),
('L_BTN_SQL', 'OUTILS SQL', 'SQL TOOLS', '', 'SQL TOOLS', 'SQL TOOLS'),
('L_BTN_API', 'API', 'API', '', 'API', 'API'),
('L_BTN_LANGUAGES', 'LANGUES', 'LANGUAGES', 'لغة', 'SPRACHEN', 'IDIOMAS'),
('L_BTN_DEBUG', 'DÉBUG', 'DEBUG', '', 'DEBUG', 'DEPURAR'),
('L_BTN_TRBACK', 'TRACKBACKS', 'TRACKBACKS', '', 'TRACKBACKS', 'TRACKBACKS'),
('L_BTN_YES', 'OUI', 'YES', 'بلى', 'JA', 'SI'),
('L_BTN_NO', 'NON', 'NO', 'لا', 'NEIN', 'NO'),
('L_CONX_ERR', 'Mauvais pseudo / mot de passe', 'Bad login / password', '', 'schlecht Login / password', 'contraseña equivocada'),
('L_ASSET', 'Asset', 'Asset', '', 'Asset', 'Asset'),
('L_BTN_RECETTE', 'RECETTE', 'ACCEPTANCE TESTING', '', 'ABNAHMEPRÜFUNG', ''),
('L_WIP', '! Travaux en cours !', '! work in progress !', '', 'In Arbeit', 'Trabajo en elaboración'),
('L_DEMO_MODE', 'Mode démo actif', 'Demo mode activated', '', 'Demo-Modus aktiviert', ''),
('L_BTN_FAQ', 'FAQ', 'FAQ', '', 'FAQ', 'PREGUNTAS FRECUENTES'),
('L_BTN_FRAMEWORK', 'FRAMEWORK', 'FRAMEWORK', '', 'FRAMEWORK', 'INFRAESTRUCTURA'),
('L_BTN_WIP', 'WIP', 'WIP', '', 'WIP', 'WIP'),
('L_PROJECTS', 'Projets', 'Projects', '', 'Projekten', 'Proyectos'),
('L_SEQUENCES', 'Séquences', 'Sequences', '', 'Sequences', 'Secuencias'),
('L_SHOTS', 'Plans', 'Shots', '', 'Shots', 'Shots'),
('L_BTN_DISCONNECT', 'Déconnexion', 'Logout', '', 'Logout', 'Cessar session'),
('L_GL_ADD', 'ajouter', 'add', '', 'hinzufügen', 'añadir'),
('L_GL_DEL', 'supprimer', 'delete', '', 'löshen', 'borrar'),
('L_GL_MOD', 'modifier', 'modify', '', 'verändern', 'modificar'),
('L_BTN_PROJ_ADD', 'ajouter projet', 'add project', '', 'Projekt anfügen', 'Añadir proyecto'),
('L_BTN_SEQ_ADD', 'ajouter séquence', 'add sequence', '', 'sequence anfügen', 'añadir secuencia'),
('L_BTN_SHOT_ADD', 'ajouter plan', 'add shot', '', 'Shot anfügen', ''),
('L_BTN_ASSET_ADD', 'ajouter asset', 'add asset', '', 'Asset anfügen', 'Añadir asset'),
('L_BTN_ASSET_MOD', 'modifier asset', 'modify asset', '', 'Asset anfügen', 'Modificar asset'),
('L_BTN_ASSET_DEL', 'supprimer asset', 'delete asset', '', 'Asset abnehmen', 'Borrar asset'),
('L_ASSETS', 'Assets', 'Assets', '', 'Assets', 'Assets'),
('L_GLOBAL_VIEW', 'Vue d''ensemble', 'Global View', '', 'Overview', 'Overview'),
('L_DAILIES', 'DAILIES', 'DAILIES', '', 'DAYLIES', 'DAYLIES'),
('L_STRUCTURE', 'STRUCTURE PLANS', 'SHOTS STRUCTURE', '', 'SHOTS STRUCTURE', 'SHOTS STRUCTURE'),
('L_BUDGET', 'BUDGET', 'FINANCE', '', 'BUDGET', 'PRESUPUESTO'),
('L_FPS', 'Fps', 'Fps', '', 'Fps', 'Fps'),
('L_FORMAT', 'Format', 'Format', '', 'Format', 'Formato'),
('L_FRAMES', 'Frames', 'Frames', '', 'Frames', 'Frames'),
('L_YEAR', 'An', 'Year', '', 'Jahr', 'Año'),
('L_YEARS', 'Ans', 'Years', '', 'Jähre', 'Años'),
('L_WEEK', 'Semaine', 'Week', '', 'Woche', 'Semana'),
('L_WEEKS', 'Semaines', 'Weeks', '', 'Wochen', 'Semanas'),
('L_DAY', 'Jour', 'Day', '', 'Tag', 'Dia'),
('L_DAYS', 'Jours', 'Days', '', 'Tage', 'Dias'),
('L_DAYX', 'Jour(s)', 'Day(s)', '', 'Tag(e)', 'Dia(s)'),
('L_HOUR', 'Heure', 'Hour', '', 'Stunde', 'Hora'),
('L_HOURS', 'Heures', 'Hours', '', 'Stunden', 'Horas'),
('L_OUTDATE_SINCE', 'dépassée depuis', 'outdated since', '', 'veraltet seit', 'Anticuado desde'),
('L_MINUTE', 'Minute', 'Minute', '', 'Minute', 'Minuto'),
('L_MINUTES', 'Minutes', 'Minutes', '', 'Minuten', 'Minutos'),
('L_SECOND', 'Seconde', 'Second', '', 'Sekunde', 'Segunda'),
('L_SECONDS', 'Secondes', 'Seconds', '', 'Sekunden', 'Segundas'),
('L_MONTH', 'Mois', 'Month', '', 'Monate', 'Mes'),
('L_MONTHS', 'Mois', 'Monthes', '', 'Monaten', 'Meses'),
('L_BANK', 'BANK', 'BANK', '', 'BANK', 'BANK'),
('L_NO_RETAKE', 'Pas encore de published', 'No published', '', 'Keine Published', ''),
('L_RETAKE_MESSAGES', 'Messages du published', 'Published messages', '', 'Nächrichten von der Published ', 'Mensaje de Retake'),
('L_LEVEL', 'Niveau', 'Level', '', 'Höhe', 'Nivel'),
('L_BTN_BUGHUNTER', 'BUG HUNTER', 'BUG HUNTER', '', 'BUG HUNTER', 'BUG HUNTER'),
('L_ADD_MESSAGE', 'Nouveau message', 'New message', '', 'Neue Nachricht', 'Nuevo mensaje'),
('L_ADD_RETAKE_ERROR', 'Il faut uploader un published avant de valider. Glissez-déposez un fichier sur la zone mauve.', 'You must upload a published before validate. Drag and drop a file on the purple zone.', '', 'Sie müssen eine Wiederholungsprüfung vor validate hochladen. Drag&drop...', ''),
('L_TODAY', 'Aujourd''hui', 'Today', '', 'Heute', 'Hoy'),
('L_TOMORROW', 'Demain', 'Tomorrow', '', 'Morgen', 'Mañana'),
('L_CURRENT', 'en cours', 'current', '', 'Laufend', 'Corriente'),
('L_SHORTCUTS', 'Raccourcis', 'Shortcuts', '', 'Shortcuts', 'Atajo'),
('L_FILETYPE_ALLOW', 'Types de fichiers authorisés', 'Allowed filetypes', '', 'Erlaubte Datatypen', 'Tipo de ficheros autorizados'),
('L_FRAMERATE', 'Cadence', 'Framerate', '', 'Framerate', 'Framerate'),
('L_DESCRIPTION', 'Description', 'Description', '', 'Beschreibung', 'Descripción'),
('L_PASSWORD', 'Mot de Passe', 'Password', '', 'Passwort', 'Contraseña'),
('L_USER', 'Utilisateur', 'User', '', 'User', 'Usuario'),
('L_TITLE', 'Titre', 'Title', '', 'Title', 'Titulo'),
('L_DATE', 'Date', 'Date', '', 'Datum', 'Fecha'),
('L_DATES', 'Dates', 'Dates', '', 'Datum', 'Fechas'),
('L_MODIFICATION', 'Modification', 'Update', '', 'Änderung', 'Modificación'),
('L_BTN_VALID', 'Valider', 'Validate', '', 'bestätigen', ''),
('L_BTN_CANCEL', 'Annuler', 'Cancel', '', 'Cancel', 'Cancelar'),
('L_RANGE', 'Bornes', 'Range', '', 'Reihe', ''),
('L_START', 'Début', 'Start', '', 'Anfang', 'Principio'),
('L_END', 'Fin', 'End', '', 'Ende', 'Final'),
('L_WELCOME', 'BIENVENUE', 'WELCOME', '', 'Willkommen', 'BIENVENUDO'),
('L_TRACKBACK_LINK', 'Voir la liste des bugs', 'View the list of bugs', '', 'Die Liste der Bugs', 'Ver la lista de errores'),
('L_BTN_ADD_BUG', 'J''ai trouvé un bug', 'I found a bug', '', 'Ich habe einen Bug gefunden', 'Encontre un error'),
('L_THANKS_BUG', 'MERCI de votre contribution !', 'THANK YOU for your contribution!', '', 'DANKE für Ihren Beitrag!', 'Gracias por su contribución'),
('L_FINAL', 'FINAL', 'FINAL', '', 'FINAL', 'FINAM'),
('L_CREATOR', 'Créateur', 'Creator', '', 'Bildner', 'Creator'),
('L_SHOW', 'Montrer', 'Show', '', 'anzeigen', 'Enseñar'),
('L_HIDE', 'Cacher', 'Hide', '', 'Verstecken', 'ocultar'),
('L_LOCK', 'Bloquer', 'Lock', '', 'Sperren', 'Bloquear'),
('L_MODIFY', 'Modifier', 'Modify', '', 'Ändern', 'Modificar'),
('L_ARCHIVE', 'Archiver', 'Archive', '', 'Archivieren', 'Archivar'),
('L_RESTORE', 'restaurer', 'restore', '', 'wiederherstellen', 'Restaurar'),
('L_DESTRUCT', 'détruire', 'destroy', '', 'zerstören', 'destruir'),
('L_SCENARIO', 'scénario', 'scenario', '', 'Szenario', 'Escenario'),
('L_SOUND', 'son', 'sound', '', 'Ton', 'Sonido'),
('L_DECTECH', 'dec.tech.', 'tech.script', '', 'Tech. Skript', ''),
('L_STORYBOARD', 'storyboard', 'storyboard', '', 'storyboard', 'storyboard'),
('L_DEPTS', 'Départements', 'Departments', '', 'Abteilungen', 'Departamentos'),
('L_INTERFACE', 'Interface', 'Interface', '', 'Interface', 'Interfaz'),
('L_PLUGINS', 'Plugins', 'Plugins', '', 'Plugins', 'Plugins'),
('L_MAJ', 'Mise à jour', 'Update', '', 'Aktualisierung', 'Actualización'),
('L_ABOUT', 'À propos', 'About', '', 'Über', 'Sobre'),
('L_BTN_ADD_DEPT', 'Ajouter un département', 'Add a department', '', 'Abteilungen hinzufügen', 'Añadir un departamento'),
('L_ADMIN_DEPT', 'Gestion des départements', 'Manage departments', '', 'Abteilungen verwalten', 'Gestión de departamento'),
('L_LOCKED', 'Bloqué', 'Locked', '', 'Verklebt', 'Bloquear'),
('L_TEAM', 'Équipe', 'Team', '', 'Mannschaft', 'Equipo'),
('L_ASSIGNED_TO', 'attribués à', 'assigned to', '', 'zuweisend Shots', ''),
('L_NOTHING', 'Aucun', 'No', '', 'Nichts', 'Ningún'),
('L_FIRST_TIME', 'C''est la première fois que vous venez ? Choisissez un département ^ !', 'First time you come here ? Choose a department ^ !', '', 'Zum ersten Mal kommen Sie hier? Wählen Sie eines Abteilungen ^!', ''),
('L_ETAPES', 'Étapes', 'Steps', '', 'Stufen', 'Etapa'),
('L_TAGS', 'Tags', 'Tags', '', 'Tags', ''),
('L_APPROVED', 'Validé', 'Approved', '', 'Bewährt', 'Validar'),
('L_ASSIGNMENTS', 'Assignations', 'Assignments', '', 'Zuweisungen', ''),
('L_ADD_RETAKE', 'Créer un published', 'Add a published', '', 'Published anfügen', 'Adición de published'),
('L_ASSET_HUNG_BY', 'Tenu par', 'Hung by', '', 'Handgehabt von', ''),
('L_ASSET_FREE', 'libre', 'free', '', 'befreit', 'libre'),
('L_NOBODY', 'Personne', 'Nobody', '', 'Niemand', 'Nadie'),
('L_FREE_ASSET', 'Libérer asset', 'Free asset', '', 'Asset befreien', 'Liberar asset'),
('L_ASSET_HANDLE', 'Prendre la main', 'Handle asset', '', 'Asset handhaben', ''),
('L_NOTES', 'Notes', 'Notes', '', 'Noten', 'Apuntes'),
('L_NEWS', 'Nouvelles', 'News', '', 'Nachrichten', 'Noticias'),
('L_RETAKES', 'Published', 'Published', '', 'Published', 'Published'),
('L_USERS', 'Utilisateurs', 'Users', '', 'Benutzer', 'Usuarios'),
('L_MY_SHOTS', 'Vos plans', 'Your shots', '', 'Deinen Shots', 'Sus shots'),
('L_MY_ASSETS', 'Vos assets', 'Your assets', '', 'Deinen Assets', 'Sus assets'),
('L_MY_NOTES', 'Vos notes', 'Your notes', '', 'Deine Noten', 'Sus apuntes'),
('L_ROOT', 'Racine', 'Root', '', 'Root', 'Root'),
('L_INFOS', 'Infos', 'Infos', '', 'Infos', 'Noticias'),
('L_DIRECTOR', 'Réalisateur', 'Director', '', 'Director', 'Director'),
('L_OTHERS', 'Autres', 'Others', '', 'Anderen', 'Otros'),
('L_THIS_WEEK', 'Cette semaine', 'This week', '', 'Diese Woche', 'Esta semana'),
('L_LAST_WEEK', 'La semaine dernière', 'Past week', '', 'Letzte Woche', 'La última semana'),
('L_DAILY', 'Daily', 'Daily', '', 'Dayly', 'Dayly'),
('L_ADD', 'Ajouter', 'Add', '', 'Hinzufügen', 'Añadir'),
('L_DELETE', 'Supprimer', 'Delete', '', 'Löschen', 'Borrar'),
('L_LIST', 'Liste', 'List', '', 'Liste', 'Lista'),
('L_NO_NOTE', 'Aucune note', 'No note', '', 'Keine Anmerkungen', 'Ningún apunte'),
('L_DECTECH_LONG', 'Découpage technique', 'Technical script', '', 'Technische Skript', ''),
('L_CALENDAR', 'Calendrier', 'Calendar', '', 'Kalender', 'Calendario'),
('L_INTRANET', 'Intranet', 'Intranet', '', 'Intranet', 'Intranet'),
('L_BTN_SAVE', 'Sauvegarder', 'Save', '', 'sparen', 'Guardar'),
('L_DEPENDENCIES', 'Dépendances', 'Dependencies', '', 'Abhängigkeiten', ''),
('L_IN_SCENES', 'Utilisé dans les scènes', 'Used in scenes', '', 'verwendet in Scenes', ''),
('L_SCENES', 'Scènes', 'Scenes', '', 'Scenes', 'Escenas'),
('L_SCENE', 'Scène', 'Scene', '', 'Scene', 'Escena'),
('L_PROD', 'Prod', 'Prod', '', 'Prod', 'Prod'),
('L_SCHEDULE', 'Calendrier', 'Schedule', '', 'Kalender', 'Calendario'),
('L_EVERYTHING', 'Tout', 'All', '', 'Alle', 'Todo'),
('L_KEYFRAME', 'Keyframe', 'Keyframe', '', 'Keyframe', ''),
('L_MOCAP', 'Mocap', 'Mocap', '', 'Mocap', 'Mocap'),
('L_VFX', 'VFX', 'VFX', '', 'VFX', 'VFX'),
('L_SET', 'Set', 'Set', '', 'Set', 'Set'),
('L_VIEW_MODE', 'Mode d''Affichage', 'View Mode', '', 'View Mode', ''),
('L_NO_SCENE', 'Aucune scène', 'No scene', '', 'Keine Scene', 'Ningún escena'),
('L_SELECT_SCENE', 'Sélectionnez une scène', 'Select a scene', '', 'Wählen Sie eine Scene', 'Seleccionar une escena'),
('L_DERIVATIVES', 'Dérivées', 'Derivatives', '', 'Derivatives', ''),
('L_DERIVATIVE', 'Dérivée', 'Derivative', '', 'Derivative', ''),
('L_MASTER', 'Master', 'Master', '', 'Master', ''),
('L_ASSIGN', 'Assigner', 'Assign', '', 'Zuweisen', 'Asignar'),
('L_CAMERAS', 'Cameras', 'Cameras', '', 'Kameras', 'Cámaras'),
('L_MANAGE', 'Gérer', 'Manage', '', 'Verwalten', 'Maneja'),
('L_USER_PREFS_INFOS', 'Infos utilisateur', 'User infos', '', 'Benutzer Infos', 'Info usuario'),
('L_USER_PREFS_UI', 'Interface utilisateur', 'User interface', '', 'Benutzer Interface', 'Interfaz usuario'),
('L_OPEN', 'Ouvrir', 'Open', '', 'Aufmachen', 'Abrir'),
('L_LABEL', 'Label', 'Label', '', 'Etikett', ''),
('L_ARTISTS', 'Artistes', 'Artists', '', 'Artists', 'Artistas'),
('L_ANSWER', 'Répondre', 'Answer', '', 'Antwort', 'Responder'),
('L_SUPERVISOR', 'Superviseur', 'Supervisor', '', 'Supervisor', ''),
('L_LEAD', 'Lead', 'Lead', '', 'Lead', 'Lead'),
('L_ADD_PROJECT', 'Ajout de projet', 'Adding project', '', 'Neues Projekt', 'adición de proyecto'),
('L_BASE_INFOS', 'Informations de base', 'Base informations', '', 'Basisinformationen', 'Información basica'),
('L_PROJECT_NAME', 'Nom du projet', 'Project name', '', 'Projekt Name', 'Nombre del proyecto'),
('L_PRODUCTION', 'Production', 'Production', '', 'Produktion', 'Producción'),
('L_NOMENCLATURA', 'Nomenclature', 'Nomenclatura', '', 'Nomenklatura', 'Nomenclatura'),
('L_REF', 'Référence', 'Reference', '', 'Referenz', 'Referencia'),
('L_PROJECT_TYPE', 'Type de projet', 'Project type', '', 'Projekt Type', 'Tipo de proyecto'),
('L_SOFTWARE_USED', 'Logiciels utilisés', 'Used Softwares', '', 'gebrauchten Software', 'Softwares utilizados'),
('L_VIGNETTE', 'Vignette', 'Vignette', '', 'Vignette', ''),
('L_SEND', 'Envoyer', 'Send', '', 'senden', 'Enviar'),
('L_NEXT', 'Suivant', 'Next', '', 'Nächste', 'Siguiente'),
('L_DONE', 'Terminé', 'Done', '', 'erledigt', 'Terminado'),
('L_BTN_TUTOS', 'TUTORIELS', 'TUTORIALS', '', 'TUTORIALS', 'TUTORIALES'),
('L_TUTO_FOR', 'Tutoriels pour', 'Tutorials for', '', 'Tutorials für', 'Tutoriales para'),
('L_ALL_USERS', 'Tout le monde', 'Everybody', '', 'Alle', 'Todo el mundo'),
('L_ARTIST', 'Artiste', 'Artist', '', 'Artist', 'Artista'),
('L_DIR_PROD', 'Dir. production', 'Prod manager', '', 'Prod manager', 'Dir. Producción'),
('L_DEVELOPPER', 'Développeur', 'Developer', '', 'Entwickler', 'Programador'),
('L_VISITOR', 'Visiteur', 'Visitor', '', 'Gast', 'Visitante'),
('L_MAGIC', 'Magique', 'Magic', '', 'Magic', 'Mágico'),
('L_SKILLS', 'Compétences', 'Skills', '', 'Fähigkeiten', 'Conocimientos'),
('L_RENAME', 'Renommer', 'Rename', '', 'Wieder ernennen', 'Renombrar'),
('L_PATH', 'Chemin', 'Path', '', 'path', 'Camino'),
('L_FIRST_NAME', 'Prénom', 'First name', '', 'Vorname', 'Nombre'),
('L_NAME', 'Nom', 'Name', '', 'Name', 'Apellido'),
('L_STATUS', 'Status', 'Status', '', 'Status', 'Estatuto'),
('L_BTN_OVERVIEW', 'OVERVIEW', 'OVERVIEW', '', 'OVERVIEW', 'OVERVIEW'),
('L_MESSAGES', 'Messages', 'Messages', '', 'Beiträge', 'Mensajes'),
('L_BTN_CHARTS', 'STATS', 'CHARTS', '', 'STATS', 'GRAFICAS'),
('L_MONTHLY_ACTIVITY', 'Activité mensuelle', 'Monthly activity', '', 'Monate aktivität', 'Actividad mensual'),
('L_NUMBER_OF', 'Nombre de', 'Number of', '', 'Anzahl der', 'Número de'),
('L_STORAGE_DISTRIBUTION', 'Répartition du stockage', 'Storage distribution', '', 'Lagerung Distribution', 'Distribucion del almacenamiento'),
('L_STORAGE', 'Stockage', 'Storage', '', 'Lagerung', 'Almacenamiento'),
('L_STANDBY', 'En attente', 'Standby', '', 'Warten', 'Stand-by'),
('L_TASKS', 'Tâches', 'Tasks', '', 'Aufgaben', 'Taeras'),
('L_TASK', 'Tâche', 'Task', '', 'Aufgabe', 'Taera'),
('L_ADD_TASK', 'Ajouter une tâche', 'Add a task', '', 'Task hinzufügen', 'Añadir una tarea'),
('L_TYPE', 'Type', 'Type', '', 'Art', 'Tipo'),
('L_MY_TASKS', 'Vos tâches', 'Your tasks', '', 'Deinen Aufgaben', 'Sus tareas'),
('L_FROM', 'De', 'From', '', 'Von', 'De'),
('L_TO', 'Pour', 'To', '', 'Für', 'Para'),
('L_BTN_SECTION_ROOT', 'Racine', 'Root', '', 'Root', 'Root'),
('L_BTN_SECTION_SHOTS', 'Séq. & Plans', 'Seq. & Shots', '', 'Seq. & Shots', 'Seq. & Shots'),
('L_BTN_SECTION_SCENES', 'Scènes', 'Scenes', '', 'Scenes', 'Escenas'),
('L_BTN_SECTION_ASSETS', 'Assets', 'Assets', '', 'Assets', 'Assets'),
('L_BTN_SECTION_TASKS', 'Tâches', 'Tasks', '', 'Aufgaben', 'Tareas'),
('L_MY_SCENES', 'Vos scènes', 'Your scenes', '', 'Deinen Scenes', 'Sus escenas'),
('L_BACKUP', 'Sauvegarde ZIP', 'Backup ZIP', '', 'ZIP Backup', 'Backup Zip'),
('L_OPEN_PROJECT', 'Ouvrir le projet', 'Open project', '', 'Offnen Projekt', 'Abrir el proyecto'),
('L_FREE_SCENE', 'Libérer la scène', 'Free scene', '', 'Scene befreien', 'Liberar escena'),
('L_PROGRESS', 'Progression', 'Progress', '', 'Progression', 'Progreso'),
('L_REFRESH', 'Rafraîchir', 'Refresh', '', 'Aktualisieren', 'Actualizar');

-- --------------------------------------------------------

--
-- Structure de la table `saam_assets`
--

DROP TABLE IF EXISTS `saam_assets`;
CREATE TABLE IF NOT EXISTS `saam_assets` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `category` int(2) NOT NULL DEFAULT '0',
  `filename` varchar(256) NOT NULL,
  `path_relative` varchar(256) NOT NULL,
  `ID_projects` text NOT NULL,
  `ID_shots` text NOT NULL,
  `ID_creator` int(6) NOT NULL,
  `ID_handler` int(6) NOT NULL DEFAULT '0',
  `description` varchar(256) NOT NULL,
  `review` text NOT NULL,
  `team` varchar(256) NOT NULL,
  `custom_attr` text NOT NULL,
  `deadline` datetime NOT NULL,
  `date` datetime NOT NULL,
  `update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` int(6) NOT NULL,
  `hide` tinyint(1) NOT NULL,
  `checkretake` tinyint(1) NOT NULL,
  `progress` int(3) NOT NULL,
  `relations_assets` text NOT NULL,
  `archive` tinyint NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `filename` (`filename`),
  KEY `category` (`category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Structure de la table `saam_assets_depts_infos`
--
DROP TABLE IF EXISTS `saam_assets_depts_infos`;
CREATE TABLE IF NOT EXISTS `saam_assets_depts_infos` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `ID_project` int(4) NOT NULL,
  `ID_asset` int(6) NOT NULL,
  `8` varchar(256) NOT NULL,
  `9` varchar(256) NOT NULL,
  `10` varchar(256) NOT NULL,
  `12` varchar(256) NOT NULL,
  `11` varchar(256) NOT NULL,
  `13` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Structure de la table `saam_cameras`
--

DROP TABLE IF EXISTS `saam_cameras`;
CREATE TABLE IF NOT EXISTS `saam_cameras` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `ID_project` int(6) NOT NULL,
  `ID_scene` int(6) NOT NULL,
  `ID_sequence` int(6) NOT NULL,
  `ID_shot` int(6) NOT NULL,
  `ID_creator` int(6) NOT NULL,
  `update` datetime NOT NULL,
  `updated_by` int(6) NOT NULL,
  `tags` text NOT NULL,
  `hide` tinyint(1) NOT NULL,
  `archive` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Structure de la table `saam_comments_asset`
--

DROP TABLE IF EXISTS `saam_comments_asset`;
CREATE TABLE IF NOT EXISTS `saam_comments_asset` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `ID_asset` int(9) NOT NULL,
  `ID_project` int(6) NOT NULL,
  `dept` varchar(64) NOT NULL,
  `response_to` int(12) NOT NULL,
  `comment` text NOT NULL,
  `senderId` int(6) NOT NULL,
  `senderLogin` varchar(32) NOT NULL,
  `senderStatus` int(2) NOT NULL,
  `sender` varchar(32) NOT NULL,
  `num_retake` int(3) NOT NULL,
  `read_by` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ID_asset` (`ID_asset`),
  KEY `response_to` (`response_to`),
  KEY `num_retake` (`num_retake`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



-- --------------------------------------------------------

-- suppression old table "blockanim" pour nettoyage
DROP TABLE IF EXISTS `saam_comments_blockanim`;

--
-- Structure de la table `saam_comments_final`
--

DROP TABLE IF EXISTS `saam_comments_final`;
CREATE TABLE `saam_comments_final` (
  `id` int(12) NOT NULL auto_increment,
  `ID_project` int(9) NOT NULL,
  `response_to` int(12) NOT NULL,
  `comment` text NOT NULL,
  `senderId` int(6) NOT NULL,
  `senderLogin` varchar(32) NOT NULL,
  `senderStatus` int(2) NOT NULL,
  `sender` varchar(32) NOT NULL,
  `read_by` text NOT NULL,
  `date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `ID_project` (`ID_project`),
  KEY `response_to` (`response_to`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `saam_comments_scenes`
--

DROP TABLE IF EXISTS `saam_comments_scenes`;
CREATE TABLE IF NOT EXISTS `saam_comments_scenes` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `ID_scene` int(9) NOT NULL,
  `ID_project` int(6) NOT NULL,
  `dept` varchar(64) NOT NULL,
  `response_to` int(12) NOT NULL,
  `comment` text NOT NULL,
  `senderId` int(6) NOT NULL,
  `senderLogin` varchar(32) NOT NULL,
  `senderStatus` int(2) NOT NULL,
  `sender` varchar(256) NOT NULL,
  `num_retake` int(3) NOT NULL,
  `read_by` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ID_scene` (`ID_scene`),
  KEY `response_to` (`response_to`),
  KEY `num_retake` (`num_retake`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `saam_comments_shots`
--

DROP TABLE IF EXISTS `saam_comments_shots`;
CREATE TABLE IF NOT EXISTS `saam_comments_shots` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `ID_project` int(6) NOT NULL,
  `ID_shot` int(9) NOT NULL,
  `dept` varchar(64) NOT NULL,
  `response_to` int(12) NOT NULL,
  `senderId` int(6) NOT NULL,
  `senderLogin` varchar(32) NOT NULL,
  `senderStatus` int(2) NOT NULL,
  `comment` text NOT NULL,
  `sender` varchar(32) NOT NULL,
  `num_retake` int(3) NOT NULL,
  `read_by` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ID_shot` (`ID_shot`),
  KEY `response_to` (`response_to`),
  KEY `num_retake` (`num_retake`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `saam_comments_tasks`
--

DROP TABLE IF EXISTS `saam_comments_tasks`;
CREATE TABLE IF NOT EXISTS `saam_comments_tasks` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `ID_task` int(9) NOT NULL,
  `ID_project` int(6) NOT NULL,
  `comment` text NOT NULL,
  `sender` varchar(256) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ID_asset` (`ID_task`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Structure de la table `saam_dailies`
--

CREATE TABLE IF NOT EXISTS `saam_dailies` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `ID_project` int(4) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user` int(4) NOT NULL,
  `groupe` varchar(32) NOT NULL,
  `type` varchar(32) NOT NULL,
  `corresp` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ID_project` (`ID_project`),
  KEY `date` (`date`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Structure de la table `saam_dailies_summary`
--

CREATE TABLE IF NOT EXISTS `saam_dailies_summary` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `ID_project` int(5) NOT NULL,
  `week` varchar(8) NOT NULL,
  `user` int(5) NOT NULL,
  `comment` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Structure de la table `saam_depts`
--

DROP TABLE IF EXISTS `saam_depts`;
CREATE TABLE IF NOT EXISTS `saam_depts` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `type` varchar(12) NOT NULL,
  `label` varchar(32) NOT NULL,
  `position` int(2) NOT NULL,
  `template_name` varchar(32) NOT NULL,
  `etapes` text NOT NULL,
  `hide` tinyint(1) NOT NULL,
  `dict` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `position` (`position`),
  KEY `label` (`label`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 ;

--
-- Contenu de la table `saam_depts`
--

INSERT INTO `saam_depts` (`id`, `type`, `label`, `position`, `template_name`, `etapes`, `hide`, `dict`) VALUES
(1, 'shots', 'blockanim', 1, 'template_1', '["WIP"]', 0, ''),
(2, 'shots', 'tournage', 2, 'template_1', '["En tournage","D\\u00e9rushage"]', 0, ''),
(3, 'shots', 'mocap', 3, 'template_1', '["Shooting","Cleaning"]', 0, ''),
(4, 'shots', 'anims', 4, 'template_1', '["Poses","Anim draft","Anim fine"]', 0, ''),
(5, 'shots', 'vfx', 5, 'template_1', '["Passe 1","Passe 2"]', 0, ''),
(6, 'shots', 'compositing', 6, 'template_1', '["Matte-painting","VFX","DoF"]', 0, ''),
(7, 'shots', 'montage', 7, 'template_1', '["Dérushage","Passe 1","Passe 2"]', 0, ''),
(8, 'shots', 'etalonnage', 8, 'template_1', '["WIP"]', 0, ''),
(9, 'scenes', 'keyframes', 1, '05_scenes', '["todo","layout","draft","fine"]', 0, ''),
(10, 'scenes', 'mocap', 2, '05_scenes', '["todo","WIP"]', 0, ''),
(11, 'scenes', 'vfx', 3, '05_scenes', '["todo","WIP"]', 0, ''),
(12, 'assets', 'concept', 1, '06_assets', '["todo","WIP"]', 0, ''),
(13, 'assets', 'modeling', 2, '06_assets', '["todo","WIP"]', 0, ''),
(14, 'assets', 'sculpting', 3, '06_assets', '["todo","WIP"]', 0, ''),
(15, 'assets', 'rigging', 4, '06_assets', '["todo","WIP"]', 0, ''),
(16, 'assets', 'furs', 5, '06_assets', '["todo","WIP"]', 0, ''),
(17, 'assets', 'texturing', 6, '06_assets', '["todo","WIP"]', 0, '');

-- --------------------------------------------------------

-- suppression old table "infos" pour nettoyage
DROP TABLE IF EXISTS `saam_infos`;
--

--
-- Structure de la table `saam_config`
--

DROP TABLE IF EXISTS `saam_config`;

CREATE TABLE IF NOT EXISTS `saam_config` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `version` varchar(25) NOT NULL,
  `oldversion` varchar(25) NOT NULL,
  `default_lang` varchar(2) NOT NULL,
  `default_theme` varchar(12) NOT NULL,
  `fps_list` varchar(256) NOT NULL,
  `default_fps` int(3) NOT NULL DEFAULT '25',
  `ratio_list` varchar(256) NOT NULL,
  `date_format` varchar(256) NOT NULL,
  `url_intranet` varchar(1024) NOT NULL,
  `home_max_news` int(11) NOT NULL DEFAULT '4',
  `calendar_file` varchar(256) NOT NULL,
  `default_depts` text NOT NULL,
  `default_project_types` text NOT NULL,
  `default_seqLabel` varchar(128) NOT NULL,
  `default_shotLabel` varchar(128) NOT NULL,
  `default_scenesLabel` varchar(128) NOT NULL,
  `alert_uploads` tinyint(1) NOT NULL DEFAULT '1',
  `alert_retakes` tinyint(1) NOT NULL DEFAULT '1',
  `alert_messages` tinyint(1) NOT NULL DEFAULT '1',
  `global_tags` text NOT NULL,
  `assets_categories` text NOT NULL,
  `default_assets_dirs` text NOT NULL,
  `default_assets_exclude_dirs` text NOT NULL,
  `default_data_folders` text NOT NULL,
  `default_status` varchar(256) NOT NULL,
  `available_softs` text NOT NULL,
  `available_assets_extensions` text NOT NULL,
  `available_competences` text NOT NULL,
  `user_status` varchar(256) NOT NULL,
  `dectech_infos` text NOT NULL,
  `wip` tinyint(1) NOT NULL DEFAULT '0',
  `dailies_max_weeks` int(3) NOT NULL,
  `projects_size` float NOT NULL,
  `plugins_enabled` varchar(256) NOT NULL,
  `alert_tasks` int(3) NOT NULL,
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `version` (`version`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;

--
-- Contenu de la table `saam_config`
--

INSERT INTO `saam_config` (`id`, `version`, `oldversion`, `default_lang`, `default_theme`, `fps_list`, `default_fps`, `ratio_list`, `date_format`, `url_intranet`, `home_max_news`, `calendar_file`, `default_depts`, `default_project_types`, `default_seqLabel`, `default_shotLabel`, `default_scenesLabel`, `alert_uploads`, `alert_retakes`, `alert_messages`, `global_tags`, `assets_categories`, `default_assets_dirs`, `default_assets_exclude_dirs`, `default_data_folders`, `default_status`, `available_softs`, `available_assets_extensions`, `available_competences`, `user_status`, `dectech_infos`, `wip`, `dailies_max_weeks`, `projects_size`, `plugins_enabled`, `alert_tasks`) VALUES
(1, '0.1', '0.0', 'fr', 'dark', '16|18|23.97|24|25|30|60|100', 25, '16:9|4:3', 'd/m/Y', 'http://saamanager.net', 4, 'datas/calendar/events.json', '["layout"]', '["demo","short","feature","commercial","effects","game","web","test"]', 'seq_##', 'shot_###', '', 1, 1, 1, '', '{"1":"characters","2":"sets","3":"props","4":"textures","5":"matte"}', '["characters","props","sets","vehicles","terrains"]', '["refs","bank","stills"]', '["refs","bank","stills"]', '{"0":"WIP","1":"On Hold","2":"VALID"}', '["blender","Zbrush","AfterEffect","Maya","Ardour"]', '', '["director","scenarist","cameraman","lightTech","soundTech","artist2D","artist3D","editor","coder","VFX","soundMix"]', '', '{"action":{"1":["Lieu",""],"2":["Int-Ext",""],"3":["day-night",""],"4":["Action",""]},"camera":{"1":["Valeur_Plan",""],"2":["Mvt_Cam",""],"3":["Angle",""],"4":["Raccord",""]},"son":{"1":["Son_Amb",""],"2":["Dialogue",""],"3":["In-Off",""],"4":["Musique",""]}}', 0, 0, 0, '', 0),
(2, '0.2', '0.1', 'fr', 'dark', '16|18|23.97|24|25|30|60|100', 25, '16:9|4:3', 'd/m/Y', 'http://saamanager.net', 4, 'datas/calendar/events.json', '["layout"]', '["demo","short","feature","commercial","effects","game","web","test"]', 'seq_##', 'shot_###', '', 1, 1, 1, '', '{"1":"characters","2":"sets","3":"props","4":"textures","5":"matte"}', '["characters","props","sets","vehicles","terrains"]', '["refs","bank","stills"]', '["refs","bank","stills"]', '{"0":"WIP","1":"On Hold","2":"VALID"}', '["blender","Zbrush","AfterEffect","Maya","Ardour"]', '', '["director","scenarist","cameraman","lightTech","soundTech","artist2D","artist3D","editor","coder","VFX","soundMix"]', '', '{"action":{"1":["Lieu",""],"2":["Int-Ext",""],"3":["day-night",""],"4":["Action",""]},"camera":{"1":["Valeur_Plan",""],"2":["Mvt_Cam",""],"3":["Angle",""],"4":["Raccord",""]},"son":{"1":["Son_Amb",""],"2":["Dialogue",""],"3":["In-Off",""],"4":["Musique",""]}}', 0, 0, 0, '', 0),
(3, '0.3', '0.2', 'fr', 'dark', '16|18|23.97|24|25|30|60|100', 25, '16:9|4:3', 'd/m/Y', 'http://saamanager.net', 4, 'datas/calendar/events.json', '["layout"]', '["demo","short","feature","commercial","effects","game","web","test"]', 'seq_##', 'shot_###', '', 1, 1, 1, '', '{"1":"characters","2":"sets","3":"props","4":"textures","5":"matte"}', '["characters","props","sets","vehicles","terrains"]', '["refs","bank","stills"]', '["refs","bank","stills"]', '{"0":"WIP","1":"On Hold","2":"VALID"}', '["blender","Zbrush","AfterEffect","Maya","Ardour"]', '', '["director","scenarist","cameraman","lightTech","soundTech","artist2D","artist3D","editor","coder","VFX","soundMix"]', '', '{"action":{"1":["Lieu",""],"2":["Int-Ext",""],"3":["day-night",""],"4":["Action",""]},"camera":{"1":["Valeur_Plan",""],"2":["Mvt_Cam",""],"3":["Angle",""],"4":["Raccord",""]},"son":{"1":["Son_Amb",""],"2":["Dialogue",""],"3":["In-Off",""],"4":["Musique",""]}}', 0, 0, 0, '', 0),
(4, '0.4', '0.3', 'fr', 'dark', '16|18|23.97|24|25|30|60|100', 25, '16:9|4:3', 'd/m/Y', 'http://saamanager.net', 4, 'datas/calendar/events.json', '["layout"]', '["demo","short","feature","commercial","effects","game","web","test"]', 'seq_##', 'shot_###', '', 1, 1, 1, '', '{"1":"characters","2":"sets","3":"props","4":"textures","5":"matte"}', '["characters","props","sets","vehicles","terrains"]', '["refs","bank","stills"]', '["refs","bank","stills"]', '{"0":"WIP","1":"On Hold","2":"VALID"}', '["blender","Zbrush","AfterEffect","Maya","Ardour"]', '', '["director","scenarist","cameraman","lightTech","soundTech","artist2D","artist3D","editor","coder","VFX","soundMix"]', '', '{"action":{"1":["Lieu",""],"2":["Int-Ext",""],"3":["day-night",""],"4":["Action",""]},"camera":{"1":["Valeur_Plan",""],"2":["Mvt_Cam",""],"3":["Angle",""],"4":["Raccord",""]},"son":{"1":["Son_Amb",""],"2":["Dialogue",""],"3":["In-Off",""],"4":["Musique",""]}}', 0, 0, 0, '', 0),
(5, '0.41', '0.4', 'fr', 'dark', '16|18|23.97|24|25|30|60|100', 25, '16:9|4:3', 'd/m/Y', 'http://saamanager.net', 4, 'datas/calendar/events.json', '["layout"]', '["demo","short","feature","commercial","effects","game","web","test"]', 'seq_##', 'shot_###', '', 1, 1, 1, '', '{"1":"characters","2":"sets","3":"props","4":"textures","5":"matte"}', '["characters","props","sets","vehicles","terrains"]', '["refs","bank","stills"]', '["refs","bank","stills"]', '{"0":"WIP","1":"On Hold","2":"VALID"}', '["blender","Zbrush","AfterEffect","Maya","Ardour"]', '', '["director","scenarist","cameraman","lightTech","soundTech","artist2D","artist3D","editor","coder","VFX","soundMix"]', '', '{"action":{"1":["Lieu",""],"2":["Int-Ext",""],"3":["day-night",""],"4":["Action",""]},"camera":{"1":["Valeur_Plan",""],"2":["Mvt_Cam",""],"3":["Angle",""],"4":["Raccord",""]},"son":{"1":["Son_Amb",""],"2":["Dialogue",""],"3":["In-Off",""],"4":["Musique",""]}}', 0, 0, 0, '', 0),
(6, '0.45', '0.41', 'fr', 'dark', '16|18|23.97|24|25|30|60|100', 25, '16:9|4:3', 'd/m/Y', 'http://saamanager.net', 4, 'datas/calendar/events.json', '["layout"]', '["demo","short","feature","commercial","effects","game","web","test"]', 'seq_##', 'shot_###', '', 1, 1, 1, '["Need review","Stable"]', '{"1":"characters","2":"sets","3":"props","4":"textures","5":"matte"}', '["characters","props","sets","vehicles","terrains"]', '["refs","bank","stills"]', '["refs","bank","stills"]', '{"0":"WIP","1":"On Hold","2":"VALID"}', '["blender","Zbrush","AfterEffect","Maya","Ardour"]', '', '["director","scenarist","cameraman","lightTech","soundTech","artist2D","artist3D","editor","coder","VFX","soundMix"]', '', '{"action":{"1":["Lieu",""],"2":["Int-Ext",""],"3":["day-night",""],"4":["Action",""]},"camera":{"1":["Valeur_Plan",""],"2":["Mvt_Cam",""],"3":["Angle",""],"4":["Raccord",""]},"son":{"1":["Son_Amb",""],"2":["Dialogue",""],"3":["In-Off",""],"4":["Musique",""]}}', 0, 0, 0, '', 0),
(7, '0.5', '0.45', 'fr', 'dark', '16|18|23.97|24|25|30|60|100', 25, '16:9|4:3', 'd/m/Y', 'http://saamanager.net', 4, 'datas/calendar/events.json', '["layout"]', '["demo","short","feature","commercial","effects","game","web","test"]', 'seq_##', 'shot_###', '', 1, 1, 1, '["Need review","Stable"]', '{"1":"characters","2":"sets","3":"props","4":"textures","5":"matte"}', '["characters","props","sets","vehicles","terrains"]', '["refs","bank","stills"]', '["refs","bank","stills"]', '{"0":"WIP","1":"On Hold","2":"VALID"}', '["blender","Zbrush","AfterEffect","Maya","Ardour"]', '', '["director","scenarist","cameraman","lightTech","soundTech","artist2D","artist3D","editor","coder","VFX","soundMix"]', '', '{"action":{"1":["Lieu",""],"2":["Int-Ext",""],"3":["day-night",""],"4":["Action",""]},"camera":{"1":["Valeur_Plan",""],"2":["Mvt_Cam",""],"3":["Angle",""],"4":["Raccord",""]},"son":{"1":["Son_Amb",""],"2":["Dialogue",""],"3":["In-Off",""],"4":["Musique",""]}}', 0, 0, 0, '', 0),
(8, '0.6', '0.5', 'fr', 'dark', '16|18|23.97|24|25|30|60|100', 25, '16:9|4:3', 'd/m/Y', 'http://saamanager.net', 4, 'datas/calendar/events.json', '["1","8"]', '["demo","short","feature","commercial","effects","game","web","test"]', 'SEQ', 'SHOT', '', 1, 1, 1, '["Need review","Stable"]', '{"1":"characters","2":"sets","3":"props","4":"textures","5":"matte"}', '["characters","props","sets","vehicles","terrains"]', '["refs","bank","stills"]', '["refs","bank","stills"]', '{"0":"WIP","1":"On Hold","2":"VALID"}', '["blender","Zbrush","AfterEffect","Maya","Ardour"]', '', '["producer","assistantProd","director","scenarist","cameraman","lightTech","soundTech","artist2D","artist3D","editor","coder","VFX","soundMix"]', '', '{"action":{"1":["Lieu",""],"2":["Int-Ext",""],"3":["day-night",""],"4":["Action",""]},"camera":{"1":["Valeur_Plan",""],"2":["Mvt_Cam",""],"3":["Angle",""],"4":["Raccord",""]},"son":{"1":["Son_Amb",""],"2":["Dialogue",""],"3":["In-Off",""],"4":["Musique",""]}}', 0, 8, 0, '', 0),
(9, '0.7', '0.6', 'fr', 'dark', '16|18|23.97|24|25|30|60|100', 25, '16:9|4:3', 'd/m/Y', 'http://saamanager.net', 4, 'datas/calendar/events.json', '["1","8"]', '["demo","short","feature","commercial","effects","game","web","test"]', 'SEQ', 'SHOT', '', 1, 1, 1, '["Need review","Stable"]', '{"1":"characters","2":"sets","3":"props","4":"textures","5":"matte"}', '["characters","props","sets","vehicles","terrains"]', '["refs","bank","stills"]', '["refs","bank","stills"]', '["--","WIP","OnHold","VALID","Disabled"]', '["blender","Zbrush","AfterEffect","Maya","Ardour"]', '', '["producer","assistantProd","director","scenarist","cameraman","lightTech","soundTech","artist2D","artist3D","editor","coder","VFX","soundMix"]', '{"1":"visitor","2":"demo","3":"artist","4":"lead","5":"supervisor","6":"prod.dir.","7":"magic","8":"dev","9":"root"}', '{"action":{"1":["Lieu",""],"2":["Int-Ext",""],"3":["day-night",""],"4":["Action",""]},"camera":{"1":["Valeur_Plan",""],"2":["Mvt_Cam",""],"3":["Angle",""],"4":["Raccord",""]},"son":{"1":["Son_Amb",""],"2":["Dialogue",""],"3":["In-Off",""],"4":["Musique",""]}}', 0, 8, 0, '', 0),
(10, '0.8', '0.7', 'fr', 'dark', '16|18|23.97|24|25|30|60|100', 25, '16:9|4:3', 'd/m/Y', 'http://saamanager.net', 4, 'datas/calendar/events.json', '["1","8"]', '["demo","short","feature","commercial","effects","game","web","test"]', 'SEQ', 'SHOT', '', 1, 1, 1, '["Need review","Stable"]', '{"1":"characters","2":"sets","3":"props","4":"textures","5":"matte"}', '["characters","props","sets","vehicles","terrains"]', '["refs","bank","stills"]', '["refs","bank","stills"]', '["--","WIP","OnHold","VALID","Disabled"]', '["blender","Zbrush","AfterEffect","Maya","Ardour"]', '', '["producer","assistantProd","director","scenarist","cameraman","lightTech","soundTech","artist2D","artist3D","editor","coder","VFX","soundMix"]', '{"1":"visitor","2":"demo","3":"artist","4":"lead","5":"supervisor","6":"prod.dir.","7":"magic","8":"dev","9":"root"}', '{"action":{"1":["Lieu",""],"2":["Int-Ext",""],"3":["day-night",""],"4":["Action",""]},"camera":{"1":["Valeur_Plan",""],"2":["Mvt_Cam",""],"3":["Angle",""],"4":["Raccord",""]},"son":{"1":["Son_Amb",""],"2":["Dialogue",""],"3":["In-Off",""],"4":["Musique",""]}}', 0, 8, 0, '', 0),
(11, '0.9', '0.8', 'fr', 'dark', '16|18|23.97|24|25|30|60|100', 25, '16:9|4:3', 'd/m/Y', 'http://saamanager.net', 4, 'datas/calendar/events.json', '["1","9","12"]', '["demo","short","feature","commercial","effects","game","web","test"]', 'SEQ', 'SHOT', 'M_SC', 1, 1, 1, '["Need review","Stable"]', '{"1":"characters","2":"sets","3":"props","4":"textures","5":"matte"}', '["characters","props","sets","vehicles","terrains"]', '["refs","bank","stills"]', '["refs","bank","stills"]', '["--","WIP","OnHold","VALID","Disabled"]', '["blender","Zbrush","AfterEffect","Maya","Ardour"]', '', '["producer","assistantProd","director","scenarist","cameraman","lightTech","soundTech","artist2D","artist3D","editor","coder","VFX","soundMix"]', '{"1":"visitor","2":"demo","3":"artist","4":"lead","5":"supervisor","6":"prod.dir.","7":"magic","8":"dev","9":"root"}', '{"action":{"1":["Lieu",""],"2":["Int-Ext",""],"3":["day-night",""],"4":["Action",""]},"camera":{"1":["Valeur_Plan",""],"2":["Mvt_Cam",""],"3":["Angle",""],"4":["Raccord",""]},"son":{"1":["Son_Amb",""],"2":["Dialogue",""],"3":["In-Off",""],"4":["Musique",""]}}', 0, 8, 0, '', 0),
(12, '0.99', '0.9', 'fr', 'dark', '16|18|23.97|24|25|30|60|100', 25, '16:9|4:3', 'd/m/Y', 'http://saamanager.net', 4, 'datas/calendar/events.json', '["1","9","12"]', '["demo","short","feature","commercial","effects","game","web","test"]', 'SEQ', 'SHOT', 'M_SC', 1, 1, 1, '["Need review","Stable"]', '{"1":"characters","2":"sets","3":"props","4":"textures","5":"matte"}', '["characters","props","sets","vehicles","terrains"]', '["refs","bank","stills"]', '["refs","bank","stills"]', '["--","WIP","OnHold","VALID","Disabled"]', '["blender","Zbrush","AfterEffect","Maya","Ardour"]', '["blend","ma","jpg","png","wav","aif","psd","ae","obj"]', '["producer","assistantProd","director","scenarist","cameraman","lightTech","soundTech","artist2D","artist3D","editor","coder","VFX","soundMix"]', '{"1":"visitor","2":"demo","3":"artist","4":"lead","5":"supervisor","6":"prod.dir.","7":"magic","8":"dev","9":"root"}', '{"action":{"1":["Lieu",""],"2":["Int-Ext",""],"3":["day-night",""],"4":["Action",""]},"camera":{"1":["Valeur_Plan",""],"2":["Mvt_Cam",""],"3":["Angle",""],"4":["Raccord",""]},"son":{"1":["Son_Amb",""],"2":["Dialogue",""],"3":["In-Off",""],"4":["Musique",""]}}', 0, 8, 0, '', 0),
(13, '1.0', '0.99', 'fr', 'dark', '16|18|23.97|24|25|30|60|100', 25, '16:9|4:3', 'd/m/Y', 'http://saamanager.net', 4, 'datas/calendar/events.json', '["1","9","12"]', '["demo","short","feature","commercial","effects","game","web","test"]', 'SEQ', 'SHOT', 'M_SC', 1, 1, 1, '["Need review","Stable"]', '{"1":"characters","2":"sets","3":"props","4":"textures","5":"matte"}', '["characters","props","sets","vehicles","terrains"]', '["refs","bank","stills"]', '["refs","bank","stills"]', '["--","WIP","OnHold","VALID","Disabled"]', '["blender","Zbrush","AfterEffect","Maya","Ardour"]', '["blend","ma","jpg","png","wav","aif","psd","ae","obj"]', '["producer","assistantProd","director","scenarist","cameraman","lightTech","soundTech","artist2D","artist3D","editor","coder","VFX","soundMix"]', '{"1":"visitor","2":"demo","3":"artist","4":"lead","5":"supervisor","6":"prod.dir.","7":"magic","8":"dev","9":"root"}', '{"action":{"1":["Lieu",""],"2":["Int-Ext",""],"3":["day-night",""],"4":["Action",""]},"camera":{"1":["Valeur_Plan",""],"2":["Mvt_Cam",""],"3":["Angle",""],"4":["Raccord",""]},"son":{"1":["Son_Amb",""],"2":["Dialogue",""],"3":["In-Off",""],"4":["Musique",""]}}', 0, 8, 0, '', 0),
(14, '1.1', '1.0', 'fr', 'dark', '16|18|23.97|24|25|30|60|100', 25, '16:9|4:3', 'd/m/Y', 'http://saamanager.net', 4, 'datas/calendar/events.json', '["1","9","12"]', '["demo","short","feature","commercial","effects","game","web","test"]', 'SEQ', 'SHOT', 'M_SC', 1, 1, 1, '["Need review","Stable"]', '{"1":"characters","2":"sets","3":"props","4":"textures","5":"matte"}', '["characters","props","sets","vehicles","terrains"]', '["refs","bank","stills"]', '["refs","bank","stills"]', '["TODO","WIP","OnHold","VALID","Disabled"]', '["blender","Zbrush","AfterEffect","Maya","Ardour"]', '["blend","ma","jpg","png","wav","aif","psd","ae","obj","may"]', '["producer","assistantProd","director","scenarist","cameraman","lightTech","soundTech","artist2D","artist3D","editor","coder","VFX","soundMix"]', '{"1":"visitor","2":"demo","3":"artist","4":"lead","5":"supervisor","6":"prod.dir.","7":"magic","8":"dev","9":"root"}', '{"action":{"1":["Lieu",""],"2":["Int-Ext",""],"3":["day-night",""],"4":["Action",""]},"camera":{"1":["Valeur_Plan",""],"2":["Mvt_Cam",""],"3":["Angle",""],"4":["Raccord",""]},"son":{"1":["Son_Amb",""],"2":["Dialogue",""],"3":["In-Off",""],"4":["Musique",""]}}', 0, 8, 0.8, '{"chat":true,"drawtool":true}', 1),
(15, '1.2', '1.1', 'fr', 'dark', '16|18|23.97|24|25|30|60|100', 25, '16:9|4:3', 'd/m/Y', 'http://saamanager.net', 4, 'datas/calendar/events.json', '["1","9","12"]', '["demo","short","feature","commercial","effects","game","web","test"]', 'SEQ', 'SHOT', 'M_SC', 1, 1, 1, '["Need review","Stable"]', '{"1":"characters","2":"sets","3":"props","4":"textures","5":"matte"}', '["characters","props","sets","vehicles","terrains"]', '["refs","bank","stills"]', '["refs","bank","stills"]', '["TODO","WIP","OnHold","VALID","Disabled"]', '["blender","Zbrush","AfterEffect","Maya","Ardour"]', '["blend","ma","jpg","png","wav","aif","psd","ae","obj","may"]', '["producer","assistantProd","director","scenarist","cameraman","lightTech","soundTech","artist2D","artist3D","editor","coder","VFX","soundMix"]', '{"1":"visitor","2":"demo","3":"artist","4":"lead","5":"supervisor","6":"prod.dir.","7":"magic","8":"dev","9":"root"}', '{"action":{"1":["Lieu",""],"2":["Int-Ext",""],"3":["day-night",""],"4":["Action",""]},"camera":{"1":["Valeur_Plan",""],"2":["Mvt_Cam",""],"3":["Angle",""],"4":["Raccord",""]},"son":{"1":["Son_Amb",""],"2":["Dialogue",""],"3":["In-Off",""],"4":["Musique",""]}}', 0, 8, 0.8, '{"chat":true,"drawtool":true}', 1);

-- --------------------------------------------------------

--
-- Structure de la table `saam_jchat`
--

DROP TABLE IF EXISTS `saam_jchat`;
CREATE TABLE IF NOT EXISTS `saam_jchat` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `from` int(5) NOT NULL,
  `to` int(5) NOT NULL,
  `message` text NOT NULL,
  `sent` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `recd` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `to` (`to`),
  KEY `from` (`from`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `saam_news`
--

DROP TABLE IF EXISTS `saam_news`;
CREATE TABLE IF NOT EXISTS `saam_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ID_creator` int(4) NOT NULL,
  `visible` tinyint(1) NOT NULL,
  `new_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `new_title` varchar(255) NOT NULL,
  `new_text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `saam_news`
--


INSERT INTO `saam_news` (`id`, `ID_creator`, `visible`, `new_date`, `new_title`, `new_text`) VALUES
(1, 1, 1, '2030-01-01 15:12:36', 'IMPORTANT', '<p>\nWelcome on SaAM. You can type "H" key to get help. Start exploring the DEMO project !\n</p>');

-- --------------------------------------------------------

--
-- Structure de la table `saam_notes`
--

DROP TABLE IF EXISTS `saam_notes`;
CREATE TABLE IF NOT EXISTS `saam_notes` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `position` int(9) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ID_user` int(5) NOT NULL,
  `note` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------


--
-- Structure de la table `saam_prod_custom`
--

DROP TABLE IF EXISTS `saam_prod_custom`;
CREATE TABLE IF NOT EXISTS `saam_prod_custom` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `ID_project` int(6) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `saam_prod_custom`
--

INSERT INTO `saam_prod_custom` (`id`, `ID_project`, `deleted`) VALUES
(0, 0, 0);
UPDATE `saam_prod_custom` SET `id` = '0' WHERE `saam_prod_custom`.`id` = 1;

-- --------------------------------------------------------


--
-- Structure de la table `saam_projects`
--

DROP TABLE IF EXISTS `saam_projects`;
CREATE TABLE IF NOT EXISTS `saam_projects` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `ID_creator` int(4) NOT NULL,
  `fps` float NOT NULL,
  `nomenclature` text NOT NULL,
  `project_type` varchar(255) NOT NULL,
  `dpts` text NOT NULL,
  `position` int(5) NOT NULL,
  `supervisor` varchar(256) NOT NULL,
  `title` varchar(256) NOT NULL,
  `description` text NOT NULL,
  `director` varchar(255) NOT NULL,
  `equipe` text NOT NULL,
  `company` varchar(256) NOT NULL,
  `date` datetime NOT NULL,
  `update` date NOT NULL,
  `updated_by` int(6) NOT NULL,
  `deadline` datetime NOT NULL,
  `progress` int(3) NOT NULL,
  `demo` tinyint(1) NOT NULL DEFAULT '0',
  `hide` tinyint(1) NOT NULL,
  `lock` tinyint(1) NOT NULL,
  `archive` tinyint(1) NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  `reference` varchar(64) NOT NULL,
  `softwares` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`),
  KEY `demo` (`demo`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Contenu de la table `saam_projects`
--

INSERT INTO `saam_projects` (`id`, `ID_creator`, `fps`, `nomenclature`, `project_type`, `dpts`, `position`, `supervisor`, `title`, `description`, `director`, `equipe`, `company`, `date`, `update`, `updated_by`, `deadline`, `progress`, `demo`, `hide`, `lock`, `archive`, `deleted`, `reference`, `softwares`) VALUES
(1, 0, 25, 'SEQ###_SHOT###', 'demo', '[1,2,3,4,5,6,7]', 1, 'Demo', 'DEMO', 'Documentaire sur les koalas en capture de mouvement ... ', 'Melville', '[1,2,3]', 'LRDS', '2012-07-11 00:00:00', '2013-02-20', 1, '2015-07-30 00:00:00', 22, 1, 0, 0, 0, 0, 'REF_DEMO', '["blender","AfterEffect","Maya","Ardour"]');

-- --------------------------------------------------------

--
-- Structure de la table `saam_relations`
--

DROP TABLE IF EXISTS `saam_relations`;
CREATE TABLE IF NOT EXISTS `saam_relations` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `master_table` varchar(64) NOT NULL,
  `master_column` varchar(64) NOT NULL,
  `link_type` varchar(64) DEFAULT NULL,
  `link_table` varchar(64) NOT NULL,
  `link_column` varchar(64) NOT NULL,
  `link_default_return` varchar(64) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=58 ;

--
-- Contenu de la table `saam_relations`
--

INSERT INTO `saam_relations` (`id`, `master_table`, `master_column`, `link_type`, `link_table`, `link_column`, `link_default_return`) VALUES
(1, 'saam_assets', 'ID_creator', 'direct', 'saam_users', 'id', 'pseudo'),
(2, 'saam_assets', 'ID_handler', 'direct', 'saam_users', 'id', 'pseudo'),
(3, 'saam_assets', 'updated_by', 'direct', 'saam_users', 'id', 'pseudo'),
(4, 'saam_comments_asset', 'ID_asset', 'direct', 'saam_assets', 'id', 'filename'),
(5, 'saam_comments_asset', 'response_to', 'direct', 'saam_comments_asset', 'id', 'id'),
(6, 'saam_comments_shots', 'ID_shot', 'direct', 'saam_shots', 'id', 'title'),
(7, 'saam_comments_shots', 'response_to', 'direct', 'saam_comments_shots', 'id', 'id'),
(8, 'saam_shots_depts_infos', 'ID_shot', 'direct', 'saam_shots', 'id', 'title'),
(9, 'saam_shots_depts_infos', 'ID_project', 'direct', 'saam_projects', 'id', 'title'),
(10, 'saam_users', 'ID_creator', 'direct', 'saam_users', 'id', 'pseudo'),
(11, 'saam_news', 'ID_creator', 'direct', 'saam_users', 'id', 'pseudo'),
(12, 'saam_notes', 'ID_user', 'direct', 'saam_users', 'id', 'pseudo'),
(13, 'saam_projects', 'ID_creator', 'direct', 'saam_users', 'id', 'pseudo'),
(14, 'saam_projects', 'updated_by', 'direct', 'saam_users', 'id', 'pseudo'),
(15, 'saam_sequences', 'ID_creator', 'direct', 'saam_users', 'id', 'pseudo'),
(16, 'saam_sequences', 'ID_project', 'direct', 'saam_projects', 'id', 'title'),
(17, 'saam_sequences', 'updated_by', 'direct', 'saam_users', 'id', 'pseudo'),
(18, 'saam_shots', 'ID_creator', 'direct', 'saam_users', 'id', 'pseudo'),
(19, 'saam_shots', 'ID_project', 'direct', 'saam_projects', 'id', 'title'),
(20, 'saam_shots', 'ID_sequence', 'direct', 'saam_sequences', 'id', 'title'),
(21, 'saam_shots', 'supervisor', 'direct', 'saam_users', 'id', 'pseudo'),
(22, 'saam_shots', 'lead', 'direct', 'saam_users', 'id', 'pseudo'),
(23, 'saam_shots', 'equipe', 'json>val', 'saam_users', 'id', 'pseudo'),
(24, 'saam_assets', 'category', 'val>json', 'saam_config', 'assets_categories', 'assets_categories'),
(25, 'saam_assets', 'ID_shots', 'json>val', 'saam_shots', 'id', 'title'),
(26, 'saam_assets', 'team', 'json>val', 'saam_users', 'id', 'pseudo'),
(27, 'saam_assets', 'relations_assets', 'json>val', 'saam_assets', 'filename', 'filename'),
(28, 'saam_shots', 'tags', 'json>json', 'saam_config', 'global_tags', 'global_tags'),
(29, 'saam_shots', 'updated_by', 'direct', 'saam_users', 'id', 'pseudo'),
(30, 'saam_sequences', 'supervisor', 'direct', 'saam_users', 'id', 'pseudo'),
(31, 'saam_users', 'status', 'val>json', 'saam_config', 'user_status', 'user_status'),
(32, 'saam_users', 'competences', 'json>json', 'saam_config', 'available_competences', 'available_competences'),
(33, 'saam_users', 'status', 'val>json', 'saam_config', 'user_status', 'user_status'),
(34, 'saam_scenes', 'assets', 'json>val', 'saam_assets', 'id', 'filename'),
(35, 'saam_scenes', 'master', 'direct', 'saam_scenes', 'id', 'title'),
(36, 'saam_scenes', 'derivatives', 'json>val', 'saam_scenes', 'id', 'title'),
(37, 'saam_scenes', 'ID_handler', 'direct', 'saam_users', 'id', 'pseudo'),
(38, 'saam_scenes', 'ID_creator', 'direct', 'saam_users', 'id', 'pseudo'),
(39, 'saam_scenes', 'supervisor', 'direct', 'saam_users', 'id', 'pseudo'),
(40, 'saam_scenes', 'lead', 'direct', 'saam_users', 'id', 'pseudo'),
(41, 'saam_scenes', 'equipe', 'json>val', 'saam_users', 'id', 'pseudo'),
(42, 'saam_scenes', 'updated_by', 'json>val', 'saam_users', 'id', 'pseudo'),
(43, 'saam_scenes', 'sequences', 'json>val', 'saam_sequences', 'id', 'title'),
(44, 'saam_scenes', 'shots', 'json>val', 'saam_shots', 'id', 'title'),
(45, 'saam_shots', 'ID_scene', 'direct', 'saam_scenes', 'id', 'title'),
(46, 'saam_cameras', 'ID_project', 'direct', 'saam_projects', 'id', 'title'),
(47, 'saam_cameras', 'ID_scene', 'direct', 'saam_scenes', 'id', 'title'),
(48, 'saam_cameras', 'ID_sequence', 'direct', 'saam_sequences', 'id', 'title'),
(49, 'saam_cameras', 'ID_shot', 'direct', 'saam_shots', 'id', 'title'),
(50, 'saam_cameras', 'ID_creator', 'direct', 'saam_users', 'id', 'pseudo'),
(51, 'saam_cameras', 'updated_by', 'direct', 'saam_users', 'id', 'pseudo'),
(52, 'saam_tasks', 'ID_creator', 'direct', 'saam_users', 'id', 'pseudo'),
(53, 'saam_tasks', 'assigned_to', 'json>val', 'saam_users', 'id', 'pseudo'),
(54, 'saam_tasks', 'status', 'val>json', 'saam_config', 'default_status', 'default_status'),
(55, 'saam_tasks', 'ID_project', 'direct', 'saam_projects', 'id', 'title'),
(56, 'saam_dailies', 'user', 'direct', 'saam_users', 'id', 'pseudo'),
(57, 'saam_dailies', 'ID_project', 'direct', 'saam_projects', 'id', 'title');


-- --------------------------------------------------------

--
-- Structure de la table `saam_scenes`
--

DROP TABLE IF EXISTS `saam_scenes`;
CREATE TABLE IF NOT EXISTS `saam_scenes` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `label` varchar(256) NOT NULL,
  `title` varchar(256) NOT NULL,
  `ID_creator` int(6) NOT NULL,
  `ID_handler` int(6) NOT NULL,
  `ID_project` int(4) NOT NULL,
  `sequences` text NOT NULL,
  `shots` text NOT NULL,
  `derivatives` text NOT NULL,
  `master` int(6) NOT NULL,
  `assets` text NOT NULL,
  `nb_frames` int(6) NOT NULL,
  `fps` int(3) NOT NULL,
  `description` text NOT NULL,
  `supervisor` int(6) NOT NULL,
  `lead` int(6) NOT NULL,
  `equipe` text NOT NULL,
  `date` datetime NOT NULL,
  `update` datetime NOT NULL,
  `deadline` datetime NOT NULL,
  `updated_by` int(6) NOT NULL,
  `tags` text NOT NULL,
  `progress` int(3) NOT NULL,
  `hide` tinyint(1) NOT NULL,
  `lock` tinyint(1) NOT NULL,
  `archive` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ID_project` (`ID_project`),
  KEY `label` (`label`),
  KEY `title` (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



-- --------------------------------------------------------

--
-- Structure de la table `saam_scenes_depts_infos`
--

DROP TABLE IF EXISTS `saam_scenes_depts_infos`;
CREATE TABLE IF NOT EXISTS `saam_scenes_depts_infos` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `ID_project` int(4) NOT NULL,
  `ID_scene` int(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



-- --------------------------------------------------------

--
-- Structure de la table `saam_sequences`
--

DROP TABLE IF EXISTS `saam_sequences`;
CREATE TABLE IF NOT EXISTS `saam_sequences` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `label` varchar(256) NOT NULL,
  `title` varchar(256) NOT NULL,
  `description` text NOT NULL,
  `ID_creator` int(4) NOT NULL,
  `ID_project` int(5) NOT NULL,
  `date` datetime NOT NULL,
  `deadline` datetime NOT NULL,
  `supervisor` varchar(256) NOT NULL,
  `lead` varchar(256) NOT NULL,
  `position` int(9) NOT NULL,
  `reference` varchar(256) NOT NULL,
  `update` date NOT NULL,
  `updated_by` int(6) NOT NULL,
  `progress` int(3) NOT NULL,
  `hide` tinyint(1) NOT NULL,
  `lock` tinyint(1) NOT NULL,
  `archive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ID_project` (`ID_project`),
  KEY `label` (`label`),
  KEY `title` (`title`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Contenu de la table `saam_sequences`
--

INSERT INTO `saam_sequences` (`id`, `ID_creator`, `ID_project`, `date`, `deadline`, `supervisor`, `lead`, `position`, `title`, `reference`, `label`, `description`, `update`, `updated_by`, `progress`, `hide`, `lock`, `archive`) VALUES
(1, 4, 1, '2013-07-10 00:00:00', '2013-07-25 00:00:00', '', 'Karlova,Polo', 1, 'SEQ001', '', 'SEQ001', 'Présentation du koala', '2013-08-06', 1, 0, 0, 0, 0),
(2, 4, 1, '2013-07-10 00:00:00', '2013-08-22 00:00:00', '', 'Karlova,Polo', 2, 'SEQ002', '', 'SEQ002', 'Le Koala en langue Aborigène veut dire: ''Qui  ne boit jamais''', '2013-08-06', 1, 0, 0, 0, 0),
(3, 4, 1, '2013-07-10 00:00:00', '2013-10-24 00:00:00', '', 'Karlova,Polo', 3, 'SEQ003', '', 'SEQ003', 'Le Koala aime dormir, et ne se déplace que rarement dans l''année ... il faut pas ewagéré non plus !!!', '2013-08-06', 1, 0, 0, 0, 0),
(4, 4, 1, '2013-07-10 00:00:00', '2013-05-14 00:00:00', '', 'Karlova,Polo', 4, 'SEQ004', '', 'SEQ004', 'Le Koala ne veut pas qu''on coupe ses poils', '2013-08-08', 1, 0, 0, 0, 0),
(5, 4, 1, '2013-07-10 00:00:00', '2013-11-21 00:00:00', '', 'Karlova,Polo', 5, 'SEQ005', '', 'SEQ005', 'Le Koala doit boire un jour. Et là ...', '2013-08-08', 1, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Structure de la table `saam_shots`
--

DROP TABLE IF EXISTS `saam_shots`;
CREATE TABLE IF NOT EXISTS `saam_shots` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `ID_sequence` int(9) NOT NULL,
  `title` varchar(256) NOT NULL,
  `label` varchar(256) NOT NULL,
  `ID_creator` int(4) NOT NULL,
  `ID_project` int(5) NOT NULL,
  `position` int(12) NOT NULL,
  `nbframes` int(8) NOT NULL,
  `description` text NOT NULL,
  `supervisor` int(4) NOT NULL,
  `lead` int(4) NOT NULL,
  `equipe` varchar(256) NOT NULL,
  `date` datetime NOT NULL,
  `update` datetime NOT NULL,
  `updated_by` int(6) NOT NULL,
  `deadline` datetime NOT NULL,
  `hide` tinyint(1) NOT NULL,
  `lock` tinyint(1) NOT NULL,
  `archive` tinyint(1) NOT NULL DEFAULT '0',
  `progress` int(3) NOT NULL,
  `reference` varchar(50) NOT NULL,
  `tags` text NOT NULL,
  `fps` int(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ID_sequence` (`ID_sequence`),
  KEY `label` (`label`),
  KEY `title` (`title`),
  KEY `ID_project` (`ID_project`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=21 ;

--
-- Contenu de la table `saam_shots`
--

INSERT INTO `saam_shots` (`id`, `ID_creator`, `ID_project`, `ID_sequence`, `position`, `nbframes`, `label`, `title`, `description`, `supervisor`, `lead`, `equipe`, `date`, `update`, `updated_by`, `deadline`, `hide`, `lock`, `archive`, `progress`, `reference`, `tags`, `fps`) VALUES
(6, 4, 1, 1, 6, 0, 'SHOT006', 'last one', '', '', '', '', '2013-08-01 00:00:00', '2013-08-12 00:00:00', 1, '2013-12-12 00:00:00', 0, 0, 0, 0, '', '', 0),
(5, 4, 1, 1, 5, 0, 'SHOT005', 'fifth shot', '', '', '', '', '2013-08-01 00:00:00', '2013-08-15 00:00:00', 1, '2013-10-31 00:00:00', 0, 0, 0, 0, '', '', 0),
(4, 4, 1, 1, 4, 0, 'SHOT004', 'test shot 4', '', '', '', '', '2013-08-13 00:00:00', '2013-08-15 00:00:00', 1, '2013-08-31 00:00:00', 0, 0, 0, 0, '', '', 25),
(3, 4, 1, 1, 3, 0, 'SHOT003', 'blabla', '', '', '', '', '2013-08-05 00:00:00', '2013-08-13 00:00:00', 1, '2013-10-13 00:00:00', 0, 0, 0, 0, '', '', 0),
(2, 4, 1, 1, 2, 2500, 'SHOT002', 'The shot', '', '', '', '', '2013-08-03 00:00:00', '2013-08-18 00:00:00', 1, '2013-08-29 00:00:00', 0, 0, 0, 0, '', '', 0),
(7, 4, 1, 2, 1, 0, 'SHOT001', '', '', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, '0000-00-00 00:00:00', 0, 0, 0, 0, '', '', 0),
(11, 4, 1, 2, 5, 0, 'SHOT005', '', '', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, '0000-00-00 00:00:00', 0, 0, 0, 0, '', '', 0),
(10, 4, 1, 2, 4, 0, 'SHOT004', '', '', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, '0000-00-00 00:00:00', 0, 0, 0, 0, '', '', 0),
(9, 4, 1, 2, 3, 0, 'SHOT003', '', '', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, '0000-00-00 00:00:00', 0, 0, 0, 0, '', '', 0),
(8, 4, 1, 2, 2, 0, 'SHOT002', '', '', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, '0000-00-00 00:00:00', 0, 0, 0, 0, '', '', 0),
(12, 4, 1, 3, 1, 0, 'SHOT001', '', '', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, '0000-00-00 00:00:00', 0, 0, 0, 0, '', '', 0),
(15, 4, 1, 3, 4, 0, 'SHOT004', '', '', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, '0000-00-00 00:00:00', 0, 0, 0, 0, '', '', 0),
(14, 4, 1, 3, 3, 0, 'SHOT003', '', '', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, '0000-00-00 00:00:00', 0, 0, 0, 0, '', '', 0),
(13, 4, 1, 3, 2, 0, 'SHOT002', 'gniuk gniuk', '', '', '', '', '2013-08-01 00:00:00', '2013-08-19 00:00:00', 1, '2013-08-31 00:00:00', 0, 0, 0, 0, '', '', 0),
(1, 4, 1, 1, 1, 9874, 'SHOT001', 'Plan 001', 'FX Koala Furs to Koala Mécha', '4', '2', '["Karlova","Polo"]', '2013-08-01 00:00:00', '2013-08-17 00:00:00', 1, '2013-05-15 00:00:00', 0, 0, 0, 0, '', '', 25),
(16, 4, 1, 4, 1, 0, 'SHOT001', 'title SHOT001', '', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, '0000-00-00 00:00:00', 0, 0, 0, 0, '', '', 0),
(17, 4, 1, 4, 2, 0, 'SHOT002', 'title SHOT002', '', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, '0000-00-00 00:00:00', 0, 0, 0, 0, '', '', 0),
(18, 4, 1, 4, 3, 0, 'SHOT003', 'title SHOT003', '', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, '0000-00-00 00:00:00', 0, 0, 0, 0, '', '', 0),
(19, 4, 1, 5, 1, 1500, 'SHOT001', 'title SHOT001', '', '', '', '', '2013-08-01 00:00:00', '2013-08-18 00:00:00', 1, '2014-08-30 00:00:00', 0, 0, 0, 0, '', '', 0),
(20, 4, 1, 5, 2, 0, 'SHOT002', 'title SHOT002', '', '', '', '', '2013-08-01 00:00:00', '2013-08-15 00:00:00', 1, '2013-05-30 00:00:00', 0, 0, 0, 0, '', '', 0);

-- --------------------------------------------------------

--
-- Structure de la table `saam_shots_depts_infos`
--

DROP TABLE IF EXISTS `saam_shots_depts_infos`;
CREATE TABLE IF NOT EXISTS `saam_shots_depts_infos` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `ID_project` int(6) NOT NULL,
  `ID_shot` int(6) NOT NULL,
  `scenario` text NOT NULL,
  `dectech` text NOT NULL,
  `storyboard` text NOT NULL,
  `sound` text NOT NULL,
  `1` varchar(256) NOT NULL,
  `2` varchar(256) NOT NULL,
  `3` varchar(256) NOT NULL,
  `4` varchar(256) NOT NULL,
  `5` varchar(256) NOT NULL,
  `6` varchar(256) NOT NULL,
  `7` varchar(256) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ID_shot` (`ID_shot`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Contenu de la table `saam_shots_depts_infos`
--

INSERT INTO `saam_shots_depts_infos` (`id`, `ID_project`, `ID_shot`, `scenario`, `dectech`, `storyboard`, `sound`, `1`, `2`, `3`, `4`, `5`, `6`, `7`) VALUES
(1, 1, 1, '', '{"fps":25}', '{"fps":25}', '{"clock":48000,"fps":25}', '{"fps":25}', '{"fps":"30","startF":"155","endF":"3456"}', '{"fps":"25","startF":"155","endF":"7560","retake":true}', '{"fps":25}', '{"fps":25}', '', ''),
(2, 1, 2, '', '', '', '', '{"fps":"25"}', '{"fps":25}', '{"fps":"25","startF":"150","endF":"250"}', '{"fps":"25"}', '{"fps":"25"}', '', ''),
(3, 1, 4, '', '', '', '', '', '', '{"retake":true,"fps":"25"}', '{"fps":25}', '', '', '');


-- --------------------------------------------------------

--
-- Structure de la table `saam_tasks`
--

DROP TABLE IF EXISTS `saam_tasks`;
CREATE TABLE IF NOT EXISTS `saam_tasks` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `ID_project` int(4) NOT NULL,
  `title` varchar(256) NOT NULL,
  `description` text NOT NULL,
  `type` varchar(50) NOT NULL,
  `section` varchar(256) NOT NULL,
  `hooked_entity` text NOT NULL,
  `ID_creator` int(6) NOT NULL,
  `assigned_to` text NOT NULL,
  `standby` text NOT NULL,
  `status` int(2) NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `hide` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Structure de la table `saam_users`
--

DROP TABLE IF EXISTS `saam_users`;

CREATE TABLE IF NOT EXISTS `saam_users` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `pseudo` varchar(32) DEFAULT NULL,
  `login` varchar(25) NOT NULL,
  `passwd` varchar(256) NOT NULL,
  `nom` varchar(256) NOT NULL,
  `prenom` varchar(256) NOT NULL,
  `status` int(2) DEFAULT NULL,
  `competences` text NOT NULL,
  `mail` varchar(256) NOT NULL,
  `vcard` text NOT NULL,
  `lang` varchar(25) NOT NULL,
  `theme` varchar(25) NOT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `receiveMails` tinyint(1) NOT NULL DEFAULT '0',
  `receiveNotifs` tinyint(1) NOT NULL DEFAULT '0',
  `my_projects` text NOT NULL,
  `my_dpts` text NOT NULL,
  `my_sequences` text NOT NULL,
  `my_shots` text NOT NULL,
  `my_scenes` text NOT NULL,
  `my_assets` text NOT NULL,
  `my_tags` text NOT NULL,
  `my_msgs` text NOT NULL,
  `projects_local_path` varchar(256) NOT NULL,
  `projects_distant_url` varchar(256) NOT NULL,
  `ID_creator` int(6) DEFAULT NULL,
  `date_inscription` int(11) NOT NULL,
  `date_last_connexion` int(10) NOT NULL,
  `date_last_action` int(10) NOT NULL,
  `deconx_time` int(3) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Contenu de la table `saam_users`
--

INSERT INTO `saam_users` (`id`, `pseudo`, `login`, `passwd`, `nom`, `prenom`, `status`, `competences`, `mail`, `vcard`, `lang`, `theme`, `actif`, `receiveMails`, `receiveNotifs`, `my_projects`, `my_dpts`, `my_sequences`, `my_shots`, `my_scenes`, `my_assets`, `my_tags`, `my_msgs`, `projects_local_path`, `projects_distant_url`, `ID_creator`, `date_inscription`, `date_last_connexion`, `date_last_action`, `deconx_time`) VALUES
(1, 'Demo', 'demo', 'd32bb9c982791415b53df04aa0357d42', 'DEGUN', 'Demonstration', 2, '["artist3D","editor","coder","VFX","soundMix"]', '', '', 'fr', 'dark', 1, 0, 0, '["1"]', '[]', '[]', '[]', '[]', '[]', '', '', '', '', 2, 1337273676, 1378240336, 1378242472, 30),
(2, 'Karlova', 'vincseize', '613fcb7a8080693edbd08f86197ae2dd', 'POTTIER', 'Charles', 9, '["director","scenarist","artist2D","artist3D","coder"]', 'vincseize@gmail.com', '', 'fr', 'dark', 1, 1, 0, '["1"]', '[]', '[]', '[]', '[]', '[]', '', '', '', '', 2, 1337273676, 1367162125, 1367163057, 30),
(3, 'Polo', 'polosson', '670bd210128c4256c0b39c161a1dccb7', 'MAILLARDET', 'Paul', 9, '["soundTech","artist3D","coder","soundMix"]', 'polo@polosson.com', '', 'fr', 'dark', 1, 1, 0, '["1"]', '[]', '[]', '[]', '[]', '[]', '', '', '', '', 2, 1337273676, 1382285012, 1382289790, 30);
