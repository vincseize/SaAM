<?php
	@session_start();
	require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
?>

<div class="stageContent pad10 gros">
	<h2>SaAM - Shots and Assets Manager</h2>

	<p class="ui-state-disabled">Broadcast production management web-application (pipeline tool)</p>

	<div class="inline top demi">
		<p class="gros">Current version: <b class="colorErrText">SaAM <?php echo SAAM_VERSION; ?></b></p>

		<p>Developers team:</p>
		<ul>
			<li class="colorBtnFake"><a class="colorBtnFake" href="mailto:vincseize@gmail.com">Charles POTTIER</a></li>
			<li class="colorBtnFake"><a class="colorBtnFake" href="mailto:polo@polosson.com">Paul MAILLARDET</a></li>
		</ul>

		<p>Beta testers <span class="ui-state-disabled">(many thanks to them!)</span> :</p>
		<ul>
			<li class="colorBtnFake"><a class="colorBtnFake" href="http://sandman-pictures.com">SANDMAN PICTURES</a></li>
			<li class="colorBtnFake"><a class="colorBtnFake" href="#">LRDT (Le rêve du Tigre)</a></li>
			<li class="colorBtnFake"><a class="colorBtnFake" href="#">Ben D.</a></li>
			<li class="colorBtnFake"><a class="colorBtnFake" href="#">Emmanuel M.</a></li>
			<li class="colorBtnFake"><a class="colorBtnFake" href="#">All LRDS artists</a></li>
		</ul>

		<p class="">Additionnal credits:</p>
		<ul>
			<li class="colorMid"><a class="colorSoft" href="http://www.inwebson.com/jquery/jpreloader-a-preloading-screen-to-preload-images/">jpreloader</a></li>
			<li class="colorMid"><a class="colorSoft" href="http://modernizr.com/">ModernizR</a></li>
			<li class="colorMid"><a class="colorSoft" href="http://www.designyourway.net/drb/roundabout-jquery-slider/">CanvasTree</a></li>
			<li class="colorMid"><a class="colorSoft" href="http://jquery.com/">JQuery 1.7 API</a></li>
			<li class="colorMid"><a class="colorSoft" href="http://jqueryui.com/">JQuery 1.8.2 UI</a></li>
			<li class="colorMid"><a class="colorSoft" href="http://wiki.jqueryui.com/w/page/12138056/Selectmenu">JQuery UI selectmenu</a></li>
			<li class="colorMid"><a class="colorSoft" href="http://www.quasipartikel.at/multiselect/">JQuery UI multiselect</a></li>
			<li class="colorMid"><a class="colorSoft" href="http://foliotek.github.io/AjaxQ/">JQuery ajaxq</a></li>
			<li class="colorMid"><a class="colorSoft" href="https://github.com/carhartl/jquery-cookie">JQuery cookie</a></li>
			<li class="colorMid"><a class="colorSoft" href="http://rocha.la/jQuery-slimScroll">JQuery slimScroll</a></li>
			<li class="colorMid"><a class="colorSoft" href="https://github.com/brandonaaron/jquery-mousewheel">JQuery mousewheel</a></li>
			<li class="colorMid"><a class="colorSoft" href="http://fancybox.net/">JQuery fancybox</a></li>
			<li class="colorMid"><a class="colorSoft" href="http://www.designyourway.net/drb/roundabout-jquery-slider/">JQuery roundabout</a></li>
			<li class="colorMid"><a class="colorSoft" href="https://github.com/robmonie/jquery-week-calendar/wiki">JQuery weekcalendar</a></li>
			<li class="colorMid"><a class="colorSoft" href="https://github.com/blueimp/jQuery-File-Upload/wiki">Blueimp FileUploader</a></li>
		</ul>

		<p class="gras">&COPY; <a href="http://lerevedelasalamandre.net/">Le rêve de la salamandre</a> (LRDS) | 2012-<?php echo date('Y'); ?></p>
	</div>
	<div class="inline top demi">
		<p class="big">Project's home website: <a class="colorBtnFake" href="http://saamanager.net">http://saamanager.net</a></p>

		<p>SaAM issues and bugs tracker: <a class="colorBtnFake" href="http://bughunter.saamanager.net">bughunter.saamanager.net</a></p>
		<p>SaAM development Roadmap: <a class="colorBtnFake" href="http://saamtrackbacks.saamanager.org">saamtrackbacks.saamanager.org</a></p>
		<p>Frequently Asked Questions: <a class="colorBtnFake" href="http://saamfaq.saamanager.com">saamfaq.saamanager.org</a></p>
	</div>
</div>