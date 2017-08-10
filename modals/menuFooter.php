<?php
	@session_start();
	$dontTouchSSID = true;
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	require_once ('dates.php');

	if (!isset($_SESSION["user"])) die('No user connected.');

	$my_id		= $_SESSION["user"]->getUserInfos(Users::USERS_ID);
	$my_pseudo	= $_SESSION['user']->getUserInfos(Users::USERS_PSEUDO);
	$my_shots	= $_SESSION['user']->getUserShots();
	$my_assets	= $_SESSION['user']->getUserAssets();
	$my_scenes	= $_SESSION['user']->getUserScenes();

try {
	$l = new Liste();
	$l->addFiltre('sender', '!=', $my_pseudo);
	$l->getListe(TABLE_COMM_SHOT, '*', 'date', 'DESC');
	$allCommShots = $l->simplifyList('id');
	$l->getListe(TABLE_COMM_ASSET, '*', 'date', 'DESC');
	$allCommAssets = $l->simplifyList('id');
	$l->getListe(TABLE_COMM_SCENES, '*', 'date', 'DESC');
	$allCommScenes = $l->simplifyList('id');

	$comments = Array();

	// SHOTS
	if (is_array($allCommShots)) {
		foreach($allCommShots as $comID => $commShot){
			try {
				// Check si le shot appartient à l'user
				if (!in_array((int)$commShot[Comments::COMM_ID_SHOT], $my_shots))
					continue;
				// Check si message lu par user courant
				$reader_list = json_decode($commShot[Comments::COMM_READ_BY]);
				if (is_array($reader_list) && in_array((int)$my_id, $reader_list))
					continue;
				// Check si le projet est visible (si non, on passe au suivant)
				$p = new Projects($commShot[Comments::COMM_ID_PROJECT]);
				if (!$p->isVisible())
					continue;
				$cts = SQLdateConvert($commShot[Comments::COMM_DATE], 'timeStamp');
				// Récup des infos à afficher
				$comments[$cts]['commId']  = $comID;
				$comments[$cts]['cType']   = 'retake';
				$comments[$cts]['sender']  = $commShot[Comments::COMM_SENDER];
				$comments[$cts]['comment'] = $commShot[Comments::COMM_COMMENT];
				$comments[$cts]['date']    = SQLdateConvert($commShot[Comments::COMM_DATE]);

				$comments[$cts]['projId']    = $commShot[Comments::COMM_ID_PROJECT];
				$comments[$cts]['projTitle'] = $p->getTitleProject();

				try {
					$d = new Infos(TABLE_DEPTS);
					$d->loadInfos('id', $commShot[Comments::COMM_DEPT]);
					$comments[$cts]['deptName'] = $d->getInfo('label');
					$comments[$cts]['tplDept']  = $d->getInfo('template_name');
				}
				catch (Exception $void) {
					$comments[$cts]['deptName'] = 'storyboard';
					$comments[$cts]['tplDept']  = 'template_1';
				}

				$sh = new Shots($commShot[Comments::COMM_ID_SHOT]);
				$comments[$cts]['shotId']	 = $commShot[Comments::COMM_ID_SHOT];
				$comments[$cts]['shotTitle'] = $sh->getShotInfos(Shots::SHOT_TITLE);
				$comments[$cts]['seqId']	 = $sh->getShotInfos(Shots::SHOT_ID_SEQUENCE);

				$se = new Sequences($comments[$cts]['seqId']);
				$comments[$cts]['seqTitle']	 = $se->getSequenceInfos(Sequences::SEQUENCE_TITLE);
			} // Si erreur (quelque chose n'existe pas en BDD, par exemple)
			catch (Exception $e) { //	echo '<tr><td colspan="6">'.$e->getMessage().'</td></tr>';		// for debug
				continue; }
		}
	}
	// ASSETS
	if (is_array($allCommAssets)){
		foreach($allCommAssets as $comID => $commAsset){
			try {
				// Check si l'asset appartient à l'user
				if (!in_array((int)$commAsset[Comments::COMM_ID_ASSET], $my_assets))
					continue;
				// Check si message lu par user courant
				$reader_list = json_decode($commAsset[Comments::COMM_READ_BY]);
				if (is_array($reader_list) && in_array((int)$my_id, $reader_list))
					continue;
				// Check si le projet est visible (si non, on passe au suivant)
				$p = new Projects($commAsset[Comments::COMM_ID_PROJECT]);
				if (!$p->isVisible())
					continue;
				$cts = SQLdateConvert($commAsset[Comments::COMM_DATE], 'timeStamp');
				// Récup des infos à afficher
				$comments[$cts]['commId']  = $comID;
				$comments[$cts]['cType']   = 'retake_asset';
				$comments[$cts]['sender']  = $commAsset[Comments::COMM_SENDER];
				$comments[$cts]['comment'] = $commAsset[Comments::COMM_COMMENT];
				$comments[$cts]['date']    = SQLdateConvert($commAsset[Comments::COMM_DATE]);

				$comments[$cts]['projId']    = $commAsset[Comments::COMM_ID_PROJECT];
				$comments[$cts]['projTitle'] = $p->getTitleProject();

				$d = new Infos(TABLE_DEPTS);
				$d->loadInfos('id', $commAsset[Comments::COMM_DEPT]);
				$comments[$cts]['deptName'] = $d->getInfo('label');
				$comments[$cts]['tplDept']  = $d->getInfo('template_name');

				$as = new Assets($commAsset[Comments::COMM_ID_PROJECT], (int)$commAsset[Comments::COMM_ID_ASSET]);
				$comments[$cts]['assetId'] = $commAsset[Comments::COMM_ID_ASSET];
				$comments[$cts]['assetName']  = $as->getName();
				$comments[$cts]['assetPath']  = $as->getPath();
				$comments[$cts]['assetCateg'] = $as->getCategory();
			} // Si erreur (quelque chose n'existe pas en BDD, par exemple)
			catch (Exception $e) { //	echo '<tr><td colspan="6">'.$e->getMessage().'</td></tr>';		// for debug
				continue; }
		}
	}
	// ASSETS
	if (is_array($allCommScenes)){
		foreach($allCommScenes as $comID => $commScene){
			try {
				// Check si l'asset appartient à l'user
				if (!in_array((int)$commScene[Comments::COMM_ID_SCENE], $my_scenes))
					continue;
				// Check si message lu par user courant
				$reader_list = json_decode($commScene[Comments::COMM_READ_BY]);
				if (is_array($reader_list) && in_array((int)$my_id, $reader_list))
					continue;
				// Check si le projet est visible (si non, on passe au suivant)
				$p = new Projects($commScene[Comments::COMM_ID_PROJECT]);
				if (!$p->isVisible())
					continue;
				$cts = SQLdateConvert($commScene[Comments::COMM_DATE], 'timeStamp');
				// Récup des infos à afficher
				$comments[$cts]['commId']  = $comID;
				$comments[$cts]['cType']   = 'retake_scene';
				$comments[$cts]['sender']  = $commScene[Comments::COMM_SENDER];
				$comments[$cts]['comment'] = $commScene[Comments::COMM_COMMENT];
				$comments[$cts]['date']    = SQLdateConvert($commScene[Comments::COMM_DATE]);

				$comments[$cts]['projId']    = $commScene[Comments::COMM_ID_PROJECT];
				$comments[$cts]['projTitle'] = $p->getTitleProject();

				$d = new Infos(TABLE_DEPTS);
				$d->loadInfos('id', $commScene[Comments::COMM_DEPT]);
				$comments[$cts]['deptName'] = $d->getInfo('label');
				$comments[$cts]['tplDept']  = $d->getInfo('template_name');

				$ss = new Scenes((int)$commScene[Comments::COMM_ID_SCENE]);
				$comments[$cts]['sceneId'] = $commScene[Comments::COMM_ID_SCENE];
				$comments[$cts]['sceneName']  = $ss->getSceneInfos(Scenes::TITLE);
			} // Si erreur (quelque chose n'existe pas en BDD, par exemple)
			catch (Exception $e) { //	echo '<tr><td colspan="6">'.$e->getMessage().'</td></tr>';		// for debug
				continue; }
		}
	}
	krsort($comments);
}
catch(Exception $e) { echo '<pre class="pad5 red">'.$e->getMessage().'</pre>';  }
?>

