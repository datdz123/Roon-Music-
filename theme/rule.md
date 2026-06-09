# Rule.md — Quy tắc phát triển theme **roon** (Roon Music)

> Tài liệu quy ước bắt buộc cho mọi thay đổi code trong theme này. Đọc trước khi sửa.
> Theme là một music library (Album/Track/Artist) tích hợp **Jellyfin** + **ACF**, build bằng **Tailwind CSS** theo khung **`_tw` / TailPress**.

---

## 1. Kiến trúc & quy trình build (QUAN TRỌNG NHẤT)

Theme dùng pipeline build. **KHÔNG được sửa file đã compile.**

| File compiled (ĐỪNG sửa tay) | Nguồn (sửa ở đây) | Lệnh build |
|---|---|---|
| `theme/style.css` | `tailwind/tailwind.css` + class trong `.php` | `npm run dev` / `npm run watch` |
| `theme/style-editor.css`, `theme/style-editor-extra.css` | `tailwind/*.css` | như trên |
| `theme/js/script.min.js` | `javascript/script.js` (esbuild) | `npm run dev` / `npm run watch` |

- Sửa giao diện → sửa **class Tailwind trong file `.php`** rồi chạy build, **không** viết CSS tay vào `style.css`.
- Sửa logic JS chung → sửa `javascript/script.js`, **không** sửa `script.min.js`.
- Trước khi giao việc / commit: chạy `npm run prod` (production) hoặc `npm run watch` khi dev.
- Lint trước khi xong: `npm run lint` (eslint + prettier theo `@wordpress/prettier-config`).
- `theme/inc/acf/` là **thư viện ACF bundled** — KHÔNG bao giờ sửa, không refactor, không format.

---

## 2. Quy ước CSS / Tailwind

- **Tailwind utility-first**: ưu tiên class tiện ích, không tạo class CSS tùy biến trừ khi bắt buộc.
- Token thương hiệu tùy biến (định nghĩa trong `tailwind.config.js`) — dùng đúng tên, không hardcode màu:
  - Màu: `roon-blue`, `roon-indigo` (hover), `bg-roon-blue/90`, `focus:ring-roon-blue/10`
  - Font: `font-inter` (đặt trên container gốc của mỗi page/part)
  - Kích thước player: `h-roon-player`, `pb-roon-player`, `z-100`
- CSS đặc thị runtime (offset player theo `safe-area-inset`) đặt trong khối `<style>` của `template-parts/roon-player.php` qua biến `--roon-player-*`. Giữ nguyên cơ chế này.
- Giá trị lẻ dùng arbitrary value Tailwind: `text-[13px]`, `w-[260px]`, `min-h-[42px]` — theo đúng style hiện có, không đổi sang CSS rời.

---

## 3. Quy ước PHP

- **Prefix mọi hàm/biến global/hook/option/transient bằng `roon_`** (vd: `roon_get_library_albums`, `roon_VERSION`, `roon_view_count`). Không thêm hàm global không prefix.
- Indent bằng **tab**, theo WordPress Coding Standards (xem các hàm trong `functions.php`).
- Mỗi hàm có **docblock** `/** ... */` kèm `@param` / `@return` (kiểu PHPDoc, có array shape khi cần).
- **Escape khi xuất, sanitize khi nhận** — bắt buộc:
  - Xuất: `esc_html()`, `esc_url()`, `esc_attr()`, `the_title_attribute()`, `esc_html_e()`
  - Nhận: `sanitize_text_field( wp_unslash( $_GET[...] ) )`, validate URL bằng `wp_http_validate_url()` / `wp_parse_url()`
- **i18n**: mọi chuỗi UI bọc `__( '...', 'roon' )` / `_e( '...', 'roon' )`. Text domain luôn là **`roon`**.
- **Defensive**: kiểm tra `function_exists('get_field')` trước khi gọi ACF; kiểm tra `is_wp_error()`, `! empty()` trước khi dùng kết quả WP.
- File mới thêm vào theme → `require get_template_directory() . '/inc/<file>.php';` trong `functions.php` (theo mẫu các `require` cuối file). Đặt file include trong `theme/inc/`.

