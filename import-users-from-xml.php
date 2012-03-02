<?php
/**
 * @package iview-xml-import
 */
/*
Plugin Name: Import Users from XML files
Plugin URI: https://github.com/digitales/Import-Users-from-XML
Description: Import Users data and metadata from an XML file.
Version: 0.1
Author: Ross Tweedie
Author URI: http://dachisgroup.com
License: GPL2
Text Domain: import-users-from-xml
*/
/*  Copyright 2012  Ross Tweedie (https://github.com/digitales)

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
*/

load_plugin_textdomain( 'import-users-from-xml', false, basename( dirname( __FILE__ ) ) . '/languages' );

/**
 * Main plugin class
 *
 * @since 0.1
 **/
class Import_Users_From_Xml {
	private static $log_dir_path = '';
	private static $log_dir_url  = '';

    var $depth = array();
    
    /**
     * Field name mapping
     *
     * @param array
     */
    var $mapping;
	
    /**
	 * Initialization
	 *
	 * @since 0.1
	 **/
	public function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_pages' ) );
		add_action( 'init', array( __CLASS__, 'process_xml' ) );

		$upload_dir = wp_upload_dir();
		self::$log_dir_path = trailingslashit( $upload_dir['basedir'] );
		self::$log_dir_url  = trailingslashit( $upload_dir['baseurl'] );
	}

    
	/**
	 * Add the administration menu to the user menu
	 *
	 * @param void
	 * @return void
	 * 
	 * @since 0.1
	 **/
	public function add_admin_pages() {
		add_users_page( __( 'Import From XML' , 'import-users-from-xml' ), __( 'Import From XML' , 'import-users-from-xml' ), 'create_users', 'import-users-from-xml', array( __CLASS__, 'users_page' ) );
	}

	/**
	 * Process content of the XML file
	 *
	 * @since 0.1
	 **/
	public function process_xml() {
		if ( isset( $_POST['_wpnonce-iu-fx-xml_import'] ) ) {
			check_admin_referer( 'iu-fx-xml_import', '_wpnonce-iu-fx-xml_import' );
        
			if ( isset( $_FILES['xml_file']['tmp_name'] ) ) {
				// Setup settings variables
				$filename              = $_FILES['xml_file']['tmp_name'];
				$password_nag          = isset( $_POST['password_nag'] ) ? $_POST['password_nag'] : false;
				$new_user_notification = isset( $_POST['new_user_notification'] ) ? $_POST['new_user_notification'] : false;

				$results = self::import_xml( $filename, $password_nag, $new_user_notification );
                

				// No users imported?
				if ( ! $results['user_ids'] )
					wp_redirect( add_query_arg( 'import', 'fail', wp_get_referer() ) );

				// Some users imported?
				elseif ( $results['errors'] )
					wp_redirect( add_query_arg( 'import', 'errors', wp_get_referer() ) );

				// All users imported? :D
				else
					wp_redirect( add_query_arg( 'import', 'success', wp_get_referer() ) );

				exit;
			}

			wp_redirect( add_query_arg( 'import', 'file', wp_get_referer() ) );
			exit;
		}
	}

	/**
	 * Content of the settings page
	 *
	 * @since 0.1
	 **/
	public function users_page() {
		if ( ! current_user_can( 'create_users' ) ){
			wp_die( __( 'You do not have sufficient permissions to access this page.' , 'import-users-from-xml' ) );
		}
        
        $show_form      = true;
        $message        = '';
        
        $error_log_file = self::$log_dir_path . 'iu_fx_errors.log';
        $error_log_url  = self::$log_dir_url . 'iu_fx_errors.log';

        if ( ! file_exists( $error_log_file ) ) {
            if ( ! @fopen( $error_log_file, 'x' ) )
                $message = '<div class="updated"><p><strong>' . sprintf( __( 'Notice: please make the directory %s writable so that you can see the error log.' , 'import-users-from-xml' ), self::$log_dir_path ) . '</strong></p></div>';
        }
        
        if ( isset( $_GET['import'] ) ) {
            $error_log_msg = '';
            if ( file_exists( $error_log_file ) )
                $error_log_msg = sprintf( __( ', please <a href="%s">check the error log</a>' , 'import-users-from-xml' ), $error_log_url );
    
            switch ( $_GET['import'] ) {
                case 'file':
                    $message = '<div class="error"><p><strong>' . __( 'Error during file upload.' , 'import-users-from-xml' ) . '</strong></p></div>';
                    break;
                case 'data':
                    $message = '<div class="error"><p><strong>' . __( 'Cannot extract data from uploaded file or no file was uploaded.' , 'import-users-from-xml' ) . '</strong></p></div>';
                    break;
                case 'fail':
                    $message = '<div class="error"><p><strong>' . sprintf( __( 'No user was successfully imported%s.' , 'import-users-from-xml' ), $error_log_msg ) . '</strong></p></div>';
                    $show_form = false;
                    break;
                case 'errors':
                    $message = '<div class="error"><p><strong>' . sprintf( __( 'Some users were successfully imported but some were not%s.' , 'import-users-from-xml' ), $error_log_msg ) . '</strong></p></div>';
                    $show_form = false;
                    break;
                case 'success':
                    $message = '<div class="updated"><p><strong>' . __( 'Users import was successful.' , 'import-users-from-xml' ) . '</strong></p></div>';
                    $show_form = false;
                    break;
                default:
                    break;
            }
        }
        
        include( 'views/settings-page.php' );
	
	}
    
    /**
     * Get the field mapping config between the XML and the WordPress database
     *
     * @param void
     * @return array
     */
    private function get_field_mapping( )
    {
        global $mapping;
        if ( !empty(self::$mapping) ){
            return self::$mapping;
        }
        include( 'fieldname-mapping.php');
        $mapping = $fieldname_mapping;
        return $mapping;
    }

	/**
	 * Import a XML  file
	 *
	 * @since 0.1
	 */
	public static function import_xml( $filename, $password_nag = false, $new_user_notification = false ) {
		$userdata = $usermeta = $errors = $user_ids = array();
        $xml_file = simplexml_load_file( $filename );
        
        if ( empty($xml_file) ) {
            return false;
        }
        
        $count = 0;
        foreach( $xml_file AS $row ) :
            $data = self::process_userdata( $row );
            $userdata = $data->userdata;
            $usermeta = $data->usermeta;
            
            // A plugin may need to filter the data and meta
            $userdata = apply_filters( 'iu_fx_import_userdata', $userdata, $usermeta );
            $usermeta = apply_filters( 'iu_fx_import_usermeta', $usermeta, $userdata );

            // If no user data :(
            if ( empty( $userdata ) ) {
                continue;
            }

        	// Something to be done before importing one user?
            do_action( 'iu_fx_pre_user_import', $userdata, $usermeta );
            
            $result = self::save_user( $userdata, $usermeta, array( 'password_nag' => $password_nag, 'new_user_notification' => $new_user_notification ) );
            
            if ( (int) $result > 0 ) {
                $user_ids[] = $result->user_id;
            } else {
                $errors[ $count ] = $result->error;
            }
            
            $count++;
        endforeach;
        
		// One more thing to do after all imports?
		do_action( 'iu_fx_post_users_import', $user_ids, $errors );

		// Let's log the errors
		self::log_errors( $errors );

		return array(
			'user_ids' => $user_ids,
			'errors'   => $errors,
		);
	}
    
    /**
     * Add or update the WordPress user record
     *
     * @param array $userdata
     * @param array $usermeata
     * @param array $args e.g. array( 'password_nag' => true, 'new_user_notification' => true )
     * 
     * @return object
     */
    private function save_user( $userdata, $usermeta, $args = array() ){
        global $wpdb;
        
        $return = new stdClass();
		$update = false; // Are we updating an old user or creating a new one?
		$user_id = 0;
		if ( ! empty( $userdata['ID'] ) ) {
			$update = true;
			$user_id = $userdata['ID'];
		}
        
        $hash = ( isset( $userdata['user_hash'] ) and !empty( $userdata['user_hash'] ) )? $userdata['user_hash'] : null ;
        unset( $userdata['user_hash']);
        
        // Insert or update... at last! If only user ID was provided, we don't need to do anything at all. :)
		if ( array( 'ID' => $user_id ) == $userdata ) {
			$user_id = get_userdata( $user_id )->ID; // To check if the user id exists
		} elseif ( $update ){
			$user_id = wp_update_user( $userdata );
        } else {
            if ( empty( $userdata['user_pass'] ) ){
                $userdata['user_pass'] = wp_generate_password( 12, false );
            }
            
            $user_id = wp_insert_user( $userdata );
		}
        
        /**
         * If there was a hash provided, we should store it agains the user account.
         * This will be changed to the WordPress encrypted password next time the user logs in.
         */
        if ( $hash ){
            $wpdb->update( $wpdb->users, array( 'user_pass' => $hash, 'user_activation_key' => '' ), array( 'ID' => $user_id ) );
        }
        
        
        // Is there an error ?
		if ( is_wp_error( $user_id ) ) {
            $return->error = $user_id;
            return $return;
        }
        
		// If no error, let's update the user meta too!
		if ( $usermeta ) {
			foreach ( $usermeta as $metakey => $metavalue ) {
				$metavalue = maybe_unserialize( $metavalue );
				update_user_meta( $user_id, $metakey, $metavalue );
			}
		}

		// If we created a new user, maybe set password nag and send new user notification?
		if ( ! $update ) {
            if ( isset( $args['password_nag'] ) and $args['password_nag'] == true ) {
                update_user_option( $user_id, 'default_password_nag', true, true );
			}

            if ( isset( $args['new_user_notification'] ) and $args['new_user_notification'] == tue ){
                wp_new_user_notification( $user_id, $userdata['user_pass'] );
            }
        }

		// Some plugins may need to do things after one user has been imported. Who know?
		do_action( 'iu_fx_post_user_import', $user_id );
        
        $return->user_id = $user_id;

        return $return;
    }
    
    
    /**
     * Process user data and return the userdata and user metadata
     *
     * @param object $data
     * @return object
     */
    private function process_userdata( $data ) {
        $userdata_fields = $usermeta = array();
    
        $userdata_fields = self::get_userdata_fields();
        $mapping = self::get_field_mapping();
        
        // Normalise the data.
        foreach ( $data AS $column_name => $value ) {
            $value = (string) trim( $value );
            $key = ( isset( $mapping[ $column_name ] ) )? $mapping[ $column_name ] : $column_name;

            // Split the data between user data and metadata.
            if ( isset( $userdata_fields[ $key ] ) and !empty( $userdata_fields[ $key ] ) ){
                $userdata[ $key ] = $value;
            } else {
                $usermeta[ $key ] = $value;
            }
        }
        
        $returndata->userdata = $userdata;
        $returndata->usermeta = $usermeta;
        
        return $returndata;        
    }
    
    /**
     * Get the list of default WordPress userdata fields
     *
     * @param void
     * @return array
     */
     private function get_userdata_fields() {
        // User data fields list used to differentiate with user meta
		return array(
			'id' => true, 'user_login' => true, 'user_pass'  => true,
			'user_email' => true, 'user_url'  => true, 'user_nicename' => true,
			'display_name' => true, 'user_registered' => true, 'first_name' => true,
			'last_name' => true, 'nickname' => true, 'description' => true,
			'rich_editing' => true, 'comment_shortcuts' => true, 'admin_color' => true,
			'use_ssl' => true, 'show_admin_bar_front' => true, 'show_admin_bar_admin' => true,
			'role' => true,
		);
    }
    

	/**
	 * Log errors to a file
	 *
	 * @since 0.2
	 **/
	private static function log_errors( $errors ) {
		if ( empty( $errors ) )
			return;

		$log = @fopen( self::$log_dir_path . 'iu_fx_import_errors.log', 'a' );
		@fwrite( $log, sprintf( __( 'BEGIN %s' , 'import-users-from-xml' ), date( 'Y-m-d H:i:s', time() ) ) . "\n" );

		foreach ( $errors as $key => $error ) {
			$line = $key + 1;
			$message = $error->get_error_message();
			@fwrite( $log, sprintf( __( '[Element %1$s] %2$s', 'import-users-from-xml' ), $line, $message ) . "\n" );
		}

		@fclose( $log );
	}
}

Import_Users_From_Xml::init();