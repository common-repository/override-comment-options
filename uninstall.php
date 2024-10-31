<?php // uninstall remove options

if(!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN')) exit();

// delete post meta
delete_metadata('post', 0, 'override_close_comments_for_old_posts', '', true);