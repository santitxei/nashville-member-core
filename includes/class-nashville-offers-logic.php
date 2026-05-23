<?php
/**
 * Lógica de las ofertas de socios
 */
class Nashville_Offers_Logic {

    public static function init() {
        // Programar el reset mensual si no está programado
        add_action( 'init', array( __CLASS__, 'schedule_monthly_reset' ) );
        
        // Hook para ejecutar el reset mensual
        add_action( 'nashville_monthly_reset', array( __CLASS__, 'execute_monthly_reset' ) );
    }

    /**
     * Genera un código de reclamo único anti-fraude
     * Formato: BP-{PrefijoSocio}-{Random}-{NombreUsuario}
     */
    public static function generate_claim_code( $partner_prefix, $member_name ) {
        $random = strtoupper( substr( bin2hex( random_bytes( 3 ) ), 0, 6 ) );
        // Eliminar caracteres no alfabéticos y tomar los primeros 5
        $name_part = strtoupper( substr( preg_replace( '/[^a-zA-Z]/', '', $member_name ), 0, 5 ) );
        $prefix = strtoupper( preg_replace( '/[^a-zA-Z0-9]/', '', $partner_prefix ) );
        
        return "BP-{$prefix}-{$random}-{$name_part}";
    }

    /**
     * Programa el cron job mensual
     */
    public static function schedule_monthly_reset() {
        if ( ! wp_next_scheduled( 'nashville_monthly_reset' ) ) {
            // Programar para el primer día del próximo mes a medianoche
            $next_month = strtotime( 'first day of next month midnight' );
            wp_schedule_event( $next_month, 'monthly', 'nashville_monthly_reset' );
        }
    }

    /**
     * Ejecuta el reset mensual de reclamos
     */
    public static function execute_monthly_reset() {
        global $wpdb;

        // 1. Resetear 'claims_used' a 0 en todas las ofertas
        $wpdb->query( "
            UPDATE {$wpdb->postmeta} 
            SET meta_value = '0' 
            WHERE meta_key = 'claims_used'
        " );

        // 2. Invalidar códigos de reclamo que ya pasaron su fecha de expiración
        $table_claims = $wpdb->prefix . 'nashville_offer_claims';
        $wpdb->query( "
            UPDATE {$table_claims} 
            SET is_redeemed = 1 
            WHERE expires_at < NOW() AND is_redeemed = 0
        " );
    }

    /**
     * Verifica si un miembro puede reclamar una oferta
     * (Se usará en los endpoints de la API / frontend)
     */
    public static function can_member_claim_offer( $offer_id, $member_id ) {
        // 1. Verificar si la oferta está activa
        $is_active = get_field( 'is_active', $offer_id );
        if ( ! $is_active ) {
            return new WP_Error( 'offer_inactive', __( 'This offer is currently inactive.', 'nashville-member-core' ) );
        }

        // 2. Verificar límites globales mensuales de la oferta
        $monthly_limit = (int) get_field( 'monthly_claim_limit', $offer_id );
        $claims_used   = (int) get_field( 'claims_used', $offer_id );

        if ( $monthly_limit > 0 && $claims_used >= $monthly_limit ) {
            return new WP_Error( 'limit_reached', __( 'This offer has reached its maximum claims for this month.', 'nashville-member-core' ) );
        }

        // 3. Verificar límites por usuario
        $member_limit = (int) get_field( 'member_claim_limit', $offer_id );
        if ( ! $member_limit ) {
            $member_limit = 1; // Por defecto 1
        }

        global $wpdb;
        $table_claims = $wpdb->prefix . 'nashville_offer_claims';
        
        // Contar reclamos del usuario este mes para esta oferta
        $first_day_of_month = date( 'Y-m-01 00:00:00' );
        $claims_by_user = $wpdb->get_var( $wpdb->prepare( "
            SELECT COUNT(*) FROM {$table_claims}
            WHERE offer_id = %d AND member_id = %d AND claim_date >= %s
        ", $offer_id, $member_id, $first_day_of_month ) );

        if ( $claims_by_user >= $member_limit ) {
            return new WP_Error( 'user_limit_reached', __( 'You have reached your personal claim limit for this offer this month.', 'nashville-member-core' ) );
        }

        return true;
    }
}
