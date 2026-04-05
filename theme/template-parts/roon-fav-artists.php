<?php
/**
 * Template Part: Roon Fav Artists — Ca Sĩ Yêu Thích
 * Hiển thị danh sách ca sĩ / artist sắp xếp theo lượt xem (nhiều → ít)
 * @package roon
 */

// Sắp xếp theo lượt xem giảm dần (dựa trên số bài viết trong category)
$artist_terms = get_categories(array('hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC'));
$sorted_artists = [];
foreach ($artist_terms as $term) {
    $name = trim($term->name);
    if ('' === $name) continue;
    $words    = preg_split('/\s+/', $name);
    $initials = '';
    foreach (array_slice($words, 0, 2) as $word) {
        $initials .= function_exists('mb_substr') ? mb_substr($word, 0, 1) : substr($word, 0, 1);
    }
    // Lấy ảnh
    $artist_image_url = '';
    if (function_exists('get_field')) {
        $img = get_field('artist_image', 'category_' . $term->term_id);
        if (!empty($img) && is_array($img)) {
            $artist_image_url = $img['sizes']['medium'] ?? $img['url'] ?? '';
        } elseif (!empty($img) && is_string($img)) {
            $artist_image_url = $img;
        }
    }
    
    $sorted_artists[] = array(
        'name'     => $name,
        'initials' => strtoupper($initials),
        'count'    => $term->count,
        'image'    => $artist_image_url,
        'url'      => get_category_link($term->term_id),
    );
}
?>

<div id="page-fav-artists" class="roon-page hidden font-inter">
    <div class="mb-6">
        <h1 class="text-[40px] font-bold tracking-tight text-gray-900 leading-tight m-0">Ca Sĩ Yêu Thích</h1>
        <p class="mt-1 mb-0 text-[13px] text-gray-500"><?php echo count($sorted_artists); ?> ca sĩ</p>
    </div>

    <?php if (!empty($sorted_artists)) : ?>
    <div id="fav-artists-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
        <?php foreach ($sorted_artists as $idx => $artist) : ?>
        <a class="roon-fav-artist-card flex flex-col items-center gap-2.5 cursor-pointer group no-underline"
           href="<?php echo esc_url($artist['url']); ?>"
           title="<?php echo esc_attr($artist['name']); ?>">
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
    
    <!-- Phân trang -->
    <div id="fav-artists-pagination" class="mt-10 flex justify-center gap-2 items-center hidden pb-8">
        <button id="fav-artists-prev-page" class="flex items-center justify-center w-8 h-8 rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors bg-transparent border-none cursor-pointer disabled:opacity-50" disabled>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
        <div id="fav-artists-page-numbers" class="flex items-center gap-1"></div>
        <button id="fav-artists-next-page" class="flex items-center justify-center w-8 h-8 rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors bg-transparent border-none cursor-pointer disabled:opacity-50">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
    </div>
    <?php else : ?>
    <div class="flex flex-col items-center justify-center py-20 text-center">
        <svg class="w-16 h-16 text-gray-200 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/>
            <polygon points="21 8 22.5 11 26 11.5 23.5 13.9 24.1 17.4 21 15.8 17.9 17.4 18.5 13.9 16 11.5 19.5 11"/>
        </svg>
        <p class="text-gray-400 text-[15px]">Chưa có ca sĩ yêu thích nào</p>
    </div>
    <?php endif; ?>
</div>

<script>
(function() {
    var grid        = document.getElementById('fav-artists-grid');
    if (!grid) return;
    
    // Pagination attributes
    var currentPage = 1;
    var itemsPerPage = 18; // 6 cột x 3 hàng = 18
    var paginationWrapper = document.getElementById('fav-artists-pagination');
    var prevBtn = document.getElementById('fav-artists-prev-page');
    var nextBtn = document.getElementById('fav-artists-next-page');
    var pageNumbers = document.getElementById('fav-artists-page-numbers');

    var allCards = Array.from(grid.querySelectorAll('.roon-fav-artist-card'));

    function renderPagination() {
        var visibleCount = allCards.length;
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
