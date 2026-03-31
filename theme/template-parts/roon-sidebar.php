<?php
/**
 * Template Part: Roon Sidebar — Cập nhật Menu rút gọn
 * @package roon
 */
?>

<aside id="roon-sidebar"
       class="fixed inset-y-0 left-0 z-[80] flex h-screen w-[280px] max-w-[85vw] -translate-x-full flex-col overflow-y-auto overflow-x-hidden border-r border-gray-200 bg-gray-50 pb-roon-player font-inter shadow-xl transition-transform duration-300 lg:sticky lg:top-0 lg:left-0 lg:z-50 lg:w-roon-sidebar lg:min-w-roon-sidebar lg:max-w-none lg:translate-x-0 lg:shadow-none">

    <!-- ── Logo Row ── -->
    <div class="mb-2 flex flex-shrink-0 items-center justify-between border-b border-gray-100 px-4 pt-5 pb-3">
        <a href="<?php echo home_url('/'); ?>" class="text-[26px] font-bold tracking-tight text-gray-900 no-underline leading-none">roon</a>
        <div class="flex items-center gap-1">
            <button type="button"
                    id="roon-sidebar-close"
                    class="flex h-8 w-8 items-center justify-center rounded-md text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-700 lg:hidden"
                    title="Close menu"
                    aria-label="Close menu">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
            <button class="flex items-center justify-center w-8 h-8 rounded-md text-gray-400 hover:bg-gray-200 hover:text-gray-700 transition-colors" title="Settings">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- ── Menu Items ── -->
    <nav class="flex-1 py-2 select-none">
        <ul class="m-0 p-0 list-none space-y-0.5">
            <?php
            // Menu chắt lọc theo yêu cầu
            $sidebar_items = [
                ['id'=>'home',        'label'=>'Trang chủ',       'page'=>'home',       'icon'=>'home'],
                ['id'=>'lossless',    'label'=>'Nhạc Lossless',   'page'=>'albums',     'icon'=>'disc'],
                ['id'=>'artists',     'label'=>'Nghệ sĩ',         'page'=>'artists',    'icon'=>'users'],
                ['id'=>'search',      'label'=>'Tìm kiếm',        'page'=>'search',     'icon'=>'search'],
                ['id'=>'my-playlists','label'=>'Playlist của tôi','page'=>'my-playlists','icon'=>'music'],
                ['id'=>'fav-albums',  'label'=>'Album yêu thích', 'page'=>'fav-albums', 'icon'=>'heart'],
                ['id'=>'player',      'label'=>'Trình phát',      'page'=>'player',     'icon'=>'play-circle'],
                ['id'=>'queue',       'label'=>'Danh sách phát',  'page'=>'tracks',     'icon'=>'list'],
            ];
            
            foreach ($sidebar_items as $item) :
            ?>
            <li>
                <a href="#"
                   data-page="<?php echo $item['page']; ?>"
                   class="roon-nav-item flex items-center gap-3.5 px-4 py-2.5 mx-2 rounded-lg text-[13.5px] font-medium text-gray-600 no-underline cursor-pointer transition-all duration-200 hover:bg-gray-200/80 hover:text-gray-900 <?php echo $item['id'] === 'home' ? 'text-roon-blue bg-blue-50/80 hover:bg-blue-100/60' : ''; ?>">
                    
                    <?php if ($item['icon'] === 'home') : ?>
                    <svg class="w-[18px] h-[18px] flex-shrink-0 <?php echo $item['id']==='home' ? 'stroke-roon-blue' : 'stroke-gray-500'; ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    <?php elseif ($item['icon'] === 'disc') : ?>
                    <svg class="w-[18px] h-[18px] flex-shrink-0 stroke-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/>
                    </svg>
                    <?php elseif ($item['icon'] === 'users') : ?>
                    <svg class="w-[18px] h-[18px] flex-shrink-0 stroke-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                 
                    <?php elseif ($item['icon'] === 'search') : ?>
                    <svg class="w-[18px] h-[18px] flex-shrink-0 stroke-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <?php elseif ($item['icon'] === 'music') : ?>
                    <svg class="w-[18px] h-[18px] flex-shrink-0 stroke-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>
                    </svg>
                    <?php elseif ($item['icon'] === 'heart') : ?>
                    <svg class="w-[18px] h-[18px] flex-shrink-0 stroke-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                    <?php elseif ($item['icon'] === 'play-circle') : ?>
                    <svg class="w-[18px] h-[18px] flex-shrink-0 stroke-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/>
                    </svg>
                    <?php elseif ($item['icon'] === 'list') : ?>
                    <svg class="w-[18px] h-[18px] flex-shrink-0 stroke-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/>
                        <line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/>
                        <line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
                    </svg>
                    <?php endif; ?>
                    
                    <?php echo $item['label']; ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</aside>
