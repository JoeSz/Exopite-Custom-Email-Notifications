<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://joe.szalai.org
 * @since      1.0.0
 *
 * @package    Exopite_Custom_Email_Notifications
 * @subpackage Exopite_Custom_Email_Notifications/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Exopite_Custom_Email_Notifications
 * @subpackage Exopite_Custom_Email_Notifications/admin
 * @author     Joe Szalai <joe@szalai.org>
 */
class Exopite_Custom_Email_Notifications_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Exopite_Custom_Email_Notifications_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Exopite_Custom_Email_Notifications_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/exopite-custom-email-notifications-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Exopite_Custom_Email_Notifications_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Exopite_Custom_Email_Notifications_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/exopite-custom-email-notifications-admin.js', array( 'jquery' ), $this->version, true );

	}

    public function send_wp_email( $seleted_users, $subject ) {

        // As HTML email
        $headers = array('Content-Type: text/html; charset=UTF-8');

        $email_body = apply_filters( 'exopite-custom-email-notifications-email-before-body', '' );
        $email_body .= Exopite_Template::get_template();
        $email_body .= apply_filters( 'exopite-custom-email-notifications-email-after-body', '' );

        return wp_mail( $seleted_users, $subject, $email_body, $headers );

    }

    /**
     * Send email on save post and/or page
     *
     * @param int   $post_ID    The post ID.
     * @param post  $post       The post object.
     * @param bool  $update     Whether this is an existing post being updated or not.
     *
     * @return void
     */
    public function send_notification_post_or_page( $post_id, $post, $update ) {

        // If this is just a revision, don't send the email.
        if ( wp_is_post_revision( $post_id ) ) return;

        // Get post meta
        $custom = get_post_custom( $post_id );

        // Only run if email sending is activated
        if ( ! isset( $custom['ecen-activate'][0] ) || $custom['ecen-activate'][0] !== 'yes' ) return;

        // Get seleted actions (added, updated, commented)
        $seleted_actions = maybe_unserialize( $custom['ecen-actions'][0] );

        // Check if it is a new or an updated post
        //https://wordpress.stackexchange.com/questions/120996/add-action-only-on-post-publish-not-update/261311#261311
        $is_update = ( $post->post_date != $post->post_modified );

        if ( ! is_array( $seleted_actions ) || // Action is not exist
             ( $is_update && ! in_array( 'updated', $seleted_actions ) ) || // is an update but actions do not have update
             ( ! $is_update && ! in_array( 'added', $seleted_actions ) ) // is a new one but actions do not have added
        ) return;

        $seleted_users = maybe_unserialize( $custom['ecen-users'][0] );

        // Send the emails.
        if ( apply_filters( 'exopite-custom-email-notifications-use-wp-mail', true ) ) {

            $post_type_obj = get_post_type_object( $post->post_type );

            if ( $is_update ) {
                $action = 'updated';
                $subject = sprintf( __( '"%s" has been updated. ', 'exopite-custom-email-notifications' ), $post->post_title );
            } else {
                $action = 'added';
                $subject = sprintf( __( '"%s" has been added. ', 'exopite-custom-email-notifications' ), $post->post_title );
            }

            // email-{post-type}-{action}-{locate}.html
            // eg.:
            // email-post-updated-de_DE.html
            // email-post-updated.html
            // email-updated-de_DE.html
            // email-updated.html
            // email-de_DE.html
            // email.html

            $files = array(
                'email-' . $post->post_type . '-' . $action . '-' . get_locale() . '.html',
                'email-' . $post->post_type . '-' . $action . '.html',
                'email-' . $action . '-' . get_locale() . '.html',
                'email-' . $action . '.html',
                'email-' . get_locale() . '.html',
                'email.html'
            );

            // Get template
            Exopite_Template::$variables_array['title'] = $post->post_title;
            Exopite_Template::$variables_array['url'] = get_permalink( $post_id );
            Exopite_Template::$variables_array['action'] = $action;
            Exopite_Template::$variables_array['post-type'] = $post_type_obj->labels->singular_name;
            Exopite_Template::$variables_array['content'] = apply_filters( 'the_content', $post->post_content );
            Exopite_Template::$filename = $this->get_template_name( $files );

            $this->send_wp_email( $seleted_users, $subject );

        } else {

            do_action( 'exopite-custom-email-notifications-custom-mail-engine', $seleted_users, $post );
            do_action( 'exopite-custom-email-notifications-custom-mail-engine-post-page', $seleted_users, $post );

        }

    }

    /**
     * @param int      $id Comment ID.
     * @param stdClass $comment Comment data.
     *
     * @return void
     */
    public function pre_send_notification_new_comment( $id, $comment ) {
        $this->send_notification_comment( $id, $comment->comment_approved );
    }

    /**
     * @param string   $new_status New status of comment.
     * @param string   $old_status Old status of comment.
     * @param stdClass $comment Comment data.
     *
     * @return void
     */
    public function pre_send_notification_update_comment( $new_status, $old_status, $comment ) {
        $this->send_notification_comment( $comment->comment_ID, $new_status, $old_status );
    }

    /**
     * Detect whether the comment has approved.
     *
     * @param string $new_status New status of comment.
     * @param string $old_status Optional old status of comment.
     *
     * @return boolean           Returns true if the comment has approved.
     */
    protected function has_approved( $new_status, $old_status = null ) {
        $approved = false;
        $approved_statuses = array( '1', 'approved', 'approve' );
        if ( ! in_array( $old_status, $approved_statuses, true ) && in_array( $new_status, $approved_statuses, true ) ) {
            $approved = true;
        }
        return $approved;
    }

    public function get_template_name( $files ) {

        $path = join( DIRECTORY_SEPARATOR, array( EXOPITE_CUSTOM_EMAIL_NOTIFICATIONS_PLUGIN_DIR, 'templates' ) );

        foreach ( $files as $file ) {
            if ( file_exists( $path . DIRECTORY_SEPARATOR . $file ) ) {
                return $path . DIRECTORY_SEPARATOR . $file;
            }
        }

        return null;
    }

    /**
     * Nofity users when publish a comment.
     *
     * @param int    $id Comment ID.
     * @param string $new_status New status of comment.
     * @param string $old_status Optional old status of comment.
     *
     * @return void
     */
    public function send_notification_comment( $id, $new_status, $old_status = null ) {

        $has_approved = $this->has_approved( $new_status, $old_status );
        $allowed_statuses = apply_filters( 'notify_users_email_allowed_comment_statuses', $has_approved, $new_status, $old_status );

        if ( $allowed_statuses ) {

            $comment = get_comment( $id );

            $custom = get_post_custom( $comment->comment_post_ID );

            if ( ! isset( $custom['ecen-activate'][0] ) || $custom['ecen-activate'][0] !== 'yes' ) return;

            $seleted_actions = maybe_unserialize( $custom['ecen-actions'][0] );

            if ( ! is_array( $seleted_actions ) || ( $update && ! in_array( 'updated', $seleted_actions ) ) ) return;

            $seleted_users = maybe_unserialize( $custom['ecen-users'][0] );

            // Send the emails.
            if ( apply_filters( 'notify_users_email_use_wp_mail', true ) ) {

                $title = get_the_title( $comment->comment_post_ID );
                $subject = sprintf( __( '"%s" has a new comment. ', 'exopite-custom-email-notifications' ), $title );

                $files = array(
                    'email-comment-' . get_locale() . '.html',
                    'email-comment.html'
                );

                $user = get_user_by( 'id', $comment->comment_author );
                if ( ! $user ) {
                    $user = get_user_by( 'email', $comment->comment_author_email );
                }

                // Get user name if not empty, if empty get username
                $display_name = ( strlen( $user->display_name ) > 0 ) ? $user->display_name : $user->user_login;

                // Get template
                Exopite_Template::$variables_array['title'] = $title;
                Exopite_Template::$variables_array['url'] = get_permalink( $comment->comment_post_ID ) . '#comment-' . $comment->comment_ID;
                Exopite_Template::$variables_array['comment_content'] = $comment->comment_content;
                Exopite_Template::$variables_array['user_name'] = $display_name;
                Exopite_Template::$filename = $this->get_template_name( $files );

                $this->send_wp_email( $seleted_users, $subject );

            } else {

                do_action( 'exopite-custom-email-notifications-custom-mail-engine', $seleted_users, $comment );
                do_action( 'exopite-custom-email-notifications-custom-mail-engine-comments', $seleted_users, $comment );

            }

        }

    }


}
