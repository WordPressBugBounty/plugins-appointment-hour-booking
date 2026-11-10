<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$this->item = (isset($_GET["cal"]) ? intval($_GET["cal"]) : 0);

$current_user = wp_get_current_user();
$current_user_access = current_user_can('manage_options');

if ( !is_admin() || (!$current_user_access && !@in_array($current_user->ID, unserialize($this->get_option("cp_user_access",serialize(array()))))))
{
    echo 'Direct access not allowed.';
    exit;
}

$current_page = intval( (empty($_GET["p"])?0:$_GET["p"]) );
if (!$current_page) $current_page = 1;
$records_per_page = 50;

$message = "";

if (isset($_GET['statusmark']) && $_GET['statusmark'] != '')
{
    $this->verify_nonce ( sanitize_text_field($_GET["anonce"]), 'cpappb_actions_booking');
    for ($i=0; $i<=$records_per_page; $i++)
    if (isset($_GET['c'.$i]) && $_GET['c'.$i] != '')   
    {
        $this->update_status( intval($_GET['c'.$i]), sanitize_text_field($_GET['sbmi']) );        
    }
    $message = __('Marked items status updated','appointment-hour-booking');
}
else if (isset($_GET['resend']) && $_GET['resend'] != '')
{
    $this->verify_nonce ( sanitize_text_field($_GET["anonce"]), 'cpappb_actions_booking');
    $this->ready_to_go_reservation( intval($_GET['resend']), '', true);        
    $message = __('Notification emails resent for the booking','appointment-hour-booking');
}
else if (isset($_GET['delmark']) && $_GET['delmark'] != '')
{
    $this->verify_nonce ( sanitize_text_field($_GET["anonce"]), 'cpappb_actions_booking');
    for ($i=0; $i<=$records_per_page; $i++)
    if (isset($_GET['c'.$i]) && $_GET['c'.$i] != '') {
        do_action( 'cpappb_item_deleted', intval($_GET['c'.$i]) );
        $wpdb->query( $wpdb->prepare('DELETE FROM `'.$wpdb->prefix.'cpappbk_messages` WHERE id=%d', sanitize_text_field($_GET['c'.$i])) ); 
    }
    do_action( 'cpappb_cache_clean', $this->item );
    $message = __('Marked items deleted','appointment-hour-booking');
}
else if (isset($_GET['del']) && $_GET['del'] == 'all')
{
    $this->verify_nonce (sanitize_text_field($_GET["anonce"]), 'cpappb_actions_booking');
    if ($this->item == '' || $this->item == '0')
        $wpdb->query('DELETE FROM `'.$wpdb->prefix.'cpappbk_messages`'); 
    else
        $wpdb->query($wpdb->prepare('DELETE FROM `'.$wpdb->prefix.'cpappbk_messages` WHERE formid=%d', $this->item)); 
    $message = __('All items deleted','appointment-hour-booking');
}
else if (isset($_GET['lu']) && $_GET['lu'] != '')
{
    $this->verify_nonce (sanitize_text_field($_GET["anonce"]), 'cpappb_actions_booking');
    $myrows = $wpdb->get_results( $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."cpappbk_messages WHERE id=%d", sanitize_text_field($_GET['lu'])) ); 
    $params = unserialize($myrows[0]->posted_data);
    $params["paid"] = sanitize_text_field($_GET["status"]);
    $params["payment_type"] = __('Manually updated','appointment-hour-booking');
    $wpdb->query( $wpdb->prepare('UPDATE `'.$wpdb->prefix.'cpappbk_messages` SET posted_data=%s WHERE id=%d', serialize($params), sanitize_text_field($_GET['lu'])) ); 
    $message = __('Item updated','appointment-hour-booking');
}
else if (isset($_GET['ld']) && $_GET['ld'] != '')
{
    $this->verify_nonce (sanitize_text_field($_GET["anonce"]), 'cpappb_actions_booking');
    do_action( 'cpappb_item_deleted', intval($_GET['ld']) );
    $wpdb->query( $wpdb->prepare('DELETE FROM `'.$wpdb->prefix.'cpappbk_messages` WHERE id=%d', sanitize_text_field($_GET['ld'])) ); 
    $message = __('Item deleted','appointment-hour-booking');
}
else if (isset($_GET['ud']) && $_GET['ud'] != '')
{
    $this->verify_nonce (sanitize_text_field($_GET["anonce"]), 'cpappb_actions_booking');      
    if (isset($_GET["udidx"]))
        $this->update_status(sanitize_text_field($_GET['ud']), sanitize_text_field($_GET['status']), intval($_GET["udidx"]));
    else  
        $this->update_status(sanitize_text_field($_GET['ud']), sanitize_text_field($_GET['status']));
    
    if ( !empty( $_GET["or"] ) && $_GET["or"] == 'shlist')
    {
      ?><script type="text/javascript">
       document.location = 'admin.php?page=<?php echo esc_js($this->menu_parameter); ?>&schedule=1&cal=<?php echo intval($_GET["cal"]); ?>#sb<?php echo intval($_GET["ud"]).'_'.intval($_GET["udidx"]); ?>';
       </script>
      <?php
      exit;
    }        
    $message = __('Status updated','appointment-hour-booking');
}

if ($this->item != 0)
    $myform = $wpdb->get_results( $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'cpappbk_forms WHERE id=%d' ,$this->item) ); 

$rawfrom = (isset($_GET["dfrom"]) ? sanitize_text_field($_GET["dfrom"]) : '');
$rawto = (isset($_GET["dto"]) ? sanitize_text_field(@$_GET["dto"]) : '');
if ($this->get_option('date_format', 'mm/dd/yy') == 'dd/mm/yy')
{
    $rawfrom = str_replace('/','.',$rawfrom);
    $rawto = str_replace('/','.',$rawto);
}

