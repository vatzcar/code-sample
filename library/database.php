<?php
	/**
	 * Class for database operations
	 * 
	 * @package database
	 * @deprecated singleton method
	 * @author Bhaskar Banerjee
	 * @version 1.1
	 * @copyright vatzcar.com
	 * @license GNU/GPL 2
	 */
	
	class Database {
		/** @var string variable to store sql query */
		private   $_sql;
		/** @var string variable to store error message */
		private $_sqlerror;
		/** @var mysql database connection resource */
		private $_resource;
		/** @var mysql database query resource */
		private $_query;
		/** @var system object instance */
		private $_system;
		/** @var last insert ID */
		private $_insertId;
		
		/**
		 * Singleton method
		 *
		 * @return object
		 */
		/*public static function setInstance() 
    	{
	        if (!isset(self::$dbInstance)) {
	            $class = __CLASS__;
	            self::$dbInstance = new $class;
	        }
	
	        return self::$dbInstance;
    	}*/
		
		/**
		 * Constructor of the class. It reads the databse configuration from config file.
		 *
		 * @param object $system, system object instance
		 *
		 */
		function Database(&$system) {
			// read configuration
			$conf = new Config();
			
			// reset error
			//$system->site_error = false;
			$this->_system = $system;
			
			// check for database facilities
			if (!function_exists( 'mysql_connect' )) {
				$this->_system->site_error = true;
				$this->_system->site_error_no = 1;
				$this->_system->site_error_msg = "MySQL is not present";
				
				return;
			}
			if (!($this->_resource = @mysql_connect($conf->db_host, $conf->db_user, $conf->db_pwd))) {
				$this->_system->site_error = true;
				$this->_system->site_error_no = 2;
				$this->_system->site_error_msg = "Database server connection error";
				
				return;
			}
			if ($conf->db_name != '' && !mysql_select_db($conf->db_name, $this->_resource)) {
				$this->_system->site_error = true;
				$this->_system->site_error_no = 3;
				$this->_system->site_error_msg = "Could not connect to Database.";
			}
		}
		
		/**
		 * Destructor
		 */
		/*function __destruct() {
			if (is_resource($this->_resource)) {
				mysql_close($this->_resource);
			}
		}*/
		
		/**
		 * function to set the sql query to the member
		 *
		 * @param string $query
		 * @return bool
		 */
		public function executeQuery($query){
			$this->_sql = $query;
			
			$this->_query = mysql_query($query, $this->_resource);
			
			if (!$this->_query) {
				$this->_sqlerror = mysql_error() . "\n" . $query;
				return false;
			}
			$this->_insertId = mysql_insert_id($this->_resource);
			
			return true;
		}
		
		/**
		 * function to fetch object result
		 *
		 * @return object array
		 */
		public function fetchResultObject() {
			if (!$this->_query) {
				return null;
			}
			$rows = array();
			while ($row = mysql_fetch_object( $this->_query )) {
				$rows[] = $row;
			}
			
			return $rows;
		}
		
		/**
		 * function to fetch row result
		 *
		 * @return array
		 */
		public function fetchResultRow() {
			if (!$this->_query) {
				return null;
			}
			$row = null;
			if ($res = mysql_fetch_row( $this->_query )) {
				$row = $res;
			}
			
			return $row;
		}
		
		/**
		 * function for formatting DB field value quote
		 *
		 * @param mixed $f
		 * @return string
		 */
		private function quote($f) {
			return '\'' . $f .'\'';
		}
		
		/**
		 * function for formatting DB field quote
		 *
		 * @param string $f
		 * @return string
		 */
		private function nameQuote($f) {
			return '`' . $f . '`';
		}
		
		/**
		 * method to return last insert ID
		 * 
		 * @return int
		 */
		public function getInserId() {
			return $this->_insertId;
		}
		
		/**
		 * function to insert data into database table
		 *
		 * @param string $table
		 * @param object $object, database object instance
		 * @return bool
		 */
		public function insertObject( $table, &$object ) {
			$fmtsql = "INSERT INTO $table ( %s ) VALUES ( %s ) ";
			$fields = array();
			foreach (get_object_vars( $object ) as $k => $v) {
				if (is_array($v) or is_object($v) or $v === NULL) {
					continue;
				}
				// internal field
				if ($k[0] == '_') {
					continue;
				}
				// primary key
				if ($k == $object->_tbl_key) {
					continue;
				}
				$fields[] = $this->nameQuote($k);
				
				// password data
				if ($k == $object->_pass_key) {
					$values[] = "password({$this->quote($v)})";
				} else {
					$values[] = $this->quote($v);
				}
				
			}
			
			$query = sprintf( $fmtsql, implode( ",", $fields ) ,  implode( ",", $values ) );
			
			$check = $this->executeQuery($query);
			
			if (!$check) {
				return false;
			}
			

			return true;
		}
	
		/**
		 * function to update database table
		 *
		 * @param string $table table name
		 * @param object $object database table object
		 * @param string $keyName primary key
		 * @param string $passkey password key
		 * @return bool
		 */
		public function updateObject( $table, &$object, $keyName, $passkey ) {
			$fmtsql = "UPDATE $table SET %s WHERE %s";
			$tmp = array();
			foreach (get_object_vars( $object ) as $k => $v) {
				if( is_array($v) or is_object($v) or $k[0] == '_' ) { // internal or NA field
					continue;
				}
				if( $k == $keyName ) { // PK not to be updated
					$where = $keyName . '=' . $this->Quote( $v );
					continue;
				}
				if ($v === NULL) {
					continue;
				}
				if( $v == '' ) {
					$val = "''";
				} else {
					if ($k == $passkey) {
						$val = "password({$this->quote($v)})";
					} else {
						$val = $this->quote($v);
					}
					
				}
				$tmp[] = $this->nameQuote($k) . '=' . $val;
			}
			if (!$this->executeQuery( sprintf( $fmtsql, implode( ",", $tmp ) , $where ) )) {
				return false;
			}
			
			return true;
		}
		
	}
	
	/**
	 * Class for automated database table operation
	 * 
	 * @package JMTFW
	 * @subpackage Database
	 * @author Bhaskar Banerjee
	 * @version 1.1
	 * @copyright vatzcar.com
	 * @license GNU/GPL 2
	 *
	 */
	class DatabaseTables extends JMTFWBase {
		/** @var database table name */
		var $_tbl_name     = '';
		/** @var database table key */
		var $_tbl_key      = '';
		/** @var password key */
		var $_pass_key     = '';
		/** @var database object */
		private $_db_obj       = '';
		
		/**
		 * Constructor
		 *
		 * @param string $tablename
		 * @param string $key
		 * @param object $db
		 * @param string $passkey
		 */
		function DatabaseTables($tablename, $key, &$db, $passkey='') {
			$this->_tbl_name = $tablename;
			$this->_tbl_key = $key;
			$this->_db_obj = $db;
			$this->_pass_key = $passkey;
		}
		
		/**
		 * function to bind array to object
		 *
		 * @param array $array
		 * @return bool
		 */
		public function bind( $array ) {
			if (!is_array( $array )) {
				$this->system->$site_error_no = 4;
				$this->system->$site_error_msg = "Bind failed";
				return false;
			} else {
				return $this->system->bindArrayToObject( $array, $this );
			}
		}
		
		/**
		 * function for storing the data to database
		 *
		 * @return bool
		 */
		public function store() {
			$k = $this->_tbl_key;
			
	
			if ($this->$k) {
				$ret = $this->_db_obj->updateObject( $this->_tbl_name, $this, $this->_tbl_key, $this->_pass_key );
			} else {
				$ret = $this->_db_obj->insertObject( $this->_tbl_name, $this );
			}
			
			if(!$ret) {
				$this->system->site_error_no = 5;
				$this->system->site_error_msg = strtolower(get_class( $this ))."::store failed <br />";
				return false;
			} else {
				return true;
			}
		}
		
		/**
		*	function to delete data from database
		*
		*	@return true if successful otherwise returns and error message
		*/
		public function delete($rid='',$where='') {
			//if (!$this->canDelete( $msg )) {
			//	return $msg;
			//}
	
			$k = $this->_tbl_key;
	
			$query = "DELETE FROM $this->_tbl_name";
			
			if ($rid != '') {
				$query .= "\n WHERE $this->_tbl_key = " . $this->_db->Quote($rid);
			}
			
			if ($where != '') {
				if ($rid != '') {
					$query .= " AND $where";
				} else {
					$query .= "\n WHERE $where";
				}
			}
	
			if ($this->_db_obj->executeQuery($query)) {
				return true;
			} else {
				return false;
			}
		}
		
		/**
		*	binds an array/hash to this object
		*
		*	@param int $oid optional argument, if not specifed then the value of current key is used
		*	@return any result from the database operation
		*/
		public function load($rid='',$where='',$order='',$asc='ASC') {
			$query = "SELECT *"
			. "\n FROM $this->_tbl_name";
			
			if ($rid != '') {
				$query .= "\n WHERE $this->_tbl_key = " . $this->_db_obj->Quote( $rid );
			}
			
			if ($where != '') {
				if ($rid != '') {
					$query .= " AND $where";
				} else {
					$query .= "\n WHERE $where";
				}
			}
			
			if ($order != '') {
				$query .= "\n ORDER BY $order $asc";
			}
			
			$this->_db_obj->executeQuery($query);
			
			return $this->_db_obj->fetchResultObject();
		}
	}
	
?>