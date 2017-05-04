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
	var gl, camera, cube_model, cube_object, shaderProgram, fS, vS, tri1, tri2, angle;
	var object_list = [];
	var CN_TRIANGLE_SHADER_PROGRAM;
	var yy = 0;
	var angle = 0;

	function init() {
		console.log(gl);
		//Basic WebGL Properties
		gl.clearColor(0.0, 0.0, 0.0, 1);
		gl.clearDepth(1.0);
		gl.enable(gl.DEPTH_TEST);
		gl.depthFunc(gl.LEQUAL);

		//Create shader programs
		CN_TRIANGLE_SHADER_PROGRAM = gl.createProgram();
		gl.attachShader(CN_TRIANGLE_SHADER_PROGRAM, cn_gl_get_shader("CN_TRIANGLE_FRAGMENT"));
		gl.attachShader(CN_TRIANGLE_SHADER_PROGRAM, cn_gl_get_shader("CN_TRIANGLE_VERTEX"));
		gl.linkProgram(CN_TRIANGLE_SHADER_PROGRAM);

		if (!gl.getProgramParameter(CN_TRIANGLE_SHADER_PROGRAM, gl.LINK_STATUS)) {
			console.log("Unable to init shader program");
		}

		//Create a camera
		camera = new CN_CAMERA();
		camera.set_projection_ext(2, 2, 2, 0, 0, 0, 0, 1, 0, 75, gl.canvas.clientWidth / gl.canvas.clientHeight, 0.1, 4096.0);

		//Start the draw event.
		draw();
	}

	function draw() {
		gl.clear(gl.CLEAR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);
		
		camera.set_projection_ext(Math.cos(angle) * 2, 2, -Math.sin(angle) * 2, 0, 0, 0, 0, 1, 0, 75, gl.canvas.clientWidth / gl.canvas.clientHeight, 0.1, 4096.0);
		angle += 0.1;
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
		?>
	</body>
</html>
