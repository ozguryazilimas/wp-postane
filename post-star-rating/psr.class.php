<?php
/**
 * Class that manages all the features of Post Star Rating Wordpress plugin
 *
 */
class PSR {
	var $_points = 0;
	var $_user;
	var $_momentLimit = 10;

	/**
	 * Create the database tables to support plugin behaviour.
	 *
	 * @param boolean $echo If true echoes messages to user
	 */
	function install($echo = false) {
		global $table_prefix, $wpdb;

		$table_name = $table_prefix . "psr_post";
		if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") !== $table_name) {
			$sql = "CREATE TABLE {$table_name} (
			  ID bigint(20) unsigned NOT NULL default '0',
			  votes int(10) unsigned NOT NULL default '0',
			  points int(10) unsigned NOT NULL default '0',
			  PRIMARY KEY (ID)
			);";

			require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
			dbDelta($sql);
			if ($echo) _e("Se ha creado la tabla de valoraci�n\n");
		} else {
			if ($echo) _e("La tabla de valoraci�n ya estaba creada\n");
		}

		$table_name = $table_prefix . "psr_user";
		if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") !== $table_name) {
			$sql = "CREATE TABLE {$table_name} (
			  user varchar(32) NOT NULL default '',
			  post bigint(20) unsigned NOT NULL default '0',
			  points int(10) unsigned NOT NULL default '0',
			  ip char(15) NOT NULL,
			  vote_date datetime NOT NULL,
			  PRIMARY KEY (`user`,post),
			  KEY vote_date (vote_date)
  		);";
			require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
			dbDelta($sql);
			if ($echo) _e("Se ha creado la tabla de puntuaciones\n");
		} elseif (!$wpdb->get_row("SHOW COLUMNS FROM {$table_name} LIKE 'vote_date'")) {
			$wpdb->query("ALTER TABLE {$table_name} ADD ip CHAR( 15 ) NOT NULL, ADD vote_date DATETIME NOT NULL");
			$wpdb->query("ALTER TABLE {$table_name} ADD INDEX (vote_date)");
			if ($echo) _e("Se ha actualizado la tabla de puntuaciones\n");
		} else {
			if ($echo) _e("La tabla de puntuaciones ya estaba creada\n");
		}
	}

	/**
	 * Get the html that shows the stars for voting
	 * If the user has already vote then it shows stars with puntuation. No voting is allowed
	 *
	 * @return string
	 */
	function getVotingStars() {
		global $id, $wpdb, $table_prefix;
		$rated = false;
		if (isset($this->_user)) {
			$user = $wpdb->escape($this->_user);
			$table_name = $table_prefix . "psr_user";
			$rated = (bool) $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE user='{$user}' AND post={$id}");
		}
		if (($this->_points > 0) && !$rated) {
			$user = $wpdb->escape($this->_user);
			$table_name = $table_prefix . "psr_user";
			$ip = $_SERVER['REMOTE_ADDR'];
			$vote_date = date('Y-m-d H:i:s');
			$wpdb->query("INSERT INTO {$table_name} (user, post, points, ip, vote_date) VALUES ('{$user}', {$id}, {$this->_points}, '{$ip}', '{$vote_date}')");
			$table_name = $table_prefix . "psr_post";
			if ($wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE ID={$id}")) {
				$wpdb->query("UPDATE {$table_name} SET votes=votes+1, points=points+{$this->_points} WHERE ID={$id};");
			} else {
				$wpdb->query("INSERT INTO {$table_name} (ID, votes, points) VALUES ({$id}, 1, {$this->_points});");
			}
			$rated = true;
//			$this->_setBestsOfMoment();
		}
		$data = $this->_getPoints();
		if ($rated || !isset($_COOKIE['wp_psr'])) {
			$html = $this->_drawStars($data->votes, $data->points);
		} else {
			$html = $this->_drawVotingStars($data->votes, $data->points);
		}
		return $html;
	}

	/**
	 * Get the html that shows the stars with puntuation.
	 *
	 * @return string
	 */
	function getStars() {
		$data = $this->_getPoints();
		return $this->_drawStars($data->votes, $data->points);
	}

	/**
	 * Get the points and votes of current post
	 *
	 * @return object
	 */
	function _getPoints() {
		global $id, $wpdb, $table_prefix;
		$table_name = $table_prefix . "psr_post";
		return $wpdb->get_row("SELECT votes, points FROM {$table_name} WHERE ID={$id}");
	}
	
	/**
	 * Draw the stars
	 *
	 * @param int $votes
	 * @param int $points
	 * @return string
	 */
	function _drawStars($votes, $points) {
		if ($votes > 0) {
			$rate = $points / $votes;
		} else {
			$rate = 0;
		}
		$html = '<div class="PSR_container"><div class="PSR_stars"> ';
		for ($i = 1; $i <= 5; ++$i) {
			if ($i <= $rate) {
				$class = 'PSR_full_star';
				$char = '*';
			} elseif ($i <= ($rate + .5)) {
				$class = 'PSR_half_star';
				$char = '&frac12;';
			} else {
				$class = 'PSR_no_star';
				$char = '&nbsp;';
			}
			$html .= '<span class="' . $class . '">' . $char . '</span> ';
		}
		$html .= '<span class="PSR_votes">' . (int) $votes . '</span> <span class="PSR_tvotes">' . __('votos') . '</span>';
		$html .= '</div></div>';
		return $html;
	}

	/**
	 * Draw the voting stars
	 *
	 * @param int $votes
	 * @param int $points
	 * @return string
	 */
	function _drawVotingStars($votes, $points) {
		global $id;
		if ($votes > 0) {
			$rate = $points / $votes;
		} else {
			$rate = 0;
		}
		$html = '<div class="PSR_container"><form id="PSR_form_' . $id . '" action="' . $_SERVER['PHP_SELF'] . '" method="post" class="PSR_stars" onmouseout="PSR_star_out(this)"> ';
		for ($i = 1; $i <= 5; ++$i) {
			if ($i <= $rate) {
				$class = 'PSR_full_voting_star';
				$char = '*';
			} elseif ($i <= ($rate + .5)) {
				$class = 'PSR_half_voting_star';
				$char = '&frac12;';
			} else {
				$class = 'PSR_no_voting_star';
				$char = '&nbsp;';
			}
			$html .= sprintf('<input type="radio" id="psr_star_%1$d_%2$d" class="star" name="psr_stars" value="%2$d" onclick="PSR_save_vote(%1$d,%2$d)" /><label class="%3$s" for="psr_star_%1$d_%2$d" onmouseover="PSR_star_over(this, %2$d)">%2$d</label> ', $id, $i, $class);
		}
		$html .= '<span class="PSR_votes">' . (int) $votes . '</span> <span class="PSR_tvotes">' . __('votos') . '</span> <span class="PSR_tvote">' . __('Vota!!') . '</span>';
		$html .= '<input type="hidden" name="p" value="' . $id . '" />';
		$html .= '<input type="submit" name="vote" value="' . __('Votar') . '" />';
		$html .= '</form></div>';
		return $html;
	}