$cond = '';

if ( ! empty( $_GET['search'] ) ) {
    $search = sanitize_text_field( wp_unslash( $_GET['search'] ) );
    $like   = '%' . $wpdb->esc_like( $search ) . '%';    
    $cond .= $wpdb->prepare( ' AND (data LIKE %s OR posted_data LIKE %s)', $like, $like );
}

if ( $rawfrom != '' ) {
    $date_start = gmdate( 'Y-m-d', strtotime( $rawfrom ) );
    $cond      .= $wpdb->prepare( ' AND (`time` >= %s)', $date_start );
}

if ( $rawto != '' ) {
    $date_end = gmdate( 'Y-m-d', strtotime( $rawto ) );
    $cond    .= $wpdb->prepare( ' AND (`time` <= %s)', $date_end . ' 23:59:59' );
}

if ( $this->item != 0 ) {
    $cond .= $wpdb->prepare( ' AND formid = %d', $this->item );
}

$table_name = $wpdb->prefix . 'cpappbk_messages';

// First Query: Pagination Count
$count_query = "SELECT count(id) as ck FROM {$table_name} WHERE 1=1 {$cond} ORDER BY `time` DESC";
// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$eventscount = $wpdb->get_results( $count_query );

$total_pages = ceil( $eventscount[0]->ck / $records_per_page );

$offset       = ( $current_page - 1 ) * $records_per_page;
$limit_clause = $wpdb->prepare( " LIMIT %d, %d", $offset, $records_per_page );

$events_query = "SELECT * FROM {$table_name} WHERE 1=1 {$cond} ORDER BY `time` DESC {$limit_clause}";

$events_query = apply_filters( 'cpappb_messages_query', $events_query );

// Second Query: Items
// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$events = $wpdb->get_results( $events_query );

$nonce = wp_create_nonce( 'cpappb_actions_booking' );
$reply_nonce = wp_create_nonce( 'cpappb_reply_nonce' );
$ai_reply_enabled = ( get_option( 'cpappb_ai_reply_enabled', '0' ) === '1' );

?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Booking Orders','appointment-hour-booking'); ?> - <?php if ($this->item != 0) echo esc_html($myform[0]->form_name); else echo 'All forms'; ?></h1>
    <a href="<?php print esc_attr(admin_url('admin.php?page='.$this->menu_parameter));?>" class="page-title-action">&larr; <?php esc_html_e('Return to the calendars list','appointment-hour-booking'); ?></a>
    <hr class="wp-header-end">

    <?php if ($message && !isset($_GET['ud'])) echo "<div id='setting-error-settings_updated' class='updated notice is-dismissible'><p><strong>".esc_html($message)."</strong></p></div>"; ?>

