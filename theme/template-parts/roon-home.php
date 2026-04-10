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


$recent_albums = function_exists('roon_get_library_albums') ? roon_get_library_albums(10) : [];
$popular_albums = function_exists('roon_get_popular_albums') ? roon_get_popular_albums(10) : array_slice($recent_albums, 0, 10);

	global $wpdb;
	$cache_key = 'roon_popular_artists_top10';
	$popular_artists = get_transient($cache_key);

	if ( is_array($popular_artists) ) {
		foreach ($popular_artists as $cached_artist) {
			if ( ! is_array($cached_artist) || ! array_key_exists('url', $cached_artist) ) {
				$popular_artists = false;
				delete_transient($cache_key);
				break;
			}
		}
	}

	if ( false === $popular_artists ) {
		// Dùng SQL query gộp (JOIN) để sum tổng view count của mỗi ca sĩ (category) một lần duy nhất thay vì N+1 queries.
		$query = "
			SELECT tt.term_id, SUM(CAST(pm.meta_value AS UNSIGNED)) as total_views
			FROM {$wpdb->terms} t
			INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
			INNER JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
			INNER JOIN {$wpdb->postmeta} pm ON tr.object_id = pm.post_id AND pm.meta_key = 'roon_view_count'
			WHERE tt.taxonomy = 'category'
			GROUP BY tt.term_id
			ORDER BY total_views DESC
		";
		$views_results = $wpdb->get_results($query);
		$term_views = [];
		if ( ! empty($views_results) ) {
			foreach ($views_results as $row) {
				$term_views[$row->term_id] = (int) $row->total_views;
			}
		}

		$artist_terms = get_categories(array('hide_empty' => true));
		$popular_artists = [];

		foreach ($artist_terms as $term) {
			$name = trim($term->name);
			if ('' === $name) {
				continue;
			}
			$words = preg_split('/\s+/', $name);
			$initials = '';
			foreach (array_slice($words, 0, 2) as $word) {
				$initials .= function_exists('mb_substr') ? mb_substr($word, 0, 1) : substr($word, 0, 1);
			}

			// Chỉ tra cứu mảng đã tổng hợp thay vì dùng get_posts và get_post_meta qua nhiều tầng lặp.
			$artist_views = isset($term_views[$term->term_id]) ? $term_views[$term->term_id] : 0;

			$popular_artists[] = array(
				'name'     => $name,
				'initials' => strtoupper($initials),
				'count'    => $term->count, // số album
				'views'    => $artist_views,
				'url'      => get_term_link($term),
			);
		}

		usort($popular_artists, function($a, $b) {
			return $b['views'] <=> $a['views'];
		});

		$popular_artists = array_slice($popular_artists, 0, 10);
		set_transient($cache_key, $popular_artists, 4 * HOUR_IN_SECONDS);
	}

?>

