function doNormals(mesh) {
	mesh.model.tri_normal  = [];
	mesh.model.tver_normal = [];
	mesh.model.ver_normal  = [];
	for (var i = 0; i < mesh.model.vertices.length; i++) {
		mesh.model.tver_normal[i] = { x: 0, y: 0, z: 0 };
		mesh.model.ver_normal [i] = { x: 0, y: 0, z: 0 };
	}

	for (var i = 0; i < mesh.model.indices.length / 3; i++) {
		mesh.model.tri_normal[i] = calculateNormal(
			mesh,
			mesh.model.indices[ i * 3     ],
			mesh.model.indices[(i * 3) + 1],
			mesh.model.indices[(i * 3) + 2],
			i
		);

		for (var j = 0; j < 3; j++) {
			var k = (i * 3) + j;
			mesh.model.tver_normal[mesh.model.indices[k]].x += mesh.model.tri_normal[i].x;
			mesh.model.tver_normal[mesh.model.indices[k]].y += mesh.model.tri_normal[i].y;
			mesh.model.tver_normal[mesh.model.indices[k]].z += mesh.model.tri_normal[i].z;
		}
	}

	//Now put every vertex in its respective spot.
	for (var i = 0; i < mesh.model.indices.length; i++) {
		mesh.model.ver_normal[i] = mesh.model.tver_normal[mesh.model.indices[i]];

		//Absolute value it
		mesh.model.ver_normal[i].x = mesh.model.ver_normal[i].x;
		mesh.model.ver_normal[i].y = mesh.model.ver_normal[i].y;
		mesh.model.ver_normal[i].z = mesh.model.ver_normal[i].z;
	}

	//Normalise every vertex
	for (var i = 0; i < mesh.model.ver_normal.length; i++) {
		var m = Math.sqrt(
			Math.pow(mesh.model.ver_normal[i].x, 2) + 
			Math.pow(mesh.model.ver_normal[i].y, 2) + 
			Math.pow(mesh.model.ver_normal[i].z, 2)
		);

		mesh.model.ver_normal[i].x /= m;
		mesh.model.ver_normal[i].y /= m;
		mesh.model.ver_normal[i].z /= m;
	}
}

function calculateNormal(mesh, a, b, c, i) {
	var va = {
		x: mesh.model.vertices[ b * 3     ] - mesh.model.vertices[ a * 3     ], 
		y: mesh.model.vertices[(b * 3) + 1] - mesh.model.vertices[(a * 3) + 1],
		z: mesh.model.vertices[(b * 3) + 2] - mesh.model.vertices[(a * 3) + 2],
	};
	var vb = {
		x: mesh.model.vertices[ c * 3     ] - mesh.model.vertices[ a * 3     ], 
		y: mesh.model.vertices[(c * 3) + 1] - mesh.model.vertices[(a * 3) + 1],
		z: mesh.model.vertices[(c * 3) + 2] - mesh.model.vertices[(a * 3) + 2],
	};

	return {
		x: (va.y * vb.z) - (vb.y * va.z),
			y: (va.z * vb.x) - (vb.z * va.x),
			z: (va.x * vb.y) - (vb.x * va.y)
	};

	//Normalise
	//m = Math.sqrt(Math.pow(r.x, 2) + Math.pow(r.y, 2) + Math.pow(r.z, 2));

	//r.x /= m;
	//r.y /= m;
	//r.z /= m;
}
