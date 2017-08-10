<?php
require_once ('users_fcts.php');

function chooseThemeFolder() {
	if(isset($_SESSION['theme']) && $_SESSION["theme"] != '' ) {
		$repCss = FOLDER_CSS.$_SESSION['theme'];
		if (file_exists($repCss))
			return $repCss ;
		else return FOLDER_CSS.THEME_DEFAULT;
	}
	else return FOLDER_CSS.THEME_DEFAULT;
}

$domName =  (preg_match('/^saam_/', BASE)) ? preg_replace('/^saam_/', '', BASE) : $host;

$resetLocalStorage = false;
$cache_version = '1.1';

try {
	if (isset($_SESSION['user']))
		 $ACL = new ACL($_SESSION['user']);
	else $ACL = false;
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="keywords" content="SaAM, shots, assets, management, CG, post-production" />
	<meta name="description" content="Shots and Assets Manager" />

	<meta name="robots" content="noindex,nofollow" />

	<title><?php echo @$helpTitle."SaAM v".SAAM_VERSION.' '.$domName; ?></title>

	<link type="image/x-icon" href="gfx/favicon.ico" rel="shortcut icon" />

	<link type="text/css" href="<?php echo chooseThemeFolder(); ?>/jquery-ui-1.8.17.custom.css" rel="stylesheet" />
	<link type="text/css" href="css/ossature.css?ver=<?php echo $cache_version; ?>" rel="stylesheet" />
	<link type="text/css" href="css/ossature_print.css" rel="stylesheet" media="print"/>
	<link type="text/css" href="<?php echo chooseThemeFolder(); ?>/colors.css?ver=<?php echo $cache_version; ?>" rel="stylesheet" />
	<link type="text/css" href="css/jquery.ui.selectmenu.css" rel="stylesheet" />
	<link type="text/css" href="css/jquery.multiselect.css" rel="stylesheet" />
	<link type="text/css" href="css/help.css" rel="stylesheet" />
	<link type="text/css" href="css/jpreloader.css" rel="stylesheet" />

	<script type="text/javascript" src="js/jquery-1.7.min.js">// JQUERY CORE</script>
	<script type="text/javascript" src="js/jpreloader.min.js">// Preloader site</script>
	<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.js?ver=<?php echo $cache_version; ?>">// JQUERY UI</script>
	<script type="text/javascript" src="js/highcharts/highcharts.js">// For stats</script>
	<script type="text/javascript" src="js/highcharts/modules/exporting.js">// For stats export img</script>
	<script type="text/javascript" src="js/jquery.ui.datepicker-fr.js">// Patch français pour DatePicker</script>
	<script type="text/javascript" src="js/jquery.ui.selectmenu.js">// Pour des selects menus jolis</script>
	<script type="text/javascript" src="js/jquery.ui.multiselect.min.js">// Pour des selects multiples jolis</script>
	<script type="text/javascript" src="js/jquery.ajaxq-0.0.1.js">// JQUERY plugin : file d'attente des requêtes ajax</script>
	<script type="text/javascript" src="js/jquery.urlGet.js">// JQUERY plugin : récupérer les variables GET dans l'url</script>
	<script type="text/javascript" src="js/JSON.js">// JSON plugin : utilitaire pour décoder/encoder le JSON</script>
	<script type="text/javascript" src="js/JSONError.js">// JSON plugin : Objet d'erreur JSON</script>
	<script type="text/javascript" src="js/jquery.cookie.js">// JQUERY plugin : gestion des cookies</script>
	<script type="text/javascript" src="js/jquery.slimscroll.min.js">// JQUERY plugin : barre de scroll</script>
	<script type="text/javascript" src="js/jquery.mousewheel.min.js">// JQUERY plugin : utilitaire de gestion de molette souris</script>
	<script type="text/javascript" src="js/modernizr-latest.js">// MODERNIZR : pour rétro compatibilité de certains éléments</script>

    <!-- Bank & FancyBox -->
    <!-- Add mousewheel plugin (this is optional) -->
    <script type="text/javascript" src="js/fancybox/lib/jquery.mousewheel-3.0.6.pack.js"></script>
    <!-- Add fancyBox -->
    <link rel="stylesheet" href="js/fancybox/source/jquery.fancybox.css?v=2.0.6" type="text/css" media="screen" />
    <script type="text/javascript" src="js/fancybox/source/jquery.fancybox.pack.js?v=2.0.6"></script>



<!--**************************** PLUGINS SAAM ********************************************-->

	<!-- CHAT NODEJS -->
		<?php
        if ($ACL) { if ($ACL->check('VIEW_HEADER_JCHAT') && SOCKET_CHAT_URL != false) : ?>
			<script>
				var socketURL = '<?php echo SOCKET_CHAT_URL; ?>';
				var chatTimeout = 3;					// Temps d'attente avant de laisser tomber le tchat (en secondes)
				var chatSound = true;					// Activer / désactiver le son d'alerte
				var timeChatSoundOff = 1000 * 30;		// 30 secondes avant de réactiver le son d'alerte
			</script>
			<audio src="<?php echo FOLDER_PLUGINS; ?>chat/saam_new_chat_msg.wav" preload="auto" id="soundchat"></audio>
			<script src="<?php echo FOLDER_PLUGINS; ?>chat/clientChat.js"></script>
			<link type='text/css' href='<?php echo FOLDER_PLUGINS;?>chat/chat.css' rel='stylesheet' />
		<?php else: ?>
			<script>
				$(function(){
					$('#avatarsList').html('<span class="ui-state-disabled" title="Instant message system unavailable">.</span>');
				});
			</script>
		<?php endif; } ?>
	<!-- ..... -->


<!--***************************************************************************************-->

	<script type="text/javascript" src="js/init_all_pages.js?ver=<?php echo $cache_version; ?>">// scripts et fonctions à charger pour toutes les pages</script>
	<script type="text/javascript" src="js/interface.js?ver=<?php echo $cache_version; ?>">// gestions des menus </script>
	<script type="text/javascript" src="js/initAjaxMVC.js?ver=<?php echo $cache_version; ?>">// gestions des menus </script>


	<script type="text/javascript">
		<?php
		if (isset($_GET['proj']))
			echo 'var ongletOnLoad = "'.$_GET['proj'].'" ;';
		else echo 'var ongletOnLoad = false ;';
		?>
		var langue = '<?php echo (isset($_SESSION['user'])) ? $_SESSION['user']->getUserInfos('lang') : LANG_DEFAULT; ?>';
		var userID = <?php echo (isset($userID)) ? $userID : '"none"'; ?>;
		var userStatus = <?php echo (isset($userStatus)) ? $userStatus : '"none"'; ?>;
		var userStatusStr = '<?php echo (isset($userLogin)) ? @$_SESSION['STATUS_LIST'][$userStatus] : ''; ?>';
		var userLogin = '<?php echo (isset($userLogin)) ? $userLogin : '"none"'; ?>';
		var userProjects = <?php echo (isset($userProjectsJS)) ? $userProjectsJS : '[]'; ?>;
		var activeProject = 0;
		var checkConxItv; var refreshWip; var refreshMyShots; var refreshMessages;
		var hideLoader = false;

		$(function(){
			$.datepicker.setDefaults($.datepicker.regional['<?php echo LANG; ?>']);

			// Check de connexion régulier : pour déconx auto.
			checkConxItv = setInterval(function() {
//				console.log('checking connexion validity...');
				hideLoader = true;
				$.getJSON('actions/check_deconx_auto.php', function(data){
					hideLoader = false;
					if (data.status == 'deconnexion_auto')
						disconnect(data.login);
					else if (data.status == 'still_valid_connexion') {
						$('#avatarUserDiv').attr('title', userLogin + "\n" + data.remain + ' minutes remaining');
						$('#loginUserDiv').removeClass('warning_anim').attr('title', 'Status: '+userStatusStr);
						if (parseInt(data.remain) <= 10)
							console.log(data.remain + ' minutes remaining, before deconnexion auto!');
						if (parseInt(data.remain) <= 2) {
							console.log("WARNING!! "+data.remain+" minute before deconnexino auto!");
							$('#loginUserDiv').addClass('warning_anim').attr('title', 'WARNING!! '+data.remain+' minute before deconnexino auto!');
						}
					}
					else if (data.status == 'no_user_connected') {
						clearInterval(checkConxItv);
						console.log('No connexion available. Nothing to do. Reloading if needed...');
						<?php echo ($logged) ? "window.location.href = './';" : ""; ?>
					}
				});
			}, <?php echo REMAIN_REFRESH_INTERVAL ;?> * 1000);		// toutes les 30 secondes

			// REFRESH du mode WIP
			refreshWip = setInterval(function() {
//				console.log('refreshing WIP indicator...');
				hideLoader = true;
				$("#wip_refresh").load('modals/wip.php', function(){
					hideLoader = false;
				});
			}, <?php echo WIP_REFRESH_INTERVAL ;?> * 1000);			// toutes les 2 minutes

			// REFRESH des my_shots
			refreshMyShots = setInterval(function() {
//				console.log('refreshing My_Menu...');
				$('.myMenuHeadEntry.ui-state-focus').click();
			}, <?php echo MY_SHOTS_REFRESH_INTERVAL ;?> * 1000);	// toutes les 5 minutes

			// REFRESH des messages en bas
			refreshMessages = setInterval(function() {
//				console.log('refreshing Messages...');
				hideLoader = true;
				$("#footerPage").load('modals/menuFooter.php', function(){
					hideLoader = false;
				});
			},<?php echo MESSAGES_REFRESH_INTERVAL ;?> * 1000);		// toutes les 5 minutes

			// REFRESH des jchat avatars (pour serveur DEMO)
			// -------------------------------------------------->> @Voir dans ./modals/menuHeader.php

		});

	</script>

	<?php
		}
		catch(Exception $e) {
			echo $e->getMessage();
		}
	?>

</head>
