<?php

$telegram_chatbot_code = get_post_meta( $order->ID, 'telegram_chatbot_code' );

?>

<p>Chúc mừng bạn đã đăng ký thành công Mayor Capital Membership.</p>

<p>Link Bot: <a href="https://web.telegram.org/#/im?p=@BonBonVN_bot">https://web.telegram.org/#/im?p=@BonBonVN_bot</a></p>

<p>Mã code truy cập của bạn: <strong><?php echo $telegram_chatbot_code; ?></strong></p>

<p>Xem hướng dẫn tham gia group tại đây: <a href="https://mayor.capital/huongdan">https://mayor.capital/huongdan</a></p>
