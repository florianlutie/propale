<?php
/*
Plugin Name: Propale Manager
Description: Gestion des propositions commerciales pour l'agence.
Version: 0.1
Author: propale team
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Propale_Manager {
    public function __construct() {
        add_action( 'init', array( $this, 'register_post_type' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post_propale', array( $this, 'save_meta' ) );
    }

    public function register_post_type() {
        register_post_type( 'propale', array(
            'labels' => array(
                'name' => 'Propositions',
                'singular_name' => 'Proposition'
            ),
            'public' => false,
            'show_ui' => true,
            'supports' => array( 'title', 'editor' ),
            'capability_type' => 'post',
            'rewrite' => false,
        ) );
    }

    public function add_meta_boxes() {
        add_meta_box( 'propale_details', 'Détails Propale', array( $this, 'render_meta_box' ), 'propale', 'normal', 'default' );
    }

    public function render_meta_box( $post ) {
        $video = get_post_meta( $post->ID, '_propale_video', true );
        $status = get_post_meta( $post->ID, '_propale_status', true );
        $version = get_post_meta( $post->ID, '_propale_version', true );
        ?>
        <p>
            <label for="propale_video">URL vidéo</label><br />
            <input type="text" name="propale_video" id="propale_video" value="<?php echo esc_attr( $video ); ?>" class="widefat" />
        </p>
        <p>
            <label for="propale_status">Statut</label><br />
            <select name="propale_status" id="propale_status" class="widefat">
                <option value="draft" <?php selected( $status, 'draft' ); ?>>Brouillon</option>
                <option value="sent" <?php selected( $status, 'sent' ); ?>>Envoyée</option>
                <option value="accepted" <?php selected( $status, 'accepted' ); ?>>Acceptée</option>
            </select>
        </p>
        <p>
            <label for="propale_version">Version</label><br />
            <input type="text" name="propale_version" id="propale_version" value="<?php echo esc_attr( $version ); ?>" />
        </p>
        <?php
    }

    public function save_meta( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( isset( $_POST['propale_video'] ) ) {
            update_post_meta( $post_id, '_propale_video', sanitize_text_field( $_POST['propale_video'] ) );
        }
        if ( isset( $_POST['propale_status'] ) ) {
            update_post_meta( $post_id, '_propale_status', sanitize_text_field( $_POST['propale_status'] ) );
        }
        if ( isset( $_POST['propale_version'] ) ) {
            update_post_meta( $post_id, '_propale_version', sanitize_text_field( $_POST['propale_version'] ) );
        }
    }
}

new Propale_Manager();
