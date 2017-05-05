/*
 * CN_GL - Main Javascript File
 *
 * Description:
 *     Implements JS functions that are needed to utilise CN_GL globally. These
 *     functions may include things such as initialising canvas, starting GL
 *     mode, dealing with page information, etc.
 * 
 * Author:
 *     Clara Van Nguyen
 */

function cn_gl_init_gl(canvas_id, init_function) {
	var canvas = document.getElementById(canvas_id);

	//Set to webgl for browsers that officially support it.
	gl = canvas.getContext("webgl");

	if (!gl) {
		//Stubborn web browser. Resort to experimental.
		console.log("WebGL not supported. Resorting to experimental-webgl");
		gl = canvas.getContext("experimental-webgl");
	}

	if (!gl) {
		//Get a better browser please. :)
		console.log("Your browser doesn't support webgl or experimental-webgl.");
	}

	//Set the viewport.
	gl.viewport(0, 0, canvas.width, canvas.height);

	//Execute custom initialisation function
	if (init_function) {
		init_function(); //Yes
	}
}
