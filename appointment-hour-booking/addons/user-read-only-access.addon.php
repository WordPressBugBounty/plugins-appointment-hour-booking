<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPAPPB_UserReadOnly' ) )
{
    class CPAPPB_UserReadOnly extends CPAPPB_BaseAddon
    {

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-UserReadOnly-20250129";
		protected $name = "User (admin) Read-only access to reports";
		protected $description;
        public $category = 'Improvements';
        public $help = 'https://apphourbooking.dwbooster.com/contact-us';


		/************************ ADDON CODE *****************************/

        /************************ ATTRIBUTES *****************************/

        private $form_table = 'cpappbk_form_userreg';
        private $_inserted = false;

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = $this->tr_apply("Gives read-only access to specific admin users/groups to messages list and reports", 'appointment-hour-booking' );
            // Check if the plugin is active


        } // End __construct



        /************************ PRIVATE METHODS *****************************/


    } // End Class

    // Main add-on code
    $cpappb_UserReadOnly_obj = new CPAPPB_UserReadOnly();

	// Add addon object to the objects list
	global $cpappb_addons_objs_list;
	$cpappb_addons_objs_list[ $cpappb_UserReadOnly_obj->get_addon_id() ] = $cpappb_UserReadOnly_obj;    
}
?>