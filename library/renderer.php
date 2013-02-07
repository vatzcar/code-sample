<?php
	/**
	 * Template renderer class
	 * 
	 * @package JMTFW
	 * @subpackage Renderer
	 * @version 1.1
	 * @author Bhaskar Banerjee
	 * @copyright vatzcar.com
	 * @license GNU/GPL 2
	 *
	 */
	
	class Renderer {
		private $sysObj;
		private $controllerObj;
		private $templateName;
		private $templateData;
		
		/**
		 * Constructor
		 * 
		 * @return none
		 */
		function __construct() {
			$this->sysObj = new Connector();
		}
		
		/**
		 * Render the site HTML
		 * 
		 * @return none
		 */
		public function render () {
			$appsHTML = '';
			$appletsHtml = '';
			$templatePositions = array();
			$appletLists = array();
			$appletHTML = array();
			
			// get positions for template
			$templatePositions = $this->getTemplatePositions();
			
			// get HTML for app
			$this->processApps();
			$this->controllerObj->setHeader();
			$appsHTML = $this->controllerObj->parseApps();
			
			// load the template file and replace header data on header position and apps data on content position
			$this->loadTemplate();
			$this->templateData = ereg_replace('{header}', $this->controllerObj->showHeader(), $this->templateData);
			$this->templateData = ereg_replace('{content}', $appsHTML, $this->templateData);
			
			// loop through all dynamic template position and place applet HTML in them
			foreach ($templatePositions as $position) {
				$appletLists = $this->getApplets($position);
				$appletsHtml = '';
				
				// loop through each applet assigned for this position and prepare the HTML
				foreach ($appletLists as $applet) {
					$this->controllerObj = Router::loadController($applet,'applet');
					$this->controllerObj->setView($applet, 'applet');
					
					$this->controllerObj->runApplet();
					$appletsHtml .= $this->controllerObj->parseApplet();
				}
				$appletHTML[$position] = $appletsHtml;
			}
			
			// now loop through each position of template and replace applet data
			foreach ($appletHTML as $position => $htmlData) {
				$this->templateData = ereg_replace('{'.$position.'}',$htmlData, $this->templateData);
			}
			
			// finally show generated HTML
			echo $this->templateData;
		}
		
		/**
		 * Call Apps controller object, checking ACL
		 * 
		 * @return none
		 */
		private function processApps() {
			$config = new Config();
			$systemObj = &$this->sysObj->getSystemObject();
			$this->sysObj->checkACL($systemObj->task, $systemObj->act);
			
			// if ACL status passed then call the action else redirect to home page
			if ( $this->sysObj->getACLStatus() ) {
					$this->controllerObj = Router::loadController($systemObj->task,'apps');
					$this->controllerObj->setView($systemObj->task,'apps');
					
					$this->controllerObj->{$systemObj->act}();
			} else {
				$systemObj->redirect($config->index_page);
			}
		}
		
		/**
		 * Call applet controller object, checking ACL
		 * 
		 * @param $position, template position
		 * @return array
		 */
		private function getApplets($position) {
			$appletLists = array();
			
			$systemObj = &$this->sysObj->getSystemObject();
			$dbObject = &$this->sysObj->getDBObject();
			
			$query = "SELECT * FROM `jmtfw_applets` WHERE `position`='{$position}' ORDER BY `orders`";
			$dbObject->executeQuery($query);
			$result = $dbObject->fetchResultObject();
			
			// get applets list for the specified position, if they pass ACL, include them on passed list
			if ($result) {
				foreach ($result as $row) {
					$this->sysObj->checkACL($systemObj->task, $systemObj->act, $row->appletname);
					
					if ($this->sysObj->getACLStatus()) {
						$appletLists[] = $row->appletname;
					}
				}
			}
			
			return $appletLists;
		}
		
		/**
		 * Get assigned template from DB and get all registered positions
		 * 
		 * @return array
		 */
		private function getTemplatePositions() {
			$positions = array();
			
			$dbObject = &$this->sysObj->getDBObject();
			$query = "SELECT * FROM `jmtfw_template` WHERE `active`='1'";
			$dbObject->executeQuery($query);
			$result = $dbObject->fetchResultObject();
			
			if ($result) {
				$this->templateName = $result[0]->templatename;
				if ($result[0]->positions) {
					$positions = explode(',',$result[0]->positions);
				}
			}
			
			return $positions;
		}
		
		/**
		 * Read template file and load file data to templatedata member
		 * 
		 * @return none
		 */
		private function loadTemplate() {
			$this->templateData = join('',file('template/'.$this->templateName.'/index.php'));
		}
	}
?>
