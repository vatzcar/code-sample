<?php
	/**
	 * Top class of framework which loads ass required files and execute renderer.
	 * 
	 * @package JMTFW
	 * @author Bhaskar Banerjee
	 * @version 1.1
	 * @copyright vatzcar.com
	 * @license GNU/GPL 2
	 *
	 */
	class JMTFW {
		public function dispatch() {
			// start the session
			session_start();
			
			// include configuration file
			require_once 'config.php';
			
			// include all library file
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
			require_once 'renderer.php';
			
			// create System, Config and Request object
			$system = new System();
			$config = new Config();
			$request = new Request();
			
			// if the domain doesn't match with config domain name, redirect to it
			if ($_SERVER['SERVER_NAME'] != $config->site_url) {
				$system->redirect($config->index_page);
			} else {
				// whether the apps is running within iFrame on different site declared in config, then check for session HTTP var.
				// if there's none, redirect to main page
				if ($config->foreign_session) {
					if (!$request->getPost('session')) {
						$system->redirect($config->index_page);
					}
				}
			}
			
			// create renderer object and render the URL
			$renderer = new Renderer();
			$renderer->render();
		}
	}
?>