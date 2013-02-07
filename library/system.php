<?php
	/**
	 * Class for system level operation of framework
	 * 
	 * @package JMTFW
	 * @subpackage System
	 * @deprecated singleton method
	 * @author Bhaskar Banerjee
	 * @version 1.1
	 * @copyright vatzcar.com
	 * @license GNU GPL 2
	 * 
	 * @todo evaluate mailer class and deprecate maiIt method
	 * 
	 */
	
	class System {
		/** @var class instance variable */
		public static $systemInstance;
		/** @var site error status */
		public  $site_error = false;
		/** @var site error no */
		public  $site_error_no; // last no 6
		/** @var site error message */
		public  $site_error_msg;
		/** @var request variable act */
		public  $act;
		/** @var request variable task */
		public  $task;
		
		/**
		 * Singleton method
		 *
		 * @return object
		 */
		/*public static function setInstance() 
    	{
	        if (!isset(self::$systemInstance)) {
	            $class = __CLASS__;
	            self::$systemInstance = new $class;
	        }
	
	        return self::$systemInstance;
    	}*/
		
		/**
		 * Strip slashes from strings or arrays of strings
		 * 
		 * @param mixed, string or array
		 * @return mixed, String or array stripped of slashes
		 */
		public function stripslash( &$value ) {
			$ret = '';
			if (is_string( $value )) {
				$ret = stripslashes( $value );
			} else {
				if (is_array( $value )) {
					$ret = array();
					foreach ($value as $key => $val) {
						$ret[$key] = stripslash( $val );
					}
				} else {
					$ret = $value;
				}
			}
			return $ret;
		}
		
		/**
		* Copy the named array content into the object as properties
		* only existing properties of object are filled. when undefined in hash, properties wont be deleted
		* 
		* @param array
		* @param object, byref the object to fill of any class
		* @return bool
		*/
		function bindArrayToObject( $array, &$obj) {
			if (!is_array( $array ) || !is_object( $obj )) {
				return (false);
			}
		
			foreach (get_object_vars($obj) as $k => $v) {
				if( substr( $k, 0, 1 ) != '_' ) {			// internal attributes of an object are ignored
					$ak = $k;
					
					if (isset($array[$ak])) {
						$obj->$k = (get_magic_quotes_gpc()) ? $this->stripslash( $array[$ak] ) : $array[$ak];
					}
				}
			}
		
			return true;
		}
		
		/**
		 * redirect to a location. If suffixSession is set true it'll append the sessionID at last
		 * 
		 * @param string, $page
		 * @return none
		 */
		function redirect($page){
			$conf = new Config();
			$request = new Request();
			
			// if foreign session is set tue in config, set suffixSession to true
			if ($conf->foreign_session) {
				$suffixSession = true;
			} else {
				$suffixSession = false;
			}
			
			// append the session id to the URL
			if ($suffixSession) {
				if (strpos($page,'?') === false) {
					if ($request->getPost('session')) {
						$url = 'http://' . $conf->site_url . "/" . $page . '?session=' . $request->getPost('session');
					} else {
						$url = 'http://' . $conf->site_url . "/" . $page . '?session=' . session_id();
					}
				} else {
					if ($request->getPost('session')) {
						$url = 'http://' . $conf->site_url . "/" . $page . '&session=' . $request->getPost('session');
					} else {
						$url = 'http://' . $conf->site_url . "/" . $page . '&session=' . session_id();
					}
				}
			} else {
				$url = 'http://' . $conf->site_url . "/" . $page;
			}
			
			// if header is sent for some reason, redirect using JS or send header
			if (headers_sent()) {
				echo "<script>document.location.href='$url';</script>\n";
			} else {
				@ob_end_clean(); // clear output buffer
				header( 'HTTP/1.1 301 Moved Permanently' );
				header( "Location: ". $url );
			}
		}
		
		/**
		 * send mail using php Mailer
		 * @param $recipient, Mail Recipient
		 * @param $from, Mail send by
		 * @param $subject, Mail Subject
		 * @param $msg, Mail Body
		 * @return none
		 */
		public function mailIt($recipient,$from,$subject,$msg,$fromname='') {
			if ($fromname == '') {
				$fromname = $from;
			}
			
			$ob = "----=_OuterBoundary_000";
		   	$ib = "----=_InnerBoundery_001";
				
			$headers  = "MIME-Version: 1.0\r\n"; 
			$headers .= "From: {$fromname}<{$from}>\n"; 
			$headers .= "X-Priority: 1\n"; 
			$headers .= "X-Mailer: PH Mailer1.1\n"; 
			$headers .= "Content-Type: multipart/mixed;\n\tboundary=\"{$ob}\"\n";
			   	
			$message  = "This is a multi-part message in MIME format.\n";
			$message .= "\n--{$ob}\n";
			$message .= "Content-Type: multipart/alternative;\n\tboundary=\"{$ib}\"\n\n";
			$message .= "\n--{$ib}\n";
			$message .= "Content-Type: text/html;\n\tcharset=\"utf-8\"\n";
			$message .= "Content-Transfer-Encoding: quoted-printable\n\n";
			$message .= $msg."\n\n";
			$message .= "\n--{$ib}--\n";
			$message .= "\n--{$ob}--\n";
			
			mail($recipient,$subject,$message,$headers);
			//mail($recipient,$subject,$msg,'From:'.$from);
		}
	}
?>