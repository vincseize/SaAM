<?php
	@session_start();
	require_once ('directories.php');


	try { $acl = new ACL(@$_SESSION['user']);
?>

<script>
	$(function(){
		$('#calendarTools').datepicker({dateFormat: 'yymmdd', firstDay: 1, changeMonth: false, changeYear: false});

		$('#devBtns').children('div').hover(
			function() { $(this).addClass('ui-state-hover'); },
			function() { $(this).removeClass('ui-state-hover'); }
		);
	});
</script>

<?php if ($acl->check('VIEW_TOOLS_CAL')) : ?>
	<div class="inline" id="calendarTools"></div>
<?php endif; ?>

<div id="rightMenuSection" class="margeTop10">

	<?php if ($acl->check('VIEW_TOOLS_BTNS_ADMIN')) : ?>
    	<button class="bouton lien w9p margeTop1" goto="admin_panel">SaAM <?php echo L_BTN_ADMIN; ?></button>
        <br /><br />
		<?php if ($acl->check('VIEW_TOOLS_BTNS_ADMIN_NEWS')) : ?>
			<button class="bouton lien w9p margeTop1" goto="admin_news"><?php echo L_BTN_ADMIN_NEWS; ?></button>
		<?php endif; ?>
		<button class="bouton lien w9p margeTop1" goto="admin_users"><?php echo L_BTN_ADMIN_USERS; ?></button>
		<button class="bouton lien w9p margeTop1" goto="admin_projects"><?php echo L_BTN_ADMIN_PROJECTS; ?></button>
		<br /><br />
	<?php endif; ?>

	<?php if ($acl->check('VIEW_TOOLS_BTNS_NOTES')) : ?>
		<button class="bouton lien w9p margeTop1" goto="notes"><?php echo L_BTN_NOTES; ?></button>
		<button class="bouton lien w9p margeTop1" goto="tags"><?php echo strtoupper(L_TAGS); ?></button>
		<br /><br />
	<?php endif; ?>

	<?php if ($acl->check('VIEW_TOOLS_BTNS_SCRIPT')) : ?>
		<button class="bouton lien w9p margeTop1" goto="scripts"><?php echo L_BTN_SCRIPT; ?></button>
	<?php endif; ?>

	<?php if ($acl->check('VIEW_TOOLS_BTNS_PLUGINS')) : ?>
		<button class="bouton w9p margeTop1" id="affPlugins">PLUGINS</button><br />
        <div class="inline fondSect1 leftText pad5 ui-corner-bottom" style="width:82%; display:none;" id="pluginsBtns"><?php
			$dir = 'plugins';
			$list_plugins = listDir($dir, $filter='subdir');
			foreach($list_plugins as $plugin):
				$icon = 'icone_invalid.png';
				if ((bool)$_SESSION['CONFIG']['plugins_enabled'][$plugin])
					$icon = "icone_valid.png"; ?>
				<div class='pad3 margeTop1 doigt'>
					<div class="floatR"><img src="gfx/icones/<?php echo $icon; ?>" height="20" /></div>
					<?php echo $plugin; ?>
				</div>
				<div class="fixFloat"></div><?php
			endforeach; ?>
		</div>

	<?php endif; ?>

	<?php if ($acl->check('VIEW_TOOLS_BTNS_BUGHUNTER')) : ?>
		<button class="bouton lien w9p margeTop1" goto="bughunter"><?php echo L_BTN_BUGHUNTER; ?></button
	<?php endif; ?>

         <br /><br /><br />

	<?php if ($acl->check('VIEW_TOOLS_BTNS_DEV')) : ?>
        <button class="bouton w9p" id="affDevBtns">DEV</button><br />
        <div class="inline fondSect1 leftText pad5 ui-corner-bottom" style="width:82%; display:none;" id="devBtns">
			<div class="lien pad3 margeTop1 doigt" goto="langs"><?php echo L_BTN_LANGUAGES; ?></div>
			<div class="lien pad3 margeTop1 doigt" goto="config_panel">SaAM <?php echo L_BTN_CONFIG; ?></div>
			<div class="lien pad3 margeTop1 doigt" goto="sql_utils"><?php echo L_BTN_SQL; ?></div>
			<div class="lien pad3 margeTop1 doigt" goto="api"><?php echo L_BTN_API; ?></div>
			<div class="lien pad3 margeTop1 doigt" goto="debug"><?php echo L_BTN_DEBUG; ?></div>
			<div class="lien pad3 margeTop1 doigt" goto="update_mngr"><?php echo L_BTN_UPDATE; ?></div>
			<div class="pad3 margeTop1"><a href="<?php echo URL_TRACKBACKS;?>" target="_blank"><?php echo L_BTN_TRBACK; ?></a></div>
		</div>
	<?php endif; ?>

</div>

<?php
	}
	catch (Exception $e) { die('<span class="colorErreur">'. $e->getMessage().'</span>'); }
?>

