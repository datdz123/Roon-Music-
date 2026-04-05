<?php
/**
 * Jellyfin → WordPress Auto Import
 *
 * Tự động import albums từ Jellyfin server sang WordPress Posts.
 * Mỗi album = 1 Post, Category = Artist, Featured Image = Cover từ Jellyfin.
 *
 * @package roon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ───────────────────────────────────────────────
 * 1. ADMIN MENU
 * ─────────────────────────────────────────────── */

add_action( 'admin_menu', 'roon_register_import_page' );

function roon_register_import_page() {
	add_menu_page(
		'Import Jellyfin',
		'Import Jellyfin',
		'manage_options',
		'roon-jellyfin-import',
		'roon_jellyfin_import_page_html',
		'dashicons-download',
		30
	);
}

/* ───────────────────────────────────────────────
 * 2. LẤY TẤT CẢ ALBUMS TỪ JELLYFIN
 * ─────────────────────────────────────────────── */

function roon_get_jellyfin_all_albums() {
	$server_url = roon_get_jellyfin_server_url();
	$api_key    = roon_get_jellyfin_api_key();

	if ( '' === $server_url || '' === $api_key ) {
		return new WP_Error( 'missing_config', 'Chưa cấu hình Jellyfin Server URL hoặc API Key trong Cài đặt chung.' );
	}

	$request_url = add_query_arg(
		array(
			'IncludeItemTypes' => 'MusicAlbum',
			'Recursive'        => 'true',
			'Fields'           => 'PrimaryImageTag,ImageTags,ImageBlurHashes,AlbumArtist,AlbumArtists,ProductionYear,Overview,DateCreated,ChildCount',
			'SortBy'           => 'DateCreated',
			'SortOrder'        => 'Descending',
			'Limit'            => 500,
			'api_key'          => $api_key,
		),
		$server_url . '/Items'
	);

	$response = wp_remote_get( $request_url, array( 'timeout' => 30, 'sslverify' => false ) );

	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'api_error', 'Không thể kết nối Jellyfin: ' . $response->get_error_message() );
	}

	$status_code = wp_remote_retrieve_response_code( $response );

	if ( 200 !== (int) $status_code ) {
		return new WP_Error( 'api_error', 'Jellyfin trả về lỗi HTTP ' . $status_code );
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( ! is_array( $body ) || empty( $body['Items'] ) ) {
		return array();
	}

	$albums = array();

	foreach ( $body['Items'] as $item ) {
		if ( empty( $item['Id'] ) || empty( $item['Name'] ) ) {
			continue;
		}

		$artist_names = array();
		if ( ! empty( $item['AlbumArtists'] ) && is_array( $item['AlbumArtists'] ) ) {
			foreach ( $item['AlbumArtists'] as $a ) {
				if ( ! empty( $a['Name'] ) ) {
					$artist_names[] = $a['Name'];
				}
			}
		}
		if ( empty( $artist_names ) && ! empty( $item['AlbumArtist'] ) ) {
			$artist_names[] = $item['AlbumArtist'];
		}
		$artist = implode( ', ', $artist_names );

		$cover_url = '';
		$image_tag = '';

		// Lấy image tag từ Jellyfin response.
		if ( ! empty( $item['ImageTags']['Primary'] ) ) {
			$image_tag = $item['ImageTags']['Primary'];
		} elseif ( ! empty( $item['PrimaryImageTag'] ) ) {
			$image_tag = $item['PrimaryImageTag'];
		}

		// Tạo URL ảnh đúng format Jellyfin: /Items/{id}/Images/Primary?maxHeight=600&tag={tag}&quality=90
		$cover_url = $server_url . '/Items/' . $item['Id'] . '/Images/Primary?maxHeight=600&quality=90';
		if ( '' !== $image_tag ) {
			$cover_url .= '&tag=' . $image_tag;
		}
		$cover_url .= '&api_key=' . $api_key;

		$albums[] = array(
			'jellyfin_id'  => sanitize_text_field( $item['Id'] ),
			'name'         => sanitize_text_field( $item['Name'] ),
			'artist'       => sanitize_text_field( $artist ),
			'year'         => isset( $item['ProductionYear'] ) ? (int) $item['ProductionYear'] : 0,
			'overview'     => ! empty( $item['Overview'] ) ? wp_kses_post( $item['Overview'] ) : '',
			'cover_url'    => $cover_url,
			'has_image'    => '' !== $image_tag,
			'track_count'  => isset( $item['ChildCount'] ) ? (int) $item['ChildCount'] : 0,
			'date_created' => ! empty( $item['DateCreated'] ) ? $item['DateCreated'] : '',
		);
	}

	return $albums;
}

