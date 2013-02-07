<?php
	/**
	 * @package config
	 */
	class Config {
		/** @var site name */
		var $site_name   = 'Bhaskar Banerjee';
		/** @var site path */
		var $site_path   = '';
		/** @var site URL */
		var $site_url    = 'localhost';
		/** @var index page, typically index.php but change if you use different one */
		var $index_page = '';
		/** @var database host */
		var $db_host     = 'localhost';
		/** @var database user */
		var $db_user     = 'user';
		/** @var database password */
		var $db_pwd      = 'password';
		/** @var database name */
		var $db_name     = 'bhaskar';
		/** @var site status (offline/online) */
		var $site_online = true;
		/** @var no. of record limit per page. pagination data */
		var $page_limit = 20;
		/** @var is URL SEF. if set true then must use a proper .htaccess file to rewrite URL with entry point to index page */
		var $is_sef = false;
		/** @var image thumbnail width */
		var $thumb_width = 180;
		/** @var image thumbnail height */
		var $thumb_height = 120;
		/** @var large thumbnail width */
		var $lthumb_width = 240;
		/** @var large thumbnail height */
		var $lthumb_height = 180;
		/** @var session timeout, in minute */
		var $session_life = 30;
		
		/*
		 * This block is for foreign session. enable foriegn session if you're showing the site within an iFrame and want to facilitate user 
		 * login/session within the iFrame.
		 * 
		 */
		var $foreign_session = false;
	}
?>