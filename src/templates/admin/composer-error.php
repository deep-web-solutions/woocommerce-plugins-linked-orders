<?php
/**
 * A very early error message displayed if something doesn't check out.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\WC-Plugins\LinkedOrders\templates\admin
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="error notice dws-plugin-corrupted-error">
	<p>
		<?php
		echo wp_kses(
			wp_sprintf(
				/* translators: %s: Plugin Name */
				__( 'It seems like <strong>%s</strong> is corrupted. Please reinstall!', 'linked-orders-for-woocommerce' ),
				dws_lowc_name()
			),
			array(
				'strong' => array(),
			)
		);
		?>
	</p>
</div>
