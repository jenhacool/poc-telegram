<?php

if ( ! defined( 'ABSPATH' ) ) exit;

do_action( 'woocommerce_email_header', $email_heading, $email );

$telegram_chatbot_code = get_post_meta( $order->get_id(), 'telegram_chatbot_code', true );

?>

	<p>Chúc mừng bạn đã đăng ký thành công Mayor Capital Membership.</p>

	<p>Dưới đây là mã code truy cập của bạn: <strong><?php echo $telegram_chatbot_code; ?></strong></p>
	<p>Xem hướng dẫn tham gia group tại đây: <a href="https://mayor.capital/huongdan">https://mayor.capital/huongdan</a></p>

<?php

do_action( 'woocommerce_email_footer', $email );
