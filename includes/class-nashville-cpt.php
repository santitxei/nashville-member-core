<?php

class Nashville_CPT {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_partner_offer' ) );
	}

	public static function register_partner_offer() {
		$labels = array(
			'name'                  => 'Partner Offers',
			'singular_name'         => 'Partner Offer',
			'menu_name'             => 'Partner Offers',
			'name_admin_bar'        => 'Partner Offer',
			'add_new'               => 'Add New',
			'add_new_item'          => 'Add New Partner Offer',
			'new_item'              => 'New Partner Offer',
			'edit_item'             => 'Edit Partner Offer',
			'view_item'             => 'View Partner Offer',
			'all_items'             => 'All Partner Offers',
			'search_items'          => 'Search Partner Offers',
			'not_found'             => 'No partner offers found.',
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_icon'          => 'dashicons-tickets-alt',
			'query_var'          => false,
			'rewrite'            => false,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 30,
			'supports'           => array( 'title', 'custom-fields' ),
			'show_in_rest'       => false,
		);

		register_post_type( 'partner_offer', $args );
	}
}

Nashville_CPT::init();
