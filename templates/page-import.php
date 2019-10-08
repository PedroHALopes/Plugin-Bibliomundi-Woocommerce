<?php global $plugin_meta, $plugin_settings, $plugin_domain, $categories; ?>
<form class="row form-import-manual" method="POST" autocomplete="off">
	<div class="col-md-8 col-md-offset-2">
		<div class="row form-group">
			<h4 class="col-md-12"><?php echo __('Set Product ID Standard', 'bibliomundi-woocommerce'); ?></h4>		
		</div>
		<div class="row form-group">
			<label class="col-md-12" style="font-weight: normal;">
				<input type="radio" class="form-control" name="radio_isbn" id="bibliomundi-input-isbn-sku" <?php echo ($plugin_settings['isbn_sku'] == 0) ? 'checked' : ''; ?> value="0" />
			<?php echo __('Set ISBN as ID(SKU) for my products. (recommended)', 'bibliomundi-woocommerce'); ?>
			</label>
		</div>
		<div class="row form-group">
			<label class="col-md-12" style="font-weight: normal;">
				<input type="radio" class="form-control" name="radio_isbn" id="bibliomundi-input-isbn-proprietary" <?php echo ($plugin_settings['isbn_sku'] == 1) ? 'checked' : ''; ?> value="1" />
			<?php echo __('Set proprietary BiblioMundi ID as SKU.', 'bibliomundi-woocommerce'); ?>
			</label>
		</div>
		<div class="row form-group">
			<h4 class="col-md-12"><?php echo __('Set Catalogue Import', 'bibliomundi-woocommerce'); ?></h4>		
		</div>
		<div class="row form-group">
			<label class="col-md-12" style="font-weight: normal;">
				<input type="radio" class="form-control" name="bibliomundicategory" value="0" <?php echo ($plugin_settings['category_type'] == 0) ? 'checked' : ''; ?> />
			<?php echo __('I only sell eBooks.', 'bibliomundi-woocommerce'); ?>
			</label>
		</div>
		<div class="row form-group">
			<label class="col-md-12" style="font-weight: normal;">
				<input type="radio" class="form-control" name="bibliomundicategory" value="1" <?php echo ($plugin_settings['category_type'] == 1) ? 'checked' : ''; ?> />
			<?php echo __('I sell eBooks and/or other products.', 'bibliomundi-woocommerce'); ?>
			</label>
			<div class="col-md-10 col-md-offset-1 sub-content-category <?php echo ($plugin_settings['category_type'] == 1) ? '' : 'hide'; ?>" style="padding-top: 1em;">
				<div class="row form-group">
					<label class="col-md-12"><?php echo __('Set Category where the eBooks will import to in your store.', 'bibliomundi-woocommerce'); ?></label>
					<div class="col-md-12">
					<?php if(count($categories) > 0){ ?>
						<select class="form-control" id="bibliomundi-input-category" required>
							<option value=""><?php echo __('Select dropdown', 'bibliomundi-woocommerce'); ?></option>
						<?php foreach($categories as $category){ ?>
							<option value="<?php echo $category->term_id; ?>" <?php echo ($plugin_settings['category_id'] == $category->term_id) ? 'selected' : ''; ?>><?php echo $category->name; ?></option>
						<?php } ?>
						</select>
					<?php }else{ ?>
						<select class="form-control" id="bibliomundi-input-category" required>
							<option value=""><?php echo __('There is no Product category.', 'bibliomundi-woocommerce'); ?></option>
						</select>
					<?php } ?>
						<small>
						<?php echo sprintf(
							__('You can manage %s.', 'bibliomundi-woocommerce'), 
							'<a href="'.admin_url('edit-tags.php?taxonomy=product_cat&post_type=product').'" target="_blank">'.__('Product category', 'bibliomundi-woocommerce').'</a>'
						); ?>
						</small>
					</div>
				</div>
			</div>
		</div>
		<div class="row form-group">
			<label class="col-md-12" style="font-weight: normal;">
				<input type="radio" class="form-control" name="bibliomundicategory" value="2" <?php echo ($plugin_settings['category_type'] == 2) ? 'checked' : ''; ?> />
			<?php echo __('I do not sell eBooks yet.', 'bibliomundi-woocommerce'); ?>
			</label>
		</div>
		<div class="row form-group">
			<div class="col-md-12 text-right">
				<a href="javascript:;" id="bibliomundi-button-submit-save" class="btn btn-primary"><?php echo __('Save', 'bibliomundi-woocommerce'); ?></a>
				<a href="javascript:;" id="bibliomundi-button-manual-import" class="btn btn-primary"><?php echo __('Import Manual', 'bibliomundi-woocommerce'); ?></a>
			</div>
		</div>
		<div class="row form-group">
			<div class="col-md-12 text-left">
				<p style="margin: 1em 0;"><?php echo __('Need help setting up the import of your catalog automatically?', 'bibliomundi-woocommerce'); ?> 
				<?php echo __('Please contact BiblioMundi at:', 'bibliomundi-woocommerce'); ?>: <a href="mailto:<?php echo $plugin_settings['email_support']; ?>"><?php echo $plugin_settings['email_support']; ?></a>.</p>
								
				<p style="margin: 1em 0;"><?php echo __('To filter the contents of your import, click here:', 'bibliomundi-woocommerce'); ?> 
				<a href="<?php echo $plugin_settings['url_platform']; ?>" target="_blank"><?php echo $plugin_settings['url_platform']; ?></a>.</p>
			</div>
		</div>
	</div>
</form>