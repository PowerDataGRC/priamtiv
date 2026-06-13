<?php
/**
 * PowerData Theme — functions.php (PRIAM child)
 * Genesis child theme for priamtiv.com
 *
 * @package PowerData
 */

// ── 1. CONSTANTS ─────────────────────────────────────────────────────────────
define( 'POWERDATA_VERSION',   '1.1.0' );
define( 'POWERDATA_DIR',       get_stylesheet_directory() );
define( 'POWERDATA_URI',       get_stylesheet_directory_uri() );
define( 'POWERDATA_SITE_NAME', 'PRIAM' );
define( 'POWERDATA_SITE_URL',  'https://priamtiv.com' );

// ── 2. GENESIS SETUP ─────────────────────────────────────────────────────────
add_action( 'genesis_setup', 'powerdata_genesis_setup', 15 );
function powerdata_genesis_setup() {

	// Support for custom logo
	add_theme_support( 'custom-logo', [
		'height'      => 74,
		'width'       => 394,
		'flex-height' => true,
		'flex-width'  => true,
		'header-text' => [ '.site-title', '.site-description' ],
	] );

	// HTML5 markup
	add_theme_support( 'html5', [
		'search-form', 'comment-form', 'comment-list',
		'gallery', 'caption', 'style', 'script',
	] );

	// Remove Genesis layout options we don't need
	genesis_unregister_layout( 'content-sidebar' );
	genesis_unregister_layout( 'sidebar-content' );
	genesis_unregister_layout( 'content-sidebar-sidebar' );
	genesis_unregister_layout( 'sidebar-content-sidebar' );
	genesis_unregister_layout( 'sidebar-sidebar-content' );

	// Default layout: full width
	add_theme_support( 'genesis-full-width-content' );

	// Structural wrap
	add_theme_support( 'genesis-structural-wrap', [
		'header', 'menu-primary', 'menu-secondary', 'footer-widgets', 'footer',
	] );

	// Remove the default Genesis breadcrumbs
	remove_action( 'genesis_before_loop', 'genesis_do_breadcrumbs' );

	// Force full-width layout site-wide
	add_filter( 'genesis_site_layout', '__genesis_return_full_width_content' );

	// Move primary nav inside the header so logo and links share one bar
	remove_action( 'genesis_after_header', 'genesis_do_nav' );
	add_action( 'genesis_header', 'genesis_do_nav', 12 );

	// Only register primary nav
	add_theme_support( 'genesis-menus', [ 'primary' => __( 'Primary Navigation', 'powerdata-theme' ) ] );
}

// Deregister the Genesis header-right sidebar
add_action( 'widgets_init', function () {
	unregister_sidebar( 'header-right' );
}, 20 );

// ── 3. ENQUEUE STYLES & SCRIPTS ──────────────────────────────────────────────
add_action( 'wp_enqueue_scripts', 'powerdata_enqueue_assets' );
function powerdata_enqueue_assets() {

	// Google Fonts
	wp_enqueue_style(
		'powerdata-fonts',
		'https://fonts.googleapis.com/css2?family=Schibsted+Grotesk:wght@400;500;600;700;800&family=Hanken+Grotesk:wght@400;500;600;700&family=Instrument+Serif:ital@0;1&display=swap',
		[],
		null
	);

	// Genesis parent stylesheet (required)
	wp_enqueue_style(
		'genesis-style',
		get_template_directory_uri() . '/style.css',
		[],
		POWERDATA_VERSION
	);

	// Child theme stylesheet
	wp_enqueue_style(
		'powerdata-style',
		POWERDATA_URI . '/style.css',
		[ 'genesis-style' ],
		POWERDATA_VERSION
	);

	// Site JS (scroll reveal + mobile nav)
	wp_enqueue_script(
		'powerdata-site',
		POWERDATA_URI . '/assets/site.js',
		[],
		POWERDATA_VERSION,
		true
	);

	// Cloudflare Turnstile — load on pages/posts that have a contact form
	if ( powerdata_page_has_form() ) {
		wp_enqueue_script(
			'cf-turnstile',
			'https://challenges.cloudflare.com/turnstile/v0/api.js',
			[],
			null,
			true
		);
	}
}

