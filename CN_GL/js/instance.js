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
	this.x = 0;
	this.y = 0;
	this.z = 0;
	this.start_x = 0;
	this.start_y = 0;
	this.start_z = 0;

	//Model Information
	this.model = null;
	
	//Shader Information
	this.program = null;

	//Texture Information
	this.texture = null;
	this.texture_image = null;
	this.texture_path = "";
}

CN_INSTANCE.prototype.init = function(_x, _y, _z) {
	this.x = _x;
	this.y = _y;
	this.z = _z;
	this.start_x = _x;
	this.start_y = _y;
	this.start_z = _z;
}

CN_INSTANCE.prototype.set_model = function(modelOBJ) {
	this.model = modelOBJ;
}

CN_INSTANCE.prototype.set_program = function(programID) {
	this.program = programID;
}

CN_INSTANCE.prototype.set_texture = function(texturePath) {
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
}

CN_INSTANCE.prototype.draw = function() {
	if (this.model != undefined) {
		//Draw only if the instance has a model

		
		//Create vertex buffer
		var vertex_buffer = gl.createBuffer();
		gl.bindBuffer(gl.ARRAY_BUFFER, vertex_buffer);
		gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(this.model.vertex_buffer), gl.STATIC_DRAW);
		
		var ver_pos_attr = gl.getAttribLocation(this.program, "vec_pos");

		gl.bindBuffer(gl.ARRAY_BUFFER, vertex_buffer);
		gl.vertexAttribPointer(
			ver_pos_attr,
			3,
			gl.FLOAT,
			gl.FALSE,
			3 * Float32Array.BYTES_PER_ELEMENT,
			0
		);
		gl.enableVertexAttribArray(ver_pos_attr);

		//gl.bindBuffer(gl.ARRAY_BUFFER, texture_buffer);
		//gl.vertexAttribPointer(tex_coord_attr, 2, gl.FLOAT, false, 0, 0);
		
		gl.useProgram(this.program);
		gl.drawArrays(gl.TRIANGLES, 0, this.model.vertex_id.length);
	}
}
