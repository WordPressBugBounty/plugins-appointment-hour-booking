<?php
/*
  
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPAPPB_CapacityOverride' ) )
{
    class CPAPPB_CapacityOverride extends CPAPPB_BaseAddon
    {

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-CapacityOverride-20230817";
		protected $name = "Capacity Override for Services";
		protected $description;
        public $category = 'Improvements';
        public $help = 'https://apphourbooking.dwbooster.com/customdownloads/capacity-override.png';



        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = $this->tr_apply("Override the available capacity of a service for specific dates.", 'appointment-hour-booking' );
            

        } // End __construct



    } // End Class

    // Main add-on code
    $CPAPPB_CapacityOverride_obj = new CPAPPB_CapacityOverride();

	// Add addon object to the objects list
	global $cpappb_addons_objs_list;
	$cpappb_addons_objs_list[ $CPAPPB_CapacityOverride_obj->get_addon_id() ] = $CPAPPB_CapacityOverride_obj;
}

