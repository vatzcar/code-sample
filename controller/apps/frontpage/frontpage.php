<?php
	/**
	 * Class for frontpage gallery
	 * 
	 * @author Bhaskar Banerjee
	 *
	 */
	class Frontpage extends Controller {
		/**
		 * Default action
		 * 
		 * @return none
		 */
		public function frontpageDefault() {
			// init multi dimensional array for setting entire gallery
			$galleryarr = array();
			$i = 0;
			
			$query = "SELECT * FROM `jmtfw_gallery` WHERE `parent`='0'";
			$this->database->executeQuery($query);
			$galleries = $this->database->fetchResultObject();
			
			// loop through album list to polulate gallery
			foreach ($galleries as $gallery) {
				// add album info to array
				$galleryarr[$i][0] = $gallery->id;
				$galleryarr[$i][1] = $gallery->gallery_title;
				
				//$catimage = false;
				/*if (count($thumb)>0) {
					$galleryarr[$i][2] = $thumb[0]->image_name;
				} else {
					$galleryarr[$i][2] = $images[0]->image_name;
				}*/
				$query = "SELECT * FROM `jmtfw_gallery` WHERE `parent`='{$gallery->id}'";
				$this->database->executeQuery($query);
				$subcat = $this->database->fetchResultObject();
				
				// the album has sub-album
				if (count($subcat) > 0) {
					$subcatrnd = rand(0,count($subcat)-1);
					$galleryarr[$i][2] = $this->getThumbnail($subcat[$subcatrnd]->id);
					$galleryarr[$i][3] = 1;
					$galleryarr[$i][4] = array();
					$k = 0;
					
					// loop through sub-album list of the album to populate sub-albums
					// store sub-album info in array
					foreach ($subcat as $scat) {
						$galleryarr[$i][4][$k][0] = $scat->id;
						$galleryarr[$i][4][$k][1] = $scat->gallery_title;
						$galleryarr[$i][4][$k][2] = $this->getThumbnail($scat->id);
						$galleryarr[$i][4][$k][3] = 0;
						$galleryarr[$i][4][$k][4] = array();
						$galleryarr[$i][5] = $subcat[$subcatrnd]->gallery_title;
						$galleryarr[$i][4][$k][5] = $gallery->gallery_title;
						
						$j = 0;
						
						// get images for the sub-album
						$images = $this->getGalleryImages($scat->id);
						
						// loop through image list and put them in gallery array
						foreach ($images as $image) {
							$galleryarr[$i][4][$k][4][$j] = array();
							$galleryarr[$i][4][$k][4][$j][0] = $image->image_name;
							$galleryarr[$i][4][$k][4][$j][1] = $image->image_title;
							$galleryarr[$i][4][$k][4][$j][2] = $image->description;
							$galleryarr[$i][4][$k][4][$j][3] = $image->id;
							
							/*if (!$catimage) {
								$galleryarr[$i][2] = $image->image_name;
								$catimage = true;
							}*/
							
							$j++;
						}
						$k++;
					}
				} else {
					$galleryarr[$i][2] = $this->getThumbnail($gallery->id);
					$galleryarr[$i][3] = 0;
					$galleryarr[$i][4] = array();
					$galleryarr[$i][5] = '';
					$j = 0;
					
					// get images for album
					$images = $this->getGalleryImages($gallery->id);
					
					// loop through image list and put them in gallery array
					foreach ($images as $image) {
						$galleryarr[$i][4][$j] = array();
						$galleryarr[$i][4][$j][0] = $image->image_name;
						$galleryarr[$i][4][$j][1] = $image->image_title;
						$galleryarr[$i][4][$j][2] = $image->description;
						$galleryarr[$i][4][$j][3] = $image->id;
						
						/*if (!$catimage) {
							$galleryarr[$i][2] = $image->image_name;
							$catimage = true;
						}*/
						
						$j++;
					}
				}
				$i++;
			}
			
			$this->view->galleries = $galleryarr;
		}
		
		/**
		 * Get thumbnail image name of specified album
		 * 
		 * @param $galleryId, album ID
		 * @return string
		 */
		private function getThumbnail($galleryId) {
			//$query = "SELECT `image_name` FROM `jmtfw_gallery_items` WHERE `iwidth`>`iheight` AND `gallery_id`='{$gallery->id}' ORDER BY RAND() LIMIT 0,1";
				$query = "SELECT `image_name` FROM `jmtfw_gallery_items` WHERE `gallery_id`='{$galleryId}' ORDER BY RAND() LIMIT 0,1";
				$this->database->executeQuery($query);
				$thumb = $this->database->fetchResultObject();
				
				return $thumb[0]->image_name;
		}
		
		/**
		 * Get images of specified album
		 * 
		 * @param $galleryId, album ID
		 * @return array
		 */
		private function getGalleryImages($galleryId) {
			$query = "SELECT * FROM `jmtfw_gallery_items` WHERE `gallery_id`='{$galleryId}' ORDER BY `image_name`";
			$this->database->executeQuery($query);
			$images = $this->database->fetchResultObject();
			
			return $images;
		}
		
		/**
		 * Hit counter action
		 * 
		 * @return none
		 */
		public function counthit() {
			$image = basename($this->request->getPost('fileid'));
			
			$query = "UPDATE `jmtfw_gallery_items` SET `visited`=`visited`+1 WHERE `id`='{$image}'";
			$this->database->executeQuery($query);
		}
	}
?>