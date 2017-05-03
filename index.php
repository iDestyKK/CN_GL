<!--
 * CN_GL Demo - Quake Map Demonstration
 *
 * Description:
 *     This library was developed by me to aide in this final project. It uses
 *     all of my knowledge of WebGL. Feel free to look at how the engine is
 *     documented.
 *
 *     This engine was only written to aid me in CS456's final project. Since
 *     I wrote everything in it, only I am entitled to use it for educational
 *     purposes.
 * 
 * Author:
 *     Clara Van Nguyen
 *
-->

<?php
	//Initialise CN_GL Engine
	require_once("CN_GL/cn_gl.php");
	
	//Initialise CN_GL
	cn_gl_init();
?>

<html>
	<head>
		<title>CN_GL Demo: CS456 Final Project</title>
		<?php
			//Call to CN_GL to include all needed JS files.
			cn_gl_inject_js();
		?>
	</head>
	<body>
		<?php
			//Setup our 3D view
			cn_gl_create_canvas("glCanvas", 800, 600);

			//Behold our shaders
			cn_gl_load_fragment_shader("FragmentShader1", "shader/FragmentShader1.frag");
			cn_gl_load_vertex_shader("VertexShader1", "shader/VertexShader1.vert");
		?>
	</body>
</html>
