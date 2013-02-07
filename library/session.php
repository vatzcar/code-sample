<?php
	/**
	 * Class for handling the session
	 * 
	 * @package JMTFW
	 * @subpackage Session
	 * @deprecated singleton method
	 * @author Bhaskar Banerjee
	 * @version 1.1
	 * @copyright vatzcar.com
	 * @license GNU/GPL 2
	 *
	 */
	class Session {
		/** @var class instance variable */
		public static $sessionInstance;
		/** @var session ID */
		private $sessionID     = null;
		/** @var user logged in */
		private $isLoggedIn    = false;
		/** @var user admin */
		private $isAdmin	   = false;
		/** @var user Id (logged in) */
		private $userID        = '';
		/** @var user details */
		private $userVar       = null;
		/** @var user profile details */
		private $userProfileVar = null;
				
		/**
		 * Singleton method
		 *
		 * @return object
		 */
		/*public static function setInstance() 
    	{
	        if (!isset(self::$sessionInstance)) {
	            $class = __CLASS__;
	            self::$sessionInstance = new $class;
	        }
	
	        return self::$sessionInstance;
    	}*/
    	
    	/**
    	 * function to initialize session
    	 *
    	 * @param string $sessionID
    	 * @return none
    	 */
    	public function initSession($sessionID = "") {
    		// if session ID supplied initialize the session with it or get php one
    		if ($sessionID == '') {
	    		if ($this->sessionID === null) {
					$this->sessionID = session_id();
				}
    		} else {
    			$this->sessionID = $sessionID;
    		}
    		
    		// if there's any users with this session log him in
    		if ($this->getSessionUserID($this->sessionID)) {
    			$this->doLogIn();
    			$this->setUserID($this->getSessionUserID($this->sessionID));
    		}
    		
    	}
		
		/**
		 * function to get current session ID
		 *
		 * @return string
		 */
		public function getSessionID() {
			return $this->sessionID;
		}
		
		/**
		 * function to set the user logged in
		 *
		 */
		public function doLogIn() {
			$this->isLoggedIn = true;
		}
		
		/**
		 * function to set user logged out
		 *
		 */
		public function doLogout() {
			$system = new System();
			$database = new Database($system);
			
			/*setcookie('UID','',TIME()-3600,'/');
			unset($_SESSION['UID']);*/
			$query = "DELETE FROM `jmtfw_user_session` WHERE `uid`='{$this->userID}'";
			$database->executeQuery($query);
			$this->isLoggedIn = false;
			$this->userID = '';
			$this->userVar = null;
			
			session_destroy();
		}
		
		/**
		 * function to get user login status
		 *
		 * @return bool
		 */
		public function getLogInStatus() {
			return $this->isLoggedIn;
		}
		
		/**
		 * function to set session user ID
		 *
		 * @param string $id
		 */
		public function setUserID($id) {
			$this->userID = $id;
			
			// find out user details of the user and store it for any later use through out the framework
			$system = new System();
			$database = new Database($system);
			$query = "SELECT * FROM `jmtfw_users` WHERE `id`='{$this->userID}'";
			$database->executeQuery($query);
			$results = $database->fetchResultObject();
			
			$this->userVar = $results[0];
			
			// store the profile also 
			$query = "SELECT * FROM `jmtfw_users_profile` WHERE `user_id`='{$this->userID}'";
			$database->executeQuery($query);
			$results = $database->fetchResultObject();
			
			$this->userProfileVar = $results[0];
		}
		
		/**
		 * function to get current logged in user ID
		 *
		 * @return string
		 */
		public function getUserID() {
			return $this->userID;
		}	

		
		/**
		 * function to get var with user details
		 *
		 * @return array
		 */
		public function getUserVar() {
			return $this->userVar;
		}
		
		/**
		 * Method to get user attribute
		 * 
		 * @param mixed $attrib
		 * @return mixed
		 */
		public function getUserAttrib($attrib) {
			return $this->userVar->$attrib;
		}
		
		/**
		 * Method to get user profile attribute
		 * 
		 * @param mixed $attrib
		 * @return mixed
		 */
		public function getUserProfileAttrib($attrib) {
			return $this->userProfileVar->$attrib;
		}
		
		
		/**
		 * function to check admin status
		 * 
		 * @return bool
		 */
		public function getAdminStatus() {
			return $this->userVar->isAdmin;
		}
		
		/**
		 * Method to set user ID to session
		 * @param $sid, session ID
		 * 
		 */
		public function setUserSession($sid) {
			/*setcookie('UID',$sid,0,'/');
			$_SESSION['UID'] = $sid;*/
			$system = new System();
			$database = new Database($system);
			$time = time();
			
			// update the timestamp for session so it doesn't expires, (only site activity will invoke this)
			
			// delete the entry from DB if it's too old
			$query = "DELETE FROM `jmtfw_user_session` WHERE `uid`='{$sid}' AND ({$time}-`stamp`)>900";
			$database->executeQuery($query);
			
			// and insert a new one
			$query = "INSERT INTO `jmtfw_user_session`(`sessionid`,`stamp`,`uid`) VALUES('{$this->sessionID}','".time()."','{$sid}')";
			$database->executeQuery($query);
		}
		
		/**
		 * Method to check the user id for current session from DB
		 * 
		 * @param string, $session, session ID
		 * @return mixed, false if not matched or the UID
		 */
		private function getSessionUserID($session) {
			$system = new System();
			$database = new Database($system);
			$config = new Config();
			
			$query = "SELECT * FROM `jmtfw_user_session` WHERE `sessionid`='{$session}'";
			$database->executeQuery($query);
			$result = $database->fetchResultObject();
			
			$time = time();
			
			// if the session is idle for more than config'd session activity time, delete the entry and tell that no session existing
			// otherwise return the relevant user ID
			if (count($result) > 0) {
				if (($time - $result[0]->stamp) > ($config->session_life * 60)) {
					$query = "DELETE FROM `jmtfw_user_session` WHERE `id`='{$result[0]->id}'";
					$database->executeQuery($query);
					
					return false;
				} else {
					return $result[0]->uid;
				}
			} else {
				return false;
			}
		}
	}
?>