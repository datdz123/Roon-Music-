<?php
/**
 * The template for displaying all single posts (Roon Style Album page)
 *
 * @package roon
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php wp_title('|', true, 'right'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php wp_head(); ?>
</head>
<body <?php body_class('roon-body'); ?>>
<?php wp_body_open(); ?>

<div id="roon-app" class="flex h-screen overflow-hidden bg-white font-inter">

    <!-- Sidebar -->
    <?php get_template_part('template-parts/roon-sidebar'); ?>

    <!-- Main Area -->
    <div id="roon-main" class="flex-1 min-w-0 flex flex-col h-screen overflow-hidden">

        <!-- Header Bar -->
        <?php get_template_part('template-parts/roon-header-bar'); ?>

        <!-- Scrollable Page Content -->
        <div id="roon-content" class="flex-1 overflow-y-auto overflow-x-hidden px-8 py-7 pb-roon-player"
             style="scrollbar-width:thin; scrollbar-color:#d1d5db transparent;">

			<?php
			while ( have_posts() ) :
				the_post();

                // Lấy thông tin Album
                $album_title = get_the_title();
                $album_cover = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'large') : 'https://placehold.co/240x240/3d2a1a/e8d5b0?text=ALBUM';
                
                // Trích xuất Categories -> Tên Ca sĩ
                $categories = get_the_category();
                $artist_name = !empty($categories) ? $categories[0]->name : 'Nhiều ca sĩ';
                
                // Track list: Jellyfin Album ID first, manual ACF repeater as fallback.
                $tracks = function_exists('roon_get_post_album_tracks') ? roon_get_post_album_tracks(get_the_ID()) : [];
                $track_count = is_array($tracks) ? count($tracks) : 0;
            ?>

            <!-- Bọc trong container có cấu trúc giống SPA layout - ID được đổi một chút tránh conflict nếu rẽ nhánh -->
            <div id="page-single-album-wp" class="font-inter w-full max-w-5xl mx-auto">
                
                <!-- ── Album Hero ── -->
                <div class="flex gap-7 items-start mb-7 flex-wrap">

                    <!-- Cover Art -->
                    <div class="flex-shrink-0 w-[200px] h-[200px] rounded-xl overflow-hidden shadow-md bg-gray-200">
                        <img src="<?php echo esc_url($album_cover); ?>"
                                alt="<?php echo esc_attr($album_title); ?>"
                                class="w-full h-full object-cover"/>
                    </div>

                    <!-- Metadata column -->
                    <div class="flex flex-col gap-1.5 flex-1 min-w-0">
                        <p class="text-[13px] text-gray-500 m-0 uppercase font-semibold tracking-wider">Album</p>
                        <h1 class="text-[34px] font-bold tracking-tight text-gray-900 m-0 leading-[1.1]"><?php echo esc_html($album_title); ?></h1>
                        <p class="text-[15px] font-medium text-gray-500 m-0"><?php echo esc_html($artist_name); ?></p>

                        <!-- Action buttons row -->
                        <div class="flex items-center gap-2 mt-2 flex-wrap">
                            <button class="flex items-center gap-2 bg-roon-blue text-white text-[13px] font-semibold px-5 py-2 rounded-full border-none cursor-pointer hover:bg-roon-indigo transition-colors" onclick="alert('Chức năng Play All sẽ gọi vào RoonPlayer')">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="white"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                Phát tất cả
                            </button>
                            <button class="flex items-center justify-center bg-roon-blue text-white w-9 h-9 rounded-full border-none cursor-pointer hover:bg-roon-indigo transition-colors">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                            <!-- Icon buttons -->
                            <?php
                            $icon_btns = [
                                ['title'=>'Lưu thư viện', 'icon'=>'heart'],
                                ['title'=>'Tìm kiếm',     'icon'=>'search'],
                                ['title'=>'Thêm',         'icon'=>'more'],
                            ];
                            foreach ($icon_btns as $btn) : ?>
                            <button title="<?php echo esc_attr($btn['title']); ?>"
                                    class="flex items-center justify-center w-[34px] h-[34px] rounded-full border border-gray-200 text-gray-500 bg-transparent cursor-pointer hover:border-gray-400 transition-colors">
                                <?php if ($btn['icon'] === 'heart') : ?>
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                </svg>
                                <?php elseif ($btn['icon'] === 'search') : ?>
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                </svg>
                                <?php else : ?>
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
                                    <circle cx="5" cy="12" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="19" cy="12" r="1.8"/>
                                </svg>
                                <?php endif; ?>
                            </button>
                            <?php endforeach; ?>
                        </div>

                        <!-- Info row -->
                        <div class="flex items-center gap-3 text-[12px] text-gray-400 mt-2 flex-wrap">
                            <span><?php echo $track_count; ?> Bài hát</span>
                            <span>/ Đã đăng <?php echo get_the_date('d/m/Y'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- ── Tabs: TRACKS / NỘI DUNG ── -->
                <div class="flex items-end border-b border-gray-200 mb-4 gap-0" id="album-tabs-wp">
                    <button data-album-tab="tracks"
                            class="album-tab-wp px-3.5 py-2 text-[12px] font-semibold tracking-wider text-roon-blue border-b-2 border-roon-blue -mb-px bg-transparent cursor-pointer transition-colors">BÀI HÁT</button>
                    <button data-album-tab="credits"
                            class="album-tab-wp px-3.5 py-2 text-[12px] font-semibold tracking-wider text-gray-400 border-b-2 border-transparent -mb-px bg-transparent cursor-pointer hover:text-gray-600 transition-colors">THÔNG TIN</button>
                </div>

                <!-- ── Tracks Tab ── -->
                <div id="album-tab-tracks-wp">
                    <div class="flex flex-col divide-y divide-gray-100">
                        <?php 
                        if ($tracks) :
                            $index = 1;
                            foreach ($tracks as $track) : 
                                $t_title    = !empty($track['track_title']) ? $track['track_title'] : 'Unknown Track';
                                $t_duration = !empty($track['track_duration']) ? $track['track_duration'] : '--:--';
                                $t_url      = !empty($track['stream_url']) ? $track['stream_url'] : '#';
                                $t_dl       = !empty($track['download_url']) ? $track['download_url'] : '#';
                        ?>
                        <div class="flex items-center gap-3 py-2.5 px-2 cursor-pointer group hover:bg-gray-50 transition-colors rounded-lg">
                            <!-- Index -->
                            <span class="text-gray-400 text-[13px] w-5 text-right font-medium"><?php echo $index; ?></span>

                            <!-- Play circle button -->
                            <button class="flex-shrink-0 p-0 border-none bg-transparent cursor-pointer transition-transform hover:scale-110 ml-1"
                                    data-stream-url="<?php echo esc_url($t_url); ?>"
                                    data-track-title="<?php echo esc_attr($t_title); ?>"
                                    data-track-artist="<?php echo esc_attr($artist_name); ?>"
                                    data-track-cover="<?php echo esc_url($album_cover); ?>">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="#3b3ef6">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polygon points="10 8 16 12 10 16 10 8" fill="white"/>
                                </svg>
                            </button>

                            <!-- Track title -->
                            <div class="flex-1 text-[13.5px] text-gray-800 min-w-0 truncate font-medium"><?php echo esc_html($t_title); ?></div>

                            <!-- Right actions -->
                            <div class="flex items-center gap-4 flex-shrink-0">
                                <!-- Duration -->
                                <span class="text-[13px] text-gray-400 tabular-nums min-w-[36px] text-right"><?php echo esc_html($t_duration); ?></span>
                                
                                <!-- Download -->
                                <?php if ($t_dl && $t_dl !== '#') : ?>
                                <a href="<?php echo esc_url($t_dl); ?>" download
                                   class="text-gray-400 hover:text-roon-blue transition-colors no-underline flex items-center bg-gray-100/50 hover:bg-gray-200 px-2.5 py-1.5 rounded-md"
                                   title="Tải xuống">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                        <polyline points="7 10 12 15 17 10"/>
                                        <line x1="12" y1="15" x2="12" y2="3"/>
                                    </svg>
                                    <span class="text-[11px] font-semibold tracking-wide ml-1.5 hidden sm:inline-block">TẢI</span>
                                </a>
                                <?php else: ?>
                                    <div class="w-[50px]"></div> <!-- Placeholder for alignment if no download link -->
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php 
                            $index++;
                            endforeach; 
                        else :
                        ?>
                            <div class="py-10 text-center flex flex-col items-center">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#ddd" stroke-width="1.5" class="mb-3">
                                    <path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>
                                </svg>
                                <p class="text-[14px] text-gray-500 m-0">Album này chưa có danh sách bài hát nào.</p>
                                <p class="text-[12px] text-gray-400 m-0 mt-1">Sử dụng ACF Repeater trong trang chỉnh sửa bài viết để thêm bài hát.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ── Content Tab (hidden) ── -->
                <div id="album-tab-credits-wp" class="hidden py-6 text-gray-700 text-[14.5px] prose max-w-none">
                    <?php the_content(); ?>
                </div>

            </div>

			<?php endwhile; ?>

        </div>
    </div>

    <!-- Audio Player Fixed Bottom -->
    <?php get_template_part('template-parts/roon-player'); ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Simple Tabs logic for Single WP Post because script.js might be targeting SPAs ID exclusively
        const tabs = document.querySelectorAll('.album-tab-wp');
        const tracksTab = document.getElementById('album-tab-tracks-wp');
        const creditsTab = document.getElementById('album-tab-credits-wp');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => {
                    t.classList.remove('text-roon-blue', 'border-roon-blue');
                    t.classList.add('text-gray-400', 'border-transparent');
                });
                tab.classList.remove('text-gray-400', 'border-transparent');
                tab.classList.add('text-roon-blue', 'border-roon-blue');

                if (tab.dataset.albumTab === 'tracks') {
                    tracksTab.classList.remove('hidden');
                    creditsTab.classList.add('hidden');
                } else {
                    tracksTab.classList.add('hidden');
                    creditsTab.classList.remove('hidden');
                }
            });
        });
    });
</script>

<?php wp_footer(); ?>
</body>
</html>
