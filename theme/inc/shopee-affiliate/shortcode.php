<?php
/**
 * Shopee affiliate shortcode.
 *
 * @package roon
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Render Shopee affiliate button shortcode.
 *
 * @param array<string, string> $atts Shortcode attributes.
 * @return string
 */
function roon_shopee_aff_shortcode($atts)
{
	$atts = shortcode_atts(
		array(
			'label' => __('Mua ngay', 'roon'),
		),
		$atts,
		'shopee_aff'
	);

	$data = roon_shopee_get_data();
	$link = isset($data['output_aff_link']) ? trim($data['output_aff_link']) : '';

	if ('' === $link && function_exists('get_field')) {
		$field_link = get_field('output_aff_link', 'option');
		$link       = is_string($field_link) ? trim($field_link) : '';
	}

	if ('' === $link) {
		return '';
	}

	return sprintf(
		'<a href="%1$s" target="_blank" rel="nofollow sponsored noopener noreferrer">%2$s</a>',
		esc_url($link),
		esc_html($atts['label'])
	);
}
add_shortcode('shopee_aff', 'roon_shopee_aff_shortcode');
