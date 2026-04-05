<?php
/**
 * Template Part: Roon Fav Albums (Most Viewed Albums)
 * @package roon
 */

$popular_albums = function_exists('roon_get_popular_albums') ? roon_get_popular_albums(100) : [];
?>

<div id="page-fav-albums" class="roon-page hidden font-inter">
    <div class="mb-6">
        <h1 class="text-[40px] font-bold tracking-tight text-gray-900 leading-tight m-0">Album có lượt xem nhiều</h1>
        <p class="mt-1 mb-0 text-[13px] text-gray-500"><?php echo count($popular_albums); ?> albums</p>
    </div>

    <!-- Grid -->
    <div id="fav-albums-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
        <?php foreach ($popular_albums as $album) : ?>
        <a class="roon-fav-album-card cursor-pointer group no-underline relative"
           href="<?php echo esc_url($album['url']); ?>"
           title="<?php echo esc_attr($album['title']); ?>">
            <div class="relative w-full pb-[100%] rounded-lg overflow-hidden bg-gray-200">
                <img src="<?php echo esc_url($album['cover']); ?>" alt="<?php echo esc_attr($album['title']); ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy"/>
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 flex items-center justify-center transition-all duration-200">
                    <button class="flex items-center justify-center w-10 h-10 rounded-full bg-roon-blue/90 text-white border-none cursor-pointer scale-0 group-hover:scale-100 transition-transform duration-200 pl-0.5">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="white"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    </button>
                </div>
            </div>
            <p class="mt-2 mb-0.5 text-[14px] font-semibold text-gray-900 truncate leading-snug"><?php echo esc_html($album['title']); ?></p>
            <div class="flex items-center justify-between">
                <p class="text-[12px] text-gray-500 truncate m-0 pr-2"><?php echo esc_html($album['artist']); ?></p>
                <span class="text-[10px] bg-gray-100 text-gray-500 px-1.5 py-[1px] rounded flex-shrink-0"><?php echo isset($album['views']) ? esc_html($album['views']) : 0; ?> view</span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <div id="fav-albums-empty" class="hidden py-20 text-center text-gray-400">
        <svg class="mx-auto mb-3" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <p class="text-sm">Không có album nào</p>
    </div>

    <!-- Phân trang -->
    <div id="fav-albums-pagination" class="mt-10 flex justify-center gap-2 items-center hidden pb-8">
        <button id="fav-albums-prev-page" class="flex items-center justify-center w-8 h-8 rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors bg-transparent border-none cursor-pointer disabled:opacity-50" disabled>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
        <div id="fav-albums-page-numbers" class="flex items-center gap-1"></div>
        <button id="fav-albums-next-page" class="flex items-center justify-center w-8 h-8 rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors bg-transparent border-none cursor-pointer disabled:opacity-50">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
    </div>
</div>

<script>
(function() {
    var grid        = document.getElementById('fav-albums-grid');
    if (!grid) return;
    var emptyState  = document.getElementById('fav-albums-empty');
    
    // Pagination attributes
    var currentPage = 1;
    var itemsPerPage = 18; // 6 cột x 3 hàng = 18
    var paginationWrapper = document.getElementById('fav-albums-pagination');
    var prevBtn = document.getElementById('fav-albums-prev-page');
    var nextBtn = document.getElementById('fav-albums-next-page');
    var pageNumbers = document.getElementById('fav-albums-page-numbers');

    var allCards = Array.from(grid.querySelectorAll('.roon-fav-album-card'));

    function renderPagination() {
        var visibleCount = allCards.length;
        if (emptyState) {
            if (visibleCount === 0) emptyState.classList.remove('hidden');
            else emptyState.classList.add('hidden');
        }

        var totalPages = Math.ceil(visibleCount / itemsPerPage);
        if (currentPage > totalPages) currentPage = Math.max(1, totalPages);

        if (paginationWrapper) {
            if (totalPages > 1) paginationWrapper.classList.remove('hidden');
            else paginationWrapper.classList.add('hidden');
        }

        if (prevBtn) prevBtn.disabled = currentPage <= 1;
        if (nextBtn) nextBtn.disabled = currentPage >= totalPages;

        if (pageNumbers) {
            pageNumbers.innerHTML = '';
            for (var i = 1; i <= totalPages; i++) {
                var btn = document.createElement('button');
                btn.className = 'w-8 h-8 rounded-md text-[13px] font-medium transition-colors border-none cursor-pointer flex items-center justify-center ';
                if (i === currentPage) {
                    btn.className += 'bg-roon-blue text-white';
                } else {
                    btn.className += 'bg-transparent text-gray-500 hover:bg-gray-100';
                }
                btn.textContent = i;
                (function(page) {
                    btn.addEventListener('click', function() {
                        currentPage = page;
                        renderGridDisplay();
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    });
                })(i);
                pageNumbers.appendChild(btn);
            }
        }
    }

    function renderGridDisplay() {
        allCards.forEach(function(card, index) {
            var startIdx = (currentPage - 1) * itemsPerPage;
            var endIdx   = startIdx + itemsPerPage;
            if (index >= startIdx && index < endIdx) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
        renderPagination();
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                renderGridDisplay();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            var totalPages = Math.ceil(allCards.length / itemsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                renderGridDisplay();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    }

    renderGridDisplay();
})();
</script>
