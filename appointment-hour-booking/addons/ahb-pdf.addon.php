<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once __DIR__.'/base.addon.php';

if( !class_exists( 'CPAPPB_PDFAddon' )  )
{
    class CPAPPB_PDFAddon extends CPAPPB_BaseAddon
    {

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-Edition-20211123";
		protected $name = "PDF Generation add-on";
		protected $description;
        public $category = 'Improvements';
        public $help = 'https://apphourbooking.dwbooster.com/customdownloads/pdf-invoice-attached.png'; 


		/************************ ADDON CODE *****************************/

        /************************ ATTRIBUTES *****************************/

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = __("The add-on generates PDF files or invoices with the booking data and optionally attach it to emails", 'appointment-hour-booking' );
        } // End __construct    
        
        
    } // End Class

    // Main add-on code
    $CPAPPB_PDFAddon_obj = new CPAPPB_PDFAddon();

	// Add addon object to the objects list
	global $cpappb_addons_objs_list;
	$cpappb_addons_objs_list[ $CPAPPB_PDFAddon_obj->get_addon_id() ] = $CPAPPB_PDFAddon_obj;
}

