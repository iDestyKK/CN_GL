precision mediump float;

//uniform vec3 vPos;
varying vec3 vector_pos;

varying vec4 norm;

//uniform vec3 normal;
varying vec3 c_pos;
varying mat4 matrix_MV;
varying mat4 matrix_P;
varying lowp vec4 vert_colour;
varying vec3 v_translate;

void main(void) {
	//Polished Gold
	const vec3 ambient   = vec3(0.329412, 0.223529, 0.027451);
	const vec3 diffuse   = vec3(0.780392, 0.568627, 0.113725);
	const vec3 specular  = vec3(0.992157, 0.941176, 0.807843);
	float shiny          = 27.8974;
	
	//Let's make a light as well
	const vec4 light_amb = vec4(1.0, 1.0, 1.0, 1.0);
	const vec4 light_dif = vec4(1.0, 1.0, 1.0, 1.0);
	const vec4 light_spe = vec4(1.0, 1.0, 1.0, 1.0);

	vec4 light_pos = vec4(c_pos, 1.0);
	vec4 modelView_vertex = matrix_MV * vec4(vector_pos, 1.0);
	vec4 modelView_normal = normalize(matrix_MV * vec4(vec3(norm), 0.0));

	vec4 view_angle = normalize(light_pos - modelView_vertex); //normalize(vec4(uMVMatrix * vec4(vPos, 1.0)));

	vec4 light_vertex = normalize(vec4(light_pos - modelView_vertex));
	vec4 reflect_vec  = normalize(reflect(-light_vertex, modelView_normal));

	vec3 total_ambient  = ambient;//	* light_amb;
	vec3 total_diffuse  = diffuse * max(dot(modelView_normal, light_vertex), 0.0);
	vec3 total_specular = specular * pow(max(dot(reflect_vec, view_angle), 0.0), shiny);
	 
	gl_FragColor = vec4(total_ambient + total_diffuse + total_specular, 1.0); //+ total_specular;
}
