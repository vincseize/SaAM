<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

try {
	$SaAMinfo = new Infos(TABLE_CONFIG);
	$SaAMinfo->loadInfos('version', SAAM_VERSION);
	$globalTags = json_decode($SaAMinfo->getInfo('global_tags'));
}
catch (Exception $e) { }

try {
	$acl = new ACL(@$_SESSION['user']);
?>

<script>
	$(function(){
		$('.bouton').button();

		var tglH = stageHeight - 115;
		$('#tagsList').slimScroll({
			position: 'right',
			height: tglH+'px',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		});

		$('.globalTagLine')
			.hover(
				function(){ $(this).addClass('ui-state-hover'); },
				function(){ $(this).removeClass('ui-state-hover'); }
			)
			.click(
				function(){
					$('.globalTagLine').removeClass('ui-state-active');
					$(this).addClass('ui-state-active');
					var tagName = $(this).attr('tagName');
					$('#tagNameTitle').html("'"+tagName+"'");
					var req = { tagName: tagName, type: 'global'};
					$('#divListeShotTags').load('modals/show_shots_by_tag.php', req);
				}
			);
	});
</script>

<div class="stageContent padV5">

	<div class="inline top bordBankSection padV10">

		<?php if ($acl->check('VIEW_TOOLS_BTNS_ADMIN')) : ?>
			<div class="margeTop10">
					<div class="inline mid ui-corner-all margeTop10">
						<input class="noBorder pad3 enorme ui-corner-all fondSect3" type="text" style="width:165px;" title="Tag name" id="addGlobalTag" />
					</div>
					<div class="inline mid micro margeTop10"><button class="bouton" title="Add a tag" id="addGlobalTagBtn"><span class="ui-icon ui-icon-plusthick"></span></button></div>
			</div>
		<?php endif; ?>

		<h3 class="colorMid">Global Tags</h3>

		<div style="padding-right:39px;" id="tagsList">
		<?php if (is_array(@$globalTags)) :
			foreach ($globalTags as $uTag) : ?>
				<div class="inline mid w150 ui-state-default ui-corner-bl ui-corner-tr pad10 doigt globalTagLine" tagName="<?php echo $uTag; ?>"><?php echo $uTag; ?></div>
				<?php if ($acl->check('VIEW_TOOLS_BTNS_ADMIN')) : ?>
					<div class="inline mid pico">
						<button class="bouton delGlobalTagBtn" title="Delete this tag" tagName="<?php echo $uTag; ?>"><span class="ui-icon ui-icon-trash"></span></button>
					</div>
				<?php endif; ?>
				<br />
			<?php endforeach;
			else : ?>
				<div class="inline mid w100 gros ui-state-disabled ui-corner-all pad5"><?php echo L_NOTHING . ' ' . L_TAGS ?></div><br />
		<?php endif; ?>
		</div>
	</div>


	<div class="inline top marge30l">
		<div id="divListeShotTags">
			<div class="ui-corner-all fondSect4 pad10 margeTop10"><span class="ui-state-disabled">Choose a tag.</span></div>
		</div>
	</div>

	<?php
		}
		catch(Exception $e) { die('<span class="colorErreur">'. $e->getMessage().'</span>'); }
	?>
</div>