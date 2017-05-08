precision mediump float;	
attribute vec3 vec_pos; //vertex position
varying vec3 vector_pos;
varying vec3 c_pos;
varying vec4 norm;
varying mat4 matrix_MV;
varying mat4 matrix_P;
varying vec3 v_translate;

uniform mat4 lMVMatrix;
uniform mat4 lPMatrix;

attribute vec3 normal;
uniform vec3 camera_pos;
uniform mat4 uMVMatrix;//modelviewmatrix
uniform mat4 uPMatrix;//projectionmatrix

uniform vec3 transform;
uniform vec3 scale;
uniform vec3 rotate;

varying vec3 v_light_pos;

void main(void) {
	norm = vec4(normal, 1.0);
	c_pos = camera_pos;
	v_translate = transform;

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
	norm = vec4(normal, 1.0) * rotX * rotY * rotZ;

	//Transform if possible
	vec_real += transform;

	//Pass on to fragment shader
	vector_pos = vec_real;

	vec4 light_position = lMVMatrix * vec4(vec_real, 1.0);
	light_position = lPMatrix * light_position;
	vec3 light_position_dnc = light_position.xyz / light_position.w;
	v_light_pos = vec3(0.5, 0.5, 0.5) + light_position_dnc * 0.5;

	//Yes
	matrix_MV = uMVMatrix;
	matrix_P  = uPMatrix;

	gl_Position = uPMatrix * uMVMatrix * vec4(vec_real, 1.0);
}
