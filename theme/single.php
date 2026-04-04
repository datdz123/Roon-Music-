<?php
/**
 * The template for displaying all single posts (Roon Style Album page).
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
<body <?php body_class( 'roon-body' ); ?>>
<?php wp_body_open(); ?>

<div id="roon-app" class="flex h-[100dvh] min-h-screen overflow-hidden bg-white font-inter">
	<div id="roon-sidebar-overlay" class="fixed inset-0 bg-black/20 z-40 hidden lg:hidden"></div>
	<?php
	get_template_part(
		'template-parts/roon',
		'sidebar',
		array(
			'is_premium' => isset($is_premium) ? $is_premium : false,
		)
	);
	?>
	<div id="roon-main" class="flex h-[100dvh] min-w-0 flex-1 flex-col overflow-hidden transition-transform duration-300 ease-in-out">
		<?php
		get_template_part(
			'template-parts/roon',
			'header-bar'
		);
		?>
		<div id="roon-content" class="flex-1 overflow-y-auto overflow-x-hidden p-4 sm:p-6 pb-roon-player">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<div id="page-single-album-wp" class="mx-auto w-full max-w-5xl font-inter">
					<div class="mb-6 flex flex-col gap-5 sm:mb-7 sm:flex-row sm:flex-wrap sm:items-start sm:gap-7">
						<div class="mx-auto w-48 flex-shrink-0 sm:mx-0 sm:w-52">
							<?php if ( has_post_thumbnail() ) : ?>
								<img
									class="h-48 w-48 rounded-lg object-cover shadow-lg sm:h-52 sm:w-52"
									src="<?php the_post_thumbnail_url( 'full' ); ?>"
									alt="<?php the_title_attribute(); ?>"
								>
							<?php else : ?>
								<div class="flex h-48 w-48 items-center justify-center rounded-lg bg-gray-200 text-gray-400 sm:h-52 sm:w-52">
									<svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
								</div>
							<?php endif; ?>
						</div>
						<div class="flex min-w-0 flex-1 flex-col gap-1.5 text-center sm:text-left">
							<p class="text-[11px] font-semibold uppercase tracking-widest text-gray-500">
								<?php
								$post_type = get_post_type_object( get_post_type() );
								echo esc_html( $post_type->labels->singular_name );
								?>
							</p>
							<h1 class="m-0 text-2xl font-bold text-gray-900 sm:text-4xl"><?php the_title(); ?></h1>
							<p class="text-md mt-1 text-gray-600 sm:text-lg">
								<?php
								$artist_terms = get_the_category();
								if ( $artist_terms && ! is_wp_error( $artist_terms ) ) {
									$artist_links = array();
									foreach ( $artist_terms as $term ) {
										$artist_links[] = '<a href="' . esc_url( get_category_link( $term->term_id ) ) . '" class="hover:text-roon-blue">' . esc_html( $term->name ) . '</a>';
									}
									echo implode( ', ', $artist_links );
								} else {
									echo 'Unknown Artist';
								}
								?>
							</p>
							<p class="text-xs text-gray-400">
								<?php
								$genre_terms = get_the_terms( get_the_ID(), 'genre' );
								$year          = get_field( 'release_year' );
								$track_count   = get_field( 'tracklist' ) ? count( get_field( 'tracklist' ) ) : 0;

								$meta = array();
								if ( $year ) {
									$meta[] = $year;
								}
								if ( $genre_terms && ! is_wp_error( $genre_terms ) ) {
									$meta[] = esc_html( $genre_terms[0]->name );
								}
								if ( $track_count > 0 ) {
									$meta[] = $track_count . ' songs';
								}
								echo implode( ' &bull; ', $meta );
								?>
							</p>

							<div class="mt-3 flex flex-wrap items-center justify-center gap-2 sm:justify-start">
								<button
									id="play-all-tracks"
									class="flex min-h-[42px] items-center gap-2 rounded-full border-none bg-roon-blue px-5 py-2 text-[13px] font-semibold text-white transition-colors hover:bg-roon-indigo sm:min-h-0"
								>
									<svg width="13" height="13" viewBox="0 0 24 24" fill="white"><polygon points="5 3 19 12 5 21 5 3"/></svg>
									Phát tất cả
								</button>
								<?php
								$enable_download = function_exists('get_field') ? get_field('enable_album_download', 'option') : false;
								$album_dl_url    = function_exists('get_field') ? get_field('album_download_url_manual', get_the_ID()) : '';
								
								if ( ! $enable_download ) :
								?>
									<button class="flex min-h-[42px] items-center gap-2 rounded-full border border-gray-800 bg-gray-900 px-5 py-2 text-[13px] font-semibold text-gray-300 transition-colors sm:min-h-0 cursor-not-allowed" title="Tính năng tải về bị hạn chế do Phần cứng máy chủ">
										<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
										Tải về
									</button>
								<?php else : ?>
									<?php if ( $album_dl_url ) : ?>
									<a href="<?php echo esc_url( $album_dl_url ); ?>" target="_blank" class="flex min-h-[42px] items-center gap-2 rounded-full border-none bg-gray-100 px-5 py-2 text-[13px] font-semibold text-gray-700 hover:bg-gray-200 transition-colors sm:min-h-0 no-underline cursor-pointer">
										<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
										Tải về
									</a>
									<?php endif; ?>
								<?php endif; ?>
							</div>
						</div>
					</div>

					<div class="mt-8">
						<div class="mb-4 flex items-center border-b border-gray-200">
							<button data-album-tab="tracks" class="album-tab -mb-px border-b-2 border-roon-blue px-4 py-2 text-sm font-medium text-roon-blue">Tracks</button>
						</div>

						<div id="album-tab-tracks">
							<?php
							$tracks      = function_exists( 'roon_get_post_album_tracks' ) ? roon_get_post_album_tracks( get_the_ID() ) : array();
							$artist_name = function_exists( 'roon_get_album_artist_name' ) ? roon_get_album_artist_name( get_the_ID() ) : 'Unknown Artist';
							$album_cover = function_exists( 'roon_get_album_cover_url' ) ? roon_get_album_cover_url( get_the_ID() ) : get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' );

							if ( 'Unknown Artist' === $artist_name && isset( $artist_terms ) && ! is_wp_error( $artist_terms ) && ! empty( $artist_terms ) ) {
								$artist_name = $artist_terms[0]->name;
							}
							?>
							<div class="flex flex-col divide-y divide-gray-100">
								<?php
								if ( $tracks ) :
									$index = 1;
									foreach ( $tracks as $track ) :
										$t_title    = ! empty( $track['track_title'] ) ? $track['track_title'] : 'Unknown Track';
										$t_duration = ! empty( $track['track_duration'] ) ? $track['track_duration'] : '--:--';
										$t_url      = ! empty( $track['stream_url'] ) ? $track['stream_url'] : '#';
										$t_dl       = ! empty( $track['download_url'] ) ? $track['download_url'] : '#';
										?>
										<div class="group flex cursor-pointer items-center gap-3 rounded-lg px-2 py-2.5 transition-colors hover:bg-gray-50">
											<!-- Index -->
											<span class="w-5 text-right text-[13px] font-medium text-gray-400"><?php echo $index; ?></span>

											<!-- Play circle button -->
											<button class="ml-1 flex-shrink-0 cursor-pointer border-none bg-transparent p-0 transition-transform hover:scale-110"
													data-stream-url="<?php echo esc_url( $t_url ); ?>"
													data-track-title="<?php echo esc_attr( $t_title ); ?>"
													data-track-artist="<?php echo esc_attr( $artist_name ); ?>"
													data-track-cover="<?php echo esc_url( $album_cover ); ?>">
												<svg width="20" height="20" viewBox="0 0 24 24" fill="#3b3ef6">
													<circle cx="12" cy="12" r="10"/>
													<polygon points="10 8 16 12 10 16 10 8" fill="white"/>
												</svg>
											</button>

											<!-- Track title -->
											<div class="min-w-0 flex-[2] truncate text-[13.5px] font-medium text-gray-800"><?php echo esc_html( $t_title ); ?></div>

											<!-- Artist Name -->
											<div class="hidden md:block min-w-0 flex-[1.5] truncate text-[12.5px] text-gray-500"><?php echo esc_html( $artist_name ); ?></div>

											<!-- Right actions -->
											<div class="flex flex-shrink-0 items-center gap-4">
												<!-- Duration -->
												<span class="min-w-[40px] tabular-nums text-right text-[13px] text-gray-400"><?php echo esc_html( $t_duration ); ?></span>
											</div>
										</div>
										<?php
										$index++;
									endforeach;
								else :
									?>
									<p class="py-4 text-sm text-gray-500">No tracks found for this album.</p>
								<?php endif; ?>
							</div>
						</div>

						<div id="album-tab-credits" class="hidden">
							<p>Credits tab content goes here.</p>
						</div>
					</div>
				</div>

				<?php
			endwhile;
			?>

			<!-- Album gợi ý / yêu thích -->
			<div class="mx-auto w-full max-w-5xl px-0 mt-8 sm:mt-12 mb-4 border-t border-gray-100 pt-8 sm:pt-10">
				<?php get_template_part('template-parts/roon', 'popular-albums', array('title' => 'Album yêu thích')); ?>
			</div>

			<div class="h-24"></div>
		</div>
	</div>

	<!-- Audio Player Fixed Bottom -->
	<?php get_template_part( 'template-parts/roon', 'player' ); ?>
</div>

<?php wp_footer(); ?>
</body>
</html>
