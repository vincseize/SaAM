<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC'].'/checkConnect.php' );


extract($_POST);

$value = urldecode($dVal);

// Récupération des possibilités d'une colonne ayant une relation
try {
	if ($customMode == 'true') {
		$ct = new CustomTable($table, $idProj);
		$ct->getRows();
		$dType	 = $ct->getRowType($dRow);
		$poss	 = $ct->getPossibilities($dRow, $idProj);
		$relName = $ct->getRelInfo($dRow);
	}
	else {
		$rel = new RelCheck($table);
		$dType	 = $rel->get_rowType($dRow, '0');
		$poss	 = $rel->get_all_possibilities($dRow, $idProj);
		$relName = $rel->get_link_info($dRow);
	}
}
catch(Exception $e) { die('<span class="colorErreur gras">'.$e->getMessage().'</span>'); }?>


<?php if ($dType == 'menu_rel'): ?>
		<select class="modEntryVal" therow="<?php echo $dRow; ?>" colType="<?php echo $dType; ?>">
			<?php $valueOK = RelCheck::NOT_FOUND_MSG;
			foreach($poss as $pId => $p):
				$selected = ($pId == $value) ? 'selected="selected"' : "" ;
				if ($pId == $value) $valueOK = $p; ?>
				<option value="<?php echo $pId; ?>" <?php echo $selected; ?>><?php echo $p; ?></option>
			<?php endforeach;
			$value = '<span class="gros colorBtnFake">*</span> '.$valueOK; ?>
		</select>
		<div class="rightText pico">
			<button class="bouton ui-state-highlight prod_saveModVal"><span class="ui-icon ui-icon-check"></span></button>
			<button class="bouton ui-state-error prod_cancelModVal"><span class="ui-icon ui-icon-cancel"></span></button>
		</div>
	<script>
		$(function(){
			$('.modEntryVal').selectmenu({style: 'dropdown', width: 200}).change(function(){
				if (selected.length == 0)
					return;
				var tdIdx	= <?php echo $rIdx; ?>;
				var trID	= $(this).parents('tr').attr('idEntry');
				var newVal	= $(this).val();
				var poss = $.parseJSON('<?php echo json_encode($poss); ?>');
				$.each(selected, function(i,idElem){
					if (idElem == trID) return true;
					var tdList = $('table tr[idEntry="'+idElem+'"]').find('td');
					$(tdList[tdIdx]).html('<span class="gros colorBtnFake">*</span> '+poss[newVal]);
				});
			});
		});
	</script>



<?php elseif ($dType == 'tags' || $dType == 'menu_rel_multiple'):
		$valArr = json_decode($value); ?>
		<select class="modEntryVal" multiple="multiple" therow="<?php echo $dRow; ?>" tdIdx="<?php echo $rIdx; ?>" colType="<?php echo $dType; ?>">
			<?php
			$oldValues = '';
			foreach($poss as $pId => $p):
				$toSearch = ($dType == 'tags') ? $p : $pId;
				$checked = (in_array($toSearch, $valArr)) ? 'selected="selected"' : "" ;
				if (in_array($toSearch, $valArr)) $oldValues .= '<div class="inline ui-state-highlight ui-corner-all" style="padding:1px 4px;">'.$p.'</div> '; ?>
				<option value="<?php echo $pId; ?>" <?php echo $checked; ?>><?php echo $p; ?></option>
			<?php endforeach;
			$value = ($oldValues != '') ? $oldValues : '<div class="inline ui-state-highlight ui-corner-all" style="padding:1px 4px;">'.RelCheck::NOT_FOUND_MSG.'</div>'; ?>
		</select>
		<div class="rightText pico">
			<button class="bouton ui-state-highlight prod_saveModVal"><span class="ui-icon ui-icon-check"></span></button>
			<button class="bouton ui-state-error prod_cancelModVal"><span class="ui-icon ui-icon-cancel"></span></button>
		</div>
	<script>
		$(function(){
			$('.modEntryVal').multiselect({
				noneSelectedText: '<i><?php echo L_NOTHING; ?></i>',
				selectedText: '# <?php echo $dRow; ?>',
				selectedList: 3,
				checkAllText: ' ',
				uncheckAllText: ' ',
				minWidth: 200}
			).change(function(){
				if (selected.length == 0)
					return;
				var tdIdx	= <?php echo $rIdx; ?>;
				var trID	= $(this).parents('tr').attr('idEntry');
				var newVal	= $(this).val();
				if (newVal === null || newVal === undefined)
					newVal = '';
				var poss = $.parseJSON('<?php echo json_encode($poss); ?>');
				$.each(selected, function(i,idElem){
					if (idElem == trID) return true;
					var tdList = $('table tr[idEntry="'+idElem+'"]').find('td');
					$(tdList[tdIdx]).html('');
					$.each(newVal, function(id, val){
						$(tdList[tdIdx]).append('<div class="inline ui-state-highlight ui-corner-all" style="padding:1px 4px;">'+poss[val]+'</div> ');
					});
				});
			});;
		});
	</script>



