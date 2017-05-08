/*
 * CN_GL - Skybox Assist Functions
 *
 * Description:
 *     One way to cheat in making a skybox is to make an inside-out shape and
 *     then draw it behind everything else. This implements a few helper
 *     functions which will allow you to easily create skyboxes for the world.
 * 
 * Author:
 *     Clara Van Nguyen
 */

function CN_CUBE_SKYBOX(OBJ_ARRAY, TEX_ARRAY) {
	//Default Values
	this.obj_array = null;
	this.mdl_array = null;
	this.tex_array = null;
	this.camera    = null;

	//Loads all of the OBJs and TEXs
	if (OBJ_ARRAY != undefined && TEX_ARRAY != undefined) {
		this.obj_array = Array();
		this.tex_array = Array();
		this.mdl_array = Array();
		
		//Load the textures first
		for (var i = 0; i < TEX_ARRAY.length; i++) {
			this.tex_array.push(new CN_TEXTURE(TEX_ARRAY[i]));
		}

		//Load the OBJs next
		for (var i = 0; i < OBJ_ARRAY.length; i++) {
			this.mdl_array.push(new CN_MODEL(OBJ_ARRAY[i]));
			this.obj_array.push(new CN_INSTANCE(
				0, 0, 0,
				this.mdl_array[i],
				this.tex_array[i],
				program_list["CN_SKYBOX_SHADER_PROGRAM"]
			));
		}
	}
}

CN_CUBE_SKYBOX.prototype.bind_to_camera = function(cam) {
	this.camera = cam;
}

CN_CUBE_SKYBOX.prototype.set_range = function(val) {
	for (var i = 0; i < this.obj_array.length; i++) {
		this.obj_array[i].set_scale(val, val, val);
	}
}

CN_CUBE_SKYBOX.prototype.set_rotation = function(x, y, z) {
	for (var i = 0; i < this.obj_array.length; i++) {
		this.obj_array[i].set_rotation(x, y, z);
	}
}

CN_CUBE_SKYBOX.prototype.draw = function() {
	if (this.camera != null) {
		for (var i = 0; i < this.obj_array.length; i++) {
			this.obj_array[i].set_position(
				this.camera.pos[0],
				this.camera.pos[1],
				this.camera.pos[2]
			);
		}
	}
	for (var i = 0; i < this.obj_array.length; i++) {
		this.obj_array[i].draw();
	}
}