/* ───────────────────────────────────────────────
 * 3. DANH SÁCH ALBUM ĐÃ IMPORT
 * ─────────────────────────────────────────────── */

function roon_get_imported_jellyfin_ids() {
	global $wpdb;

	$results = $wpdb->get_col(
		"SELECT meta_value FROM {$wpdb->postmeta}
		 WHERE meta_key = 'jellyfin_album_id'
		 AND meta_value != ''
		 AND post_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_status IN ('publish','draft','pending','private'))"
	);

	return is_array( $results ) ? array_filter( $results ) : array();
}

/* ───────────────────────────────────────────────
 * 4. IMPORT MỘT ALBUM
 * ─────────────────────────────────────────────── */

function roon_import_single_jellyfin_album( $album_data ) {
	if ( empty( $album_data['jellyfin_id'] ) || empty( $album_data['name'] ) ) {
		return new WP_Error( 'invalid_data', 'Thiếu dữ liệu album.' );
	}

	// Kiểm tra đã import chưa.
	$existing = get_posts(
		array(
			'post_type'   => 'post',
			'post_status' => 'any',
			'meta_key'    => 'jellyfin_album_id',
			'meta_value'  => $album_data['jellyfin_id'],
			'numberposts' => 1,
			'fields'      => 'ids',
		)
	);

	if ( ! empty( $existing ) ) {
		return new WP_Error( 'already_imported', 'Album này đã được import (Post #' . $existing[0] . ').' );
	}

	// ── Tạo / lấy Category (Artist) ──
	$category_ids = array();
	$artist_string = trim( $album_data['artist'] ?? '' );

	if ( '' !== $artist_string ) {
		$artists = array_map( 'trim', explode( ',', $artist_string ) );
		foreach ( $artists as $artist_name ) {
			if ( '' === $artist_name ) {
				continue;
			}
			$term = term_exists( $artist_name, 'category' );

			if ( $term ) {
				$category_ids[] = (int) $term['term_id'];
			} else {
				$new_term = wp_insert_term( $artist_name, 'category' );
				if ( ! is_wp_error( $new_term ) ) {
					$category_ids[] = (int) $new_term['term_id'];
				}
			}
		}
	}

	// ── Tạo Post ──
	$post_data = array(
		'post_title'   => $album_data['name'],
		'post_content' => $album_data['overview'] ?? '',
		'post_status'  => 'publish',
		'post_type'    => 'post',
		'post_date'    => ! empty( $album_data['date_created'] )
			? gmdate( 'Y-m-d H:i:s', strtotime( $album_data['date_created'] ) )
			: current_time( 'mysql' ),
	);

	if ( ! empty( $category_ids ) ) {
		$post_data['post_category'] = $category_ids;
	}

	$post_id = wp_insert_post( $post_data, true );

	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}

	// ── Lưu Jellyfin Album ID (ACF hoặc post_meta) ──
	if ( function_exists( 'update_field' ) ) {
		update_field( 'jellyfin_album_id', $album_data['jellyfin_id'], $post_id );
	} else {
		update_post_meta( $post_id, 'jellyfin_album_id', $album_data['jellyfin_id'] );
	}

	// ── Download & set Featured Image ──
	$cover_url   = $album_data['cover_url'] ?? '';
	$image_error = '';

	if ( '' !== $cover_url ) {
		$image_id = roon_sideload_image( $cover_url, $post_id, $album_data['name'] );

		if ( is_wp_error( $image_id ) ) {
			$image_error = $image_id->get_error_message() . ' | URL: ' . $cover_url;
		} else {
			set_post_thumbnail( $post_id, $image_id );
		}
	} else {
		$image_error = 'cover_url rỗng';
	}

	return array(
		'post_id'     => $post_id,
		'image_error' => $image_error,
	);
}

