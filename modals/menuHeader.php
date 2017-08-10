<?php
	@session_start();

	require_once ($_SESSION['INSTALL_PATH'].'/inc/checkConnect.php' );
	require_once ('users_fcts.php');
	require_once ('vignettes_fcts.php');
	require_once ('dates.php');

	$avatar = check_user_vignette_ext($userID, $userLogin);
	$md5pass = $_SESSION['user']->getPassword(md5('YawollJesuiSbiEnMoIMêm'));

	$tutoSaAM = (IS_LOCAL) ? 'http://localhost/SaAM_tutos/index.php' : 'https://tuto.saamanager.net/index.php';
	if(@$_SESSION['isDemoSaAM'] === false)
		$tutoSaAM .= "?saam=".BASE."&autoConx=$userLogin&pw=$md5pass"
?>

<div class="floatR" help="chat_user_logout">

	<div class='inline mid' id='avatarsList'><span class="ui-state-disabled">Connecting to chat...</span></div>

	<span class="colorSoft"> | </span>

	<div class="inline mid" title="<?php echo $_SESSION['user']->getUserInfos('login')."\n".(DECONNEXION_AUTO_TIME / 60).' minutes remaining'; ?>" id="avatarUserDiv">
		<img id="avatar" src="<?php echo $avatar; ?>" height="12px">
	</div>
	<div class="inline mid" title="status : <?php echo $_SESSION['STATUS_LIST'][$_SESSION['user']->getUserInfos('status')]; ?>" id="loginUserDiv">
		<b><?php echo $_SESSION['user']->getUserInfos('login'); ?></b>
	</div>
	<div class="inline mid ui-state-default ui-corner-all" id="delogBtn" style="margin-top:-2px;">
		<a href="#" onClick="disconnect('<?php echo $_SESSION['user']->getUserInfos('login'); ?>');" title="<?php echo L_BTN_DISCONNECT; ?>"><span class="inline bot ui-icon ui-icon-close"></span></a>
	</div>
</div>

<div class="inline top" style="font-size:0.05em; margin-top: -2px;"  help="main_menu_top">
	<a href="index.php" title="<?php echo L_REFRESH; ?>"><div class="bouton"><span class="inline top ui-icon ui-icon-arrowrefresh-1-s"></span></div></a>
</div>

<div class="inline top" style="margin-top: 2px;" help="main_menu_top">
    <div class="inline mid" id="wip_refresh" >
	<?php include 'modals/wip.php';?>
    </div>

        <span class="lien marge10l ui-corner-all pad3" goto="overview_global"><?php echo L_BTN_CHARTS; ?></span>

	<?php if ($_SESSION['user']->isArtist() || $_SESSION['user']->isDemo()) : ?>
		<span class="lien marge10l ui-corner-all pad3" goto="prefs"><?php echo L_BTN_PREFS; ?></span>

	<?php endif; ?>
	<a href="indexHelp.php" target="_blank" class="colorSoft"><span class="marge10l"><?php echo L_BTN_HELP; ?></span></a>
	<a href="<?php echo $tutoSaAM; ?>" target="_blank" class="colorSoft"><span class="marge10l"><?php echo L_BTN_TUTOS; ?></span></a>
	<a href="http://saamfaq.saamanager.com" target="_blank" class="colorSoft"><span class="marge10l"><?php echo L_BTN_FAQ; ?></span></a>
</div>

<div class="inline top"help="main_menu_top">
	<span class="inline top marge10l ui-icon ui-icon-search doigt showSearch"></span>
	<input class="noBorder ui-corner-all fondSect2 hide searchInput" type="text" size="30" />
	<span class="inline top ui-icon ui-icon-circle-zoomin doigt submitSearch" style="display:none;" id="globalSearch"  ></span>
</div>

<?php if ($_SESSION['user']->isDemo()) : ?>
	<div class="colorErreur gros gras" id="demoMsg">!! <?php echo @L_DEMO_MODE; ?> !!</div>
<?php endif;

	if (defined('GLOBAL_ERROR')): ?>
		<div class="inline colorErreur gros gras"><?php echo(GLOBAL_ERROR); ?></div>
		<script>
			$(function(){
				$('#btnRightPanelToggle').click();
				$('.lien[goto="update_mngr"]').click();
			});
		</script>
<?php endif; ?>