<?php elseif ($dType == 'calendar'):
		require_once('dates.php');
		$date = SQLdateConvert($value);
		if ($date == '?')
			$date = date(DATE_FORMAT);
		$value = $date; ?>
		<input type="text" class="ui-corner-all noBorder pad3 fondSect3 modEntryVal calendarVal" therow="<?php echo $dRow; ?>" colType="<?php echo $dType; ?>" size="20" value="<?php echo $date; ?>" />
		<div class="rightText pico">
			<button class="bouton ui-state-highlight prod_saveModVal"><span class="ui-icon ui-icon-check"></span></button>
			<button class="bouton ui-state-error prod_cancelModVal"><span class="ui-icon ui-icon-cancel"></span></button>
		</div>
	<script>
		$(function(){
			$('.calendarVal').datepicker({dateFormat: 'dd/mm/yy', firstDay: 1, changeMonth: false, changeYear: false, defaultDate: '<?php echo $date; ?>'});
			$('.modEntryVal').change(function(){
				var tdIdx	= <?php echo $rIdx; ?>;
				var trID	= $(this).parents('tr').attr('idEntry');
				var newVal	= $(this).val();
				$.each(selected, function(i,idElem){
					if (idElem == trID) return true;
					var tdList = $('table tr[idEntry="'+idElem+'"]').find('td');
					$(tdList[tdIdx]).html(newVal);
				});
			});
		});
	</script>



<?php elseif ($dType == 'boolean'):
		$selNo   = ($value == '' || $value == '0') ? 'selected="selected"' : '';
		$selYes  = ($value == '1') ? 'selected="selected"' : '';
		$value	 = ($value == '1') ? L_BTN_YES : L_BTN_NO;
		?>
		<select class="modEntryVal" therow="<?php echo $dRow; ?>" tdIdx="<?php echo $rIdx; ?>" colType="<?php echo $dType; ?>">
			<option value="0" <?php echo $selNo; ?>><?php echo L_BTN_NO;  ?> (0)</option>
			<option value="1" <?php echo $selYes; ?>><?php echo L_BTN_YES; ?> (1)</option>
		</select>
		<div class="rightText pico">
			<button class="bouton ui-state-highlight prod_saveModVal"><span class="ui-icon ui-icon-check"></span></button>
			<button class="bouton ui-state-error prod_cancelModVal"><span class="ui-icon ui-icon-cancel"></span></button>
		</div>
	<script>
		$(function(){
			$('.modEntryVal').selectmenu({style: 'dropdown', width: 100}).change(function(){
				var tdIdx	= <?php echo $rIdx; ?>;
				var trID	= $(this).parents('tr').attr('idEntry');
				var newVal	= ($(this).val() == '0') ? '<?php echo L_BTN_NO ?>' : '<?php echo L_BTN_YES ?>';
				$.each(selected, function(i,idElem){
					if (idElem == trID) return true;
					var tdList = $('table tr[idEntry="'+idElem+'"]').find('td');
					$(tdList[tdIdx]).html(newVal);
				});
			});
		});
	</script>



