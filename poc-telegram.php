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