<?php
	/**
	 * Class for handling site language and translation for multi-lingual solution
	 * 
	 * @package JMTFW
	 * @subpackage Language
	 * @author Bhaskar Banerjee
	 * @version 1.1
	 * @copyright vatzcar.com
	 * @license GNU/GPL 2
	 *
	 */
	class Language {
		/** @var apps/applet name */
		private $processName;
		/** @var type of process (1 for Apps, 2 for Applet) */
		private $processType;
		/** @var language name */
		private $language;
		/** @var parsed array from translation ini file */
		private $translation;
		
		/**
		 * Constructor
		 * 
		 * @param $database, database object
		 * @param $language, activated language
		 * @return none
		 */
		function __construct(&$database, $language) {
			if ( $language ) {
				$this->language = $language;
			} else {
				$query = "SELECT * FROM `jmtfw_language` WHERE `default`='1'";
				$database->executeQuery($query);
				$results = $database->fetchResultObject();
				
				if ($results) {
					$this->language = $results[0]->languagecode;
				} else {
					$this->language = "en-GB";
				}
			}
		}
		
		/**
		 * Get current language
		 * 
		 * @return string
		 */
		public function getLanguage() {
			return $this->language;
		}
		
		/**
		 * Get the translation for using app.
		 * 
		 * @param $process, name of process
		 * @param $type, type of user (core for framework itself, apps for main add-on and others)
		 * @return bool
		 */
		public function setContext($process, $type) {
			$this->processName = $process;
			$this->processType = $type;
			
			if ( $this->processType == 'apps' ) {
				if (file_exists('language/apps/'.$this->language.'/'.$this->processName.'.ini')) {
					$ini_path = 'language/apps/'.$this->language.'/'.$this->processName.'.ini';
				} else {
					$ini_path = '../language/apps/'.$this->language.'/'.$this->processName.'.ini';
				}
			} else if ($this->processType == 'core') {
				if (file_exists('language/core/'.$this->language.'/'.$this->processName.'.ini')) {
					$ini_path = 'language/core/'.$this->language.'/'.$this->processName.'.ini';
				} else {
					$ini_path = '../language/core/'.$this->language.'/'.$this->processName.'.ini';
				}
			} else {
				if (file_exists('language/applets/'.$this->language.'/'.$this->processName.'.ini')) {
					$ini_path = 'language/applets/'.$this->language.'/'.$this->processName.'.ini';
				} else {
					$ini_path = '../language/applets/'.$this->language.'/'.$this->processName.'.ini';
				}
			}
			
			// parse the proper ini file
			if (file_exists($ini_path)) {
				$this->translation = parse_ini_file($ini_path);
				return true;
			} else {
				return false;
			}
		}
		
		/**
		 * Get the translation of supplied text
		 * 
		 * @param $text, text to be translated
		 * @return string
		 */
		public function _($text) {
			// if no translation found, return original text
			if (isset($this->translation[$text])) {
				return $this->translation[$text];
			} else {
				return $text;
			}
		}
	}
?>