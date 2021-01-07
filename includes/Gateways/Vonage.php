<?php

namespace Texty\Gateways;

use WP_Error;

/**
 * Nexmo Class
 *
 * @see https://developer.nexmo.com/api/sms
 */
class Vonage implements GatewayInterface {

    /**
     * API Endpoint
     */
    const ENDPOINT = 'https://rest.nexmo.com';

    /**
     * Get the name
     *
     * @return string
     */
    public function name() {
        return __( 'Vonage (nexmo)', 'texty' );
    }

    /**
     * Get the logo
     *
     * @return string
     */
    public function logo() {
        return TEXTY_URL . '/assets/images/vonage.svg';
    }

    /**
     * Get the credentials
     *
     * @return array
     */
    public function get_credential() {
        return [];
    }

    /**
     * Send SMS
     *
     * @param string $to
     * @param string $message
     *
     * @return WP_Error|bool
     */
    public function send( $to, $message ) {
        $creds = texty()->settings()->get( 'vonage' );

        $args = [
            'body' => [
                'from'       => texty()->settings()->from(),
                'text'       => $message,
                'to'         => $to,
                'api_key'    => $creds['key'],
                'api_secret' => $creds['secret'],
            ],
        ];

        $request       = wp_remote_post( self::ENDPOINT . '/sms/json', $args );
        $body          = json_decode( wp_remote_retrieve_body( $request ) );
        $response_code = wp_remote_retrieve_response_code( $request );

        if ( is_wp_error( $request ) ) {
            return $request;
        }

        if ( $body->messages[0]->status != '0' ) {
            return new WP_Error(
                $body->messages[0]->status,
                $body->messages[0]->{'error-text'}
            );
        }

        return true;
    }

    /**
     * Validate a REST API request
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|true
     */
    public function validate( $request ) {
        $creds = $request->get_param( 'vonage' );

        $args = [
            'api_key'    => $creds['key'],
            'api_secret' => $creds['secret'],
        ];

        $endpoint      = self::ENDPOINT . '/account/get-balance?' . http_build_query( $args );
        $response      = wp_remote_get( $endpoint, $args );
        $body          = json_decode( wp_remote_retrieve_body( $response ) );
        $response_code = wp_remote_retrieve_response_code( $response );

        if ( 401 === $response_code ) {
            return new WP_Error(
                $body->{'error-code'},
                $body->{'error-code-label'}
            );
        }

        return [
            'key'    => $creds['key'],
            'secret' => $creds['secret'],
        ];
    }
}