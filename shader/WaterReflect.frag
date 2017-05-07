precision mediump float;

uniform vec2 screen_res;
uniform sampler2D u_texture;
varying vec2 v_texcoord;
varying vec4 vector_pos;

void main() {
	//Now we are going to have some REAL fun.
	//Convert the 3D coordinate into a screen coordinate.
	vec4 tmp = vector_pos / vector_pos.w;
	vec2 new_tex = vec2(
		1.0 - (gl_FragCoord.x / screen_res.x),
		gl_FragCoord.y / screen_res.y
	);

	gl_FragColor = vec4(
		texture2D(u_texture, new_tex).xyz, 0.85
	);
	//gl_FragColor = vec4(gl_FragCoord.x / screen_res.x, new_tex.y, 1.0, 1.0);
}
