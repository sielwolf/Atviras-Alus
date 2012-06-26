﻿<?php

class IndexController extends Zend_Controller_Action {

	function init() {
		$storage = new Zend_Auth_Storage_Session();
		$user_info = $storage->read();
		$this->show_beta = false;
		if (isset($user_info->user_id) && !empty($user_info->user_id)){
			$db = Zend_Registry::get("db");
			$select = $db->select()
					->from("users_attributes")
					->where("users_attributes.user_id = ?", $user_info->user_id)
					->limit(1);
			$u_atribs= $db->fetchRow($select);
			if ($u_atribs['beta_tester'] == 1) {
				$this->show_beta = true;
				$this->_helper->layout()->setLayout('layoutnew');
			}
		}
	}
	public function enablebetaAction(){
		$storage = new Zend_Auth_Storage_Session();
		$user_info = $storage->read();
		if (isset($user_info->user_id) && !empty($user_info->user_id)){
			$db = Zend_Registry::get("db");
			$db->update("users_attributes", array("beta_tester" => "1"), array("user_id = '" . $user_info->user_id . "'"));
		}
		$this->_redirect('/');
	}
	public function disablebetaAction(){
		$storage = new Zend_Auth_Storage_Session();
		$user_info = $storage->read();
		if (isset($user_info->user_id) && !empty($user_info->user_id)){
			$db = Zend_Registry::get("db");
			$db->update("users_attributes", array("beta_tester" => "0"), array("user_id = '" . $user_info->user_id . "'"));
		}
		$this->_redirect('/');
	}

