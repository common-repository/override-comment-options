<?php 
/*
	Plugin Name: Override Comment Options
	Plugin URI: https://perishablepress.com/wordpress-override-comment-options/
	Description: Enables override of "Automatically close comments on posts older than" option in the WP Discussion Settings.
	Tags: comments, discussion, options
	Author: Jeff Starr
	Author URI: https://plugin-planet.com/
	Donate link: https://monzillamedia.com/donate.html
	Contributors: specialk
	Requires at least: 4.6
	Tested up to: 6.7
	Stable tag: 2.5
	Version:    2.5
	Requires PHP: 5.6.20
	Text Domain: override-comment-options
	Domain Path: /languages
	License: GPL v2 or later
*/

/*
	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 
	2 of the License, or (at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	with this program. If not, visit: https://www.gnu.org/licenses/
	
	Copyright 2024 Monzilla Media. All rights reserved.
*/

/*
	Adapted from Override Comment Deadline by Scott Nelle, Union Street Media
	@ https://wordpress.org/plugins/override-comment-deadline/
*/

if (!defined('ABSPATH')) die();

function override_comment_options_add_meta() {
	
	if (get_option('close_comments_for_old_posts')) { // checks wp option
		
		add_meta_box('override-close-comments', 'Override Close Comments', 'override_comment_options_close_comments', 'post', 'normal', 'low');
		
	}
	
}
add_action('add_meta_boxes', 'override_comment_options_add_meta');

function override_comment_options_save_post($post_id) {
	
	if (!current_user_can('edit_post', $post_id)) return;
	
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return; 
	
	if (!isset($_POST['override_comment_options_close_comments']) || !wp_verify_nonce($_POST['override_comment_options_close_comments'], plugin_basename(__FILE__))) return;
	
	$value = (isset($_POST['override-close-comments']) && $_POST['override-close-comments'] == 1) ? 1 : 0;
	
	update_post_meta($post_id, 'override_close_comments_for_old_posts', $value); 
	
}
add_action('save_post', 'override_comment_options_save_post');

function override_comment_options_comment_check($open, $post_id) {
	
	if (get_option('default_comment_status') != 'open') return false;
	
	if (!get_option('close_comments_for_old_posts')) return $open;
	
	if (get_post_meta($post_id, 'override_close_comments_for_old_posts', true) == 1) return true;
	
	return $open;
	
}
add_filter('comments_open', 'override_comment_options_comment_check', 10, 2);

function override_comment_options_close_comments($post) {
	
	$check = get_post_meta($post->ID, 'override_close_comments_for_old_posts', true);
	
	$checked = ($check == 1) ? 'checked="checked"' : '';
	
	wp_nonce_field(plugin_basename(__FILE__), 'override_comment_options_close_comments');
	
	?>
	
	<p class="howto">
		<?php esc_html_e('Your Discussion Settings currently close comments on posts older than', 'override-comment-options'); ?> 
		<?php echo esc_html(get_option('close_comments_days_old')); ?> 
		<?php esc_html_e('days. To override the deadline for this post, check the box and save changes.', 'override-comment-options'); ?>
	</p>
	<label class="selectit" for="override-close-comments">
		<input type="checkbox" name="override-close-comments" id="override-close-comments" <?php echo $checked; ?> value="1"> 
		<?php esc_html_e('Leave Comments Open', 'override-comment-options'); ?>
	</label>
	<ul>
		<li>
			<a target="_blank" rel="noopener noreferrer" href="<?php echo admin_url('options-discussion.php'); ?>">
				<?php esc_html_e('Visit Discussion Settings', 'override-comment-options'); ?>&nbsp;&raquo;
			</a>
		</li>
		<li>
			<a target="_blank" rel="noopener noreferrer" href="https://wordpress.org/plugins/override-comment-options/">
				<?php esc_html_e('Visit Plugin Homepage', 'override-comment-options'); ?>&nbsp;&raquo;
			</a>
		</li>
	</ul>
	
	<?php
	
}