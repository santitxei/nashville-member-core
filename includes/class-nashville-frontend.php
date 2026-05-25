<?php

class Nashville_Frontend {

    public static function init() {
        // Registrar scripts y estilos
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );

        // Registrar shortcodes
        add_shortcode( 'nashville_digital_card', array( __CLASS__, 'render_digital_card' ) );
        add_shortcode( 'nashville_offers_dashboard', array( __CLASS__, 'render_offers_dashboard' ) );

        // API endpoints para el frontend (AJAX / REST)
        add_action( 'rest_api_init', array( __CLASS__, 'register_rest_routes' ) );
    }

    public static function enqueue_assets() {
        global $post;
        
        // Solo cargar los assets si estamos en una página que contiene nuestros shortcodes
        if ( is_a( $post, 'WP_Post' ) && ( has_shortcode( $post->post_content, 'nashville_digital_card' ) || has_shortcode( $post->post_content, 'nashville_offers_dashboard' ) ) ) {
            
            wp_enqueue_style( 
                'nashville-frontend-css', 
                plugins_url( 'assets/css/nashville-frontend.css', NASHVILLE_MEMBER_CORE_DIR . 'nashville-member-core.php' ), 
                array(), 
                NASHVILLE_MEMBER_CORE_VERSION 
            );

            wp_enqueue_script( 
                'nashville-frontend-js', 
                plugins_url( 'assets/js/nashville-frontend.js', NASHVILLE_MEMBER_CORE_DIR . 'nashville-member-core.php' ), 
                array(), 
                NASHVILLE_MEMBER_CORE_VERSION, 
                true // Cargar en el footer
            );

            // Pasar variables de PHP a Javascript
            wp_localize_script( 'nashville-frontend-js', 'nashvilleData', array(
                'apiUrl' => esc_url_raw( rest_url( 'nashville/v1' ) ),
                'nonce'  => wp_create_nonce( 'wp_rest' )
            ) );
        }
    }

    public static function render_digital_card( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<p>' . __( 'You must be logged in to view your Backstage Pass.', 'nashville-member-core' ) . '</p>';
        }

        // Comprobación doble de seguridad: Verificar si es socio activo de MemberPress
        if ( class_exists('MeprUser') ) {
            $mepr_user = new MeprUser( get_current_user_id() );
            if ( ! $mepr_user->is_active() ) {
                return '<p>' . __( 'Your Backstage Pass membership is not active. Please renew your subscription.', 'nashville-member-core' ) . '</p>';
            }
        }

        $current_user = wp_get_current_user();
        $user_name = $current_user->first_name ? $current_user->first_name : $current_user->display_name;

        // Cargar y devolver la plantilla HTML
        ob_start();
        include NASHVILLE_MEMBER_CORE_DIR . 'templates/digital-card.php';
        return ob_get_clean();
    }

    public static function render_offers_dashboard( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<p>' . __( 'You must be logged in to view partner offers.', 'nashville-member-core' ) . '</p>';
        }

        // Comprobación doble de seguridad: Verificar si es socio activo de MemberPress
        if ( class_exists('MeprUser') ) {
            $mepr_user = new MeprUser( get_current_user_id() );
            if ( ! $mepr_user->is_active() ) {
                return '<p>' . __( 'Your Backstage Pass membership is not active. Please renew your subscription to see the offers.', 'nashville-member-core' ) . '</p>';
            }
        }

        ob_start();
        include NASHVILLE_MEMBER_CORE_DIR . 'templates/offers-dashboard.php';
        return ob_get_clean();
    }

    public static function register_rest_routes() {
        // Endpoint para verificar la tarjeta digital (Tap to verify)
        register_rest_route( 'nashville/v1', '/verify-card', array(
            'methods'  => 'POST',
            'callback' => array( __CLASS__, 'api_verify_card' ),
            'permission_callback' => function () {
                return is_user_logged_in();
            }
        ) );

        // Endpoint para reclamar una oferta (Pre-Claim)
        register_rest_route( 'nashville/v1', '/claim-offer', array(
            'methods'  => 'POST',
            'callback' => array( __CLASS__, 'api_claim_offer' ),
            'permission_callback' => function () {
                return is_user_logged_in();
            }
        ) );
    }

    public static function api_verify_card( $request ) {
        return rest_ensure_response( array(
            'verified'  => true,
            'timestamp' => current_time( 'H:i:s' )
        ) );
    }

    public static function api_claim_offer( $request ) {
        $offer_id = (int) $request->get_param( 'offer_id' );
        $member_id = get_current_user_id();

        if ( ! $offer_id ) {
            return rest_ensure_response( array( 'success' => false, 'message' => 'Invalid offer ID.' ) );
        }

        // 1. Verificar si puede reclamar
        $can_claim = Nashville_Offers_Logic::can_member_claim_offer( $offer_id, $member_id );
        if ( is_wp_error( $can_claim ) ) {
            return rest_ensure_response( array( 'success' => false, 'message' => $can_claim->get_error_message() ) );
        }

        // 2. Generar código
        $partner_prefix = get_field( 'partner_prefix', $offer_id ) ?: 'OFFR';
        $current_user = wp_get_current_user();
        $member_name = $current_user->first_name ?: $current_user->display_name;
        
        $code = Nashville_Offers_Logic::generate_claim_code( $partner_prefix, $member_name );

        // 3. Registrar en DB
        global $wpdb;
        $table_claims = $wpdb->prefix . 'nashville_offer_claims';
        $expires_at = date( 'Y-m-d H:i:s', strtotime( '+24 hours' ) ); // Expira en 24h
        
        $wpdb->insert(
            $table_claims,
            array(
                'offer_id'    => $offer_id,
                'member_id'   => $member_id,
                'claim_date'  => current_time( 'mysql' ),
                'claim_code'  => $code,
                'is_redeemed' => 0,
                'expires_at'  => $expires_at,
            ),
            array( '%d', '%d', '%s', '%s', '%d', '%s' )
        );

        // 4. Actualizar contador global de la oferta (ACF)
        $claims_used = (int) get_field( 'claims_used', $offer_id );
        update_field( 'claims_used', $claims_used + 1, $offer_id );

        // 5. Obtener notas de redención
        $redemption_notes = get_field( 'redemption_notes', $offer_id );

        return rest_ensure_response( array( 
            'success' => true, 
            'code' => $code,
            'notes' => $redemption_notes
        ) );
    }
}
