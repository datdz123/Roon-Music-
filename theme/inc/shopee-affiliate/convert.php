<?php
/**
 * Shopee conversion logic.
 *
 * @package roon
 */

if (! defined('ABSPATH')) {
	exit;
}

if (! defined('ROON_SHOPEE_AFF_OPTION')) {
	define('ROON_SHOPEE_AFF_OPTION', 'roon_shopee_aff_data');
}

/**
 * Get all Shopee option values.
 *
 * @return array<string, string>
 */
function roon_shopee_get_data()
{
	$defaults = array(
		'input_url'        => '',
		'sub_id'           => '',
		'cookie_endpoint'  => '',
		'aff_cookie'       => '',
		'service_url'      => 'http://127.0.0.1:3018/convert',
		'service_token'    => '',
		'output_aff_link'  => '',
		'original_url'     => '',
		'clean_url'        => '',
		'shopid'           => '',
		'itemid'           => '',
		'last_error'       => '',
		'last_converted_at'=> '',
	);

	$data = get_option(ROON_SHOPEE_AFF_OPTION, array());

	if (! is_array($data)) {
		return $defaults;
	}

	return wp_parse_args($data, $defaults);
}

/**
 * Persist Shopee conversion data.
 *
 * @param array<string, string> $data Data to store.
 * @return void
 */
function roon_shopee_save_data($data)
{
	if (! is_array($data)) {
		return;
	}

	$current = roon_shopee_get_data();
	$merged  = wp_parse_args($data, $current);

	update_option(ROON_SHOPEE_AFF_OPTION, $merged, false);

	if (function_exists('update_field')) {
		if (isset($merged['input_url'])) {
			update_field('input_url', $merged['input_url'], 'option');
		}
		if (isset($merged['sub_id'])) {
			update_field('sub_id', $merged['sub_id'], 'option');
		}
		if (isset($merged['cookie_endpoint'])) {
			update_field('cookie_endpoint', $merged['cookie_endpoint'], 'option');
		}
		if (isset($merged['aff_cookie'])) {
			update_field('aff_cookie', $merged['aff_cookie'], 'option');
		}
		if (isset($merged['service_url'])) {
			update_field('service_url', $merged['service_url'], 'option');
		}
		if (isset($merged['service_token'])) {
			update_field('service_token', $merged['service_token'], 'option');
		}
		if (isset($merged['output_aff_link'])) {
			update_field('output_aff_link', $merged['output_aff_link'], 'option');
		}
		if (isset($merged['original_url'])) {
			update_field('original_url', $merged['original_url'], 'option');
		}
		if (isset($merged['clean_url'])) {
			update_field('clean_url', $merged['clean_url'], 'option');
		}
		if (isset($merged['shopid'])) {
			update_field('shopid', $merged['shopid'], 'option');
		}
		if (isset($merged['itemid'])) {
			update_field('itemid', $merged['itemid'], 'option');
		}
	}
}

/**
 * Build affiliate link by calling Shopee endpoint with cookie session.
 *
 * @param string $clean_url Canonical Shopee product URL.
 * @param string $sub_id    Optional sub id.
 * @param string $endpoint  Endpoint to call.
 * @param string $cookie    Full cookie string from logged-in browser session.
 * @return string
 */
