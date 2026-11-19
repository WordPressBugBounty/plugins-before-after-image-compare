<?php

namespace ImageCompare\Admin;

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( __NAMESPACE__ . '\ImageCompare_PromoBanner' ) ) {

    class ImageCompare_PromoBanner {
        const DISMISSED_KEY   = 'icb_promo_banner_dismissed_permanent';
        const EXPIRATION_DATE = '2026-12-05 23:59:59';

        /** Target landing URL */
        const COUPON_URL      = 'https://bplugins.com/products/before-after-image-compare/pricing/?utm_source=admin&utm_medium=dashboard&utm_campaign=bfcm2026';

        /** Flag to prevent duplicate rendering */
        private $rendered = false;

        public function __construct() {
            add_action( 'admin_notices',                     [ $this, 'render' ], 5 );
            add_action( 'all_admin_notices',                 [ $this, 'render' ], 5 );
            add_action( 'admin_head',                        [ $this, 'enqueue_styles' ] );
            add_action( 'admin_footer',                      [ $this, 'enqueue_scripts' ] );
            add_action( 'wp_ajax_icb_dismiss_promo_banner',  [ $this, 'ajax_dismiss' ] );
        }

        /* ------------------------------------------------------------------ */
        /*  Screen, Expiration, Dismissal & Pro Helpers                       */
        /* ------------------------------------------------------------------ */

        /**
         * Check if the current admin screen belongs strictly to post_type=image-compare
         */
        private function is_plugin_screen(): bool {
            global $typenow;

            // 1. Direct post_type matching via WordPress global
            if ( 'image-compare' === $typenow ) {
                return true;
            }

            // 2. Screen object post_type checking
            $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
            if ( $screen ) {
                if ( isset( $screen->post_type ) && 'image-compare' === $screen->post_type ) {
                    return true;
                }
            }

            // 3. Query string post_type (edit.php?post_type=image-compare, post-new.php?post_type=image-compare)
            if ( isset( $_GET['post_type'] ) && 'image-compare' === $_GET['post_type'] ) {
                return true;
            }

            // 4. Edit post screen (post.php?post=123&action=edit)
            if ( isset( $_GET['post'] ) ) {
                $post_id = absint( $_GET['post'] );
                if ( $post_id && 'image-compare' === get_post_type( $post_id ) ) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Check if current date is past December 5, 2026
         */
        private function is_expired(): bool {
            $expiry_timestamp = strtotime( self::EXPIRATION_DATE );
            $current_time     = time();
            return ( $current_time > $expiry_timestamp );
        }

        /**
         * Check if current user has permanently dismissed the banner
         */
        private function is_dismissed(): bool {
            return (bool) get_user_meta( get_current_user_id(), self::DISMISSED_KEY, true );
        }

        /**
         * Check if the plugin has active Premium / Pro features
         * Returns true if user is Premium (banner will NOT show)
         * Returns false if user is Free (banner WILL show)
         */
        private function is_premium(): bool {
            // 1. Check plugin's own checker function
            if ( function_exists( 'icbImageCompareChecker' ) && icbImageCompareChecker() ) {
                return true;
            }

            // 2. Check Freemius SDK (works safely on both BPluginsFSLite and Full Freemius)
            if ( function_exists( 'icb_fs' ) ) {
                $fs = icb_fs();
                if ( is_object( $fs ) ) {
                    if ( method_exists( $fs, 'can_use_premium_code' ) && $fs->can_use_premium_code() ) {
                        return true;
                    }
                    if ( method_exists( $fs, 'is_premium' ) && $fs->is_premium() ) {
                        return true;
                    }
                }
            }

            // 3. Check plugin pro constant
            if ( defined( 'BAICB_IS_PREMIUM' ) && BAICB_IS_PREMIUM ) {
                return true;
            }

            return false;
        }

        /**
         * Determine if the banner should be omitted
         */
        private function should_skip(): bool {
            // 1. Only show inside post_type=image-compare screens
            if ( ! $this->is_plugin_screen() ) {
                return true;
            }

            // 2. Automatically remove after Dec 5, 2026
            if ( $this->is_expired() ) {
                return true;
            }

            // 3. Never display again once dismissed by the user (bypass with ?show_promo=1 for testing)
            if ( ! isset( $_GET['show_promo'] ) && $this->is_dismissed() ) {
                return true;
            }

            // 4. If user is Premium -> do NOT show; If NOT Premium -> SHOW
            if ( $this->is_premium() ) {
                return true;
            }

            return false;
        }

        /* ------------------------------------------------------------------ */
        /*  Pro CSS Styles (Design 5)                                         */
        /* ------------------------------------------------------------------ */

        public function enqueue_styles(): void {
            if ( $this->should_skip() ) { return; }
            ?>
            <style id="icb-promo-banner-css">
            /* Specificity override to prevent theme/dashboard/Gutenberg suppression */
            body #wpbody-content #icb-promo-banner,
            body .bPlDashboard #icb-promo-banner,
            body.block-editor-page #wpbody-content #icb-promo-banner,
            #icb-promo-banner {
                --bpl-primary:        var(--bpl-dashboard-primary-color, #6155f5);
                --bpl-primary-hover:  #3425f2;
                --bpl-dark:           var(--bpl-dashboard-title-color, #001737);
                --bpl-text:           var(--bpl-dashboard-content-color, #1b2e4b);
                --bpl-muted:          rgba(27, 46, 75, 0.72);
                --bpl-border:         #eef0f5;

                background: #ffffff !important;
                border: 1px solid var(--bpl-border) !important;
                border-radius: 8px !important;
                box-shadow: 0 2px 10px rgba(9, 30, 66, 0.05) !important;
                display: flex !important;
                align-items: stretch !important;
                min-height: 84px !important;
                height: auto !important;
                width: auto !important;
                max-width: 100% !important;
                overflow: hidden !important;
                position: relative !important;
                left: auto !important;
                top: auto !important;
                visibility: visible !important;
                opacity: 1 !important;
                margin: 16px 20px 16px 0 !important;
                padding: 0 !important;
                font-family: Roboto, -apple-system, BlinkMacSystemFont, "Segoe UI", Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif !important;
                box-sizing: border-box !important;
                animation: icbBannerSlide .3s ease both !important;
               
            }
            @keyframes icbBannerSlide {
                from { opacity: 0; transform: translateY(-6px); }
                to   { opacity: 1; transform: translateY(0); }
            }

            /* Block editor specific positioning */
            body.block-editor-page #wpbody-content #icb-promo-banner {
                margin: 10px 15px !important;
            }

            /* Left Brand Ribbon Block */
            #icb-promo-banner .icb-brand-ribbon {
                width: 140px;
                min-width: 140px;
                background: linear-gradient(135deg, #091e42 0%, var(--bpl-primary) 100%);
                color: #ffffff;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 12px 10px;
                gap: 6px;
                flex-shrink: 0;
            }
            #icb-promo-banner .icb-brand-ribbon img {
                width: 38px;
                height: 38px;
                object-fit: contain;
                filter: drop-shadow(0 2px 5px rgba(0, 0, 0, 0.25));
                border-radius: 4px;
            }
            #icb-promo-banner .icb-brand-ribbon .icb-ribbon-title {
                font-size: 10.5px;
                font-weight: 800;
                letter-spacing: .7px;
                text-transform: uppercase;
                color: #ffffff;
                text-align: center;
                line-height: 1.2;
            }

            /* Main Content Zone */
            #icb-promo-banner .icb-content-zone {
                flex: 1;
                padding: 14px 22px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 20px;
                flex-wrap: wrap;
                padding-right: 38px;
            }

            /* Left Text Stack */
            #icb-promo-banner .icb-text-stack {
                display: flex;
                flex-direction: column;
                gap: 6px;
            }
            #icb-promo-banner .icb-headline {
                font-size: 15px;
                font-weight: 700;
                color: var(--bpl-dark);
                display: flex;
                align-items: center;
                gap: 8px;
                margin: 0;
                line-height: 1.3;
            }
            #icb-promo-banner .icb-discount-chip {
                background: rgba(97, 85, 245, 0.08);
                color: var(--bpl-primary);
                border: 1px solid rgba(97, 85, 245, 0.22);
                font-size: 11px;
                font-weight: 800;
                padding: 2.5px 9px;
                border-radius: 12px;
                letter-spacing: .3px;
                text-transform: uppercase;
                white-space: nowrap;
            }
            #icb-promo-banner .icb-description {
                font-size: 13px;
                color: var(--bpl-text);
                line-height: 1.5;
                margin: 4px 0 8px 0;
                max-width: 780px;
                opacity: 0.9;
            }

            /* Feature Checklist */
            #icb-promo-banner .icb-feature-chips {
                display: flex;
                align-items: center;
                gap: 12px;
                font-size: 12px;
                color: var(--bpl-muted);
                flex-wrap: wrap;
            }
            #icb-promo-banner .icb-feature-chips span {
                display: inline-flex;
                align-items: center;
                gap: 4px;
            }
            #icb-promo-banner .icb-feature-chips .check-icon {
                color: #16a34a;
                font-weight: 800;
            }

            /* CTA Action Button */
            #icb-promo-banner .icb-btn-cta {
                background: var(--bpl-primary);
                color: #ffffff !important;
                padding: 10px 20px;
                border-radius: 6px;
                font-size: 12.5px;
                font-weight: 600;
                text-decoration: none !important;
                box-shadow: 0 3px 10px rgba(97, 85, 245, 0.28);
                transition: background-color .15s ease, transform .15s ease, box-shadow .15s ease;
                white-space: nowrap;
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }
            #icb-promo-banner .icb-btn-cta:hover {
                background: var(--bpl-primary-hover);
                transform: translateY(-1px);
                box-shadow: 0 5px 16px rgba(97, 85, 245, 0.4);
            }

            /* Dismiss Button (×) */
            #icb-promo-banner .icb-banner-dismiss-btn {
                position: absolute;
                top: 8px;
                right: 8px;
                background: none;
                border: none;
                cursor: pointer;
                color: rgba(27, 46, 75, 0.4);
                font-size: 20px;
                line-height: 1;
                padding: 6px;
                border-radius: 4px;
                transition: color .15s ease, background-color .15s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10;
            }
            #icb-promo-banner .icb-banner-dismiss-btn:hover {
                color: var(--bpl-dark);
                background-color: rgba(27, 46, 75, 0.06);
            }

            /* Responsive */
            @media (max-width: 960px) {
                #icb-promo-banner .icb-brand-ribbon {
                    width: 110px;
                    min-width: 110px;
                }
            }
            @media (max-width: 782px) {
                body #wpbody-content #icb-promo-banner,
                #icb-promo-banner {
                    flex-direction: column !important;
                    align-items: stretch !important;
                }
                #icb-promo-banner .icb-brand-ribbon {
                    width: 100%;
                    flex-direction: row;
                    justify-content: flex-start;
                    padding: 8px 16px;
                }
                #icb-promo-banner .icb-content-zone {
                    flex-direction: column;
                    align-items: flex-start;
                    padding: 14px 16px;
                }
            }
            </style>
            <?php
        }

        /* ------------------------------------------------------------------ */
        /*  Permanent Dismiss Script (AJAX + Animation)                       */
        /* ------------------------------------------------------------------ */

        public function enqueue_scripts(): void {
            if ( $this->should_skip() ) { return; }
            $dismiss_nonce = wp_create_nonce( 'icb_dismiss_promo' );
            $ajax_url      = admin_url( 'admin-ajax.php' );
            ?>
            <script id="icb-promo-banner-js">
            (function () {
                var btn = document.getElementById('icb-banner-close');
                if (!btn) return;
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var banner = document.getElementById('icb-promo-banner');
                    if (banner) {
                        banner.style.transition = 'opacity .25s ease, transform .25s ease';
                        banner.style.opacity    = '0';
                        banner.style.transform  = 'translateY(-6px)';
                        setTimeout(function() { banner.remove(); }, 260);
                    }
                    var fd = new FormData();
                    fd.append('action', 'icb_dismiss_promo_banner');
                    fd.append('nonce',  '<?php echo esc_js( $dismiss_nonce ); ?>');
                    fetch('<?php echo esc_url( $ajax_url ); ?>', {
                        method: 'POST',
                        credentials: 'same-origin',
                        body: fd
                    });
                });
            })();
            </script>
            <?php
        }

        /* ------------------------------------------------------------------ */
        /*  Render Single Banner (Design 5)                                   */
        /* ------------------------------------------------------------------ */

        public function render(): void {
            if ( $this->rendered ) { return; }
            if ( $this->should_skip() ) { return; }
            $this->rendered = true;

            $coupon_url = esc_url( self::COUPON_URL );
            $icon_url   = esc_url('https://ps.w.org/before-after-image-compare/assets/icon.svg?rev=3623241');
            ?>
            <div id="icb-promo-banner" class="icb-promo-banner">

                <!-- Left Asymmetric Brand Ribbon -->
                <div class="icb-brand-ribbon">
                    <img src="<?php echo $icon_url; ?>" alt="Before After Comparison" />
                    <span class="icb-ribbon-title">Before After</span>
                </div>

                <!-- Main Content Zone -->
                <div class="icb-content-zone">
                    <div class="icb-text-stack">
                        <div class="icb-headline">
                            <span><?php esc_html_e( 'Before After Comparison Biggest Black Friday Sale is Live', 'image-compare' ); ?></span>
                            <span class="icb-discount-chip"><?php esc_html_e( 'Flat 45% OFF!', 'image-compare' ); ?></span>
                        </div>
                        <p class="icb-description">
                            <?php esc_html_e( 'Upgrade to Pro today at an exclusive Black Friday price and unlock video comparisons, auto-sliding, and premium controls.', 'image-compare' ); ?>
                        </p>
                        <div class="icb-feature-chips">
                            <span><strong class="check-icon">✓</strong> <?php esc_html_e( 'Video Comparison', 'image-compare' ); ?></span>
                            <span><strong class="check-icon">✓</strong> <?php esc_html_e( 'Multi Comparison Slider', 'image-compare' ); ?></span>
                            <span><strong class="check-icon">✓</strong> <?php esc_html_e( 'Auto Slide & Speed', 'image-compare' ); ?></span>
                            <span><strong class="check-icon">✓</strong> <?php esc_html_e( 'Move on Hover & Click', 'image-compare' ); ?></span>
                            <span><strong class="check-icon">✓</strong> <?php esc_html_e( 'Custom Handle & Styling', 'image-compare' ); ?></span>
                        </div>
                    </div>

                    <!-- CTA Button -->
                    <a href="<?php echo $coupon_url; ?>" target="_blank" rel="noopener noreferrer" class="icb-btn-cta">
                        <?php esc_html_e( 'Unlock PRO Features →', 'image-compare' ); ?>
                    </a>
                </div>

                <!-- Dismiss Button -->
                <button id="icb-banner-close"
                        class="icb-banner-dismiss-btn"
                        title="<?php esc_attr_e( 'Dismiss', 'image-compare' ); ?>"
                        aria-label="<?php esc_attr_e( 'Dismiss banner', 'image-compare' ); ?>">
                    &times;
                </button>

            </div>
            <?php
        }

        /* ------------------------------------------------------------------ */
        /*  AJAX Handler: Permanent Dismissal                                 */
        /* ------------------------------------------------------------------ */

        public function ajax_dismiss(): void {
            check_ajax_referer( 'icb_dismiss_promo', 'nonce' );
            update_user_meta( get_current_user_id(), self::DISMISSED_KEY, 1 );
            wp_send_json_success();
        }
    }

    new ImageCompare_PromoBanner();
}