<?php
/*
....
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once __DIR__.'/base.addon.php';

if( !class_exists( 'CPAPPB_WebHook' ) )
{
    class CPAPPB_WebHook extends CPAPPB_BaseAddon
    {
        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-webhook-20150403";
		protected $name = "WebHook";
		protected $description;
        public $category = 'Integration with third party services';
        public $help = 'https://apphourbooking.dwbooster.com/add-ons/webhook';


        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = $this->tr_apply("The add-on allows put the submitted information to a webhook URL, and integrate the forms with the Zapier service", 'appointment-hour-booking');

        } // End __construct


    } // End Class

    // Main add-on code
    $cpappb_webhook_obj = new CPAPPB_WebHook();

	// Add addon object to the objects list
	global $cpappb_addons_objs_list;
	$cpappb_addons_objs_list[ $cpappb_webhook_obj->get_addon_id() ] = $cpappb_webhook_obj;
}
