<?php
/**
 * Template Part: Roon Header Bar — Tailwind classes
 * @package roon
 */
?>
<div class="flex items-center justify-between h-roon-header min-h-roon-header px-3 sm:px-5 border-b border-gray-200 bg-white gap-2 sm:gap-3 z-40 flex-shrink-0 font-inter">

    <!-- Left: nav arrows -->
    <div class="flex items-center gap-1">
        <button id="btn-sidebar-toggle" title="Menu"
                class="flex items-center justify-center w-8 h-8 rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors lg:hidden">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>
        <button id="btn-nav-back" title="Back"
                class="flex items-center justify-center w-8 h-8 rounded-md text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition-colors">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
        </button>
        <button id="btn-nav-forward" title="Forward"
                class="flex items-center justify-center w-8 h-8 rounded-md text-gray-300 cursor-not-allowed transition-colors" disabled>
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        </button>
    </div>

    <!-- Center: dynamic page title (filled by JS) -->
    <div id="roon-page-title" class="flex-1 text-[13px] font-medium text-gray-600 text-left sm:text-center truncate hidden sm:block"></div>

    <!-- Right: actions -->
    <div class="flex items-center gap-1 sm:gap-2">
        <!-- Bookmark -->
        <button id="btn-bookmark" title="Bookmark"
                class="hidden sm:flex items-center justify-center w-8 h-8 rounded-md text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition-colors">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
            </svg>
        </button>
        <!-- Search -->
        <button id="btn-global-search" title="Search" data-page="search"
                class="flex items-center justify-center w-8 h-8 rounded-md text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition-colors">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
        </button>
        <!-- User avatar -->
        <button id="btn-user-avatar" title="User profile"
                class="w-[30px] h-[30px] rounded-full bg-red-500 text-white text-[13px] font-semibold flex items-center justify-center cursor-pointer border-none leading-none">
            R
        </button>
    </div>
</div>
