<?php

/**
 * roon functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package roon
 */
$random_ver = rand(1, 1000000000);
if (! defined('roon_VERSION')) {
	/*
	 * Set the theme’s version number.
	 *
	 * This is used primarily for cache busting. If you use `npm run bundle`
	 * to create your production build, the value below will be replaced in the
	 * generated zip file with a timestamp, converted to base 36.
	 */
	define('roon_VERSION', $random_ver);
}

if (! defined('roon_TYPOGRAPHY_CLASSES')) {
	/*
	 * Set Tailwind Typography classes for the front end, block editor and
	 * classic editor using the constant below.
	 *
	 * For the front end, these classes are added by the `roon_content_class`
	 * function. You will see that function used everywhere an `entry-content`
	 * or `page-content` class has been added to a wrapper element.
	 *
	 * For the block editor, these classes are converted to a JavaScript array
	 * and then used by the `./javascript/block-editor.js` file, which adds
	 * them to the appropriate elements in the block editor (and adds them
	 * again when they’re removed.)
	 *
	 * For the classic editor (and anything using TinyMCE, like Advanced Custom
	 * Fields), these classes are added to TinyMCE’s body class when it
	 * initializes.
	 */
	define(
		'roon_TYPOGRAPHY_CLASSES',
		'prose prose-neutral max-w-none prose-a:text-primary'
	);
}

if (! function_exists('roon_setup')) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function roon_setup()
	{
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on roon, use a find and replace
		 * to change 'roon' to the name of your theme in all the template files.
		 */
		load_theme_textdomain('roon', get_template_directory() . '/languages');

		// Add default posts and comments RSS feed links to head.
		add_theme_support('automatic-feed-links');

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support('title-tag');

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support('post-thumbnails');

		// This theme uses wp_nav_menu() in two locations.
		register_nav_menus(
			array(
				'menu-1' => __('Menu Chính', 'roon'),
				'menu-2' => __('Footer Menu', 'roon'),
			)
		);

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);

		// Add theme support for selective refresh for widgets.
		add_theme_support('customize-selective-refresh-widgets');

		// Add support for editor styles.
		add_theme_support('editor-styles');

		// Enqueue editor styles.
		add_editor_style('style-editor.css');
		add_editor_style('style-editor-extra.css');

		// Add support for responsive embedded content.
		add_theme_support('responsive-embeds');
		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support(
			'custom-logo',
			array(
				'height' => 250,
				'width' => 250,
				'flex-width' => true,
				'flex-height' => true,
			)
		);

		// Remove support for block templates.
		remove_theme_support('block-templates');
	}
endif;
add_action('after_setup_theme', 'roon_setup');

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function roon_widgets_init()
{
	register_sidebar(
		array(
			'name' => __('Footer', 'roon'),
			'id' => 'sidebar-1',
			'description' => __('Add widgets here to appear in your footer.', 'roon'),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget' => '</section>',
			'before_title' => '<h2 class="widget-title">',
			'after_title' => '</h2>',
		)
	);
}
add_action('widgets_init', 'roon_widgets_init');

/**
 * Enqueue scripts and styles.
 */
function roon_scripts()
{
	if (class_exists('WPCF7')) {
		wp_enqueue_style('roon-alert', get_template_directory_uri() . '/assets/alert/css/cf7simplepopup-core.css', array(), roon_VERSION);
		wp_enqueue_script('roon-jquery_alert', get_template_directory_uri() . '/assets/alert/js/cf7simplepopup-core.js', array(), roon_VERSION, true);
		wp_enqueue_script('roon-jquery_alert_main', get_template_directory_uri() . '/assets/alert/js/sweetalert.js', array(), roon_VERSION, true);
	}
	// wp_enqueue_style( 'roon-fancybox', get_template_directory_uri() . '/assets/libs/jquery.fancybox.css' );
	// wp_enqueue_style( 'roon-css-flickity', get_template_directory_uri() . '/assets/libs/flickity.min.css' );
	wp_enqueue_style('roon-css-font', get_template_directory_uri() . '/assets/fonts/font.css');
	wp_enqueue_style('roon-style', get_stylesheet_uri(), array(), roon_VERSION);

	// //JS
	wp_enqueue_script('jquery');
	// wp_enqueue_script( 'roon-js-flickity', get_template_directory_uri() . '/assets/libs/flickity.pkgd.js', array(), roon_VERSION, true );
	// wp_enqueue_script( 'roon-js-fancybox', get_template_directory_uri() . '/assets/libs/jquery.fancybox.js', array(), roon_VERSION, true );
	wp_enqueue_script('roon-script', get_template_directory_uri() . '/js/script.min.js', array(), roon_VERSION, true);

	wp_localize_script('roon-script', 'ajaxurl', array('ajaxurl' => admin_url('admin-ajax.php')));
	wp_localize_script(
		'roon-script',
		'roonPlayerSettings',
		array(
			'affiliateUrl'     => roon_get_shopee_aff_link(),
			'dailyAffiliateLimit' => roon_get_daily_ad_open_limit(),
		)
	);
	// Export home URL cho navigation từ single.php
	wp_add_inline_script( 'roon-script', 'window.roonHomeUrl = ' . json_encode( home_url( '/' ) ) . ';', 'before' );

	if (is_singular() && comments_open() && get_option('thread_comments')) {
		wp_enqueue_script('comment-reply');
	}
}
add_action('wp_enqueue_scripts', 'roon_scripts');


