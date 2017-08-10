<?php

$trucs = '';
$machins ='';

?>

<div class="ui-state-focus mini ui-corner-top pad3 doigt gras" id="arboHeadProj" style="padding:5px 0px 6px 0px;" title="click to display or refresh current view" help="menu_shortcuts">
	<?php echo $titleProj; ?>
</div>
<div class="ui-state-focus petit ui-corner-top pad3 doigt hide" id="arboHeadSeq" title="click to return to... something">
	<span class="inline mid ui-icon ui-icon-arrowthickstop-1-n"></span> <span id="titleSeq"></span>
</div>

<div class="fondSect1 ui-corner-bottom" id="arboSeq">
	<?php echo $trucs; ?>
</div>

<?php echo $machins; ?>
