<?php
$phpFiles = 0; $jsFiles = 0; $htmlFiles = 0; $cssFiles = 0; $pyFiles = 0; $shFiles = 0;
$phpLines = 0; $jsLines = 0; $htmlLines = 0; $cssLines = 0; $pyLines = 0; $shLines = 0;

function countLinesOfCode($path,$excludeDir,$type_files) {

	global $phpLines; global $jsLines; global $htmlLines; global $cssLines; global $pyLines; global $shLines;
	global $phpFiles; global $jsFiles; global $htmlFiles; global $cssFiles; global $pyFiles; global $shFiles;

	$items = glob(rtrim($path, '/') . '/*');
	foreach($items as $item) {
		if (is_dir($item)) {
			if (in_array((pathinfo($item, PATHINFO_FILENAME )), @$excludeDir, true)) {
				echo '<span style="color:red;">exclude dir <b>'. $item .'</b> !</span><br />';
				continue;
			}
			countLinesOfCode($item,$excludeDir,$type_files);
			continue;
		}
		elseif (is_file($item)){
			$ext = pathinfo($item, PATHINFO_EXTENSION);

			if (in_array($ext,$type_files,true)) {
				if ($ext == 'htm') $ext = 'html' ;
				$var = $ext.'Files';
				$$var ++;

				$fileContents = file_get_contents($item);
				$totLineFile = substr_count($fileContents, PHP_EOL) + 1;

				if ($ext == 'php' || $ext == 'html') {
					if ($ext == 'html') echo $item;
					$filePhpLine = 0; $filejsLine = 0;
					// Count PHP
					preg_match_all('/<\?(.*?)\?>/Uis', $fileContents, $phpMatches);
					foreach($phpMatches[0] as $matchPhp) {
						$filePhpLine += substr_count($matchPhp, PHP_EOL) + 1;
					}
					// Count JS
					preg_match_all('/<script[^>]*>(.*)<\/script>/Uis', $fileContents, $jsMatches);
					foreach($jsMatches[0] as $matchJs) {
						$filejsLine += substr_count($matchJs, PHP_EOL);
					}
					$phpLines += $filePhpLine;
					$jsLines  += $filejsLine;
					// Count HTML
					$fileHtmLines = $totLineFile - $filePhpLine;
					$htmlLines += $fileHtmLines;
//					echo $item.' : '.$totLineFile.' lignes totales<br /> >>>>>>>>>>>>>>>>> '.$filePhpLine.' lignes php, '.$filejsLine.' lignes js, '.$fileHtmLines.' lignes html<br />';
				}
				elseif ($ext == "js") {
					$jsLines += substr_count($fileContents, PHP_EOL);
//					echo $item.' : '.$totLineFile.' lignes JS totale<br />';
				}
				elseif ($ext == "css") {
					$cssLines += substr_count($fileContents, PHP_EOL);
//					echo $item.' : '.$totLineFile.' lignes CSS totale<br />';
				}
				// Count PY
				elseif ($ext == "py") {
					$pyLines += substr_count($fileContents, PHP_EOL);
//					echo $item.' : '.$totLineFile.' lignes PYTHON totale<br />';
				}
				// Count SH
				elseif ($ext == "sh") {
					$shLines += substr_count($fileContents, PHP_EOL);
//					echo $item.' : '.$totLineFile.' lignes SHELL totale<br />';
				}
			}
		}
	}
	return array('files' => array($phpFiles, $jsFiles, $htmlFiles, $cssFiles, $pyFiles, $shFiles),
				 'lines' => array($phpLines, $jsLines, $htmlLines, $cssLines, $pyLines, $shLines));
}

$path = '..';
$type_files = array('php','js','html','css','py','sh');				// files accepted
$excludeDir = array('_DOCS','_RECETTE','datas','temp','js');		// dir to exclude

$countCode = countLinesOfCode($path,$excludeDir,$type_files);
?>


<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
	<meta charset="utf-8" />
	<meta name="robots" content="noindex,nofollow" />
</head>

<body>
	<h1>Analyse du code source du projet SaAM</h1>

	<div style="padding-left:30px; font-size:1.5em;">

	<?php
		$total = 0;
		foreach($type_files as $i=>$type){
			$total += @$countCode['lines'][$i];
			echo "<div style='display:inline-block; width:100px;'><b>".strtoupper($type).":</b></div>
					".number_format(@$countCode['files'][$i], 0, ',', ' ')." fichiers, ".number_format(@$countCode['lines'][$i], 0, ',', ' ')." lignes<br />";
		}
		echo '<p>Total : '.number_format($total, 0, ',', ' ').' lignes de code.</p>'
	?>
		</div>
</body>
</html>