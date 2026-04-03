<?php
/**
 * Template Part: Roon Fav Artists — Ca Sĩ Yêu Thích
 * Hiển thị danh sách ca sĩ / artist sắp xếp theo lượt xem (nhiều → ít)
 * @package roon
 */

$artists = roon_get_library_artists();
// Sắp xếp theo lượt xem giảm dần (dựa trên số bài viết trong category)
$artist_terms = get_categories(array('hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC'));
$sorted_artists = [];
foreach ($artist_terms as $term) {
    $name = trim($term->name);
    if ('' === $name) continue;
    $words    = preg_split('/\s+/', $name);
    $initials = '';
    foreach (array_slice($words, 0, 2) as $word) {
        $initials .= function_exists('mb_substr') ? mb_substr($word, 0, 1) : substr($word, 0, 1);
    }
    $sorted_artists[] = array(
        'name'     => $name,
        'initials' => strtoupper($initials),
        'count'    => $term->count,
    );
}
?>

<div id="page-fav-artists" class="roon-page hidden">
    <!-- Filter bar -->
    <?php get_template_part('template-parts/roon-filter-bar'); ?>

    <?php if (!empty($sorted_artists)) : ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6 mt-6">
        <?php foreach ($sorted_artists as $idx => $artist) : ?>
        <div class="group flex flex-col items-center text-center">
            <!-- Avatar -->
            <div class="w-28 h-28 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center mb-3 shadow-sm group-hover:shadow-md transition-shadow duration-200 group-hover:scale-[1.03] cursor-pointer" title="<?php echo esc_attr($artist['name']); ?>">
                <span class="text-2xl font-bold text-indigo-400/70"><?php echo esc_html($artist['initials']); ?></span>
            </div>
            <!-- Name -->
            <p class="text-[14px] font-semibold text-gray-800 leading-snug mb-0.5 truncate max-w-[120px]"><?php echo esc_html($artist['name']); ?></p>
            <p class="text-[12px] text-gray-400"><?php echo esc_html($artist['count']); ?> album</p>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else : ?>
    <div class="flex flex-col items-center justify-center py-20 text-center">
        <svg class="w-16 h-16 text-gray-200 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/>
            <polygon points="21 8 22.5 11 26 11.5 23.5 13.9 24.1 17.4 21 15.8 17.9 17.4 18.5 13.9 16 11.5 19.5 11"/>
        </svg>
        <p class="text-gray-400 text-[15px]">Chưa có ca sĩ yêu thích nào</p>
    </div>
    <?php endif; ?>
</div>
