precision mediump float;

uniform sampler2D u_texture;
uniform sampler2D sampler_shadow;
varying vec2 v_texcoord;
varying vec3 v_light_pos;

void main() {
	vec2 uv_shadow_map = v_light_pos.xy;
	vec4 shadow_map_color = texture2D(sampler_shadow, uv_shadow_map);
	float z_shadow_map = shadow_map_color.r;
	float shadow_coeff = 1.0 - (smoothstep(0.002, 0.003, v_light_pos.z - z_shadow_map) * 0.75);
	
	if (shadow_coeff != 1.0) {
		//There is a shadow on this
		vec4 tex_col = texture2D(u_texture, v_texcoord);
		vec4 tmp_col = vec4(
			tex_col.r * shadow_coeff,
			tex_col.g * shadow_coeff,
			tex_col.b * shadow_coeff,
			tex_col.a
		);
		gl_FragColor = tmp_col;
	}
	else
		//No shadow. Draw the texture normally
		gl_FragColor = texture2D(u_texture, v_texcoord);
}
