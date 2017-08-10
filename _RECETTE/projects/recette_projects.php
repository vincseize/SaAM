<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	require_once ('dates.php' );
	require_once ('directories.php' );

	$new_position = Liste::getMAx(TABLE_PROJECTS,'position')+1;
	$title_form = TITLE_PROJECT_DEV.$new_position;

?>

<script>
	var actionFile = '../_RECETTE/projects/recette_projects_actions';
	var newPos = parseInt('<?php echo $new_position; ?>');
	$(function() {
		$('.bouton').button(); // (fait des jolis boutons) lol

		// clic sur le bouton add project
		$('#add').click(function(){
			var titlenewProj = $('#titleNewProj').html();
			if (titlenewProj == '') { alert('titre manquant !'); return false; }
			var ajaxStr = 'action=add&titleProj='+titlenewProj;
			AjaxJson(ajaxStr, actionFile, retourStep1);
			newPos++;
		});

		// clic sur un bouton delete dans la liste
		$('.pageContent').off('click', '.deleteProj');
		$('.pageContent').on('click', '.deleteProj', function(){
			var idToDel = $(this).attr('idProj');
			var titleToDel = $(this).attr('title');
			var ajaxStr = 'action=del&idProj='+idToDel+'&titleProj='+titleToDel;
			AjaxJson(ajaxStr, actionFile, retourDelete);
		});

		// clic sur un bouton Masterfile dans la liste
		$('.pageContent').off('click', '.genMasterXml');
		$('.pageContent').on('click', '.genMasterXml', function(){
			var idProj = $(this).attr('idProj');
			var titleProj = $(this).attr('titleProj');
			var ajaxStr = 'action=masterfile&idProj='+idProj+'&titleProj='+titleProj;
			AjaxJson(ajaxStr, actionFile, retourMasterfile);
		});

		// clic sur un bouton pdf dans la liste
		$('.pageContent').off('click', '.genPdfProject');
		$('.pageContent').on('click', '.genPdfProject', function(){
			var idProj = $(this).attr('idProj');
			var titleProj = $(this).attr('titleProj');
			var ajaxStr = 'action=pdf&idProj='+idProj+'&titleProj='+titleProj;
			AjaxJson(ajaxStr, actionFile, retourPdf);
		});

	});
	// FIN du $ready


// callbak premier step
function retourStep1 (data) {
	if (data.error == 'OK') {
		$('#callback').html('<p>STEP 1 : add project ... <span class="ok">OK</span></p>');
		var titlenewProj = $('#titleNewProj').html();
		var ajaxStr = 'action=add&titleProj='+titlenewProj;
		AjaxJson(ajaxStr, actionFile, retourStep2);
	}
	else
		$('#callback').html('<p>STEP 1 : add project ... <span class="colorErreur gras">error : '+data.message+'</span></p>');
}


// callbak 2eme step
function retourStep2 (data) {
	if (data.error == 'error') {
		$('#callback').append('<p>STEP 2 : add same project to check doublon ... <span class="ok">OK</span> <span class="mini"><i>(normal message : '+data.message+')</i></span></p>');
		var titlenewProj = $('#titleNewProj').html();
		var ajaxStr = 'action=mod&titleProj='+titlenewProj;
		AjaxJson(ajaxStr, actionFile, retourStep3);
	}
	else
		$('#callback').append('<p>STEP 2 : add same project to check doublon ... <span class="colorErreur gras">error : doublon added !! check BDD.</span></p>');
}


// callbak 3eme step
function retourStep3 (data) {
	if (data.error == 'OK') {
		$('#callback').append('<p>STEP 3 : modify project ... <span class="ok">OK</span> Next and last step-> folders creation [Coffee Time ...] </p>');
                var foldersassets = ['sets','characters','props'];
                var foldersAssetsOK = encodeURIComponent(JSON.encode(foldersassets));
                var ajaxStr = 'action=folder&titleProj='+data.titleBack+'&foldersassets='+foldersAssetsOK;
		AjaxJson(ajaxStr, actionFile, retourStep4);
	}
	else
		$('#callback').append('<p>STEP 3 : modify project ... <span class="colorErreur gras">error : '+data.message+'</span></p>');
}


