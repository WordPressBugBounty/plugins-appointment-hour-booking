<?php
/*
....
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once __DIR__.'/base.addon.php';

if( !class_exists( 'CPAPPB_Uppercase' ) )
{    
   class CPAPPB_Uppercase extends CPAPPB_BaseAddon
    {

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-uppercase-20210809";
		protected $name = "Uppercase-Capitalize fields";
		protected $description;
        public $category = 'Improvements';
        public $help = 'https://apphourbooking.dwbooster.com/customdownloads/uppercase-capitalize.png';

	

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = $this->tr_apply( "Uppercase and capitalize feature for fields.", 'appointment-hour-booking' );
           
        } // End __construct
        

    } // End Class

    // Main add-on code
    $CPAPPB_Uppercase_obj = new CPAPPB_Uppercase();

	// Add addon object to the objects list
	global $cpappb_addons_objs_list;
	$cpappb_addons_objs_list[ $CPAPPB_Uppercase_obj->get_addon_id() ] = $CPAPPB_Uppercase_obj;
    
}