/* ───────────────────────────────────────────────
 * 5. DOWNLOAD ẢNH COVER TỪ JELLYFIN
 * ─────────────────────────────────────────────── */

function roon_sideload_image( $image_url, $post_id, $description = '' ) {
	if ( ! function_exists( 'media_handle_sideload' ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
	}

	/*
	 * KHÔNG dùng download_url() vì nó gọi wp_safe_remote_get()
	 * → chặn port không chuẩn (8096) và domain nội bộ.
	 * Thay bằng wp_remote_get() trực tiếp.
	 */
	$response = wp_remote_get(
		$image_url,
		array(
			'timeout'   => 60,
			'sslverify' => false, // Jellyfin nội bộ thường không có SSL hợp lệ.
		)
	);

	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'download_failed', 'Không tải được ảnh: ' . $response->get_error_message() );
	}

	$status_code = wp_remote_retrieve_response_code( $response );

	if ( 200 !== (int) $status_code ) {
		return new WP_Error( 'download_failed', 'Ảnh trả về HTTP ' . $status_code );
	}

	$image_data = wp_remote_retrieve_body( $response );

	if ( empty( $image_data ) ) {
		return new WP_Error( 'download_failed', 'Dữ liệu ảnh trống.' );
	}

	// Xác định extension từ Content-Type header.
	$content_type = wp_remote_retrieve_header( $response, 'content-type' );
	$ext = 'jpg';
	if ( strpos( $content_type, 'png' ) !== false ) {
		$ext = 'png';
	} elseif ( strpos( $content_type, 'webp' ) !== false ) {
		$ext = 'webp';
	} elseif ( strpos( $content_type, 'gif' ) !== false ) {
		$ext = 'gif';
	}

	// Ghi ra file tạm.
	$tmp_file = wp_tempnam( 'jf_cover_' );
	file_put_contents( $tmp_file, $image_data );

	$file_array = array(
		'name'     => sanitize_file_name( $description ) . '.' . $ext,
		'tmp_name' => $tmp_file,
		'type'     => $content_type,
		'size'     => strlen( $image_data ),
	);

	$attachment_id = media_handle_sideload( $file_array, $post_id, $description );

	// Xóa file tạm nếu lỗi.
	if ( is_wp_error( $attachment_id ) ) {
		@unlink( $tmp_file );
	}

	return $attachment_id;
}

/* ───────────────────────────────────────────────
 * 6. AJAX HANDLERS
 * ─────────────────────────────────────────────── */

add_action( 'wp_ajax_roon_fetch_jellyfin_albums', 'roon_ajax_fetch_jellyfin_albums' );

function roon_ajax_fetch_jellyfin_albums() {
	check_ajax_referer( 'roon_import_nonce', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'Bạn không có quyền thực hiện.' );
	}

	$albums = roon_get_jellyfin_all_albums();

	if ( is_wp_error( $albums ) ) {
		wp_send_json_error( $albums->get_error_message() );
	}

	$imported_ids = roon_get_imported_jellyfin_ids();

	foreach ( $albums as &$album ) {
		$album['imported'] = in_array( $album['jellyfin_id'], $imported_ids, true );
	}

	wp_send_json_success(
		array(
			'albums'         => $albums,
			'total'          => count( $albums ),
			'imported_count' => count( array_filter( $albums, fn( $a ) => $a['imported'] ) ),
		)
	);
}

add_action( 'wp_ajax_roon_import_album', 'roon_ajax_import_album' );

