<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Post_BiblioMundi' ) ) :
	
class WC_Post_BiblioMundi extends WC_Base_BiblioMundi {
	
	private static $ID_TYPE_ISBN      = 15;
	private static $VISIBILITY_STATUS = 20;
	private static $INSTANCE;

	private $post_metas;
	private $post_data;

	public static function get_instance() {
		if ( is_null( self::$INSTANCE ) ) {
			self::$INSTANCE = new self;
		}
		return self::$INSTANCE;
	}

	protected function exists( $id = null ) {
		if ( ! $id && array_key_exists( 'id_ebook', $this->post_metas ) ) {
			$id = $this->post_metas['id_ebook'];
		}

		if ( $id ) {
			return $this->db->get_var( $this->db->prepare( "SELECT post_id FROM {$this->db->postmeta} WHERE meta_key = %s AND meta_value = %d LIMIT 1", 'id_ebook', $id ) );
		}
	}

	protected function get_post_data() {
		$this->post_data = array();
		
		if ( $this->get_element() ) {
			$this->post_data = array(
				'post_type'    => 'product',
				'post_status'  => 'publish',
				'post_title'   => (string) $this->element->DescriptiveDetail->TitleDetail->TitleElement->TitleText,
				'post_content' => (string) $this->element->CollateralDetail->TextContent->Text->p,
			);
		}

		return apply_filters( 'woocommerce_post_data_bibliomundi', $this->post_data );
	}

	protected function get_post_metas() {
		$this->post_metas = array();
		
		if( $this->get_element() ) {
			$price = (float) $this->element->ProductSupply->SupplyDetail->Price->PriceAmount;
			$visibility = (int) $this->element->ProductSupply->SupplyDetail->ProductAvailability;

			$url_file = 'http://www.bibliomundi.com/ebook.epub';
			$downloadable_files[ md5( $url_file ) ] = array(
				'name' => 'Bibliomundi',
				'file' => $url_file,
			);

			$this->post_metas = array(
				'notification_type'   => (int) $this->element->NotificationType,
				'id_bibliomundi'      => (int) $this->element->ProductIdentifier->ProductIDType,
				'id_ebook'            => (int) $this->element->ProductIdentifier->IDValue,
				'subtitle'            => (string) $this->element->DescriptiveDetail->TitleDetail->TitleElement->Subtitle,
				'edition_number'      => (string) $this->element->DescriptiveDetail->EditionNumber,
				'_visibility'         => self::$VISIBILITY_STATUS == $visibility ? 'visible' : 'hidden',
				'_manage_stock'       => 'no',
				'_stock_status'       => 'instock',
				'_regular_price'      => $price,
				'_price'              => $price,
				'publishers'          => (string) $this->element->PublishingDetail->Imprint->ImprintName,
				'_virtual'            => 'yes',
				'_downloadable'       => 'yes',
				'_downloadable_files' => $downloadable_files,
			);

			if ( self::$ID_TYPE_ISBN === ( int ) $this->element->ProductIdentifier[1]->ProductIDType ) {
				$this->post_metas['isbn'] = (string) $this->element->ProductIdentifier[1]->IDValue;
			}
		}

		return apply_filters( 'woocommerce_post_metas_bibliomundi', $this->post_metas );
	}

	public function can_insert() {
		return $this->get_element() && self::$VISIBILITY_STATUS == $this->element->ProductSupply->SupplyDetail->ProductAvailability;
	}

	public function insert() {
		$this->get_post_data();
		$this->get_post_metas();
		$insert  = $this->can_insert();
		$post_id = $this->exists();
		if ( ! $insert && $post_id ) {
			wp_update_post( array( 'ID' => $post_id, 'post_status' => 'trash' ) );
		} elseif ( $insert && ! empty( $this->post_data ) && ! empty( $this->post_metas ) ) {
			if ( $post_id ) {
				$this->post_data['ID'] = $post_id;
				switch( $this->post_metas['notification_type'] ) {
					case WC_Notification_Type_BiblioMundi::UPDATE:
						wp_update_post( $this->post_data );
						break;
					case WC_Notification_Type_BiblioMundi::DELETE:
						$insert = false;
						$this->post_data['post_status'] = 'trash';
						wp_update_post( $this->post_data );
						break;
				}
			} else {
				$post_id = wp_insert_post( $this->post_data, true );
			}

			if ( $insert && $post_id ) {
				$this->insert_metas( $post_id );
				$this->insert_thumbnail( $post_id );
				$this->insert_categories( $post_id );
			}
			return $post_id;
		}
		return false;
	}

	protected function insert_metas( $post_id ) {
		if ( $this->post_metas ) {
			foreach( $this->post_metas as $key => $value ) {
				update_post_meta( $post_id, $key, $value );
			}
		}
	}

	protected function insert_thumbnail( $post_id ) {
		if ( $this->element && $this->post_data ) {
			$file = (string) $this->element->CollateralDetail->SupportingResource->ResourceVersion->ResourceLink;
			return WC_Media_BiblioMundi::insert( $post_id, $file, $this->post_data['post_title'] );
		}
	}

	protected function insert_categories( $post_id ) {
		if ( $this->element ) {
			$subject = $this->element->DescriptiveDetail->Subject;
			foreach( $subject as $s ) {
				$identifier = (string) $s->SubjectSchemeIdentifier;
				$code       = (string) $s->SubjectCode;
				WC_Category_BiblioMundi::add_relationship( $post_id, $code, $identifier );
			}
		}
	}

}

endif;