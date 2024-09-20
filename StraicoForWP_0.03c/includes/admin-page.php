<?php

add_action( 'admin_menu', 'straico_add_admin_page' );

function straico_add_admin_page() {
    add_menu_page(
        'Straico Content Generator',
        'Straico Generator',
        'edit_posts',
        'straico-content-generator',
        'straico_render_admin_page',
        'dashicons-admin-page',
        6
    );
}

add_action( 'admin_post_straico_generate_content', 'straico_handle_form_submission' );

function straico_render_admin_page() {

    // Ensure the user has entered an API key
    $api_key = get_option( 'straico_api_key' );
    if ( !$api_key ) {
        echo '<div class="notice notice-error"><p>Please set your Straico API key in the <a href="options-general.php?page=straico-settings">settings page</a>.</p></div>';
        return;
    }

    // Check for error messages from form submission
    if ( isset( $_GET['error_message'] ) ) {
        echo '<div class="notice notice-error"><p>' . esc_html( urldecode( $_GET['error_message'] ) ) . '</p></div>';
    }

    // Get available models
    $models = straico_get_models();

    if ( is_wp_error( $models ) ) {
        $error_message = $models->get_error_message();
        echo '<div class="notice notice-error"><p>' . esc_html( $error_message ) . '</p></div>';
        return; // Exit if models cannot be retrieved
    }

    ?>

    <div class="wrap">
        <h1>Straico Content Generator</h1>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="straico-form">
            <?php wp_nonce_field( 'straico_generate_content_nonce' ); ?>
            <input type="hidden" name="action" value="straico_generate_content">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Prompt</th>
                    <td>
                        <textarea name="straico_prompt" rows="5" cols="80" required><?php echo isset( $_POST['straico_prompt'] ) ? esc_textarea( $_POST['straico_prompt'] ) : ''; ?></textarea>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Model</th>
                    <td>
                        <select name="straico_model" required>
                            <?php foreach ( $models as $model_group ) : ?>
                                <?php foreach ( $model_group as $model_option ) : ?>
                                    <option value="<?php echo esc_attr( $model_option['model'] ); ?>" <?php selected( $model_option['model'], isset( $_POST['straico_model'] ) ? $_POST['straico_model'] : '' ); ?>>
                                        <?php echo esc_html( $model_option['name'] ); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Post Type</th>
                    <td>
                        <select name="straico_post_type">
                            <option value="post" <?php selected( 'post', isset( $_POST['straico_post_type'] ) ? $_POST['straico_post_type'] : '' ); ?>>Post</option>
                            <option value="page" <?php selected( 'page', isset( $_POST['straico_post_type'] ) ? $_POST['straico_post_type'] : '' ); ?>>Page</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Title</th>
                    <td>
                        <input type="text" name="straico_post_title" size="50" value="<?php echo isset( $_POST['straico_post_title'] ) ? esc_attr( $_POST['straico_post_title'] ) : ''; ?>" />
                    </td>
                </tr>
            </table>
            <?php submit_button( 'Generate Content', 'primary', 'straico_generate_content' ); ?>
        </form>
        <div id="straico-loading-message" style="display:none; margin-top: 20px;">
            <p>Requesting your new content from Straico... This may take a minute...<span id="loading-dots"></span></p>
        </div>
    </div>

    <script type="text/javascript">
    jQuery(document).ready(function($){

        $('#straico-form').on('submit', function(){
            // Show the loading message
            $('#straico-loading-message').show();

            // Disable the submit button to prevent multiple submissions
            $('input[type="submit"][name="straico_generate_content"]').prop('disabled', true);

            // Start the dots animation
            var loadingDots = $('#loading-dots');
            var dots = 0;
            var maxDots = 3;
            var loadingInterval = setInterval(function(){
                dots = (dots + 1) % (maxDots + 1);
                loadingDots.text('.'.repeat(dots));
            }, 500);

            // Allow the form to submit
            return true;
        });

    });
    </script>

    <?php
}

function straico_handle_form_submission() {
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'Unauthorized user' );
    }

    check_admin_referer( 'straico_generate_content_nonce' );

    // Ensure the user has entered an API key
    $api_key = get_option( 'straico_api_key' );
    if ( !$api_key ) {
        wp_die( 'Please set your Straico API key in the settings.' );
    }

    $prompt = sanitize_textarea_field( $_POST['straico_prompt'] );
    $model = sanitize_text_field( $_POST['straico_model'] );
    $title = sanitize_text_field( $_POST['straico_post_title'] );
    $post_type = sanitize_text_field( $_POST['straico_post_type'] );

    // Call the API function to get the completion
    $completion = straico_get_completion( $model, $prompt );

    if ( is_wp_error( $completion ) ) {
        $error_message = $completion->get_error_message();
        // Redirect back with error message
        wp_redirect( add_query_arg( 'error_message', urlencode( $error_message ), wp_get_referer() ) );
        exit;
    } elseif ( $completion && isset( $completion['content'] ) ) {
        // Process the completion content with Parsedown and sanitize
        $Parsedown = new Parsedown();
        $post_content = wp_kses_post( $Parsedown->text( $completion['content'] ) );

        // Create a new post or page
        $post_data = array(
            'post_title'   => $title ? $title : 'Straico Generated Content',
            'post_content' => $post_content,
            'post_status'  => 'draft',
            'post_author'  => get_current_user_id(),
            'post_type'    => $post_type,
        );

        $post_id = wp_insert_post( $post_data );

        if ( $post_id ) {
            // Redirect to the post editor
            wp_redirect( get_edit_post_link( $post_id, '' ) );
            exit;
        } else {
            $error_message = 'Failed to create ' . ucfirst( $post_type ) . '.';
            wp_redirect( add_query_arg( 'error_message', urlencode( $error_message ), wp_get_referer() ) );
            exit;
        }
    } else {
        $error_message = 'Failed to get a completion from Straico API.';
        wp_redirect( add_query_arg( 'error_message', urlencode( $error_message ), wp_get_referer() ) );
        exit;
    }
}