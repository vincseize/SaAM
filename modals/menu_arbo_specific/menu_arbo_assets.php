<?php

$folders = '';	$assetsByFolder ='';

if ($assetsList = $p->getAssets("actifs", "by_path")) {
	foreach ($assetsList as $firstLevel => $assets) {
		$folders	 .= '<div class="bordColInv1 arboItem" rootPathAsset="'.$firstLevel.'">'.$firstLevel.'</div>';
		$assetsByFolder .= '<div class="fondSect1 ui-corner-bottom arboAssets" idProj="'.$idProj.'" rootPathAsset="'.$firstLevel.'">';
		if (is_array($assets)) {
			foreach ($assets as $asset) {
				$assetsByFolder .= '<div class="bordColInv1 ui-corner-tl ui-corner-br arboItem" nameAsset="'.$asset['filename'].'">'.$asset['filename'].'</div>';
			}
		}
		$assetsByFolder .= '</div>';
	}
}

?>

<div class="ui-state-focus mini ui-corner-top pad3 doigt gras" id="arboHeadProj" style="padding:5px 0px 6px 0px;" title="click to display or refresh assets root tree"  help="menu_shortcuts">
	<?php echo $titleProj; ?>
</div>
<div class="ui-state-focus petit ui-corner-top pad3 doigt hide" id="arboHeadSeq" title="click to return to assets root tree">
	<span class="inline mid ui-icon ui-icon-arrowthickstop-1-n"></span> <span id="titleSeq"></span>
</div>

<div class="fondSect1 ui-corner-bottom" id="arboSeq">
	<?php echo $folders; ?>
</div>

<?php echo $assetsByFolder; ?>
