<?php
/**
 * Template Part: Roon Auth — Đăng nhập bằng Google (gắn ở header)
 *
 * Chưa đăng nhập: nút "Đăng nhập" -> popup chứa nút Google (GIS).
 * Đã đăng nhập: chip avatar + tên, dropdown đăng xuất.
 *
 * @package roon
 */

$roon_is_logged_in = is_user_logged_in();
$roon_user_name    = '';
$roon_user_avatar  = '';

if ( $roon_is_logged_in ) {
	$roon_current_id  = get_current_user_id();
	$roon_user_name   = function_exists( 'roon_google_get_display_name' ) ? roon_google_get_display_name( $roon_current_id ) : wp_get_current_user()->display_name;
	$roon_user_avatar = function_exists( 'roon_google_get_avatar_url' ) ? roon_google_get_avatar_url( $roon_current_id ) : '';
}

// Cấu hình cho JS — nhúng trực tiếp để không phụ thuộc thứ tự script footer.
$roon_auth_cfg = array(
	'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
	'nonce'    => wp_create_nonce( 'roon_google_login' ),
	'clientId' => function_exists( 'roon_get_google_client_id' ) ? roon_get_google_client_id() : '',
);
?>
<div id="roon-auth" class="relative flex items-center font-inter">
	<?php if ( $roon_is_logged_in ) : ?>
		<!-- Chip user -->
		<button id="roon-user-chip" type="button" class="flex items-center gap-2 rounded-full border border-gray-200 bg-white py-1 pl-1 pr-2.5 hover:bg-gray-50 transition-colors cursor-pointer">
			<?php if ( $roon_user_avatar ) : ?>
				<img src="<?php echo esc_url( $roon_user_avatar ); ?>" alt="<?php echo esc_attr( $roon_user_name ); ?>" class="w-7 h-7 rounded-full object-cover" referrerpolicy="no-referrer"/>
			<?php else : ?>
				<span class="flex w-7 h-7 items-center justify-center rounded-full bg-roon-blue text-white text-xs font-semibold"><?php echo esc_html( strtoupper( mb_substr( $roon_user_name, 0, 1 ) ) ); ?></span>
			<?php endif; ?>
			<span class="hidden sm:block max-w-[120px] truncate text-[13px] font-medium text-gray-800"><?php echo esc_html( $roon_user_name ); ?></span>
			<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-gray-400"><polyline points="6 9 12 15 18 9"/></svg>
		</button>
		<!-- Dropdown -->
		<div id="roon-user-menu" class="absolute right-0 top-full mt-2 z-50 hidden min-w-[180px] rounded-xl border border-gray-100 bg-white shadow-lg py-1">
			<div class="px-4 py-2.5 border-b border-gray-100">
				<p class="m-0 text-[13px] font-semibold text-gray-900 truncate"><?php echo esc_html( $roon_user_name ); ?></p>
			</div>
			<a id="roon-fav-link" href="<?php echo esc_url( home_url( '/#fav-tracks' ) ); ?>" data-page="fav-tracks" class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-[13px] text-gray-700 no-underline hover:bg-gray-50 cursor-pointer">
				<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f43f5e" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
				<?php esc_html_e( 'Bài hát yêu thích', 'roon' ); ?>
			</a>
			<div class="my-1 border-t border-gray-100"></div>
			<button id="roon-logout-btn" type="button" class="flex w-full items-center gap-2 border-none bg-transparent px-4 py-2.5 text-left text-[13px] text-gray-700 hover:bg-gray-50 cursor-pointer">
				<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
				<?php esc_html_e( 'Đăng xuất', 'roon' ); ?>
			</button>
		</div>
	<?php else : ?>
		<!-- Nút mở popup đăng nhập -->
		<button id="roon-login-btn" type="button" class="flex items-center gap-2 rounded-full bg-roon-blue px-4 py-2 text-[13px] font-semibold text-white hover:bg-roon-indigo transition-colors cursor-pointer">
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
			<?php esc_html_e( 'Đăng nhập', 'roon' ); ?>
		</button>
	<?php endif; ?>
</div>

<?php if ( ! $roon_is_logged_in ) : ?>
<!-- Popup đăng nhập -->
<div id="roon-login-overlay" class="hidden fixed inset-0 z-[1000] flex items-center justify-center bg-black/30 p-4 backdrop-blur-sm">
	<div class="relative w-full max-w-sm rounded-3xl border border-gray-200 bg-white p-7 text-center shadow-2xl">
		<button id="roon-login-close" type="button" class="absolute right-4 top-4 flex h-8 w-8 items-center justify-center rounded-full border-none bg-transparent text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition-colors cursor-pointer" title="<?php esc_attr_e( 'Đóng', 'roon' ); ?>">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
		</button>

		<div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-roon-blue/10">
			<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#3b3ef6" stroke-width="1.8"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
		</div>
		<h2 class="m-0 text-lg font-bold text-gray-900"><?php esc_html_e( 'Đăng nhập', 'roon' ); ?></h2>
		<p class="mb-5 mt-2 text-[13px] leading-6 text-gray-500"><?php esc_html_e( 'Đăng nhập bằng tài khoản Google để lưu nhạc yêu thích và đồng bộ thiết bị.', 'roon' ); ?></p>

		<!-- Nút Google (GIS) render vào đây -->
		<div id="roon-google-btn" class="flex min-h-[44px] justify-center"></div>

		<p id="roon-login-loading" class="mt-3 hidden text-[12.5px] text-gray-400"><?php esc_html_e( 'Đang đăng nhập…', 'roon' ); ?></p>
		<p id="roon-login-error" class="mt-3 hidden text-[12.5px] text-red-500"></p>

		<p class="mt-5 text-[11px] leading-5 text-gray-400"><?php esc_html_e( 'Bằng việc đăng nhập, bạn đồng ý với điều khoản sử dụng của chúng tôi.', 'roon' ); ?></p>
	</div>
