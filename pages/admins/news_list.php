<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	require_once ('dates.php' );

	if (!($_SESSION['user']->isSupervisor() || $_SESSION['user']->isDemo())) die('list news : Access denied.');
	$userId = $_SESSION['user']->getUserInfos('id');
	$userDev = $_SESSION['user']->isDev();

	$l = new Liste();
	$news_list = $l->getListe(TABLE_NEWS,'*', 'new_date', 'DESC');
	$users_list= $l->getListe(TABLE_USERS, 'id, pseudo');
	$userList = Array();
	foreach($users_list as $user)
		$userList[$user['id']] = $user['pseudo'];
?>

<script>
	var age_MAX_NEWS  = <?php echo AGE_NEWS_MAX; ?>;
	var home_MAX_NEWS = <?php echo HOME_MAX_NEWS ?>;
	$(function(){
		$('.bouton').button();
	});
</script>

<script src="ajax/admin_news.js"></script>

<div class="stageContent pad5">
	<h2>Gestion des news</h2>


	<?php
	if (!is_array($news_list)) echo 'Pas de nouvelle enregistrée. Ajoutez une nouvelle en cliquant sur le menu "Ajouter", ci-dessus.';
	else {
		$n = 1;
		foreach ($news_list as $i => $new) {
			$classVisible = 'ui-state-activeFake';
			if (!$new['visible'] || $new['visible'] == 0) $classVisible = '';

			$classNew = 'fondSect3';
			$dateNew = SQLdateConvert($new['new_date'], 'timeStamp');
			// Si la new a moins de AGE_NEWS_MAX jours, qu'elle est visible et qu'il y a moins de 4 news déjà en home, on la surbrille
			if (($dateNew >= time()-AGE_NEWS_MAX || $n <= HOME_MAX_NEWS) && $classVisible != '') {
				$classNew = 'fondPage bordHi';
				$n++;
			}
			if ($new['id'] == 1) $classNew .= ' sticky';
			$affDateNew = date('d/m/Y à H\hi', $dateNew);

			$creatorPseudo = $userList[$new['ID_creator']];
			if ($i == 1) {
				echo '<div id="listeNews">';
				$openDiv = true;
			}

			echo '
				<div class="ui-corner-all '.$classNew.' adminNewsNew" visible="'.$new['visible'].'">
					<div class="floatR nano" idNews="'.$new['id'].'">
						<button class="bouton marge10r hideNews '.$classVisible.'"><span class="ui-icon ui-icon-lightbulb"></span></button>';
			if ($new['id'] != 1 && ($new['ID_creator'] == $userId || $userDev))
				echo '  <button class="bouton modNews"><span class="ui-icon ui-icon-pencil"></span></button>
						<button class="bouton delNews"><span class="ui-icon ui-icon-trash"></span></button>';
			else if ($new['id'] != 1) echo '<span class="terra"><i>by <b>'.$creatorPseudo.'</b></i></span>';
			if ($new['id'] == 1 && !$_SESSION['user']->isDemo())
				echo '  <button class="bouton modNews"><span class="ui-icon ui-icon-pencil"></span></button>';
			echo '  </div>
					<div class="inline mid pad5 gros gras titleNew" idNews="'.$new['id'].'">'.stripslashes($new['new_title']).'</div>
					<br />';
			if ($new['id'] == 1)
				echo '<span class="mini colorMid"><i>sticky</i></span>';
			else echo '<span class="mini colorMid dateNew" timeNew="'.$dateNew.'" idNews="'.$new['id'].'"><i>'.$affDateNew.'</i></span>';
				echo '<div class="textNew" idNews="'.$new['id'].'">'.stripslashes($new['new_text']).'</div>
				</div>
			';
		}
		if (@$openDiv) echo '</div>';
	}
	?>
</div>