function roon_shopee_generate_via_cookie($clean_url, $sub_id, $endpoint, $cookie)
{
	if ('' === $endpoint || ! wp_http_validate_url($endpoint) || '' === $cookie) {
		return '';
	}

	$csrf_token = '';
	$af_ec_token = '';
	if (preg_match('/(?:^|;\s*)csrftoken=([^;]+)/i', $cookie, $csrf_matches)) {
		$csrf_token = rawurldecode((string) $csrf_matches[1]);
	}
	if (preg_match('/(?:^|;\s*)SPC_EC=([^;]+)/i', $cookie, $ec_matches)) {
		$af_ec_token = rawurldecode((string) $ec_matches[1]);
	}

	$request_args = array(
		'timeout' => 25,
		'headers' => array(
			'Content-Type' => 'application/json',
			'Cookie'       => $cookie,
			'Origin'       => 'https://affiliate.shopee.vn',
			'Referer'      => 'https://affiliate.shopee.vn/custom_link',
			'User-Agent'   => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
			'X-Requested-With' => 'XMLHttpRequest',
		),
		'body'    => wp_json_encode(
			array(
				'url'    => $clean_url,
				'sub_id' => $sub_id,
			)
		),
	);

	if ('' !== $csrf_token) {
		$request_args['headers']['Csrf-Token'] = $csrf_token;
		$request_args['headers']['X-CSRFToken'] = $csrf_token;
	}
	if ('' !== $af_ec_token) {
		$request_args['headers']['Af-Ac-Enc-Token'] = $af_ec_token;
	}

	$response = wp_remote_post($endpoint, $request_args);
	if (is_wp_error($response)) {
		return '';
	}

	$status = (int) wp_remote_retrieve_response_code($response);
	if ($status < 200 || $status >= 300) {
		return '';
	}

	$payload = json_decode(wp_remote_retrieve_body($response), true);
	if (! is_array($payload)) {
		return '';
	}

	$candidates = array(
		$payload['affiliate_link'] ?? '',
		$payload['link'] ?? '',
		$payload['data']['affiliate_link'] ?? '',
		$payload['data']['link'] ?? '',
		$payload['data']['short_link'] ?? '',
		$payload['data']['url'] ?? '',
	);

	foreach ($candidates as $candidate) {
		if (is_string($candidate) && '' !== trim($candidate)) {
			return trim($candidate);
		}
	}

	return '';
}

/**
 * Convert Shopee URL into affiliate link.
 *
 * @param string $url    Input URL.
 * @param string $sub_id Optional sub id.
 * @return array<string, string|bool>
 */
function convert_shopee_aff($url, $sub_id = '')
{
	$url    = is_string($url) ? trim($url) : '';
	$sub_id = is_string($sub_id) ? trim($sub_id) : '';

	if ('' === $url || ! wp_http_validate_url($url)) {
		return array(
			'success' => false,
			'error'   => 'Invalid URL.',
		);
	}

	$cache_key = 'roon_shopee_aff_' . md5($url . '|' . $sub_id);
	$cached    = get_transient($cache_key);

	if (is_array($cached) && ! empty($cached['success'])) {
		return $cached;
	}

	$resolved_url = roon_shopee_resolve_url($url);

	if ('' === $resolved_url) {
		return array(
			'success' => false,
			'error'   => 'Cannot resolve redirect URL.',
		);
	}

	$product = roon_shopee_extract_product($resolved_url);

	if (empty($product['shopid']) || empty($product['itemid'])) {
		return array(
			'success'       => false,
			'error'         => 'Cannot extract Shopee product ID.',
			'resolved_url'  => $resolved_url,
		);
	}

	$clean_url = sprintf(
		'https://shopee.vn/product/%s/%s',
		rawurlencode($product['shopid']),
		rawurlencode($product['itemid'])
	);

	$affiliate_link = roon_shopee_generate_affiliate_link($clean_url, $sub_id);
	if ('' === $affiliate_link) {
		$affiliate_link = $clean_url;
	}

	$result = array(
		'success'         => true,
		'original_url'    => $url,
		'resolved_url'    => $resolved_url,
		'clean_url'       => $clean_url,
		'shopid'          => (string) $product['shopid'],
		'itemid'          => (string) $product['itemid'],
		'affiliate_link'  => $affiliate_link,
		'sub_id'          => $sub_id,
	);

	set_transient($cache_key, $result, 12 * HOUR_IN_SECONDS);

	return $result;
}

/**
 * Resolve final URL by following redirects.
 *
 * @param string $url URL to resolve.
 * @return string
 */
