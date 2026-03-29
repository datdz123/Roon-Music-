<?php
/**
 * Template Part: Roon Filter Bar Component
 * @package roon
 */
$type = $args['type'] ?? 'albums'; // albums, artists, tracks
?>
<div class="flex items-center justify-between mb-5 flex-wrap gap-3 roon-filter-bar">
    
    <!-- Nhóm bên trái -->
    <div class="flex items-center gap-2.5">
        <button class="flex items-center justify-center w-[34px] h-[34px] rounded-full border border-gray-200 text-gray-500 bg-white shadow-sm cursor-pointer hover:bg-roon-blue hover:border-roon-blue hover:text-white transition-all duration-300">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
        </button>
        <button class="flex items-center gap-2 text-[13px] text-gray-700 bg-white border border-gray-200 shadow-sm cursor-pointer px-3.5 py-1.5 rounded-full hover:bg-roon-blue hover:text-white hover:border-roon-blue transition-all duration-300 font-medium">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
            Focus
        </button>
    </div>

    <!-- Nhóm bên phải -->
    <div class="flex items-center gap-2.5">
        
        <!-- Dropdown sắp xếp (Dùng CSS group hover của Tailwind) -->
        <div class="relative group">
            <button class="flex items-center gap-2 text-[13px] text-gray-700 bg-white border border-gray-200 shadow-sm cursor-pointer px-3.5 py-1.5 rounded-full hover:bg-gray-50 transition-all duration-300 font-medium">
                Sắp xếp: Mới nhất
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <!-- Dropdown content -->
            <div class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 shadow-lg rounded-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 overflow-hidden transform origin-top-right scale-95 group-hover:scale-100">
                <ul class="py-1 m-0 list-none">
                    <li><a href="#" class="block px-4 py-2.5 text-[13px] text-gray-700 no-underline hover:bg-roon-blue hover:text-white transition-colors sort-action" data-sort="newest">Ngày thêm: Mới nhất</a></li>
                    <li><a href="#" class="block px-4 py-2.5 text-[13px] text-gray-700 no-underline hover:bg-roon-blue hover:text-white transition-colors sort-action" data-sort="oldest">Ngày thêm: Cũ nhất</a></li>
                    <li><a href="#" class="block px-4 py-2.5 text-[13px] text-gray-700 no-underline hover:bg-roon-blue hover:text-white transition-colors sort-action" data-sort="alpha">Tên: A - Z</a></li>
                    <li><a href="#" class="block px-4 py-2.5 text-[13px] text-gray-700 no-underline hover:bg-roon-blue hover:text-white transition-colors sort-action" data-sort="release">Năm phát hành</a></li>
                </ul>
            </div>
        </div>

        <!-- Nút Filter -->
        <button class="flex items-center gap-2 text-[13px] text-gray-700 bg-white border border-gray-200 shadow-sm cursor-pointer px-3.5 py-1.5 rounded-full hover:bg-roon-blue hover:text-white hover:border-roon-blue transition-all duration-300 font-medium">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
            </svg>
            Bộ lọc
        </button>

    </div>
</div>
