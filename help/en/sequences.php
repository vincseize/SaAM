

<a id="sequences"></a>
<h2 class="ui-state-default ui-corner-top pad3">Sequences<div class="floatR marge30r doigt btnTop"><span class="ui-icon ui-icon-arrowreturn-1-n"></span></div></h2>
	<ol>
		<li>
			<h4>Definition of a sequence</h4>
			<div class="margeTop10">
				A "sequence" is a series of shots.
			</div>
		</li>
		<li>
			<h4>Sequence departments</h4>
			<div class="margeTop10">
				<img src="<?php echo $dirHelp ?>/help_shot_menu.jpg" class="marge5bot shadowOut" />
				<p>Sequences departements are the same as ><a class="helpBtn noBorder" content="shots">shots</a>< departments.</p>

			</div>
		</li>
		<li>
			<h4>Add a sequence</h4>
			<div class="margeTop10">
				<p>To add sequence, you must open the "SHOTS STRUCTURE" department (so have access granted to this dept).</p>
			</div>
			<div id="help_add_sequence">
				<p>
					In the sequences list window's header, you'll find a button <small class="micro"><button class="bouton"><i class="ui-icon ui-icon-plusthick"></i></button></small>.
					When clicking on it, a new line appears on top of the sequences list (see image below).<br /><br />
					<img src="<?php echo $dirHelp ?>/help_seq_add.jpg" class="marge5 shadowOut" />
				</p>
				<p>
					You can name the sequence with a title (optionnal). If you ommit it, the label will be used as title.
					Then, specify a <b>number of shots</b> that the sequence shall contain (very useful to quickly create your project structure).
					After that, specify a <b>start date</b>, an <b>end date</b> for the sequence. These dates will be used by the "schedule" department ("root" section),
					but will also inform you about the "deadline" of the sequence (remaining time before delivery date).
				</p>
				<p>
					Once done, you can click on the button <small class="micro"><button class="bouton ui-state-highlight"><i class="ui-icon ui-icon-check"></i></button></small>
					at the right end of the line. The sequence adds to the bottom of the list.
				</p>
				<p>
					If you want, you can add to it a description, once it has been added, by clicking on button
					<small class="micro"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-pencil"></i></button></small>.
				</p>
				<div class="fixFloat"></div>
			</div>
		</li>
		<li>
			<h4>Modify a sequence</h4>
			<div class="margeTop10" id="help_modify_sequence">
				<p>
					Every sequence line contain informations about the sequence. First, its <b>title</b>, then its <b>label</b>, and the number of <b>shots</b> it contains:
					the first number stands for the visible shots, the second for the total number of shots in the sequence.<br />
					Then, there is a gauge which display the global sequence progress (mean of alll shots progress).<br />
					And finally, the <b>start date</b>, <b>end date</b>, and the list of <b>users assigned</b> to the sequence.
				</p>
				<p>
					By <b>clicking</b> on a sequence line, you can display the <b>shots list</b> of the sequence.<br />
					You can also <b>reorder</b> sequences, by drag and drop them vertically.
				</p>
				<p>
					At the very right of the sequence line, are 5 buttons:
				</p>
				<p>
					<small class="micro"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-plusthick"></i></button></small>
					To <b>Add some shots</b> to the sequence. (see <a class="helpBtn noBorder" content="shots">Add shots</a>).<br />
					<small class="micro"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-lightbulb"></i></button></small>
					To <b>hide</b> the sequence. It will be visible only in the "shots structure" department.<br />
					<small class="micro"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-unlocked"></i></button></small>
					To <b>lock</b> the sequence. It will still be visible everywhere, but won't be alterable.<br />
					<small class="micro"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-pencil"></i></button></small>
					To <b>modify informations</b> of the sequence. Clicking on this button will transform the sequence line with some text input
					and selectors in order to change each information. Save and Cancel buttons are on the right.<br />
					<small class="micro"><button class="bouton ui-state-default"><i class="ui-icon ui-icon-trash"></i></button></small>
					To <b>archive</b> the sequence. Once archived, it will be considered as deleted, so invisible and not modifiable.
					However you can restore it at any time with the button
					<small class="micro"><button class="bouton ui-state-error"><i class="ui-icon ui-icon-refresh"></i></button></small> that will appear
					instead of those 5 buttons.
				</p>
			</div>
		</li>
	</ol>
