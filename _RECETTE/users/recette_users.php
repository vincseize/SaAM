<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
	require_once ('dates.php' );
	require_once ('directories.php' );

	$new_position = Liste::getMAx(TABLE_USERS,'id')+1;
	$login_form = LOGIN_USER_DEV.$new_position;
    $adminLogin = $_SESSION["user"]->getUserInfos('login');

?>

<script>
	var actionFile = '../_RECETTE/users/recette_users_actions';
	var newPos = parseInt('<?php echo $new_position; ?>');
	$(function() {
		$('.bouton').button(); // (fait des jolis boutons) lol

		// clic sur le bouton add user
		$('#add').click(function(){
			var loginnewUser = $('#loginnewUser').html();
			if (loginnewUser == '') { alert('login manquant !'); return false; }
			var ajaxStr = 'action=add&loginUser='+loginnewUser;
			AjaxJson(ajaxStr, actionFile, retourStep4);
			newPos++;
		});

		// clic sur un bouton delete dans la liste
		$('.pageContent').off('click', '.deleteUser');
		$('.pageContent').on('click', '.deleteUser', function(){
			var idToDel = $(this).attr('idUser');
			var loginToDel = $(this).attr('title');
			var ajaxStr = 'action=del&idUser='+idToDel+'&loginUser='+loginToDel;
			AjaxJson(ajaxStr, actionFile, retourDelete);
		});

	});
	// FIN du $ready


// callbak 4eme et DERNIER step (plus de requete après)
function retourStep4 (data) {
	if (data.error == 'OK') {
		$('#callback').append('<p>STEP 4 : create Datas folder ... <span class="ok">OK</span></p>');
		$('#TBlist').load('_RECETTE/users/list_users.php');
                 var ajaxStr = 'action=folder&loginUser='+data.loginnewUser;
                 AjaxJson(ajaxStr, actionFile, retourFinal);
	}
	else
		$('#callback').append('<p>STEP 4 : create Project folders -> séquences -> shots ... <span class="colorErreur gras">error : '+data.message+'</span></p>');
}


// callbak final et DERNIER step (plus de requete après)
function retourFinal (data) {
	if (data.error == 'OK') {



		$('#callback').append('<p>Create user ... <span class="ok">OK</span></p>');



		$('#TBlist').load('_RECETTE/users/list_users.php');
		$('#loginnewUser').html('<?php echo LOGIN_USER_DEV; ?>'+newPos);
	}
	else
		$('#callback').append('<p>Create user ... <span class="colorErreur gras">error : '+data.message+'</span></p>');
}

//// callbak premier step
//function retourStep1 (data) {
//	if (data.error == 'OK') {
//		$('#callback').html('<p>STEP 1 : add project ... <span class="ok">OK</span></p>');
//		//var loginnewUser = $('#loginnewUser').html();
//		//var ajaxStr = 'action=add&titleProj='+titlenewProj;
//		AjaxJson(ajaxStr, actionFile, retourStep2);
//	}
//	else
//		$('#callback').html('<p>STEP 1 : add project ... <span class="colorErreur gras">error : '+data.message+'</span></p>');
//}
//
//
//// callbak 2eme step
//function retourStep2 (data) {
//	if (data.error == 'error') {
//		$('#callback').append('<p>STEP 2 : add same project to check doublon ... <span class="ok">OK</span> <span class="mini"><i>(normal message : '+data.message+')</i></span></p>');
//		var titlenewProj = $('#titleNewProj').html();
//		var ajaxStr = 'action=mod&titleProj='+titlenewProj;
//		AjaxJson(ajaxStr, actionFile, retourStep3);
//	}
//	else
//		$('#callback').append('<p>STEP 2 : add same project to check doublon ... <span class="colorErreur gras">error : doublon added !! check BDD.</span></p>');
//}
//
//
//// callbak 3eme step
//function retourStep3 (data) {
//	if (data.error == 'OK') {
//		$('#callback').append('<p>STEP 3 : modify project ... <span class="ok">OK</span> Next and last step-> folders creation [Coffee Time ...] </p>');
//                var foldersassets = ['sets','characters','props'];
//                var foldersAssetsOK = encodeURIComponent(JSON.encode(foldersassets));
//                var ajaxStr = 'action=folder&titleProj='+data.titleBack+'&foldersassets='+foldersAssetsOK;
//		AjaxJson(ajaxStr, actionFile, retourStep4);
//	}
//	else
//		$('#callback').append('<p>STEP 3 : modify project ... <span class="colorErreur gras">error : '+data.message+'</span></p>');
//}
//
//




// callback delete
function retourDelete (data){
	if (data.error == 'OK')
		$('#callback').html('<p>DELETE USER : <span class="ok">OK</span></p>');
	else
		$('#callback').html('<p>DELETE USER : <span class="colorErreur gras">error : '+data.message+'</span></p>');
	$('#TBlist').load('_RECETTE/users/list_users.php');
}

</script>


<div class="stageContent pad5">
	<h4>LISTE DES UTILISATEURS</h4>

	<div id='TBlist'>
		<?php include ('list_users.php'); ?>
	</div>


	<h4>RECETTE : ADD-MOD USER</h4>

	<div class="inline top marge30r pad5" id="form_add">
		<span class="colorMid">Login : </span>
		<span class="gros gras fondSect1 pad3 padV10 ui-corner-all" id="loginnewUser"><?php echo $login_form; ?></span>
		<span class="petit"><button class="bouton" id="add">GO</button></span>
	</div>



	<div class="inline top marge30l pad5" id="callback">

	</div>
</div>