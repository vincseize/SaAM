<?php
@session_start(); // 2 lignes à placer toujours en haut du code des pages
require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
if ($_SESSION['user']->isVisitor()) die('{"error":"error", "message":"actions shots : Access denied."}');
require_once ('url_fcts.php');
$plugUrl = getSaamURL() . "/plugins/drawtool/"; ?>

<link href="<?php echo $plugUrl; ?>drawtool.css?v=7" rel="stylesheet" type="text/css">

<?php
extract($_GET);
$pubUrl  = getSaamURL().'/'.FOLDER_DATA_PROJ.$pubFile;
$pubFile = INSTALL_PATH.FOLDER_DATA_PROJ.$pubFile;
if (!file_exists($pubFile))
	die("<div class='ui-widget ui-state-error ui-corner-all' id='msgSave'><h3>Drawtool error:</h3>Image file not found : '".basename($pubFile)."'<p>Hit <b>ESCAPE</b> key to go back.</p></div>");

$size = getimagesize($pubFile);
$mime = $size['mime'];
if (!preg_match('/jpeg|png/', $mime))
	die("<div class='ui-widget ui-state-error ui-corner-all' id='msgSave'><h3>Drawtool error:</h3>File type ($mime) not allowed ! Only <b>JPEG</b> or <b>PNG</b> are supported.<p>Hit <b>ESCAPE</b> key to go back.</p></div>");

if (!isset($Wmax))
	$Wmax = 1150;
if (!isset($Hmax))
	$Hmax = 750;
$wX = $size[0];
$wY = $size[1];
$needResize = false;

if ($wX > $Wmax) {
	$newW = $Wmax;
	$newH = round($wY * ($Wmax / $wX));
	$needResize = true;
}
elseif ($wY > $Hmax) {
	$newW = round($wX * ($Hmax / $wY));
	$newH = $Hmax;
	$needResize = true;
}
if (@$newH > $Hmax) {
	$newW = round($wX * ($Hmax / $wY));
	$newH = $Hmax;
}

if ($needResize) {
	if ($mime == 'image/jpeg')
		$origImg = imagecreatefromjpeg($pubFile);
	if ($mime == 'image/png')
		$origImg = imagecreatefrompng($pubFile);
	$tmpImg = imagecreatetruecolor($wX, $wY);
	imagecopyresampled($tmpImg, $origImg, 0, 0, 0, 0, $newW, $newH, $wX, $wY);
	ob_start();
		imagejpeg($tmpImg);
		$contents = ob_get_contents();
	ob_end_clean();
	imagedestroy($origImg);
	imagedestroy($tmpImg);
	$dataUri = base64_encode($contents);
	$wX = $newW;
	$wY = $newH;
}
else
	$dataUri = base64_encode(file_get_contents($pubFile));

?>

<script src="<?php echo $plugUrl; ?>jsDep/_Base.js" type="text/javascript"></script>
<script src="<?php echo $plugUrl; ?>jsDep/CanvasWidget.js" type="text/javascript"></script>
<script src="<?php echo $plugUrl; ?>jsDep/CanvasPainter.js" type="text/javascript"></script>
<script src="<?php echo $plugUrl; ?>jsDep/CPWidgets.js" type="text/javascript"></script>
<script src="<?php echo $plugUrl; ?>jsDep/CPDrawing.js" type="text/javascript"></script>
<script type="text/javascript">
	var chooserWidgets_left = <?php echo $wX + 100; ?>;
	var pubFile  = "<?php echo $pubFile; ?>";
	var dataFond = "<?php echo urlencode($dataUri); ?>";
	var Wmax	 = <?php echo (int)$Wmax; ?>;
	var Hmax	 = <?php echo (int)$Hmax; ?>;
</script>
<script type="text/javascript" src="<?php echo $plugUrl; ?>drawtool.js?v=2"></script>

<div class="ui-widget" id="drawToolContainer">
	<div id="brushes">
		<div class="ctrl_btn brushBtn selected" brushNo="1" title="Brush 1"><img src="<?php echo $plugUrl; ?>icons/i_brush1.gif" /></div>
		<div class="ctrl_btn brushBtn" brushNo="0" title="Brush 2"><img src="<?php echo $plugUrl; ?>icons/i_brush2.gif" /></div>
		<br />
		<div class="ctrl_btn brushBtn" brushNo="3" title="Rectangle"><img src="<?php echo $plugUrl; ?>icons/i_rectangle.gif" /></div>
		<div class="ctrl_btn brushBtn" brushNo="4" title="Circle"><img src="<?php echo $plugUrl; ?>icons/i_circle.gif" /></div>
		<br />
		<div class="ctrl_btn brushBtn" brushNo="2" title="Line"><img src="<?php echo $plugUrl; ?>icons/i_line.gif" /></div>
		<hr>
		<div class="ctrl_btn" onclick="undoDraw()" title="Undo"><img src="<?php echo $plugUrl; ?>icons/i_undo.gif" /></div>
		<div class="ctrl_btn" onclick="redoDraw()" title="Redo"><img src="<?php echo $plugUrl; ?>icons/i_redo.gif" /></div>
		<br />
		<div class="ctrl_btn" onclick="clearDraw()" title="Clear ALL"><img src="<?php echo $plugUrl; ?>icons/i_clear.gif" /></div>
		<hr>
		<div class="ctrl_btn" onclick="saveCanvas()" title="Save drawing and close drawtool"><img src="<?php echo $plugUrl; ?>icons/i_save.gif" /></div>
		<div class="ctrl_btn" onclick="closeDrawtool()" title="Cancel and close drawtool"><img src="<?php echo $plugUrl; ?>icons/i_close.gif" /></div>
	</div>
	<div id="logoDT">
		<img src="plugins/drawtool/logo.png" width="70" />
	</div>

	<canvas id="canvasDrawtool" style="background-image:url(<?php echo "data:$mime;base64,".$dataUri;?>);"  width="<?php echo $wX; ?>" height="<?php echo $wY; ?>"></canvas>
	<canvas id="canvasInterface" width="<?php echo $wX; ?>" height="<?php echo $wY; ?>"></canvas>
	<?php if ($needResize): ?>
		<div style="margin-top:<?php echo $wY+15; ?>px; margin-left: 90px;" class="ui-state-disabled">
			Notice: this image have been resized (to <?php echo $wX; ?>x<?php echo $wY; ?> px.), to fit the drawing canvas.
			The original image size is <?php echo $size[0]; ?> x <?php echo $size[1]; ?> px.
		</div>
	<?php endif; ?>


	<div id="chooserWidgets" style="left: <?php echo $wX + 100; ?>px;">
		<canvas id="colorChooser" width="275" height="80"></canvas>
		<canvas id="lineWidthChooser" width="275" height="80"></canvas>
	</div>
	<div id="addMessageFromDraw" style="left: <?php echo $wX + 100; ?>px;">
		<textarea placeholder="Write a comment here if needed..."></textarea>
	</div>
</div>

<div class="ui-widget ui-corner-all hide" id="msgSave"></div>