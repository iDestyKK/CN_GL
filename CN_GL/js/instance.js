/*
 * CN_GL - Instances (Objects)
 *
 * Description:
 *     In GML, all instances are handled as objects. Because we plan to make
 *     this engine work in a similar way, all models must be drawn or handled
 *     by means of objects. Each object will have a create event, step event,
 *     and draw event handling it. This makes it easier to draw copies of
 *     models since we only have to load them once.
 * 
 * Author:
 *     Clara Van Nguyen
 */

function CN_INSTANCE() {
	//Coordinate Information
	this.x          = 0;
	this.y          = 0;
	this.z          = 0;
	this.start_x    = 0;
	this.start_y    = 0;
	this.start_z    = 0;
	this.previous_x = 0;
	this.previous_y = 0;
	this.previous_z = 0;

	//Attributes
	this.xscale = 1;
	this.yscale = 1;
	this.zscale = 1;
	this.xrot = 0;
	this.yrot = 0;
	this.zrot = 0;

	//Model Information
	this.model = null;
	
	//Shader Information
	this.program = null;

	//Texture Information
	this.texture = null;
}

function CN_INSTANCE(_x, _y, _z, _model, _texture, _program) {
	//Coordinate Information
	this.x          = _x;
	this.y          = _y;
	this.z          = _z;
	this.start_x    = _x;
	this.start_y    = _y;
	this.start_z    = _z;
	this.previous_x = _x;
	this.previous_y = _y;
	this.previous_z = _z;

	//Attributes
	this.xscale = 1;
	this.yscale = 1;
	this.zscale = 1;
	this.xrot = 0;
	this.yrot = 0;
	this.zrot = 0;

	//Model Information
	this.model = _model;
	
	//Shader Information
	this.program = _program;

	//Texture Information
	this.texture = _texture;
}

CN_INSTANCE.prototype.init = function(_x, _y, _z) {
	this.x          = _x;
	this.y          = _y;
	this.z          = _z;
	this.start_x    = _x;
	this.start_y    = _y;
	this.start_z    = _z;
	this.previous_x = _x;
	this.previous_y = _y;
	this.previous_z = _z;
}

//Scale Functions
CN_INSTANCE.prototype.set_scale = function(_x, _y, _z) {
	this.xscale = _x;
	this.yscale = _y;
	this.zscale = _z;
}

//TODO: Add X Y Z only scale functions

//Rotation Functions
CN_INSTANCE.prototype.set_rotation = function(_x, _y, _z) {
	this.xrot = _x / 180 * Math.PI;
	this.yrot = _y / 180 * Math.PI;
	this.zrot = _z / 180 * Math.PI;
}

//TODO: Add X Y Z only rotation functions

CN_INSTANCE.prototype.set_position = function(_x, _y, _z) {
	this.previous_x = this.x;
	this.previous_y = this.y;
	this.previous_z = this.z;

	this.x = _x;
	this.y = _y;
	this.z = _z;
}

CN_INSTANCE.prototype.set_model = function(modelOBJ) {
	this.model = modelOBJ;
}

CN_INSTANCE.prototype.set_program = function(programID) {
	this.program = programID;
}

CN_INSTANCE.prototype.set_texture = function(_texture) {
	this.texture = _texture;
}

/*CN_INSTANCE.prototype.set_texture = function(texturePath) {
	this.texture_path = texturePath;
	
	//Set up the basic texture
	this.texture                 = gl.createTexture();
	this.texture_image           = new Image();
	this.texture_image.src       = this.texture_path;

	//Watch this hack.
	this.texture_image.cn_parent = this;

	//Give it a 1x1 white placement texture until the image loads
	gl.bindTexture(gl.TEXTURE_2D, this.texture);

	gl.texImage2D(
		gl.TEXTURE_2D,
		0,
		gl.RGBA,
		1,
		1,
		0,
		gl.RGBA,
		gl.UNSIGNED_BYTE,
		new Uint8Array([255, 255, 255, 255])
	);

	//Whenever the texture actually loads. Replace the blank texture with this one.
	this.texture_image.addEventListener('load', function () {
		gl.bindTexture(gl.TEXTURE_2D, this.cn_parent.texture);
		gl.texImage2D(
			gl.TEXTURE_2D,
			0,
			gl.RGBA,
			gl.RGBA,
			gl.UNSIGNED_BYTE,
			this.cn_parent.texture_image
		);
		gl.generateMipmap(gl.TEXTURE_2D);
	});
}*/