---

## 4. Template & template-parts

- Trang Roon (single, page-roon, home...) theo layout: `roon-sidebar` + `roon-main` (`roon-header-bar` + `#roon-content`) + `roon-player`.
- Gọi part qua: `get_template_part( 'template-parts/roon', '<tên>', array( ... ) )`. File đặt tên `template-parts/roon-<tên>.php`.
- Mỗi part bắt đầu bằng docblock `/** Template Part: ... @package roon */` và (nếu cần data) tự gọi hàm `roon_get_*()` với guard `function_exists(...)`.
- Đặt container gốc của part class `font-inter` + id/`roon-page` theo mẫu hiện có.
- JS riêng của 1 part → đặt trong `<script>` cuối part dưới dạng **IIFE vanilla JS** (`(function(){ ... })()`), không phụ thuộc framework. Logic player/chung → để trong `javascript/script.js`.

---

## 5. Dữ liệu: Jellyfin / ACF / cache

- **Nguồn track** theo thứ tự ưu tiên: ACF `jellyfin_album_id` (gọi API Jellyfin) → repeater ACF `album_tracks`. Dùng `roon_get_post_album_tracks()`, không tự query lại.
- Cấu hình Jellyfin/affiliate đọc từ **ACF options page** (`get_field( 'key', 'option' )`) qua các helper `roon_get_jellyfin_*()`, `roon_get_shopee_aff_link()`. Không hardcode URL/API key.
- Audio Jellyfin **luôn proxy qua same-origin** bằng `roon_get_jellyfin_proxy_stream_url()` (HTTPS, hỗ trợ range). Không xuất thẳng URL Jellyfin có `api_key` ra client.
- **Transient cache** cho dữ liệu nặng: `roon_library_albums_all`, `roon_library_stats_v2`, `roon_popular_albums_top50`, `roon_jellyfin_album_*`. Khi thêm cache mới:
  - Set TTL hợp lý (`HOUR_IN_SECONDS`, `MINUTE_IN_SECONDS`).
  - **Phải xóa** trong `roon_clear_library_cache()` (hook `save_post`/`edit_post`/`delete_post`) nếu dữ liệu phụ thuộc post.
- Lượt xem lưu ở post meta `roon_view_count`. "Album mới phát" dùng bản real-time (`roon_get_popular_albums`), nơi khác dùng bản cache (`roon_get_popular_albums_cached`).

---

## 6. REST API

- Namespace cố định **`roon/v1`** (vd `/wp-json/roon/v1/search`).
- Đăng ký trong `rest_api_init`, có `args` với `sanitize_callback`, `permission_callback` rõ ràng.
- Endpoint public (search/player) để `permission_callback => '__return_true'`. Endpoint mới cần auth phải set capability check thật.

---

## 7. Ngôn ngữ & UI

- **UI hiển thị bằng tiếng Việt** (vd "Phát tất cả", "Tải về", "Sắp xếp: Mới nhất").
- **Comment code có thể bằng tiếng Việt** theo phong cách hiện tại — viết ngắn, giải thích "tại sao", nhất quán với file đang sửa.
- Bỏ dấu khi search/sort: dùng `remove_accents()` (PHP) / hàm `removeDiacritics()` (JS) theo mẫu, không tự viết lại logic khác.

---

## 8. An toàn & nhất quán (Definition of Done)

Trước khi coi là xong một thay đổi:
1. ✅ Không sửa file compiled (`style.css`, `*.min.js`, `inc/acf/*`).
2. ✅ Đã chạy build Tailwind/esbuild nếu đổi class hoặc JS nguồn.
3. ✅ Output đã escape, input đã sanitize, chuỗi UI đã i18n với domain `roon`.
4. ✅ Hàm mới có prefix `roon_` + docblock.
5. ✅ Thêm cache → đã thêm vào hàm xóa cache tương ứng.
6. ✅ `npm run lint` không lỗi mới.
7. ✅ Giữ nguyên phong cách (tab indent, tên class token, layout part) của code xung quanh.
