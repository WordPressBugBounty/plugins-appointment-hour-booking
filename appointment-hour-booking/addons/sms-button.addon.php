<?php
/*

*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPAPPB_SMSButton' ) )
{
    class CPAPPB_SMSButton extends CPAPPB_BaseAddon
    {

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-SMSButton-20220129";
		protected $name = "SMS open chat button";
		protected $description;
        public $category = 'SMS Delivery / Text Messaging';
        public $help = '';


        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = $this->tr_apply("Adds a button in the booking orders list to start a SMS chat", 'appointment-hour-booking' );
           

        } // End __construct


    } // End Class

    // Main add-on code
    $cpappb_SMSButton_obj = new CPAPPB_SMSButton();

	// Add addon object to the objects list
	global $cpappb_addons_objs_list;
	$cpappb_addons_objs_list[ $cpappb_SMSButton_obj->get_addon_id() ] = $cpappb_SMSButton_obj;
}

