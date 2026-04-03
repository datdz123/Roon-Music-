<?php
/**
 * Template Part: Roon Tracks
 * @package roon
 */

$tracks = function_exists('roon_get_library_tracks') ? roon_get_library_tracks() : [];
?>

<div id="page-tracks" class="roon-page hidden font-inter">
    <div class="flex items-end justify-between mb-4 flex-wrap gap-3">
        <div>
            <h1 class="text-[40px] font-bold tracking-tight text-gray-900 leading-tight m-0">
                My Tracks <span class="text-[16px] font-normal text-gray-400 ml-2"><?php echo count($tracks); ?> tracks</span>
            </h1>
        </div>
        <button class="flex items-center gap-2 bg-roon-blue text-white text-[13px] font-semibold px-5 py-2 rounded-full border-none cursor-pointer hover:bg-roon-indigo transition-colors">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="white"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            Play now
        </button>
    </div>

    <!-- Search + Filter bar -->
    <div class="mb-3 flex flex-wrap items-center gap-2">
        <!-- Search -->
        <div class="relative flex-1 min-w-[180px] max-w-sm">
            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input id="tracks-search-input" type="text" placeholder="Tìm kiếm bài hát, ca sĩ, album…"
                   class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 pl-8 pr-3 text-[13px] text-gray-700 outline-none focus:border-roon-blue focus:bg-white focus:ring-2 focus:ring-roon-blue/10 transition"/>
        </div>
        <!-- Count -->
        <span id="tracks-count" class="ml-auto text-[12px] text-gray-400"><?php echo count($tracks); ?> bài</span>
    </div>

    <!-- Column headers -->
    <div class="flex items-center gap-3 pb-2 border-b border-gray-200 mb-1 px-2">
        <div class="w-11 flex-shrink-0"></div><!-- thumbnail col -->
        <div class="flex items-center gap-1 flex-[2]">
            <button class="tracks-sort-btn flex items-center gap-1 text-[12px] text-gray-400 hover:text-gray-700 bg-transparent border-none cursor-pointer p-0" data-col="title">
                Track
                <svg class="tracks-sort-icon hidden" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
        </div>
        <div class="hidden md:flex items-center gap-1 flex-[1.5]">
            <button class="tracks-sort-btn flex items-center gap-1 text-[12px] text-gray-400 hover:text-gray-700 bg-transparent border-none cursor-pointer p-0" data-col="album">
                Album
                <svg class="tracks-sort-icon hidden" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
        </div>
        <div class="hidden md:flex items-center gap-1 flex-[1.5]">
            <button class="tracks-sort-btn flex items-center gap-1 text-[12px] text-gray-400 hover:text-gray-700 bg-transparent border-none cursor-pointer p-0" data-col="artist">
                Artist
                <svg class="tracks-sort-icon hidden" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
        </div>
        <div class="flex items-center justify-center flex-shrink-0 w-10">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="text-gray-400">
                <line x1="4" y1="6" x2="20" y2="6"/><line x1="1" y1="12" x2="23" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/>
            </svg>
        </div>
    </div>

    <div id="tracks-list" class="flex flex-col">
        <?php foreach ($tracks as $track) : ?>
        <div class="roon-track-row flex items-center gap-3 px-2 py-1.5 rounded-lg cursor-pointer group hover:bg-gray-50 transition-colors"
             data-title="<?php echo esc_attr(strtolower($track['title'])); ?>"
             data-album="<?php echo esc_attr(strtolower($track['album'])); ?>"
             data-artist="<?php echo esc_attr(strtolower($track['artist'])); ?>"
             data-post-url="<?php echo esc_url($track['post_url']); ?>">
            <div class="relative w-11 h-11 rounded-md overflow-hidden flex-shrink-0 bg-gray-200">
                <img src="<?php echo esc_url($track['cover']); ?>" alt="" class="w-full h-full object-cover"/>
                <button class="absolute inset-0 bg-black/50 flex items-center justify-center border-none cursor-pointer opacity-0 group-hover:opacity-100 transition-opacity"
                        data-stream-url="<?php echo esc_url($track['stream_url']); ?>"
                        data-track-title="<?php echo esc_attr($track['title']); ?>"
                        data-track-artist="<?php echo esc_attr($track['artist']); ?>"
                        data-track-cover="<?php echo esc_url($track['cover']); ?>"
                        data-track-album-url="<?php echo esc_url($track['post_url']); ?>">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="white"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                </button>
            </div>
            <div class="flex flex-col min-w-0 flex-[2]">
                <span class="text-[13px] font-medium text-gray-900 truncate"><?php echo esc_html($track['num']); ?>. <?php echo esc_html($track['title']); ?></span>
                <span class="text-[12px] text-gray-500 truncate"><?php echo esc_html($track['artist']); ?></span>
            </div>
            <a href="<?php echo esc_url($track['post_url']); ?>" class="hidden md:block flex-[1.5] text-[12.5px] text-roon-blue truncate no-underline hover:underline"><?php echo esc_html($track['album']); ?></a>
            <div class="hidden md:block flex-[1.5] text-[12.5px] text-roon-blue truncate"><?php echo esc_html($track['artist']); ?></div>
            <div class="flex items-center gap-2.5 flex-shrink-0 w-10 justify-end">
                <span class="text-[12.5px] text-gray-400 tabular-nums"><?php echo esc_html($track['duration']); ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div id="tracks-empty" class="hidden py-16 text-center text-gray-400">
        <svg class="mx-auto mb-3" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <p class="text-sm">Không tìm thấy bài hát nào</p>
    </div>
</div>

<script>
(function() {
    var searchInput = document.getElementById('tracks-search-input');
    var list        = document.getElementById('tracks-list');
    var countEl     = document.getElementById('tracks-count');
    var emptyEl     = document.getElementById('tracks-empty');
    var sortBtns    = document.querySelectorAll('.tracks-sort-btn');
    var sortCol     = null;
    var sortAsc     = true;

    function filterAndSort() {
        var q = searchInput ? searchInput.value.trim().toLowerCase() : '';
        var rows = Array.from(list.querySelectorAll('.roon-track-row'));

        // Sort
        if (sortCol) {
            rows.sort(function(a, b) {
                var va = (a.dataset[sortCol] || '');
                var vb = (b.dataset[sortCol] || '');
                return sortAsc ? va.localeCompare(vb) : vb.localeCompare(va);
            });
            rows.forEach(function(r) { list.appendChild(r); });
        }

        // Filter
        var visible = 0;
        rows.forEach(function(row) {
            var show = !q ||
                (row.dataset.title  || '').includes(q) ||
                (row.dataset.album  || '').includes(q) ||
                (row.dataset.artist || '').includes(q);
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        if (countEl) countEl.textContent = visible + ' bài';
        if (emptyEl) emptyEl.classList.toggle('hidden', visible > 0);
    }

    if (searchInput) searchInput.addEventListener('input', filterAndSort);

    sortBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var col = this.dataset.col;
            if (sortCol === col) {
                sortAsc = !sortAsc;
            } else {
                sortCol = col;
                sortAsc = true;
            }
            // Update icons
            document.querySelectorAll('.tracks-sort-icon').forEach(function(ic) { ic.classList.add('hidden'); });
            var icon = this.querySelector('.tracks-sort-icon');
            if (icon) {
                icon.classList.remove('hidden');
                icon.style.transform = sortAsc ? '' : 'rotate(180deg)';
            }
            filterAndSort();
        });
    });
})();
</script>
