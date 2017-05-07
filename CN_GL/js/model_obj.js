/*
 * CN_GL - Model Object
 *
 * Description:
 *     Initialises an object type to hold information about a 3D model.
 *     This makes it easier to manage 3D models later on in CN_GL.
 * 
 * Author:
 *     Clara Van Nguyen
 */

//Constructor
function CN_MODEL(filepath) {
	//Generic information
	this.model_path = "";
	this.ready = false;

	//Stores all information about the model
	this.vertex               = Array();
	this.normal               = Array();
	this.texture              = Array();
	
	//Actual information for drawing the model (Face information)
	this.vertex_id            = Array();
	this.normal_id            = Array();
	this.texture_id           = Array();

	//Extra data
	this.tri_normal           = Array();
	this.ver_normal           = Array();

	//Buffers
	this.vertex_buffer        = null;
	this.vertex_normal_buffer = null;
	this.texture_buffer       = null;

	if (filepath != undefined) {
		//Load information from an OBJ file and put it in the model
		this.model_path = filepath;
		var obj = this;
		$.ajax({
			url    : filepath,
			async  : false,
			success: function (data) {
				parse_obj(obj, data);
			}
		});
	}
}

CN_MODEL.prototype.load_from_obj = function(filepath) {
	//Load information from an OBJ file and put it in the model
	this.model_path = filepath;
	var obj = this;
	$.ajax({
		url    : filepath,
		async  : false,
		success: function (data) {
			parse_obj(obj, data);
		}
	});

	//Disregard the fact it's async. We proceed after it loads.
	//...and cause an infinite loop while we're at it. Ugh.
	//while (!this.ready) {}
}