// callbak 4eme et DERNIER step (plus de requete après)
function retourStep4 (data) {
	if (data.error == 'OK') {
		$('#callback').append('<p>STEP 4 : create Datas folder ... <span class="ok">OK<br /><br />Project created ( default status = hide )</span></p>');
		$('#TBlist').load('_RECETTE/projects/list_projects.php');
		$('#titleNewProj').html('<?php echo TITLE_PROJECT_DEV; ?>'+newPos);
	}
	else
		$('#callback').append('<p>STEP 4 : create Project folders -> séquences -> shots ... <span class="colorErreur gras">error : '+data.message+'</span></p>');
}


// callback delete
function retourDelete (data){
	if (data.error == 'OK')
		$('#callback').html('<p>DELETE PROJECT <i>"'+data.titleProj+'"</i> (#'+data.idProj+') : <span class="ok">OK</span></p><a href="index.php"><b class="red">REFRESH</b></a>');
	else
		$('#callback').html('<p>DELETE PROJECT <i>"'+data.titleProj+'"</i> (#'+data.idProj+') : <span class="colorErreur gras">error : '+data.message+'</span></p>');
	$('#TBlist').load('_RECETTE/projects/list_projects.php');
}

// callback MasterFile
function retourMasterfile (data){
	if (data.error == 'OK'){
		var masterfile_path = 'datas/projects/'+data.idProj+'_'+data.titleProj+'/'+data.idProj+'_'+data.titleProj+'_master.xml';
		$('#callback').html('<p>Masterfile <i>'+data.idProj+'_'+data.titleProj+'_master.xml [#'+data.idProj+']</i> : <span class="ok">OK</span></p><br /><a href="'+masterfile_path+'" target="blank"  title="open xml"><img src="gfx/icones/xml.png"></a>');
	}
	else {
		$('#callback').html('<p>Masterfile PROJECT <i>"'+data.titleProj+'"</i> (#'+data.idProj+') : <span class="colorErreur gras">error : '+data.message+'</span></p>');
	//$('#TBlist').load('_RECETTE/projects/list_projects.php');
	}
}

// callback Pdf
function retourPdf (data){
	if (data.error == 'OK'){
		var pdf_path = 'datas/projects/'+data.idProj+'_'+data.titleProj+'/'+data.idProj+'_'+data.titleProj+'.pdf';
		$('#callback').html('<p>Pdf <i>'+data.idProj+'_'+data.titleProj+' [#'+data.idProj+']</i> : <span class="ok">OK</span></p><br /><a href="'+pdf_path+'" target="blank" title="open pdf"><img src="gfx/icones/pdf.png"></a>');
	}
	else {
		$('#callback').html('<p>PDF PROJECT <i>"'+data.titleProj+'"</i> (#'+data.idProj+') : <span class="colorErreur gras">error : '+data.message+'</span></p>');
	//$('#TBlist').load('_RECETTE/projects/list_projects.php');
	}
}
</script>

<div class="stageContent pad5">

	<h4>LISTE DES PROJETS</h4>

	<div id='TBlist'>
		<?php include ('list_projects.php'); ?>
	</div>


	<h4>RECETTE : ADD-MOD PROJECT</h4>

	<div class="inline top marge30r pad5" id="form_add">
		<span class="colorMid">Title : </span>
		<span class="gros gras fondSect1 pad3 padV10 ui-corner-all" id="titleNewProj"><?php echo $title_form; ?></span>
		<span class="petit"><button class="bouton" id="add">GO</button></span>
	</div>

	<br /><br /><br />

	<div class="inline top marge30l pad5" id="callback">

	</div>

</div>