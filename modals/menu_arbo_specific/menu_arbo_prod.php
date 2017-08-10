<?php

$lct = new Liste();
$customRows = $lct->getRows(TABLE_PROD_CUSTOM);
$customCats = Array();
$acplCT = '[';
foreach ($customRows as $i => $cat) {
	if ($cat == 'id' || $cat == 'ID_project' || $cat == 'deleted')
		continue;
	$acplCT .= "'$cat',";
	$ct = new CustomTable($cat, $idProj);
	if ($ct->getRows()) {
		$customCats[] = $cat;
	}
}
$autocompleteCT = preg_replace('/,$/', '', $acplCT) . ']';

$table = $_POST['template'];
?>


<script>
	var activeTable = '<?php echo $table; ?>';
	var autocompleteCT = <?php echo preg_replace('/_/', ' ',$autocompleteCT); ?>;

	$(function(){
		$('.arboItem').removeClass('ui-state-focus');
		$('.arboItem[prodCat='+activeTable+']').addClass('ui-state-focus');
		$('#addProdCustomCatInput').autocomplete({source: autocompleteCT, autoFocus: true});

		$('#addProdCustomCatBtn').click(function(){
			$('#addProdCustomCat').show(150);
			$('#addProdCustomCatInput').val('').focus();
		});

		$('#cancelAddProdCustomCat').click(function(){
			$('#addProdCustomCat').hide(150);
		});

		$('#validAddProdCustomCat').click(function(){
			var newCatName = $('#addProdCustomCatInput').val();
			var req = 'action=addCustomCat&newCat='+newCatName.replace(/ /g, '_')+'&idProj='+idProj;
			AjaxJson(req, 'depts/prod_actions', retourAjaxMsg, true );
		});

		// Bouton import CSV
		$('#prod_importCSV').click(function(){
			var $dialog = $('#modalImportTable').clone(true);
			$dialog.dialog({
				autoOpen: true, height: 240, width: 500, modal: false, hide: "fade",
				open: function() {
					// Init de l'uploader du fichier à importer
					$(this).find('#importTableFile').fileupload({
						url: "actions/upload_importCSV.php",
						dataType: 'json',
						dropZone: null,
						change: function (e, data) {
							$('#retourAjax')
								.html('Importing file...<br /><div class="uploadProg mini" filename="'+data.files[0].name+'"><span class="floatL marge5 colorMid"></span></div>')
								.addClass('ui-state-highlight').show(transition);
						},
						done: function (e, data) {
							var retour = data.result;
							if (retour[0].error) {
								$('#retourAjax')
									.html('<span class="colorErreur gras">Failed : '+retour[0].error+'</span>')
									.addClass('ui-state-error').show(transition);
							}
							else {
								$('#retourAjax').html('OK, file uploaded.').removeClass('ui-state-error');
								$dialog.find('.importTableFileDiv').html('<span class="ui-state-disabled">File uploaded.</span>');
								setTimeout(function(){ $('#retourAjax').hide(transition); }, 2000);
								importFileTempName = retour[0].name;
							}
						}
					});
				},
				buttons: {
					'OK' : function() {
						var tableName = $(this).find('.importTableName').val();
						if (tableName == '') { alert('Please give a name for the table to create.'); return false; }
						if (importFileTempName == '') { alert('Please upload a file to import.'); return false; }
						var ajaxReq = 'action=createCatFromCSVFile&customMode='+customMode+'&projID='+project_ID+'&tableName='+tableName+'&tempFileName='+importFileTempName;
						AjaxJson(ajaxReq, 'depts/prod_actions', retourAjaxMsg, true);
						$(this).dialog('close');
					},
					'Cancel' : function() { $(this).dialog('close');  }
				},
				close: function() { $dialog.dialog('destroy'); $dialog.remove(); }
			});
		});

		$('.renameCustomCat').click(function(){
			var theCat = $(this).attr('prodCat');
			var $dialog = $('#dialogRenameCat').clone(true);
			$dialog.dialog({
				autoOpen: true, height: 140, width: 500, modal: true, hide: "fade", title: 'Rename category "'+theCat+'"',
				close: function() { $dialog.dialog('destroy'); $dialog.remove(); },
				buttons: {
					'OK' : function() {
						var newName = encodeURIComponent($(this).find('.catRenameNewName').val());
						var req = 'action=renameCustomCat&idProj='+idProj+'&cat='+encodeURIComponent(theCat)+'&newName='+newName;
						AjaxJson(req, 'depts/prod_actions', retourAjaxMsg, true );
						$(this).dialog('close');
					},
					'Cancel' : function() { $(this).dialog('close');  }
				}
			});
		});

		$('.customCat').hover(
			function(){
				$(this).find('.renameCustomCat').show();
			},
			function(){
				$(this).find('.renameCustomCat').hide();
			});

	});
