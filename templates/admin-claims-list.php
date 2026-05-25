<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Historial de Canjes', 'nashville-member-core'); ?></h1>
    
    <a href="<?php echo esc_url( admin_url('edit.php?post_type=partner_offer&page=nashville-claims-history&export_claims=1') ); ?>" class="page-title-action">
        <?php _e('Exportar a CSV', 'nashville-member-core'); ?>
    </a>
    
    <hr class="wp-header-end">
    
    <p><?php _e('Aquí se muestran los últimos 100 códigos VIP generados por los socios. Usa el botón de Exportar para descargar el historial completo.', 'nashville-member-core'); ?></p>
    
    <table class="wp-list-table widefat fixed striped table-view-list">
        <thead>
            <tr>
                <th><?php _e('Fecha', 'nashville-member-core'); ?></th>
                <th><?php _e('Código', 'nashville-member-core'); ?></th>
                <th><?php _e('Socio', 'nashville-member-core'); ?></th>
                <th><?php _e('Email', 'nashville-member-core'); ?></th>
                <th><?php _e('Oferta Reclamada', 'nashville-member-core'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            global $wpdb;
            $table_claims = $wpdb->prefix . 'nashville_offer_claims';
            $table_users = $wpdb->prefix . 'users';
            $table_posts = $wpdb->prefix . 'posts';

            $claims = $wpdb->get_results("
                SELECT 
                    c.claim_code,
                    c.claim_date,
                    u.display_name as member_name,
                    u.user_email as member_email,
                    p.post_title as offer_name
                FROM {$table_claims} c
                LEFT JOIN {$table_users} u ON c.member_id = u.ID
                LEFT JOIN {$table_posts} p ON c.offer_id = p.ID
                ORDER BY c.claim_date DESC
                LIMIT 100
            ");

            if ($claims) {
                foreach ($claims as $claim) {
                    echo '<tr>';
                    echo '<td>' . esc_html($claim->claim_date) . '</td>';
                    echo '<td><strong>' . esc_html($claim->claim_code) . '</strong></td>';
                    echo '<td>' . esc_html($claim->member_name) . '</td>';
                    echo '<td><a href="mailto:' . esc_attr($claim->member_email) . '">' . esc_html($claim->member_email) . '</a></td>';
                    echo '<td>' . esc_html($claim->offer_name) . '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="5">' . __('No hay canjes registrados todavía.', 'nashville-member-core') . '</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>
