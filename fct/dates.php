<?php

// Compare deux dates
function compare_dates($date1,$date2,$filter){
    switch ($filter) {
        case 'date2SUPdate1':
            $date1 = strtotime($date1);
            $date2 = strtotime($date2);
            if ($date2 > $date1) return TRUE;					// Comparaison simple
            return FALSE;
        case 'connected_users':
			$diff = $date1 - $date2 ;
            if ($diff > AVATAR_VISIBLE_TIME) return FALSE;						// Affichage avatars des users connectés
            return TRUE;
        case 'connected_session':
			$diff = $date1 - $date2 ;
			if ($diff > DECONNEXION_AUTO_TIME) return true;		// Déconnexion auto
            return false;
    }
}


/**
 * Transforme une date venant de MYSQL en timestamp, en date formatée ou en objet date
 *
 * @param STRING $SQLdate La date venant de MySQL
 * @param STRING $mode Le type de date qu'on veut en retour (default 'format') ('timeStamp', 'object', 'format', 'messages') -- Le mode message supprimer les infos de jours si la date est aujourd'hui
 * @param STRING $format Le format de date à retourner, ou FALSE pour utiliser celui de la config (default False)
 * @return \DateTime|STRING La date formattée, le timestamp ou l'objet php DateTime
 */
function SQLdateConvert ($SQLdate, $mode='format', $format=false) {
	if ($format === false)
		$format = DATE_FORMAT;
	switch ($mode) {
        case 'timeStamp':
			return strtotime($SQLdate);
        case 'object':
			$d = new DateTime($SQLdate);
			return $d;
		case 'format':
			if ($SQLdate == '0000-00-00 00:00:00') return '?';
			$ts = strtotime($SQLdate);
			if ($ts === false) return '?';
			return date($format, $ts);
		case 'messages':
			if ($SQLdate == '0000-00-00 00:00:00') return '?';
			$ts = strtotime($SQLdate);
			$today = strtotime('today');
			if ( $ts >= $today)
				return date('H:i', $ts);
			return date($format, $ts);
	}
}

// transforme un format de date PHP en format de date JS
function JSdateFormatConvert ($format) {
	$dp1 = preg_replace('/d/', 'dd', $format);
	$dp2 = preg_replace('/m/', 'mm', $dp1);
	return preg_replace('/Y/', 'yy', $dp2);
}


// Transforme un nombre de jour en chaine + lisible
function days_to_date ($nbDays) {
	if (!is_int($nbDays)) return '?NaN '.L_DAYS;
	$days	= abs($nbDays);
	$an		= floor($days/365.25);
	$mois	= floor($days/30.4375) - ($an*12);
	$jours	= $days - floor($mois*30.4375 + $an*365.25);

	$L_year  = ($an>1) ? L_YEARS : L_YEAR;
	$L_month = ($mois>1) ? L_MONTHS : L_MONTH;
	$L_days  = ($jours>1) ? L_DAYS : L_DAY;

	$result = ($nbDays < 0) ?  '<span class="ui-state-error">'.L_OUTDATE_SINCE.' ' : '';
	if ($an>0)	 $result .= $an.' '.$L_year.' ';
	if ($mois>0) $result .= $mois.' '.$L_month.' ';
	$result .= $jours.' '.$L_days;
	$result .= ($nbDays <= 0) ? ' !</span>' : '';

	if ($nbDays == 1) $result = '<span class="ui-state-error">'.L_TOMORROW.' ! </span>';
	if ($nbDays == 0) $result = '<span class="ui-state-error">'.L_TODAY.' ! </span>';

	return $result;
}

// Retourne les jours de début et de fin d'une semaine en fonction de son numéro
function getweekStartEndDays($week, $year) {
    $time = strtotime("1 January $year", time());
    $day = date('w', $time);
    $time += ((7*($week-1))+1-$day)*24*3600;
    $return['start'] = date('d', $time);
    $time += 6*24*3600;
	$format = preg_replace('/\//', ' ', DATE_FORMAT);
	$format = preg_replace('/m/', 'M', $format);
    $return['end'] = date($format, $time);
    return $return;
}


?>