<script>
	$(function(){

		$('#messageFootList').slimScroll({
			position: 'right',
			height: footerHeight+'px',
			size: '10px',
			wheelStep: 7,
			railVisible: true
		});

		// Clic sur Hide MyMessage Shot
		$('#messageFootList').off('click','.hideMsg');
		$('#messageFootList').on('click','.hideMsg', function(){
			var typeComm = $(this).parent('tr').attr('typeCom');
			var idComm   = $(this).parent('tr').attr('idComm');
			var ajaxReq  = 'action=setMessageRead&typeComm='+typeComm+'&idComm='+idComm+'&userID=<?php echo $my_id; ?>';
			AjaxJson(ajaxReq, 'msg_bottom_actions', retourActionMsgBottom);
		});

		// Clic sur un message pour aller direct sur le shot / asset
		$('#messageFootList').off('click','.commentMsg');
		$('#messageFootList').on('click','.commentMsg', function(){
			var typeCom = $(this).parent('tr').attr('typeCom');
			var idProj  = $(this).parent('tr').attr('idProj');
			var dept    = $(this).parent('tr').attr('dept');
			var tpl     = $(this).parent('tr').attr('tplDept');
			localStorage['activeBtn_'+idProj]	= tpl;
			localStorage['lastDept_'+idProj]	= dept;

			// Clic sur un MyMessage Shot    ## TODO A MUTUALISER AVEC js my_shots.php
			if (typeCom == 'retake'){
				seq_ID		= $(this).parent('tr').attr('idSeq');
				shot_ID		= $(this).parent('tr').attr('idShot');
				localStorage['lastGroupDepts_'+idProj] = "shots";
				localStorage['lastDept_'+idProj+'_GRP_shots'] = tpl;
				localStorage['openSeq_'+idProj]	= seq_ID;
				localStorage['openShot_'+idProj]	= shot_ID;
			}
			// Clic sur un MyMessage Asset    ## TODO A MUTUALISER AVEC js my_asset.php
			if (typeCom == 'retake_asset'){
				localStorage['lastGroupDepts_'+idProj] = "assets";
				localStorage['lastDept_'+idProj+'_GRP_assets'] = tpl;
				localStorage['openAsset_'+idProj]	= $(this).parent('tr').attr('nameAsset');
				localStorage['openAssetPath_'+idProj]	= $(this).parent('tr').attr('pathAsset');
			}
			// Clic sur un MyMessage Scene    ## TODO A MUTUALISER AVEC js my_scene.php
			if (typeCom == 'retake_scene'){
				localStorage['lastGroupDepts_'+idProj] = "scenes";
				localStorage['lastDept_'+idProj+'_GRP_scenes'] = tpl;
				localStorage['openScene_'+idProj]	= $(this).parent('tr').attr('idScene');
			}
			// On lance la redirection
			if (localStorage['activeContent'] == idProj) {
				$('#selectDeptsList').val(localStorage['lastGroupDepts_'+idProj]).change();
				$('#'+localStorage['lastGroupDepts_'+idProj]+'_depts').find('.deptBtn[label="'+dept+'"]').click();
			}
			else
				openProjectTab (idProj);
		});
	});

	function retourActionMsgBottom (retour) {
		if (retour == undefined)
			return false;
		if (retour.error == 'OK')
			$('#footerPage').load('modals/menuFooter.php');
		else {
			$('#retourAjax').html(retour.error+' : '+retour.message).addClass('ui-state-error').removeClass('ui-state-highlight').show(transition);
			setTimeout(function(){
				$('#retourAjax').fadeOut(transition);
			}, 10000);
		}
	}