</div>
<?php endif; ?>

<script>
(function () {
	var cfg = <?php echo wp_json_encode( $roon_auth_cfg ); ?>;

	function post(action, fields) {
		var body = new URLSearchParams();
		body.append('action', action);
		body.append('nonce', cfg.nonce || '');
		Object.keys(fields || {}).forEach(function (key) {
			body.append(key, fields[key]);
		});
		return fetch(cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString()
		}).then(function (res) { return res.json(); });
	}

	/* ── Đã đăng nhập: dropdown + đăng xuất ── */
	var chip = document.getElementById('roon-user-chip');
	var menu = document.getElementById('roon-user-menu');
	var logoutBtn = document.getElementById('roon-logout-btn');

	if (chip && menu) {
		chip.addEventListener('click', function (e) {
			e.stopPropagation();
			menu.classList.toggle('hidden');
		});
		document.addEventListener('click', function (e) {
			if (!menu.contains(e.target) && !chip.contains(e.target)) {
				menu.classList.add('hidden');
			}
		});
	}

	// Bấm "Bài hát yêu thích" → đóng dropdown (RoonNav lo phần điều hướng).
	var favLink = document.getElementById('roon-fav-link');
	if (favLink && menu) {
		favLink.addEventListener('click', function () {
			menu.classList.add('hidden');
		});
	}

	if (logoutBtn) {
		logoutBtn.addEventListener('click', function () {
			logoutBtn.disabled = true;
			post('roon_google_logout', {}).then(function () {
				window.location.reload();
			}).catch(function () {
				window.location.reload();
			});
		});
	}

	/* ── Chưa đăng nhập: popup + Google Identity Services ── */
	var loginBtn = document.getElementById('roon-login-btn');
	var overlay = document.getElementById('roon-login-overlay');
	var closeBtn = document.getElementById('roon-login-close');
	var googleHolder = document.getElementById('roon-google-btn');
	var errorEl = document.getElementById('roon-login-error');
	var loadingEl = document.getElementById('roon-login-loading');
	var gisRendered = false;

	function showError(msg) {
		if (loadingEl) loadingEl.classList.add('hidden');
		if (errorEl) {
			errorEl.textContent = msg;
			errorEl.classList.remove('hidden');
		}
	}

	function handleCredential(response) {
		if (!response || !response.credential) {
			showError('Không nhận được phản hồi từ Google. Vui lòng thử lại.');
			return;
		}
		if (errorEl) errorEl.classList.add('hidden');
		if (loadingEl) loadingEl.classList.remove('hidden');

		post('roon_google_login', { credential: response.credential }).then(function (data) {
			if (data && data.success) {
				window.location.reload();
			} else {
				showError((data && data.data && data.data.message) || 'Đăng nhập thất bại.');
			}
		}).catch(function () {
			showError('Có lỗi xảy ra. Vui lòng thử lại.');
		});
	}

	function renderGoogleButton() {
		if (gisRendered) return;

		if (!cfg.clientId) {
			showError('Chưa cấu hình Google Client ID trong phần Cài đặt.');
			return;
		}
		if (!(window.google && google.accounts && google.accounts.id)) {
			// Thư viện GIS chưa tải xong — thử lại.
			setTimeout(renderGoogleButton, 200);
			return;
		}

		google.accounts.id.initialize({
			client_id: cfg.clientId,
			callback: handleCredential,
			ux_mode: 'popup',
			auto_select: false
		});
		google.accounts.id.renderButton(googleHolder, {
			theme: 'outline',
			size: 'large',
			type: 'standard',
			shape: 'pill',
			text: 'continue_with',
			logo_alignment: 'center',
			width: 280
		});
		gisRendered = true;
	}

	function openOverlay() {
		if (!overlay) return;
		if (errorEl) errorEl.classList.add('hidden');
		if (loadingEl) loadingEl.classList.add('hidden');
		overlay.classList.remove('hidden');
		renderGoogleButton();
	}

	function closeOverlay() {
		if (overlay) overlay.classList.add('hidden');
	}

	if (loginBtn) loginBtn.addEventListener('click', openOverlay);
	// Mọi nút .roon-open-login (vd nút trong trang Yêu thích khi chưa đăng nhập).
	document.addEventListener('click', function (e) {
		if (e.target.closest && e.target.closest('.roon-open-login')) {
			e.preventDefault();
			openOverlay();
		}
	});
	if (closeBtn) closeBtn.addEventListener('click', closeOverlay);
	if (overlay) {
		overlay.addEventListener('click', function (e) {
			if (e.target === overlay) closeOverlay();
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') closeOverlay();
		});
	}
})();
</script>
