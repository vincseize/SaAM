<?php

	$sequences = '';	$scenesBySeqs ='';

	if ($seqList = $p->getSequences()) {
		foreach ($seqList as $seq) {
			if ($seq['hide'] == 1 || $seq['archive'] == 1) continue;
			$scenesList    = $p->getScenes($seq['id'], 'actifs');
			$countScenes   = (is_array($scenesList)) ? '('.count($scenesList).')' : '&nbsp;&nbsp;&nbsp;&nbsp;';
			$sequences	  .= '<div class="bordColInv1 arboItem" idSeq="'.$seq['id'].'" title="Display this sequence\'s scenes">'.$seq['title'].' <i class="colorDiscret petit">'.$countScenes.'</i></div>';
			$scenesBySeqs .= '<div class="fondSect1 ui-corner-bottom arboScenes" idProj="'.$idProj.'" idSeq="'.$seq['id'].'">';
			if (is_array($scenesList)) {
				foreach ($scenesList as $scene) {
					$sceneTxt = (strlen($scene[Scenes::TITLE]) >= 17) ? $scene[Scenes::LABEL] : $scene[Scenes::TITLE] ;
					$scenesBySeqs .= '<div class="bordColInv1 ui-corner-tl ui-corner-br arboItem colorErrText mini" sequenceID="'.$seq['id'].'" idScene="'.$scene[Scenes::ID_SCENE].'">'.$sceneTxt.'</div>';
				}
			}
			$scenesBySeqs .= '</div>';
		}
	}
	$scNoSeqList = $p->getScenes('', 'actifs');
	if (is_array($scNoSeqList)) {
		$sequences	  .= '<div class="bordColInv1 arboItem" idSeq="noSeq" title="Display scenes that has no sequence"><i>Not assigned</i> <i class="colorDiscret petit">('.count($scNoSeqList).')</i></div>';
		$scenesBySeqs .= '<div class="fondSect1 ui-corner-bottom arboScenes" idProj="'.$idProj.'" idSeq="noSeq">';
		foreach($scNoSeqList as $scene) {
			$sceneTxt = (strlen($scene[Scenes::TITLE]) >= 17) ? $scene[Scenes::LABEL] : $scene[Scenes::TITLE] ;
			$scenesBySeqs .= '<div class="bordColInv1 ui-corner-tl ui-corner-br arboItem colorErrText mini" sequenceID="noSeq" idScene="'.$scene[Scenes::ID_SCENE].'">'.$sceneTxt.'</div>';
		}
		$scenesBySeqs .= '</div>';
	}
?>

<div class="ui-state-focus mini ui-corner-top pad3 doigt gras" id="arboHeadProj" style="padding:5px 0px 6px 0px;" title="click to display or refresh sequences list" help="menu_shortcuts">
	<?php echo $titleProj; ?>
</div>
<div class="ui-state-focus petit ui-corner-top pad3 doigt hide" id="arboHeadSeq" title="click to return to sequences list">
	<span class="inline mid ui-icon ui-icon-arrowthickstop-1-n"></span> <span id="titleSeq"></span>
</div>

<div class="fondSect1 ui-corner-bottom" id="arboSeq">
	<?php echo $sequences; ?>
</div>

<?php echo $scenesBySeqs; ?>
