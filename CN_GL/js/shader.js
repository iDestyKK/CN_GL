/*
 * CN_GL - Shader Assist Functions
 *
 * Description:
 *     Implements functions that help in creating, initialising, or destroying
 *     GLSL shaders. This is mainly to help clean up code that ends up looking
 *     clustered in the init function of the WebGL program.
 * 
 * Author:
 *     Clara Van Nguyen
 */

function cn_gl_get_shader(id) {
	var shaderScript, theSource, currentChild, shader;

	shaderScript = document.getElementById(id);
	if(!shaderScript){
		return null;
	}

	theSource = "";

	currentChild = shaderScript.firstChild;

	while(currentChild){
		if(currentChild.nodeType == currentChild.TEXT_NODE){
			theSource += currentChild.textContent;
		}
		currentChild = currentChild.nextSibling;
	}

	if(shaderScript.type == "x-shader/x-fragment"){
		shader = gl.createShader(gl.FRAGMENT_SHADER);
	} else if(shaderScript.type == "x-shader/x-vertex"){
		shader = gl.createShader(gl.VERTEX_SHADER);
	} else {
		return null;
	}

	gl.shaderSource(shader, theSource);

	gl.compileShader(shader);

	if(!gl.getShaderParameter(shader, gl.COMPILE_STATUS)){
		console.log("error compiling shaders -- " + gl.getShaderInfoLog(shader));
		return null;
	}

	return shader;
}

function cn_gl_create_shader_program(FRAG_SHADER, VERT_SHADER) {
	var PROGRAM = gl.createProgram();
	gl.attachShader(PROGRAM, FRAG_SHADER);
	gl.attachShader(PROGRAM, VERT_SHADER);
	gl.linkProgram(PROGRAM);

	if (!gl.getProgramParameter(PROGRAM, gl.LINK_STATUS)) {
		console.log("Unable to init shader program");
	}

	return PROGRAM;
}
