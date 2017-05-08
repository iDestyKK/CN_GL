precision mediump float;

//uniform vec3 vPos;
varying vec3 vector_pos;

varying vec4 norm;

//uniform vec3 normal;
varying vec3 c_pos;
varying mat4 matrix_MV;
varying mat4 matrix_P;
uniform mat4 inverseMV;
varying lowp vec4 vert_colour;
varying vec3 v_translate;
uniform vec4 water_height;

uniform sampler2D sampler_shadow;
varying vec3 v_light_pos;

void main(void) {
	//Water checking.
	if (water_height.w == 1.0 && vector_pos.z < water_height.z)
		discard;

	//Polished Gold
	const vec3 ambient   = vec3(0.329412, 0.223529, 0.027451);
	const vec3 diffuse   = vec3(0.780392, 0.568627, 0.113725);
	const vec3 specular  = vec3(0.992157, 0.941176, 0.807843);
	float shiny          = 27.8974;
	
	//Let's make a light as well
	const vec4 light_amb = vec4(1.0, 1.0, 1.0, 1.0);
	const vec4 light_dif = vec4(1.0, 1.0, 1.0, 1.0);
	const vec4 light_spe = vec4(1.0, 1.0, 1.0, 1.0);
	
	//Do Phong Calculations
	vec3 light_pos = vec3(c_pos);
	vec3 modelView_vertex = vec3(matrix_MV * vec4(vector_pos, 1.0));
	vec3 modelView_normal = normalize(vec3(inverseMV * vec4(vec3(norm), 0.0)));
	
	vec3 L = normalize(v_light_pos - modelView_vertex);
	vec3 E = normalize(-modelView_vertex);
	vec3 R = normalize(-reflect(L, modelView_normal));

	vec3 total_ambient  = ambient;
	vec3 total_diffuse  = diffuse  * max(dot(modelView_normal, L), 0.0);
	vec3 total_specular = specular * pow(max(dot(R, E), 0.0), shiny);
	total_specular = clamp(total_specular, 0.0, 1.0);

	//Now do shadows
	vec2 uv_shadow_map = v_light_pos.xy;
	vec4 shadow_map_color = texture2D(sampler_shadow, uv_shadow_map);
	float z_shadow_map = shadow_map_color.r;
	float shadow_coeff = 1.0 - (smoothstep(0.0021, 0.003, v_light_pos.z - z_shadow_map) * 0.75);

	if (shadow_coeff != 1.0) {
		vec4 tex_col = vec4(total_ambient + total_diffuse, 1.0);
		vec4 tmp_col = vec4(
			tex_col.r * shadow_coeff,
			tex_col.g * shadow_coeff,
			tex_col.b * shadow_coeff,
			tex_col.a
		);
		gl_FragColor = tmp_col;
	}
	else
		gl_FragColor = vec4(total_ambient + total_diffuse + total_specular, 1.0);
}
