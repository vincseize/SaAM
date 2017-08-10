<?php

////////////////////////////////////////////// FCTS GENERIQUE //////////////////////////////////////////////////

/**
 * @param $xml : object SimpleXmlIterator
 * @param $ns? : if specified will take care of namespaces
 * @return Array of the xml content
 *
 * Fonction générale de parsing XML vers ARRAY()
 */
function xmlToArray($xml,$ns=null){
  $a = array();
  for($xml->rewind(); $xml->valid(); $xml->next()) {
    $key = $xml->key();
    if(!isset($a[$key])) { $a[$key] = array(); $i=0; }
    else $i = count($a[$key]);
    $simple = true;
    foreach($xml->current()->attributes() as $k=>$v) {
        $a[$key][$i][$k]=(string)$v;
        $simple = false;
    }
    if($ns) foreach($ns as $nid=>$name) {
      foreach($xml->current()->attributes($name) as $k=>$v) {
         $a[$key][$i][$nid.':'.$k]=(string)$v;
         $simple = false;
      }
    }
    if($xml->hasChildren()) {
        if($simple) $a[$key][$i] = xmlToArray($xml->current(), $ns);
        else $a[$key][$i]['content'] = xmlToArray($xml->current(), $ns);
    } else {
        if($simple) $a[$key][$i] = strval($xml->current());
        else $a[$key][$i]['content'] = strval($xml->current());
    }
    $i++;
  }
  return $a;
}

/**
 * @param $array the array to be converted
 * @param $rootElement? if specified will be taken as root element, otherwise defaults to <root>
 * @param SimpleXMLElement? if specified content will be appended, used for recursion
 * @return string XML version of $array
 *
 * Fonction générale de parsing ARRAY() vers XML
 */
function arrayToXml($array, $rootElement = null, $xml = null) {
	$_xml = $xml;
	if ($_xml === null)
		$_xml = new SimpleXMLElement($rootElement !== null ? $rootElement : '<root/>');
	foreach ($array as $k => $v) {
		if (is_array($v))
		  arrayToXml($v, $k, $_xml->addChild($k));
		else
		  $_xml->addChild($k, $v);
	}
	return $_xml->asXML();
}





/////////////////////////////////////// EXPORT DE SHOT LIST selon TAGS /////////////////////////////////////////
function export_shotList_tag ($tagName=false, $type='txt') {
	if (!$tagName) { throw new Exception ('export_shotList_tag() : $tagName undefined !'); return false; }

	$shotList = Shots::getShotsByTag($tagName);
	if (!is_array($shotList)) return false;

	switch ($type) {
		case 'txt' :
			$textExport  = "ID\tPROJECT\t\tSEQUENCE\t\tSHOT";
			$textExport .= "\n----------------------------------------------------------\n";
			foreach($shotList as $shot) {
				if($shot[Shots::SHOT_ID_PROJECT] == 1) continue;
				$pr = new Projects($shot[Shots::SHOT_ID_PROJECT]);
				$projShot	= $pr->getTitleProject();
				$seq = new Sequences($shot[Shots::SHOT_ID_SEQUENCE]);
				$seqShot	= $seq->getSequenceInfos(Sequences::SEQUENCE_TITLE);
				$textExport .= $shot[Shots::SHOT_ID_SHOT]."\t$projShot\t\t$seqShot\t\t\t".$shot[Shots::SHOT_TITLE]."\n";
			}
			$textExport .= "----------------------------------------------------------\n";
			return $textExport;
			break;
		case 'xml':
			$arrayExport = Array('tag_name'=>$tagName);
			foreach($shotList as $shot) {
				if ($shot[Shots::SHOT_ID_PROJECT] == 1) continue;
				$pr = new Projects($shot[Shots::SHOT_ID_PROJECT]);
				$seq = new Sequences($shot[Shots::SHOT_ID_SEQUENCE]);
				$arrayExport['shot_'.$shot[Shots::SHOT_ID_SHOT]] = array(
					'project'	=> $pr->getTitleProject(),
					'sequence'	=> $seq->getSequenceInfos(Sequences::SEQUENCE_TITLE),
					'shot_ID'	=> $shot[Shots::SHOT_ID_SHOT],
					'shot_title'=> $shot[Shots::SHOT_TITLE],
					'shot_label'=> $shot[Shots::SHOT_LABEL],
					'shot_FPS'	=> $shot[Shots::SHOT_FPS]
				);
			}
			return arrayToXml($arrayExport);
			break;
		default:
			throw new Exception ('export_shotList_tag() : unknown $type !'); return false;
			break;
	}
}



///////////////////////////////////////////////// DECTECH ///////////////////////////////////////////////////////