</script>

<div class="ui-state-focus mini ui-corner-top pad3 doigt gras" id="arboHeadProj" style="padding:5px 0px 6px 0px;" title="click to display or refresh current view" help="menu_shortcuts">
	<?php echo $titleProj; ?>
</div>

<div class="fondSect1 ui-corner-bottom" id="arboSeq">
	<div class="ui-state-highlight">
		<div class="inline mid">SaAM prod categories</div>
	</div>
	<div class="bordColInv1 arboItem ui-state-focus" prodCat="saam_sequences"><?php echo mb_strtoupper(L_SEQUENCES, 'UTF-8'); ?></div>
	<div class="bordColInv1 arboItem" prodCat="saam_shots"><?php echo mb_strtoupper(L_SHOTS, 'UTF-8'); ?></div>
	<div class="bordColInv1 arboItem" prodCat="saam_scenes"><?php echo mb_strtoupper(L_SCENES, 'UTF-8'); ?></div>
	<div class="bordColInv1 arboItem" prodCat="saam_derivatives"><?php echo mb_strtoupper(L_DERIVATIVES, 'UTF-8'); ?></div>
	<div class="bordColInv1 arboItem" prodCat="saam_cameras"><?php echo mb_strtoupper(L_CAMERAS, 'UTF-8'); ?></div>
	<div class="bordColInv1 arboItem" prodCat="saam_assets"><?php echo mb_strtoupper(L_ASSETS, 'UTF-8'); ?></div>
	<div class="bordColInv1 arboItem" prodCat="saam_tasks"><?php echo mb_strtoupper(L_TASKS, 'UTF-8'); ?></div>
	<div class="bordColInv1 arboItem" prodCat="saam_dailies"><?php echo mb_strtoupper(L_DAILIES, 'UTF-8'); ?></div>
	<div class="bordColInv1 arboItem" prodCat="saam_users"><?php echo mb_strtoupper(L_USERS, 'UTF-8'); ?></div>
	<div class="catSeparator"></div>
	<div class="fondError pad3">
		<div class="inline mid">Custom categories</div>
		<div class="ui-state-highlight noBG noBorder doigt floatR" title="Create a custom category">
			<span class="inline mid ui-icon ui-icon-plusthick" id="addProdCustomCatBtn"></span>
			<span class="inline mid ui-icon ui-icon-document" title="Create custom category from CSV file (import)" id="prod_importCSV"></span>
		</div>
	</div>
	<div class="fondError marge5bot pad3 petit hide" id="addProdCustomCat">
		<input type="text" size="11" value="new categ" class="noBorder ui-corner-all fondPage pad3" id="addProdCustomCatInput" />
		<div class="inline mid pico">
			<div class="inline mid ui-state-highlight noBG noBorder doigt" id="validAddProdCustomCat"><span class="ui-icon ui-icon-check"></span></div>
			<div class="inline mid ui-state-error-text doigt" id="cancelAddProdCustomCat"><span class="ui-icon ui-icon-cancel"></span></div>
		</div>
	</div>
	<div class="catSeparator"></div>
	<?php foreach($customCats as $cat):
		$catName = preg_replace('/_/', ' ', $cat); ?>
		<div class="customCat">
			<div class="floatR doigt renameCustomCat hide" prodCat="<?php echo $cat; ?>"><span class="ui-icon ui-icon-pencil"></span></div>
			<div class="bordColInv1 arboItem" prodCat="<?php echo $cat; ?>">
				<?php echo $catName; ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>

<div class="hide" id="dialogRenameCat">
	<div class="inline mid w150">
		New name:
	</div>
	<div class="inline mid w300">
		<input type="text" class="noBorder noBG ui-corner-all fondSect3 pad3 w100p catRenameNewName" />
	</div>
</div>


<div class="hide" title="Import csv file" id="modalImportTable">
	This will <b>create a new</b> custom category!<br />
	<p class="ui-state-disabled">Note : the separator of the CSV file must be a semicolon (;)</p>
	<div class="margeTop5">
		<div class="inline mid w150 colorBtnFake">
			File to upload
		</div>
		<div class="inline mid importTableFileDiv">
			<input type="file" class="ui-corner-all noBorder fondSect3" id="importTableFile" size="30" />
		</div>
	</div>
	<div class="margeTop5">
		<div class="inline mid w150 colorBtnFake">
			Created category name
		</div>
		<div class="inline mid">
			<input type="text" class="ui-corner-all noBorder pad3 fondSect3 importTableName" size="30" />
		</div>
	</div>
</div>
