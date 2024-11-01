<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

if ( ! $enabled ) { return; }

if ( $item['current_time'] < $item['woosale_start'] ) : ?>

	<h3><?php echo esc_html( get_option( 'rllc_ob_title_coming', 'Coming' ) ) ?></h3>

<?php else: ?>
	<h3><?php echo esc_html( get_option( 'rllc_ob_title_sale', 'Sale' ) ) ?></h3>
<?php endif; ?>