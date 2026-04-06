<?php
/**
 * Template Part: Roon Search — Tailwind classes
 * @package roon
 */
?>

<div id="page-search" class="roon-page hidden font-inter w-full mt-4">

    <!-- Header & Input Area -->
    <div class="w-full max-w-4xl mx-auto px-4 flex flex-col items-center">
        <!-- Heading -->
        <h1 class="text-[20px] font-bold text-gray-900 mb-6 w-full text-center">Tìm kiếm</h1>

        <!-- Search Input -->
        <div class="relative w-full md:w-3/4 mb-5">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </div>
            <input type="text" id="roon-search-input" 
                   class="w-full bg-white border border-gray-300 text-gray-900 text-[14px] rounded-full focus:outline-none focus:border-roon-blue focus:ring-1 focus:ring-roon-blue block pl-11 pr-10 py-3 transition-colors shadow-sm placeholder-gray-400" 
                   placeholder="Tìm kiếm bài hát, album, nghệ sĩ..." autocomplete="off">
            <!-- Clear Button -->
            <button id="roon-search-clear" class="absolute hidden inset-y-0 right-2 pr-2 pl-2 flex items-center text-gray-400 hover:text-gray-700 bg-transparent border-none cursor-pointer">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- Filters (Chips) -->
        <div class="flex items-center justify-start md:justify-center gap-2 mb-12 w-full overflow-x-auto pb-2 scrollbar-none" style="scrollbar-width: none;">
            <button class="flex-shrink-0 bg-green-500 text-white text-[13px] font-medium px-4 py-1.5 rounded-full border-none cursor-pointer">Tất cả</button>
            <button class="flex-shrink-0 bg-gray-100/80 text-gray-700 hover:bg-gray-200 text-[13px] font-medium px-4 py-1.5 rounded-full border border-gray-200 cursor-pointer transition-colors">Bài hát</button>
            <button class="flex-shrink-0 bg-gray-100/80 text-gray-700 hover:bg-gray-200 text-[13px] font-medium px-4 py-1.5 rounded-full border border-gray-200 cursor-pointer transition-colors">Nhạc Lossless</button>
            <button class="flex-shrink-0 bg-gray-100/80 text-gray-700 hover:bg-gray-200 text-[13px] font-medium px-4 py-1.5 rounded-full border border-gray-200 cursor-pointer transition-colors">Nghệ sĩ</button>
        </div>
    </div>

    <!-- ===== STATES ===== -->

    <!-- State: Empty (No query) -->
    <div id="search-state-empty" class="flex flex-col items-center justify-center text-center w-full max-w-3xl mx-auto py-10 mt-4">
        <div class="flex items-center justify-center w-16 h-16 rounded-full border-[1.5px] border-gray-200 mb-5 bg-gray-50/50">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="text-green-500">
                <path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>
            </svg>
        </div>
        <h2 class="text-[16px] font-semibold text-gray-900 m-0 mb-1.5">Bắt đầu tìm kiếm âm nhạc yêu thích</h2>
        <p class="text-[13px] text-gray-500 m-0">Tìm album và nghệ sĩ bằng cách nhập từ khóa ở trên</p>
    </div>

    <!-- State: Results -->
    <div id="search-state-results" class="hidden w-full mx-auto pb-10">
        <!-- Tracks Section -->
        <div class="mb-12">
            <div class="flex justify-between items-end mb-4 border-b border-gray-100 pb-2">
                <h2 class="text-[18px] font-bold text-gray-900 m-0">Bài hát</h2>
                <a href="#" class="text-[12px] text-gray-500 hover:text-gray-800 font-medium no-underline mb-0.5">Xem tất cả &rarr;</a>
            </div>

            <div id="search-results-tracks" class="grid grid-cols-1 lg:grid-cols-2 gap-x-8 gap-y-1.5">
                <!-- Dynamic results will be rendered here -->
            </div>
        </div>

        <!-- Lossless Section -->
        <div>
            <div class="flex justify-between items-end mb-4 border-b border-gray-100 pb-2">
                <h2 class="text-[18px] font-bold text-gray-900 m-0">Nhạc Lossless</h2>
                <a href="#" class="text-[12px] text-gray-500 hover:text-gray-800 font-medium no-underline mb-0.5">Xem tất cả &rarr;</a>
            </div>

            <div id="search-results-albums" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                <!-- Dynamic results will be rendered here -->
            </div>
        </div>
    </div>
</div>

