<?php
/**
 * Component: Popular Albums
 *
 * @package roon
 */

$title = isset($args['title']) ? $args['title'] : 'Album có lượt xem nhiều';
$popular_albums = function_exists('roon_get_popular_albums') ? roon_get_popular_albums(6) : [];
if (empty($popular_albums)) {
    $recent_albums = function_exists('roon_get_library_albums') ? roon_get_library_albums(6) : [];
    $popular_albums = array_slice($recent_albums, 0, 6);
}
?>
    <div class="mt-8">
        <div class="flex items-center justify-between mb-3.5">
            <h2 class="text-[18px] font-semibold text-gray-900 m-0"><?php echo esc_html($title); ?></h2>
            <button data-page="fav-albums" class="text-[12px] font-semibold tracking-wide text-gray-400 bg-transparent border-none cursor-pointer px-2 py-1 rounded hover:text-roon-blue hover:bg-blue-50 transition-colors">XEM THÊM <svg width="12" height="12" class="inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg></button>
        </div>
        <?php if (!empty($popular_albums)) : ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4" style="scrollbar-width:none;">
            <?php foreach ($popular_albums as $item) : ?>
            <a class="w-full cursor-pointer group no-underline" href="<?php echo esc_url($item['url']); ?>" title="<?php echo esc_attr($item['title']); ?>">
                <div class="relative w-full pb-[100%] rounded-lg overflow-hidden bg-gray-200">
                    <img src="<?php echo esc_url($item['cover']); ?>" alt="<?php echo esc_attr($item['title']); ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy"/>
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 flex items-center justify-center transition-all duration-200">
                        <button class="flex items-center justify-center w-10 h-10 rounded-full bg-roon-blue/90 text-white border-none cursor-pointer scale-0 group-hover:scale-100 transition-transform duration-200 pl-0.5">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="white"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        </button>
                    </div>
                </div>
                <p class="mt-2 mb-0.5 text-[12.5px] font-medium text-gray-900 truncate leading-snug"><?php echo esc_html($item['title']); ?></p>
                <div class="flex items-center justify-between">
                    <p class="text-[11.5px] text-gray-500 truncate m-0 flex-1 pr-1"><?php echo esc_html($item['artist']); ?></p>
                    <span class="text-[10px] text-gray-400 bg-gray-100 px-1 rounded flex-shrink-0"><?php echo isset($item['views']) ? esc_html($item['views']) : 0; ?> view</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else : ?>
            <p class="text-sm text-gray-400">Chưa có album nào.</p>
        <?php endif; ?>
    </div>