<script type="text/javascript">
     // AJAX Status Update
     function cp_UpsItem(id) {
         var selectBox = document.getElementById("sb" + id);
         var status = selectBox.options[selectBox.selectedIndex].value;
         var url = 'admin.php?page=<?php echo esc_js($this->menu_parameter); ?>&anonce=<?php echo esc_js($nonce); ?>&cal=<?php echo intval($_GET["cal"]); ?>&list=1&ud=' + id + '&status=' + encodeURIComponent(status) + '&r=' + Math.random();
         
         selectBox.disabled = true;
         
         jQuery.get(url, function() {
             selectBox.disabled = false;
             var tr = jQuery(selectBox).closest('tr');
             var lowerStatus = status.toLowerCase();
             
             // Toggle cancelled class based on status
             if (lowerStatus === 'cancelled' || lowerStatus === 'cancelled by customer') {
                 tr.addClass('cpappb_cancelled');
             } else {
                 tr.removeClass('cpappb_cancelled');
             }
             
             // Visual feedback
             var check = jQuery('<span style="color:green; margin-left:5px; font-weight:bold;">&#10003;</span>');
             jQuery(selectBox).after(check);
             check.fadeOut(2000, function() { jQuery(this).remove(); });
         });
     }

     // AJAX Toggle Payment
     function cp_updateMessageItem(id, status, linkElement) {
         var url = 'admin.php?page=<?php echo esc_js($this->menu_parameter); ?>&anonce=<?php echo esc_js($nonce); ?>&cal=<?php echo intval($_GET["cal"]); ?>&list=1&status=' + status + '&lu=' + id + '&r=' + Math.random();
         
         var $link = jQuery(linkElement);
         var $cell = $link.closest('tr').find('td').eq(4); // Target the Paid Status column
         
         $link.css('opacity', '0.5');

         jQuery.get(url, function() {
             $link.css('opacity', '1');
             // Toggle the link status for next click
             var newStatus = (status == '1') ? '0' : '1';
             $link.attr('onclick', 'cp_updateMessageItem(' + id + ',' + newStatus + ', this);');

             // Update the visual icon in the "Paid Status" column
             if (status == '1') {
                 $cell.html('<span class="dashicons dashicons-money-alt" style="color: green;" title="Paid"></span><br/><em style="font-size: 11px; color: #666;"><?php esc_html_e('Manually updated','appointment-hour-booking'); ?></em>');
             } else {
                 $cell.html('<span class="dashicons dashicons-minus" style="color: #d63638;" title="Not Paid"></span>');
             }
         });
     }

    function cp_resendMessageItem(id, element) {
        if (confirm('<?php echo esc_js(__('Are you sure that you want to resend the notification emails for this item?','appointment-hour-booking')); ?>')) {
            var url = 'admin.php?page=<?php echo esc_js($this->menu_parameter); ?>&anonce=<?php echo esc_js($nonce); ?>&cal=<?php echo intval($_GET["cal"]); ?>&list=1&resend='+id+'&r='+Math.random();
            var $link = jQuery(element);
            var originalText = $link.text();
            
            // Visual feedback while processing
            $link.text('<?php echo esc_js(__('Sending...','appointment-hour-booking')); ?>').css({'pointer-events': 'none', 'opacity': '0.5'});
            
            jQuery.get(url, function() {
                // Success state
                $link.text('<?php echo esc_js(__('Sent!','appointment-hour-booking')); ?>').css('color', 'green');
                
                // Revert to original state after 2 seconds
                setTimeout(function() {
                    $link.text(originalText).css({'pointer-events': 'auto', 'opacity': '1', 'color': ''});
                }, 2000);
            });
        }
     } 
     
     function cp_deleteMessageItem(id, element) {
        if (confirm('<?php echo esc_js(__('Are you sure that you want to delete this item?','appointment-hour-booking')); ?>')) {
            var url = 'admin.php?page=<?php echo esc_js($this->menu_parameter); ?>&anonce=<?php echo esc_js($nonce); ?>&cal=<?php echo intval($_GET["cal"]); ?>&list=1&ld='+id+'&r='+Math.random();
            var $tr = jQuery(element).closest('tr');
            
            // Visual feedback while processing
            $tr.css('opacity', '0.5');
            
            jQuery.get(url, function() {
                // Smoothly fade out and remove the row
                $tr.fadeOut(400, function() {
                    jQuery(this).remove();
                });
            });
        }
     }
     function cp_deletemarked() {
        if (confirm('Are you sure that you want to delete the marked items?'))
            document.dex_table_form.submit();
     }
     function cp_statusmarked() {
        if (confirm('Are you sure that you want to change the status of the marked items?')) {                
            document.dex_table_form.delmark.value = '';
            document.dex_table_form.statusmark.value = '1';
            var status = document.getElementById("statusbox_markeditems").options[document.getElementById("statusbox_markeditems").selectedIndex].value;
            document.dex_table_form.sbmi.value = status;        
            document.dex_table_form.submit();
        }
     }  
     function cp_deleteall() {
        if (confirm('Are you sure that you want to delete ALL bookings for this form?')) {
            if (confirm('Please note that this action cannot be undone. ALL THE BOOKINGS of this form will be DELETED. Are you sure that you want to delete ALL bookings for this form?'))
                document.location = 'admin.php?page=<?php echo esc_js($this->menu_parameter); ?>&cal=<?php echo intval($_GET["cal"]); ?>&list=1&del=all&anonce=<?php echo esc_js($nonce); ?>&r='+Math.random();
        }
     }
     function cp_markall() {
         var ischecked = document.getElementById("cpcontrolck").checked;
         <?php for ($i=0; $i<$records_per_page; $i++) if (isset($events[$i])) { ?>
         if(document.forms.dex_table_form.c<?php echo intval($i); ?>) {
             document.forms.dex_table_form.c<?php echo intval($i); ?>.checked = ischecked;
         }
         <?php } ?>
     }
     function do_dexapp_print() {
          w=window.open();
          w.document.write("<style>.ahbnoprint, .check-column, .cpnopr{display:none;} table{border-collapse:collapse;width:100%;font-family:sans-serif;} th, td{border:1px solid #ddd;padding:8px;text-align:left;} th{background-color:#f4f4f4;}</style>"+document.getElementById('dex_printable_contents').innerHTML);
          w.print();
          w.close();
     }
    </script>

    <div class="tablenav top">
        <form action="admin.php" method="get" style="display:inline-block; margin-bottom: 10px;">
            <input type="hidden" name="page" value="<?php echo esc_attr($this->menu_parameter); ?>" />
            <input type="hidden" name="cal" value="<?php echo esc_attr($this->item); ?>" />
            <input type="hidden" name="list" value="1" />
            <input type="hidden" name="anonce" value="<?php echo esc_attr($nonce); ?>" />
            
            <div class="alignleft actions ahbno-wrap-div">
                <select id="cal" name="cal" class="selectmh">
                    <?php if ($current_user_access) { ?> <option value="0">[<?php esc_html_e('All Items','appointment-hour-booking'); ?>]</option><?php } ?>
                    <?php
                    $myrows = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."cpappbk_forms" ); 
                    $saved_id = $this->item;
                    foreach ($myrows as $item) {
                        $this->setId($item->id);
                        if ($current_user_access || @in_array($current_user->ID, unserialize($this->get_option("cp_user_access",serialize(array())))))
                            echo '<option value="'.esc_attr($item->id).'"'.(intval($item->id)==intval($saved_id)?" selected":"").'>'.esc_html($item->form_name).'</option>';
                    }
                    $this->setId($saved_id);
                    ?>
                </select>
                
                <input  type="search" name="search" placeholder="<?php esc_attr_e('Search...','appointment-hour-booking'); ?>" value="<?php echo esc_attr((!empty($_GET["search"])?sanitize_text_field($_GET["search"]):'')); ?>">
                <input autocomplete="off" type="date" id="dfrom" name="dfrom" placeholder="<?php esc_attr_e('From','appointment-hour-booking'); ?>" value="<?php echo esc_attr((!empty($_GET["dfrom"])?sanitize_text_field($_GET["dfrom"]):'')); ?>" size="10">
                <input autocomplete="off" type="date" id="dto" name="dto" placeholder="<?php esc_attr_e('To','appointment-hour-booking'); ?>" value="<?php echo esc_attr((!empty($_GET["dto"])?sanitize_text_field($_GET["dto"]):'')); ?>" size="10">
                
                <input type="submit" name="ds" value="<?php esc_html_e('Filter','appointment-hour-booking'); ?>" class="button buttonnf">
                <input type="submit" name="<?php echo esc_attr($this->prefix); ?>_csv1" value="<?php esc_html_e('Export CSV','appointment-hour-booking'); ?>" class="button buttonnf">
            </div>
        </form>

        <style>
            /* Force exact standard WordPress List Table pagination styling to override plugin conflicts */
            .ahb-wp-pagination { float: right; display: flex; align-items: center; margin-top: 3px; color: #3c434a; font-size: 13px; }
            .ahb-wp-pagination .displaying-num { margin-right: 7px; }
            .ahb-wp-pagination .pagination-links { display: flex; align-items: center; }
            .ahb-wp-pagination .pagination-links .button { 
                margin: 0 2px; 
                padding: 0 4px; 
                min-width: 30px; 
                min-height: 30px; 
                line-height: 28px; 
                text-align: center; 
                display: inline-block;
                box-sizing: border-box;
                border: 1px solid #8c8f94;
                border-radius: 3px;
                background: #f6f7f7;
                color: #2271b1;
                text-decoration: none;
                font-weight: normal;
            }
            .ahb-wp-pagination .pagination-links .button.disabled { border-color: #dcdcde; color: #a7aaad; background: #f6f7f7; cursor: default; }
            .ahb-wp-pagination .pagination-links .button:hover:not(.disabled) { background: #f0f0f1; border-color: #0a4b78; color: #0a4b78; }
            .ahb-wp-pagination .paging-input { margin: 0 4px; display: inline-flex; align-items: center; }
            .ahb-wp-pagination .paging-input input { width: 40px !important; margin: 0 4px; text-align: center; border: 1px solid #8c8f94; border-radius: 3px; line-height: 2; min-height: 30px; padding: 0 4px; color: #2c3338; }
        </style>

        <div class="tablenav-pages ahb-wp-pagination<?php if($total_pages <= 1) echo ' one-page'; ?>">
            <?php if ( $total_pages > 0 ) : ?>
                <span class="displaying-num"><?php
	/* translators: %s: Number of items. */
	echo esc_html( sprintf( _n( '%s item', '%s items', $eventscount[0]->ck, 'appointment-hour-booking' ), number_format_i18n( $eventscount[0]->ck ) ) );
	?></span>
                
                <?php 
                // Build the base URL for the pagination links
                $base_url = 'admin.php?page='.esc_attr($this->menu_parameter).'&cal='.intval($this->item).'&list=1&dfrom='.urlencode(sanitize_text_field((!empty($_GET["dfrom"])?sanitize_text_field($_GET["dfrom"]):''))).'&dto='.urlencode(sanitize_text_field((!empty($_GET["dto"])?sanitize_text_field($_GET["dto"]):''))).'&search='.urlencode($this->clean_sanitize((!empty($_GET["search"])?sanitize_text_field(wp_unslash($_GET["search"])):'')));
                ?>
                <span class="pagination-links">
                    <?php if ( $current_page == 1 ) : ?>
                        <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>
                        <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>
                    <?php else : ?>
                        <a class="first-page button" href="<?php echo esc_url($base_url . '&p=1'); ?>"><span class="screen-reader-text"><?php esc_html_e( 'First page', 'appointment-hour-booking' ); ?></span><span aria-hidden="true">&laquo;</span></a>
                        <a class="prev-page button" href="<?php echo esc_url($base_url . '&p=' . ($current_page - 1)); ?>"><span class="screen-reader-text"><?php esc_html_e( 'Previous page', 'appointment-hour-booking' ); ?></span><span aria-hidden="true">&lsaquo;</span></a>
                    <?php endif; ?>

                    <span class="paging-input">
                        <label for="current-page-selector" class="screen-reader-text"><?php esc_html_e( 'Current Page', 'appointment-hour-booking' ); ?></label>
                        <input class="current-page" id="current-page-selector" type="text" name="p" value="<?php echo esc_attr( $current_page ); ?>" size="<?php echo intval(strlen( (string) $total_pages )); ?>" aria-describedby="table-paging" onkeydown="if(event.keyCode==13) { window.location.href='<?php echo esc_url_raw($base_url); ?>&p='+this.value; return false; }">
                        <span class="tablenav-paging-text"> <?php esc_html_e( 'of', 'appointment-hour-booking' ); ?> <span class="total-pages"><?php echo esc_html( number_format_i18n( $total_pages ) ); ?></span></span>
                    </span>

                    <?php if ( $current_page == $total_pages ) : ?>
                        <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>
                        <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>
                    <?php else : ?>
                        <a class="next-page button" href="<?php echo esc_url($base_url . '&p=' . ($current_page + 1)); ?>"><span class="screen-reader-text"><?php esc_html_e( 'Next page', 'appointment-hour-booking' ); ?></span><span aria-hidden="true">&rsaquo;</span></a>
                        <a class="last-page button" href="<?php echo esc_url($base_url . '&p=' . $total_pages); ?>"><span class="screen-reader-text"><?php esc_html_e( 'Last page', 'appointment-hour-booking' ); ?></span><span aria-hidden="true">&raquo;</span></a>
                    <?php endif; ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
    <div class="clear"></div>
    <div id="dex_printable_contents">
        <form name="dex_table_form" id="dex_table_form" action="admin.php" method="get">
            <input type="hidden" name="page" value="<?php echo esc_attr($this->menu_parameter); ?>" />
            <input type="hidden" name="cal" value="<?php echo intval($_GET["cal"]); ?>" />
            <input type="hidden" name="list" value="1" />
            <input type="hidden" name="delmark" value="1" />
            <input type="hidden" name="statusmark" value="" />
            <input type="hidden" name="sbmi" value="" /> 
            <input type="hidden" name="anonce" value="<?php echo esc_attr($nonce); ?>" />
            <div class="ahb-orderssection-container">
            <table class="wp-list-table widefat fixed striped ahb-orders-list">
                <thead>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column"><input type="checkbox" name="cpcontrolck" id="cpcontrolck" value="" onclick="cp_markall();"></td>
                    <th scope="col" class="manage-column column-id" ><?php esc_html_e('ID','appointment-hour-booking'); ?></th>
                    <th scope="col" class="manage-column column-date" ><?php esc_html_e('Submission Date','appointment-hour-booking'); ?></th>
                    <th scope="col" class="manage-column column-email" ><?php esc_html_e('Email','appointment-hour-booking'); ?></th>
                    <th scope="col" class="manage-column column-message" ><?php esc_html_e('Message','appointment-hour-booking'); ?></th>
                    <th scope="col" class="manage-column column-status" ><?php esc_html_e('Paid Status','appointment-hour-booking'); ?></th>
                    <th scope="col" class="manage-column column-options cpnopr"><?php esc_html_e('Actions','appointment-hour-booking'); ?></th>
                </tr>
                </thead>
                <tbody id="the-list">
                 <?php for ($i=0; $i<$records_per_page; $i++) if (isset($events[$i])) {
                          $posted_data = unserialize($events[$i]->posted_data);
                          $cancelled = 0;
                          $status = '';
                          if (!is_array($posted_data)) $posted_data = array();
                          if (!is_array($posted_data["apps"])) $posted_data["apps"] = array();			  
                          for($k=0; $k<count($posted_data["apps"]); $k++)
                              if ($posted_data["apps"][$k]["cancelled"] != '') {
                                  $cancelled++;
                                  $status = $posted_data["apps"][$k]["cancelled"];
                              }
                          if ($cancelled && $cancelled != count($posted_data["apps"])) 
                             $status = '';

                          $raw_data = $events[$i]->data;
                          $display_email = sanitize_email($events[$i]->notifyto);
                          if (empty($display_email) && preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $raw_data, $matches)) {
                              $display_email = sanitize_email($matches[0]);
                          }
                 ?>
                  <tr class="<?php if ( $cancelled && $cancelled == count( $posted_data["apps"] ) && $status != 'Attended') { echo 'cpappb_cancelled'; } ?>" valign="top">
                    <th scope="row" class="check-column"><input type="checkbox" name="c<?php echo intval($i); ?>" value="<?php echo intval($events[$i]->id); ?>" /></th>
                    <td><?php echo intval($events[$i]->id); ?></td>
                    <td><?php echo esc_html($this->format_date(substr($events[$i]->time,0,16)).gmdate(" H:i",strtotime($events[$i]->time))); ?></td>
                    <td><a href="mailto:<?php echo esc_attr(sanitize_email($events[$i]->notifyto)); ?>"><?php echo esc_html(sanitize_email($events[$i]->notifyto)); ?></a></td>
                    <td class="ahbmessage">
                      <div class="ahbmessagemw">
                        <?php
                        if ( $cancelled && $cancelled != count( $posted_data["apps"] ) ) echo '<div class="notice notice-warning inline"><p><strong>* '.esc_html(__('Contains','appointment-hour-booking')).' '.esc_html($cancelled).' '.esc_html(__('non-approved or cancelled dates','appointment-hour-booking')).'. <a href="?page='.esc_attr($this->menu_parameter).'&cal='.intval($this->item).'&schedule=1">'.esc_html(__('See details in schedule','appointment-hour-booking')).'</a>.</strong></p></div>';
                        
                        $data = str_replace("\n","<br />",str_replace('<','&lt;',$events[$i]->data));
                        foreach ($posted_data as $item => $value)
                            if (strpos($item,"_url") && $value != '') {
                                $data = str_replace ($posted_data[str_replace("_url","",$item)],'<a href="'.$value[0].'" target="_blank">'.$posted_data[str_replace("_url","",$item)].'</a><br />',$data);
                            }
                        $data = str_replace("&lt;img ","<img ", $data);
                        echo '<div class="ahb-appointment-header">'; // style="display:none"
                        $data = str_replace('<br /><br />', '</div><div>',$this->filter_allowed_tags(apply_filters( 'cpappb_booking_orders_item', $data, $posted_data ))); 

                        $appts = "";

                        for($k=0; $k<count($posted_data["apps"]); $k++) {
                            $app = $posted_data["apps"][$k];
                            $appts .=   '<div class="ahb-appointment-badge">' .
                                           '<span class="dashicons dashicons-clock"></span>' .
                                           '<span class="ahb-time">'.$this->format_date($app["date"]).' '.esc_html($app["slot"]).(isset($app["quant"]) && $app["quant"]>1?' ('.$app["quant"].')':'').'</span>' .
                                           '<span class="ahb-service">'.esc_html($app["service"]).'</span>' .
                                       '</div>';
                        }

                        
                        echo $appts.'</div><div style="display:none">'.$data; // phpcs:ignore WordPress.Security.EscapeOutput
                        echo '</div>';
                        
                        
                        ?>
                       </div>
                    </td>
                    <td align="center">
                        <?php 
                        $is_paid = (!empty($posted_data["paid"]) && $posted_data["paid"]=='1');
                        echo '<span class="dashicons dashicons-'.($is_paid ? 'money-alt" style="color: green;" title="Paid"' : 'minus" style="color: #d63638;" title="Not Paid"').'></span>'; 
                        if ($is_paid) {
                            echo '<br/><em style="font-size: 11px; color: #666;">'.esc_html($posted_data["payment_type"]).'</em>';
                        }
                        ?>
                    </td>
                    <td class="cpnopr">
                      <div style="margin-bottom: 5px;">
                          <?php $this->render_status_box('sb'.intval($events[$i]->id), $status); ?>
                      </div>
                      <div class="row-actions visible">
                          <span class="reply"><a href="javascript:void(0);"
                              data-id="<?php echo intval($events[$i]->id); ?>"
                              data-email="<?php echo esc_attr($display_email); ?>"
                              data-message="<?php echo esc_attr(trim($raw_data)); ?>"
                              onclick="cp_openReplyModal(this);"><?php esc_html_e('Reply','appointment-hour-booking'); ?></a> | </span>
                          <span class="edit"><a href="javascript:void(0);" onclick="cp_updateMessageItem(<?php echo intval($events[$i]->id); ?>,<?php echo ($is_paid ? '0' : '1'); ?>, this);"><?php esc_html_e('Toggle Payment','appointment-hour-booking'); ?></a> | </span>
                          <span class="inline"><a href="javascript:void(0);" onclick="cp_resendMessageItem(<?php echo intval($events[$i]->id); ?>, this);"><?php esc_html_e('Resend Email','appointment-hour-booking'); ?></a> | </span>
                          <span class="trash"><a href="javascript:void(0);" onclick="cp_deleteMessageItem(<?php echo intval($events[$i]->id); ?>, this);" class="submitdelete"><?php esc_html_e('Delete','appointment-hour-booking'); ?></a></span>
                      </div>                      
                    </td>
                  </tr>
                 <?php } ?>
                </tbody>
            </table>
            </div>
            
            <div class="ahbnoprint tablenav bottom" style="margin-top: 15px;">
                <div class="alignleft actions">
                    <?php $this->render_status_box('statusbox_markeditems', ''); ?>
                    <input class="button" type="button" name="pbutton" value="<?php esc_html_e('Apply Status','appointment-hour-booking'); ?>" onclick="cp_statusmarked();" />
                    <input class="button" type="button" name="pbutton" value="<?php esc_html_e('Delete Selected','appointment-hour-booking'); ?>" onclick="cp_deletemarked();" style="margin-left: 10px;" /> 
                </div>
                <div class="alignright actions">
                    <input class="button" type="button" value="<?php esc_html_e('Print List','appointment-hour-booking'); ?>" onclick="do_dexapp_print();" style="margin-right:20px;" />
                    <input class="button button-link-delete" type="button" name="pbutton" value="<?php esc_html_e('Delete ALL Bookings','appointment-hour-booking'); ?>" onclick="cp_deleteall();" />
                </div>
                <div class="clear"></div>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
<?php
    $dformatc = $this->get_option('date_format', 'mm/dd/yy');
    if ($dformatc == 'd M, y') $dformatc = 'dd/mm/yy';
?>
 var $j = jQuery.noConflict();
 $j(function() {
    // Bind AJAX onchange listener for status dropdowns
    $j("select[id^='sb']").each(function() {
        var idAttr = $j(this).attr('id');
        // Make sure we only catch item statuses (sb123) and not things like sbmi (statusbox_markeditems)
        if (/^sb\d+$/.test(idAttr)) {
            $j(this).on('change', function() {
                var id = idAttr.replace('sb', '');
                cp_UpsItem(id);
            });
        }
    });
 });
</script>

<style>
/* Modal Overlay & Box */
#cpappb-reply-modal {
    display: none;
    position: fixed;
    z-index: 99999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    overflow-y: auto;
}
.cpappb-modal-content {
    background: #fff;
    width: 90%;
    max-width: 620px;
    margin: 4% auto;
    padding: 24px 28px;
    border-radius: 8px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.25);
    box-sizing: border-box;
}
.cpappb-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #dcdcde;
    padding-bottom: 14px;
    margin-bottom: 16px;
}
.cpappb-modal-header h2 {
    margin: 0 !important;
    font-size: 18px;
    color: #1d2327;
}
.cpappb-modal-close {
    background: none;
    border: none;
    font-size: 22px;
    line-height: 1;
    color: #646970;
    cursor: pointer;
    padding: 0 4px;
}
.cpappb-modal-close:hover {
    color: #d63638;
}
.cpappb-modal-field {
    margin-bottom: 14px;
}
.cpappb-modal-field label {
    display: block;
    font-weight: 600;
    margin-bottom: 6px;
    color: #1d2327;
}
.cpappb-modal-field input[type="email"],
.cpappb-modal-field input[type="text"],
.cpappb-modal-field textarea {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #8c8f94;
    border-radius: 4px;
    box-sizing: border-box;
}
.cpappb-ai-box {
    background: #f0f6fc;
    padding: 14px 16px;
    border-left: 4px solid #2271b1;
    border-radius: 4px;
    margin-bottom: 16px;
}
.cpappb-ai-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}
.cpappb-ai-toggle-wrap {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 600;
    color: #1d2327;
    cursor: pointer;
    user-select: none;
}
.cpappb-switch {
    position: relative;
    display: inline-block;
    width: 38px;
    height: 20px;
}
.cpappb-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.cpappb-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #8c8f94;
    transition: .2s;
    border-radius: 20px;
}
.cpappb-slider:before {
    position: absolute;
    content: "";
    height: 14px;
    width: 14px;
    left: 3px;
    bottom: 3px;
    background-color: #fff;
    transition: .2s;
    border-radius: 50%;
}
.cpappb-switch input:checked + .cpappb-slider {
    background-color: #2271b1;
}
.cpappb-switch input:checked + .cpappb-slider:before {
    transform: translateX(18px);
}
.cpappb-ai-notice {
    margin: 8px 0 0;
    font-size: 12px;
    line-height: 1.5;
    color: #3c434a;
}
.cpappb-ai-controls {
    margin-top: 12px;
    padding-top: 10px;
    border-top: 1px solid #d0e3f5;
}
.cpappb-ai-row {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-top: 8px;
}
.cpappb-ai-row input[type="text"] {
    flex: 1;
    margin: 0 !important;
    padding: 8px 10px;
}
#cpappb-reply-modal .button {
    float: none !important;
    margin: 0 !important;
    padding: 8px 18px !important;
    height: auto !important;
    line-height: 1.4 !important;
    font-size: 13px !important;
    border-radius: 4px !important;
    cursor: pointer;
    white-space: nowrap;
}
.cpappb-modal-footer {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 12px;
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid #dcdcde;
    clear: both;
}
#cpappb-reply-status {
    margin-right: auto;
    font-weight: 600;
}
</style>

