/*
 * CN_GL - Shapes
 *
 * Description:
 *     Provides functions to draw shapes to the screen.
 * 
 * Author:
 *     Clara Van Nguyen
 */

function make_colour_rgb(_r, _g, _b) {
	return {
		r: _r / 255,
		g: _g / 255,
		b: _b / 255
	};
}

function make_color_rgb(_r, _g, _b) {
	//For you USA people
	return make_colour_rgb(_r, _g, _b);
}

function draw_triangle(x1, y1, z1, x2, y2, z2, x3, y3, z3, col1, col2, col3) {
	//Create Buffers
	var vertices_buffer = [
		x1, y1, z1,
		x2, y2, z2,
		x3, y3, z3
	];

	var colour_buffer = [
		col1.r, col1.g, col1.b,
		col2.r, col2.g, col2.b,
		col3.r, col3.g, col3.b
	];
	
	//Pass the buffers into the GL shaders
	var triangle_buffer_object = gl.createBuffer();
	gl.bindBuffer(gl.ARRAY_BUFFER, triangle_buffer_object);
	gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(vertices_buffer), gl.STATIC_DRAW);

	var position_attrib_location = gl.getAttribLocation(CN_TRIANGLE_SHADER_PROGRAM, "vec_pos");
	gl.vertexAttribPointer(
		position_attrib_location,
		3,
		gl.FLOAT,
		gl.FALSE,
		3 * Float32Array.BYTES_PER_ELEMENT,
		0
	);
	gl.enableVertexAttribArray(position_attrib_location);
	
	var colour_buffer_object = gl.createBuffer();
	gl.bindBuffer(gl.ARRAY_BUFFER, colour_buffer_object);
	gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(colour_buffer), gl.STATIC_DRAW);
	var colour_attrib_location = gl.getAttribLocation(CN_TRIANGLE_SHADER_PROGRAM, "vec_col");
	gl.vertexAttribPointer(
		colour_attrib_location,
		3,
		gl.FLOAT,
		gl.FALSE,
		3 * Float32Array.BYTES_PER_ELEMENT,
		0
	);
	gl.enableVertexAttribArray(colour_attrib_location);

	gl.useProgram(CN_TRIANGLE_SHADER_PROGRAM);
	gl.drawArrays(gl.TRIANGLES, 0, 3);
}

//We will also allow the user to make a class to do it for them
function CN_TRIANGLE() {
	this.p1 = null;
	this.p2 = null;
	this.p3 = null;
	this.col1 = null;
	this.col2 = null;
	this.col3 = null;
}

CN_TRIANGLE.prototype.init = function(x1, y1, z1, x2, y2, z2, x3, y3, z3, _col1, _col2, _col3) {
	this.p1 = [x1, y1, z1];
	this.p2 = [x2, y2, z2];
	this.p3 = [x3, y3, z3];
	this.col1 = _col1;
	this.col2 = _col2;
	this.col3 = _col3;
}

CN_TRIANGLE.prototype.draw = function() {
	draw_triangle(
		this.p1[0], this.p1[1], this.p1[2],
		this.p2[0], this.p2[1], this.p2[2],
		this.p3[0], this.p3[1], this.p3[2],
		this.col1, this.col2, this.col3
	);
}

function cn_gl_ortho_draw_texture(tex, x, y, width, height) {
	gl.bindTexture(gl.TEXTURE_2D, tex);

	gl.useProgram(program_list["CN_ORTHO_TEXTURE_SHADER_PROGRAM"]);
	
	var x1 = x, y1 = y, x2 = x + width, y2 = y + height;
	//Create Buffers
	var vertices_buffer = [
		x1, y1,
		x2, y1,
		x2, y2,

		x2, y2,
		x1, y2,
		x1, y1
	];

	var texture_buffer = [
		0, 0,
		1, 0,
		1, 1,

		1, 1,
		0, 1,
		0, 0
	];
	
	//Pass the buffers into the GL shaders
	var triangle_buffer_object = gl.createBuffer();
	gl.bindBuffer(gl.ARRAY_BUFFER, triangle_buffer_object);
	gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(vertices_buffer), gl.STATIC_DRAW);

	var position_attrib_location = gl.getAttribLocation(
		program_list["CN_ORTHO_TEXTURE_SHADER_PROGRAM"],
		"position"
	);
	gl.vertexAttribPointer(
		position_attrib_location,
		2,
		gl.FLOAT,
		gl.FALSE,
		2 * Float32Array.BYTES_PER_ELEMENT,
		0
	);
	gl.enableVertexAttribArray(position_attrib_location);
	
	var tex_buffer_object = gl.createBuffer();
	gl.bindBuffer(gl.ARRAY_BUFFER, tex_buffer_object);
	gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(texture_buffer), gl.STATIC_DRAW);
	var tex_attrib_location = gl.getAttribLocation(
		program_list["CN_ORTHO_TEXTURE_SHADER_PROGRAM"],
		"texcoord"
	);
	gl.vertexAttribPointer(
		tex_attrib_location,
		2,
		gl.FLOAT,
		gl.FALSE,
		2 * Float32Array.BYTES_PER_ELEMENT,
		0
	);
	gl.enableVertexAttribArray(tex_attrib_location);

	gl.drawArrays(gl.TRIANGLES, 0, 6);
}

function cn_gl_ortho_draw_texture_loose(tex, x, y, width, height) {
	gl.bindTexture(gl.TEXTURE_2D, tex);

	var x1 = x, y1 = y, x2 = x + width, y2 = y + height;
	//Create Buffers
	var vertices_buffer = [
		x1, y1,
		x2, y1,
		x2, y2,

		x2, y2,
		x1, y2,
		x1, y1
	];

	var texture_buffer = [
		0, 0,
		1, 0,
		1, 1,

		1, 1,
		0, 1,
		0, 0
	];
	
	//Pass the buffers into the GL shaders
	var triangle_buffer_object = gl.createBuffer();
	gl.bindBuffer(gl.ARRAY_BUFFER, triangle_buffer_object);
	gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(vertices_buffer), gl.STATIC_DRAW);

	var position_attrib_location = gl.getAttribLocation(
		program_list["CN_ORTHO_TEXTURE_SHADER_PROGRAM"],
		"position"
	);
	gl.vertexAttribPointer(
		position_attrib_location,
		2,
		gl.FLOAT,
		gl.FALSE,
		2 * Float32Array.BYTES_PER_ELEMENT,
		0
	);
	gl.enableVertexAttribArray(position_attrib_location);
	
	var tex_buffer_object = gl.createBuffer();
	gl.bindBuffer(gl.ARRAY_BUFFER, tex_buffer_object);
	gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(texture_buffer), gl.STATIC_DRAW);
	var tex_attrib_location = gl.getAttribLocation(
		program_list["CN_ORTHO_TEXTURE_SHADER_PROGRAM"],
		"texcoord"
	);
	gl.vertexAttribPointer(
		tex_attrib_location,
		2,
		gl.FLOAT,
		gl.FALSE,
		2 * Float32Array.BYTES_PER_ELEMENT,
		0
	);
	gl.enableVertexAttribArray(tex_attrib_location);

	gl.drawArrays(gl.TRIANGLES, 0, 6);
}