/**
 * Decide which pages need Turnstile.
 */
function powerdata_page_has_form() {
	if ( is_page( [ 'contact' ] ) ) return true;
	global $post;
	if ( $post && has_shortcode( $post->post_content, 'pd_turnstile' ) ) return true;
	return false;
}

// ── 4. CLOUDFLARE TURNSTILE SHORTCODE ────────────────────────────────────────
/**
 * Usage: [pd_turnstile]
 * Keys defined in wp-config.php:
 *   define( 'CF_TURNSTILE_SITE_KEY',   'YOUR_SITE_KEY_HERE' );
 *   define( 'CF_TURNSTILE_SECRET_KEY', 'YOUR_SECRET_KEY_HERE' );
 */
add_shortcode( 'pd_turnstile', 'powerdata_turnstile_widget' );
function powerdata_turnstile_widget( $atts ) {
	$atts = shortcode_atts( [
		'theme'   => 'light',
		'size'    => 'normal',
		'action'  => '',
	], $atts, 'pd_turnstile' );

	$site_key = defined( 'CF_TURNSTILE_SITE_KEY' ) ? CF_TURNSTILE_SITE_KEY : get_option( 'cf_turnstile_site_key', '' );

	if ( empty( $site_key ) ) {
		if ( current_user_can( 'manage_options' ) ) {
			return '<p style="color:red;font-size:13px;">⚠ Turnstile: add CF_TURNSTILE_SITE_KEY to wp-config.php</p>';
		}
		return '';
	}

	return sprintf(
		'<div class="cf-turnstile" data-sitekey="%s" data-theme="%s" data-size="%s"%s></div>',
		esc_attr( $site_key ),
		esc_attr( $atts['theme'] ),
		esc_attr( $atts['size'] ),
		$atts['action'] ? ' data-action="' . esc_attr( $atts['action'] ) . '"' : ''
	);
}

/**
 * Server-side Turnstile verification helper.
 */
function powerdata_verify_turnstile( $token ) {
	$secret = defined( 'CF_TURNSTILE_SECRET_KEY' ) ? CF_TURNSTILE_SECRET_KEY : get_option( 'cf_turnstile_secret_key', '' );

	if ( empty( $secret ) || empty( $token ) ) return false;

	$response = wp_remote_post(
		'https://challenges.cloudflare.com/turnstile/v0/siteverify',
		[
			'body' => [
				'secret'   => $secret,
				'response' => $token,
				'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
			],
		]
	);

	if ( is_wp_error( $response ) ) return false;

	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	return ! empty( $body['success'] );
}

// ── 5. AJAX FORM HANDLER ─────────────────────────────────────────────────────
add_action( 'wp_ajax_nopriv_pd_contact_form', 'powerdata_handle_contact_form' );
add_action( 'wp_ajax_pd_contact_form',        'powerdata_handle_contact_form' );
function powerdata_handle_contact_form() {
	check_ajax_referer( 'pd_contact_nonce', 'nonce' );

	$token = sanitize_text_field( $_POST['cf-turnstile-response'] ?? '' );
	if ( ! powerdata_verify_turnstile( $token ) ) {
		wp_send_json_error( [ 'message' => 'Human verification failed. Please try again.' ] );
	}

	$name    = sanitize_text_field(     $_POST['pd_name']    ?? '' );
	$email   = sanitize_email(          $_POST['pd_email']   ?? '' );
	$company = sanitize_text_field(     $_POST['pd_company'] ?? '' );
	$message = sanitize_textarea_field( $_POST['pd_message'] ?? '' );

	if ( empty( $name ) || ! is_email( $email ) || empty( $message ) ) {
		wp_send_json_error( [ 'message' => 'Please fill in all required fields.' ] );
	}

	$to      = get_option( 'admin_email' );
	$subject = 'PRIAM — New walkthrough request from ' . $name;
	$body    = "Name: {$name}\nEmail: {$email}";
	if ( $company ) $body .= "\nCompany: {$company}";
	$body .= "\n\nMessage:\n{$message}";
	$headers = [
		'Content-Type: text/plain; charset=UTF-8',
		'From: PRIAM <noreply@priamtiv.com>',
		'Reply-To: ' . $name . ' <' . $email . '>',
	];

	$sent = wp_mail( $to, $subject, $body, $headers );

	if ( $sent ) {
		wp_send_json_success( [ 'message' => "Message sent! We will be in touch within one business day." ] );
	} else {
		wp_send_json_error( [ 'message' => 'Sorry, there was a problem sending your message. Please email us directly at hello@priamtiv.com.' ] );
	}
}