CN_INSTANCE.prototype.draw = function() {
	if (this.model != undefined && this.model.ready == true) {
		//Draw only if the instance has a model

		//Deal with textures
		var ver_pos_attr = gl.getAttribLocation(this.program, "vec_pos");

		gl.bindBuffer(gl.ARRAY_BUFFER, this.model.vertex_buffer);
		gl.vertexAttribPointer(
			ver_pos_attr,
			3,
			gl.FLOAT,
			gl.FALSE,
			0,
			0
		);
		gl.enableVertexAttribArray(ver_pos_attr);

		//Deal with normals
		var norm_pos_attr = gl.getAttribLocation(this.program, "normal");
		if (norm_pos_attr != -1) {
			//This shader supports normals. Let's push the normal buffer onto it.
			gl.bindBuffer(gl.ARRAY_BUFFER, this.model.vertex_normal_buffer);
			gl.vertexAttribPointer(
				norm_pos_attr,
				3,
				gl.FLOAT,
				gl.FALSE,
				0,
				0
			);
			gl.enableVertexAttribArray(norm_pos_attr);
		}

		//There's a chance that this will also deal with camera coordinates...
		var camera_loc = gl.getUniformLocation(this.program, "camera_pos");
		gl.uniform3fv(camera_loc, camera.pos);

		//Deal with textures
		if (this.texture != null) {
			if (this.texture.texture_type == "CUBE_MAP") {
				//You're getting fancy...
			} else {
				//Standard Texture
				var texture_loc = gl.getUniformLocation(this.program, "texture");
				
				if (texture_loc != -1) {
					gl.activeTexture(gl.TEXTURE0);
					gl.bindTexture(gl.TEXTURE_2D, this.texture.texture);
					gl.uniform1i(texture_loc, 0);

					var tex_pos_attr = gl.getAttribLocation(this.program, "texcoord");

					gl.bindBuffer(gl.ARRAY_BUFFER, this.model.texture_buffer);
					gl.vertexAttribPointer(
						tex_pos_attr,
						2,
						gl.FLOAT,
						gl.FALSE,
						0,
						0
					);
					gl.enableVertexAttribArray(tex_pos_attr);
				}
			}
		}

		//Deal with the possibility of water shaders
		var water_height_loc = gl.getUniformLocation(this.program, "water_height");
		if (water_height_loc != null) {
			gl.uniform4fv(water_height_loc, new Float32Array(water_array));
		}

		//Deal with transformations
		var transform_loc = gl.getUniformLocation(this.program, "transform");
		if (transform_loc != -1) {
			//The shader "must" support transformations to do them!
			gl.uniform3fv(transform_loc, new Float32Array([this.x, this.y, this.z]));
		}
		
		//Deal with scaling
		var scale_loc = gl.getUniformLocation(this.program, "scale");
		if (scale_loc != -1) {
			//The shader "must" support scaling to do them!
			gl.uniform3fv(scale_loc, new Float32Array([
				this.xscale, this.yscale, this.zscale
			]));
		}

		//Deal with rotating
		var rotate_loc = gl.getUniformLocation(this.program, "rotate");
		if (rotate_loc != -1) {
			//The shader "must" support rotating to do them!
			gl.uniform3fv(rotate_loc, new Float32Array([
				this.xrot, this.yrot, this.zrot
			]));
		}

		//Deal with shadows
		var sampler_shadow_loc = gl.getUniformLocation(this.program, "sampler_shadow");
		if (sampler_shadow_loc != null && typeof fboTex !== 'undefined') {
			gl.uniform1i(sampler_shadow_loc, 1);
			gl.activeTexture(gl.TEXTURE1);
			gl.bindTexture(gl.TEXTURE_2D, fboTex);

			//Must also have lMBMatrix and lPMatrix!
			lightPOV.push_matrix_to_shader(
				this.program,
				"lPMatrix",
				"lMVMatrix"
			);
			gl.activeTexture(gl.TEXTURE0);
		}

		//gl.bindBuffer(gl.ARRAY_BUFFER, texture_buffer);
		//gl.vertexAttribPointer(tex_coord_attr, 2, gl.FLOAT, false, 0, 0);
		
		gl.useProgram(this.program);
		gl.drawArrays(gl.TRIANGLES, 0, this.model.vertex_id.length);
	}
}

CN_INSTANCE.prototype.draw_just_triangles = function(program) {
	if (this.model != undefined && this.model.ready == true) {
		//Draw only if the instance has a model

		//Deal with textures
		var ver_pos_attr = gl.getAttribLocation(this.program, "vec_pos");

		gl.bindBuffer(gl.ARRAY_BUFFER, this.model.vertex_buffer);
		gl.vertexAttribPointer(
			ver_pos_attr,
			3,
			gl.FLOAT,
			gl.FALSE,
			0,
			0
		);
		gl.enableVertexAttribArray(ver_pos_attr);
		
		if (program != undefined) {
			//Deal with transformations
			var transform_loc = gl.getUniformLocation(program, "transform");
			if (transform_loc != null) {
				//The shader "must" support transformations to do them!
				gl.uniform3fv(transform_loc, new Float32Array([this.x, this.y, this.z]));
			}
			
			//Deal with scaling
			var scale_loc = gl.getUniformLocation(program, "scale");
			if (scale_loc != null) {
				//The shader "must" support scaling to do them!
				gl.uniform3fv(scale_loc, new Float32Array([
					this.xscale, this.yscale, this.zscale
				]));
			}

			//Deal with rotating
			var rotate_loc = gl.getUniformLocation(program, "rotate");
			if (rotate_loc != null) {
				//The shader "must" support rotating to do them!
				gl.uniform3fv(rotate_loc, new Float32Array([
					this.xrot, this.yrot, this.zrot
				]));
			}
		}

		gl.drawArrays(gl.TRIANGLES, 0, this.model.vertex_id.length);
	}
}
