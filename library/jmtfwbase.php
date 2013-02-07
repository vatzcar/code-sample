<?php
	/**
	 * Base class of the framework
	 * 
	 * @package JMTFW
	 * @subpackage Base
	 * @author Bhaskar Banerjee
	 * @version 1.1
	 * @copyright vatzcar.com
	 * @license GNU/GPL 2
	 *
	 */
	class JMTFWBase {
		/** @var system object instance */
		protected $system;
		
		/** @var request object instance */
		protected $request;
		
		/** @var session object instance */
		protected $session;
		
		/** @var database object instance */
		protected $database;
		
		/** @var language object instance */
		protected $language;
		
		/** @var header HTML */
		protected $headerData;
		
		/**
		 * Framework initialization method
		 * 
		 * @return none
		 */
		public function initFramework() {
			// System object
			$this->system = new System();
			// Session object
			$this->session = new Session();
			// Database object
			$this->database = new Database($this->system);
			// Request object
			$this->request = new Request();
			// Configuration object
			$config = new Config();
			
			// parse Search Engine friendly URL
			if ($config->is_sef) {
				// parse the URL
				$this->request->parseURI();
			}
			
			// if there's HTTP var containing session ID (foreign session or ajax call), initialize the session with Session ID
			// otherwise initialize the session with new ID
			if ($this->request->getPost('session')) {
				$this->session->initSession($this->request->getPost('session'));
			} else {
				$this->session->initSession();
			}
		}
		
		/**
		 * DB object getter
		 * @return object, Database object
		 */
		public function getDBObject() {
			return $this->database;
		}
		
		/**
		 * System object getter
		 * @return object, System object
		 */
		public function getSystemObject() {
			return $this->system;
		}
		
		/**
		 * Session object getter
		 * @return object, Session object
		 */
		public function getSessionObject() {
			return $this->session;
		}
		
		/**
		 * Request object getter
		 * 
		 * @return object, Request object
		 */
		public function getRequestObject() {
			return $this->request;
		}
		
		/**
		 * Method to set user login on run tume
		 * @param string, $uid
		 * 
		 */
		public function setLogin($uid) {
			$this->session->doLogIn();
			$this->session->setUserID($uid);
		}
		
		/**
		 * Method to insert style def into header
		 * 
		 * @param $styledef (string), css style definitions
		 * @return none
		 */
		public function addCSS ($styledef) {
			$this->headerData .= "\n<style type=\"text/css\">\n{$styledef}</style>";
		}
		
		/**
		 * Method to insert javascript into header
		 * 
		 * @param $script (string), Javascript code
		 * @return none
		 */
		public function addJS ($script) {
			$this->headerData .= "\n<script type=\"text/javascript\" language=\"javascript\">\n{$script}</script>";
		}
		
		/**
		 * Method to insert stylesheet link into header
		 * 
		 * @param $stylesheet (string), css file href
		 * @return none
		 */
		public function addStylesheet ($stylesheet) {
			$this->headerData .= "\n<link type=\"text/css\" rel=\"stylesheet\" href=\"{$stylesheet}\" />";
		}
		
		/**
		 * Method to insert javascript source into header
		 * 
		 * @param $script (string), Javascript file source
		 * @return none
		 */
		public function addScript ($script) {
			$this->headerData .= "\n<script type=\"text/javascript\" src=\"{$script}\"></script>";
		}
		
		/**
		 * Method to show HTML header part
		 * 
		 * @return string, header HTML
		 */
		public function showHeader() {
			return $this->headerData;
		}
		
		/**
		 * Method to dynamically load feature
		 * 
		 * @param $featureName (string)
		 * @return none
		 */
		public function loadFeature ($featureName) {
			// include file for requested feature
			switch ($featureName) {
				case 'image':
					require_once 'phpthumb/ThumbLib.inc.php';
					break;
				case 'id3':
					require_once 'getid3/getid3.php';
					break;
				default :
					$this->system->site_error = true;
					$this->system->site_error_msg = 'Feature not available';
			}
		}
	}
?>