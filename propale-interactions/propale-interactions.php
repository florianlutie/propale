<?php
/*
Plugin Name: Propale Interactions
Description: Tracks opens, clicks, and follow-ups.
Version: 1.0.0
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Propale_Interactions {
    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        add_action( 'wp_ajax_propale_track', array( $this, 'handle_track' ) );
        add_action( 'wp_ajax_nopriv_propale_track', array( $this, 'handle_track' ) );
        add_action( 'wp_footer', array( $this, 'footer_pixel' ) );
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'wp_dashboard_setup', array( $this, 'dashboard_widget' ) );
    }

    public function activate() {
        global $wpdb;
        $table = $wpdb->prefix . 'propale_interactions';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned DEFAULT NULL,
            post_id bigint(20) unsigned DEFAULT NULL,
            type varchar(50) NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY type (type),
            KEY post_id (post_id)
        ) $charset_collate;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    public function handle_track() {
        global $wpdb;
        $type = isset( $_GET['type'] ) ? sanitize_text_field( $_GET['type'] ) : '';
        if ( ! $type ) {
            wp_die();
        }
        $post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : null;
        $table = $wpdb->prefix . 'propale_interactions';
        $wpdb->insert( $table, array(
            'user_id'    => get_current_user_id(),
            'post_id'    => $post_id,
            'type'       => $type,
            'created_at' => current_time( 'mysql' ),
        ) );
        wp_die();
    }

    public function footer_pixel() {
        if ( ! is_singular() ) {
            return;
        }
        global $post;
        $url = admin_url( 'admin-ajax.php?action=propale_track&type=open&post_id=' . $post->ID );
        echo '<img src="' . esc_url( $url ) . '" style="width:1px;height:1px;display:none" alt="" />';
        $ajax_url = admin_url( 'admin-ajax.php' );
        echo "<script>var propale_data = {ajax_url: '" . esc_js( $ajax_url ) . "', post_id:" . (int) $post->ID . "};
        document.addEventListener('click',function(e){var el=e.target.closest('[data-propale-event]');if(el){var t=el.getAttribute('data-propale-event');fetch(propale_data.ajax_url+'?action=propale_track&type='+encodeURIComponent(t)+'&post_id='+propale_data.post_id);}});</script>";
    }

    public function admin_menu() {
        add_menu_page( 'Propale Interactions', 'Propale Interactions', 'manage_options', 'propale-interactions', array( $this, 'admin_page' ) );
    }

    public function admin_page() {
        global $wpdb;
        $table = $wpdb->prefix . 'propale_interactions';
        $results = $wpdb->get_results( "SELECT type, COUNT(*) as cnt FROM $table GROUP BY type" );
        echo '<div class="wrap"><h1>Propale Interactions</h1><table class="widefat"><thead><tr><th>Type</th><th>Count</th></tr></thead><tbody>'; 
        foreach ( $results as $row ) {
            echo '<tr><td>' . esc_html( $row->type ) . '</td><td>' . intval( $row->cnt ) . '</td></tr>';
        }
        echo '</tbody></table></div>';
    }

    public function dashboard_widget() {
        wp_add_dashboard_widget( 'propale_interactions_widget', 'Propale Interactions', array( $this, 'dashboard_widget_display' ) );
    }

    public function dashboard_widget_display() {
        global $wpdb;
        $table = $wpdb->prefix . 'propale_interactions';
        $results = $wpdb->get_results( "SELECT type, COUNT(*) as cnt FROM $table GROUP BY type" );
        echo '<ul>';
        foreach ( $results as $row ) {
            echo '<li>' . esc_html( $row->type ) . ': ' . intval( $row->cnt ) . '</li>';
        }
        echo '</ul>';
    }
}
Propale_Interactions::instance();
