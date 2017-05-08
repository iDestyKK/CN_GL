<!--
 * CN_GL Demo - FPS Camera Example
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
	var gl, spec_camera, fps_camera, camera;
	var object_list  = [];
	var model_list   = {};
	var texture_list = {};
	var program_list = {};
	var yy = 0;
	var angle = 0;
	var SKYBOX_OBJ, sky_rotation, sun_location;
	var fbo, fboTex, depth_buffer;
	var special_cube, special_tex;
	var light_pos, light_lookat, lightPOV;
	var water_height, water_array;
	var water_buffer, water_tex_buf, water_obj, water_tex;
	var bloom_bufferX, bloom_bufferY, bloom_texX, bloom_texY;
	var spawned;
	var player;
	var keymap;
	var prev_mouse_pos;
	var rotating_block;
	var got_railgun, railgun_obj, player_railgun_obj, railgun_recoil;

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
		sky_rotation = 0;
		sun_location = [-512, -108, 300];
		light_pos = [-512, -108, 300];
		light_lookat = [0, 0, 0];

		//Set water height
		water_height = -128;
		water_array = [0.0, 0.0, water_height, 0.0];

		//Set up our player
		player = {
			x    : 0,
			y    : 0,
			z    : 0,
			xydir: 0,
			zdir : 0
		};

		prev_mouse_pos = [0, 0];

		//Set up button presses
		keymap = {};

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
		spec_camera   = new CN_CAMERA();
		fps_camera    = new CN_CAMERA();
		mirror_camera = new CN_CAMERA();

		//Create our light too
		lightPOV = new CN_CAMERA();

		//Set the camera to the spec one by default
		camera = spec_camera;
		spawned = false;

		//Load Object Textures
		texture_list["TEX_MARIO_BLOCK"] = new CN_TEXTURE("texture/mario_question_block.png");
		texture_list["TEX_RAILGUN"]     = new CN_TEXTURE("texture/Railgun_Tex.gif");

		//Load the level textures
		texture_list["TEX_LEVEL_SAND"]  = new CN_TEXTURE("texture/sand_diffuse.jpg");
		texture_list["TEX_LEVEL_BRICK"] = new CN_TEXTURE("texture/brick1_diffuse.png");
		texture_list["TEX_LEVEL_METAL"] = new CN_TEXTURE("texture/metal_diffuse.png");

		//Load cube model
		model_list["MDL_CUBE"]        = new CN_MODEL("model/obj/cube.obj");
		model_list["MDL_TEAPOT"]      = new CN_MODEL("model/obj/teapot.obj");
		model_list["MDL_RAILGUN"]     = new CN_MODEL("model/obj/rail.obj");
		model_list["MDL_WATER_PLANE"] = new CN_MODEL("model/obj/water_plane.obj");

		//Create the level models
		model_list["MDL_LEVEL_SAND" ] = new CN_MODEL("model/obj/CN_GL_FINAL_SAND.obj" );
		model_list["MDL_LEVEL_BRICK"] = new CN_MODEL("model/obj/CN_GL_FINAL_BRICK.obj");
		model_list["MDL_LEVEL_METAL"] = new CN_MODEL("model/obj/CN_GL_FINAL_METAL.obj");
		//model_list["MDL_LEVEL_BOTTOM"] = new CN_MODEL("model/obj/gl_map_bottom.obj");

		//Create the level objects
		object_list.push(new CN_INSTANCE(
			0, 0, 0,
			model_list["MDL_LEVEL_SAND"],
			texture_list["TEX_LEVEL_SAND"],
			program_list["CN_PHONG_TEXTURE_SHADER_PROGRAM"]
		));

		object_list.push(new CN_INSTANCE(
			0, 0, 0,
			model_list["MDL_LEVEL_BRICK"],
			texture_list["TEX_LEVEL_BRICK"],
			program_list["CN_PHONG_TEXTURE_SHADER_PROGRAM"]
		));

		object_list.push(new CN_INSTANCE(
			0, 0, 0,
			model_list["MDL_LEVEL_METAL"],
			texture_list["TEX_LEVEL_METAL"],
			program_list["CN_PHONG_TEXTURE_SHADER_PROGRAM"]
		));
		
		//Mario Blocks
		object_list.push(new CN_INSTANCE(
			0, 0, 64,
			model_list["MDL_CUBE"],
			texture_list["TEX_MARIO_BLOCK"],
			program_list["CN_PHONG_TEXTURE_SHADER_PROGRAM"]
		));
		rotating_block = object_list[object_list.length - 1];
		rotating_block.set_scale(64, 64, 64);

		object_list.push(new CN_INSTANCE(
			-128, 0, 64,
			model_list["MDL_CUBE"],
			texture_list["TEX_MARIO_BLOCK"],
			program_list["CN_PHONG_TEXTURE_SHADER_PROGRAM"]
		));
		object_list[object_list.length - 1].set_scale(64, 64, 64);
		object_list[object_list.length - 1].set_rotation(-90, 0, 0);

		object_list.push(new CN_INSTANCE(
			128, 0, 64,
			model_list["MDL_CUBE"],
			texture_list["TEX_MARIO_BLOCK"],
			program_list["CN_PHONG_TEXTURE_SHADER_PROGRAM"]
		));
		object_list[object_list.length - 1].set_scale(64, 64, 64);
		object_list[object_list.length - 1].set_rotation(-90, 0, 0);

		//Hide the Teapot in the level
		object_list.push(new CN_INSTANCE(
			-840, -345, -64,
			model_list["MDL_TEAPOT"],
			null,
			program_list["CN_PHONG_NO_TEXTURE_SHADER_PROGRAM"]
		));
		object_list[object_list.length - 1].set_scale(8, 8, 8);
		object_list[object_list.length - 1].xrot = 90 / 180 * Math.PI;
		object_list[object_list.length - 1].zrot = 145 / 180 * Math.PI;
		
		//Hide the Railgun in the level too
		got_railgun = false;
		railgun_obj = new CN_INSTANCE(
			-800, -484, -16,
			model_list["MDL_RAILGUN"],
			texture_list["TEX_RAILGUN"],
			program_list["CN_PHONG_TEXTURE_SHADER_PROGRAM"]
		);
		railgun_obj.set_rotation(90, 180, 90);
		railgun_obj.set_scale(2.2, 2.2, 2.2);
		railgun_recoil = 0;

		//Make the railgun that the player has whenever they pick it up.
		player_railgun_obj = new CN_INSTANCE(
			0, 0, 0,
			model_list["MDL_RAILGUN"],
			texture_list["TEX_RAILGUN"],
			program_list["CN_PHONG_TEXTURE_SHADER_PROGRAM"]
		);

		//Create water objects
		water_tex = new CN_TEXTURE();
		water_obj = new CN_INSTANCE(
			0, 0, water_height,
			model_list["MDL_WATER_PLANE"],
			water_tex,
			program_list["CN_WATER_REFLECT_SHADER_PROGRAM"]
		);
		water_obj.set_scale(2048, 2048, 1);


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

		//Assign key pressing events to the player
		document.addEventListener("keydown", function(event) {
			key_pressed(event, true);
		});
		document.addEventListener("keyup", function(event) {
			key_pressed(event, false);
		});

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

		//If we haven't gotten the railgun yet, draw it too.
		if (!got_railgun) {
			if (prev_program != railgun_obj.program) {
				gl.useProgram(railgun_obj.program);
				_camera.push_matrix_to_shader(
					railgun_obj.program,
					"uPMatrix",
					"uMVMatrix"
				);
			}
			railgun_obj.draw();
		} else {
			//Draw the railgun on the player	
			if (prev_program != player_railgun_obj.program) {
				gl.useProgram(player_railgun_obj.program);
				_camera.push_matrix_to_shader(
					player_railgun_obj.program,
					"uPMatrix",
					"uMVMatrix"
				);
			}
			player_railgun_obj.draw();
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
		//If we haven't gotten the railgun yet, draw it too.
		if (!got_railgun) {
			railgun_obj.draw_just_triangles(
				program_list["CN_DEPTH_GEN_SHADER_PROGRAM"]
			);
		}
		else {
			player_railgun_obj.draw_just_triangles(
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

	function key_pressed(e, bool) {
		console.log(e);
		keymap[e.keyCode] = bool;
	}

	function step() {
		//Step event is where all button presses and calculations are made.
		//This is all guaranteed to happen prior to draw();
		var canvasID = gl.canvas;
		
		//Check if we have come in contact with the railgun
		if (!got_railgun) {
			var rdist = Math.sqrt(
				Math.pow(fps_camera.pos[0] - railgun_obj.x, 2) + 
				Math.pow(fps_camera.pos[1] - railgun_obj.y, 2) + 
				Math.pow(fps_camera.pos[2] - railgun_obj.z, 2) 
			);
			if (rdist < 64)
				got_railgun = true;
		}



		//Act upon key presses
		if (keymap[87] != undefined && keymap[87] == true) {
			//"W" Key
			player.x += Math.cos(player.xydir) * 4;
			player.y -= Math.sin(player.xydir) * 4;
			player.z += Math.sin(player.zdir ) * 4;
		}
		if (keymap[83] != undefined && keymap[83] == true) {
			//"S" Key
			player.x -= Math.cos(player.xydir) * 4;
			player.y += Math.sin(player.xydir) * 4;
			player.z -= Math.sin(player.zdir ) * 4;
		}
		if (keymap[65] != undefined && keymap[65] == true) {
			//"A" Key
			player.x += Math.cos(player.xydir - Math.PI * 0.5) * 4;
			player.y -= Math.sin(player.xydir - Math.PI * 0.5) * 4;
		}
		if (keymap[68] != undefined && keymap[68] == true) {
			//"D" Key
			player.x += Math.cos(player.xydir + Math.PI * 0.5) * 4;
			player.y -= Math.sin(player.xydir + Math.PI * 0.5) * 4;
		}
		if (keymap[27] != undefined && keymap[27] == true) {
			//"Esc" Key
			despawn();
			show_menu();
		}

		//Rotate the centre cube
		rotating_block.xrot += 0.004;

		//Rotate the sky
		sky_rotation += 0.001;
		var orig_dist = Math.sqrt(
			Math.pow(sun_location[0], 2) +
			Math.pow(sun_location[1], 2)
		);
		var orig_dir = Math.atan2(sun_location[1], sun_location[0]);
		light_pos = [
			orig_dist * Math.cos(orig_dir + sky_rotation),
			orig_dist * -Math.sin(orig_dir + sky_rotation),
			light_pos[2]
		];
		SKYBOX_OBJ.set_rotation(0, 0, (Math.PI-0.382-orig_dir - sky_rotation) * (180 / Math.PI));
	}

	function draw() {
		//Carry out our button handling first.
		step();
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
			-1500, 1500, -1500, 1500, -2048.0, 4096.0
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
		//Start with updating the spectator camera.
		spec_camera.set_projection_ext(
			Math.cos(angle) * 512, -Math.sin(angle) * 512, 128,   //Camera position
			0, 0, 0,                                        //Point to look at
			0, 0, 1,                                        //Up Vector
			75,                                             //FOV
			gl.canvas.clientWidth / gl.canvas.clientHeight, //Aspect Ratio
			1.0,                                            //Closest distance
			4096.0                                         //Farthest distance
		);

		//Update the fps camera.
		fps_camera.set_projection_ext(
			player.x,
			player.y,
			player.z,
			player.x + (Math.cos(player.xydir) * Math.cos(player.zdir)),
			player.y - (Math.sin(player.xydir) * Math.cos(player.zdir)),
			player.z + Math.sin(player.zdir),
			0, 0, 1,
			75,
			gl.canvas.clientWidth / gl.canvas.clientHeight,
			1.0,
			4096.0
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

		//Update the Railgun position and rotation
		player_railgun_obj.set_position(
			fps_camera.pos[0] + Math.cos(player.xydir + 0.7 + 0.2 * Math.abs(Math.sin(player.zdir))) * 6,
			fps_camera.pos[1] - Math.sin(player.xydir + 0.7 + 0.2 * Math.abs(Math.sin(player.zdir))) * 6,
			fps_camera.pos[2] - 3.0
		);
		player_railgun_obj.x += Math.cos(player.xydir) * Math.cos(player.zdir) * railgun_recoil;
		player_railgun_obj.y -= Math.sin(player.xydir) * Math.cos(player.zdir) * railgun_recoil;
		player_railgun_obj.z += Math.sin(player.zdir) * railgun_recoil;

		player_railgun_obj.xrot = -0.5 * Math.PI;
		player_railgun_obj.yrot = -player.zdir;
		player_railgun_obj.zrot = -player.xydir + 0.12;
		if (railgun_recoil < 0) {
			railgun_recoil += 1.2;
		}
		
		gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);

		//Draw reflected view first.
		//Draw the skybox
		draw_skybox(mirror_camera, SKYBOX_OBJ);

		//Ensure that the skybox is always behind whatever else is drawn
		gl.clear(gl.DEPTH_BUFFER_BIT);
		
		//Draw every object
		water_array[3] = 1.0;
		render(mirror_camera);

		water_array[3] = 0.0;
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

	//Page dependent functions
	function hide_menu() {
		var element = document.getElementsByClassName("tutorial-bg")[0];
		if (element != undefined) {
			element.style.left = "200%";
		}
	}

	function show_menu() {
		var element = document.getElementsByClassName("tutorial-bg")[0];
		if (element != undefined) {
			element.style.left = "0%";
		}
	}

	function mouse_rotate(e) {
		player.xydir += e.movementX / 150;
		player.zdir -= e.movementY  / 150;
		if (player.zdir >= Math.PI / 2)
			player.zdir = Math.PI / 2 - 0.01;
		if (player.zdir <= -Math.PI / 2)
			player.zdir = -Math.PI / 2 + 0.01;
	}

	function spawn() {
		camera = fps_camera;
		spawned = true;

		//Lock the pointer
		var element = document.body;
		element.requestPointerLock = element.requestPointerLock || element.mozRequestPointerLock || element.webkitRequestPointerLock;
		element.requestPointerLock();
		document.addEventListener("mousemove", mouse_rotate, false);
		document.addEventListener("mousedown", shoot, false);

		//Add events to ensure that it works right
		document.addEventListener('pointerlockchange', check_spawn, false);
		document.addEventListener('mozpointerlockchange', check_spawn, false);
		document.addEventListener('webkitpointerlockchange', check_spawn, false);
	}

	function shoot() {
		if (got_railgun)
			railgun_recoil = -12;
	}

	function despawn() {
		camera = spec_camera;
		spawned = false;

		//Unlock the pointer
		var element = document.body;
		element.exitPointerLock = element.requestPointerLock || element.mozRequestPointerLock || element.webkitRequestPointerLock;
		element.exitPointerLock();
		document.removeEventListener("mousemove", mouse_rotate, false);
	}

	function check_spawn(requestedElement) {
		console.log("Triggered");
		console.log(requestedElement);
		console.log(document.pointerLockElement);
		console.log(document.mozPointerLockElement);
		console.log(document.webkitPointerLockElement);
		if ((document.pointerLockElement === null || document.pointerLockElement === undefined) &&
			(document.mozPointerLockElement === null || document.mozPointerLockElement === undefined) && 
			(document.webkitPointerLockElement === null || document.webkidPointerLockElement === undefined)) {
			despawn();
			show_menu();
		}
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

		canvas {
			z-index: 1;
		}

		div.tutorial-bg {
			position: fixed;
			left: 0px;
			top: 0px;
			right: 0px;
			bottom: 0px;
			z-index: 100;
			background: rgba(0, 0, 0, 0.5);
			color: #FFF;
		}
		
		div.tutorial-bg table.welcome {
			width: 600px;
			height: 100%;
			position: relative;
			left: calc(50% - 300px);
			color: inherit;
		}
		
		div.tutorial-bg table.welcome div.title {
			font-size: 150%;
			padding-bottom: 22px;
		}
		input[type=button] {
			background: transparent;
			border: 2px solid #fff;
			border-radius: 4px;
			color: #fff;
			font-size: 125%;
			padding: 15px;
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
		<!-- Show the welcome screen -->
		<div class = "tutorial-bg">
			<table class = "welcome">
				<tr>
					<td width = "100%">
						<center><div class = "title">Welcome to CN_GL's FPS Example!</div></center>
						</br>
						In this tutorial, which is also my 456 final project,
						I will be showcasing how to implement a fully graphical FPS-like mode into a web browser.
						If you want to play, just click the button below.</br>
						This engine was based on the DERPG UX2 Engine that I made in 2013. You can read about it <a href = "http://wiki.derpg.xyz/index.php?title=UX2_Engine" target = "_blank">here</a>.

					</td>
				</tr>
				<tr>
					<td>
						<table width = "100%" style = "color: inherit;">
							<tr>
								<td valign = "top" width = "50%">
									<center><b>Controls:</b></center>
									</br>
									<ul>
										<li><b>W/A/S/D</b> - move (noclip).</br></li>
										<li><b>Move mouse</b> - look.</br></li>
										<li><b>Click left mouse</b> - to shoot (when you get Railgun).</li>
										<li><b>Escape</b> to go back to this menu.</b></li>
									</ul>
								</td>
								<td valign = "top" width = "50%">
									<center><b>Some Effects:</b></center>
									<ul>
										<li>Water Reflections</li>
										<li>Slight Phong Shading on the entire environment</li>
										<li>Realtime Dynamic Texture-based Shadows</li>
										<li>Two-pass Blur effect</li>
										<li><a href = "finalp_details.html" target = "_blank">Click here for the full list...</a></li>
									</ul>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<center><b>Well?</b></center>
						</br>
						<center>
							<input type = "button" value = "Click here to get started!" onclick = "hide_menu();spawn();"/>
						</center>
					</td>
				</tr>
			</table>
		</div>
	</body>
</html>