<!-- AI-Assisted Reply Modal -->
<div id="cpappb-reply-modal">
    <div class="cpappb-modal-content">
        <div class="cpappb-modal-header">
            <h2><?php esc_html_e('Reply to Booking','appointment-hour-booking'); ?></h2>
            <button type="button" class="cpappb-modal-close" onclick="document.getElementById('cpappb-reply-modal').style.display='none';">&times;</button>
        </div>

        <input type="hidden" id="cpappb-reply-msg-id" value="" />
        
        <div class="cpappb-modal-field">
            <label for="cpappb-reply-to"><?php esc_html_e('To:','appointment-hour-booking'); ?></label>
            <input type="email" id="cpappb-reply-to" />
        </div>

        <div class="cpappb-modal-field">
            <label for="cpappb-reply-original"><?php esc_html_e('Original Booking Details:','appointment-hour-booking'); ?></label>
            <textarea id="cpappb-reply-original" rows="4" style="background:#f6f7f7;" readonly></textarea>
        </div>

        <div class="cpappb-ai-box">
            <div class="cpappb-ai-header">
                <strong style="color:#1d2327;">✨ <?php esc_html_e('AI Reply Assistant','appointment-hour-booking'); ?></strong>
                <label class="cpappb-ai-toggle-wrap" for="cpappb-ai-toggle">
                    <span id="cpappb-ai-toggle-label"><?php echo $ai_reply_enabled ? esc_html__('On','appointment-hour-booking') : esc_html__('Off','appointment-hour-booking'); ?></span>
                    <span class="cpappb-switch">
                        <input type="checkbox" id="cpappb-ai-toggle" <?php checked( $ai_reply_enabled ); ?> onchange="cp_toggleAiAssistant(this);" />
                        <span class="cpappb-slider"></span>
                    </span>
                </label>
            </div>

            <p class="cpappb-ai-notice">
                <?php esc_html_e('Privacy Notice: When enabled, clicking "Generate with AI" sends your site name, the Original Booking Details shown above, and your instructions to the external AI service text.pollinations.ai to generate a draft reply.','appointment-hour-booking'); ?>
            </p>

            <div id="cpappb-ai-controls" class="cpappb-ai-controls" style="<?php echo $ai_reply_enabled ? '' : 'display:none;'; ?>">
                <label for="cpappb-ai-instructions" style="font-weight:600; color:#1d2327; display:block;"><?php esc_html_e('AI Assistant Instructions (optional):','appointment-hour-booking'); ?></label>
                <div class="cpappb-ai-row">
                    <input type="text" id="cpappb-ai-instructions" placeholder="<?php esc_attr_e('e.g., Confirm the booking for Tuesday at 10 AM and thank them','appointment-hour-booking'); ?>" <?php disabled( ! $ai_reply_enabled ); ?> />
                    <button type="button" class="button" id="cpappb-btn-ai" onclick="cp_generateAiReply();" <?php disabled( ! $ai_reply_enabled ); ?>>✨ <?php esc_html_e('Generate with AI','appointment-hour-booking'); ?></button>
                </div>
            </div>
        </div>

        <div class="cpappb-modal-field">
            <label for="cpappb-reply-subject"><?php esc_html_e('Subject:','appointment-hour-booking'); ?></label>
            <input type="text" id="cpappb-reply-subject" value="Re: <?php echo esc_attr($this->get_option('fp_subject', (defined('CP_APPBOOK_DEFAULT_fp_subject') ? CP_APPBOOK_DEFAULT_fp_subject : 'Booking'))); ?>" />
        </div>

        <div class="cpappb-modal-field">
            <label for="cpappb-reply-body"><?php esc_html_e('Your Reply:','appointment-hour-booking'); ?></label>
            <textarea id="cpappb-reply-body" rows="6"></textarea>
        </div>

        <div class="cpappb-modal-footer">
            <span id="cpappb-reply-status"></span>
            <button type="button" class="button" onclick="document.getElementById('cpappb-reply-modal').style.display='none';"><?php esc_html_e('Cancel','appointment-hour-booking'); ?></button>
            <button type="button" class="button button-primary" id="cpappb-btn-send" onclick="cp_sendReplyEmail();"><?php esc_html_e('Send Reply','appointment-hour-booking'); ?></button>
        </div>
    </div>