//	function _updateScoreboard($data, $type) {
//		global $wpdb, $table_prefix;
//		if (is_array($data)) {
//			$table_name = $table_prefix . "psr_scoreboard";
//			$type = (int)$type;
//			$sql = "SELECT position, post FROM {$table_name} WHERE type={$type}";
//			$oldData = $wpdb->get_results($sql);
//			if (is_array($oldData)) {
//				foreach ($oldData as $row) {
//					$board[$row->post] = $row->position;
//				}
//				$wpdb->query("DELETE FROM {$table_name} WHERE type={$type}");
//			}
//			$i = 1;
//			$now = date('Y-m-d H:i:s');
//			foreach ($data as $row) {
//			  $trend = 'up';
//				if (isset($board[$row->post])) {
//					if ($i > $board[$row->post]) {
//						$trend = 'down';
//					} elseif ($i == $board[$row->post]) {
//						$trend = 'hold';
//					}
//				}
//				$sql = "INSERT INTO {$table_name} (type,position,post,votes,points,trend,score_date)
//					VALUES ({$type},{$i},{$row->post},{$row->votes},{$row->points},'{$trend}','{$now}')";
//				$wpdb->query($sql);
//				$i++;
//			}
//		}
//	}
//
	function getBestsOfMonth($month = null, $limit = 10) {
		global $wpdb, $table_prefix;
		$month = is_null($month) ? date('m') : (int)$month;
		$limit = (int)$limit;
		$table_name = $table_prefix . "psr_user";
		$sql = "SELECT post, COUNT(*) AS votes, SUM(points) AS points, AVG(points)
			FROM {$table_name}
			WHERE MONTH(vote_date)={$month} AND YEAR(vote_date)=YEAR(NOW())
			GROUP BY 1
			ORDER BY 4 DESC, 2 DESC
			LIMIT {$limit}";
		$data = $wpdb->get_results($sql);
		if (is_array($data)) {
			$html = '<ul class="PSR_month_scores">';
			foreach ($data AS $row) {
				$title = get_the_title($row->post);
				$html .= '<li><a class="post_title" href="' . get_permalink($row->post) . '" title="' . $title . '">' . $title . '</a> ' . $this->_drawStars($row->votes, $row->points) . '</li>';
			}
			$html .= '</ul>';
			return $html;
		}
	}

