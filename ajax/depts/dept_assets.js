
var oldDescr;
// Document ready
$(function() {

	// Pour griser les boutons de départements si pas d'infos pour cet asset
	if (window['nameAsset'] != undefined && window['disabledDepts'] != undefined) {
		$('.deptBtn[content="06_assets"]').addClass('ui-state-disabled noBG noBorder');
		$.each(disabledDepts, function(i, dept) {
			$('#assets_depts').find('.deptBtn[label="'+dept+'"]').removeClass('ui-state-disabled noBG noBorder');
		});
		$('#assets_depts').find('.deptBtn[label="'+dept+'"]').removeClass('ui-state-disabled noBorder');
	}

	$('#displayPathAsset').attr('title','created by '+creatorAsset);

	$('#assetDescription').slimScroll({
		position: 'right',
		height: '75px',
		size: '10px',
		wheelStep: 10,
		railVisible: true
	});

	recalc_scrolls_asset();

	// Empêche les drop de fichier dans le browser
	$(document).bind('drop dragover', function (e) {
		e.preventDefault();
	});

	// Upload progress bar
	$('#vignetteAsset_upload').bind('fileuploadprogress', function (e, data) {
		var filename = data.files[0].name;
		var percent =  data.loaded / data.total * 100;
		$('#retourAjax').find('.uploadProg[filename="'+filename+'"]')
						.progressbar({value: percent})
						.children('span').html('speed : '+Math.round(data.bitrate / 10000)+'Kb/s => '+Math.round(percent)+' %...');
	});

	// Init de l'uploader de vignette ASSET
	$('#vignetteAsset_upload').fileupload({
		url: "actions/upload_vignettes.php?type=asset",
		dataType: 'json',
		dropZone: $('#vignetteAsset'),
		drop: function (e, data) {
			$('#retourAjax')
				.html('Sending vignette...<br /><div class="uploadProg mini" filename="'+data.files[0].name+'"><span class="floatL marge5 colorMid"></span></div>')
				.addClass('ui-state-highlight')
				.show(transition);
		},
		done: function (e, data) {
			var retour = data.result;
			if (retour[0].error) {
				$('#retourAjax')
					.append('<br /><span class="colorErreur gras">Failed : '+retour[0].error+'</span>')
					.addClass('ui-state-error')
					.show(transition);
			}
			else {
				var ajaxReq = "action=moveVignette&idProj="+idProj+"&nameAsset="+nameAsset+"&pathAsset="+pathAsset+"&vignetteName="+decodeURI(retour[0].name);
				AjaxJson(ajaxReq, "depts/assets_actions", retourAjaxAssets, true);
			}
		}
	});
	// Modif du status de l'asset
	$('.modifAssetStatus').click(function(){
		if (hasStep === false) {
			if (!confirm('Set the step for this department ? Sure ?')) return;
		}
		var idNewStatus = $(this).attr('idStatus');
		var ajaxReq = "action=modifAssetStatus&nameAsset="+nameAsset+"&idProj="+idProj+"&idDept="+deptID+"&idNewStatus="+idNewStatus;
		AjaxJson(ajaxReq, "depts/assets_actions", retourAjaxAssets, true);
	});

	// Show asset (unHide)
	$('.setHideAssetOff').click(function(){
		var ajaxReq = "action=modifAssetHide&nameAsset="+nameAsset+"&hidden=show";
		AjaxJson(ajaxReq, "depts/assets_actions", retourAjaxAssets, true);
	});
	// Hide asset
	$('.setHideAssetOn').click(function(){
		var ajaxReq = "action=modifAssetHide&nameAsset="+nameAsset+"&hidden=hide";
		AjaxJson(ajaxReq, "depts/assets_actions", retourAjaxAssets, true);
	});

	// Click sur modif description
	$('#modifDescrBtn').click(function(){
		$(this).hide();
		$(this).parent('div').find('.modifBtns').show();
		oldDescr = $('#assetDescription').html();
		var oldDescrTxt = oldDescr.replace(/<br\s*\/?>/mg, "\r");
		if(oldDescrTxt == '<span class="ui-state-disabled"><i>No description</i></span>') oldDescrTxt = '';
		$('#assetDescription').html('<textarea class="fondSect3 ui-corner-all noBorder w9p noMarge pad3" rows="3" id="newDescrAsset">'+oldDescrTxt+'</textarea>');
	});
	// Validation modif descritpion
	$('#modifDescrOK').click(function(){
		var newDescr   = $('#newDescrAsset').val();
		if (newDescr == oldDescr) { $('#modifDescrANNUL').click(); return; }
		var ajaxReq = "action=modifAssetDescr&nameAsset="+nameAsset+"&newDescr="+encodeURIComponent(newDescr);
		AjaxJson(ajaxReq, "depts/assets_actions", retourAjaxAssets, true);
	});
	// Annulation modif description
	$('#modifDescrANNUL').click(function(){
		$(this).parent('div').hide();
		$('#modifDescrBtn').show();
		$('#assetDescription').html(oldDescr);
	});

	// Modif de catégorie
	$('#modifCategBtn').click(function(){
		var btnPos		= $(this).position();
		var btnPosTop	= btnPos.top;
		$('#categModifBox').css({top: btnPosTop+'px'}).show(transition);
	});
	$('.categModifLine').click(function(){
		var idNewCateg = $(this).attr('idCat');
		var ajaxReq = "action=modifAssetCateg&nameAsset="+nameAsset+"&idNewCat="+idNewCateg;
		AjaxJson(ajaxReq, "depts/assets_actions", retourAjaxAssets, true);
	});

	// Modif (assignation) Handler
	$('#btn_HandleAsset').click(function(){
		if (!authHandling) return;
		var wi = parseInt($('#asset_hungBy').width());
		var le = wi - 145;
		var aH = parseInt($('#assetDetails').height());
		$('#handlerModifBox').css({top: '20px', left: le+'px'}).show(transition, function(){
			$('.handlersList').slimScroll({position: 'right', height: aH+'px', size: '10px', wheelStep: 10, railVisible: true});
		});
	});
	$('.handlerModifLine').click(function(){
		if ($(this).hasClass('ui-state-highlight')) return;
		var idNewHandler = $(this).attr('idHandler');
		var ajaxReq = "action=modifAssetHandler&idProj="+idProj+"&nameAsset="+nameAsset+"&idNewHandler="+idNewHandler;
		AjaxJson(ajaxReq, "depts/assets_actions", retourAjaxAssets, true);
	});

	// Fermeture d'une modif box
	$('.closeModifBox').click(function(){
		$('#categModifBox').hide(transition);
		$('#handlerModifBox').hide(transition);
	});

	// modal gestion team asset
	$('#addArtistToAssetInput').multiselect({height: '340px', selectedList: 4, noneSelectedText: 'Aucun', selectedText: '# artists', checkAllText: ' ', uncheckAllText: ' '});

	// BOUTON ajout d'artiste à la liste de l'équipe de l'asset
	$('#showAddArtistToAsset').toggle(
		function(){
			$('#addArtistToAssetDiv').show(transition);
			$(this).find('span').addClass('ui-icon-minus');
		},
		function(){
			$('#addArtistToAssetDiv').hide(transition);
			$(this).find('span').removeClass('ui-icon-minus');
		}
	);
	$('#addArtistToAssetBtn').click(function(){
		var artistToAdd = $('#addArtistToAssetInput').val();
		var jsonArtists = JSON.encode(artistToAdd);
		var ajaxReq = 'action=modAssetTeam&nameAsset='+nameAsset+'&newTeam='+jsonArtists;
		AjaxJson(ajaxReq, 'depts/assets_actions', retourAjaxAssets, true);
		$('#showAddArtistToAsset').click();
	});

	// ouverture de la fenêtre des scènes
	$('#showAssetScenes').click(function(){
		var $depModal = $('#assetScenesModal').clone();
		$depModal.dialog({
			autoOpen: true, height: 600, width: 500, modal: true, resizable: false,
			show: "fade", hide: "fade",
			open: function() {
				$(this).slimScroll({position: 'right', height: '500px', size: '10px', wheelStep: 10, railVisible: true});
			},
			close: function() { $(this).remove(); },
			buttons: {'Close' : function() { $(this).dialog('close'); } }
		});
	});

	// ouverture de la fenêtre des inter-dépendances
	$('#showAssetDependencies').click(function(){
		var $depModal = $('#assetDepModal').clone();
		$depModal.dialog({
			autoOpen: true, height: 600, width: 500, modal: true, resizable: false,
			show: "fade", hide: "fade",
			open: function() {
				$(this).slimScroll({position: 'right', height: '500px', size: '10px', wheelStep: 10, railVisible: true});

				// Click sur un asset (item) dans la fenêtre des dépendances
				$(this).off('click', '.assetItemDep');
				$(this).on('click', '.assetItemDep', function() {
					var nameDep = $(this).attr('filename');
					var pathDep = $(this).attr('filePath');
					var params	  = {nameAsset : nameDep, pathAsset : pathDep, idProj: idProj, titleProj: titleProj, dept: dept, deptID: deptID};
					$('#assetDetails').load('modals/showAsset.php', params, function(){
						$('#displayPathAsset').html(pathDep+nameDep);
						// surligne le bon asset dans la vue Tags
						$('.assetItemTag').removeClass('ui-state-activeFake');
						$('.assetItemTag[filename="'+nameDep+'"]').addClass('ui-state-activeFake');
						// surligne le bon asset dans la vue Categ
						$('.assetItemCateg').removeClass('ui-state-activeFake');
						$('.assetCategList').hide();
						$('.assetItemCateg[filename="'+nameDep+'"]').addClass('ui-state-activeFake').parent('.assetCategList').show(150);
						// Ouvre les bons sous dossiers dans la vue Tree
						closeArbo();
						$('.assetItem[filename="'+nameDep+'"]').css('color','#26b3f7');
						$('.assetFolderContent').has('.assetItem[filename="'+nameDep+'"]').each(function(){
							$(this).prev('.assetFolder').click();
						});
						// Ferme la fenêtre
						$depModal.dialog('close');
					});
				}).on('mouseenter', '.assetItemDep', function(){
					$(this).addClass('ui-state-hover');
				}).on('mouseleave', '.assetItemDep', function(){
					$(this).removeClass('ui-state-hover');
				});
				// Ouverture des catégories
				$(this).off('click', '.depCategHead');
				$(this).on('click', '.depCategHead', function(){
					$('.depCategList').hide(150);
					$(this).next('.depCategList').show(150);
				});

			},
			close: function() { $(this).remove(); },
			buttons: {'Close' : function() { $(this).dialog('close'); } }
		});
	});

	// Suppression d'un asset en BDD si n'existe plus dans fichier XML
	$('#deleteAssetDB').click(function(){
		if (!confirm('Delete this asset from DB?\n\nAre you sure?')) return;
		var assID = $(this).attr('idAsset');
		AjaxJson('action=deleteAsset&idProj='+idProj+'&idAsset='+assID, 'depts/assets_actions', retourAjaxAssets, 'reloadSansAsset');
	});

	// Ajout d'un asset dans le XML
	$('#addAssetToXML').click(function(){
		if (!confirm('Add this asset to XML?\n\nThis is needed to be able to work on it.\nHowever this will make it impossible to be removed or renamed.\n\nAre you sure?')) return;
		var assID = $(this).attr('idAsset');
		AjaxJson('action=addAssetToXML&idProj='+idProj+'&idAsset='+assID, 'depts/assets_actions', retourAjaxAssets, true);
	});

	// Renommer un asset uniquement présent en BDD
	$('#renameAssetDB').click(function(){
		var assID = $(this).attr('idAsset');
		var newName = prompt('Enter new NAME for asset "'+nameAsset+'":');
		if (newName == null || newName == '')
			return;
		localStorage['openAsset_'+idProj] = newName;
		AjaxJson('action=renameAsset&idProj='+idProj+'&idAsset='+assID+'&newName='+newName, 'depts/assets_actions', retourAjaxAssets, true);
	});

	// Changer le chemin d'un asset uniquement présent en BDD
	$('#repathAssetDB').click(function(){
		var assID = $(this).attr('idAsset');
		$.getJSON( "../../actions/depts/assets_actions.php", {action:'getAllXMLpaths', projID :idProj}, function( data ) {
			if (data.error !== 'OK') {
				retourAjaxMsg(data, false);
				return;
			}

			var $chPathModal = $('#chPathModele').clone();
			$chPathModal.dialog({
				autoOpen: true, height: 200, width: 700, modal: true, resizable: false,
				show: "fade", hide: "fade",
				open: function() {
					$chPathModal.find('#changePathAssName').html(nameAsset);
					$chPathModal.find('#changePathInput').autocomplete({source: data.paths}).val(pathAsset).focus();
				},
				buttons: {
					'Cancel' : function() {
						$(this).dialog('close');
					},
					'OK' : function(){
						var newPath = $chPathModal.find('#changePathInput').val();
						if (newPath == null || newPath == '')
							return;
						localStorage['openAssetPath_'+idProj] = newPath.replace(/[\/]{1,}$/, '')+'/';
						AjaxJson('action=changePathAsset&idProj='+idProj+'&idAsset='+assID+'&newPath='+newPath, 'depts/assets_actions', retourAjaxAssets, true);
						$(this).dialog('close');
					}
				},
				close: function() { $(this).remove(); }
			});
		});
	});

});
// FIN document ready

	function recalc_scrolls_asset(){
		var assetViewH = $('#assetViewLeft').height();
		$('#messagesList').slimScroll({
			position: 'right',
			height: assetViewH+'px',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		});
		$('#retakesList').slimScroll({
			position: 'left',
			height: assetViewH+'px',
			size: '10px',
			wheelStep: 10,
			railVisible: true
		});
	}