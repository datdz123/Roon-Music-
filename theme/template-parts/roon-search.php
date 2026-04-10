<?php
/**
 * Template Part: Roon Search
 *
 * @package roon
 */
?>

<div id="page-search" class="roon-page hidden font-inter w-full mt-4">
    <div class="w-full max-w-4xl mx-auto px-4 flex flex-col items-center">
        <h1 class="text-[20px] font-bold text-gray-900 mb-6 w-full text-center">Tìm kiếm</h1>

        <div class="relative w-full md:w-3/4 mb-5">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </div>
            <input
                type="text"
                id="roon-search-input"
                class="w-full bg-white border border-gray-300 text-gray-900 text-[14px] rounded-full focus:outline-none focus:border-roon-blue focus:ring-1 focus:ring-roon-blue block pl-11 pr-10 py-3 transition-colors shadow-sm placeholder-gray-400"
                placeholder="Tìm bài hát, album, nghệ sĩ..."
                autocomplete="off"
            >
            <button id="roon-search-clear" class="absolute hidden inset-y-0 right-2 pr-2 pl-2 flex items-center text-gray-400 hover:text-gray-700 bg-transparent border-none cursor-pointer" type="button" aria-label="Xóa tìm kiếm">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <div class="flex items-center justify-start md:justify-center gap-2 mb-8 w-full overflow-x-auto pb-2 scrollbar-none" style="scrollbar-width: none;">
            <button type="button" class="roon-search-filter flex-shrink-0 bg-green-500 text-white text-[13px] font-medium px-4 py-1.5 rounded-full border-none cursor-pointer" data-type="all">Tất cả</button>
            <button type="button" class="roon-search-filter flex-shrink-0 bg-gray-100/80 text-gray-700 hover:bg-gray-200 text-[13px] font-medium px-4 py-1.5 rounded-full border border-gray-200 cursor-pointer transition-colors" data-type="tracks">Bài hát</button>
            <button type="button" class="roon-search-filter flex-shrink-0 bg-gray-100/80 text-gray-700 hover:bg-gray-200 text-[13px] font-medium px-4 py-1.5 rounded-full border border-gray-200 cursor-pointer transition-colors" data-type="albums">Nhạc Lossless</button>
            <button type="button" class="roon-search-filter flex-shrink-0 bg-gray-100/80 text-gray-700 hover:bg-gray-200 text-[13px] font-medium px-4 py-1.5 rounded-full border border-gray-200 cursor-pointer transition-colors" data-type="artists">Nghệ sĩ</button>
        </div>

        <p id="search-results-summary" class="hidden text-[12px] text-gray-400 mb-6 w-full text-center"></p>
    </div>

    <div id="search-state-empty" class="flex flex-col items-center justify-center text-center w-full max-w-3xl mx-auto py-10 mt-4">
        <div class="flex items-center justify-center w-16 h-16 rounded-full border-[1.5px] border-gray-200 mb-5 bg-gray-50/50">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="text-green-500">
                <path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>
            </svg>
        </div>
        <h2 class="text-[16px] font-semibold text-gray-900 m-0 mb-1.5">Bắt đầu tìm kiếm âm nhạc yêu thích</h2>
        <p class="text-[13px] text-gray-500 m-0">Nhập từ khóa để tìm bài hát, album và nghệ sĩ</p>
    </div>

    <div id="search-state-loading" class="hidden w-full max-w-3xl mx-auto py-10 text-center text-[13px] text-gray-400">
        Đang tìm kiếm...
    </div>

    <div id="search-state-no-results" class="hidden w-full max-w-3xl mx-auto py-10 text-center">
        <h2 class="text-[16px] font-semibold text-gray-900 m-0 mb-1.5">Không tìm thấy kết quả</h2>
        <p class="text-[13px] text-gray-500 m-0">Thử một từ khóa khác hoặc chuyển tab lọc</p>
    </div>

    <div id="search-state-results" class="hidden w-full mx-auto pb-10">
        <div id="search-section-tracks" class="mb-12">
            <div class="flex justify-between items-end mb-4 border-b border-gray-100 pb-2">
                <h2 class="text-[18px] font-bold text-gray-900 m-0">Bài hát</h2>
                <button type="button" class="roon-search-see-all text-[12px] text-gray-500 hover:text-gray-800 font-medium no-underline mb-0.5 bg-transparent border-none cursor-pointer p-0" data-target="tracks">Xem tất cả</button>
            </div>
            <div id="search-results-tracks" class="grid grid-cols-1 lg:grid-cols-2 gap-x-8 gap-y-1.5"></div>
        </div>

        <div id="search-section-albums" class="mb-12">
            <div class="flex justify-between items-end mb-4 border-b border-gray-100 pb-2">
                <h2 class="text-[18px] font-bold text-gray-900 m-0">Nhạc Lossless</h2>
                <button type="button" class="roon-search-see-all text-[12px] text-gray-500 hover:text-gray-800 font-medium no-underline mb-0.5 bg-transparent border-none cursor-pointer p-0" data-target="albums">Xem tất cả</button>
            </div>
            <div id="search-results-albums" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4"></div>
        </div>

        <div id="search-section-artists">
            <div class="flex justify-between items-end mb-4 border-b border-gray-100 pb-2">
                <h2 class="text-[18px] font-bold text-gray-900 m-0">Nghệ sĩ</h2>
                <button type="button" class="roon-search-see-all text-[12px] text-gray-500 hover:text-gray-800 font-medium no-underline mb-0.5 bg-transparent border-none cursor-pointer p-0" data-target="artists">Xem tất cả</button>
            </div>
            <div id="search-results-artists" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const page = document.getElementById('page-search');
    const searchInput = document.getElementById('roon-search-input');
    const clearBtn = document.getElementById('roon-search-clear');
    const emptyState = document.getElementById('search-state-empty');
    const loadingState = document.getElementById('search-state-loading');
    const noResultsState = document.getElementById('search-state-no-results');
    const resultsState = document.getElementById('search-state-results');
    const summaryEl = document.getElementById('search-results-summary');
    const filterButtons = Array.from(document.querySelectorAll('.roon-search-filter'));
    const seeAllButtons = Array.from(document.querySelectorAll('.roon-search-see-all'));
    const tracksContainer = document.getElementById('search-results-tracks');
    const albumsContainer = document.getElementById('search-results-albums');
    const artistsContainer = document.getElementById('search-results-artists');
    const sectionTracks = document.getElementById('search-section-tracks');
    const sectionAlbums = document.getElementById('search-section-albums');
    const sectionArtists = document.getElementById('search-section-artists');

    if (!page || !searchInput) {
        return;
    }

    let searchTimeout;
    let activeType = 'all';
    let activeQuery = '';
    let abortController = null;

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text == null ? '' : String(text);
        return div.innerHTML;
    }

    function setState(state) {
        emptyState.classList.toggle('hidden', state !== 'empty');
        loadingState.classList.toggle('hidden', state !== 'loading');
        noResultsState.classList.toggle('hidden', state !== 'no-results');
        resultsState.classList.toggle('hidden', state !== 'results');
        summaryEl.classList.toggle('hidden', state !== 'results');
    }

    function setActiveFilter(type) {
        activeType = type;
        filterButtons.forEach(function(button) {
            const isActive = button.dataset.type === type;
            button.className = isActive
                ? 'roon-search-filter flex-shrink-0 bg-green-500 text-white text-[13px] font-medium px-4 py-1.5 rounded-full border-none cursor-pointer'
                : 'roon-search-filter flex-shrink-0 bg-gray-100/80 text-gray-700 hover:bg-gray-200 text-[13px] font-medium px-4 py-1.5 rounded-full border border-gray-200 cursor-pointer transition-colors';
        });
    }

    function updateSectionVisibility(data) {
        const counts = {
            tracks: (data.tracks || []).length,
            albums: (data.albums || []).length,
            artists: (data.artists || []).length
        };

        const showTracks = activeType === 'all' ? counts.tracks > 0 : activeType === 'tracks';
        const showAlbums = activeType === 'all' ? counts.albums > 0 : activeType === 'albums';
        const showArtists = activeType === 'all' ? counts.artists > 0 : activeType === 'artists';

        sectionTracks.classList.toggle('hidden', !showTracks);
        sectionAlbums.classList.toggle('hidden', !showAlbums);
        sectionArtists.classList.toggle('hidden', !showArtists);
    }

    function renderTracks(tracks) {
        tracksContainer.innerHTML = '';

        if (!tracks.length) {
            tracksContainer.innerHTML = '<p class="text-sm text-gray-400 col-span-full">Không tìm thấy bài hát</p>';
            return;
        }

        tracks.forEach(function(track) {
            const artist = track.artist || 'Chưa rõ nghệ sĩ';
            const trackEl = document.createElement('a');
            trackEl.href = track.post_url || '#';
            trackEl.className = 'flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer group border border-transparent hover:border-gray-100 no-underline';
            trackEl.innerHTML = `
                <div class="relative w-[46px] h-[46px] flex-shrink-0 bg-gray-200 rounded overflow-hidden">
                    <img src="${escapeHtml(track.cover || '')}" class="w-full h-full object-cover" alt="${escapeHtml(track.title || '')}" onerror="this.src='https://placehold.co/46x46/6a4a3a/f0d0b0?text=+'" />
                    <button type="button" class="absolute inset-0 bg-black/40 flex items-center justify-center border-none opacity-0 group-hover:opacity-100 transition-opacity pl-0.5" data-stream-url="${escapeHtml(track.stream_url || '#')}" data-track-title="${escapeHtml(track.title || '')}" data-track-artist="${escapeHtml(artist)}" data-track-cover="${escapeHtml(track.cover || '')}" data-track-album-url="${escapeHtml(track.post_url || '#')}">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="white"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    </button>
                </div>
                <div class="flex flex-col flex-1 min-w-0 justify-center gap-0.5">
                    <div class="text-[13px] font-semibold text-gray-900 truncate leading-none">${escapeHtml(track.title || '')}</div>
                    <div class="text-[11.5px] text-gray-500 leading-tight truncate" title="${escapeHtml(artist)}">${escapeHtml(artist)}</div>
                </div>
                <div class="text-[12px] text-gray-400 tabular-nums pr-2 flex-shrink-0">${escapeHtml(track.duration || '--:--')}</div>
            `;
            tracksContainer.appendChild(trackEl);
        });
    }

    function renderAlbums(albums) {
        albumsContainer.innerHTML = '';

        if (!albums.length) {
            albumsContainer.innerHTML = '<p class="text-sm text-gray-400 col-span-full">Không tìm thấy album</p>';
            return;
        }

        albums.forEach(function(album) {
            const artist = album.artist || 'Chưa rõ nghệ sĩ';
            const albumEl = document.createElement('a');
            albumEl.href = album.url || '#';
            albumEl.className = 'roon-album-card cursor-pointer group no-underline';
            albumEl.innerHTML = `
                <div class="relative w-full pb-[100%] rounded-md overflow-hidden bg-gray-200 mb-2 shadow-sm border border-gray-100">
                    <img src="${escapeHtml(album.cover || '')}" alt="${escapeHtml(album.title || '')}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" onerror="this.src='https://placehold.co/240x240/6a4a3a/f0d0b0?text=ALBUM'">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center">
                        <span class="w-10 h-10 rounded-full bg-roon-blue text-white flex items-center justify-center opacity-0 group-hover:opacity-100 scale-75 group-hover:scale-100 transition-transform shadow-md pl-0.5">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        </span>
                    </div>
                </div>
                <p class="text-[13px] font-semibold text-gray-800 m-0 truncate">${escapeHtml(album.title || '')}</p>
                <p class="text-[11px] text-gray-400 m-0 truncate" title="${escapeHtml(artist)}">${escapeHtml(artist)}</p>
            `;
            albumsContainer.appendChild(albumEl);
        });
    }

    function renderArtists(artists) {
        artistsContainer.innerHTML = '';

        if (!artists.length) {
            artistsContainer.innerHTML = '<p class="text-sm text-gray-400 col-span-full">Không tìm thấy nghệ sĩ</p>';
            return;
        }

        artists.forEach(function(artist) {
            const artistEl = document.createElement('a');
            artistEl.href = artist.url || '#';
            artistEl.className = 'roon-album-card cursor-pointer group no-underline';
            artistEl.innerHTML = `
                <div class="relative w-full pb-[100%] rounded-md overflow-hidden bg-gradient-to-br from-indigo-100 to-purple-100 mb-2 shadow-sm border border-gray-100">
                    <div class="absolute inset-0 flex items-center justify-center text-3xl font-bold text-indigo-400/70">${escapeHtml(artist.initials || '')}</div>
                </div>
                <p class="text-[13px] font-semibold text-gray-800 m-0 truncate">${escapeHtml(artist.name || '')}</p>
                <p class="text-[11px] text-gray-400 m-0">${escapeHtml(artist.count || 0)} albums</p>
            `;
            artistsContainer.appendChild(artistEl);
        });
    }

    function updateSummary(data) {
        const counts = data.meta && data.meta.counts ? data.meta.counts : {
            tracks: (data.tracks || []).length,
            albums: (data.albums || []).length,
            artists: (data.artists || []).length
        };

        if (activeType === 'all') {
            summaryEl.textContent = `${counts.tracks} bài hát, ${counts.albums} album, ${counts.artists} nghệ sĩ`;
            return;
        }

        const labels = {
            tracks: 'bài hát',
            albums: 'album',
            artists: 'nghệ sĩ'
        };

        summaryEl.textContent = `${counts[activeType] || 0} ${labels[activeType] || 'kết quả'}`;
    }

    function hasVisibleResults(data) {
        if (activeType === 'tracks') {
            return (data.tracks || []).length > 0;
        }
        if (activeType === 'albums') {
            return (data.albums || []).length > 0;
        }
        if (activeType === 'artists') {
            return (data.artists || []).length > 0;
        }

        return (data.tracks || []).length > 0 || (data.albums || []).length > 0 || (data.artists || []).length > 0;
    }

    function performSearch(query) {
        activeQuery = query.trim();

        if (activeQuery.length < 2) {
            if (abortController) {
                abortController.abort();
            }
            clearBtn.classList.toggle('hidden', activeQuery.length === 0);
            summaryEl.classList.add('hidden');
            setState('empty');
            return;
        }

        clearBtn.classList.remove('hidden');
        setState('loading');

        if (abortController) {
            abortController.abort();
        }

        abortController = new AbortController();

        const endpoint = `/wp-json/roon/v1/search?s=${encodeURIComponent(activeQuery)}&type=${encodeURIComponent(activeType)}&limit=12`;

        fetch(endpoint, { signal: abortController.signal })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                renderTracks(data.tracks || []);
                renderAlbums(data.albums || []);
                renderArtists(data.artists || []);
                updateSectionVisibility(data);

                if (!hasVisibleResults(data)) {
                    setState('no-results');
                    return;
                }

                updateSummary(data);
                setState('results');
            })
            .catch(function(error) {
                if (error && error.name === 'AbortError') {
                    return;
                }
                console.error('Search error:', error);
                setState('no-results');
            });
    }

    filterButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            setActiveFilter(button.dataset.type || 'all');
            performSearch(searchInput.value || '');
        });
    });

    seeAllButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const target = button.dataset.target || 'all';
            setActiveFilter(target);
            performSearch(searchInput.value || '');

            if (resultsState.classList.contains('hidden')) {
                return;
            }

            const sectionMap = {
                tracks: sectionTracks,
                albums: sectionAlbums,
                artists: sectionArtists
            };

            const section = sectionMap[target];
            if (section) {
                section.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    searchInput.addEventListener('input', function(event) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            performSearch(event.target.value || '');
        }, 250);
    });

    clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        clearBtn.classList.add('hidden');
        searchInput.focus();
        performSearch('');
    });

    page.addEventListener('click', function(event) {
        const playButton = event.target.closest('button[data-stream-url]');

        if (playButton) {
            event.preventDefault();
            event.stopPropagation();
        }
    });

    setActiveFilter(activeType);
});
</script>
