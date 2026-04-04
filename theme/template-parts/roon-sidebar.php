<?php
/**
 * Template Part: Roon Sidebar — Menu cập nhật + QR Popup
 * @package roon
 */

// Lấy ảnh QR từ ACF option page
$donate_qr = function_exists('get_field') ? get_field('donate_qr_image', 'option') : null;
$donate_qr_url = '';
if (!empty($donate_qr) && is_array($donate_qr) && !empty($donate_qr['url'])) {
    $donate_qr_url = esc_url($donate_qr['url']);
} elseif (!empty($donate_qr) && is_string($donate_qr)) {
    $donate_qr_url = esc_url($donate_qr);
}
?>

<aside id="roon-sidebar"
       class="fixed inset-y-0 left-0 z-[80] flex h-screen w-[280px] max-w-[85vw] -translate-x-full flex-col overflow-y-auto overflow-x-hidden border-r border-gray-200 bg-gray-50 pb-roon-player font-inter shadow-xl transition-transform duration-300 lg:sticky lg:top-0 lg:left-0 lg:z-50 lg:w-roon-sidebar lg:min-w-roon-sidebar lg:max-w-none lg:translate-x-0 lg:shadow-none">

    <!-- ── Logo Row ── -->
    <div class="mb-2 flex flex-shrink-0 items-center justify-between border-b border-gray-100 px-4 pt-5 pb-3">
        <a href="<?php echo home_url('/'); ?>" class="text-[26px] font-bold tracking-tight text-gray-900 no-underline leading-none flex items-center">
            <?php
            if (has_custom_logo()) {
                $custom_logo_id = get_theme_mod('custom_logo');
                $logo = wp_get_attachment_image_src($custom_logo_id, 'full');
                echo '<img src="' . esc_url($logo[0]) . '" alt="' . esc_attr(get_bloginfo('name')) . '" class="h-8 max-w-[150px] w-auto">';
            } else {
                echo esc_html(get_bloginfo('name'));
            }
            ?>
        </a>
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
            
        </div>
    </div>

    <!-- ── Menu Items ── -->
    <nav class="flex-1 py-2 select-none">
        <ul class="m-0 p-0 list-none space-y-0.5">
            <?php
            // Menu cập nhật theo bảng yêu cầu
            $sidebar_items = [
                ['id'=>'home',          'label'=>'Trang Chủ',              'page'=>'home',          'icon'=>'home',         'type'=>'nav'],
                ['id'=>'albums',        'label'=>'Album Nhạc',             'page'=>'albums',        'icon'=>'disc',         'type'=>'nav'],
                ['id'=>'artists',       'label'=>'Ca Sĩ / Artist',        'page'=>'artists',       'icon'=>'users',        'type'=>'nav'],
                ['id'=>'tracks',        'label'=>'Bài Hát / Track',        'page'=>'tracks',        'icon'=>'music-note',   'type'=>'nav'],
                ['id'=>'search',        'label'=>'Tìm Kiếm',              'page'=>'search',        'icon'=>'search',       'type'=>'nav'],
                ['id'=>'fav-albums',    'label'=>'Album có lượt xem nhiều',       'page'=>'fav-albums',    'icon'=>'heart',        'type'=>'nav'],
                ['id'=>'fav-artists',   'label'=>'Ca Sĩ Yêu Thích',      'page'=>'fav-artists',   'icon'=>'star-user',    'type'=>'nav'],

                ['id'=>'donate',        'label'=>'Donate / Ủng hộ',       'page'=>'',              'icon'=>'gift',         'type'=>'popup-qr'],
                ['id'=>'contact',       'label'=>'Liên Hệ Quảng Cáo',    'page'=>'contact',       'icon'=>'mail',         'type'=>'nav'],
            ];
            
            foreach ($sidebar_items as $item) :
                $is_home   = $item['id'] === 'home' && !is_single();
                $is_albums = $item['id'] === 'albums' && is_single();
                $is_active = $is_home || $is_albums;
                $is_popup  = $item['type'] === 'popup-qr';
            ?>
            <li>
                <a href="<?php echo $is_popup ? '#' : esc_url( home_url( '/#' . $item['page'] ) ); ?>"
                   <?php if (!$is_popup) : ?>data-page="<?php echo esc_attr( $item['page'] ); ?>"<?php endif; ?>
                   <?php if ($is_popup) : ?>id="roon-donate-trigger" role="button"<?php endif; ?>
                   class="roon-nav-item flex items-center gap-3.5 px-4 py-3 mx-2 rounded-lg text-[15px] font-semibold text-gray-800 no-underline cursor-pointer transition-all duration-200 hover:bg-gray-200/80 hover:text-gray-950 <?php echo $is_active ? 'text-roon-blue bg-blue-50/80 hover:bg-blue-100/60' : ''; ?>">
                    
                    <?php /* ── Icon: home ── */ ?>
                    <?php if ($item['icon'] === 'home') : ?>
                    <svg class="w-[20px] h-[20px] flex-shrink-0 <?php echo $is_active && $item['id'] === 'home' ? 'stroke-roon-blue' : 'stroke-gray-700'; ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>

                    <?php /* ── Icon: disc (album) ── */ ?>
                    <?php elseif ($item['icon'] === 'disc') : ?>
                    <svg class="w-[20px] h-[20px] flex-shrink-0 <?php echo $is_active && $item['id'] === 'albums' ? 'stroke-roon-blue' : 'stroke-gray-700'; ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/>
                    </svg>

                    <?php /* ── Icon: users (ca sĩ) ── */ ?>
                    <?php elseif ($item['icon'] === 'users') : ?>
                    <svg class="w-[20px] h-[20px] flex-shrink-0 stroke-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>

                    <?php /* ── Icon: music-note (bài hát / track) ── */ ?>
                    <?php elseif ($item['icon'] === 'music-note') : ?>
                    <svg class="w-[20px] h-[20px] flex-shrink-0 stroke-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>
                    </svg>

                    <?php /* ── Icon: search ── */ ?>
                    <?php elseif ($item['icon'] === 'search') : ?>
                    <svg class="w-[20px] h-[20px] flex-shrink-0 stroke-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>

                    <?php /* ── Icon: heart (album yêu thích) ── */ ?>
                    <?php elseif ($item['icon'] === 'heart') : ?>
                    <svg class="w-[20px] h-[20px] flex-shrink-0 stroke-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>

                    <?php /* ── Icon: star-user (ca sĩ yêu thích) ── */ ?>
                    <?php elseif ($item['icon'] === 'star-user') : ?>
                    <svg class="w-[20px] h-[20px] flex-shrink-0 stroke-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/>
                        <polygon points="21 8 22.5 11 26 11.5 23.5 13.9 24.1 17.4 21 15.8 17.9 17.4 18.5 13.9 16 11.5 19.5 11" fill="none" stroke="currentColor" stroke-width="1.8"/>
                    </svg>

                    <?php /* ── Icon: play-circle (trình phát) ── */ ?>
                    <?php elseif ($item['icon'] === 'play-circle') : ?>
                    <svg class="w-[20px] h-[20px] flex-shrink-0 stroke-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/>
                    </svg>

                    <?php /* ── Icon: gift (donate) ── */ ?>
                    <?php elseif ($item['icon'] === 'gift') : ?>
                    <svg class="w-[20px] h-[20px] flex-shrink-0 stroke-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 12 20 22 4 22 4 12"/>
                        <rect x="2" y="7" width="20" height="5"/>
                        <line x1="12" y1="22" x2="12" y2="7"/>
                        <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/>
                        <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>
                    </svg>

                    <?php /* ── Icon: mail (liên hệ) ── */ ?>
                    <?php elseif ($item['icon'] === 'mail') : ?>
                    <svg class="w-[20px] h-[20px] flex-shrink-0 stroke-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    <?php endif; ?>
                    
                    <?php echo $item['label']; ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</aside>

