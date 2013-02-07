<?php
/**
 * Class to manage the views and display the html
 * 
 * @package JMTFW
 * @subpackage View
 * @author Bhaskar Banerjee
 * @version 1.1
 * @copyright vatzcar.com
 * @license GNU/GPL 2
 *
 */
class Views extends Connector {
	/**
	 * Create the view object
	 * 
	 * @param $objectName, name of the view(same as controller)
	 * @param $type, type of add-on
	 * @return object
	 */
	public static function getViewObject($objectName, $type){
		$viewclass = ucfirst($objectName).'View';
		
		// include the view file
		if ($type == 'apps') {
			if (file_exists('views/apps/'.$objectName.'/'.$objectName.'.php')) {
				require_once 'views/apps/'.$objectName.'/'.$objectName.'.php';
			} else {
				require_once '../views/apps/'.$objectName.'/'.$objectName.'.php';
			}
		} else if ($type == 'applet') {
			if (file_exists('views/applets/'.$objectName.'/'.$objectName.'.php')) {
				require_once 'views/applets/'.$objectName.'/'.$objectName.'.php';
			} else {
				require_once '../views/applets/'.$objectName.'/'.$objectName.'.php';
			}
		}
		
		// and dynamically load the view object
		$obj_instance = new $viewclass();
		return $obj_instance;
	}
}
?>