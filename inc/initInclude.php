<?php

$pathClass = INSTALL_PATH."classes";
$pathConf = INSTALL_PATH."config";
$pathFCT = INSTALL_PATH."fct";
$pathFCTAdmins = INSTALL_PATH."fct/admins";
$pathInc = INSTALL_PATH."inc";
$pathPages = INSTALL_PATH."pages";
$pathModals = INSTALL_PATH."modals";
$pathRecette = INSTALL_PATH."_RECETTE";

set_include_path(
	get_include_path() .
	PATH_SEPARATOR . $pathClass .
	PATH_SEPARATOR . $pathConf .
	PATH_SEPARATOR . $pathFCT .
	PATH_SEPARATOR . $pathFCTAdmins .
	PATH_SEPARATOR . $pathInc .
	PATH_SEPARATOR . $pathPages .
	PATH_SEPARATOR . $pathModals .
	PATH_SEPARATOR . $pathRecette
);

//echo get_include_path();

?>
