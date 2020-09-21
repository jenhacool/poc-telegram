<?php

class POC_Telegram_API
{
    const ENDPOINT = 'http://51.15.215.182:3000/api';

    public function get_chatbot_code( $params )
    {
        $response = wp_remote_post( self::ENDPOINT . '/user/request_code', array(
            'body' => array(
                'token' => 'AAGfk5BfIftgaNxViSb62OEumIrj9yh_1Pg',
            )
        ) );


        if ( is_wp_error( $response ) ) {
            return '';
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( ! $data || ! isset( $data['data'] ) || ! isset( $data['data']['code'] ) ) {
            return '';
        }

        $code = $data['data']['code'];

        if ( empty( $code ) ) {
            return '';
        }

        $body = array(
            'token' => 'AAGfk5BfIftgaNxViSb62OEumIrj9yh_1Pg',
            'code' => $code,
            'phoneNumber' => '',
            'fullName' => '',
            'email' => ''
        );

        $response = wp_remote_post( self::ENDPOINT . '/user/create', array(
            'body' => array_merge( $body, $params )
        ) );

        if ( is_wp_error( $response ) ) {
            return '';
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( ! $data || ! isset( $data['data'] ) || empty( $data['data'] ) ) {
            return '';
        }

        return $code;
    }
}