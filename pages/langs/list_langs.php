<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

	if (!$_SESSION['user']->isDev()) die('list langs : Access denied.');

	// Listing langues
	$table_fields = Liste::getRows(TABLE_LANGS);			// recup noms columns

	$l = new Liste();
	$l->getListe(TABLE_LANGS,'*', 'constante', 'ASC');		// recup values
	$lang_constList = $l->simplifyList();	// re-trie par l'id
?>

<script>
	$(function(){
		$('.bouton').button();

		// Bouton d'ajout d'une langue
		$('#addLang').click(function() {
			$('.langList').find('.trHead').append(
				'<td>'
					+'<input type="text" class="w50 pad3 ui-corner-all noBorder fondSect3" id="addLangName" onKeypress="return checkChar(event,true,true,true)" /> '
					+'<div class="inline bot nano">'
					  +'<button title="valider" id="validAddLang"><span class="ui-icon ui-icon-check"></span></button>'
					+'</div>'
				+'</td>');
			$('#validAddLang').button();
		});

		// Validation de l'ajout de langue
		$('.langList').on('click', '#validAddLang', function(){
			var newLangName = $('#addLangName').val();
			var ajaxReq = 'action=addLang&newLang='+newLangName;
			AjaxJson(ajaxReq, '../actions/lang_actions', retourAjaxMsg, true);
		});

		// Delete de langue
		$('.delLang').click(function(){
			var row = $(this).attr('row');
			if (!confirm('Do you really want to delete language "'+row+'" ? Sure ??'))
				return;
			var ajaxReq = 'action=delLang&langName='+row;
			AjaxJson(ajaxReq, '../actions/lang_actions', retourAjaxMsg, true);
		});

		// export langue to csv
		$('.expLang').click(function(){
			var row = $(this).attr('row');
			if (!confirm("Export all language entries of '"+row+"' to CSV file ?\nNotes:\n- The CSV separator will be the semicolon ( ; )\n- The list will be alphabetically sorted with 'constante' field."))
				return;
			window.location.href = 'actions/lang_to_csv.php?lang='+row;
		});

		// Traitement des boutons OK (clic)
		$('.modLangBtn').click(function(){
			// récupération de l'id et des inputs de la ligne
			var idConst  = $(this).parents('tr').attr('idConst');				// récupère l'id de la constante à redéfinir
			var langVals = $('tr[idConst="'+idConst+'"]').find('.langValue');	// récupère tous les input de cette ligne
			// construction de la requête
			var ajaxReq  = 'action=modif&id='+idConst;
			$.each(langVals, function(i,val) {								// Pour chaque input présent sur la ligne
				var langName = $(val).attr('lang');								// récup le nom de la langue (attribut 'lang' de l'input)
				var langVal  = encodeURIComponent($(val).attr('value'));		// récup la valeur de la traduction (attribut 'value' de l'input)
				ajaxReq += '&'+langName+'='+langVal;							// Et on ajoute tout ça à la chaine de requête
			});
			// éxécution de la requête
			AjaxJson(ajaxReq, '../actions/lang_actions', retourAjaxMsg);	// Enfin on éxécute la requête, et on affiche le retour dans la div #retourAjax (créée dans langs.php)
		});


		// Bouton "ajouter une constante"
		$('#addConstLang').click(function(){
			$('#langAddPlace').html($('#modeleAddConstante').html());
		});


		// Bouton "validation de l'ajout"
		$('.langList').on('click', '#validAddConstante', function(){
			var ajaxReq = 'action=addConst';
			var ok = false;
			$('.addConstInput').each(function(i,input){
				var row   = $(input).attr('row');
				var value = $(input).val();
				if (row == 'constante' && (value == undefined || value == '')) return false;
				ajaxReq += '&'+row+'='+value;
				ok = true;
			});
			if (ok)
				AjaxJson(ajaxReq, '../actions/lang_actions', retourAjaxMsg, true);
			else {
				$('#retourAjax').html('You must fill the "constante" field (please also check for allowed characters) !')
								 .addClass('ui-state-error').removeClass('ui-state-highlight').show(transition);
			}
		});

		// empêche de taper des caractères non autorisés
		$('.langList').on('keypress', '.addConstInput[row="constante"]', function(e){
			return checkChar(e,true,true,true);
		});

		// ajoute le L_ devant le nom de la constante si oublié, et mets en majuscule
		$('.langList').on('blur', '.addConstInput[row="constante"]', function(){
			var constVal = $(this).val();
			if (constVal.match(/^L_/) == null) constVal = 'L_'+constVal;
			$(this).val(constVal.toUpperCase());
		});


		// Bouton "supprimer une constante"
		$('.delConstBtn').click(function(){
			if (!confirm("Delete this constant ? Are you sure ?"))
				return;
			var idConst = $(this).parents('tr').attr('idConst');
			var ajaxReq = 'action=delConst&id='+idConst;
			AjaxJson(ajaxReq, '../actions/lang_actions', retourAjaxMsg, true);
		});
	});

