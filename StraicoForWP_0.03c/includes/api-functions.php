<?php

function straico_get_models() {
    $api_key = get_option( 'straico_api_key' );

    // Check if API key is set
    if ( empty( $api_key ) ) {
        return new WP_Error( 'straico_api_error', 'API Key is missing. Please set your Straico API key in the settings.' );
    }

    $response = wp_remote_get( 'https://api.straico.com/v1/models', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
        ),
        'timeout' => 60, // Set a timeout to handle timeouts gracefully
    ) );

    if ( is_wp_error( $response ) ) {
        return new WP_Error( 'straico_api_error', 'Failed to connect to Straico API: ' . $response->get_error_message() );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body = wp_remote_retrieve_body( $response );

    if ( $status_code != 200 ) {
        if ( $status_code == 401 ) {
            return new WP_Error( 'straico_api_error', 'Unauthorized: Invalid API Key. Please check your Straico API key in the settings.' );
        } else if ( $status_code == 429 ) {
            return new WP_Error( 'straico_api_error', 'Too Many Requests: You have exceeded your rate limit.' );
        } else {
            return new WP_Error( 'straico_api_error', 'Straico API returned status code ' . $status_code . '. Response: ' . $body );
        }
    }

    $data = json_decode( $body, true );

    if ( isset( $data['data'] ) ) {
        return $data['data'];
    } else {
        return new WP_Error( 'straico_api_error', 'Invalid response from Straico API.' );
    }
}

function straico_get_completion( $model, $prompt ) {
    $api_key = get_option( 'straico_api_key' );

    // Check if API key is set
    if ( empty( $api_key ) ) {
        return new WP_Error( 'straico_api_error', 'API Key is missing. Please set your Straico API key in the settings.' );
    }

    $endpoint = 'https://api.straico.com/v1/prompt/completion';

    $request_body = array(
        'models' => array( $model ),
        'message' => $prompt,
    );

    $response = wp_remote_post( $endpoint, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
        ),
        'body' => wp_json_encode( $request_body ),
        'timeout' => 120, // Set a timeout to handle timeouts gracefully
    ) );

    if ( is_wp_error( $response ) ) {
        return new WP_Error( 'straico_api_error', 'Failed to connect to Straico API: ' . $response->get_error_message() );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body = wp_remote_retrieve_body( $response );

    if ( $status_code != 200 && $status_code != 201 ) {
        if ( $status_code == 401 ) {
            return new WP_Error( 'straico_api_error', 'Unauthorized: Invalid API Key. Please check your Straico API key in the settings.' );
        } else if ( $status_code == 429 ) {
            return new WP_Error( 'straico_api_error', 'Too Many Requests: You have exceeded your rate limit.' );
        } else {
            return new WP_Error( 'straico_api_error', 'Straico API returned status code ' . $status_code . '. Response: ' . $body );
        }
    }

    $data = json_decode( $body, true );

    if ( isset( $data['data']['completions'][ $model ]['completion']['choices'][0]['message']['content'] ) ) {
        return array(
            'content' => $data['data']['completions'][ $model ]['completion']['choices'][0]['message']['content'],
        );
    } else {
        // Capture error messages from Straico API
        if ( isset( $data['message'] ) ) {
            return new WP_Error( 'straico_api_error', 'Straico API error: ' . $data['message'] );
        } else {
            return new WP_Error( 'straico_api_error', 'Invalid response from Straico API.' );
        }
    }
}