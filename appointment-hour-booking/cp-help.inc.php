<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! is_admin() ) { echo 'Direct access not allowed.'; exit; } 
?>

<div class="wrap ahb-help-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Knowledge base and support','appointment-hour-booking'); ?></h1>
    <hr class="wp-header-end">

    <div class="notice notice-info inline ahb-support-notice">
        <p><?php esc_html_e("If the answer to your question doesn't appear in this section (try the search option below first), then contact our support service:",'appointment-hour-booking'); ?></p>
        <p>
            <a href="https://wordpress.org/support/plugin/appointment-hour-booking#new-post" target="_blank" class="button button-primary">
                <?php esc_html_e('Contact Support Service','appointment-hour-booking'); ?>
            </a>
        </p>
    </div>

    <div class="postbox" style="padding: 20px; margin-top: 20px; max-width: 800px;">
        <label for="ahb-search-input" style="font-size: 14px; font-weight: 600; display: block; margin-bottom: 10px;">
            <?php esc_html_e('Search Documentation', 'appointment-hour-booking'); ?>
        </label>
        <input type="search" id="ahb-search-input" class="regular-text" style="width: 100%; max-width: 400px;" onkeyup="filterHelp()" placeholder="<?php esc_attr_e('Type search keyword...', 'appointment-hour-booking'); ?>">
        <p class="description" style="margin-top: 5px;"><em>Use single keywords for the search, example: Calendar, Google, time, email, price...</em></p>
    </div>

    <style>
        .ahb-help-wrap .highlight { background-color: #fffdc9; color: #000; font-weight: bold; border-radius: 3px; padding: 0 2px; }
        .ahb-help-wrap h2 { margin-top: 1.5em; padding-bottom: 10px; border-bottom: 1px solid #ccd0d4; }
        .ahb-help-section { background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); margin-bottom: 20px; border-radius: 4px; }
        .ahb-help-list { margin: 0; padding: 0; list-style-type: none; column-count: 1; column-gap: 30px; }
        @media (min-width: 768px) { .ahb-help-list { column-count: 2; } }
        @media (min-width: 1200px) { .ahb-help-list { column-count: 3; } }
        .ahb-help-list li { margin-bottom: 12px; break-inside: avoid; display: flex; align-items: baseline; }
        .ahb-help-list li .item-number { font-weight: 600; margin-right: 8px; color: #646970; min-width: 25px; text-align: right; }
        .ahb-help-list a { text-decoration: none; color: #2271b1; line-height: 1.4; }
        .ahb-help-list a:hover { color: #135e96; text-decoration: underline; }
        .ahb-footer-actions { margin-top: 30px; display: flex; gap: 10px; flex-wrap: wrap; align-items: center; padding-top: 20px; border-top: 1px solid #ccd0d4; }
    </style>

    <script>
        function filterHelp() {
            const input = document.getElementById("ahb-search-input");
            const filter = input.value.trim().toUpperCase();
            const lists = document.querySelectorAll('.ahb-help-list');

            lists.forEach(list => {
                const items = list.querySelectorAll('li');
                items.forEach(item => {
                    const link = item.querySelector('a');
                    const text = link.textContent || link.innerText;

                    // Reset HTML safely to remove previous highlights
                    link.innerHTML = text;

                    if (text.toUpperCase().indexOf(filter) > -1) {
                        item.style.display = ""; // Show
                        if (filter !== '') {
                            // Highlight matching text cleanly using Regex
                            const regex = new RegExp(`(${filter})`, "gi");
                            link.innerHTML = text.replace(regex, "<span class='highlight'>$1</span>");
                        }
                    } else {
                        item.style.display = "none"; // Hide
                    }
                });
            });
        }
    </script>
      
    <div id="searchContent">
        
        <h2>Cases of use & documentation</h2>
        <div class="ahb-help-section">
            <ul class="ahb-help-list">
                <li><span class="item-number">1 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/01/05/custom-statuses" target="_blank">Adding custom statuses for the bookings</a></li>
                <li><span class="item-number">2 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/01/10/data-lookup-and-autofilling-fields" target="_blank">Data-lookup and auto-filling form fields</a></li>
                <li><span class="item-number">3 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/01/12/edition-booking-modification-by-customers-addon" target="_blank">Edition / Booking modification for customers</a></li>
                <li><span class="item-number">4 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/01/15/qrcode-image-barcode-add-on" target="_blank">Using the QRCode Image - Barcode add-on</a></li>
                <li><span class="item-number">5 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/01/20/sharing-booked-times-between-calendars" target="_blank">Sharing booked times between different calendars</a></li>
                <li><span class="item-number">6 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/01/24/auto-select-time" target="_blank">Auto-select the only available time for a date</a></li>
                <li><span class="item-number">7 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/01/26/change-max-quantity" target="_blank">Changing the selectable max quantity in the booking form</a></li>
                <li><span class="item-number">8 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/01/30/sms-with-mmdsmart" target="_blank">SMS notification for bookings with the MMD Smart service</a></li>
                <li><span class="item-number">9 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/02/10/address-auto-complete-google-maps" target="_blank">Address auto-complete fields using the Google Maps API</a></li>
                <li><span class="item-number">10 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/02/15/require-quantity-selection" target="_blank">Require explicit quantity selection</a></li>
                <li><span class="item-number">11 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/02/24/change-min-quantity" target="_blank">Changing the selectable min quantity in the booking form</a></li>
                <li><span class="item-number">12 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/03/27/password-addon" target="_blank">Requesting a password for appointments</a></li>
                <li><span class="item-number">13 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/05/15/integration-with-calculated-fields-form" target="_blank">Integration of the Appointment Hour Booking with the Calculated Fields Form plugin</a></li>
                <li><span class="item-number">14 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/09/15/remove-or-ignore-old-bookings" target="_blank">Remove or Ignore Old Bookings</a></li>
                <li><span class="item-number">15 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/10/02/price-linked-to-slots-number" target="_blank">Setting different prices per number of time-slots selected</a></li>
                <li><span class="item-number">16 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/10/03/price-linked-to-quantity" target="_blank">Setting different prices per quantity</a></li>
                <li><span class="item-number">17 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/10/04/quantity-dependant-fields" target="_blank">Adding quantity dependent fields</a></li>
                <li><span class="item-number">18 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/10/05/service-dependant-fields" target="_blank">Adding service dependent fields</a></li>
                <li><span class="item-number">19 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/10/07/posting-text-instead-value" target="_blank">Posting the dropdown, radiobutton and checkboxes text instead value</a></li>
                <li><span class="item-number">20 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/10/30/appointment-form-with-woocommerce" target="_blank">Appointment booking form with WooCommerce</a></li>
                <li><span class="item-number">21 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/11/01/schedule-calendar-contents-customization" target="_blank">Customizing the schedule calendar contents and colors</a></li>
                <li><span class="item-number">22 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/11/02/customizing-styles" target="_blank">How to customize the booking form styles?</a></li>
                <li><span class="item-number">23 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/11/10/logged-in-users-booking" target="_blank">Creating a booking form for logged in users</a></li>
                <li><span class="item-number">24 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/11/28/coupon-codes-addon" target="_blank">Using the coupon codes add-on in the Appointment Hour Booking plugin.</a></li>
                <li><span class="item-number">25 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/12/19/adding-google-iphone-outlook" target="_blank">Adding the appointments to Google Calendar and iPhone/ iPad Calendars</a></li>
                <li><span class="item-number">26 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/12/20/ical-import" target="_blank">Automatically importing/sync events from external calendars using iCal</a></li>
                <li><span class="item-number">27 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/12/24/conditional-rules" target="_blank">Using the conditional logic / dependent fields</a></li>
                <li><span class="item-number">28 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/12/26/grouped-frontend-lists" target="_blank">How to display lists of bookings in the frontend?</a></li>
                <li><span class="item-number">29 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/12/27/status-update-emails" target="_blank">Email notifications on booking status updates.</a></li>
                <li><span class="item-number">30 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2018/12/28/additional-services" target="_blank">Adding additional items / extras with prices to the booking form</a></li>
                <li><span class="item-number">31 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2019/01/10/double-opt-in-addon" target="_blank">Double opt-in verification links</a></li>
                <li><span class="item-number">32 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2019/01/11/timezone-conversion" target="_blank">Timezone Conversion: Displaying the booking times converted to the customer time zone</a></li>
                <li><span class="item-number">33 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2019/01/24/bookings-for-multiple-persons" target="_blank">Allowing bookings for multiple persons</a></li>
                <li><span class="item-number">34 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2019/05/11/booked-date-colors" target="_blank">Changing styles of the dates depending of the amount of booked/available bookings</a></li>
                <li><span class="item-number">35 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2019/07/25/book-multiple-services-timeslot" target="_blank">Booking multiple services for the same time slot</a></li>
                <li><span class="item-number">36 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2020/04/18/google-calendar-api-connection" target="_blank">Setup of the Google Calendar API add-on</a></li>
                <li><span class="item-number">37 -</span> <a href="https://apphourbooking.dwbooster.com/blog/2021/05/12/minimum-available-time" target="_blank">Minimun available date: min time required before a booking</a></li>
            </ul>
        </div>

        <h2>FAQ</h2>
        <div class="ahb-help-section">
            <ul class="ahb-help-list">
                <li><span class="item-number">1 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q94" class="collapsed" aria-expanded="false">General installation and upgrade instructions.</a></li>
                <li><span class="item-number">2 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q215" class="collapsed" aria-expanded="false">Where can I publish a appointment booking form?</a></li>
                <li><span class="item-number">3 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q316" class="collapsed" aria-expanded="false">Minimun available date: min time required before a booking</a></li>
                <li><span class="item-number">4 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q227" class="collapsed" aria-expanded="false">How to create multi-page forms?</a></li>
                <li><span class="item-number">5 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q222" class="collapsed" aria-expanded="false">How to display an image in a checkbox or radio button?</a></li>
                <li><span class="item-number">6 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q229" class="collapsed" aria-expanded="false">How to highlight the fields in the summary control?</a></li>
                <li><span class="item-number">7 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q249" class="collapsed" aria-expanded="false">How to insert a link in the form?</a></li>
                <li><span class="item-number">8 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q195" class="collapsed" aria-expanded="false">How to populate the fields with data stored in database?</a></li>
                <li><span class="item-number">9 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q82" class="collapsed" aria-expanded="false">How can I apply CSS styles to the form fields?</a></li>
                <li><span class="item-number">10 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q218" class="collapsed" aria-expanded="false">How to make the calendar 100% width / responsive?</a></li>
                <li><span class="item-number">11 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q219" class="collapsed" aria-expanded="false">How to change the calendar header row color?</a></li>
                <li><span class="item-number">12 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q240" class="collapsed" aria-expanded="false">How to center the submit button?</a></li>
                <li><span class="item-number">13 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q220" class="collapsed" aria-expanded="false">How to change the styles of the calendar day names?</a></li>
                <li><span class="item-number">14 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q221" class="collapsed" aria-expanded="false">How to change the styles of the calendar dates?</a></li>
                <li><span class="item-number">15 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q223" class="collapsed" aria-expanded="false">How to remove the calendar borders?</a></li>
                <li><span class="item-number">16 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q224" class="collapsed" aria-expanded="false">How to change the styles of the available slots?</a></li>
                <li><span class="item-number">17 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q228" class="collapsed" aria-expanded="false">How to change the styles of the used slots?</a></li>
                <li><span class="item-number">18 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q230" class="collapsed" aria-expanded="false">How to change the styles of the selected slots?</a></li>
                <li><span class="item-number">19 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q233" class="collapsed" aria-expanded="false">How assign multiple class names to a field?</a></li>
                <li><span class="item-number">20 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q237" class="collapsed" aria-expanded="false">Display fully-booked dates in different color.</a></li>
                <li><span class="item-number">21 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q312" class="collapsed" aria-expanded="false">How turn off the up/down arrows in the number fields?</a></li>
                <li><span class="item-number">22 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q66" class="collapsed" aria-expanded="false">How can I align the form using various columns?</a></li>
                <li><span class="item-number">23 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q275" class="collapsed" aria-expanded="false">How to replace submit button text to icon/image?</a></li>
                <li><span class="item-number">24 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q232" class="collapsed" aria-expanded="false">How to hide the fields on forms?</a></li>
                <li><span class="item-number">25 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q509" class="collapsed" aria-expanded="false">How to edit or remove the form title / header?</a></li>
                <li><span class="item-number">26 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q231" class="collapsed" aria-expanded="false">Is possible to modify any of predefined templates included with the plugin?</a></li>
                <li><span class="item-number">27 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q732" class="collapsed" aria-expanded="false">Is there a way to not have the ### ### #### in public phone field?</a></li>
                <li><span class="item-number">28 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q507" class="collapsed" aria-expanded="false">Can the emails be customized?</a></li>
                <li><span class="item-number">29 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q81" class="collapsed" aria-expanded="false">How can I add specific fields into the email message?</a></li>
                <li><span class="item-number">30 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q97" class="collapsed" aria-expanded="false">Can I add a reference to the item number (submission number) into the email?</a></li>
                <li><span class="item-number">31 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q160" class="collapsed" aria-expanded="false">How can I include the link to the uploaded file into the email message?</a></li>
                <li><span class="item-number">32 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q309" class="collapsed" aria-expanded="false">How to insert an image in the notification emails?</a></li>
                <li><span class="item-number">33 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q234" class="collapsed" aria-expanded="false">How to insert changes of lines in the notification emails, when the HTML format is selected?</a></li>
                <li><span class="item-number">34 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q512" class="collapsed" aria-expanded="false">How to highlight specific dates?</a></li>
                <li><span class="item-number">35 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q241" class="collapsed" aria-expanded="false">How to center the phone field?</a></li>
                <li><span class="item-number">36 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q252" class="collapsed" aria-expanded="false">How integrate the forms with the WooCommerce products?</a></li>
                <li><span class="item-number">37 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q315" class="collapsed" aria-expanded="false">How to customize the fields displayed in the cart page of WooCommerce?</a></li>
                <li><span class="item-number">38 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q272" class="collapsed" aria-expanded="false">How to export the submitted files to DropBox?</a></li>
                <li><span class="item-number">39 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q273" class="collapsed" aria-expanded="false">How to generate a PDF file with the submitted information, and send the file to the user?</a></li>
                <li><span class="item-number">40 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q274" class="collapsed" aria-expanded="false">How to use a file field with multiple selection from Zapier?</a></li>
                <li><span class="item-number">41 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q508" class="collapsed" aria-expanded="false">The form doesn't appear. Solution?</a></li>
                <li><span class="item-number">42 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q502" class="collapsed" aria-expanded="false">I'm not receiving the emails with the appointment data.</a></li>
                <li><span class="item-number">43 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q226" class="collapsed" aria-expanded="false">The form doesn't appear in the public website. Solution?</a></li>
                <li><span class="item-number">44 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q225" class="collapsed" aria-expanded="false">Non-latin characters aren't being displayed in the form. There is a workaround?</a></li>
                <li><span class="item-number">45 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q317" class="collapsed" aria-expanded="false">Getting unexpected cancellations through the cancellation add-on?</a></li>
                <li><span class="item-number">46 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q95" class="collapsed" aria-expanded="false">I'm getting this message: "Destination folder already exists". Solution?</a></li>
                <li><span class="item-number">47 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q214" class="collapsed" aria-expanded="false">Is the "Appointment Hour Booking" plugin compatible with "Autoptimize"?</a></li>
                <li><span class="item-number">48 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#qwr214" class="collapsed" aria-expanded="false">Is the plugin compatible with "WP Rocket"?</a></li>
                <li><span class="item-number">49 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q216" class="collapsed" aria-expanded="false">About the datasource fields.</a></li>
                <li><span class="item-number">50 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q235" class="collapsed" aria-expanded="false">Using the datasource to load the logged in user info.</a></li>
                <li><span class="item-number">51 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q217" class="collapsed" aria-expanded="false">Additional form CSS styles.</a></li>
                <li><span class="item-number">52 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q511" class="collapsed" aria-expanded="false">Can I display a list with the appointments?</a></li>
                <li><span class="item-number">53 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q304" class="collapsed" aria-expanded="false">Can I publish multiple calendars in the same page?</a></li>
                <li><span class="item-number">54 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q305" class="collapsed" aria-expanded="false">Can I add the submitted form data as parameters to the redirection "Thank you" page?</a></li>
                <li><span class="item-number">55 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q306" class="collapsed" aria-expanded="false">How to pre-select the service on different pages?</a></li>
                <li><span class="item-number">56 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q307" class="collapsed" aria-expanded="false">How to pre-fill the form fields in the public booking form?</a></li>
                <li><span class="item-number">57 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q308" class="collapsed" aria-expanded="false">Can I show capacity of service?</a></li>
                <li><span class="item-number">58 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q311" class="collapsed" aria-expanded="false">Can I hide the service drop-down?</a></li>
                <li><span class="item-number">59 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q314" class="collapsed" aria-expanded="false">How to modify the date format in the "Schedule Calendar view"?</a></li>
                <li><span class="item-number">60 -</span> <a data-toggle="collapse" target="_blank" href="https://apphourbooking.dwbooster.com/faq/#q310" class="collapsed" aria-expanded="false">Can I add a "reset" button for the form fields and selected times?</a></li>
            </ul>
        </div>
        
        <h2>Screenshots</h2>
        <div class="ahb-help-section">
            <ul class="ahb-help-list">
    <li><span class="item-number">1 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/admin-only-field.png" target="_blank">admin only field</a></li>
    <li><span class="item-number">2 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/appointment-end-time.png" target="_blank">appointment end time</a></li>
    <li><span class="item-number">3 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/auto-focus-jump.png" target="_blank">auto focus jump</a></li>
    <li><span class="item-number">4 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/autofill-fields-from-URL-GET-params.png" target="_blank">autofill fields from URL GET params</a></li>
    <li><span class="item-number">5 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/avoid-overlapping.png" target="_blank">avoid overlapping</a></li>
    <li><span class="item-number">6 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/confirmation-email-to-user.png" target="_blank">confirmation email to user</a></li>
    <li><span class="item-number">7 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/custom-css-editor.png" target="_blank">custom css editor</a></li>
    <li><span class="item-number">8 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/dashboard-documentation.png" target="_blank">dashboard documentation</a></li>
    <li><span class="item-number">9 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/dashboard-widget-enable.png" target="_blank">dashboard widget enable</a></li>
    <li><span class="item-number">10 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/dashboard-widget-settings.png" target="_blank">dashboard widget settings</a></li>
    <li><span class="item-number">11 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/dashboard-widget.png" target="_blank">dashboard widget</a></li>
    <li><span class="item-number">12 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/display-slot-capacity.png" target="_blank">display slot capacity</a></li>
    <li><span class="item-number">13 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/edit-feature.png" target="_blank">edit feature</a></li>
    <li><span class="item-number">14 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/equal-to-rule.png" target="_blank">equal to rule</a></li>
    <li><span class="item-number">15 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/exclude-csv-fields.png" target="_blank">exclude csv fields</a></li>
    <li><span class="item-number">16 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/giving-access-to-users.png" target="_blank">giving access to users</a></li>
    <li><span class="item-number">17 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/google-calendar-api.png" target="_blank">google calendar api</a></li>
    <li><span class="item-number">18 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/make-user-select-service.png" target="_blank">make user select service</a></li>
    <li><span class="item-number">19 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/min-date-hours.png" target="_blank">min date hours</a></li>
    <li><span class="item-number">20 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/recaptcha-keys.png" target="_blank">recaptcha keys</a></li>
    <li><span class="item-number">21 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/recurrent-panel.png" target="_blank">recurrent panel</a></li>
    <li><span class="item-number">22 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/repeat-and-statuses.png" target="_blank">repeat and statuses</a></li>
    <li><span class="item-number">23 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/required-field.png" target="_blank">required field</a></li>
    <li><span class="item-number">24 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/schedule-csv.png" target="_blank">schedule csv</a></li>
    <li><span class="item-number">25 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/schedule-data-settings.png" target="_blank">schedule data settings</a></li>
    <li><span class="item-number">26 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/setting-prices-and-duration.png" target="_blank">setting prices and duration</a></li>
    <li><span class="item-number">27 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/show-used-slots.png" target="_blank">show used slots</a></li>
    <li><span class="item-number">28 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/special-open-hours.png" target="_blank">special open hours</a></li>
    <li><span class="item-number">29 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/special-padding.png" target="_blank">special padding</a></li>
    <li><span class="item-number">30 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/start-weekday.png" target="_blank">start weekday</a></li>
    <li><span class="item-number">31 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/statuses.png" target="_blank">statuses</a></li>
    <li><span class="item-number">32 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/style-for-displaying-slots.png" target="_blank">style for displaying slots</a></li>
    <li><span class="item-number">33 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/thank-you-page.png" target="_blank">thank you page</a></li>
    <li><span class="item-number">34 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/time-format.png" target="_blank">time format</a></li>
    <li><span class="item-number">35 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/tooltip-contents.png" target="_blank">tooltip contents</a></li>
    <li><span class="item-number">36 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/./../images/articles/zoom-add-on-settings.png" target="_blank">zoom add on settings</a></li>
    <li><span class="item-number">37 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/Google-Calendar-Enable-API.png" target="_blank">Google Calendar Enable API</a></li>
    <li><span class="item-number">38 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/admin-only-field-control-administration.png" target="_blank">admin only field control administration</a></li>
    <li><span class="item-number">39 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/appointment-limits-per-user.png" target="_blank">appointment limits per user</a></li>
    <li><span class="item-number">40 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/attach-ical-file-emails.png" target="_blank">attach ical file emails</a></li>
    <li><span class="item-number">41 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/auth-net-addon.png" target="_blank">auth net addon</a></li>
    <li><span class="item-number">42 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/authorize.net.png" target="_blank">authorize.net</a></li>
    <li><span class="item-number">43 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/block-times.png" target="_blank">block times</a></li>
    <li><span class="item-number">44 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/booking-form-tooltip.png" target="_blank">booking form tooltip</a></li>
    <li><span class="item-number">45 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/booking-orders-list.png" target="_blank">booking orders list</a></li>
    <li><span class="item-number">46 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/bulk-delete-and-status-update-booking-orders.png" target="_blank">bulk delete and status update booking orders</a></li>
    <li><span class="item-number">47 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/calendar-admin-in-frontend.png" target="_blank">calendar admin in frontend</a></li>
    <li><span class="item-number">48 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/calendar-end-time.png" target="_blank">calendar end time</a></li>
    <li><span class="item-number">49 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/calendar-language.png" target="_blank">calendar language</a></li>
    <li><span class="item-number">50 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/cancellation-notification-emails.png" target="_blank">cancellation notification emails</a></li>
    <li><span class="item-number">51 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/cancellation-redirection-pages.png" target="_blank">cancellation redirection pages</a></li>
    <li><span class="item-number">52 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/capacity-override.png" target="_blank">capacity override</a></li>
    <li><span class="item-number">53 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/captcha-settings.png" target="_blank">captcha settings</a></li>
    <li><span class="item-number">54 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/cloudflare-turnstile-captcha.png" target="_blank">cloudflare turnstile captcha</a></li>
    <li><span class="item-number">55 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/conditional-content-emails.png" target="_blank">conditional content emails</a></li>
    <li><span class="item-number">56 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/credits-purchase-add-on.png" target="_blank">credits purchase add on</a></li>
    <li><span class="item-number">57 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/css-additional-class.png" target="_blank">css additional class</a></li>
    <li><span class="item-number">58 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/csv-char-encoding.png" target="_blank">csv char encoding</a></li>
    <li><span class="item-number">59 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/currency-setting.png" target="_blank">currency setting</a></li>
    <li><span class="item-number">60 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/default-date.png" target="_blank">default date</a></li>
    <li><span class="item-number">61 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/delete-field.png" target="_blank">delete field</a></li>
    <li><span class="item-number">62 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/deposits-payments.png" target="_blank">deposits payments</a></li>
    <li><span class="item-number">63 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/elementor.png" target="_blank">elementor</a></li>
    <li><span class="item-number">64 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/email-blacklist.png" target="_blank">email blacklist</a></li>
    <li><span class="item-number">65 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/email-from-name.png" target="_blank">email from name</a></li>
    <li><span class="item-number">66 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/email-settings.png" target="_blank">email settings</a></li>
    <li><span class="item-number">67 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/enable-google-api-addon.png" target="_blank">enable google api addon</a></li>
    <li><span class="item-number">68 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/equal-to-rule.png" target="_blank">equal to rule</a></li>
    <li><span class="item-number">69 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/field-ids-tags-form-builder.png" target="_blank">field ids tags form builder</a></li>
    <li><span class="item-number">70 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/flow-payment-gateway-chile.png" target="_blank">flow payment gateway chile</a></li>
    <li><span class="item-number">71 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/generate-time-slots.png" target="_blank">generate time slots</a></li>
    <li><span class="item-number">72 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/google-cal-api-time-adjustment.png" target="_blank">google cal api time adjustment</a></li>
    <li><span class="item-number">73 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/google-calendar-test-user.png" target="_blank">google calendar test user</a></li>
    <li><span class="item-number">74 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/hide-service-field.png" target="_blank">hide service field</a></li>
    <li><span class="item-number">75 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/honeypot-feature.png" target="_blank">honeypot feature</a></li>
    <li><span class="item-number">76 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/ical-content.png" target="_blank">ical content</a></li>
    <li><span class="item-number">77 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/ical-time-difference.png" target="_blank">ical time difference</a></li>
    <li><span class="item-number">78 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/import-export-form-settings.png" target="_blank">import export form settings</a></li>
    <li><span class="item-number">79 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/installation-process.png" target="_blank">installation process</a></li>
    <li><span class="item-number">80 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/invalid-dates.png" target="_blank">invalid dates</a></li>
    <li><span class="item-number">81 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/license.png" target="_blank">license</a></li>
    <li><span class="item-number">82 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/load-speed-cache.png" target="_blank">load speed cache</a></li>
    <li><span class="item-number">83 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/max-appointments.png" target="_blank">max appointments</a></li>
    <li><span class="item-number">84 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/military-time.png" target="_blank">military time</a></li>
    <li><span class="item-number">85 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/min-date-max-date.png" target="_blank">min date max date</a></li>
    <li><span class="item-number">86 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/min-date-mixed.png" target="_blank">min date mixed</a></li>
    <li><span class="item-number">87 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/mycred_activating_and_settings.png" target="_blank">mycred activating and settings</a></li>
    <li><span class="item-number">88 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/mycred_enabling.png" target="_blank">mycred enabling</a></li>
    <li><span class="item-number">89 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/mycred_visual_settings.png" target="_blank">mycred visual settings</a></li>
    <li><span class="item-number">90 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/only-date-rule.png" target="_blank">only date rule</a></li>
    <li><span class="item-number">91 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/order-payment-options-add-on.png" target="_blank">order payment options add on</a></li>
    <li><span class="item-number">92 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/order-payment-options.png" target="_blank">order payment options</a></li>
    <li><span class="item-number">93 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/overwrite-price-edit-edition.png" target="_blank">overwrite price edit edition</a></li>
    <li><span class="item-number">94 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/padding-time.png" target="_blank">padding time</a></li>
    <li><span class="item-number">95 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/paid-later-status.png" target="_blank">paid later status</a></li>
    <li><span class="item-number">96 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/pay-later-label.png" target="_blank">pay later label</a></li>
    <li><span class="item-number">97 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/payment-add-ons-settings.png" target="_blank">payment add ons settings</a></li>
    <li><span class="item-number">98 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/payment-product-name.png" target="_blank">payment product name</a></li>
    <li><span class="item-number">99 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/paypal-add-ons-settings.png" target="_blank">paypal add ons settings</a></li>
    <li><span class="item-number">100 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/pdf-invoice-attached.png" target="_blank">pdf invoice attached</a></li>
    <li><span class="item-number">101 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/pending-status-expiration.png" target="_blank">pending status expiration</a></li>
    <li><span class="item-number">102 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/plugin-version-number.png" target="_blank">plugin version number</a></li>
    <li><span class="item-number">103 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/query-monitor.png" target="_blank">query monitor</a></li>
    <li><span class="item-number">104 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/razorpay.png" target="_blank">razorpay</a></li>
    <li><span class="item-number">105 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/read-only-access.png" target="_blank">read only access</a></li>
    <li><span class="item-number">106 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/revolut-payments.png" target="_blank">revolut payments</a></li>
    <li><span class="item-number">107 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/sales-force-add-on-settings.png" target="_blank">sales force add on settings</a></li>
    <li><span class="item-number">108 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/schedule-calendar-add-on-configuration.png" target="_blank">schedule calendar add on configuration</a></li>
    <li><span class="item-number">109 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/schedule-calendar-add-on-enabling.png" target="_blank">schedule calendar add on enabling</a></li>
    <li><span class="item-number">110 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/schedule-calendar-view.png" target="_blank">schedule calendar view</a></li>
    <li><span class="item-number">111 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/schedule-colors-per-calendar.png" target="_blank">schedule colors per calendar</a></li>
    <li><span class="item-number">112 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/schedule-list-view.png" target="_blank">schedule list view</a></li>
    <li><span class="item-number">113 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/shortcode-quote-characters.png" target="_blank">shortcode quote characters</a></li>
    <li><span class="item-number">114 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/show-total-cost.png" target="_blank">show total cost</a></li>
    <li><span class="item-number">115 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/single-days-selection-interface.png" target="_blank">single days selection interface</a></li>
    <li><span class="item-number">116 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/sms-button.png" target="_blank">sms button</a></li>
    <li><span class="item-number">117 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/split-day-hours.png" target="_blank">split day hours</a></li>
    <li><span class="item-number">118 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/square-settings.png" target="_blank">square settings</a></li>
    <li><span class="item-number">119 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/stripe-add-on-settings.png" target="_blank">stripe add on settings</a></li>
    <li><span class="item-number">120 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/submit-label.png" target="_blank">submit label</a></li>
    <li><span class="item-number">121 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/summary-field-configuration.png" target="_blank">summary field configuration</a></li>
    <li><span class="item-number">122 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/summary-field.png" target="_blank">summary field</a></li>
    <li><span class="item-number">123 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/text-em-all-add-on.png" target="_blank">text em all add on</a></li>
    <li><span class="item-number">124 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/troubleshoot-area.png" target="_blank">troubleshoot area</a></li>
    <li><span class="item-number">125 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/twilio-sms.png" target="_blank">twilio sms</a></li>
    <li><span class="item-number">126 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/uppercase-capitalize.png" target="_blank">uppercase capitalize</a></li>
    <li><span class="item-number">127 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/user-registration-addon.png" target="_blank">user registration addon</a></li>
    <li><span class="item-number">128 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/validation-texts.png" target="_blank">validation texts</a></li>
    <li><span class="item-number">129 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/whatsapp-button-addon.png" target="_blank">whatsapp button addon</a></li>
    <li><span class="item-number">130 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/widget.png" target="_blank">widget</a></li>
    <li><span class="item-number">131 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/woocommerce-billing-autofill.png" target="_blank">woocommerce billing autofill</a></li>
    <li><span class="item-number">132 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/working-dates.png" target="_blank">working dates</a></li>
    <li><span class="item-number">133 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/zoom-oauth-permissions-scopes.png" target="_blank">zoom oauth permissions scopes</a></li>
    <li><span class="item-number">134 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/zoom-oauth-setup-II.png" target="_blank">zoom oauth setup II</a></li>
    <li><span class="item-number">135 -</span> <a href="https://apphourbooking.dwbooster.com/customdownloads/zoom-oauth-setup.png" target="_blank">zoom oauth setup</a></li>
</ul>
        </div>
    </div>

    <div class="ahb-footer-actions">
        <a href="https://apphourbooking.dwbooster.com/blog" target="_blank" class="button button-secondary"><?php esc_html_e('Common Cases of Use','appointment-hour-booking'); ?></a>
        <a href="https://apphourbooking.dwbooster.com/faq" target="_blank" class="button button-secondary"><?php esc_html_e('Complete FAQ','appointment-hour-booking'); ?></a>
        <a href="https://apphourbooking.dwbooster.com/documentation" target="_blank" class="button button-secondary"><?php esc_html_e('General Documentation','appointment-hour-booking'); ?></a>
        <a href="https://wordpress.org/support/plugin/appointment-hour-booking#new-post" target="_blank" class="button button-primary"><?php esc_html_e('Contact Support Service','appointment-hour-booking'); ?></a>
    </div>

    <p style="margin-top: 15px;">
        <a href="https://wordpress.org/support/plugin/appointment-hour-booking#new-post" target="_blank">
            <?php esc_html_e('Further questions? Contact our support service.','appointment-hour-booking'); ?>
        </a>
    </p>

</div>