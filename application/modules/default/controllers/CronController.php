<?php

class CronController extends Zend_Controller_Action {

	public function init() {
		
	}

	public function indexAction() {
		
	}

	private function plato2sg($p) {
		return 1.00001 + 3.8661E-3 * $p + 1.3488E-5 * $p * $p + 4.3074E-8 * $p * $p * $p;
	}

	private function genuid() {
		$db = Zend_Registry::get("db");
		$uid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
		$select = $db->select()
				->from("users")
				->where("api_token = '" . $uid . "'");
		$result = $db->FetchAll($select);
		if (sizeof($result) == 0) {
			return $uid;
		} else {
			return $this->genuid();
		}
	}

	public function gentokensAction() {
		exit;
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$db = Zend_Registry::get("db");
		$select = $db->select()
				->from("users")
				->order("user_id ASC");
		$result = $db->FetchAll($select);
		foreach ($result as $key => $val) {
			$uuid = $this->genuid();
			$update = $db->update("users", array(
				"api_token" => $uuid
					), "user_id = '" . $val['user_id'] . "'");
		}
		echo "done";
	}

	public function fixplatoAction() {
		exit;
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$db = Zend_Registry::get("db");
		$select = $db->select()
				->from("beer_brew_sessions")
				->where("session_og > 1.610");
		$result = $db->fetchAll($select);
		foreach ($result as $key => $val) {
			$update = $db->update("beer_brew_sessions", array(
				"session_og" => $this->plato2sg($val['session_og'])
					), "session_id = '" . $val['session_id'] . "'");
		}
		$select = $db->select()
				->from("beer_brew_sessions")
				->where("session_fg > 1.160");
		$result = $db->fetchAll($select);
		foreach ($result as $key => $val) {
			$update = $db->update("beer_brew_sessions", array(
				"session_fg" => $this->plato2sg($val['session_fg'])
					), "session_id = '" . $val['session_id'] . "'");
		}
		echo "done";
		exit;
	}

	public function fixmailsubjectsAction() {
		exit;
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$db = Zend_Registry::get("db");
		$select = $db->select()
				->from("mail", array("mail_id", "mail_subject"))
				->order("mail_id DESC");
		$result = $db->fetchAll($select);
		foreach ($result as $key => $val) {
			$count = $result[$key]['count'] = substr_count($val['mail_subject'], 'Ats:');
			$plain = $result[$key]['plain'] = trim(str_replace("Ats:", "", $val['mail_subject']));
			if ($count > 0) {
				if ($count == 1) {
					$new_subject = $result[$key]['new_subject'] = "Ats: " . $plain;
				} else {
					$new_subject = $result[$key]['new_subject'] = "Ats[" . $count . "]: " . $plain;
				}
			} else {
				$new_subject = $result[$key]['new_subject'] = $plain;
			}
			$db->update("mail", array("mail_subject" => $new_subject), "mail_id = '" . $val['mail_id'] . "'");
		}
		echo "done.";
	}

