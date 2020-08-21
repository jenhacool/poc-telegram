<?php
/**
 * Plugin Name: POC Telegram
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function poc_telegram_add_email_class( $email_classes ) {
	require_once dirname( __FILE__ ) . '/email-class.php';

	$email_classes['WC_POC_Telegram_Email'] = new WC_POC_Telegram_Email();

	return $email_classes;
}

add_filter( 'woocommerce_email_classes', 'poc_telegram_add_email_class' );

function poc_telegram_process_chatbot( $order_id ) {
	$order = wc_get_order( $order_id );

	if ( ! $order ) {
		return;
	}

	$response = wp_remote_post( 'http://51.15.215.182:3000/api/user/request_code' );

	if ( is_wp_error( $response ) ) {
		return;
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( ! $data || ! isset( $data['data'] ) || ! isset( $data['data']['code'] ) ) {
		return;
	}

	$code = $data['data']['code'];

	if ( empty( $code ) ) {
		return;
	}

	$response = wp_remote_post( 'http://51.15.215.182:3000/api/user/create', array(
		'body' => array(
			'token' => 'AAGfk5BfIftgaNxViSb62OEumIrj9yh_1Pg',
			'code' => $code,
			'phoneNumber' => $order->get_billing_phone(),
			'fullName' => $order->get_billing_last_name() . ' ' . $order->get_billing_first_name(),
			'email' => $order->get_billing_email()
		)
	) );

	if ( is_wp_error( $response ) ) {
		return;
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( ! $data || ! isset( $data['data'] ) || empty( $data['data'] ) ) {
		return;
	}

	add_post_meta( $order_id, 'telegram_chatbot_code', $code );

	WC()->mailer()->emails['WC_POC_Telegram_Email']->trigger($order_id);
}

add_action( 'woocommerce_order_status_completed', 'poc_telegram_send_email' );