/**
 * Add the Tailwind Typography classes to TinyMCE.
 *
 * @param array $settings TinyMCE settings.
 * @return array
 */
function roon_tinymce_add_class($settings)
{
	$settings['body_class'] = roon_TYPOGRAPHY_CLASSES;
	return $settings;
}
add_filter('tiny_mce_before_init', 'roon_tinymce_add_class');

/**
 * Read the Shopee affiliate link from the shared options page.
 *
 * @return string
 */
function roon_get_shopee_aff_link()
{
	if (! function_exists('get_field')) {
		return '';
	}

	$link = get_field('shopee_aff_link', 'option');

	if (! is_string($link)) {
		return '';
	}

	$link = trim(wp_strip_all_tags($link));
	$link = preg_replace('/[\x00-\x1F\x7F\x{00A0}\x{200B}-\x{200D}\x{FEFF}]/u', '', $link);

	if (empty($link) || ! wp_http_validate_url($link)) {
		return '';
	}

	$parts = wp_parse_url($link);

	if (is_array($parts) && ! empty($parts['host']) && 's.shopee.vn' === strtolower((string) $parts['host'])) {
		$scheme   = ! empty($parts['scheme']) ? strtolower((string) $parts['scheme']) : 'https';
		$host     = (string) $parts['host'];
		$path     = '/' . ltrim((string) ($parts['path'] ?? ''), '/');
		$query    = isset($parts['query']) && '' !== (string) $parts['query'] ? '?' . (string) $parts['query'] : '';
		$fragment = isset($parts['fragment']) && '' !== (string) $parts['fragment'] ? '#' . (string) $parts['fragment'] : '';

		if ('/' !== $path) {
			$path = rtrim($path, '/');
		}

		$link = $scheme . '://' . $host . $path . $query . $fragment;
	}

	return $link;
}

/**
 * Read the daily ad-open limit from the shared options page.
 *
 * @return int
 */
function roon_get_daily_ad_open_limit()
{
	if (! function_exists('get_field')) {
		return 2;
	}

	$limit = get_field('daily_ad_open_limit', 'option');
	$limit = is_numeric($limit) ? (int) $limit : 2;

	return max(0, $limit);
}

/**
 * Read the Jellyfin API key from the shared options page.
 *
 * @return string
 */
function roon_get_jellyfin_api_key()
{
	if (! function_exists('get_field')) {
		return '';
	}

	$api_key = get_field('jellyfin_api_key', 'option');

	return is_string($api_key) ? trim($api_key) : '';
}

/**
 * Read the Jellyfin server URL from the shared options page.
 *
 * @return string
 */
function roon_get_jellyfin_server_url()
{
	if (! function_exists('get_field')) {
		return '';
	}

	$server_url = get_field('jellyfin_server_url', 'option');

	if (! is_string($server_url)) {
		return '';
	}

	return untrailingslashit(trim($server_url));
}

/**
 * Build a same-origin HTTPS URL that proxies Jellyfin audio through WordPress.
 *
 * @param string $item_id Jellyfin audio item ID.
 * @return string
 */
function roon_get_jellyfin_proxy_stream_url($item_id)
{
	$item_id = is_string($item_id) ? trim($item_id) : '';

	if ('' === $item_id) {
		return '';
	}

	return add_query_arg(
		'roon_stream',
		rawurlencode($item_id),
		home_url('/')
	);
}

/**
 * Proxy Jellyfin audio through the current site so browsers can play it over HTTPS.
 *
 * @return void
 */
function roon_maybe_proxy_jellyfin_stream()
{
	if (! isset($_GET['roon_stream'])) {
		return;
	}

	$item_id = sanitize_text_field(wp_unslash($_GET['roon_stream']));

	if ('' === $item_id) {
		status_header(400);
		exit('Missing stream id.');
	}

	roon_proxy_jellyfin_stream($item_id);
}
add_action('template_redirect', 'roon_maybe_proxy_jellyfin_stream', 0);

