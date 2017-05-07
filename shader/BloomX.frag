precision mediump float;

varying vec2 v_texcoord;
uniform float blur_amount;
uniform vec2 resolution;
uniform sampler2D u_texture;

void main() {
	vec4 sum = vec4(0.0, 0.0, 0.0, 0.0);

	vec2 tc = v_texcoord;

	float blur = blur_amount / resolution.x;

	float hstep = 1.0;
	float vstep = 0.0;

	sum += texture2D(u_texture, vec2(tc.x - 4.0 * blur * hstep, tc.y)) * 0.0162162162;
	sum += texture2D(u_texture, vec2(tc.x - 3.0 * blur * hstep, tc.y)) * 0.0540540541;
	sum += texture2D(u_texture, vec2(tc.x - 2.0 * blur * hstep, tc.y)) * 0.1216216216;
	sum += texture2D(u_texture, vec2(tc.x - 1.0 * blur * hstep, tc.y)) * 0.1945945946;

	sum += texture2D(u_texture, vec2(tc.x, tc.y)) * 0.2270270270;

	sum += texture2D(u_texture, vec2(tc.x + 1.0 * blur * hstep, tc.y)) * 0.1945945946;
	sum += texture2D(u_texture, vec2(tc.x + 2.0 * blur * hstep, tc.y)) * 0.1216216216;
	sum += texture2D(u_texture, vec2(tc.x + 3.0 * blur * hstep, tc.y)) * 0.0540540541;
	sum += texture2D(u_texture, vec2(tc.x + 4.0 * blur * hstep, tc.y)) * 0.0162162162;

	gl_FragColor = vec4(sum.rgb, 1.0);
}
