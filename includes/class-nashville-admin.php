<?php

class Nashville_Admin {

    public static function init() {
        // Registrar el menú
        add_action( 'admin_menu', array( __CLASS__, 'register_admin_menu' ) );
        
        // Manejar la exportación CSV antes de que cargue el HTML de la página
        add_action( 'admin_init', array( __CLASS__, 'handle_csv_export' ) );
    }

    public static function register_admin_menu() {
        // Añadir submenú bajo "Partner Offers"
        add_submenu_page(
            'edit.php?post_type=partner_offer',
            __( 'Historial de Canjes', 'nashville-member-core' ),
            __( 'Historial de Canjes', 'nashville-member-core' ),
            'manage_options',
            'nashville-claims-history',
            array( __CLASS__, 'render_claims_page' )
        );
    }

    public static function render_claims_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'nashville-member-core' ) );
        }

        // Cargar la vista HTML
        include NASHVILLE_MEMBER_CORE_DIR . 'templates/admin-claims-list.php';
    }

    public static function handle_csv_export() {
        // Verificar que estamos en la página correcta y se ha pulsado el botón de exportar
        if ( isset( $_GET['page'] ) && $_GET['page'] === 'nashville-claims-history' && isset( $_GET['export_claims'] ) ) {
            
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( __( 'Unauthorized access', 'nashville-member-core' ) );
            }

            global $wpdb;
            $table_claims = $wpdb->prefix . 'nashville_offer_claims';
            $table_users = $wpdb->prefix . 'users';
            $table_posts = $wpdb->prefix . 'posts';

            // Consulta para unir los canjes con el nombre del usuario y la oferta
            $results = $wpdb->get_results( "
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
            " );

            // Configurar las cabeceras para forzar la descarga del CSV
            header( 'Content-Type: text/csv; charset=utf-8' );
            header( 'Content-Disposition: attachment; filename=historial_canjes_' . date( 'Y-m-d' ) . '.csv' );
            
            $output = fopen( 'php://output', 'w' );
            
            // Añadir marca BOM para que Excel lea bien los acentos
            fputs( $output, $bom = ( chr(0xEF) . chr(0xBB) . chr(0xBF) ) );
            
            // Escribir la cabecera del CSV
            fputcsv( $output, array( 'Código', 'Fecha', 'Socio', 'Email', 'Oferta Reclamada' ) );

            // Escribir los datos
            if ( $results ) {
                foreach ( $results as $row ) {
                    fputcsv( $output, array(
                        $row->claim_code,
                        $row->claim_date,
                        $row->member_name,
                        $row->member_email,
                        $row->offer_name
                    ) );
                }
            }
            
            fclose( $output );
            exit; // Detener la ejecución de WordPress para que solo se descargue el archivo
        }
    }
}
