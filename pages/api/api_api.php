<?php
@session_start();

require_once ($_SESSION['INSTALL_PATH_INC']."/checkConnect.php" );
// includes


if (!$_SESSION['user']->isDev()) die();


?>

<link rel="stylesheet" type="text/css" href="_RECETTE/css/recette.css">

<div class="stageContent pad5">

	<p>For Plug-in development for example, you need to use some of our <b>SaAM's <?php echo L_BTN_API; ?></b></p>
	<br/>
	
	<hr>

	<p><b>Projects :</b></p>

	<p>Get Projects </p>
	<div class="highlight_file">
	<?php
	highlight_file("api_projects.txt");
	?>
	</div>
	<br/>

	<hr>

	<p><b>Sequences :</b></p>

	<p>Get Sequences from a project </p>
	<div class="highlight_file">
	<?php
	highlight_file("api_sequences.txt");
	?>
	</div>

</div>