function parse_obj(obj, data) {
	/*
	 * OBJ Importer based on class driver code
	 *
	 * Assumes that CN_MODEL's "load_from_obj" function worked.
	 * DO NOT call this function yourself.
	 */
	
	var lines = data.split('\n');
	for (var i = 0; i < lines.length; i++) {
		//Remove double spaces
		lines[i] = lines[i].replace(/\s{2,}/g, " ");

		if (lines[i].startsWith('v ')) {
			//This is a vertex point
			var line = lines[i].slice(2).split(" ");
			obj.vertex.push(line[0], line[1], line[2]);
		}
		else if (lines[i].startsWith('vn')) {
			//This is a vertex normal
			var line = lines[i].slice(3).split(" ");
			obj.normal.push(line[0], line[1], line[2]);
		}
		else if (lines[i].startsWith('vt')) {
			//This is a vertex texture
			var line = lines[i].slice(3).split(" ");
			obj.texture.push(line[0], line[1]);
		}
		else if (lines[i].startsWith('f ')) {
			//This is a face
			var line = lines[i].slice(2).split(" ");
			for (var j = 1; j <= line.length - 2; j++) {
				//Push vertex indices
				obj.vertex_id.push(
					line[0    ].split('/')[0] - 1,
					line[j    ].split('/')[0] - 1,
					line[j + 1].split('/')[0] - 1
				);

				//Push texture indices
				obj.texture_id.push(
					line[0    ].split('/')[1] - 1,
					line[j    ].split('/')[1] - 1,
					line[j + 1].split('/')[1] - 1
				);

				//Push normal indices
				obj.normal_id.push(
					line[0    ].split('/')[2] - 1,
					line[j    ].split('/')[2] - 1,
					line[j + 1].split('/')[2] - 1
				);
			}
		}
	}

	//Post-Processing Effects
	
	//Clear out this array since it probably contains garbage (a lot of "NaN"s)
	if (obj.normal.length == 0)
		normal_id = Array();
	temp_ver_normal = Array();

	//Now for the REAL deal...
	for (var i = 0; i < obj.vertex.length; i++) {
		temp_ver_normal[i] = { x: 0, y: 0, z: 0 };
		obj.ver_normal [i] = { x: 0, y: 0, z: 0 };
	}

	for (var i = 0; i < obj.vertex_id.length / 3; i++) {
		//if (obj.normal.length == 0) {
			obj.tri_normal[i] = cn_gl_calculate_normal(
				obj,
				obj.vertex_id[ i * 3     ],
				obj.vertex_id[(i * 3) + 1],
				obj.vertex_id[(i * 3) + 2]
			);
		//}
		/*else {
			obj.tri_normal[i] = {
				x: obj.normal_id[ i * 3     ],
				y: obj.normal_id[(i * 3) + 1],
				z: obj.normal_id[(i * 3) + 2]
			};
		}*/

		for (var j = 0; j < 3; j++) {
			var k = (i * 3) + j;
			temp_ver_normal[obj.vertex_id[k]].x += obj.tri_normal[i].x;
			temp_ver_normal[obj.vertex_id[k]].y += obj.tri_normal[i].y;
			temp_ver_normal[obj.vertex_id[k]].z += obj.tri_normal[i].z;
		}
	}

	//Now put every vertex in its respective spot.
	for (var i = 0; i < obj.vertex_id.length; i++) {
		obj.ver_normal[i] = temp_ver_normal[obj.vertex_id[i]];
	}

	//Normalise every vertex
	for (var i = 0; i < obj.ver_normal.length; i++) {
		var m = Math.sqrt(
			Math.pow(obj.ver_normal[i].x, 2) +
			Math.pow(obj.ver_normal[i].y, 2) +
			Math.pow(obj.ver_normal[i].z, 2)
		);
		obj.ver_normal[i].x /= m;
		obj.ver_normal[i].y /= m;
		obj.ver_normal[i].z /= m;
	}
	
	//Create the vertex buffer
	var vert_buf = Array();
	obj.vertex_buffer = gl.createBuffer();
	for (var i = 0; i < obj.vertex_id.length; i++) {
		vert_buf.push(
			parseFloat(obj.vertex[(obj.vertex_id[i] * 3)    ]),
			parseFloat(obj.vertex[(obj.vertex_id[i] * 3) + 1]),
			parseFloat(obj.vertex[(obj.vertex_id[i] * 3) + 2])
		);
	}
	gl.bindBuffer(gl.ARRAY_BUFFER, obj.vertex_buffer);
	gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(vert_buf), gl.STATIC_DRAW);

	//Create the vertex normal buffer
	var norm_buf = Array();
	obj.vertex_normal_buffer = gl.createBuffer();
	for (var i = 0; i < obj.ver_normal.length; i++) {
		norm_buf.push(
			obj.ver_normal[i].x,
			obj.ver_normal[i].y,
			obj.ver_normal[i].z
		);
	}
	gl.bindBuffer(gl.ARRAY_BUFFER, obj.vertex_normal_buffer);
	gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(norm_buf), gl.STATIC_DRAW);

	//Create the texture buffer.
	var tex_buf = Array();
	obj.texture_buffer = gl.createBuffer();
	for (var i = 0; i < obj.texture_id.length; i++) {
		tex_buf.push(
			obj.texture[ obj.texture_id[i] * 2     ],
			obj.texture[(obj.texture_id[i] * 2) + 1]
		);
	}
	gl.bindBuffer(gl.ARRAY_BUFFER, obj.texture_buffer);
	gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(tex_buf), gl.STATIC_DRAW);

	
	//This model is ready to be displayed.
	obj.ready = true;
}

function cn_gl_calculate_normal(modelID, a, b, c) {
	var va = {
		x: modelID.vertex[ b * 3     ] - modelID.vertex[ a * 3     ],
		y: modelID.vertex[(b * 3) + 1] - modelID.vertex[(a * 3) + 1],
		z: modelID.vertex[(b * 3) + 2] - modelID.vertex[(a * 3) + 2]
	}

	var vb = {
		x: modelID.vertex[ c * 3     ] - modelID.vertex[ a * 3     ],
		y: modelID.vertex[(c * 3) + 1] - modelID.vertex[(a * 3) + 1],
		z: modelID.vertex[(c * 3) + 2] - modelID.vertex[(a * 3) + 2]
	}

	var r = {
		x: (va.y * vb.z) - (vb.y * va.z),
		y: (va.z * vb.x) - (vb.z * va.x),
		z: (va.x * vb.y) - (vb.x * va.y)
	}

	//Normalise
	var m = Math.sqrt(Math.pow(r.x, 2) + Math.pow(r.y, 2) + Math.pow(r.z, 2));

	r.x /= m;
	r.y /= m;
	r.z /= m;

	return r;
}
