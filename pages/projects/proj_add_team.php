<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

	$l = new Liste();
	$l->addFiltre('id', '!=', 1);
	$l->getListe(TABLE_USERS,'id, login, pseudo, status, nom, prenom, competences', 'nom');
	$users_list  = $l->simplifyList('id');

	$showModBtns = $_SESSION['user']->isSupervisor();
	$isDemoUser  = $_SESSION['user']->isDemo();
?>

<script>
	$(function() {
		$('.bouton').button();
	});
</script>

<script src="ajax/add_project_team.js"></script>


<div class="stageContent pad5">
	<h2><span id="team_projTitle"></span>: <?php echo mb_convert_case(L_TEAM, MB_CASE_LOWER); ?> </h2>

	<div class="inline top demi petit">
		<div class="ui-widget-content ui-corner-all w9p pad3">
			<div class="floatR nano" id="filtreToggle">
				<button class="bouton"><span class="ui-icon ui-icon-gear"></span></button>
			</div>

			<div id="headerList">
				<div class="pad3 gros" id="headerList_title"><?php echo L_LIST.' '.mb_convert_case(L_USERS, MB_CASE_LOWER); ?></div>
				<div class="petit hide" id="headerList_filtres">
					<?php
					foreach($_SESSION['CONFIG']['AV_COMPETENCES'] as $compet): ?>
						<button class="bouton filtreComp" comp="<?php echo $compet; ?>"><?php echo $compet; ?></button>
					<?php endforeach; ?>
				</div>
			</div>
		</div>


		<div class="pad5 ui-corner-all bordFin bordColInv w9p" style="min-height:350px;" id="artistsList">
			<?php
				foreach ($users_list as $idUser => $userInfos) {
					if (!$isDemoUser && $userInfos['status'] == Users::USERS_STATUS_VISITOR && $userInfos['status'] != Users::USERS_STATUS_DEMO) continue;

					$imgVignette = 'datas/users/'.$idUser.'_'.$userInfos['login'].'/vignette.png';
					if (file_exists('../../'.$imgVignette)){
						$img = '<img src="'.$imgVignette.'"  width="70px" class="hide">';
					}
					else $img = '<img src="gfx/novignette/novignette_user.png" width="70px" class="hide">';

					$compList = json_decode($userInfos['competences']);
					$comps = '';
					if (is_array($compList)) { foreach ($compList as $comp) $comps .= $comp.' '; } else $comps = '';
					echo '<div class="ui-state-default ui-corner-all marge10r shadowOut marge5bot curMove proj_user" idArtist="'.$idUser.'" comp="'.$comps.'">';
						if ($showModBtns) {
							echo '<div class="floatR pico pad3 ui-state-disabled">
									<button class="bouton delUser"><span class="ui-icon ui-icon-trash"></span></button> &nbsp;
									<button class="bouton modUser"><span class="ui-icon ui-icon-pencil"></span></button>
								</div>';
						}
						$nameUser = ($userInfos['nom'] != '') ? $userInfos['prenom'].' '.$userInfos['nom'] : $userInfos['pseudo'] ;
						echo'<div class="inline top pad5 w150">'.$nameUser.'</div>
							 '.$img.'
							 <div class="inline top pad5 w200 colorSoft">';
								if (is_array($compList)) { foreach ($compList as $comp) echo '<i>'.$comp.'</i> '; }
						echo '</div>
					</div>';
				}
			?>
		</div>

	<br /><br /><br />

	</div>

	<div class="demi petit" id="fixedDiv" style="position:absolute; top:53px; right:30px;">
		<div class="ui-widget-content ui-corner-all w9p pad3">
			<div class="pad3 gros" id="headerList_title"><?php echo L_TEAM; ?></div>
		</div>

		<p class="padH5 ui-state-disabled">
			<?php if ($_SESSION["user"]->getUserInfos('lang') == 'fr'): ?>
				Glissez-déposez le personnel dans le cadre ci-dessous :
			<?php else:	?>
				Drag and drop users in the frame below:
			<?php endif; ?>
		</p>
		<div class="ui-corner-all w9p pad5 bordFin bordColInv shadowIn" style="min-height:350px;" id="proj_Team">

		</div>

		<div class="margeTop10 rightText ui-state-disabled submitBtns">
			<br /><br />
			<button class="bouton" id="proj_NEXT_struct"><?php echo mb_convert_case(L_NEXT, MB_CASE_UPPER); ?></button>
			<button class="bouton marge30l" id="proj_DONE"><?php echo mb_convert_case(L_DONE, MB_CASE_UPPER); ?></button>
		</div>

	</div>
</div>