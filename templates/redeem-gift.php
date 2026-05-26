<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Mensaje de estado (éxito o error)
$message = '';
$message_type = '';

if ( isset( $_GET['gift_error'] ) ) {
    $message = sanitize_text_field( $_GET['gift_error'] );
    $message_type = 'error';
} elseif ( isset( $_GET['gift_success'] ) ) {
    $message = __( '¡Felicidades! Tu Backstage Pass ha sido activado. Redirigiendo a tus ofertas...', 'nashville-member-core' );
    $message_type = 'success';
}
?>

<div class="nashville-digital-card-container">
    <div class="digital-card verified">
        <div class="card-header">
            <div class="card-logo">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#C9A84C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
            <h2 style="font-size: 1.2rem; margin-top: 10px; color: #C9A84C; letter-spacing: 2px;">CANJEA TU REGALO</h2>
        </div>

        <div class="card-body">
            <?php if ( $message ) : ?>
                <div style="background: <?php echo $message_type === 'error' ? 'rgba(255,0,0,0.1)' : 'rgba(201, 168, 76, 0.1)'; ?>; 
                            color: <?php echo $message_type === 'error' ? '#ff6b6b' : '#C9A84C'; ?>; 
                            padding: 15px; border-radius: 5px; margin-bottom: 20px; font-size: 0.9rem; text-align: center;">
                    <?php echo esc_html( $message ); ?>
                </div>
                <?php if ( $message_type === 'success' ) : ?>
                    <script>
                        setTimeout(function(){
                            window.location.href = '/member-portal/offers/';
                        }, 3000);
                    </script>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ( $message_type !== 'success' ) : ?>
                <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST" class="nashville-redeem-form" style="text-align: left;">
                    <input type="hidden" name="action" value="nashville_redeem_gift_process">
                    <?php wp_nonce_field( 'nashville_redeem_gift_nonce', 'nashville_redeem_nonce' ); ?>
                    
                    <div style="margin-bottom: 15px;">
                        <label for="gift_code" style="display:block; color:#aaa; font-size:0.8rem; margin-bottom:5px;">CÓDIGO DE REGALO</label>
                        <input type="text" id="gift_code" name="gift_code" required placeholder="GIFT-XXXXXX" 
                               style="width: 100%; padding: 10px; background: rgba(0,0,0,0.3); border: 1px solid #333; color: #fff; font-family: monospace; font-size: 1.1rem; text-transform: uppercase;">
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label for="user_name" style="display:block; color:#aaa; font-size:0.8rem; margin-bottom:5px;">TU NOMBRE</label>
                        <input type="text" id="user_name" name="user_name" required placeholder="Ej: Santiago" 
                               style="width: 100%; padding: 10px; background: rgba(0,0,0,0.3); border: 1px solid #333; color: #fff; font-size: 1rem;">
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label for="user_email" style="display:block; color:#aaa; font-size:0.8rem; margin-bottom:5px;">EMAIL (Tu usuario)</label>
                        <input type="email" id="user_email" name="user_email" required placeholder="correo@ejemplo.com" 
                               style="width: 100%; padding: 10px; background: rgba(0,0,0,0.3); border: 1px solid #333; color: #fff; font-size: 1rem;">
                    </div>

                    <div style="margin-bottom: 25px;">
                        <label for="user_password" style="display:block; color:#aaa; font-size:0.8rem; margin-bottom:5px;">CONTRASEÑA SECRETA</label>
                        <input type="password" id="user_password" name="user_password" required placeholder="••••••••" 
                               style="width: 100%; padding: 10px; background: rgba(0,0,0,0.3); border: 1px solid #333; color: #fff; font-size: 1rem;">
                    </div>

                    <button type="submit" style="width: 100%; padding: 12px; background: #C9A84C; color: #000; font-weight: bold; border: none; border-radius: 4px; cursor: pointer; text-transform: uppercase; letter-spacing: 1px;">
                        Activar mi Backstage Pass
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
