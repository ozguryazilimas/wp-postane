<?php
/*
Plugin Name: √ WP Missed Schedule
Plugin URI: http://slangji.wordpress.com/wp-missed-schedule/
Description: &#9733;&#9733;&#9733; This plugin try to fix <code>Missed Schedule</code> Future Posts Cron Job: find missed schedule posts that match this problem every 1 minute and it republish them correctly fixed 10 items per session for not use too many resources. All others Future Posts failed, will be solved on next sessions, until no longer exist (10 failed future posts every minute, 600 failed future posts every hour, 1 session every minute, 60 sessions every hour). <code>This plugin not decrase server performaces, why it check only wp_schedule_single_event and wp_publish_post Function Behavior, to no waste resources, and not phisical Cron Job!</code> The default 10 Failed Future Posts per session, was introduced for compatibility with default WordPress Items Feed Syndication. This plugin is designed for heavy use of Scheduled Future Posts and RSS Syndication (as FeedWordPress or WP-O-Matic), but also work well with a simple WordPress Blog or for use as a CMS. The configuration of this plugin is Automattic! and not need other actions from the Administrator except installing, uninstall or delete it, but, for completely automatization, try on put it on `/mu-plugin/` directoy, and also your activation is Automattic! starting from first installation. Try also WordPress 3.5 Regression Ticket <a href="http://core.trac.wordpress.org/ticket/22944" title="WordPress 3.5 Scheduled Posts Regression Ticket #22944">#22944</a>. Work under <a href="http://www.gnu.org/licenses/gpl-2.0.html" title"GPLv2 or later License compatible">GPLv2</a> or later License. <a href="http://www.gnu.org/prep/standards/standards.html" title"GNU style indentation coding standard compatible">GNU style</a> indentation coding standard compatible. | <a href="http://slangji.wordpress.com/donate/" title="Free Donation">Donate</a> | <a href="http://slangji.wordpress.com/contact/" title="Send Me Bug and Suggestionsor Make your own Contribute on it!">Contact</a> | <a href="http://profiles.wordpress.org/slangji" title="sLaNGjI's Profile @ WordPress.org">My Profile</a> | <a href="http://wordpress.org/extend/plugins/wp-overview-lite/" title="Show Dashboard Overview and Footer Memory Load Usage">WP Overview?</a> | <a href="http://wordpress.org/extend/plugins/wp-missed-schedule/" title="Fix Missed Scheduled Future Posts Cron Job">WP Missed Schedule?</a> | <a href="http://wordpress.org/extend/plugins/wp-admin-bar-removal/" title="Remove Admin Bar Frontend Backend User Profile and Code">Admin Bar Removal?</a> | <a href="http://wordpress.org/extend/plugins/wp-toolbar-removal/" title="Remove ToolBar Frontend Backend User Profile and Code">ToolBar Removal?</a> | <a href="http://wordpress.org/extend/plugins/wp-login-deindexing/" title="Total DeIndexing WordPress LogIn from all Search Engines">LogIn DeIndexing?</a> | <a href="http://wordpress.org/extend/plugins/wp-total-deindexing/" title="Total DeIndexing WordPress from all Search Engines">WP DeIndexing?</a> | <a href="http://wordpress.org/extend/plugins/wp-ie-enhancer-and-modernizer/" title="Enhancer and Modernizer IE Surfing Expirience">Enhancer IE Surfing?</a> | <a href="http://wordpress.org/extend/plugins/wp-wp-memory-db-indicator/" title="Memory Load Consumption db size Usage Indicator">Memory and db Indicator?</a> | <a href="http://wordpress.org/extend/plugins/wp-header-and-footer-log/" title="Insert Informational Text Log on Header and Footer when Plugin is Activated">Header and Footer Log?</a>
Version: 2013.0131.3333
Author: sLa
Author URI: http://slangji.wordpress.com/
Requires at least: 2.6
Tested up to: 3.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Indentation: GNU (style coding standard)
Indentation URI: http://www.gnu.org/prep/standards/standards.html
 *
 * DEVELOPMENT Release: Version 2013 Build 0509-BUGFIX Revision 0000-DEVELOPMENTAL
 *
 * [WP Missed Schedule](http://wordpress.org/extend/plugins/wp-missed-schedule/) Fix Missed Scheduled Future Posts Cron Job
 *
 * Copyright (C) 2008-2013 [sLaNGjI's](http://slangji.wordpress.com/slangjis/) (email: <slangji[at]gmail[dot]com>)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the [GNU General Public License](http://wordpress.org/about/gpl/)
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, see [GNU General Public Licenses](http://www.gnu.org/licenses/),
 * or write to the Free Software Foundation, Inc., 51 Franklin Street,
 * Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * √ THERMS
 *
 * This uses (or it parts) code derived from
 * wp-header-footer-log.php by sLa <slangji[at]gmail[dot]com>
 * according to the terms of the GNU General Public License version 2 (or later)
 *
 * According to the Terms of the GNU General Public License version 2 (or later) part of Copyright belongs to your own author and part belongs to their respective others authors:
 *
 * Copyright (C) 2008-2013 sLa (email: <slangji[at]gmail[dot]com>)
 *
 * √ DISCLAIMER
 *
 * The license under which the WordPress software is released is the GPLv2 (or later) from the
 * Free Software Foundation. A copy of the license is included with every copy of WordPress.
 *
 * Part of this license outlines requirements for derivative works, such as plugins or themes.
 * Derivatives of WordPress code inherit the GPL license.
 *
 * There is some legal grey area regarding what is considered a derivative work, but we feel
 * strongly that plugins and themes are derivative work and thus inherit the GPL license.
 *
 * The license for this software can be found on [Free Software Foundation](http://www.gnu.org/licenses/gpl-2.0.html) and as license.txt into this plugin package.
 *
 * The author of this plugin is available at any time, to make all changes, or corrections, to respect these specifications.
 *
 * √ VIOLATIONS
 *
 * [Violations of the GNU Licenses](http://www.gnu.org/licenses/gpl-violation.en.html)
 * The author of this plugin is available at any time, to make all changes, or corrections, to respect these specifications.
 *
 * √ GUIDELINES
 *
 * This software meet [Detailed Plugin Guidelines](http://wordpress.org/extend/plugins/about/guidelines/) paragraphs 1,4,10,12,13,16,17 quality requirements.
 * The author of this plugin is available at any time, to make all changes, or corrections, to respect these specifications.
 *
 * √ CODING
 *
 * This software implement [GNU style](http://www.gnu.org/prep/standards/standards.html) coding standard indentation.
 * The author of this plugin is available at any time, to make all changes, or corrections, to respect these specifications.
 *
 * √ VALIDATION
 *
 * This readme.txt rocks. Seriously. Flying colors. It meet the specifications according to WordPress [Readme Validator](http://wordpress.org/extend/plugins/about/validator/) directives.
 * The author of this plugin is available at any time, to make all changes, or corrections, to respect these specifications.
 */