/**
 * Stream a Jellyfin track to the browser while forwarding range requests.
 *
 * @param string $item_id Jellyfin audio item ID.
 * @return void
 */
function roon_proxy_jellyfin_stream($item_id)
{
	$server_url = roon_get_jellyfin_server_url();
	$api_key    = roon_get_jellyfin_api_key();

	if ('' === $server_url || '' === $api_key) {
		status_header(500);
		exit('Jellyfin is not configured.');
	}

	if (! function_exists('curl_init')) {
		status_header(500);
		exit('cURL is required for audio proxying.');
	}

	$remote_url = add_query_arg(
		array(
			'static'  => 'true',
			'api_key' => $api_key,
		),
		$server_url . '/Audio/' . rawurlencode($item_id) . '/stream'
	);

	while (ob_get_level() > 0) {
		ob_end_clean();
	}

	$method          = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
	$request_headers = array();

	if (! empty($_SERVER['HTTP_RANGE'])) {
		$request_headers[] = 'Range: ' . trim(wp_unslash($_SERVER['HTTP_RANGE']));
	}

	if (! empty($_SERVER['HTTP_IF_RANGE'])) {
		$request_headers[] = 'If-Range: ' . trim(wp_unslash($_SERVER['HTTP_IF_RANGE']));
	}

	$curl = curl_init($remote_url);

	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($curl, CURLOPT_TIMEOUT, 0);
	curl_setopt($curl, CURLOPT_BUFFERSIZE, 65536);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

	if ('HEAD' === $method) {
		curl_setopt($curl, CURLOPT_NOBODY, true);
	}

	$allowed_headers = array(
		'content-type',
		'content-length',
		'content-range',
		'accept-ranges',
		'cache-control',
		'etag',
		'last-modified',
		'content-disposition',
	);

	curl_setopt(
		$curl,
		CURLOPT_HEADERFUNCTION,
		static function ($curl_handle, $header_line) use ($allowed_headers) {
			$length = strlen($header_line);
			$header = trim($header_line);

			if ('' === $header) {
				return $length;
			}

			if (preg_match('#^HTTP/\S+\s+(\d{3})#', $header, $matches)) {
				status_header((int) $matches[1]);
				return $length;
			}

			$parts = explode(':', $header, 2);
			if (2 !== count($parts)) {
				return $length;
			}

			$name  = strtolower(trim($parts[0]));
			$value = trim($parts[1]);

			if (in_array($name, $allowed_headers, true)) {
				header($parts[0] . ': ' . $value, true);
			}

			return $length;
		}
	);

	if ('HEAD' !== $method) {
		curl_setopt(
			$curl,
			CURLOPT_WRITEFUNCTION,
			static function ($curl_handle, $chunk) {
				echo $chunk;
				if (function_exists('flush')) {
					flush();
				}

				return strlen($chunk);
			}
		);
	}

	$success = curl_exec($curl);
	$error   = curl_error($curl);

	if (false === $success) {
		if (! headers_sent()) {
			status_header(502);
			header('Content-Type: text/plain; charset=utf-8');
		}
		echo 'Audio proxy failed: ' . $error;
	}

	curl_close($curl);
	exit;
}

/**
 * Normalize track URLs from ACF/Jellyfin into download + stream URLs.
 *
 * For non-Jellyfin URLs (for example local uploaded .m4a files), stream_url
 * falls back to the direct download URL so the HTML5 audio element can play it.
 *
 * @param string|array<string,mixed> $download_url Raw track file value from ACF.
 * @return array{download_url:string,stream_url:string,item_id:string,base_url:string}
 */
