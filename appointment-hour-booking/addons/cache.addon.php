<?php
/*
    Shared Availability Addon
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPAPPB_Cache' ) )
{
    class CPAPPB_Cache extends CPAPPB_BaseAddon
    {

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-Cache-20260309";
		protected $name = "Cache Booking Availability";
		protected $description;
        public $category = ' Add-ons included in this plugin version';
        public $help = 'https://apphourbooking.dwbooster.com/contact-us';


		/************************ ADDON CODE *****************************/

        /************************ ATTRIBUTES *****************************/

        private $form_table = 'cpappbk_cache';
        private $_inserted = false;

        /************************ CONSTRUCT *****************************/

        function __construct()
        {            
			$this->description = $this->tr_apply("The add-on keeps a cache of the available times for faster booking form loading.", 'appointment-hour-booking' );
            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;      
                      
            add_action( 'cpappb_cache_check', array( &$this, 'cache_check'), 99, 2 ); // $formid, $dataquery
            
            add_action( 'cpappb_cache_store', array( &$this, 'cache_store'), 99, 3 ); // $formid, $dataquery, $dataoutput
            
            add_action( 'cpappb_cache_clean', array( &$this, 'cache_clean'), 99, 1 ); // $formid
                        
            add_action( 'cpappb_update_status', array( &$this, 'update_status' ), 10, 3 );
            add_action( 'cpappb_process_data', array( &$this, 'new_submission' ), 10, 2 );
            add_action( 'cpappb_process_data_second_round', array( &$this, 'new_submission' ), 10, 2 );
            add_action( 'cpappb_item_deleted', array( &$this, 'item_deleted' ), 10, 1 );
            add_action( 'cpappb_edited_booking', array( &$this, 'item_deleted' ), 10, 1 );
            add_action( 'cpappb_item_all_deleted', array( &$this, 'cache_clean_all' ), 10, 0 );
            add_action( 'cpappb_item_formbookings_deleted', array( &$this, 'cache_clean' ), 10, 1 );
            
            $this->update_database();

        } // End __construct


        /************************ PRIVATE METHODS *****************************/

		/**
         * Create the database tables and purge any legacy uncontextualized cache entries
         */
        protected function update_database()
		{
			global $wpdb;

			$charset_collate = $wpdb->get_charset_collate();
            $table_name      = $wpdb->prefix . esc_sql( $this->form_table );

            // Note: dbDelta does not need "IF NOT EXISTS"
            $sql = "CREATE TABLE {$table_name} (
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    formid INT NOT NULL,
                    cachedquery mediumtext,
                    cacheddata mediumtext,
                    UNIQUE KEY id (id)
                ) $charset_collate;";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta( $sql );

            // One-time purge of legacy cache entries so no previously cached admin payload remains
            if ( get_option( 'cpappb_cache_v2_purged', '0' ) !== '1' ) {
                $wpdb->query( "UPDATE {$wpdb->prefix}cpappbk_cache SET cacheddata = ''" );
                update_option( 'cpappb_cache_v2_purged', '1' );
            }
            
		} // end update_database

        /**
         * Never cache or serve cache for multiview ('mv') or admin requests
         */
        private function is_privileged_or_mv_request()
        {
            if ( isset( $_REQUEST['cp_app_action'] ) && $_REQUEST['cp_app_action'] === 'mv' ) {
                return true;
            }
            return false;
        }

        /**
         * Build a context-aware hash for public slot queries
         */
        private function build_query_hash( $formid, $dataquery )
        {
            $action = isset( $_REQUEST['cp_app_action'] ) ? sanitize_key( wp_unslash( $_REQUEST['cp_app_action'] ) ) : '';
            $field  = isset( $_REQUEST['formfield'] ) ? sanitize_key( wp_unslash( $_REQUEST['formfield'] ) ) : '';

            return md5( 'v2|public|' . intval( $formid ) . '|' . $action . '|' . $field . '|' . $dataquery );
        }


		/************************ PUBLIC METHODS  *****************************/


		/**
         * cache_clean
         */
        public function cache_clean( $formid = 0 )
		{
            global $wpdb;
            $formid = intval( $formid );
            if ( $formid > 0 ) {
                $wpdb->query( 
                    $wpdb->prepare( 
                        "UPDATE {$wpdb->prefix}cpappbk_cache SET cacheddata = '' WHERE formid = %d OR formid = 0", 
                        $formid 
                    ) 
                );
            } else {
                $wpdb->query( "UPDATE {$wpdb->prefix}cpappbk_cache SET cacheddata = ''" );
            }
		} // end cache_clean  
        
        public function cache_clean_all()
        {
            $this->cache_clean( 0 );
        }

		/**
         * update_status
         */
        public function update_status( $itemnumber, $status = '', $indexonly = '' )
		{
            global $wpdb;
            
            $myrows = $wpdb->get_results( $wpdb->prepare( "SELECT formid FROM {$wpdb->prefix}cpappbk_messages WHERE id = %d", intval( $itemnumber ) ) ); 
            
            if ( is_array( $myrows ) && count( $myrows ) ) {
                $this->cache_clean( $myrows[0]->formid );
            } else {
                $this->cache_clean( 0 );
            }
		} // end update_status  
        
        
		/**
         * item_deleted
         */
        public function item_deleted( $itemnumber )
		{
            global $wpdb;
            
            $myrows = $wpdb->get_results( $wpdb->prepare( "SELECT formid FROM {$wpdb->prefix}cpappbk_messages WHERE id = %d", intval( $itemnumber ) ) ); 
            
            if ( is_array( $myrows ) && count( $myrows ) ) {
                $this->cache_clean( $myrows[0]->formid );
            } else {
                $this->cache_clean( 0 );
            }
		} // end item_deleted  
        
        
		/**
         * new_submission
         */
        public function new_submission( $params, $indexonly = -1 )
		{
            if ( isset( $params['formid'] ) ) {
                $this->cache_clean( intval( $params['formid'] ) );
            } else {
                $this->cache_clean( 0 );
            }
		} // end new_submission          
        
        
		/**
         * cache_check
         */
        public function cache_check( $formid, $dataquery )
		{
            global $wpdb;

            if ( $this->is_privileged_or_mv_request() || empty( $dataquery ) ) {
                return;
            }
            
            $queryhash = $this->build_query_hash( $formid, $dataquery );
            $is_cached = $wpdb->get_results( 
                $wpdb->prepare( 
                    "SELECT cacheddata FROM {$wpdb->prefix}cpappbk_cache WHERE formid = %d AND cachedquery = %s AND cacheddata <> ''", 
                    intval( $formid ), 
                    $queryhash 
                ) 
            );
            if ( count( $is_cached ) )
            {
                echo $is_cached[0]->cacheddata; // phpcs:ignore WordPress.Security.EscapeOutput
                exit;
            }
		} // end cache_check  
        
        
		/**
         * cache_store
         */
        public function cache_store( $formid, $dataquery, $dataoutput )
		{
            global $wpdb;

            if ( $this->is_privileged_or_mv_request() || empty( $dataquery ) ) {
                return;
            }
            
            $queryhash = $this->build_query_hash( $formid, $dataquery );
            $is_cached = $wpdb->get_results( 
                $wpdb->prepare( 
                    "SELECT id FROM {$wpdb->prefix}cpappbk_cache WHERE formid = %d AND cachedquery = %s", 
                    intval( $formid ), 
                    $queryhash 
                ) 
            ); 
            if ( ! count( $is_cached ) ) {
                $wpdb->insert(
                    $wpdb->prefix . "cpappbk_cache",
                    array(
                        'formid'      => intval( $formid ),
                        'cachedquery' => $queryhash,
                        'cacheddata'  => $dataoutput,
                    ),
                    array( '%d', '%s', '%s' )
                ); 
            } else {
                $wpdb->update(
                    $wpdb->prefix . "cpappbk_cache",
                    array( 'cacheddata' => $dataoutput ),
                    array( 'formid' => intval( $formid ), 'cachedquery' => $queryhash ),
                    array( '%s' ),
                    array( '%d', '%s' )
                );
            }
		} // end cache_store          
        

		/**
		 * mark the item as paid
		 */
		private function _log($adarray = array())
		{
			// not needed right now
		}

    } // End Class

    // Main add-on code
    $CPAPPB_Cache_obj = new CPAPPB_Cache();

	// Add addon object to the objects list
	global $cpappb_addons_objs_list;
	$cpappb_addons_objs_list[ $CPAPPB_Cache_obj->get_addon_id() ] = $CPAPPB_Cache_obj;
}