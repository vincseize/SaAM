<?php
if (!function_exists('array_replace_recursive')) {
	function array_replace_recursive($array, $array1) {
		function recurse($array, $array1) {
			foreach ($array1 as $key => $value) {
			// create new key in $array, if it is empty or not an array
			if (!isset($array[$key]) || (isset($array[$key]) && !is_array($array[$key]))) {
				$array[$key] = array();
			}
			// overwrite the value in the base array
			if (is_array($value)) {
				$value = recurse($array[$key], $value);
			}
			$array[$key] = $value;
			}
			return $array;
		}
		// handle the arguments, merge one by one
		$args = func_get_args();
		$array = $args[0];
		if (!is_array($array)) {
			return $array;
		}
		for ($i = 1; $i < count($args); $i++) {
			if (is_array($args[$i])) {
				$array = recurse($array, $args[$i]);
			}
		}
		return $array;
	}
}

/**
 * Retourne la liste des FPS définis dans la config sous la forme (str|str|str...)
 * @return ARRAY La liste des FPS dispos
 */
function recup_fps () {
        $array_fps = array();
        $list_fps = explode('|',LIST_FPS);
        foreach($list_fps as $val){
            array_push ($array_fps,$val);
        }
        return $array_fps;
}


/**
 * Export d'une table en CSV
 * @param STRING $head La description des données à exporter (entête de tableau) sous la forme "col1;col2;col3;..."
 * @param ARRAY $data Les données à exporter au format CSV
 * @param STRING $sep Le séparateur du fichier CSV (default : point-virgule ';')
 */
function data_export_CSV ($head, $data, $sep=';') {
	$csv = "$head\n";
	foreach($data as $entry) {
		foreach($entry as $k=>$val) {
			if ($k != 'id' && $k != 'constante')
				$val = '"'.$val.'"';
			$csv .= "$val$sep";
		}
		$csv = trim($csv, $sep);
		$csv .= "\n";
	}
	return $csv;
}