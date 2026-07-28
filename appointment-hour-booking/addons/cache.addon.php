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
            add_action( 'cpappb_item_deleted', array( &$this, 'item_deleted' ), 10, 1 );
            
            
            $this->update_database();

        } // End __construct


        /************************ PRIVATE METHODS *****************************/

		/**
         * Create the database tables
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
            
		} // end update_database


		/************************ PUBLIC METHODS  *****************************/


		/**
         * cache_clean
         */
        public function	cache_clean( $formid )
		{
            global $wpdb, $cp_appb_plugin;
            
            $wpdb->query( 
                $wpdb->prepare( 
                    "UPDATE {$wpdb->prefix}cpappbk_cache SET cacheddata = '' WHERE formid = %d", 
                    $formid 
                ) 
            ); 
		} // end cache_clean  
        
        

		/**
         * update_status
         */
        public function	update_status($itemnumber, $status = '', $indexonly = '')
		{
            global $wpdb, $cp_appb_plugin;
            
            $myrows = $wpdb->get_results( $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."cpappbk_messages WHERE id=%d", $itemnumber) ); 
            
            if (is_array($myrows) && count($myrows))
                $this->cache_clean( $myrows[0]->formid );
            
		} // end update_status  
        
        
		/**
         * item_deleted
         */
        public function	item_deleted($itemnumber)
		{
            global $wpdb, $cp_appb_plugin;
            
            $myrows = $wpdb->get_results( $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."cpappbk_messages WHERE id=%d", $itemnumber) ); 
            
            if (is_array($myrows) && count($myrows))
                $this->cache_clean( $myrows[0]->formid );
            
		} // end item_deleted  
        
        
		/**
         * new_submission
         */
        public function	new_submission($params, $indexonly = -1 )
		{
            global $wpdb, $cp_appb_plugin;
            
            if (isset($params[ 'formid' ]))
                $this->cache_clean( intval( $params[ 'formid' ] ) );
            
		} // end new_submission          
        
        
		/**
         * cache_check
         */
        public function	cache_check( $formid, $dataquery )
		{
            global $wpdb, $cp_appb_plugin;
            
            $is_cached = $wpdb->get_results( 
                $wpdb->prepare( 
                    "SELECT * FROM {$wpdb->prefix}cpappbk_cache WHERE formid = %d AND cachedquery = %s AND cacheddata <> ''", 
                    $formid, 
                    md5( $dataquery ) 
                ) 
            );
            if (count($is_cached))
            {
                echo $is_cached[0]->cacheddata; // phpcs:ignore WordPress.Security.EscapeOutput
                exit;
            }
		} // end cache_check  
        
        
		/**
         * cache_store
         */
        public function	cache_store( $formid, $dataquery, $dataoutput )
		{
            global $wpdb, $cp_appb_plugin;
            
            $queryhash = md5($dataquery);
            $is_cached = $wpdb->get_results( 
                $wpdb->prepare( 
                    "SELECT * FROM {$wpdb->prefix}cpappbk_cache WHERE formid = %d AND cachedquery = %s", 
                    $formid, 
                    $queryhash 
                ) 
            ); 
            if (!count($is_cached))
                $wpdb->insert($wpdb->prefix."cpappbk_cache", array('formid' => intval($formid), 'cachedquery' => ($queryhash) ) ); 
            $wpdb->update($wpdb->prefix."cpappbk_cache", array('cacheddata' => ($dataoutput) ), array('formid' => intval($formid), 'cachedquery' => ($queryhash) ) );
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

