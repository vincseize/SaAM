<?php
@session_start();

if (!isset($_SESSION["user"])) die('No user connected.');

$dontTouchSSID = true;

require_once ($_SESSION['INSTALL_PATH'].'inc/checkConnect.php' );
require_once ('vignettes_fcts.php');
require_once ('dates.php');

$userID = $_SESSION['user']->getUserInfos(Users::USERS_ID);
Users::purge_user_assets($userID);

$myAssetList =  $_SESSION['user']->getUserAssets();

if (is_array($myAssetList) && count($myAssetList) > 0) :

	$myAssets = Array();

	foreach ($myAssetList as $assetID) :
		try {
			$asset = new Assets(false, (int)$assetID);
			$assetProjs = $asset->getProjects();
		}
		catch(Exception $e) { continue; }
		if (@$_SESSION['active_project_id'] && @$_SESSION['active_project_id'] != $assetProjs[0])
			continue;
		$LU = $asset->getLastModifDate('timeStamp');
		$myAssets[$LU] = Array(
			"idProj" => $assetProjs[0],
			"name" => $asset->getName(),
			"path" => $asset->getPath(),
			"lock" => $asset->getHandler(Users::USERS_PSEUDO),
			"vignette" => check_asset_vignette($asset->getPath(), $asset->getName(), $assetProjs[0])
		);
	endforeach;
	krsort($myAssets);
	foreach ($myAssets as $TS => $a) : ?>
		<div class="inline gray-layer bordFin bordColInv2 ui-corner-all w9p margeTop5 pad3 doigt myAsset" help="vos_assets"
			 idProj="<?php echo $a['idProj']; ?>"
			 nameAsset="<?php echo $a['name']; ?>"
			 pathAsset="<?php echo $a['path']; ?>"
			 title="<?php echo $a['path'].$a['name']; ?>">
			<div class="center" style="position:relative; margin-bottom:-50px; padding-right:8px; height:50px;">
				<?php if ($a['lock']): ?>
				 <div class="inline top" style="width:10px; padding-right:58px; margin-top:0px;" title="<?php echo L_ASSET_HUNG_BY.' '.$a['lock']; ?>">
					<span class="ui-icon ui-icon-locked"></span>
				 </div>
				<?php endif; ?>
			</div>
			<img src="<?php echo $a['vignette']; ?>" height="50" />
		</div>

<?php endforeach;
else: ?>
	<br /><span class="ui-state-disabled"><?php echo L_NOTHING . ' ' . L_ASSIGNED_TO . ' ' . $_SESSION['user']->getUserInfos(Users::USERS_PSEUDO); ?>.</span>
<?php
endif; ?>

<script>
	$(function(){
		reCalcScrollMyMenu();
		$('.myAsset').hover(
			function(){$(this).addClass('ui-state-active').removeClass('gray-layer');},
			function(){$(this).removeClass('ui-state-active').addClass('gray-layer');}
		);

		var firstDept = $('#assets_depts').find('.deptBtn').first().next().attr('label');
		if (!localStorage['lastDeptMyAsset'])
			localStorage['lastDeptMyAsset'] = firstDept;

		// Clic sur un MyAsset
		$('#myMenu').off('click', '.myAsset');
		$('#myMenu').on('click', '.myAsset', function() {
			var idProj  = $(this).attr('idProj');
			localStorage['lastGroupDepts_'+idProj] = "assets";
			localStorage['lastDept_'+idProj+'_GRP_assets'] = localStorage['lastDeptMyAsset'];
			localStorage['activeBtn_'+idProj]	= '06_assets';
			localStorage['lastDept_'+idProj]	= localStorage['lastDeptMyAsset'];
			localStorage['openAsset_'+idProj]	= $(this).attr('nameAsset');
			localStorage['openAssetPath_'+idProj]	= $(this).attr('pathAsset');

			if (localStorage['activeContent'] == idProj) {
				$('#selectDeptsList').val('assets').change();
				$('#assets_depts').find('.deptBtn[label="'+localStorage['lastDeptMyAsset']+'"]').click();
			}
			else
				openProjectTab (idProj);
		});
	});
</script>