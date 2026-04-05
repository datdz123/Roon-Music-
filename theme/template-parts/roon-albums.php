<?php
/**
 * Template Part: Roon Albums
 * @package roon
 */

$albums = function_exists('roon_get_library_albums') ? roon_get_library_albums() : [];
?>

<div id="page-albums" class="roon-page hidden font-inter">
    <!-- Header -->
    <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="m-0 text-[40px] font-bold leading-tight tracking-tight text-gray-900">Tất cả album</h1>
            <p class="mt-1 mb-0 text-[13px] text-gray-500"><?php echo count($albums); ?> albums</p>
        </div>
       
    </div>

    <!-- Filter Toolbar -->
    <div class="mb-4 flex flex-wrap items-center gap-2">
        <!-- Search -->
        <div class="relative flex-1 min-w-[180px] max-w-xs">
            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input id="albums-search-input"
                   type="text"
                   placeholder="Tìm kiếm theo Tên Album…"
                   class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 pl-8 pr-3 text-[13px] text-gray-700 outline-none transition focus:border-roon-blue focus:bg-white focus:ring-2 focus:ring-roon-blue/10"/>
        </div>

        <!-- Sort dropdown -->
        <div class="relative group">
            <button id="albums-sort-btn" class="flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-[13px] text-gray-600 hover:border-gray-300 cursor-pointer">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="6" y1="12" x2="18" y2="12"/><line x1="9" y1="18" x2="15" y2="18"/></svg>
                <span id="albums-sort-label">Sắp xếp: Mới nhất</span>
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div class="absolute right-0 top-full mt-1 z-20 hidden group-hover:block min-w-[170px] rounded-lg border border-gray-100 bg-white shadow-lg py-1">
                <button class="albums-sort-action block w-full px-4 py-2 text-left text-[13px] text-gray-700 hover:bg-gray-50 cursor-pointer" data-sort="newest">Ngày thêm: Mới nhất</button>
                <button class="albums-sort-action block w-full px-4 py-2 text-left text-[13px] text-gray-700 hover:bg-gray-50 cursor-pointer" data-sort="oldest">Ngày thêm: Cũ nhất</button>
                <button class="albums-sort-action block w-full px-4 py-2 text-left text-[13px] text-gray-700 hover:bg-gray-50 cursor-pointer" data-sort="alpha">Tên: A → Z</button>
                <button class="albums-sort-action block w-full px-4 py-2 text-left text-[13px] text-gray-700 hover:bg-gray-50 cursor-pointer" data-sort="plays">Lượt nghe nhiều</button>
            </div>
        </div>

        <!-- Total display -->
        <span id="albums-count-label" class="text-[12px] text-gray-400 ml-auto"><?php echo count($albums); ?> kết quả</span>
    </div>

    <!-- Grid -->
    <div id="albums-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
        <?php foreach ($albums as $album) : ?>
        <a class="roon-album-card cursor-pointer group no-underline"
           href="<?php echo esc_url($album['url']); ?>"
           data-album-title="<?php echo esc_attr($album['title']); ?>"
           data-album-artist="<?php echo esc_attr($album['artist']); ?>"
           data-album-year="<?php echo esc_attr($album['year'] ?? 0); ?>">
            <div class="relative w-full pb-[100%] rounded-lg overflow-hidden bg-gray-200">
                <img src="<?php echo esc_url($album['cover']); ?>" alt="<?php echo esc_attr($album['title']); ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy"/>
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 flex items-center justify-center transition-all duration-200">
                    <button class="flex items-center justify-center w-10 h-10 rounded-full bg-roon-blue/90 text-white border-none cursor-pointer scale-0 group-hover:scale-100 transition-transform duration-200 pl-0.5">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="white"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    </button>
                </div>
            </div>
            <p class="mt-2 mb-0.5 text-[12.5px] font-medium text-gray-900 truncate leading-snug"><?php echo esc_html($album['title']); ?></p>
            <p class="text-[11.5px] text-gray-500 truncate m-0"><?php echo esc_html($album['artist']); ?></p>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Empty state -->
    <div id="albums-empty" class="hidden py-20 text-center text-gray-400">
        <svg class="mx-auto mb-3" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <p class="text-sm">Không tìm thấy album nào</p>
    </div>

    <!-- Phân trang -->
    <div id="albums-pagination" class="mt-10 flex justify-center gap-2 items-center hidden">
        <button id="albums-prev-page" class="flex items-center justify-center w-8 h-8 rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors bg-transparent border-none cursor-pointer" disabled>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
        <div id="albums-page-numbers" class="flex items-center gap-1"></div>
        <button id="albums-next-page" class="flex items-center justify-center w-8 h-8 rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors bg-transparent border-none cursor-pointer">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
    </div>
