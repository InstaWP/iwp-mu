<?php
/**
 * Dashboard Template
 */

use InstaWP\Connect\Helpers\Helper;
use InstaWP\Connect\Helpers\Option;

$dismissible_url         = wp_nonce_url( iwp_get_current_admin_url( [ 'iwp_hide_welcome_panel' => 'yes' ] ), 'iwp_welcome_nonce', 'iwp_nonce' );
$welcome_panel_dismissed = (bool) get_user_meta( get_current_user_id(), 'iwp_welcome_panel_dismissed', true );

if ( $welcome_panel_dismissed !== false ) {
	return;
}

$iwp_welcome_details = Option::get_option( 'iwp_welcome_details' );
//$iwp_welcome_details = array(
//	'site'     => array(
//		'username'         => 'jaedm97',
//		'password'         => 'FL7cMdPY',
//		'temporary'        => true,
//		'expiry'           => 1721113943,
//		'reserve_site_url' => 'https://instawp.com',
//		'manage_site_url'  => 'https://instawp.com/manage',
//	),
//	'partners' => array(
//		array(
//			'name'        => 'Omnisend',
//			'logo_url'    => 'https://www.omnisend.com/wp-content/uploads/2022/07/cropped-favi-32x32.png',
//			'description' => 'Email Marketing WordPress plugin for WooCommerce by Omnisend',
//			'slug'        => 'omnisend-connect',
//			'plugin_file' => 'omnisend-connect/omnisend-woocommerce.php',
//			'zip_url'     => '',
//			'cta_text'    => 'Check Offer',
//			'cta_link'    => 'https://www.omnisend.com/',
//		),
//		array(
//			'name'        => 'Yoast SEO',
//			'logo_url'    => 'https://ps.w.org/wordpress-seo/assets/icon-256x256.gif?rev=3112542',
//			'description' => 'Write better content and have a fully optimized WordPress site using the Yoast SEO plugin.',
//			'slug'        => 'wordpress-seo',
//			'plugin_file' => 'wordpress-seo/wp-seo.php',
//			'zip_url'     => '',
//			'cta_text'    => 'Check Offer',
//			'cta_link'    => 'https://yoa.st/1uk',
//		),
//		array(
//			'name'        => 'Elementor',
//			'logo_url'    => 'https://elementor.com/wp-content/uploads/2021/04/elementor-favicon-512.png',
//			'description' => 'Elementor Website Builder – More than Just a Page Builder',
//			'slug'        => 'elementor',
//			'plugin_file' => 'elementor/elementor.php',
//			'zip_url'     => '',
//			'cta_text'    => 'Check Offer',
//			'cta_link'    => 'Elementor.com',
//		),
//	),
//);

if ( empty( $iwp_welcome_details ) ) {
	return;
}

$iwp_details_partners = Helper::get_args_option( 'partners', $iwp_welcome_details, [] );
$iwp_details_site     = Helper::get_args_option( 'site', $iwp_welcome_details, [] );
$iwp_site_temporary   = (bool) Helper::get_args_option( 'temporary', $iwp_details_site, 'true' );
$iwp_site_expiry      = Helper::get_args_option( 'expiry', $iwp_details_site );
$site_username        = Helper::get_args_option( 'username', $iwp_details_site );
$site_password        = Helper::get_args_option( 'password', $iwp_details_site );

$diff_in_sec = $iwp_site_expiry - time();
$days        = floor( $diff_in_sec / ( 24 * 60 * 60 ) );
$hours       = floor( ( $diff_in_sec % ( 24 * 60 * 60 ) ) / ( 60 * 60 ) );
$minutes     = floor( ( $diff_in_sec % ( 60 * 60 ) ) / 60 );

?>

