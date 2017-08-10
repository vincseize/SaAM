<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC'].'/checkConnect.php' );
	require_once('xml_fcts.php');

	extract($_POST);

// définition des divs et de leurs classes
$default_classes = 'ui-state-default ui-corner-all pad5 noBG gros doigt';
$default_style   = 'margin-left: 5px; border-top: none; border-bottom: none; height:16px;';
$divSfolder = '<div class="'.$default_classes.' inline assetFolder hide" style="'.$default_style.'" ';
$divFile    = '<div class="'.$default_classes.' assetItem hide" style="color:#000 !important; '.$default_style.'" ';
try {
	$ACL = new ACL($_SESSION['user']);
	$assetCreate = $ACL->check('ASSETS_CREATE');
	$assetAdmin  = $ACL->check('ASSETS_ADMIN');

	$p = new Projects($idProj);
    $projInfos	= $p->getProjectInfos();
	$teamAll	= $p->getEquipe();
	$dateEnd	= SQLdateConvert($projInfos[Projects::PROJECT_DEADLINE]);

	$assetsCategs = $_SESSION['CONFIG']['ASSETS_CATEGORIES'];

	$availExt = $_SESSION['CONFIG']['AV_ASSETS_EXTS'];
	sort($availExt);

}
catch(Exception $e) {  }
$level		= 0;

global $default_classes, $default_style, $divSfolder, $divFile, $level, $assetCreate, $assetAdmin;

// Fonction RÉCURSIVE
// le param $node correspond à une dimension de l'array issue du XML.
// Cette fonction retourne une chaine formatée pour le dept ASSETS
function recursive_folder_view($node) {
	global $divSfolder, $divFile, $level, $assetCreate, $assetAdmin;
	$level  += 1;
	$line = '<div class="assetFolderContent" style="border-left: 2px dashed #555; margin-left: 50px;">';
	if ($node['url'] == './') $node['url'] = '';
	$urlAsset = './'.$node['url'].$node['name'].'/';
	if (is_array(@$node['content']['dir'])) {
		foreach($node['content']['dir'] as $sNode) {
			if (in_array($sNode['name'], $_SESSION['CONFIG']['asset_exclude_dirs'])) continue;
			$line .= $divSfolder.' level="'.$level.'" path="'.$sNode['url'].$sNode['name'].'/">'
					. $sNode['name'];
			if ($assetCreate) {
				$line .= '<div class="inline mid" style="display:none;">'
						. '<span class="inline doigt marge10l ui-icon ui-icon-plus addSubFolderBtn" title="Create subfolder into this folder"></span>'
						. '<span class="inline doigt marge10l ui-icon ui-icon-image addAssetBtn" title="Create an asset into this folder"></span>';
				if (!is_array(@$sNode['content']))
					$line .= '<div class="inline ui-state-error marge10l noBG noBorder"><span class="inline doigt ui-icon ui-icon-trash delSubFolderBtn" title="delete this folder"></span></div>';
				$line .= '</div>';
			}
			$line .= '</div>';
			$line .= recursive_folder_view($sNode);
		}
	}
	if (is_array(@$node['content']['file'])) {
		$countFiles = 0;
		foreach($node['content']['file'] as $fileAsset) {
			$countFiles++;
			$assetGotDBinfo = true;
			try {
				$a = new Assets(false, $fileAsset['name']);
				$aHung		= $a->getHandler(Users::USERS_PSEUDO);
				$aHclass	= ($aHung) ? 'ui-state-error' : 'colorHard';
				$aHclass	= ($a->isActive()) ? $aHclass : 'ui-state-error noBG" style="color:#F23518 !important;"';
				$aHandler	= ($aHung) ? '<span class="inline bot ui-icon ui-icon-person"></span> '.$aHung : L_ASSET_FREE;
				$aHandler	= ($a->isActive()) ? $aHandler : '<span class="inline bot ui-icon ui-icon-cancel"></span>Hidden asset !&nbsp;';
				if (!$assetAdmin && !$a->isActive())
					continue;
			}
			catch(Exception $e) { $assetGotDBinfo = false; }
			$line .=  $divFile.' level="'.$level.'" filename="'. $fileAsset['name'] .'" filePath="'.$urlAsset.'">';
			if ($assetGotDBinfo) {
				$line .= '<div class="floatR mini '.$aHclass.'" title="Asset '.L_ASSET_HUNG_BY.'">'
							.$aHandler
						.'</div>';
			}
			$line .= '<b title="'. $fileAsset['name'] .'">'. $fileAsset['name'] .'</b>'
			.'</div>';
		}
	}
	$level  -= 1;
	$line .= '</div>';
	return $line;
}
try {
	$assetsArbo = get_assets_arbo($idProj,$titleProj);	// Récup de l'arborescence du masterFile_assets.xml
}
catch (Exception $e) {
	die($e->getMessage());
}
?>

