<?php
	/*
	 * CN_GL - Shader Functions
	 *
	 * Last Updated:
	 *     2017/05/02 - 18:22 EST (Version 0.0.1)
	 *
	 * Description:
	 *     CN_GL (Clara Nguyen's GL Wrapper) helper PHP script to include functions
	 *     for dealing with shaders. These allow users to put their shaders in a
	 *     separate file such as a ".vert" or a ".frag" file for better management.
	 *
	 * Author:
	 *     Clara Van Nguyen
	 */

	//Requires "cn_gl.php".

	//Shader defined functions
	function cn_gl_load_fragment_shader($id, $path) {
		echo '<script id = "' . $id . '" type = "x-shader/x-fragment">' . PHP_EOL;
		require_once($path);
		echo '</script>' . PHP_EOL;
	}

	function cn_gl_load_vertex_shader($id, $path) {
		echo '<script id = "' . $id . '" type = "x-shader/x-vertex">' . PHP_EOL;
		require_once($path);
		echo '</script>' . PHP_EOL;
	}
?>
