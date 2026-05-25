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
        // Aquí iría la llamada a Nashville_Offers_Logic::can_member_claim_offer
        return rest_ensure_response( array( 'success' => false, 'message' => 'Pending full implementation' ) );
    }
}
