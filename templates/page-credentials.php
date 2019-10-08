<?php global $plugin_meta, $plugin_settings, $plugin_domain; ?>
<form class="row" method="POST" autocomplete="off">
	<div class="col-md-8 col-md-offset-2">
		<div class="row form-group">
			<label class="col-md-2 form-label"><?php echo __('Client ID', 'bibliomundi-woocommerce'); ?></label>
			<div class="col-md-10">
				<input type="text" class="form-control" id="bibliomundi-input-api-key" placeholder="" value="<?php echo $plugin_settings['api_key']; ?>" />
			</div>
		</div>
		<div class="row form-group">
			<label class="col-md-2 form-label"><?php echo __('Client Key', 'bibliomundi-woocommerce'); ?></label>
			<div class="col-md-10">
				<input type="text" class="form-control" id="bibliomundi-input-api-secret" placeholder="" value="<?php echo $plugin_settings['api_secret']; ?>" />
			</div>
		</div>
		<div class="row form-group">
			<div class="col-md-12 text-right">
				<a href="javascript:;" id="bibliomundi-button-validate" class="btn btn-primary"><?php echo __('Validate', 'bibliomundi-woocommerce'); ?></a>
				<a href="javascript:;" id="bibliomundi-button-submit-credentials" class="btn btn-primary"><?php echo __('Save', 'bibliomundi-woocommerce'); ?></a>
			</div>
		</div>
		<div class="row form-group">
			<div class="col-md-12 text-left">
				<p style="margin: 1em 0;"><?php echo __('To check your integration credentials, go to:', 'bibliomundi-woocommerce'); ?> 
				<a href="<?php echo $plugin_settings['url_integration']; ?>" target="_blank"><?php echo $plugin_settings['url_integration']; ?></a>.</p>
			</div>
		</div>
	</div>
</form>