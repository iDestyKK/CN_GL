precision mediump float;

varying vec3 frag_colour;

void main() {
	gl_FragColor = vec4(frag_colour, 1.0);
}