function roon_get_jellyfin_track_urls($download_url)
{
	$result = array(
		'download_url' => '',
		'stream_url'   => '',
		'item_id'      => '',
		'base_url'     => '',
	);

	if (is_array($download_url)) {
		if (! empty($download_url['url']) && is_string($download_url['url'])) {
			$download_url = $download_url['url'];
		} elseif (! empty($download_url['link']) && is_string($download_url['link'])) {
			$download_url = $download_url['link'];
		} elseif (! empty($download_url['ID'])) {
			$download_url = (string) $download_url['ID'];
		} elseif (! empty($download_url['id'])) {
			$download_url = (string) $download_url['id'];
		} else {
			$download_url = '';
		}
	}

	if (is_int($download_url) || (is_string($download_url) && ctype_digit($download_url))) {
		$attachment_url = wp_get_attachment_url((int) $download_url);
		$download_url   = is_string($attachment_url) ? $attachment_url : '';
	}

	if (! is_string($download_url)) {
		$download_url = '';
	}

	$download_url = trim($download_url);

	if ('' !== $download_url) {
		if (0 === strpos($download_url, '//')) {
			$download_url = (is_ssl() ? 'https:' : 'http:') . $download_url;
		} elseif (0 === strpos($download_url, '/')) {
			$download_url = home_url($download_url);
		} elseif (false === strpos($download_url, '://') && false === strpos($download_url, 'data:')) {
			$download_url = home_url('/' . ltrim($download_url, '/'));
		}
	}

	if ('' === $download_url) {
		return $result;
	}

	$result['download_url'] = $download_url;
	$result['stream_url']   = $download_url;

	$parts = wp_parse_url($download_url);

	if (empty($parts['scheme']) || empty($parts['host']) || empty($parts['path'])) {
		return $result;
	}

	$path = trim($parts['path'], '/');

	if (! preg_match('#^Items/([^/]+)/Download/?$#i', $path, $matches)) {
		return $result;
	}

	$item_id = sanitize_text_field($matches[1]);

	if ('' === $item_id) {
		return $result;
	}

	$base_url = $parts['scheme'] . '://' . $parts['host'];

	if (! empty($parts['port'])) {
		$base_url .= ':' . (int) $parts['port'];
	}

	$result['item_id']  = $item_id;
	$result['base_url'] = $base_url;

	$query_args = array(
		'static' => 'true',
	);

	$api_key = roon_get_jellyfin_api_key();

	if ('' !== $api_key) {
		$query_args['api_key'] = $api_key;
	}

	$result['stream_url'] = roon_get_jellyfin_proxy_stream_url($item_id);

	return $result;
}

/**
 * Convert Jellyfin ticks to a human readable duration.
 *
 * @param int|string $runtime_ticks Jellyfin RunTimeTicks.
 * @return string
 */
function roon_format_jellyfin_duration($runtime_ticks)
{
	$seconds = (int) floor((int) $runtime_ticks / 10000000);

	if ($seconds <= 0) {
		return '--:--';
	}

	$hours   = floor($seconds / HOUR_IN_SECONDS);
	$minutes = floor(($seconds % HOUR_IN_SECONDS) / MINUTE_IN_SECONDS);
	$secs    = $seconds % MINUTE_IN_SECONDS;

	if ($hours > 0) {
		return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
	}

	return sprintf('%d:%02d', $minutes, $secs);
}

/**
 * Fetch album tracks directly from Jellyfin using an album item ID.
 *
 * @param string $album_id Jellyfin album item ID.
 * @return array<int, array<string, mixed>>
 */
function roon_get_jellyfin_album_tracks($album_id)
{
	$album_id   = is_string($album_id) ? trim($album_id) : '';
	$server_url = roon_get_jellyfin_server_url();
	$api_key    = roon_get_jellyfin_api_key();

	if ('' === $album_id || '' === $server_url || '' === $api_key) {
		return array();
	}

	$cache_key = 'roon_jellyfin_album_' . md5($server_url . '|' . $album_id);
	$cached    = get_transient($cache_key);

	if (is_array($cached)) {
		return $cached;
	}

	$request_url = add_query_arg(
		array(
			'ParentId'         => $album_id,
			'IncludeItemTypes' => 'Audio',
			'Recursive'        => 'true',
			'SortBy'           => 'ParentIndexNumber,IndexNumber,SortName',
			'Fields'           => 'RunTimeTicks,ParentIndexNumber,IndexNumber',
			'api_key'          => $api_key,
		),
		$server_url . '/Items'
	);

	$response = wp_remote_get(
		$request_url,
		array(
			'timeout' => 15,
		)
	);

	if (is_wp_error($response)) {
		return array();
	}

	$status_code = wp_remote_retrieve_response_code($response);

	if (200 !== (int) $status_code) {
		return array();
	}

	$body = json_decode(wp_remote_retrieve_body($response), true);
	$items = is_array($body) && ! empty($body['Items']) && is_array($body['Items']) ? $body['Items'] : array();
	$tracks = array();

	foreach ($items as $item) {
		if (empty($item['Id']) || empty($item['Name'])) {
			continue;
		}

		$track_id      = sanitize_text_field((string) $item['Id']);
		$download_url  = add_query_arg('api_key', $api_key, $server_url . '/Items/' . rawurlencode($track_id) . '/Download');
		$stream_url    = roon_get_jellyfin_proxy_stream_url($track_id);
		$track_number  = isset($item['IndexNumber']) ? (int) $item['IndexNumber'] : 0;
		$disc_number   = isset($item['ParentIndexNumber']) ? (int) $item['ParentIndexNumber'] : 0;

		$tracks[] = array(
			'track_title'    => sanitize_text_field((string) $item['Name']),
			'track_duration' => roon_format_jellyfin_duration($item['RunTimeTicks'] ?? 0),
			'stream_url'     => $stream_url,
			'download_url'   => $download_url,
			'track_number'   => $track_number,
			'disc_number'    => $disc_number,
		);
	}

	usort(
		$tracks,
		static function ($left, $right) {
			$left_disc   = (int) ($left['disc_number'] ?? 0);
			$right_disc  = (int) ($right['disc_number'] ?? 0);
			$left_track  = (int) ($left['track_number'] ?? 0);
			$right_track = (int) ($right['track_number'] ?? 0);

			if ($left_disc === $right_disc) {
				return $left_track <=> $right_track;
			}

			return $left_disc <=> $right_disc;
		}
	);

	set_transient($cache_key, $tracks, 10 * MINUTE_IN_SECONDS);

	return $tracks;
}

