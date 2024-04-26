<?php
/**
 * Plugin Name: University of Michigan: Alerts
 * Plugin URI: https://github.com/umdigital/umich-alerts/
 * Description: Display Univeristy Alert banners
 * Version: 1.1.3
 * Author: U-M: Digital
 * Author URI: http://vpcomm.umich.edu
 * Update URI: https://github.com/umdigital/umich-alerts/
 */

define( 'UMALERTS_PATH', dirname( __FILE__ ) . DIRECTORY_SEPARATOR );

class UMichAlerts
{
    static private $_defaultOptions = array(
        'mode'     => 'prod',
        'location' => 'top'
    );

    static public function getDefaultOptions()
    {
        return self::$_defaultOptions;
    }

    static public function init()
    {
        if( !class_exists( 'UMOneTrust' ) ) {
            include_once UMALERTS_PATH .'includes'. DIRECTORY_SEPARATOR .'umonetrust.php';
        }

        // load updater library
        if( file_exists( UMALERTS_PATH . implode( DIRECTORY_SEPARATOR, [ 'vendor', 'umdigital', 'wordpress-github-updater', 'github-updater.php' ] ) ) ) {
            include UMALERTS_PATH . implode( DIRECTORY_SEPARATOR, [ 'vendor', 'umdigital', 'wordpress-github-updater', 'github-updater.php' ] );
        }
        else if( file_exists( UMALERTS_PATH .'includes'. DIRECTORY_SEPARATOR .'github-updater.php' ) ) {
            include UMALERTS_PATH .'includes'. DIRECTORY_SEPARATOR .'github-updater.php';
        }

        // Initialize Github Updater
        if( class_exists( '\Umich\GithubUpdater\Init' ) ) {
            new \Umich\GithubUpdater\Init([
                'repo' => 'umdigital/umich-alerts',
                'slug' => plugin_basename( __FILE__ ),
            ]);
        }
        // Show error upon failure
        else {
            add_action( 'admin_notices', function(){
                echo '<div class="error notice"><h3>WARNING</h3><p>U-M: Alerts plugin is currently unable to check for updates due to a missing dependency.  Please <a href="https://github.com/umdigital/umich-alerts">reinstall the plugin</a>.</p></div>';
            });
        }

        add_action( 'init', function(){
            $umAlertsOptions = array_replace_recursive(
                UmichAlerts::getDefaultOptions(),
                get_option( 'umich_alerts_options' ) ?: array()
            );

            if( $umAlertsOptions != get_option( 'umich_alerts_options' ) ) {
                update_option( 'umich_alerts_options', $umAlertsOptions );
            }


            if( ($umAlertsOptions['mode'] == 'prod' || current_user_can( 'administrator' )) ) {
                add_action( 'wp_enqueue_scripts', function(){
                    wp_enqueue_script( 'umich-alerts', 'https://umich.edu/apis/umalerts/umalerts.js', array(), '1.0', true );
                });

                add_action( 'wp_head', function(){
                    $umAlertsOptions = get_option( 'umich_alerts_options' ) ?: array();

                    $jsOptions = array();

                    if( ($umAlertsOptions['mode'] != 'prod') && current_user_can( 'administrator' ) ) {
                        $jsOptions['mode'] = $umAlertsOptions['mode'];
                    }

                    if( $umAlertsOptions['location'] != 'top' ) {
                        $jsOptions['location'] = $umAlertsOptions['location'];
                    }

                    if( $jsOptions ) {
                        echo "<script>\n";
                        echo "window.umalerts = ". json_encode( $jsOptions ) ."\n";
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
}
UMichAlerts::init();
