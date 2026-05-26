<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Gift Codes Manager', 'nashville-member-core' ); ?></h1>
    <hr class="wp-header-end">

    <?php if ( isset( $_GET['codes_generated'] ) ) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php printf( esc_html__( 'Successfully generated %d new gift code(s).', 'nashville-member-core' ), absint( $_GET['codes_generated'] ) ); ?></p>
        </div>
    <?php endif; ?>

    <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); max-width: 600px; margin-top: 20px; margin-bottom: 30px;">
        <h2><?php esc_html_e( 'Generate New Codes', 'nashville-member-core' ); ?></h2>
        <p><?php esc_html_e( 'Enter the number of VIP gift codes you want to generate. These codes will grant 1 year of free Backstage Pass access to whoever redeems them.', 'nashville-member-core' ); ?></p>
        
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="nashville_generate_gift_codes">
            <?php wp_nonce_field( 'nashville_generate_codes_nonce', 'nashville_generate_nonce' ); ?>
            
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><label for="num_codes"><?php esc_html_e( 'Quantity', 'nashville-member-core' ); ?></label></th>
                        <td>
                            <input name="num_codes" type="number" id="num_codes" value="1" min="1" max="50" class="small-text">
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="submit">
                <button type="submit" class="button button-primary"><?php esc_html_e( 'Generate Codes', 'nashville-member-core' ); ?></button>
            </p>
        </form>
    </div>

    <h2><?php esc_html_e( 'All Gift Codes', 'nashville-member-core' ); ?></h2>
    
    <table class="wp-list-table widefat fixed striped table-view-list">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Gift Code', 'nashville-member-core' ); ?></th>
                <th><?php esc_html_e( 'Status', 'nashville-member-core' ); ?></th>
                <th><?php esc_html_e( 'Claimed By', 'nashville-member-core' ); ?></th>
                <th><?php esc_html_e( 'Claimed At', 'nashville-member-core' ); ?></th>
                <th><?php esc_html_e( 'Created At', 'nashville-member-core' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $codes ) ) : ?>
                <?php foreach ( $codes as $code_row ) : ?>
                    <tr>
                        <td style="font-family: monospace; font-size: 14px;"><strong><?php echo esc_html( $code_row->code ); ?></strong></td>
                        <td>
                            <?php if ( $code_row->status === 'unclaimed' ) : ?>
                                <span style="background: #e5f5fa; color: #007cba; padding: 3px 8px; border-radius: 3px; font-weight: 500;"><?php esc_html_e( 'Unclaimed', 'nashville-member-core' ); ?></span>
                            <?php else : ?>
                                <span style="background: #f0f0f1; color: #50575e; padding: 3px 8px; border-radius: 3px;"><?php echo esc_html( ucfirst( $code_row->status ) ); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            if ( $code_row->claimed_by ) {
                                $user = get_userdata( $code_row->claimed_by );
                                echo esc_html( $user ? $user->display_name . ' (' . $user->user_email . ')' : '#' . $code_row->claimed_by );
                            } else {
                                echo '&mdash;';
                            }
                            ?>
                        </td>
                        <td><?php echo $code_row->claimed_at ? esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $code_row->claimed_at ) ) ) : '&mdash;'; ?></td>
                        <td><?php echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $code_row->created_at ) ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5"><?php esc_html_e( 'No gift codes found.', 'nashville-member-core' ); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
