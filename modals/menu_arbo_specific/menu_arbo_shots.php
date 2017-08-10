<?php

$sequences = '';	$shotsBySeqs ='';

if ($seqList = $p->getSequences()) {
	foreach ($seqList as $seq) {
		if ($seq['hide'] == 1 || $seq['archive'] == 1) continue;
		$sequences	 .= '<div class="bordColInv1 arboItem" idSeq="'.$seq['id'].'">'.$seq['title'].'</div>';
		$shotsBySeqs .= '<div class="fondSect1 ui-corner-bottom arboShots" idProj="'.$idProj.'" idSeq="'.$seq['id'].'">';
		$shotsList = $p->getShots($seq['id'], 'actifs');
		if (is_array($shotsList)) {
			foreach ($shotsList as $shot) {
				$shotTxt = ($shot['title']=='') ? $shot['label'] : $shot['title'] ;
				$shotsBySeqs .= '<div class="bordColInv1 ui-corner-tl ui-corner-br arboItem" idShot="'.$shot['id'].'">'.$shotTxt.'</div>';
			}
		}
		$shotsBySeqs .= '</div>';
	}
}

?>

<div class="ui-state-focus mini ui-corner-top pad3 doigt gras" id="arboHeadProj" style="padding:5px 0px 6px 0px;" title="click to display or refresh main sequences list" help="menu_shortcuts">
	<?php echo $titleProj; ?>
</div>
<div class="ui-state-focus petit ui-corner-top pad3 doigt hide" id="arboHeadSeq" title="click to return to sequences list">
	<span class="inline mid ui-icon ui-icon-arrowthickstop-1-n"></span> <span id="titleSeq"></span>
</div>

<div class="fondSect1 ui-corner-bottom" id="arboSeq">
	<?php echo $sequences; ?>
</div>

<?php echo $shotsBySeqs; ?>