// ── 5b. OUTBOUND SMTP ────────────────────────────────────────────────────────
// Define in wp-config.php (outside git):
//   define( 'SMTP_PASSWORD', '...' );
add_action( 'phpmailer_init', 'powerdata_smtp_config' );
function powerdata_smtp_config( $phpmailer ) {
	$phpmailer->isSMTP();
	$phpmailer->Host       = 'smtp.hostinger.com';
	$phpmailer->SMTPAuth   = true;
	$phpmailer->Port       = 465;
	$phpmailer->SMTPSecure = 'ssl';
	$phpmailer->Username   = 'hello@priamtiv.com';
	$phpmailer->Password   = defined( 'SMTP_PASSWORD' ) ? SMTP_PASSWORD : '';
}

// ── 6. LOCALIZE AJAX DATA FOR JS ─────────────────────────────────────────────
add_action( 'wp_enqueue_scripts', 'powerdata_localize_ajax' );
function powerdata_localize_ajax() {
	wp_localize_script( 'powerdata-site', 'pdAjax', [
		'url'          => admin_url( 'admin-ajax.php' ),
		'contactNonce' => wp_create_nonce( 'pd_contact_nonce' ),
	] );
}

// ── 7. JSON-LD STRUCTURED DATA ────────────────────────────────────────────────
add_action( 'wp_head', 'powerdata_json_ld', 5 );
function powerdata_json_ld() {
	$schema = [
		'@context' => 'https://schema.org',
		'@graph'   => [
			[
				'@type'  => 'Organization',
				'@id'    => 'https://powerdatainc.com/#organization',
				'name'   => 'PowerData Solutions Inc.',
				'url'    => 'https://powerdatainc.com',
				'sameAs' => [
					'https://www.linkedin.com/company/powerdatasolutions/',
				],
			],
			[
				'@type'               => 'SoftwareApplication',
				'@id'                 => POWERDATA_SITE_URL . '/#application',
				'name'                => 'PRIAM',
				'url'                 => POWERDATA_SITE_URL,
				'applicationCategory' => 'BusinessApplication',
				'operatingSystem'     => 'Web',
				'description'         => 'The simple GRC platform for small businesses — policies, risk, incidents, and assets in one place from day one.',
				'offers'              => [
					'@type'       => 'Offer',
					'description' => 'Free trial available. No credit card required.',
				],
				'provider'            => [ '@id' => 'https://powerdatainc.com/#organization' ],
				'contactPoint'        => [
					'@type'       => 'ContactPoint',
					'contactType' => 'customer service',
					'url'         => POWERDATA_SITE_URL . '/contact/',
				],
			],
			[
				'@type'     => 'WebSite',
				'@id'       => POWERDATA_SITE_URL . '/#website',
				'url'       => POWERDATA_SITE_URL,
				'name'      => 'PRIAM',
				'publisher' => [ '@id' => 'https://powerdatainc.com/#organization' ],
			],
		],
	];

	if ( is_singular() ) {
		global $post;
		$schema['@graph'][] = [
			'@type'         => 'WebPage',
			'@id'           => get_permalink() . '#webpage',
			'url'           => get_permalink(),
			'name'          => wp_get_document_title(),
			'description'   => wp_strip_all_tags( get_the_excerpt( $post ) ),
			'isPartOf'      => [ '@id' => POWERDATA_SITE_URL . '/#website' ],
			'datePublished' => get_the_date( 'c', $post ),
			'dateModified'  => get_the_modified_date( 'c', $post ),
			'inLanguage'    => 'en-US',
		];
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) . '</script>' . "\n";
}

