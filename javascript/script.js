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
          $(this).next(".sub-menu").slideToggle(200);
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
      currentPage: "home",
      history: ["home"],
      historyIndex: 0,

      pageTitles: {
        "home":         "",
        "albums":       "Album Nhạc",
        "artists":      "Ca Sĩ / Artist",
        "tracks":       "Bài Hát / Track",
        "single-album": "",
        "search":       "",
        "fav-albums":   "Album Yêu Thích",
        "fav-artists":  "Ca Sĩ Yêu Thích",
        "player":       "Trình Phát",
        "contact":      "Liên Hệ Quảng Cáo",
        "genres":       "Genres",
        "live-radio":   "Live Radio",
        "listen-later": "Listen Later",
        "tags":         "Tags",
        "history":      "History",
        "composers":    "Composers",
        "compositions": "Compositions",
        "my-radio":     "My Live Radio",
        "folders":      "Folders"
      },

      init() {
        // Đọc hash từ URL để tự động mở đúng trang sau redirect từ single.php
        // Ví dụ: /#albums → mở trang Albums
        let startPage = 'home';
        if (window.location.hash) {
          const hashPage = window.location.hash.replace('#', '');
          if (hashPage && $('#page-' + hashPage).length > 0) {
            startPage = hashPage;
          }
          // Xóa hash khỏi URL bar cho gọn
          if (window.history && window.history.replaceState) {
            window.history.replaceState(null, '', window.location.pathname + window.location.search);
          }
        }

        if ($('body').hasClass('single-post')) {
            // Đỏ menu Albums trên màn hình single.php
            $('.roon-nav-item').removeClass('text-roon-blue bg-blue-50/80 hover:bg-blue-100/60').addClass('text-gray-800');
            $('[data-page="albums"]').removeClass('text-gray-800').addClass('text-roon-blue bg-blue-50/80 hover:bg-blue-100/60');
            $('[data-page] svg').removeClass('stroke-roon-blue').addClass('stroke-gray-700');
            $('[data-page="albums"] svg').removeClass('stroke-gray-700').addClass('stroke-roon-blue');
        } else {
            // Khởi tạo history với trang ban đầu
            this.history = [startPage];
            this.historyIndex = 0;
            this.showPage(startPage, false);
        }

        // Mọi action liên kết (Sidebar / Stat grid) → Navigate
        $(document).on('click', '[data-page]', (e) => {
          e.preventDefault();
          const page = $(e.currentTarget).data('page');
          // Nếu DOM không chứa SPA pages (đang ở single.php hoặc trang khác)
          // thì redirect về homepage với hash để RoonNav tự mở đúng tab
          if ($('#page-' + page).length === 0) {
            const homeUrl = (window.roonHomeUrl || '/') + '#' + page;
            window.location.href = homeUrl;
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
        $('#btn-nav-back').on('click', () => {
             if ($('body').hasClass('single-post')) {
                 if (document.referrer && document.referrer.includes(window.location.host)) {
                     window.history.back();
                 } else {
                     window.location.href = window.roonHomeUrl || '/';
                 }
             } else {
                 this.goBack();
             }
        });
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
        $('.roon-nav-item').removeClass('text-roon-blue bg-blue-50/80 hover:bg-blue-100/60').addClass('text-gray-800');
        $(`[data-page="${page}"]`).removeClass('text-gray-800').addClass('text-roon-blue bg-blue-50/80 hover:bg-blue-100/60');
        // Remove stroke override from icons
        $('[data-page] svg').removeClass('stroke-roon-blue').addClass('stroke-gray-700');
        $(`[data-page="${page}"] svg`).removeClass('stroke-gray-700').addClass('stroke-roon-blue');

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
      isShuffle: false,
      repeatMode: 'none', // 'none' | 'all' | 'one'
      volumeBeforeMute: 0.5,
      playlist: [],       // [{url, title, artist, cover, albumUrl}]
      currentTrackIndex: 0,
      affiliateUrl: "",
      dailyAffiliateLimit: 2,
      init() {
        this.audio = document.getElementById('roon-audio');
        if (!this.audio) return;

        this.audio.volume = 0.5;
        this.restoreState();
        this.bindControls();
        this.bindTracklistClicks();
        this.bindAudioEvents();
        this.bindProgressBar();
        this.bindVolumeBar();
        this.bindSidebarToggle();

        window.addEventListener('beforeunload', () => {
          this.saveState();
        });
      },
      saveState() {
        if (!this.playlist || !this.playlist.length) return;
        try {
          sessionStorage.setItem('roonPlayerState', JSON.stringify({
            playlist: this.playlist,
            currentIndex: this.currentTrackIndex,
            currentTime: this.audio.currentTime || 0,
            isPlaying: !this.audio.paused && this.isPlaying,
            repeatMode: this.repeatMode,
            isShuffle: this.isShuffle,
            src: this.audio.src || ''
          }));
        } catch (e) {}
      },
      restoreState() {
        try {
          const st = sessionStorage.getItem('roonPlayerState');
          if (!st) return;
          const pst = JSON.parse(st);
          if (pst.playlist && pst.playlist.length) {
            this.playlist = pst.playlist;
            this.currentTrackIndex = pst.currentIndex || 0;
            this.isShuffle = pst.isShuffle || false;
            this.repeatMode = pst.repeatMode || 'none';

            // Restore UI Buttons
            $('#player-shuffle').toggleClass('text-roon-blue bg-blue-50/50', this.isShuffle).toggleClass('text-gray-400', !this.isShuffle);
            const rModes = { 'none': 'Repeat', 'all': 'Lặp tất cả', 'one': 'Lặp 1 bài' };
            $('#player-repeat').removeClass('text-roon-blue text-gray-400 bg-blue-50/50');
            if (this.repeatMode === 'none') {
               $('#player-repeat').addClass('text-gray-400');
            } else {
               $('#player-repeat').addClass('text-roon-blue bg-blue-50/50');
            }
            $('#player-repeat').attr('title', rModes[this.repeatMode]);

            const t = this.playlist[this.currentTrackIndex];
            this.updateTrackInfo(t.title, t.artist, t.cover, t.albumUrl);

            // Restore audio details
            if (pst.src) {
              this.audio.src = pst.src;
              this.audio.currentTime = pst.currentTime || 0;
              if (pst.isPlaying) {
                const playPromise = this.audio.play();
                if (playPromise !== undefined) {
                    playPromise.then(() => {
                        this.isPlaying = true;
                        this.updatePlayUI();
                    }).catch(() => {
                        this.isPlaying = false;
                        this.updatePlayUI();
                    });
                }
              }
            }
          }
        } catch(e) {}
      },
      bindSidebarToggle() {
        const $sidebar = $('#roon-sidebar');
        const $overlay = $('#roon-sidebar-overlay');
        const closeSidebar = () => {
          $sidebar.removeClass('max-lg:translate-x-0');
          $overlay.addClass('hidden');
        };
        const isOpen = () => $sidebar.hasClass('max-lg:translate-x-0');

        $('#btn-sidebar-toggle').on('click', function(e) {
          e.stopPropagation();
          $sidebar.toggleClass('max-lg:translate-x-0');
          $overlay.toggleClass('hidden');
        });

        $('#roon-sidebar-close').on('click', function() {
          closeSidebar();
        });

        $overlay.on('click', function() {
          closeSidebar();
        });

        $(document).on('click', function(e) {
          if (!isOpen()) return;
          const $target = $(e.target);
          if ($target.closest('#roon-sidebar').length) {
            if ($target.closest('a').length || $target.closest('#roon-donate-trigger').length) {
              closeSidebar();
            }
            return;
          }
          if ($target.closest('#btn-sidebar-toggle').length) return;
          closeSidebar();
        });
      },
      bindControls() {
        // Play/Pause
        $('#player-play-pause').on('click', () => this.togglePlay());

        // Prev
        $('#player-prev').on('click', () => this.playPrev());

        // Next
        $('#player-next').on('click', () => this.playNext());

        // Mute
        $('#player-mute').on('click', () => this.toggleMute());

        // Shuffle (thực sự đảo ngẫu nhiên)
        $('#player-shuffle').on('click', () => {
          this.isShuffle = !this.isShuffle;
          $('#player-shuffle').toggleClass('text-roon-blue bg-blue-50', this.isShuffle)
                              .toggleClass('text-gray-400', !this.isShuffle);
        });

        // Repeat: none → all → one → none
        $('#player-repeat').on('click', () => {
          if (this.repeatMode === 'none') {
            this.repeatMode = 'all';
            $('#player-repeat').addClass('text-roon-blue bg-blue-50').removeClass('text-gray-400');
            $('#player-repeat').attr('title', 'Lặp tất cả');
          } else if (this.repeatMode === 'all') {
            this.repeatMode = 'one';
            $('#player-repeat').addClass('text-roon-blue bg-blue-50');
            $('#player-repeat').attr('title', 'Lặp 1 bài');
          } else {
            this.repeatMode = 'none';
            $('#player-repeat').removeClass('text-roon-blue bg-blue-50').addClass('text-gray-400');
            $('#player-repeat').attr('title', 'Repeat');
          }
        });

        // Heart
        $('#player-heart').on('click', function () {
          $(this).toggleClass('text-roon-blue text-gray-300');
        });

        // ── Phát tất cả (play-all-tracks) ──
        $(document).on('click', '#play-all-tracks', (e) => {
          e.preventDefault();
          const allBtns = $('[data-stream-url][data-track-title]').toArray();
          if(allBtns.length === 0) return;
          this.playlist = allBtns.map((b) => ({
            url:      $(b).data('stream-url'),
            title:    $(b).data('track-title'),
            artist:   $(b).data('track-artist') || '',
            cover:    $(b).data('track-cover') || 'https://placehold.co/48x48/e5e5e5/999?text=\u266B',
            albumUrl: $(b).data('track-album-url') || '',
          }));
          this.currentTrackIndex = 0;
          const t = this.playlist[0];
          this.loadTrack(t.url, t.title, t.artist, t.cover, t.albumUrl);
        });
      },
      bindTracklistClicks() {
        // Tracklist play buttons (single album + track rows)
        $(document).on('click', '[data-stream-url][data-track-title]', (e) => {
          e.preventDefault();
          e.stopPropagation();
          const $btn     = $(e.currentTarget);
          const url      = $btn.data('stream-url');
          const title    = $btn.data('track-title');
          const artist   = $btn.data('track-artist') || '';
          const cover    = $btn.data('track-cover') || 'https://placehold.co/48x48/e5e5e5/999?text=♫';
          const albumUrl = $btn.data('track-album-url') || '';

          // Build playlist từ tất cả nút play hiện có trong DOM
          const allBtns = $('[data-stream-url][data-track-title]').toArray();
          this.playlist = allBtns.map((b) => ({
            url:      $(b).data('stream-url'),
            title:    $(b).data('track-title'),
            artist:   $(b).data('track-artist') || '',
            cover:    $(b).data('track-cover') || 'https://placehold.co/48x48/e5e5e5/999?text=♫',
            albumUrl: $(b).data('track-album-url') || '',
          }));
          this.currentTrackIndex = allBtns.indexOf(e.currentTarget);

          this.loadTrack(url, title, artist, cover, albumUrl);
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
          if (this.repeatMode === 'one') {
            this.audio.currentTime = 0;
            this.audio.play().catch(() => {});
          } else if (this.repeatMode === 'all' || this.isShuffle) {
            this.playNext();
          } else {
            this.isPlaying = false;
            this.updatePlayUI();
          }
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

      playNext() {
        if (this.playlist.length === 0) return;
        let idx;
        if (this.isShuffle) {
          idx = Math.floor(Math.random() * this.playlist.length);
        } else {
          idx = (this.currentTrackIndex + 1) % this.playlist.length;
        }
        this.currentTrackIndex = idx;
        const t = this.playlist[idx];
        this.loadTrack(t.url, t.title, t.artist, t.cover, t.albumUrl);
      },

      playPrev() {
        if (this.playlist.length === 0) { this.audio.currentTime = 0; return; }
        let idx = (this.currentTrackIndex - 1 + this.playlist.length) % this.playlist.length;
        this.currentTrackIndex = idx;
        const t = this.playlist[idx];
        this.loadTrack(t.url, t.title, t.artist, t.cover, t.albumUrl);
      },

      loadTrack(url, title, artist, cover, albumUrl) {
        if (!url || url === '#') {
          this.updateTrackInfo(title, artist, cover, albumUrl);
          this.fakePlay();
          return;
        }
        this.audio.src = url;
        this.audio.load();
        this.audio.play().catch(() => {});
        this.updateTrackInfo(title, artist, cover, albumUrl);
      },

      fakePlay() {
        // Khi chưa có src thực, chỉ update UI
        this.isPlaying = true;
        this.updatePlayUI();
      },

      updateTrackInfo(title, artist, cover, albumUrl) {
        // Dùng link thay vì <p> trong HTML mới
        const $titleEl = $('#player-track-title-link');
        if ($titleEl.length) {
          $titleEl.text(title || 'Unknown track');
          $titleEl.attr('href', albumUrl || '#');
        } else {
          $('#player-track-title').text(title || 'Unknown track');
        }
        $('#player-track-artist').text(artist || '—');
        $('#player-cover').attr('src', cover);
        // Cập nhật link ảnh cover về album
        const $albumLink = $('#player-album-link');
        if ($albumLink.length) {
          $albumLink.attr('href', albumUrl || '#');
        }
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
