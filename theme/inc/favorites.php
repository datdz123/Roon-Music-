<?php
/**
 * Danh sách bài hát yêu thích (per-user, lưu trong user meta để đồng bộ thiết bị).
 *
 * - Mỗi track có 1 "key" ổn định (sha1 của stream URL) dùng làm khóa lưu trữ.
 * - Nút trái tim (roon_fav_heart_button) render kèm data-* để client gửi snapshot.
 * - Trang "Bài hát yêu thích" render từ snapshot đã lưu, dùng chung UI với Track.
 *
 * @package roon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Số bài hát yêu thích tối đa lưu cho mỗi user. */
if ( ! defined( 'ROON_FAV_TRACKS_MAX' ) ) {
	define( 'ROON_FAV_TRACKS_MAX', 500 );
}

/**
 * Sinh key ổn định cho 1 track.
 *
 * @param string $stream_url Stream URL của track.
 * @param string $title      Tiêu đề (fallback khi không có stream URL).
 * @param string $artist     Ca sĩ (fallback).
 * @return string sha1 hex 40 ký tự.
 */
function roon_track_identity( $stream_url, $title = '', $artist = '' ) {
	$stream = trim( (string) $stream_url );

	if ( '' !== $stream && '#' !== $stream ) {
		return sha1( $stream );
	}

	$title  = function_exists( 'mb_strtolower' ) ? mb_strtolower( trim( (string) $title ) ) : strtolower( trim( (string) $title ) );
	$artist = function_exists( 'mb_strtolower' ) ? mb_strtolower( trim( (string) $artist ) ) : strtolower( trim( (string) $artist ) );

	return sha1( 'meta:' . $title . '|' . $artist );
}

/**
 * Lấy mảng favorites của user (assoc: key => snapshot).
 *
 * @param int $user_id User ID (0 = user hiện tại).
 * @return array<string, array<string, mixed>>
 */
function roon_get_user_fav_tracks( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : get_current_user_id();

	if ( ! $user_id ) {
		return array();
	}

	$fav = get_user_meta( $user_id, 'roon_fav_tracks', true );

	return is_array( $fav ) ? $fav : array();
}

/**
 * Track đã được user yêu thích chưa.
 *
 * @param string $key     Track key.
 * @param int    $user_id User ID (0 = user hiện tại).
 * @return bool
 */
function roon_is_track_favorited( $key, $user_id = 0 ) {
	$fav = roon_get_user_fav_tracks( $user_id );

	return isset( $fav[ $key ] );
}

/**
 * Chuẩn hóa + sanitize snapshot track từ dữ liệu POST.
 *
 * @param array $raw Dữ liệu thô ($_POST).
 * @return array<string, mixed>
 */
function roon_sanitize_fav_track( $raw ) {
	$title    = isset( $raw['title'] ) ? sanitize_text_field( wp_unslash( $raw['title'] ) ) : '';
	$artist   = isset( $raw['artist'] ) ? sanitize_text_field( wp_unslash( $raw['artist'] ) ) : '';
	$album    = isset( $raw['album'] ) ? sanitize_text_field( wp_unslash( $raw['album'] ) ) : '';
	$duration = isset( $raw['duration'] ) ? sanitize_text_field( wp_unslash( $raw['duration'] ) ) : '';
	$cover    = isset( $raw['cover'] ) ? esc_url_raw( wp_unslash( $raw['cover'] ) ) : '';
	$stream   = isset( $raw['stream_url'] ) ? esc_url_raw( wp_unslash( $raw['stream_url'] ) ) : '';
	$post_url = isset( $raw['post_url'] ) ? esc_url_raw( wp_unslash( $raw['post_url'] ) ) : '';

	return array(
		'title'      => '' !== $title ? $title : 'Unknown Track',
		'artist'     => '' !== $artist ? $artist : 'Unknown Artist',
		'album'      => $album,
		'duration'   => '' !== $duration ? $duration : '--:--',
		'cover'      => $cover,
		'stream_url' => $stream,
		'post_url'   => $post_url,
		'added'      => time(),
	);
}

/**
 * AJAX: thêm/bỏ 1 bài hát khỏi danh sách yêu thích.
 *
 * @return void
 */
