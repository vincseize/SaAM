<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	require_once ('dates.php' );
	require_once ('vignettes_fcts.php');

	if (!$_SESSION['user']->isDev()) die();		// secu status


	// Listing projects
	$table_fields = Liste::getRows(TABLE_PROJECTS);			// recup noms columns
	$l = new Liste();
	$project_list = $l->getListe(TABLE_PROJECTS,'*');		// recup values projets
	$n_projects = count($project_list);
	$userID = $_SESSION["user"]->getUserInfos('id');
 	$userStatus = $_SESSION["user"]->getUserInfos('status');
    $user_list = $l->getListe(TABLE_USERS,'*');				// recup values users

?>


<link rel="stylesheet" type="text/css" href="_RECETTE/css/recette.css">

<font class="mini">

<table class="listForRecette">
	<thead>
		<tr>
                    <th><span class='colorDiscret'><?php echo $n_projects ;?></span></th>
			<?php
			foreach ($table_fields as $t) {

				$ts = $t;

				if($t=='ID_creator') $ts='Ic';
				if($t=='project_type') $ts='type';
				if($t=='description') $ts='desc.';
				if($t=='nomenclature') $ts='nomc.';
				if($t=='demo') $ts='D.';
				if($t=='position') $ts='p';
				if($t=='archive') $ts='A.';
				if($t=='hide') $ts='H.';
				if($t=='lock') $ts='L.';
				if($t=='deleted') $ts='D.';
				if($t=='progress') $ts='prog.';
				if($t=='reference') $ts='ref.';
				if($t=='softwares') $ts='soft.';

				echo "<th title='$t'>$ts</th>";

			}
			?>
            <th></th>	<!--pdf-->
			<th></th>	<!--masterfile-->
			<th></th>	<!--delete-->
		<tr>
	</thead>
	<tbody>
	<?php
	if (!empty($project_list)) {

		$icone_pdf = "<img src='gfx/icones/pdf.png' title='generate pdf'>";
		$icone_xml = "<img src='gfx/icones/xml.png' title='generate masterfile'>";
		//
		$i=0;
		foreach ($project_list as $v) {
			if ($i%2==1) $bgTR = '';
			else $bgTR = 'TRmodulo';
			$i++;

			$vignette = check_proj_vignette_ext($v['id'], $v['title']);

			$deptsArr = @json_decode($v['dpts'], true);
			$dpts = '';
			if (is_array($deptsArr) AND !empty($deptsArr)) {
				$dpts .="<select id='dpts'>";
				foreach ($deptsArr as $d) $dpts .= "<option style='width:70px'>".$d."</option>";
				$dpts ."</select>";
			}

                        $teamArr = array();
                        $team_recup = @json_decode($v['equipe'], true);
                        if (is_array($team_recup)) {
                                foreach ($team_recup as $tr) {
                                    foreach ($user_list as $u) {
                                        if($u['id']==$tr){
                                            array_push ($teamArr,$u['pseudo']);
                                        }
                                    }
                                }
                        }
                        $team = '';
			if (is_array($teamArr) AND !empty($teamArr)) {
				$team .="<select id='team'>";
				foreach ($teamArr as $t) $team .= "<option style='width:70px'>".$t."</option>";
				$team ."</select>";
			}

                        $supervisor_pseudo = '';
                        $supervisor_array = array();
                        foreach ($user_list as $s) {
                            if($s['id']==$v['supervisor']){
                                array_push ($supervisor_array,$s['pseudo']);
                            }
                        }
                        if(!empty($supervisor_array)){
                            $supervisor_pseudo = $supervisor_array[0];
                        }

			$softArr = @json_decode($v['softwares'], true);
			$softwares = '';
			if (is_array($softArr) AND !empty($softArr)) {
				$softwares .="<select id='sf'>";
				foreach ($softArr as $sf) $softwares .= "<option style='width:70px'>".$sf."</option>";
				$softwares ."</select>";
			}

                        echo "
				<tr class='".$bgTR."'>
                                        <td><img src='$vignette' width='50px height='35px'></td>
					<td>".$v['id']."</td>
					<td>".$v['ID_creator']."</td>
                                        <td>".$v['fps']."</td>
                                        <td>".substr($v['nomenclature'],0, 6)."</td>
                                        <td>".$v['project_type']."</td>
					<td>".$dpts."</td>
					<td>".$v['position']."</td>
					<td>".$supervisor_pseudo."</td>
					<td>".substr($v['title'],0, 5)."</td>
					<td>".substr($v['description'],0, 4)."...</td>
					<td>".substr($v['director'],0, 5)."</td>
					<td>".$team."</td>
					<td>".substr($v['company'],0, 5)."</td>
					<td>".substr($v['date'],5, 5)."</td>
                    <td>".substr($v['update'],5, 5)."</font></td>
					<td>".substr($v['deadline'],5, 5)."</td>
					<td>".$v['progress']."</td>
					<td>".$v['demo']."</td>
					<td>".$v['hide']."</td>
					<td>".$v['lock']."</td>
					<td>".$v['archive']."</td>
					<td>".$v['deleted']."</td>
					<td>".substr($v['reference'],0,5)."</td>
					<td>".$softwares."</td>";
					echo "<td><span class='genPdfProject doigt' idProj='".$v['id']."' titleProj='".$v['title']."'>".$icone_pdf."</span></td>";
					echo "<td><span class='genMasterXml doigt' idProj='".$v['id']."' titleProj='".$v['title']."'>".$icone_xml."</span></td>";
					echo "<td class='colorErreur'>";
					if ((($v['project_type']=='test') || ($v['project_type']=='demo')) || $v['ID_creator'] == $userID  || $userStatus == 9){
						if( $v['id'] != '1'){ // id=1 est le project demo
							echo '<span class="deleteProj doigt" idProj="'.$v['id'].'" title="'.$v['title'].'">X</span>';
						}
					}
					echo "</td>";

					echo "</tr>";
		}
	}


	?>
	</tbody>
</table>

</font>