</div>


<script>
(function() {
    var searchInput = document.getElementById('albums-search-input');
    var sortBtns    = document.querySelectorAll('.albums-sort-action');
    var sortLabel   = document.getElementById('albums-sort-label');
    var grid        = document.getElementById('albums-grid');
    var countLabel  = document.getElementById('albums-count-label');
    var emptyState  = document.getElementById('albums-empty');
    var sortMode    = 'newest';
    var sortNames   = { newest: 'Mới nhất', oldest: 'Cũ nhất', alpha: 'Tên: A → Z', plays: 'Lượt nghe nhiều' };
    
    // Pagination attributes
    var currentPage = 1;
    var itemsPerPage = 18; // 6 cột x 3 hàng = 18
    var paginationWrapper = document.getElementById('albums-pagination');
    var prevBtn = document.getElementById('albums-prev-page');
    var nextBtn = document.getElementById('albums-next-page');
    var pageNumbers = document.getElementById('albums-page-numbers');

    function filterAndSort() {
        var q = searchInput ? searchInput.value.trim().toLowerCase() : '';
        var cards = Array.from(grid.querySelectorAll('.roon-album-card'));
        var filteredCards = [];

        // Sort
        cards.sort(function(a, b) {
            if (sortMode === 'alpha') {
                return (a.dataset.albumTitle || '').localeCompare(b.dataset.albumTitle || '');
            }
            if (sortMode === 'oldest') {
                return (parseInt(a.dataset.albumYear) || 0) - (parseInt(b.dataset.albumYear) || 0);
            }
            // newest (default)
            return (parseInt(b.dataset.albumYear) || 0) - (parseInt(a.dataset.albumYear) || 0);
        });

        // Search & Filter
        cards.forEach(function(card) {
            var title  = (card.dataset.albumTitle  || '').toLowerCase();
            var artist = (card.dataset.albumArtist || '').toLowerCase();
            var show   = !q || title.includes(q) || artist.includes(q);
            if (show) {
                filteredCards.push(card);
            } else {
                card.style.display = 'none';
            }
            grid.appendChild(card);
        });

        var visibleCount = filteredCards.length;
        if (countLabel) countLabel.textContent = visibleCount + ' kết quả';
        if (emptyState) emptyState.classList.toggle('hidden', visibleCount > 0);

        // Render Pagination
        var totalPages = Math.ceil(visibleCount / itemsPerPage);
        if (currentPage > totalPages) currentPage = Math.max(1, totalPages);

        if (totalPages > 1) {
            paginationWrapper.classList.remove('hidden');
        } else {
            paginationWrapper.classList.add('hidden');
        }

        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages;

        if (pageNumbers) {
            pageNumbers.innerHTML = '';
            for (let i = 1; i <= totalPages; i++) {
                if (totalPages > 5 && i !== 1 && i !== totalPages && Math.abs(i - currentPage) > 1) {
                    if (Math.abs(i - currentPage) === 2) {
                        const dot = document.createElement('span');
                        dot.className = 'text-gray-400 px-1';
                        dot.textContent = '...';
                        pageNumbers.appendChild(dot);
                    }
                    continue;
                }
                const btn = document.createElement('button');
                btn.className = 'w-8 h-8 rounded-md border-none cursor-pointer transition-colors text-[13px] font-medium ';
                btn.className += i === currentPage ? 'bg-gray-800 text-white' : 'bg-transparent text-gray-600 hover:bg-gray-100 hover:text-gray-900';
                btn.textContent = i;
                if (i === currentPage) { btn.disabled = true; }
                btn.onclick = function() {
                    currentPage = i;
                    filterAndSort();
                    document.getElementById('page-albums').scrollTop = 0;
                    document.getElementById('roon-content').scrollTop = 0;
                };
                pageNumbers.appendChild(btn);
            }
        }

        // Setup DOM visual per chunk
        filteredCards.forEach(function(card, index) {
            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            if (index >= start && index < end) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    if (prevBtn) prevBtn.addEventListener('click', function() { if (currentPage > 1) { currentPage--; filterAndSort(); } });
    if (nextBtn) nextBtn.addEventListener('click', function() { currentPage++; filterAndSort(); });

    if (searchInput) {
        searchInput.addEventListener('input', function() { currentPage = 1; filterAndSort(); });
    }

    sortBtns.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            sortMode = this.dataset.sort;
            if (sortLabel) sortLabel.textContent = 'Sắp xếp: ' + (sortNames[sortMode] || sortMode);
            currentPage = 1;
            filterAndSort();
        });
    });
    
    // Init state
    filterAndSort();
})();
</script>