<!-- ── QR Donate Popup Modal ── -->
<div id="roon-qr-modal"
     class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300"
     style="display:none;">
    <div class="relative w-[90vw] max-w-[380px] bg-white rounded-2xl shadow-2xl p-8 text-center transform scale-95 transition-transform duration-300"
         id="roon-qr-modal-content">
        <!-- Close -->
        <button id="roon-qr-close"
                class="absolute top-3 right-3 w-9 h-9 flex items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-800 transition-colors"
                aria-label="Đóng">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>

        <!-- Title -->
        <div class="mb-5">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-gradient-to-br from-rose-400 to-pink-500 mb-3 shadow-lg">
                <svg class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-1">Ủng hộ Roon Music</h3>
            <p class="text-[15px] text-gray-500 leading-relaxed">Quét mã QR bên dưới để ủng hộ<br>duy trì và phát triển dịch vụ 🎵</p>
        </div>

        <!-- QR Image -->
        <?php if ($donate_qr_url) : ?>
        <div class="bg-gray-50 rounded-xl p-4 mb-4 inline-block">
            <img src="<?php echo $donate_qr_url; ?>"
                 alt="Mã QR Donate"
                 class="w-[220px] h-[220px] object-contain rounded-lg mx-auto"
                 loading="lazy"/>
        </div>
        <?php else : ?>
        <div class="bg-gray-50 rounded-xl p-8 mb-4">
            <p class="text-gray-400 text-sm">Chưa cài đặt ảnh mã QR.<br>Vui lòng cập nhật trong<br><strong>Cài đặt Site → Cài đặt chung</strong></p>
        </div>
        <?php endif; ?>

        <p class="text-[13px] text-gray-400 mt-2">Cảm ơn bạn rất nhiều! 💖</p>
    </div>
</div>

<script>
(function() {
    var trigger = document.getElementById('roon-donate-trigger');
    var modal   = document.getElementById('roon-qr-modal');
    var content = document.getElementById('roon-qr-modal-content');
    var closeBtn = document.getElementById('roon-qr-close');

    if (!trigger || !modal) return;

    function openModal(e) {
        e.preventDefault();
        modal.style.display = 'flex';
        requestAnimationFrame(function() {
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('opacity-100');
            content.classList.remove('scale-95');
            content.classList.add('scale-100');
        });
    }

    function closeModal() {
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0', 'pointer-events-none');
        content.classList.remove('scale-100');
        content.classList.add('scale-95');
        setTimeout(function() { modal.style.display = 'none'; }, 300);
    }

    trigger.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.classList.contains('opacity-0')) closeModal();
    });
})();
</script>
