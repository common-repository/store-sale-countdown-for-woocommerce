<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

global $rllc_store_sale_countdown;
?>
<div class="<?php echo join( ' ', $classes ); ?>">
	<?php foreach ( $products as $product ) : ?>
		<?php $rllc_store_sale_countdown->rllc_get_template( 'loop/loop.php', array( 'product' => $product, 'options' => $options ) ) ?>
	<?php endforeach; ?>
</div>
