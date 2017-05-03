attribute vec3 vPos; //vertex position
attribute vec3 bary; //barycentric
attribute vec3 normal;
attribute vec2 texcoord;

varying vec3 vector_pos;
varying vec3 interpBary;
varying vec2 v_texcoord;
varying vec4 norm;
varying vec4 vert_colour;
varying vec3 cpos;

varying mat4 matrix_MV;
varying mat4 matrix_P;

uniform mat4 uMVMatrix;//modelviewmatrix
uniform mat4 uPMatrix;//projectionmatrix
uniform vec3 camera_pos;

void main(void) {
	vector_pos = vPos;
	v_texcoord = texcoord;
	interpBary = bary;
	norm = vec4(normal, 0.0);
	cpos = camera_pos;

	//Yes
	matrix_MV = uMVMatrix;
	matrix_P  = uPMatrix;

	gl_Position = uPMatrix * uMVMatrix * vec4(vPos, 1.0);
}
