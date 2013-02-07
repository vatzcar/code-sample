<?php
	/**
	 * Class for handling HTTP Variables
	 * 
	 * @package JMTFW
	 * @subpackage Request
	 * @author Bhaskar Banerjee
	 * @version 1.1
	 * @copyright vatzcar.com
	 * @license GNU/GPL 2
	 *
	 */
	class Request {
		/** @var HTTP POST variable */
		private $post;
		/** @var HTTP GET variable */
		private $get;
		
		/**
		 * Constructor
		 * @return unknown_type
		 */
		function __construct() {
			$this->get = $_GET;
			$this->post = $_POST;
		}
		/**
		 * Method to retreive HTTP data (GET or POST). POST is encountered first.
		 * 
		 * @param $key, string
		 * @return string, HTTP data, returns false if not found
		 */
		public function getPost($key) {	
			if (isset($this->post[$key])) {
				return $this->post[$key];
			} else if (isset($this->get[$key])) {
				return $this->get[$key];
			} else {
				return false;
			}
		}
		
		/**
		 * Method to set GET/POST value. If GET/POST key found value will be overwritten, else data stored in POST
		 * 
		 * @param mixed, $key
		 * @param mixed, $value
		 * 
		 */
		public function setPost($key,$value) {
			if (isset($this->post[$key])) {
				$this->post[$key] = $value;
			} else if (isset($this->get[$key])) {
				$this->get[$key] = $value;
			} else {
				$this->post[$key] = $value;
			}
		}
		
		/**
		 * Method to get URI from browser URL
		 * 
		 * @return string
		 */
		public function getURI() {
			return substr($_SERVER['REQUEST_URI'],1);
		}
		
		/**
		 * Method to get domain name of the site
		 * 
		 * @return string
		 */
		public function getDomain() {
			return $_SERVER['HTTP_HOST'];
		}
		
		/**
		 * Method to parse SEF URL for the framework, pattern is, first element after HOST is controller name
		 * second one is action name and from third on fisrt element of a pair is GET var and second one is it's value
		 * 
		 */
		public function parseURI() {
			$uCompArr = explode("/",$_SERVER['REQUEST_URI']);
			
			for ($i=1; $i <= count($uCompArr); $i++) {
				if (trim($uCompArr[$i]) != '') {
					if ($i == 1) {
						$this->post['task'] = $uCompArr[$i];
					} else if ($i == 2) {
						$this->post['act'] = $uCompArr[$i];
					} else {
						$this->post[$uCompArr[$i]] = $uCompArr[++$i];
					}
				}
			}
		}
	}
?>