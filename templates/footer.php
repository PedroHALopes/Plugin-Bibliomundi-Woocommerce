<?php global $plugin_domain; ?>
	</div>
</div>

<script>
var bibliomundi_page = '<?php echo admin_url('admin.php?page='.$plugin_domain); ?>';
var bibliomundi_error = {
	'input_api_error': '<?php echo __('Client ID or Client Key is wrong.', 'bibliomundi-woocommerce'); ?>',
	'loading': '<?php echo __('Loading.', 'bibliomundi-woocommerce'); ?>',
	'server_error': '<?php echo __('Server error.', 'bibliomundi-woocommerce'); ?>',
	'category_empty_error': '<?php echo __('Please set a category.', 'bibliomundi-woocommerce'); ?>',
	'import_confirmation': '<?php echo __('Do you want to manually import catalogs?', 'bibliomundi-woocommerce'); ?>',
	'restore_confirmation': '<?php echo __('By restoring the settings all products related to Bibliomundi Woocommerce will be removed and the process of configuring the plugin will be necessary again.', 'bibliomundi-woocommerce'); ?>',
	'import_continue_confirmation': '<?php echo __('Do you want to continue previous importing catalogs?', 'bibliomundi-woocommerce'); ?>',
	'txt_import': '<?php echo __('Import', 'bibliomundi-woocommerce'); ?>',
	'txt_import_new': '<?php echo __('Import new', 'bibliomundi-woocommerce'); ?>',
	'txt_import_continue': '<?php echo __('Continue import', 'bibliomundi-woocommerce'); ?>',
	'txt_cancel': '<?php echo __('Cancel', 'bibliomundi-woocommerce'); ?>',
	'txt_confirm': '<?php echo __('Confirm', 'bibliomundi-woocommerce'); ?>',
};
</script>