// ── 8. SEO META TAGS (Open Graph + Twitter Card) ─────────────────────────────
add_action( 'wp_head', 'powerdata_og_meta', 2 );
function powerdata_og_meta() {
	global $post;

	$title       = wp_get_document_title();
	$description = get_bloginfo( 'description' );
	$url         = home_url( '/' );
	$image       = POWERDATA_URI . '/assets/og-default.png';

	if ( is_singular() && $post ) {
		$description = wp_strip_all_tags( get_the_excerpt( $post ) ) ?: $description;
		$url         = get_permalink();
		if ( has_post_thumbnail( $post ) ) {
			$thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $post ), 'large' );
			if ( $thumb ) $image = $thumb[0];
		}
	}
	?>
<meta property="og:type"        content="website">
<meta property="og:site_name"   content="PRIAM">
<meta property="og:title"       content="<?php echo esc_attr( $title ); ?>">
<meta property="og:description" content="<?php echo esc_attr( $description ); ?>">
<meta property="og:url"         content="<?php echo esc_url( $url ); ?>">
<meta property="og:image"       content="<?php echo esc_url( $image ); ?>">
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="<?php echo esc_attr( $title ); ?>">
<meta name="twitter:description" content="<?php echo esc_attr( $description ); ?>">
<meta name="twitter:image"       content="<?php echo esc_url( $image ); ?>">
	<?php
}

// ── 9. REGISTER BLOCK PATTERNS ───────────────────────────────────────────────
add_action( 'init', 'powerdata_register_block_patterns' );
function powerdata_register_block_patterns() {

	register_block_pattern_category( 'powerdata', [
		'label' => 'PowerData',
	] );

	$pattern_dir = POWERDATA_DIR . '/patterns';
	if ( is_dir( $pattern_dir ) ) {
		foreach ( glob( $pattern_dir . '/*.php' ) as $pattern_file ) {
			require $pattern_file;
		}
	}
}

// ── 10. REMOVE SITEORIGIN PAGE BUILDER (harmless if plugin is absent) ────────
add_action( 'wp_enqueue_scripts', 'powerdata_dequeue_siteorigin', 100 );
function powerdata_dequeue_siteorigin() {
	wp_dequeue_script( 'siteorigin-panels-front-styles' );
	wp_dequeue_style(  'siteorigin-panels-front' );
	wp_dequeue_style(  'siteorigin-panels-front-flex' );
}

// ── 11. GENESIS HEADER / NAV TWEAKS ──────────────────────────────────────────
add_filter( 'genesis_seo_title', 'powerdata_custom_title_html', 10, 3 );
function powerdata_custom_title_html( $title, $inside, $wrap ) {
	if ( function_exists( 'get_custom_logo' ) && has_custom_logo() ) {
		$inside = get_custom_logo();
	}
	return '<' . $wrap . ' class="site-title"><a href="' . home_url( '/' ) . '" rel="home">' . $inside . '</a></' . $wrap . '>';
}
remove_action( 'genesis_site_description', 'genesis_seo_site_description' );

// ── 11b. MOBILE NAVIGATION ────────────────────────────────────────────────
add_action( 'genesis_header', 'powerdata_mobile_nav_toggle', 13 );
function powerdata_mobile_nav_toggle() {
	?>
	<button class="pd-nav-toggle" data-menu-toggle aria-expanded="false" aria-controls="pd-mobile-menu" aria-label="Open navigation">
		<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
			<line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
		</svg>
	</button>
	<?php
}

add_action( 'genesis_after_header', 'powerdata_mobile_menu', 4 );
function powerdata_mobile_menu() {
	?>
	<nav id="pd-mobile-menu" aria-label="Mobile navigation">
		<?php wp_nav_menu( [
			'theme_location' => 'primary',
			'container'      => false,
		] ); ?>
		<a class="btn btn-primary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" style="margin-top:16px;width:100%;justify-content:center;display:inline-flex;">Let's Talk →</a>
	</nav>
	<?php
}

// ── 12. REMOVE GENESIS FEATURES WE DON'T USE ────────────────────────────────
remove_action( 'genesis_before_loop', 'genesis_do_breadcrumbs' );
add_filter( 'genesis_footer_backtotop_text', '__return_empty_string' );
add_filter( 'genesis_footer_creds_text',     '__return_empty_string' );
remove_action( 'genesis_footer', 'genesis_footer_markup_open', 5 );
remove_action( 'genesis_footer', 'genesis_do_footer' );
remove_action( 'genesis_footer', 'genesis_footer_markup_close', 15 );

