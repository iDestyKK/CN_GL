/*
 * CN_GL - Instances (Objects)
 *
 * Description:
 *     In GML, all instances are handled as objects. Because we plan to make
 *     this engine work in a similar way, all models must be drawn or handled
 *     by means of objects. Each object will have a create event, step event,
 *     and draw event handling it. This makes it easier to draw copies of
 *     models since we only have to load them once.
 * 
 * Author:
 *     Clara Van Nguyen
 */

function INSTANCE() {
	this.x = 0;
	this.y = 0;
	this.z = 0;
	this.start_x = 0;
	this.start_y = 0;
	this.start_z = 0;

	this.model_ID = -1;
}

INSTANCE.prototype.init = function(_x, _y, _z) {
	this.x = _x;
	this.y = _y;
	this.z = _z;
	this.start_x = _x;
	this.start_y = _y;
	this.start_z = _z;
}

INSTANCE.prototype.set_model = function(modelOBJ) {
	this.model_ID = modelOBJ;
}
