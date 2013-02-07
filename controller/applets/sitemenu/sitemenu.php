<?php
	/**
	 * Class for site frontend menu
	 * 
	 * @author Bhaskar Banerjee
	 *
	 */
	class Sitemenu extends Controller {
		// mandatory applet call
		public function runApplet() {
			// get the text file where contact and info text will be saved
			$kontaktFile = '/home/bhaskar/public_html/kontakt.txt';
			$infoFile = '/home/bhaskar/public_html/info.txt';
			
			$this->view->kontaktData = '';
			$this->view->infoData = '';
			
			// read the contact file and load data
			if (file_exists($kontaktFile)) {
				$kfh =@ fopen($kontaktFile,'r');

				$this->view->kontaktData = fread($kfh, filesize($kontaktFile));
			}
			
			// read the info file and load data
			if (file_exists($infoFile)) {
				$kfh =@ fopen($infoFile,'r');

				$this->view->infoData = fread($kfh, filesize($infoFile));
			}
		}
	}
?>