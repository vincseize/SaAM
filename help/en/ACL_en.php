
<style>
	.acl_N	 { font-size:1.2em; color:lightcoral; }
	.acl_A	 { font-size:1.2em; color:lightgreen; }
	.acl_O	 { font-size:1.2em; color:#0b93d5; }
</style>
<b>Users management</b>
<table class="tableListe center">
	<tr>
		<th class="bordBottom bordColInv2 leftText">Action</th>
		<th class="w100 bordBottom bordColInv2">Visitor</th>
		<th class="w150 bordBottom bordColInv2">demo</th>
		<th class="w150 bordBottom bordColInv2">artist</th>
		<th class="w150 bordBottom bordColInv2">lead</th>
		<th class="w200 bordBottom bordColInv2">supervisor</th>
		<th class="w200 bordBottom bordColInv2">Prod. Dir.</th>
		<th class="w200 bordBottom bordColInv2">magic</th>
		<th class="w100 bordBottom bordColInv2">dev</th>
		<th class="w100 bordBottom bordColInv2">root</th>
	</tr>
	<tr>
		<td class="leftText">Display users</td>
		<td class="acl_N">NO</td>
		<td class="acl_O">Demo only</td>
		<td class="acl_N">NO</td>
		<td class="acl_N">NO</td>
		<td class="acl_O">His own</td>
		<td class="acl_A">ALL</td>
		<td class="acl_O">His own</td>
		<td class="acl_A">ALL</td>
		<td class="acl_A">ALL</td>
	</tr>
	<tr>
		<td class="leftText">Assign projects to users</td>
		<td class="acl_N">NO</td>
		<td class="acl_N">NO</td>
		<td class="acl_N">NO</td>
		<td class="acl_N">NO</td>
		<td class="acl_O">His own<br />(created projects only)</td>
		<td class="acl_O">His own<br />(created projects only)</td>
		<td class="acl_O">His own<br />(every projects)</td>
		<td class="acl_A">ALL</td>
		<td class="acl_A">ALL</td>
	</tr>
	<tr>
		<td class="leftText">Modify / delete users</td>
		<td class="acl_N">NO</td>
		<td class="acl_N">NO</td>
		<td class="acl_N">NO</td>
		<td class="acl_N">NO</td>
		<td class="acl_O">His own</td>
		<td class="acl_O">His own</td>
		<td class="acl_O">His own</td>
		<td class="acl_A">ALL</td>
		<td class="acl_A">ALL</td>
	</tr>
	<tr>
		<td class="leftText">Add users</td>
		<td class="acl_N">NO</td>
		<td class="acl_N">NO</td>
		<td class="acl_N">NO</td>
		<td class="acl_N">NO</td>
		<td class="acl_A">YES</td>
		<td class="acl_A">YES</td>
		<td class="acl_A">YES</td>
		<td class="acl_A">YES</td>
		<td class="acl_A">YES</td>
	</tr>
</table>

<b>Projects management</b>
<table class="tableListe center">
	<tr>
		<th class="bordBottom bordColInv2 leftText">Action</th>
		<th class="w100 bordBottom bordColInv2">Visitor</th>
		<th class="w150 bordBottom bordColInv2">demo</th>
		<th class="w150 bordBottom bordColInv2">artist</th>
		<th class="w150 bordBottom bordColInv2">lead</th>
		<th class="w200 bordBottom bordColInv2">supervisor</th>
		<th class="w200 bordBottom bordColInv2">Prod. Dir.</th>
		<th class="w200 bordBottom bordColInv2">magic</th>
		<th class="w100 bordBottom bordColInv2">dev</th>
		<th class="w100 bordBottom bordColInv2">root</th>
	</tr>
	<tr>
		<td class="leftText">Display projects</td>
		<td class="acl_N">NO</td>
		<td class="acl_O">Assigned only</td>
		<td class="acl_N">NO</td>
		<td class="acl_N">NO</td>
		<td class="acl_O">Assigned only</td>
		<td class="acl_O">Assigned only</td>
		<td class="acl_A">ALL</td>
		<td class="acl_A">ALL</td>
		<td class="acl_A">ALL</td>
	</tr>
	<tr>
		<td class="leftText">Modify / delete projects</td>
		<td class="acl_N">NO</td>
		<td class="acl_N">NO</td>
		<td class="acl_N">NO</td>
		<td class="acl_N">NO</td>
		<td class="acl_O">Créés seulement</td>
		<td class="acl_O">Assigned only</td>
		<td class="acl_O">Assigned only</td>
		<td class="acl_A">ALL</td>
		<td class="acl_A">ALL</td>
	</tr>
	<tr>
		<td class="leftText">Add a project</td>
		<td class="acl_N">NO</td>
		<td class="acl_N">NO</td>
		<td class="acl_N">NO</td>
		<td class="acl_N">NO</td>
		<td class="acl_A">YES</td>
		<td class="acl_A">YES</td>
		<td class="acl_A">YES</td>
		<td class="acl_A">YES</td>
		<td class="acl_A">YES</td>
	</tr>
</table>