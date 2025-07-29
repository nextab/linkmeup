<?php
#region Clean Up WP Admin Bar
function remove_admin_bar_links() {
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu('wp-logo');          // Remove the Wordpress logo + sub links
	// $wp_admin_bar->remove_menu('site-name');        // Remove the site name menu
	// $wp_admin_bar->remove_menu('view-site');        // Remove the view site link
	// $wp_admin_bar->remove_menu('updates');          // Remove the updates link
	// $wp_admin_bar->remove_menu('comments');         // Remove the comments link
	$wp_admin_bar->remove_menu('new-content');      // Remove the content link
	// $wp_admin_bar->remove_menu('my-account');       // Remove the user details tab
}
add_action( 'wp_before_admin_bar_render', 'remove_admin_bar_links' );
#endregion

#region Replace Logo on WP Login Screen
add_action('login_head', 'nextab_custom_login_logo');
function nextab_custom_login_logo() {
	$theme_dir = get_stylesheet_directory_uri();
	echo '<style type="text/css">
	h1 a { background-image:url("'. $theme_dir . '/assets/img/link-me-up-logo.svg") !important; background-size: 320px 290px !important; width: 320px !important; height: 290px !important; margin-bottom: 40px !important; padding-bottom: 0 !important; }
	.login form { margin-top: 10px !important; }
	</style>';
}
#endregion

#region Pimp my login screen
function nextab_url_login_logo(){
	return get_bloginfo( 'wpurl' );
}
add_filter('login_headerurl', 'nextab_url_login_logo');

/* function nextab_login_logo_url_title() {
	return 'Zurück zu Link Me Up';
}
add_filter( 'login_headertext', 'nextab_login_logo_url_title' ); */

function nextab_add_dashboard_widgets() {
	wp_add_dashboard_widget('wp_dashboard_widget', 'Designer & Developer Info', 'nextab_theme_info');
}
add_action('wp_dashboard_setup', 'nextab_add_dashboard_widgets' );
 
function nextab_theme_info() {
	echo '<ul>
	<li><strong>Entwickelt von:</strong> <a href="https://nextab.de">nexTab.de</a></li>
	<li><strong>E-Mail:</strong> <a href="mailto:info@nextab.de">info@nextab.de</a></li>
	</ul>';
}
#endregion Pimp my login screen

#region Add custom styling to Gutenberg backend editor
function nxt_add_gutenberg_styles() {
	// Add support for custom styles in Gutenberg editor.
	add_theme_support( 'editor-styles' );

	// Enqueue frontend stylesheet in Gutenberg editor as well as additional styles just for the backend.
	// add_editor_style( 'style.css' );
	
	// Enqueue custom styles for the Gutenberg backend editor
	wp_enqueue_style('nxt_gutenberg_custom_styles', get_stylesheet_directory_uri() . '/assets/css/nxt_editor-custom-styles.css', [], filemtime(get_stylesheet_directory() . '/assets/css/nxt_editor-custom-styles.css'));
	
	// Add functionality to extend core Gutenberg blocks (allow groups to hide, make details / accordions work, etc.)
	wp_register_script('nxt_hack_core_blocks', get_stylesheet_directory_uri() . '/assets/js/hack_core_blocks.js', false, '', true);
	wp_enqueue_script('nxt_hack_core_blocks');
}
add_action( 'enqueue_block_editor_assets', 'nxt_add_gutenberg_styles' );
#endregion Add custom styling to Gutenberg backend editor

#region Enqueue custom styles in frontend
function enqueue_theme_styles() {
	// Enqueue the main stylesheet
	wp_enqueue_style('theme_styles', get_stylesheet_uri());
	wp_register_script('frontend_scripts', get_stylesheet_directory_uri() . '/assets/js/frontend_scripts.js', false, '', true);
	wp_enqueue_script('frontend_scripts');
}
add_action('wp_enqueue_scripts', 'enqueue_theme_styles');
#endregion Enqueue custom styles in frontend

#region Enqueue Gutenberg Scripts
add_theme_support( 'custom-spacing' );
add_theme_support( 'border' );
add_theme_support( 'appearance-tools' );
add_theme_support( 'editor-color-palette' );
#endregion Enqueue Gutenberg Scripts

#region Change Gutenberg crap behavior when it comes to responsive websites
function add_viewport_meta_tag() {
	echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
}
add_action('wp_head', 'add_viewport_meta_tag');
#endregion Change Gutenberg crap behavior when it comes to responsive websites

#region Allow .svg files
function nxt_allow_svg($mimes) {
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
}
add_filter('upload_mimes', 'nxt_allow_svg');

function nxt_really_allow_svg($checked, $file, $filename, $mimes){
	if(!$checked['type']){
		$wp_filetype = wp_check_filetype( $filename, $mimes );
		$ext = $wp_filetype['ext'];
		$type = $wp_filetype['type'];
		$proper_filename = $filename;
		if($type && 0 === strpos($type, 'image/') && $ext !== 'svg'){
			$ext = $type = false;
		}	
		$checked = compact('ext','type','proper_filename');
	}
	return $checked;
}
add_filter('wp_check_filetype_and_ext', 'nxt_really_allow_svg', 10, 4);
#endregion Allow .svg files

#region Redirect non-admins from Dashboard
function nxt_redirect_non_admins_from_dashboard() {
    if (
        is_admin()
        && is_user_logged_in()
        && !current_user_can('administrator')
		&& !(defined('DOING_AJAX') && DOING_AJAX)
    ) {
        wp_safe_redirect(home_url(), 302, 'nxt redirect non admins from dashboard');
        exit;
    }
}
add_action('init', 'nxt_redirect_non_admins_from_dashboard');
#endregion Redirect non-admins from Dashboard

#region Do not show admin bar for non-admins
function nxt_hide_admin_bar_for_non_admins() {
    if (!current_user_can('administrator')) {
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'nxt_hide_admin_bar_for_non_admins');
#endregion Do not show admin bar for non-admins

#region Sanitize names of uploaded files
function sanitize_upload_name($filename) {
	$sanitized_filename = remove_accents($filename); // Convert to ASCII

	// Standard replacements
	$invalid = array(
	' ' => '-',
	'%20' => '-',
	'_' => '-',
	);
	$sanitized_filename = str_replace(array_keys($invalid), array_values($invalid), $sanitized_filename);

	// Remove all non-alphanumeric except .
	$sanitized_filename = preg_replace('/[^A-Za-z0-9-\. ]/', '', $sanitized_filename);
	// Remove all but last .
	$sanitized_filename = preg_replace('/\.(?=.*\.)/', '-', $sanitized_filename);
	// Replace any more than one - in a row
	$sanitized_filename = preg_replace('/-+/', '-', $sanitized_filename);
	// Remove last - if at the end
	$sanitized_filename = str_replace('-.', '.', $sanitized_filename);
	// Lowercase
	$sanitized_filename = strtolower($sanitized_filename);
	return $sanitized_filename;
}	
add_filter("sanitize_file_name", "sanitize_upload_name", 10, 1);
#endregion Sanitize names of uploaded files

#region Pattern Category Registration
// Register custom pattern category
add_action( 'init', function() {
	register_block_pattern_category( 'linkmeup', [
		'label' => __( 'Link Me Up', 'linkmeup' ),
		'description' => __( 'Custom patterns from Link Me Up theme', 'linkmeup' ),
	] );
} );
#endregion