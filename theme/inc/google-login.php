<?php
/**
 * Đăng nhập bằng Google (Google Identity Services).
 *
 * Luồng: nút "Đăng nhập" ở header mở popup -> nút Google (GIS) -> Google trả về
 * ID token (JWT) -> AJAX về server -> xác thực token -> tạo/đăng nhập user WP ->
 * lưu tên + ảnh Google -> reload hiển thị avatar + tên.
 *
 * Client ID / Secret cấu hình ở ACF options page "Cài đặt chung" (slug cai-dat-chung).
 *
 * @package roon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Đọc Google Client ID từ ACF options page.
 *
 * @return string
 */
function roon_get_google_client_id() {
	if ( ! function_exists( 'get_field' ) ) {
		return '';
	}

	$client_id = get_field( 'google_client_id', 'option' );

	return is_string( $client_id ) ? trim( $client_id ) : '';
}

/**
 * Đọc Google Client Secret từ ACF options page.
 *
 * Hiện chưa dùng cho luồng Identity Services (chỉ cần Client ID), giữ sẵn cho
 * các luồng OAuth code-exchange về sau.
 *
 * @return string
 */
function roon_get_google_client_secret() {
	if ( ! function_exists( 'get_field' ) ) {
		return '';
	}

	$secret = get_field( 'google_client_secret', 'option' );

	return is_string( $secret ) ? trim( $secret ) : '';
}

/**
 * Tên hiển thị ưu tiên lấy từ profile Google đã lưu.
 *
 * @param int $user_id User ID.
 * @return string
 */
function roon_google_get_display_name( $user_id ) {
	$name = get_user_meta( (int) $user_id, 'roon_google_name', true );

	if ( is_string( $name ) && '' !== $name ) {
		return $name;
	}

	$user = get_userdata( (int) $user_id );

	return $user ? $user->display_name : '';
}

/**
 * URL ảnh đại diện Google đã lưu cho user.
 *
 * @param int $user_id User ID.
 * @return string
 */
function roon_google_get_avatar_url( $user_id ) {
	$avatar = get_user_meta( (int) $user_id, 'roon_google_avatar', true );

	return is_string( $avatar ) ? $avatar : '';
}

/**
 * Nạp thư viện Google Identity Services ở footer (chỉ khi chưa đăng nhập).
 *
 * @return void
 */
function roon_google_login_assets() {
	if ( is_user_logged_in() ) {
		return;
	}

	// Thư viện GIS chính chủ. Không gắn version để Google tự cache.
	wp_enqueue_script( 'roon-google-gis', 'https://accounts.google.com/gsi/client', array(), null, true );
}
add_action( 'wp_enqueue_scripts', 'roon_google_login_assets' );

/**
 * Xác thực ID token Google qua endpoint tokeninfo (Google đã ký, không cần secret).
 *
 * @param string $credential JWT credential trả về từ GIS.
 * @return array{sub:string,email:string,name:string,picture:string}|false
 */
function roon_google_verify_id_token( $credential ) {
	$credential = is_string( $credential ) ? trim( $credential ) : '';
	$client_id  = roon_get_google_client_id();

	if ( '' === $credential || '' === $client_id ) {
		return false;
	}

	$response = wp_remote_get(
		add_query_arg( 'id_token', rawurlencode( $credential ), 'https://oauth2.googleapis.com/tokeninfo' ),
		array( 'timeout' => 15 )
	);

	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		return false;
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( ! is_array( $data ) || empty( $data['sub'] ) || empty( $data['email'] ) ) {
		return false;
	}

	// aud bắt buộc khớp Client ID của site (chống token phát cho app khác).
	if ( empty( $data['aud'] ) || ! hash_equals( $client_id, (string) $data['aud'] ) ) {
		return false;
	}

	// Email phải đã được Google xác minh.
	$email_verified = isset( $data['email_verified'] ) ? $data['email_verified'] : false;
	if ( 'true' !== $email_verified && true !== $email_verified ) {
		return false;
	}

	// Token còn hạn.
	if ( ! empty( $data['exp'] ) && (int) $data['exp'] < time() ) {
		return false;
	}

	return array(
		'sub'     => sanitize_text_field( (string) $data['sub'] ),
		'email'   => sanitize_email( (string) $data['email'] ),
		'name'    => isset( $data['name'] ) ? sanitize_text_field( (string) $data['name'] ) : '',
		'picture' => isset( $data['picture'] ) ? esc_url_raw( (string) $data['picture'] ) : '',
	);
}