function roon_ajax_toggle_fav_track() {
	check_ajax_referer( 'roon_fav', 'nonce' );

	$user_id = get_current_user_id();

	if ( ! $user_id ) {
		wp_send_json_error(
			array(
				'message'      => __( 'Vui lòng đăng nhập để lưu bài hát yêu thích.', 'roon' ),
				'requireLogin' => true,
			),
			401
		);
	}

	$key = isset( $_POST['key'] ) ? preg_replace( '/[^a-f0-9]/', '', strtolower( (string) wp_unslash( $_POST['key'] ) ) ) : '';

	if ( 40 !== strlen( $key ) ) {
		$key = roon_track_identity(
			isset( $_POST['stream_url'] ) ? wp_unslash( $_POST['stream_url'] ) : '',
			isset( $_POST['title'] ) ? wp_unslash( $_POST['title'] ) : '',
			isset( $_POST['artist'] ) ? wp_unslash( $_POST['artist'] ) : ''
		);
	}

	$fav = roon_get_user_fav_tracks( $user_id );

	if ( isset( $fav[ $key ] ) ) {
		unset( $fav[ $key ] );
		$favorited = false;
	} else {
		$fav[ $key ] = roon_sanitize_fav_track( $_POST );
		$favorited   = true;

		// Giới hạn dung lượng: bỏ bớt các bài cũ nhất.
		if ( count( $fav ) > ROON_FAV_TRACKS_MAX ) {
			uasort(
				$fav,
				static function ( $left, $right ) {
					return ( $left['added'] ?? 0 ) <=> ( $right['added'] ?? 0 );
				}
			);
			$fav = array_slice( $fav, count( $fav ) - ROON_FAV_TRACKS_MAX, null, true );
		}
	}

	update_user_meta( $user_id, 'roon_fav_tracks', $fav );

	wp_send_json_success(
		array(
			'favorited' => $favorited,
			'count'     => count( $fav ),
			'key'       => $key,
		)
	);
}
add_action( 'wp_ajax_roon_toggle_fav_track', 'roon_ajax_toggle_fav_track' );
add_action( 'wp_ajax_nopriv_roon_toggle_fav_track', 'roon_ajax_toggle_fav_track' );

/**
 * Trả về danh sách favorites đã chuẩn hóa để render (mới thêm lên đầu).
 *
 * @param int $user_id User ID (0 = user hiện tại).
 * @return array<int, array<string, mixed>>
 */
function roon_get_fav_tracks_for_display( $user_id = 0 ) {
	$fav = roon_get_user_fav_tracks( $user_id );

	uasort(
		$fav,
		static function ( $left, $right ) {
			return ( $right['added'] ?? 0 ) <=> ( $left['added'] ?? 0 );
		}
	);

	$list  = array();
	$index = 1;

	foreach ( $fav as $key => $track ) {
		$list[] = array(
			'key'        => $key,
			'num'        => sprintf( '%02d', $index ),
			'title'      => $track['title'] ?? 'Unknown Track',
			'artist'     => $track['artist'] ?? 'Unknown Artist',
			'album'      => $track['album'] ?? '',
			'duration'   => $track['duration'] ?? '--:--',
			'cover'      => $track['cover'] ?? '',
			'stream_url' => $track['stream_url'] ?? '#',
			'post_url'   => $track['post_url'] ?? '#',
		);
		$index++;
	}

	return $list;
}

/**
 * Render HTML nút trái tim (thêm/bỏ yêu thích) cho 1 track.
 *
 * @param array     $args   Dữ liệu track: title, artist, album, cover, stream_url, duration, post_url, key.
 * @param bool|null $is_fav Trạng thái yêu thích (null = tự tra cứu theo user hiện tại).
 * @return string HTML đã escape.
 */
function roon_fav_heart_button( $args, $is_fav = null ) {
	$title    = $args['title'] ?? '';
	$artist   = $args['artist'] ?? '';
	$album    = $args['album'] ?? '';
	$cover    = $args['cover'] ?? '';
	$stream   = $args['stream_url'] ?? '';
	$duration = $args['duration'] ?? '';
	$post_url = $args['post_url'] ?? '';
	$key      = ! empty( $args['key'] ) ? $args['key'] : roon_track_identity( $stream, $title, $artist );

	if ( null === $is_fav ) {
		$is_fav = roon_is_track_favorited( $key );
	}

	$state_class = $is_fav ? 'text-rose-500' : 'text-gray-300 hover:text-rose-400';

	ob_start();
	?>
	<button type="button"
		class="roon-fav-heart flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full border-none bg-transparent cursor-pointer transition-colors <?php echo esc_attr( $state_class ); ?>"
		aria-pressed="<?php echo $is_fav ? 'true' : 'false'; ?>"
		title="<?php echo esc_attr( $is_fav ? __( 'Bỏ khỏi yêu thích', 'roon' ) : __( 'Thêm vào yêu thích', 'roon' ) ); ?>"
		data-fav-key="<?php echo esc_attr( $key ); ?>"
		data-fav-title="<?php echo esc_attr( $title ); ?>"
		data-fav-artist="<?php echo esc_attr( $artist ); ?>"
		data-fav-album="<?php echo esc_attr( $album ); ?>"
		data-fav-cover="<?php echo esc_url( $cover ); ?>"
		data-fav-stream="<?php echo esc_url( $stream ); ?>"
		data-fav-duration="<?php echo esc_attr( $duration ); ?>"
		data-fav-post="<?php echo esc_url( $post_url ); ?>">
		<svg class="roon-fav-heart-icon" width="17" height="17" viewBox="0 0 24 24" fill="<?php echo $is_fav ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
			<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
		</svg>
	</button>
	<?php
	return ob_get_clean();
}

/**
 * Đẩy cấu hình favorites cho script chính (ajaxUrl, nonce, trạng thái đăng nhập).
 *
 * @return void
 */
function roon_fav_localize() {
	if ( ! wp_script_is( 'roon-script', 'registered' ) && ! wp_script_is( 'roon-script', 'enqueued' ) ) {
		return;
	}

	wp_localize_script(
		'roon-script',
		'roonFav',
		array(
			'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
			'nonce'      => wp_create_nonce( 'roon_fav' ),
			'isLoggedIn' => is_user_logged_in(),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'roon_fav_localize', 20 );
