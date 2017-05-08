precision mediump float;

varying vec2 v_texcoord;
uniform float blur_amount;
uniform vec2 resolution;
uniform sampler2D u_texture;

void main() {
	vec4 sum = vec4(0.0, 0.0, 0.0, 0.0);

	vec2 tc = v_texcoord;

	float blur = blur_amount / resolution.x;

	sum += texture2D(u_texture, vec2(tc.x, tc.y - 4.0 * blur)) * 0.0162162162;
	sum += texture2D(u_texture, vec2(tc.x, tc.y - 3.0 * blur)) * 0.0540540541;
	sum += texture2D(u_texture, vec2(tc.x, tc.y - 2.0 * blur)) * 0.1216216216;
	sum += texture2D(u_texture, vec2(tc.x, tc.y - 1.0 * blur)) * 0.1945945946;

	sum += texture2D(u_texture, vec2(tc.x, tc.y)) * 0.2270270270;

	sum += texture2D(u_texture, vec2(tc.x, tc.y + 1.0 * blur)) * 0.1945945946;
	sum += texture2D(u_texture, vec2(tc.x, tc.y + 2.0 * blur)) * 0.1216216216;
	sum += texture2D(u_texture, vec2(tc.x, tc.y + 3.0 * blur)) * 0.0540540541;
	sum += texture2D(u_texture, vec2(tc.x, tc.y + 4.0 * blur)) * 0.0162162162;
	
	//Level out the colours.
	float yes = 
		((sum.r - 0.70) * 4.4285) +
		((sum.g - 0.70) * 4.4285) +
		((sum.b - 0.70) * 4.4285);
	yes /= 3.0;

	vec4 col = vec4(
		yes, yes, yes,
		0.5
	);
	
	gl_FragColor = col;
}
