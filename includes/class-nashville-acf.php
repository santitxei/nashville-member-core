<?php

class Nashville_ACF {

    public static function init() {
        add_action('acf/init', array(__CLASS__, 'register_offer_fields'));
    }

    public static function register_offer_fields() {
        if( function_exists('acf_add_local_field_group') ):

        acf_add_local_field_group(array(
            'key' => 'group_nashville_offer_details',
            'title' => __('Offer Details', 'nashville-member-core'),
            'fields' => array(
                array(
                    'key' => 'field_offer_is_active',
                    'label' => __('Is Active?', 'nashville-member-core'),
                    'name' => 'is_active',
                    'type' => 'true_false',
                    'ui' => 1,
                    'default_value' => 1,
                ),
                array(
                    'key' => 'field_offer_partner_name',
                    'label' => __('Partner Name', 'nashville-member-core'),
                    'name' => 'partner_name',
                    'type' => 'text',
                    'required' => 1,
                ),
                array(
                    'key' => 'field_offer_partner_prefix',
                    'label' => __('Partner Prefix (for codes)', 'nashville-member-core'),
                    'name' => 'partner_prefix',
                    'type' => 'text',
                    'instructions' => __('e.g. HTN, GLC, ZOO (max 4 chars)', 'nashville-member-core'),
                    'required' => 1,
                    'maxlength' => 4,
                ),
                array(
                    'key' => 'field_offer_partner_category',
                    'label' => __('Category', 'nashville-member-core'),
                    'name' => 'partner_category',
                    'type' => 'select',
                    'choices' => array(
                        'hotel' => __('Hotel', 'nashville-member-core'),
                        'restaurant' => __('Restaurant', 'nashville-member-core'),
                        'bar' => __('Bar', 'nashville-member-core'),
                        'experience' => __('Experience', 'nashville-member-core'),
                    ),
                    'required' => 1,
                ),
                array(
                    'key' => 'field_offer_type',
                    'label' => __('Offer Type', 'nashville-member-core'),
                    'name' => 'offer_type',
                    'type' => 'select',
                    'choices' => array(
                        'pre-claim' => __('Pre-Claim (Requires code)', 'nashville-member-core'),
                        'show-and-go' => __('Show and Go (Show card)', 'nashville-member-core'),
                    ),
                    'required' => 1,
                ),
                array(
                    'key' => 'field_offer_description',
                    'label' => __('Offer Description', 'nashville-member-core'),
                    'name' => 'offer_description',
                    'type' => 'textarea',
                    'required' => 1,
                ),
                array(
                    'key' => 'field_offer_monthly_limit',
                    'label' => __('Global Monthly Limit', 'nashville-member-core'),
                    'name' => 'monthly_claim_limit',
                    'type' => 'number',
                    'instructions' => __('Total claims allowed per month for all members combined.', 'nashville-member-core'),
                    'required' => 1,
                    'min' => 1,
                ),
                array(
                    'key' => 'field_offer_claims_used',
                    'label' => __('Claims Used (This Month)', 'nashville-member-core'),
                    'name' => 'claims_used',
                    'type' => 'number',
                    'default_value' => 0,
                ),
                array(
                    'key' => 'field_offer_member_limit',
                    'label' => __('Limit Per Member', 'nashville-member-core'),
                    'name' => 'member_claim_limit',
                    'type' => 'number',
                    'instructions' => __('How many times a single member can claim this per month.', 'nashville-member-core'),
                    'default_value' => 1,
                    'min' => 1,
                    'required' => 1,
                ),
                array(
                    'key' => 'field_offer_valid_from',
                    'label' => __('Valid From', 'nashville-member-core'),
                    'name' => 'valid_from',
                    'type' => 'date_picker',
                    'display_format' => 'd/m/Y',
                    'return_format' => 'Y-m-d',
                ),
                array(
                    'key' => 'field_offer_valid_until',
                    'label' => __('Valid Until', 'nashville-member-core'),
                    'name' => 'valid_until',
                    'type' => 'date_picker',
                    'display_format' => 'd/m/Y',
                    'return_format' => 'Y-m-d',
                ),
                array(
                    'key' => 'field_offer_availability',
                    'label' => __('Availability Window', 'nashville-member-core'),
                    'name' => 'availability_window',
                    'type' => 'text',
                    'instructions' => __('e.g. Valid tonight, Valid this weekend', 'nashville-member-core'),
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_offer_type',
                                'operator' => '==',
                                'value' => 'show-and-go',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_offer_redemption_notes',
                    'label' => __('Redemption Notes', 'nashville-member-core'),
                    'name' => 'redemption_notes',
                    'type' => 'textarea',
                    'instructions' => __('Instructions for the member to redeem the code.', 'nashville-member-core'),
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_offer_type',
                                'operator' => '==',
                                'value' => 'pre-claim',
                            ),
                        ),
                    ),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'partner_offer',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'seamless',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => array('the_content'),
            'active' => true,
            'description' => '',
            'show_in_rest' => 0,
        ));
        
        endif;
    }
}