</script>

<style type="text/css">
	.langList tr:hover { background-color: #555; }
</style>

<div class="stageContent pad5">

	<div class="mini margeTop10">
		<table class="langList gros">
			<tr class="trHead">
			<?php
				$nbLangs = -2;
				foreach ($table_fields as $t) {
					if ($t != 'id') {
						echo '<th class="fondPage pad3">';
						if ($t != 'en' && $t != 'constante')
							echo '<span class="floatR ui-icon ui-icon-trash doigt delLang" title="delete language" row="'.$t.'"></span>';
							// export lang to csv
							if ($nbLangs != -1)
									echo '<span class="floatR ui-icon ui-icon-extlink doigt expLang" title="Export language to CSV file" row="'.$t.'"></span>';

						echo $t.'</th>';
					}
					$nbLangs++;
				}
				echo '	<th class="micro">
							<button class="bouton" title="Add a language" id="addLang"><span class="ui-icon ui-icon-plusthick"></span></button>
						</th>';
			echo '  </tr>
					<tr>
						<td class="micro padB10" colspan="'.($nbLangs+2).'" id="langAddPlace">
							<button class="bouton" title="Add a language entry (constante)" id="addConstLang"><span class="ui-icon ui-icon-plus"></span></button>
						</td>
					</tr>';

			foreach ($lang_constList as $idConst => $valConst) {
				echo '<tr idConst="'.$idConst.'">';
				unset($valConst['id']);
				foreach ($valConst as $col => $lang) {
					if ($col == "constante") {
						$classBtn = (preg_match('/BTN_/', $lang)) ? 'colorBtnFake' : '' ;
						echo '<td class="w150 '.$classBtn.'">'.$lang.'</td>';
					}
					else
						echo '<td class="w150">
								<input type="text" class="w9p pad3 ui-corner-all noBorder fondSect3 langValue" lang="'.$col.'" value="'.$lang.'" />
							</td>';
				}
				echo '<td class="nano">
						<button class="bouton modLangBtn" title="Validate language entry (constante) modification"><span class="ui-icon ui-icon-check"></span></button>
						<button class="bouton delConstBtn" title="Delete language entry (constante)"><span class="ui-icon ui-icon-trash"></span></button>
					</td>';
				echo '</tr>';
			}
		?>
		</table>
	</div>
</div>



<div class="hide" id="modeleAddConstante">
	<table class="w100p">
		<?php foreach ($table_fields as $t) {
			if ($t != 'id')
				echo '<td class="w150 giant"><input type="text" class="w9p pad3 ui-corner-all noBorder fondSect3 addConstInput" row="'.$t.'" /></td>';
		}
		?>
			<td class="micro"><button class="bouton" id="validAddConstante"><span class="ui-icon ui-icon-check"></span></button></td>
	</table>
</div>