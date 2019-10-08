jQuery(document).ready(function($){
	$('body').on('click', '.bibliomundi-button-move', function(){
		var container = $(this);
		var target = container.attr('href');
		
		if(target.includes('http') || target.includes('j'))
			return;
		
		if($(target).length){
			var step = parseInt(target.replace('#bibliomundi-step-', '')) || 1;
			
			if(step > 0){
				var steps = container.closest('.bibliomundi-step').closest('.bibliomundi-steps').find('.bibliomundi-step').length;
				
				return bibliomundi_move(target, step, steps, container.hasClass('bibliomundi-button-next'));
			}
		}
		
		return false;
	});
	
	$('body').on('click', '#bibliomundi-button-validate', function(){
		var container = $(this);
		var api_key = $('#bibliomundi-input-api-key').val() || '';
		var api_secret = $('#bibliomundi-input-api-secret').val() || '';
		
		bibliomundi_loading();
		
		$.ajax({
			type: 'POST',
			url: bibliomundi_url.ajax,
			dataType: 'json',
			data: {
				action: 'bibliomundi_api_validation',
				api_key: api_key,
				api_secret: api_secret,
			},
			success: function(ret) { 
				if (ret.success){
					return swal('', ret.message, 'success');
				}else{
					return swal('', ret.message, 'error');
				}
				
				return swal('', bibliomundi_error.server_error, 'error');
			},
			error: function(jqXHR, textStatus, errorThrown) {
				var error_msg = textStatus + ': ' + errorThrown;
				console.log(error_msg);
				
				return swal('', error_msg, 'error');
			}
		});		
	});	
	
	$('body').on('click', '#bibliomundi-end', function(){
		var container = $(this);
		
		bibliomundi_loading();
		
		$.ajax({
			type: 'POST',
			url: bibliomundi_url.ajax,
			dataType: 'json',
			data: {
				action: 'bibliomundi_settings',
				settings: {
					is_wizard: 1,
				}
			},
			success: function(ret) { 
				if (ret.success){
					return location.reload();
				}else{
					return swal('', ret.message, 'error');
				}
				
				return swal('', bibliomundi_error.server_error, 'error');
			},
			error: function(jqXHR, textStatus, errorThrown) {
				var error_msg = textStatus + ': ' + errorThrown;
				console.log(error_msg);
				
				return swal('', error_msg, 'error');
			}
		});		
	});		
	
	$('body').on('click', '.form-import-manual input[name="bibliomundicategory"]', function(){
		var container = $(this);
		var category_type = parseInt($('input[name="bibliomundicategory"]:checked').val()) || 0;
		
		if(category_type == 1){
			$('.sub-content-category').removeClass('hide');
		}else{
			$('.sub-content-category').addClass('hide');
		}
	});					
	
	$('body').on('click', '#bibliomundi-button-submit-credentials', function(){
		var container = $(this);
		var api_key = $('#bibliomundi-input-api-key').val() || '';
		var api_secret = $('#bibliomundi-input-api-secret').val() || '';
		
		$.ajax({
			type: 'POST',
			url: bibliomundi_url.ajax,
			dataType: 'json',
			data: {
				action: 'bibliomundi_settings',
				settings: {
					api_key: api_key,
					api_secret: api_secret,
				}
			},
			success: function(ret) { 
				if (ret.success){
					return swal('', ret.message, 'success');
				}else{
					return swal('', ret.message, 'error');
				}
				
				return swal('', bibliomundi_error.server_error, 'error');
			},
			error: function(jqXHR, textStatus, errorThrown) {
				var error_msg = textStatus + ': ' + errorThrown;
				console.log(error_msg);
				
				return swal('', error_msg, 'error');
			}
		});
	});
	
	$('body').on('click', '#bibliomundi-button-submit-save', function(){
		var container = $(this);
		var isbn_sku = parseInt($('input[name="radio_isbn"]:checked').val()) || 0;
		var category_type = parseInt($('input[name="bibliomundicategory"]:checked').val()) || 0;
		var category_id = parseInt($('#bibliomundi-input-category').val()) || 0;
		
		if(category_type == 1){
			if(category_id == 0){
				return swal('', bibliomundi_error.category_empty_error, 'error');
			}
		}else{
			category_id = 0;
		}
		
		$.ajax({
			type: 'POST',
			url: bibliomundi_url.ajax,
			dataType: 'json',
			data: {
				action: 'bibliomundi_settings',
				settings: {
					category_type: category_type,
					category_id: category_id,
					isbn_sku: isbn_sku,
				}
			},
			success: function(ret) { 
				if (ret.success){
					return swal('', ret.message, 'success');
				}else{
					return swal('', ret.message, 'error');
				}
				
				return swal('', bibliomundi_error.server_error, 'error');
			},
			error: function(jqXHR, textStatus, errorThrown) {
				var error_msg = textStatus + ': ' + errorThrown;
				console.log(error_msg);
				
				return swal('', error_msg, 'error');
			}
		});
	});	
	
	$('body').on('click', '#bibliomundi-button-manual-import', function(){
		var container = $(this);
		
		swal({
			title: '',
			text: bibliomundi_error.import_confirmation,
			type: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#DD6B55',
			confirmButtonText: bibliomundi_error.txt_import,
			cancelButtonText: bibliomundi_error.txt_cancel,
			closeOnConfirm: false,
			html: false
		}, function(){
			bibliomundi_loading();
			
			return bibliomundi_import_manual_ajax();
		});
	});
	
	$('body').on('click', '#btn-submit-reset', function(){
		var container = $(this);
		
		swal({
			title: '',
			text: bibliomundi_error.restore_confirmation,
			type: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#DD6B55',
			confirmButtonText: bibliomundi_error.txt_confirm,
			cancelButtonText: bibliomundi_error.txt_cancel,
			closeOnConfirm: false,
			html: false
		}, function(){
			bibliomundi_loading();
			
			return bibliomundi_restore();
		});
	});
	
	if($('.bibliomundi-video').length > 0){
		var videos = new ModalVideo('.bibliomundi-video');
	}
	
	function bibliomundi_move(target, step, steps, next){
		if(step == 2 && next){
			window.onbeforeunload = function(e) {
				return 'Dialog text here.';
			};
		}else
		if(step == 3 && next){
			return bibliomundi_api_valid(target, step, steps);
		}else
		if(step == 4 && next){
			return bibliomundi_isbn(target, step, steps);
		}else
		if(step == 5 && next){
			return bibliomundi_category_type(target, step, steps);
		}else
		if(step == 6 && next){
			return bibliomundi_category_choice(target, step, steps);
		}else
		if(step == 7 && next){
			return bibliomundi_import(target, step, steps);
		}else
		if(step == 8 && next){
			$('#sub-step-6-2').addClass('hide').siblings().removeClass('hide');
		}			
		
		return bibliomundi_move_tab(target, step, steps);
	}
	
	function bibliomundi_move_tab(target, step, steps){
		var progress = ((step / steps) * 100);
		
		$('#bibliomundi-import-bar').find('.progress-bar').attr('aria-valuenow', progress);
		
		$('#bibliomundi-bar').find('.progress-bar').css({
			width: progress+'%',
		});
		
		$(target).removeClass('hide').siblings().addClass('hide');
		
		return false;
	}
	
	function bibliomundi_api_valid(target, step, steps){
		var api_key = $('#bibliomundi-input-api-key').val() || '';
		var api_secret = $('#bibliomundi-input-api-secret').val() || '';
		
		if(api_key == '' || api_secret == ''){
			return swal('', bibliomundi_error.input_api_error, 'error');
		}
		
		return bibliomundi_move_tab(target, step, steps);
	}
	
	function bibliomundi_isbn(target, step, steps){
		var sku = 0;
		
		if($('#bibliomundi-input-isbn-sku').is(':checked')){
			//
		}else
		if($('#bibliomundi-input-isbn-proprietary').is(':checked')){
			sku = 1;
		}
		
		$.ajax({
			type: 'POST',
			url: bibliomundi_url.ajax,
			dataType: 'json',
			data: {
				action: 'bibliomundi_settings',
				settings: {
					isbn_sku: sku,
				}
			},
			success: function(ret) { 
				if (ret.success){
					return bibliomundi_move_tab(target, step, steps);
				}else{
					return swal('', ret.message, 'error');
				}
				
				return swal('', bibliomundi_error.server_error, 'error');
			},
			error: function(jqXHR, textStatus, errorThrown) {
				var error_msg = textStatus + ': ' + errorThrown;
				console.log(error_msg);
				
				return swal('', error_msg, 'error');
			}
		});
	}
	
	function bibliomundi_category_type(target, step, steps){
		var category_type = parseInt($('input[name="bibliomundicategory"]:checked').val()) || 0;
		
		$.ajax({
			type: 'POST',
			url: bibliomundi_url.ajax,
			dataType: 'json',
			data: {
				action: 'bibliomundi_settings',
				settings: {
					category_type: category_type,
				}
			},
			success: function(ret) { 
				if (ret.success){
					if(category_type != 1){
						var next_step = step + 1;
						
						target = target.replace(step, next_step);
						step = next_step;
					}
					
					return bibliomundi_move_tab(target, step, steps);
				}else{
					return swal('', ret.message, 'error');
				}
				
				return swal('', bibliomundi_error.server_error, 'error');
			},
			error: function(jqXHR, textStatus, errorThrown) {
				var error_msg = textStatus + ': ' + errorThrown;
				console.log(error_msg);
				
				return swal('', error_msg, 'error');
			}
		});
	}
	
	function bibliomundi_category_choice(target, step, steps){
		var category_id = parseInt($('#bibliomundi-input-category').val()) || 0;
		
		if(category_id == 0){
			return $('#bibliomundi-input-category').focus();
		}
		
		$.ajax({
			type: 'POST',
			url: bibliomundi_url.ajax,
			dataType: 'json',
			data: {
				action: 'bibliomundi_settings',
				settings: {
					category_id: category_id,
				}
			},
			success: function(ret) { 
				if (ret.success){
					return bibliomundi_move_tab(target, step, steps);
				}else{
					return swal('', ret.message, 'error');
				}
				
				return swal('', bibliomundi_error.server_error, 'error');
			},
			error: function(jqXHR, textStatus, errorThrown) {
				var error_msg = textStatus + ': ' + errorThrown;
				console.log(error_msg);
				
				return swal('', error_msg, 'error');
			}
		});
	}
	
	function bibliomundi_import(target, step, steps){
		$('#bibliomundi-step-6').removeClass('hide').siblings().addClass('hide');
		$('#sub-step-6-1').addClass('hide').siblings().removeClass('hide');
		
		bibliomundi_loading();
		
		if($('.import-can-continue').length > 0){ //return bibliomundi_import_ajax(step, steps, true);
			swal({
				title: '',
				text: bibliomundi_error.import_continue_confirmation,
				type: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#DD6B55',
				confirmButtonText: bibliomundi_error.txt_import_continue,
				cancelButtonText: bibliomundi_error.txt_import_new,
				closeOnConfirm: false,
				html: false
			}, function(cont){
				if(cont) return bibliomundi_import_ajax(step, steps, true);
				else return bibliomundi_import_ajax(step, steps, false);
			});
		}else{
			return bibliomundi_import_ajax(step, steps, false);
		}
	}

	function bibliomundi_import_bar(progress){
		progress = parseFloat(progress) || 0;
		
		$('#bibliomundi-import-bar').find('.progress-bar').attr('aria-valuenow', progress);
		
		$('#bibliomundi-import-bar').find('.progress-bar').css({
			width: progress+'%',
		});
		
		return $('#bibliomundi-import-bar').find('.progress-bar-text').html(progress.toFixed(0)+'%');
	}
	
	function bibliomundi_import_ajax(step, steps, continue_status){		
		$.ajax({
			type: 'POST',
			url: bibliomundi_url.ajax,
			dataType: 'json',
			data: {
				action: 'bibliomundi_import',
				continue_status: continue_status,
			},
			success: function(ret) { 
				if (ret.success){
					var books = ret.books;
					var current_page = ret.current_page;
					var current_percent = ret.current_percent;
					
					if(books){
						swal.close();
						
						//bar
						bibliomundi_import_bar(current_percent);						
						
						return bibliomundi_import_process_ajax(current_page, step, steps);
					}
				}else{
					// error import
					$('#sub-step-7-1').addClass('hide');
					$('#sub-step-7-2').removeClass('hide');
					bibliomundi_move_tab('#bibliomundi-step-7', step, steps);
					
					return swal('', ret.message, 'error');
				}
				
				// error import
				$('#sub-step-7-1').addClass('hide');
				$('#sub-step-7-2').removeClass('hide');
				bibliomundi_move_tab('#bibliomundi-step-7', step, steps);
				
				return swal('', bibliomundi_error.server_error, 'error');
			},
			error: function(jqXHR, textStatus, errorThrown) {
				var error_msg = textStatus + ': ' + errorThrown;
				console.log(error_msg);
				
				// error import
				$('#sub-step-7-1').addClass('hide');
				$('#sub-step-7-2').removeClass('hide');
				bibliomundi_move_tab('#bibliomundi-step-7', step, steps);
				
				return swal('', error_msg, 'error');
			}
		});
	}
	
	function bibliomundi_import_process_ajax(page, step, steps){
		page = parseInt(page) || 1;
		
		if(page < 1)
			page = 1;
		
		$.ajax({
			type: 'POST',
			url: bibliomundi_url.ajax,
			dataType: 'json',
			data: {
				action: 'bibliomundi_import_process',
				page: page,
			},
			success: function(ret) { 
				if (ret.success){
					var end = ret.end || false;
					var current_percent = ret.current_percent;
					
					//bar
					bibliomundi_import_bar(current_percent);
					
					if(!end){						
						return bibliomundi_import_process_ajax(page+1, step, steps);
					}else{
						// error import
						$('#sub-step-7-2').addClass('hide');
						$('#sub-step-7-1').removeClass('hide');
						$('#alert-num').html(ret.books);
						
						window.onbeforeunload = function (e) {};
						
						bibliomundi_move_tab('#bibliomundi-step-7', step, steps);
				
						return swal('', ret.message, 'success');
					}
				}else{
					// error import
					$('#sub-step-7-1').addClass('hide');
					$('#sub-step-7-2').removeClass('hide');
					bibliomundi_move_tab('#bibliomundi-step-7', step, steps);
					
					return swal('', ret.message, 'error');
				}
				
				// error import
				$('#sub-step-7-1').addClass('hide');
				$('#sub-step-7-2').removeClass('hide');
				bibliomundi_move_tab('#bibliomundi-step-7', step, steps);
				
				return swal('', bibliomundi_error.server_error, 'error');
			},
			error: function(jqXHR, textStatus, errorThrown) {
				var error_msg = textStatus + ': ' + errorThrown;
				console.log(error_msg);
				
				return swal('', error_msg, 'error');
			}
		});
	}
	
	function bibliomundi_import_manual_ajax(){	
		window.onbeforeunload = function(e) {
			return 'Dialog text here.';
		};
	
		$.ajax({
			type: 'POST',
			url: bibliomundi_url.ajax,
			dataType: 'json',
			data: {
				action: 'bibliomundi_import',
				manual: true,
			},
			success: function(ret) { 
				if (ret.success){
					var books = ret.books;
					var current_percent = ret.current_percent;
					
					if(books){
						var loading_text = '<i class="dashicons dashicons-image-rotate bibliomundi-dash"></i> '+bibliomundi_error.txt_import+': '+current_percent.toFixed(0)+'%';
						swal({
							html: true,
							title: '',
							text: loading_text,
							html: true,
							confirmButtonColor: '#e23e57',
							showConfirmButton: false,
							showCancelButton: false,
						});	// percent						
						
						return bibliomundi_import_manual_process_ajax(1);
					}
				}else{
					// error import
					return swal('', ret.message, 'error');
				}
				
				// error import
				return swal('', bibliomundi_error.server_error, 'error');
			},
			error: function(jqXHR, textStatus, errorThrown) {
				var error_msg = textStatus + ': ' + errorThrown;
				console.log(error_msg);
				
				return swal('', error_msg, 'error');
			}
		});
	}
	
	function bibliomundi_import_manual_process_ajax(page){
		page = parseInt(page) || 1;
		
		if(page < 1) page = 1;
		
		$.ajax({
			type: 'POST',
			url: bibliomundi_url.ajax,
			dataType: 'json',
			data: {
				action: 'bibliomundi_import_process',
				page: page,
				manual: true,
			},
			success: function(ret) { 
				if (ret.success){
					var end = ret.end || false;
					var current_percent = ret.current_percent;
					
					if(!end){
						var loading_text = '<i class="dashicons dashicons-image-rotate bibliomundi-dash"></i> '+bibliomundi_error.txt_import+': '+current_percent.toFixed(0)+'%';
						swal({
							html: true,
							title: '',
							text: loading_text,
							html: true,
							confirmButtonColor: '#e23e57',
							showConfirmButton: false,
							showCancelButton: false,
						});	// percent	
						
						return bibliomundi_import_manual_process_ajax(page+1);
					}else{
						// end
						window.onbeforeunload = function (e) {};
						
						return swal('', ret.message, 'success');
					}
				}else{
					// error import
					return swal('', ret.message, 'error');
				}
				
				// error import
				return swal('', bibliomundi_error.server_error, 'error');
			},
			error: function(jqXHR, textStatus, errorThrown) {
				var error_msg = textStatus + ': ' + errorThrown;
				console.log(error_msg);
				
				return swal('', error_msg, 'error');
			}
		});
	}
	
	function bibliomundi_restore(){
		$.ajax({
			type: 'POST',
			url: bibliomundi_url.ajax,
			dataType: 'json',
			data: {
				action: 'bibliomundi_restore',
			},
			success: function(ret) { 
				if (ret.success){
					swal.close();
					
					return window.location.href = bibliomundi_page;
				}else{
					return swal('', ret.message, 'error');
				}
				
				return swal('', bibliomundi_error.server_error, 'error');
			},
			error: function(jqXHR, textStatus, errorThrown) {
				var error_msg = textStatus + ': ' + errorThrown;
				console.log(error_msg);
				
				return swal('', error_msg, 'error');
			}
		});
	}
});

function bibliomundi_loading(){
	return swal({
		html: true,
		title: '',
		text: '<i class="dashicons dashicons-image-rotate bibliomundi-dash"></i> '+bibliomundi_error.loading,
		confirmButtonColor: '#e23e57',
		showConfirmButton: false,
		showCancelButton: false,
	});
}