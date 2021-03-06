<?php
/**
 * Plugin Name: Mtollwc
 * Plugin URI:  http://github.com/oakwoodgates/mtollwc
 * Description: A radical new plugin for WordPress!
 * Version:     0.0.1
 * Author:      WPGuru4u
 * Author URI:  http://wpguru4u.com
 * Donate link: http://github.com/oakwoodgates/mtollwc
 * License:     GPLv2
 * Text Domain: mtollwc
 * Domain Path: /languages
 *
 * @link http://github.com/oakwoodgates/mtollwc
 *
 * @package Mtollwc
 * @version 0.0.1
 */

/**
 * Copyright (c) 2016 WPGuru4u (email : wpguru4u@gmail.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Built using generator-plugin-wp
 */

require_once dirname(__FILE__) . '/../vendor/cpt-core/CPT_Core.php';
require_once dirname(__FILE__) . '/../vendor/cmb2/init.php';
/**
 * Autoloads files with classes when needed
 *
 * @since  NEXT
 * @param  string $class_name Name of the class being requested.
 * @return void
 */
function mtollwc_autoload_classes( $class_name ) {
	if ( 0 !== strpos( $class_name, 'M_' ) ) {
		return;
	}

	$filename = strtolower( str_replace(
		'_', '-',
		substr( $class_name, strlen( 'M_' ) )
	) );

	Mtollwc::include_file( 'includes/class-' . $filename );
}
spl_autoload_register( 'mtollwc_autoload_classes' );

/**
 * Main initiation class
 *
 * @since  NEXT
 */
final class Mtollwc {

	/**
	 * Current version
	 *
	 * @var  string
	 * @since  NEXT
	 */
	const VERSION = '0.0.1';

	/**
	 * URL of plugin directory
	 *
	 * @var string
	 * @since  NEXT
	 */
	protected $url = '';

	/**
	 * Path of plugin directory
	 *
	 * @var string
	 * @since  NEXT
	 */
	protected $path = '';

	/**
	 * Plugin basename
	 *
	 * @var string
	 * @since  NEXT
	 */
	protected $basename = '';

	/**
	 * Singleton instance of plugin
	 *
	 * @var Mtollwc
	 * @since  NEXT
	 */
	protected static $single_instance = null;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since  NEXT
	 * @return Mtollwc A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin
	 *
	 * @since  NEXT
	 */
	protected function __construct() {
		$this->basename = plugin_basename( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );
	}

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since  NEXT
	 * @return void
	 */
	public function plugin_classes() {
		// Attach other plugin classes to the base plugin class.

	//	$this->maia_admin = new M_Maia_Admin( $this );

		// post types
		$this->lounge = new M_Lounge( $this );
		$this->premium = new M_Premium( $this );

		// widgets
		require( self::dir( 'includes/class-badge143.php' ) );
		require( self::dir( 'includes/class-dynamic-lounge-image.php' ) );
		require( self::dir( 'includes/class-moon-phase.php' ) );
		require( self::dir( 'includes/class-landing-login.php' ) );

		// options panel
		require( self::dir( 'includes/admin.php' ) );

		// some functions for woocommerce subscriptions
		require( self::dir( 'includes/subscription-functions.php' ) );

		// should probably be in the theme
		$this->theme_settings = new M_Theme_Settings( $this );

		// magic
		$this->points = new M_Points( $this );
		M_Create_Relate_Points::get_instance();
	//	$this->luna_woofunnels = new M_Luna_Woofunnels( $this );
	} // END OF PLUGIN CLASSES FUNCTION

	/**
	 * Add hooks and filters
	 *
	 * @since  NEXT
	 * @return void
	 */
	public function hooks() {

		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Activate the plugin
	 *
	 * @since  NEXT
	 * @return void
	 */
	public function _activate() {
		// Make sure any rewrite functionality has been loaded.
		flush_rewrite_rules();
	}

	/**
	 * Deactivate the plugin
	 * Uninstall routines should be in uninstall.php
	 *
	 * @since  NEXT
	 * @return void
	 */
	public function _deactivate() {}

	/**
	 * Init hooks
	 *
	 * @since  NEXT
	 * @return void
	 */
	public function init() {
		if ( $this->check_requirements() ) {
			load_plugin_textdomain( 'mtollwc', false, dirname( $this->basename ) . '/languages/' );
			$this->plugin_classes();
		}
	}

	/**
	 * Check if the plugin meets requirements and
	 * disable it if they are not present.
	 *
	 * @since  NEXT
	 * @return boolean result of meets_requirements
	 */
	public function check_requirements() {
		if ( ! $this->meets_requirements() ) {

			// Add a dashboard notice.
			add_action( 'all_admin_notices', array( $this, 'requirements_not_met_notice' ) );

			// Deactivate our plugin.
			add_action( 'admin_init', array( $this, 'deactivate_me' ) );

			return false;
		}

		return true;
	}

	/**
	 * Deactivates this plugin, hook this function on admin_init.
	 *
	 * @since  NEXT
	 * @return void
	 */
	public function deactivate_me() {
		deactivate_plugins( $this->basename );
	}

	/**
	 * Check that all plugin requirements are met
	 *
	 * @since  NEXT
	 * @return boolean True if requirements are met.
	 */
	public function meets_requirements() {
		// Do checks for required classes / functions
		// function_exists('') & class_exists('').
		// We have met all requirements.
		return true;
	}

	/**
	 * Adds a notice to the dashboard if the plugin requirements are not met
	 *
	 * @since  NEXT
	 * @return void
	 */
	public function requirements_not_met_notice() {
		// Output our error.
		echo '<div id="message" class="error">';
		echo '<p>' . sprintf( __( 'Mtollwc is missing requirements and has been <a href="%s">deactivated</a>. Please make sure all requirements are available.', 'mtollwc' ), admin_url( 'plugins.php' ) ) . '</p>';
		echo '</div>';
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  NEXT
	 * @param string $field Field to get.
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'version':
				return self::VERSION;
			case 'basename':
			case 'url':
			case 'path':
				return $this->$field;
			default:
				throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
		}
	}

	/**
	 * Include a file from the includes directory
	 *
	 * @since  NEXT
	 * @param  string $filename Name of the file to be included.
	 * @return bool   Result of include call.
	 */
	public static function include_file( $filename ) {
		$file = self::dir( $filename .'.php' );
		if ( file_exists( $file ) ) {
			return include_once( $file );
		}
		return false;
	}

	/**
	 * This plugin's directory
	 *
	 * @since  NEXT
	 * @param  string $path (optional) appended path.
	 * @return string       Directory and path
	 */
	public static function dir( $path = '' ) {
		static $dir;
		$dir = $dir ? $dir : trailingslashit( dirname( __FILE__ ) );
		return $dir . $path;
	}

	/**
	 * This plugin's url
	 *
	 * @since  NEXT
	 * @param  string $path (optional) appended path.
	 * @return string       URL and path
	 */
	public static function url( $path = '' ) {
		static $url;
		$url = $url ? $url : trailingslashit( plugin_dir_url( __FILE__ ) );
		return $url . $path;
	}
}

/**
 * Grab the Mtollwc object and return it.
 * Wrapper for Mtollwc::get_instance()
 *
 * @since  NEXT
 * @return Mtollwc  Singleton instance of plugin class.
 */
function mtollwc() {
	return Mtollwc::get_instance();
}

// Kick it off.
add_action( 'plugins_loaded', array( mtollwc(), 'hooks' ) );

register_activation_hook( __FILE__, array( mtollwc(), '_activate' ) );
register_deactivation_hook( __FILE__, array( mtollwc(), '_deactivate' ) );
