precision mediump float;

attribute vec3 vec_pos;
attribute vec2 texcoord;

uniform mat4 uMVMatrix;
uniform mat4 uPMatrix;
uniform vec3 transform;
uniform vec3 scale;

varying vec2 v_texcoord;

void main() {
	//Scale if possible
	vec3 vec_real = vec3(
		vec_pos.x * scale.x,
		vec_pos.y * scale.y,
		vec_pos.z * scale.z
	);

	//Transform if possible
	vec_real += transform;
	v_texcoord = texcoord;

	gl_Position = uPMatrix * uMVMatrix * vec4(vec_real, 1.0);
}
