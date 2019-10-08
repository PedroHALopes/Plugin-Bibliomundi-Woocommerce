<?php global $plugin_settings, $plugin_meta, $plugin_domain; ?>
<div class="row" style="margin-bottom: 4em;">
	<div class="col-md-12">
		<ul class="nav nav-tabs">
		<?php if($plugin_settings['is_wizard'] == 1){ ?>
			<li class="<?php echo (@$plugin_meta['Pagetab'] == 'credentials') ? 'active' : ''; ?>">
				<a href="<?php echo admin_url('admin.php?page='.$plugin_domain.'-credentials'); ?>"><?php echo __('Edit Credentials', 'bibliomundi-woocommerce'); ?></a>
			</li>
			<li class="<?php echo (@$plugin_meta['Pagetab'] == 'import') ? 'active' : ''; ?>">
				<a href="<?php echo admin_url('admin.php?page='.$plugin_domain.'-import'); ?>"><?php echo __('Import Manual', 'bibliomundi-woocommerce'); ?></a>
			</li>
		<?php } ?>
		
		<?php if($plugin_settings['is_wizard'] == 0){ ?>
			<li class="<?php echo (@$plugin_meta['Pagetab'] == '') ? 'active' : ''; ?>">
				<a href="<?php echo admin_url('admin.php?page='.$plugin_domain); ?>"><?php echo __('Settings', 'bibliomundi-woocommerce'); ?></a>
			</li>
		<?php } ?>
		
			<li class="<?php echo (@$plugin_meta['Pagetab'] == 'tutorials') ? 'active' : ''; ?>">
				<a href="<?php echo admin_url('admin.php?page='.$plugin_domain.'-tutorials'); ?>"><?php echo __('Video Tutorials', 'bibliomundi-woocommerce'); ?></a>
			</li>
			<li class="<?php echo (@$plugin_meta['Pagetab'] == 'faq') ? 'active' : ''; ?>">
				<a href="<?php echo admin_url('admin.php?page='.$plugin_domain.'-faq'); ?>"><?php echo __('FAQ', 'bibliomundi-woocommerce'); ?></a>
			</li>
			
		<?php if($plugin_settings['is_wizard'] == 1){ ?>
			<li class="<?php echo (@$plugin_meta['Pagetab'] == 'restore') ? 'active' : ''; ?>">
				<a href="<?php echo admin_url('admin.php?page='.$plugin_domain.'-restore'); ?>"><?php echo __('Restore Configuration', 'bibliomundi-woocommerce'); ?></a>
			</li>
		<?php } ?>
		</ul>
	</div>
</div>