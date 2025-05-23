<?php
/*
....
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once __DIR__.'/base.addon.php';

if( !class_exists( 'CPAPPB_reCAPTCHA' ) )
{
    class CPAPPB_reCAPTCHA extends CPAPPB_BaseAddon
    {
        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-recaptcha-20151106";
		protected $name = "reCAPTCHA";
		protected $description;
        public $category = 'Integration with third party services';
        public $help = 'https://apphourbooking.dwbooster.com/add-ons/recaptcha';

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = $this->tr_apply("The add-on allows to protect the forms with reCAPTCHA service of Google", 'appointment-hour-booking');

        } // End __construct

    } // End Class

    // Main add-on code
    $cpappb_recaptcha_obj = new CPAPPB_reCAPTCHA();

	// Add addon object to the objects list
	global $cpappb_addons_objs_list;
	$cpappb_addons_objs_list[ $cpappb_recaptcha_obj->get_addon_id() ] = $cpappb_recaptcha_obj;
}