</script>

<div class="pad5 center" id="messageFootList">
	<table class="tableListe">
	<?php
	foreach($comments as $comm): ?>
		<tr idComm="<?php echo $comm['commId']; ?>" typeCom="<?php echo $comm['cType']; ?>" class="doigt"
			idProj="<?php echo $comm['projId']; ?>"
			dept="<?php echo $comm['deptName']; ?>"
			tplDept="<?php echo $comm['tplDept']; ?>"
			<?php if($comm['cType'] == 'retake'): ?>
				idSeq="<?php echo $comm['seqId']; ?>"
				idShot="<?php echo $comm['shotId']; ?>"
			<?php elseif($comm['cType'] == 'retake_scene'): ?>
				idScene="<?php echo $comm['sceneId']; ?>"
			<?php else: ?>
				nameAsset="<?php echo $comm['assetName']; ?>"
				pathAsset="<?php echo $comm['assetPath']; ?>"
			<?php endif; ?>>

			<td class="hideMsg w20" title="Hide (mark read)">&radic;</td>
			<td class="commentMsg w80"><?php echo $comm['sender']; ?></td>
			<td class="commentMsg w300">
				<?php
				$what = ($comm['cType'] == 'retake') ? mb_strtoupper(L_SHOT) : mb_strtoupper(L_ASSET);
				$what = ($comm['cType'] == 'retake_scene') ? mb_strtoupper(L_SCENE) : $what;
				$col = 'colorDiscret1';
				if ($comm['cType'] == 'retake_asset') $col = 'colorDiscret2';
				if ($comm['cType'] == 'retake_scene') $col = 'colorDiscret3';
				echo '<span class="'.$col.'">'.$what.' <i>['.$comm['deptName'].']</i></span><br />';
				echo $comm['projTitle'].' / ';
				if ($comm['cType'] == 'retake')
					echo $comm['seqTitle'].' / '.$comm['shotTitle'];
				elseif($comm['cType'] == 'retake_asset')
					echo $comm['assetCateg'].' / '.$comm['assetName'];
				elseif($comm['cType'] == 'retake_scene')
					echo $comm['sceneName'];
				?>
			</td>
			<td class="commentMsg w20"></td>
			<td class="commentMsg"><?php echo $comm['comment']; ?></td>
			<td class="commentMsg w80"><?php echo $comm['date']; ?></td>
		</tr>
	<?php endforeach; ?>
	</table>
</div>