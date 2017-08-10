<?php
	@session_start(); // 2 lignes à placer toujours en haut du code des pages
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );

	if (!$_SESSION['user']->isDev()) die();		// secu status

	$l = new Liste();
	$l->getListe(TABLE_ACL, '*', 'grp_name', 'ASC');
	$listeACLgrps = $l->simplifyList('id');
?>

<style>
	.acl_N	 { font-size:1.2em; color:red; }
	.acl_A	 { font-size:1.2em; color:green; }
	.acl_O	 { font-size:1.2em; color:#0b93d5; }
	.legende { font-size:0.9em; margin:20px 10px 100px 210px; }
</style>

<script>
	$(function(){
		$('.bouton').button();

		// Ajout d'une ligne : prép
		$('.pageContent').off('click', '#addACLgrp');
		$('.pageContent').on('click', '#addACLgrp', function(){
			$('#listeACL').children('table').prepend(
				'<tr id="addACLline">'
				+  '<td class="leftText"><input type="text" class="noBorder pad3 ui-corner-all fondSect3 addGrpVal" id="grp_name" /></td>'
				+  '<td class="center"><input type="text" size=4 class="noBorder pad3 ui-corner-all fondSect3 addGrpVal" id="1" /></td>'
				+  '<td class="center"><input type="text" size=4 class="noBorder pad3 ui-corner-all fondSect3 addGrpVal" id="2" /></td>'
				+  '<td class="center"><input type="text" size=4 class="noBorder pad3 ui-corner-all fondSect3 addGrpVal" id="3" /></td>'
				+  '<td class="center"><input type="text" size=4 class="noBorder pad3 ui-corner-all fondSect3 addGrpVal" id="4" /></td>'
				+  '<td class="center"><input type="text" size=4 class="noBorder pad3 ui-corner-all fondSect3 addGrpVal" id="5" /></td>'
				+  '<td class="center"><input type="text" size=4 class="noBorder pad3 ui-corner-all fondSect3 addGrpVal" id="6" /></td>'
				+  '<td class="center"><input type="text" size=4 class="noBorder pad3 ui-corner-all fondSect3 addGrpVal" id="7" /></td>'
				+  '<td class="center"><input type="text" size=4 class="noBorder pad3 ui-corner-all fondSect3 addGrpVal" id="8" /></td>'
				+  '<td class="center"><input type="text" size=4 class="noBorder pad3 ui-corner-all fondSect3 addGrpVal" id="9" /></td>'
				+  '<td class="center nano">'
				+		'<button class="btnAdd" title="valider" id="validAddAclGrp"><span class="ui-icon ui-icon-check"></span></button> '
				+		'<button class="btnAdd" title="annuler" id="annulAddAclGrp"><span class="ui-icon ui-icon-cancel"></span></button>'
				+  '</td>'
			);
			$(this).hide();
			$('.addGrpVal#grp_name').focus();
			$('.btnAdd').button();
		});

		// Blocage de caractères
		$('.pageContent').off('keypress', '.addGrpVal, .modGrpVal');
		$('.pageContent').on('keypress', '.addGrpVal, .modGrpVal', function(event) {
			if ($(this).attr('id') == 'grp_name')
				return checkChar(event,true,true,true);
			else {
				var keyCode = event.which ? event.which : event.keyCode;
				var allowed = 'nao';
				if (allowed.indexOf(String.fromCharCode(keyCode)) == -1 && keyCode != 9) {
					return false;
				}
			}
		});

		// Mise en MAJUSCULE de valeur
		$('.pageContent').off('blur', '.addGrpVal, .modGrpVal');
		$('.pageContent').on('blur', '.addGrpVal, .modGrpVal', function(event) {
			var value = $(this).val();
			var valMAJ = value.toUpperCase();
			$(this).val(valMAJ);
		});

		// Validation ajout d'une ligne
		$('.pageContent').off('click', '#validAddAclGrp');
		$('.pageContent').on('click', '#validAddAclGrp', function() {
			var ajaxReq = 'action=ACL_addGrp';
			$('.addGrpVal').each(function(ix,el){
				var champ = $(this).attr('id');
				var val   = $(this).val();
				if (val=='' || val==undefined) { alert('Manque un champ !'); return false; }
				ajaxReq += '&'+champ+'='+val;
			})
			$('.addGrpVal#grp_name').parents('tr').remove();
			AjaxJson(ajaxReq, 'admin/admin_config_actions', retourAjaxMsg, true);
		});

		// Annulation ajout d'une ligne
		$('.pageContent').off('click', '#annulAddAclGrp');
		$('.pageContent').on('click', '#annulAddAclGrp', function(){
			$('.addGrpVal#grp_name').parents('tr').remove();
			$(this).hide();
			$('#validAddAclGrp').hide();
			$('#addACLgrp').show();
		});

		// Modification de ligne
		$('.pageContent').off('click', '.modifACL');
		$('.pageContent').on('click', '.modifACL', function(){
			var idLine = $(this).attr('idACL');
			var line = $(this).parents('tr');
			line.find('td[class*="acl_"]').each(function(i,e){
				var oldVal = $(e).html();
				$(e).html('<input type="text" value="'+oldVal+'" size="2" groupID="'+(i+1)+'" class="noBorder pad3 ui-corner-all fondSect3 modGrpVal" />');
			});
			line.find('.pico').html(
					 '<button class="btnMod validModAclGrp" idGrp="'+idLine+'"><span class="ui-icon ui-icon-check"></span></button> &nbsp;&nbsp;'
					+'<button class="btnMod annulModAclGrp"><span class="ui-icon ui-icon-cancel"></span></button>'
			);
			$('.btnMod').button();
			line.find('.modGrpVal').first().focus();
		});

		// Validation modification d'une ligne
		$('.pageContent').off('click', '.validModAclGrp');
		$('.pageContent').on('click', '.validModAclGrp', function() {
			var group = $(this).attr('idGrp');
			var ajaxReq = 'action=ACL_modGrp&group='+group;
			$('.modGrpVal').each(function(ix,el){
				var champ = $(this).attr('groupID');
				var val   = $(this).val();
				if (val=='' || val==undefined) { alert('Manque un champ !'); return false; }
				ajaxReq += '&'+champ+'='+val;
			});
			AjaxJson(ajaxReq, 'admin/admin_config_actions', retourAjaxMsg, true);
		});

		// Annulation modification d'une ligne
		$('.pageContent').off('click', '.annulModAclGrp');
		$('.pageContent').on('click', '.annulModAclGrp', function(){
			$('.deptBtn[active]').click();
		});

	});
</script>

<div class="floatR marge30r margeTop10 micro">
	<button class="bouton" title="ajouter un groupe" id="addACLgrp"><span class="ui-icon ui-icon-plusthick"></span></button>
</div>

<h2 class="marge10l">CONFIG ACL</h2>


<div id="listeACL">

	<table class="tableListe ui-corner-all petit">
		<tr class="ui-widget-content">
			<td class="w100 leftText">Group Name</td>
			<td class="w80 center">visitor</td>
			<td class="w80 center">demo</td>
			<td class="w80 center">artist</td>
			<td class="w80 center">lead</td>
			<td class="w80 center">supervisor</td>
			<td class="w80 center">prod. dir.</td>
			<td class="w80 center">magic</td>
			<td class="w80 center">dev</td>
			<td class="w80 center">root</td>
			<td class="w50 leftText"></td>
		</tr>

		<?php
		foreach($listeACLgrps as $idACL => $acl) : ?>
			<tr>
				<td><?php echo $acl['grp_name']; ?></td>
				<td class="acl_<?php echo $acl[1]; ?> center"><?php echo $acl[1]; ?></td>
				<td class="acl_<?php echo $acl[2]; ?> center"><?php echo $acl[2]; ?></td>
				<td class="acl_<?php echo $acl[3]; ?> center"><?php echo $acl[3]; ?></td>
				<td class="acl_<?php echo $acl[4]; ?> center"><?php echo $acl[4]; ?></td>
				<td class="acl_<?php echo $acl[5]; ?> center"><?php echo $acl[5]; ?></td>
				<td class="acl_<?php echo $acl[6]; ?> center"><?php echo $acl[6]; ?></td>
				<td class="acl_<?php echo $acl[7]; ?> center"><?php echo $acl[7]; ?></td>
				<td class="acl_<?php echo $acl[8]; ?> center"><?php echo $acl[8]; ?></td>
				<td class="acl_<?php echo $acl[9]; ?> center"><?php echo $acl[9]; ?></td>
				<td class="center pico">
					<button class="bouton modifACL" idACL="<?php echo $idACL; ?>"><span class="ui-icon ui-icon-pencil"></span></button>
				</td>
			</tr>
		<?php endforeach; ?>

	</table>

</div>

<div class="legende">
	<span class="acl_A">A = access allowed</span><br />
	<span class="acl_O">O = access to your own</span><br />
	<span class="acl_N">N = access denied</span><br />
</div>