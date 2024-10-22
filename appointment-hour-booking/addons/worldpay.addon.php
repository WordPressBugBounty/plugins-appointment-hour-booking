<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once __DIR__.'/base.addon.php';

if( !class_exists( 'CPAPPB_WorldPay' ) )
{
    class CPAPPB_WorldPay extends CPAPPB_BaseAddon
    {

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-WorldPay-20201216";
		protected $name = "WorldPay Payment Gateway";
		protected $description;
        public $category = 'Payment Gateways Integration';
        public $help = 'https://apphourbooking.dwbooster.com/contact-us';

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = __("The add-on adds support for WorldPay payments", 'appointment-hour-booking' );

        } // End __construct





    } // End Class

    // Main add-on code
    $CPAPPB_WorldPay_obj = new CPAPPB_WorldPay();

	// Add addon object to the objects list
	global $cpappb_addons_objs_list;
	$cpappb_addons_objs_list[ $CPAPPB_WorldPay_obj->get_addon_id() ] = $CPAPPB_WorldPay_obj;
}


