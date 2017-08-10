<?php

/*
 * jQuery File Upload Plugin PHP Class 5.11.1
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

include ('arrays_fcts.php');
require_once ('directories.php');

class UploadHandler {
    protected $options;

    function __construct($options=null) {
        $this->options = array(
            'script_url' => $this->getFullUrl()."/actions/".basename($_SERVER['SCRIPT_FILENAME']),
            'upload_dir' => INSTALL_PATH.'temp/uploads/',
            'upload_url' => $this->getFullUrl().'/temp/uploads/',
            'is_single_file' => false,		// TRUE to erase all files in the /temp folder
            'rename_single_file' => false,	// RENAME THE FILE WITH A STRING IF NOT FALSE !! use it only if single file !!
            'param_name' => 'files',
            'delete_type' => 'DELETE',	// Set the following option to 'POST', if your server does not support DELETE requests. This is a parameter sent to the client
            'max_file_size' => null,	// The php.ini settings upload_max_filesize and post_max_size take precedence over this max_file_size setting
            'min_file_size' => 1,
            'accept_file_types' => '/.+$/i',
            'max_number_of_files' => null,		// The maximum number of files for the upload directory:
            'max_width' => null,				// Image resolution restrictions:
            'max_height' => null,
            'min_width' => 64,
            'min_height' => 36,
            'discard_aborted_uploads' => true,	// Set the following option to false to enable resumable uploads:
            'orient_image' => false,			// Set to true to rotate images based on EXIF meta data, if available:
            'image_versions' => array(			// Uncomment the version(s) you want to use (can be multiples !)
//				'large' => array(
//					'upload_dir' => $this->getFullUrl().'/temp/uploads/images',
//					'upload_url' => $this->getFullUrl().'/temp/uploads/images',
//					'max_width' => VIGNETTE_MEDIUM_W,
//					'max_height' => VIGNETTE_MEDIUM_H,
//					'jpeg_quality' => 90
//				),
                    'vignette' => array(
                    'upload_dir' => INSTALL_PATH.'/temp/uploads/vignettes/',
                    'upload_url' => $this->getFullUrl().'/temp/uploads/vignettes/',
                    'max_width' => VIGNETTE_MEDIUM_W,
                    'max_height' => VIGNETTE_MEDIUM_H,
                    'jpeg_quality' => JPG_QUALITY_H
                )
            )
        );
        if ($options) {
            $this->options = array_replace_recursive($this->options, $options);
        }
    }

    protected function getFullUrl() {
		$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
		$url = ($https) ? 'https://' : 'http://' ;
		$url.= (!empty($_SERVER['REMOTE_USER'])) ? $_SERVER['REMOTE_USER'].'@' : '';
		if (!isset($_SERVER['HTTP_HOST'])) {
			$url.= ($_SERVER['SERVER_NAME']. ($https && $_SERVER['SERVER_PORT'] === 443 || $_SERVER['SERVER_PORT'] === 80) ? '' : ':'.$_SERVER['SERVER_PORT']);
		}
		else $url.= $_SERVER['HTTP_HOST'];
		$url.= ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1' ) ? '/SaAM' : '';

		return $url;
    }

    protected function set_file_delete_url($file) {
        $file->delete_url = $this->options['script_url']
            .'?file='.rawurlencode($file->name);
        $file->delete_type = $this->options['delete_type'];
        if ($file->delete_type !== 'DELETE') {
            $file->delete_url .= '&_method=DELETE';
        }
    }

    protected function get_file_object($file_name) {
        $file_path = $this->options['upload_dir'].$file_name;
        if (is_file($file_path) && $file_name[0] !== '.') {
            $file = new stdClass();
            $file->name = $file_name;
            $file->size = filesize($file_path);
            $file->url = $this->options['upload_url'].rawurlencode($file->name);
            foreach($this->options['image_versions'] as $version => $options) {
                if (is_file($options['upload_dir'].$file_name)) {
                    $file->{$version.'_url'} = $options['upload_url']
                        .rawurlencode($file->name);
                }
            }
            $this->set_file_delete_url($file);
            return $file;
        }
        return null;
    }

    protected function get_file_objects() {
        return array_values(array_filter(array_map(
            array($this, 'get_file_object'),
            scandir($this->options['upload_dir'])
        )));
    }

    protected function create_scaled_image($file_name, $options) {
        $file_path = $this->options['upload_dir'].$file_name;
        $new_file_path = $options['upload_dir'].$file_name;
        list($img_width, $img_height) = @getimagesize($file_path);
        if (!$img_width || !$img_height) {
            return false;
        }
        $scale = min(
            $options['max_width'] / $img_width,
            $options['max_height'] / $img_height
        );
        if ($scale >= 1) {
            if ($file_path !== $new_file_path) {
                return copy($file_path, $new_file_path);
            }
            return true;
        }

        $new_width = $img_width * $scale;
        $new_height = $img_height * $scale;
        $new_img = @imagecreatetruecolor($new_width, $new_height);
        switch (strtolower(substr(strrchr($file_name, '.'), 1))) {
            case 'jpg':
            case 'jpeg':
                $src_img = @imagecreatefromjpeg($file_path);
                $write_image = 'imagejpeg';
                $image_quality = isset($options['jpeg_quality']) ?
                $options['jpeg_quality'] : JPG_QUALITY_M;
                break;
            case 'gif':
                @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                $src_img = @imagecreatefromgif($file_path);
                $write_image = 'imagegif';
                $image_quality = null;
                break;
            case 'png':
                @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                @imagealphablending($new_img, false);
                @imagesavealpha($new_img, true);
                $src_img = @imagecreatefrompng($file_path);
                $write_image = 'imagepng';
                $image_quality = isset($options['png_quality']) ?
                $options['png_quality'] : PNG_QUALITY_H;
                break;
            default:
                $src_img = null;
        }


        $success = $src_img && @imagecopyresampled(
            $new_img,
            $src_img,
            0, 0, 0, 0,
            $new_width,
            $new_height,
            $img_width,
            $img_height
        ) && $write_image($new_img, $new_file_path, $image_quality);
        // Free up memory (imagedestroy does not delete files):
        @imagedestroy($src_img);
        @imagedestroy($new_img);
        return $success;
    }

    protected function validate($uploaded_file, $file, $error, $index) {
        if ($error) {
            $file->error = $error;
            return false;
        }
        if (!$file->name) {
            $file->error = 'missing filename';
            return false;
        }
        if (!preg_match($this->options['accept_file_types'], $file->name)) {
            $file->error = 'file type not allowed!';
            return false;
        }
        if ($uploaded_file && is_uploaded_file($uploaded_file)) {
            $file_size = filesize($uploaded_file);
        } else {
            $file_size = $_SERVER['CONTENT_LENGTH'];
        }
        if ($this->options['max_file_size'] && (
                $file_size > $this->options['max_file_size'] ||
                $file->size > $this->options['max_file_size'])
            ) {
            $file->error = 'max file size reached';
            return false;
        }
        if ($this->options['min_file_size'] &&
            $file_size < $this->options['min_file_size']) {
            $file->error = 'min file size reached';
            return false;
        }
        if (is_int($this->options['max_number_of_files']) && (
                count($this->get_file_objects()) >= $this->options['max_number_of_files'])
            ) {
            $file->error = 'max number of files reached';
            return false;
        }
        list($img_width, $img_height) = @getimagesize($uploaded_file);
        if (is_int($img_width) && !preg_match('/video/i', check_mime_type($uploaded_file))) {
            if ($this->options['max_width'] && $img_width > $this->options['max_width'] ||
                    $this->options['max_height'] && $img_height > $this->options['max_height']) {
                $file->error = 'max resolution reached';
                return false;
            }
            if ($this->options['min_width'] && $img_width < $this->options['min_width'] ||
                    $this->options['min_height'] && $img_height < $this->options['min_height']) {
                $file->error = 'min resolution reached';
                return false;
            }
        }
        return true;
    }

    protected function upcount_name_callback($matches) {
        $index = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
        $ext = isset($matches[2]) ? $matches[2] : '';
        return '('.$index.')'.$ext;
    }

    protected function upcount_name($name) {
        return preg_replace_callback(
            '/(?:(?:\(([\d]+)\))?(\.[^.]+))?$/',
            array($this, 'upcount_name_callback'),
            $name,
            1
        );
    }

    protected function trim_file_name($name, $type, $index) {
		if ($this->options['rename_single_file']) $name = $this->options['rename_single_file'];
        // Remove path information and dots around the filename, to prevent uploading into different directories or replacing hidden system files.
        // Also remove control characters and spaces (\x00..\x20) around the filename:
        $file_name = trim(basename(stripslashes($name)), ".\x00..\x20");
		if ($this->options['is_single_file']) {
			$fichier = $this->options['upload_dir'].$file_name;
			if (file_exists($fichier.'.jpg')) unlink($fichier.'.jpg');
			if (file_exists($fichier.'.png')) unlink($fichier.'.png');
			if (file_exists($fichier.'.gif')) unlink($fichier.'.gif');
			if (file_exists($fichier.'.ogg')) unlink($fichier.'.ogg');
			if (file_exists($fichier.'.avi')) unlink($fichier.'.avi');
			if (file_exists($fichier.'.mov')) unlink($fichier.'.mov');
			if (file_exists($fichier.'.mp4')) unlink($fichier.'.mp4');
		}
        // Add missing file extension for known types:
        if (strpos($file_name, '.') === false && preg_match('/(gif|jpg|png|jpeg|ogg|x-msvideo|quicktime|mp4)/', $type, $matches)) {
			if ($matches[1] == 'jpeg') $matches[1] = 'jpg';
			if ($matches[1] == 'x-msvideo') $matches[1] = 'avi';
			if ($matches[1] == 'quicktime') $matches[1] = 'mov';
            $file_name .= '.'.$matches[1];
        }
        else {
			if ($this->options['discard_aborted_uploads']) {
				while(is_file($this->options['upload_dir'].$file_name)) {
					$file_name = $this->upcount_name($file_name);
				}
			}
		}
		return $file_name;
    }

    protected function handle_form_data($file, $index) {
        // Handle form data, e.g. $_REQUEST['description'][$index]
    }

    protected function orient_image($file_path) {
      	$exif = @exif_read_data($file_path);
        if ($exif === false) {
            return false;
        }
      	$orientation = intval(@$exif['Orientation']);
      	if (!in_array($orientation, array(3, 6, 8))) {
      	    return false;
      	}
      	$image = @imagecreatefromjpeg($file_path);
      	switch ($orientation) {
        	  case 3:
          	    $image = @imagerotate($image, 180, 0);
          	    break;
        	  case 6:
          	    $image = @imagerotate($image, 270, 0);
          	    break;
        	  case 8:
          	    $image = @imagerotate($image, 90, 0);
          	    break;
          	default:
          	    return false;
      	}
      	$success = imagejpeg($image, $file_path);
      	@imagedestroy($image);						// Free up memory (imagedestroy does not delete files):
      	return $success;
    }

    protected function handle_file_upload($uploaded_file, $name, $size, $type, $error, $index) {
        $file = new stdClass();
        $file->name = $this->trim_file_name($name, $type, $index);
        $file->size = intval($size);
        $file->type = $type;
        if ($this->validate($uploaded_file, $file, $error, $index)) {
            $this->handle_form_data($file, $index);
            $file_path = $this->options['upload_dir'].$file->name;
            $append_file = !$this->options['discard_aborted_uploads'] && is_file($file_path) && $file->size > filesize($file_path);
            clearstatcache();
            if ($uploaded_file && is_uploaded_file($uploaded_file)) {			// multipart/formdata uploads (POST method uploads)
                if ($append_file) {
                    file_put_contents(
                        $file_path,
                        fopen($uploaded_file, 'r'),
                        FILE_APPEND
                    );
                }
				else {
                    move_uploaded_file($uploaded_file, $file_path);
                }
            }
			else {																// Non-multipart uploads (PUT method support)
                file_put_contents(
                    $file_path,
                    fopen('php://input', 'r'),
                    $append_file ? FILE_APPEND : 0
                );
            }
            $file_size = filesize($file_path);
            if ($file_size === $file->size) {
            	if ($this->options['orient_image']) {
            		$this->orient_image($file_path);
            	}
                $file->url = $this->options['upload_url'].rawurlencode($file->name);
                foreach($this->options['image_versions'] as $version => $options) {
                    if ($this->create_scaled_image($file->name, $options)) {
                        if ($this->options['upload_dir'] !== $options['upload_dir']) {
                            $file->{$version.'_url'} = $options['upload_url'].rawurlencode($file->name);
                        }
						else {
                            clearstatcache();
                            $file_size = filesize($file_path);
                        }
                    }
                }
																				// CONVERSION À LA VOLÉE EN OGV (avec audio)
				$mimeType_file = check_mime_type($file_path);
				if (preg_match('/video|MPEG|QuickTime/i', $mimeType_file) && !preg_match('/ogg|ogv|audio/i', $mimeType_file)) {
					$vIn  = escapeshellarg($file_path);
					$vOut = escapeshellarg($file_path . '.ogv');
					exec("avconv -i $vIn -c:v libtheora -c:a libvorbis -q 6 $vOut", $retour, $errStatus);
					if ($errStatus == 0) {
						unlink($file_path);
						$file->name = $file->name . '.ogv';
					}
					// pour DEBUG :
//					$file->error = "retour de avconv : $errStatus<br />
//									vIn : $vIn<br />
//									vOut : $vOut<br />";
//					foreach ($retour as $retourLine)
//						$file->error .= $retourLine . '<br />';
				}
            }
			else if ($this->options['discard_aborted_uploads']) {
                unlink($file_path);
                $file->error = 'aborted. It may be caused by an unknown directory. Check temp/ folder.';
            }
            $file->size = $file_size;
            $this->set_file_delete_url($file);
        }
        return $file;
    }

    public function get() {
        $file_name = isset($_REQUEST['file']) ?
            basename(stripslashes($_REQUEST['file'])) : null;
        if ($file_name) {
            $info = $this->get_file_object($file_name);
        } else {
            $info = $this->get_file_objects();
        }
        header('Content-type: application/json');
        echo json_encode($info);
    }

    public function post() {
        if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
            return $this->delete();
        }
        // param_name is an array identifier like "files", $_FILES is a multi-dimensional array:
        $upload = isset($_FILES[$this->options['param_name']]) ? $_FILES[$this->options['param_name']] : null;
        $info = array();
		if ($upload && is_array($upload['tmp_name'])) {
            foreach ($upload['tmp_name'] as $index => $value) {
                $info[] = $this->handle_file_upload(
                    $upload['tmp_name'][$index],
                    isset($_SERVER['HTTP_X_FILE_NAME']) ? $_SERVER['HTTP_X_FILE_NAME'] : $upload['name'][$index],
                    isset($_SERVER['HTTP_X_FILE_SIZE']) ? $_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'][$index],
                    isset($_SERVER['HTTP_X_FILE_TYPE']) ? $_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'][$index],
                    $upload['error'][$index],
                    $index
                );
            }
        } elseif ($upload || isset($_SERVER['HTTP_X_FILE_NAME'])) {
            $info[] = $this->handle_file_upload(
                isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
                isset($_SERVER['HTTP_X_FILE_NAME']) ?
                    $_SERVER['HTTP_X_FILE_NAME'] : (isset($upload['name']) ? $upload['name'] : null),
                isset($_SERVER['HTTP_X_FILE_SIZE']) ?
                    $_SERVER['HTTP_X_FILE_SIZE'] : (isset($upload['size']) ? $upload['size'] : null),
                isset($_SERVER['HTTP_X_FILE_TYPE']) ?
                    $_SERVER['HTTP_X_FILE_TYPE'] : (isset($upload['type']) ? $upload['type'] : null),
                isset($upload['error']) ? $upload['error'] : null
            );
        }
		else $info[] = array('error'=>'File corrupted. Please check encoding.');
        header('Vary: Accept');
        $json = json_encode($info);
        $redirect = isset($_REQUEST['redirect']) ? stripslashes($_REQUEST['redirect']) : null;
        if ($redirect) {
            header('Location: '.sprintf($redirect, rawurlencode($json)));
            return;
        }
        if (isset($_SERVER['HTTP_ACCEPT']) && (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
            header('Content-type: application/json');
        } else {
            header('Content-type: text/plain');
        }
        echo $json;
    }

    public function delete() {
        $file_name = isset($_REQUEST['file']) ?
            basename(stripslashes($_REQUEST['file'])) : null;
        $file_path = $this->options['upload_dir'].$file_name;
        $success = is_file($file_path) && $file_name[0] !== '.' && unlink($file_path);
        if ($success) {
            foreach($this->options['image_versions'] as $version => $options) {
                $file = $options['upload_dir'].$file_name;
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        header('Content-type: application/json');
        echo json_encode($success);
    }

}


if (!function_exists('array_replace_recursive')) {
	function array_replace_recursive($array, $array1) {
		function recurse($array, $array1) {
			foreach ($array1 as $key => $value) {
				// create new key in $array, if it is empty or not an array
				if (!isset($array[$key]) || (isset($array[$key]) && !is_array($array[$key]))) {
					$array[$key] = array();
				}
				// overwrite the value in the base array
				if (is_array($value)) {
					$value = recurse($array[$key], $value);
				}
				$array[$key] = $value;
			}
			return $array;
		}

		// handle the arguments, merge one by one
		$args = func_get_args();
		$array = $args[0];
		if (!is_array($array)) {
			return $array;
		}
		for ($i = 1; $i < count($args); $i++) {
			if (is_array($args[$i])) {
				$array = recurse($array, $args[$i]);
			}
		}
		return $array;
	}
}