function roon_shopee_resolve_url($url)
{
	$args = array(
		'timeout'     => 15,
		'redirection' => 5,
		'sslverify'   => false,
		'headers'     => array(
			'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
		),
	);

	$response = wp_remote_get($url, $args);

	if (is_wp_error($response)) {
		return '';
	}

	$http_response = wp_remote_retrieve_header($response, 'x-final-url');
	if (is_string($http_response) && '' !== trim($http_response)) {
		return trim($http_response);
	}

	if (isset($response['http_response']) && is_object($response['http_response']) && method_exists($response['http_response'], 'get_response_object')) {
		$response_object = $response['http_response']->get_response_object();
		if (is_object($response_object) && ! empty($response_object->url)) {
			return (string) $response_object->url;
		}
	}

	$status = (int) wp_remote_retrieve_response_code($response);
	if ($status >= 300 && $status < 400) {
		$location = wp_remote_retrieve_header($response, 'location');
		if (is_string($location) && '' !== trim($location)) {
			return trim($location);
		}
	}

	return $url;
}

/**
 * Extract shop and item IDs from known Shopee URL formats.
 *
 * @param string $url Resolved URL.
 * @return array{shopid:string,itemid:string}
 */
function roon_shopee_extract_product($url)
{
	$patterns = array(
		'#/(?:product|item)/(\d+)/(\d+)#i',
		'#/-i\.(\d+)\.(\d+)#i',
	);

	foreach ($patterns as $pattern) {
		if (preg_match($pattern, $url, $matches)) {
			return array(
				'shopid' => sanitize_text_field($matches[1]),
				'itemid' => sanitize_text_field($matches[2]),
			);
		}
	}

	return array(
		'shopid' => '',
		'itemid' => '',
	);
}

/**
 * Generate affiliate link via API/filter. Returns empty string on failure.
 *
 * @param string $clean_url Canonical product URL.
 * @param string $sub_id    Optional sub id.
 * @return string
 */
function roon_shopee_generate_affiliate_link($clean_url, $sub_id = '')
{
	$option_data   = roon_shopee_get_data();
	$cookie_endpoint = isset($option_data['cookie_endpoint']) ? trim((string) $option_data['cookie_endpoint']) : '';
	$aff_cookie      = isset($option_data['aff_cookie']) ? trim((string) $option_data['aff_cookie']) : '';
	$service_url   = isset($option_data['service_url']) ? trim((string) $option_data['service_url']) : '';
	$service_token = isset($option_data['service_token']) ? trim((string) $option_data['service_token']) : '';

	$cookie_aff_link = roon_shopee_generate_via_cookie($clean_url, $sub_id, $cookie_endpoint, $aff_cookie);
	if ('' !== $cookie_aff_link) {
		return $cookie_aff_link;
	}

	if ('' !== $service_url && wp_http_validate_url($service_url)) {
		$request_args = array(
			'timeout' => 45,
			'headers' => array(
				'Content-Type' => 'application/json',
			),
			'body'    => wp_json_encode(
				array(
					'url'    => $clean_url,
					'sub_id' => $sub_id,
				)
			),
		);

		if ('' !== $service_token) {
			$request_args['headers']['Authorization'] = 'Bearer ' . $service_token;
		}

		$response = wp_remote_post($service_url, $request_args);
		if (! is_wp_error($response)) {
			$status = (int) wp_remote_retrieve_response_code($response);
			if ($status >= 200 && $status < 300) {
				$payload = json_decode(wp_remote_retrieve_body($response), true);
				if (is_array($payload) && ! empty($payload['affiliate_link']) && is_string($payload['affiliate_link'])) {
					return trim($payload['affiliate_link']);
				}
			}
		}
	}

	$custom_aff_link = apply_filters('roon_shopee_affiliate_link', '', $clean_url, $sub_id);
	if (is_string($custom_aff_link) && '' !== trim($custom_aff_link)) {
		return trim($custom_aff_link);
	}

	$api_url = apply_filters('roon_shopee_aff_api_url', defined('ROON_SHOPEE_AFF_API_URL') ? (string) constant('ROON_SHOPEE_AFF_API_URL') : '');
	$api_key = apply_filters('roon_shopee_aff_api_key', defined('ROON_SHOPEE_AFF_API_KEY') ? (string) constant('ROON_SHOPEE_AFF_API_KEY') : '');

	if (! is_string($api_url) || '' === trim($api_url)) {
		return '';
	}

	$request_args = array(
		'timeout' => 20,
		'headers' => array(
			'Content-Type' => 'application/json',
		),
		'body'    => wp_json_encode(
			array(
				'url'    => $clean_url,
				'sub_id' => $sub_id,
			)
		),
	);

	if (is_string($api_key) && '' !== trim($api_key)) {
		$request_args['headers']['Authorization'] = 'Bearer ' . trim($api_key);
	}

	$response = wp_remote_post(trim($api_url), $request_args);
	if (is_wp_error($response)) {
		return '';
	}

	$status = (int) wp_remote_retrieve_response_code($response);
	if ($status < 200 || $status >= 300) {
		return '';
	}

	$payload = json_decode(wp_remote_retrieve_body($response), true);
	if (! is_array($payload)) {
		return '';
	}

	$candidates = array(
		$payload['affiliate_link'] ?? '',
		$payload['data']['affiliate_link'] ?? '',
		$payload['data']['link'] ?? '',
		$payload['link'] ?? '',
	);

	foreach ($candidates as $candidate) {
		if (is_string($candidate) && '' !== trim($candidate)) {
			return trim($candidate);
		}
	}

	return '';
}

