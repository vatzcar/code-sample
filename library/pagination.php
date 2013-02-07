<?php
	/**
	 * Class for creating pagination in page
	 * 
	 * @package JMTFW
	 * @subpackage Package
	 * @author Bhaskar Banerjee
	 * @version 1.1
	 * @copyright vatzcar.com
	 * @license GNU/GPL 2
	 * 
	 * @todo comment all AJAX pagination and uncomment all regular paginationm in order to work, and find one solution to work with both.
	 *
	 */
	class Pagination {
		/** @var int, $page, page number */
		private $page;
		/** @var int, $limit, limit of no. of records */
		public $limit;
		/** @var int, $limitStart, starting limit value for SQL */
		public $limitStart;
		/** @var object, $language, language object instance */
		private $language;
		/** @var object, $request, request object instance */
		private $request;
		/** @var string, $pageName, page variable name */
		private $pageName;
		
		/**
		 * Constructor
		 * 
		 * @param object, $request, request object instance
		 * @param object, $database, database object instance
		 * 
		 */
		function __construct(&$request, &$database, $pageVar = 'page'){
			$this->pageName = $pageVar;
			$config = new Config();
			if ($request->getPost($pageVar)) {
				$this->page = $request->getPost($pageVar);
			} else {
				$this->page = 0;
			}
			$this->limit = $config->page_limit;
			$this->limitStart = (int)$this->page * (int)$this->limit;
			$this->request = $request;
			$this->language = new Language($database,$request->getPost('language'));
			$this->language->setContext('pagination','core');
		}
		
		/**
		 * Method to create page nav HTML (AJAX)
		 * 
		 * @param int, $records, total number of record for the pagination data
		 * @return string
		 */
		public function show($records,$uid,$tabid) {
			$baseUri = $this->request->getURI();
			$lastPageNo = ceil(((int)$records / (int)$this->limit) - 1);
			$html = '';
			
			// if total no. of records are more than per page limit, we're getting a pagination
			if ($records > $this->limit) {
				$firstLink = $this->preparePageURL(0,$uid,$tabid);
				$lastLink = $this->preparePageURL($lastPageNo,$uid,$tabid);
				
				$prevLink = $this->preparePageURL((int)$this->page - 1,$uid,$tabid);
				$nextLink = $this->preparePageURL((int)$this->page + 1,$uid,$tabid);
				
				// some pre number pagination
				$html = "<ul class=\"pagenav\">\n<li class=\"pagenav-first\">\n";
				$html .= ($this->page == 0)?"<span>".$this->language->_('PageFirst')."</span>\n":"<a href=\"javascript:void(0)\" onclick=\"{$firstLink}\">".$this->language->_('PageFirst')."</a>\n";
				$html .= "</li>\n<li class=\"pagenav-prev\">\n";
				$html .= ($this->page == 0)?"<span>".$this->language->_('PagePrev')."</span>\n":"<a href=\"javascript:void(0)\" onclick=\"{$prevLink}\">".$this->language->_('PagePrev')."</a>\n";
				$html .= "</li>\n";
				
				// if total no. of pages more than 14 we not going to show them all.
				
				// if current page no. is more than 14 then push fist pages (like if current page is 15, we'll start pagination from 2nd page)
				if ((int)$this->page > 14) {
					$i = (int)$this->page - 14;
					$j = $i + 1;
				} else {
					$i = 0;
					$j = 1;
				}
				
				// if last page no. (total page) is greater than 14 then limit it to 14 or keep as is
				if ($lastPageNo > 14) {
					$listLimit = 14;
				} else {
					$listLimit = $lastPageNo;
				}
					
				// now loop till we reach the limit
				for ($k = 0; $k <= $listLimit; $i++, $j++, $k++) {
					
					$pageLink = $this->preparePageURL($i,$uid,$tabid); 
					$html .= "<li id=\"{$i}\" class=\"pagenav-page";
					$html .= ($this->page == $i)?" current-page\"><span>{$j}</span>":"\"><a href=\"javascript:void(0)\" onclick=\"{$pageLink}\">{$j}</a>";
					$html .= "</li>\n";
				}
				
				// some post number pagination
				$html .= "<li class=\"pagenav-next\">\n";
				$html .= ($this->page == $lastPageNo)?"<span>".$this->language->_('PageNext')."</span>\n":"<a href=\"javascript:void(0)\" onclick=\"{$nextLink}\">".$this->language->_('PageNext')."</a>\n";
				$html .= "</li>\n<li class=\"pagenav-last\">\n";
				$html .= ($this->page == $lastPageNo)?"<span>".$this->language->_('PageLast')."</span>\n":"<a href=\"javascript:void(0)\" onclick=\"{$lastLink}\">".$this->language->_('PageLast')."</a>\n";
				$html .= "</li>\n</ul>\n";
			}
			
			return $html;
		}
		/*
		 public function show($records) {
			$baseUri = $this->request->getURI();
			$lastPageNo = ceil(((int)$records / (int)$this->limit) - 1);
			$html = '';
			
			if ($lastPageNo > 14) {
				$listLimit = 14;
			} else {
				$listLimit = $lastPageNo;
			}
			
			if ($records > $this->limit) {
				$firstLink = $baseUri;
				$lastLink = $this->preparePageURL($lastPageNo);
				
				$prevLink = $this->preparePageURL((int)$this->page - 1);
				$nextLink = $this->preparePageURL((int)$this->page + 1);
				
				$html = "<ul class=\"pagenav\">\n<li class=\"pagenav-first\">\n";
				$html .= ($this->page == 0)?"<span>".$this->language->_('PageFirst')."</span>\n":"<a href=\"{$firstLink}\">".$this->language->_('PageFirst')."</a>\n";
				$html .= "</li>\n<li class=\"pagenav-prev\">\n";
				$html .= ($this->page == 0)?"<span>".$this->language->_('PagePrev')."</span>\n":"<a href=\"{$prevLink}\">".$this->language->_('PagePrev')."</a>\n";
				$html .= "</li>\n";
				
				for ($i = 0, $j = 1; $i <= $listLimit; $i++, $j++) {
					if ($this->page > 14) {
						$i = $this->page - 14;
						$j = $i + 1;
					}
					$pageLink = $this->preparePageURL($i); 
					$html .= "<li class=\"pagenav-page";
					$html .= ($this->page == $i)?" current-page\"><span>{$j}</span>":"\"><a href=\"{$pageLink}\">{$j}</a>";
					$html .= "</li>\n";
				}
				
				$html .= "<li class=\"pagenav-next\">\n";
				$html .= ($this->page == $lastPageNo)?"<span>".$this->language->_('PageNext')."</span>\n":"<a href=\"{$nextLink}\">".$this->language->_('PageNext')."</a>\n";
				$html .= "</li>\n<li class=\"pagenav-last\">\n";
				$html .= ($this->page == $lastPageNo)?"<span>".$this->language->_('PageLast')."</span>\n":"<a href=\"{$lastLink}\">".$this->language->_('PageLast')."</a>\n";
				$html .= "</li>\n</ul>\n";
			}
			
			return $html;
		}
		*/
		
		/**
		 * Method to create relevant pagination link (AJAX)
		 * 
		 * @param int, $pageNo, page number
		 * @return string
		 */
		private function preparePageURL($pageNo,$uid,$tabid) {
			$baseUri = $this->request->getURI();
			
			$ajaxURL = "loadAJAX('type,task,act,userid,{$this->pageName}','apps,admin,adminDefault,{$uid},{$pageNo}','.bodyarea','initTabs',{$tabid});";
			
			return $ajaxURL;
		}
		
		/*
		 private function preparePageURL($pageNo) {
			$baseUri = $this->request->getURI();
			
			if (strpos($baseUri,'?') === false) {
				$pageLink = $baseUri . '?' . $this->pageName . '=' . $pageNo;
			} else {
				$tmpLinkArr = explode('?',$baseUri);
				$pURI = $tmpLinkArr[0];
				
				if (strpos($tmpLinkArr[1],'&') === false) {
					if (strpos($tmpLinkArr[1],$this->pageName) === false) {
						$pageLink = $pURI . '?' . $tmpLinkArr[1] . '&' . $this->pageName . '=' . $pageNo;
					} else {
						$pageLink = $pURI . '?' . $this->pageName . '=' . $pageNo;
					}
				} else {
					$tmpLinkArr1 = explode('&',$tmpLinkArr[1]);
					$i = 0;
					
					foreach ($tmpLinkArr1 as $tmpItem) {
						if (strpos($tmpLinkArr[1],$this->pageName) === false) {
							if ($i == 0) {
								$pageLink = $pURI . '?' . $tmpItem;
							} else {
								$pageLink .= $pURI . '&' . $tmpItem;
							}
						} else {
							if ($i == 0) {
								$pageLink = $pURI . '?' . $this->pageName . '=' . $pageNo;
							} else {
								$pageLink .= $pURI . '&' . $this->pageName . '=' . $pageNo;
							}
						}
					}
				}
			}
			
			return $pageLink;
		}
		*/
	}
?>