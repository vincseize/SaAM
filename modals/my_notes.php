<?php
@session_start();

if (!isset($_SESSION["user"])) die('No user connected.');

$dontTouchSSID = true;

require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

$ln = new Liste();
$ln->getListe(TABLE_NOTES, '*', 'position', 'DESC', 'ID_user', '=', $_SESSION['user']->getUserInfos(Users::USERS_ID));
$listeNotes = $ln->simplifyList('id');

if (is_array($listeNotes)) :
	foreach ($listeNotes as $note) :
		switch ($note['position']) {
			case 2:		$classNote = 'ui-state-error'; break;
			case 1:		$classNote = 'ui-state-active'; break;
			default:	$classNote = 'fondBlanc'; break;
		} ?>
		<div class="inline gray-layer noBorder ui-corner-all w9p margeTop5 leftText pad3 doigt myNote <?php echo $classNote; ?>" help="vos_notes"
			 idNote="<?php echo $note['id']; ?>">
			<?php echo nl2br(urldecode($note['note'])); ?>
		</div>
	<?php endforeach;
	else: ?>
		<div class="ui-state-disabled margeTop5 noNote">
			<?php echo L_NO_NOTE; ?>
		</div>
<?php endif; ?>

<script>
	$(function(){
		reCalcScrollMyMenu();
		$('.myNote').hover(
			function(){$(this).removeClass('gray-layer');},
			function(){$(this).addClass('gray-layer');}
		);

		$('.myNote').click(function(){
			$('.lien[goto="notes"]').click();
		});

	});
</script>