<?php elseif ($dType == 'timecode'):
	$valTC = explode(':', $value); ?>
	<input type="text"class="ui-corner-all noBorder pad3 marge10r fondBlanc" size="10" maxlength="11" id="modEntryVal_TCplain" value="<?php echo $value; ?>" />
	|
	<div class="inline mid marge10l modEntryVal_TC" therow="<?php echo $dRow; ?>" tdIdx="<?php echo $rIdx; ?>" colType="<?php echo $dType; ?>">
		<input type="text" class="ui-corner-all noBorder pad3 fondBlanc numericInput" size="2" maxlength="2" idx="0" value="<?php echo $valTC[0]; ?>" /> :
		<input type="text" class="ui-corner-all noBorder pad3 fondBlanc numericInput" size="2" maxlength="2" idx="1" value="<?php echo $valTC[1]; ?>" /> :
		<input type="text" class="ui-corner-all noBorder pad3 fondBlanc numericInput" size="2" maxlength="2" idx="2" value="<?php echo $valTC[2]; ?>" /> :
		<input type="text" class="ui-corner-all noBorder pad3 fondBlanc numericInput" size="2" maxlength="2" idx="3" value="<?php echo $valTC[3]; ?>" />
	</div>
	<div class="inline mid marge10l rightText pico">
		<button class="bouton ui-state-highlight prod_saveModVal"><span class="ui-icon ui-icon-check"></span></button>
		<button class="bouton ui-state-error prod_cancelModVal"><span class="ui-icon ui-icon-cancel"></span></button>
	</div>
	<script>
		$(function(){
			$('#modEntryVal_TCplain').keyup(function(){
				var newTC = $(this).val();
				var TCarr = newTC.split(':');
				$.each(TCarr, function(i, TCv){
					if (TCv == '') TCv = '00';
					if (TCv.length == 1) TCv = '0'+TCv;
					$('.modEntryVal_TC').find('input[idx="'+i+'"]').val(TCv);
				});
			});

			$('.modEntryVal_TC').find('input')
				.change(function(){
					var tci = $(this).val();
					if (tci == '') tci = '00';
					if (tci.length == 1) tci = '0'+tci;
					$(this).val(tci);
				})
				.keyup(function(){
					var tdIdx	= <?php echo $rIdx; ?>;
					var trID	= $(this).parents('tr').attr('idEntry');
					var val	= '';
					$('.modEntryVal_TC').find('input').each(function(){
						var tci = $(this).val();
						if (tci == '') tci = '00';
						if (tci.length == 1) tci = '0'+tci;
						val += tci+':';
					});
					var newVal = val.replace(/\:$/g, '');
					$('#modEntryVal_TCplain').val(newVal);
					$.each(selected, function(i,idElem){
						if (idElem == trID) return true;
						var tdList = $('table tr[idEntry="'+idElem+'"]').find('td');
						$(tdList[tdIdx]).html(newVal);
					});
				});
		});
	</script>



<?php elseif ($dType == 'int' || $dType == 'float'): ?>
	<input type="text" class="ui-corner-all noBorder pad3 fondBlanc modEntryVal w9p numericInput" therow="<?php echo $dRow; ?>" colType="<?php echo $dType; ?>" value="<?php echo $value; ?>" />
	<div class="rightText pico">
		<button class="bouton ui-state-highlight prod_saveModVal"><span class="ui-icon ui-icon-check"></span></button>
		<button class="bouton ui-state-error prod_cancelModVal"><span class="ui-icon ui-icon-cancel"></span></button>
	</div>
	<script>
		$(function(){
			$('.modEntryVal').keydown(function(e){
				var rType = $(this).attr('colType');
				if (rType == 'int' && e.which == 190) // interdire le "." si nbre entier
					return false;
			});
			$('.modEntryVal').keyup(function(){
				var tdIdx	= <?php echo $rIdx; ?>;
				var trID	= $(this).parents('tr').attr('idEntry');
				var newVal	= $(this).val();
				$.each(selected, function(i,idElem){
					if (idElem == trID) return true;
					var tdList = $('table tr[idEntry="'+idElem+'"]').find('td');
					$(tdList[tdIdx]).html(newVal);
				});
			});
		});
	</script>



