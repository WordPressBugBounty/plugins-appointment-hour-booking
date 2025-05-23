<?php
/*
....
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once __DIR__.'/base.addon.php';

if( !class_exists( 'CPAPPB_SMSBroadcast' ) )
{
    class CPAPPB_SMSBroadcast extends CPAPPB_BaseAddon
    {
        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-SMSBroadcast-20170403";
		protected $name = "SMSBroadcast.com.au";
		protected $description;
        public $category = 'SMS Delivery / Text Messaging';
        public $help = 'https://apphourbooking.dwbooster.com/contact-us';


        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = $this->tr_apply("The add-on allows to send notification messages (SMS) via SMSBroadcast.com.au after submitting the form", 'appointment-hour-booking');

        } // End __construct


    } // End Class

    // Main add-on code
    $apphbSMSBroadcast_obj = new CPAPPB_SMSBroadcast();

    global $cpappb_addons_objs_list;
	$cpappb_addons_objs_list[ $apphbSMSBroadcast_obj->get_addon_id() ] = $apphbSMSBroadcast_obj;
}
