<?php
/*
Plugin Name: OpenFuego for WordPress
Plugin URI: http://github.com/AramZS/openfuego
Description: A WordPress adaptation of the OpenFuego system from Nieman Journalism Lab (as created by Andrew Phelps).
Version: 0.0.1
Author: Aram Zucker-Scharff, Andrew Phelps
Author URI: http://github.com/AramZS
License: GPL2
*/

/*  Developed for the CFO Publishing

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
    
    
    
    I should also note that I borrowed heavily from coding patterns in the PressForward 
    project that were created by Boone Gorges.
    
*/

//Set up some constants
define( 'OF_SLUG', 'of' );
define( 'OF_TITLE', 'OpenFuego' );
define( 'OF_MENU_SLUG', PF_SLUG . '-menu' );
define( 'OF_ROOT', dirname(__FILE__) );
define( 'OF_FILE_PATH', PF_ROOT . '/' . basename(__FILE__) );
define( 'OF_URL', plugins_url('/', __FILE__) );
define( 'OF_VERSION', '0.0.1' );

class OpenFuegoWP {
    
    var $openfuego_installer;
    var $openfuego_wp_adminspace;
    var $openfuego_bootstrapper;
    var $openfuego_runtime;
    var $openfuego_pressedforward;
    var $openfuego_frontend;
    
    public static function init(){
        static $instance;
        
        if (! is_a($instance, 'OpenFuegoWP' )){
            $instance = new self();
        }
        
        return $instance;
    }
    
    private function __construct(){
        
        $this->includes();
        $this->openfuego_install();
        $this->openfuego_wp_admin();
        $this->openfuego_bootstrap();
        $this->openfuego_run();
        $this->openfuego_unto_pf();
        $this->openfuego_display();
        
		add_action( 'wp', array( $this, 'check_installed' ), 0 );

		load_plugin_textdomain( 'of', false, OF_ROOT . '/languages' );
    }
    
    
   function includes() {
        // Pull and parse Open Graph data from a page.
		require_once( OF_ROOT . "/includes/openfuego_install.php" );   
   }

    function openfuego_install(){
        if ( empty( $this->openfuego_installer ) ) {
			$this->openfuego_installer = new OpenFuego_Installer;
		}
    }
    
    function openfuego_wp_admin(){
         if ( empty( $this->openfuego_wp_adminspace ) ) {
			$this->openfuego_wp_adminspace = new OpenFuegoWPAdmin;
		}
    }
    
    function openfuego_bootstrap(){
         if ( empty( $this->openfuego_bootstrapper ) ) {
			$this->openfuego_bootstrapper = new OpenFuegoBootstrap;
		}
    }
    
    function openfuego_run(){
         if ( empty( $this->openfuego_runtime ) ) {
			$this->openfuego_runtime = new OpenFuegoRunner;
		}
    }    
    
    function openfuego_unto_pf(){
        if ( empty( $this->openfuego_pressedforward ) ) {
			$this->openfuego_pressedforward = new OpenFuegoPF;
		}
    }
    
    function openfuego_display(){
        if ( empty( $this->openfuego_frontend ) ) {
			$this->openfuego_frontend = new OpenFuegoDisplay;
		}
    }
    
	/**
	 * Set up first feed and other install/upgrade tasks
	 * Code via Boone
	 *
	 * @since 0.0.1
	 *
	 *
	 */
	public function check_installed() {
		$current_version = OF_VERSION; // define this constant in the loader file
		$saved_version = get_option( 'of_version' );

		// This is a new installation
		if ( ! $saved_version ) {
			// Do whatever you need to do during first installation


		// This is an upgrade
		} else if ( version_compare( $saved_version, $current_version, '<' ) ) {
			// Do whatever you need to do on an upgrade

		// Version is up to date - do nothing
		} else {
			return;
		}

		// Update the version number stored in the db (so this does not run again)
		update_option( 'of_version', OF_VERSION );
	}    
    

       
}

/**
 * Bootstrap
 *
 * You can also use this to get a value out of the global, eg
 *
 *    $foo = openfuego_for_wp()->bar;
 *
 * @since 0.0.1
 */
function openfuego_for_wp() {
	return OpenFuegoWP::init();
}

// Start me up!
openfuego_for_wp();
