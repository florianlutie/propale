<?php
/**
 * Plugin Name: Propale
 * Description: Manage proposals and clients.
 * Version: 1.0.0
 * Author: Example
 */

if ( ! class_exists( 'PropalePlugin' ) ) {
    class PropalePlugin {
        public function __construct() {
            add_action( 'init', array( $this, 'register_post_types' ) );
            add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
            add_action( 'save_post', array( $this, 'save_post' ) );
        }

        public function register_post_types() {
            // Register Client post type
            register_post_type( 'client', array(
                'labels' => array(
                    'name' => __( 'Clients', 'propale' ),
                    'singular_name' => __( 'Client', 'propale' )
                ),
                'public' => true,
                'has_archive' => true,
                'show_in_rest' => true,
                'supports' => array( 'title', 'editor' )
            ) );

            // Register Propale (proposal) post type
            register_post_type( 'propale', array(
                'labels' => array(
                    'name' => __( 'Proposals', 'propale' ),
                    'singular_name' => __( 'Proposal', 'propale' )
                ),
                'public' => true,
                'has_archive' => true,
                'show_in_rest' => true,
                'supports' => array( 'title', 'editor' )
            ) );
        }

        public function add_meta_boxes() {
            add_meta_box( 'client_info', __( 'Client Information', 'propale' ), array( $this, 'render_client_meta_box' ), 'client', 'normal', 'default' );
            add_meta_box( 'propale_client', __( 'Related Client', 'propale' ), array( $this, 'render_propale_client_box' ), 'propale', 'side', 'default' );
        }

        public function render_client_meta_box( $post ) {
            wp_nonce_field( 'save_client_meta', 'client_meta_nonce' );
            $name  = get_post_meta( $post->ID, '_client_name', true );
            $email = get_post_meta( $post->ID, '_client_email', true );
            $phone = get_post_meta( $post->ID, '_client_phone', true );
            $ltv   = get_post_meta( $post->ID, '_client_ltv', true );
            ?>
            <p>
                <label for="client_name"><?php _e( 'Name', 'propale' ); ?></label><br />
                <input type="text" name="client_name" id="client_name" class="regular-text" value="<?php echo esc_attr( $name ); ?>" />
            </p>
            <p>
                <label for="client_email"><?php _e( 'Email', 'propale' ); ?></label><br />
                <input type="email" name="client_email" id="client_email" class="regular-text" value="<?php echo esc_attr( $email ); ?>" />
            </p>
            <p>
                <label for="client_phone"><?php _e( 'Phone', 'propale' ); ?></label><br />
                <input type="text" name="client_phone" id="client_phone" class="regular-text" value="<?php echo esc_attr( $phone ); ?>" />
            </p>
            <p>
                <label for="client_ltv"><?php _e( 'LTV', 'propale' ); ?></label><br />
                <input type="number" step="0.01" name="client_ltv" id="client_ltv" class="regular-text" value="<?php echo esc_attr( $ltv ); ?>" />
            </p>
            <?php
        }

        public function render_propale_client_box( $post ) {
            wp_nonce_field( 'save_propale_client', 'propale_client_nonce' );
            $selected_client = get_post_meta( $post->ID, '_propale_client_id', true );
            $clients = get_posts( array( 'post_type' => 'client', 'numberposts' => -1 ) );
            ?>
            <p>
                <label for="propale_client_id"><?php _e( 'Client', 'propale' ); ?></label>
                <select name="propale_client_id" id="propale_client_id">
                    <option value=""><?php _e( 'Select a client', 'propale' ); ?></option>
                    <?php foreach ( $clients as $client ) : ?>
                        <option value="<?php echo esc_attr( $client->ID ); ?>" <?php selected( $selected_client, $client->ID ); ?>><?php echo esc_html( get_the_title( $client->ID ) ); ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <?php
        }

        public function save_post( $post_id ) {
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return;
            }

            if ( isset( $_POST['client_meta_nonce'] ) && wp_verify_nonce( $_POST['client_meta_nonce'], 'save_client_meta' ) ) {
                if ( isset( $_POST['client_name'] ) ) {
                    update_post_meta( $post_id, '_client_name', sanitize_text_field( $_POST['client_name'] ) );
                }
                if ( isset( $_POST['client_email'] ) ) {
                    update_post_meta( $post_id, '_client_email', sanitize_email( $_POST['client_email'] ) );
                }
                if ( isset( $_POST['client_phone'] ) ) {
                    update_post_meta( $post_id, '_client_phone', sanitize_text_field( $_POST['client_phone'] ) );
                }
                if ( isset( $_POST['client_ltv'] ) ) {
                    update_post_meta( $post_id, '_client_ltv', floatval( $_POST['client_ltv'] ) );
                }
            }

            if ( isset( $_POST['propale_client_nonce'] ) && wp_verify_nonce( $_POST['propale_client_nonce'], 'save_propale_client' ) ) {
                if ( isset( $_POST['propale_client_id'] ) ) {
                    update_post_meta( $post_id, '_propale_client_id', intval( $_POST['propale_client_id'] ) );
                }
            }
        }
    }

    new PropalePlugin();
}
