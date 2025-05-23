<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once __DIR__.'/base.addon.php';

if( !class_exists( 'CPAPPB_FollowUp' ) )
{
    class CPAPPB_FollowUp extends CPAPPB_BaseAddon
    {

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-FollowUp-20170903";
		protected $name = "FollowUp notifications for bookings";
		protected $description;
        public $category = 'Improvements';
        public $help = 'https://apphourbooking.dwbooster.com/documentation#followup-addon';

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = $this->tr_apply("The add-on adds support for followup notifications", 'appointment-hour-booking' );

        } // End __construct


    } // End Class

    // Main add-on code
    $cpappb_FollowUp_obj = new CPAPPB_FollowUp();

	// Add addon object to the objects list
	global $cpappb_addons_objs_list;
	$cpappb_addons_objs_list[ $cpappb_FollowUp_obj->get_addon_id() ] = $cpappb_FollowUp_obj;
}

