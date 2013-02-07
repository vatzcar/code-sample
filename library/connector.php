<?php
	/**
	 * Class to connect public level classes with core classes
	 * 
	 * @package JMTFW
	 * @subpackage Connector
	 * @author Bhaskar Banerjee
	 * @version 1.1
	 * @copyright vatzcar.com
	 * @license GNU/GPL 2
	 *
	 */
	class Connector extends ACL {
		public $utility;
		
		/**
		 * Constructor
		 * 
		 */
		function __construct() {
			// Initialize the framework
			$this->initFramework();
			// Utility object
			$this->utility = new Util();
			
			// update the system object with appropriate controller and action name
			if ($this->request->getPost('task')) {
				$this->system->task = $this->request->getPost('task');
				
				if ($this->request->getPost('act')) {
					$this->system->act = $this->request->getPost('act');
				} else {
					$this->system->act = $this->system->task . 'Default';
				}
			} else { // if no controller name passed get the frontpage controller from DB and use that
				$this->system->task = $this->getFrontpageApp();
				$this->system->act = $this->system->task . 'Default';
			}
			
			// prepare the language object
			$this->language = new Language($this->database, $this->request->getPost('language'));
		}
		
		/**
		 * Method to get frontpage apps from databse
		 * 
		 * @return string, apps name
		 */
		private function getFrontpageApp () {
			$query = "SELECT * FROM `jmtfw_apps` WHERE `frontpage`='1'";
			$this->database->executeQuery($query);
			$appName = $this->database->fetchResultObject();
				
			return $appName[0]->appname;
		}
	}
?>