<?php
/**
 * Template Part: Roon Home
 * @package roon
 */

$library_stats = function_exists('roon_get_library_stats') ? roon_get_library_stats() : [];
$stats = [
    ['label' => 'ARTISTS',   'count' => $library_stats['artists'] ?? 0,   'icon' => 'artists'],
    ['label' => 'ALBUMS',    'count' => $library_stats['albums'] ?? 0,    'icon' => 'albums'],
    ['label' => 'TRACKS',    'count' => $library_stats['tracks'] ?? 0,    'icon' => 'tracks'],
    ['label' => 'COMPOSERS', 'count' => $library_stats['composers'] ?? 0, 'icon' => 'composers'],
];

$recent_albums = function_exists('roon_get_library_albums') ? roon_get_library_albums(5) : [];
$listen_later = array_slice($recent_albums, 0, 3);
?>

<div id="page-home" class="roon-page font-inter">
    <h1 class="text-[38px] font-bold tracking-tight text-gray-900 mb-6 leading-tight">Hi, ROON</h1>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-8">
        <?php foreach ($stats as $stat) : ?>
        <div class="flex items-center gap-3 sm:gap-4 p-4 sm:p-5 bg-white border border-gray-100 rounded-2xl shadow-sm cursor-pointer hover:shadow-xl hover:border-gray-200 hover:-translate-y-1 transition-all duration-300 group"
             data-page="<?php echo esc_attr($stat['icon']); ?>">
            <div class="text-gray-300 group-hover:text-roon-blue transition-colors duration-300 flex-shrink-0">
                <?php if ($stat['icon'] === 'artists') : ?>
                <svg class="w-7 h-7 sm:w-[34px] sm:h-[34px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <?php elseif ($stat['icon'] === 'albums') : ?>
                <svg class="w-7 h-7 sm:w-[34px] sm:h-[34px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/>
                </svg>
                <?php elseif ($stat['icon'] === 'tracks') : ?>
                <svg class="w-7 h-7 sm:w-[34px] sm:h-[34px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>
                </svg>
                <?php else : ?>
                <svg class="w-7 h-7 sm:w-[34px] sm:h-[34px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/>
                    <line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/>
                    <line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
                </svg>
                <?php endif; ?>
            </div>
            <div class="flex flex-col">
                <span class="text-2xl sm:text-[28px] font-bold text-gray-900 leading-none"><?php echo (int) $stat['count']; ?></span>
                <span class="text-[10px] sm:text-[11px] font-semibold tracking-[0.1em] text-gray-400 uppercase mt-1"><?php echo esc_html($stat['label']); ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="bg-roon-blue rounded-xl px-5 py-5 overflow-hidden">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
            <div class="flex items-center gap-5 flex-wrap">
                <h2 class="text-[16px] font-semibold text-white m-0">Recent activity</h2>
                <div class="flex items-center gap-1" id="recent-tabs">
                    <button data-tab="played" class="roon-tab text-white bg-white/15 px-2.5 py-1 rounded-md text-[12px] font-semibold tracking-wider cursor-pointer border-none transition-all">PLAYED</button>
                    <button data-tab="added" class="roon-tab text-white/60 px-2.5 py-1 rounded-md text-[12px] font-semibold tracking-wider cursor-pointer border-none hover:text-white/90 transition-all">ADDED</button>
                </div>
            </div>
            <div class="flex items-center gap-1.5">
                <button id="btn-recent-prev" class="flex items-center justify-center w-7 h-7 rounded-md text-white/80 hover:bg-white/15 hover:text-white transition-colors">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <button id="btn-recent-next" class="flex items-center justify-center w-7 h-7 rounded-md text-white/80 hover:bg-white/15 hover:text-white transition-colors">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
                <button class="hidden sm:block text-[11px] font-semibold tracking-widest text-white/60 bg-transparent border-none cursor-pointer px-2 py-1 rounded hover:text-white hover:bg-white/10 transition-colors">MORE</button>
            </div>
        </div>

        <div id="recent-albums-grid" class="flex gap-3.5 overflow-x-auto pb-1" style="scrollbar-width:none;">
            <?php foreach ($recent_albums as $album) : ?>
            <a class="roon-album-card flex-shrink-0 w-36 cursor-pointer group no-underline" href="<?php echo esc_url($album['url']); ?>">
                <div class="relative w-full pb-[100%] rounded-lg overflow-hidden bg-gray-700">
                    <img src="<?php echo esc_url($album['cover']); ?>" alt="<?php echo esc_attr($album['title']); ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy"/>
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 flex items-center justify-center transition-all duration-200">
                        <button class="flex items-center justify-center w-10 h-10 rounded-full bg-roon-blue/90 text-white border-none cursor-pointer scale-0 group-hover:scale-100 transition-transform duration-200 pl-0.5">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="white"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        </button>
                    </div>
                </div>
                <p class="mt-2 mb-0.5 text-[12.5px] font-medium text-white truncate leading-snug"><?php echo esc_html($album['title']); ?></p>
                <p class="text-[11.5px] text-white/65 truncate m-0"><?php echo esc_html($album['artist']); ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="mt-7">
        <div class="flex items-center justify-between mb-3.5">
            <h2 class="text-[18px] font-semibold text-gray-900 m-0">Listen later</h2>
            <button class="text-[12px] font-semibold tracking-widest text-gray-400 bg-transparent border-none cursor-pointer px-2 py-1 rounded hover:text-gray-700 hover:bg-gray-100 transition-colors">MORE</button>
        </div>
        <div class="flex gap-4 overflow-x-auto pb-1" style="scrollbar-width:none;">
            <?php foreach ($listen_later as $item) : ?>
            <a class="flex-shrink-0 w-36 cursor-pointer group no-underline" href="<?php echo esc_url($item['url']); ?>">
                <div class="relative w-full pb-[100%] rounded-lg overflow-hidden bg-gray-200">
                    <img src="<?php echo esc_url($item['cover']); ?>" alt="<?php echo esc_attr($item['title']); ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy"/>
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 flex items-center justify-center transition-all duration-200">
                        <button class="flex items-center justify-center w-10 h-10 rounded-full bg-roon-blue/90 text-white border-none cursor-pointer scale-0 group-hover:scale-100 transition-transform duration-200 pl-0.5">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="white"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        </button>
                    </div>
                </div>
                <p class="mt-2 mb-0.5 text-[12.5px] font-medium text-gray-900 truncate leading-snug"><?php echo esc_html($item['title']); ?></p>
                <p class="text-[11.5px] text-gray-500 truncate m-0"><?php echo esc_html($item['artist']); ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
