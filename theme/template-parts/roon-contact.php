<?php
/**
 * Template Part: Roon Contact — Liên Hệ Quảng Cáo
 * @package roon
 */

$title = function_exists('get_field') ? get_field('contact_title', 'option') : 'Liên Hệ Quảng Cáo';
if (empty($title)) $title = 'Liên Hệ Quảng Cáo';

$desc = function_exists('get_field') ? get_field('contact_desc', 'option') : 'Bạn muốn quảng cáo sản phẩm hoặc hợp tác cùng Roon Music? Hãy liên hệ với chúng tôi qua các kênh bên dưới.';
if (empty($desc)) $desc = 'Bạn muốn quảng cáo sản phẩm hoặc hợp tác cùng Roon Music? Hãy liên hệ với chúng tôi qua các kênh bên dưới.';

$methods = function_exists('get_field') ? get_field('contact_methods', 'option') : null;
?>

<div id="page-contact" class="roon-page hidden">
    <div class="max-w-2xl mx-auto py-12">
        <!-- Hero -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 mb-5 shadow-lg">
                <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2"><?php echo esc_html($title); ?></h2>
            <div class="text-[15px] text-gray-500 leading-relaxed max-w-md mx-auto">
                <?php echo nl2br(esc_html($desc)); ?>
            </div>
        </div>

        <!-- Contact Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <?php if (!empty($methods) && is_array($methods)) : ?>
                <?php foreach ($methods as $method) : 
                    $type = $method['method_type'] ?? 'email';
                    $label = $method['method_label'] ?? '';
                    $val = $method['method_value'] ?? '';
                    $link = $method['method_link'] ?? '';
                ?>
                <div class="flex items-start gap-4 bg-gray-50 rounded-xl p-5 border border-gray-100 hover:border-blue-200 hover:shadow-sm transition-all duration-200">
                    <div class="flex-shrink-0 w-11 h-11 rounded-lg <?php echo $type === 'phone' ? 'bg-green-100' : 'bg-blue-100'; ?> flex items-center justify-center">
                        <?php if ($type === 'email'): ?>
                            <svg class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        <?php elseif ($type === 'facebook'): ?>
                            <svg class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        <?php elseif ($type === 'zalo'): ?>
                            <span class="text-blue-600 text-[13px] font-bold">Zalo</span>
                        <?php elseif ($type === 'phone'): ?>
                            <svg class="w-5 h-5 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                        <?php else: ?>
                            <svg class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        <?php endif; ?>
                    </div>
                    <div>
                        <p class="text-[14px] font-semibold text-gray-800 mb-1"><?php echo esc_html($label); ?></p>
                        <?php if (!empty($link)): ?>
                            <a href="<?php echo esc_url($link); ?>" class="text-[14px] text-blue-600 hover:text-blue-700 no-underline" target="_blank"><?php echo esc_html($val); ?></a>
                        <?php else: ?>
                            <p class="text-[14px] text-gray-500 mb-0"><?php echo esc_html($val); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else : ?>
                <!-- Fallback khi chưa nhập data ACF -->
                <div class="col-span-1 border border-dashed border-gray-200 rounded-xl p-6 text-center text-gray-400 text-sm">
                    Vui lòng thêm thông tin liên hệ trong phần quản trị (Cài đặt chung).
                </div>
            <?php endif; ?>
        </div>

        <!-- Note -->
        <div class="mt-8 bg-blue-50/50 border border-blue-100 rounded-xl p-5 text-center">
            <p class="text-[14px] text-gray-600 mb-0">
                📩 Chúng tôi sẽ phản hồi trong vòng <strong>24 giờ</strong> làm việc.
            </p>
        </div>
    </div>
</div>
