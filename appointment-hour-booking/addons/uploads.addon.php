<?php
/*
....
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once __DIR__.'/base.addon.php';

if( !class_exists( 'CPAPPB_Uploads' ) )
{
    class CPAPPB_Uploads extends CPAPPB_BaseAddon
    {
        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-uploads-20160330";
		protected $name = "Uploads";
		protected $description;
        public $category = 'Improvements';
        public $help = 'https://apphourbooking.dwbooster.com/documentation#uploads-addon';


        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = $this->tr_apply("The add-on allows to add the uploaded files to the Media Library, and the support for new mime types", 'appointment-hour-booking');

		} // End __construct


    } // End Class

    // Main add-on code
    $cpappb_uploads_obj = new CPAPPB_Uploads();

	// Add addon object to the objects list
	global $cpappb_addons_objs_list;
	$cpappb_addons_objs_list[ $cpappb_uploads_obj->get_addon_id() ] = $cpappb_uploads_obj;
}
