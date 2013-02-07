<?php
	/**
	 * Class perform ACL for framework
	 * 
	 * @package JMTFW
	 * @subpackage ACL
	 * @author Bhaskar Banerjee
	 * @version 1.1
	 * @copyright vatzcar.com
	 * @license GNU/GPL 2
	 *
	 */
	class ACL extends JMTFWBase {
		/** @var ACL status   */
		private $isACLPassed;
		
		/**
		 * function to check the ACL for the given task
		 * 
		 * @param string $task
		 * @param string $act
		 * 
		 */
		public function checkACL($task , $act = "", $applet = "") {
			$query = "SELECT `access` FROM `jmtfw_acl` WHERE `task`='{$task}'";
				
			if ($act != "") {
				$query .= " AND `act`='{$act}'";
			}
			
			if ($applet != "") {
				$query .= " AND `applet`='1' AND `appletname`='{$applet}'"; 
			} else {
				$query .= " AND `applet`='0'";
			}
				
			$this->database->executeQuery($query);
			$result = $this->database->fetchResultObject();
				
			// if user is admin don't perform the check, but the ACL entry must be present in DB else nobody will be passed
			if (count($result) > 0) {
				if ($this->session->getAdminStatus()) {
					$this->isACLPassed = true;
				} elseif ($result[0]->access == 0 || ($this->session->getLogInStatus() && $result[0]->access == 2)) {
					$this->isACLPassed = true;
				} else {
					$this->isACLPassed = false;
				}
			} else {
				$this->isACLPassed = false;
			}
		}
		
		/**
		 * Method to get the access status
		 * @return bool
		 */
		public function getACLStatus () {
			return $this->isACLPassed;
		}
	}
?>