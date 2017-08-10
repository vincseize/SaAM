<?php
@session_start(); // 2 lignes à placer toujours en haut du code des pages
require_once ($_SESSION['INSTALL_PATH_INC'] . "/checkConnect.php" );
require_once ("vignettes_fcts.php" );

// Récupère la liste des utilisateurs existants
$l = new Liste();
$l->getListe(TABLE_USERS, '*', 'pseudo', 'ASC');
$users_list = $l->simplifyList('id');
$l->resetFiltre();

$l->getListe(TABLE_PROJECTS, 'id,ID_creator,title,hide', 'id', 'ASC', Projects::PROJECT_ARCHIVE, '=', 0 );
$projects_list = $l->simplifyList();
$l->resetFiltre();

// Récupère la liste des status users lisibles
$statusList = $_SESSION['STATUS_LIST'];

// Récupère la liste des compétences disponibles
try {
	$inf = new Infos(TABLE_CONFIG);
	$inf->loadInfos('version', SAAM_VERSION);
	$compList = json_decode($inf->getInfo('available_competences'));
}
catch (Exception $e) { $compList = $_SESSION['CONFIG']['AV_COMPETENCES']; }

try {
	$acl = new ACL(@$_SESSION['user']); ?>

<script>
	$(function() {
		$('.bouton').button();
		$('#filtres').show();
		$('#filtreContent').buttonset().hide();
		$('#filtreToggle').children('button').removeClass('ui-state-error');
	});
</script>

<script src="ajax/mod_user.js"></script>


<div class="stageContent">
	<h3 class="marge10l"><?php echo mb_convert_case(L_BTN_ADMIN_USERS, MB_CASE_UPPER); ?></h3>

	<div id="usersList">

		<table class="tableListe ui-corner-all petit">
			<tr class="ui-widget-content headerUsersList">
				<th class="w100">Pseudo</th>
				<th class="w50" title="Last Activity">L.A.</th>
				<th class="w150"><?php echo L_NAME; ?></th>
				<th class="w100"><?php echo L_LEVEL; ?></th>
				<th class="w300" colspan="2"><?php echo L_PROJECTS; ?></th>
				<th class="w200 center">Actions</th>
			</tr><?php
		foreach($users_list as $idUser => $user) :
			// ACL checks
			if ($idUser != $_SESSION['user']->getUserInfos(Users::USERS_ID) && !$acl->check('ADMIN_USERS_VIEW', "user:".$user[Users::USERS_CREATOR]))
				continue;
			$ACLcheckAssign = ($idUser == $_SESSION['user']->getUserInfos(Users::USERS_ID) || $acl->check('ADMIN_USERS_ASSIGN', "user:".$user[Users::USERS_CREATOR]));
			$ACLcheckModify = ($idUser == $_SESSION['user']->getUserInfos(Users::USERS_ID) || $acl->check('ADMIN_USERS_MODIFY', "user:".$user[Users::USERS_CREATOR]));
			$disabledBtns	= ($_SESSION['user']->isDemo()) ? 'ui-state-disabled' : '';
			// Récup Avatar image, formate la date de dernière connexion, et donne une couleur au status de l'utilisateur
			$avatar = check_user_vignette_ext($idUser, $user[Users::USERS_LOGIN]);
			$lastConx = ($user[Users::USERS_DATE_LASTCON] == 0) ? '<span class="ui-state-disabled">never</span>' : date('d/m <\i>H:i</\i>', $user[Users::USERS_DATE_LASTACTION]);
			switch ($user[Users::USERS_STATUS]) {
				case '9':
					$colorLevel = '#f00'; break;
				case '8':
					$colorLevel = '#FCB553'; break;
				case '5':
					$colorLevel = '#2975D1'; break;
				default:
					$colorLevel = '#aaa'; break;
			}
			// Formate les compétences de l'user
			$competences = '';
			$userComps = json_decode($user[Users::USERS_COMPETENCES]);
			if (is_array($userComps)) {
				foreach ($userComps as $comp)
					$competences .= '<li class="gras">'.$comp.'</li>';
			}
			// Formate les projets de l'user
			$myProjects = '';
			$userProjs  = json_decode($user[Users::USERS_MY_PROJECTS]);
			if (is_array($userProjs)) {
				foreach ($userProjs as $idProjUser) {
					if (isset($projects_list[$idProjUser]))
						$myProjects .= $projects_list[$idProjUser][Projects::PROJECT_TITLE].', ';
				}
				$myProjects = substr($myProjects, 0, -2);
			}
			// Compte le nombre de shots assignés à l'user
			$countShots = count(json_decode($user[Users::USERS_MY_SHOTS]));
			// Check si l'user est visible par l'user actuel : vérif si au moins un projet est commun
			if (!$ACLcheckAssign && count(array_intersect($_SESSION['user']->getUserProjects(), $userProjs)) == 0)
				continue; ?>

			<tr class="<?php echo ($user[Users::USERS_ACTIVE]) ? 'ui-state-default' : 'ui-state-disabled'; ?> doigt" idUser="<?php echo $idUser; ?>"
				loginUser="<?php echo $user[Users::USERS_LOGIN]; ?>"
				pseudoUser="<?php echo $user[Users::USERS_PSEUDO]; ?>"
				nameUser="<?php echo $user[Users::USERS_PRENOM].' '.$user[Users::USERS_NOM]; ?>"
				status="<?php echo $_SESSION['STATUS_LIST'][$user[Users::USERS_STATUS]]; ?>">
				<td><?php echo $user[Users::USERS_PSEUDO]; ?></td>
				<td class="petit"><?php echo $lastConx; ?></td>
				<td><?php echo $user[Users::USERS_PRENOM].' '.$user[Users::USERS_NOM]; ?></td>
				<td><b style="color:<?php echo $colorLevel; ?>;"><?php echo $_SESSION['STATUS_LIST'][$user[Users::USERS_STATUS]]; ?></b></td>
				<td colspan="2" class="colorBtnFake"><?php echo $myProjects; ?></td>
				<td class="rightText micro actions nowrap <?php echo $disabledBtns; ?>">
					<a href="mailto:<?php echo $user[Users::USERS_MAIL]; ?>" title="contact"><button class="bouton contactUser"><span class="ui-icon ui-icon-mail-closed"></span></button></a>
					&nbsp;&nbsp;&nbsp;&nbsp;<?php
					if ($ACLcheckAssign): ?>
						<button class="bouton ui-state-highlight assignUserBtn" title="<?php echo L_ASSIGNMENTS.' '.L_PROJECTS; ?>"><span class="ui-icon ui-icon-shuffle"></span></button>
						&nbsp;&nbsp;&nbsp;&nbsp;<?php
					endif;
					if ($ACLcheckModify) : ?>
						<button class="bouton <?php echo ($user[Users::USERS_ACTIVE]) ? 'ui-state-activeFake' : ''; ?> activeUserBtn" title="Enable / Disable"><span class="ui-icon ui-icon-lightbulb"></span></button>
						<button class="bouton modUserBtn" title="<?php echo L_MODIFY; ?>"><span class="ui-icon ui-icon-pencil"></span></button><?php
						if ($user[Users::USERS_STATUS] != Users::USERS_STATUS_ROOT): ?>
							<button class="bouton ui-state-default delUserBtn" title="<?php echo L_DELETE; ?>"><span class="ui-icon ui-icon-trash"></span></button><?php
						else : ?>
							<div class="inline" style="margin-right:4.9em;">&nbsp;</div><?php
						endif;
					endif; ?>
				</td>
			</tr>
			<tr class="fondSect3 colorPage detailUser hide">
				<td class="nowrap"><img src="<?php echo $avatar; ?>" width="100" /></td>
				<td class="top nowrap">Login<br /><br /><b><?php echo $user[Users::USERS_LOGIN]; ?></b></td>
				<td class="top nowrap"></td>
				<td class="top nowrap"></td>
				<td class="top nowrap">Email<br /><br /><b><?php echo $user[Users::USERS_MAIL]; ?></b></td>
				<td class="top nowrap">compétences<br /><br /><?php echo $competences; ?></td>
				<td class="top nowrap">plans<br /><br /><b><?php echo $countShots; ?></b></td>
			</tr><?php
		endforeach; ?>
		</table>
	</div>
</div>


<div class="petit" id="modUserModal" title="<?php echo L_MODIFY.' '.L_USER; ?>" style="display:none;">
	<div class="margeTop1">
		<div class="inline mid w150 marge30l panavignette"><img src="gfx/novignette/novignette_user.png" width="150" id="vignette_user" /></div>
	</div>
	<input type="hidden" id="id" class="modUser" />
	<div class="margeTop10">
		<div class="inline mid w150 marge30l">Login: </div>
		<input type="text" id="login" class="noBorder pad3 ui-corner-top w300 fondSect3 modUser requiredField" title="Login" />
		<div class="inline mid ui-state-error noBG noBorder"><span class="ui-icon ui-icon-notice"></span></div>
	</div>
	<div class="margeTop1">
		<div class="inline mid w150 marge30l"><?php echo L_PASSWORD; ?>: </div>
		<input type="password" id="passwd" class="noBorder pad3 w300 fondSect3 modUser" title="Enter new password ONLY if you want to change it." />
		<div class="inline mid mini ui-state-disabled w200">4 lettres minimum</div>
	</div>
	<div class="margeTop1">
		<div class="inline mid w150 marge30l">e-mail address: </div>
		<input type="text" id="mail" class="noBorder pad3 w300 fondSect3 modUser requiredField" title="Email" />
		<div class="inline mid ui-state-error mini noBG noBorder"><span class="ui-icon ui-icon-notice"></span></div>
	</div>
	<div class="margeTop1">
		<div class="inline mid w150 marge30l">Pseudo: </div>
		<input type="text" id="pseudo" class="noBorder pad3 w300 fondSect3 modUser requiredField" title="Pseudo" />
		<div class="inline mid ui-state-error noBG noBorder"><span class="ui-icon ui-icon-notice"></span></div>
	</div>
	<div class="margeTop1">
		<div class="inline mid w150 marge30l"><?php echo L_FIRST_NAME; ?>: </div>
		<input type="text" id="prenom" class="noBorder pad3 w300 fondSect3 modUser" title="First Name" />
	</div>
	<div class="margeTop1">
		<div class="inline mid w150 marge30l"><?php echo L_NAME; ?>: </div>
		<input type="text" id="nom" class="noBorder pad3 ui-corner-bottom w300 fondSect3 modUser" title="Name" />
	</div>
	<div class="margeTop10">
		<div class="inline mid w150 marge30l"><?php echo L_LEVEL; ?>: </div>
		<div class="inline mid mini">
			<select id="status" class="w300 modUser">
				<?php
				foreach ($statusList as $status => $statusName) {
					if ($status != 9)
						echo '<option class="mini" value="'.$status.'">'.$statusName.'</option>';
				}
				?>
			</select>
		</div>
	</div>
	<div class="margeTop10">
		<div class="inline top margeTop5 w150 marge30l"><?php echo L_SKILLS; ?>: </div>
		<div class="inline mid mini">
			<select id="competences" multiple="multiple" class="w300 modUser">
				<?php
				foreach ($compList as $comp)
					echo '<option class="mini" value="'.$comp.'">'.strtoupper($comp).'</option>';
				?>
			</select>
		</div>
	</div>
</div>

<div class="petit" id="assignUserModal" title="<?php echo L_ASSIGN.' '.L_USER; ?>" style="display:none;">

	<div class="margeTop10">
		<div class="inline mid w150 marge30l"><?php echo L_PROJECTS; ?>: </div>
		<div class="inline mid mini">
			<input type="hidden" id="idUser" class="assignUser" />
			<select id="my_projects" multiple="multiple" class="w300 assignUser"><?php
				foreach ($projects_list as $idProj => $proj):
					if ($idProj != 1 && $_SESSION['user']->getUserInfos(Users::USERS_STATUS) < 7 && $proj[Projects::PROJECT_ID_CREATOR] != $_SESSION['user']->getUserInfos(Users::USERS_ID)) continue; ?>
					<option class="mini" value="<?php echo $idProj; ?>"><?php echo $proj[Projects::PROJECT_TITLE]; ?></option><?php
				endforeach; ?>
			</select>
		</div>
	</div>
</div><?php
}
catch (Exception $e) { die('<span class="colorErreur">'. $e->getMessage().'</span>'); }
?>