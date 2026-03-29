(() => {
  (function ($) {
    window.onload = function () {
      $(document).ready(function () {
        menuMobile();
        backToTop();
        toggleContent();

        // ── ROON SPA ──
        if ($('#roon-app').length) {
          RoonNav.init();
          RoonPlayer.init();
          RoonTabs.init();
        }
      });
    };

    /* =========================================================
     * MOBILE MENU (theme gốc)
     * ======================================================= */
    function menuMobile() {
      const elements = ['.bar__mb', '.header__menu'];
      if (elements.some((el) => $(el).length)) {
        $('.bar__mb').click(function () {
          $('.header__menu').toggleClass('active');
          $('.overlay').toggleClass('active');
          $('html').toggleClass('overflow-hidden');
        });
        $('.overlay').click(function () {
          $('.header__menu').removeClass('active');
          $('.overlay').removeClass('active');
          $('html').removeClass('overflow-hidden');
        });
      }
      $('.header__menu ul li.menu-item-has-children>ul').before(
        `<span class="li-plus"></span>`
      );
      if ($('.li-plus').length) {
        $('.li-plus').click(function (e) {
          $(this).toggleClass('clicked');
          $(this).next('.sub-menu').slideToggle(200);
          $(this)
            .parent()
            .siblings()
            .find('.li-plus')
            .removeClass('clicked')
            .siblings('.sub-menu')
            .slideUp();
        });
      }
    }

    /* =========================================================
     * BACK TO TOP (theme gốc)
     * ======================================================= */
    function backToTop() {
      var $bt = $('.back-to-top');
      $bt.hide();
      $(window).on('scroll', function () {
        $(this).scrollTop() > 200 ? $bt.fadeIn() : $bt.fadeOut();
      });
      $bt.on('click', function () {
        $('html, body').animate({ scrollTop: 0 }, 50);
      });
    }

    /* =========================================================
     * SEARCH TOGGLE (theme gốc)
     * ======================================================= */
    function toggleContent() {
      $('.search-icon').click(function (event) {
        event.stopPropagation();
        $(this).next().toggleClass('active');
      });
      $(document).click(function (event) {
        if (!$(event.target).closest('.search-icon,.search-wrapper').length) {
          $('.search-wrapper').removeClass('active');
        }
      });
    }

    /* =========================================================
     * ROON SPA NAVIGATION
     * ======================================================= */
    const RoonNav = {
      currentPage: 'home',
      history: ['home'],
      historyIndex: 0,

      pageTitles: {
        'home':         '',
        'albums':       'My Albums',
        'artists':      'My Artists',
        'tracks':       'My Tracks',
        'single-album': '',
        'search':       '',
        'genres':       'Genres',
        'live-radio':   'Live Radio',
        'listen-later': 'Listen Later',
        'tags':         'Tags',
        'history':      'History',
        'composers':    'Composers',
        'compositions': 'Compositions',
        'my-radio':     'My Live Radio',
        'folders':      'Folders',
      },

      init() {
        // Set home active on load
        this.showPage('home', false);

        // Mọi action liên kết (Sidebar / Stat grid) → Navigate
        $(document).on('click', '[data-page]', (e) => {
          e.preventDefault();
          const page = $(e.currentTarget).data('page');
          if ($('#page-' + page).length === 0 && !window.location.pathname.endsWith('/')) {
             // Dành cho trường hợp đang ở trong file single.php, muốn back về Home
             window.location.href = '/'; 
             return;
          }
          this.navigate(page);
        });

        // Album card clicks → single-album
        $(document).on('click', '[data-page-target="single-album"]', (e) => {
          const $card = $(e.currentTarget).closest('[data-album-title]');
          const title  = $card.data('album-title') || $(e.currentTarget).data('album-title');
          const artist = $card.data('album-artist') || '';
          this.navigate('single-album', { title, artist });
        });

        // Back / Forward buttons
        $('#btn-nav-back').on('click', () => this.goBack());
        $('#btn-nav-forward').on('click', () => this.goForward());

        // ── Logic Search Page ──
        const $searchInput = $('#roon-search-input');
        const $searchClear = $('#roon-search-clear');
        const $searchEmpty = $('#search-state-empty');
        const $searchResults = $('#search-state-results');

        $searchInput.on('input', function() {
            const val = $(this).val().trim();
            if (val.length > 0) {
                $searchClear.removeClass('hidden');
                $searchEmpty.addClass('hidden');
                $searchResults.removeClass('hidden');
            } else {
                $searchClear.addClass('hidden');
                $searchEmpty.removeClass('hidden');
                $searchResults.addClass('hidden');
            }
        });

        $searchClear.on('click', function() {
            $searchInput.val('').trigger('input').focus();
        });

        // ── Logic Filter Bar Sắp Xếp (Basic Client-Side Sort) ──
        $(document).on('click', '.sort-action', function(e) {
             e.preventDefault();
             const $btn = $(this);
             const sortType = $btn.data('sort');
             const text = $btn.text().split(':')[1] ? $btn.text().split(':')[1].trim() : $btn.text();
             
             // Update button html main text
             const $mainBtn = $btn.closest('.group').find('button');
             $mainBtn.html(`Sắp xếp: ${text} <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>`);

             // Demo Sort cho Albums grid
             if (RoonNav.currentPage === 'albums') {
                 const $grid = $('#page-albums .grid');
                 const $items = $grid.find('.roon-album-card').get();
                 
                 $items.sort(function(a, b) {
                     const titleA = $(a).data('album-title')?.toLowerCase() || '';
                     const titleB = $(b).data('album-title')?.toLowerCase() || '';
                     const yearA = $(a).data('album-year') || 0;
                     const yearB = $(b).data('album-year') || 0;
                     
                     if (sortType === 'alpha') return titleA.localeCompare(titleB);
                     if (sortType === 'release') return yearB - yearA; // mới nhất
                     return 0; // oldest/newest (mặc định)
                 });
                 $.each($items, function(i, itm) { $grid.append(itm); });
             }

             // Đóng dropdown (by removing hover state via removing focus, Tailwind group-hover handles mostly)
             document.activeElement.blur();
        });
      },

      navigate(page, meta = {}) {
        if (page === this.currentPage) return;
        // Push to history
        if (this.historyIndex < this.history.length - 1) {
          this.history = this.history.slice(0, this.historyIndex + 1);
        }
        this.history.push(page);
        this.historyIndex = this.history.length - 1;
        this.showPage(page, true, meta);
        this.updateNavArrows();
      },

      showPage(page, animate = true, meta = {}) {
        this.currentPage = page;

        // Hide all pages
        $('.roon-page').addClass('hidden').css('animation', 'none');

        // Show target
        const $target = $(`#page-${page}`);
        $target.removeClass('hidden');
        if (animate) {
          $target.css('animation', '');
          // trigger reflow
          $target[0].offsetHeight;
          $target.css('animation', 'roonFadeIn 0.2s ease');
        }

        // Update sidebar active
        $('.roon-nav-item').removeClass('text-roon-blue bg-blue-50 font-medium');
        $(`[data-page="${page}"]`).addClass('text-roon-blue bg-blue-50 font-medium');
        // Remove stroke override from icons
        $('[data-page] svg').removeClass('stroke-roon-blue');
        $(`[data-page="${page}"] svg`).addClass('stroke-roon-blue');

        // Update header title
        let title = this.pageTitles[page] || '';
        if (page === 'single-album' && meta.title) title = meta.title;
        $('#roon-page-title').text(title);

        // Scroll content to top
        $('#roon-content').scrollTop(0);
      },

      goBack() {
        if (this.historyIndex > 0) {
          this.historyIndex--;
          this.showPage(this.history[this.historyIndex], true);
          this.updateNavArrows();
        }
      },

      goForward() {
        if (this.historyIndex < this.history.length - 1) {
          this.historyIndex++;
          this.showPage(this.history[this.historyIndex], true);
          this.updateNavArrows();
        }
      },

      updateNavArrows() {
        const canBack    = this.historyIndex > 0;
        const canForward = this.historyIndex < this.history.length - 1;
        $('#btn-nav-back').toggleClass('text-gray-400', canBack).toggleClass('text-gray-200', !canBack).prop('disabled', !canBack);
        $('#btn-nav-forward').toggleClass('text-gray-400', canForward).toggleClass('text-gray-200', !canForward).prop('disabled', !canForward);
      },
    };

    /* =========================================================
     * ROON AUDIO PLAYER
     * ======================================================= */
    const RoonPlayer = {
      audio: null,
      isPlaying: false,
      isMuted: false,
      volumeBeforeMute: 0.75,

      init() {
        this.audio = document.getElementById('roon-audio');
        if (!this.audio) return;

        this.audio.volume = 0.75;
        this.bindControls();
        this.bindTracklistClicks();
        this.bindAudioEvents();
        this.bindProgressBar();
        this.bindVolumeBar();
      },

      bindControls() {
        // Play/Pause
        $('#player-play-pause').on('click', () => this.togglePlay());

        // Prev / Next (placeholder — future playlist)
        $('#player-prev').on('click', () => { this.audio.currentTime = 0; });
        $('#player-next').on('click', () => { /* next track */ });

        // Mute
        $('#player-mute').on('click', () => this.toggleMute());

        // Shuffle / Repeat (visual toggle only)
        $('#player-shuffle').on('click', function () {
          $(this).toggleClass('text-roon-blue text-gray-400');
        });
        $('#player-repeat').on('click', function () {
          $(this).toggleClass('text-roon-blue text-gray-400');
        });

        // Heart
        $('#player-heart').on('click', function () {
          $(this).toggleClass('text-roon-blue text-gray-300');
        });
      },

      bindTracklistClicks() {
        // Tracklist play buttons (single album + track rows)
        $(document).on('click', '[data-stream-url][data-track-title]', (e) => {
          const $btn    = $(e.currentTarget);
          const url     = $btn.data('stream-url');
          const title   = $btn.data('track-title');
          const artist  = $btn.data('track-artist') || '';
          const cover   = $btn.data('track-cover') || 'https://placehold.co/48x48/e5e5e5/999?text=♫';
          this.loadTrack(url, title, artist, cover);
        });
      },

      bindAudioEvents() {
        const audio = this.audio;

        audio.addEventListener('timeupdate', () => {
          const pct = audio.duration ? (audio.currentTime / audio.duration) * 100 : 0;
          $('#player-progress-fill').css('width', pct + '%');
          $('#player-progress-thumb').css('left', pct + '%');
          $('#player-current-time').text(this.formatTime(audio.currentTime));
        });

        audio.addEventListener('loadedmetadata', () => {
          $('#player-total-time').text(this.formatTime(audio.duration));
        });

        audio.addEventListener('ended', () => {
          this.isPlaying = false;
          this.updatePlayUI();
        });

        audio.addEventListener('play',  () => { this.isPlaying = true;  this.updatePlayUI(); });
        audio.addEventListener('pause', () => { this.isPlaying = false; this.updatePlayUI(); });
      },

      bindProgressBar() {
        let isDragging = false;

        $('#player-progress-bar').on('click', (e) => {
          const rect = e.currentTarget.getBoundingClientRect();
          const pct  = (e.clientX - rect.left) / rect.width;
          if (this.audio.duration) this.audio.currentTime = pct * this.audio.duration;
        });

        $('#player-progress-bar').on('mousedown', (e) => {
          isDragging = true;
          this.seekTo(e);
        });

        $(document).on('mousemove', (e) => {
          if (!isDragging) return;
          this.seekTo(e);
        });

        $(document).on('mouseup', () => { isDragging = false; });
      },

      seekTo(e) {
        const bar  = document.getElementById('player-progress-bar');
        const rect = bar.getBoundingClientRect();
        const pct  = Math.min(Math.max((e.clientX - rect.left) / rect.width, 0), 1);
        if (this.audio.duration) this.audio.currentTime = pct * this.audio.duration;
      },

      bindVolumeBar() {
        $('#player-volume-bar').on('click', (e) => {
          const bar  = e.currentTarget;
          const rect = bar.getBoundingClientRect();
          const pct  = Math.min(Math.max((e.clientX - rect.left) / rect.width, 0), 1);
          this.setVolume(pct);
        });
      },

      loadTrack(url, title, artist, cover) {
        if (!url || url === '#') {
          // Mock: just update UI
          this.updateTrackInfo(title, artist, cover);
          this.fakePlay();
          return;
        }
        this.audio.src = url;
        this.audio.load();
        this.audio.play().catch(() => {});
        this.updateTrackInfo(title, artist, cover);
      },

      fakePlay() {
        // Khi chưa có src thực, chỉ update UI
        this.isPlaying = true;
        this.updatePlayUI();
      },

      updateTrackInfo(title, artist, cover) {
        $('#player-track-title').text(title || 'Unknown track');
        $('#player-track-artist').text(artist || '—');
        $('#player-cover').attr('src', cover);
      },

      togglePlay() {
        if (!this.audio.src || this.audio.src === window.location.href) {
          this.isPlaying = !this.isPlaying;
          this.updatePlayUI();
          return;
        }
        this.isPlaying ? this.audio.pause() : this.audio.play().catch(() => {});
      },

      toggleMute() {
        this.isMuted = !this.isMuted;
        this.audio.muted = this.isMuted;
        if (this.isMuted) {
          $('#player-volume-fill').css('width', '0%');
          $('#player-volume-thumb').css('left', '0%');
        } else {
          const pct = this.audio.volume * 100;
          $('#player-volume-fill').css('width', pct + '%');
          $('#player-volume-thumb').css('left', pct + '%');
        }
      },

      setVolume(pct) {
        this.audio.volume = pct;
        $('#player-volume-fill').css('width', (pct * 100) + '%');
        $('#player-volume-thumb').css('left', (pct * 100) + '%');
        if (this.isMuted && pct > 0) {
          this.isMuted = false;
          this.audio.muted = false;
        }
      },

      updatePlayUI() {
        if (this.isPlaying) {
          $('#player-play-icon').addClass('hidden');
          $('#player-pause-icon').removeClass('hidden');
        } else {
          $('#player-play-icon').removeClass('hidden');
          $('#player-pause-icon').addClass('hidden');
        }
      },

      formatTime(sec) {
        if (isNaN(sec)) return '0:00';
        const m = Math.floor(sec / 60);
        const s = Math.floor(sec % 60);
        return `${m}:${s.toString().padStart(2, '0')}`;
      },
    };

    /* =========================================================
     * ROON TABS (Recent Activity tabs + Album tabs)
     * ======================================================= */
    const RoonTabs = {
      init() {
        // Recent activity tabs (Played / Added)
        $(document).on('click', '#recent-tabs .roon-tab', function () {
          $('#recent-tabs .roon-tab')
            .removeClass('bg-white/15 text-white')
            .addClass('text-white/60');
          $(this)
            .removeClass('text-white/60')
            .addClass('bg-white/15 text-white');
        });

        // Album detail tabs
        $(document).on('click', '.album-tab', function () {
          const tab = $(this).data('album-tab');
          // Reset tabs
          $('.album-tab')
            .removeClass('text-roon-blue border-roon-blue')
            .addClass('text-gray-400 border-transparent');
          $(this)
            .removeClass('text-gray-400 border-transparent')
            .addClass('text-roon-blue border-roon-blue');
          // Show / hide content
          if (tab === 'tracks') {
            $('#album-tab-tracks').removeClass('hidden');
            $('#album-tab-credits').addClass('hidden');
          } else {
            $('#album-tab-tracks').addClass('hidden');
            $('#album-tab-credits').removeClass('hidden');
          }
        });
      },
    };

  })(jQuery);
})();

/* ── CSS animation for page transitions ── */
(function () {
  const style = document.createElement('style');
  style.textContent = `
    @keyframes roonFadeIn {
      from { opacity:0; transform:translateY(8px); }
      to   { opacity:1; transform:translateY(0); }
    }
    /* Thin custom scrollbar for roon-content */
    #roon-content::-webkit-scrollbar { width: 5px; }
    #roon-content::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
    #roon-sidebar::-webkit-scrollbar { width: 4px; }
    #roon-sidebar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
    /* Smooth progress fill */
    #player-progress-fill { transition-property: width; transition-timing-function: linear; transition-duration: 100ms; }
    /* Prevent horizontal scroll on recent row */
    #recent-albums-grid::-webkit-scrollbar { display:none; }
  `;
  document.head.appendChild(style);
})();
