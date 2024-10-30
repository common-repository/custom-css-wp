<?php
/**
 * Plugin Name: Custom CSS for Wordpress
 * Plugin URI: http://www.chetanprajapati.com
 * Description: The Easy and Lightweight Plugin to add custom CSS to your wordpress website. This plugin will help you to override Theme or Plugin CSS.
 * Version: 1.0.0
 * Author: Chetan Prajapati
 * Author URI: http://www.chetanprajapati.com
 * Text Domain: ccsswp
 * License: GPL2

 * Copyright 2016 Chetan Prajapati

 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.

 * This program is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program; if not, see <http://www.gnu.org/licenses/>.
*/

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CCSSWP_PATH', __FILE__ );

if( ! is_admin() ) {

	// Add Frontend CSS Query
	function ccsswp_register_style() {
		$url = site_url();
		
		wp_register_style( 'ccss', add_query_arg( array( 'ccss' => 1 ), $url ) );
		wp_enqueue_style( 'ccss' );
	}
	add_action( 'wp_enqueue_scripts', 'ccsswp_register_style', 99 );

	// Use custom css when query used.
	function ccss_css() {

		// Only print CSS if this is a stylesheet request
		if( ! isset( $_GET['ccss'] ) || intval( $_GET['ccss'] ) !== 1 ) {
			return;
		}

		ob_start();
		header( 'Content-type: text/css' );
		$options     = get_option( 'ccsswp_settings' );
		$raw_content = isset( $options['ccsswp-content'] ) ? $options['ccsswp-content'] : '';
		$content     = wp_kses( $raw_content, array( '\'', '\"' ) );
		$content     = str_replace( '&gt;', '>', $content );
		echo $content; //xss okay
		die();
	}

	add_action( 'plugins_loaded', 'ccss_css' );

} elseif( ! defined( 'DOING_AJAX' ) ) {
	
	//Setting Link in Plugins Page
	function ccsswp_settings_link( $links ) {
		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'themes.php?page=custom-css-wp.php' ) . '">' . __( 'Add Custom CSS', 'ccsswp' ) . '</a>'
			),
			$links
		);
	}
	add_filter( 'plugin_action_links_' . plugin_basename( CCSSWP_PATH ), 'ccsswp_settings_link' );

	// Delete Option 
	function ccsswp_uninstall() {
		delete_option( 'ccsswp_settings' );
	}
	register_uninstall_hook( CCSSWP_PATH, 'ccsswp_uninstall' );

	// CodeFlask Register CSS/JSS
	function ccsswp_register_codemirror( $hook ) {
		if ( 'appearance_page_custom-css-wp' === $hook ) {
			wp_enqueue_script( 'ace', plugins_url( 'custom-css-wp/assets/ace/ace.js' ) );
			wp_enqueue_script( 'ace_jquery', plugins_url( 'custom-css-wp/assets/jquery-ace.min.js' ) );
			//wp_enqueue_script( 'twilight', plugins_url( 'custom-css-wordpress/assets/ace/theme-xcode.js' ) );
			wp_enqueue_script( 'ruby', plugins_url( 'custom-css-wp/assets/ace/mode-css.js' ) );
		}
	}
	add_action( 'admin_enqueue_scripts', 'ccsswp_register_codemirror' );

	//Add Menu in Admin
	function ccsswp_register_submenu_page() {
		add_theme_page( __( 'Custom CSS for Wordpress', 'ccsswp' ), __( 'Custom CSS', 'ccsswp' ), 'edit_theme_options', basename( CCSSWP_PATH ), 'ccsswp_render_submenu_page' );
	}
	add_action( 'admin_menu', 'ccsswp_register_submenu_page' );


	// Register Setting
	function ccsswp_register_settings() {
		register_setting( 'ccsswp_settings_group', 'ccsswp_settings' );
	}
	add_action( 'admin_init', 'ccsswp_register_settings' );


	// Menu Page
	function ccsswp_render_submenu_page() {

		$options = get_option( 'ccsswp_settings' );
		$content = isset( $options['ccsswp-content'] ) && ! empty( $options['ccsswp-content'] ) ? $options['ccsswp-content'] : __( '/* Enter Your Custom CSS Here */', 'ccsswp' );

		if ( isset( $_GET['settings-updated'] ) ) : ?>
			<div id="message" class="updated"><p><?php _e( 'Your Custom CSS updated successfully.', 'ccsswp' ); ?></p></div>
		<?php endif; ?>
		<div class="wrap">
			<h2 style="margin-bottom: 30px;"><?php _e( 'Custom CSS for Wordpress', 'ccsswp' ); ?></h2>
			<form name="ccsswp_form" action="options.php" method="post" enctype="multipart/form-data">
				<?php settings_fields( 'ccsswp_settings_group' ); ?>
				<div id="ccsswp_wrap">
					<?php do_action( 'ccsswp-form-top' ); ?>
					<div>
						<textarea rows="20" name="ccsswp_settings[ccsswp-content]" id="ccsswp_content" ><?php echo esc_html( $content ); ?></textarea>
					</div>
					<?php do_action( 'ccsswp-textarea-bottom' ); ?>
						<?php submit_button( __( 'Save Custom CSS', 'ccsswp' ), 'primary', 'submit', true ); ?>
					<?php do_action( 'ccsswp-form-bottom' ); ?>
				</div>
			</form>
			<script language="javascript">
				jQuery(document).ready(function(){
					jQuery('#ccsswp_content').ace({ printmargin: false, theme: 'dreamweaver', lang: 'css' })
				});
			</script>
			<style type="text/css">
				#ccsswp_wrap .ace_print-margin-layer{display: none !important;}
				#ccsswp_wrap textarea{padding: 15px; box-sizing: border-box; width: 100%; }
			</style>
		</div>
		<div class="clear"></div>
	<?php
	}
}