/**
 * Tạo user WP mới từ profile Google.
 *
 * @param array $profile Profile đã xác thực (sub/email/name/picture).
 * @return int|WP_Error
 */
function roon_google_create_user( $profile ) {
	$email = $profile['email'];
	$base  = sanitize_user( current( explode( '@', $email ) ), true );

	if ( '' === $base ) {
		$base = 'user';
	}

	$username = $base;
	$suffix   = 1;

	while ( username_exists( $username ) ) {
		$username = $base . $suffix;
		$suffix++;
	}

	return wp_insert_user(
		array(
			'user_login'   => $username,
			'user_email'   => $email,
			'user_pass'    => wp_generate_password( 24, true, true ),
			'display_name' => '' !== $profile['name'] ? $profile['name'] : $username,
			'first_name'   => $profile['name'],
			'role'         => 'subscriber',
		)
	);
}

/**
 * AJAX: đăng nhập bằng Google.
 *
 * @return void
 */
function roon_google_ajax_login() {
	check_ajax_referer( 'roon_google_login', 'nonce' );

	$credential = isset( $_POST['credential'] ) ? sanitize_text_field( wp_unslash( $_POST['credential'] ) ) : '';
	$profile    = roon_google_verify_id_token( $credential );

	if ( ! $profile ) {
		wp_send_json_error( array( 'message' => __( 'Xác thực Google thất bại. Vui lòng thử lại.', 'roon' ) ), 401 );
	}

	$user = get_user_by( 'email', $profile['email'] );

	if ( ! $user ) {
		$user_id = roon_google_create_user( $profile );

		if ( is_wp_error( $user_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Không thể tạo tài khoản. Vui lòng thử lại.', 'roon' ) ), 500 );
		}

		$user = get_user_by( 'id', $user_id );
	}

	if ( ! $user ) {
		wp_send_json_error( array( 'message' => __( 'Không tìm thấy tài khoản.', 'roon' ) ), 500 );
	}

	// Lưu/đồng bộ profile Google.
	update_user_meta( $user->ID, 'roon_google_sub', $profile['sub'] );

	if ( '' !== $profile['picture'] ) {
		update_user_meta( $user->ID, 'roon_google_avatar', $profile['picture'] );
	}

	if ( '' !== $profile['name'] ) {
		update_user_meta( $user->ID, 'roon_google_name', $profile['name'] );
	}

	wp_set_current_user( $user->ID );
	wp_set_auth_cookie( $user->ID, true );

	wp_send_json_success(
		array(
			'name'   => roon_google_get_display_name( $user->ID ),
			'avatar' => roon_google_get_avatar_url( $user->ID ),
		)
	);
}
add_action( 'wp_ajax_nopriv_roon_google_login', 'roon_google_ajax_login' );
add_action( 'wp_ajax_roon_google_login', 'roon_google_ajax_login' );

/**
 * AJAX: đăng xuất.
 *
 * @return void
 */
function roon_google_ajax_logout() {
	check_ajax_referer( 'roon_google_login', 'nonce' );
	wp_logout();
	wp_send_json_success();
}
add_action( 'wp_ajax_roon_google_logout', 'roon_google_ajax_logout' );

/**
 * Dùng ảnh Google làm avatar WP (thay Gravatar) nếu user có lưu.
 *
 * @param string $url         URL avatar gốc.
 * @param mixed  $id_or_email User ID / email / WP_User / comment object.
 * @param array  $args        Tham số get_avatar_url.
 * @return string
 */
function roon_google_filter_avatar_url( $url, $id_or_email, $args ) {
	$user_id = 0;

	if ( is_numeric( $id_or_email ) ) {
		$user_id = (int) $id_or_email;
	} elseif ( $id_or_email instanceof WP_User ) {
		$user_id = $id_or_email->ID;
	} elseif ( is_string( $id_or_email ) ) {
		$user    = get_user_by( 'email', $id_or_email );
		$user_id = $user ? $user->ID : 0;
	} elseif ( is_object( $id_or_email ) && ! empty( $id_or_email->user_id ) ) {
		$user_id = (int) $id_or_email->user_id;
	}

	if ( $user_id ) {
		$google_avatar = get_user_meta( $user_id, 'roon_google_avatar', true );

		if ( is_string( $google_avatar ) && '' !== $google_avatar ) {
			return $google_avatar;
		}
	}

	return $url;
}
add_filter( 'get_avatar_url', 'roon_google_filter_avatar_url', 10, 3 );
