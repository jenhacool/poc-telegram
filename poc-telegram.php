<?php
/**
 * Plugin Name: POC Telegram
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function poc_telegram_add_email_class( $email_classes ) {
    include_once __DIR__ . '/class-api.php';
    include_once __DIR__ . '/class-email.php';
    $email_classes['WC_POC_Telegram_Email'] = new WC_POC_Telegram_Email();
	return $email_classes;
}

add_filter( 'woocommerce_email_classes', 'poc_telegram_add_email_class' );

function poc_telegram_add_order_meta_box_action( $actions ) {
    $actions['wc_poc_telegram_get_code'] = __( 'Get & send Telegram chatbot code', 'poc-foundation' );

    return $actions;
}

add_action( 'woocommerce_order_actions', 'poc_telegram_add_order_meta_box_action' );

function poc_telegram_process_get_code( WC_Order $order ) {
    $mailer = WC()->mailer();

    $mails = $mailer->get_emails();

    if ( empty( $mails ) ) {
        return;
    }

    foreach ( $mails as $mail ) {
        if ( $mail->id == 'wc_poc_telegram' ) {
            $mail->trigger( $order->get_id() );
        }
    }
}

add_action( 'woocommerce_order_action_wc_poc_telegram_get_code', 'poc_telegram_process_get_code' );

function poc_telegram_cron_schedules($schedules){
    if( ! isset($schedules['5min'] ) ) {
        $schedules['5min'] = array(
            'interval' => 5 * 60,
            'display' => __( 'Once every 5 minutes', 'poc-foundation' )
        );
    }

    return $schedules;
}

add_filter( 'cron_schedules', 'poc_telegram_cron_schedules' );

if ( ! wp_next_scheduled( 'poc_telegram_task_hook' ) ) {
    wp_schedule_event( time(), '5min', 'poc_telegram_task_hook' );
}

add_action ( 'poc_telegram_task_hook', 'poc_foundation_task_function' );

function poc_foundation_task_function() {
    $orders = wc_get_orders( array(
        'status' => 'completed',
        'meta_query' => array(
            'key' => 'telegram_chatbot_code_sent',
            'value' => 1,
            'compare' => '!='
        ),
    ) );

    if ( empty( $orders ) ) {
        return;
    }

    $mailer = WC()->mailer();

    $mails = $mailer->get_emails();

    $mail = null;

    if ( empty( $mails ) ) {
        return;
    }

    foreach ( $mails as $m ) {
        if ( $m->id == 'wc_poc_telegram' ) {
            $mail = $m;
        }
    }

    foreach ( $orders as $order ) {
        $mail->trigger( $order->get_id() );
    }

    return;
}