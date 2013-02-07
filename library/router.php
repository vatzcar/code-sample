<?php
	/**
	 * Class for routing data flow
	 * 
	 * @package JMTFW
	 * @subpackage Router
	 * @author Bhaskar Banerjee
	 * @version 1.1
	 * @copyright vatzcar.com
	 * @license GNU/GPL 2
	 *
	 */
	class Router extends Connector {
		public $view;
		
		/**
		 * Create the controller object
		 * 
		 * @param $objectName, controller name
		 * @param $type, type of add-on
		 * @return unknown_type
		 */
		public static function loadController($objectName, $type) {
			// include the controller file
			if ($type == 'apps') {
				if (file_exists('controller/apps/'.$objectName.'/'.$objectName.'.php')) {
					require_once 'controller/apps/'.$objectName.'/'.$objectName.'.php';
				} else {
					require_once '../controller/apps/'.$objectName.'/'.$objectName.'.php';
				}
			} else if ($type == 'applet') {
				if (file_exists('controller/applets/'.$objectName.'/'.$objectName.'.php')) {
					require_once 'controller/applets/'.$objectName.'/'.$objectName.'.php';
				} else {
					require_once '../controller/applets/'.$objectName.'/'.$objectName.'.php';
				}
			}
			
			// and create the controller object dynamically
			$classname = ucfirst($objectName);
			$controllerObj = new $classname();
			
			return $controllerObj;
		}
		
		/**
		 * Set the view for controller
		 * 
		 * @param $objectName, view name
		 * @param $type, add-on type
		 * @return none
		 */
		public function setView($objectName, $type) {
			$this->view = Views::getViewObject($objectName, $type);
			$this->view->language->setContext($objectName, $type);
		}
		
		/**
		 * Load new view within the same controller
		 * 
		 * @param $act, view name
		 * @param $type, add-on type
		 * @return none
		 */
		public function resetView ($act, $type) {
			$this->system->act = $act;
			$this->setView($this->system->task, $type);
		}
	}
?>