<?php
/**
 * Shopee affiliate integration bootstrap.
 *
 * @package roon
 */

if (! defined('ABSPATH')) {
	exit;
}

require_once get_template_directory() . '/inc/shopee-affiliate/convert.php';
require_once get_template_directory() . '/inc/shopee-affiliate/admin.php';
require_once get_template_directory() . '/inc/shopee-affiliate/shortcode.php';
