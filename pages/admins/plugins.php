<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	require_once('url_fcts.php');

$plugPath = INSTALL_PATH.'plugins/';

$plugins = Array();
foreach(glob($plugPath.'*', GLOB_ONLYDIR) as $pp) {
	$pname = basename($pp);
	$plugins[$pname] = json_decode(file_get_contents($pp.'/plugInfos.json'), true);
	if (is_file($pp.'/logo.png'))
		$plugins[$pname]['logo'] = getSaamURL()."/plugins/".$pname.'/logo.png';
	$plugins[$pname]['enabled']  = @(bool)$_SESSION['CONFIG']['plugins_enabled'][$pname];
}

?>
<script>
$(function(){
	$('.bouton').button();

	$('.disEnPlug').click(function(){
		var state	 = $(this).attr('state');
		var plugName = $(this).parent('div').attr('plug');
		var ajaxStr	 = 'action=disenplug&plugName='+plugName+'&state='+state;
		AjaxJson(ajaxStr, 'admin/admin_plugs_actions', function(R){
			retourAjaxMsg(R, true);
		});
	});
});

</script>

<div class="stageContent pad5">
	<div class="floatR marge30r margeTop5 marge15bot">
		<button class="bouton">Ask for a new plugin</button>
	</div>

	<h2>PLUGINS MANAGER</h2>

	<?php foreach($plugins as $plug):
		$enabled = $plug['enabled'];
		if ($plug['forceEnable'])
			$enabled = true; ?>
		<div class="ui-widget-content ui-corner-all pad5 marge10l marge30r gros">
			<div class="big gras padV5 colorBtnFake" style="margin-left: 65px;"><?php echo mb_convert_case($plug['name'], MB_CASE_UPPER) ?></div>
			<div class="floatR">
				<div class="inline mid w100 gros"><?php
					if ($enabled): ?>
						<div class="colorBtnFake"><span class="inline top ui-icon ui-icon-check"></span> Enabled</div><?php
					else: ?>
						<div class="ui-state-disabled"><span class="inline top ui-icon ui-icon-close"></span> Disabled</div><?php
					endif;	?>
				</div>
				<div class="inline mid w300 marge30l" plug="<?php echo $plug['name']; ?>"><?php
					if ($plug['forceEnable']): ?>
						<p class="ui-state-disabled">This SaAM plugin is FREE to use!</p><?php
					else: ?>
						<button class="bouton disEnPlug <?php echo ($enabled) ? 'hide':''; ?>" state="enable">Enable plugin</button>
						<button class="bouton disEnPlug <?php echo ($enabled) ? '':'hide'; ?>" state="disable">Disable plugin</button><?php
					endif; ?>
				</div>
			</div>
			<img class="floatL marge5" src="<?php echo @$plug['logo'] ?>" alt="" height="60" width="60" />
			<div style="margin-top:20px;">
				<?php echo $plug['descr']; ?>
			</div>
			<div class="fixFloat"></div>
		</div>
	<?php endforeach; ?>
	<div class="ui-widget-content ui-corner-all pad5 marge10l marge30r margeTop10 hide">
	</div>
</div>