	public function indexAction() {
		// $this->view->addHelperPath(APPLICATION_PATH .'/default/helpers', 'View_Helper');
		$db = Zend_Registry::get("db");
		$frontendOptions = array(
			'lifetime' => 3600, // cache lifetime of 2 hours
			'automatic_serialization' => true
		);

		$backendOptions = array(
			'cache_dir' => './cache/' // Directory where to put the cache files
		);


		// getting a Zend_Cache_Core object
		$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
                
                //
                
                $frontendOptions2 = array(
			'lifetime' => (3600*24), // cache lifetime of 24 hours
			'automatic_serialization' => true
		);

		$backendOptions2 = array(
			'cache_dir' => './cache/' // Directory where to put the cache files
		);


		// getting a Zend_Cache_Core object
		$cache2 = Zend_Cache::factory('Core', 'File', $frontendOptions2, $backendOptions2);
		
		$storage = new Zend_Auth_Storage_Session();
		$user_info = $storage->read();
		if (isset($user_info->user_id) && !empty($user_info->user_id)){
			$select = $db->select()
					->from("idea_items", array("idea_id"))
					->where("idea_items.idea_status != 1 AND idea_items.idea_status != 2 AND idea_items.user_id != ?", $user_info->user_id);
			$ideas = $db->fetchAll($select);
			$ids = array();
			foreach($ideas as $key=>$val){
				$ids[] = $val['idea_id'];
			}
			$select = $db->select()
					->from("idea_votes", array("COUNT(idea_id) AS kiekis"))
					->where("idea_votes.idea_id IN (".implode(",", $ids).") AND idea_votes.user_id = '".$user_info->user_id."'");
			$voted = $db->fetchRow($select);
			$this->view->unvoted = sizeof($ids)-$voted['kiekis'];
		}
		if ($this->show_beta === true){
			$filter_type = $this->getRequest()->getParam("type");
			if (!isset($filter_type) || empty($filter_type)) $filter_type = "all";
			$this->view->filter_type = $filter_type;
			$this->_helper->layout->setLayout('layoutnew');
			$select = $db->select()
					->from("activity")
					->joinLeft("users", "users.user_id = activity.user_id", array("user_name", "MD5 (user_email) as email_hash"))
					->order("posted DESC")
					->limit(100);
			switch($filter_type){
				case "idea":
					$select->where("type = 'idea'");
				break;
				case "idea_comment":
					$select->where("type = 'idea_comment'");
				break;
				case "forum_post":
					$select->where("type = 'forum_post'");
				break;
				case "article":
					$select->where("type = 'article'");
				break;
				case "article_comment":
					$select->where("type = 'article_comment'");
				break;
				case "session":
					$select->where("type = 'session'");
				break;
				case "event":
					$select->where("type = 'event'");
				break;
				case "recipe":
					$select->where("type = 'recipe'");
				break;
				case "recipe_comment":
					$select->where("type = 'recipe_comment'");
				break;
				case "tweet":
					$select->where("type = 'tweet'");
				break;
				case "user":
					$select->where("type = 'user'");
				break;
				case "rss":
					$select->where("type = 'rss'");
				break;
				case "blog":
					$select->where("type = 'blog'");
				break;
			}
			$result = $db->fetchAll($select);
			$this->view->items = $result;

			$select = $db->select()
					->from("beer_events", array("*", "DATE_FORMAT(event_start, '%Y-%m-%d %H:%i') as event_start"))
					->where("event_registration_end >= CURDATE( )")
					->where("event_registration_end  != '0000-00-00'")
					->order("event_start");
			$this->view->events = $db->fetchAll($select);

			$select = $db->select()
					->from("beer_brew_sessions", array("SUM(beer_brew_sessions.session_size) AS beer_total", "COUNT(DISTINCT(beer_brew_sessions.session_brewer)) AS brewers_total"))
					->where("beer_brew_sessions.session_primarydate >= (curdate() - interval 30 day)");
			$this->view->total_brewed = $db->fetchRow($select);

			$this->_helper->viewRenderer('indexnew'); 
			
		} else {
			$select = $db->select()
					->from("users", array("user_name", "user_id", "user_email"))
					->joinLeft("VIEW_public_recipes", "VIEW_public_recipes.brewer_id=users.user_id", array("count" => "count(VIEW_public_recipes.recipe_id)"))
					->where("users.user_active = ?", '1')
					->group("users.user_id")
					->order("user_lastlogin DESC")
					->order("count DESC")
					->order("recipe_created DESC")
					->order("user_name ASC")
					->limit(12);
			$this->view->users = $db->fetchAll($select);

			$select = $db->select()
					->from("users", array("count" => "count(*)"))
					->where("users.user_active = ?", '1');
			$this->view->users_total = $db->fetchRow($select);
			$select = $db->select()
					->from("VIEW_public_recipes", array("recipe_id", "recipe_name", "recipe_published" => "MAX(recipe_published)"))
					->joinLeft("beer_styles", "VIEW_public_recipes.recipe_style=beer_styles.style_id", array("style_name"))
					->group("VIEW_public_recipes.recipe_id")
					->order("recipe_published DESC")
					->limit(12);
			$this->view->recipes = $db->fetchAll($select);
			$select = $db->select()
					->from("users", array("user_name", "user_id", "user_created" => "MAX(user_created)"))
					->where("user_active =?", '1')
					->group("users.user_id")
					->order("user_created DESC")
					->limit(1);
			$this->view->welcome = $db->fetchRow($select);
			$select = $db->select()
					->from("VIEW_public_recipes", array("count" => "count(*)"));
			$this->view->total_recipes = $db->fetchRow($select);
			$select = $db->select()
					->from("beer_recipes_comments", array("comment_id", "comment_brewer", "comment_recipe", "comment_date" => "MAX(comment_date)"))
					->join("VIEW_public_recipes", "VIEW_public_recipes.recipe_id=beer_recipes_comments.comment_recipe", array("recipe_name", "recipe_id"))
					->join("users", "beer_recipes_comments.comment_brewer=users.user_id", array("user_name", "user_email", "user_id"))
					->group("comment_id")
					->order("comment_date DESC")
					->limit(10);
			$this->view->comments = $db->fetchAll($select);
			$select = $db->select()
					->from("VIEW_public_recipes", array("recipe_id", "recipe_name"))
					->join("beer_brew_sessions", "VIEW_public_recipes.recipe_id=beer_brew_sessions.session_recipe")
					->join("users", "beer_brew_sessions.session_brewer=users.user_id", array("user_id", "user_name"))
					->where("session_primarydate <= CURDATE( )")
					->where("session_primarydate  >= DATE_SUB(CURDATE(),INTERVAL 2 MONTH)")
					->where("recipe_publish =?", '1')
					->where("session_caskingdate  = '0000-00-00' OR session_caskingdate > CURDATE( )")
					->order("session_primarydate DESC")
					->limit(10);
			$this->view->brew_session = $db->fetchAll($select);


			$select = $db->select()
					->from("beer_brew_sessions", array("SUM(beer_brew_sessions.session_size) AS beer_total", "COUNT(DISTINCT(beer_brew_sessions.session_brewer)) AS brewers_total"))
					->where("beer_brew_sessions.session_primarydate >= (curdate() - interval 30 day)");
			$this->view->total_brewed = $db->fetchRow($select);

			$select = $db->select()
					->from('cache_fav_recipes')
					->join("users", "users.user_id=cache_fav_recipes.brewer_id", array("user_name"))
					->limit(5)
					->order("total DESC");
			$this->view->fav_recipes = $db->fetchAll($select);

			$select = $db->select()
					->from("beer_articles")
					->joinLeft("beer_articles_comments", "beer_articles_comments.comment_article=beer_articles.article_id", array("COUNT(comment_id) as total"))
					->group("beer_articles.article_id")
					->where("article_cat =?", 1)
					->where("article_publish =?", '1')
					->order("article_modified DESC")
					->limit(5);
			$this->view->articles = $db->fetchAll($select);

			$select = $db->select()
					->from("bb_posts", array("post_id", "forum_id", "topic_id", "poster_id", "post_text", "post_time", "poster_ip", "post_status", "post_position"))
					->join("bb_topics", "bb_topics.topic_id = bb_posts.topic_id", array("topic_title"))
					->join("users", "bb_posts.poster_id = users.user_id", array("user_name", "MD5(user_email) AS email_hash"))
					->where("bb_posts.post_status = 0")
					->order("bb_posts.post_time DESC")
					->limit(7);
			;
			$this->view->posts = $db->fetchAll($select);
			if ($this->view->blogs = $cache->load('blog_latest')) {

			} else {
				$blog_ids = array(20, 19, 16, 18, 17, 15, 7, 11, 10, 8, 9, 2, 3, 4, 5, 6);
				foreach($blog_ids as $blog_id){
					$select_ar[$blog_id] = $db->select()
							->from("wp_".$blog_id."_posts", array("post_date", "post_modified", "ID", "post_author", "post_title", "comment_count", "guid", "post_content"))
							->join("users", "users.user_id=wp_".$blog_id."_posts.post_author", array("user_name"))
							->where("wp_".$blog_id."_posts.post_status = 'publish' AND wp_".$blog_id."_posts.post_type = 'post'");
				}
				$select = $db->select()->union($select_ar)->order("post_date DESC")->limit(5);
				$this->view->blogs = $db->fetchAll($select);
				$cache->save($this->view->blogs, 'blog_latest');
			}
			$select = $db->select()
					->from("beer_events", array("*", "DATE_FORMAT(event_start, '%Y-%m-%d %H:%i') as event_start"))
					->where("event_registration_end >= CURDATE( )")
					->where("event_registration_end  != '0000-00-00'")
					->order("event_start");
			$this->view->events = $db->fetchAll($select);
		}
	}

