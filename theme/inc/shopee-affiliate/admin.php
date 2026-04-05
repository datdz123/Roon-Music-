<?php
/**
 * Shopee affiliate admin page.
 *
 * @package roon
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Register admin menu for Shopee converter.
 *
 * @return void
 */
function roon_shopee_register_admin_page()
{
	add_menu_page(
		__('Shopee Affiliate', 'roon'),
		__('Shopee Affiliate', 'roon'),
		'manage_options',
		'roon-shopee-affiliate',
		'roon_shopee_render_admin_page',
		'dashicons-admin-links',
		60
	);
}
add_action('admin_menu', 'roon_shopee_register_admin_page');

/**
 * Save converter service settings.
 *
 * @return void
 */
function roon_shopee_handle_save_service_settings()
{
	if (! current_user_can('manage_options')) {
		wp_die(esc_html__('You are not allowed to do this.', 'roon'));
	}

	check_admin_referer('roon_shopee_save_service_settings_action');

	$cookie_endpoint = isset($_POST['cookie_endpoint']) ? esc_url_raw(wp_unslash($_POST['cookie_endpoint'])) : '';
	$aff_cookie      = isset($_POST['aff_cookie']) ? sanitize_textarea_field(wp_unslash($_POST['aff_cookie'])) : '';
	$service_url   = isset($_POST['service_url']) ? esc_url_raw(wp_unslash($_POST['service_url'])) : '';
	$service_token = isset($_POST['service_token']) ? sanitize_text_field(wp_unslash($_POST['service_token'])) : '';

	roon_shopee_save_data(
		array(
			'cookie_endpoint' => $cookie_endpoint,
			'aff_cookie'      => $aff_cookie,
			'service_url'   => $service_url,
			'service_token' => $service_token,
		)
	);

	$redirect_url = add_query_arg(
		array(
			'page'    => 'roon-shopee-affiliate',
			'message' => 'service_saved',
			'tab'     => 'single',
		),
		admin_url('admin.php')
	);

	wp_safe_redirect($redirect_url);
	exit;
}
add_action('admin_post_roon_shopee_save_service_settings', 'roon_shopee_handle_save_service_settings');

/**
 * Handle manual convert submit from admin page.
 *
 * @return void
 */
function roon_shopee_handle_admin_post()
{
	if (! current_user_can('manage_options')) {
		wp_die(esc_html__('You are not allowed to do this.', 'roon'));
	}

	check_admin_referer('roon_shopee_convert_action');

	$input_url = isset($_POST['input_url']) ? esc_url_raw(wp_unslash($_POST['input_url'])) : '';
	$sub_id    = isset($_POST['sub_id']) ? sanitize_text_field(wp_unslash($_POST['sub_id'])) : '';
	$cookie_endpoint = isset($_POST['cookie_endpoint']) ? esc_url_raw(wp_unslash($_POST['cookie_endpoint'])) : '';
	$aff_cookie      = isset($_POST['aff_cookie']) ? sanitize_textarea_field(wp_unslash($_POST['aff_cookie'])) : '';
	$service_url   = isset($_POST['service_url']) ? esc_url_raw(wp_unslash($_POST['service_url'])) : '';
	$service_token = isset($_POST['service_token']) ? sanitize_text_field(wp_unslash($_POST['service_token'])) : '';

	roon_shopee_save_data(
		array(
			'cookie_endpoint' => $cookie_endpoint,
			'aff_cookie'      => $aff_cookie,
			'service_url'   => $service_url,
			'service_token' => $service_token,
		)
	);

	if ('' === $input_url) {
		$redirect_url = add_query_arg(
			array(
			'page'    => 'roon-shopee-affiliate',
			'message' => 'error_empty',
			'tab'     => 'single',
			'_wpnonce'=> wp_create_nonce('roon_shopee_notice'),
			),
			admin_url('admin.php')
		);
		wp_safe_redirect($redirect_url);
		exit;
	}

	$result  = roon_shopee_convert_and_store($input_url, $sub_id);
	$message = ! empty($result['success']) ? 'success' : 'error_convert';

	$redirect_url = add_query_arg(
		array(
			'page'    => 'roon-shopee-affiliate',
			'message' => $message,
			'tab'     => 'single',
		),
		admin_url('admin.php')
	);

	wp_safe_redirect($redirect_url);
	exit;
}
add_action('admin_post_roon_shopee_convert', 'roon_shopee_handle_admin_post');

