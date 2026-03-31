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
			'is_premium' => $is_premium,
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
			get_template_part( 'template-parts/roon', 'home' );
			get_template_part( 'template-parts/roon', 'albums' );
			get_template_part( 'template-parts/roon', 'artists' );
			get_template_part( 'template-parts/roon', 'tracks' );
			get_template_part( 'template-parts/roon', 'search' );
			get_template_part( 'template-parts/roon', 'genres' );
			get_template_part( 'template-parts/roon', 'playlists' );
			get_template_part( 'template-parts/roon', 'settings' );
			?>
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
								$artist_terms = get_the_terms( get_the_ID(), 'artist' );
								if ( $artist_terms && ! is_wp_error( $artist_terms ) ) {
									$artist_links = array();
									foreach ( $artist_terms as $term ) {
										$artist_links[] = '<a href="' . esc_url( get_term_link( $term ) ) . '" class="hover:text-roon-blue">' . esc_html( $term->name ) . '</a>';
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
								<button class="flex h-10 w-10 items-center justify-center rounded-full border-none bg-roon-blue text-white transition-colors hover:bg-roon-indigo sm:h-9 sm:w-9">
									<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
								</button>
								<?php
								$icon_btns = array(
									array(
										'title' => 'Luu thu vien',
										'icon'  => 'heart',
									),
									array(
										'title' => 'Tim kiem',
										'icon'  => 'search',
									),
									array(
										'title' => 'Them',
										'icon'  => 'more',
									),
								);
								foreach ( $icon_btns as $btn ) :
									?>
									<button
										<?php if ( 'search' === $btn['icon'] ) {
											echo 'id="btn-show-search"';
										} ?>
										title="<?php echo esc_attr( $btn['title'] ); ?>"
										class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-transparent text-gray-500 transition-colors hover:border-gray-400 sm:h-[34px] sm:w-[34px]"
									>
										<?php if ( 'heart' === $btn['icon'] ) : ?>
											<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
												<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
											</svg>
										<?php elseif ( 'search' === $btn['icon'] ) : ?>
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
						</div>
					</div>

					<div class="mt-8">
						<div class="mb-4 flex items-center border-b border-gray-200">
							<button data-album-tab="tracks" class="album-tab -mb-px border-b-2 border-roon-blue px-4 py-2 text-sm font-medium text-roon-blue">Tracks</button>
							<button data-album-tab="credits" class="album-tab -mb-px border-b-2 border-transparent px-4 py-2 text-sm font-medium text-gray-400 hover:text-gray-700">Credits</button>
						</div>

						<div id="album-tab-tracks">
							<?php
							$tracklist = get_field( 'tracklist' );
							if ( $tracklist ) :
								?>
								<div class="space-y-1">
									<?php
									foreach ( $tracklist as $index => $track ) :
										$track_title  = ! empty( $track['track_title'] ) ? $track['track_title'] : 'Unknown Track';
										$stream_url   = ! empty( $track['stream_url'] ) ? $track['stream_url'] : '#';
										$duration     = ! empty( $track['duration'] ) ? $track['duration'] : '0:00';
										$track_artist = '';
										if ( ! empty( $track['artist'] ) ) {
											$artist_term = get_term( $track['artist'] );
											if ( $artist_term && ! is_wp_error( $artist_term ) ) {
												$track_artist = $artist_term->name;
											}
										}
										if ( empty( $track_artist ) && $artist_terms && ! is_wp_error( $artist_terms ) ) {
											$track_artist = $artist_terms[0]->name;
										}
										?>
										<div
											class="flex cursor-pointer items-center gap-4 rounded-lg p-2 text-sm text-gray-700 hover:bg-gray-100"
											data-stream-url="<?php echo esc_url( $stream_url ); ?>"
											data-track-title="<?php echo esc_attr( $track_title ); ?>"
											data-track-artist="<?php echo esc_attr( $track_artist ); ?>"
											data-track-cover="<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ) ); ?>"
										>
											<div class="w-5 text-center text-xs text-gray-400"><?php echo $index + 1; ?></div>
											<div class="flex-1 font-medium text-gray-800"><?php echo esc_html( $track_title ); ?></div>
											<?php if ( $track_artist ) : ?>
												<div class="hidden text-gray-500 sm:block"><?php echo esc_html( $track_artist ); ?></div>
											<?php endif; ?>
											<div class="w-10 text-right text-xs text-gray-400"><?php echo esc_html( $duration ); ?></div>
											<div class="flex items-center">
												<button class="h-7 w-7 rounded-full text-gray-400 hover:bg-gray-200 hover:text-gray-700">
													<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="5" cy="12" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="19" cy="12" r="1.8"/></svg>
												</button>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							<?php else : ?>
								<p>No tracks found for this album.</p>
							<?php endif; ?>
						</div>

						<div id="album-tab-credits" class="hidden">
							<p>Credits tab content goes here.</p>
						</div>
					</div>
				</div>

				<?php
			endwhile;
			?>
			<div class="h-24"></div>
		</div>
	</div>
</div>
