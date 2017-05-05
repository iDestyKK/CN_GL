precision mediump float;

attribute vec3 vec_pos;
attribute vec2 texcoord;

uniform mat4 uMVMatrix;
uniform mat4 uPMatrix;
uniform vec3 transform;

varying vec2 v_texcoord;

void main() {
	v_texcoord = texcoord;
	gl_Position = uPMatrix * uMVMatrix * vec4((vec_pos + transform), 1.0);
}
