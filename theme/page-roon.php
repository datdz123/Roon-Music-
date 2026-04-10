<?php
/**
 * Template Name: Roon Music Player
 * Trang chủ Roon Music - SPA layout
 *
 * @package roon
 */

// Không output header/footer WP mặc định, dùng layout riêng
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Losslessvn.top -  Chia sẻ nhạc chất lượng cao, Direct play 100%
Web nghe nhạc lossless chuẩn audiophile với chất lượng âm thanh cao cấp, hỗ trợ direct play không nén, không transcode. Trải nghiệm âm nhạc trung thực, chi tiết, tối ưu cho người yêu nhạc khó tính.">
    <title><?php bloginfo('name'); ?> - Thư viện nhạc</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php wp_head(); ?>
</head>
<body <?php body_class('roon-body'); ?>>
<?php wp_body_open(); ?>

<?php get_template_part('template-parts/roon-layout'); ?>

<?php wp_footer(); ?>
</body>
</html>
