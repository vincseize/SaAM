<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	require_once ('dates.php' );

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
		$('#calendarNav').hide();
		$('.newsDiv:not(.viewedNew)').children('p').hide();
		$('.newsDiv').click(function(){
			$('.newsDiv').removeClass('ui-state-focusFake viewedNew').children('p').hide(transition);
			$('.newsDiv:not(.bordHi)').addClass('fondSect3');

			$(this).removeClass('fondPage fondSect3').addClass('ui-state-focusFake viewedNew').children('p').show(transition);
			$('.bordHi:not(.viewedNew)').addClass('fondPage');

			var stageHeight = $('#stage').height();
			$('.stageContent').slimScroll({
				position: 'right',
				height: stageHeight+'px',
				size: '10px',
				wheelStep: 10,
				railVisible: true
			});
		});
	});
</script>

<div class="stageContent pad5">
	<div id="listeNews">
		<?php
		if (!is_array($news_list)) echo 'Pas de nouvelle, bonne nouvelle !';
		else {
			$n = 1;
			foreach ($news_list as $new) {
				if (!$new['visible'] || $new['visible'] == 0) continue;

				$classNew = 'inline top ui-corner-all colorHard fondSect3';
				$dateNew = SQLdateConvert($new['new_date'], 'timeStamp');
				// Si c'est la sticky
				if ($new['id'] == 1) $classNew = 'floatL marge10r ui-corner-all ui-state-focusFake bordHi newsDiv viewedNew';
				// Si la new a moins de AGE_NEWS_MAX jours, qu'elle est visible et qu'il y a moins de 4 news déjà en home, on la surbrille
				else if ($dateNew >= time()-AGE_NEWS_MAX && $n < HOME_MAX_NEWS) {
					$classNew = 'inline top ui-corner-all colorHard fondPage bordHi';
					$n++;
				}
				$affDateNew = date('\l\e d/m/Y à H\hi', $dateNew);
				if ($new['id'] == 1) $affDateNew = 'sticky';
				else $creatorPseudo = 'by '.$userList[$new['ID_creator']];

				echo '
					<div class="'.$classNew.' newsDiv">
						<span class="floatL ui-icon ui-icon-newwin"></span>
						<span class="pad5 gros gras">'.stripslashes($new['new_title']).'</span> <span class="petit colorMid creatorNew">'.@$creatorPseudo.'</span><br />
						<span class="mini colorMid dateNew" timeNew="'.$dateNew.'" idNews="'.$new['id'].'"><i>'.$affDateNew.'</i></span>
						<p>'.stripslashes($new['new_text']).'</p>
					</div>
				';
			}
		}
		?>
	</div>
</div>