<?php
/**
 * The template for displaying artist archive pages.
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
	get_template_part( 'template-parts/roon', 'sidebar', array( 'is_premium' => isset( $is_premium ) ? $is_premium : false ) );
	?>
	<div id="roon-main" class="flex h-[100dvh] min-w-0 flex-1 flex-col overflow-hidden transition-transform duration-300 ease-in-out">
		<?php get_template_part( 'template-parts/roon', 'header-bar' ); ?>

		<div id="roon-content" class="flex-1 overflow-y-auto overflow-x-hidden p-4 sm:p-6 pb-roon-player">
			<div class="mx-auto w-full max-w-6xl font-inter">
				<?php
				$term          = get_queried_object();
				$artist_name   = ! empty( $term->name ) ? $term->name : 'Unknown Artist';
				$artist_term_id = ! empty( $term->term_id ) ? (int) $term->term_id : 0;
				$artist_image  = '';

				if ( $artist_term_id && function_exists( 'get_field' ) ) {
					$artist_image_field = get_field( 'artist_image', 'category_' . $artist_term_id );

					if ( is_array( $artist_image_field ) ) {
						$artist_image = $artist_image_field['sizes']['medium'] ?? $artist_image_field['url'] ?? '';
					} elseif ( is_string( $artist_image_field ) ) {
						$artist_image = $artist_image_field;
					}
				}

				$album_posts = array();
				if ( $artist_term_id ) {
					$album_posts = get_posts(
						array(
							'post_type'      => 'post',
							'post_status'    => 'publish',
							'posts_per_page' => -1,
							'orderby'        => 'date',
							'order'          => 'DESC',
							'category__in'   => array( $artist_term_id ),
						)
					);
				}

				$albums      = array();
				$total_tracks = 0;

				foreach ( $album_posts as $album_post ) {
					$album_id     = (int) $album_post->ID;
					$album_tracks = function_exists( 'roon_get_post_album_tracks' ) ? roon_get_post_album_tracks( $album_id ) : array();
					$album_cover  = function_exists( 'roon_get_album_cover_url' ) ? roon_get_album_cover_url( $album_id ) : '';
					$album_artist = function_exists( 'roon_get_album_artist_names' ) ? roon_get_album_artist_names( $album_id ) : $artist_name;

					$albums[] = array(
						'id'          => $album_id,
						'title'       => get_the_title( $album_id ),
						'url'         => get_permalink( $album_id ),
						'cover'       => $album_cover,
						'year'        => get_the_date( 'Y', $album_id ),
						'artist'      => $album_artist,
						'track_count' => count( $album_tracks ),
						'tracks'      => $album_tracks,
					);

					$total_tracks += count( $album_tracks );
				}
				?>

				<div class="mb-6 flex flex-col gap-5 sm:mb-7 sm:flex-row sm:items-start sm:gap-7">
					<div class="mx-auto w-40 flex-shrink-0 sm:mx-0 sm:w-44">
						<?php if ( $artist_image ) : ?>
							<img
								class="h-40 w-40 rounded-2xl object-cover shadow-lg sm:h-44 sm:w-44"
								src="<?php echo esc_url( $artist_image ); ?>"
								alt="<?php echo esc_attr( $artist_name ); ?>"
							>
						<?php else : ?>
							<div class="flex h-40 w-40 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-50 to-purple-100 text-5xl font-bold text-indigo-400/70 shadow-lg sm:h-44 sm:w-44">
								<?php
								$words    = preg_split( '/\s+/', trim( $artist_name ) );
								$initials = '';
								foreach ( array_slice( $words, 0, 2 ) as $word ) {
									$initials .= function_exists( 'mb_substr' ) ? mb_substr( $word, 0, 1 ) : substr( $word, 0, 1 );
								}
								echo esc_html( strtoupper( $initials ) );
								?>
							</div>
						<?php endif; ?>
					</div>

					<div class="flex min-w-0 flex-1 flex-col gap-2 text-center sm:text-left">
						<h1 class="m-0 text-[40px] font-bold tracking-tight text-gray-900 leading-tight" style="text-transform: capitalize;">
							<?php echo esc_html( $artist_name ); ?>
						</h1>
						<p class="m-0 text-[14px] text-gray-500">
							<?php echo esc_html( count( $albums ) ); ?> album
							<?php if ( 1 !== count( $albums ) ) : ?>s<?php endif; ?>
							<span class="mx-2 text-gray-300">&bull;</span>
							<?php echo esc_html( $total_tracks ); ?> bài hát
						</p>
						<p class="m-0 max-w-2xl text-[13px] text-gray-400">
							Chọn một album bên dưới để mở danh sách bài hát của ca sĩ này.
						</p>
					</div>
				</div>

				<div class="mb-4 flex items-center justify-between gap-3 border-b border-gray-200 pb-3">
					<h2 class="m-0 text-[18px] font-semibold text-gray-900">Danh sách album</h2>
					<span class="text-[12px] text-gray-400"><?php echo esc_html( count( $albums ) ); ?> kết quả</span>
				</div>

				<div id="artist-albums-list" class="flex flex-col gap-4">
					<?php if ( empty( $albums ) ) : ?>
						<div class="rounded-2xl border border-dashed border-gray-200 py-16 text-center text-gray-400">
							<p class="text-sm">Chưa có album nào của ca sĩ này.</p>
						</div>
					<?php else : ?>
						<?php foreach ( $albums as $index => $album ) : ?>
							<div class="artist-album-card overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
								<button
									type="button"
									class="artist-album-toggle flex w-full items-center gap-4 border-none bg-transparent px-4 py-4 text-left cursor-pointer transition-colors hover:bg-gray-50"
									data-target="artist-album-panel-<?php echo esc_attr( $index ); ?>"
									aria-expanded="false"
								>
									<div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-xl bg-gray-100">
										<?php if ( ! empty( $album['cover'] ) ) : ?>
											<img src="<?php echo esc_url( $album['cover'] ); ?>" alt="<?php echo esc_attr( $album['title'] ); ?>" class="h-full w-full object-cover">
										<?php else : ?>
											<div class="flex h-full w-full items-center justify-center text-gray-300">
												<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
											</div>
										<?php endif; ?>
									</div>

									<div class="min-w-0 flex-1">
										<div class="truncate text-[16px] font-semibold text-gray-900"><?php echo esc_html( $album['title'] ); ?></div>
										<div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-[12.5px] text-gray-500">
											<span><?php echo esc_html( $album['track_count'] ); ?> bài hát</span>
											<?php if ( ! empty( $album['year'] ) ) : ?>
												<span><?php echo esc_html( $album['year'] ); ?></span>
											<?php endif; ?>
											<?php if ( ! empty( $album['artist'] ) ) : ?>
												<span class="truncate"><?php echo esc_html( $album['artist'] ); ?></span>
											<?php endif; ?>
										</div>
									</div>

									<div class="flex items-center gap-3 pl-2">
										<a
											href="<?php echo esc_url( $album['url'] ); ?>"
											class="artist-album-link hidden rounded-full bg-gray-100 px-3 py-1.5 text-[12px] font-medium text-gray-700 no-underline hover:bg-gray-200 sm:inline-block"
										>
											Mở album
										</a>
										<span class="artist-album-chevron text-gray-400 transition-transform duration-200">
											<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
										</span>
									</div>
								</button>

								<div id="artist-album-panel-<?php echo esc_attr( $index ); ?>" class="artist-album-panel hidden border-t border-gray-100 bg-gray-50/60 px-4 py-3">
									<?php if ( empty( $album['tracks'] ) ) : ?>
										<p class="m-0 py-4 text-sm text-gray-500">Album này chưa có danh sách bài hát.</p>
									<?php else : ?>
										<div class="mb-2 flex items-center gap-3 px-2 pb-2 text-[12px] text-gray-400">
											<div class="w-8 flex-shrink-0 text-right">#</div>
											<div class="flex-[2]">Tên bài hát</div>
											<div class="hidden md:block flex-[1.3]">Thời lượng</div>
										</div>
										<div class="flex flex-col divide-y divide-gray-100 rounded-xl bg-white">
											<?php foreach ( $album['tracks'] as $track_index => $track ) : ?>
												<?php
												$track_title    = ! empty( $track['track_title'] ) ? $track['track_title'] : 'Unknown Track';
												$track_duration = ! empty( $track['track_duration'] ) ? $track['track_duration'] : '--:--';
												$track_stream   = ! empty( $track['stream_url'] ) ? $track['stream_url'] : '#';
												?>
												<div class="group flex items-center gap-3 px-2 py-2.5">
													<div class="w-8 flex-shrink-0 text-right text-[12px] font-medium text-gray-400">
														<?php echo esc_html( $track_index + 1 ); ?>
													</div>
													<button
														type="button"
														class="play-this-track ml-1 flex-shrink-0 cursor-pointer border-none bg-transparent p-0 transition-transform hover:scale-110"
														data-stream-url="<?php echo esc_url( $track_stream ); ?>"
														data-track-title="<?php echo esc_attr( $track_title ); ?>"
														data-track-artist="<?php echo esc_attr( $album['artist'] ); ?>"
														data-track-cover="<?php echo esc_url( $album['cover'] ); ?>"
														data-track-album-url="<?php echo esc_url( $album['url'] ); ?>"
													>
														<svg width="18" height="18" viewBox="0 0 24 24" fill="#3b3ef6">
															<circle cx="12" cy="12" r="10"/>
															<polygon points="10 8 16 12 10 16 10 8" fill="white"/>
														</svg>
													</button>
													<div class="min-w-0 flex-[2]">
														<div class="truncate text-[13.5px] font-medium text-gray-800"><?php echo esc_html( $track_title ); ?></div>
													</div>
													<div class="hidden md:block min-w-0 flex-[1.3] text-[12.5px] text-gray-400 tabular-nums">
														<?php echo esc_html( $track_duration ); ?>
													</div>
												</div>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<?php get_template_part( 'template-parts/roon', 'player' ); ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	var albumToggles = Array.from(document.querySelectorAll('.artist-album-toggle'));

	albumToggles.forEach(function(toggle) {
		toggle.addEventListener('click', function(event) {
			if (event.target.closest('.artist-album-link')) {
				return;
			}

			var panelId = toggle.getAttribute('data-target');
			var panel = panelId ? document.getElementById(panelId) : null;
			var isExpanded = toggle.getAttribute('aria-expanded') === 'true';

			albumToggles.forEach(function(otherToggle) {
				var otherPanelId = otherToggle.getAttribute('data-target');
				var otherPanel = otherPanelId ? document.getElementById(otherPanelId) : null;

				otherToggle.setAttribute('aria-expanded', 'false');
				otherToggle.classList.remove('bg-gray-50');

				if (otherPanel) {
					otherPanel.classList.add('hidden');
				}
			});

			if (!panel || isExpanded) {
				return;
			}

			toggle.setAttribute('aria-expanded', 'true');
			toggle.classList.add('bg-gray-50');
			panel.classList.remove('hidden');
		});
	});
});
</script>

<?php wp_footer(); ?>
</body>
</html>