/**
 * Handle batch conversion submit from admin page.
 *
 * @return void
 */
function roon_shopee_handle_admin_post_batch()
{
	if (! current_user_can('manage_options')) {
		wp_die(esc_html__('You are not allowed to do this.', 'roon'));
	}

	check_admin_referer('roon_shopee_convert_batch_action');

	$raw_urls = isset($_POST['batch_urls']) ? wp_unslash($_POST['batch_urls']) : '';
	$sub_id   = isset($_POST['batch_sub_id']) ? sanitize_text_field(wp_unslash($_POST['batch_sub_id'])) : '';
	$cookie_endpoint = isset($_POST['cookie_endpoint']) ? esc_url_raw(wp_unslash($_POST['cookie_endpoint'])) : '';
	$aff_cookie      = isset($_POST['aff_cookie']) ? sanitize_textarea_field(wp_unslash($_POST['aff_cookie'])) : '';
	$service_url   = isset($_POST['service_url']) ? esc_url_raw(wp_unslash($_POST['service_url'])) : '';
	$service_token = isset($_POST['service_token']) ? sanitize_text_field(wp_unslash($_POST['service_token'])) : '';

	roon_shopee_save_data(
		array(
			'cookie_endpoint' => $cookie_endpoint,
			'aff_cookie'      => $aff_cookie,
			'service_url'   => $service_url,
			'service_token' => $service_token,
		)
	);

	$urls = array_filter(
		array_map('trim', preg_split('/\r\n|\r|\n/', (string) $raw_urls)),
		static function ($url) {
			return '' !== $url;
		}
	);

	$results = array();
	foreach ($urls as $url) {
		if (! wp_http_validate_url($url)) {
			$results[] = array(
				'input'   => $url,
				'success' => false,
				'error'   => 'Invalid URL.',
			);
			continue;
		}

		$converted = convert_shopee_aff($url, $sub_id);
		$results[] = array(
			'input'   => $url,
			'success' => ! empty($converted['success']),
			'output'  => ! empty($converted['affiliate_link']) ? $converted['affiliate_link'] : '',
			'error'   => ! empty($converted['error']) ? $converted['error'] : '',
		);
	}

	set_transient('roon_shopee_batch_results_' . get_current_user_id(), $results, 10 * MINUTE_IN_SECONDS);

	$redirect_url = add_query_arg(
		array(
			'page'    => 'roon-shopee-affiliate',
			'message' => 'batch_done',
			'tab'     => 'batch',
		),
		admin_url('admin.php')
	);

	wp_safe_redirect($redirect_url);
	exit;
}
add_action('admin_post_roon_shopee_convert_batch', 'roon_shopee_handle_admin_post_batch');

/**
 * Render admin page.
 *
 * @return void
 */