</div>

<script type="text/javascript">
function cp_openReplyModal(link) {
    document.getElementById('cpappb-reply-msg-id').value = link.getAttribute('data-id');
    document.getElementById('cpappb-reply-to').value = link.getAttribute('data-email');
    document.getElementById('cpappb-reply-original').value = link.getAttribute('data-message');
    document.getElementById('cpappb-reply-body').value = '';
    document.getElementById('cpappb-reply-status').innerText = '';
    document.getElementById('cpappb-reply-modal').style.display = 'block';
}

function cp_toggleAiAssistant(checkbox) {
    var enabled = checkbox.checked;
    var controls = document.getElementById('cpappb-ai-controls');
    var label = document.getElementById('cpappb-ai-toggle-label');
    var input = document.getElementById('cpappb-ai-instructions');
    var btn = document.getElementById('cpappb-btn-ai');
    var status = document.getElementById('cpappb-reply-status');

    controls.style.display = enabled ? 'block' : 'none';
    input.disabled = !enabled;
    btn.disabled = !enabled;
    label.innerText = enabled
        ? '<?php echo esc_js(__('On','appointment-hour-booking')); ?>'
        : '<?php echo esc_js(__('Off','appointment-hour-booking')); ?>';

    $j.post(ajaxurl, {
        action: 'cpappb_toggle_ai_reply',
        nonce: '<?php echo esc_js($reply_nonce); ?>',
        cal_id: '<?php echo intval($this->item); ?>',
        enabled: enabled ? 1 : 0
    }, function(response) {
        if (!response || !response.success) {
            checkbox.checked = !enabled;
            controls.style.display = !enabled ? 'block' : 'none';
            input.disabled = enabled;
            btn.disabled = enabled;
            label.innerText = !enabled
                ? '<?php echo esc_js(__('On','appointment-hour-booking')); ?>'
                : '<?php echo esc_js(__('Off','appointment-hour-booking')); ?>';
            status.style.color = '#d63638';
            status.innerText = (response && response.data) ? response.data : '<?php echo esc_js(__('Could not save setting.','appointment-hour-booking')); ?>';
        }
    }).fail(function() {
        checkbox.checked = !enabled;
        controls.style.display = !enabled ? 'block' : 'none';
        input.disabled = enabled;
        btn.disabled = enabled;
        label.innerText = !enabled
            ? '<?php echo esc_js(__('On','appointment-hour-booking')); ?>'
            : '<?php echo esc_js(__('Off','appointment-hour-booking')); ?>';
        status.style.color = '#d63638';
        status.innerText = '<?php echo esc_js(__('Request failed while saving AI preference.','appointment-hour-booking')); ?>';
    });
}

