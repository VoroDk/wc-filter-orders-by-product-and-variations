<?php
/**
 * @author  FlyoutApps
 * @since   1.0
 * @version 1.0
 */

namespace flyoutapps\wfobpp;

class Filter_By_Product extends Filter_By {

	public function __construct() {
		$this->id = 'wfobpp_by_product';
		parent::__construct();

		add_filter( 'posts_where', array( $this, 'filter_where' ) );
	}

	public function dropdown_fields(){
		global $wpdb;

		$status    = apply_filters( 'wfobp_product_status', 'publish' );
		$sql       = "SELECT ID,post_title FROM $wpdb->posts WHERE ";

		$where_post_type  = apply_filters( 'wfobp_filter_post_types', [ 'product' ] );
		$count_post_types = count( $where_post_type );
		if ( $count_post_types == 1 ) {
			$post_types = "post_type = '" . $where_post_type[0] . "'";
		} elseif ( $count_post_types > 1 ) {
			$post_types = "(";
			$index      = 0;
			foreach ( $where_post_type as $post_type ) {
				$post_types .= "post_type = '" . $post_type . "'";
				$index ++;
				if ( $count_post_types > $index ) {
					$post_types .= " OR ";
				}
			}
			$post_types .= ")";
		}
		$sql .= $post_types;

		$sql       .= ( $status == 'any' ) ? '' : " AND post_status = '$status'";
		$all_posts = $wpdb->get_results( $sql, ARRAY_A );

		$fields    = array();
		$fields[0] = esc_html__( 'All Products', 'woocommerce-filter-orders-by-product' );
		foreach ( $all_posts as $all_post ) {
			$fields[$all_post['ID']] = $all_post['post_title'];
		}

		return $fields;
	}

	// Modify where clause in query
	public function filter_where( $where ) {
		if ( isset( $_GET[$this->id] ) && !empty( $_GET[$this->id] ) ) {
			$product = intval($_GET[$this->id]);

			// Check if selected product is inside order query
			$where .= " AND $product IN (";
			$where .= $this->query_by_product();
			$where .= ")";
		}
		return $where;
	}
}

new Filter_By_Product();