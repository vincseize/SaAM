<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

	$userTags = $_SESSION['user']->getUserTags();
	$userProjs = $_SESSION['user']->getUserProjects();
	$userLevel = $_SESSION['user']->getUserInfos(Users::USERS_STATUS);

	$friendsList = Users::getUsers_by_projects($userProjs, $userLevel);

?>

<script>
	$(function(){
		$('.bouton').button();
		$('#tagHashTag').selectmenu({style: 'dropdown'});

		var tglH = stageHeight - 130;
		$('#tagsList').slimScroll({
			position: 'right',
			height: tglH+'px',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		});

		$('.userTagLine')
			.hover(
				function(){ $(this).addClass('ui-state-hover'); },
				function(){ $(this).removeClass('ui-state-hover'); }
			)
			.click(
				function(){
					$('.userTagLine').removeClass('ui-state-active');
					$(this).addClass('ui-state-active');
					var tagName = $(this).attr('tagName');
					$('#tagNameTitle').html("'"+tagName+"'");
					var req = { tagName: tagName, type: 'user'};
					$('#divListeShotTags').load('modals/show_shots_by_tag.php', req);
				}
			);

		$('#tagHashTag').change(function(){
			var tagType = $(this).val();
			var oldTagName = $('#addUserTag').val();
			if (tagType == 'none') {
				oldTagName = oldTagName.replace(/^#[A-Z]{2}_/, '');
				tagType = '';
			}
			$('#addUserTag').val(tagType+oldTagName);
		});

		$('.showShareBox').click(function(){
			var tagName = $(this).attr('tagName');
			var btnCoord = $(this).position();
			var topBox	 = btnCoord.top;
			$('#shareList').css('top',topBox).show(transition);
			$('#titleShareTag').html(tagName);
			$('.shareUserTagBtn').attr('tagName', tagName);
		});

		$('#closeShareBox').click(function(){
			$('#shareList').hide(transition);
		});

	});
</script>

<div class="stageContent padV5">

	<div class="inline top bordBankSection padV10">

		<div class="margeTop10">
			<div class="margeTop10" title="Type of tag">
				<select style="width: 170px;" id="tagHashTag">
					<option value="none" class="colorDiscret" selected>Default tag</i></option>
					<option value="#FT_">#<?php echo L_FINAL; ?> tag</option>
				</select>
			</div>
			<div class="inline mid ui-corner-all">
				<input class="noBorder pad3 enorme ui-corner-all fondSect3" style="width:165px;" type="text" title="Tag name" id="addUserTag" />
			</div>
			<div class="inline mid micro"><button class="bouton" title="Add a tag" id="addUserTagBtn"><span class="ui-icon ui-icon-plusthick"></span></button></div>
		</div>

		<h3 class="colorMid">Tags of <?php echo $_SESSION['user']->getUserInfos(Users::USERS_PSEUDO); ?></h3>

		<div style="padding-right:15px;" id="tagsList">
			<?php
			if (is_array(@$userTags)) :
				foreach ($userTags as $uTag) : ?>
					<div class="inline mid w150 ui-state-default ui-corner-bl ui-corner-tr pad10 doigt userTagLine" tagName="<?php echo $uTag; ?>"><?php echo $uTag; ?></div>
					<div class="inline mid pico">
						<button class="bouton delUserTagBtn" title="Delete this tag" tagName="<?php echo $uTag; ?>"><span class="ui-icon ui-icon-trash"></span></button>
						<?php if (!preg_match('/^#FT_/', $uTag)): ?>
						<button class="bouton showShareBox" title="Share this tag with..." tagName="<?php echo $uTag; ?>"><span class="ui-icon ui-icon-star"></span></button>
						<?php endif; ?>
					</div><br />
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

	<div class="ui-corner-all fondSect1 shadowOut pad10 hide" id="shareList">
		<div class="floatR doigt" id="closeShareBox"><span class="ui-icon ui-icon-closethick"></span></div>
		<span class="colorDiscret gras">Share tag <span class="colorMid" id="titleShareTag"></span> with : </span>
		<div class="margeTop5 padH5 petit">
			<?php
			if (is_array($friendsList)) :
				foreach($friendsList as $idFriend => $friend) :
					if ($idFriend == $_SESSION['user']->getUserInfos(Users::USERS_ID)) continue; ?>
					<button class="bouton shareUserTagBtn margeTop1" idFriend="<?php echo $idFriend; ?>"><?php echo $friend[Users::USERS_PSEUDO]; ?></button>
				<?php
				endforeach;
			else : echo '<p>Nobody to share with.</p>';
			endif;
			?>
		</div>
	</div>
</div>