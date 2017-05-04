attribute vec3 vec_pos;
attribute vec2 texcoord;

varying vec2 v_texcoord;

//uniform mat4 uMVMatrix;
//uniform mat4 uPMatrix;

void main(void) {
	v_texcoord = texcoord;
	gl_Position = vec4(vec_pos, 1.0);
}
