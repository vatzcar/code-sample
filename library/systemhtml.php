<?php
/**
 * Class for handling HTML
 * 
 * @package JMTFW
 * @subpackage HTML
 * @author Bhaskar Banerjee
 * @version 1.1
 * @copyright vatzcar.com
 * @license GNU/GPL 2
 *
 */
class SystemHTML extends Router {
	/**
	 * Function to show the HTML of the relevant view
	 * 
	 */
	public function parseApps() {
		$outputBuffer = '';
		
		ob_start();
		$this->view->{$this->system->act}();
		$this->updateHeader($this->view->showHeader());
		$outputBuffer = ob_get_contents();
		ob_end_clean();

		return $outputBuffer;
	}
	
	/**
	 * Method to populate menu HTML
	 * 
	 */
	public function showMenu() {
		$query = "SELECT * FROM `jmtfw_menu_items`";
		
		if (!$this->session->getLogInStatus()) {
			$query .= " WHERE `access`='1'";
		}
		$query .= " AND `menutype`='1' AND ORDER BY `orders`";
		
		$this->database->executeQuery($query);
		$results = $this->database->fetchResultObject();
		
		$menucount = count($results);
		$i = 0;
		$itemposition = "";
?>
	<ul class="mainmenu">
<?php foreach ($results as $result) {
		if ($this->session->getAdminStatus() || ($result->access == 2 && $this->session->getLogInStatus()) || !$result->access) {
			if ($i == 0) {
				$itemposition = "first";
			} else if ($i == $menucount -1) {
				$itemposition = "last";
			}
?>
		<li class="menuitem <?php echo $itemposition; ?> <?php echo $result->styleclass; ?>"><a href="<?php echo $result->link; ?>"><?php echo $result->title; ?></a></li>
<?php
			$i++;
	 	} 
	  }
?>
	</ul>
<?php 
	}
	
	/**
	 * Method to parse applet HTML
	 * @return STRING, buffered HTML
	 */
	public function parseApplet() {
		$outputBuffer = '';
		
		ob_start();
		$this->view->show();
		$outputBuffer = ob_get_contents();
		ob_end_clean();
		
		return $outputBuffer;
	}
	
		/**
		 * Core method to set site HTML header
		 */
		public function setHeader() {
			$config = new Config();
			
			if ($config->is_sef) {
				$header = '<script type="text/javascript" src="http://'.$this->request->getDomain().'/js/jquery/jquery-1.4.1.min.js"></script>
<script type="text/javascript" src="http://'.$this->request->getDomain().'/js/jquery/jquery-ui-1.7.custom.min.js"></script>
<script type="text/javascript" src="http://'.$this->request->getDomain().'/js/jquery/jquery.easing.1.3.js"></script>
<script type="text/javascript" src="http://'.$this->request->getDomain().'/js/jquery/jquery.fancybox-1.3.0.js"></script>
<script type="text/javascript" src="http://'.$this->request->getDomain().'/js/jquery/menu.js"></script>
<script type="text/javascript" src="http://'.$this->request->getDomain().'/js/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="http://'.$this->request->getDomain().'/js/php.min.js"></script>
<script type="text/javascript" src="http://'.$this->request->getDomain().'/js/editor.js"></script>
<link type="text/css" href="http://'.$this->request->getDomain().'/js/jquery/themes/smoothness/ui.all.css" rel="stylesheet" />
<link type="text/css" href="http://'.$this->request->getDomain().'/js/jquery/themes/smoothness/menu.css" rel="stylesheet" />
<link type="text/css" href="http://'.$this->request->getDomain().'/js/jquery/fancybox/jquery.fancybox-1.3.0.css" rel="stylesheet" />';
			} else {
				$header = '<script type="text/javascript" src="js/jquery/jquery-1.4.1.min.js"></script>
<script type="text/javascript" src="js/jquery/jquery-ui-1.7.custom.min.js"></script>
<script type="text/javascript" src="js/jquery/jquery.easing.1.3.js"></script>
<script type="text/javascript" src="js/jquery/jquery.fancybox-1.3.0.js"></script>
<script type="text/javascript" src="js/jquery/menu.js"></script>
<script type="text/javascript" src="js/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="js/php.min.js"></script>
<script type="text/javascript" src="js/editor.js"></script>
<link type="text/css" href="js/jquery/themes/smoothness/ui.all.css" rel="stylesheet" />
<link type="text/css" href="js/jquery/themes/smoothness/menu.css" rel="stylesheet" />
<link type="text/css" href="js/jquery/fancybox/jquery.fancybox-1.3.0.css" rel="stylesheet" />';
			}
			
			$this->headerData = $header;
		}
		
		/**
		 * Append header data
		 * 
		 * @param $data, data to inject in header
		 * @return none
		 */
		public function updateHeader ($data) {
			$this->headerData .= $data;
		}
}
?>