function roon_ajax_import_album() {
	check_ajax_referer( 'roon_import_nonce', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'Bạn không có quyền thực hiện.' );
	}

	$album_data = array(
		'jellyfin_id'  => sanitize_text_field( $_POST['jellyfin_id'] ?? '' ),
		'name'         => sanitize_text_field( $_POST['name'] ?? '' ),
		'artist'       => sanitize_text_field( $_POST['artist'] ?? '' ),
		'year'         => (int) ( $_POST['year'] ?? 0 ),
		'overview'     => wp_kses_post( $_POST['overview'] ?? '' ),
		'cover_url'    => esc_url_raw( $_POST['cover_url'] ?? '' ),
		'date_created' => sanitize_text_field( $_POST['date_created'] ?? '' ),
	);

	$result = roon_import_single_jellyfin_album( $album_data );

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( $result->get_error_message() );
	}

	$post_id     = $result['post_id'];
	$image_error = $result['image_error'] ?? '';

	wp_send_json_success(
		array(
			'post_id'     => $post_id,
			'edit_url'    => get_edit_post_link( $post_id, 'raw' ),
			'view_url'    => get_permalink( $post_id ),
			'message'     => 'Import thành công: ' . $album_data['name'],
			'image_error' => $image_error,
			'cover_url'   => $album_data['cover_url'],
		)
	);
}

/* ───────────────────────────────────────────────
 * 7. ADMIN PAGE HTML
 * ─────────────────────────────────────────────── */

