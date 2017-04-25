<?php

/**
 * Manage meta boxes
 *
 * @link       http://joe.szalai.org
 * @since      1.0.0
 *
 * @package    Exopite_Custom_Email_Notifications
 * @subpackage Exopite_Custom_Email_Notifications/includes
 */

/**
 * Manage meta boxes
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Exopite_Custom_Email_Notifications
 * @subpackage Exopite_Custom_Email_Notifications/includes
 * @author     Joe Szalai <joe@szalai.org>
 */
class Exopite_Custom_Email_Notifications_Meta_Boxes {

    /* Create a meta box for our custom fields */
    public function render_meta_options() {

        if( ! current_user_can( 'administrator' ) && ! current_user_can( 'editor' ) ) return;

        // https://developer.wordpress.org/reference/functions/add_meta_box/
        add_meta_box("exopite-custom-email-notifications-meta-box", __( 'Send E-Mails', 'exopite-custom-email-notifications' ), array($this, "display_meta_options"), get_post_types(), "side", "low");
    }

    // Display meta box and custom fields
    public function display_meta_options() {

        global $post;

        $custom = get_post_custom( $post->ID );
        $users = get_users();
        $ignored_users = apply_filters( 'exopite-custom-email-notifications-ignored_users', array() );
        $seleted_actions = ( isset( $custom['ecen-actions'] ) ) ? maybe_unserialize( $custom['ecen-actions'][0] ) : array();
        $seleted_users = ( isset( $custom['ecen-users'] ) ) ? maybe_unserialize( $custom['ecen-users'][0] ) : array();

        ?>
        <div class="meta-row">
            <label for="ecen-activate" id="ecen-activate-label">
                <input type="checkbox" name="ecen-activate" id="ecen-activate" value="yes" <?php if ( isset ( $custom['ecen-activate'][0] ) ) checked( $custom['ecen-activate'][0], 'yes' ); ?> />
                        <?php _e( 'Activate email notifications', 'exopite-custom-email-notifications' )?>
            </label>
        </div>
        <div class="meta-row" id="ecen-actions-emails">
            <div class="meta-col meta-col-50">
                <label for="ecen-actions"><?php esc_attr_e( 'when:', 'exopite-custom-email-notifications' ); ?></label>
                <select name="ecen-actions[]" id="ecen-actions" multiple size="3">
                    <?php

                    // Translatable actions names
                    $actions = array(
                        'added' => __( 'Added', 'exopite-custom-email-notifications' ),
                        'updated' => __( 'Updated', 'exopite-custom-email-notifications' ),
                        'commented' => __( 'Commented', 'exopite-custom-email-notifications' ),
                    );

                    foreach ( $actions as $action => $name ) {

                        echo '<option value="' . $action . '"';

                        // Select previously saved items
                        if ( is_array( $seleted_actions ) && in_array( $action, $seleted_actions ) ) {
                            echo ' selected';
                        }

                        echo '>' . $name . '</option>';

                    }

                    ?>
                </select>
            </div>
            <div class="meta-col meta-col-50">
                <label for="ecen-users"><?php esc_attr_e( 'to:', 'exopite-custom-email-notifications' ); ?></label>
                    <?php

                    $options = '';
                    $count = 0;

                    foreach ( $users as $user ) {

                        if ( empty( $user->user_email ) ) continue;

                        $count++;

                        // Get user name if not empty, if empty get email
                        $display_name = ( strlen( $user->display_name ) > 0 ) ? $user->display_name : $user->user_email;

                        $options .= '<option value="' . $user->user_email . '"';

                        if ( is_array( $seleted_users ) && in_array( $user->user_email, $seleted_users ) ) {
                            $options .= ' selected';
                        }

                        $options .= '>' . $display_name . '</option>';

                    }

                    $count = ( $count > 12 ) ? 12 : $count;

                    ?>
                <select name="ecen-users[]" id="ecen-users" multiple size="<?php echo $count; ?>">
                    <?php echo $options; ?>
                </select>
            </div>
        </div>

        <?php

        //https://wordpress.stackexchange.com/questions/49453/do-i-need-a-nonce-field-for-every-meta-box-i-add-to-my-custom-post-type-admin
        //wp_nonce_field( 'theme_meta_box_nonce', 'meta_box_nonce' );

    }

    // Save custom fields
    public function save_meta_options() {

        if( ! current_user_can( 'administrator' ) && ! current_user_can( 'editor' ) ) return;

        global $post;

        update_post_meta($post->ID, "ecen-activate", sanitize_text_field( $_POST["ecen-activate"]) );

        if ( isset( $_POST["ecen-actions"] ) && is_array( $_POST["ecen-actions"] ) ) {
            update_post_meta($post->ID, "ecen-actions", array_map( 'sanitize_text_field', $_POST["ecen-actions"] ) );
        }

        if ( isset( $_POST["ecen-users"] ) && is_array( $_POST["ecen-users"] ) ) {
            update_post_meta($post->ID, "ecen-users", array_map( 'sanitize_email', $_POST["ecen-users"] ) );
        }

    }

}