<?php else: ?>
	<textarea class="ui-corner-all noBorder pad3 fondBlanc modEntryVal w9p" style="min-width:180px; min-height:80px" therow="<?php echo $dRow; ?>" colType="<?php echo $dType; ?>"><?php echo $value; ?></textarea>
	<div class="rightText pico">
		<button class="bouton ui-state-highlight prod_saveModVal"><span class="ui-icon ui-icon-check"></span></button>
		<button class="bouton ui-state-error prod_cancelModVal"><span class="ui-icon ui-icon-cancel"></span></button>
	</div>
	<script>
		$(function(){
			$('.modEntryVal').keyup(function(){
				var tdIdx	= <?php echo $rIdx; ?>;
				var trID	= $(this).parents('tr').attr('idEntry');
				var newVal	= $(this).val();
				$.each(selected, function(i,idElem){
					if (idElem == trID) return true;
					var tdList = $('table tr[idEntry="'+idElem+'"]').find('td');
					$(tdList[tdIdx]).html(newVal.replace(/[\n\r]/g, '<br />'));
				});
			});
		});
	</script>
<?php endif; ?>

<script>
	var oldVal  = '<?php echo addslashes(preg_replace('/\n/', "<br />", $value)); ?>';
	var modType = '<?php echo $dType; ?>';
	$(function(){
		$('.bouton').button();
		$('.modEntryVal').focus();

		$('input').keyup(function(e){
			if (e.keyCode == 13)
				$('.prod_saveModVal').click();
		});

		// click sur bouton SAVE modif
		$('.prod_saveModVal').click(function(){
			var thisID	= $(this).parents('tr').attr('idEntry');
			var lines	= [thisID];
			$.each(selected, function(i,idElem){
				if (idElem == thisID) return true;
				lines.push(idElem);
			});

			var thisEntry = $(this).parents('td').find('.modEntryVal');
			if (modType == 'timecode')
				thisEntry = $('.modEntryVal_TC');
			var thisRow  = thisEntry.attr('therow');
			var thisVal  = thisEntry.val();

			if (modType == '' || thisRow === undefined || thisVal === undefined) {
				alert('There was some errors. Please reload and try again.');
				console.log(modType, thisRow, thisVal);
				return;
			}

			if (modType == 'tags') {
				var poss    = $.parseJSON('<?php echo json_encode($poss); ?>');
				var theIds  = thisEntry.val();
				if (theIds === null || theIds === undefined)
					thisVal = '';
				else {
					var theVals = [];
					$.each(theIds, function(i,idV){
						theVals.push(poss[idV]);
					});
					thisVal = JSON.encode(theVals);
				}
			}
			if (modType == 'menu_rel_multiple')
				thisVal = JSON.encode(thisEntry.val());
			if (modType == 'calendar') {
				var dateVal = thisEntry.datepicker("getDate");
				thisVal = $.datepicker.formatDate("yy-mm-dd 00:00:00", dateVal);
			}
			if (modType == 'timecode') {
				var theVal = '';
				$('.modEntryVal_TC').find('input').each(function(){
					var tci = $(this).val();
					if (tci == '') tci = '00';
					if (tci.length == 1) tci = '0'+tci;
					theVal += tci+':';
				});
				thisVal = theVal.replace(/\:$/g, '');
			}
			var ajaxReq = 'action=modifyLines'
						+'&table='+activeTable
						+'&customMode='+customMode
						+'&projID='+project_ID
						+'&modType='+modType
						+'&lines='+encodeURIComponent(JSON.encode(lines))
						+'&row='+thisRow
						+'&newVal='+encodeURIComponent(thisVal);
			AjaxJson(ajaxReq, "depts/prod_actions", retourModifLine, $(this).parents('td'));
		});

		// Click sur bouton annuler modif
		$('.prod_cancelModVal').click(function(){
			editMode = false;
			if (selected.length >= 1) {
				$('.arboItem[prodCat="'+activeTable+'"]').click();
				return;
			}
			$(this).parents('td').html(oldVal);
		});
	});
</script>