<?php
	/*
	 * CN_GL - An attempt at a nice WebGL wrapper
	 *
	 * Last Updated:
	 *     2017/05/02 - 18:22 EST (Version 0.0.1)
	 *
	 * Description:
	 *     CN_GL (Clara Nguyen's GL Wrapper) is a wrapper to aide in writing WebGL
	 *     applications on the web with JavaScript and PHP. The approach is to
	 *     hide the implementation behind the scenes with functions to do all of
	 *     the hard work for them. This is similar to how GML works with Direct3D.
	 *
	 *     The library will consist of OBJ loading support, custom shaders, and
	 *     handlers for animations.
	 * 
	 *     Changelog is in the file "CHANGELOG".
	 *
	 * Author:
	 *     Clara Van Nguyen
	 */

	//Global Functions
	function cn_gl_init() {
		//Initialise new CN_GL Environment Global
		$GLOBALS['CN_GL'] = (object) array();

		//Set values
		cn_gl_set_environment('INIT', true);
	}

	function cn_gl_inject_js() {
		//Array of JS scripts to include
		$scripts = array(
			//jQuery... because we are apparently forced to use it.
			"http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js",

			//Other libraries
			"CN_GL/js/sylvester.js",

			//CN_GL Dependencies
			"CN_GL/js/cn_gl.js",
			"CN_GL/js/shader.js",
			"CN_GL/js/skybox.js",
			"CN_GL/js/texture.js",
			"CN_GL/js/model_obj.js",
			"CN_GL/js/instance.js",
			"CN_GL/js/camera.js",
			"CN_GL/js/draw_shapes.js"
		);

		//Loop through and add all of them in.
		foreach ($scripts as $scr_path) {
			echo '<script type = "text/javascript" src = "';
			echo $scr_path . '"></script>' . PHP_EOL;
		}
	}

	//Functions to deal with setting up 3D
	function cn_gl_create_canvas($id, $width, $height) {
		echo '<canvas id = "'.$id.'" style = "width:'.$width.';height:'.$height.';">';
		echo PHP_EOL;
		echo '	Your browser doesn\'t support the "canvas" element.' . PHP_EOL;
		echo '</canvas>' . PHP_EOL;
	}


	//Functions to deal with environment variables
	function cn_gl_get_environment($key) {
		return $GLOBALS['CN_GL']->{$key};
	}

	function cn_gl_set_environment($key, $value) {
		return ($GLOBALS['CN_GL']->{$key} = $value);
	}

	//Necessary includes
	require_once("shader.php");
?>