function cp_generateAiReply() {
    var toggle = document.getElementById('cpappb-ai-toggle');
    var status = document.getElementById('cpappb-reply-status');

    if (!toggle || !toggle.checked) {
        status.style.color = '#d63638';
        status.innerText = '<?php echo esc_js(__('Please enable the AI Reply Assistant first.','appointment-hour-booking')); ?>';
        return;
    }

    var original = document.getElementById('cpappb-reply-original').value;
    var instructions = document.getElementById('cpappb-ai-instructions').value;
    var btn = document.getElementById('cpappb-btn-ai');

    btn.disabled = true;
    btn.innerText = '<?php echo esc_js(__('Generating...','appointment-hour-booking')); ?>';
    status.style.color = '#2271b1';
    status.innerText = '<?php echo esc_js(__('Contacting AI...','appointment-hour-booking')); ?>';

    $j.post(ajaxurl, {
        action: 'cpappb_generate_ai_reply',
        nonce: '<?php echo esc_js($reply_nonce); ?>',
        cal_id: '<?php echo intval($this->item); ?>',
        original_message: original,
        instructions: instructions
    }, function(response) {
        btn.disabled = false;
        btn.innerText = '✨ <?php echo esc_js(__('Generate with AI','appointment-hour-booking')); ?>';
        if (response.success) {
            document.getElementById('cpappb-reply-body').value = response.data.reply;
            status.style.color = '#00a32a';
            status.innerText = '<?php echo esc_js(__('Draft generated!','appointment-hour-booking')); ?>';
        } else {
            status.style.color = '#d63638';
            status.innerText = response.data || '<?php echo esc_js(__('Failed to generate reply.','appointment-hour-booking')); ?>';
        }
    }).fail(function() {
        btn.disabled = false;
        btn.innerText = '✨ <?php echo esc_js(__('Generate with AI','appointment-hour-booking')); ?>';
        status.style.color = '#d63638';
        status.innerText = '<?php echo esc_js(__('Request failed.','appointment-hour-booking')); ?>';
    });
}

