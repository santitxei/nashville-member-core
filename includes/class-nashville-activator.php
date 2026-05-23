<?php

class Nashville_Activator {

	public static function activate() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		// Tabla 1: wp_nashville_offer_claims
		$table_claims = $wpdb->prefix . 'nashville_offer_claims';
		$sql_claims = "CREATE TABLE $table_claims (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			offer_id BIGINT UNSIGNED NOT NULL,
			member_id BIGINT UNSIGNED NOT NULL,
			claim_date DATETIME NOT NULL,
			claim_code VARCHAR(30) NOT NULL UNIQUE,
			is_redeemed TINYINT(1) DEFAULT 0,
			redeemed_at DATETIME NULL,
			expires_at DATETIME NOT NULL,
			INDEX (offer_id),
			INDEX (member_id),
			INDEX (claim_code)
		) $charset_collate;";

		// Tabla 2: wp_nashville_gift_codes
		$table_gifts = $wpdb->prefix . 'nashville_gift_codes';
		$sql_gifts = "CREATE TABLE $table_gifts (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			code VARCHAR(20) NOT NULL UNIQUE,
			purchaser_email VARCHAR(200) NOT NULL,
			stripe_payment_id VARCHAR(100),
			status ENUM('unclaimed','claimed','expired') DEFAULT 'unclaimed',
			claimed_by BIGINT UNSIGNED NULL,
			claimed_at DATETIME NULL,
			expires_at DATETIME NOT NULL,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			INDEX (code),
			INDEX (purchaser_email)
		) $charset_collate;";

		dbDelta( $sql_claims );
		dbDelta( $sql_gifts );
	}
}
