<?php
/**
 * Handles the Erase Tool to erase personal birth data.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Delete all birth data from orders when ZPSR Cleanup Tool is used.
 */
function zpsr_erase_tool_all_orders() {
	if ( current_user_can( 'edit_shop_orders' ) && check_admin_referer( 'zpsr-cleanup-tool', '_nonce' ) ) {

		$args = array(
			'limit' => -1,
		);
		$orders = wc_get_orders( $args );
		foreach ( $orders as $order ) {
			zpsr_delete_order_item_birthdata_meta( $order );
		}

		$url = esc_url_raw( add_query_arg( array(
			'page'	=> 'zodiacpress-tools',
			'tab'	=> 'cleanup',
			'zpsr-done'	=> 'all'
			), admin_url( 'admin.php' )
		) );
		wp_redirect( wp_sanitize_redirect( $url ) );
		exit;
	}
}
add_action( 'admin_post_zpsr_delete_all_birth_data', 'zpsr_erase_tool_all_orders' );

/**
 * Delete birth data from a single order when ZPSR Cleanup Tool is used.
 */
function zpsr_erase_tool_single_order() {
	if ( ! empty( $_POST['zpsr-cleanup-tool-single'] ) && wp_verify_nonce( $_POST['zpsr-cleanup-tool-single'], 'zpsr-cleanup-tool-single' ) ) {

		if ( current_user_can( 'edit_shop_orders' ) ) {

			// Make sure order number field is filled in

			if ( empty( $_POST['zpsr_cleanup_order_id'] ) ) {
				$url = esc_url_raw( add_query_arg( array(
					'page'	=> 'zodiacpress-tools',
					'tab'	=> 'cleanup',
					'zpsr-done'	=> 'blank'
					), admin_url( 'admin.php' )
				) );
				wp_safe_redirect( wp_sanitize_redirect( $url ) );
				exit;
			}

			$order_id = (int) $_POST['zpsr_cleanup_order_id'];

			$order = wc_get_order( $order_id );

			zpsr_delete_order_item_birthdata_meta( $order );

			$url = esc_url_raw( add_query_arg( array(
				'page'	=> 'zodiacpress-tools',
				'tab'	=> 'cleanup',
				'zpsr-done'	=> $order_id
				), admin_url( 'admin.php' )
			) );
			wp_safe_redirect( wp_sanitize_redirect( $url ) );
			exit;
		}

	}
}
add_action( 'admin_post_zpsr_delete_single_birth_data', 'zpsr_erase_tool_single_order' );

/**
 * Add tool to erase all ZP form data from all WooCommerce orders to the ZP Cleanup Tools tab
 */
function zpsr_erase_tool() {
	?>
	<div class="stuffbox">
		<div class="inside">
			<h2><?php _e( 'Erase Birth Data For WooCommerce Orders', 'zp-sell-reports' ); ?></h2>

			<?php
			_e( 'Use these tools to <strong>permanently erase</strong> all birth data from existing WooCommerce orders, including date of birth, time of birth, and place of birth. This does not erase the order itself, only the sensitive birth data. Be aware that erasing this data means that the customers for these orders will not be able to get a copy of their previous astrology reports from their account area. Erasing this data does not affect future report orders. Future reports will still be visible immediately when a customer pays. Future report orders will continue to have their birth data saved, and those reports will be available to customers when logged in to their account area.', 'zp-sell-reports' );
			?>

			<h3><?php _e( 'All Orders', 'zp-sell-reports' ); ?></h3>

			<p><a href="<?php echo esc_url( add_query_arg( array(
								'action'	=> 'zpsr_delete_all_birth_data',
								'_nonce'	=> wp_create_nonce( 'zpsr-cleanup-tool' )
								), admin_url( 'admin-post.php' ) ) ); ?>" class="button-secondary"><?php _e( 'Delete birth data from all report orders', 'zp-sell-reports' ); ?></a></p>


			<h3><?php _e( 'A Single Order', 'zp-sell-reports' ); ?></h3>

			<p><?php _e( 'To delete birth data from one single order, enter the order number, below, and click "Delete birth data."', 'zp-sell-reports' ); ?></p>

			<form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
				<p><input type="text" name="zpsr_cleanup_order_id" value="" /></p>
				<p>
					<input type="hidden" name="action" value="zpsr_delete_single_birth_data" />
					<?php wp_nonce_field( 'zpsr-cleanup-tool-single', 'zpsr-cleanup-tool-single' ); ?>
					<?php submit_button( __( 'Delete birth data', 'zp-sell-reports' ), 'secondary', 'submit', false ); ?>
				</p>
			</form>
		</div>
	</div>
	<?php
}
add_action( 'zp_tools_tab_cleanup', 'zpsr_erase_tool', 20 );

/**
 * Admin notice: Success notice for ZPSR erase tool.
 */
function zpsr_tools_admin_notice() {
	if ( ! empty( $_GET['zpsr-done'] ) ) {
		if ( is_numeric( $_GET['zpsr-done'] ) ) { // a single order
			$subject = sprintf( __( 'order #%s', 'zp-sell-reports' ), $_GET['zpsr-done'] );
		} elseif ( 'all' == $_GET['zpsr-done'] ) { // all orders
			$subject = __( 'all paid orders', 'zp-sell-reports' );
		} elseif ( 'blank' == $_GET['zpsr-done'] ) {
			printf( '<div class="notice notice-error is-dismissible"><p>%s</p></div>', __( 'You forgot to enter an order number.', 'zp-sell-reports' ) );
			return;			
		}

		if ( ! empty( $subject ) ) {

			$notice = sprintf( __( 'Birth data for %s was successfully erased.', 'zp-sell-reports' ), $subject );

			printf( '<div class="notice notice-success is-dismissible"><p>%s</p></div>', $notice );

		}
	}
}
add_action( 'admin_notices', 'zpsr_tools_admin_notice' );
