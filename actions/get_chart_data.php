<?php
@session_start();
require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

extract($_POST);

$retour['error']	= 'error';
$retour['message']	= 'action undefined';

try {
	// Récup du titre projet si défini
	$titleProj = 'All projects';
	$idProj = (int)$filterProj;
	if ($filterProj != 'ALL') {
		$p = new Projects($idProj);
		$titleProj = $p->getTitleProject();
	}

	// Type de graphique LINE
	if ($action == 'getChartData_lineChart') {
		$retour['message']	= "Unknown dataType ($dataType)";
		$year = date('Y');
		$mois = Array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');

		$retour['data']['allSeries'][0]['name'] = L_ASSETS; $retour['data']['allSeries'][0]['data'] = Array();
		$retour['data']['allSeries'][1]['name'] = L_SCENES; $retour['data']['allSeries'][1]['data'] = Array();
		$retour['data']['allSeries'][2]['name'] = L_SHOTS;  $retour['data']['allSeries'][2]['data'] = Array();

		// Type de données COMMENTAIRES
		if ($dataType == 'messages') {
			$l = new Liste();
			$l->addFiltre(Comments::COMM_DATE, '>', "$year-01-01", 'AND');
			$l->addFiltre(Comments::COMM_ID_PROJECT, '!=', 1, 'AND');
			if ($filterProj != 'ALL')
				$l->addFiltre(Comments::COMM_ID_PROJECT, '=', $idProj, 'AND');
			$allCommShots  = $l->getListe(TABLE_COMM_SHOT, Comments::COMM_DATE);
			$allCommAssets = $l->getListe(TABLE_COMM_ASSET, Comments::COMM_DATE);
			$allCommScenes = $l->getListe(TABLE_COMM_SCENES, Comments::COMM_DATE);
			if (!$allCommShots) $allCommShots = Array();
			if (!$allCommAssets) $allCommAssets = Array();
			if (!$allCommScenes) $allCommScenes = Array();
			foreach ($mois as $m) {
				$retour['data']['allSeries'][0]['data'][] = count(preg_grep("/^$year-$m/", $allCommShots));
				$retour['data']['allSeries'][1]['data'][] = count(preg_grep("/^$year-$m/", $allCommAssets));
				$retour['data']['allSeries'][2]['data'][] = count(preg_grep("/^$year-$m/", $allCommScenes));
			}
			$retour['data']['dataDescr']	 = L_NUMBER_OF.' '.L_MESSAGES;
			$retour['data']['axe_X']		 = Array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
			$retour['data']['chartType']	 = 'line';
			$retour['data']['chartTitle']	 = L_MONTHLY_ACTIVITY.' '.$year;
			$retour['data']['chartSubTitle'] = "<b>$titleProj</b>: ".L_MESSAGES;
			$retour['error'] = $retour['message'] = 'OK';
		}

		// Type de données PUBLISHED
		if ($dataType == 'published') {

			foreach ($mois as $m) {		// @TODO : calcul du nombre de published (ouch!!! dur dur)
				$retour['data']['allSeries'][0]['data'][] = 0;
				$retour['data']['allSeries'][1]['data'][] = 0;
				$retour['data']['allSeries'][2]['data'][] = 0;
			}

			$retour['data']['dataDescr']	 = L_NUMBER_OF.' '.L_RETAKES;
			$retour['data']['axe_X']		 = Array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
			$retour['data']['chartType']	 = 'line';
			$retour['data']['chartTitle']	 = L_MONTHLY_ACTIVITY.' '.$year;
			$retour['data']['chartSubTitle'] = "<b>$titleProj</b>: ".L_RETAKES;
			$retour['error'] = $retour['message'] = 'OK';
		}

		// Type de données QUOTAS
		if ($dataType == 'quotas') {

			foreach ($mois as $m) {		// @TODO : définir ce que sont les quotas ! ??
				$retour['data']['allSeries'][0]['data'][] = 0;
				$retour['data']['allSeries'][1]['data'][] = 0;
				$retour['data']['allSeries'][2]['data'][] = 0;
			}

			$retour['data']['dataDescr']	 = L_NUMBER_OF.' '.'??';
			$retour['data']['axe_X']		 = Array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
			$retour['data']['chartType']	 = 'line';
			$retour['data']['chartTitle']	 = L_MONTHLY_ACTIVITY.' '.$year;
			$retour['data']['chartSubTitle'] = "<b>$titleProj</b>: (???)";
			$retour['error'] = $retour['message'] = 'OK';
		}

	}


	// Type de graphique PIE
	if ($action == 'getChartData_pieChart') {
		$retour['message']	= "Unknown dataType ($dataType)";

		// Type de données STORAGE
		if ($dataType == 'storage') {
			if ($filterProj == 'ALL') {
				$l = new Liste();
				$l->addFiltre(Projects::PROJECT_ARCHIVE, '=', '0');
				$l->getListe(TABLE_PROJECTS, 'id,title');
				$allProjs = $l->simplifyList();
				$totalSize = 0;
				$retour['data']['allSeries'][0]['name'] = L_STORAGE_DISTRIBUTION;
				$retour['data']['allSeries'][0]['data'] = Array();
				foreach ($allProjs as $pID => $pInf) {
					$projSize = get_project_size($pID);
					$retour['data']['allSeries'][0]['data'][] = Array('name' => $pInf['title'], 'realSize' => $projSize[3], 'detail' => $projSize[0]);
					$totalSize += $projSize[3];
				}
				foreach ($retour['data']['allSeries'][0]['data'] as $i => $d) {
					$pRatio = $retour['data']['allSeries'][0]['data'][$i]['realSize'] / $totalSize * 100;
					$retour['data']['allSeries'][0]['data'][(int)$i]['y'] = $pRatio;
				}
				$retour['data']['allSeries'][0]['data'][] = Array('name' => 'TOTAL', 'y' => null, 'detail' => round($totalSize/1024).' MB', 'color' => 'transparent');
			}
			else {
				$bankSize	= get_project_folder_size($idProj, 'bank');
				$assetsSize	= get_project_folder_size($idProj, 'assets');
				$scenesSize	= get_project_folder_size($idProj, 'scenes');
				$shotsSize	= get_project_folder_size($idProj, 'sequences');
				$freeSize	= $_SESSION['CONFIG']['projects_size'] - $bankSize - $assetsSize - $scenesSize - $shotsSize;

				$bankPC		= $bankSize		/ ($_SESSION['CONFIG']['projects_size']) * 100;
				$assetsPC	= $assetsSize	/ ($_SESSION['CONFIG']['projects_size']) * 100;
				$scenesPC	= $scenesSize	/ ($_SESSION['CONFIG']['projects_size']) * 100;
				$shotsPC	= $shotsSize	/ ($_SESSION['CONFIG']['projects_size']) * 100;
				$freePC		= 100 - $bankPC - $assetsPC - $scenesPC - $shotsPC;

				$retour['data']['allSeries'][0]['name'] = L_STORAGE_DISTRIBUTION;
				$retour['data']['allSeries'][0]['data'] = Array(
					Array('name' => L_BANK,			'y' => $bankPC,		'detail' => round($bankSize/1024).' MB'),
					Array('name' => L_ASSETS,		'y' => $assetsPC,	'detail' => round($assetsSize/1024).' MB'),
					Array('name' => L_SCENES,		'y' => $scenesPC,	'detail' => round($scenesSize/1024).' MB'),
					Array('name' => L_SHOTS,		'y' => $shotsPC,	'detail' => round($shotsSize/1024).' MB'),
					Array('name' => 'PROJECT TOTAL SIZE', 'y' => null, 'detail' => round(($bankSize + $assetsSize + $scenesSize + $shotsSize)/1024).' MB', 'color' => 'transparent'),
					Array('name' => 'FREE SPACE',	'y' => $freePC,		'detail' => round($freeSize/1024).' MB',		'color' => '#CCCCCC', 'sliced' => true)
				);
			}
			$retour['data']['dataDescr']	 = L_STORAGE_DISTRIBUTION;
			$retour['data']['chartType']	 = 'pie';
			$retour['data']['chartTitle']	 = mb_strtoupper(L_STORAGE_DISTRIBUTION);
			$retour['data']['chartSubTitle'] = "<b>$titleProj</b>";
			$retour['error'] = $retour['message'] = 'OK';
		}

		// Type de données USERS
		if ($dataType == 'users') {

			$retour['data']['dataDescr']	 = 'Users ?????';
			$retour['data']['chartType']	 = 'pie';
			$retour['data']['chartTitle']	 = 'USERS ?? data ?';
			$retour['data']['chartSubTitle'] = "<b>$titleProj</b>";
			$retour['error'] = $retour['message'] = 'OK';
		}
	}
}
catch(Exception $e) { $retour['message'] = $e->getMessage(); }

echo json_encode($retour);