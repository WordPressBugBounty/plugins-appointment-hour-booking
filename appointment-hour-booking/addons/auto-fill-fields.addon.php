<?php
/*
    Shared Availability Addon
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPAPPB_AutoFillFields' ) )
{
    class CPAPPB_AutoFillFields extends CPAPPB_BaseAddon
    {

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-AutoFillFields-20241203";
		protected $name = "Auto-fill fields from URL params";
		protected $description;
        public $category = 'Improvements';
        public $help = 'https://apphourbooking.dwbooster.com/images/articles/autofill-fields-from-URL-GET-params.png';

        /************************ CONSTRUCT *****************************/

        function __construct()
        {            
			$this->description = $this->tr_apply("Automatically pre-fill form fields with data form URL GET parameters.", 'appointment-hour-booking' );
        } // End __construct



    } // End Class

    // Main add-on code
    $CPAPPB_AutoFillFields_obj = new CPAPPB_AutoFillFields();

	// Add addon object to the objects list
	global $cpappb_addons_objs_list;
	$cpappb_addons_objs_list[ $CPAPPB_AutoFillFields_obj->get_addon_id() ] = $CPAPPB_AutoFillFields_obj;
}

