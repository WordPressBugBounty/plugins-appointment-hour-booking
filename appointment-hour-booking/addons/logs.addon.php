<?php
/*
* Add-on: Activity Logs for Appointment Hour Booking
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once __DIR__.'/base.addon.php';

if( !class_exists( 'CPAPPB_ActivityLogsWidget' ) )
{
    class CPAPPB_ActivityLogsWidget extends CPAPPB_BaseAddon
    {

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
        protected $addonID = "addon-ActivityLogsWidget-20260707";
        protected $name = 'Activity Logs';
        protected $description;
        public $category = 'Improvements';
        public $help = 'https://apphourbooking.dwbooster.com/customdownloads/logs-addon.png';

        public function __construct()
        {
           
            $this->description = $this->tr_apply("The add-on logs actions made in the plugin (additions, updates, deletions) and displays them on a dedicated dashboard page.", 'appointment-hour-booking');
       
        }// End __construct


   } // End Class

    // Main add-on code
    $CPAPPB_ActivityLogsWidget_obj = new CPAPPB_ActivityLogsWidget();

    // Add addon object to the objects list
    global $cpappb_addons_objs_list;
    $cpappb_addons_objs_list[ $CPAPPB_ActivityLogsWidget_obj->get_addon_id() ] = $CPAPPB_ActivityLogsWidget_obj;
}