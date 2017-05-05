<!--
 * CN_GL Demo - Triangle Example
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
	var gl, camera, cube_model;
	var object_list = [];
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
		camera.set_projection_ext(4, 2, 4, 0, 0, 0, 0, 1, 0, 75, gl.canvas.clientWidth / gl.canvas.clientHeight, 0.1, 256.0);
		
		//Load the block model
		cube_model = new CN_MODEL();
		cube_model.load_from_obj("model/obj/cube.obj");
		
		//Create 4 block objects that use this model
		var cube_object = new CN_INSTANCE();
		cube_object.set_position(2, 0, 0);
		cube_object.set_model(cube_model);
		cube_object.set_texture("texture/tex_ut.png");
		cube_object.set_program(CN_TEXTURE_SIMPLE_SHADER_PROGRAM);
		object_list.push(cube_object);

		cube_object = new CN_INSTANCE();
		cube_object.set_position(-2, 0, 0);
		cube_object.set_model(cube_model);
		cube_object.set_texture("texture/tex_ut.png");
		cube_object.set_program(CN_TEXTURE_SIMPLE_SHADER_PROGRAM);
		object_list.push(cube_object);

		cube_object = new CN_INSTANCE();
		cube_object.set_position(0, 0, -2);
		cube_object.set_model(cube_model);
		cube_object.set_texture("texture/tex_ut.png");
		cube_object.set_program(CN_TEXTURE_SIMPLE_SHADER_PROGRAM);
		object_list.push(cube_object);

		cube_object = new CN_INSTANCE();
		cube_object.set_position(0, 0, 2);
		cube_object.set_model(cube_model);
		cube_object.set_texture("texture/tex_ut.png");
		cube_object.set_program(CN_TEXTURE_SIMPLE_SHADER_PROGRAM);
		object_list.push(cube_object);

		//Start the draw event.
		draw();
	}

	function draw() {
		gl.clear(gl.CLEAR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);
		
		camera.set_projection_ext(
			Math.cos(angle) * 4, 2, -Math.sin(angle) * 4,   //Camera position
			0, 0, 0,                                        //Point to look at
			0, 1, 0,                                        //Up Vector (always this)
			75,                                             //FOV
			gl.canvas.clientWidth / gl.canvas.clientHeight, //Aspect Ratio
			1.0,                                            //Closest distance
			256.0                                          //Farthest distance
		);
		angle += 0.01;
		//camera.push_matrix_to_shader(CN_TRIANGLE_SHADER_PROGRAM, "uPMatrix", "uMVMatrix");

		gl.useProgram(CN_TRIANGLE_SHADER_PROGRAM);
		camera.push_matrix_to_shader(CN_TRIANGLE_SHADER_PROGRAM, "uPMatrix", "uMVMatrix");

		draw_triangle(
			-0.5,  0.5, 0,
			 0.5,  0.5, 0,
			 0  , -0.5, 0, 
			make_color_rgb(255, 0, 0), 
			make_color_rgb(255, 0, 255),
			make_color_rgb(255, 255, 0)
		);
		draw_triangle(
			-0.5, yy    , 0,
			 0.5, yy    , 0,
			 0  , yy - 1, 0,
			make_color_rgb(255, 0, 0),
			make_color_rgb(255, 0, 255),
			make_color_rgb(255, 255, 0)
		);

		//Draw the floor
		draw_triangle(-1, -1, -1, 1, -1, -1, 1, -1, 1,
			make_color_rgb(127, 127, 127),
			make_color_rgb(127, 127, 127),
			make_color_rgb(127, 127, 127)
		);
		draw_triangle(1, -1, 1, -1, -1, 1, -1, -1, -1,
			make_color_rgb(127, 127, 127),
			make_color_rgb(127, 127, 127),
			make_color_rgb(127, 127, 127)
		);
		
		gl.useProgram(CN_TEXTURE_SIMPLE_SHADER_PROGRAM);
		camera.push_matrix_to_shader(CN_TEXTURE_SIMPLE_SHADER_PROGRAM, "uPMatrix", "uMVMatrix");

		for (var i = 0; i < object_list.length; i++)
			object_list[i].draw();

		yy += 0.1;
		if (yy > 1)
			yy = 0;

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
