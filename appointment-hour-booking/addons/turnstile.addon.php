<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPAPPB_Turnstile' ) )
{
    class CPAPPB_Turnstile extends CPAPPB_BaseAddon
    {
        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-Turnstile-20151106";
		protected $name = "Cloudflare Turnstile";
		protected $description;
        public $category = 'Integration with third party services';
        public $help = 'https://apphourbooking.dwbooster.com/customdownloads/cloudflare-turnstile-captcha.png';
		

        /************************ CONSTRUCT *****************************/
		
        function __construct()
        {
			$this->description = $this->tr_apply("The add-on allows to protect the forms with Cloudflare Turnstile", 'appointment-hour-booking');
	
        } // End __construct
		
    } // End Class
    
    // Main add-on code
    $cpappb_Turnstile_obj = new CPAPPB_Turnstile();
    
	// Add addon object to the objects list
	global $cpappb_addons_objs_list;
	$cpappb_addons_objs_list[ $cpappb_Turnstile_obj->get_addon_id() ] = $cpappb_Turnstile_obj;
}

