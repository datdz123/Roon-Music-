<?php
/**
 * Giới hạn lượt tải về mỗi ngày.
 *
 * Đếm theo người dùng (nếu đã đăng nhập) hoặc theo IP (nếu chưa). Khi hết lượt
 * trong ngày, nút "Tải về" bị khóa (cả phía server khi render lẫn phía client).
 * Liên kết tải lấy từ server qua AJAX (không lộ URL NAS ra HTML).
 *
 * @package roon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tính năng tải Album có đang bật không.
 *
 * @return bool
 */
function roon_is_album_download_enabled() {
	if ( ! function_exists( 'get_field' ) ) {
		return false;
	}

	return (bool) get_field( 'enable_album_download', 'option' );
}

/**
 * Số lượt tải tối đa mỗi ngày (ACF options). Mặc định 1.
 *
 * @return int
 */
function roon_get_daily_download_limit() {
	if ( ! function_exists( 'get_field' ) ) {
		return 1;
	}

	$limit = get_field( 'daily_download_limit', 'option' );
	$limit = is_numeric( $limit ) ? (int) $limit : 1;

	return max( 0, $limit );
}

/**
 * IP client (ưu tiên X-Forwarded-For khi đứng sau proxy).
 *
 * @return string
 */
function roon_download_client_ip() {
	$ip = '';

	if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$parts = explode( ',', (string) wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		$ip    = trim( $parts[0] );
	} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = (string) wp_unslash( $_SERVER['REMOTE_ADDR'] );
	}

	$valid = filter_var( $ip, FILTER_VALIDATE_IP );

	return $valid ? $valid : '0.0.0.0';
}

/**
 * Định danh dùng để đếm lượt tải (user hoặc IP).
 *
 * @return string
 */
function roon_download_identity() {
	$user_id = get_current_user_id();

	if ( $user_id ) {
		return 'u' . $user_id;
	}

	return 'ip' . md5( roon_download_client_ip() );
}

/**
 * Khóa transient đếm lượt tải của ngày hôm nay (theo múi giờ site).
 *
 * @return string
 */
function roon_download_count_key() {
	return 'roon_dl_' . roon_download_identity() . '_' . wp_date( 'Ymd' );
}

/**
 * Số lượt đã tải hôm nay.
 *
 * @return int
 */
function roon_get_download_count() {
	$count = get_transient( roon_download_count_key() );

	return is_numeric( $count ) ? (int) $count : 0;
}

/**
 * Số lượt tải còn lại hôm nay.
 *
 * @return int
 */
function roon_get_download_remaining() {
	$limit = roon_get_daily_download_limit();

	if ( $limit <= 0 ) {
		return 0;
	}

	return max( 0, $limit - roon_get_download_count() );
}

/**
 * Đã hết lượt tải hôm nay chưa.
 *
 * @return bool
 */
function roon_is_download_exhausted() {
	$limit = roon_get_daily_download_limit();

	return $limit > 0 && roon_get_download_count() >= $limit;
}

/**
 * Tăng số lượt tải lên 1 cho ngày hôm nay.
 *
 * @return int Số lượt sau khi tăng.
 */
function roon_increment_download_count() {
	$count = roon_get_download_count() + 1;
	set_transient( roon_download_count_key(), $count, DAY_IN_SECONDS + HOUR_IN_SECONDS );

	return $count;
}

/**
 * AJAX: kiểm tra hạn mức + cấp liên kết tải.
 *
 * @return void
 */
function roon_ajax_consume_download() {
	check_ajax_referer( 'roon_download', 'nonce' );

	if ( ! roon_is_album_download_enabled() ) {
		wp_send_json_error(
			array(
				'message'   => __( 'Chức năng tải về hiện đang tắt.', 'roon' ),
				'remaining' => 0,
			),
			403
		);
	}

	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$raw_url = '';

	if ( $post_id && function_exists( 'get_field' ) ) {
		$raw_url = get_field( 'album_download_url_manual', $post_id );
	}

	// ACF field kiểu "url" trả về string; phòng trường hợp trả về array (file/link field).
	if ( is_array( $raw_url ) ) {
		$raw_url = $raw_url['url'] ?? ( $raw_url['link'] ?? '' );
	}

	// Dùng esc_url_raw thay vì wp_http_validate_url: KHỚP với điều kiện render ở single.php,
	// đồng thời cho phép link NAS/IP nội bộ (192.168.*, 10.*, *.synology.me, có cổng…).
	// wp_http_validate_url() là hàm chống SSRF, nó chặn IP nội bộ nên KHÔNG dùng được ở đây.
	$url = esc_url_raw( trim( (string) $raw_url ) );

	if ( '' === $url ) {
		wp_send_json_error(
			array( 'message' => __( 'Không tìm thấy liên kết tải hợp lệ.', 'roon' ) ),
			404
		);
	}

	$limit = roon_get_daily_download_limit();

	if ( $limit > 0 && roon_get_download_count() >= $limit ) {
		wp_send_json_error(
			array(
				'message'   => sprintf(
					/* translators: %d: số lượt tải tối đa mỗi ngày. */
					__( 'Bạn đã dùng hết %d lượt tải trong hôm nay. Vui lòng quay lại vào ngày mai.', 'roon' ),
					$limit
				),
				'remaining' => 0,
				'limit'     => $limit,
				'exhausted' => true,
			),
			429
		);
	}

	$count     = roon_increment_download_count();
	$remaining = $limit > 0 ? max( 0, $limit - $count ) : 0;

	wp_send_json_success(
		array(
			'url'       => $url,
			'remaining' => $remaining,
			'limit'     => $limit,
			'exhausted' => ( $remaining <= 0 ),
		)
	);
}
add_action( 'wp_ajax_roon_consume_download', 'roon_ajax_consume_download' );
add_action( 'wp_ajax_nopriv_roon_consume_download', 'roon_ajax_consume_download' );

/**
 * Đẩy cấu hình tải về cho script chính.
 *
 * @return void
 */
function roon_download_localize() {
	if ( ! wp_script_is( 'roon-script', 'registered' ) && ! wp_script_is( 'roon-script', 'enqueued' ) ) {
		return;
	}

	wp_localize_script(
		'roon-script',
		'roonDownload',
		array(
			'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
			'nonce'     => wp_create_nonce( 'roon_download' ),
			'limit'     => roon_get_daily_download_limit(),
			'remaining' => roon_get_download_remaining(),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'roon_download_localize', 20 );
