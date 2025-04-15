<?php

/**
 * Plugin Name: Before After Image Comparison - Block
 * Description: Compare and filter between two images
 * Version: 1.1.9
 * Author: bPlugins
 * Author URI: https://bplugins.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: image-compare
 */
// ABS PATH
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( function_exists( 'icb_fs' ) ) {
    icb_fs()->set_basename( false, __FILE__ );
} else {
    define( 'ICB_PLUGIN_VERSION', ( isset( $_SERVER['HTTP_HOST'] ) && 'localhost' === $_SERVER['HTTP_HOST'] ? time() : '1.1.9' ) );
    define( 'ICB_DIR_URL', plugin_dir_url( __FILE__ ) );
    define( 'ICB_DIR_PATH', plugin_dir_path( __FILE__ ) );
    define( 'ICB_HAS_FREE', 'before-after-image-compare/plugin.php' === plugin_basename( __FILE__ ) );
    define( 'ICB_HAS_PRO', 'before-after-image-compare-pro/plugin.php' === plugin_basename( __FILE__ ) );
    if ( !function_exists( 'icb_fs' ) ) {
        // Create a helper function for easy SDK access.
        function icb_fs() {
            global $icb_fs;
            if ( !isset( $icb_fs ) ) {
                $fsStartPath = dirname( __FILE__ ) . '/freemius/start.php';
                $bSDKInitPath = dirname( __FILE__ ) . '/bplugins_sdk/init.php';
                if ( ICB_HAS_PRO && file_exists( $fsStartPath ) ) {
                    require_once $fsStartPath;
                } else {
                    if ( ICB_HAS_FREE && file_exists( $bSDKInitPath ) ) {
                        require_once $bSDKInitPath;
                    }
                }
                $icbConfig = array(
                    'id'                  => '18090',
                    'slug'                => 'before-after-image-compare',
                    'premium_slug'        => 'before-after-image-compare-pro',
                    'type'                => 'plugin',
                    'public_key'          => 'pk_6a648d36975ea248f33e60908ed11',
                    'is_premium'          => true,
                    'premium_suffix'      => 'Pro',
                    'has_premium_version' => true,
                    'has_addons'          => false,
                    'has_paid_plans'      => true,
                    'trial'               => array(
                        'days'               => 7,
                        'is_require_payment' => false,
                    ),
                    'menu'                => array(
                        'slug'       => 'image-compare',
                        'first-path' => 'tools.php?page=image-compare#/dashboard',
                        'support'    => false,
                        'parent'     => array(
                            'slug' => 'tools.php',
                        ),
                    ),
                );
                $icb_fs = ( ICB_HAS_PRO && file_exists( $fsStartPath ) ? fs_dynamic_init( $icbConfig ) : fs_lite_dynamic_init( $icbConfig ) );
            }
            return $icb_fs;
        }

        // Init Freemius.
        icb_fs();
        // Signal that SDK was initiated.
        do_action( 'icb_fs_loaded' );
    }
    function icbIsPremium() {
        return icb_fs()->can_use_premium_code();
    }

    // ... Your plugin's main file logic ...
    if ( !class_exists( 'ICBPlugin' ) ) {
        class ICBPlugin {
            function __construct() {
                add_action( 'init', [$this, 'onInit'] );
                add_action( 'wp_ajax_icbPremiumChecker', [$this, 'icbPremiumChecker'] );
                add_action( 'wp_ajax_nopriv_icbPremiumChecker', [$this, 'icbPremiumChecker'] );
                add_action( 'admin_init', [$this, 'registerSettings'] );
                add_action( 'rest_api_init', [$this, 'registerSettings'] );
                // check premium
                if ( !icb_fs()->can_use_premium_code() ) {
                    add_filter(
                        'plugin_action_links',
                        [$this, 'plugin_action_links'],
                        10,
                        2
                    );
                }
            }

            function icbPremiumChecker() {
                $nonce = sanitize_text_field( $_POST['_wpnonce'] ?? null );
                if ( !wp_verify_nonce( $nonce, 'wp_ajax' ) ) {
                    wp_send_json_error( 'Invalid Request' );
                }
                wp_send_json_success( [
                    'isPipe' => icbIsPremium(),
                ] );
            }

            function registerSettings() {
                register_setting( 'icbUtils', 'icbUtils', [
                    'show_in_rest'      => [
                        'name'   => 'icbUtils',
                        'schema' => [
                            'type' => 'string',
                        ],
                    ],
                    'type'              => 'string',
                    'default'           => wp_json_encode( [
                        'nonce' => wp_create_nonce( 'wp_ajax' ),
                    ] ),
                    'sanitize_callback' => 'sanitize_text_field',
                ] );
            }

            public function plugin_action_links( $links, $file ) {
                if ( plugin_basename( __FILE__ ) == $file ) {
                    $links['go_pro'] = sprintf(
                        '<a href="%s" style="%s" target="__blank">%s</a>',
                        'https://checkout.freemius.com/plugin/18090/plan/30020/?sandbox=true',
                        'color:#4527a4;font-weight:bold',
                        __( 'Go Pro!', 'image-compare' )
                    );
                }
                return $links;
            }

            function onInit() {
                register_block_type( __DIR__ . '/build' );
            }

        }

        new ICBPlugin();
    }
    require_once "includes/ImageCompare.php";
}