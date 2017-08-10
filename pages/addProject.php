<?php
	@session_start();
	require_once ("../inc/checkConnect.php" );

	$l = new Liste();
	$list_reals = $l->getListe(TABLE_USERS, Users::USERS_PSEUDO, 'id', 'ASC', Users::USERS_STATUS, '>=', Users::USERS_STATUS_ARTIST);
	$list_sups  = $l->getListe(TABLE_USERS, Users::USERS_PSEUDO, 'id', 'ASC', Users::USERS_STATUS, '>=', Users::USERS_STATUS_SUPERVISOR);

	$AC_reals = ''; $AC_sups = '';
	if (is_array($list_reals)) {
		foreach($list_reals as $real) { $AC_reals .= '"'.$real.'", '; }
		$AC_reals = substr($AC_reals, 0, -2);
	}
	if (is_array($list_sups)) {
		foreach($list_sups as $sup) { $AC_sups .= '"'.$sup.'", '; }
		$AC_sups = substr($AC_sups, 0, -2);
	}
?>

<script>

	var lastUploadedVignette = '';
	var autocompleteReals	 = [<?php echo $AC_reals; ?>];
	var autocompleteSups	 = [<?php echo $AC_sups; ?>];

	// retour d'info après ajout de projet
	function retourAjaxAddProj (data, etape) {
		if (data.error == 'OK') {
			$('#retourAjax').html('Project added!').removeClass('ui-state-error').addClass('ui-state-highlight').show(transition);
			$('#microVignetteHead').hide(transition);
			setTimeout(function(){
				localStorage.removeItem('addProj_EtapeWIP');
				localStorage.removeItem('addProj_BaseVals');
				localStorage.removeItem('addProj_TeamList');
				localStorage.removeItem('addProj_DeptsList');
				localStorage.removeItem('addProj_NbSeq');
				localStorage.removeItem('addProj_VignetteTempUrl');
				localStorage.removeItem('addProj_VignetteTempName');
				$('#retourAjax').hide(transition, function(){
					$('.lien[goto="admin_projects"]').click();
				});
			}, 1000);
		}
		else $('#retourAjax').html('Error : <b>'+data.message+'</b>').addClass('ui-state-error').show(transition);
	}

	$(function(){
		console.log(localStorage['addProj_BaseVals']);
		if (localStorage['addProj_BaseVals']) {
			$('.deptBtn[content="proj_add_team"]').removeClass('ui-state-disabled inactiveBtn');
			$('.submitBtns').removeClass("ui-state-disabled");
		}
		if (localStorage['addProj_BaseVals'] && localStorage['addProj_TeamList']){
			$('.deptBtn[content="proj_add_struct"]').removeClass('ui-state-disabled inactiveBtn');
			$('.submitBtns').removeClass("ui-state-disabled");
		}
		$('#retourAjax').click(function(){ $(this).hide(transition); });
	});

</script>

<div class="headerStage">
	<div class="floatR"><img id="microVignetteHead" src="" height="24" /></div>
	<div class="inline deptBtn colorSoft" type="projects" content="proj_add_base" active>Base</div>
	<div class="inline deptBtn colorSoft ui-state-disabled inactiveBtn" type="projects" content="proj_add_team"><?php echo L_TEAM; ?></div>
	<div class="inline deptBtn colorSoft ui-state-disabled inactiveBtn" type="projects" content="proj_add_struct">Structuration</div>
</div>

<div class="ui-corner-all doigt" id="retourAjax"></div>

<div class="pageContent">

</div>