// Add custom footer
add_action( 'genesis_footer', 'powerdata_custom_footer', 10 );
function powerdata_custom_footer() {
	?>
	<footer class="site-footer" itemscope itemtype="https://schema.org/WPFooter">
		<div class="pd-wrap">
			<div class="pd-footer-top">

				<div>
					<div style="margin-bottom:16px;">
						<?php if ( has_custom_logo() ) : ?>
							<?php echo get_custom_logo(); ?>
						<?php else : ?>
							<span style="font-family:var(--font-display);font-weight:700;font-size:22px;color:#fff;">PRIAM</span>
						<?php endif; ?>
					</div>
					<p class="pd-foot-desc">The simple GRC platform for small businesses — policies, risk, incidents, and assets in one place from day one.</p>
					<p style="margin-top:20px;">
						<a class="btn btn-sm btn-outline-light" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Let's Talk →</a>
					</p>
				</div>

				<div>
					<h5>Platform</h5>
					<ul>
						<li><a href="<?php echo esc_url( home_url( '/priam/#platform' ) ); ?>">Platform</a></li>
						<li><a href="<?php echo esc_url( home_url( '/priam/#how' ) ); ?>">How it works</a></li>
						<li><a href="<?php echo esc_url( home_url( '/priam/#why-priam' ) ); ?>">Why PRIAM</a></li>
						<li><a href="<?php echo esc_url( home_url( '/priam/#faq' ) ); ?>">FAQ</a></li>
						<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a></li>
					</ul>
				</div>

				<div>
					<h5>Company</h5>
					<ul>
						<li><a href="https://powerdatainc.com" target="_blank" rel="noopener">PowerData ↗</a></li>
						<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a></li>
					</ul>
				</div>

			</div>
			<div class="pd-footer-bottom">
				<span>© <?php echo esc_html( date( 'Y' ) ); ?> PowerData Solutions Inc. All rights reserved.</span>
				<div class="links">
					<a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>">Privacy</a>
					<a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">Terms</a>
				</div>
			</div>
		</div>
	</footer>
	<?php
}

// ── 13. GUTENBERG / BLOCK EDITOR SUPPORT ─────────────────────────────────────
add_theme_support( 'align-wide' );
add_theme_support( 'editor-styles' );
add_theme_support( 'responsive-embeds' );
add_theme_support( 'wp-block-styles' );

add_editor_style( 'assets/editor-style.css' );

add_filter( 'should_load_separate_core_block_assets', '__return_false' );

// ── 14. HIDE PAGE TITLE OPTION ────────────────────────────────────────────────
add_action( 'add_meta_boxes', 'powerdata_add_page_options_box' );
function powerdata_add_page_options_box() {
	add_meta_box(
		'powerdata-page-options',
		'Page Options',
		'powerdata_page_options_html',
		[ 'page', 'post' ],
		'side',
		'default'
	);
}

function powerdata_page_options_html( $post ) {
	wp_nonce_field( 'powerdata_page_options', 'powerdata_page_options_nonce' );
	$hide = get_post_meta( $post->ID, '_pd_hide_title', true );
	echo '<label style="display:flex;align-items:center;gap:8px;font-size:13px;">';
	echo '<input type="checkbox" name="pd_hide_title" value="1" ' . checked( $hide, '1', false ) . '>';
	echo 'Hide page title</label>';
}

add_action( 'save_post', 'powerdata_save_page_options' );
function powerdata_save_page_options( $post_id ) {
	if ( ! isset( $_POST['powerdata_page_options_nonce'] ) ) return;
	if ( ! wp_verify_nonce( $_POST['powerdata_page_options_nonce'], 'powerdata_page_options' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	update_post_meta( $post_id, '_pd_hide_title', isset( $_POST['pd_hide_title'] ) ? '1' : '' );
}

add_filter( 'genesis_post_title_output', 'powerdata_maybe_hide_title' );
function powerdata_maybe_hide_title( $title ) {
	if ( get_post_meta( get_the_ID(), '_pd_hide_title', true ) ) {
		return '';
	}
	return $title;
}