/**
 * Return normalized album tracks for a post.
 * Priority: Jellyfin album ID -> manual repeater.
 *
 * @param int $post_id Post ID.
 * @return array<int, array<string, mixed>>
 */
function roon_get_post_album_tracks($post_id)
{
	if (! function_exists('get_field')) {
		return array();
	}

	$post_id           = (int) $post_id;
	$jellyfin_album_id = get_field('jellyfin_album_id', $post_id);
	$jellyfin_tracks   = roon_get_jellyfin_album_tracks($jellyfin_album_id);

	if (! empty($jellyfin_tracks)) {
		return $jellyfin_tracks;
	}

	$tracks = get_field('album_tracks', $post_id);

	if (! is_array($tracks)) {
		return array();
	}

	$normalized = array();

	foreach ($tracks as $track) {
		$track_urls = roon_get_jellyfin_track_urls($track['track_file'] ?? '');

		$normalized[] = array(
			'track_title'    => ! empty($track['track_title']) ? $track['track_title'] : 'Unknown Track',
			'track_duration' => ! empty($track['track_duration']) ? $track['track_duration'] : '--:--',
			'stream_url'     => ! empty($track_urls['stream_url']) ? $track_urls['stream_url'] : '',
			'download_url'   => ! empty($track_urls['download_url']) ? $track_urls['download_url'] : '',
		);
	}

	return $normalized;
}

/**
 * Get the primary artist name for an album post.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function roon_get_album_artist_name($post_id)
{
	$categories = get_the_category((int) $post_id);

	if (! empty($categories) && ! empty($categories[0]->name)) {
		return $categories[0]->name;
	}

	return 'Nhiều ca sĩ';
}

/**
 * Get the full artist list for an album post.
 *
 * @param int    $post_id   Post ID.
 * @param string $separator Separator used between artist names.
 * @return string
 */
function roon_get_album_artist_names($post_id, $separator = ', ')
{
	$categories = get_the_category((int) $post_id);

	if (empty($categories) || is_wp_error($categories)) {
		return roon_get_album_artist_name($post_id);
	}

	$artist_names = array();

	foreach ($categories as $category) {
		$name = isset($category->name) ? trim((string) $category->name) : '';

		if ('' !== $name) {
			$artist_names[] = $name;
		}
	}

	$artist_names = array_values(array_unique($artist_names));

	if (empty($artist_names)) {
		return roon_get_album_artist_name($post_id);
	}

	return implode($separator, $artist_names);
}

/**
 * Get a safe album cover URL for an album post.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function roon_get_album_cover_url($post_id)
{
	if (has_post_thumbnail($post_id)) {
		$cover = get_the_post_thumbnail_url($post_id, 'large');

		if ($cover) {
			return $cover;
		}
	}

	return 'https://placehold.co/240x240/3d2a1a/e8d5b0?text=ALBUM';
}

/**
 * Return album cards from published posts.
 *
 * @param int $limit Number of albums. 0 means all.
 * @return array<int, array<string, mixed>>
 */
function roon_get_library_albums($limit = 0)
{
	$cache_key = 'roon_library_albums_all';
	$albums = get_transient($cache_key);

	if ( false === $albums ) {
		$args = array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$posts  = get_posts($args);
		$albums = array();

		foreach ($posts as $post) {
			$albums[] = array(
				'id'     => $post->ID,
				'title'  => get_the_title($post),
				'artist' => roon_get_album_artist_names($post->ID),
				'year'   => get_the_date('Y', $post),
				'cover'  => roon_get_album_cover_url($post->ID),
				'url'    => get_permalink($post),
			);
		}
		
		set_transient($cache_key, $albums, 6 * HOUR_IN_SECONDS);
	}

	if ( $limit > 0 && count($albums) > $limit ) {
		return array_slice($albums, 0, $limit);
	}

	return $albums;
}