	public function populatemwusersAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$db = Zend_Registry::get("db");
		$select = $db->select()
				->from("users");
		$users = $db->FetchAll($select);
		foreach ($users as $user) {
			$insert = $db->insert("wiki_user", array(
				"user_id" => $user['user_id'],
				"user_name" => $user['user_name'],
				"user_real_name" => $user['user_name'],
				"user_password" => ":A:" . $user['user_password'],
				"user_newpassword" => "",
				"user_newpass_time" => null,
				"user_email" => $user['user_email'],
				"user_touched" => str_replace(array(" ", "-", ":"), array("", "", ""), $user['user_lastlogin']),
				"user_token" => $user['user_password'],
				"user_email_authenticated" => null,
				"user_email_token" => null,
				"user_email_token_expires" => null,
				"user_registration" => str_replace(array(" ", "-", ":"), array("", "", ""), $user['user_created']),
				"user_editcount" => "0"
					));
		}
	}

	public function populaterecipecommentsAction() {
		exit;
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$db = Zend_Registry::get("db");
		$select = $db->select()
				->from("beer_recipes", array("beer_recipes.recipe_id"));
		$result = $db->fetchAll($select);
		foreach ($result as $key => $val) {
			$last = false;
			$count = 0;
			$select2 = $db->select()
					->from("beer_recipes_comments")
					->order("comment_date DESC")
					->where("comment_recipe = ?", $val['recipe_id']);
			$result2 = $db->FetchAll($select2);
			foreach ($result2 as $k => $v) {
				if ($last === false)
					$last = $v['comment_date'];
				$count++;
			}
			$db->update("beer_recipes", array(
				"recipe_total_comments" => $count,
				"recipe_last_comment_date" => $last,
					), "beer_recipes.recipe_id = '" . $val['recipe_id'] . "'");
		}
		echo "done.";
	}

	public function populaterecipesessionsAction() {
		exit;
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$db = Zend_Registry::get("db");
		$select = $db->select()
				->from("beer_recipes", array("beer_recipes.recipe_id"));
		$result = $db->fetchAll($select);
		foreach ($result as $key => $val) {
			$last = false;
			$count = 0;
			$select2 = $db->select()
					->from("beer_brew_sessions")
					->order("session_primarydate DESC")
					->where("session_recipe = ?", $val['recipe_id']);
			$result2 = $db->FetchAll($select2);
			foreach ($result2 as $k => $v) {
				if ($last === false)
					$last = $v['session_primarydate'];
				$count++;
			}
			$db->update("beer_recipes", array(
				"recipe_total_sessions" => $count,
				"recipe_last_session" => $last,
					), "beer_recipes.recipe_id = '" . $val['recipe_id'] . "'");
		}
		echo "done.";
	}

	public function populaterecipelikesAction() {
		exit;
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$db = Zend_Registry::get("db");
		$select = $db->select()
				->from("beer_recipes", array("beer_recipes.recipe_id"));
		$result = $db->fetchAll($select);
		foreach ($result as $key => $val) {
			$count = 0;
			$select2 = $db->select()
					->from("beer_recipes_favorites", array("COUNT(user_id) AS kiekis"))
					->order("favorite_date DESC")
					->where("recipe_id = ?", $val['recipe_id']);
			$result2 = $db->FetchRow($select2);
			$db->update("beer_recipes", array(
				"recipe_total_likes" => $result2['kiekis'],
					), "beer_recipes.recipe_id = '" . $val['recipe_id'] . "'");
		}
		echo "done.";
	}

	public function populaterecipeawardsAction() {
		exit;
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$db = Zend_Registry::get("db");
		$select = $db->select()
				->from("beer_recipes", array("beer_recipes.recipe_id"));
		$result = $db->fetchAll($select);
		foreach ($result as $key => $val) {
			$count = 0;
			$weight = 0;
			$select2 = $db->select()
					->from("beer_awards")
					->where("recipe_id = ?", $val['recipe_id']);
			$result2 = $db->FetchAll($select2);
			foreach ($result2 as $k => $v) {
				$count++;
				switch ($v['icon']) {
					case "bronze":
					case "cbronze":
						$weight = $weight + 2;
						break;
					case "silver":
					case "csilver":
						$weight = $weight + 4;
						break;
					case "gold":
					case "cgold":
						$weight = $weight + 8;
						break;
					case "common":
						$weight = $weight + 1;
						break;
				}
			}
			$db->update("beer_recipes", array(
				"recipe_total_awards" => $count,
				"recipe_total_awards_weight" => $weight,
					), "beer_recipes.recipe_id = '" . $val['recipe_id'] . "'");
		}
		echo "done.";
	}

	public function rssnewsAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$db = Zend_Registry::get("db");
		echo "<pre>";
		$url = "http://pipes.yahoo.com/pipes/pipe.run?_id=210aea0f65d951e98dc682a8136c4ee9&_render=rss";
		$feed = Zend_Feed::import($url);

		foreach ($feed as $item) {
			$select = $db->select()
					->from("activity", "id")
					->where("type = 'rss' AND rss_guid = '" . $item->guid() . "'");
			$result = $db->fetchAll($select);
			if (sizeof($result) == 0) {
				$db->insert("activity", array(
					"user_id" => "0",
					"item_id" => "0",
					"posted" => date("Y-m-d H:i:s"),
					"type" => "rss",
					"rss_title" => $item->title(),
					"rss_link" => $item->link(),
					"rss_description" => $item->description(),
					"rss_guid" => $item->guid(),
					"rss_posted" => date("Y-m-d H:i:s", strtotime($item->pubDate())),
				));
			}
		}
		echo "Done.";
	}

	public function favoritesAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$db = Zend_Registry::get("db");
		$select = "select count(distinct `beer_recipes_favorites`.`user_id`) AS `votes`,count(distinct `beer_recipes_comments`.`comment_id`) AS `comments`,count(distinct `beer_brew_sessions`.`session_id`) AS `sessions`,`beer_recipes`.`recipe_id` AS `recipe_id`,`beer_recipes`.`recipe_style` AS `recipe_style`,`beer_recipes`.`brewer_id` AS `brewer_id`,`beer_recipes`.`recipe_name` AS `recipe_name`,((count(distinct `beer_recipes_comments`.`comment_id`) + (count(distinct `beer_brew_sessions`.`session_id`) * 3)) + (count(distinct `beer_recipes_favorites`.`user_id`) * 5)) AS `total` from (`beer_recipes_comments` left join ((`beer_recipes` left join `beer_recipes_favorites` on((`beer_recipes_favorites`.`recipe_id` = `beer_recipes`.`recipe_id`))) left join `beer_brew_sessions` on((`beer_brew_sessions`.`session_recipe` = `beer_recipes`.`recipe_id`))) on((`beer_recipes_comments`.`comment_recipe` = `beer_recipes`.`recipe_id`))) where (`beer_recipes`.`recipe_publish` = '1') group by `beer_recipes`.`recipe_id` order by ((count(distinct `beer_recipes_comments`.`comment_id`) + (count(distinct `beer_brew_sessions`.`session_id`) * 3)) + (count(distinct `beer_recipes_favorites`.`user_id`) * 5)) desc";
		$result = $db->fetchAll($select);
		$db->query("TRUNCATE TABLE cache_fav_recipes");
		foreach ($result as $key => $val) {
			$db->insert("cache_fav_recipes", array(
				"votes" => $val['votes'],
				"comments" => $val['comments'],
				"sessions" => $val['sessions'],
				"recipe_id" => $val['recipe_id'],
				"recipe_style" => $val['recipe_style'],
				"brewer_id" => $val['brewer_id'],
				"recipe_name" => $val['recipe_name'],
				"total" => $val['total'],
				"updated" => date("Y-m-d H:i:s"),
			));
		}
		echo "Done.";
	}

	public function populateAction() {
		exit;
		set_time_limit(0);
		$tpl = array();
		$tpl['user_id'] = NULL;
		$tpl['item_id'] = NULL;
		$tpl['posted'] = NULL;
		$tpl['type'] = NULL;
		$tpl['forum_post_topic_id'] = NULL;
		$tpl['forum_post_post_position'] = NULL;
		$tpl['forum_post_post_text'] = NULL;
		$tpl['forum_post_topic_title'] = NULL;
		$tpl['article_article_title'] = NULL;
		$tpl['article_article_resume'] = NULL;
		$tpl['article_comment_article_title'] = NULL;
		$tpl['article_comment_comment_text'] = NULL;
		$tpl['article_comment_comment_article'] = NULL;
		$tpl['session_size'] = NULL;
		$tpl['session_recipe_name'] = NULL;
		$tpl['session_recipe_id'] = NULL;
		$tpl['session_recipe_publish'] = NULL;
		$tpl['event_name'] = NULL;
		$tpl['event_resume'] = NULL;
		$tpl['event_start'] = NULL;
		$tpl['event_registration_end'] = NULL;
		$tpl['event_type'] = NULL;
		$tpl['event_published'] = NULL;
		$tpl['recipe_name'] = NULL;
		$tpl['recipe_style_id'] = NULL;
		$tpl['recipe_style_name'] = NULL;
		$tpl['recipe_comment_recipe_name'] = NULL;
		$tpl['recipe_comment_text'] = NULL;
		$tpl['recipe_comment_recipe_id'] = NULL;
		$tpl['tweet_message'] = NULL;
		$tpl['tweet_link_url'] = NULL;
		$tpl['tweet_link_description'] = NULL;
		$tpl['tweet_link_title'] = NULL;
		$tpl['tweet_link_image'] = NULL;
		$tpl['idea_title'] = NULL;
		$tpl['idea_description'] = NULL;
		$tpl['idea_status'] = NULL;
		$tpl['idea_finishdate'] = NULL;
		$tpl['idea_comment_idea_title'] = NULL;
		$tpl['idea_comment_text'] = NULL;
		$tpl['idea_comment_idea_id'] = NULL;
		$tpl['user_name'] = NULL;
		$tpl['rss_title'] = NULL;
		$tpl['rss_link'] = NULL;
		$tpl['rss_description'] = NULL;
		$tpl['rss_guid'] = NULL;
		$tpl['rss_posted'] = NULL;
		$tpl['blog_title'] = NULL;
		$tpl['blog_content'] = NULL;
		$tpl['blog_link'] = NULL;

		$limit = 100000000000;

		echo "<pre>";
		$activity = array();
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$db = Zend_Registry::get("db");

		$url = "http://pipes.yahoo.com/pipes/pipe.run?_id=210aea0f65d951e98dc682a8136c4ee9&_render=rss";
		$feed = Zend_Feed::import($url);

		foreach ($feed as $item) {
			$temp = $tpl;
			$temp['type'] = "rss";
			$temp['user_id'] = "0";
			$temp['item_id'] = "0";
			$temp['posted'] = date("Y-m-d H:i:s", strtotime($item->pubDate()));
			$temp['rss_title'] = $item->title();
			$temp['rss_link'] = $item->link();
			$temp['rss_description'] = $item->description();
			$temp['rss_guid'] = $item->guid();
			$temp['rss_posted'] = date("Y-m-d H:i:s", strtotime($item->pubDate()));
			$activity[] = $temp;
		}

		// FORUMO ŽINUTĖS
		$select = $db->select()
				->from("bb_posts", array("post_id", "poster_id AS user_id", "post_text", "post_time", "post_position"))
				->join("bb_topics", "bb_posts.topic_id=bb_topics.topic_id", array("topic_title", "topic_id"))
				->order("bb_posts.post_time ASC")
				->limit($limit);
		$result = $db->fetchAll($select);
		foreach ($result as $key => $val) {
			$temp = $tpl;
			$temp['type'] = "forum_post";
			$temp['user_id'] = $val['user_id'];
			$temp['item_id'] = $val['post_id'];
			$temp['posted'] = $val['post_time'];
			$temp['forum_post_topic_id'] = $val['topic_id'];
			$temp['forum_post_post_position'] = $val['post_position'];
			$temp['forum_post_post_text'] = $val['post_text'];
			$temp['forum_post_topic_title'] = $val['topic_title'];
			$activity[] = $temp;
		}

		// STRAIPSNIAI
		$select = $db->select()
				->from("beer_articles", array("article_id", "article_resume", "article_title", "article_created"))
				->order("beer_articles.article_created ASC")
				->where("beer_articles.article_publish = ?", 1)
				->limit($limit);
		$result = $db->fetchAll($select);
		foreach ($result as $key => $val) {
			$temp = $tpl;
			$temp['type'] = "article";
			$temp['user_id'] = 0;
			$temp['item_id'] = $val['article_id'];
			$temp['posted'] = $val['article_created'];
			$temp['article_article_title'] = $val['article_title'];
			$temp['article_article_resume'] = $val['article_resume'];
			$activity[] = $temp;
		}

		// STRAIPSNIŲ KOMENTARAI
		$select = $db->select()
				->from("beer_articles_comments", array("comment_id", "comment_text", "comment_brewer AS user_id", "comment_date"))
				->join("beer_articles", "beer_articles_comments.comment_article = beer_articles.article_id", array("article_id", "article_title"))
				->order("beer_articles_comments.comment_date ASC")
				->where("beer_articles.article_publish = ?", 1)
				->limit($limit);
		$result = $db->fetchAll($select);
		foreach ($result as $key => $val) {
			$temp = $tpl;
			$temp['type'] = "article_comment";
			$temp['user_id'] = $val['user_id'];
			$temp['item_id'] = $val['comment_id'];
			$temp['posted'] = $val['comment_date'];
			$temp['article_comment_article_title'] = $val['article_title'];
			$temp['article_comment_comment_text'] = $val['comment_text'];
			$temp['article_comment_comment_article'] = $val['article_id'];
			$activity[] = $temp;
		}

		// VIRIMAI
		$select = $db->select()
				->from("beer_brew_sessions", array("session_id", "session_brewer AS user_id", "session_size", "session_primarydate"))
				->join("beer_recipes", "beer_brew_sessions.session_recipe = beer_recipes.recipe_id", array("recipe_id", "recipe_name", "recipe_publish"))
				->order("beer_brew_sessions.session_primarydate ASC")
				->where("beer_brew_sessions.session_primarydate != '0000-00-00' AND beer_brew_sessions.session_size != '0'")
				->limit($limit);
		$result = $db->fetchAll($select);
		foreach ($result as $key => $val) {
			$temp = $tpl;
			$temp['type'] = "session";
			$temp['user_id'] = $val['user_id'];
			$temp['item_id'] = $val['session_id'];
			$temp['posted'] = $val['session_primarydate'];
			$temp['session_size'] = $val['session_size'];
			$temp['session_recipe_name'] = $val['recipe_name'];
			$temp['session_recipe_id'] = $val['recipe_id'];
			$temp['session_recipe_publish'] = $val['recipe_publish'];
			$activity[] = $temp;
		}

		// ĮVYKIAI
		$select = $db->select()
				->from("beer_events", array("event_id", "event_name", "event_resume", "event_start", "event_registration_end", "event_type", "event_posted", "event_published"))
				->limit($limit);
		$result = $db->fetchAll($select);
		foreach ($result as $key => $val) {
			$temp = $tpl;
			$temp['type'] = "event";
			$temp['user_id'] = 0;
			$temp['item_id'] = $val['event_id'];
			if ($val['event_posted'] == "0000-00-00 00:00:00")
				$val['event_posted'] = $val['event_start'];
			$temp['posted'] = $val['event_posted'];
			$temp['event_name'] = $val['event_name'];
			$temp['event_resume'] = $val['event_resume'];
			$temp['event_start'] = $val['event_start'];
			$temp['event_registration_end'] = $val['event_registration_end'];
			$temp['event_type'] = $val['event_type'];
			$temp['event_published'] = $val['event_published'];
			$activity[] = $temp;
		}

		// RECEPTAI
		$select = $db->select()
				->from("beer_recipes", array("recipe_id", "recipe_name", "recipe_style", "brewer_id AS user_id", "recipe_created", "recipe_published"))
				->joinLeft("beer_styles", "beer_recipes.recipe_style=beer_styles.style_id", array("style_name"))
				->where("beer_recipes.recipe_publish = '1'")
				->limit($limit);
		$result = $db->fetchAll($select);
		foreach ($result as $key => $val) {
			$temp = $tpl;
			$temp['type'] = "recipe";
			$temp['user_id'] = $val['user_id'];
			$temp['item_id'] = $val['recipe_id'];
			if ($val['recipe_published'] == "0000-00-00 00:00:00")
				$val['recipe_published'] = $val['recipe_created'];
			$temp['posted'] = $val['recipe_published'];
			$temp['recipe_name'] = $val['recipe_name'];
			$temp['recipe_style_id'] = $val['recipe_style'];
			$temp['recipe_style_name'] = $val['style_name'];
			$activity[] = $temp;
		}

		// RECEPTŲ KOMENTARAI
		$select = $db->select()
				->from("beer_recipes_comments", array("comment_id", "comment_text", "comment_brewer AS user_id", "comment_date"))
				->join("beer_recipes", "beer_recipes_comments.comment_recipe = beer_recipes.recipe_id", array("recipe_id", "recipe_name"))
				->where("beer_recipes.recipe_publish = 1")
				->limit($limit);
		$result = $db->fetchAll($select);
		foreach ($result as $key => $val) {
			$temp = $tpl;
			$temp['type'] = "recipe_comment";
			$temp['user_id'] = $val['user_id'];
			$temp['item_id'] = $val['comment_id'];
			$temp['posted'] = $val['comment_date'];
			$temp['recipe_comment_recipe_name'] = $val['recipe_name'];
			$temp['recipe_comment_text'] = $val['comment_text'];
			$temp['recipe_comment_recipe_id'] = $val['recipe_id'];
			$activity[] = $temp;
		}

		// BURBULIATORIUS
		$select = $db->select()
				->from("beer_tweets", array("tweet_id", "tweet_message", "tweet_owner AS user_id", "tweet_link_url", "tweet_link_description", "tweet_link_title", "tweet_link_image", "tweet_date"))
				->limit($limit);
		$result = $db->fetchAll($select);
		foreach ($result as $key => $val) {
			$temp = $tpl;
			$temp['type'] = "tweet";
			$temp['user_id'] = $val['user_id'];
			$temp['item_id'] = $val['tweet_id'];
			$temp['posted'] = $val['tweet_date'];
			$temp['tweet_message'] = $val['tweet_message'];
			$temp['tweet_link_url'] = $val['tweet_link_url'];
			$temp['tweet_link_description'] = $val['tweet_link_description'];
			$temp['tweet_link_title'] = $val['tweet_link_title'];
			$temp['tweet_link_image'] = $val['tweet_link_image'];
			$activity[] = $temp;
		}

		// IDĖJŲ KOMENTARAI
		$select = $db->select()
				->from("idea_comments", array("comment_id", "comment_text", "user_id", "comment_date"))
				->join("idea_items", "idea_comments.idea_id = idea_items.idea_id", array("idea_id", "idea_title"))
				->limit($limit);
		$result = $db->fetchAll($select);
		foreach ($result as $key => $val) {
			$temp = $tpl;
			$temp['type'] = "idea_comment";
			$temp['user_id'] = $val['user_id'];
			$temp['item_id'] = $val['comment_id'];
			$temp['posted'] = $val['comment_date'];
			$temp['idea_comment_idea_title'] = $val['idea_title'];
			$temp['idea_comment_text'] = $val['comment_text'];
			$temp['idea_comment_idea_id'] = $val['idea_id'];
			$activity[] = $temp;
		}

		// IDĖJOS
		$select = $db->select()
				->from("idea_items", array("idea_id", "idea_title", "idea_description", "user_id", "idea_posted", "idea_finishdate", "idea_status"))
				->limit($limit);
		$result = $db->fetchAll($select);
		foreach ($result as $key => $val) {
			$temp = $tpl;
			$temp['type'] = "idea";
			$temp['user_id'] = $val['user_id'];
			$temp['item_id'] = $val['idea_id'];
			$temp['posted'] = $val['idea_posted'];
			$temp['idea_title'] = $val['idea_title'];
			$temp['idea_description'] = $val['idea_description'];
			$temp['idea_status'] = $val['idea_status'];
			$temp['idea_finishdate'] = $val['idea_finishdate'];
			$activity[] = $temp;
		}

		// NAUDOTOJAI
		$select = $db->select()
				->from("users", array("user_id", "user_name", "user_created"))
				->where("users.user_active = '1' AND users.user_enabled = '1'")
				->limit($limit);
		$result = $db->fetchAll($select);
		foreach ($result as $key => $val) {
			$temp = $tpl;
			$temp['type'] = "user";
			$temp['user_id'] = $val['user_id'];
			$temp['item_id'] = $val['user_id'];
			$temp['posted'] = $val['user_created'];
			$temp['user_name'] = $val['user_name'];
			$activity[] = $temp;
		}

		$sql = "TRUNCATE TABLE activity";
		$db->query($sql);

		foreach ($activity as $key => $val) {
			$db->insert("activity", $val);
		}
		echo "Done.";
		//print_r($activity);
	}

	public function fixsessionsAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$db = Zend_Registry::get("db");
		$select = $db->select()
				->from("activity")
				->where("type = 'session'");
		$result = $db->fetchAll($select);
		foreach ($result as $key => $val) {
			$select = $db->select()
					->from("beer_recipes", "recipe_name")
					->where("recipe_id = '" . $val['session_recipe_id'] . "'");
			$row = $db->fetchRow($select);
			$update = $db->update("activity", array("session_recipe_name" => $row['recipe_name']), "item_id = '" . $val['item_id'] . "'");
		}
		echo "Done";
	}

	public function fixideasAction() {
		exit;
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$db = Zend_Registry::get("db");
		$select = $db->select()
				->from("idea_items");
		$result = $db->fetchAll($select);
		foreach ($result as $key => $val) {
			$db->delete("idea_votes", array("user_id = '" . $val['user_id'] . "'", "idea_id = '" . $val['idea_id'] . "'"));
		}

		foreach ($result as $key => $val) {
			$select2 = $db->select()
					->from("idea_votes", "SUM(idea_votes.vote_value) as total")
					->where("idea_votes.idea_id = ?", $val['idea_id']);
			$result2 = $db->fetchRow($select2);
			$db->update("idea_items", array(
				"idea_vote_sum" => $result2['total']
					), "idea_items.idea_id = '" . $val['idea_id'] . "'");
		}

		echo "Done";
	}

	public function populatevotesAction() {
		exit;
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$db = Zend_Registry::get("db");
		$select = $db->select()
				->join("beer_recipes", "beer_recipes.recipe_id = beer_votes.recipe_id", array("recipe_name"))
				->from("beer_votes");
		$result = $db->fetchAll($select);
		foreach($result as $key=>$val){
			if ($val['rate_type'] == "simple"){
				$rate = $val['simple_vote'];
			} else {
				$rate = round(($val['aroma_vote'] + $val['appearance_vote'] + $val['taste_vote'] + $val['palate_vote'] + $val['overall_vote']) / 5, 1);
			}
			$db->insert("activity", array(
				"user_id" => $val['user_id'],
				"item_id" => $val['rate_id'],
				"posted" => $val['posted'],
				"type" => "vote",
				"vote_recipe_id" => $val['recipe_id'],
				"vote_recipe_name" => $val['recipe_name'],
				"vote_value" => $rate
			));
		}
		echo "Done";
	}
	
	public function getbonusesAction(){
		$db = Zend_Registry::get("db");
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$nick = "server";
		$password = "makatukasa";
		$api_url = "https://api.iv.lt/json.php?nick=$nick&password=$password&command=";
		$bonuses = json_decode(file_get_contents($api_url."account_bonuses&count=100"), true);
		foreach($bonuses as $item){
			$select = $db->select()
					->from("bonuses")
					->where("bo_id = ?", $item['bo_id']);
			$result = $db->fetchAll($select);
			if ($result == false){
				$insert = $db->insert("bonuses", array(
					"bo_id" => $item['bo_id'],
					"bo_created" => $item['bo_created'],
					"bo_amount" => $item['bo_amount'],
					"bo_description" => $item['bo_description'],
				));
			}
			
		}
		echo "done";
	}
}
