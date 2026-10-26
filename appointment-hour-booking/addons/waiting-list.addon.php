<?php
/*
* Add-on: Waiting List for Appointment Hour Booking (Backend + Frontend)
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once __DIR__ . '/base.addon.php';

if ( ! class_exists( 'CPAPPB_WaitingListAddon' ) ) {

    class CPAPPB_WaitingListAddon extends CPAPPB_BaseAddon {

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
        protected $addonID = "addon-WaitingList-20261022";
        protected $name = 'Waiting List';
        protected $description;
        public $category = 'Improvements';
        public $help = 'https://apphourbooking.dwbooster.com/customdownloads/waiting-list-addon.png';

        private $table_name;
        private $option_name = 'cpappb_waiting_list_settings';
        private $cached_bookings = array();
        private $frontend_assets_printed = false;

        public function __construct() {

            $this->description = $this->tr_apply(
                "Manages a waiting list for booked time slots on the frontend and backend, and sends automatic or manual email notifications when a spot opens up.",
                'appointment-hour-booking'
            );

        }

 

    } // End Class

    // Instantiate and register add-on in the global add-ons list
    $CPAPPB_WaitingListAddon_obj = new CPAPPB_WaitingListAddon();

    global $cpappb_addons_objs_list;
    $cpappb_addons_objs_list[ $CPAPPB_WaitingListAddon_obj->get_addon_id() ] = $CPAPPB_WaitingListAddon_obj;
}