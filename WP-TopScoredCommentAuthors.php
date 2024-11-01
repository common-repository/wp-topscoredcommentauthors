<?php
/*
Plugin Name: WP-TopScoredCommentAuthors
Plugin URI: http://blog.knut.me/
Description: This Widget shows your Top-Comment-Authors based on a score calculated from comment-age and frequency
Version: 0.4.1
Author: Knut Ahlers
Author URI: http://blog.knut.me/
*/
/*  Copyright 2009  Knut Ahlers  (email: knut@ahlers.me)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function WPTopScoredCommentAuthorsINIT() {
	
	// If there is no sidebar-widget-functionality skip everything. Then the
	// plugin will not work. Sorry.
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
  	return;

	// The stuff to print everything into your frontend.
	function TopScoredCommentAuthors($args) {
		
		global $current_user;
		get_currentuserinfo();
		
		extract($args);
		
		// Get the options from the database
		$options = (array) get_option('TopScoredCommentAuthors');
		
		// Get the results from database
		$res = TopScoredCommentAuthorsQuery($options, $options['count']);
		
		// Print the defined startup for the widget
		echo $before_widget . $before_title . $options['title'] . $after_title;
		echo "<ul>";
		
		foreach($res as $author) {
			echo "<li>";
			echo "<a ";
			echo "href=\"{$author['url']}\""; 
			echo $options['nofollow'] == '1' ? ' rel="nofollow"' : '';
			echo $options['target'] == '1' ? ' target="_blank"' : '';
			echo ($options['adminscore'] == '1' && $current_user->user_level == 10) ? " title=\"{$author['score']}\"" : '';
			echo ">{$author['name']}</a>";
			echo "</li>";
		}
		
		echo "</ul>";
		
		// Finish the widget
		echo $after_widget;
	}
	
	// The stuff to print everything into your frontend.
	function TopScoredCommentGrid($args) {
		
		global $current_user;
		get_currentuserinfo();
		
		extract($args);
		
		// Get the options from the database
		$options = (array) get_option('TopScoredCommentAuthors');
		
		// Get the results from database
		$res = TopScoredCommentAuthorsQuery($options, $options['gridcount']);
		
		// Print the defined startup for the widget
		echo $before_widget . $before_title . $options['title'] . $after_title;
		echo "<p style=\"text-align:center;\">";
		
		foreach($res as $author) {
			$gravatar = md5(strtolower($author['email']));
			echo "<a ";
			echo "href=\"{$author['url']}\""; 
			echo $options['nofollow'] == '1' ? ' rel="nofollow"' : '';
			echo $options['target'] == '1' ? ' target="_blank"' : '';
			echo ($options['adminscore'] == '1' && $current_user->user_level == 10) ? " title=\"{$author['score']}\"" : '';
			echo "><img src=\"http://www.gravatar.com/avatar.php?gravatar_id={$gravatar}&amp;s={$options['gridsize']}\" alt=\"{$author['name']}\" /></a>";
		}
		
		echo "</p>";
		
		// Finish the widget
		echo $after_widget;
	}
	
	function TopScoredCommentAuthorsQuery($options, $count) {
		global $wpdb;
		
		$query = "";
		$query .= "SELECT comment_author AS name, ";
		$query .= "		   MIN(comment_author_url) AS url, ";
		$query .= "		   MIN(comment_author_email) AS email, ";
		$query .= "		   SUM(1 / (DATEDIFF(NOW(), comment_date) / 30 + 1)) AS score ";
		//$query .= "      SUBSTRING_INDEX(comment_author_url, '/', 3) AS urlbegin ";
		$query .= "FROM {$wpdb->prefix}comments ";
		$query .= "WHERE comment_date > ADDDATE(NOW(), INTERVAL -{$options['range']} MONTH) ";
		$query .= "	AND comment_type = '' ";
		if($options['wpusers'] != '1') {
			$query .= "	AND user_id = 0 ";
		}
		$query .= "	AND comment_approved = 1 "; 
		$query .= "	AND comment_author_url LIKE 'http%' ";
		$query .= "GROUP BY comment_author_email ";
		$query .= "ORDER BY SUM(1 / (DATEDIFF(NOW(), comment_date) / 30 + 1)) DESC ";
		$query .= "LIMIT {$count}; ";
		
		$dbres = mysql_query($query);
		$result = array();
		while($ds = mysql_fetch_assoc($dbres)) {
			$result[] = $ds;
		}
		
		return $result;
		
	}
	
	function TopScoredCommentAuthors_hint() {
		_e('See settings page to customize your widgets.');
	}
	
	// Tell Wordpress what to do at every stage for this widget.
	register_sidebar_widget('WP-TopScoredCommentAuthors', 'TopScoredCommentAuthors');
	register_widget_control('WP-TopScoredCommentAuthors', 'TopScoredCommentAuthors_hint', 270, 300);      
	
	register_sidebar_widget('WP-TopScoredCommentGrid', 'TopScoredCommentGrid');
	register_widget_control('WP-TopScoredCommentGrid', 'TopScoredCommentAuthors_hint', 270, 300);

}

function WPTopScoredCommentAuthorsPluginLinks($links, $file) {
	$base = plugin_basename(__FILE__);
	if ($file == $base) {
		$links[] = '<a href="http://blog.knut.me/wp-donate/">' . __('Donate','wp-topscoredcommentauthors') . '</a>';
	}
	return $links;
}

// The stuff for the admin panel
function TopScoredCommentAuthors_control() {
	$options = $newoptions = get_option('TopScoredCommentAuthors');

	$plugin_dir = basename(dirname(__FILE__));                                                          
	load_plugin_textdomain( 'wp-topscoredcommentauthors', 'wp-content/plugins/' . $plugin_dir, $plugin_dir );
	
	// If there are new options set read them from the browser
	if($_POST['TopScoredCommentAuthors_submit']) {
		$newoptions['title'] = strip_tags(stripslashes($_POST['TopScoredCommentAuthors-title']));
		$newoptions['count'] = strip_tags(stripslashes($_POST['TopScoredCommentAuthors-count']));
		$newoptions['gridcount'] = strip_tags(stripslashes($_POST['TopScoredCommentAuthors-gridcount']));
		$newoptions['range'] = strip_tags(stripslashes($_POST['TopScoredCommentAuthors-range']));
		$newoptions['gridsize'] = strip_tags(stripslashes($_POST['TopScoredCommentAuthors-gridsize']));
		$newoptions['nofollow'] = isset($_POST['TopScoredCommentAuthors-nofollow']) ? '1' : '0';
		$newoptions['wpusers'] = isset($_POST['TopScoredCommentAuthors-wpusers']) ? '1' : '0';
		$newoptions['adminscore'] = isset($_POST['TopScoredCommentAuthors-adminscore']) ? '1' : '0';
		$newoptions['target'] = isset($_POST['TopScoredCommentAuthors-target']) ? '1' : '0';
	}
	
	// If the options changed write them to the wordpress database
	if ( $options != $newoptions ) {
	    $options = $newoptions;
	    update_option('TopScoredCommentAuthors', $options);
    }

	// Default value for the number of links shown
	if ($options['count'] == '')
		$options['count'] = '10';
	// Default value for the number of links shown
	if ($options['gridcount'] == '')
		$options['gridcount'] = '20';
		
	// Default value for the number of months
	if ($options['range'] == '')
		$options['range'] = '10';
	
	// Default value for the number of months
	if ($options['gridsize'] == '')
		$options['gridsize'] = '20';
		
	// Default value for the title
	if ($options['title'] == '')
		$options['title'] = __('Top Commentators', 'wp-topscoredcommentauthors');
	
	// Some HTML-Stuff for the administration panel.	
	?>
		<form action="?page=TSCA" method="post">
		<h3><?php _e('Settings for both widgets'); ?></h3>
		<p style="text-align:left">
			<label for="TopScoredCommentAuthors-title"><?php _e('Widget-Title:', 'wp-topscoredcommentauthors'); ?></label><br>
			<input style="width: 250px;" id="TopScoredCommentAuthors-title" name="TopScoredCommentAuthors-title" value="<?php echo wp_specialchars($options['title'], true); ?>" type="text" />
		</p>
		<p style="text-align:left">
			<label for="TopScoredCommentAuthors-range"><?php _e('Number of months to get commentators from:', 'wp-topscoredcommentauthors'); ?></label><br>
			<input style="width: 250px;" id="TopScoredCommentAuthors-range" name="TopScoredCommentAuthors-range" value="<?php echo wp_specialchars($options['range'], true); ?>" type="text" />
		</p>
		<p style="text-align:left">
			<input id="TopScoredCommentAuthors-wpusers" name="TopScoredCommentAuthors-wpusers" <?php echo ($options['wpusers'] == '1') ? 'checked="checked"' : '' ?> type="checkbox" value="1" />
			<label for="TopScoredCommentAuthors-wpusers"><?php _e('Include logged in commentators?', 'wp-topscoredcommentauthors'); ?></label>
		</p>
		<p style="text-align:left">
			<input id="TopScoredCommentAuthors-nofollow" name="TopScoredCommentAuthors-nofollow" <?php echo ($options['nofollow'] == '1') ? 'checked="checked"' : '' ?> type="checkbox" value="1" />
			<label for="TopScoredCommentAuthors-nofollow"><?php _e('Set links to NoFollow?', 'wp-topscoredcommentauthors'); ?></label>
		</p>
		<p style="text-align:left">
			<input id="TopScoredCommentAuthors-target" name="TopScoredCommentAuthors-target" <?php echo ($options['target'] == '1') ? 'checked="checked"' : '' ?> type="checkbox" value="1" />
			<label for="TopScoredCommentAuthors-target"><?php _e('Open links in new window?', 'wp-topscoredcommentauthors'); ?></label>
		</p>
		<p style="text-align:left">
			<input id="TopScoredCommentAuthors-adminscore" name="TopScoredCommentAuthors-adminscore" <?php echo ($options['adminscore'] == '1') ? 'checked="checked"' : '' ?> type="checkbox" value="1" />
			<label for="TopScoredCommentAuthors-adminscore"><?php _e('Show scores to admins?', 'wp-topscoredcommentauthors'); ?></label>
		</p>
		<h3><?php _e('Settings for the list widget'); ?></h3>
		<p style="text-align:left">
			<label for="TopScoredCommentAuthors-count"><?php _e('Number of commentators shown:', 'wp-topscoredcommentauthors'); ?></label><br>
			<input style="width: 250px;" id="TopScoredCommentAuthors-count" name="TopScoredCommentAuthors-count" value="<?php echo wp_specialchars($options['count'], true); ?>" type="text" />
		</p>
		<h3><?php _e('Settings for the grid widget'); ?></h3>
		<p style="text-align:left">
			<label for="TopScoredCommentAuthors-gridcount"><?php _e('Number of commentators shown:', 'wp-topscoredcommentauthors'); ?></label><br>
			<input style="width: 250px;" id="TopScoredCommentAuthors-gridcount" name="TopScoredCommentAuthors-gridcount" value="<?php echo wp_specialchars($options['gridcount'], true); ?>" type="text" />
		</p>
		<p style="text-align:left">
			<label for="TopScoredCommentAuthors-gridsize"><?php _e('Size of the gravatars (in px):', 'wp-topscoredcommentauthors'); ?></label><br>
			<input style="width: 250px;" id="TopScoredCommentAuthors-gridsize" name="TopScoredCommentAuthors-gridsize" value="<?php echo wp_specialchars($options['gridsize'], true); ?>" type="text" />
		</p>
		<p style="text-align:left">
			<input type="hidden" name="TopScoredCommentAuthors_submit" id="TopScoredCommentAuthors_submit" value="1" />
		</p>
		<p style="text-align:left">
			<input type="submit" value="<?php _e('Save'); ?>" />
		</p>
		</form>
		<div class="wrap">
			<h3>Please support me:</h3>
			<p>If you like this plugin please think about donating me a small amount of money by clicking on the PayPal-button below:</p>
			<p>
				<a href="http://blog.knut.me/wp-donate/">
					<img src="https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif" alt="<?php _e('Donate','wp-topscoredcommentauthors') ?>" />
				</a>
			</p>
			<p><a href="http://knut.in/twitter" target="_blank">Knut Ahlers</a> -
			<a href="http://www.kahlers.de" target="_blank">Software developer &amp; Webhoster</a> -
			<a href="http://blog.knut.me/" target="_blank">Blogger</a></p>
		</div>
	<?php
}

function TopScoredCommentAuthors_registermenu() {
	add_options_page(__('Top Scored Comment Authors Options'), __('Top Scored Comment Authors'), 8, 'TSCA', 'TopScoredCommentAuthors_control');
}

add_action('plugins_loaded', 'WPTopScoredCommentAuthorsINIT');
add_filter('plugin_row_meta', 'WPTopScoredCommentAuthorsPluginLinks', 10, 2);

if ( is_admin() ){ // admin actions
	add_action('admin_menu', 'TopScoredCommentAuthors_registermenu');
}

?>
