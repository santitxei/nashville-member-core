<?php
// Evitar acceso directo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="member-card-wrapper">
    <div class="member-card__pulse">
        <div class="member-card__logo">
            <!-- Icono abstracto premium dorado -->
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#C9A84C" stroke-width="1.5"><path d="M12 2L2 22h20L12 2z"/><path d="M12 12l5 5"/><path d="M12 12l-5 5"/></svg>
        </div>
        <div class="member-card__badge"><?php echo esc_html__( 'Backstage Pass', 'nashville-member-core' ); ?></div>
        <div class="member-card__name"><?php echo esc_html( $user_name ); ?></div>
        <div class="member-card__since"><?php echo esc_html__( 'MEMBER SINCE', 'nashville-member-core' ); ?> <?php echo date('Y'); ?></div>
        <div class="verify-status"><?php echo esc_html__( 'Tap to verify', 'nashville-member-core' ); ?></div>
    </div>
</div>
