<?php

/*

    OpenFuego inital install
    This file will setup the database through WordPress functions for OpenFuego to access.
    
    The installer will build the tables normally built by OpenFuego.

*/

class OpenFuego_Installer {

    public function init() {
		static $instance;

		if ( empty( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}
    
    /**
	 * Constructor
	 */
	private function __construct() {
        // Maybe install custom table for relationships
        add_action( 'admin_init', array( $this, 'maybe_install_openfuego_tables' ) );
    }

	/**
	 * Checks to see whether the relationship table needs to be installed, and installs if so
	 *
	 * A regular activation hook won't work correctly given where how
	 * this file is loaded. Might change this in the future
	 */
	public function maybe_install_openfuego_tables() {
		if ( ! is_super_admin() ) {
			return;
		}

		global $wpdb;
		$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $wpdb->prefix . 'openfuego_citizens' ) );

		if ( ! $table_exists ) {
			self::install_openfuego_tables();
		}
	}
    
    # Thieving this from https://github.com/PressForward/pressforward/blob/master/includes/schema.php#L118 and https://github.com/AramZS/openfuego/blob/master/consume.php#L34
    public static function install_openfuego_tables(){
        global $wpdb;
        
        require_once( ABSPATH.'wp-admin/includes/upgrade.php' );
        
        $sql = array();
        $sql[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}openfuego_citizens (
                      user_id bigint(20) unsigned NOT NULL, PRIMARY KEY,
                      influence tinyint(2) unsigned NOT NULL,
                      
                )";
        
        $sql[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}openfuego_links (
                      `link_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT, PRIMARY KEY,
                      `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
                      `first_seen` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                      `first_tweet` bigint(20) unsigned NOT NULL,
                      `first_user` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
                      `first_user_id` bigint(20) unsigned DEFAULT NULL,
                      `weighted_count` smallint(5) unsigned NOT NULL,
                      `count` smallint(5) unsigned NOT NULL DEFAULT '1',
                      `last_seen` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                      
                      UNIQUE KEY `url` (`url`),
                      KEY first_user (first_user)
                      
                    )";
        $sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}openfuego_short_links` (
                      `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                      `input_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL, PRIMARY KEY
                      `long_url` text COLLATE utf8_unicode_ci NOT NULL,
                      `last_seen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                      UNIQUE KEY `id` (`id`)
                    )";
        
        $sql[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}openfuego_tweets_cache` (
                  `link_id` mediumint(8) unsigned NOT NULL, PRIMARY KEY
                  `id_str` bigint(20) unsigned NOT NULL,
                  `screen_name` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
                  `text` varchar(140) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
                  `profile_image_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
                  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  
                  KEY first_user (first_user)
                )";
        
        $sql[] = "ALTER TABLE `{$wpdb->prefix}openfuego_tweets_cache`
                    ADD CONSTRAINT `FK.{$wpdb->prefix}openfuego_tweets_cache.link_id` FOREIGN KEY (`link_id`) REFERENCES `{$wpdb->prefix}openfuego_links` (`link_id`) ON DELETE CASCADE ON UPDATE CASCADE";
        

            
        # You know what, I'm too lazy to manually remove backticks at the pleasure of 
        # dbDelta. 
        $prepped_sql = array();
        foreach ($sql as $sqlite) {
            $prepped_sql[] = str_replace("`","",$sqlite);    
        }
        
        dbDelta( $prepped_sql );
        
        
    }
    
}