function roon_shopee_render_admin_page()
{
	$data          = roon_shopee_get_data();
	$current_tab   = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'single';
	$current_tab   = in_array($current_tab, array('single', 'batch'), true) ? $current_tab : 'single';
	$message       = isset($_GET['message']) ? sanitize_key(wp_unslash($_GET['message'])) : '';
	$batch_results = get_transient('roon_shopee_batch_results_' . get_current_user_id());

	?>
	<div class="wrap">
		<h1><?php echo esc_html__('Shopee Affiliate Converter', 'roon'); ?></h1>

		<?php if ('success' === $message) : ?>
			<div class="notice notice-success"><p><?php echo esc_html__('Converted successfully.', 'roon'); ?></p></div>
		<?php elseif ('error_convert' === $message) : ?>
			<div class="notice notice-error"><p><?php echo esc_html__('Conversion failed. Please check the URL.', 'roon'); ?></p></div>
		<?php elseif ('error_empty' === $message) : ?>
			<div class="notice notice-warning"><p><?php echo esc_html__('Please enter an input URL.', 'roon'); ?></p></div>
		<?php elseif ('batch_done' === $message) : ?>
			<div class="notice notice-info"><p><?php echo esc_html__('Batch conversion completed.', 'roon'); ?></p></div>
		<?php elseif ('service_saved' === $message) : ?>
			<div class="notice notice-success"><p><?php echo esc_html__('Converter service settings saved.', 'roon'); ?></p></div>
		<?php endif; ?>

		<h2><?php echo esc_html__('Converter Settings (Cookie First)', 'roon'); ?></h2>
		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
			<?php wp_nonce_field('roon_shopee_save_service_settings_action'); ?>
			<input type="hidden" name="action" value="roon_shopee_save_service_settings" />
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="cookie_endpoint"><?php echo esc_html__('Cookie Endpoint', 'roon'); ?></label></th>
					<td>
						<input type="url" name="cookie_endpoint" id="cookie_endpoint" class="large-text" value="<?php echo esc_attr($data['cookie_endpoint']); ?>" placeholder="https://affiliate.shopee.vn/..." />
						<p class="description"><?php echo esc_html__('Endpoint lấy affiliate link từ network tab (XHR/Fetch) khi bạn bấm "Lấy link" trên Shopee Affiliate.', 'roon'); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="aff_cookie"><?php echo esc_html__('Affiliate Cookie', 'roon'); ?></label></th>
					<td>
						<textarea name="aff_cookie" id="aff_cookie" rows="5" class="large-text code" placeholder="SPC_CDS=...; SPC_AFTID=...;"><?php echo esc_textarea($data['aff_cookie']); ?></textarea>
						<p class="description"><?php echo esc_html__('Dán chuỗi Cookie đầy đủ của phiên đã login tài khoản Shopee Affiliate.', 'roon'); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="service_url"><?php echo esc_html__('Service URL', 'roon'); ?></label></th>
					<td>
						<input type="url" name="service_url" id="service_url" class="large-text" value="<?php echo esc_attr($data['service_url']); ?>" placeholder="http://127.0.0.1:3018/convert" />
						<p class="description"><?php echo esc_html__('Tùy chọn fallback nếu bạn vẫn dùng service ngoài.', 'roon'); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="service_token"><?php echo esc_html__('Service Token', 'roon'); ?></label></th>
					<td>
						<input type="text" name="service_token" id="service_token" class="large-text" value="<?php echo esc_attr($data['service_token']); ?>" autocomplete="off" />
						<p class="description"><?php echo esc_html__('Chỉ cần khi dùng Service URL fallback.', 'roon'); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button(__('Save Converter Settings', 'roon')); ?>
		</form>

		<h2 class="nav-tab-wrapper">
			<a href="<?php echo esc_url(add_query_arg(array('page' => 'roon-shopee-affiliate', 'tab' => 'single'), admin_url('admin.php'))); ?>" class="nav-tab <?php echo 'single' === $current_tab ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__('Single Convert', 'roon'); ?></a>
			<a href="<?php echo esc_url(add_query_arg(array('page' => 'roon-shopee-affiliate', 'tab' => 'batch'), admin_url('admin.php'))); ?>" class="nav-tab <?php echo 'batch' === $current_tab ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__('Batch Convert', 'roon'); ?></a>
		</h2>

		<?php if ('batch' === $current_tab) : ?>
			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
				<?php wp_nonce_field('roon_shopee_convert_batch_action'); ?>
				<input type="hidden" name="action" value="roon_shopee_convert_batch" />
				<input type="hidden" name="cookie_endpoint" value="<?php echo esc_attr($data['cookie_endpoint']); ?>" />
				<input type="hidden" name="aff_cookie" value="<?php echo esc_attr($data['aff_cookie']); ?>" />
				<input type="hidden" name="service_url" value="<?php echo esc_attr($data['service_url']); ?>" />
				<input type="hidden" name="service_token" value="<?php echo esc_attr($data['service_token']); ?>" />
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="batch_urls"><?php echo esc_html__('URLs', 'roon'); ?></label></th>
						<td>
							<textarea name="batch_urls" id="batch_urls" rows="8" class="large-text" placeholder="https://shope.ee/...&#10;https://bit.ly/...&#10;https://..." required></textarea>
							<p class="description"><?php echo esc_html__('One URL per line.', 'roon'); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="batch_sub_id"><?php echo esc_html__('Sub ID', 'roon'); ?></label></th>
						<td><input type="text" name="batch_sub_id" id="batch_sub_id" class="regular-text" /></td>
					</tr>
				</table>
				<?php submit_button(__('Convert Batch', 'roon')); ?>
			</form>

			<?php if (is_array($batch_results) && ! empty($batch_results)) : ?>
				<h2><?php echo esc_html__('Batch Results', 'roon'); ?></h2>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php echo esc_html__('Input URL', 'roon'); ?></th>
							<th><?php echo esc_html__('Status', 'roon'); ?></th>
							<th><?php echo esc_html__('Affiliate Link / Error', 'roon'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($batch_results as $result) : ?>
							<tr>
								<td><?php echo esc_html($result['input'] ?? ''); ?></td>
								<td><?php echo ! empty($result['success']) ? esc_html__('Success', 'roon') : esc_html__('Failed', 'roon'); ?></td>
								<td>
									<?php if (! empty($result['success']) && ! empty($result['output'])) : ?>
										<a href="<?php echo esc_url($result['output']); ?>" target="_blank" rel="noreferrer noopener"><?php echo esc_html($result['output']); ?></a>
									<?php else : ?>
										<?php echo esc_html($result['error'] ?? 'Unknown error'); ?>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		<?php else : ?>
			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="roon-shopee-single-form">
				<?php wp_nonce_field('roon_shopee_convert_action'); ?>
				<input type="hidden" name="action" value="roon_shopee_convert" />
				<input type="hidden" name="cookie_endpoint" value="<?php echo esc_attr($data['cookie_endpoint']); ?>" />
				<input type="hidden" name="aff_cookie" value="<?php echo esc_attr($data['aff_cookie']); ?>" />
				<input type="hidden" name="service_url" value="<?php echo esc_attr($data['service_url']); ?>" />
				<input type="hidden" name="service_token" value="<?php echo esc_attr($data['service_token']); ?>" />

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="input_url"><?php echo esc_html__('Input URL', 'roon'); ?></label></th>
						<td><input type="url" name="input_url" id="input_url" class="large-text" value="<?php echo esc_attr($data['input_url']); ?>" required /></td>
					</tr>
					<tr>
						<th scope="row"><label for="sub_id"><?php echo esc_html__('Sub ID', 'roon'); ?></label></th>
						<td><input type="text" name="sub_id" id="sub_id" class="regular-text" value="<?php echo esc_attr($data['sub_id']); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html__('Output Affiliate Link', 'roon'); ?></th>
						<td>
							<input type="text" readonly class="large-text code" value="<?php echo esc_attr($data['output_aff_link']); ?>" />
							<?php if ('' !== $data['output_aff_link']) : ?>
								<p><a href="<?php echo esc_url($data['output_aff_link']); ?>" target="_blank" rel="nofollow sponsored noopener noreferrer"><?php echo esc_html__('Open affiliate link', 'roon'); ?></a></p>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html__('Metadata', 'roon'); ?></th>
						<td>
							<p><strong><?php echo esc_html__('Original URL:', 'roon'); ?></strong> <?php echo esc_html($data['original_url']); ?></p>
							<p><strong><?php echo esc_html__('Clean URL:', 'roon'); ?></strong> <?php echo esc_html($data['clean_url']); ?></p>
							<p><strong><?php echo esc_html__('Shop ID:', 'roon'); ?></strong> <?php echo esc_html($data['shopid']); ?></p>
							<p><strong><?php echo esc_html__('Item ID:', 'roon'); ?></strong> <?php echo esc_html($data['itemid']); ?></p>
							<?php if ('' !== $data['last_error']) : ?>
								<p style="color:#b32d2e;"><strong><?php echo esc_html__('Last error:', 'roon'); ?></strong> <?php echo esc_html($data['last_error']); ?></p>
							<?php endif; ?>
						</td>
					</tr>
				</table>

				<?php submit_button(__('Convert Link', 'roon'), 'primary', 'submit', false, array('id' => 'roon-shopee-submit')); ?>
				<span id="roon-shopee-loading" style="margin-left:8px;display:none;"><?php echo esc_html__('Converting...', 'roon'); ?></span>
			</form>

			<script>
				document.addEventListener('DOMContentLoaded', function () {
					var form = document.getElementById('roon-shopee-single-form');
					if (!form) {
						return;
					}
					var submit = document.getElementById('roon-shopee-submit');
					var loading = document.getElementById('roon-shopee-loading');
					form.addEventListener('submit', function () {
						if (submit) {
							submit.setAttribute('disabled', 'disabled');
						}
						if (loading) {
							loading.style.display = 'inline-block';
						}
					});
				});
			</script>
		<?php endif; ?>
	</div>
	<?php
}
