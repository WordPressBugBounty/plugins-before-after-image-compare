<?php
$id = wp_unique_id( 'icbImageCompare-' );
?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?> id='<?php echo esc_attr( $id ); ?>' data-attributes='<?php echo esc_attr( wp_json_encode( icbSanitizeAttributes( $attributes ) ) ); ?>' data-icbchecker='<?php echo esc_attr( wp_json_encode( icbImageCompareChecker() ) ); ?>'></div>