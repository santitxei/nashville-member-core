<?php
/**
 * Lógica de las membresías de regalo
 */
class Nashville_Gift_Logic {

    public static function init() {
        // En un futuro engancharemos aquí los webhooks de Stripe
        // para auto-generar códigos tras una compra exitosa
    }

    /**
     * Genera un código de regalo único
     * Formato: GIFT-XXXXXX
     */
    public static function generate_gift_code() {
        $random = strtoupper( substr( bin2hex( random_bytes( 3 ) ), 0, 6 ) );
        return "GIFT-{$random}";
    }

    /**
     * Valida si un código de regalo es válido para ser canjeado
     */
    public static function validate_gift_code( $code ) {
        global $wpdb;
        $table_gifts = $wpdb->prefix . 'nashville_gift_codes';

        $code = sanitize_text_field( $code );

        $gift = $wpdb->get_row( $wpdb->prepare( "
            SELECT * FROM {$table_gifts}
            WHERE code = %s
        ", $code ) );

        if ( ! $gift ) {
            return new WP_Error( 'invalid_code', __( 'This gift code does not exist.', 'nashville-member-core' ) );
        }

        if ( $gift->status === 'claimed' ) {
            return new WP_Error( 'already_claimed', __( 'This gift code has already been claimed.', 'nashville-member-core' ) );
        }

        if ( $gift->status === 'expired' ) {
            return new WP_Error( 'expired_code', __( 'This gift code has expired.', 'nashville-member-core' ) );
        }

        return $gift;
    }

    /**
     * Marca un código de regalo como canjeado por un usuario específico
     * y le da 12 meses de acceso.
     */
    public static function mark_gift_claimed( $code, $user_id ) {
        global $wpdb;
        $table_gifts = $wpdb->prefix . 'nashville_gift_codes';

        $code = sanitize_text_field( $code );
        $user_id = absint( $user_id );

        // El acceso durará exactamente 12 meses desde el momento del canje
        $expires_at = date( 'Y-m-d H:i:s', strtotime( '+12 months' ) );

        $updated = $wpdb->update(
            $table_gifts,
            array(
                'status'     => 'claimed',
                'claimed_by' => $user_id,
                'claimed_at' => current_time( 'mysql' ),
                'expires_at' => $expires_at,
            ),
            array( 'code' => $code ),
            array( '%s', '%d', '%s', '%s' ),
            array( '%s' )
        );

        return $updated !== false;
    }

    /**
     * Completa todo el flujo: marca en DB y crea transacción en MemberPress
     */
    public static function mark_gift_claimed_with_mepr( $code, $user_id ) {
        // 1. Actualizar nuestra tabla de regalos
        $updated = self::mark_gift_claimed( $code, $user_id );
        
        if ( ! $updated ) {
            return false;
        }

        // 2. Crear transacción gratuita de MemberPress (12 meses)
        if ( class_exists( 'MeprTransaction' ) ) {
            $txn = new MeprTransaction();
            $txn->user_id = $user_id;
            $txn->product_id = 2209; // ID de Backstage Pass
            $txn->amount = 0.00;
            $txn->total = 0.00;
            $txn->tax_amount = 0.00;
            $txn->tax_rate = 0.00;
            $txn->trans_num = uniqid( 'gift_' );
            $txn->status = MeprTransaction::$complete_str;
            $txn->txn_type = MeprTransaction::$payment_str;
            $txn->expires_at = date( 'Y-m-d H:i:s', strtotime( '+12 months' ) );
            $txn->gateway = 'manual';
            $txn->store();
            
            return true;
        }
        
        return false;
    }
}
