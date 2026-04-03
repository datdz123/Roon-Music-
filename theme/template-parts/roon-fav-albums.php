<?php
/**
 * Template Part: Roon Fav Albums (Most Viewed Albums)
 * @package roon
 */

$popular_albums = function_exists('roon_get_popular_albums') ? roon_get_popular_albums(50) : [];
?>

<div id="page-fav-albums" class="roon-page hidden font-inter">
    <div class="mb-6">
        <h1 class="text-[40px] font-bold tracking-tight text-gray-900 leading-tight m-0">Album có lượt xem nhiều</h1>
        <p class="mt-1 mb-0 text-[13px] text-gray-500"><?php echo count($popular_albums); ?> albums</p>
    </div>

    <!-- Grid -->
    <div class="grid gap-5" style="grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));">
        <?php foreach ($popular_albums as $album) : ?>
        <a class="roon-album-card cursor-pointer group no-underline relative"
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
            <p class="mt-2 mb-0.5 text-[12.5px] font-medium text-gray-900 truncate leading-snug"><?php echo esc_html($album['title']); ?></p>
            <div class="flex items-center justify-between">
                <p class="text-[11.5px] text-gray-500 truncate m-0 pr-2"><?php echo esc_html($album['artist']); ?></p>
                <span class="text-[10px] bg-gray-100 text-gray-500 px-1.5 py-[1px] rounded flex-shrink-0"><?php echo isset($album['views']) ? esc_html($album['views']) : 0; ?> view</span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($popular_albums)) : ?>
    <div class="py-20 text-center text-gray-400">
        <svg class="mx-auto mb-3" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <p class="text-sm">Không có album nào</p>
    </div>
    <?php endif; ?>
</div>
