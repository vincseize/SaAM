<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH'].'/inc/checkConnect.php' );

	$l = new Liste();
    $news_list = $l->getListe(TABLE_NEWS,'*', 'new_date', 'DESC');
?>

<script>
	$(function(){
		$('#modalNewsContent').slimScroll({
			position: 'right',
			height: '380px',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		});
	});
</script>

<div id="modalNewsContent">

	<div class="gros gras"><?php echo strtoupper(L_NEWS); ?></div>
	<p></p>
	<?php foreach($news_list as $new) :
		$dateNew = SQLdateConvert($new['new_date'], 'timeStamp');
		if ($new['id'] != 1) {
			if ($dateNew <= time()-AGE_NEWS_MAX)  continue;
		}
		$newsClass = ($new['id'] == 1) ? 'fondSect4' : 'fondPage';
		$affDateNew = ($new['id'] == 1) ? '' : date('d/m/Y', $dateNew);
		?>
		<div class="<?php echo $newsClass; ?> ui-corner-all pad3 marge5bot">
			<span class="inline mid doigt ui-icon ui-icon-newwin"></span>
			<span class="inline mid pad5 gros gras"><?php echo stripslashes($new['new_title']); ?></span>
			<span class="inline mid mini colorDiscret dateNew" timeNew="<?php echo $dateNew; ?>"><i><?php echo $affDateNew; ?></i></span>
			<div class="padV5">
				<?php echo stripslashes($new['new_text']); ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>