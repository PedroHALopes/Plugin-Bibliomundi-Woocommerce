<?php global $plugin_meta, $plugin_settings, $plugin_domain, $categories; ?>
<div class="row">
	<div class="col-md-offset-3 col-md-6 col-xs-12">
		<div id="bibliomundi-bar" class="progress">
			<div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 16.67%;"></div>
		</div>
		<form id="bibliomundi-steps" class="row bibliomundi-steps" method="POST" autocomplete="off">
			<div id="bibliomundi-step-1" class="col-md-12 bibliomundi-step">
				<div class="bibliomundi-step-content">
					<div class="bibliomundi-step-content-header text-center">
						<h4><?php echo __('Let \'s start selling eBooks?', 'bibliomundi-woocommerce'); ?></h4>
					</div>
					<div class="bibliomundi-step-content-body">
						<div class="row">
							<div class="col-md-4 col-md-offset-1">
								<a href="#bibliomundi-step-2" class="btn btn-primary bibliomundi-button-move bibliomundi-button-next bibliomundi-button-big" style="width: 100%;height: 54px;">
								<?php echo __('I am already registered at BiblioMundi', 'bibliomundi-woocommerce'); ?>
								</a>
							</div>
							<div class="col-md-4 col-md-offset-2">
								<a href="<?php echo $plugin_settings['url_store']; ?>" class="btn btn-primary bibliomundi-button-move bibliomundi-button-big" style="width: 100%;height: 54px;" target="_blank">
								<?php echo __('I want to register and sell eBooks', 'bibliomundi-woocommerce'); ?>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="bibliomundi-step-2" class="col-md-12 bibliomundi-step hide">
				<div class="bibliomundi-step-content">
					<div class="bibliomundi-step-content-header text-center">
						<h4><?php echo __('Add your seller credentials', 'bibliomundi-woocommerce'); ?></h4>
						<p>
						<?php echo sprintf(
							__('%s to learn where to find your credentials.', 'bibliomundi-woocommerce'), 
							'<a href="'.$plugin_settings['url_credentials'].'" target="_blank">'.__('Click here', 'bibliomundi-woocommerce').'</a>'
						); ?>
						</p>
					</div>
					<div class="bibliomundi-step-content-body">
						<div class="row">
							<div class="col-md-8 col-md-offset-2">
								<div class="row form-group">
									<label class="col-md-3 form-label"><?php echo __('Client ID', 'bibliomundi-woocommerce'); ?></label>
									<div class="col-md-9">
										<input type="text" class="form-control" id="bibliomundi-input-api-key" placeholder="" value="<?php echo $plugin_settings['api_key']; ?>" autocomplete="on" />
									</div>
								</div>
								<div class="row form-group">
									<label class="col-md-3 form-label"><?php echo __('Client Key', 'bibliomundi-woocommerce'); ?></label>
									<div class="col-md-9">
										<input type="text" class="form-control" id="bibliomundi-input-api-secret" placeholder="" value="<?php echo $plugin_settings['api_secret']; ?>" autocomplete="on" />
									</div>
								</div>
								<div class="row form-group text-center">
									<a href="javascript:;" id="bibliomundi-button-validate" class="btn btn-primary"><?php echo __('Validate', 'bibliomundi-woocommerce'); ?></a>
								</div>
							</div>
							<div class="col-md-12 text-right">
								<a href="#bibliomundi-step-1" class="btn btn-primary bibliomundi-button-move"><?php echo __('Previous', 'bibliomundi-woocommerce'); ?></a>
								<a href="#bibliomundi-step-3" class="btn btn-primary bibliomundi-button-move bibliomundi-button-next pull-right"><?php echo __('Next', 'bibliomundi-woocommerce'); ?></a>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="bibliomundi-step-3" class="col-md-12 bibliomundi-step hide">
				<div class="bibliomundi-step-content">
					<div class="bibliomundi-step-content-header text-center">
						<h4><?php echo __('Set Product ID Standard', 'bibliomundi-woocommerce'); ?></h4>
					</div>
					<div class="bibliomundi-step-content-body">
						<div class="row">
							<div class="col-md-8 col-md-offset-2">
								<div class="form-group">
									<label style="font-weight: normal;">
										<input type="radio" class="form-control" name="radio_isbn" id="bibliomundi-input-isbn-sku" <?php echo ($plugin_settings['isbn_sku'] == 0) ? 'checked' : ''; ?> value="0" />
									<?php echo __('Set ISBN as ID(SKU) for my products. (recommended)', 'bibliomundi-woocommerce'); ?>
									</label>
								</div>
								<div class="form-group">
									<label style="font-weight: normal;">
										<input type="radio" class="form-control" name="radio_isbn" id="bibliomundi-input-isbn-proprietary" <?php echo ($plugin_settings['isbn_sku'] == 1) ? 'checked' : ''; ?> value="1" />
									<?php echo __('Set proprietary BiblioMundi ID as SKU.', 'bibliomundi-woocommerce'); ?>
									</label>
								</div>
							</div>
							<div class="col-md-12 text-right">
								<a href="#bibliomundi-step-2" class="btn btn-primary bibliomundi-button-move"><?php echo __('Previous', 'bibliomundi-woocommerce'); ?></a>
								<a href="#bibliomundi-step-4" class="btn btn-primary bibliomundi-button-move bibliomundi-button-next pull-right"><?php echo __('Next', 'bibliomundi-woocommerce'); ?></a>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="bibliomundi-step-4" class="col-md-12 bibliomundi-step hide">
				<div class="bibliomundi-step-content">
					<div class="bibliomundi-step-content-header text-center">
						<h4><?php echo __('Set Catalogue Import', 'bibliomundi-woocommerce'); ?></h4>
					</div>
					<div class="bibliomundi-step-content-body">
						<div class="row">
							<div class="col-md-8 col-md-offset-2">
								<div class="form-group">
									<label style="font-weight: normal;">
										<input type="radio" class="form-control" name="bibliomundicategory" value="0" <?php echo ($plugin_settings['category_type'] == 0) ? 'checked' : ''; ?> />
									<?php echo __('I only sell eBooks.', 'bibliomundi-woocommerce'); ?>
									</label>
								</div>
								<div class="form-group">
									<label style="font-weight: normal;">
										<input type="radio" class="form-control" name="bibliomundicategory" value="1" <?php echo ($plugin_settings['category_type'] == 1) ? 'checked' : ''; ?> />
									<?php echo __('I sell eBooks and/or other products.', 'bibliomundi-woocommerce'); ?>
									</label>
								</div>
								<div class="form-group">
									<label style="font-weight: normal;">
										<input type="radio" class="form-control" name="bibliomundicategory" value="2" <?php echo ($plugin_settings['category_type'] == 2) ? 'checked' : ''; ?> />
									<?php echo __('I do not sell eBooks yet.', 'bibliomundi-woocommerce'); ?>
									</label>
								</div>
							</div>
							<div class="col-md-12 text-right">
								<a href="#bibliomundi-step-3" class="btn btn-primary bibliomundi-button-move"><?php echo __('Previous', 'bibliomundi-woocommerce'); ?></a>
								<a href="#bibliomundi-step-5" class="btn btn-primary bibliomundi-button-move bibliomundi-button-next pull-right"><?php echo __('Next', 'bibliomundi-woocommerce'); ?></a>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="bibliomundi-step-5" class="col-md-12 bibliomundi-step hide">
				<div class="bibliomundi-step-content">
					<div class="bibliomundi-step-content-header text-center">
						<h4><?php echo __('Set Catalogue Import', 'bibliomundi-woocommerce'); ?></h4>
					</div>
					<div class="bibliomundi-step-content-body">
						<div class="row">
							<div class="col-md-8 col-md-offset-2">
								<div class="row form-group text-center">
									<label class="col-md-12 form-label"><?php echo __('Set Category where the eBooks will import to in your store.', 'bibliomundi-woocommerce'); ?></label>
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
									</div>
								</div>
							</div>
							<div class="col-md-12 text-right">
								<a href="#bibliomundi-step-4" class="btn btn-primary bibliomundi-button-move"><?php echo __('Previous', 'bibliomundi-woocommerce'); ?></a>
								<a href="#bibliomundi-step-6" class="btn btn-primary bibliomundi-button-move bibliomundi-button-next pull-right"><?php echo __('Next', 'bibliomundi-woocommerce'); ?></a>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="bibliomundi-step-6" class="col-md-12 bibliomundi-step hide">
				<div class="bibliomundi-step-content text-center">
					<div class="bibliomundi-step-content-header">
						<h4><?php echo __('Your first Import', 'bibliomundi-woocommerce'); ?></h4>						
					</div>
					<div class="bibliomundi-step-content-body">						
						<div id="sub-step-6-1" class="row">
							<div class="col-md-10 col-md-offset-1">
								<p>
								<?php echo __('If you have already configured your settings at BiblioMundi, clique below to import your selected catalogue and start selling.', 'bibliomundi-woocommerce'); ?>
								</p>
								<p>
								<?php echo sprintf(
									__('However if you haven\'t chosen your default catalog, %s before import the catalog.', 'bibliomundi-woocommerce'), 
									'<a href="'.$plugin_settings['url_platform'].'" target="_blank">'.__('go to the BiblioMundi\'s Platform', 'bibliomundi-woocommerce').'</a>'
								); ?>
								</p>
								<a href="#bibliomundi-step-7" class="btn btn-primary bibliomundi-button-move bibliomundi-button-next <?php echo (@$plugin_settings['continue_status']) ? 'import-can-continue' : ''; ?>"><?php echo __('Import Catalogue', 'bibliomundi-woocommerce'); ?></a>
							</div>
						</div>					
						<div id="sub-step-6-2" class="row hide">
							<div class="col-md-10 col-md-offset-1">
								<p><?php echo __('Import in progress.', 'bibliomundi-woocommerce'); ?><br/><?php echo __('This progress may take a few minutes.', 'bibliomundi-woocommerce'); ?></p>
								<div id="bibliomundi-import-bar" class="progress">
									<div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>
									<div class="text-center progress-bar-text">0%</div>
								</div>
							</div>
						</div>	
					</div>
				</div>
			</div>
			<div id="bibliomundi-step-7" class="col-md-12 bibliomundi-step hide">
				<div class="bibliomundi-step-content text-center">
					<div class="bibliomundi-step-content-header">
						<h4><?php echo __('Set Catalogue Import', 'bibliomundi-woocommerce'); ?></h4>						
					</div>
					<div class="bibliomundi-step-content-body">						
						<div id="sub-step-7-1" class="row hide">
							<div class="col-md-10 col-md-offset-1">
								<div class="alert alert-success">
									<p class="bold"><?php echo __('Import was successfully', 'bibliomundi-woocommerce'); ?></p>
									<p>
									<?php echo sprintf(
										__('A total of %s eBooks were added.', 'bibliomundi-woocommerce'), 
										'<span id="alert-num"></span> '
									); ?>
									</p>
								</div>
							</div>
							<div class="col-md-10 col-md-offset-1 text-right">
								<a href="#bibliomundi-step-8" class="btn btn-primary bibliomundi-button-move bibliomundi-button-next"><?php echo __('Next', 'bibliomundi-woocommerce'); ?></a>
							</div>
						</div>					
						<div id="sub-step-7-2" class="row hide">
							<div class="col-md-10 col-md-offset-1">
								<div class="alert alert-danger">
									<p><?php echo __('The process couldn\'t be completed. It had an importation problem. Click on the following button to send the error report to the Bibliomundi\'s support team. You can also try to import the catalog again.', 'bibliomundi-woocommerce'); ?></p>
								</div>
							</div>
							<div class="col-md-10 col-md-offset-1 text-right">
								<a href="#bibliomundi-step-7" class="btn btn-primary bibliomundi-button-move bibliomundi-button-next <?php echo (@$plugin_settings['continue_status']) ? 'import-can-continue' : ''; ?>"><?php echo __('Import', 'bibliomundi-woocommerce'); ?></a>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="bibliomundi-step-8" class="col-md-12 bibliomundi-step hide">
				<div class="bibliomundi-step-content text-center">
					<div class="bibliomundi-step-content-header">
						<h4><?php echo __('Automatic updates', 'bibliomundi-woocommerce'); ?></h4>						
					</div>
					<div class="bibliomundi-step-content-body">		
						<div class="row">
							<div class="col-md-10 col-md-offset-1">
								<p><?php echo __('You need to configure your catalogue update routine at server level.', 'bibliomundi-woocommerce'); ?></p>
								<div class="alert alert-info">
								0,30 * * * * curl '<?php echo get_home_url(); ?>/bibliomundi-cron/' > /dev/null 2>&1
								</div>
								<p>
								<?php echo sprintf(
									__('%s to learn how to set the routine.', 'bibliomundi-woocommerce'), 
									'<a href="alert-num" target="_blank">'.__('Click here', 'bibliomundi-woocommerce').'</a>'
								); ?>
								</p>
							</div>
							<div class="col-md-10 col-md-offset-1 text-right">
								<a href="#bibliomundi-step-9" class="btn btn-primary bibliomundi-button-move bibliomundi-button-next"><?php echo __('Next', 'bibliomundi-woocommerce'); ?></a>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="bibliomundi-step-9" class="col-md-12 bibliomundi-step hide">
				<div class="bibliomundi-step-content text-center">
					<div class="bibliomundi-step-content-header">
						<h4><?php echo __('Congratulation! The plugin was installed successfully.', 'bibliomundi-woocommerce'); ?></h4>						
					</div>
					<div class="bibliomundi-step-content-body">		
						<div class="row">
							<div class="col-md-10 col-md-offset-1">
								<p><?php echo __('Remember to set the eBooks sales tax rules for your region (where applicable).', 'bibliomundi-woocommerce'); ?></p>
								<p><?php echo __('Always keep your plugin updated.', 'bibliomundi-woocommerce'); ?></p>
							</div>
							<div class="col-md-10 col-md-offset-1 text-center">
								<a href="javascript:;" id="bibliomundi-end" class="btn btn-primary"><?php echo __('Finish', 'bibliomundi-woocommerce'); ?></a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>