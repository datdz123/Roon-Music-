<?php
/**
 * Template Part: Roon Tracks
 * @package roon
 */

$tracks = function_exists('roon_get_library_tracks') ? roon_get_library_tracks() : [];
?>

<div id="page-tracks" class="roon-page hidden font-inter">
    <div class="flex items-end justify-between mb-5 flex-wrap gap-3">
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

    <div class="flex items-center gap-2 mb-3">
        <button class="flex items-center gap-1.5 text-[13px] text-gray-600 bg-transparent border-none cursor-pointer px-2 py-1.5 rounded-md hover:bg-gray-100 transition-colors">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
            Focus
        </button>
        <button class="flex items-center justify-center w-[30px] h-[30px] rounded-full border border-gray-200 text-gray-400 bg-transparent cursor-pointer hover:border-roon-blue hover:text-roon-blue transition-colors">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
        </button>
    </div>

    <div class="flex items-center gap-3 pb-2 border-b border-gray-200 mb-1">
        <div class="flex items-center gap-1.5 flex-[2]"><span class="text-[12px] text-gray-400">Track</span></div>
        <div class="flex items-center gap-1 flex-[1.5]"><span class="text-[12px] text-gray-400">Album</span></div>
        <div class="flex items-center gap-1 flex-[1.5]"><span class="text-[12px] text-gray-400">Artist</span></div>
        <button class="flex items-center justify-center text-gray-400 hover:text-gray-700 bg-transparent border-none cursor-pointer p-1 transition-colors flex-shrink-0">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <line x1="4" y1="6" x2="20" y2="6"/><line x1="1" y1="12" x2="23" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/>
            </svg>
        </button>
    </div>

    <div class="flex flex-col">
        <?php foreach ($tracks as $track) : ?>
        <a class="flex items-center gap-3 px-2 py-1.5 rounded-lg cursor-pointer group hover:bg-gray-50 transition-colors no-underline" href="<?php echo esc_url($track['post_url']); ?>">
            <div class="relative w-11 h-11 rounded-md overflow-hidden flex-shrink-0 bg-gray-200">
                <img src="<?php echo esc_url($track['cover']); ?>" alt="" class="w-full h-full object-cover"/>
                <button class="absolute inset-0 bg-black/50 flex items-center justify-center border-none cursor-pointer opacity-0 group-hover:opacity-100 transition-opacity"
                        data-stream-url="<?php echo esc_url($track['stream_url']); ?>"
                        data-track-title="<?php echo esc_attr($track['title']); ?>"
                        data-track-artist="<?php echo esc_attr($track['artist']); ?>">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="white"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                </button>
            </div>
            <div class="flex flex-col min-w-0 flex-[2]">
                <span class="text-[13px] font-medium text-gray-900 truncate"><?php echo esc_html($track['num']); ?>. <?php echo esc_html($track['title']); ?></span>
                <span class="text-[12px] text-gray-500 truncate"><?php echo esc_html($track['artist']); ?></span>
            </div>
            <div class="hidden md:block flex-[1.5] text-[12.5px] text-roon-blue truncate"><?php echo esc_html($track['album']); ?></div>
            <div class="hidden md:block flex-[1.5] text-[12.5px] text-roon-blue truncate"><?php echo esc_html($track['artist']); ?></div>
            <div class="flex items-center gap-2.5 flex-shrink-0">
                <span class="text-[12.5px] text-gray-400 min-w-[36px] text-right tabular-nums"><?php echo esc_html($track['duration']); ?></span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