	public function sitemapAction() {
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		$sitemap = $this->view->navigation(new Zend_Navigation(new Zend_Config_Xml(APPLICATION_PATH . "/configs/defaultNavigation.xml", "nav")));
		$sitemap->sitemap()

				// ->setFormatOutput(true); 
				->setUseXmlDeclaration(true) // default is true
				->setServerUrl('http://atvirasalus.lt');

// default is to detect automatically
// print sitemap

		echo $sitemap->sitemap();
	}

	public function rssAction() {
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		$db = Zend_Registry::get("db");
		$feed = new Zend_Feed_Writer_Feed();
		$feed->setTitle('Atviro Alaus dienoraštis');
		$feed->setLink('http://www.atvirasalus.lt');
		$feed->setFeedLink('http://www.atvirasalus.lt/index/rss', 'rss');
		$feed->addAuthor(array(
			'name' => 'atvirasalus.lt',
			'email' => 'info@atvirasalus.lt',
			'uri' => 'http://www.atvirasalus.lt',
		));
		$feed->setId('http://www.atvirasalus.lt/index/rss');
		$feed->setDateModified(time());
		if (!isset($_GET['blogs_only'])) {
			$select = $db->select()
					->from("beer_articles")
					->where("article_publish = 1")
					->order("article_created DESC")
					->limit(10);
			$articles = $db->fetchAll($select);
			for ($i = 0; $i < count($articles); $i++) {
				$entry = $feed->createEntry();
				$entry->setTitle($articles[$i]['article_title']);
				$entry->setLink('http://www.atvirasalus.lt/content/read/1/' . $articles[$i]['article_id'] . urlencode($articles[$i]['article_title']));
				$entry->setId('http://www.atvirasalus.lt/content/read/1/' . $articles[$i]['article_id'] . urlencode($articles[$i]['article_title']));
				$entry->addAuthor(array('name' => 'Atviras Alus', 'email' => 'info@atvirasalus.lt', 'uri' => 'http://www.atvirasalus.lt'));
				$entry->setDateCreated(new Zend_Date($articles[$i]['article_created'], Zend_Date::ISO_8601));
				$entry->setDescription($articles[$i]['article_resume']);
				$entry->setContent($articles[$i]['article_text']);
				$feed->addEntry($entry);
			}
		}
		if (!isset($_GET['articles_only'])) {
			$blog_ids = array(20, 19, 16, 18, 17, 15, 7, 11, 10, 8, 9, 2, 3, 4, 5, 6);
			foreach($blog_ids as $blog_id){
				$select_ar[$blog_id] = $db->select()
						->from("wp_".$blog_id."_posts", array("post_date", "post_modified", "ID", "post_author", "post_title", "comment_count", "guid", "post_content"))
						->join("users", "users.user_id=wp_".$blog_id."_posts.post_author", array("user_name"))
						->where("wp_".$blog_id."_posts.post_status = 'publish' AND wp_".$blog_id."_posts.post_type = 'post'");
			}
			$select = $db->select()->union($select_ar)->order("post_date DESC")->limit(5);
			$blogs = $db->fetchAll($select);
			for ($i = 0; $i < count($blogs); $i++) {
				$entry = $feed->createEntry();
				$entry->setTitle($blogs[$i]['post_title']);
				$entry->setLink($blogs[$i]['guid']);
				$entry->setId($blogs[$i]['guid']);
				$entry->addAuthor(array('name' => $blogs[$i]['user_name'], 'email' => 'info@atvirasalus.lt', 'uri' => 'http://www.atvirasalus.lt'));
				// $entry->setDateModified($blogs[$i]['post_modified']);
				// $entry->setDateCreated($blogs[$i]['post_modified']);
				// $entry->setDateModified(new Zend_Date($blogs[$i]['post_modified'], Zend_Date::ISO_8601));
				$entry->setDateCreated(new Zend_Date($blogs[$i]['post_date'], Zend_Date::ISO_8601));
				$entry->setDescription($blogs[$i]['post_title']);
				$entry->setContent($blogs[$i]['post_content']);
				$feed->addEntry($entry);
			}
		}
		header("content-type: text/xml");
		print trim($feed->export('rss', true));
	}