/**
 * Return all artists from album categories.
 *
 * @return array<int, array<string, string>>
 */
function roon_get_library_artists()
{
	$terms = get_categories(
		array(
			'hide_empty' => true,
		)
	);

	$artists = array();

	foreach ($terms as $term) {
		$name = trim($term->name);

		if ('' === $name) {
			continue;
		}

		$words    = preg_split('/\s+/', $name);
		$initials = '';

		foreach (array_slice($words, 0, 2) as $word) {
			$initials .= function_exists('mb_substr') ? mb_substr($word, 0, 1) : substr($word, 0, 1);
		}

		$artists[] = array(
			'name'     => $name,
			'initials' => strtoupper($initials),
		);
	}

	return $artists;
}

/**
 * Flatten all album tracks into a site-wide track list.
 *
 * @param int $limit Number of tracks. 0 means all.
 * @return array<int, array<string, string>>
 */
function roon_get_library_tracks($limit = 0)
{
	$albums = roon_get_library_albums();
	$tracks = array();

	foreach ($albums as $album) {
		$album_tracks = roon_get_post_album_tracks($album['id']);

		foreach ($album_tracks as $index => $track) {
			$tracks[] = array(
				'num'        => sprintf('%02d', $index + 1),
				'title'      => $track['track_title'] ?? 'Unknown Track',
				'album'      => $album['title'],
				'artist'     => $album['artist'],
				'duration'   => $track['track_duration'] ?? '--:--',
				'cover'      => $album['cover'],
				'stream_url' => $track['stream_url'] ?? '#',
				'post_url'   => $album['url'],
			);

			if ($limit > 0 && count($tracks) >= $limit) {
				return $tracks;
			}
		}
	}

	return $tracks;
}

/**
 * Return basic library stats for the dashboard.
 *
 * @return array<string, int>
 */
function roon_get_library_stats()
{
	$cache_key = 'roon_library_stats_v2';
	$stats = get_transient($cache_key);

	if ( false === $stats ) {
		$albums_count = wp_count_posts('post')->publish;
		$artists_count = wp_count_terms(array('taxonomy' => 'category', 'hide_empty' => true));

		// Tracks mất nhiều thời gian lấy qua API hoặc meta, nên đếm qua mảng rồi cache
		$tracks = roon_get_library_tracks();

		$stats = array(
			'artists'   => (int) $artists_count,
			'albums'    => (int) $albums_count,
			'tracks'    => count($tracks),
			'composers' => 0,
		);

		// Cache 12 tiếng để tối ưu hiệu suất, không cần đếm lại sau mỗi truy cập
		set_transient($cache_key, $stats, 12 * HOUR_IN_SECONDS);
	}

	return $stats;
}

/**
 * Xóa transient cache khi post được publish/update/delete.
 */
function roon_clear_library_cache( $post_id ) {
	$post = get_post( $post_id );
	if ( $post && $post->post_type === 'post' ) {
		delete_transient( 'roon_library_albums_all' );
		delete_transient( 'roon_library_stats_v2' );
		delete_transient( 'roon_popular_albums_top50' );
		delete_transient( 'roon_popular_artists_top10' );
	}
}
add_action( 'save_post', 'roon_clear_library_cache' );
add_action( 'delete_post', 'roon_clear_library_cache' );
add_action( 'edit_post', 'roon_clear_library_cache' );

/**
 * Theo dõi lượt xem của Album khi người dùng truy cập trang Single.
 */
function roon_track_album_views() {
    if ( ! is_single() ) return;
    $post_id = get_the_ID();
    $view_count = get_post_meta( $post_id, 'roon_view_count', true );
    if ( empty( $view_count ) ) {
        $view_count = 0;
    }
    update_post_meta( $post_id, 'roon_view_count', intval( $view_count ) + 1 );
}
add_action( 'wp_head', 'roon_track_album_views' );

/**
 * Return popular albums (Lượt xem nhiều) - REAL-TIME, không cache.
 * Dùng cho "Album mới phát" để cập nhật ngay khi khách xem.
 *
 * @param int $limit Number of albums.
 * @return array<int, array<string, mixed>>
 */
function roon_get_popular_albums($limit = 5)
{
	$args = array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 50, // Lấy sẵn 50 albums nhiều view nhất
		'meta_key'       => 'roon_view_count',
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
	);

	$posts  = get_posts($args);
	$albums = array();

	foreach ($posts as $post) {
		$albums[] = array(
			'id'     => $post->ID,
			'title'  => get_the_title($post),
			'artist' => roon_get_album_artist_name($post->ID),
			'year'   => get_the_date('Y', $post),
			'cover'  => roon_get_album_cover_url($post->ID),
			'url'    => get_permalink($post),
			'views'  => (int) get_post_meta($post->ID, 'roon_view_count', true) ?: 0,
		);
	}

	if ( $limit > 0 && count($albums) > $limit ) {
		return array_slice($albums, 0, $limit);
	}

	return $albums;
}

