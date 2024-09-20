<?php

add_action( 'admin_menu', 'straico_add_settings_page' );
add_action( 'admin_init', 'straico_register_settings' );

function straico_add_settings_page() {
    add_options_page(
        'Straico Settings',
        'Straico Settings',
        'manage_options',
        'straico-settings',
        'straico_render_settings_page'
    );
}

function straico_register_settings() {
    register_setting( 'straico_settings_group', 'straico_api_key', 'sanitize_text_field' );
}

function straico_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Straico Settings</h1>
        <form method="post" action="options.php">
            <?php
                settings_fields( 'straico_settings_group' );
                do_settings_sections( 'straico-settings' );
                $api_key = get_option( 'straico_api_key' );
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Straico API Key</th>
                    <td><input type="password" name="straico_api_key" value="<?php echo esc_attr( $api_key ); ?>" size="50" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}