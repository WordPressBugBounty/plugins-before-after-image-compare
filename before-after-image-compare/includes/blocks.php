<?php

if (!class_exists('bPlDashboardBlocks')){
    class bPlDashboardBlocks{
        function __construct(){
            add_action('wp_ajax_bPlBlocksDisabled',[$this,'bPlBlocksDisabled']);
        }
        public function bPlBlocksDisabled(){
			$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) ?? null;

			if( !wp_verify_nonce( $nonce, 'wp_ajax' )){
				wp_send_json_error( 'Invalid Request' );
			}

			$data = json_decode( stripslashes( $_POST['data'] ), true );
			$db_data = get_option( 'bPlBlocksDisabled', [] );

			if( !isset( $data ) && $db_data ){
				wp_send_json_success( $db_data );
			}

			update_option( 'bPlBlocksDisabled', $data );
			wp_send_json_success( $data );
		
		}
    }

    new bPlDashboardBlocks();
}