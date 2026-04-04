<?php
/**
 * The template for displaying archive pages (Artist Tracker List)
 *
 * @package roon
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php wp_title( '|', true, 'right' ); ?></title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'roon-body single-post' ); ?>>
<?php wp_body_open(); ?>

<div id="roon-app" class="flex h-[100dvh] min-h-screen overflow-hidden bg-white font-inter">
	<div id="roon-sidebar-overlay" class="fixed inset-0 bg-black/20 z-40 hidden lg:hidden"></div>
	<?php
	get_template_part( 'template-parts/roon', 'sidebar', array( 'is_premium' => isset($is_premium) ? $is_premium : false ) );
	?>
	<div id="roon-main" class="flex h-[100dvh] min-w-0 flex-1 flex-col overflow-hidden transition-transform duration-300 ease-in-out">
		<?php get_template_part( 'template-parts/roon', 'header-bar' ); ?>
		
		<div id="roon-content" class="flex-1 overflow-y-auto overflow-x-hidden p-4 sm:p-6 pb-roon-player">
			<div class="mx-auto w-full max-w-6xl font-inter">
                <?php
                $term = get_queried_object();
                $artist_name = !empty($term->name) ? $term->name : 'Unknown Artist';
                
                // Thu thập all track của nghệ sĩ
                $tracks = array();
                $all_tracks = function_exists('roon_get_library_tracks') ? roon_get_library_tracks() : [];
                foreach ($all_tracks as $t) {
                    if (strtolower(trim($t['artist'])) === strtolower(trim($artist_name)) || strtolower($term->slug) === sanitize_title($t['artist'])) {
                        $tracks[] = $t;
                    }
                }
                ?>
                <div class="flex items-end justify-between mb-4 flex-wrap gap-3">
                    <div>
                        <h1 class="text-[40px] font-bold tracking-tight text-gray-900 leading-tight m-0" style="text-transform: capitalize;">
                            <?php echo esc_html($artist_name); ?>
                            <span class="text-[16px] font-normal text-gray-400 ml-2 block sm:inline mt-2 sm:mt-0"><?php echo count($tracks); ?> bài hát</span>
                        </h1>
                    </div>
                </div>

                <!-- Column headers -->
                <div class="flex items-center gap-3 pb-2 border-b border-gray-200 mb-1 px-2 mt-6">
                    <div class="w-11 flex-shrink-0"></div>
                    <div class="flex items-center gap-1 flex-[2]"><span class="text-[12px] text-gray-400 font-medium">Tên bài hát</span></div>
                    <div class="hidden md:flex items-center gap-1 flex-[1.5]"><span class="text-[12px] text-gray-400 font-medium">Album</span></div>
                    <div class="flex items-center justify-center flex-shrink-0 w-10">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="text-gray-400">
                            <line x1="4" y1="6" x2="20" y2="6"/><line x1="1" y1="12" x2="23" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/>
                        </svg>
                    </div>
                </div>

                <div id="artist-tracks-list" class="flex flex-col">
                    <?php if (empty($tracks)): ?>
                    <div class="py-16 text-center text-gray-400">
                        <p class="text-sm">Chưa có bài hát nào của ca sĩ này!</p>
                    </div>
                    <?php else: ?>
                        <?php foreach ($tracks as $index => $track) : ?>
                        <div class="artist-track-row roon-track-row flex items-center gap-3 px-2 py-1.5 rounded-lg cursor-pointer group hover:bg-gray-50 transition-colors" data-index="<?php echo $index; ?>">
                            <div class="relative w-11 h-11 rounded-md overflow-hidden flex-shrink-0 bg-gray-200">
                                <img src="<?php echo esc_url($track['cover']); ?>" alt="" class="w-full h-full object-cover"/>
                                <button class="absolute inset-0 bg-black/50 flex items-center justify-center border-none cursor-pointer opacity-0 group-hover:opacity-100 transition-opacity play-this-track"
                                        data-stream-url="<?php echo esc_url($track['stream_url']); ?>"
                                        data-track-title="<?php echo esc_attr($track['title']); ?>"
                                        data-track-artist="<?php echo esc_attr($track['artist']); ?>"
                                        data-track-cover="<?php echo esc_url($track['cover']); ?>"
                                        data-track-album-url="<?php echo esc_url($track['post_url']); ?>">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="white"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                </button>
                            </div>
                            <div class="flex flex-col min-w-0 flex-[2]">
                                <span class="text-[13px] font-medium text-gray-900 truncate"><?php echo esc_html($track['title']); ?></span>
                                <span class="text-[12px] text-gray-500 truncate"><?php echo esc_html($track['artist']); ?></span>
                            </div>
                            <a href="<?php echo esc_url($track['post_url']); ?>" class="hidden md:block flex-[1.5] text-[12.5px] text-roon-blue truncate no-underline hover:underline"><?php echo esc_html($track['album']); ?></a>
                            <div class="flex items-center gap-2.5 flex-shrink-0 w-10 justify-end">
                                <span class="text-[12.5px] text-gray-400 tabular-nums"><?php echo esc_html($track['duration']); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Phân trang JS cho artist tracks -->
                <div id="artist-pagination" class="mt-8 flex justify-center gap-2 items-center hidden">
                    <button id="artist-prev-page" class="flex items-center justify-center w-8 h-8 rounded-md text-gray-500 hover:bg-gray-100 transition-colors bg-transparent border-none cursor-pointer disabled:opacity-50">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                    </button>
                    <div id="artist-page-numbers" class="flex items-center gap-1"></div>
                    <button id="artist-next-page" class="flex items-center justify-center w-8 h-8 rounded-md text-gray-500 hover:bg-gray-100 transition-colors bg-transparent border-none cursor-pointer disabled:opacity-50">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                </div>
                
			</div>
            <div class="h-24"></div>
		</div>
	</div>

	<!-- Audio Player Fixed Bottom -->
	<?php get_template_part( 'template-parts/roon', 'player' ); ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var rows = Array.from(document.querySelectorAll('.artist-track-row'));
    var currentPage = 1;
    var itemsPerPage = 10;
    var totalPages = Math.ceil(rows.length / itemsPerPage);
    
    var paginationWrapper = document.getElementById('artist-pagination');
    var prevBtn = document.getElementById('artist-prev-page');
    var nextBtn = document.getElementById('artist-next-page');
    var pageNumbers = document.getElementById('artist-page-numbers');

    function renderPagination() {
        if (totalPages <= 1) return;
        paginationWrapper.classList.remove('hidden');
        if (prevBtn) prevBtn.disabled = currentPage === 1;
        if (nextBtn) nextBtn.disabled = currentPage === totalPages;

        if (pageNumbers) {
            pageNumbers.innerHTML = '';
            for (let i = 1; i <= totalPages; i++) {
                if (totalPages > 6 && i !== 1 && i !== totalPages && Math.abs(i - currentPage) > 1) {
                    if (Math.abs(i - currentPage) === 2) {
                        const dot = document.createElement('span');
                        dot.className = 'text-gray-400 px-1';
                        dot.textContent = '...';
                        pageNumbers.appendChild(dot);
                    }
                    continue;
                }
                const btn = document.createElement('button');
                btn.className = 'w-8 h-8 rounded-md border-none cursor-pointer text-[13px] font-medium transition-colors ';
                btn.className += i === currentPage ? 'bg-gray-800 text-white' : 'bg-transparent text-gray-600 hover:bg-gray-100 hover:text-gray-900';
                btn.textContent = i;
                if (i === currentPage) { btn.disabled = true; }
                btn.onclick = function() {
                    currentPage = i;
                    renderPage();
                    document.getElementById('roon-content').scrollTop = 0;
                };
                pageNumbers.appendChild(btn);
            }
        }
    }

    function renderPage() {
        var start = (currentPage - 1) * itemsPerPage;
        var end = start + itemsPerPage;
        rows.forEach(function(row, idx) {
            if (idx >= start && idx < end) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
        renderPagination();
    }

    if (prevBtn) prevBtn.addEventListener('click', function() { if (currentPage > 1) { currentPage--; renderPage(); } });
    if (nextBtn) nextBtn.addEventListener('click', function() { if (currentPage < totalPages) { currentPage++; renderPage(); } });

    renderPage();
});
</script>

<?php wp_footer(); ?>
</body>
</html>