/**
 * Return popular albums với cache 2 tiếng - dùng cho các nơi khác cần tối ưu.
 *
 * @param int $limit Number of albums.
 * @return array<int, array<string, mixed>>
 */
function roon_get_popular_albums_cached($limit = 5)
{
	$cache_key = 'roon_popular_albums_top50';
	$albums = get_transient($cache_key);

	if ( false === $albums ) {
		$albums = roon_get_popular_albums(50);
		set_transient($cache_key, $albums, 2 * HOUR_IN_SECONDS);
	}

	if ( $limit > 0 && count($albums) > $limit ) {
		return array_slice($albums, 0, $limit);
	}

	return $albums;
}


/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer Wordpress.
 */
require get_template_directory() . '/inc/customizer-wp.php';

/**
 * Customizer Widget.
 */
require get_template_directory() . '/inc/customizer-widget.php';


/**
 * Customizer Block.
 */
require get_template_directory() . '/inc/customizer-block.php';

/**
 * Jellyfin → WordPress Auto Import.
 */
require get_template_directory() . '/inc/jellyfin-import.php';

/**
 * Hide Custom Theme
 */
// define('DISALLOW_FILE_EDIT', true);
// add_filter('acf/settings/show_admin', '__return_false');
// Ẩn "Theme File Editor" khỏi menu Appearance
// add_action('admin_menu', function () {
//     remove_submenu_page('themes.php', 'theme-editor.php');
// }, 999);

/**
 * REST API Search Posts (Động - không cache)
 * Endpoint: /wp-json/roon/v1/search
 */
add_action('rest_api_init', function() {
	register_rest_route('roon/v1', '/search', array(
		'methods' => 'GET',
		'callback' => 'roon_rest_search_posts_v2',
		'permission_callback' => '__return_true',
		'args' => array(
			's' => array(
				'type' => 'string',
				'required' => false,
				'sanitize_callback' => 'sanitize_text_field',
			),
			'type' => array(
				'type' => 'string',
				'required' => false,
				'sanitize_callback' => 'sanitize_text_field',
			),
			'limit' => array(
				'type' => 'integer',
				'required' => false,
				'default' => 10,
			),
		),
	));
});

/**
 * Callback function for search posts REST endpoint
 */
function roon_rest_search_posts($request) {
	$search_query = $request->get_param('s') ?: '';
	$type = $request->get_param('type') ?: 'all'; // all, tracks, albums
	$limit = (int) $request->get_param('limit') ?: 10;

	$results = array(
		'tracks' => array(),
		'albums' => array(),
		'artists' => array(),
	);

	if (strlen($search_query) < 2) {
		return rest_ensure_response($results);
	}

	// Search Posts (bài hát / album)
	$args = array(
		'post_type' => 'post',
		'post_status' => 'publish',
		'posts_per_page' => $limit * 2,
		's' => $search_query,
		'orderby' => 'relevance',
	);

	$query = new WP_Query($args);

	foreach ($query->posts as $post) {
		$post_data = array(
			'id' => $post->ID,
			'title' => get_the_title($post),
			'artist' => roon_get_album_artist_name($post->ID),
			'cover' => roon_get_album_cover_url($post->ID),
			'url' => get_permalink($post),
			'views' => (int) get_post_meta($post->ID, 'roon_view_count', true) ?: 0,
		);

		if ($type === 'all' || $type === 'tracks') {
			$results['albums'][] = $post_data;
		}
	}

	// Search Artists (Categories)
	$artist_args = array(
		'taxonomy' => 'category',
		'hide_empty' => true,
		'search' => $search_query,
		'number' => $limit,
	);

	$artists = get_terms($artist_args);

	foreach ($artists as $artist) {
		$name = trim($artist->name);
		if ('' === $name) {
			continue;
		}
		$words = preg_split('/\s+/', $name);
		$initials = '';
		foreach (array_slice($words, 0, 2) as $word) {
			$initials .= function_exists('mb_substr') ? mb_substr($word, 0, 1) : substr($word, 0, 1);
		}

		if ($type === 'all' || $type === 'artists') {
			$results['artists'][] = array(
				'id' => $artist->term_id,
				'name' => $name,
				'initials' => strtoupper($initials),
				'count' => $artist->count,
				'url' => get_term_link($artist),
			);
		}
	}

	// Limit results
	$results['tracks'] = array_slice($results['tracks'], 0, $limit);
	$results['albums'] = array_slice($results['albums'], 0, $limit);
	$results['artists'] = array_slice($results['artists'], 0, $limit);

	return rest_ensure_response($results);
}