function roon_jellyfin_import_page_html() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$nonce = wp_create_nonce( 'roon_import_nonce' );
	?>
	<style>
		.roon-import-wrap {
			max-width: 1200px;
			margin: 20px auto;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
		}
		.roon-import-header {
			display: flex;
			align-items: center;
			justify-content: space-between;
			margin-bottom: 24px;
			flex-wrap: wrap;
			gap: 12px;
		}
		.roon-import-header h1 {
			font-size: 26px;
			font-weight: 700;
			margin: 0;
			display: flex;
			align-items: center;
			gap: 10px;
		}
		.roon-import-header h1 .dashicons {
			font-size: 28px;
			width: 28px;
			height: 28px;
			color: #6366f1;
		}
		.roon-stats {
			display: flex;
			gap: 16px;
			margin-bottom: 20px;
			flex-wrap: wrap;
		}
		.roon-stat-card {
			background: #fff;
			border: 1px solid #e5e7eb;
			border-radius: 12px;
			padding: 16px 24px;
			min-width: 140px;
			box-shadow: 0 1px 3px rgba(0,0,0,.06);
		}
		.roon-stat-card .num {
			font-size: 28px;
			font-weight: 700;
			color: #1f2937;
			line-height: 1;
		}
		.roon-stat-card .label {
			font-size: 12px;
			color: #9ca3af;
			text-transform: uppercase;
			letter-spacing: .05em;
			margin-top: 4px;
		}
		.roon-stat-card.highlight { border-color: #6366f1; background: #eef2ff; }
		.roon-stat-card.highlight .num { color: #6366f1; }
		.roon-stat-card.success { border-color: #22c55e; background: #f0fdf4; }
		.roon-stat-card.success .num { color: #22c55e; }

		.roon-btn {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			padding: 10px 20px;
			border-radius: 8px;
			font-size: 13px;
			font-weight: 600;
			border: none;
			cursor: pointer;
			transition: all .2s;
		}
		.roon-btn-primary { background: #6366f1; color: #fff; }
		.roon-btn-primary:hover { background: #4f46e5; }
		.roon-btn-success { background: #22c55e; color: #fff; }
		.roon-btn-success:hover { background: #16a34a; }
		.roon-btn-outline { background: #fff; color: #374151; border: 1px solid #d1d5db; }
		.roon-btn-outline:hover { background: #f9fafb; border-color: #9ca3af; }
		.roon-btn:disabled { opacity: .5; cursor: not-allowed; }
		.roon-btn .dashicons { font-size: 16px; width: 16px; height: 16px; }

		.roon-toolbar {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 12px;
			margin-bottom: 16px;
			flex-wrap: wrap;
		}
		.roon-search-box {
			padding: 8px 14px;
			border: 1px solid #d1d5db;
			border-radius: 8px;
			font-size: 13px;
			min-width: 280px;
			outline: none;
			transition: border-color .2s;
		}
		.roon-search-box:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }

		.roon-table-wrap {
			background: #fff;
			border: 1px solid #e5e7eb;
			border-radius: 12px;
			overflow: hidden;
			box-shadow: 0 1px 3px rgba(0,0,0,.06);
		}
		.roon-table {
			width: 100%;
			border-collapse: collapse;
		}
		.roon-table th {
			background: #f9fafb;
			padding: 10px 16px;
			font-size: 11px;
			font-weight: 600;
			text-transform: uppercase;
			letter-spacing: .05em;
			color: #6b7280;
			text-align: left;
			border-bottom: 1px solid #e5e7eb;
		}
		.roon-table td {
			padding: 10px 16px;
			font-size: 13px;
			color: #374151;
			border-bottom: 1px solid #f3f4f6;
			vertical-align: middle;
		}
		.roon-table tr:last-child td { border-bottom: none; }
		.roon-table tr:hover td { background: #fafbff; }

		.roon-album-cover {
			width: 44px;
			height: 44px;
			border-radius: 6px;
			object-fit: cover;
			background: #f3f4f6;
		}
		.roon-album-info { display: flex; align-items: center; gap: 12px; }
		.roon-album-title { font-weight: 600; color: #111827; }
		.roon-album-artist { font-size: 12px; color: #9ca3af; }

		.roon-badge {
			display: inline-flex;
			align-items: center;
			gap: 4px;
			padding: 3px 10px;
			border-radius: 20px;
			font-size: 11px;
			font-weight: 600;
		}
		.roon-badge-imported { background: #dcfce7; color: #16a34a; }
		.roon-badge-new { background: #dbeafe; color: #2563eb; }

		.roon-btn-sm {
			padding: 5px 14px;
			font-size: 12px;
			border-radius: 6px;
		}

		.roon-progress-bar {
			width: 100%;
			height: 6px;
			background: #e5e7eb;
			border-radius: 3px;
			overflow: hidden;
			margin: 12px 0;
			display: none;
		}
		.roon-progress-bar .bar {
			height: 100%;
			background: linear-gradient(90deg, #6366f1, #8b5cf6);
			border-radius: 3px;
			transition: width .3s ease;
			width: 0%;
		}
		.roon-progress-text {
			font-size: 13px;
			color: #6b7280;
			margin-bottom: 8px;
			display: none;
		}
		.roon-loading {
			display: flex;
			flex-direction: column;
			align-items: center;
			padding: 60px 20px;
			color: #9ca3af;
		}
		.roon-loading .spinner { margin-bottom: 12px; }
		.roon-empty {
			text-align: center;
			padding: 60px 20px;
			color: #9ca3af;
		}
		.roon-select-all { margin-right: 4px; }

		@keyframes roon-spin { to { transform: rotate(360deg); } }
		.roon-spinner {
			width: 24px;
			height: 24px;
			border: 3px solid #e5e7eb;
			border-top-color: #6366f1;
			border-radius: 50%;
			animation: roon-spin .7s linear infinite;
		}
	</style>

	<div class="roon-import-wrap">
		<!-- Header -->
		<div class="roon-import-header">
			<h1><span class="dashicons dashicons-format-audio"></span> Import Jellyfin Albums</h1>
			<div style="display:flex;gap:8px;">
				<button class="roon-btn roon-btn-outline" id="btn-refresh" onclick="roonFetchAlbums()">
					<span class="dashicons dashicons-update"></span> Làm mới
				</button>
				<button class="roon-btn roon-btn-success" id="btn-import-selected" onclick="roonImportSelected()" disabled>
					<span class="dashicons dashicons-download"></span> Import đã chọn (<span id="selected-count">0</span>)
				</button>
				<button class="roon-btn roon-btn-primary" id="btn-import-all" onclick="roonImportAllNew()" disabled>
					<span class="dashicons dashicons-download"></span> Import tất cả mới
				</button>
			</div>
		</div>

		<!-- Stats -->
		<div class="roon-stats" id="stats-row" style="display:none;">
			<div class="roon-stat-card highlight">
				<div class="num" id="stat-total">0</div>
				<div class="label">Tổng trên Jellyfin</div>
			</div>
			<div class="roon-stat-card success">
				<div class="num" id="stat-imported">0</div>
				<div class="label">Đã import</div>
			</div>
			<div class="roon-stat-card">
				<div class="num" id="stat-new">0</div>
				<div class="label">Chưa import</div>
			</div>
		</div>

		<!-- Progress -->
		<div class="roon-progress-text" id="progress-text"></div>
		<div class="roon-progress-bar" id="progress-bar"><div class="bar" id="progress-bar-inner"></div></div>

		<!-- Toolbar -->
		<div class="roon-toolbar" id="toolbar" style="display:none;">
			<div style="display:flex;align-items:center;gap:12px;">
				<label style="font-size:13px;color:#6b7280;">
					<input type="checkbox" id="chk-select-all" class="roon-select-all" onchange="roonToggleAll(this)"> Chọn tất cả
				</label>
				<select id="filter-status" onchange="roonFilter()" style="padding:6px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;">
					<option value="all">Tất cả</option>
					<option value="new" selected>Chưa import</option>
					<option value="imported">Đã import</option>
				</select>
			</div>
			<input type="text" class="roon-search-box" id="search-box" placeholder="🔍 Tìm album, ca sĩ..." oninput="roonFilter()">
		</div>

		<!-- Table -->
		<div id="table-container">
			<div class="roon-loading" id="loading-state">
				<div class="roon-spinner"></div>
				<p>Đang kết nối Jellyfin...</p>
			</div>
		</div>
	</div>

	<script>
	(function() {
		const NONCE = '<?php echo esc_js( $nonce ); ?>';
		const AJAX  = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>';

		let allAlbums = [];
		let selectedIds = new Set();
		let importing = false;

		window.roonFetchAlbums = function() {
			document.getElementById('loading-state') && (document.getElementById('table-container').innerHTML = '<div class="roon-loading"><div class="roon-spinner"></div><p>Đang kết nối Jellyfin...</p></div>');
			document.getElementById('stats-row').style.display = 'none';
			document.getElementById('toolbar').style.display = 'none';

			fetch(AJAX, {
				method: 'POST',
				headers: {'Content-Type':'application/x-www-form-urlencoded'},
				body: new URLSearchParams({action:'roon_fetch_jellyfin_albums', nonce:NONCE})
			})
			.then(r => r.json())
			.then(res => {
				if (!res.success) { showError(res.data); return; }
				allAlbums = res.data.albums;
				selectedIds.clear();

				document.getElementById('stat-total').textContent    = res.data.total;
				document.getElementById('stat-imported').textContent = res.data.imported_count;
				document.getElementById('stat-new').textContent      = res.data.total - res.data.imported_count;
				document.getElementById('stats-row').style.display   = 'flex';
				document.getElementById('toolbar').style.display     = 'flex';

				const hasNew = allAlbums.some(a => !a.imported);
				document.getElementById('btn-import-all').disabled = !hasNew;

				roonFilter();
			})
			.catch(e => showError('Lỗi kết nối: ' + e.message));
		};

		window.roonFilter = function() {
			const status = document.getElementById('filter-status').value;
			const search = document.getElementById('search-box').value.toLowerCase().trim();

			let filtered = allAlbums;
			if (status === 'new')      filtered = filtered.filter(a => !a.imported);
			if (status === 'imported') filtered = filtered.filter(a => a.imported);
			if (search) filtered = filtered.filter(a => a.name.toLowerCase().includes(search) || a.artist.toLowerCase().includes(search));

			renderTable(filtered);
		};

		window.roonToggleAll = function(el) {
			const checkboxes = document.querySelectorAll('.roon-album-chk');
			checkboxes.forEach(cb => {
				if (!cb.disabled) { cb.checked = el.checked; }
				if (el.checked && !cb.disabled) selectedIds.add(cb.value);
			});
			if (!el.checked) selectedIds.clear();
			updateSelectedCount();
		};

		window.roonToggleOne = function(cb) {
			if (cb.checked) selectedIds.add(cb.value);
			else selectedIds.delete(cb.value);
			updateSelectedCount();
		};

		window.roonImportSelected = async function() {
			if (selectedIds.size === 0 || importing) return;
			const toImport = allAlbums.filter(a => selectedIds.has(a.jellyfin_id) && !a.imported);
			if (toImport.length === 0) { alert('Không có album mới nào để import.'); return; }
			await batchImport(toImport);
		};

		window.roonImportAllNew = async function() {
			if (importing) return;
			const toImport = allAlbums.filter(a => !a.imported);
			if (toImport.length === 0) { alert('Tất cả albums đã được import!'); return; }
			if (!confirm(`Bạn có chắc muốn import ${toImport.length} album?`)) return;
			await batchImport(toImport);
		};

		window.roonImportOne = async function(jfId) {
			if (importing) return;
			const album = allAlbums.find(a => a.jellyfin_id === jfId);
			if (!album) return;
			await batchImport([album]);
		};

		async function batchImport(albums) {
			importing = true;
			const total = albums.length;
			let done = 0, errors = 0;

			showProgress(true);
			setButtons(true);

			for (const album of albums) {
				updateProgress(done, total, `Đang import: ${album.name}...`);

				try {
					const res = await fetch(AJAX, {
						method: 'POST',
						headers: {'Content-Type':'application/x-www-form-urlencoded'},
						body: new URLSearchParams({
							action:       'roon_import_album',
							nonce:        NONCE,
							jellyfin_id:  album.jellyfin_id,
							name:         album.name,
							artist:       album.artist,
							year:         album.year,
							overview:     album.overview,
							cover_url:    album.cover_url,
							date_created: album.date_created,
						})
					});
					const json = await res.json();

					if (json.success) {
						album.imported = true;
						const row = document.getElementById('row-' + album.jellyfin_id);
						if (row) {
							row.querySelector('.roon-badge')?.remove();
							const statusCell = row.querySelector('.status-cell');
							if (statusCell) statusCell.innerHTML = '<span class="roon-badge roon-badge-imported">✓ Đã import</span>';
							const btnCell = row.querySelector('.action-cell');
							if (btnCell) btnCell.innerHTML = '<span style="color:#22c55e;font-size:12px;">✓</span>';
							const chk = row.querySelector('.roon-album-chk');
							if (chk) { chk.checked = false; chk.disabled = true; }
						}
					} else {
						errors++;
						console.warn('Import lỗi:', album.name, json.data);
					}
				} catch (e) {
					errors++;
					console.error('Import error:', album.name, e);
				}

				done++;
				updateProgress(done, total, done < total ? `Đang import: ${albums[done]?.name ?? ''}...` : 'Hoàn tất!');
			}

			// Update stats.
			const importedCount = allAlbums.filter(a => a.imported).length;
			document.getElementById('stat-imported').textContent = importedCount;
			document.getElementById('stat-new').textContent      = allAlbums.length - importedCount;

			selectedIds.clear();
			updateSelectedCount();

			const hasNew = allAlbums.some(a => !a.imported);
			document.getElementById('btn-import-all').disabled = !hasNew;

			importing = false;
			setButtons(false);

			setTimeout(() => showProgress(false), 3000);

			if (errors > 0) alert(`Hoàn tất! ${done - errors}/${total} thành công, ${errors} lỗi.`);
			else alert(`Hoàn tất! Đã import ${total} album thành công! 🎉`);
		}

		function renderTable(albums) {
			if (albums.length === 0) {
				document.getElementById('table-container').innerHTML = '<div class="roon-empty"><p>Không tìm thấy album nào.</p></div>';
				return;
			}

			let html = '<div class="roon-table-wrap"><table class="roon-table"><thead><tr>';
			html += '<th style="width:40px;"></th>';
			html += '<th style="width:50px;">#</th>';
			html += '<th>Album</th>';
			html += '<th>Ca sĩ</th>';
			html += '<th style="width:60px;">Năm</th>';
			html += '<th style="width:70px;">Tracks</th>';
			html += '<th style="width:110px;">Trạng thái</th>';
			html += '<th style="width:100px;"></th>';
			html += '</tr></thead><tbody>';

			albums.forEach((a, i) => {
				const coverSrc = a.cover_url || 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 44 44"><rect width="44" height="44" fill="%23f3f4f6"/><text x="22" y="26" text-anchor="middle" font-size="10" fill="%239ca3af">♪</text></svg>';
				html += `<tr id="row-${a.jellyfin_id}">`;
				html += `<td><input type="checkbox" class="roon-album-chk" value="${a.jellyfin_id}" ${a.imported ? 'disabled' : ''} onchange="roonToggleOne(this)"></td>`;
				html += `<td style="color:#9ca3af;">${i + 1}</td>`;
				html += `<td><div class="roon-album-info"><img class="roon-album-cover" src="${coverSrc}" loading="lazy" onerror="this.style.display='none'"><div><div class="roon-album-title">${escHtml(a.name)}</div></div></div></td>`;
				html += `<td><span class="roon-album-artist">${escHtml(a.artist || '—')}</span></td>`;
				html += `<td>${a.year || '—'}</td>`;
				html += `<td>${a.track_count || '—'}</td>`;
				html += `<td class="status-cell">${a.imported ? '<span class="roon-badge roon-badge-imported">✓ Đã import</span>' : '<span class="roon-badge roon-badge-new">Mới</span>'}</td>`;
				html += `<td class="action-cell">${a.imported ? '<span style="color:#22c55e;font-size:12px;">✓</span>' : `<button class="roon-btn roon-btn-primary roon-btn-sm" onclick="roonImportOne('${a.jellyfin_id}')">Import</button>`}</td>`;
				html += '</tr>';
			});

			html += '</tbody></table></div>';
			document.getElementById('table-container').innerHTML = html;
		}

		function showError(msg) {
			document.getElementById('table-container').innerHTML = `<div class="roon-empty" style="color:#ef4444;"><p>❌ ${msg}</p><p style="margin-top:12px;"><button class="roon-btn roon-btn-outline" onclick="roonFetchAlbums()">Thử lại</button></p></div>`;
		}

		function updateSelectedCount() {
			const count = selectedIds.size;
			document.getElementById('selected-count').textContent = count;
			document.getElementById('btn-import-selected').disabled = count === 0;
		}

		function showProgress(show) {
			document.getElementById('progress-bar').style.display  = show ? 'block' : 'none';
			document.getElementById('progress-text').style.display = show ? 'block' : 'none';
		}

		function updateProgress(done, total, text) {
			const pct = total > 0 ? Math.round((done / total) * 100) : 0;
			document.getElementById('progress-bar-inner').style.width = pct + '%';
			document.getElementById('progress-text').textContent = `${text} (${done}/${total})`;
		}

		function setButtons(disabled) {
			document.getElementById('btn-import-all').disabled      = disabled;
			document.getElementById('btn-import-selected').disabled = disabled;
			document.getElementById('btn-refresh').disabled         = disabled;
		}

		function escHtml(str) {
			const div = document.createElement('div');
			div.textContent = str;
			return div.innerHTML;
		}

		// Auto-fetch khi load page.
		roonFetchAlbums();
	})();
	</script>
	<?php
}