// Retourne les infos de découpage technique enregistrées dans le fichier XML du dept "dectech"
function get_dectech_data ($idProj=false, $titleProj=false, $labelSeq=false, $labelShot=false) {
	if (!$idProj) { throw new Exception ('get_dectech_data() : $idProj undefined !'); return; }
	if (!$titleProj) { throw new Exception ('get_dectech_data() : $titleProj undefined !'); return; }
	if (!$labelSeq) { throw new Exception ('get_dectech_data() : $labelSeq undefined !'); return; }
	if (!$labelShot) { throw new Exception ('get_dectech_data() : $labelShot undefined !'); return; }

	$xmlDecTech = INSTALL_PATH.FOLDER_DATA_PROJ.$idProj.'_'.$titleProj.'/sequences/'.$labelSeq.'/'.$labelShot.'/dectech/dectech.xml';

	// Si fichier n'existe pas, on retourne l'array par défaut, vide
	if (!is_file($xmlDecTech)) return $_SESSION['CONFIG']['decTech_DEFAULT'];

	$dectechData = array();
	$dom = new DOMDocument();
	$dom->load($xmlDecTech);
	$domCategTechs = $dom->getElementsByTagName('categTech');
	foreach($domCategTechs as $categ) {
//		$categTech = $categ->getAttribute('name');
		$domTechItems = $categ->getElementsByTagName('dectechItem');
//		$dectechData[$categTech] = array();
		foreach($domTechItems as $item) {
			$typeTech = $item->getAttribute('type');
//			$posTech  = $item->getAttribute('pos');
			$textTech = $item->nodeValue;
			$dectechData[$typeTech] = $textTech;
		}
	}
	// Si il manque des infos dans le XML, on va les chercher dans le decTechDefault (cf. config.inc)
	$manqueXml = array(); $dectechDataOK = array();
	foreach($_SESSION['CONFIG']['decTech_DEFAULT'] as $categName=>$categTechDefault) {
		foreach ($categTechDefault as $defaultPos => $defaultChamp) {
			$dectechDataOK[$categName][$defaultPos] = $defaultChamp;
			if (isset($dectechData[$defaultChamp[0]]))
				$dectechDataOK[$categName][$defaultPos] = Array($defaultChamp[0], $dectechData[$defaultChamp[0]]);
		}
	}
	return $dectechDataOK;
}


// Modifie le fichier XML de dectech
function set_dectech_data ($dirShot=false, $categTechName=false, $typeTech=false, $posTech=false, $newTextTech=' ') {
	if (!$dirShot) { throw new Exception ('set_dectech_data() : $dirShot undefined !'); return; }
	if (!$categTechName) { throw new Exception ('set_dectech_data() : $categTechName undefined !'); return; }
	if (!$typeTech) { throw new Exception ('set_dectech_data() : $typeTech undefined !'); return; }
	if (!$posTech) { throw new Exception ('set_dectech_data() : $posTech undefined !'); return; }

	$dectechPath = INSTALL_PATH.FOLDER_DATA_PROJ.$dirShot.'/dectech';
	if (!is_dir($dectechPath))
		mkdir($dectechPath, 0755, true);
	$xmlDecTech = $dectechPath.'/dectech.xml';
	$dom = new DOMDocument();
	if (is_file($xmlDecTech)) {
		$dom->load($xmlDecTech);
		$domDecTech = $dom->getElementsByTagName('dectechItem');
		$nodeExist = false;
		foreach($domDecTech as $item) {											// Si le noeud existe déjà, on le modifie
			if ($item->getAttribute('type') == $typeTech) {
				$item->nodeValue = stripslashes($newTextTech);
				$nodeExist = true;
			}
		}
		if (!$nodeExist) {														// Sinon, on crée le noeud
			$nodeRoot  = $dom->getElementsByTagName('dectech');
			$nodeCateg = $dom->getElementsByTagName('categTech');
			$nodeItem  = $dom->createElement('dectechItem');
			$nodeItem->setAttribute('type', $typeTech);
			$nodeItem->setAttribute('pos', $posTech);
			$nodeItem->nodeValue = stripslashes($newTextTech);
			$categExist = false;
			foreach($nodeCateg as $ic=>$categ) {									// Check si le noeud de catégorie existe
				if ($categ->getAttribute('name') == $categTechName) {
					$categTech = $nodeCateg->item($ic);
					$categTech->appendChild($nodeItem);
					$categExist = true;
				}
			}
			if (!$categExist) {														// Sinon, on crée le noeud de catégorie
				$categTech = $dom->createElement('categTech');
				$categTech->setAttribute('name', $categTechName);
				$categTech->appendChild($nodeItem);
				$nodeRoot->item(0)->appendChild($categTech);
			}
		}
	}
	else {
		$nodeRoot  = $dom->createElement('dectech');
		$categTech = $dom->createElement('categTech');
		$nodeItem  = $dom->createElement('dectechItem');
		$categTech->setAttribute('name', $categTechName);
		$nodeItem->setAttribute('type', $typeTech);
		$nodeItem->setAttribute('pos', $posTech);
		$nodeItem->nodeValue = stripslashes($newTextTech);
		$categTech->appendChild($nodeItem);
		$nodeRoot->appendChild($categTech);
		$dom->appendChild($nodeRoot);
	}
	$dom->save($xmlDecTech);
}



