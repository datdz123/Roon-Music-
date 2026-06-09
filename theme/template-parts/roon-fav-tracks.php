<?php
/**
 * Template Part: Roon Fav Tracks — Danh sách bài hát yêu thích.
 *
 * Dùng chung UI với "Bài hát / Track" + phân trang. Yêu cầu đăng nhập.
 *
 * @package roon
 */

$roon_fav_logged_in = is_user_logged_in();
$fav_tracks         = $roon_fav_logged_in && function_exists( 'roon_get_fav_tracks_for_display' ) ? roon_get_fav_tracks_for_display() : array();
?>

<div id="page-fav-tracks" class="roon-page hidden font-inter">

	<?php if ( ! $roon_fav_logged_in ) : ?>
		<!-- Gate: chưa đăng nhập -->
		<div class="mx-auto flex max-w-md flex-col items-center py-24 text-center">
			<div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-rose-50">
				<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#f43f5e" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
			</div>
			<h1 class="m-0 text-2xl font-bold text-gray-900"><?php esc_html_e( 'Bài hát yêu thích', 'roon' ); ?></h1>
			<p class="mb-5 mt-2 text-[14px] text-gray-500"><?php esc_html_e( 'Đăng nhập để lưu và đồng bộ danh sách bài hát yêu thích trên mọi thiết bị.', 'roon' ); ?></p>
			<button type="button" class="roon-open-login flex items-center gap-2 rounded-full bg-roon-blue px-5 py-2.5 text-[13px] font-semibold text-white hover:bg-roon-indigo transition-colors cursor-pointer">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
				<?php esc_html_e( 'Đăng nhập', 'roon' ); ?>
			</button>
		</div>
	<?php else : ?>

		<div class="mb-4 flex flex-wrap items-end justify-between gap-3">
			<div>
				<h1 class="m-0 text-[40px] font-bold leading-tight tracking-tight text-gray-900">
					<?php esc_html_e( 'Bài hát yêu thích', 'roon' ); ?>
					<span id="fav-tracks-total" class="ml-2 text-[16px] font-normal text-gray-400"><?php echo count( $fav_tracks ); ?> tracks</span>
				</h1>
			</div>
		</div>

		<!-- Search -->
		<div class="mb-3 flex flex-wrap items-center gap-2">
			<div class="relative min-w-[180px] max-w-sm flex-1">
				<svg class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
				<input id="fav-tracks-search-input" type="text" placeholder="<?php esc_attr_e( 'Tìm trong yêu thích…', 'roon' ); ?>"
					class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 pl-8 pr-3 text-[13px] text-gray-700 outline-none transition focus:border-roon-blue focus:bg-white focus:ring-2 focus:ring-roon-blue/10"/>
			</div>
			<span id="fav-tracks-count" class="ml-auto text-[12px] text-gray-400"><?php echo count( $fav_tracks ); ?> bài</span>
		</div>

		<!-- Column headers -->
		<div class="mb-1 flex items-center gap-3 border-b border-gray-200 px-2 pb-2">
			<div class="w-11 flex-shrink-0"></div>
			<div class="flex-[2] text-[12px] text-gray-400">Track</div>
			<div class="hidden flex-[1.5] text-[12px] text-gray-400 md:block">Album</div>
			<div class="hidden flex-[1.5] text-[12px] text-gray-400 md:block">Artist</div>
			<div class="w-[72px] flex-shrink-0 text-right text-[12px] text-gray-400">⏱</div>
		</div>

		<div id="fav-tracks-list" data-roon-playlist="fav-tracks" class="flex flex-col">
			<?php foreach ( $fav_tracks as $track ) : ?>
			<div class="roon-fav-track-row group flex items-center gap-3 rounded-lg px-2 py-1.5 transition-colors hover:bg-gray-50"
				data-fav-key="<?php echo esc_attr( $track['key'] ); ?>"
				data-title="<?php echo esc_attr( strtolower( $track['title'] ) ); ?>"
				data-album="<?php echo esc_attr( strtolower( $track['album'] ) ); ?>"
				data-artist="<?php echo esc_attr( strtolower( $track['artist'] ) ); ?>">
				<div class="relative h-11 w-11 flex-shrink-0 overflow-hidden rounded-md bg-gray-200">
					<?php if ( ! empty( $track['cover'] ) ) : ?>
						<img src="<?php echo esc_url( $track['cover'] ); ?>" alt="" class="h-full w-full object-cover" referrerpolicy="no-referrer"/>
					<?php endif; ?>
					<button class="absolute inset-0 flex cursor-pointer items-center justify-center border-none bg-black/50 opacity-0 transition-opacity group-hover:opacity-100"
						data-stream-url="<?php echo esc_url( $track['stream_url'] ); ?>"
						data-track-title="<?php echo esc_attr( $track['title'] ); ?>"
						data-track-artist="<?php echo esc_attr( $track['artist'] ); ?>"
						data-track-cover="<?php echo esc_url( $track['cover'] ); ?>"
						data-track-album-url="<?php echo esc_url( $track['post_url'] ); ?>">
						<svg width="13" height="13" viewBox="0 0 24 24" fill="white"><polygon points="5 3 19 12 5 21 5 3"/></svg>
					</button>
				</div>
				<div class="flex min-w-0 flex-[2] flex-col">
					<span class="truncate text-[13px] font-medium text-gray-900"><?php echo esc_html( $track['title'] ); ?></span>
					<span class="truncate text-[12px] text-gray-500 md:hidden"><?php echo esc_html( $track['artist'] ); ?></span>
				</div>
				<a href="<?php echo esc_url( $track['post_url'] ); ?>" class="hidden flex-[1.5] truncate text-[12.5px] text-roon-blue no-underline hover:underline md:block"><?php echo esc_html( $track['album'] ); ?></a>
				<div class="hidden flex-[1.5] truncate text-[12.5px] text-gray-500 md:block"><?php echo esc_html( $track['artist'] ); ?></div>
				<div class="flex w-[72px] flex-shrink-0 items-center justify-end gap-1">
					<?php
					if ( function_exists( 'roon_fav_heart_button' ) ) {
						echo roon_fav_heart_button( $track, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- đã escape trong hàm.
					}
					?>
					<span class="w-9 text-right text-[12.5px] tabular-nums text-gray-400"><?php echo esc_html( $track['duration'] ); ?></span>
				</div>
			</div>
			<?php endforeach; ?>
		</div>

		<!-- Empty state -->
		<div id="fav-tracks-empty" class="<?php echo empty( $fav_tracks ) ? '' : 'hidden'; ?> py-20 text-center text-gray-400">
			<svg class="mx-auto mb-3" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
			<p class="text-sm"><?php esc_html_e( 'Chưa có bài hát yêu thích nào.', 'roon' ); ?></p>
			<p class="mt-1 text-[12.5px] text-gray-400"><?php esc_html_e( 'Bấm vào biểu tượng trái tim ở mỗi bài hát để thêm vào đây.', 'roon' ); ?></p>
		</div>

		<!-- Phân trang -->
		<div id="fav-tracks-pagination" class="mt-8 hidden items-center justify-center gap-2 pb-8">
			<button id="fav-tracks-prev-page" class="flex h-8 w-8 items-center justify-center rounded-md border-none bg-transparent text-gray-500 transition-colors hover:bg-gray-100 cursor-pointer disabled:opacity-50" disabled>
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
			</button>
			<div id="fav-tracks-page-numbers" class="flex items-center gap-1"></div>
			<button id="fav-tracks-next-page" class="flex h-8 w-8 items-center justify-center rounded-md border-none bg-transparent text-gray-500 transition-colors hover:bg-gray-100 cursor-pointer disabled:opacity-50">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
			</button>
		</div>

	<?php endif; ?>
</div>

<script>
(function () {
	var list = document.getElementById('fav-tracks-list');
	if (!list) return; // chưa đăng nhập

	var searchInput = document.getElementById('fav-tracks-search-input');
	var countEl     = document.getElementById('fav-tracks-count');
	var totalEl     = document.getElementById('fav-tracks-total');
	var emptyEl     = document.getElementById('fav-tracks-empty');

	var currentPage  = 1;
	var itemsPerPage = 10;
	var paginationWrapper = document.getElementById('fav-tracks-pagination');
	var prevBtn     = document.getElementById('fav-tracks-prev-page');
	var nextBtn     = document.getElementById('fav-tracks-next-page');
	var pageNumbers = document.getElementById('fav-tracks-page-numbers');

	function allRows() {
		return Array.from(list.querySelectorAll('.roon-fav-track-row'));
	}

	function render() {
		var q = searchInput ? searchInput.value.trim().toLowerCase() : '';
		var rows = allRows();
		var filtered = [];

		rows.forEach(function (row) {
			var show = !q ||
				(row.dataset.title || '').includes(q) ||
				(row.dataset.album || '').includes(q) ||
				(row.dataset.artist || '').includes(q);
			if (show) {
				filtered.push(row);
			} else {
				row.style.display = 'none';
			}
		});

		var visible = filtered.length;
		var total   = rows.length;
		if (countEl) countEl.textContent = visible + ' bài';
		if (totalEl) totalEl.textContent = total + ' tracks';
		if (emptyEl) emptyEl.classList.toggle('hidden', total > 0);

		var totalPages = Math.ceil(visible / itemsPerPage);
		if (currentPage > totalPages) currentPage = Math.max(1, totalPages);

		if (paginationWrapper) paginationWrapper.classList.toggle('hidden', totalPages <= 1);
		if (prevBtn) prevBtn.disabled = currentPage === 1;
		if (nextBtn) nextBtn.disabled = currentPage === totalPages || totalPages === 0;

		if (pageNumbers) {
			pageNumbers.innerHTML = '';
			for (var i = 1; i <= totalPages; i++) {
				if (totalPages > 6 && i !== 1 && i !== totalPages && Math.abs(i - currentPage) > 1) {
					if (Math.abs(i - currentPage) === 2) {
						var dot = document.createElement('span');
						dot.className = 'text-gray-400 px-1';
						dot.textContent = '...';
						pageNumbers.appendChild(dot);
					}
					continue;
				}
				var btn = document.createElement('button');
				btn.className = 'w-8 h-8 rounded-md border-none cursor-pointer text-[13px] font-medium transition-colors ';
				btn.className += i === currentPage ? 'bg-gray-800 text-white' : 'bg-transparent text-gray-600 hover:bg-gray-100 hover:text-gray-900';
				btn.textContent = i;
				if (i === currentPage) btn.disabled = true;
				(function (page) {
					btn.addEventListener('click', function () {
						currentPage = page;
						render();
						var content = document.getElementById('roon-content');
						if (content) content.scrollTop = 0;
					});
				})(i);
				pageNumbers.appendChild(btn);
			}
		}

		filtered.forEach(function (row, index) {
			var start = (currentPage - 1) * itemsPerPage;
			var end   = start + itemsPerPage;
			row.style.display = (index >= start && index < end) ? '' : 'none';
		});
	}

	if (prevBtn) prevBtn.addEventListener('click', function () { if (currentPage > 1) { currentPage--; render(); } });
	if (nextBtn) nextBtn.addEventListener('click', function () { currentPage++; render(); });
	if (searchInput) searchInput.addEventListener('input', function () { currentPage = 1; render(); });

	// Khi bỏ yêu thích ở bất kỳ đâu → xóa khỏi danh sách này.
	document.addEventListener('roon:fav-changed', function (e) {
		if (!e.detail || e.detail.favorited) return;
		var row = list.querySelector('.roon-fav-track-row[data-fav-key="' + e.detail.key + '"]');
		if (row) {
			row.parentNode.removeChild(row);
			render();
		}
	});

	render();
})();
</script>
