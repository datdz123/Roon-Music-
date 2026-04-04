<?php
/**
 * Template Part: Roon Audio Player — Tailwind classes
 * @package roon
 */
?>

<div id="roon-player"
     class="fixed bottom-0 left-0 right-0 z-100 flex flex-wrap items-center gap-3 border-t border-gray-200 bg-white px-3 py-2 shadow-[0_-2px_12px_rgba(0,0,0,0.06)] font-inter sm:px-4 md:h-roon-player md:flex-nowrap md:gap-4 md:py-0">

    <!-- Hidden audio element -->
    <audio id="roon-audio" preload="none"></audio>

    <!-- ── Left: Track Info (260px) ── -->
    <div class="flex min-w-0 flex-1 items-center gap-2.5 md:w-[260px] md:flex-shrink-0 md:flex-none">
        <!-- Thumb (clickable → về trang album) -->
        <a id="player-album-link" href="#" class="block w-11 h-11 rounded-md overflow-hidden flex-shrink-0 bg-gray-200 no-underline cursor-pointer hover:opacity-80 transition-opacity" title="Xem album">
            <img id="player-cover"
                 src="https://placehold.co/48x48/e5e5e5/999?text=♫"
                 alt="Now playing"
                 class="w-full h-full object-cover"/>
        </a>
        <!-- Info -->
        <div class="flex flex-col min-w-0 flex-1">
            <a id="player-track-title-link" href="#" class="text-[13px] font-medium text-gray-900 truncate m-0 no-underline hover:text-roon-blue transition-colors cursor-pointer">Chọn bài để phát</a>
            <p id="player-track-artist" class="text-[11.5px] text-gray-500 truncate m-0 mt-0.5">—</p>
        </div>
        <!-- Heart -->
      
    </div>

    <!-- ── Center: Controls + Progress ── -->
    <div class="order-3 flex w-full min-w-0 flex-col items-center gap-1.5 md:order-none md:flex-1 md:w-auto">
        <!-- Controls row -->
        <div class="flex items-center gap-1.5 sm:gap-2">
            <!-- Shuffle -->
            <button id="player-shuffle" title="Shuffle"
                    class="hidden md:flex items-center justify-center w-8 h-8 rounded-md text-gray-400 hover:bg-gray-100 hover:text-gray-700 bg-transparent border-none cursor-pointer transition-colors">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <polyline points="16 3 21 3 21 8"/><line x1="4" y1="20" x2="21" y2="3"/>
                    <polyline points="21 16 21 21 16 21"/><line x1="15" y1="15" x2="21" y2="21"/>
                </svg>
            </button>
            <!-- Prev -->
            <button id="player-prev" title="Previous"
                    class="flex items-center justify-center w-8 h-8 rounded-md text-gray-600 hover:bg-gray-100 hover:text-gray-900 bg-transparent border-none cursor-pointer transition-colors">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <polygon points="19 20 9 12 19 4 19 20"/><line x1="5" y1="19" x2="5" y2="5"/>
                </svg>
            </button>
            <!-- Play/Pause -->
            <button id="player-play-pause" title="Play/Pause"
                    class="flex items-center justify-center w-9 h-9 rounded-full bg-gray-900 text-white border-none cursor-pointer hover:bg-roon-blue transition-colors pl-0.5">
                <svg id="player-play-icon" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <polygon points="5 3 19 12 5 21 5 3"/>
                </svg>
                <svg id="player-pause-icon" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" class="hidden">
                    <rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/>
                </svg>
            </button>
            <!-- Next -->
            <button id="player-next" title="Next"
                    class="flex items-center justify-center w-8 h-8 rounded-md text-gray-600 hover:bg-gray-100 hover:text-gray-900 bg-transparent border-none cursor-pointer transition-colors">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <polygon points="5 4 15 12 5 20 5 4"/><line x1="19" y1="5" x2="19" y2="19"/>
                </svg>
            </button>
            <!-- Repeat -->
            <button id="player-repeat" title="Repeat"
                    class="hidden md:flex items-center justify-center w-8 h-8 rounded-md text-gray-400 hover:bg-gray-100 hover:text-gray-700 bg-transparent border-none cursor-pointer transition-colors">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/>
                    <polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/>
                </svg>
            </button>
        </div>

        <!-- Progress bar row -->
        <div class="flex items-center gap-2 w-full">
            <span id="player-current-time" class="text-[11px] text-gray-400 min-w-[30px] flex-shrink-0 tabular-nums">0:00</span>
            <!-- Progress track -->
            <div id="player-progress-bar"
                 class="flex-1 h-1 bg-gray-200 rounded-full relative cursor-pointer group/progress">
                <div id="player-progress-fill"
                     class="h-full bg-gray-800 rounded-full transition-none group-hover/progress:bg-roon-blue"
                     style="width:0%"></div>
                <div id="player-progress-thumb"
                     class="absolute top-1/2 -translate-y-1/2 -translate-x-1/2 w-3 h-3 rounded-full bg-gray-900 scale-0 group-hover/progress:scale-100 transition-transform pointer-events-none"
                     style="left:0%"></div>
            </div>
            <span id="player-total-time" class="text-[11px] text-gray-400 min-w-[30px] flex-shrink-0 text-right tabular-nums">0:00</span>
        </div>
    </div>

    <!-- ── Right: Volume + Extras ── -->
    <div class="order-2 flex items-center gap-1.5 justify-end md:w-auto md:flex-shrink-0">
      
        <!-- Mute button -->
        <button id="player-mute" title="Mute/Unmute"
                class="flex items-center justify-center w-7 h-7 text-gray-400 hover:text-gray-700 bg-transparent border-none cursor-pointer transition-colors">
            <svg id="player-vol-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/>
                <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/>
            </svg>
        </button>
        <!-- Volume bar -->
        <div id="player-volume-bar"
             class="relative hidden h-1 w-20 cursor-pointer rounded-full bg-gray-200 group/vol md:block">
            <div id="player-volume-fill"
                 class="h-full bg-gray-600 rounded-full group-hover/vol:bg-roon-blue transition-colors"
                 style="width:75%"></div>
            <div id="player-volume-thumb"
                 class="absolute top-1/2 -translate-y-1/2 -translate-x-1/2 w-2.5 h-2.5 rounded-full bg-gray-900 scale-0 group-hover/vol:scale-100 transition-transform pointer-events-none"
                 style="left:75%"></div>
        </div>
    </div>

</div>

<!-- Affiliate Setup script for Ads popup -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    function openShopeeAd() {
        if (!window.roonPlayerSettings || !window.roonPlayerSettings.affiliateUrl) return;
        
        let adOpenedCount = parseInt(localStorage.getItem('roon_ad_count_' + new Date().toDateString()) || '0');
        const adLimit = parseInt(window.roonPlayerSettings.dailyAffiliateLimit) || 2;
        const affUrl = window.roonPlayerSettings.affiliateUrl;

        if (adOpenedCount >= adLimit) return;
        
        window.open(affUrl, '_blank');
        localStorage.setItem('roon_ad_count_' + new Date().toDateString(), adOpenedCount + 1);
    }

    document.addEventListener('click', function(e) {
        // Áp dụng cho: Nút bài hát, Nút Phát tất cả, Nút Play của Player chính.
        const isPlayBtn = e.target.closest('[data-stream-url], #play-all-tracks, #player-play-pause');
        if (isPlayBtn) {
            openShopeeAd();
        }
    });
});
</script>
