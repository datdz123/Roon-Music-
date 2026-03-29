<?php
/**
 * Template Part: Roon Layout Wrapper — Tailwind classes
 * @package roon
 */
?>
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
            <?php get_template_part('template-parts/roon-home'); ?>
            <?php get_template_part('template-parts/roon-albums'); ?>
            <?php get_template_part('template-parts/roon-artists'); ?>
            <?php get_template_part('template-parts/roon-tracks'); ?>
            <?php get_template_part('template-parts/roon-single-album'); ?>
            <?php get_template_part('template-parts/roon-search'); ?>
        </div>
    </div>

    <!-- Audio Player Fixed Bottom -->
    <?php get_template_part('template-parts/roon-player'); ?>
</div>
