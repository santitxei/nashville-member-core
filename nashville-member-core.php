<?php
/**
 * Plugin Name: Nashville Member Core
 * Description: Núcleo a medida para la gestión de ofertas de socios, límites mensuales y códigos de regalo para "The Nashville Insider", integrado con MemberPress.
 * Version: 1.0.0
 * Author: The Nashville Insider
 */

// Evitar acceso directo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Constantes del plugin
define( 'NASHVILLE_MEMBER_CORE_VERSION', '1.0.0' );
define( 'NASHVILLE_MEMBER_CORE_DIR', plugin_dir_path( __FILE__ ) );

// 1. Hook de activación (para crear las tablas de base de datos)
require_once NASHVILLE_MEMBER_CORE_DIR . 'includes/class-nashville-activator.php';
register_activation_hook( __FILE__, array( 'Nashville_Activator', 'activate' ) );

// 2. Cargar dependencias (CPTs y Lógica)
function nashville_member_core_load() {
    require_once NASHVILLE_MEMBER_CORE_DIR . 'includes/class-nashville-cpt.php';
    require_once NASHVILLE_MEMBER_CORE_DIR . 'includes/class-nashville-offers-logic.php';
    Nashville_Offers_Logic::init();
    require_once NASHVILLE_MEMBER_CORE_DIR . 'includes/class-nashville-gift-logic.php';
    Nashville_Gift_Logic::init();

    require_once NASHVILLE_MEMBER_CORE_DIR . 'includes/class-nashville-acf.php';
    Nashville_ACF::init();
}
add_action( 'plugins_loaded', 'nashville_member_core_load' );
