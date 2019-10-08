<?php global $plugin_meta, $plugin_domain; ?>
<div class="alert alert-danger">
<?php echo sprintf(
	__('Sorry, but %s requires %s to be installed and active. %s', 'bibliomundi-woocommerce'), 
	'<strong>'.$plugin_meta['Name'].'</strong>', 
	'<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">'.__('WooCommerce', 'bibliomundi-woocommerce').'</a>',
	'<br/><br/><a href="'.admin_url('plugins.php').'">&laquo; '.__('Return to Plugins', 'bibliomundi-woocommerce').'</a>'
); ?>
</div>