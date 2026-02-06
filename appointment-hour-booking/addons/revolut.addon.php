<?php
/*

*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPAPPB_Revolut' ) )
{
    class CPAPPB_Revolut extends CPAPPB_BaseAddon
    {

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-Revolut-20170903";
		protected $name = "Revolut Payments Integration";
		protected $description;
        public $category = 'Payment Gateways Integration';
        public $help = 'https://apphourbooking.dwbooster.com/customdownloads/revolut-payments.png';
        protected $default_label = 'Pay with Revolut';


        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = $this->tr_apply("The add-on adds support for Revolut payments", 'appointment-hour-booking' );
           

        } // End __construct



    } // End Class

    // Main add-on code
    $cpappb_Revolut_obj = new CPAPPB_Revolut();

	// Add addon object to the objects list
	global $cpappb_addons_objs_list;
	$cpappb_addons_objs_list[ $cpappb_Revolut_obj->get_addon_id() ] = $cpappb_Revolut_obj;
}