/**
 * Convert and store option data.
 *
 * @param string $input_url Input URL.
 * @param string $sub_id    Optional sub id.
 * @return array<string, string|bool>
 */
function roon_shopee_convert_and_store($input_url, $sub_id = '')
{
	$input_url = is_string($input_url) ? trim($input_url) : '';
	$sub_id    = is_string($sub_id) ? trim($sub_id) : '';
	$current   = roon_shopee_get_data();

	if (
		'' !== $current['output_aff_link']
		&& $current['input_url'] === $input_url
		&& $current['sub_id'] === $sub_id
	) {
		return array(
			'success'         => true,
			'original_url'    => $current['original_url'],
			'resolved_url'    => $current['clean_url'],
			'clean_url'       => $current['clean_url'],
			'shopid'          => $current['shopid'],
			'itemid'          => $current['itemid'],
			'affiliate_link'  => $current['output_aff_link'],
			'sub_id'          => $current['sub_id'],
		);
	}

	$result = convert_shopee_aff($input_url, $sub_id);

	if (! empty($result['success'])) {
		roon_shopee_save_data(
			array(
				'input_url'         => $input_url,
				'sub_id'            => $sub_id,
				'output_aff_link'   => (string) $result['affiliate_link'],
				'original_url'      => (string) $result['original_url'],
				'clean_url'         => (string) $result['clean_url'],
				'shopid'            => (string) $result['shopid'],
				'itemid'            => (string) $result['itemid'],
				'last_error'        => '',
				'last_converted_at' => gmdate('c'),
			)
		);

		return $result;
	}

	$fallback = array(
		'input_url'         => $input_url,
		'sub_id'            => $sub_id,
		'output_aff_link'   => '',
		'original_url'      => $input_url,
		'clean_url'         => '',
		'shopid'            => '',
		'itemid'            => '',
		'last_error'        => isset($result['error']) ? (string) $result['error'] : 'Conversion failed.',
		'last_converted_at' => gmdate('c'),
	);

	roon_shopee_save_data($fallback);

	return $result;
}

/**
 * Auto-convert when ACF options page is saved.
 *
 * @param mixed $post_id ACF post id.
 * @return void
 */
function roon_shopee_acf_auto_convert($post_id)
{
	if ('options' !== $post_id && 'option' !== $post_id) {
		return;
	}

	if (! function_exists('get_field')) {
		return;
	}

	$input_url = get_field('input_url', 'option');
	$sub_id    = get_field('sub_id', 'option');

	$input_url = is_string($input_url) ? trim($input_url) : '';
	$sub_id    = is_string($sub_id) ? trim($sub_id) : '';

	if ('' === $input_url) {
		return;
	}

	roon_shopee_convert_and_store($input_url, $sub_id);
}
add_action('acf/save_post', 'roon_shopee_acf_auto_convert', 20);
