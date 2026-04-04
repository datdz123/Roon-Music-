<?php
/**
 * Template Part: Roon Artists
 * @package roon
 */

// Lấy danh sách artists với ảnh ACF từ category
function roon_get_library_artists_with_image() {
    $terms = get_categories(array('hide_empty' => true));
    $artists = array();
    foreach ($terms as $term) {
        $name = trim($term->name);
        if ('' === $name) continue;

        $words    = preg_split('/\s+/', $name);
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= function_exists('mb_substr') ? mb_substr($word, 0, 1) : substr($word, 0, 1);
        }

        // Lấy ảnh từ ACF field (gắn vào category term)
        $artist_image_url = '';
        if (function_exists('get_field')) {
            $img = get_field('artist_image', 'category_' . $term->term_id);
            if (!empty($img) && is_array($img)) {
                $artist_image_url = $img['sizes']['medium'] ?? $img['url'] ?? '';
            } elseif (!empty($img) && is_string($img)) {
                $artist_image_url = $img;
            }
        }

        $artists[] = array(
            'name'     => $name,
            'initials' => strtoupper($initials),
            'image'    => $artist_image_url,
            'url'      => get_category_link($term->term_id),
            'count'    => isset($term->count) ? $term->count : 0,
        );
    }
    return $artists;
}

$artists = roon_get_library_artists_with_image();
?>

<div id="page-artists" class="roon-page hidden font-inter">
    <h1 class="text-[40px] font-bold tracking-tight text-gray-900 leading-tight m-0">Tất cả ca sĩ</h1>
    <p class="text-[13px] text-gray-500 mt-1 mb-5"><?php echo count($artists); ?> ca sĩ</p>

    <!-- Filter Toolbar -->
    <div class="mb-4 flex flex-wrap items-center gap-2">
        <!-- Search -->
        <div class="relative flex-1 min-w-[180px] max-w-xs">
            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input id="artists-search-input"
                   type="text"
                   placeholder="Tìm kiếm ca sĩ…"
                   class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 pl-8 pr-3 text-[13px] text-gray-700 outline-none transition focus:border-roon-blue focus:bg-white focus:ring-2 focus:ring-roon-blue/10"/>
        </div>

        <!-- Sort dropdown -->
        <div class="relative group">
            <button id="artists-sort-btn" class="flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-[13px] text-gray-600 hover:border-gray-300 cursor-pointer">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="6" y1="12" x2="18" y2="12"/><line x1="9" y1="18" x2="15" y2="18"/></svg>
                <span id="artists-sort-label">Sắp xếp: Tên: A → Z</span>
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div class="absolute right-0 top-full mt-1 z-20 hidden group-hover:block min-w-[170px] rounded-lg border border-gray-100 bg-white shadow-lg py-1">
                <button class="artists-sort-action block w-full px-4 py-2 text-left text-[13px] text-gray-700 hover:bg-gray-50 cursor-pointer" data-sort="alpha">Tên: A → Z</button>
                <button class="artists-sort-action block w-full px-4 py-2 text-left text-[13px] text-gray-700 hover:bg-gray-50 cursor-pointer" data-sort="alpha_desc">Tên: Z → A</button>
            </div>
        </div>

        <!-- Total display -->
        <span id="artists-count-label" class="text-[12px] text-gray-400 ml-auto"><?php echo count($artists); ?> kết quả</span>
    </div>

    <!-- Grid -->
    <div id="artists-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
        <?php foreach ($artists as $artist) : ?>
        <a class="roon-artist-card flex flex-col items-center gap-2.5 cursor-pointer group no-underline"
           href="<?php echo esc_url($artist['url']); ?>"
           data-artist-name="<?php echo esc_attr(strtolower($artist['name'])); ?>">
            <div class="w-full pb-[100%] rounded-full bg-gradient-to-br from-indigo-50 to-purple-100 relative overflow-hidden shadow-sm group-hover:shadow-md transition-all duration-200 group-hover:scale-[1.03]">
                <?php if (!empty($artist['image'])) : ?>
                    <img src="<?php echo esc_url($artist['image']); ?>"
                         alt="<?php echo esc_attr($artist['name']); ?>"
                         class="absolute inset-0 w-full h-full object-cover"/>
                <?php else : ?>
                    <span class="absolute inset-0 flex items-center justify-center text-[28px] font-bold text-indigo-400/70 select-none"><?php echo esc_html($artist['initials']); ?></span>
                <?php endif; ?>
                <div class="absolute inset-0 rounded-full bg-black/0 group-hover:bg-black/20 flex items-center justify-center transition-all duration-200">
                    <button class="flex items-center justify-center w-11 h-11 rounded-full bg-roon-blue/90 text-white border-none cursor-pointer scale-0 group-hover:scale-100 transition-transform duration-200 pl-0.5 shadow-lg">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="white"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    </button>
                </div>
            </div>
            <div class="w-full text-center px-1">
                <p class="text-[14px] font-semibold text-gray-800 leading-snug mb-0.5 truncate w-full"><?php echo esc_html($artist['name']); ?></p>
                <p class="text-[12px] text-gray-400 truncate w-full m-0"><?php echo esc_html($artist['count']); ?> album</p>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Empty state -->
    <div id="artists-empty" class="hidden py-20 text-center text-gray-400">
        <svg class="mx-auto mb-3" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <p class="text-sm">Không tìm thấy ca sĩ nào</p>
    </div>

    <!-- Phân trang -->
    <div id="artists-pagination" class="mt-10 flex justify-center gap-2 items-center hidden pb-8">
        <button id="artists-prev-page" class="flex items-center justify-center w-8 h-8 rounded-md text-gray-500 hover:bg-gray-100 transition-colors bg-transparent border-none cursor-pointer disabled:opacity-50" disabled>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
        <div id="artists-page-numbers" class="flex items-center gap-1"></div>
        <button id="artists-next-page" class="flex items-center justify-center w-8 h-8 rounded-md text-gray-500 hover:bg-gray-100 transition-colors bg-transparent border-none cursor-pointer disabled:opacity-50">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
    </div>