//	function getScoreBoard($type, $limit) {
//		global $wpdb, $table_prefix;
//		$table_name = $table_prefix . "psr_scoreboard";
//		$sql = "SELECT * FROM {$table_name}
//			WHERE type={$type}
//			ORDER BY position
//			LIMIT {$limit}";
//		$data = $wpdb->get_results($sql);
//		if (is_array($data)) {
//			$html = '<ol class="PSR_moment_scores">';
//			foreach ($data AS $row) {
//				$trends = array('down'=>__('baja'), 'up'=>__('sube'), 'hold'=>__('se mantiene'));
//				$html .= '<li>';
//				$html .= $this->_drawStars($row->votes, $row->points);
//				$html .= '<span class="trend_' . $row->trend . '" title="' . $trends[$row->trend] . '">(' . $trends[$row->trend] . ')</span>';
////				$html .= ' <span class="position">' . $row->position . '</span>';
//				$html .= ' <a class="post_title" href="' . get_permalink($row->post) . '">' . get_the_title($row->post) . '</a> ';
//				$html .= '</li>';
//			}
//			$html .= '</ol>';
//			return $html;
//		}
//	}

//	function _setBestsOfMoment() {
//		global $wpdb, $table_prefix;
//		$table_name = $table_prefix . "psr_user";
//		$sql = "SELECT post, COUNT(*) AS votes, SUM(points) AS points, AVG(points)
//			FROM {$table_name}
//			WHERE vote_date BETWEEN DATE_SUB(NOW(), INTERVAL 1 MONTH) AND NOW()
//			GROUP BY 1
//			ORDER BY 4 DESC, 2 DESC
//			LIMIT {$this->_momentLimit}";
//		$this->_updateScoreboard($wpdb->get_results($sql), 1);
//	}

	/**
	 * Get the best post of the moment. The moment is the time between now and 30 days before
	 *
	 * @return string
	 */
	function getBestsOfMoment($limit = 10) {
		global $wpdb, $table_prefix;
		$table_name = $table_prefix . "psr_user";
		$avg = (int)$wpdb->get_var("SELECT COUNT( * ) / COUNT( DISTINCT post ) AS votes FROM {$table_name} WHERE vote_date BETWEEN DATE_SUB(DATE_SUB(NOW(), INTERVAL 1 DAY), INTERVAL 1 MONTH) AND DATE_SUB(NOW(), INTERVAL 1 DAY)");
		$sql = "SELECT post, COUNT(*) AS votes, SUM(points) AS points, AVG(points)
			FROM {$table_name}
			WHERE vote_date BETWEEN DATE_SUB(DATE_SUB(NOW(), INTERVAL 1 DAY), INTERVAL 1 MONTH) AND DATE_SUB(NOW(), INTERVAL 1 DAY)
			GROUP BY 1
			HAVING votes > {$avg}
			ORDER BY 4 DESC, 2 DESC
			LIMIT {$limit}";
		$data = $wpdb->get_results($sql);
		$oldScore = array();
		if (is_array($data)) {
			$i = 1;
			foreach ($data AS $row) {
				$oldScore[$row->post] = $i++;
			}
		}
		$avg = (int)$wpdb->get_var("SELECT COUNT( * ) / COUNT( DISTINCT post ) AS votes FROM {$table_name} WHERE vote_date BETWEEN DATE_SUB(NOW(), INTERVAL 1 MONTH) AND NOW()");
		$sql = "SELECT post, COUNT(*) AS votes, SUM(points) AS points, AVG(points)
			FROM {$table_name}
			WHERE vote_date BETWEEN DATE_SUB(NOW(), INTERVAL 1 MONTH) AND NOW()
			GROUP BY 1
			HAVING votes > {$avg}
			ORDER BY 4 DESC, 2 DESC
			LIMIT {$limit}";
		return $this->_drawScoreBoard($wpdb->get_results($sql), $oldScore);
	}

	/**
	 * Draw a scoreboard from two arrays comparing positions to set trends
	 *
	 * @param array $score
	 * @param array $oldScore
	 * @return string
	 */
	function _drawScoreBoard($score, $oldScore = null) {
		if (is_array($score)) {
			$html = '<ol class="PSR_moment_scores">';
			$position = 1;
			$trends = array(__('Baja'), __('Sube'), __('Se mantiene'));
			foreach ($score AS $row) {
				$html .= '<li>';
				$html .= $this->_drawStars($row->votes, $row->points);
				if (is_array($oldScore)) {
					$trend = '<span class="trend_up" title="' . $trends[1] . '">(' . $trends[1] . ')</span>';
					if (isset($oldScore[$row->post])) {
						if ($position > $oldScore[$row->post]) {
							$trend = '<span class="trend_dw" title="' . $trends[0] . '">(' . $trends[0] . ')</span>';
						} elseif ($position == $oldScore[$row->post]) {
							$trend = '<span class="trend_eq" title="' . $trends[2] . '">(' . $trends[2] . ')</span>';
						}
					}
					$html .= $trend;
				}
//				$html .= ' <span class="position">' . $row->position . '</span>';
				$title = get_the_title($row->post);
				if (strlen($title) > 32) {
					$titleAbbr = substr($title, 0, 32) . '...';
				} else {
					$titleAbbr = $title;
				}
				$html .= ' <a class="post_title" href="' . get_permalink($row->post) . '" title="' . $title . '">' . $titleAbbr . '</a> ';
				$html .= '</li>';
				$position++;
			}
			$html .= '</ol>';
			return $html;
		}
	}

	/**
	 * Initialize the values.
	 * Get the puntuation from url and the user from the cookies.
	 * If no cookie exists generate a new user.
	 * Refresh the cookie to hold the value of user for 1 year
	 *
	 */
	function init() {
		if (isset($_COOKIE['wp_psr'])) {
			$this->_user = $_COOKIE['wp_psr'];
		} else {
		  if (!isset($this->_user)) {
		    srand((double)microtime()*1234567);
  			$this->_user = md5(microtime() . rand(1000, 90000000));
		  }
		}
		setcookie('wp_psr', $this->_user, time()+60*60*24*365, '/');
		if (isset($_REQUEST['psr_stars'])) {
			$points = (int) $_REQUEST['psr_stars'];
			if (($points > 0) && ($points <= 5)) {
				$this->_points = $points;
			}
		}
	}

	/**
	 * Echo CSS style to render the stars
	 *
	 */
	function css()
	{
		$home = get_settings('home');
		echo "<style type=\"text/css\">
		.PSR_stars {
		  height: 15px;
		  overflow: hidden;
		  padding: 0;
		  margin: 0;
		}
		* html .PSR_stars .star {
			display: block;
			position: absolute;
			height: 0;
			width: 0;
		}
		.PSR_stars input {
			display: none;
		}
		.PSR_no_star, .PSR_half_star, .PSR_full_star, .PSR_no_voting_star, .PSR_half_voting_star, .PSR_full_voting_star {
		  display: block;
		  float: left;
		  width: 17px;
		  height: 15px;
		  text-indent: -1000em;
		  text-align: left;
		  background-repeat: no-repeat;
		}
		.PSR_no_star {
		  background-image: url(" . $home . "/wp-content/plugins/post-star-rating/img/stars/no_star.gif);
		}
		.PSR_full_star {
		  background-image: url(" . $home . "/wp-content/plugins/post-star-rating/img/stars/full_star.gif);
		}
		.PSR_half_star {
		  background-image: url(" . $home . "/wp-content/plugins/post-star-rating/img/stars/half_star.gif);
		}
		.PSR_no_voting_star {
		  background-image: url(" . $home . "/wp-content/plugins/post-star-rating/img/stars/no_star.gif);
		}
		.PSR_full_voting_star {
		  background-image: url(" . $home . "/wp-content/plugins/post-star-rating/img/stars/full_voting_star.gif);
		}
		.PSR_half_voting_star {
		  background-image: url(" . $home . "/wp-content/plugins/post-star-rating/img/stars/half_voting_star.gif);
		}
		.PSR_votes {
			padding-left: .5em;
		}
		.PSR_moment_scores li {
			position: relative;
			height: 2.2ex;
		  list-style: decimal outside;
		}
		* html .PSR_moment_scores li {
		  list-style: none;
		}
		.PSR_moment_scores .trend_up, .PSR_moment_scores .trend_dw, .PSR_moment_scores .trend_eq {
		  display:block;
		  width: 14px;
		  height: 15px;
		  overflow: hidden;
		  text-indent: -100em;
		  float: left;
		  background-repeat: no-repeat;
			margin-right: .5em;
		}
		.PSR_moment_scores .trend_up {
		  background-image: url(" . $home . "/wp-content/plugins/post-star-rating/img/stars/up_arrow.gif);
		}
		.PSR_moment_scores .trend_dw {
		  background-image: url(" . $home . "/wp-content/plugins/post-star-rating/img/stars/dw_arrow.gif);
		}
		.PSR_moment_scores .trend_eq {
		  background-image: url(" . $home . "/wp-content/plugins/post-star-rating/img/stars/eq_arrow.gif);
		}
		.PSR_moment_scores .PSR_container {
			position: absolute;
			top: 0;
			right: 0;
			width: 21ex;
			text-align: right;
		}
		</style>\n";
	}

	/**
	 * Echo javascript that generates the onMouseOver behaviour
	 *
	 */
	function js() {
		echo "<script type=\"text/javascript\">
		<!--
		function PSR_star_over(obj, star_number) {
			var psr=obj.parentNode;
			var as=psr.getElementsByTagName('label');
			for (i=0;i<star_number;++i) {
				as[i].lastClass = as[i].className;
				as[i].className = 'PSR_full_star';
			}
			for (;i<as.length;++i) {
				as[i].lastClass = as[i].className;
//				as[i].className = 'PSR_no_star';
			}
		}
		function PSR_star_out(obj) {
			var as=obj.getElementsByTagName('label');
			for (i=0;i<as.length;++i) {
				if (as[i].lastClass) {
					as[i].className = as[i].lastClass;
				}
			}
		}
		function PSR_getHTTPObject() {
		  var xmlhttp;
		  /*@cc_on
		  @if (@_jscript_version >= 5)
		    try {
		      xmlhttp = new ActiveXObject('Msxml2.XMLHTTP');
		    } catch (e) {
		      try {
		        xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
		      } catch (E) {
		        xmlhttp = false;
		      }
		    }
		  @else
		  xmlhttp = false;
		  @end @*/
		  if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
		    try {
		      xmlhttp = new XMLHttpRequest();
		    } catch (e) {
		      xmlhttp = false;
		    }
		  }
		  return xmlhttp;
		}
		function PSR_save_vote(post, points) {
		  if (!PSR_isWorking) {
		  	PSR_current_post=post;
				PSR_http.open('GET', '" . get_settings('home') . "/wp-content/plugins/post-star-rating/psr-ajax-stars.php?p=' + PSR_current_post + '&psr_stars=' + points, true); 
				PSR_http.onreadystatechange = PSR_update_vote; 
			 	PSR_isWorking = true;
				PSR_http.send(null);
		  }
		}
		function PSR_update_vote() {
		  if (PSR_http.readyState == 4) {
		  	PSR_isWorking = false;
		  	var cont = document.getElementById('PSR_form_' + PSR_current_post).parentNode;
	    	cont.innerHTML=PSR_http.responseText;
		  }
		}
		PSR_current_post = null;
		PSR_http=PSR_getHTTPObject();
	  PSR_isWorking=false;
		//-->
		</script>\n";
	}

	/**
	 * Callback for Wordpress action wp_head.
	 * Echo CSS and Javascript
	 *
	 * @param unknown_type $unused
	 */
	function wp_head($unused) {
		$this->css();
		$this->js();
	}
}
?>