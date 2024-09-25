<?php
if ( !defined( 'ABSPATH' ) ) { exit; }

if( !class_exists('ICBBlock') ){
	class ICBBlock{
		function __construct(){
			add_action( 'init', [$this, 'onInit'] );
		}
	
		function onInit() {
			wp_register_style( 'icb-image-compare-style', ICB_DIR_URL . 'dist/style.css', [], ICB_PLUGIN_VERSION ); // Style
			wp_register_style( 'icb-image-compare-editor-style', ICB_DIR_URL . 'dist/editor.css', [ 'icb-image-compare-style' ], ICB_PLUGIN_VERSION ); // Backend Style
	
			register_block_type( __DIR__, [
				'editor_style'		=> 'icb-image-compare-editor-style',
				'render_callback'	=> [$this, 'render']
			] ); // Register Block
	
			wp_set_script_translations( 'icb-image-compare-editor-script', 'image-compare', ICB_DIR_PATH . 'languages' );
		}
	
		function render( $attributes ){
			extract( $attributes );
	
			wp_enqueue_style( 'icb-image-compare-style' );
			wp_enqueue_script( 'icb-image-compare-script', ICB_DIR_URL . 'dist/script.js', [ 'react', 'react-dom', 'jquery' ], true );
			wp_set_script_translations( 'icb-image-compare-script', 'image-compare', ICB_DIR_PATH . 'languages' );
	
			$className = $className ?? '';
			$blockClassName = "wp-block-icb-image-compare $className align$align";
	
			ob_start(); ?>
			<div class='<?php echo esc_attr( $blockClassName ); ?>' id='icbImageCompare-<?php echo esc_attr( $cId ); ?>' data-attributes='<?php echo esc_attr( wp_json_encode( $attributes ) ); ?>'></div>
	
			<?php return ob_get_clean();
		} // Render
	}
	new ICBBlock;
}