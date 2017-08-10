<?php

function autoload ($classname) {
	if (file_exists ($file = INSTALL_PATH.'classes/'.$classname.'.class.php'))
		require ($file);
}

spl_autoload_register ('autoload');

?>
