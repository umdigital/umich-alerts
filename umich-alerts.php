<?php
/**
 * Plugin Name: University of Michigan: Alerts
 * Plugin URI: https://github.com/umdigital/umich-alerts/
 * Description: Display Univeristy Alert banners
 * Version: 1.0
 * Author: U-M: Digital
 * Author URI: http://vpcomm.umich.edu
 */

define( 'UMALERTS_PATH', dirname( __FILE__ ) . DIRECTORY_SEPARATOR );

class UMichAlerts
{
    static private $_cacheTimeout = 60 * 999;
    static private $_alertsFeeds = array(
        'test' => array(
            'ealert' => 'https://dpss.umich.edu/rss/alert/test/'
        ),
        'prod' => array(
            'ealert' => 'https://dpss.umich.edu/rss/alert/'
        )
    );
    static private $_defaultOptions = array(
        'mode' => 'test'
    );
    static private $_alerts = array();

    static public function init()
    {
        // UPDATER SETUP
        if( !class_exists( 'WP_GitHub_Updater' ) ) {
            include_once UMALERTS_PATH .'vendor'. DIRECTORY_SEPARATOR .'updater.php';
        }
        if( isset( $_GET['force-check'] ) && $_GET['force-check'] && !defined( 'WP_GITHUB_FORCE_UPDATE' ) ) {
            define( 'WP_GITHUB_FORCE_UPDATE', true );
        }
        if( is_admin() ) {
            new WP_GitHub_Updater(array(
                // this is the slug of your plugin
                'slug' => plugin_basename(__FILE__),
                // this is the name of the folder your plugin lives in
                'proper_folder_name' => dirname( plugin_basename( __FILE__ ) ),
                // the github API url of your github repo
                'api_url' => 'https://api.github.com/repos/umdigital/umich-alerts',
                // the github raw url of your github repo
                'raw_url' => 'https://raw.githubusercontent.com/umdigital/umich-alerts/master',
                // the github url of your github repo
                'github_url' => 'https://github.com/umdigital/umich-alerts',
                 // the zip url of the github repo
                'zip_url' => 'https://github.com/umdigital/umich-alerts/zipball/master',
                // wether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
                'sslverify' => true,
                // which version of WordPress does your plugin require?
                'requires' => '4.9',
                // which version of WordPress is your plugin tested up to?
                'tested' => '4.9.1',
                // which file to use as the readme for the version number
                'readme' => 'README.md',
                // Access private repositories by authorizing under Appearance > Github Updates when this example plugin is installed
                'access_token' => '',
            ));
        }

        add_action( 'init', function(){
            $umAlertsOptions = array_replace_recursive(
                self::$_defaultOptions,
                get_option( 'umich_alerts_options' ) ?: array()
            );

            if( $umAlertsOptions != get_option( 'umich_alerts_options' ) ) {
                update_option( 'umich_alerts_options', $umAlertsOptions );
            }


            if( UMichAlerts::getAlert( 'ealert' ) && ($umAlertsOptions['mode'] == 'prod' || current_user_can( 'administrator' )) ) {
                add_action( 'wp_enqueue_scripts', function(){
                    wp_enqueue_script( 'umich-alerts', plugins_url( 'assets/umich-alerts.js', __FILE__ ), array(), '1.0', true );
                    wp_enqueue_style( 'umich-alerts', plugins_url( 'assets/umich-alerts.css', __FILE__ ) );
                });

                add_action( 'wp_head', function(){
                    if( $alert = UMichAlerts::getAlert( 'ealert' ) ) {
                        ob_start();
                        include UMALERTS_PATH .'templates'. DIRECTORY_SEPARATOR .'ealert.tpl';
                        $html = ob_get_clean();

                        echo "<script>\n";
                        echo "var umEAlertHtml = ". json_encode( $html ) .";";
                        echo "</script>\n";
                    }
                });
            }
        });

        
        /** ADMIN **/
        add_filter( 'plugin_action_links_'. plugin_basename(__FILE__), function( $links ){
            return array_merge(
                $links,
                array(
                    '<a href="'. admin_url( 'options-general.php?page=umich-alerts' ) .'">Settings</a>'
                )
            );
        });

        add_action( 'admin_init', function(){
            register_setting(
                'umich-alerts',
                'umich_alerts_options'
            );
        });

        add_action( 'admin_menu', function(){
            add_options_page(
                'U-M: Alerts',
                'U-M: Alerts',
                'administrator',
                'umich-alerts',
                function(){
                    include UMALERTS_PATH .'templates'. DIRECTORY_SEPARATOR .'admin.tpl';
                }
            );
        });
    }

    static public function getAlert( $type )
    {
        if( isset( self::$_alerts[ $type ] ) ) {
            return self::$_alerts[ $type ];
        }

        $umAlertsOptions = get_option( 'umich_alerts_options' ) ?: array();

        if( ($umAlertsOptions['mode'] == 'test') && !current_user_can( 'administrator' ) ) {
            return false;
        }

        $wpUpload  = wp_upload_dir();
        $cachePath = implode( DIRECTORY_SEPARATOR, array(
            $wpUpload['basedir'],
            'umich-alerts-cache',
            $type .'--'. $umAlertsOptions['mode'] .'.cache'
        ));

        // PULL FROM SOURCE
        if( !file_exists( $cachePath ) || ((@filemtime( $cachePath ) + self::$_cacheTimeout) < time()) ) {
            // update timestamp so other request don't make a pull request at the same time
            @touch( $cachePath );

            // get live results
            $stream = stream_context_create(array(
                'http' => array(
                    'timeout' => 1
                )
            ));
            if( $xml = @file_get_contents( self::$_alertsFeeds[ $umAlertsOptions['mode'] ][ $type ], false, $stream ) ) {
                if( $xml = @simplexml_load_string( $xml ) ) {
                    // CACHE RESULTS
                    wp_mkdir_p( dirname( $cachePath ) );

                    @file_put_contents( $cachePath, $xml->asXML() );
                }
            }
        }

        if( $alerts = @simplexml_load_string( file_get_contents( $cachePath ) ) ) {
            foreach( $alerts->channel->item as $item ) {
                if( $item->alertlevel == 1 ) {
                    $alert = array(
                        'title' => (string) $item->title,
                        'link'  => str_replace( 'www.', '', (string) $item->link ),
                        'desc'  => (string) $item->description,
                        'guid'  => (string) $item->guid
                    );

                    self::$_alerts[ $type ] = $alert;
                    return $alert;
                }
            }
        }

        return false;
    }
}
UMichAlerts::init();
