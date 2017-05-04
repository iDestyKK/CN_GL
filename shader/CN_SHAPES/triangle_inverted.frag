precision mediump float;

varying vec3 frag_colour;

void main() {
	gl_FragColor = vec4(1.0 - frag_colour.r, 1.0 - frag_colour.g, 1.0 - frag_colour.b, 1.0);
}