<div class="iwp-dashboard d-inline-block my-6 w-full">
    <a href="<?php echo esc_url( $dismissible_url ); ?>" class="iwp-dashboard-close text-white hover:text-white focus:text-white active:text-white flex justify-between items-center">
        <span class="font-inter text-sm pr-1.5 line leading-6"><?php esc_html_e( 'Dismiss', 'iwp-mu' ); ?></span>
        <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M1.5 10.5L10.5 1.5M1.5 1.5L10.5 10.5" stroke="#EBF9F5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </a>

    <div class="iwp-dashboard-content p-8 rounded-t-lg overflow-hidden">
        <span class="bg-primary-950"></span>

        <div class="text-white text-[24px] leading-[36px] font-medium mb-4"><?php esc_html_e( 'Welcome to WordPress', 'iwp-mu' ); ?></div>

        <div class="space-y-2 text-grayCust-200 mb-4 text-sm font-inter">
            <div class="flex items-center">
                <span class="min-w-[70px]"><?php esc_html_e( 'Username', 'iwp-mu' ); ?></span>
                <span class="mx-1">:</span>
                <span class="mx-1"><?php echo esc_html( $site_username ); ?></span>
                <span class="iwp-copy-content cursor-pointer" data-content="<?php echo esc_attr( $site_username ); ?>" data-text-copied="<?php echo esc_attr__( 'Copied!', 'iwp-mu' ); ?>">
                    <svg class="mr-2" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 10H2.5C1.67157 10 1 9.32843 1 8.5V2.5C1 1.67157 1.67157 1 2.5 1H8.5C9.32843 1 10 1.67157 10 2.5V4M5.5 13H11.5C12.3284 13 13 12.3284 13 11.5V5.5C13 4.67157 12.3284 4 11.5 4H5.5C4.67157 4 4 4.67157 4 5.5V11.5C4 12.3284 4.67157 13 5.5 13Z" stroke="#D4D4D8" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            </div>
            <div class="flex items-center">
                <span class="min-w-[70px]"><?php esc_html_e( 'Password', 'iwp-mu' ); ?></span>
                <span class="mx-1">:</span>
                <span class="mx-1"><?php echo esc_html( $site_password ); ?></span>
                <span class="iwp-copy-content cursor-pointer" data-content="<?php echo esc_attr( $site_password ); ?>" data-text-copied="<?php echo esc_attr__( 'Copied!', 'iwp-mu' ); ?>">
                    <svg class="mr-2" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 10H2.5C1.67157 10 1 9.32843 1 8.5V2.5C1 1.67157 1.67157 1 2.5 1H8.5C9.32843 1 10 1.67157 10 2.5V4M5.5 13H11.5C12.3284 13 13 12.3284 13 11.5V5.5C13 4.67157 12.3284 4 11.5 4H5.5C4.67157 4 4 4.67157 4 5.5V11.5C4 12.3284 4.67157 13 5.5 13Z" stroke="#D4D4D8" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            </div>
        </div>

        <div class="iwp-external-links flex items-center justify-between">
            <div class="flex items-center space-x-3 text-sm font-inter font-medium text-yellowCust-100">
                <a target="_blank" href="<?php echo esc_url( Helper::get_args_option( 'manage_site_url', $iwp_details_site ) ); ?>" class="flex items-center gap-2 focus:text-yellowCust-100 hover:text-yellowCust-100 active:text-yellowCust-100 visited:text-yellowCust-100">
                    <span><?php esc_html_e( 'Manage Site', 'iwp-mu' ); ?></span>
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5.5 2.5H2.5C1.67157 2.5 1 3.17157 1 4V11.5C1 12.3284 1.67157 13 2.5 13H10C10.8284 13 11.5 12.3284 11.5 11.5V8.5M8.5 1H13M13 1V5.5M13 1L5.5 8.5" stroke="#F3E98D" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
<!--                <a href="--><?php //echo esc_url( Helper::get_args_option( 'reserve_site_url', $iwp_details_site ) ); ?><!--" class="flex items-center gap-2 focus:text-yellowCust-100 hover:text-yellowCust-100 active:text-yellowCust-100 visited:text-yellowCust-100">-->
<!--                    <span>--><?php //esc_html_e( 'Reserve Site', 'iwp-mu' ); ?><!--</span>-->
<!--                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">-->
<!--                        <path d="M5.5 2.5H2.5C1.67157 2.5 1 3.17157 1 4V11.5C1 12.3284 1.67157 13 2.5 13H10C10.8284 13 11.5 12.3284 11.5 11.5V8.5M8.5 1H13M13 1V5.5M13 1L5.5 8.5" stroke="#F3E98D" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>-->
<!--                    </svg>-->
<!--                </a>-->
<!--                <span class="text-sm font-inter font-medium text-redCust-100">--><?php //printf( esc_html__( 'Expiring in %s days %s hours and %s minutes', 'iwp-mu' ), $days, $hours, $minutes ); ?><!--</span>-->
            </div>
            <div class="iwp-logo flex-none self-end cursor-pointer">
                <svg width="50" height="44" viewBox="0 0 50 44" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M41.4225 16.8945H48.365C48.4823 16.8942 48.5982 16.9266 48.7004 16.9885C48.8027 17.0505 48.8883 17.1399 48.9473 17.2476C49.007 17.3554 49.038 17.4778 49.0387 17.6026C49.0394 17.7275 49.0092 17.8503 48.9516 17.9588L36.4584 41.4547C36.2684 41.8127 35.992 42.1106 35.6574 42.3181C35.3228 42.5254 34.9428 42.635 34.5549 42.6353H29.2403C28.7976 42.6353 28.3652 42.4925 28.001 42.2259C27.6361 41.9593 27.357 41.5815 27.2 41.1425L25.8989 37.5185L25.9904 37.5838C26.4062 37.8692 26.8726 38.0608 27.3613 38.1471C27.8506 38.2333 28.3508 38.2121 28.8316 38.0849C29.3122 37.9577 29.7627 37.7271 30.1564 37.4076C30.5493 37.088 30.8767 36.6863 31.1171 36.2271L38.6137 21.9972H32.6327C32.5047 22 32.3786 21.9638 32.2692 21.8931C32.1598 21.8224 32.0713 21.72 32.0152 21.598C31.9591 21.476 31.9368 21.3395 31.9512 21.2045C31.9656 21.0697 32.0166 20.942 32.0973 20.8365L47.128 1.02141C47.2042 0.934338 47.3064 0.878104 47.4173 0.862371C47.5288 0.846637 47.6411 0.872386 47.7361 0.935191C47.831 0.997996 47.903 1.09393 47.9383 1.20652C47.9735 1.31911 47.9714 1.44131 47.9311 1.55212L41.4225 16.8945Z"
                          fill="white"/>
                    <path d="M17.0128 41.2134C17.0128 41.3998 16.9783 41.5842 16.9107 41.7564C16.8437 41.9286 16.7451 42.085 16.6207 42.2169C16.4968 42.3486 16.3486 42.4531 16.1866 42.5244C16.0241 42.5957 15.8498 42.6324 15.6742 42.6324H9.97389C9.69755 42.6324 9.42839 42.542 9.20243 42.3732C8.97645 42.2046 8.8059 41.966 8.71306 41.6902L8.03587 39.6497L1.08258 18.81C1.0099 18.5934 0.987585 18.3615 1.01709 18.1338C1.04732 17.9061 1.12792 17.6893 1.25314 17.5017C1.37836 17.314 1.54388 17.1609 1.73675 17.0552C1.92889 16.9495 2.14263 16.8943 2.35924 16.8944H7.56737C7.90849 16.8941 8.24169 17.0055 8.52164 17.2132C8.80158 17.4209 9.01387 17.7148 9.13045 18.0551L16.5601 39.6241L16.9409 40.7252C16.9913 40.8822 17.0157 41.0474 17.0128 41.2134Z" fill="white"/>
                    <path d="M22.7929 28.891L18.5094 41.7246C18.4195 41.9925 18.2532 42.2243 18.0344 42.3884C17.8157 42.5524 17.5537 42.6408 17.286 42.6413H9.97363C9.69655 42.6407 9.42668 42.5489 9.20072 42.3786C8.97474 42.2083 8.80419 41.9679 8.7128 41.6905L8.03271 39.65C8.16657 39.8827 9.28275 41.699 10.6724 39.6244L17.8157 18.8841C17.8157 18.8841 18.6728 18.3335 19.1974 18.8841L22.7929 28.891Z" fill="white"/>
                    <path d="M32.4531 33.704L29.2219 39.8256C29.0894 40.076 28.893 40.2814 28.6548 40.4172C28.4158 40.5532 28.146 40.6141 27.8761 40.5931C27.6063 40.5718 27.3472 40.4694 27.1305 40.2978C26.914 40.1261 26.7477 39.8924 26.6512 39.6242L22.7932 28.8907L19.3288 19.2414C19.2539 19.0388 19.1143 18.8708 18.9337 18.7669C18.7538 18.6631 18.5443 18.63 18.3436 18.6738C18.1558 18.7155 17.9744 18.7881 17.8082 18.8896C17.9852 18.3644 18.299 17.903 18.7127 17.5584C19.218 17.1292 19.8462 16.8951 20.4932 16.8944H25.2703C25.7661 16.8932 26.2497 17.0542 26.6564 17.3554C27.0622 17.6565 27.371 18.0833 27.5401 18.5773L32.5013 32.9775C32.5437 33.095 32.561 33.221 32.5532 33.3463C32.5446 33.4718 32.5106 33.5938 32.4531 33.704Z" fill="white"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="iwp-dashboard-footer bg-white border border-[#E4E4E7] rounded-b-lg p-8">
        <h6 class="text-[#71717A] uppercase font-sm mb-6"><?php esc_html_e( 'In partnership with', 'iwp-mu' ); ?></h6>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

			<?php foreach ( $iwp_details_partners as $index => $partner ) {
				$name        = Helper::get_args_option( 'name', $partner );
				$logo_url    = Helper::get_args_option( 'logo_url', $partner );
				$description = Helper::get_args_option( 'description', $partner );
				$slug        = Helper::get_args_option( 'slug', $partner );
				$plugin_file = Helper::get_args_option( 'plugin_file', $partner );
				$zip_url     = Helper::get_args_option( 'zip_url', $partner );
				$cta_text    = Helper::get_args_option( 'cta_text', $partner );
				$cta_link    = Helper::get_args_option( 'cta_link', $partner );
				$classes     = 'iwp-dashboard-partner flex space-x-2 ';
				$btn_classes = 'iwp-mu-install-plugin bg-[#11BF85] text-white px-4 py-1 min-h-[36px] rounded-lg flex items-center gap-2 ';

				if ( $index === 0 || $index % 2 !== 0 ) {
					$classes .= 'border-solid border-r-2 border-[#D4D4D8] ';
				}

				if ( iwp_is_plugin_active( $plugin_file ) ) {
					$btn_classes   .= 'activated ';
					$plugin_action = '';
					$btn_text      = esc_html__( 'Activated', 'iwp-mu' );
				} elseif ( iwp_is_plugin_installed( $plugin_file ) ) {
					$btn_classes   .= 'installed ';
					$plugin_action = 'activate';
					$btn_text      = esc_html__( 'Activate Plugin', 'iwp-mu' );
				} else {
					$btn_classes   .= 'install ';
					$plugin_action = 'install_activate';
					$btn_text      = esc_html__( 'Install Plugin', 'iwp-mu' );
				}

				?>
                <div class="<?php echo esc_attr( $classes ); ?>">
                    <div class="flex-none w-8 h-8 overflow-hidden rounded-lg">
                        <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $name ); ?>">
                    </div>
                    <div class="min-w-0 flex-auto px-2">
                        <div class="text-[20px] leading-[30px] text-[#27272A] font-inter font-medium mb-2"><?php echo esc_html( $name ); ?></div>
                        <div class="text-[14px] leading-[21px] text-[#71717A]"><?php echo esc_html( $description ); ?></div>
                        <div class="flex items-center gap-3 mt-4">
                            <button class="<?php echo esc_attr( $btn_classes ); ?>"
                                    data-text-installing="<?php echo esc_attr__( 'Installing...', 'iwp-mu' ); ?>"
                                    data-text-installed="<?php echo esc_attr__( 'Installed', 'iwp-mu' ); ?>"
                                    data-text-activating="<?php echo esc_attr__( 'Activating...', 'iwp-mu' ); ?>"
                                    data-text-activated="<?php echo esc_attr__( 'Activated', 'iwp-mu' ); ?>"
                                    data-install-nonce="<?php echo wp_create_nonce( 'iwp_install_plugin' ); ?>"
                                    data-slug="<?php echo esc_attr( $slug ); ?>"
                                    data-zip-url="<?php echo esc_url( $zip_url ); ?>"
                                    data-plugin-action="<?php echo esc_attr( $plugin_action ); ?>"
                                    data-plugin-file="<?php echo esc_attr( $plugin_file ); ?>">
                                <svg class="icon-install" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 10L1 10.75C1 11.9926 2.00736 13 3.25 13L10.75 13C11.9926 13 13 11.9926 13 10.75L13 10M10 7L7 10M7 10L4 7M7 10L7 1" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <svg class="icon-installed" width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672 13.684 16.6m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672ZM12 2.25V4.5m5.834.166-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243-1.59-1.59"/>
                                </svg>
                                <svg class="icon-activated" width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                </svg>
                                <span><?php echo $btn_text; ?></span>
                            </button>
                            <a href="<?php echo esc_url( $cta_link ); ?>" class="iwp-cta-link flex items-center gap-2 text-primary-900 hover:text-primary-900" target="_blank">
                                <span class="text-sm"><?php echo esc_html( $cta_text ); ?></span>
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5.5 2.5H2.5C1.67157 2.5 1 3.17157 1 4V11.5C1 12.3284 1.67157 13 2.5 13H10C10.8284 13 11.5 12.3284 11.5 11.5V8.5M8.5 1H13M13 1V5.5M13 1L5.5 8.5" stroke="#005E54" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
			<?php } ?>
        </div>
    </div>
</div>