<!-- JavaScript: Dynamic Search -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('roon-search-input');
    const clearBtn = document.getElementById('roon-search-clear');
    const emptyState = document.getElementById('search-state-empty');
    const resultsState = document.getElementById('search-state-results');
    const tracksContainer = document.getElementById('search-results-tracks');
    const albumsContainer = document.getElementById('search-results-albums');

    if (!searchInput) return;

    let searchTimeout;

    function performSearch(query) {
        if (query.length < 2) {
            emptyState.classList.remove('hidden');
            resultsState.classList.add('hidden');
            clearBtn.classList.add('hidden');
            return;
        }

        clearBtn.classList.remove('hidden');

        fetch('/wp-json/roon/v1/search?s=' + encodeURIComponent(query) + '&limit=10')
            .then(response => response.json())
            .then(data => {
                // Render tracks/albums
                tracksContainer.innerHTML = '';
                if (data.albums && data.albums.length > 0) {
                    data.albums.forEach(album => {
                        const trackEl = document.createElement('div');
                        trackEl.className = 'flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer group border border-transparent hover:border-gray-100';
                        trackEl.innerHTML = `
                            <div class="relative w-[46px] h-[46px] flex-shrink-0 bg-gray-200 rounded overflow-hidden">
                                <img src="${escapeHtml(album.cover)}" class="w-full h-full object-cover" onerror="this.src='https://placehold.co/46x46/6a4a3a/f0d0b0?text=+'" />
                                <button class="absolute inset-0 bg-black/40 flex items-center justify-center border-none opacity-0 group-hover:opacity-100 transition-opacity pl-0.5" data-stream-url="${escapeHtml(album.url)}" data-track-title="${escapeHtml(album.title)}" data-track-artist="${escapeHtml(album.artist)}" data-track-cover="${escapeHtml(album.cover)}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="white"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                </button>
                            </div>
                            <div class="flex flex-col flex-1 min-w-0 justify-center gap-0.5">
                                <div class="text-[13px] font-semibold text-gray-900 truncate leading-none">${escapeHtml(album.title)}</div>
                                <div class="text-[11.5px] text-gray-500 leading-tight">${escapeHtml(album.artist)}</div>
                            </div>
                            <div class="text-[12px] text-gray-400 tabular-nums pr-2 flex-shrink-0">${album.views} views</div>
                        `;
                        tracksContainer.appendChild(trackEl);
                    });
                } else {
                    tracksContainer.innerHTML = '<p class="text-sm text-gray-400 col-span-full">Không tìm thấy bài hát</p>';
                }

                // Render albums
                albumsContainer.innerHTML = '';
                if (data.artists && data.artists.length > 0) {
                    data.artists.forEach(artist => {
                        const albumEl = document.createElement('div');
                        albumEl.className = 'roon-album-card cursor-pointer group';
                        albumEl.innerHTML = `
                            <div class="relative w-full pb-[100%] rounded-md overflow-hidden bg-gradient-to-br from-indigo-100 to-purple-100 mb-2 shadow-sm border border-gray-100">
                                <div class="absolute inset-0 flex items-center justify-center text-3xl font-bold text-indigo-400/70">${escapeHtml(artist.initials)}</div>
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center">
                                    <button class="w-10 h-10 rounded-full bg-roon-blue text-white flex items-center justify-center border-none opacity-0 group-hover:opacity-100 scale-75 group-hover:scale-100 transition-transform shadow-md pl-0.5">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                    </button>
                                </div>
                            </div>
                            <p class="text-[13px] font-semibold text-gray-800 m-0 truncate">${escapeHtml(artist.name)}</p>
                            <p class="text-[11px] text-gray-400 m-0">${artist.count} albums</p>
                        `;
                        albumsContainer.appendChild(albumEl);
                    });
                } else {
                    albumsContainer.innerHTML = '<p class="text-sm text-gray-400 col-span-full">Không tìm thấy nghệ sĩ</p>';
                }

                emptyState.classList.add('hidden');
                resultsState.classList.remove('hidden');
            })
            .catch(err => {
                console.error('Search error:', err);
                emptyState.classList.remove('hidden');
                resultsState.classList.add('hidden');
            });
    }

    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            performSearch(e.target.value);
        }, 300);
    });

    clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        clearBtn.classList.add('hidden');
        emptyState.classList.remove('hidden');
        resultsState.classList.add('hidden');
        searchInput.focus();
    });

    // Helper: escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>
