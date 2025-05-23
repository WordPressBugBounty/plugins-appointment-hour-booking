<?php
/*
....
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once __DIR__.'/base.addon.php';

if( !class_exists( 'CPAPPB_UserRegistration' ) )
{
    class CPAPPB_UserRegistration extends CPAPPB_BaseAddon
    {

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-UserRegistration-20210216";
		protected $name = "User Registration";
		protected $description;
        public $category = 'Improvements';
        public $help = 'https://apphourbooking.dwbooster.com/customdownloads/user-registration-addon.png';

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = $this->tr_apply("The add-on creates a WordPress user account upon submission", 'appointment-hour-booking' );

        } // End __construct

    } // End Class

    // Main add-on code
    $cpappb_UserRegistration_obj = new CPAPPB_UserRegistration();

	// Add addon object to the objects list
	global $cpappb_addons_objs_list;
	$cpappb_addons_objs_list[ $cpappb_UserRegistration_obj->get_addon_id() ] = $cpappb_UserRegistration_obj;
}