</div>

<script>
(function() {
    var searchInput = document.getElementById('artists-search-input');
    var sortBtns    = document.querySelectorAll('.artists-sort-action');
    var sortLabel   = document.getElementById('artists-sort-label');
    var grid        = document.getElementById('artists-grid');
    var countLabel  = document.getElementById('artists-count-label');
    var emptyState  = document.getElementById('artists-empty');
    var sortMode    = 'alpha';
    var sortNames   = { alpha: 'Tên: A → Z', alpha_desc: 'Tên: Z → A' };
    
    // Pagination attributes
    var currentPage = 1;
    var itemsPerPage = 30; // 6 cột x 5 hàng = 30
    var paginationWrapper = document.getElementById('artists-pagination');
    var prevBtn = document.getElementById('artists-prev-page');
    var nextBtn = document.getElementById('artists-next-page');
    var pageNumbers = document.getElementById('artists-page-numbers');

    function filterAndSort() {
        var q = searchInput ? searchInput.value.trim().toLowerCase() : '';
        var cards = Array.from(grid.querySelectorAll('.roon-artist-card'));
        var filteredCards = [];

        // Sort
        cards.sort(function(a, b) {
            var valA = a.dataset.artistName || '';
            var valB = b.dataset.artistName || '';
            if (sortMode === 'alpha') {
                return valA.localeCompare(valB);
            } else if (sortMode === 'alpha_desc') {
                return valB.localeCompare(valA);
            }
            return 0;
        });

        // Search & Filter
        cards.forEach(function(card) {
            var title = (card.dataset.artistName || '').toLowerCase();
            var show = !q || title.includes(q);
            
            if (show) {
                filteredCards.push(card);
            } else {
                card.style.display = 'none';
            }
            grid.appendChild(card); // Update DOM element order
        });

        var visibleCount = filteredCards.length;
        if (countLabel) countLabel.textContent = visibleCount + ' kết quả';
        if (emptyState) {
            if (visibleCount === 0) {
                emptyState.classList.remove('hidden');
            } else {
                emptyState.classList.add('hidden');
            }
        }

        // Render Pagination
        var totalPages = Math.ceil(visibleCount / itemsPerPage);
        if (currentPage > totalPages) currentPage = Math.max(1, totalPages);

        if (paginationWrapper) {
            if (totalPages > 1) {
                paginationWrapper.classList.remove('hidden');
            } else {
                paginationWrapper.classList.add('hidden');
            }
        }

        if (prevBtn) prevBtn.disabled = currentPage <= 1;
        if (nextBtn) nextBtn.disabled = currentPage >= totalPages;

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
                    document.getElementById('page-artists').scrollTop = 0;
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
