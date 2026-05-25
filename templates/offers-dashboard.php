<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Obtener todas las ofertas activas
$offers = get_posts(array(
    'post_type' => 'partner_offer',
    'posts_per_page' => -1,
    'meta_query' => array(
        array(
            'key' => 'is_active',
            'value' => '1',
            'compare' => '=='
        )
    )
));

echo '<div class="nashville-portal">';
echo '<h2>' . esc_html__('Exclusive Partner Offers', 'nashville-member-core') . '</h2>';

if ( empty($offers) ) {
    echo '<p>' . esc_html__('No active offers at the moment. Check back soon!', 'nashville-member-core') . '</p>';
} else {
    echo '<div class="nashville-offers-grid">';
    
    foreach ( $offers as $offer ) {
        $partner_name = get_field('partner_name', $offer->ID);
        // Fallback al título del post si el campo ACF está vacío (para demos rápidas)
        if (empty($partner_name)) {
            $partner_name = get_the_title($offer->ID);
        }
        
        $offer_type = get_field('offer_type', $offer->ID);
        $description = get_field('offer_description', $offer->ID);
        
        $type_label = ($offer_type == 'show-and-go') ? __('Show & Go', 'nashville-member-core') : __('Pre-Claim', 'nashville-member-core');

        echo '<div class="offer-card">';
        echo '<span class="offer-type-badge">' . esc_html($type_label) . '</span>';
        echo '<h3>' . esc_html($partner_name) . '</h3>';
        echo '<p>' . esc_html($description) . '</p>';
        
        if ($offer_type == 'show-and-go') {
            echo '<a href="/member-portal/card/" class="claim-btn">' . esc_html__('Use Digital Card', 'nashville-member-core') . '</a>';
        } else {
            echo '<button class="claim-btn" data-offer-id="' . esc_attr($offer->ID) . '">' . esc_html__('Claim Code', 'nashville-member-core') . '</button>';
        }
        
        echo '</div>';
    }
    
    echo '</div>'; // close grid
}

echo '</div>'; // close portal
?>
