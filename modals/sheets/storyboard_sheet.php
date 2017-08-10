<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	require_once ('vignettes_fcts.php');

	extract($_GET);

	if ((!isset($proj_ID) || empty($proj_ID) || $proj_ID == '') && (!isset($tagName) || empty($tagName) || $tagName == ''))
		die('Missing project ID, or TAG name.');



	if (isset($proj_ID) && !empty($proj_ID) && $proj_ID != '') {
		$dept = 'storyboard';
		$titleDept = 'Storyboard';
		$p = new Projects($proj_ID);
		$titleProj = $p->getTitleProject();
		$prod = $p->getProjectInfos(Projects::PROJECT_COMPANY);
		$seqsList  = $p->getSequences(true);
	}
	elseif (isset($tagName) && !empty($tagName) && $tagName != '') {
		$dept = false;
		$titleDept = 'Tag "'.$tagName.'"';
		$prod = $_SESSION['user']->getUserInfos(Users::USERS_PSEUDO);
		$shotList = Shots::getShotsByTag($tagName);
		if (!is_array($shotList))
			die('No shot for this Tag!');
		$seqsList = Array();
		foreach ($shotList as $shot) {
			if ($shot[Shots::SHOT_HIDE] == 1 || $shot[Shots::SHOT_ARCHIVE] == 1) continue;
			$idSeq		= $shot[Shots::SHOT_ID_SEQUENCE];
			$se			= new Sequences($idSeq);
			if (!$se->is_active()) continue;
			$posSeq		= $se->getSequenceInfos(Sequences::SEQUENCE_POSITION);
			$seqsList[$idSeq] = $se->getSequenceInfos();
		}
	}
	else die('Missing project ID, or TAG name.');


	$vignettes = Array();
	$exclude_seqs  = Array();
	$exclude_shots = Array();
	if (isset($exclSeq))
		$exclude_seqs = json_decode(urldecode($exclSeq), true);
	if (isset($exclShot))
		$exclude_shots = json_decode(urldecode($exclShot), true);

	foreach ($seqsList as $idSeq => $sequence) {
		if (isset($seq_ID) && $seq_ID != @$idSeq) continue;
		if (in_array($idSeq, $exclude_seqs)) continue;
		$idSeqProj	= $sequence[Sequences::SEQUENCE_ID_PROJECT];
		$labelSeq	= $sequence[Sequences::SEQUENCE_LABEL];
		$titleSeq	= $sequence[Sequences::SEQUENCE_TITLE];
		$posSeq		= $sequence[Sequences::SEQUENCE_POSITION];

		$l = new Liste();
		$l->addFiltre(Shots::SHOT_ID_SEQUENCE, '=', $idSeq);
		$l->addFiltre(Shots::SHOT_HIDE, '=', 0);
		$l->addFiltre(Shots::SHOT_ARCHIVE, '=', 0);
		if (isset($tagName) && !empty($tagName) && $tagName != '') {
			$l->addFiltre(Shots::SHOT_TAGS, 'LIKE', '%'.$tagName.'%');
			$posSeq = $idSeqProj.$posSeq;
			$prs = new Projects($idSeqProj);
			$titleSeqProj = $prs->getTitleProject();
		}
		$l->getListe(TABLE_SHOTS, '*', Shots::SHOT_POSITION, 'ASC');
		$shotList = $l->simplifyList(Shots::SHOT_POSITION);
		if ($shotList == false) continue;

		$shotList['idSeqProj']		= @$idSeqProj;
		$shotList['titleSeqProj']	= @$titleSeqProj;
		$shotList['labelSeq']		= $labelSeq;
		$shotList['titleSeq']		= $titleSeq;
		$vignettes[$posSeq]	= $shotList;
	}
	ksort($vignettes, SORT_NUMERIC);

//	echo '<pre>'; print_r($vignettes); echo '</pre>';
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
	<meta charset="utf-8" />
	<meta name="keywords" content="SaAM, shots, assets, management, CG, post-production" />
	<meta name="description" content="Shots and Assets Manager" />
	<meta name="robots" content="noindex,nofollow" />
	<title>SaAM - Storyboard Contact-Sheet</title>
	<link type="image/x-icon" href="../../gfx/favicon.ico" rel="shortcut icon" />
	<link type="text/css" href="../../css/contact_sheets.css?v=1" rel="stylesheet" media="screen, print" />
	<style type="text/css" media="print">
		@page {
		  size: A4 portrait;
		  margin: 3%;
		}
		body { font-size: 8pt;}
		.printer { display: none; }
	</style>
</head>
<body>

	<div class="headPage">
		<div class="headBtn" style="position: absolute; margin:5px 10px;">
			<img src="../../gfx/logoH.png" height="50" />
		</div>
		<div class="headTitle">
			<?php echo strtoupper(@$titleProj); ?> - <?php echo $titleDept; ?>
		</div>
		<div class="headInfo">
			&COPY; <?php echo @$prod; ?> | <?php echo date('Y-m-d'); ?> <span class="printer" onClick="window.print()" title="print this document">| <img src="../../gfx/printer.png" /></span>
		</div>
	</div>

	<?php foreach ($vignettes as $posSeq => $items):
			$labelSeq = ''; $titleSeq = '';
			ksort($items); ?>
			<div class="fixFloat">
			<?php foreach ($items as $item => $infos) :
				if ($item == 'labelSeq' && $infos != '')	 { $labelSeq  = $infos; continue; }
				if ($item == 'titleSeq' && $infos != '')	 { $titleSeq  = $infos; continue; }
				if ($item == 'idSeqProj' && $infos != '')	 { $proj_ID   = $infos; continue; }
				if ($item == 'titleSeqProj' && $infos != '') { $titleProj = $infos;
					echo '</div><div class="CSsequence pageBreakI"><div class="seqTitle">'.$infos.' | '.$titleSeq.'</div>';
					continue;
				}
				elseif ($item == 'titleSeqProj' && $infos == '') {
					echo '</div><div class="CSsequence"><div class="seqTitle">'.$titleSeq.'</div>';
					continue;
				}
				if (in_array($infos[Shots::SHOT_ID_SHOT], $exclude_shots)) continue;
				$vignetteShotOK = check_shot_vignette_ext($proj_ID, $labelSeq, $infos[Shots::SHOT_LABEL], $dept);
				if (preg_match('/novignette/', $vignetteShotOK))
					$vignetteShotOK = check_shot_vignette_ext($proj_ID, $labelSeq, $infos[Shots::SHOT_LABEL], 'dectech');
				?>
				<div class="contactSheetItem">
					<div class="CSitemImg">
						<img src="../../<?php echo $vignetteShotOK; ?>" />
					</div>
					<div class="CSitemTitle">
						<div class="posSeq"><?php echo '#'.$infos[Shots::SHOT_POSITION]; ?></div>
						<b><?php echo $infos[Shots::SHOT_TITLE]; ?></b>
					</div>
					<div class="CSitemDescr">
						<?php
						echo ($infos[Shots::SHOT_DESCRIPTION] == '') ? '<i>No description</i>' : nl2br($infos[Shots::SHOT_DESCRIPTION]); ?>
					</div>
				</div>
		<?php endforeach;?>
			</div>
	<?php endforeach; ?>

</body>
</html>