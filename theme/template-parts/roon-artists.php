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
        );
    }
    return $artists;
}

$artists = roon_get_library_artists_with_image();
?>

<div id="page-artists" class="roon-page hidden font-inter">
    <h1 class="text-[40px] font-bold tracking-tight text-gray-900 leading-tight m-0">My Artists</h1>
    <p class="text-[13px] text-gray-500 mt-1 mb-5"><?php echo count($artists); ?> artists</p>

    <div class="flex items-center gap-2 mb-4 flex-wrap">
        <button class="flex items-center gap-2 bg-roon-blue text-white text-[13px] font-semibold px-5 py-2 rounded-full border-none cursor-pointer hover:bg-roon-indigo transition-colors">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="white"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            Play now
        </button>

        <!-- Search artists -->
        <div class="relative ml-auto">
            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input id="artists-search-input" type="text" placeholder="Tìm ca sĩ…"
                   class="rounded-lg border border-gray-200 bg-gray-50 py-2 pl-8 pr-3 text-[13px] text-gray-700 outline-none focus:border-roon-blue focus:bg-white focus:ring-2 focus:ring-roon-blue/10 transition w-[200px]"/>
        </div>
    </div>

    <div id="artists-grid" class="grid gap-6" style="grid-template-columns: repeat(auto-fill, minmax(120px,1fr));">
        <?php foreach ($artists as $artist) : ?>
        <a class="roon-artist-card flex flex-col items-center gap-2.5 cursor-pointer group no-underline"
           href="<?php echo esc_url($artist['url']); ?>"
           data-artist-name="<?php echo esc_attr(strtolower($artist['name'])); ?>">
            <div class="relative w-28 h-28 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden transition-shadow duration-200 group-hover:shadow-xl">
                <?php if (!empty($artist['image'])) : ?>
                    <img src="<?php echo esc_url($artist['image']); ?>"
                         alt="<?php echo esc_attr($artist['name']); ?>"
                         class="w-full h-full object-cover"/>
                <?php else : ?>
                    <span class="text-[26px] font-normal text-gray-600 tracking-tight select-none transition-opacity duration-200 group-hover:opacity-0"><?php echo esc_html($artist['initials']); ?></span>
                <?php endif; ?>
                <div class="absolute inset-0 rounded-full bg-black/0 group-hover:bg-black/35 flex items-center justify-center transition-all duration-200">
                    <button class="flex items-center justify-center w-9 h-9 rounded-full bg-roon-blue/90 text-white border-none cursor-pointer scale-0 group-hover:scale-100 transition-transform duration-200 pl-0.5">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="white"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    </button>
                </div>
            </div>
            <p class="text-[12.5px] font-normal text-gray-600 text-center m-0 leading-snug"><?php echo esc_html($artist['name']); ?></p>
        </a>
        <?php endforeach; ?>
    </div>

    <div id="artists-empty" class="hidden py-20 text-center text-gray-400">
        <svg class="mx-auto mb-3" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <p class="text-sm">Không tìm thấy ca sĩ nào</p>
    </div>
</div>

<script>
(function() {
    var input  = document.getElementById('artists-search-input');
    var grid   = document.getElementById('artists-grid');
    var empty  = document.getElementById('artists-empty');
    if (!input || !grid) return;
    input.addEventListener('input', function() {
        var q = this.value.trim().toLowerCase();
        var cards = grid.querySelectorAll('.roon-artist-card');
        var visible = 0;
        cards.forEach(function(c) {
            var match = !q || (c.dataset.artistName || '').includes(q);
            c.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        if (empty) empty.classList.toggle('hidden', visible > 0);
    });
})();
</script>
