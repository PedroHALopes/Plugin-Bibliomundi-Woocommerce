<?php
	
	$wp_config = dirname( __FILE__ ) . '/../../../wp-config.php';
	
	if ( file_exists( $wp_config ) ) {	
		require_once $wp_config;
	
		if ( ! class_exists( 'WC_Catalog_Bibliomundi' ) ) {
			$bbm_plugin = dirname( __FILE__ ) . '/woocommerce-bibliomundi.php';
			if ( file_exists( $bbm_plugin ) ) {
				require_once $bbm_plugin;
			}
		}
		
		WC_Catalog_Bibliomundi::import( 'updates' );
	}