/**
 * @package WP Missed Schedule
 * @subpackage WordPress PlugIn
 * @description Fix Missed Scheduled Future Posts Cron Job
 * @since 2.6.0
 * @version 2013.0131.3333
 * @status STABLE (release)
 * @author sLa
 * @license GPLv2 or later
 * @indentation GNU (style coding standard)
 * @keybit pUvCJMYW0xPVEMRbiJNpp2SbgCSBjvLyDPi1nhQuitbPoNPHDX8qoCnLknuWfBUGm
 * @keysum E86435203E39F9384F54F0AE25EF2689
 * @keytag 8a22836915262a9b0c04fa9101e61016
 */
?>
<?php
	if (!function_exists('add_action'))
		{
			header('HTTP/1.0 403 Forbidden');
			header('HTTP/1.1 403 Forbidden');
			exit();
		}
?>
<?php
	function wpms_log()
		{
			echo "\n<!--Plugin WP Missed Schedule 2013.0131.3333 Active - Key Tag: ".md5(md5("pUvCJMYW0xPVEMRbiJNpp2SbgCSBjvLyDPi1nhQuitbPoNPHDX8qoCnLknuWfBUGm"."E86435203E39F9384F54F0AE25EF2689"))."-->\n\n";
		}
	add_action('wp_head', 'wpms_log');
	add_action('wp_footer', 'wpms_log');
?>
<?php
	define('WPMS_DELAY', 1);
	define('WPMS_OPTION', 'wp_missed_schedule');
	function wpms_init()
		{
			$last = get_option(WPMS_OPTION, false);
			if (($last !== false) && ($last > (time() - (WPMS_DELAY * 60))))
					return;
			update_option(WPMS_OPTION, time());
			global $wpdb;
			$sql = $wpdb->prepare(
			"SELECT`ID`FROM`{$wpdb->posts}`"."WHERE("."((`post_date`>0)&&(`post_date`<=%s))OR"."((`post_date_gmt`>0)&&(`post_date_gmt`<=%s))".")AND`post_status`='future'LIMIT 10",
			current_time('mysql'),
			current_time('mysql', 1)
			);
			$scheduledIDs=$wpdb->get_col($sql);
			if (!count($scheduledIDs))
					return;
			foreach ($scheduledIDs as $scheduledID)
				{
					if (!$scheduledID)
							continue;
					wp_publish_post($scheduledID);
				}
		}
	add_action('init', 'wpms_init', 0);
	function wpms_cleanup()
		{
			delete_option(WPMS_OPTION);
		}
	register_deactivation_hook(__FILE__, 'wpms_cleanup', 0);
?>