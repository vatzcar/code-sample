<?php
	/**
	 * AJAX renderer class
	 * 
	 * @package JMTFW
	 * @subpackage AJAX
	 * @version 1.1
	 * @author Bhaskar Banerjee
	 * @copyright vatzcar.com
	 * @license GNU/GPL 2
	 *
	 */
	
	class AJAX {
		private $sysObj;
		private $controllerObj;
		
		/**
		 * Constructor
		 * @return none
		 */
		function __construct() {
			require_once '../config.php';
			
			require_once 'jmtfwbase.php';
			require_once 'system.php';
			require_once 'session.php';
			require_once 'request.php';
			require_once 'database.php';
			require_once 'pagination.php';
			require_once 'utility.php';
			require_once 'language.php';
			require_once 'mailer.php';
			require_once 'acl.php';
			require_once 'connector.php';
			require_once 'views.php';
			require_once 'router.php';
			require_once 'systemhtml.php';
			require_once 'controller.php';
			
			// if userid provided with AJAX HTTP data, make him logged in
			$this->sysObj = new Connector();
			$request = $this->sysObj->getRequestObject();
			if ($request->getPost('userid')) {
				$this->sysObj->setLogin($request->getPost('userid'));
			}
		}
		
		/**
		 * Render the HTML
		 * 
		 * @param $type, add-on type
		 * @param $applet, if the add-on is applet, supply applet name
		 * @return none
		 */
		public function render ($type, $applet='') {
			$appsHTML = '';
			
			// process the add-on and load generated HTML
			if ($type == 'apps') {
				$this->processApps();
				$appsHTML = $this->controllerObj->parseApps();
			} else {
				$this->controllerObj = Router::loadController($applet,'applet');
				$this->controllerObj->setView($applet, 'applet');
					
				$this->controllerObj->runApplet();
				$appsHtml .= $this->controllerObj->parseApplet();
			}
			
			echo $appsHTML;
		}
		
		/**
		 * Call apps controller object is ACL is passed
		 * 
		 * @return none
		 */
		public function processApps() {
			$systemObj = &$this->sysObj->getSystemObject();
			$this->sysObj->checkACL($systemObj->task, $systemObj->act);
			
			if ( $this->sysObj->getACLStatus() ) {
					$this->controllerObj = Router::loadController($systemObj->task,'apps');
					$this->controllerObj->setView($systemObj->task,'apps');
					
					$this->controllerObj->{$systemObj->act}();
			}
		}
		
	}
	
	$applet = $_POST['applet'];
	$type = $_POST['type'];
	
	$renderer = new AJAX();
	$renderer->render($type, $applet);
?>