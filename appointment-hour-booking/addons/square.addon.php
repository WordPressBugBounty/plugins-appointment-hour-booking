<?php
/*
Documentation: https://docs.connect.squareup.com/payments/online-payments
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once __DIR__.'/base.addon.php';

if( !class_exists( 'CPAPPB_Square' ) )
{
    class CPAPPB_Square extends CPAPPB_BaseAddon
    {

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-Square-20151212";
		protected $name = "Square";
		protected $description;
        public $category = 'Payment Gateways Integration';
        public $help = 'https://apphourbooking.dwbooster.com/documentation#square-addon';

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = __("The add-on adds support for Square (squareup.com) payments", 'appointment-hour-booking' );

        } // End __construct

    } // End Class

    // Main add-on code
    $CPAPPB_Square_obj = new CPAPPB_Square();

    global $cpappb_addons_objs_list;
	$cpappb_addons_objs_list[ $CPAPPB_Square_obj->get_addon_id() ] = $CPAPPB_Square_obj;
}
