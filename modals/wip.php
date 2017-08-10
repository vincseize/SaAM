<?php
@session_start();

$dontTouchSSID = true;

if (isset($_SESSION['INSTALL_PATH']))
    require_once ($_SESSION['INSTALL_PATH'].'inc/checkConnect.php' );
if (file_exists('inc/checkConnect.php'))
    require_once ('inc/checkConnect.php' );
if (file_exists('../inc/checkConnect.php'))
    require_once ('../inc/checkConnect.php' );
if (file_exists('../../inc/checkConnect.php'))
    require_once ('../../inc/checkConnect.php' );

require_once ('users_fcts.php' );


try {
	$w = new Infos(TABLE_CONFIG);
	$w->loadInfos('version', SAAM_VERSION);
	$wip = $w->getInfo('wip');
}
catch (Exception $e) {

}

if (isset($_SESSION['user'])) {
	if (@$wip == 1) {
		if ($_SESSION['user']->isDev())
			 echo '<span class="colorErreur marge10l WIPbtn doigt" setWip=0 title="unset WIP mode">'.strtoupper(L_WIP).'</span>';
		else echo '<span class="colorErreur marge10l" title="reload with CTRL+F5 to be sure !">'.strtoupper(L_WIP).'</span>';
		echo '<script>alert("'.strtoupper(L_WIP).'\n\nreload with CTRL+F5 to be sure !");</script>';
	}
	else {
		if ($_SESSION['user']->isDev())
			echo '<span class="marge10l ui-corner-all pad3 WIPbtn doigt" setWip=1 title="set WIP mode">'.strtoupper(L_BTN_WIP).'</span>';
	}
}
?>
