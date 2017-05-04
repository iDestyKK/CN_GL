precision mediump float;

attribute vec3 vec_pos;
attribute vec3 vec_col;
varying vec3 frag_colour;

uniform mat4 uMVMatrix;
uniform mat4 uPMatrix;

void main() {
	frag_colour = vec_col;
	gl_Position = uPMatrix * uMVMatrix * vec4(vec_pos, 1.0);
}
