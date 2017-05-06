/*
 * CN_GL - Textures (Objects)
 *
 * Description:
 *     In GML, all textures are handled as sprites. Because we plan to make
 *     this engine work in a similar way, all textures must be handled so that
 *     they can be reused easily. Therefore, we will make an object to store
 *     a texture, and the user is open to load normal maps or specular maps
 *     as well. CN_INSTANCEs will be able to take these as textures.
 * 
 * Author:
 *     Clara Van Nguyen
 */

function CN_TEXTURE(image_path) {
	this.type = "TEXTURE_2D";
	
	//Texture Information
	this.texture = null;
	this.texture_image = null;
	this.texture_path = "";

	if (image_path != undefined)
		this.load_from_image(image_path);
}

CN_TEXTURE.prototype.load_from_existing = function(tex) {
	this.texture = tex;
}

CN_TEXTURE.prototype.load_from_image = function(image_path) {
	this.texture_path = image_path;
	
	//Set up the basic texture
	this.texture                 = gl.createTexture();
	this.texture_image           = new Image();
	this.texture_image.src       = this.texture_path;

	//Watch this hack.
	this.texture_image.cn_parent = this;

	//Give it a 1x1 white placement texture until the image loads
	gl.bindTexture(gl.TEXTURE_2D, this.texture);

	gl.texImage2D(
		gl.TEXTURE_2D,
		0,
		gl.RGBA,
		1,
		1,
		0,
		gl.RGBA,
		gl.UNSIGNED_BYTE,
		new Uint8Array([255, 255, 255, 255])
	);

	//Whenever the texture actually loads. Replace the blank texture with this one.
	this.texture_image.addEventListener('load', function () {
		gl.bindTexture(gl.TEXTURE_2D, this.cn_parent.texture);
		gl.texImage2D(
			gl.TEXTURE_2D,
			0,
			gl.RGBA,
			gl.RGBA,
			gl.UNSIGNED_BYTE,
			this.cn_parent.texture_image
		);
		gl.generateMipmap(gl.TEXTURE_2D);
	});
}
