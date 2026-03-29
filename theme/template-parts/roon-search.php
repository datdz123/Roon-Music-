<?php
/**
 * Template Part: Roon Search — Tailwind classes
 * @package roon
 */
?>

<div id="page-search" class="roon-page hidden font-inter w-full mt-4">

    <!-- Header & Input Area -->
    <div class="w-full max-w-4xl mx-auto px-4 flex flex-col items-center">
        <!-- Heading -->
        <h1 class="text-[20px] font-bold text-gray-900 mb-6 w-full text-center md:w-3/4">Tìm kiếm</h1>

        <!-- Search Input -->
        <div class="relative w-full md:w-3/4 mb-5">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </div>
            <input type="text" id="roon-search-input" 
                   class="w-full bg-white border border-gray-300 text-gray-900 text-[14px] rounded-full focus:outline-none focus:border-roon-blue focus:ring-1 focus:ring-roon-blue block pl-11 pr-10 py-3 transition-colors shadow-sm placeholder-gray-400" 
                   placeholder="Tìm kiếm bài hát, album, nghệ sĩ..." autocomplete="off">
            <!-- Clear Button -->
            <button id="roon-search-clear" class="absolute hidden inset-y-0 right-2 pr-2 pl-2 flex items-center text-gray-400 hover:text-gray-700 bg-transparent border-none cursor-pointer">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- Filters (Chips) -->
        <div class="flex items-center justify-start md:justify-center gap-2 mb-12 w-full md:w-3/4 overflow-x-auto pb-2 scrollbar-none" style="scrollbar-width: none;">
            <button class="flex-shrink-0 bg-green-500 text-white text-[13px] font-medium px-4 py-1.5 rounded-full border-none cursor-pointer">Tất cả</button>
            <button class="flex-shrink-0 bg-gray-100/80 text-gray-700 hover:bg-gray-200 text-[13px] font-medium px-4 py-1.5 rounded-full border border-gray-200 cursor-pointer transition-colors">Bài hát</button>
            <button class="flex-shrink-0 bg-gray-100/80 text-gray-700 hover:bg-gray-200 text-[13px] font-medium px-4 py-1.5 rounded-full border border-gray-200 cursor-pointer transition-colors">Nhạc Lossless</button>
            <button class="flex-shrink-0 bg-gray-100/80 text-gray-700 hover:bg-gray-200 text-[13px] font-medium px-4 py-1.5 rounded-full border border-gray-200 cursor-pointer transition-colors">Nghệ sĩ</button>
        </div>
    </div>

    <!-- ===== STATES ===== -->

    <!-- State: Empty (No query) -->
    <div id="search-state-empty" class="flex flex-col items-center justify-center text-center w-full max-w-3xl mx-auto py-10 mt-4">
        <div class="flex items-center justify-center w-16 h-16 rounded-full border-[1.5px] border-gray-200 mb-5 bg-gray-50/50">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="text-green-500">
                <path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>
            </svg>
        </div>
        <h2 class="text-[16px] font-semibold text-gray-900 m-0 mb-1.5">Bắt đầu tìm kiếm âm nhạc yêu thích</h2>
        <p class="text-[13px] text-gray-500 m-0">Tìm album và nghệ sĩ bằng cách nhập từ khóa ở trên</p>
    </div>

    <!-- State: Results -->
    <div id="search-state-results" class="hidden w-full mx-auto pb-10">
        <!-- Tracks Section -->
        <div class="mb-12">
            <div class="flex justify-between items-end mb-4 border-b border-gray-100 pb-2">
                <h2 class="text-[18px] font-bold text-gray-900 m-0">Bài hát</h2>
                <a href="#" class="text-[12px] text-gray-500 hover:text-gray-800 font-medium no-underline mb-0.5">Xem tất cả &rarr;</a>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-8 gap-y-1.5">
                <?php 
                $search_tracks = [
                    ['title'=>'01 - Miracle', 'artist'=>"12 Girls Band\nThe Best of 12 Girls Band (2000)", 'duration'=>'2:46', 'cover'=>'https://placehold.co/48x48/6a4a3a/f0d0b0?text=M'],
                    ['title'=>'02 - El Condor Pasa', 'artist'=>"12 Girls Band\nThe Best of 12 Girls Band (2000)", 'duration'=>'4:33', 'cover'=>'https://placehold.co/48x48/6a4a3a/f0d0b0?text=E'],
                    ['title'=>'03 - New Classicism', 'artist'=>"12 Girls Band\nThe Best of 12 Girls Band (2000)", 'duration'=>'5:40', 'cover'=>'https://placehold.co/48x48/6a4a3a/f0d0b0?text=NC'],
                    ['title'=>'05 - Take Five', 'artist'=>"12 Girls Band\nThe Best of 12 Girls Band (2000)", 'duration'=>'2:44', 'cover'=>'https://placehold.co/48x48/6a4a3a/f0d0b0?text=TF'],
                    ['title'=>'06 - Reel Around the Sun (from Riverdance)', 'artist'=>"12 Girls Band\nThe Best of 12 Girls Band (2000)", 'duration'=>'5:03', 'cover'=>'https://placehold.co/48x48/6a4a3a/f0d0b0?text=RA'],
                    ['title'=>'07 - Whispering Earth', 'artist'=>"12 Girls Band\nThe Best of 12 Girls Band (2000)", 'duration'=>'4:54', 'cover'=>'https://placehold.co/48x48/6a4a3a/f0d0b0?text=WE'],
                    ['title'=>'08 - Dunhuang', 'artist'=>"12 Girls Band\nThe Best of 12 Girls Band (2000)", 'duration'=>'4:27', 'cover'=>'https://placehold.co/48x48/6a4a3a/f0d0b0?text=DH'],
                    ['title'=>'09 - The Great Valley', 'artist'=>"12 Girls Band\nThe Best of 12 Girls Band (2000)", 'duration'=>'5:05', 'cover'=>'https://placehold.co/48x48/6a4a3a/f0d0b0?text=GV'],
                    ['title'=>'10 - Carnival', 'artist'=>"12 Girls Band\nThe Best of 12 Girls Band (2000)", 'duration'=>'5:44', 'cover'=>'https://placehold.co/48x48/6a4a3a/f0d0b0?text=CV'],
                    ['title'=>'12 Girls Band - Beautiful Energy', 'artist'=>"Joshi Juni Gakubou\nBeautiful Energy", 'duration'=>'65:52', 'cover'=>'https://placehold.co/48x48/e0c0a0/6a4a3a?text=BE'],
                ];
                foreach ($search_tracks as $track) : ?>
                <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer group border border-transparent hover:border-gray-100">
                    <div class="relative w-[46px] h-[46px] flex-shrink-0 bg-gray-200 rounded overflow-hidden">
                        <img src="<?php echo esc_url($track['cover']); ?>" class="w-full h-full object-cover"/>
                        <button class="absolute inset-0 bg-black/40 flex items-center justify-center border-none opacity-0 group-hover:opacity-100 transition-opacity pl-0.5" data-stream-url="#" data-track-title="<?php echo esc_attr($track['title']); ?>" data-track-artist="<?php echo esc_attr(explode("\n", $track['artist'])[0]); ?>" data-track-cover="<?php echo esc_url($track['cover']); ?>">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="white"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        </button>
                    </div>
                    <div class="flex flex-col flex-1 min-w-0 justify-center gap-0.5">
                        <div class="text-[13px] font-semibold text-gray-900 truncate leading-none"><?php echo esc_html($track['title']); ?></div>
                        <div class="text-[11.5px] text-gray-500 leading-tight line-clamp-2"><?php echo nl2br(esc_html($track['artist'])); ?></div>
                    </div>
                    <div class="text-[12px] text-gray-400 tabular-nums pr-2 flex-shrink-0"><?php echo esc_html($track['duration']); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Lossless Section -->
        <div>
            <div class="flex justify-between items-end mb-4 border-b border-gray-100 pb-2">
                <h2 class="text-[18px] font-bold text-gray-900 m-0">Nhạc Lossless</h2>
                <a href="#" class="text-[12px] text-gray-500 hover:text-gray-800 font-medium no-underline mb-0.5">Xem tất cả &rarr;</a>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                <?php 
                $search_albums = [
                    ['title'=>'Lossless Music', 'cover'=>'https://placehold.co/180x180/374151/e2e8f0?text=lossless+music'],
                    ['title'=>'Lossless Music', 'cover'=>'https://placehold.co/180x180/374151/e2e8f0?text=lossless+music'],
                    ['title'=>'Lossless Music', 'cover'=>'https://placehold.co/180x180/374151/e2e8f0?text=lossless+music'],
                    ['title'=>'Lossless Music', 'cover'=>'https://placehold.co/180x180/374151/e2e8f0?text=lossless+music'],
                    ['title'=>'21st Century Breakdown', 'cover'=>'https://placehold.co/180x180/8b0000/ffffff?text=Green+Day'],
                    ['title'=>'Bruno Mars XXIV', 'cover'=>'https://placehold.co/180x180/e5e5e5/333333?text=Bruno'],
                ];
                foreach ($search_albums as $album) : ?>
                <div class="roon-album-card cursor-pointer group" data-page-target="single-album" data-album-title="<?php echo esc_attr($album['title']); ?>" data-album-artist="Various Artists">
                    <div class="relative w-full pb-[100%] rounded-md overflow-hidden bg-gray-200 mb-2 shadow-sm border border-gray-100">
                        <img src="<?php echo esc_url($album['cover']); ?>" class="absolute inset-0 w-full h-full object-cover transition-transform group-hover:scale-105 duration-300"/>
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center">
                            <button class="w-10 h-10 rounded-full bg-roon-blue text-white flex items-center justify-center border-none opacity-0 group-hover:opacity-100 scale-75 group-hover:scale-100 transition-transform shadow-md pl-0.5">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
