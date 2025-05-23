<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once __DIR__.'/base.addon.php';

if( !class_exists( 'CPAPPB_Skrill' ) )
{
    class CPAPPB_Skrill extends CPAPPB_BaseAddon
    {

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-Skrill-20170903";
		protected $name = "Skrill Payments Integration";
		protected $description;
        public $category = 'Payment Gateways Integration';
        public $help = 'https://apphourbooking.dwbooster.com/documentation#skrill-addon';

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = $this->tr_apply("The add-on adds support for Skrill payments", 'appointment-hour-booking' );

        } // End __construct


    } // End Class

    // Main add-on code
    $cpappb_Skrill_obj = new CPAPPB_Skrill();

	// Add addon object to the objects list
	global $cpappb_addons_objs_list;
	$cpappb_addons_objs_list[ $cpappb_Skrill_obj->get_addon_id() ] = $cpappb_Skrill_obj;
}

