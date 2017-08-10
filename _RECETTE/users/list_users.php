<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	require_once ('dates.php' );
	require_once ('vignettes_fcts.php' );

	if (!$_SESSION['user']->isDev()) die();		// secu status

	// Listing users
	$table_fields = Liste::getRows(TABLE_USERS);			// recup noms columns
	$l = new Liste();
	$user_list = $l->getListe(TABLE_USERS);					// recup values users
	$n_users = count($user_list);
	$status_list = $_SESSION['STATUS_LIST'];				// recup values status
	$userID = $_SESSION["user"]->getUserInfos('id');

	// Listing projects
	$p = new Liste();
	$projects_list =  $p->getListe(TABLE_PROJECTS);			// recup values users
?>


<link rel="stylesheet" type="text/css" href="_RECETTE/css/recette.css">

<font class="mini">

<table class="listForRecette">
	<thead>
		<tr>
			<th><span class='colorDiscret'><?php echo $n_users ;?></span></th>
			<?php
			foreach ($table_fields as $t) {
				$ts = $t;
				if($t=='lang') $ts='lg.';
				if($t=='vcard') $ts='vcd.';
				if($t=='ID_creator') $ts='Ic';
				if($t=='my_projects') $ts='proj.';
				if($t=='my_sequences') $ts='seq.';
				if($t=='my_shots') $ts='shots';
				if($t=='my_retakes') $ts='ret.';
				if($t=='my_news') $ts='news';
				if($t=='theme') $ts='th.';
				if($t=='receiveMails') $ts='rM.';
				if($t=='date_inscription') $ts='ins.';
				if($t=='date_last_connexion') $ts='last conn.';
				if($t=='date_last_action') $ts='last act.';
				echo "<th title='$t'>$ts</th>";
			}
			?>
            <th></th>

		<tr>
	</thead>
	<tbody>
	<?php
	if (!empty($user_list)) {
		$i=0;
		foreach ($user_list as $v) {
			if ($i%2==1) $bgTR = '';
			else $bgTR = 'TRmodulo';
			$i++;

			$id_status = $v['status'];
			$status = '';
			if ($id_status!='') {
				$status = $status_list[$id_status];
			}
			$img = "<img src='gfx/novignette/novignette_user_recette.png' width='50px height='35px'>";

			$compArr = @json_decode($v['competences'], true);
			$competences = '';
			if (is_array($compArr)) {
				$competences .="<select id='competences'>";
				foreach ($compArr as $d) $competences .= "<option style='width:70px'>".$d."</option>";
				$competences ."</select>";
			}

			$projArr = @json_decode($v['my_projects'], true);
			$my_projects = '';
			if (is_array($projArr)) {
				$my_projects .="<select id='my_projects'>";
				foreach ($projArr as $mp) {
					foreach ($projects_list as $pl) {
						if($pl['id']==$mp){
							$my_projects .= "<option style='width:70px'>".$pl['title']."</option>";
						}
					}
				}
				$my_projects ."</select>";
			}

			$img = '<img src="'.check_user_vignette_ext($v['id'], $v['login']).'" height="25px" />';
			if ($v['id']=='1'){
				$img = "<img src='gfx/novignette/novignette_demo_user.png' height='25px' />";
			}
			echo "
				<tr class='".$bgTR."'>
					<td class='center'>".$img."</td>
					<td>".$v['id']."</td>
					<td>".$v['ID_creator']."</td>
					<td>".$v['login']."</td>
					<td>".substr($v['passwd'], 0, 4)."...</td>
					<td>".$status."</td>
					<td>".$v['pseudo']."</td>
					<td>".$v['nom']."</td>
					<td>".$v['prenom']."</td>
					<td>".$competences."</td>
					<td>".substr($v['mail'], 0, 3)."...@</td>
					<td>".$v['vcard']."</td>
					<td>".$v['lang']."</td>
					<td>".$v['theme']."</td>
					<td>".$v['receiveMails']."</td>
					<td>".$my_projects."</td>
					<td>".$v['my_sequences']."</td>
					<td>".$v['my_shots']."</td>
					<td>".@$v['my_news']."</td>
					<td>".substr(date('m-d', $v['date_inscription']),0, 5)."</td>
					<td>".substr(date('m-d', $v['date_last_connexion']),0, 5)."</td>
					<td>".substr(date('m-d', $v['date_last_action']),0, 5)."</td>

					<td class='colorErreur'>";
			if ($v['ID_creator'] == $userID){
				echo '<span class="deleteUser doigt" idUser="'.$v['id'].'" title="'.$v['login'].'">X</span>';
			}
				echo "</td>
				</tr>";
		}
	}
	?>
	</tbody>
</table>

</font>
