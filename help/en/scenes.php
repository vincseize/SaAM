
<a id="scenes"></a>
<h2 class="ui-state-default ui-corner-top pad3">Scenes<div class="floatR marge30r doigt btnTop"><span class="ui-icon ui-icon-arrowreturn-1-n"></span></div></h2>
	<ol>
		<li>
			<h4>Definition of a scene</h4>
			<img src="<?php echo $dirHelp ?>/help_scene_diagram.jpg" class="floatR marge10 shadowOut" style="max-width: 55%;"/>
			<div class="margeTop10">
				<p>
					A <b>scene</b> is characterized by a place (set), and an action. It must not be confused with a sequence (which is only
					a succession of shots), or with a shot (which is the result of a camera filming the scene).
				</p>
				In a scene, there is:
				<ul>
					<li>some <b>assets</b>, defining characters, set, props, etc.</li>
					<li><b>animations</b> that will define action, filmed by cameras.</li>
					<li>several <b>cameras</b>. Each camera is associated to a shot, into a sequence.</li>
				</ul>
				<p>
					There are two types of scenes: <b>MASTER</b> scenes, and <b>CHILD</b> scenes (also called <b>"DERIVATIVES"</b>).<br />
					"Master" scenes are the base entity of scenes, i.e. the groupment of assets, place and action. They generally don't include
					cameras. "Child" scenes are some <b>instanciated copies</b> of the master scene, i.e. they include the same assets (characters,
					set, action, etc.) than their master scene, but a child scene also include the cameras filming the scene. A child scene can also <b>exclude
					some assets</b> which are not necessary to a shot (if they are out of frame, for example).
				</p>
				<p>
					Generally, we <b>first build a master scene</b>, to define an entire action into a set (matching film's synopsis). Then we
					<b>derivate this scene</b> into several child scenes ("derivatives"), according to the <b>storyboard</b>, in order to add cameras,
					which will be associated to shots in film's sequences.
				</p>
				<p>
					This method allows to <b>add assets later</b> in master scenes, without having to add them also in child scenes, because they
					depends on their master scene, and therefore have the same assets by default. This is very useful, especially if there are a
					lot of derivatives of the master scene. Moreover, the benefit is that cameras inside child scenes can be <b>animated independantly</b>.
				</p>
				<p>
					Scenes management is done into the "Scenes" section (drop-down menu at the top left of the project window).
				</p>
			</div>
		</li>
		<li>
			<h4>Scenes departments</h4>
			<img src="<?php echo $dirHelp ?>/help_scene_departments.jpg" class="marge5 shadowOut" />
			<p>
				These departments are only present when they have been choosen in the project's configuration.
				It's possible to rename, and add some departments in tools panel, button "SaAM admin" (for users with access granted on this tool).<br />
				In every departments, the scenes window is composed by 4 parts:
			</p>
			<ul>
				<li>
					<h4>Scenes lists</h4>
					On the left, <b>scenes listing</b>, which can be displays in multiple ways:
					<div id="help_scenes_list_tabs">
						<h5>Listing type choice for scenes</h5>
						<img src="<?php echo $dirHelp ?>/help_scene_list_tabs.jpg" class="marge5 shadowOut" /><br /><br />
						These tabs allows you to choose the way you want to sort scenes:
						<ul>
							<li>List ALL the project's scenes, even scenes which are not yet associated to shots.</li>
							<li>Sort scenes by sequence. Here are displayed only the scenes which are already associated to a shot.</li>
							<li>Sort scenes by tags. <i>(not yet implemented)</i></li>
						</ul>
						You'll also find a <b>search module</b>. Click on the button
						<small class="pico"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-search"></i></button></small>
						to display a search input. While typing a search term, the scenes list is <b>filtered in real time</b>. Hit "Esc" key to
						unfilter the list.
					</div>
					<div id="help_scenes_list_all">
						<h5>List of all project's scenes</h5>
						<img src="<?php echo $dirHelp ?>/help_scene_list_all.jpg" class="floatL marge10 shadowOut" /><br />
						<p>
							The scenes list is categorized by packs of 10.<br />
							Scenes in <b class="colorBtnFake">blue</b> are MASTER scenes.<br />
							Scenes in <b class="colorDark">grey</b> are CHILD scenes ("derivatives").<br />
							Scene  in <b class="colorErrText">yellow</b> is the selected scene, displayed in the right part of the window.<br />
						</p>
						<p>
							By clicking on a master scene, you display its child scenes below (with right-indentation), but you also display its
							informations in the right part of the window.<br />
							Scenes title's nomenclatura is not managed by SaAM, so you can name it as you want. However, by default SaAM gives the prefix
							"M_SC" to master scenes, and "#_SC" to child scenes. Of course you can change this behaviour, in SaAM configuration
							panel, if you have acces to it.
						</p>
						<p>
							The button <small class="petit"><button class="bouton ui-state-default">Create Master scene<i class="inline mid ui-icon ui-icon-plusthick"></i></button></small>
							is to create a master scene in the list (see >Add a master scene<).
						</p>
					</div>
					<div class="fixFloat"></div>
					<div id="help_scenes_list_sequences">
						<h5>List scenes by sequence</h5>
						<img src="<?php echo $dirHelp ?>/help_scene_list_seq.jpg" class="floatL marge10 shadowOut" /><br />
						<p>
							The scenes list is sorted by sequence. Sequences are the one defined in "SEQ. & SHOTS" section.<br />
							Scenes in <b class="colorBtnFake">blue</b> are MASTER scenes.<br />
							Scenes in <b class="colorDark">grey</b> are CHILD scenes ("derivatives").<br />
							Scene  in <b class="colorErrText">yellow</b> is the selected scene, displayed in the right part of the window.<br />
						</p>
						<p>
							By clicking on a sequence, you can show the list of its scenes. This will also hide other sequences lists.
							<br/>
							By clicking on a master scene, you display its child scenes below (with right-indentation), but you also display its
							informations in the right part of the window.<br />
							Scenes title's nomenclatura is not managed by SaAM, so you can name it as you want. However, by default SaAM gives the prefix
							"M_SC" to master scenes, and "#_SC" to child scenes. Of course you can change this behaviour, in SaAM configuration
							panel, if you have acces to it.
						</p>
						<p>
							The button <small class="petit"><button class="bouton ui-state-default">Create Master scene<i class="inline mid ui-icon ui-icon-plusthick"></i></button></small>
							is to create a master scene in the list (see >Add a master scene<).
						</p>
					</div>
					<div class="fixFloat"></div>
				</li>
				<li id="help_scenes_center_view">
					<h4>Information about MASTER scene</h4>
					On center, informations about the selected MASTER scene. If you selected a CHILD scene, informations about its master
					scene will be displayed here.<br />
					At the top in the black bar, the master scene title.<br /><br />
					Below, 4 tabs:<br /><br />
					- <b>"Derivatives"</b> :
					<div id="help_scenes_master_infos_derivates">
						<img src="<?php echo $dirHelp ?>/help_scene_master_infos_deriv.jpg" class="floatL marge10 shadowOut" /><br />
						Displays th <b>derivatives list</b> (children) of the selected master scene. You can click on a derivative
						to select it.
						<p>
							You can create a derivative with the button <b>"Create Derivative"</b>, which opens a form on the right part
							of the scene window.<br />
							(see ><a class="helpBtn noBorder" content="scenes">Create a derivative</a><)
						</p>
					</div>
					<div class="fixFloat"></div>
					- <b>"Infos"</b> :
					<div id="help_scenes_master_scene_infos">
						<img src="<?php echo $dirHelp ?>/help_scene_master_infos_infos.jpg" class="floatL marge10 shadowOut" /><br />
						Displays <b>informations</b> about the selected master scene (or about the master scene of the selected child scene).
					</div>
					<div class="fixFloat"></div>
					- <b>"Assets"</b> :
					<div id="help_scenes_master_infos_assets">
						<img src="<?php echo $dirHelp ?>/help_scene_master_infos_assets.jpg" class="floatL marge10 shadowOut" /><br />
						<p>
							Displays the <b>assets</b> list which contain the selected scene.<br />
							<br />
							The assets are sorted by category. Click on a category to display its assets list.
						</p>
						<p>
							To manage the scene's assets (add or remove assets from the selected master scene), you can click on button
							<b>"Manage assets (MASTER)"</b><br />
							(see ><a class="helpBtn noBorder" content="scenes">Assets > scene association</a><)
						</p>
					</div>
					<div class="fixFloat"></div>
					- <b>"Shots"</b> :
					<div id="help_scenes_master_infos_shots">
						<img src="<?php echo $dirHelp ?>/help_scene_master_infos_shots.jpg" class="floatL marge10 shadowOut" /><br />
						Displays the <b>shots list</b> associated to the selected scène. You can click on a shot image to open its
						window.<br />
						<br />
						To manage shots assignations, click on the button <b>"Assign shots (MASTER)"</b><br />
						(see ><a class="helpBtn noBorder" content="scenes">Scene > shots association</a><)
					</div>
					<div class="fixFloat"></div>
				</li>
				<li id="help_scenes_top_right_view">
					<h4>Information selected scene</h4>
					<p>
						At the top right of scenes window, are displayed:<br />
						- <b>steps</b> of the current department,<br />
						- <b>informations</b> about the selected scene,<br />
						- an <b>image</b> which identify the scene
					</p>
					<p id="help_scenes_info_bar">
						Above, in the black bar, is displayed the selected <b>scene title</b>, its <b>version number</b>, and
						the name of the <b>user</b> who handle the scene's file.<br />
						<br />
						If the selected scene is a master scene, a <b>yellow frame</b> "MASTER" is displayed on the left, to remind you.<br />
						Else, a <b>white frame</b> "DERIVATIVE" is displayed in case of a child scene.
					</p>
					<div id="help_scenes_department_steps">
						<h5>Departement steps</h5>
						<p>
							If no step is defined yet for the scene in the department, a <b>yellow edge</b> surrounds the steps. The department
							button in the top menu stay grey (with a blue edge to let you know it's selected).<br />
							<img src="<?php echo $dirHelp ?>/help_scene_steps_no.jpg" class="marge10 shadowOut" /><br />
							Once a step is selected, the yellow edge disappear, and the choosen step becomes blue. The department button in
							the top menu become blue too.<br />
							<img src="<?php echo $dirHelp ?>/help_scene_steps.jpg" class="marge10 shadowOut" />
						</p>
					</div>
					<div id="help_scenes_informations_top_right">
						<h5>Informations of selected scene</h5>
						<img src="<?php echo $dirHelp ?>/help_scene_infos_right.jpg" class="floatL marge5 shadowOut" />
						<p>
							Displayed informations are:<br />
							- Pseudos of <b>superviseur</b> and <b>lead</b> of the scene,<br />
							- Users list of the <b>artists team</b> who work on the scene,<br />
							- <b>Start</b> and <b>end</b> dates,<br />
							- Number of <b>derivatives</b> of the scene,<br />
							- Number of <b>assets</b> linked to the scene<br />
							- Number of <b>shots</b> associated (scène MASTER), or number of <b>cameras</b> contained (CHILD scene).<br />
							- A button <small class="nano"><button class="bouton"><i class="ui-icon ui-icon-bookmark"></i></button></small> to
							  assign tasks to users for this scene.
						</p>
					</div>
					<div class="fixFloat"></div>
					<div id="help_scenes_vignette">
						<h5>Image of the selected scene</h5>
						<p>
							An image which allows us to quickly identify the scene. You can change this image by dragging & dropping an
							image file onto the image frame.
						</p>
					</div>
					<div id="help_scenes_hung_by">
						<h5>User handling</h5>
						<p>
							When a scene's file is <b>free</b>, you can see : <img src="<?php echo $dirHelp ?>/help_scene_hungFree.jpg" class="inline mid marge5" /><br />
							In contrast, when it's <b>used by someone</b> who works with the file ("hung by"), this is displayed :
							<img src="<?php echo $dirHelp ?>/help_scene_hungBy.jpg" class="inline mid marge5" />
						</p>
						<p>
							However, for an assignation to a user be available, a <b>step must be defined</b> before, in any department.<br />
							<br />
							If not, assignations are <b>locked</b>, even if the file is "free" (handled by nobody) :
							<img src="<?php echo $dirHelp ?>/help_scene_hungLock.jpg" class="inline mid marge5" />
						</p>
					</div>
				</li>
				<li id="help_scenes_right_view">
					<h4>Selected scene panel</h4>
					<p>
						For master scenes, this panel is the same as the center part of the window, except for "infos" tab, which allows
						modifications of the scene's informations (and not only display).<br />
						In any cases, this panel includes tabs on top:
					</p>
					<ol id="help_scenes_right_view_tabs">
						<li id="help_scenes_published_panel">
							<b>Published</b>: Displays scene's messages and published for the current choosen department (see "Scenes discussions").
						</li>
						<li>
							<b>Derivatives</b>: ("master" scenes only): List of derivatives (child scenes) of the selected scene. The button
							<small class="mini"><button class="bouton">Create Derivative</button></small> allows to create a derivative of the selected
							master scene. (see "Create a derivative").
						</li>
						<li id="help_scenes_informations_panel">
							<b>Infos</b>: To change scene's informations (users, description, dates...)<br />
							Click on a button <small class="nano"><button class="bouton"><i class="ui-icon ui-icon-pencil"></i></button></small> to
							change an information.<br /><br />
							The <b>label</b> is also displayed here for information (with path).<br /><br />
						</li>
						<li id="help_scenes_assets_panel">
							<b>Assets</b>: The list of included assets in the scene. The button <small class="mini"><button class="bouton">Manage Assets</button></small>
							allows to manage <b>assets inclusion</b> (for master scenes), or <b>assets exclusion</b> (for child scenes)
							(see "Assets > scene association").
						</li>
						<li id="help_scenes_shots_panel">
							<b>Shots</b>: The list of all shots associated to the selected scene, with the camera used for assication.
							The button <small class="mini"><button class="bouton">Assign Shots</button></small>
							allows to manage assignation of shots to ths scene. If it's a "master" scene, the associations will be general to all derivatives.
							<br />
							The button <small class="mini"><button class="bouton">Manage Cameras</button></small> (child scenes only) allows to create cameras into scene
							and assign shots to those cameras. (see "Scene > shots association").
						</li>
					</ol>
				</li>
			</ul>
		</li>
		<li>
			<h4>Add a MASTER scene</h4>
			<div class="margeTop10" id="help_create_master_scene">
				<p>
					To create a "Master" scene, click on button
					<small class="petit"><button class="bouton ui-state-default">Create Master scene<i class="inline mid ui-icon ui-icon-plusthick"></i></button></small>.<br />
					<br />
					A form displays below:
				</p>
				<img src="<?php echo $dirHelp ?>/help_scene_create_master.jpg" class="floatL marge5 shadowOut" />
				<p>
					Here you can define a title, assign a supervisor, a lead, and a team for the scene, as well as start and end ("deadline") dates,
					and a description. Only the title is mandatory.<br />
					The label is automatically generated by SaAM, for internal operations, and API access.<br />
					Once you're done, click on the button
					<small class="pico"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-check"></i></button></small>.<br />
					You can cancel the add, with the button
					<small class="pico"><button class="bouton ui-state-error"><i class="ui-icon ui-icon-cancel"></i></button></small>.
				</p>
				<p>
					Once the scene is created, it's displayed at the bottom of the list. Please note that you must display the "all scenes" list,
					("list all" tab), because this new scene is not yet assigned to any sequence, so won't be visible in the "by sequence" list
					view.
				</p>
			</div>
			<div class="fixFloat"></div>
		</li>
		<li>
			<h4>Create a derivative (CHILD scene)</h4>
			<div class="margeTop10" id="help_scenes_create_derivative">
				<img src="<?php echo $dirHelp ?>/help_scene_create_deriv.jpg" class="floatL marge5 shadowOut" />
				<p>
					To create a derivative, you must click on the button <small class="mini"><button class="bouton">Create Derivative</button></small>.
					A form displays. Fill in the informations for the derivative scene:<br />
					- a <b>titre</b> for the derivative<br />
					- the <b>label</b> is given by SaAM automatically<br />
					- the <b>users</b> assigned on the scene (supervisor, lead, artists)<br />
					- start and end <b>dates</b><br />
					- a quick <b>description</b> for the scene<br />
				</p>
				<p>
					Once the derivateive created, it's displayed in the list on the left, below the selected master scene,
					but also in the center panel (master scene informations), and below the button "create derivative" of the
					right panel.
				</p>
				<div class="fixFloat"></div>
			</div>
		</li>
		<li>
			<h4>Scenes discussions</h4>
			<div class="margeTop10" id="help_scenes_published">
				<p>
					A "published" corresponds to a key step in the  dans la realization of a scene. It's an image or a video, which show the achievement
					progress, and around which concerned people (scene team, lead, supervisor) can start a discussion.
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
					(see "scenes folders").<br />
					- <small class="pico"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-disk"></i></button></small>
					To validate the published. This will close the discussion about the published (no more message can be posted), and to
					allow the creation of a new published.<br />
				</p>
				<div class="fixFloat"></div>
				<p id="help_scenes_add_published">
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
			<div class="margeTop10" id="help_scenes_messages">
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
				<div class="fixFloat"></div>
			</div>
		</li>
		<li>
			<h4>Scene > shots association</h4>
		</li>
		<li>
			<h4>Assets > scene association</h4>
		</li>
		<li>
			<h4>Cameras managment</h4>
		</li>
		<li>
			<h4>Your scenes</h4>
			<div class="margeTop10" id="help_vos_scenes">
				<img src="<?php echo $dirHelp ?>/help_mes_scenes.jpg" class="floatL marge10r marge15bot shadowOut"/>
				<p>This is the list of all scenes assigned to you (scenes which you are in team of).</p>
				<p>This list is refreshed all 3 minutes.<br />Vertical scroll available with mouse wheel.</p>
				<div class="fixFloat"></div>
				<p>Assignations are made within the "Scenes" in "Info" section <i>(special user status needed)</i></p>
			</div>
		</li>
	</ol>

