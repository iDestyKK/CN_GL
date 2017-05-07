<!--
 * CN_GL Demo - Dynamic Shadows + Dynamic Water + Bloom Example
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
	var light_pos, light_lookat, lightPOV;
	var water_height;
	var water_buffer, water_tex_buf, water_obj, water_tex;
	var bloom_bufferX, bloom_bufferY, bloom_texX, bloom_texY;

	//Declare CN_GL Init Function that is called whenever the "body" element is loaded.
	function init() {
		//Basic WebGL Properties
		gl.clearColor(0.0, 0.0, 0.0, 1.0);
		gl.clearDepth(1.0);
		gl.cullFace(gl.BACK);
		gl.enable(gl.DEPTH_TEST);
		gl.enable(gl.CULL_FACE);
		gl.depthFunc(gl.LESS);

		//Position our light
		light_pos = [-512, -108, 300];
		light_lookat = [0, 0, 0];

		//Set water height
		water_height = -96;

		//Create shader programs
		program_list["CN_TRIANGLE_SHADER_PROGRAM"] = cn_gl_create_shader_program(
			cn_gl_get_shader("CN_TRIANGLE_FRAGMENT"),
			cn_gl_get_shader("CN_TRIANGLE_VERTEX")
		);
		
		program_list["CN_SKYBOX_SHADER_PROGRAM"] = cn_gl_create_shader_program(
			cn_gl_get_shader("CN_SKYBOX_FRAGMENT"),
			cn_gl_get_shader("CN_SKYBOX_VERTEX")
		);

		program_list["CN_BLOOM_X_SHADER_PROGRAM"] = cn_gl_create_shader_program(
			cn_gl_get_shader("CN_BLOOM_X_FRAGMENT"),
			cn_gl_get_shader("CN_BLOOM_VERTEX")
		);
		
		program_list["CN_BLOOM_Y_SHADER_PROGRAM"] = cn_gl_create_shader_program(
			cn_gl_get_shader("CN_BLOOM_Y_FRAGMENT"),
			cn_gl_get_shader("CN_BLOOM_VERTEX")
		);
		
		program_list["CN_TEXTURE_SIMPLE_SHADER_PROGRAM"] = cn_gl_create_shader_program(
			cn_gl_get_shader("CN_TEXTURE_SIMPLE_FRAGMENT"),
			cn_gl_get_shader("CN_TEXTURE_SIMPLE_VERTEX")
		);

		program_list["CN_PHONG_NO_TEXTURE_SHADER_PROGRAM"] = cn_gl_create_shader_program(
			cn_gl_get_shader("CN_PHONG_NO_TEXTURE_FRAGMENT"),
			cn_gl_get_shader("CN_PHONG_NO_TEXTURE_VERTEX")
		);

		program_list["CN_PHONG_TEXTURE_SHADER_PROGRAM"] = cn_gl_create_shader_program(
			cn_gl_get_shader("CN_PHONG_TEXTURE_FRAGMENT"),
			cn_gl_get_shader("CN_PHONG_TEXTURE_VERTEX")
		);

		program_list["CN_ORTHO_TEXTURE_SHADER_PROGRAM"] = cn_gl_create_shader_program(
			cn_gl_get_shader("CN_ORTHO_TEXTURE_FRAGMENT"),
			cn_gl_get_shader("CN_ORTHO_TEXTURE_VERTEX")
		);

		program_list["CN_DEPTH_GEN_SHADER_PROGRAM"] = cn_gl_create_shader_program(
			cn_gl_get_shader("CN_DEPTH_GEN_FRAGMENT"),
			cn_gl_get_shader("CN_DEPTH_GEN_VERTEX")
		);

		program_list["CN_WATER_REFLECT_SHADER_PROGRAM"] = cn_gl_create_shader_program(
			cn_gl_get_shader("CN_WATER_REFLECT_FRAGMENT"),
			cn_gl_get_shader("CN_WATER_REFLECT_VERTEX")
		);

		//Create a camera
		camera        = new CN_CAMERA();
		mirror_camera = new CN_CAMERA();

		//Create our light too
		lightPOV = new CN_CAMERA();

		//Load the UT Texture
		texture_list["TEX_UT"] = new CN_TEXTURE("texture/tex_ut.png");

		//Load the level textures
		texture_list["TEX_LEVEL_GROUND"] = new CN_TEXTURE("texture/077.gif");
		texture_list["TEX_LEVEL_BOTTOM"] = new CN_TEXTURE("texture/metal_diffuse.png");

		//Load cube model
		model_list["MDL_CUBE"] = new CN_MODEL("model/obj/cube.obj");
		model_list["MDL_RAILGUN"] = new CN_MODEL("model/obj/rail.obj");
		model_list["MDL_WATER_PLANE"] = new CN_MODEL("model/obj/water_plane.obj");

		//Create the level models
		model_list["MDL_LEVEL_GROUND"] = new CN_MODEL("model/obj/gl_map_ground.obj");
		model_list["MDL_LEVEL_BOTTOM"] = new CN_MODEL("model/obj/gl_map_bottom.obj");

		//Create the level objects
		object_list.push(new CN_INSTANCE(
			0, 0, 0,
			model_list["MDL_LEVEL_GROUND"],
			texture_list["TEX_LEVEL_GROUND"],
			program_list["CN_PHONG_TEXTURE_SHADER_PROGRAM"]
		));

		object_list.push(new CN_INSTANCE(
			0, 0, -128,
			model_list["MDL_LEVEL_BOTTOM"],
			texture_list["TEX_LEVEL_BOTTOM"],
			program_list["CN_PHONG_TEXTURE_SHADER_PROGRAM"]
		));

		object_list.push(new CN_INSTANCE(
			0, 0, 64,
			model_list["MDL_CUBE"],
			texture_list["TEX_UT"],
			program_list["CN_TEXTURE_SIMPLE_SHADER_PROGRAM"]
		));
		object_list[object_list.length - 1].set_scale(64, 64, 64);

		//Create water objects
		water_tex = new CN_TEXTURE();
		water_obj = new CN_INSTANCE(
			0, 0, water_height,
			model_list["MDL_WATER_PLANE"],
			water_tex,
			program_list["CN_WATER_REFLECT_SHADER_PROGRAM"]
		);
		water_obj.set_scale(512, 512, 1);


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
		//The first one is for shadows. No question about it.
		fbo = gl.createFramebuffer();
		gl.bindFramebuffer(gl.FRAMEBUFFER, fbo);

		var buf_width = 2048;//gl.canvas.width;
		var buf_height = 2048;//gl.canvas.height;

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
		
		//Now let's make the water buffer.
		water_buffer = gl.createFramebuffer();
		gl.bindFramebuffer(gl.FRAMEBUFFER, water_buffer);
		water_buffer.width  = gl.canvas.clientWidth;
		water_buffer.height = gl.canvas.clientHeight;

		var water_buffer_rb = gl.createRenderbuffer();
		gl.bindRenderbuffer(gl.RENDERBUFFER, water_buffer_rb);
		gl.renderbufferStorage(gl.RENDERBUFFER, gl.DEPTH_COMPONENT16, water_buffer.width, water_buffer.height);
		gl.framebufferRenderbuffer(gl.FRAMEBUFFER, gl.DEPTH_ATTACHMENT, gl.RENDERBUFFER, water_buffer_rb);
		
		water_tex_buf = gl.createTexture();
		gl.bindTexture(gl.TEXTURE_2D, water_tex_buf);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.NEAREST);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.NEAREST);

		gl.texImage2D(
			gl.TEXTURE_2D,
			0,
			gl.RGBA,
			water_buffer.width,
			water_buffer.height,
			0,
			gl.RGBA,
			gl.UNSIGNED_BYTE,
			null
		);

		gl.generateMipmap(gl.TEXTURE_2D);
		gl.framebufferTexture2D(
			gl.FRAMEBUFFER,
			gl.COLOR_ATTACHMENT0,
			gl.TEXTURE_2D,
			water_tex_buf,
			0
		);

		//Next up! Bloom Filter.
		//Bloom is a tricky one because to blur it, we need to do two passes.
		//Yes... we can do it in a single pass, but there are benefits in doing two.
		bloom_bufferX = gl.createFramebuffer();
		gl.bindFramebuffer(gl.FRAMEBUFFER, bloom_bufferX);
		bloom_bufferX.width  = gl.canvas.clientWidth  * 1.0;
		bloom_bufferX.height = gl.canvas.clientHeight * 1.0;

		var bloom_bufferX_rb = gl.createRenderbuffer();
		gl.bindRenderbuffer(gl.RENDERBUFFER, bloom_bufferX_rb);
		gl.renderbufferStorage(gl.RENDERBUFFER, gl.DEPTH_COMPONENT16, bloom_bufferX.width, bloom_bufferX.height);
		gl.framebufferRenderbuffer(gl.FRAMEBUFFER, gl.DEPTH_ATTACHMENT, gl.RENDERBUFFER, bloom_bufferX_rb);
		
		bloom_texX = gl.createTexture();
		gl.bindTexture(gl.TEXTURE_2D, bloom_texX);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.NEAREST);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.NEAREST);

		gl.texImage2D(
			gl.TEXTURE_2D,
			0,
			gl.RGBA,
			bloom_bufferX.width,
			bloom_bufferX.height,
			0,
			gl.RGBA,
			gl.UNSIGNED_BYTE,
			null
		);

		gl.generateMipmap(gl.TEXTURE_2D);
		gl.framebufferTexture2D(
			gl.FRAMEBUFFER,
			gl.COLOR_ATTACHMENT0,
			gl.TEXTURE_2D,
			bloom_texX,
			0
		);

		//Now for the Bloom Y component
		bloom_bufferY = gl.createFramebuffer();
		gl.bindFramebuffer(gl.FRAMEBUFFER, bloom_bufferY);
		bloom_bufferY.width  = gl.canvas.clientWidth  * 1.0;
		bloom_bufferY.height = gl.canvas.clientHeight * 1.0;

		var bloom_bufferY_rb = gl.createRenderbuffer();
		gl.bindRenderbuffer(gl.RENDERBUFFER, bloom_bufferY_rb);
		gl.renderbufferStorage(gl.RENDERBUFFER, gl.DEPTH_COMPONENT16, bloom_bufferY.width, bloom_bufferY.height);
		gl.framebufferRenderbuffer(gl.FRAMEBUFFER, gl.DEPTH_ATTACHMENT, gl.RENDERBUFFER, bloom_bufferY_rb);
		
		bloom_texY = gl.createTexture();
		gl.bindTexture(gl.TEXTURE_2D, bloom_texY);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.NEAREST);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.NEAREST);

		gl.texImage2D(
			gl.TEXTURE_2D,
			0,
			gl.RGBA,
			bloom_bufferY.width,
			bloom_bufferY.height,
			0,
			gl.RGBA,
			gl.UNSIGNED_BYTE,
			null
		);

		gl.generateMipmap(gl.TEXTURE_2D);
		gl.framebufferTexture2D(
			gl.FRAMEBUFFER,
			gl.COLOR_ATTACHMENT0,
			gl.TEXTURE_2D,
			bloom_texY,
			0
		);
		
		//Assign the texture to the water object.
		water_tex.load_from_existing(water_tex_buf);

		//Start the draw event.
		draw();
	}

	function render(_camera) {
		//Draw every object
		var prev_program = null;
		for (var i = 0; i < object_list.length; i++) {
			//Only change shader program if the object uses a different shader program.
			if (prev_program != object_list[i].program) {
				gl.useProgram(object_list[i].program);
				_camera.push_matrix_to_shader(
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

	function draw_skybox(_camera, OBJ) {
		//Draw the skybox
		OBJ.bind_to_camera(_camera);
		gl.useProgram(SKYBOX_OBJ.obj_array[0].program);
		_camera.push_matrix_to_shader(
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
		gl.clearColor(0.0, 0.0, 0.0, 0.0);
		gl.clear(gl.CLEAR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);
		
		//Project the camera
		lightPOV.set_projection_ext(
			light_pos[0], light_pos[1], light_pos[2],         //Camera position
			light_lookat[0], light_lookat[1], light_lookat[2],//Point to look at
			0, 0, 1,                                          //Up Vector
			75,                                               //FOV
			fbo.width / fbo.height,                           //Aspect Ratio
			1.0,                                              //Closest distance
			4096.0                                           //Farthest distance
		);
		lightPOV.perspective_matrix = cn_gl_make_projection_ortho(
			-1000, 1000, -1000, 1000, -2048.0, 4096.0
		);
		//light_pos[1] += 1;
		gl.useProgram(program_list["CN_DEPTH_GEN_SHADER_PROGRAM"]);
		lightPOV.push_matrix_to_shader(
			program_list["CN_DEPTH_GEN_SHADER_PROGRAM"],
			"uPMatrix",
			"uMVMatrix"
		);
		//camera.
		render_simple();

		gl.bindFramebuffer(gl.FRAMEBUFFER, water_buffer);
		gl.viewport(0, 0, water_buffer.width, water_buffer.height);
		
		//Project the normal and mirrored camera
		camera.set_projection_ext(
			Math.cos(angle) * 512, -Math.sin(angle) * 512, 128,   //Camera position
			0, 0, 0,                                        //Point to look at
			0, 0, 1,                                        //Up Vector
			75,                                             //FOV
			gl.canvas.clientWidth / gl.canvas.clientHeight, //Aspect Ratio
			1.0,                                            //Closest distance
			4096.0                                         //Farthest distance
		);

		mirror_camera.set_projection_ext(
			camera.pos[0],
			camera.pos[1],
			camera.pos[2] - ((camera.pos[2] - water_height) * 2),
			camera.lookat[0],
			camera.lookat[1],
			camera.lookat[2] - ((camera.lookat[2] - water_height) * 2),
			-camera.up[0], -camera.up[1], -camera.up[2],
			75,
			gl.canvas.clientWidth / gl.canvas.clientHeight,
			1.0,
			4096.0
		);
		angle += 0.01;
		
		gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);

		//Draw reflected view first.
		//Draw the skybox
		draw_skybox(mirror_camera, SKYBOX_OBJ);

		//Ensure that the skybox is always behind whatever else is drawn
		gl.clear(gl.DEPTH_BUFFER_BIT);
		
		//Draw every object
		render(mirror_camera);

		//Draw the actual scene... to the bloomX buffer
		gl.bindFramebuffer(gl.FRAMEBUFFER, bloom_bufferX);
		gl.viewport(0, 0, bloom_bufferX.width, bloom_bufferX.height);
		gl.clear(gl.CLEAR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);

		//Draw the skybox
		draw_skybox(camera, SKYBOX_OBJ);

		//Ensure that the skybox is always behind whatever else is drawn
		gl.clear(gl.DEPTH_BUFFER_BIT);

		//Draw every object
		render(camera);

		//Pass screen resolution to the water shader
		gl.useProgram(water_obj.program);
		camera.push_matrix_to_shader(
			water_obj.program,
			"uPMatrix",
			"uMVMatrix"
		);

		var screen_res_loc = gl.getUniformLocation(water_obj.program, "screen_res");
		gl.uniform2fv(screen_res_loc, [bloom_bufferX.width, bloom_bufferX.height]);

		//Draw the water reflections!
		gl.enable(gl.BLEND);
		gl.blendFunc(gl.SRC_ALPHA, gl.ONE_MINUS_SRC_ALPHA);
		water_obj.draw();


		// Now for the BLOOM Y. This one is much easier since we don't have to redraw
		gl.bindFramebuffer(gl.FRAMEBUFFER, bloom_bufferY);
		gl.viewport(0, 0, bloom_bufferY.width, bloom_bufferY.height);
		gl.clear(gl.CLEAR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);
		
		gl.useProgram(program_list["CN_BLOOM_X_SHADER_PROGRAM"]);
		var screen_res_loc = gl.getUniformLocation(program_list["CN_BLOOM_X_SHADER_PROGRAM"], "resolution");
		var blur_amount_loc = gl.getUniformLocation(program_list["CN_BLOOM_X_SHADER_PROGRAM"], "blur_amount");
		gl.uniform1f(blur_amount_loc, 4.0);
		gl.uniform2fv(screen_res_loc, [bloom_bufferX.width, bloom_bufferX.height]);
		cn_gl_ortho_draw_texture_loose(
			bloom_texX,
			-1, -1, 2, 2
		);


		//Now draw the actual scene... for the love of god	
		gl.bindFramebuffer(gl.FRAMEBUFFER, null);
		gl.viewport(0, 0, gl.canvas.width, gl.canvas.height);
		gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);
		
		//Draw the skybox
		draw_skybox(camera, SKYBOX_OBJ);

		//Ensure that the skybox is always behind whatever else is drawn
		gl.clear(gl.DEPTH_BUFFER_BIT);

		//Draw every object
		render(camera);

		//Pass screen resolution to the water shader
		gl.useProgram(water_obj.program);
		camera.push_matrix_to_shader(
			water_obj.program,
			"uPMatrix",
			"uMVMatrix"
		);

		var screen_res_loc = gl.getUniformLocation(water_obj.program, "screen_res");
		gl.uniform2fv(screen_res_loc, [gl.canvas.width, gl.canvas.height]);

		//Draw the water reflections!
		gl.enable(gl.BLEND);
		gl.blendFunc(gl.SRC_ALPHA, gl.ONE_MINUS_SRC_ALPHA);
		water_obj.draw();

		gl.blendFunc(gl.ONE, gl.ONE);
		//gl.disable(gl.BLEND);
		gl.useProgram(program_list["CN_BLOOM_Y_SHADER_PROGRAM"]);
		var screen_res_loc = gl.getUniformLocation(program_list["CN_BLOOM_Y_SHADER_PROGRAM"], "resolution");
		var blur_amount_loc = gl.getUniformLocation(program_list["CN_BLOOM_Y_SHADER_PROGRAM"], "blur_amount");
		gl.uniform1f(blur_amount_loc, 4.0);
		gl.uniform2fv(screen_res_loc, [bloom_bufferY.width, bloom_bufferY.height]);
		cn_gl_ortho_draw_texture_loose(
			bloom_texY,
			-1, -1, 2, 2
		);
		gl.disable(gl.BLEND);

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
			cn_gl_load_fragment_shader("CN_WATER_REFLECT_FRAGMENT", "shader/WaterReflect.frag");
			cn_gl_load_vertex_shader  ("CN_WATER_REFLECT_VERTEX", "shader/WaterReflect.vert");
			cn_gl_load_fragment_shader("CN_BLOOM_Y_FRAGMENT", "shader/BloomY.frag");
			cn_gl_load_fragment_shader("CN_BLOOM_X_FRAGMENT", "shader/BloomX.frag");
			cn_gl_load_vertex_shader  ("CN_BLOOM_VERTEX", "shader/Bloom.vert");
			cn_gl_load_fragment_shader("CN_PHONG_TEXTURE_FRAGMENT", "shader/PhongShaderWithTexture.frag");
			cn_gl_load_vertex_shader  ("CN_PHONG_TEXTURE_VERTEX", "shader/PhongShaderWithTexture.vert");
			cn_gl_load_fragment_shader("CN_SKYBOX_FRAGMENT", "shader/CN_SHAPES/skybox.frag");
			cn_gl_load_vertex_shader  ("CN_SKYBOX_VERTEX", "shader/CN_SHAPES/skybox.vert");
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
