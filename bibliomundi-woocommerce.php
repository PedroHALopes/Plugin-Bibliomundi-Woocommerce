<?php
/*
*
* Plugin Name: BiblioMundi WooCommerce
* Plugin URI: https://github.com/bibliomundi/client-side-api
* Description: Integração com a BiblioMundi.
* Author: Devinition
* Author URI: https://bibliomundi.com/
* Version: 2.0
* Text Domain: bibliomundi-woocommerce
*
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if(!class_exists( 'WooCommerce_BiblioMundi')){
	class WooCommerce_BiblioMundi {
		protected $url = '', $dir = '', $no_wizard = 0, $per_page = 30,
		$email_support = 'suporte@bibliomundi.com.br',
		$url_store = 'https://bibliomundi.com/store',
		$url_cron = 'https://bibliomundi.com/plugin-support/cron',
		$url_platform = 'https://bibliomundi.com/dashboard/loja/distribuicao',
		$url_credentials = 'https://bibliomundi.com/plugin-support/find-credentials',
		$url_integration = 'https://bibliomundi.com/dashboard/manuais-integracao',
		$url_iframe_faq = 'https://woocommerce.bibliomundi.com/faq',
		$url_iframe_tutorial = 'https://woocommerce.bibliomundi.com/tutoriais',
		$environment = 'production',
		$domain = 'bibliomundi-woocommerce',
		$file_temp_json = 'temp.json',
		$file_cron_json = 'cron.json',
		$file_log = 'bibliomundi.log',
		$_visibility = 20, $_id_type_isbn = 15;
		
		/**
		* Class Construct
		*/
		public function __construct(){
			$this->url = trailingslashit(plugin_dir_url(__FILE__));
			$this->dir = trailingslashit(plugin_dir_path(__FILE__));
			
			// library			
			if (file_exists($this->dir.'libraries/bibliomundi/autoload.php'))
				require_once($this->dir.'libraries/bibliomundi/autoload.php');		
			
			// wp activation/deactivation
			register_activation_hook(__FILE__, array($this, 'plugin_activation'));
			register_deactivation_hook(__FILE__, array($this, 'plugin_deactivation'));
			
			// wp domain
			add_action('init', array($this, 'plugin_load_textdomain'));
			
			// wp route
			add_filter('template_include', array($this, 'plugin_include_template'));
			add_filter('init', array($this, 'plugin_rewrite_rules'));
			
			// settings page
			add_action('admin_menu', array($this, 'plugin_settings_page'));
			
			// woocommerce action
			add_action('woocommerce_order_status_changed', array($this, 'plugin_woocommerce_order_status_changed'), 10, 4);
			add_filter('woocommerce_attribute_label', array($this, 'plugin_woocommerce_attribute_label'));
			
			// ajax
			add_action('wp_ajax_bibliomundi_restore', array($this, 'plugin_ajax_bibliomundi_restore'));
			add_action('wp_ajax_bibliomundi_import', array($this, 'plugin_ajax_bibliomundi_import'));
			add_action('wp_ajax_bibliomundi_import_process', array($this, 'plugin_ajax_bibliomundi_import_process'));
			add_action('wp_ajax_bibliomundi_settings', array($this, 'plugin_ajax_bibliomundi_settings'));
			add_action('wp_ajax_bibliomundi_api_validation', array($this, 'plugin_ajax_bibliomundi_api_validation'));
		}
		
		/**
		* Function activation/deactivation
		*/		
		function plugin_activation(){
			$this->plugin_rewrite_rules();
			$this->plugin_tables();
		}	
		
		function plugin_deactivation(){
			
		}
		
		function plugin_load_textdomain(){
			load_plugin_textdomain('bibliomundi-woocommerce', false, 'bibliomundi-woocommerce/languages');
		}
		
		function plugin_required(){
			return class_exists('WooCommerce');
		}
		
		function plugin_include_template($template){
			$api = get_query_var('bibliomundi-api');

			// api doing sth
			if($api == 'cron') {
				return $this->plugin_cronjob();
			}

			return $template;
		}
		
		function plugin_rewrite_rules(){
			add_rewrite_rule('bibliomundi-cron/?$', 'index.php?bibliomundi-api=cron', 'top');
			add_rewrite_tag('%bibliomundi-api%', '([^&]+)');
			flush_rewrite_rules();
		}
		
		function plugin_writable(){
			return is_writable($this->dir);
		}
		
		function plugin_write_log($message = '', $message_type = 3){
			$plugin_log = $this->dir.$this->file_log;
			$message = '['.date('Y-m-d H:i:s', time()).'] ['.time().'] Log: '.$message.PHP_EOL;
			
			return error_log($message, $message_type, $plugin_log);
		}
		
		function plugin_tables(){
			global $wpdb;
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

			$charset_collate = $wpdb->get_charset_collate();
			
			$sql = "CREATE TABLE $wpdb->posts (
				is_bbm int(4) DEFAULT '0' NOT NULL,
				isbn varchar(100) NULL,
				PRIMARY KEY  (ID)
			) $charset_collate;";
			
			return dbDelta($sql);
		}
		
		function get_file_temp(){
			return $this->dir.$this->file_temp_json;
		}
		
		function get_file_cron(){
			return $this->dir.$this->file_cron_json;
		}
		
		/**
		* Function settings
		*/	
		function set_settings($data){
			unset($data['url_cron']);
			unset($data['url_store']);
			unset($data['url_platform']);
			unset($data['url_integration']);
			unset($data['url_credentials']);
			unset($data['url_iframe_faq']);
			unset($data['url_iframe_tutorial']);
			unset($data['email_support']);
			
			return update_option($this->domain, $data);
		}
		
		function get_settings(){
			$default = $this->get_settings_default();
			$option = get_option($this->domain, $default);
			
			$option['url_cron'] = $this->url_cron;
			$option['url_store'] = $this->url_store;
			$option['url_platform'] = $this->url_platform;
			$option['url_credentials'] = $this->url_credentials;
			$option['url_integration'] = $this->url_integration;
			$option['url_iframe_faq'] = $this->url_iframe_faq;
			$option['url_iframe_tutorial'] = $this->url_iframe_tutorial;
			$option['email_support'] = $this->email_support;
			
			return array_merge($default, $option);
		}
		
		function get_settings_default(){
			return array(
				'is_wizard' => 0,
				'api_key' => '',
				'api_secret' => '',
				'isbn_sku' => 0,
				'category_type' => 0,
				'category_id' => 0,
			);
		}	
		
		function get_plugin_meta(){
			return get_plugin_data($this->dir.$this->domain.'.php', false);
		}
		
		function get_plugin_attr($attr = ''){
			$meta = $this->get_plugin_meta();
			
			return (isset($meta[$attr])) ? $meta[$attr] : '';
		}
		
		function is_url($str){		
			return filter_var($str, FILTER_VALIDATE_URL);
		}
		
		/**
		* Function hook
		*/	
		function plugin_woocommerce_order_status_changed($order_id = 0, $from_status = '', $to_status = '', $instance = false){

			if($to_status == 'completed' && $from_status != 'completed'){				
				$order = new WC_Order($order_id);
				$order_user_id = $order->get_user_id();
				$order_currency = $currency = get_option('woocommerce_currency');	
				$order_user_address = $order->get_address();
				$order_user_name = @$order_user_address['first_name'].' '.@$order_user_address['last_name'];
				$order_items = $order->get_items();

				// data
				$items = array();
				$customer = array(
					'customerIdentificationNumber' => $order_user_id, // INT, YOUR STORE CUSTOMER ID
					'customerFullname' => $order_user_name, // STRING, CUSTOMER FULL NAME
					'customerEmail' => @$order_user_address['email'], // STRING, CUSTOMER EMAIL
					'customerGender' => 'm', // ENUM, CUSTOMER GENDER, USE m OR f (LOWERCASE!! male or female)
					'customerBirthday' => date('Y/m/d'), // STRING, CUSTOMER BIRTH DATE, USE Y/m/d (XXXX/XX/XX)
					'customerCountry' => @$order_user_address['country'], // STRING, 2 CHAR STRING THAT INDICATE THE CUSTOMER COUNTRY (BR, US, ES, etc)
					'customerZipcode' => @$order_user_address['postcode'], // STRING, POSTAL CODE, ONLY NUMBERS
					'customerState' => @$order_user_address['state'], // STRING, 2 CHAR STRING THAT INDICATE THE CUSTOMER STATE (RJ, SP, NY, etc)
				);
				
				if(count($order_items) > 0){
					foreach($order_items as $order_item){
						$product_id = (int) @$order_item['product_id'];
						$qty = (int) @$order_item['qty'];
						$line_total = (float) @$order_item['line_total'];
						$id_bibliomundi = get_post_meta($product_id, 'id_bibliomundi', true);
						
						if($id_bibliomundi != '' && $qty > 0){
							for($i = 0; $i < $qty; $i++){
								array_push(
									$items,
									array(
										'id_bibliomundi' => $id_bibliomundi,
										'price' => $line_total,
										'currency' => $order_currency,
									)
								);
							}
						}
					}
				}

				$result = $this->retrieve_order($order_id, $customer, $items);
			}
		}
		
		function plugin_woocommerce_attribute_label($label = ''){
			switch($label){
				case 'contributor':
					$label = __('Contributor', 'bibliomundi-woocommerce');
					break;
					
				case 'epub_technical_protection':
					$label = __('Technical Protection', 'bibliomundi-woocommerce');
					break;
					
				case 'isbn':
					$label = __('ISBN', 'bibliomundi-woocommerce');
					break;
					
				case 'language':
					$label = __('Language', 'bibliomundi-woocommerce');
					break;
					
				case 'publishers':
					$label = __('Publishers', 'bibliomundi-woocommerce');
					break;
					
				case '_sku':
					$label = __('SKU', 'bibliomundi-woocommerce');
					break;
			}
			
			return $label;
		}
		
		/**
		* Function retrieve
		*/	
		
		function retrieve_order($order_id = 0, $customer = array(), $books = array(), $plugin_settings = false){
			if(!$plugin_settings){
				$plugin_settings = $this->get_settings();
			}
			
			$api_key = sanitize_text_field($plugin_settings['api_key']);
			$api_secret = sanitize_text_field($plugin_settings['api_secret']);
			
			if(count($books) > 0){
				try{
					$purchase = new BBM\Purchase($api_key, $api_secret);
					$purchase->environment = $this->environment;
					
					$purchase->setCustomer($customer);
					
					foreach($books as $book){
						$purchase->addItem($book['id_bibliomundi'], $book['price'], $book['currency']);
					}
					
					$valid = $purchase->validate();
				
					if($valid){
						$transaction_key = 'TRANSACTION_'.$order_id;
						$response = $purchase->checkout($transaction_key, time());
						
						$response = json_decode($response, true);
						
						if(@$response['code'] == '201'){
							$this->plugin_write_log('Purchase process order # '. $order_id .': Done. ' .json_encode($response));
							return true;
						}else{
							$this->plugin_write_log('Purchase process order # '. $order_id. ': Error. ' .json_encode($response));
							return false;
						}
					}
				}catch(\BBM\Server\Exception $e){
					$error_msg = $e->getMessage();
					
					if($error_msg == '')
						$error_msg = __('Cannot connect to the API.', 'bibliomundi-woocommerce');
					
					$this->plugin_write_log('Purchase process: '.$error_msg);
					return false;
				}
			}else{
				$this->plugin_write_log('Purchase process: No item found.');
				return false;
			}
		}
		
	
		function get_import_continue_status(){
			$temp_file = $this->get_file_temp();		
			
			if(!file_exists($temp_file)) return false;
			
			$data_books = file_get_contents($temp_file);
			$data_books = json_decode($data_books, true);
			$data_books_page = (int) @$data_books['page'];
			$data_books_updated_at = @$data_books['updated_at'];
			
			if($data_books_page <= 0){
				if((int) strtotime($data_books_updated_at) <= 0) return false;
			}else{
				$now = date('Y-m-d H:i:s', time());
				$limit_date = strtotime($now.' -7 days');
				
				if((int) strtotime($data_books_updated_at) < $limit_date) return false;
			}
			
			return true;
		}
		
		function get_books($data = array(), $plugin_settings = false){
			if(!$plugin_settings){
				$plugin_settings = $this->get_settings();
			}
			
			$books = array();
			
			if(count($data) > 0){
				foreach($data as $data_item){
					$book = $this->get_book($data_item, $plugin_settings);
					
					if($book){
						array_push($books, $book);
					}
				}
			}
			
			return $books;
		}
		
		function get_book($data = false, $plugin_settings = false){
			if(!$data){
				return false;
			}
			
			if(!$plugin_settings){
				$plugin_settings = $this->get_settings();
			}
			
			$book = array(
				'post_type'    => 'product',
				'post_status'  => 'publish',
				'categories' => array(),
				'post_metas' => array(),
				'product_attributes' => array(), // for _product_attributes
			);
			
			// title
			$title = $data->getTitle();
			$prefix = @$title->getPrefix();
			$book['post_title'] = trim($prefix.' '.@$title->getWithoutPrefix());
			
			// content
			$book['post_content'] = trim($data->getSynopsis());
			
			// thumbnails
			$thumbnails = trim($data->getUrlFile());
			
			if($thumbnails != ''){
				$thumbnails = ($this->is_url($thumbnails)) ? $thumbnails : 'https://'.$thumbnails;
			}
			
			$book['thumbnails'] = $thumbnails;
			
			// categories
			$this->get_book_categories($data, $book, $plugin_settings);
			
			// post metas
			$this->get_book_meta($data, $book, $plugin_settings);
			
			// product attributes
			$this->get_book_attributes($data, $book, $plugin_settings);
			
			return $book;
		}
		
		function get_book_categories($data = false, &$book = array(), $plugin_settings = false){
			if(!$data){
				return false;
			}
			
			if(!$plugin_settings){
				$plugin_settings = $this->get_settings();
			}
			
			$categories = array();
			$subject = $data->getCategories();
			
			if(count($subject) > 0){
				foreach($subject as $item){					
					array_push(
						$categories,
						array(
							'name' => $item->getName(),
							'code' => $item->getCode(),
							'identifier' => $item->getIdentifier(),
							'taxonomy' => 'product_cat',
						)
					);
				}
			}
			
			$book['categories'] = $categories;
		}
		
		function get_book_meta($data = false, &$book = array(), $plugin_settings = false){
			if(!$data){
				return false;
			}
			
			if(!$plugin_settings){
				$plugin_settings = $this->get_settings();
			}
			
			$prices = array();
			$currency = get_option('woocommerce_currency');			
			
			if(count($data->getPrices()) > 0){
				foreach ($data->getPrices() as $data_price){
					$prices[strval($data_price->getCurrency())] = strval($data_price->getAmount());
				}
			}
			
			if($currency && in_array($currency, array_keys($prices))){
			    $price = (float) $prices[$currency];
			    $iso_code = $currency;
			}else{
			    $price = (float) $prices['BRL'];
			    $iso_code = 'BRL';
			}
			
			$contributor_name = array();
			$contributors = $data->getContributors();
			if(count($contributors) > 0){
				foreach($contributors as $contributor){
					array_push($contributor_name, $contributor->getName());
				}
			}
			
			$epub_technical_protection = $data->getProtectionType();
			switch ($epub_technical_protection) {
                case '01' :
                    $epub_technical_protection = 'Social DRM';
                    break;
                case '02' :
                    $epub_technical_protection = 'Adobe DRM';
                    break;
                default:
                    $epub_technical_protection = 'No DRM';
            }
									
			$metas = array(
				'notification_type'         => $data->getOperationType(),
				'id_bibliomundi'            => $data->getId(),
				'id_ebook'                  => $data->getId(),
				'subtitle'                  => $data->getSubTitle(),
				'edition_number'            => $data->getEditionNumber(),
				'iso_code'                  => $iso_code,
				'_visibility'               => ($data->isAvailable()) ? 'visible' : 'hidden',
				'_manage_stock'             => 'no',
				'_stock_status'             => ($data->isAvailable()) ? 'instock' : 'outofstock',
				'_regular_price'            => $price,
				'_price'                    => $price,
				'publishers'                => $data->getImprintName(),
				'_virtual'                  => 'yes',
				'_downloadable'             => 'yes',
				'_downloadable_files'       => '',
				'currency'                  => $iso_code,
				'contributor'               => implode(', ', $contributor_name),
                'language'                  => $data->getIdiom(),
                'size'                      => $data->getSize(),
                'size_unit'                 => $data->getSizeUnit(),
                'page_number'               => $data->getPageNumbers(),
				'isbn'                      => $data->getISBN(),
				'_sku'                      => '',
                'epub_technical_protection' => $epub_technical_protection,
			);
			
			$book['isbn'] = $metas['isbn'];
			$book['post_metas'] = $metas;
		}	
		
		function get_book_attributes($data = false, &$book = array(), $plugin_settings = false){
			if(!$data){
				return false;
			}
			
			if(!$plugin_settings){
				$plugin_settings = $this->get_settings();
			}
			
			$attributes = array(
				'currency'			         => $book['post_metas']['currency'],
				'edition_number'             => $book['post_metas']['edition_number'],
				'id_bibliomundi'             => $book['post_metas']['id_bibliomundi'],
				'id_ebook'                   => $book['post_metas']['id_ebook'],
				'isbn'                       => $book['post_metas']['isbn'],
                'contributor'                => $book['post_metas']['contributor'],
				'iso_code'		             => $book['post_metas']['iso_code'],
				'notification_type'          => $book['post_metas']['notification_type'],
				'publishers'                 => $book['post_metas']['publishers'],
				'subtitle'                   => $book['post_metas']['subtitle'],
				'language'                   => $book['post_metas']['language'],
				'size'                       => $book['post_metas']['size'],
				'size_unit'                  => $book['post_metas']['size_unit'],
				'page_number'                => $book['post_metas']['page_number'],
				'_sku'                       => '',
				'epub_technical_protection'  => $book['post_metas']['epub_technical_protection'],
			);
			
			$book['product_attributes'] = $attributes;
		}

		function import_ebook_item($book_data, $plugin_settings = false){
			global $wpdb;
			
			if(!$plugin_settings){
				$plugin_settings = $this->get_settings();
			}
			
			// insert post
			$post_data = array(
				'post_title' => trim($book_data['post_title']),
				'post_content' => trim($book_data['post_content']),
				'post_type' => $book_data['post_type'],
				'post_status' => $book_data['post_status'],
			);
			
			$post_id = wp_insert_post($post_data);
			$book_data['post_id'] = $post_id;
			
			// book data
			return $this->update_ebook_item_data($book_data, true, $plugin_settings);
		}	
		
		function update_ebook_item($book_data, $plugin_settings = false){
			global $wpdb;
			
			if(!$plugin_settings){
				$plugin_settings = $this->get_settings();
			}
			
			$isbn = (isset($book_data['post_metas']['isbn'])) ? $book_data['post_metas']['isbn'] : @$book_data['isbn'];
			
			if($isbn == ''){
				return $this->import_ebook_item($book_data, $plugin_settings);
			}
			
			$sql = 'SELECT ID FROM '.$wpdb->posts.' WHERE isbn = "'.$isbn.'"';
			$post_id = (int) @$wpdb->get_var($sql);
			
			if($post_id == 0){
				return $this->import_ebook_item($book_data, $plugin_settings);
			}
			
			// check post
			$post = get_post($post_id);
			
			if(!$post){
				return $this->import_ebook_item($book_data, $plugin_settings);
			}
			
			// update post
			$post_data = array(
				'ID' => $post_id,
				'post_title' => trim($book_data['post_title']),
                'post_content' => trim($book_data['post_content']),
			);
			
			wp_update_post($post_data);
			$book_data['post_id'] = $post_id;
			
			// book data
			return $this->update_ebook_item_data($book_data, false, $plugin_settings); 
		}
		
		function update_ebook_item_data($book_data, $thumbnails = true, $plugin_settings = false){
			global $wpdb;
			
			if(!$plugin_settings){
				$plugin_settings = $this->get_settings();
			}
			
			// col
			$wpdb->update(
				$wpdb->posts,
				array(
					'isbn' => $book_data['isbn'],
					'is_bbm' => 1,
				),
				array(
					'ID' => $book_data['post_id'],
				)
			);
			
			// thumbnail image
			if($book_data['thumbnails'] != ''){
				$this->update_ebook_thumbnail($book_data['thumbnails'], $book_data['post_id'], $book_data['post_title'], $thumbnails);
			}
			
			// categories
			if(count($book_data['categories']) > 0){
				foreach($book_data['categories'] as $category){
					$this->update_ebook_category($book_data['post_id'], $category, $plugin_settings);
				}
			}
			
			// metas
			$this->update_ebook_metas($book_data, $plugin_settings);
		}

		function update_ebook_thumbnail($file_url = '', $post_id = 0, $desc = '', $thumbnails = true){
			if(!$thumbnails){
				$thumb_id = get_post_thumbnail_id($post_id);
				
				if($thumb_id){
					return $thumb_id;
				}
			}
			
			try{
				if(!function_exists('media_handle_upload')){
					require_once(ABSPATH . 'wp-admin' . '/includes/image.php');
					require_once(ABSPATH . 'wp-admin' . '/includes/file.php');
					require_once(ABSPATH . 'wp-admin' . '/includes/media.php');
				}
				
				preg_match('/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file_url, $matches);
				
				if (!$matches){
					$this->plugin_write_log('Import process: ['.$file_url.'] '.__('Invalid image URL'));
					return 0;
				}
				
				$file_array = array();
				$file_array['name'] = sanitize_title($desc.'-'.time()).'.'.$matches[1];

				// Download file to temp location.
				$file_array['tmp_name'] = download_url($file_url);
				
				// If error storing temporarily, return the error.
				if(is_wp_error($file_array['tmp_name'])){
					return $file_array['tmp_name'];
				}

				// Do the validation and storage stuff.
				$id = media_handle_sideload($file_array, $post_id, $desc);

				// If error storing permanently, unlink.
				if (is_wp_error($id)){
					@unlink($file_array['tmp_name']);
					
					return $id;
				}
				
				return set_post_thumbnail($post_id, $id);
			}catch(Exception $e){
				$this->plugin_write_log('Import process: ['.$file_url.'] '.$e->getMessage());
				
				return 0;
			}
		}
		
		function update_ebook_category($post_id = 0, $category, $plugin_settings = false){
			if(!$plugin_settings){
				$plugin_settings = $this->get_settings();
			}
			
			if($category['name'] == '') return false;
			
			// check existed
			$term = term_exists($category['name'], $category['taxonomy']);
			
			if(!$term){
				$term = wp_insert_term($category['name'], $category['taxonomy']);
			}
			
			$term_id = $term['term_id'];			
			
			// SETTING category type
			$args = array(
				'parent' => 0
			);
			
			if($term_id > 0){
				switch($plugin_settings['category_type']){
					case '0': // I only sell eBooks. 
					case '2': // I do not sell eBooks yet. 
						wp_update_term($term_id, $category['taxonomy'], $args);
						break;
						
					case '1': // 	I sell eBooks and/or other products.
						if($plugin_settings['category_id'] > 0){
							$args['parent'] = $plugin_settings['category_id'];
						}
						
						wp_update_term($term_id, $category['taxonomy'], $args);
						break;
				}
				
				// set category
				wp_set_post_terms($post_id, $term_id, $category['taxonomy']);
			}
		}
		
		function update_ebook_metas($post, $plugin_settings = false){
			if(!$plugin_settings){
				$plugin_settings = $this->get_settings();
			}
			
			$post_id = $post['post_id'];
			$post_metas = $post['post_metas'];
			$product_attributes = $post['product_attributes'];
			
			// SETTING sku data
			switch($plugin_settings['isbn_sku']){
				case '0': // Set ISBN as SKU
					$post_metas['_sku'] = $post_metas['isbn'];
					break;
					
				case '1': // Set BiblioMundi ID as SKU
					$post_metas['_sku'] = $post_metas['id_bibliomundi'];
					break;
			}
			
			$product_attributes['_sku'] = $post_metas['_sku'];
			
			// post metas
			if(count($post_metas) > 0){
				foreach($post_metas as $meta_key=>$meta_value){
					update_post_meta($post_id, $meta_key, $meta_value);
				}
			}
			
			// product attributes
			$product_attributes_args = array();
			
			if(count($product_attributes) > 0){
				foreach($product_attributes as $key=>$value){
					$is_visible = (in_array($key, array('language', 'extent', 'publishers', 'epub_technical_protection', 'isbn', '_sku', 'contributor'))) ? 1 : 0;
					
					array_push(
						$product_attributes_args,
						array (
							'name' => $key,
							'value' => $value,
							'position' => 1,
							'is_visible' => $is_visible,
							'is_variation' => 1,
							'is_taxonomy' => 0
						)
					);
				}
				
				update_post_meta($post_id, '_product_attributes', $product_attributes_args);
			}
		}
		
		/**
		* Function cronjob
		*/
		function plugin_cronjob(){
			$plugin_settings = $this->get_settings();
			$cron_file = $this->get_file_cron();
			
			if(!is_writable($this->dir)){
				$this->plugin_write_log('Cron process: Cannot write to the plugin directory ('.$this->dir.').');
				exit;
			}
			
			if(!file_exists($cron_file)){
				$this->plugin_write_log('Cron process: File cron is not existed ('.$cron_file.'). Start to call API for catalogs.');
				
				$this->plugin_cronjob_api_book($plugin_settings);
				exit;
			}
			
			$data = file_get_contents($cron_file);
			$data = json_decode($data, true);
			
			if(!$data){
				$this->plugin_write_log('Cron process: JSON file is empty. Start to call API for catalogs.');
				
				$this->plugin_cronjob_api_book($plugin_settings);
				exit;
			}
			
			$books = isset($data['books']) ? (array) $data['books'] : array();
			$count_books = count($books);
			
			if($count_books == 0){
				$this->plugin_write_log('Cron process: JSON file contains empty data. Start to call API for catalogs.');
				
				$this->plugin_cronjob_api_book($plugin_settings);
				exit;
			}
			
			$page = (int) @$data['page'] + 1;
			$pages = (int) @$data['pages'];
			
			if($page > $pages){
				$this->plugin_write_log('Cron process: Updated completed. Start to call API for catalogs.');
				
				$this->plugin_cronjob_api_book($plugin_settings);
				exit;
			}
			
			$data['page'] = $page;
			$this->plugin_cronjob_catalogs($data, $plugin_settings);

			//update books data in cron.json
            $this->plugin_cronjob_api_book($plugin_settings,$page);
			exit;
		}
		
		function plugin_cronjob_api_book($plugin_settings = false, $current_page = 1){
			if(!$plugin_settings){
				$plugin_settings = $this->get_settings();
			}
			
			$api_key = sanitize_text_field($plugin_settings['api_key']);
			$api_secret = sanitize_text_field($plugin_settings['api_secret']);
			$cron_file = $this->get_file_cron();
			
			if($api_key == '' || $api_secret == ''){
				$this->plugin_write_log('Cron process: ID or Client Key is empty.');
				exit;
			}
			
			try{
				$catalog = new BBM\Catalog($api_key, $api_secret);
				$catalog->environment = $this->environment;
				$valid = $catalog->validate();
				
				if($valid){
					$this->plugin_write_log('Cron process: API Authenticated.');

//                    //hardcode json format call api
                    $json_book_current_page =  $current_page;
                    $json_book_per_page =  $this->per_page;
                    $catalog_format = 'json';
                    $catalog->filters(array(
                        'page' => $json_book_current_page,
                        'per_page' => $json_book_per_page,
                        'catalog_format' => $catalog_format
                    ));

					$xml = $catalog->get();		
					
					if(!$xml){
						$this->plugin_write_log('Cron process: API returns empty data.');
						exit;
					}

                    $parser = new BBMParser\Parser($xml,$catalog_format);
                    $books = $parser->getParserObject()->getProducts();
                    $books = $this->get_books($books, $plugin_settings);
					$count_books = count($books);
					if($count_books > 0){
						$now = date('Y-m-d H:i:s', time());
						switch ($catalog_format){
                            case 'xml':
                                $pages = ($count_books % $this->per_page == 0) ? ($count_books / $this->per_page) : ((int) ($count_books / $this->per_page) + 1);
                                break;
                            case 'json':
                                $header = $parser->getParserObject()->getHeader();
                                $message_note = ($header->getMessage());
                                $message_note = str_replace("'", '"' , $message_note);
                                $message_note = json_decode($message_note,true);

                                $pages = $message_note['qtyPages'];
                                break;
                        }

						$data = array(
							'api_at' => $now,
							'updated_at' => $now,
							'page' => $json_book_current_page,
							'pages' => $pages,
							'books' => $books,
						);

						$fp = fopen($cron_file, 'w');
						fwrite($fp, json_encode($data));
						fclose($fp);
						
						$this->plugin_write_log('Cron process: API returns data successfully.');
						exit;
					}else{				
						$this->plugin_write_log('Cron process: API returns empty data.');
						exit;
					}
				}
			}catch(\BBM\Server\Exception $e){
				$error_msg = $e->getMessage();
				
				if($error_msg == '')
					$error_msg = __('Cannot connect to the API.', 'bibliomundi-woocommerce');
				
				$this->plugin_write_log('Cron process: '.$error_msg);
				exit;
			}
			
			$this->plugin_write_log('Cron process: Client ID or Client Key is wrong.');
			exit;
		}
		
		function plugin_cronjob_catalogs($data, $plugin_settings = false){
			if(!$plugin_settings){
				$plugin_settings = $this->get_settings();
			}
			
			$cron_file = $this->get_file_cron();
			$page = (int) @$data['page'];
			$pages = (int) @$data['pages'];
			$books = isset($data['books']) ? (array) $data['books'] : array();
			$count_books = count($books);
			
			if($page < 1)
				$page = 1;
			
//			$min_item = ($page - 1) * $this->per_page;
//			$max_item = $page * $this->per_page;


            //import with json format
            $min_item = 0;
            $max_item = $count_books;


			if($max_item > $count_books)
				$max_item = $count_books;
			
			for($i = $min_item; $i < $max_item; $i++){
				if(!isset($books[$i]))
					break;
				
				$book = $books[$i];
				
				$this->plugin_cronjob_catalog_item($book, $plugin_settings);
			}
			
//			// update json
//			$now = date('Y-m-d H:i:s', time());
//			$data['updated_at'] = $now;
//
//			$fp = fopen($cron_file, 'w');
//			fwrite($fp, json_encode($data));
//			fclose($fp);
			
			$this->plugin_write_log('Cron process: Updated page '.$page.'/'.$pages.' successfully.');
//			exit;
		}
		
		function plugin_cronjob_catalog_item($book_data, $plugin_settings = false){
			if(!$plugin_settings){
				$plugin_settings = $this->get_settings();
			}
			
			$this->update_ebook_item($book_data, $plugin_settings);
			
			return $this->plugin_write_log('Cron process: Updated book ['.@$book_data['post_title'].'] successfully.');
		}
		
		/**
		* Function ajax
		*/
		function plugin_ajax_bibliomundi_api_validation(){
			$api_key = sanitize_text_field(@$_POST['api_key']);
			$api_secret = sanitize_text_field(@$_POST['api_secret']);
			
			if($api_key == '' || $api_secret == ''){
				wp_send_json(
					array(
						'success' => false,
						'message' => __('Client ID or Client Key is empty.', 'bibliomundi-woocommerce'),
					)
				);
			}
			
			try{
				$catalog = new BBM\Catalog($api_key, $api_secret);
				$catalog->environment = $this->environment;
				$valid = $catalog->validate();
				
				if($valid){
					$plugin_settings = $this->get_settings();
					$plugin_settings['api_key'] = $api_key; 
					$plugin_settings['api_secret'] = $api_secret;
					
					$this->set_settings($plugin_settings);
					
					wp_send_json(
						array(
							'success' => true,
							'api_key' => $api_key,
							'api_secret' => $api_secret,
							'message' => __('Credentials is valid.', 'bibliomundi-woocommerce'),
						)
					);
				}
			}catch(Exception $e){
				wp_send_json(
					array(
						'success' => false,
						'message' => __('Invalid Credentials.', 'bibliomundi-woocommerce'),
					)
				);
			}
			
			wp_send_json(
				array(
					'success' => false,
					'message' => __('Client ID or Client Key is wrong.', 'bibliomundi-woocommerce'),
				)
			);
		}
		
		function plugin_ajax_bibliomundi_settings(){
			$settings = (isset($_POST['settings'])) ? (array) $_POST['settings'] : array();
			
			if(count($settings) > 0){
				$plugin_settings = $this->get_settings();
				
				foreach($settings as $key=>$value){
					if(isset($plugin_settings[$key])){
						$plugin_settings[$key] = $value;
					}
				}
				
				$this->set_settings($plugin_settings);
			}
			
			wp_send_json(
				array(
					'success' => true,
					'message' => __('Saved successfully.', 'bibliomundi-woocommerce'),
				)
			);
		}
		
		function plugin_ajax_bibliomundi_restore(){
			global $wpdb;
			
			// restore settings
			$plugin_settings = $this->get_settings_default();
			$this->set_settings($plugin_settings);

            //delete json_book_current_page and json_book_total_page options
            delete_option( 'json_book_current_page' );
            delete_option( 'json_book_total_page' );

			// remove products
			$wpdb->delete(
				$wpdb->posts,
				array(
					'post_type' => 'product',
					'is_bbm' => 1,
				)
			);
			
			wp_send_json(
				array(
					'success' => true,
				)
			);
		}
		
		function plugin_ajax_bibliomundi_import(){
			$plugin_settings = $this->get_settings();
			$api_key = sanitize_text_field($plugin_settings['api_key']);
			$api_secret = sanitize_text_field($plugin_settings['api_secret']);
			$temp_file = $this->get_file_temp();			
			$manual = isset($_REQUEST['manual']) ? (bool) $_REQUEST['manual'] : false;
			$continue_status = isset($_REQUEST['continue_status']) ? ($_REQUEST['continue_status'] == 'true') : false;

			if($api_key == '' || $api_secret == ''){
				$this->plugin_write_log('Import process: ID or Client Key is empty.');
				
				wp_send_json(
					array(
						'success' => false,
						'message' => __('Client ID or Client Key is empty.', 'bibliomundi-woocommerce'),
					)
				);
			}
			
			if(!is_writable($this->dir)){
				$this->plugin_write_log('Import process: Cannot write to the plugin directory ('.$this->dir.').');
				
				wp_send_json(
					array(
						'success' => false,
						'message' => sprintf(
							__('Cannot write to the plugin directory (%s).', 'bibliomundi-woocommerce'), 
							$this->dir
						),
					)
				);
			}

//            if(!$manual && $continue_status && $this->get_import_continue_status()){ // continue import
//				$data_books = file_get_contents($temp_file);
//				$data_books = json_decode($data_books, true);
//				$books = (isset($data_books['books'])) ? $data_books['books']: array();
//				$count_books = count($books);
//				$current_page = (int) $data_books['page'];
//
//				// count
//				$pages = ($count_books % $this->per_page == 0) ? ($count_books / $this->per_page) : ((int) ($count_books / $this->per_page) + 1);
//				$current_percent = (100 / ($pages + 1)) * $current_page;
//
//				return wp_send_json(
//					array(
//						'success' => true,
//						'books' => $count_books,
//						'pages' => $pages,
//						'current_page' => $current_page,
//						'current_percent' => ($current_percent < 1) ? 1 : $current_percent,
//					)
//				);
//			}



			try{
				$catalog = new BBM\Catalog($api_key, $api_secret);
				$catalog->environment = $this->environment;
				$valid = $catalog->validate();

				if($valid){

				    //hardcode json format call api
				    $json_book_current_page = (get_option( 'json_book_current_page' ) !== false) ? get_option( 'json_book_current_page' ) + 1 : 1;
				    $json_book_per_page = (get_option( 'json_book_per_page' ) !== false) ? get_option( 'json_book_per_page' ) : $this->per_page;
				    $catalog_format = 'json';
				    $catalog->filters(array(
				        'page' => $json_book_current_page,
                        'per_page' => $json_book_per_page,
                        'catalog_format' => $catalog_format
                    ));

					$xml = $catalog->get();

					if(!$xml){
						$this->plugin_write_log('Import process: API returns empty data.');

						wp_send_json(
							array(
								'success' => false,
								'message' => __('Catalog is empty.', 'bibliomundi-woocommerce'),
							)
						);
					}

                    $parser = new BBMParser\Parser($xml,$catalog_format);
                    $books = $parser->getParserObject()->getProducts();
                    echo "<Pre>";
                    print_r($books);
                    $books = $this->get_books($books, $plugin_settings);
                    echo "<Pre>";
                    print_r($books);die();
					$count_books = count($books);
					
					if($count_books > 0){	
						$data = array(
							'books' => $books,
							'page' => 0,
							'updated_at' => date('Y-m-d H:i:s', time()),
						);
						$fp = fopen($temp_file, 'w');
						fwrite($fp, json_encode($data));
						fclose($fp);

						// count
						$pages = ($count_books % $this->per_page == 0) ? ($count_books / $this->per_page) : ((int) ($count_books / $this->per_page) + 1);
						$current_percent = (100 / ($pages + 1));

                        if($catalog_format == 'json'){
                            $header = $parser->getParserObject()->getHeader();

                            $message_note = ($header->getMessage());
                            $message_note = str_replace("'", '"' , $message_note);
                            $message_note = json_decode($message_note,true);



                            if(get_option( 'json_book_current_page' ) !== false){
                                update_option('json_book_current_page',$json_book_current_page);
                            }
                            else{
                                add_option('json_book_current_page',$json_book_current_page);
                            }

                            if(get_option( 'json_book_total_page' ) !== false){
                                update_option('json_book_total_page',$message_note['qtyPages']);
                            }
                            else{
                                add_option('json_book_total_page',$message_note['qtyPages']);
                            }
                            $pages = $message_note['qtyPages'];
                            $current_percent = (int)(($json_book_current_page/$pages) * 100);
                        }

						$this->plugin_write_log('Import process: API returns data successfully.');
						
						wp_send_json(
							array(
								'success' => true,
								'books' => $count_books,
								'pages' => $pages,
								'current_page' => 1,
								'current_percent' => ($current_percent < 1) ? 1 : $current_percent,
							)
						);
					}else{				
						$this->plugin_write_log('Import process: API returns empty data.');
						
						wp_send_json(
							array(
								'success' => false,
								'message' => __('Catalog is empty.', 'bibliomundi-woocommerce'),
							)
						);
					}
				}
			}catch(\BBM\Server\Exception $e){
				$error_msg = $e->getMessage();
				
				if($error_msg == '')
					$error_msg = __('Cannot connect to the API.', 'bibliomundi-woocommerce');
				
				$this->plugin_write_log('Import process: '.$error_msg);
				
				wp_send_json(
					array(
						'success' => false,
						'message' => __('Error', 'bibliomundi-woocommerce').': '.$error_msg,
					)
				);
			}
			
			$this->plugin_write_log('Import process: Client ID or Client Key is wrong.');
			
			wp_send_json(
				array(
					'success' => false,
					'message' => __('Client ID or Client Key is wrong.', 'bibliomundi-woocommerce'),
				)
			);
		}
		
		function plugin_ajax_bibliomundi_import_process(){
			$plugin_settings = $this->get_settings();
            $api_key = sanitize_text_field($plugin_settings['api_key']);
            $api_secret = sanitize_text_field($plugin_settings['api_secret']);
			$temp_file = $this->get_file_temp();			
			$manual = isset($_REQUEST['manual']) ? (bool) $_REQUEST['manual'] : false;

            //hardcode json format call api
            $json_book_current_page = (get_option( 'json_book_current_page' ) !== false) ? get_option( 'json_book_current_page' ) + 1 : 1;
            $json_book_per_page = (get_option( 'json_book_per_page' ) !== false) ? get_option( 'json_book_per_page' ) : $this->per_page;
            $json_book_total_page = (get_option( 'json_book_total_page' ) !== false) ? get_option( 'json_book_total_page' ) : 0;

            $catalog_format = 'json';

			if(!file_exists($temp_file)){
				$this->plugin_write_log('Import process: JSON file not found ('.$temp_file.').');
				
				wp_send_json(
					array(
						'success' => false,
						'message' => __('File not found.', 'bibliomundi-woocommerce'),
					)
				);
			}

			$data_books = file_get_contents($temp_file);
			$data_books = json_decode($data_books, true);
			$books = (isset($data_books['books'])) ? $data_books['books']: array();
			$count_books = count($books);
			
			if($count_books == 0){
				$this->plugin_write_log('Import process: JSON file contains empty data.');
				
				wp_send_json(
					array(
						'success' => false,
						'message' => __('There is no book.', 'bibliomundi-woocommerce'),
					)
				);
			}
			
			// import
			$page = (int) @$_POST['page'];
			if($page < 1)
				$page = 1;

			$pages = ($count_books % $this->per_page == 0) ? ($count_books / $this->per_page) : ((int) ($count_books / $this->per_page) + 1);
			$current_percent = ((($page + 1) * 100) / ($pages + 1));

            if($catalog_format == 'json'){
                $page = $json_book_current_page;
                $pages = $json_book_total_page;
                $current_percent = (int)(($json_book_current_page/$pages) * 100);
            }

			if($current_percent > 100)
				$current_percent = 100;
			
			$end = ($page >= $pages || $current_percent == 100);
			
			if($end){ // end
				$this->plugin_write_log('Import process: Import complete.');

				//delete json_book_current_page and json_book_total_page options
                delete_option( 'json_book_current_page' );
                delete_option( 'json_book_total_page' );

				//
				$data_books['page'] = 0;
				$data_books['updated_at'] = date('Y-m-d H:i:s', time());
				$fp = fopen($temp_file, 'w');
				fwrite($fp, json_encode($data_books));
				fclose($fp);
				
				wp_send_json(
					array(
						'success' => true,
						'books' => $count_books,
						'current_percent' => 100,
						'end' => true,
						'message' => __('Already imported.', 'bibliomundi-woocommerce'),
					)
				);

			}
			
			$min_item = 0;
			$max_item = $json_book_per_page;
			
			if($max_item > $count_books)
				$max_item = $count_books;
			
			for($i = $min_item; $i < $max_item; $i++){
				if(!isset($books[$i]))
					break;
				
				$book = $books[$i];
				
				$this->update_ebook_item($book, $plugin_settings);
				
				$this->plugin_write_log('Import process: Updated book ['.@$book['post_title'].'] successfully.');
			}
			
			$this->plugin_write_log('Import process: Import percent '.$current_percent.'.');


            $catalog = new BBM\Catalog($api_key, $api_secret);
            $catalog->environment = $this->environment;
            $valid = $catalog->validate();

            if($valid) {

                $catalog->filters(array(
                    'page' => $json_book_current_page,
                    'per_page' => $json_book_per_page,
                    'catalog_format' => $catalog_format
                ));
                $xml = $catalog->get();

                if (!$xml) {
                    $this->plugin_write_log('Import process: API returns empty data.');

                    wp_send_json(
                        array(
                            'success' => false,
                            'message' => __('Catalog is empty.', 'bibliomundi-woocommerce'),
                        )
                    );
                }

                $parser = new BBMParser\Parser($xml, $catalog_format);
                $books = $parser->getParserObject()->getProducts();
                $books = $this->get_books($books, $plugin_settings);


                $count_books = count($books);

                if ($count_books > 0) {
                    $data = array(
                        'books' => $books,
                        'page' => 0,
                        'updated_at' => date('Y-m-d H:i:s', time()),
                    );
                    $fp = fopen($temp_file, 'w');
                    fwrite($fp, json_encode($data));
                    fclose($fp);


                    // count
                    $pages = ($count_books % $this->per_page == 0) ? ($count_books / $this->per_page) : ((int)($count_books / $this->per_page) + 1);
                    $current_percent = (100 / ($pages + 1));

                    if ($catalog_format == 'json') {
                        if (get_option('json_book_current_page') !== false) {
                            update_option('json_book_current_page', $json_book_current_page);
                        } else {
                            add_option('json_book_current_page', $json_book_current_page);
                        }
                        $pages = $json_book_total_page;
                        $current_percent = (int)(($json_book_current_page / $pages) * 100);
                    }

                }

            }

			//
//			$data_books['page'] = ($page + 1);
//			$data_books['updated_at'] = date('Y-m-d H:i:s', time());
//			$fp = fopen($temp_file, 'w');
//			fwrite($fp, json_encode($data_books));
//			fclose($fp);
			
			wp_send_json(
				array(
					'success' => true,
					'books' => $count_books,
					'current_percent' => $current_percent,
					'end' => $end,
					'message' => ($end) ? __('Already imported.', 'bibliomundi-woocommerce') : '',
				)
			);			
		}
		
		/**
		* Function pages
		*/	
		function plugin_settings_page(){
			$plugin_settings = $this->get_settings();
			
			add_menu_page(__('Settings', 'bibliomundi-woocommerce'), __('BiblioMundi', 'bibliomundi-woocommerce'), 'manage_options', $this->domain, array($this, 'plugin_layout_options'), 'dashicons-welcome-add-page');
			add_submenu_page($this->domain, __('Settings', 'bibliomundi-woocommerce'), __('Settings', 'bibliomundi-woocommerce'), 'manage_options', $this->domain, array($this, 'plugin_layout_options'));
			add_submenu_page($this->domain, __('Video Tutorials', 'bibliomundi-woocommerce'), __('Tutorials', 'bibliomundi-woocommerce'), 'manage_options', $this->domain.'-tutorials', array($this, 'plugin_layout_tutorials'));
			add_submenu_page($this->domain, __('FAQ', 'bibliomundi-woocommerce'), __('FAQ', 'bibliomundi-woocommerce'), 'manage_options', $this->domain.'-faq', array($this, 'plugin_layout_faq'));
			
			if($plugin_settings['is_wizard'] == 1 || $this->no_wizard == 1){
				add_submenu_page($this->domain, __('Edit Credentials', 'bibliomundi-woocommerce'), __('Credentials', 'bibliomundi-woocommerce'), 'manage_options', $this->domain.'-credentials', array($this, 'plugin_layout_credentials'));
				add_submenu_page($this->domain, __('Import Manual', 'bibliomundi-woocommerce'), __('Import', 'bibliomundi-woocommerce'), 'manage_options', $this->domain.'-import', array($this, 'plugin_layout_import'));
				add_submenu_page($this->domain, __('Restore Configuration', 'bibliomundi-woocommerce'), __('Restore', 'bibliomundi-woocommerce'), 'manage_options', $this->domain.'-restore', array($this, 'plugin_layout_restore'));
			}
		}
		
		function plugin_layout($name){
			$template_file = $this->dir.'templates/'.$name.'.php';
			
			if (file_exists($template_file))
				load_template($template_file);
		}
		
		function plugin_layout_options(){
			global $plugin_meta, $plugin_settings, $plugin_domain, $categories;
			
			$plugin_meta = $this->get_plugin_meta();
			$plugin_settings = $this->get_settings();
			$plugin_domain = $this->domain;		
			$categories = array();
			
			$this->plugin_layout_header();
			
			if(!$this->plugin_required()){
				$this->plugin_layout('page-woocommerce');
			}else
			if($plugin_settings['is_wizard'] == 0 && $this->no_wizard == 0){
				$categories = $categories = get_terms('product_cat', 'orderby=name&hide_empty=0');
				$plugin_settings['continue_status'] = $this->get_import_continue_status();
				
				$this->plugin_layout('page-wizard');
			}else{
				$this->plugin_layout('page-home');
			}			
			
			$this->plugin_layout_footer();
		}
		
		function plugin_layout_faq(){
			global $plugin_meta, $plugin_settings, $plugin_domain;
			
			$plugin_meta = $this->get_plugin_meta();
			$plugin_settings = $this->get_settings();
			$plugin_domain = $this->domain;
			
			//
			$plugin_meta['Pagetitle'] = __('FAQ', 'bibliomundi-woocommerce');
			$plugin_meta['Pagetab'] = 'faq';
			
			$this->plugin_layout_header();
			
			$this->plugin_layout('page-navigation');
			
			$this->plugin_layout('page-faq');
			
			$this->plugin_layout_footer();
		}
		
		function plugin_layout_tutorials(){
			global $plugin_meta, $plugin_settings, $plugin_domain;
			
			$plugin_meta = $this->get_plugin_meta();
			$plugin_settings = $this->get_settings();
			$plugin_domain = $this->domain;
			
			//
			$plugin_meta['Pagetitle'] = __('Video Tutorials', 'bibliomundi-woocommerce');
			$plugin_meta['Pagetab'] = 'tutorials';
			
			$this->plugin_layout_header();
			
			$this->plugin_layout('page-navigation');
			
			$this->plugin_layout('page-tutorials');
			
			$this->plugin_layout_footer();
		}
		
		function plugin_layout_credentials(){
			global $plugin_meta, $plugin_settings, $plugin_domain;
			
			$plugin_meta = $this->get_plugin_meta();
			$plugin_settings = $this->get_settings();
			$plugin_domain = $this->domain;
			
			//
			$plugin_meta['Pagetitle'] = __('Edit Credentials', 'bibliomundi-woocommerce');
			$plugin_meta['Pagetab'] = 'credentials';
			
			$this->plugin_layout_header();
			
			$this->plugin_layout('page-navigation');
			
			$this->plugin_layout('page-credentials');
			
			$this->plugin_layout_footer();
		}
		
		function plugin_layout_import(){
			global $plugin_meta, $plugin_settings, $plugin_domain, $categories;
			
			$plugin_meta = $this->get_plugin_meta();
			$plugin_settings = $this->get_settings();
			$plugin_domain = $this->domain;
			$categories = array();
			
			$categories = $categories = get_terms('product_cat', 'orderby=name&hide_empty=0');
			
			//
			$plugin_meta['Pagetitle'] = __('Import Manual', 'bibliomundi-woocommerce');
			$plugin_meta['Pagetab'] = 'import';
			
			$this->plugin_layout_header();
			
			$this->plugin_layout('page-navigation');
			
			$this->plugin_layout('page-import');
			
			$this->plugin_layout_footer();
		}
		
		function plugin_layout_restore(){
			global $plugin_meta, $plugin_settings, $plugin_domain;
			
			$plugin_meta = $this->get_plugin_meta();
			$plugin_settings = $this->get_settings();
			$plugin_domain = $this->domain;
			
			//
			$plugin_meta['Pagetitle'] = __('Restore Configuration', 'bibliomundi-woocommerce');
			$plugin_meta['Pagetab'] = 'restore';
			
			$this->plugin_layout_header();
			
			$this->plugin_layout('page-navigation');
			
			$this->plugin_layout('page-restore');
			
			$this->plugin_layout_footer();
		}
		
		function plugin_layout_header(){
			global $plugin_meta, $plugin_settings;
			
			//
			$plugin_meta['Pagetitle'] = (@$plugin_meta['Pagetitle'] == '') ? $plugin_meta['Name'] : $plugin_meta['Pagetitle'];			
			
			// css
			wp_enqueue_style($this->domain.'-bootstrap', $this->url.'assets/css/bootstrap.css', array(), '3.1.1', 'all');
			wp_enqueue_style($this->domain.'-sweetalert', $this->url.'assets/css/sweetalert.css', array(), '1.0', 'all');
			// wp_enqueue_style($this->domain.'-modal-video', $this->url.'assets/css/modal-video.min.css', array(), '2.4.1', 'all');
			wp_enqueue_style($this->domain.'-style', $this->url.'assets/css/style.css', array(), $plugin_meta['Version'], 'all');
			
			// js
			wp_enqueue_script($this->domain.'-bootstrap', $this->url.'assets/js/bootstrap.min.js', array(), '3.1.1', true); 
			wp_enqueue_script($this->domain.'-sweetalert', $this->url.'assets/js/sweetalert.min.js', array(), '1.0', true); 
			// wp_enqueue_script($this->domain.'-modal-video', $this->url.'assets/js/modal-video.min.js', array(), '2.4.1', true); 
			wp_enqueue_script($this->domain.'-script', $this->url.'assets/js/script.js', array(), $plugin_meta['Version'], true); 
			wp_localize_script(
				$this->domain.'-script', 
				'bibliomundi_url', 
				array(
					'ajax' => esc_url(admin_url('admin-ajax.php')),
				)
			);
		
			$this->plugin_layout('header');
			
			if(!$this->plugin_writable()){
				echo '<div class="notice notice-error"><p>';
				echo sprintf(
					__('Sorry, but %s cannot write to the plugin directory (%s).', 'bibliomundi-woocommerce'), 
					'<strong>'.$plugin_meta['Name'].'</strong>', 
					$this->dir
				); 
				echo '</p></div>';
			}
		}
		
		function plugin_layout_footer(){
			global $plugin_domain;
			
			$this->plugin_layout('footer');
		}
	}
	
	new WooCommerce_BiblioMundi();
}