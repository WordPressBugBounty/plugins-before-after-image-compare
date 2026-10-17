<?php

/**
 * Plugin Name: Before After Image Comparison - Block
 * Description: Compare and filter between two images
 * Version: 1.1.22
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
    define( 'BAICB_PLUGIN_VERSION', '1.1.22' );
    // define( 'BAICB_PLUGIN_VERSION',  isset( $_SERVER['HTTP_HOST'] ) && ( 'localhost' === $_SERVER['HTTP_HOST'] || 'murdwahid.local' === $_SERVER['HTTP_HOST'] ) ? time() : '1.1.15' );
    define( 'BAICB_DIR_URL', plugin_dir_url( __FILE__ ) );
    define( 'BAICB_DIR_PATH', plugin_dir_path( __FILE__ ) );
    define( 'BAICB_HAS_FREE', 'before-after-image-compare/plugin.php' === plugin_basename( __FILE__ ) );
    define( 'BAICB_HAS_PRO', file_exists( dirname( __FILE__ ) . '/vendor/freemius/start.php' ) );
    // define('BAICB_HAS_PRO', BAICB_DIR_PATH .'/vendor/freemius/start.php');
    if ( !function_exists( 'icb_fs' ) ) {
        // Create a helper function for easy SDK access.
        function icb_fs() {
            global $icb_fs;
            if ( !isset( $icb_fs ) ) {
                $fsStartPath = dirname( __FILE__ ) . '/vendor/freemius/start.php';
                $bSDKInitPath = dirname( __FILE__ ) . '/vendor/freemius-lite/start.php';
                if ( BAICB_HAS_PRO && file_exists( $fsStartPath ) ) {
                    require_once BAICB_DIR_PATH . '/includes/LicenseActivation.php';
                }
                if ( BAICB_HAS_PRO && file_exists( $fsStartPath ) ) {
                    require_once $fsStartPath;
                } else {
                    if ( BAICB_HAS_FREE && file_exists( $bSDKInitPath ) ) {
                        require_once $bSDKInitPath;
                    }
                }
                $icbConfig = array(
                    'id'                  => '18090',
                    'slug'                => 'before-after-image-compare',
                    'premium_slug'        => 'before-after-image-compare-pro',
                    'type'                => 'plugin',
                    'public_key'          => 'pk_6a648d36975ea248f33e60908ed11',
                    'is_premium'          => false,
                    'premium_suffix'      => 'Pro',
                    'has_premium_version' => true,
                    'has_addons'          => false,
                    'has_paid_plans'      => true,
                    'trial'               => array(
                        'days'               => 7,
                        'is_require_payment' => false,
                    ),
                    'menu'                => array(
                        'slug'       => 'edit.php?post_type=image-compare',
                        'first-path' => 'edit.php?post_type=image-compare&page=image-compare-help',
                        'support'    => false,
                        'parent'     => array(
                            'slug' => 'edit.php?post_type=image-compare',
                        ),
                    ),
                );
                $icb_fs = ( BAICB_HAS_PRO && file_exists( $fsStartPath ) ? fs_dynamic_init( $icbConfig ) : fs_lite_dynamic_init( $icbConfig ) );
            }
            return $icb_fs;
        }

        // Init Freemius.
        icb_fs();
        // Signal that SDK was initiated.
        do_action( 'icb_fs_loaded' );
    }
    function icbImageCompareChecker() {
        return icb_fs()->can_use_premium_code();
    }

    // Sanitize block attribute values before they are printed for the frontend script. Attribute keys and structure are left untouched.
    function icbSanitizeAttributes(  $value, $key = ''  ) {
        if ( is_array( $value ) ) {
            foreach ( $value as $k => $v ) {
                $value[$k] = icbSanitizeAttributes( $v, $k );
            }
            return $value;
        }
        if ( !is_string( $value ) ) {
            return $value;
        }
        if ( in_array( $key, [
            'beforeLabel',
            'afterLabel',
            'cap',
            'caption'
        ], true ) ) {
            return wp_kses_post( $value );
        }
        if ( 'url' === $key ) {
            return esc_url_raw( $value );
        }
        return $value;
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
                add_action(
                    'default_title',
                    [$this, 'defaultTitle'],
                    10,
                    2
                );
                add_action(
                    'default_content',
                    [$this, 'defaultContent'],
                    10,
                    2
                );
                add_action( 'admin_init', [$this, 'guardOptInAjax'], 1 );
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

            // Prefill (?title= / ?content=) is only honoured for a page editor holding a nonce for 'icb_prefill_page', passed as _icbnonce.
            function isPrefillRequest( $post ) {
                if ( 'page' !== $post->post_type || !current_user_can( 'edit_pages' ) ) {
                    return false;
                }
                $nonce = ( isset( $_GET['_icbnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_icbnonce'] ) ) : '' );
                return (bool) wp_verify_nonce( $nonce, 'icb_prefill_page' );
            }

            function defaultTitle( $title, $post ) {
                if ( isset( $_GET['title'] ) && $this->isPrefillRequest( $post ) ) {
                    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce is verified in isPrefillRequest().
                    return sanitize_text_field( wp_unslash( $_GET['title'] ) );
                    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce is verified in isPrefillRequest().
                }
                return $title;
            }

            function defaultContent( $content, $post ) {
                if ( isset( $_GET['content'] ) && $this->isPrefillRequest( $post ) ) {
                    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce is verified in isPrefillRequest().
                    return wp_kses_post( wp_unslash( $_GET['content'] ) );
                    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce is verified in isPrefillRequest().
                }
                return $content;
            }

            // The opt-in SDK (vendor/freemius-lite) is shared by bPlugins plugins and the first copy loaded handles every plugin's
            // requests. Enforce its checks here too, so an outdated copy bundled by another plugin can't be reached without them.
            function guardOptInAjax() {
                if ( !wp_doing_ajax() ) {
                    return;
                }
                $action = ( isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '' );
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only selects which check to run.
                if ( 'fs_init' !== $action && !preg_match( '/^(bsdk_fetch_info_\\d+|fs_notice_dismiss_[a-z0-9_-]+)$/', $action ) ) {
                    return;
                }
                if ( !current_user_can( 'manage_options' ) ) {
                    wp_send_json_error( 'Forbidden', 403 );
                }
                $nonce = ( isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '' );
                if ( !wp_verify_nonce( $nonce, 'wp_ajax' ) ) {
                    wp_send_json_error( 'Invalid Request', 403 );
                }
                // Activation data must come with the {slug}_activate_new nonce that the opt-in return URL carries.
                $info = ( isset( $_POST['info'] ) && is_array( $_POST['info'] ) ? map_deep( wp_unslash( $_POST['info'] ), 'sanitize_text_field' ) : [] );
                if ( 'fs_init' === $action && (!empty( $info['user_id'] ) || isset( $info['pending_activation'] )) ) {
                    $fs_action = $info['fs_action'] ?? '';
                    if ( !preg_match( '/^[a-z0-9-]+_activate_new$/', $fs_action ) || !wp_verify_nonce( $info['_wpnonce'] ?? '', $fs_action ) ) {
                        wp_send_json_error( 'Invalid Request', 403 );
                    }
                }
            }

            // Read-only: returns only whether premium code is active, which the frontend already exposes.
            function icbPremiumChecker() {
                $nonce = ( isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '' );
                if ( !wp_verify_nonce( $nonce, 'wp_ajax' ) ) {
                    wp_send_json_error( 'Invalid Request', 403 );
                }
                wp_send_json_success( [
                    'isPipe' => icbImageCompareChecker(),
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
                        '<a href="%s" style="%s" target="">%s</a>',
                        esc_url( site_url( "/wp-admin/edit.php?post_type=image-compare&page=image-compare-help#/pricing" ) ),
                        'color:#4527a4;font-weight:bold',
                        esc_html__( 'Go Pro!', 'image-compare' )
                    );
                }
                return $links;
            }

            public function icbChecker() {
                wp_add_inline_script( 'icb-image-compare-editor-script', "const icbImageCompareChecker=" . wp_json_encode( icbImageCompareChecker() ), 'before' );
            }

            function onInit() {
                register_block_type( __DIR__ . '/build' );
                $this->icbChecker();
            }

        }

        new ICBPlugin();
    }
    require_once __DIR__ . "/includes/CPT.php";
}