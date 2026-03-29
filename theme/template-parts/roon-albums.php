<?php
/**
 * Template Part: Roon Albums
 * @package roon
 */

$albums = function_exists('roon_get_library_albums') ? roon_get_library_albums() : [];
?>

<div id="page-albums" class="roon-page hidden font-inter">
    <h1 class="text-[40px] font-bold tracking-tight text-gray-900 mb-6 leading-tight">My Albums</h1>

    <?php get_template_part('template-parts/roon-filter-bar', null, ['type' => 'albums']); ?>

    <div class="grid gap-5" style="grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));">
        <?php foreach ($albums as $album) : ?>
        <a class="roon-album-card cursor-pointer group no-underline" href="<?php echo esc_url($album['url']); ?>">
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
</div>
