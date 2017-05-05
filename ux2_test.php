<!--
 * CN_GL Demo - UX2 Map Example
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

<script type = "text/javascript">
	var gl, camera;
	var object_list  = [];
	var model_list   = {};
	var texture_list = {};
	var CN_TRIANGLE_SHADER_PROGRAM, CN_TRIANGLE_NO_COLOUR_SHADER_PROGRAM;
	var yy = 0;
	var angle = 0;

	function init() {
		console.log(gl);
		//Basic WebGL Properties
		gl.clearColor(0.0, 0.0, 0.0, 1);
		gl.clearDepth(1.0);
		gl.enable(gl.DEPTH_TEST);
		gl.depthFunc(gl.LESS);

		//Create shader programs
		CN_TRIANGLE_SHADER_PROGRAM = cn_gl_create_shader_program(
			cn_gl_get_shader("CN_TRIANGLE_FRAGMENT"),
			cn_gl_get_shader("CN_TRIANGLE_VERTEX")
		);
		
		CN_TEXTURE_SIMPLE_SHADER_PROGRAM = cn_gl_create_shader_program(
			cn_gl_get_shader("CN_TEXTURE_SIMPLE_FRAGMENT"),
			cn_gl_get_shader("CN_TEXTURE_SIMPLE_VERTEX")
		);

		//Create a camera
		camera = new CN_CAMERA();

		//Load a texture
		var texture = new CN_TEXTURE("texture/077.gif");
		texture_list["GROUND_TEX"] = texture;
		
		//Load the block model
		var cube_model = new CN_MODEL();
		cube_model.load_from_obj("model/obj/cube.obj");
		model_list["CUBE"] = cube_model;
		
		//Load the base of the level as a model
		var ground_model = new CN_MODEL();
		ground_model.load_from_obj("model/obj/gl_map_ground.obj");
		model_list["LEVEL_GROUND"] = ground_model;

		//Create the map object
		var map_object = new CN_INSTANCE(
			0, 0, 0,
			model_list["LEVEL_GROUND"],
			texture_list["GROUND_TEX"],
			CN_TEXTURE_SIMPLE_SHADER_PROGRAM
		);
		object_list.push(map_object);

		//Start the draw event.
		draw();
	}

	function draw() {
		//Clear the screen
		gl.clear(gl.CLEAR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);

		//Project the camera
		camera.set_projection_ext(
			Math.cos(angle) * 512, -Math.sin(angle) * 512, 184, //Camera position
			0, 0, -128,                                         //Point to look at
			0, 0,    1,                                         //Up Vector (always this)
			75,                                                 //FOV
			gl.canvas.clientWidth / gl.canvas.clientHeight,     //Aspect Ratio
			1.0,                                                //Closest distance
			4096.0                                              //Farthest distance
		);
		angle += 0.01;
		
		//Draw every object
		var prev_program = null;
		for (var i = 0; i < object_list.length; i++) {
			//Only change shader program if the object uses a different shader program.
			if (prev_program != object_list[i].program) {
				gl.useProgram(object_list[i].program);
				camera.push_matrix_to_shader(
					object_list[i].program,
					"uPMatrix",
					"uMVMatrix"
				);
			}

			//Draw the object
			object_list[i].draw();
		}
		
		//Request from the browser to draw again
		window.requestAnimationFrame(draw);
	}
</script>

<html>
	<head>
		<title>CN_GL Demo: CS456 Final Project</title>
		<?php
			//Call to CN_GL to include all needed JS files.
			cn_gl_inject_js();
		?>
	</head>
	<body onload = "cn_gl_init_gl('glCanvas', init)">
		<?php
			//Setup our 3D view
			cn_gl_create_canvas("glCanvas", "1280", "720");

			//CN Generic Shaders for "draw_shapes"
			cn_gl_load_fragment_shader("CN_TRIANGLE_FRAGMENT", "shader/CN_SHAPES/triangle.frag");
			cn_gl_load_vertex_shader  ("CN_TRIANGLE_VERTEX", "shader/CN_SHAPES/triangle.vert");
			cn_gl_load_fragment_shader("CN_TEXTURE_SIMPLE_FRAGMENT", "shader/CN_SHAPES/texture_simple.frag");
			cn_gl_load_vertex_shader  ("CN_TEXTURE_SIMPLE_VERTEX", "shader/CN_SHAPES/texture_simple.vert");
		?>
	</body>
</html>
