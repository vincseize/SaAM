
<a id="shots"></a>
<h2 class="ui-state-default ui-corner-top pad3">Shots<div class="floatR marge30r doigt btnTop"><span class="ui-icon ui-icon-arrowreturn-1-n"></span></div></h2>
	<ol>
		<li>
			<h4>Definition of a shot</h4>
			<div class="margeTop10">
				A "shot" is a sequence part, defined in time by a start and end, consisting of a continuous series of images recorded by a camera during a shoot.<br />
				Each shot has a cadence (in FPS, frame per second) which can be different depending on department.
			</div>
		</li>
		<li>
			<h4>Shot departments</h4>
			<div class="margeTop10">
				<img src="<?php echo $dirHelp ?>/help_shot_menu.jpg" class="marge5bot shadowOut" />
				<p>Shots departments are divided in two types:</p>
				<ul>
					<li id="help_shots_departments">
						<b>Variables departments</b>: These departments are only present when they have been choosen in the project's configuration.
						It's possible to rename, and add some departments in tools panel, button "SaAM admin" (for users with access granted on this tool).
					</li>
					<li>
						<b>Fixed departments</b>: They are present in every projects, you can't hide or rename it. These departments are:
						<ul>
							<li id="help_dept_shots_structure">
								<b>SHOTS STRUCTURE</b>: It's the start department, the one that allow you to add sequences, shots, manage shot teams,
								and to have a global overview of shots progress within the project.
							</li>
							<li id="help_dept_scenario">
								<b>SCENARIO</b>: In this departemnt you can write the scenario of the project, with a page layout, and export it to PDF.
								However, beware that every users of the project have access to this departemnt.
							</li>
							<li id="help_dept_tech_script">
								<b>TECH. SCRIPT</b> (stands for "technical script"): This department will allow you to define informations about the content
								of each shots (framing, sound, action, etc). You can print some contact-sheets by sequences directly from there.
							</li>
							<li id="help_dept_storyboard">
								<b>STORYBOARD</b>: Nothing fancy here, in this department you can put some storyboard images, shot by shot, and print some
								contact-sheets for entire sequences to facilitate the production.
							</li>
							<li id="help_dept_final">
								<b>FINAL</b>: This department is a bit special, because it's present in every sections (shots, assets, scenes...), and located
								at the very right of the departments toolbar.
								In this department you can have a complete preview of the film (or a particular sequence), at current production state, day by day,
								and follow project's evolution in a visual way.
							</li>
						</ul>
					</li>
				</ul>
				<div class="margeTop10">
					In every departments, the shot window is composed by 4 parts:
					<ul>
						<li id="help_shot_header">
							<b>Header</b> : In this upper part, are the global informations of the shot.<br />
							<span id="help_shot_department_name">In background, with big font, the name of the department currently opened.</span>
							From left to right, you'll find :
							<p id="help_shot_vignette">
								<img src="<?php echo $dirHelp ?>/help_shot_vignette.jpg" class="floatL marge5 shadowOut" /><br />
								The shot's <b>vignette</b> (image), with on top of it the name and its sequence name.<br />
								<br />
								This image is a thumbnail of the last published. If there is no published for the department, an empty image is displayed.<br />
								The number displayed at the bottom left of image is the number of assets which are used in the shot.
							</p>
							<div class="fixFloat"></div>
							<p id="help_shot_informations">
								<img src="<?php echo $dirHelp ?>/help_shot_infos.jpg" class="floatL marge5 shadowOut" /><br />
								The general informations of the shot. Its progress, the shot's team (supervisor, lead, artists), the start and end dates,
								its image ratio, its framerate (fps), its duration (in frames), a reminder of the remaining days before end date, and the shot description.
							</p>
							<div class="fixFloat"></div>
							<p>
								<img src="<?php echo $dirHelp ?>/help_shot_depts_infos.jpg" class="marge5 shadowOut" /><br />
								Some action buttons related to the department. Those buttons can be:
							</p>
							<p id="help_shot_action_buttons">
								- <small class="micro"><button class="bouton"><i class="ui-icon ui-icon-bookmark"></i></button></small> :
								To assign some tasks to users about this shot,<br />
								- <small class="micro"><button class="bouton"><i class="ui-icon ui-icon-arrowrefresh-1-s"></i></button></small> :
								To refresh the shot window in the current department,<br />
								- <small class="micro"><button class="bouton"><i class="ui-icon ui-icon-pencil"></i></button></small> :
								To modify some informations specific to this department for the shot.
							</p>
						</li>
						<li>
							<p>The left part displays the shot's published for the department.</p>
							<p>See below for more informations about published.</p>
						</li>
						<li id="help_shot_center">
							<p>In the center part are the <b>steps</b> of the department, and <b>tags</b> of the shot.</p>
							<div id="help_shot_steps">
								<img src="<?php echo $dirHelp ?>/help_shot_steps.jpg" class="floatL marge10 shadowOut" />
								<p>
									<b>Steps</b> are defined for each department by SaAM administrator (see ><a class="helpBtn noBorder" content="departments">manage departements</a><).
									Only step <b>"Approved"</b> is mandatory.
								</p>
								<p>
									Steps are used to <b>calculate the progression</b> of the shot, the sequence and so the project. For example, if every
									departments of a shot have the step "Approved", the progress of this shot will be 100%. If no step is defined, it will be 0%.
									Each intermediate step define the percentage of the shot progress.
								</p>
							</div>
							<div class="fixFloat"></div>
							<p>
								For help about tags, see ><a class="helpBtn noBorder" content="tags">assign tags</a>< chapter (or 'H' over the 'Tags' button).
							</p>
						</li>
						<li>
							<p>The right part displays messages of the current published of the shot for the department.</p>
							<p>See below for more informations about messages.</p>
						</li>
					</ul>
				</div>
				<div class="margeTop10">
					<p>
						Into the department <b>"SHOTS STRUCTURE"</b>, you can find an overview of each departments for a shot.<br />
						First, open a shot by clicking on an image or label in the shots lists, or using the shortcuts menu, at the top left of SaAM.
					</p>
					<div id='help_shot_all_depts_infos'>
						<p>
							<img src="<?php echo $dirHelp ?>/help_shot_depts_assign.jpg" class="marge5 shadowOut" /><br />
							On the right is a selector that allows you to quickly <b>assign some departments</b> to the shot.<br />
							At the very right, you can read the <b>date of last modification</b>, and the user's peudo who did this last change.
						</p>
						<p>
							<img src="<?php echo $dirHelp ?>/help_shot_all_depts_infos.jpg" class="floatL marge10 shadowOut" />
							Below, a list of departments which already contain some informations about this shot: the <b>number of published</b>, the <b>framerate</b>,
							and the <b>current step</b>. Note that the published number is <b>green</b> when the last published is validated.<br />
							You can directly open a department from here by clicking the little button
							<small class="nano"><button class="bouton"><i class="ui-icon ui-icon-arrowthickstop-1-e"></i></button></small>.<br />
							<br />
						</p>
					</div>
					<div class="fixFloat"></div>
				</div>
			</div>
		</li>
		<li>
			<h4>Add a shot</h4>
			<div class="margeTop10" id="help_add_shot">
				<img src="<?php echo $dirHelp ?>/help_shot_add.jpg" class="floatL marge10 shadowOut" />
				<p>To add one (or several shot(s), you must go into the department "SHOTS STRUCTURE" (so you must have access granted to it).</p>
				<p>
					On each <b>sequence line</b>, you'll find a button <small class="micro"><button class="bouton"><i class="ui-icon ui-icon-plusthick"></i></button></small>.
					Click on the one of the wanted sequence, and a window opens (image below).
				</p>
				<p>
					First, specify the <b>number of shots</b> you want to add. For each shot that appear in the list below, give a <b>title</b>, a
					<b>start date</b>, and an <b>end date</b>. These dates will be used by the "schedule" department ("root" section),
					but will also inform you about the "deadline" of the shot (remaining time before delivery date).
				</p>
				<p>
					Once done, you can click on the button <small class="petit"><button class="bouton">Validate</button></small>
					at the bottom of the window. The shots are added to the opened sequence in main window.
				</p>
				<div class="fixFloat"></div>
			</div>
		</li>
		<li>
			<h4>Modify a shot</h4>
			<div class="margeTop10">
				<p>
					You can change shot's informations within "SHOTS STRUCTURE" department (you must have access granted to it).
					<br />
					There is two ways to do that:
				</p>
				<ul>
					<li>Within sequences list window</li>
					<li>Within shot window, after having selected a shot in the list window</li>
				</ul>
				<h5>Within sequences list window</h5>
				<p id="help_modify_shots_from_sequence_window">
					Every shot line contain informations about the shot. To open the shot in its own window and display more informations,
					just click on its image or label. The image displayed here is the one present in the latest department (the most on the right).
					Then, you can see its <b>title</b>, its <b>label</b>, and the number of <b>published</b> it contains.<br />
					The department selector allows you to quickly <b>assign the shot to some departments</b>.<br />
					Then, the <b>start date</b>, <b>end date</b>, and the list of <b>users assigned</b> to the shot.<br />
					<br />
					Finally, at the very right of the shot, are 4 buttons:<br />
					<br />
					<small class="micro"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-lightbulb"></i></button></small>
					To <b>hide</b> the shot. It will be visible only in the "shots structure" department.<br />
					<small class="micro"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-unlocked"></i></button></small>
					To <b>lock</b> the shot. It will still be visible everywhere, but won't be alterable.<br />
					<small class="micro"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-pencil"></i></button></small>
					To <b>modify informations</b> of the shot. Clicking on this button will transform the shot line with some text input
					and selectors in order to change each information. Save and Cancel buttons are on the right.<br />
					<small class="micro"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-trash"></i></button></small>
					To <b>archive</b> the shot. Once archived, it will be considered as deleted, so invisible and not modifiable.
					However you can restore it at any time with the button
					<small class="micro"><button class="bouton ui-state-error"><i class="ui-icon ui-icon-refresh"></i></button></small> that will appear
					instead of those 4 buttons.<br />
					<br />
					Vous pouvez aussi <b>réorganiser</b> l'ordre des plans, en les déplaçant (cliquer-déplacer) les uns au dessus des autres.<br />
					<br />
				</p>
				<h5>Within shot window</h5>
				<p>
					Open the desired shot by clicking on its image or label, in the shots list. On the right of the opened window, there is a form
					that allows you to change shot's informations.
				</p>
				<p id="help_modify_shot">
					<img src="<?php echo $dirHelp ?>/help_shot_modify.jpg" class="floatL marge10 shadowOut" /><br />
					Here you can modify the shot <b>title</b>, start and end <b>dates</b>, choose a user which will be <b>supervisor</b> of the shot
					(a popup will give you the available users list), and a user which will be <b>lead</b>.<br />
					You can also specify the shot's <b>duration</b> in frames. This value will be used in all other departments. It's the duration given
					for the final edit.
					Fanally, a <b>description</b> may be specified, to be displayed in every headers of shots departments.<br />
					<br />
					Once done, don't forget to click on the button <small class="petit"><button class="bouton ui-state-default">Validate</button></small></small>
					at the bottom.<br />
					You can also cancel modifications and restore old values.<br />
					<br />
				</p>
				<div class="fixFloat"></div>
				<p id="help_modify_shot_team">
					<img src="<?php echo $dirHelp ?>/help_shot_modify_shot_team.jpg" class="floatL marge10 shadowOut" />
					To modify the list of users assigned to the shot, use the button "<b class="big gras">+</b>" located in the header of the shot window.<br />
					<br />
					It displays a selector, in which there is the list of users assigned to the project. Make your choice, and click on the little button
					<small class="nano"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-check"></i></button></small><br />
					To cancel, click on the button "<b class="big gras">-</b>" on the left.<br />
				</p>
				<div class="fixFloat"></div>
				<p id="help_modify_shot_buttons">
					<img src="<?php echo $dirHelp ?>/help_shot_modify_shot_btns.jpg" class="floatL marge5 shadowOut" /><br />
					At the top right of the shot window's header, are 4 buttons:<br />
					<br />
					<small class="micro"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-arrowrefresh-1-s"></i></button></small>
					To <b>refresh</b> the shot window.<br />
					<small class="micro"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-lightbulb"></i></button></small>
					To <b>hide</b> the shot. It will be visible only in the "shots structure" department.<br />
					<small class="micro"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-unlocked"></i></button></small>
					To <b>lock</b> the shot. It will still be visible everywhere, but won't be alterable.<br />
					<small class="micro"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-trash"></i></button></small>
					To <b>archive</b> the shot. Once archived, it will be considered as deleted, so invisible and not modifiable.
					However you can restore it at any time with the button
					<small class="micro"><button class="bouton ui-state-error"><i class="ui-icon ui-icon-refresh"></i></button></small> that will appear
					instead of the "delete" button.<br />
					<br />
				</p>
				<div class="fixFloat"></div>
				<p id="help_shot_back_to_sequences">
					To go back to the <b>sequences list</b>, use the button <small class="micro"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-arrowthickstop-1-n"></i></button></small>
					located at the top left corner of the shot window's header (on the image).<br />
					<br />
				</p>
			</div>
		</li>
		<li>
			<h4>Shots published</h4>
			<div class="margeTop10" id="help_shot_published">
				<p>
					A "published" corresponds to a key step in the  dans la realization of a shot. It's an image or a video, which show the achievement
					progress, and around which concerned people (shot team, lead, supervisor) can start a discussion.
				</p>
				<img src="<?php echo $dirHelp ?>/help_shot_published.jpg" class="floatL marge5 shadowOut"/>
				<p>
					At the top of the list of published, is the <b>last published</b>. Messages (right part of the window) are linked to this published.
				</p>
				<p>
					If the icon <img src="gfx/icones/icone_valid.png" /> is present, it means that the published was <b>VALIDATED</b> by a supervisor, and that
					the discussion around this published is closed (no new message can be posted).
				</p>
				<p>
					You can select an older published in the list, by clicking on its number. It displays the old published below the last one, and its
					linked messages are displayed to the right (but not alterables).
				</p>
				<p>
					The buttons that are located at the bottom right of the last published are the following <i>(this can be different depending on the case,
					for example according to your user status, or the published state)</i> :<br />
					- <small class="pico"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-arrowthickstop-1-s"></i></button></small>
					To download the published's file into your local machine.<br />
					- <small class="pico"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-pencil"></i></button></small>
					To draw directly onto the published image (only visible if plugin "DrawTool" is activated, and if the published is not validated).<br />
					- <small class="pico"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-wrench"></i></button></small>
					To replace the file of the published. This will move the previous file into the "work in progress" folder
					(see "shot folders").<br />
					- <small class="pico"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-disk"></i></button></small>
					To validate the published. This will close the discussion about the published (no more message can be posted), and to
					allow the creation of a new published.<br />
				</p>
				<div class="fixFloat"></div>
				<p id="help_shot_add_published">
					<img src="<?php echo $dirHelp ?>/help_shot_published_add.jpg" class="floatL marge5 shadowOut"/>
					To <b>add</b> a published, the previous last published must be validated (see above), or there must be no published present. Only then, a button
					<small class="nano"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-plusthick"></i></button></small>
					is displayed in the black bar above. Click on it to display the file drop zone. Then, you can drag & drop an <b>image or video</b> file
					into the zone, and when upload is done, don't forget to click on button
					<small class="pico"><button class="bouton ui-state-error"><i class="ui-icon ui-icon-check"></i></button></small> to validate the adding.<br />
					If you want to cancel, simply click on button
					<small class="pico"><button class="bouton ui-state-deault"><i class="ui-icon ui-icon-cancel"></i></button></small>.<br />
					<br />
				</p>
				<div class="fixFloat"></div>
			</div>
		</li>
		<li>
			<h4>Shot discussions</h4>
			<div class="margeTop10" id="help_shot_messages">
				<img src="<?php echo $dirHelp ?>/help_shot_messages.jpg" class="floatL marge5 shadowOut"/>
				<p>
					A shot discussion is <b>linked to a published</b>. For a discussion to be possible, at least one published must be present,
					and <b>not validated</b>. Once the published validated, the discussion is <b>closed</b> (but can still be viewed by selecting the
					published in the list on the left).<br />
					The messages list is <b>sorted by date</b>, from newest (top) to oldest (bottom).<br />
					In each message's header, are the <b>pseudo</b> and <b>avatar</b> of the user who posted it, as well as the <b>date and time</b> of its
					creation.
				</p>
				<p>
					To <b>post a message</b>, you can click the button
					<small class="pico"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-mail-closed"></i></button></small>
					located in the black bar above, or <b>answer an existing message</b> by clicking on the same button in the header of the message
					of your choice. Answers are indented to the right.
				</p>
				<p>
					If you're the message author, you can <b>delete it</b> with button
					<small class="pico"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-trash"></i></button></small>.
				</p>
			</div>
			<div class="fixFloat"></div>
		</li>
		<li>
			<h4>Shots Folders</h4>
			<div class="margeTop10" id="help_shot_folders">
				<p>
					Shots folders are used to drop various files, like references, tries, inspiration...<br />
					They are <b>linked to departments</b>, that means each department has its own set of folders.
				</p>
				<img src="<?php echo $dirHelp ?>/help_shot_folders.jpg" class="marge5 shadowOut"/>
				<p>
					By default, it exists at least 2 folders (unchangeable, not deletables): <b>Bank shot</b>, and <b>Work in progress</b>. This "WIP"
					folder is the one into which modified published's files are stored, in order to keep track of published progresseion.<br />
					<br />
					When hovering a folder with mouse cursor, you can read the <b>number of files</b> it contains, and see a button
					<small class="pico"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-trash"></i></button></small> to <b>delete</b> the
					entire folder (only folders you created).<br />
					<br />
					By clicking on a folder, you open it and display its <b>content</b> on the right.
				</p>
				<p>
					You can <b>create a folder</b> at any time, using the button
					<small class="nano"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-plusthick"></i></button></small> located
					at the top right of folders list.
				</p>
				<p>
					To <b>add a file</b> into a folder, first open it, then drag & drop a file from your local machine over the drop zone
					on the right (this zone become blue while hovering it with a file). No validation is needed after upload, the file
					simply appear into the list.
				</p>
				<p>
					Into the files list, while hovering a file with the mouse cursor, you can read the <b>number of the file</b>, and (if you have access to) a button
					<small class="pico"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-trash"></i></button></small> to <b>delete</b>
					the file.<br />
					<br />
					At the top right of the file list, are <b>2 buttons</b>:
				</p>
				<p id="help_shot_folders_btns">
					- <small class="pico"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-clipboard"></i></button></small>
					To print a contact-sheet of all image files of the folder,<br />
					- <small class="pico"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-suitcase"></i></button></small>
					To download the entire folder content as a ZIP archive in your local machine.<br />
					<br />
				</p>
			</div>
		</li>
		<li>
			<h4>Link with scenes</h4>
			<div class="margeTop10">
				Each shot can be assigned to a "scene derivative", through a camera.
				(see ><a class="helpBtn noBorder" content="scenes">Scene > shots association</a><)<br />
			</div>
		</li>
		<li>
			<h4>Your shots</h4>
			<div class="margeTop10" id="help_vos_plans">
				<img src="<?php echo $dirHelp ?>/help_mes_plans.jpg" class="floatL marge10r marge15bot shadowOut"/>
				<p>This is the list of all shots assigned to you (shots which you are in team of).</p>
				<p>This list is refreshed all 3 minutes.<br />Vertical scroll available with mouse wheel.</p>
				<div class="fixFloat"></div>
				<p>Assignations are made within the "structure" department in "Shots" section <i>(special user status needed)</i></p>
				<img src="<?php echo $dirHelp ?>/help_mes_plans2.jpg" class="shadowOut"/>
			</div>
			<p></p>
		</li>
	</ol>
