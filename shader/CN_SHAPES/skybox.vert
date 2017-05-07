precision mediump float;

attribute vec3 vec_pos;
attribute vec2 texcoord;

uniform mat4 uMVMatrix;
uniform mat4 uPMatrix;
uniform vec3 transform;
uniform vec3 scale;
uniform vec3 rotate;

varying vec2 v_texcoord;

void main() {
	//Scale if possible
	vec3 vec_real = vec3(
		vec_pos.x * scale.x,
		vec_pos.y * scale.y,
		vec_pos.z * scale.z
	);

	//Rotate if possible
	float c, s; //No pun intended (CS)
	c = cos(rotate.x);
	s = sin(rotate.x);
	mat4 rotX = mat4(
		1.0, 0.0, 0.0, 0.0,
		0.0, c  , -s , 0.0,
		0.0, s  , c  , 0.0,
		0.0, 0.0, 0.0, 1.0
	);
	c = cos(rotate.y);
	s = sin(rotate.y);
	mat4 rotY = mat4(
		c  , 0.0, s  , 0.0,
		0.0, 1.0, 0.0, 0.0,
		-s , 0.0, c  , 0.0,
		0.0, 0.0, 0.0, 1.0
	);
	c = cos(rotate.z);
	s = sin(rotate.z);
	mat4 rotZ = mat4(
		c  , -s , 0.0, 0.0,
		s  , c  , 0.0, 0.0,
		0.0, 0.0, 1.0, 0.0,
		0.0, 0.0, 0.0, 1.0
	);
	vec_real = vec3(vec4(vec_real, 1.0) * rotX * rotY * rotZ);

	//Transform if possible
	vec_real += transform;
	v_texcoord = texcoord;

	gl_Position = uPMatrix * uMVMatrix * vec4(vec_real, 1.0);
}
