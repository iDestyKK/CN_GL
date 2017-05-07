<!--
 * CN_GL Demo - UX2 Skybox Example
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
	var program_list = {};
	var yy = 0;
	var angle = 0;
	var SKYBOX_OBJ;
	var fbo, fboTex, depth_buffer;
	var special_cube, special_tex;
	var light_pos, light_lookat;

	//Declare CN_GL Init Function that is called whenever the "body" element is loaded.
	function init() {
		//Basic WebGL Properties
		gl.clearColor(0.0, 0.0, 0.0, 1);
		gl.clearDepth(1.0);
		gl.cullFace(gl.BACK);
		gl.enable(gl.DEPTH_TEST);
		gl.enable(gl.CULL_FACE);
		gl.depthFunc(gl.LESS);

		//Position our light
		light_pos = [512, 512, 256];
		light_lookat = [0, 0, 0];

		//Create shader programs
		program_list["CN_TRIANGLE_SHADER_PROGRAM"] = cn_gl_create_shader_program(
			cn_gl_get_shader("CN_TRIANGLE_FRAGMENT"),
			cn_gl_get_shader("CN_TRIANGLE_VERTEX")
		);
		
		program_list["CN_TEXTURE_SIMPLE_SHADER_PROGRAM"] = cn_gl_create_shader_program(
			cn_gl_get_shader("CN_TEXTURE_SIMPLE_FRAGMENT"),
			cn_gl_get_shader("CN_TEXTURE_SIMPLE_VERTEX")
		);

		program_list["CN_PHONG_NO_TEXTURE_SHADER_PROGRAM"] = cn_gl_create_shader_program(
			cn_gl_get_shader("CN_PHONG_NO_TEXTURE_FRAGMENT"),
			cn_gl_get_shader("CN_PHONG_NO_TEXTURE_VERTEX")
		);

		program_list["CN_ORTHO_TEXTURE_SHADER_PROGRAM"] = cn_gl_create_shader_program(
			cn_gl_get_shader("CN_ORTHO_TEXTURE_FRAGMENT"),
			cn_gl_get_shader("CN_ORTHO_TEXTURE_VERTEX")
		);

		program_list["CN_DEPTH_GEN_SHADER_PROGRAM"] = cn_gl_create_shader_program(
			cn_gl_get_shader("CN_DEPTH_GEN_FRAGMENT"),
			cn_gl_get_shader("CN_DEPTH_GEN_VERTEX")
		);

		//Create a camera
		camera = new CN_CAMERA();

		//Load the UT Texture
		texture_list["TEX_UT"] = new CN_TEXTURE("texture/tex_ut.png");

		//Load the level textures
		texture_list["TEX_LEVEL_GROUND"] = new CN_TEXTURE("texture/077.gif");
		texture_list["TEX_LEVEL_BOTTOM"] = new CN_TEXTURE("texture/185.gif");

		//Load cube model
		model_list["MDL_CUBE"] = new CN_MODEL("model/obj/cube.obj");
		model_list["MDL_RAILGUN"] = new CN_MODEL("model/obj/rail.obj");

		//Create the level models
		model_list["MDL_LEVEL_GROUND"] = new CN_MODEL("model/obj/gl_map_ground.obj");
		model_list["MDL_LEVEL_BOTTOM"] = new CN_MODEL("model/obj/gl_map_bottom.obj");

		//Create the level objects
		object_list.push(new CN_INSTANCE(
			0, 0, 0,
			model_list["MDL_LEVEL_GROUND"],
			texture_list["TEX_LEVEL_GROUND"],
			program_list["CN_TEXTURE_SIMPLE_SHADER_PROGRAM"]
		));

		object_list.push(new CN_INSTANCE(
			0, 0, -128,
			model_list["MDL_LEVEL_BOTTOM"],
			texture_list["TEX_LEVEL_BOTTOM"],
			program_list["CN_TEXTURE_SIMPLE_SHADER_PROGRAM"]
		));



		//Create the skybox object
		var SKYBOX_OBJS = [
			"model/obj/SKYBOX_CUBE/UX2_SKYBOX_FRONT.obj",
			"model/obj/SKYBOX_CUBE/UX2_SKYBOX_BACK.obj",
			"model/obj/SKYBOX_CUBE/UX2_SKYBOX_LEFT.obj",
			"model/obj/SKYBOX_CUBE/UX2_SKYBOX_RIGHT.obj",
			"model/obj/SKYBOX_CUBE/UX2_SKYBOX_TOP.obj",
			"model/obj/SKYBOX_CUBE/UX2_SKYBOX_BOTTOM.obj"
		];
		var SKYBOX_TEXS = [
			"texture/SKYBOX_CUBE/FRONT.png",
			"texture/SKYBOX_CUBE/BACK.png",
			"texture/SKYBOX_CUBE/LEFT.png",
			"texture/SKYBOX_CUBE/RIGHT.png",
			"texture/SKYBOX_CUBE/TOP.png",
			"texture/SKYBOX_CUBE/BOTTOM.png"
		];
		SKYBOX_OBJ = new CN_CUBE_SKYBOX(SKYBOX_OBJS, SKYBOX_TEXS);
		SKYBOX_OBJ.set_range(64.0);
		SKYBOX_OBJ.bind_to_camera(camera);

		//Now for the fun part. Let's make framebuffers.
		fbo = gl.createFramebuffer();
		gl.bindFramebuffer(gl.FRAMEBUFFER, fbo);

		var buf_width = 1024;//gl.canvas.width;
		var buf_height = 1024;//gl.canvas.height;

		fbo.width = buf_width;
		fbo.height = buf_height;
		
		rbo = gl.createRenderbuffer();
		gl.bindRenderbuffer(gl.RENDERBUFFER, rbo);
		gl.renderbufferStorage(gl.RENDERBUFFER, gl.DEPTH_COMPONENT16, buf_width, buf_height);
		gl.framebufferRenderbuffer(gl.FRAMEBUFFER, gl.DEPTH_ATTACHMENT, gl.RENDERBUFFER, rbo);

		fboTex = gl.createTexture();
		gl.bindTexture(gl.TEXTURE_2D, fboTex);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
		gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, buf_width, buf_height, 0, gl.RGBA, gl.UNSIGNED_BYTE, null);
		gl.generateMipmap(gl.TEXTURE_2D);
		gl.framebufferTexture2D(gl.FRAMEBUFFER, gl.COLOR_ATTACHMENT0, gl.TEXTURE_2D, fboTex, 0);
		gl.bindTexture(gl.TEXTURE_2D, null);
		gl.bindFramebuffer(gl.FRAMEBUFFER, null);

		special_tex = new CN_TEXTURE();
		special_tex.load_from_existing(fboTex);

		special_cube = new CN_INSTANCE(
			0, 0, 0,
			model_list["MDL_CUBE"],
			special_tex,
			program_list["CN_TEXTURE_SIMPLE_SHADER_PROGRAM"]
		);

		//Start the draw event.
		draw();
	}

	function render() {
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
	}

	function render_simple() {
		//Draw every object
		for (var i = 0; i < object_list.length; i++) {
			//Draw the object
			object_list[i].draw_just_triangles(
				program_list["CN_DEPTH_GEN_SHADER_PROGRAM"]
			);
		}
	}

	function draw_skybox(OBJ) {
		//Draw the skybox
		gl.useProgram(SKYBOX_OBJ.obj_array[0].program);
		camera.push_matrix_to_shader(
			SKYBOX_OBJ.obj_array[0].program,
			"uPMatrix",
			"uMVMatrix"
		);
		SKYBOX_OBJ.draw();
	}

	function draw() {
		resize(gl.canvas);

		//Render the shadow map first.
		//Clear the screen
		gl.clear(gl.CLEAR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);


		//Draw the first pass
		gl.bindFramebuffer(gl.FRAMEBUFFER, fbo);
		gl.viewport(0, 0, fbo.width, fbo.height);
		gl.clear(gl.CLEAR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);
		
		//Project the camera
		camera.set_projection_ext(
			light_pos[0], light_pos[1], light_pos[2],         //Camera position
			light_lookat[0], light_lookat[1], light_lookat[2],//Point to look at
			0, 0, 1,                                          //Up Vector
			75,                                               //FOV
			fbo.width / fbo.height,                           //Aspect Ratio
			1.0,                                              //Closest distance
			4096.0                                           //Farthest distance
		);
		gl.useProgram(program_list["CN_DEPTH_GEN_SHADER_PROGRAM"]);
		camera.push_matrix_to_shader(
			program_list["CN_DEPTH_GEN_SHADER_PROGRAM"],
			"uPMatrix",
			"uMVMatrix"
		);
		draw_skybox(SKYBOX_OBJ);
		gl.clear(gl.DEPTH_BUFFER_BIT);
		gl.useProgram(program_list["CN_DEPTH_GEN_SHADER_PROGRAM"]);
		camera.push_matrix_to_shader(
			program_list["CN_DEPTH_GEN_SHADER_PROGRAM"],
			"uPMatrix",
			"uMVMatrix"
		);
		//camera.
		render_simple();

		gl.bindFramebuffer(gl.FRAMEBUFFER, null);
		gl.viewport(0, 0, gl.canvas.width, gl.canvas.height);

		//Project the camera
		camera.set_projection_ext(
			Math.cos(angle) * 512, -Math.sin(angle) * 512, 128,   //Camera position
			0, 0, 0,                                        //Point to look at
			0, 0, 1,                                        //Up Vector
			75,                                             //FOV
			gl.canvas.clientWidth / gl.canvas.clientHeight, //Aspect Ratio
			1.0,                                            //Closest distance
			4096.0                                         //Farthest distance
		);
		angle += 0.01;

		gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);

		//Draw the skybox
		draw_skybox(SKYBOX_OBJ);

		//Ensure that the skybox is always behind whatever else is drawn
		gl.clear(gl.DEPTH_BUFFER_BIT);
		
		//Draw every object
		render();
		
		/*gl.useProgram(special_cube.program);
		camera.push_matrix_to_shader(
			special_cube.program,
			"uPMatrix",
			"uMVMatrix"
		);
		special_cube.draw();*/

		cn_gl_ortho_draw_texture(
			special_tex.texture,
			( 32 / gl.canvas.width ) + (-1),
			(-32 / gl.canvas.height) + (-1 * (-1 + (1024 / gl.canvas.height))),
			(1024 / gl.canvas.width),
			(1024 / gl.canvas.height)
		);
		
		//Request from the browser to draw again
		window.requestAnimationFrame(draw);
	}

	//Resize function to make sure that the canvas is the same size as the page.
	function resize(canvasID) {
		var retina = window.devicePixelRatio;
		if (retina / 2 > 1) {
			//Some devices may not be able to take drawing high resolution
			//Half the retina if it can be halved, but it can't go under 1x.
			retina /= 2;
		}
		canvasID.width  = canvasID.clientWidth  * retina;
		canvasID.height = canvasID.clientHeight * retina;
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
	<style type = "text/css">
		html, body {
			margin: 0px;
		}
	</style>
	<body onload = "cn_gl_init_gl('glCanvas', init)">
		<?php
			//Setup our 3D view
			cn_gl_create_canvas("glCanvas", "100vw", "100vh");

			//CN Generic Shaders for "draw_shapes"
			cn_gl_load_fragment_shader("CN_TRIANGLE_FRAGMENT", "shader/CN_SHAPES/triangle.frag");
			cn_gl_load_vertex_shader  ("CN_TRIANGLE_VERTEX", "shader/CN_SHAPES/triangle.vert");
			cn_gl_load_fragment_shader("CN_DEPTH_GEN_FRAGMENT", "shader/DepthShadowGen.frag");
			cn_gl_load_vertex_shader  ("CN_DEPTH_GEN_VERTEX", "shader/DepthShadowGen.vert");
			cn_gl_load_fragment_shader("CN_ORTHO_TEXTURE_FRAGMENT", "shader/CN_SHAPES/ortho_texture.frag");
			cn_gl_load_vertex_shader  ("CN_ORTHO_TEXTURE_VERTEX", "shader/CN_SHAPES/ortho_texture.vert");
			cn_gl_load_fragment_shader("CN_PHONG_NO_TEXTURE_FRAGMENT", "shader/PhongShaderNoTexture.frag");
			cn_gl_load_vertex_shader  ("CN_PHONG_NO_TEXTURE_VERTEX", "shader/PhongShaderNoTexture.vert");
			cn_gl_load_fragment_shader("CN_TEXTURE_SIMPLE_FRAGMENT", "shader/CN_SHAPES/texture_simple.frag");
			cn_gl_load_vertex_shader  ("CN_TEXTURE_SIMPLE_VERTEX", "shader/CN_SHAPES/texture_simple.vert");
		?>
	</body>
</html>