function cp_sendReplyEmail() {
    var btn = document.getElementById('cpappb-btn-send');
    var status = document.getElementById('cpappb-reply-status');

    btn.disabled = true;
    status.style.color = '#2271b1';
    status.innerText = '<?php echo esc_js(__('Sending...','appointment-hour-booking')); ?>';

    $j.post(ajaxurl, {
        action: 'cpappb_send_reply',
        nonce: '<?php echo esc_js($reply_nonce); ?>',
        message_id: document.getElementById('cpappb-reply-msg-id').value,
        to_email: document.getElementById('cpappb-reply-to').value,
        subject: document.getElementById('cpappb-reply-subject').value,
        reply_body: document.getElementById('cpappb-reply-body').value
    }, function(response) {
        btn.disabled = false;
        if (response.success) {
            status.style.color = '#00a32a';
            status.innerText = response.data;
            setTimeout(function() {
                location.reload();
            }, 1000);
        } else {
            status.style.color = '#d63638';
            status.innerText = response.data || '<?php echo esc_js(__('Error sending email.','appointment-hour-booking')); ?>';
        }
    }).fail(function() {
        btn.disabled = false;
        status.style.color = '#d63638';
        status.innerText = '<?php echo esc_js(__('Server error while sending.','appointment-hour-booking')); ?>';
    });
}
</script>