<div id="page-home" class="roon-page font-inter">
    <h1 class="text-[38px] font-bold tracking-tight text-gray-900 mb-6 leading-tight">Xin chào !</h1>

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

    <!-- Mới Cập Nhật -->
    <div class="bg-roon-blue rounded-xl px-5 py-5 overflow-hidden">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
            <div class="flex items-center gap-5 flex-wrap">
                <h2 class="text-[16px] font-semibold text-white m-0">Mới cập nhật</h2>
                <div class="flex items-center gap-1" id="recent-tabs">
                    <button data-tab="played" class="roon-tab text-white bg-white/15 px-2.5 py-1 rounded-md text-[12px] font-semibold tracking-wider cursor-pointer border-none transition-all">ALBUM MỚI PHÁT</button>
                    <button data-tab="added" class="roon-tab text-white/60 px-2.5 py-1 rounded-md text-[12px] font-semibold tracking-wider cursor-pointer border-none hover:text-white/90 transition-all">ALBUM MỚI THÊM</button>
                </div>
            </div>
            <div class="flex items-center gap-1.5">
                <button id="btn-recent-prev" class="flex items-center justify-center w-7 h-7 rounded-md text-white/80 hover:bg-white/15 hover:text-white transition-colors">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <button id="btn-recent-next" class="flex items-center justify-center w-7 h-7 rounded-md text-white/80 hover:bg-white/15 hover:text-white transition-colors">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
                <button data-page="albums" class="hidden sm:block text-[11px] font-semibold tracking-widest text-white/60 bg-transparent border-none cursor-pointer px-2 py-1 rounded hover:text-white hover:bg-white/10 transition-colors">XEM THÊM <svg width="12" height="12" class="inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg></button>
            </div>
        </div>

        <div id="recent-albums-wrap">
            <div id="grid-played" class="recent-albums-grid flex gap-4 overflow-x-auto scroll-smooth snap-x snap-mandatory pb-4" style="scrollbar-width:none;">
                <?php 
                // Album mới phát (giả lập bằng những album random hoặc phổ biến)
                $played_albums = function_exists('roon_get_popular_albums') ? roon_get_popular_albums(12) : $recent_albums; 
                shuffle($played_albums); // Shuffle để có cảm giác khác biệt
                foreach ($played_albums as $album) : ?>
                <a class="roon-album-card flex-shrink-0 snap-start w-[calc(50%-8px)] sm:w-[calc(33.33%-10.6px)] md:w-[calc(25%-12px)] lg:w-[calc(16.66%-13.3px)] cursor-pointer group no-underline" href="<?php echo esc_url($album['url']); ?>" title="<?php echo esc_attr($album['title']); ?>">
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

            <div id="grid-added" class="recent-albums-grid flex gap-4 overflow-x-auto scroll-smooth snap-x snap-mandatory pb-4 hidden" style="scrollbar-width:none;">
                <?php foreach ($recent_albums as $album) : ?>
                <a class="roon-album-card flex-shrink-0 snap-start w-[calc(50%-8px)] sm:w-[calc(33.33%-10.6px)] md:w-[calc(25%-12px)] lg:w-[calc(16.66%-13.3px)] cursor-pointer group no-underline" href="<?php echo esc_url($album['url']); ?>" title="<?php echo esc_attr($album['title']); ?>">
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
    </div>

    <!-- Script for Recently Tab -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.roon-tab');
            const grids = document.querySelectorAll('.recent-albums-grid');

            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const targetId = 'grid-' + this.dataset.tab;
                    
                    tabs.forEach(t => {
                        t.classList.remove('bg-white/15', 'text-white');
                        t.classList.add('text-white/60');
                    });
                    this.classList.remove('text-white/60');
                    this.classList.add('bg-white/15', 'text-white');

                    grids.forEach(g => g.classList.add('hidden'));
                    const targetGrid = document.getElementById(targetId);
                    if (targetGrid) targetGrid.classList.remove('hidden');
                });
            });

            // Slider Logic cho Mới Cập Nhật
            const btnPrev = document.getElementById('btn-recent-prev');
            const btnNext = document.getElementById('btn-recent-next');

            if (btnPrev && btnNext) {
                btnPrev.addEventListener('click', function() {
                    const activeGrid = document.querySelector('.recent-albums-grid:not(.hidden)');
                    if(activeGrid) {
                        // Trượt một đoạn bằng 80% chiều rộng khung hiển thị
                        activeGrid.scrollBy({ left: - (activeGrid.clientWidth * 0.8), behavior: 'smooth' });
                    }
                });
                btnNext.addEventListener('click', function() {
                    const activeGrid = document.querySelector('.recent-albums-grid:not(.hidden)');
                    if(activeGrid) {
                        activeGrid.scrollBy({ left: (activeGrid.clientWidth * 0.8), behavior: 'smooth' });
                    }
                });
            }
        });
    </script>

    <?php 
    $ll_title = function_exists('get_field') ? get_field('listen_later_title', 'option') : '';
    $ll_desc  = function_exists('get_field') ? get_field('listen_later_desc', 'option') : '';
    if (!empty($ll_title)) : 
        $title_parts = explode(' ', $ll_title, 2);
        $title_html  = esc_html($title_parts[0]) . (isset($title_parts[1]) ? '<br>' . esc_html($title_parts[1]) : '');
    ?>
    <div class="mt-8 bg-gradient-to-r from-purple-50 to-pink-50 rounded-2xl p-6 sm:p-8 flex flex-col sm:flex-row items-center sm:items-start justify-center sm:justify-start gap-6 border border-purple-100/50">
        <div class="text-center sm:text-left">
            <h2 class="text-3xl sm:text-4xl font-serif text-gray-900 leading-none"><?php echo $title_html; ?></h2>
        </div>
        <div class="hidden sm:block w-px h-16 bg-gradient-to-b from-transparent via-purple-200 to-transparent"></div>
        <div class="text-center sm:text-left flex-1">
            <h3 class="text-lg sm:text-xl font-medium text-gray-900 mb-1.5"><?php echo nl2br(esc_html($ll_desc)); ?></h3>
            <p class="text-sm text-gray-500 max-w-lg mb-0 flex items-center justify-center sm:justify-start">Click the <svg class="inline w-4 h-4 text-gray-400 mx-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg> button on any album, artist or track to add it to your Listen Later</p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Album Listen Later / Views -->
    <?php get_template_part('template-parts/roon', 'popular-albums'); ?>

    <!-- Ca sĩ nghe nhiều -->
    <div class="mt-8">
        <div class="flex items-center justify-between mb-3.5">
            <h2 class="text-[18px] font-semibold text-gray-900 m-0">Ca sỹ nghe nhiều</h2>
            <button data-page="fav-artists" class="text-[12px] font-semibold tracking-wide text-gray-400 bg-transparent border-none cursor-pointer px-2 py-1 rounded hover:text-roon-blue hover:bg-blue-50 transition-colors">XEM THÊM <svg width="12" height="12" class="inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg></button>
        </div>
        <?php if (!empty($popular_artists)) : ?>
        <div class="flex gap-5 overflow-x-auto pb-2" style="scrollbar-width:none;">
            <?php foreach ($popular_artists as $artist) : ?>
            <a href="<?php echo esc_url($artist['url'] ?? '#'); ?>" class="group flex flex-col items-center text-center flex-shrink-0 w-28 cursor-pointer no-underline">
                <!-- Avatar -->
                <div class="w-full pb-[100%] rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 mb-3 relative overflow-hidden shadow-sm group-hover:shadow-md transition-shadow duration-200 group-hover:scale-[1.03]" title="<?php echo esc_attr($artist['name']); ?>">
                    <span class="absolute inset-0 flex items-center justify-center text-2xl font-bold text-indigo-400/70"><?php echo esc_html($artist['initials']); ?></span>
                </div>
                <!-- Name -->
                <p class="text-[13px] font-semibold text-gray-800 leading-snug mb-0.5 truncate w-full px-1"><?php echo esc_html($artist['name']); ?></p>
                <p class="text-[11px] text-gray-400 truncate w-full"><?php echo number_format_i18n($artist['views']); ?> lượt nghe</p>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else : ?>
            <p class="text-sm text-gray-400">Chưa có dữ liệu ca sĩ.</p>
        <?php endif; ?>
    </div>
</div>
