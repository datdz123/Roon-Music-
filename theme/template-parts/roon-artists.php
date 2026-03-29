<?php
/**
 * Template Part: Roon Artists
 * @package roon
 */

$artists = function_exists('roon_get_library_artists') ? roon_get_library_artists() : [];
?>

<div id="page-artists" class="roon-page hidden font-inter">
    <h1 class="text-[40px] font-bold tracking-tight text-gray-900 leading-tight m-0">My Artists</h1>
    <p class="text-[13px] text-gray-500 mt-1 mb-5"><?php echo count($artists); ?> artists</p>

    <div class="flex items-center gap-2 mb-4">
        <button class="flex items-center gap-2 bg-roon-blue text-white text-[13px] font-semibold px-5 py-2 rounded-full border-none cursor-pointer hover:bg-roon-indigo transition-colors">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="white"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            Play now
        </button>
        <button class="flex items-center justify-center bg-roon-blue text-white w-9 h-9 rounded-full border-none cursor-pointer hover:bg-roon-indigo transition-colors">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
    </div>

    <?php get_template_part('template-parts/roon-filter-bar', null, ['type' => 'artists']); ?>

    <div class="grid gap-6" style="grid-template-columns: repeat(auto-fill, minmax(120px,1fr));">
        <?php foreach ($artists as $artist) : ?>
        <div class="flex flex-col items-center gap-2.5 cursor-pointer group" data-artist="<?php echo esc_attr($artist['name']); ?>">
            <div class="relative w-28 h-28 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden transition-shadow duration-200 group-hover:shadow-xl">
                <span class="text-[26px] font-normal text-gray-600 tracking-tight select-none transition-opacity duration-200 group-hover:opacity-0"><?php echo esc_html($artist['initials']); ?></span>
                <div class="absolute inset-0 rounded-full bg-black/0 group-hover:bg-black/35 flex items-center justify-center transition-all duration-200">
                    <button class="flex items-center justify-center w-9 h-9 rounded-full bg-roon-blue/90 text-white border-none cursor-pointer scale-0 group-hover:scale-100 transition-transform duration-200 pl-0.5">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="white"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    </button>
                </div>
            </div>
            <p class="text-[12.5px] font-normal text-gray-600 text-center m-0 leading-snug"><?php echo esc_html($artist['name']); ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</div>
