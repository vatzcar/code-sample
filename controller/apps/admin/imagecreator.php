<?php
/**
 * Class to create transparent image
 * 
 * @author Bhaskar Banerjee
 *
 */
class ImageCreator {
	public static function create($thumbpath,$filename,$galleryName,$width,$height) {
		$conf = new Config();
		
		//$thumbpath = '/home/henrikpetit/public_html/images/thumb/';

		//Create the image resource 
		$image = imagecreatetruecolor($width, $height);
		$black = imagecolorallocate($image, 0, 0, 0); 
		
		//Make the background black 
		imagecolortransparent($image, $black);
		
		//Tell the browser what kind of file is come in 
		//header("Content-Type: image/jpeg"); 
		
		//Output the newly created image in png format 
		imagepng($image,$thumbpath.$galleryName.'/tmp_'.$filename); 
		
	}
}
?>