/*
 * CN_GL - Camera
 *
 * Description:
 *     Implements functions to handle camera drawing. The "camera" is an object
 *     that has a position and a lookat vector, as well as an "up" vector. It
 *     also has to do some matrix math in order to get projections right. This
 *     library aims to handle all of that for you.
 * 
 * Author:
 *     Clara Van Nguyen
 */

function CN_CAMERA() {
	this.pos    = [0, 0, 0];
	this.lookat = [0, 0, 0];
	this.up     = [0, 1, 0];

	this.perspective_matrix = null;
	this.look_matrix        = null;
	this.lookat_matrix      = null;
}

CN_CAMERA.prototype.push_matrix_to_shader = function(program, pname, mvname) {
	var pUniform = gl.getUniformLocation(program, pname);
	gl.uniformMatrix4fv(
		pUniform,
		false,
		new Float32Array(this.perspective_matrix.flatten())
	);

	var mvUniform = gl.getUniformLocation(program, mvname);
	gl.uniformMatrix4fv(
		mvUniform,
		false,
		new Float32Array(this.lookat_matrix.flatten())
	);

	var mvInverseUniform = gl.getUniformLocation(program, "inverseMV");
	if (mvInverseUniform != null) {
		gl.uniformMatrix4fv(
			mvInverseUniform,
			false,
			new Float32Array(this.lookat_matrix.inverse().transpose().flatten())
		);
	}
}

CN_CAMERA.prototype.draw = function() {
	
}

CN_CAMERA.prototype.set_projection_ext = function(xf, yf, zf, xt, yt, zt, xup, yup, zup, fov, aspect, znear, zfar) {
	//Allows configuring the camera with far greater controls.
	this.perspective_matrix = cn_gl_make_perspective(fov, aspect, znear, zfar);
	this.look_matrix = cn_gl_make_look_at(xf, yf, zf, xt, yt, zt, xup, yup, zup);
	
	//Set initial camera lookat matrix
	this.lookat_matrix = Matrix.I(4);

	//Multiply by look matrix
	this.lookat_matrix = this.lookat_matrix.x(this.look_matrix);

	//Set Camera properties
	this.pos    = [xf , yf , zf ];
	this.lookat = [xt , yt , zt ];
	this.up     = [xup, yup, zup];
}

CN_CAMERA.prototype.set_projection = function(xf, yf, zf, xt, yt, zt, xup, yup, zup) {
	//Simpler function to set camera up.
	this.set_projection_ext(
		xf, yf, zf,
		xt, yt, zt,
		xup, yup, zup,
		45,
		gl.canvas.clientWidth / gl.canvas.clientHeight,
		0.1,
		16386.0
	);
}

function cn_gl_make_projection_ortho(l, r, b, t, znear, zfar) {
	var tx = -(r + l) / (r - l),
	    ty = -(t + b) / (t - b),
	    tz = -(zfar + znear) / (zfar - znear);

	return $M([
		[2 / (r - l), 0          , 0                  , tx],
		[0          , 2 / (t - b), 0                  , ty],
		[0          , 0          , -2 / (zfar - znear), tz],
		[0          , 0          , 0                  , 1 ]
	]);
}

//Helper functions
Matrix.prototype.flatten = function() {
	var result = [];
	if (this.elements.length == 0)
		return [];

	for (var j = 0; j < this.elements[0].length; j++)
		for (var i = 0; i < this.elements.length; i++)
			result.push(this.elements[i][j]);
	return result;
}

function cn_gl_make_look_at(xf, yf, zf, xt, yt, zt, xup, yup, zup) {
	var eye    = $V([xf , yf , zf ]);
	var centre = $V([xt , yt , zt ]);
	var up     = $V([xup, yup, zup]);

	var z = eye.subtract(centre).toUnitVector();
	var x = up.cross(z).toUnitVector();
	var y = z.cross(x).toUnitVector();

	var m = $M([
		[x.e(1), x.e(2), x.e(3), 0],
		[y.e(1), y.e(2), y.e(3), 0],
		[z.e(1), z.e(2), z.e(3), 0],
		[0     , 0     , 0     , 1]
	]);

	var t = $M([
		[1, 0, 0, -xf],
		[0, 1, 0, -yf],
		[0, 0, 1, -zf],
		[0, 0, 0,   1]
	]);

	return m.x(t);
}

function cn_gl_make_perspective(fov, aspect, znear, zfar) {
	var ymax = znear * Math.tan(fov * Math.PI / 360.0),
	    ymin = -ymax,
	    xmin = ymin * aspect,
	    xmax = ymax * aspect;

	return cn_gl_make_frustum(xmin, xmax, ymin, ymax, znear, zfar);
}

function cn_gl_make_frustum(left, right, down, up, znear, zfar) {
	var X = 2 * znear / (right - left),
	    Y = 2 * znear / (up - down),
	    A = (right + left) / (right - left),
	    B = (up + down) / (up - down),
	    C = -(zfar + znear) / (zfar - znear),
	    D = -2 * zfar * znear / (zfar - znear);

	return $M([
		[ X,  0,  A,  0],
		[ 0,  Y,  B,  0],
		[ 0,  0,  C,  D],
		[ 0,  0, -1,  0]
	]);
}