<script>
	$(function() {
		$('.bouton').button();
		// Design interface de l'arborescence des assets
		$('.assetItem, .assetFolder[level!="0"]').hide();
		$('.assetFolder, .assetItem').hover(function(){ $(this).addClass('ui-state-hover'); }, function(){ $(this).removeClass('ui-state-hover'); });
		$('.assetFolder').hover(
			function() { $(this).find('div').show(); },
			function() { $(this).find('div').hide(); }
		);

		closeArbo();
		// Si un asset est défini en mémoire
		if (localStorage['openAsset_'+idProj]) {
			var theAsset = localStorage['openAsset_'+idProj];
			$('.assetItem[filename="'+theAsset+'"]').css('color','#26b3f7');
		}
		if (localStorage['openAssetPath_'+idProj]) {
			var thePath  = localStorage['openAssetPath_'+idProj];
			var path = thePath.substring(0, thePath.length - 1);
			path = path.replace(/^\.\//, '');
			var paths = path.split('/');
			var contPath = '';
			$.each(paths, function(i,path) {
				contPath += path+'/';
				openAssetTreeFolder($('.assetFolder[path="'+contPath+'"]'), 0, true);
			});
		}

		// Gestion des clicks sur assets (folder)
		$('.assetFolder').click(function(){
			openAssetTreeFolder($(this), 150);
		});

		// Création d'un sous-dossier ASSET selon dossier choisi
		$('.addSubFolderBtn').click(function(){
			var pathBase = $(this).parents('.assetFolder').attr('path');
			$(this).parents('.assetFolder').addClass('colorErrText');
			var folderName = prompt("Adding a subfolder to "+pathBase+'\nEnter a folder name:');
			$(this).parents('.assetFolder').removeClass('colorErrText');
			if (folderName == null || folderName == '') return;
			var ajaxStr = 'action=addPathFolder&projID='+idProj+'&folderPath='+pathBase+folderName;
			AjaxJson(ajaxStr, 'depts/assets_actions', retourAjaxAssets, 'reloadTreeView');
		});

		// Création d'un ASSET dans le sous-dossier choisi
		$('.addAssetBtn').click(function(){
			var pathBase = $(this).parents('.assetFolder').attr('path');
			$('#addAssetDiv').find('.addAsset_path').val(pathBase);
			$(this).parents('.assetFolder').addClass('colorErrText');
			$('#showAddPathFolder').hide(transition);
			$('#addAssetDiv').show(transition);
		});

		// Suppression d'un dossier du XML
		$('.delSubFolderBtn').click(function(){
			var pathBase = $(this).parents('.assetFolder').attr('path');
			if (!confirm('Delete folder "'+pathBase+'"?\nSure?'))
				return;
			var ajaxStr = 'action=deletePathFolder&projID='+idProj+'&folderPath='+pathBase;
			AjaxJson(ajaxStr, 'depts/assets_actions', retourAjaxAssets, 'reloadTreeView');
		});

		// Click sur un asset (item)
		$('.assetItem').click(function() {
			$('.assetItem').css('color','#000');
			$(this).css('color','#26b3f7');
			openAsset($(this).attr('filename'), $(this).attr('filePath'));
		});
	});

	// fermeture de tous les levels de l'arbo :
	function closeArbo () {
		$('.assetFolder').each(function(){
			$('.assetItem').css('color','#000');
			$(this).removeAttr('opened').css('border-bottom','none').next('.assetFolderContent').find('.assetItem, .assetFolder').removeAttr('opened').css('border-bottom','none').hide();
		});
	}

	// Ouvre un niveau de l'arbo
	function openAssetTreeFolder(elem, speed, forceOpen) {
		if (elem.attr('opened') == 'opened' && forceOpen == null) {
			elem.removeAttr('opened').css('border-bottom','none').next('.assetFolderContent').find('.assetItem, .assetFolder').removeAttr('opened').css('border-bottom','none').hide(speed);
		}
		else {
			var level = parseInt(elem.attr('level')) + 1;
			elem.attr('opened', 'opened').css('border-bottom','2px dashed #505050').next('.assetFolderContent').show().children('div[level="'+level+'"]').show(speed);
		}
	}
</script>


<?php if ($assetCreate): ?>
	<script>
	$(function() {
		// Design interface de l'ajout de folder / assets
		$('.inputCal').datepicker({dateFormat: 'dd/mm/yy', firstDay: 1, changeMonth: true, changeYear: true});
		$('.addAsset_extension').autocomplete({minLength: 0, source:extsAC}).val(localStorage['lastExtAsset']);
		$('.addAsset_category').selectmenu({style: 'dropdown'});
		$('.addAsset_team').multiselect({height: '200px', minWidth: 206, selectedList: 2, noneSelectedText: '<?php echo L_NOBODY; ?>', selectedText: '# artists', checkAllText: ' ', uncheckAllText: ' '});


		////////////////////////////////////////////////////////////////////////

		$('#addAssetValid').click(function(){
			var aN = $('#addAssetDiv').find('.addAsset_filename').val();
			var aE = $('#addAssetDiv').find('.addAsset_extension').val();
			if (aN.length < 2) { alert('Asset filename too short!'); return false; }
			if (aE.length < 2) { alert('Invalid asset extension!'); return false; }
			var addExtToDefauts = false;
			if (extsAC.indexOf(aE) == -1) {
				if (confirm('This extension is not in default available extensions.\nDo you want to add it to default extension list?\n\n(cancel this will still continue adding the asset.)'))
					addExtToDefauts = true;
			}
			localStorage['lastExtAsset'] = aE;
			var infos = {
				<?php echo Assets::ASSET_NAME; ?>		: $('#addAssetDiv').find('.addAsset_filename').val() + '.' + $('#addAssetDiv').find('.addAsset_extension').val(),
				<?php echo Assets::ASSET_PATH_REL; ?>	: $('#addAssetDiv').find('.addAsset_path').val(),
				<?php echo Assets::ASSET_TEAM; ?>		: $('#addAssetDiv').find('.addAsset_team').val(),
				<?php echo Assets::ASSET_DATE; ?>		: $('#addAssetDiv').find('.addAsset_date').datepicker("getDate"),
				<?php echo Assets::ASSET_DEADLINE; ?>	: $('#addAssetDiv').find('.addAsset_deadline').datepicker("getDate"),
				<?php echo Assets::ASSET_CATEGORY; ?>	: $('#addAssetDiv').find('.addAsset_category').val()
			};
			localStorage['openAsset_'+idProj] = infos.<?php echo Assets::ASSET_NAME; ?>;
			localStorage['openAssetPath_'+idProj] = infos.<?php echo Assets::ASSET_PATH_REL; ?>;
			var ajaxStr = 'action=addAsset&projID='+idProj+'&infos='+encodeURIComponent(JSON.encode(infos));
			if (addExtToDefauts)
				ajaxStr += '&addExtTodefault='+aE;
			AjaxJson(ajaxStr, 'depts/assets_actions', retourAjaxAssets, true);
			$('.assetFolder').removeClass('colorErrText');
			$('#addAssetDiv').hide(transition);
			$('#showAddPathFolder').show(transition);
		});

		$('#addAssetCancel').click(function(){
			$('.assetFolder').removeClass('colorErrText');
			$('#addAssetDiv').hide(transition);
			$('#showAddPathFolder').show(transition);
		});

		////////////////////////////////////////////////////////////////////////

		$('#showAddPathFolder').click(function(){
			$(this).hide();
			$('#addPathFolderDiv').show(transition);
		});

		$('#addPathFolderValid').click(function(){
			var newFolder = $('.addPathFolder_name').val();
			if (newFolder == '') return;
			var ajaxStr = 'action=addPathFolder&projID='+idProj+'&folderPath='+newFolder;
			AjaxJson(ajaxStr, 'depts/assets_actions', retourAjaxAssets, 'reloadTreeView');
			$('#addPathFolderDiv').hide(transition);
			$('#showAddPathFolder').show(transition);
		});

		$('#addPathFolderCancel').click(function(){
			$('#addPathFolderDiv').hide(transition);
			$('#showAddPathFolder').show(transition);
		});
	});

	</script>

	<div class="inline pad3 nano marge5" id="showAddPathFolder">
		<button class="bouton" title="Add a folder to asset's root folder"><span class="inline bot ui-icon ui-icon-plusthick"></span></button>
	</div>
	<div class="hide fondSect4 gros pad5" id="addPathFolderDiv">
		<div class="inline mid w80 colorSoft margeTop1">Folder name</div>
		<div class="inline mid margeTop1" style="width:calc(100% - 100px);" title="Folder name">
			<input type="text" class="noBorder pad3 ui-corner-all fondSect3 w100p addPathFolder_name" value="" />
		</div>
		<br />
		<div class="inline mid w80"></div>
		<div class="inline mid margeTop5 pico rightText" style="width:calc(100% - 100px);">
			<button class="bouton" id="addPathFolderValid"><span class="ui-icon ui-icon-check"></span></button>&nbsp;&nbsp;
			<button class="ui-state-error bouton" id="addPathFolderCancel"><span class="ui-icon ui-icon-cancel"></span></button>
		</div>
	</div>
	<div class="hide fondSect4 gros pad5" id="addAssetDiv">
		<div class="inline mid w80"></div>
		<div class="inline mid colorMid marge5bot">Create an asset</div>
		<br />
		<div class="inline mid w80 colorSoft margeTop1">Filename</div>
		<div class="inline mid margeTop1" style="width:calc(100% - 100px);" title="Asset filename and extension">
			<input type="text" class="noBorder pad3 ui-corner-all fondSect3 addAsset_filename" title="Asset filename" style="width:calc(100% - 100px);" value="" />.
			<input type="text" class="noBorder pad3 ui-corner-all fondSect3 w50 addAsset_extension" title="Asset file extension" value="" />
		</div>
		<br />
		<div class="inline mid w80 colorSoft margeTop1">Path</div>
		<div class="inline mid margeTop1" style="width:calc(100% - 100px);" title="Asset path (relative to assets root folder, eg. './characters/name/')">
			<input type="text" disabled class="noBorder pad3 ui-corner-all noBG colorHard w100p addAsset_path" value="" />
		</div>
		<br />
		<div class="inline mid w80 colorSoft margeTop1">Team</div>
		<div class="inline mid margeTop1 mini" style="width:calc(100% - 100px);" title="Asset team">
			<select class="addAsset_team" multiple="multiple">
				<?php foreach($teamAll as $idU=>$nameU): ?>
					<option value="<?php echo $idU; ?>"><?php echo $nameU; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<br />
		<div class="inline mid w80 colorSoft margeTop1">Dates start</div>
		<div class="inline mid margeTop1" style="width:calc(100% - 100px);" title="Asset dates (start, end)">
			<input type="text" class="noBorder pad3 ui-corner-all fondSect3 inputCal addAsset_date" style="width:80px;" value="<?php echo date(DATE_FORMAT); ?>" /> <span class="colorSoft">end</span>
			<input type="text" class="noBorder pad3 ui-corner-all fondSect3 inputCal addAsset_deadline" style="width:80px;" value="<?php echo $dateEnd; ?>" />
		</div>
		<br />
		<div class="inline top w80 colorSoft margeTop5">Category</div>
		<div class="inline top margeTop1 petit" style="width:calc(100% - 100px);" title="Asset category">
			<select class="addAsset_category w200">
				<option selected disabled>---</option>
				<?php foreach($assetsCategs as $idCat => $categ): ?>
					<option value="<?php echo $idCat; ?>"><?php echo $categ; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<br />
		<div class="inline mid w80"></div>
		<div class="inline mid margeTop5 nano rightText" style="width:calc(100% - 100px);">
			<button class="bouton" id="addAssetValid"><span class="ui-icon ui-icon-check"></span></button>
			<button class="ui-state-error bouton" id="addAssetCancel"><span class="ui-icon ui-icon-cancel"></span></button>
		</div>
	</div>
<?php endif; ?>

<div id="assetsList">
	<?php if (count($assetsArbo) == 0) :
		if (LANG == "fr"): ?>
			<div class="ui-state-error ui-corner-all pad3 margeTop10 gros">
				Le <b>masterFile XML</b> n'existe pas, est vide ou erroné !<br />
				Vous devez créer au moins <b>un dossier asset</b>.<br />
				<b>Cliquez sur le bouton <span class="inline bot ui-icon ui-icon-plusthick"></span> ci-dessus.</b>
			</div>
			<br /><?php
		elseif (LANG == "de"): ?>
			<div class="ui-state-error ui-corner-all pad3 margeTop10 gros">
				<b>XML MasterFile</b> fehlt, leere oder Bug!<br />
				Sie müssen mindestens <b>ein Assetordner</b> erstellen.<br />
				<b>Klicken Sie auf den Taste <span class="inline bot ui-icon ui-icon-plusthick"></span> Oben.</b>
			</div>
			<br /><?php
		else: ?>
			<div class="ui-state-error ui-corner-all pad3 margeTop10 gros">
				<b>XML masterFile</b> is missing, empty or buggy!<br />
				You must create at least <b>one asset folder</b>.<br />
				<b>Click the <span class="inline bot ui-icon ui-icon-plusthick"></span> button above.</b>
			</div>
			<br /><?php
		endif;
	else :
		foreach($assetsArbo['dir'] as $node) {
			echo '<div class="'.$default_classes.' inline assetFolder" level="0" path="'.$node['name'].'/" style="'.$default_style.'">'		// Affiche le premier niveau (0) des dossiers
					. $node['name'] ;
			if ($assetCreate) {
				echo '<div class="inline mid" style="display:none;">'
						. '<span class="inline doigt marge10l ui-icon ui-icon-plus addSubFolderBtn" title="Create subfolder into this folder"></span>'
						. '<span class="inline doigt marge10l ui-icon ui-icon-image addAssetBtn" title="Create an asset into this folder"></span>';
				if (!is_array(@$node['content']))
					echo '<div class="inline ui-state-error marge10l noBG noBorder"><span class="inline doigt ui-icon ui-icon-trash delSubFolderBtn" title="delete this folder"></span></div>';
				echo '</div>';
			}
			echo '</div>';
			echo recursive_folder_view($node);		// Affiche les sous-niveaux et les fichiers
		}
	endif;
	?>
	<p>&nbsp;</p>
</div>
