<?php
	/**
	 * Class for Administration panel
	 * 
	 * @author Bhaskar Banerjee
	 *
	 */
	class Admin extends Controller {
		private $xmldata = '';
		
		/**
		 * Default method
		 * 
		 * @return none
		 */
		public function adminDefault() {
			if ($this->session->getLogInStatus()) {
				$this->adminFront();
			}
		}
		
		/**
		 * Login method
		 * 
		 * @return none
		 */
		public function login() {
			$query = "SELECT * FROM `jmtfw_users` WHERE `user_id`='" . $this->request->getPost('uid') . "' AND `user_password`=PASSWORD('" . $this->request->getPost('paswd') . "')";
			$this->database->executeQuery($query);
			$result = $this->database->fetchResultObject();
			
			// if not valid credencial, return to login screen with error message
			if (!$result) {
				$this->resetView('adminDefault','apps');
				$this->view->system->site_error = true;
				//$this->system->site_error = true;
				$this->view->system->site_error_msg = "Invalid User ID or Password";
			} else {
				/*$this->session->doLogIn();
				$this->session->setUserID($result[0]->id);*/
				$this->session->setUserSession($result[0]->id);
				/*$this->view->session->doLogIn();
				$this->view->session->setUserID($result[0]->id);*/
				
				$this->system->redirect('index.php?task=admin');
			}
		}
		
		/**
		 * Logout method
		 * 
		 * @return none
		 */
		public function logout() {
			$this->session->doLogout();
			$this->system->redirect('admin');
		}
		
		/**
		 * Frontpage for admin panel (if logged in)
		 * 
		 * @return none
		 */
		private function adminFront() {
			// list all gallery items with full data for view
			$query = "SELECT * FROM `jmtfw_gallery_items` ORDER BY `visited` DESC";
			$this->database->executeQuery($query);
			
			$this->resetView('adminFront','apps');
			$this->view->items = $this->database->fetchResultObject();
		}
		
		/**
		 * Gallery files replace action.
		 * This is complete automated process, so clicking on replace link will autometically parse the /public_html/ftp directory and produce/update galley
		 * Each directory under ftp directory will be used as Album (directory name as album title) and their sub-directory as sub-album. only one level sub-album
		 * is supported
		 * 
		 * @return none
		 */
		public function replace() {
			$this->dirReplace();
		}
		
		/**
		 * Gallery files replace method with DB update
		 * 
		 * @return none
		 */
		private function dirReplace() {
			// getdirectory list, names will be used as album title
			$dirList = $this->getDirFileName('/home/bhaskar/public_html/ftp','dir');
			
			// there's no ftp directory so raise an error
			if (!$dirList) {
				$this->resetView('replace','apps');
				
				$this->view->system->site_error = true;
				$this->view->system->site_error_msg = $this->language->_('DirectoryNoExist');
					
				$this->replace();
			} else {
				/*$query = "DELETE FROM `jmtfw_gallery`";
				$this->database->executeQuery($query);
				
				$query = "DELETE FROM `jmtfw_gallery_items`";
				$this->database->executeQuery($query);*/
				
				// get existing thumbnail directory list
				$thumbdirList = $this->getDirFileName('/home/bhaskar/public_html/images/gallery','dir');
				
				// delete every file and directory, we'll make all over
				foreach ($thumbdirList as $thumbdir) {
					$this->deleteFiles($thumbdir);
				}
				
				// loop through the directory list to create albums 
				foreach ($dirList as $dir) {
					/*$query = "INSERT INTO `jmtfw_gallery`(`gallery_title`) VALUES('{$dir}')";
					$this->database->executeQuery($query);
						
					$recid = $this->database->getInserId();*/
					
					// check for any sub directory as sub-album
					$subDirList = $this->getDirFileName('/home/bhaskar/public_html/ftp/'.$dir,'dir');
					
					// check if there's new album in the house. create new one else update. we won't delete DB entry as we need to keep the
					// hit count for each image. new images in any existing album will get it's own row
					if (!$this->checkGallery($dir)) {
						$query = "INSERT INTO `jmtfw_gallery`(`gallery_title`,`ordering`) VALUES('{$dir}','1')";
						$this->database->executeQuery($query);
							
						// get the newly inserted album ID, we'll require it later
						$recid = $this->database->getInserId();
					} else {
						// get the ID of the album
						$query = "SELECT * FROM `jmtfw_gallery` WHERE `gallery_title`='{$dir}'";
						$this->database->executeQuery($query);
						$result = $this->database->fetchResultObject();
							
						$recid = $result[0]->id;
							
						// we'll set the flag, so later on with purge process we'll undestand that this one will be excluded from delete list
						$query = "UPDATE `jmtfw_gallery` SET `ordering`='1' WHERE `id`='{$recid}'";
						$this->database->executeQuery($query);
					}
					
					// if there's no sub-album just copy all files in it else do exactly what we're doing with album
					if (!$subDirList) {
						$filePath = '/home/bhaskar/public_html/ftp/' .$dir;
						$fileList = $this->getDirFileName($filePath,'file');
						
						$this->makeDirectories($dir);
						$this->saveFiles($fileList,$recid,$dir);
					} else {
						foreach ($subDirList as $subDir) {
							if (!$this->checkGallery($subDir)) {
								$query = "INSERT INTO `jmtfw_gallery`(`gallery_title`,`ordering`,`parent`) VALUES('{$subDir}','1','{$recid}')";
								$this->database->executeQuery($query);
									
								$subrecid = $this->database->getInserId();
							} else {
								$query = "SELECT * FROM `jmtfw_gallery` WHERE `gallery_title`='{$subDir}'";
								$this->database->executeQuery($query);
								$result = $this->database->fetchResultObject();
									
								$subrecid = $result[0]->id;
									
								$query = "UPDATE `jmtfw_gallery` SET `ordering`='1' WHERE `id`='{$subrecid}'";
								$this->database->executeQuery($query);
							}
							
							// now create directories and copy files for sub-albums
							$filePath = '/home/bhaskar/public_html/ftp/' . $dir . '/' . $subDir;
							$fileList = $this->getDirFileName($filePath,'file');
							
							$this->makeDirectories($dir);
							$this->makeDirectories($dir.'/'.$subDir);
							$this->saveFiles($fileList,$subrecid,$dir.'/'.$subDir);
						}
					}
				}
				
				// delete all albums which we didn't set flag for skipping
				$query = "DELETE FROM `jmtfw_gallery` WHERE `ordering`='0'";
				$this->database->executeQuery($query);
					
				// now reset the flag so next time we'll get it right
				$query = "UPDATE `jmtfw_gallery` SET `ordering`='0'";
				$this->database->executeQuery($query);
				
				// delete images as we did for albums
				$query = "DELETE FROM `jmtfw_gallery_items` WHERE `ordering`='0'";
				$this->database->executeQuery($query);
			
				// reset the flag for images too
				$query = "UPDATE `jmtfw_gallery_items` SET `ordering`='0'";
				$this->database->executeQuery($query);
				
				// now drop to frontpage
				$this->adminFront();
				
				// and show success message
				$this->view->system->site_error = true;
				$this->view->system->site_error_msg = $this->language->_('Aktivering gennemført');
			}
		}
		
		
		private function updateThumbnailInfo($galid,$filename,$type) {
			$isPassed = false;
			
			if ($type == 'merge') {
				$query = "SELECT * FROM `jmtfw_gallery` WHERE `id`='{$galid}'";
				$this->database->executeQuery($query);
				$result = $this->database->fetchResultObject();
				
				if (trim($result[0]->thumb_img) != '') {
					if (!$this->isLandscape($result[0]->thumb_img,$result[0]->gallery_title)) {
						if ($this->isLandscape($filename,$result[0]->gallery_title)) {
							$isPassed = true;
						}
					}
				} else {
					$isPassed = true;
				}
			} else {
				$isPassed = true;
			}
			
			if ($isPassed) {
				$query = "UPDATE `jmtfw_gallery` SET `thumb_img`='{$filename}' WHERE `id`='{$galid}'";
				$this->database->executeQuery($query);
			}
		}
		
		/**
		 * Method to get directory/file name from the given path
		 * 
		 * @param $dirloc, path
		 * @param $type, file or directory
		 * @return mixed, array or false if no match found
		 */
		private function getDirFileName ($dirloc,$type) {
			$path = $dirloc;
			$excludeList = array('.','..','.DS_Store','Thumb.db');
			$dirList = array();
			$fileList = array();
			
			// is path valid, then go through or return false
			if (is_dir($path)) {
				$handle = @opendir($path);
				
				//running the while loop to make directory and file list
	   			 while ( false !== ($file = readdir($handle)) ) {
			        $dir =$path.'/'.$file;
			        if( is_dir($dir) && !in_array($file,$excludeList) ) {
			            $dirList[] = $file;
			        } elseif( !in_array($file,$excludeList) ) {
			               $fileList[] = $file;		        
			        }
			    }
			    
			    // if requested type is directory, return directory list. for file type return file list
			    // for any unknown, just return initiated array
			    if ($type == 'dir') {
			    	return $dirList;
			    } elseif ($type == 'file') {
			    	return $fileList;
			    } else {
			    	return array();
			    }
			} else {
				return false;
			}
		}
		
		/**
		 * Check if one album xists in DB or not
		 * 
		 * @param $galleryName, album name
		 * @return mixed, string or flase if no match found
		 */		
		private function checkGallery ($galleryName) {
			$query = "SELECT COUNT(`id`) AS tot FROM `jmtfw_gallery` WHERE `gallery_title`='{$galleryName}'";
			$this->database->executeQuery($query);
			$result = $this->database->fetchResultObject();
			
			if ($result[0]->tot > 0) {
				return $galleryName;
			} else {
				return false;
			}
		}
		
		/**
		 * Create directories with name on given album name on a static path
		 * 
		 * @param $galleryName, album name
		 * @return none
		 */
		private function makeDirectories($galleryName) {
			$targetpath = '/home/bhaskar/public_html/images/gallery/';
			$thumbpath1 = '/home/bhaskar/public_html/images/thumb/small/';
			$thumbpath2 = '/home/bhaskar/public_html/images/thumb/large/';
			// create direcories in all three paths and chmod them to rwxrwxrwx
			if (!is_dir($targetpath.$galleryName)) {
				$old = umask(0);
				mkdir($targetpath.$galleryName, 0777);
				umask($old);
			}
			if (!is_dir($thumbpath1.$galleryName)) {
				$old = umask(0);
				mkdir($thumbpath1.$galleryName, 0777);
				mkdir($thumbpath2.$galleryName, 0777);
				umask($old);
			}
			if (!is_dir($thumbpath2.$galleryName)) {
				$old = umask(0);
				mkdir($thumbpath2.$galleryName, 0777);
				umask($old);
			}
		}
		
		/**
		 * Save files for an album and update DB
		 * 
		 * @param $fileList, file list array
		 * @param $galleryId, album DB ID
		 * @param $galleryName, album name
		 * @return none
		 */
		private function saveFiles(&$fileList,$galleryId,$galleryName) {
			// load dynamically image feature, for creating thumbnails
			$this->loadFeature('image');
			
			$thumbsize = '';
			$conf = new Config();
			$srcpath = '/home/bhaskar/public_html/ftp';
			$targetpath = '/home/bhaskar/public_html/images/gallery/';
			$thumbpath1 = '/home/bhaskar/public_html/images/thumb/small/';
			$thumbpath2 = '/home/bhaskar/public_html/images/thumb/large/';
			
			// loop through file list to save them
			foreach ($fileList as $file) {
				copy($srcpath.'/'.$galleryName.'/'.$file, $targetpath.$galleryName.'/'.$file);
				
				// create large and small thumbnail and save them
				$thumb = PhpThumbFactory::create($srcpath.'/'.$galleryName.'/'.$file);
				$thumb2 = PhpThumbFactory::create($srcpath.'/'.$galleryName.'/'.$file);
				$thumb->resize($conf->thumb_width,$conf->thumb_height)->save($thumbpath1.$galleryName.'/'.$file);
				$thumb2->resize($conf->lthumb_width,$conf->lthumb_height)->save($thumbpath2.$galleryName.'/'.$file);
				unset($thumb);
				unset($thumb2);
				
				// resample thumbnails to make them even size
				$this->resampleThumb($file,$galleryName,1);
				$this->resampleThumb($file,$galleryName,0);
				
				// get thumbnail size so we can store those in DB
				$thumbsize = $this->getThumbSize($this->getFileName($file).'.png',$galleryName);
				
				// get IPTC data that user has been embedded in the image. we'll use that as image title and description
				$imgdata = $this->getIPTCData($srcpath.'/'.$galleryName.'/'.$file);
				
				// check is image existing
				$query = "SELECT `id` FROM `jmtfw_gallery_items` WHERE `image_name`='{$file}' AND `gallery_id`='{$galleryId}'";
				$this->database->executeQuery($query);
				$fileid = $this->database->fetchResultObject();
				
				// if image exists, update in DB or create a new record
				if (count($fileid) > 0) {
					$query = "UPDATE `jmtfw_gallery_items` SET `gallery_id`='{$galleryId}',`image_name`='{$file}',`image_title`='{$imgdata[0]}',`description`='{$imgdata[2]}',`iwidth`='{$thumbsize[0]}',`iheight`='{$thumbsize[1]}',`ordering`='1' WHERE `id`='{$fileid[0]->id}'";
				} else {
					if (!$imgdata) {
						$query = "INSERT INTO `jmtfw_gallery_items`(`gallery_id`,`image_name`,`image_title`,`description`,`iwidth`,`iheight`,`ordering`) VALUES('{$galleryId}','{$file}','Untitled','No Description','{$thumbsize[0]}','{$thumbsize[1]}','1')";
					} else {
						$query = "INSERT INTO `jmtfw_gallery_items`(`gallery_id`,`image_name`,`image_title`,`description`,`iwidth`,`iheight`,`ordering`) VALUES('{$galleryId}','{$file}','{$imgdata[0]}','{$imgdata[2]}','{$thumbsize[0]}','{$thumbsize[1]}','1')";
					}
				}
				
				$this->database->executeQuery($query);
			}
		}
		
		/**
		 * Check if the image landscape or portrait
		 * 
		 * @param $filename, file name
		 * @param $galleryname, album name
		 * @return bool
		 */
		private function isLandscape($filename,$galleryname) {
			$thumbpath = '/home/bhaskar/public_html/images/thumb/small/';
			$size = getimagesize($thumbpath.$galleryname.'/'.$filename,$info);
			
			if ($size[0] < $size[1]) {
				return false;
			} else {
				return true;
			}
		}
		
		/**
		 * Get the size (dimension) of given file
		 * 
		 * @param $filename, file name
		 * @param $galleryname, album name
		 * @return array
		 */
		private function getThumbSize($filename,$galleryname) {
			$thumbpath = '/home/bhaskar/public_html/images/thumb/small/';
			$size = getimagesize($thumbpath.$galleryname.'/'.$filename,$info);
			
			return $size;
		}
		
		/**
		 * Resample thumbnail making defined size. smaller image will be filled with transparency
		 * 
		 * @param $filename, file name
		 * @param $galleryName, album name
		 * @param $mode, true for large thumbnail and false for small
		 * @return none
		 */
		private function resampleThumb($filename,$galleryName,$mode) {
			if ($mode) {
				$thumbpath = '/home/bhaskar/public_html/images/thumb/large/';
			} else {
				$thumbpath = '/home/bhaskar/public_html/images/thumb/small/';
			}
			$conf = new Config();
			$format = null;
			$thumbname = $this->getFileName($filename) . '.png';
			
			$size = getimagesize($thumbpath.$galleryName.'/'.$filename,$info);
			
			switch ($size['mime']) {
				case 'image/gif':
					$format = 'GIF';
					break;
				case 'image/jpeg':
					$format = 'JPG';
					break;
				case 'image/png':
					$format = 'PNG';
					break;
				default:
					$this->view->system->site_error = true;
					$this->view->system->site_error_msg = $this->language->_('InvalidImageFormat');
					return;
			}
			
			// if image is smaller in height, remake it
			if ((!$mode && $size[1] < $conf->thumb_height) || ($mode && $size[1] < $conf->lthumb_height)) {
				require_once 'imagecreator.php';
				
				// check if asking for large one or small
				if ($mode) {
					//ImageCreator::create($thumbpath,$thumbname,$galleryName,$size[0],$conf->lthumb_height);
					$dest = imagecreatetruecolor($size[0], $conf->lthumb_height);
				} else {
					//ImageCreator::create($thumbpath,$thumbname,$galleryName,$size[0],$conf->lthumb_height);
					$dest = imagecreatetruecolor($size[0], $conf->thumb_height);
				}
				// make a pitch black image, it'll help up to create transparent
				$black = imagecolorallocate($dest, 0, 0, 0);
				imagecolortransparent($dest, $black);
				//$dest = imagecreatefrompng($thumbpath.$galleryName.'/tmp_'.$thumbname);
				
				//imagealphablending($dest, false);
				//imagesavealpha($dest, true);
				
				switch ($format) {
					case 'GIF':
						$src = imagecreatefromgif($thumbpath.$galleryName.'/'.$filename);
						break;
					case 'JPG':
						$src = imagecreatefromjpeg($thumbpath.$galleryName.'/'.$filename);
						break;
					case 'PNG':
						$src = imagecreatefrompng($thumbpath.$galleryName.'/'.$filename);
						break;
				}
				
				// Copy and merge
				/*if ($size[0] < $conf->thumb_width) {
					imagecopymerge($dest,$src,(floor($conf->thumb_width/2)-floor($size[0]/2)),0,0,0,$size[0],$size[1],100);
				} else {
					imagecopymerge($dest,$src,0,(floor($conf->thumb_height/2)-floor($size[1]/2)),0,0,$size[0],$size[1],100);
				}*/
				if ($mode) {
					imagecopymerge($dest,$src,0,($conf->lthumb_height-$size[1]),0,0,$size[0],$size[1],100);
				} else {
					imagecopymerge($dest,$src,0,($conf->thumb_height-$size[1]),0,0,$size[0],$size[1],100);
				}
				
				// save the resampled image and destroy the source image
				imagepng($dest,$thumbpath.$galleryName.'/'.$thumbname);
				imagedestroy($src);
				
				//unlink($thumbpath.$galleryName.'/tmp_'.$thumbname);
			} else {
				switch ($format) {
					case 'GIF':
						$dest = imagecreatefromgif($thumbpath.$galleryName.'/'.$filename);
						break;
					case 'JPG':
						$dest = imagecreatefromjpeg($thumbpath.$galleryName.'/'.$filename);
						break;
					case 'PNG':
						$dest = imagecreatefrompng($thumbpath.$galleryName.'/'.$filename);
						break;
				}
				
				imagepng($dest,$thumbpath.$galleryName.'/'.$thumbname);
			}
			
			// destroy the temporary image
			imagedestroy($dest);
				
			// if original file format wasn't png it wasn't overwritten. so we've got duplicate file with different extension. so delete original one
			if ($format != 'PNG') {
				unlink($thumbpath.$galleryName.'/'.$filename);
			}
		}
		
		/**
		 * Get the IPTC data out of images
		 * 
		 * @param $image, image file (full path)
		 * @return mixed, array or false if no match found
		 */
		private function getIPTCData($image) {
			$size = getimagesize ( $image, $info);
			
		    if(is_array($info)) { 
		    	$iptc = iptcparse($info["APP13"]); 
				$imgdata[] = $this->fixEncoding($iptc["2#005"][0]);
				$imgdata[] = $this->fixEncoding($iptc["2#080"][0]);
				$imgdata[] = $this->fixEncoding($iptc["2#120"][0]);
				
				return $imgdata;	
			} else {
				return false;
			}
		}
		
		/**
		 * Get the file name truncating the extension
		 * 
		 * @param $fullname, file name
		 * @return string
		 */
		private function getFileName ($fullname) {
			$namearr = explode('.',$fullname);
			$processedname = '';
			
			for ($i = 0; $i < count($namearr) -1 ; $i++) {
				$processedname .= $namearr[$i];
			}
			
			return $processedname;
		}
		
		/**
		 * Daelete all file from the supplied directory within defined path
		 * 
		 * @param $dir, directory
		 * @return none
		 */
		private function deleteFiles($dir) {
			$targetpath = '/home/bhaskar/public_html/images/gallery/';
			$thumbpath1 = '/home/bhaskar/public_html/images/thumb/small/';
			$thumbpath2 = '/home/bhaskar/public_html/images/thumb/large/';
			
			$files = $this->getDirFileName($targetpath.$dir,'file');
			
			if ($files) {
				foreach ($files as $file) {
					unlink($targetpath.$dir.'/'.$file);
					unlink($thumbpath1.$dir.'/'.$file);
					unlink($thumbpath2.$dir.'/'.$file);
				}
			}
			
			rmdir($targetpath.$dir);
			rmdir($thumbpath1.$dir);
			rmdir($thumbpath2.$dir);
		}
		
		/**
		 * Fix encoding for iso-8859-1 to utf-8
		 * 
		 * @param $str, text
		 * @return string
		 */
		private function fixEncoding($str) {
			if (trim(mb_detect_encoding($str, implode(',',mb_list_encodings()), true)) != 'UTF-8') {
				$str = utf8_encode(htmlentities($str));
				$str = str_replace(array("&frac34;","","&iquest;","&reg;","&macr;","","Ã"),array("æ","å","ø","Æ","Ø","Å","Ø"),$str);
			} /*else {
				$str = utf8_encode($str);
			}*/
			return $str;
		}
	}
?>