/**
 * Improved search callback for the Roon search page.
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response
 */
function roon_rest_search_posts_v2($request) {
	$search_query = $request->get_param('s') ?: '';
	$type = $request->get_param('type') ?: 'all';
	$limit = (int) $request->get_param('limit') ?: 10;

	$results = array(
		'tracks'  => array(),
		'albums'  => array(),
		'artists' => array(),
	);

	if (strlen($search_query) < 2) {
		return rest_ensure_response($results);
	}

	$normalized_query = remove_accents(function_exists('mb_strtolower') ? mb_strtolower($search_query) : strtolower($search_query));

	$matches_search = static function ($value) use ($normalized_query) {
		$value = is_scalar($value) ? (string) $value : '';

		if ('' === $value) {
			return false;
		}

		$normalized_value = remove_accents(function_exists('mb_strtolower') ? mb_strtolower($value) : strtolower($value));

		return false !== strpos($normalized_value, $normalized_query);
	};

	if ($type === 'all' || $type === 'tracks') {
		foreach (roon_get_library_tracks() as $track) {
			if (
				! $matches_search($track['title'] ?? '') &&
				! $matches_search($track['artist'] ?? '') &&
				! $matches_search($track['album'] ?? '')
			) {
				continue;
			}

			$results['tracks'][] = array(
				'title'      => $track['title'] ?? 'Unknown Track',
				'artist'     => $track['artist'] ?? 'Unknown Artist',
				'album'      => $track['album'] ?? '',
				'duration'   => $track['duration'] ?? '--:--',
				'cover'      => $track['cover'] ?? '',
				'stream_url' => $track['stream_url'] ?? '#',
				'post_url'   => $track['post_url'] ?? '#',
			);

			if (count($results['tracks']) >= $limit) {
				break;
			}
		}
	}

	if ($type === 'all' || $type === 'albums') {
		foreach (roon_get_library_albums() as $album) {
			if (
				! $matches_search($album['title'] ?? '') &&
				! $matches_search($album['artist'] ?? '')
			) {
				continue;
			}

			$results['albums'][] = array(
				'id'     => $album['id'] ?? 0,
				'title'  => $album['title'] ?? '',
				'artist' => $album['artist'] ?? 'Unknown Artist',
				'cover'  => $album['cover'] ?? '',
				'url'    => $album['url'] ?? '#',
				'year'   => $album['year'] ?? '',
			);

			if (count($results['albums']) >= $limit) {
				break;
			}
		}
	}

	if ($type === 'all' || $type === 'artists') {
		$artists = get_terms(array(
			'taxonomy'   => 'category',
			'hide_empty' => true,
			'number'     => 0,
		));

		if (! is_wp_error($artists)) {
			foreach ($artists as $artist) {
				$name = trim($artist->name);

				if ('' === $name || ! $matches_search($name)) {
					continue;
				}

				$words = preg_split('/\s+/', $name);
				$initials = '';
				foreach (array_slice($words, 0, 2) as $word) {
					$initials .= function_exists('mb_substr') ? mb_substr($word, 0, 1) : substr($word, 0, 1);
				}

				$results['artists'][] = array(
					'id'       => $artist->term_id,
					'name'     => $name,
					'initials' => strtoupper($initials),
					'count'    => $artist->count,
					'url'      => get_term_link($artist),
				);

				if (count($results['artists']) >= $limit) {
					break;
				}
			}
		}
	}

	$results['meta'] = array(
		'query' => $search_query,
		'type'  => $type,
		'counts' => array(
			'tracks'  => count($results['tracks']),
			'albums'  => count($results['albums']),
			'artists' => count($results['artists']),
		),
	);

	return rest_ensure_response($results);
}


define('FORCE_SSL_ADMIN', true);

if ($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
    $_SERVER['HTTPS'] = 'on';


add_filter('admin_url', function($url, $path, $blog_id, $scheme) {
    if ($path === 'admin-ajax.php') {
        return str_replace('http://', 'https://', $url);
    }
    return $url;
}, 10, 4);

add_filter('admin_url', function($url) {
    return str_replace('http://', 'https://', $url);
});

add_filter('script_loader_src', function($src) {
    return str_replace('http://', 'https://', $src);
});

// Keep REST publicly accessible for frontend features (search/player).
add_filter('rest_authentication_errors', function($result) {
	if ($result instanceof WP_Error && 'rest_not_logged_in' === $result->get_error_code()) {
		return null;
	}

	return $result;
}, 999);