	public function printRecipeAction() {
		$this->_helper->layout->setLayout('empty');

		if (isset($_GET['recipe_id'])) {
			$recipe = new Entities_Recipe(intval($_GET['recipe_id']));
			$this->view->print_data = array();
			if ($recipe_data = $recipe->getProperties()) {
				$data_mask = array('recipe_boiltime' => 'boil_time', 'recipe_comments' => 'comments', 'recipe_name' => 'beer_name', 'recipe_batch' => 'bash_size', 'recipe_efficiency' => 'efficiency', 'recipe_attenuation' => 'attenuation', 'style_name' => 'beer_style');
				foreach ($recipe_data as $key => $data) {
					if (isset($data_mask[$key])) {
						$this->view->print_data[$data_mask[$key]] = $data;
					} else {
						$this->view->print_data[$key] = $data;
					}
				}

				$malt_list = $recipe->getMalts();
				if (count($malt_list) > 0) {
					$this->view->print_data['malt_list'] = array();
					$this->view->print_data['malt_color'] = array();
					$this->view->print_data['malt_weight'] = array();
					foreach ($malt_list as $malt) {
						$this->view->print_data['malt_list'][] = $malt['malt_name'];
						$this->view->print_data['malt_color'][] = $malt['malt_ebc'];
						$this->view->print_data['malt_weight'][] = $malt['malt_weight'];
					}
				}
				$hop_list = $recipe->getHops();
				if (count($hop_list) > 0) {
					$this->view->print_data['hop_list'] = array();
					$this->view->print_data['hop_alpha'] = array();
					$this->view->print_data['hop_weight'] = array();
					$this->view->print_data['hop_time'] = array();
					foreach ($hop_list as $hop) {
						$this->view->print_data['hop_list'][] = $hop['hop_name'];
						$this->view->print_data['hop_alpha'][] = $hop['hop_alpha'];
						$this->view->print_data['hop_weight'][] = $hop['hop_weight'];
						$this->view->print_data['hop_time'][] = $hop['hop_time'];
					}
				}
				$yeast_list = $recipe->getYeasts();
				if (count($yeast_list) > 0) {
					$this->view->print_data['yeast_list'] = array();
					$this->view->print_data['yeast_weight'] = array();

					foreach ($yeast_list as $yeast) {
						$this->view->print_data['yeast_list'][] = $yeast['yeast_name'];
						$this->view->print_data['yeast_weight'][] = $yeast['yeast_weight'];
					}
				}
			}
		} else {
			$this->view->print_data = $_POST;
		}
	}

	public function calculusAction() {
		$recipe_id = $this->getRequest()->getParam("recipe");
		$this->view->data = array();
		if ($recipe_id > 0) {
			$db = Zend_Registry::get("db");
			$select = $db->select();
			$select->from("beer_recipes")
					->where("recipe_id = ?", $recipe_id);
			$this->view->data["recipe"] = $db->fetchRow($select);
			$select = $db->select();
			$select->from("beer_recipes_malt")
					->where("recipe_id = ?", $recipe_id)
					->order("malt_weight DESC");

			$this->view->data["malt"] = $db->fetchAll($select);
			$select = $db->select();
			$select->from("beer_recipes_hops")
					->where("recipe_id = ?", $recipe_id)
					->order("hop_time DESC");
			$this->view->data["hops"] = $db->fetchAll($select);
			$select = $db->select();
			$select->from("beer_recipes_yeast")
					->where("recipe_id = ?", $recipe_id);
			$this->view->data["yeast"] = $db->fetchAll($select);
		}
	}

	public function recipesAction() {
		
	}

	public function stylesAction() {
		
	}

}