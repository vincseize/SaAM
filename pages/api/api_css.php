<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	if (!$_SESSION['user']->isDev()) die();

$list_css = array(
	"colorErreur",
	"colorOk",
	"colorPage",
	"colorDiscret",
	"colorDiscret1",
	"colorDiscret2",
	"colorMid",
	"colorSoft",
	"colorHard",
	"colorDark",
	"colorErrText",
	"colorBtnFake",
	"colorActiveFolder",
	"activeShotCenter",
	"inactiveShotCenter",
); ?>

<link rel="stylesheet" type="text/css" href="_RECETTE/css/recette.css">

<script>
	function hex(x) {
		var hexDigits = ["0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f"];
		return isNaN(x) ? "00" : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16];
	}
	function rgb2hex(rgb) {
		rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
		return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
	}
	$(function() {
		$('.classLine').each(function(i,e){
			var theClass = '#class_'+$(e).attr('theClass');
			var rgb  = $(theClass).css('color');
			var hexa = rgb2hex(rgb);
			$(theClass).css('background-color', rgb);
			if ($(this).attr('typeVal') == 'rgb')
				$(this).val(rgb);
			else
				$(this).val(hexa);
		});
	});
</script>

<div class="stageContent pad5">
	<h3>Css infos</h3>

	<table border="0" class="tableAPIcss">
		<tbody bgcolor="black">
			<th>CSS class</th>
			<th>human color</th>
			<th>hexa</th>
			<th>rgb</th>
		</tbody>

		<?php foreach ($list_css as $class) : ?>
			<tr class='trAPIcss'>
				<td width='200px' class='tdAPIcss'>
					<div><?php echo $class; ?></div>
				</td>
				<td width='200px' align='middle'>
					<div id='class_<?php echo $class; ?>' class='inline mid <?php echo $class; ?>' style='width:25px; height:15px'></div>
					<div class="inline mid big marge30l <?php echo $class; ?>">TEXT</div>
				</td>
				<td>
					<div><input class="classLine" typeVal="hexa" theClass='<?php echo $class; ?>' value='hexa'></div>
				</td>
				<td>
					<div><input class="classLine" typeVal="rgb" theClass='<?php echo $class; ?>' value='rgb'></div>
				</td>
		  </tr>
		<?php endforeach; ?>

	</table>
</div>