/////////////////////////////////////////////////// ASSETS /////////////////////////////////////////////////////

// Récupération de l'arborescence des fichiers d'assets
function get_assets_arbo ($idProj=false, $titleProj=false) {
	if (!$idProj) { throw new Exception ('get_assets_arbo() : $idProj undefined !'); return; }
	if (!$titleProj) { throw new Exception ('get_assets_arbo() : $titleProj undefined !'); return; }

	$xmlAssets = INSTALL_PATH.FOLDER_DATA_PROJ.$idProj.'_'.$titleProj.'/assets/masterFile_assets.xml';
	if (!is_file($xmlAssets)) return array();

	$xml = new SimpleXmlIterator($xmlAssets, null, true);
	$namespaces = $xml->getNamespaces(true);
	$assetsArbo = xmlToArray($xml,$namespaces);

	return $assetsArbo;
}

// Récupération des dépendances d'un asset en particulier
function get_asset_libs_fromXML ($idProj=false, $nameAsset=false) {
	if (!$idProj) { throw new Exception ('get_asset_libs_fromXML() : $idProj undefined !'); }
	if (!$nameAsset) { throw new Exception ('get_asset_libs_fromXML() : $nameAsset undefined !'); }
	$p = new Projects($idProj);
	$titleProj = $p->getTitleProject();
	$xmlAssets = INSTALL_PATH.FOLDER_DATA_PROJ.$idProj.'_'.$titleProj.'/assets/masterFile_assets.xml';
	if (!is_file($xmlAssets)) { throw new Exception ('get_asset_libs_fromXML() : XML masterFile is missing!'); }

	$xml = new SimpleXmlElement($xmlAssets, null, true);
	$node = $xml->xpath(".//file[@name='$nameAsset']");
	if (count($node) < 1) return false;
	$libs = $node[0]->attributes()->libs;
	if ($libs == Null) return False;
	return explode(',',(string)$libs);
}

// Check si un asset existe bien dans le fichier xml (pour check suppresion from BDD)
function asset_exists_xml ($idProj=false, $nameAsset=false) {
	if (!$idProj) { throw new Exception ('asset_exists_xml() : $idProj undefined !'); }
	if (!$nameAsset) { throw new Exception ('asset_exists_xml() : $nameAsset undefined !'); }
	$p = new Projects($idProj);
	$titleProj = $p->getTitleProject();
	$xmlAssets = INSTALL_PATH.FOLDER_DATA_PROJ.$idProj.'_'.$titleProj.'/assets/masterFile_assets.xml';
	if (!is_file($xmlAssets)) { throw new Exception ('asset_exists_xml() : XML masterFile is missing!'); }

	$xml = new SimpleXmlElement($xmlAssets, null, true);
	$node = $xml->xpath(".//file[@name='$nameAsset']");
	if (count($node) < 1) return false;
	return true;
}

// Check si un dossier existe dans le XML
function folder_exists_xml ($idProj=false, $path=false) {
	if (!$idProj) { throw new Exception ('folder_exists_xml() : $idProj undefined!'); }
	if (!$path)	  { throw new Exception ('folder_exists_xml() : missing folder path!'); }
	$p = new Projects($idProj);
	$dirProj	= $p->getDirProject();
	$masterFile	= INSTALL_PATH . FOLDER_DATA_PROJ . $dirProj . '/assets/masterFile_assets.xml';
	if (!is_file($masterFile)) { throw new Exception ('folder_exists_xml() : XML masterFile is missing!'); }
	$folderName = basename($path);
	$fDirName	= dirname($path);
	$xml = simplexml_load_file($masterFile);
	$nodes = $xml->xpath('//dir[@name="'.$folderName.'"][@url="'.$fDirName.'/" or @url="./"]');
	if (count($nodes) < 1) return false;
	return true;
}

// Retourne tout les chemins possibles depuis le XML
function getAllXMLpaths ($idProj) {
	if (!$idProj)	{ throw new Exception('getAllXMLpaths() : missing the project ID!'); }

	$p = new Projects($idProj);
	$dirProj	= $p->getDirProject();
	$assetsDir	= INSTALL_PATH . FOLDER_DATA_PROJ . $dirProj . '/assets/';
	$masterFile = $assetsDir.'masterFile_assets.xml';
	$xml  = simplexml_load_file($masterFile);
	$allP = $xml->xpath('//dir');

	$allDirs = Array();
	foreach($allP as $item) {
		$attrs = $item->attributes();
		$allDirs[] = preg_replace('#^\./#', '', (string)$attrs['url']) . (string)$attrs['name'];
	}
	sort($allDirs);
	return $allDirs;
}

?>