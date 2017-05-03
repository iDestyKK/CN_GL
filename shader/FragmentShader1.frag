precision mediump float;
varying vec2 v_texcoord;
varying vec3 interpBary;
varying vec4 vert_colour;
varying vec3 vector_pos;
varying vec4 norm;
varying vec3 cpos;

varying mat4 matrix_MV;
varying mat4 matrix_P;

uniform samplerCube cube_texture;

void main(void){
	vec4 light_pos = vec4(cpos, 1.0);
	vec4 modelView_vertex = matrix_MV * vec4(vector_pos, 1.0);
	vec4 modelView_normal = matrix_MV * norm;
	vec4 view_angle = normalize(light_pos - modelView_vertex);
	vec4 light_vertex = normalize(vec4(light_pos - modelView_vertex));
	vec4 reflect_vec  = normalize(reflect(-light_vertex, modelView_normal));

	gl_FragColor = vec4(textureCube(cube_texture, vec3(reflect_vec) * mat3(matrix_MV)));
}
