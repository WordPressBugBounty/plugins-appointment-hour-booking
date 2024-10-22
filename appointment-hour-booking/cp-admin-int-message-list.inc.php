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
    if (isset($_GET['c'.$i]) && $_GET['c'.$i] != '')
        $wpdb->query( $wpdb->prepare('DELETE FROM `'.$wpdb->prefix.$this->table_messages.'` WHERE id=%d', sanitize_text_field($_GET['c'.$i])) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $message = __('Marked items deleted','appointment-hour-booking');
}
else if (isset($_GET['del']) && $_GET['del'] == 'all')
{
    $this->verify_nonce (sanitize_text_field($_GET["anonce"]), 'cpappb_actions_booking');
    if ($this->item == '' || $this->item == '0')
        $wpdb->query('DELETE FROM `'.$wpdb->prefix.$this->table_messages.'`'); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    else
        $wpdb->query($wpdb->prepare('DELETE FROM `'.$wpdb->prefix.$this->table_messages.'` WHERE formid=%d', $this->item)); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $message = __('All items deleted','appointment-hour-booking');
}
else if (isset($_GET['lu']) && $_GET['lu'] != '')
{
    $this->verify_nonce (sanitize_text_field($_GET["anonce"]), 'cpappb_actions_booking');
    $myrows = $wpdb->get_results( $wpdb->prepare("SELECT * FROM ".$wpdb->prefix.$this->table_messages." WHERE id=%d", sanitize_text_field($_GET['lu'])) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $params = unserialize($myrows[0]->posted_data);
    $params["paid"] = sanitize_text_field($_GET["status"]);
    $params["payment_type"] = __('Manually updated','appointment-hour-booking');
    $wpdb->query( $wpdb->prepare('UPDATE `'.$wpdb->prefix.$this->table_messages.'` SET posted_data=%s WHERE id=%d', serialize($params), sanitize_text_field($_GET['lu'])) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $message = __('Item updated','appointment-hour-booking');
}
else if (isset($_GET['ld']) && $_GET['ld'] != '')
{
    $this->verify_nonce (sanitize_text_field($_GET["anonce"]), 'cpappb_actions_booking');
    $wpdb->query( $wpdb->prepare('DELETE FROM `'.$wpdb->prefix.$this->table_messages.'` WHERE id=%d', sanitize_text_field($_GET['ld'])) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
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
    $myform = $wpdb->get_results( $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.$this->table_items .' WHERE id=%d' ,$this->item) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

$rawfrom = (isset($_GET["dfrom"]) ? sanitize_text_field($_GET["dfrom"]) : '');
$rawto = (isset($_GET["dto"]) ? sanitize_text_field(@$_GET["dto"]) : '');
if ($this->get_option('date_format', 'mm/dd/yy') == 'dd/mm/yy')
{
    $rawfrom = str_replace('/','.',$rawfrom);
    $rawto = str_replace('/','.',$rawto);
}

$cond = '';
if (!empty($_GET["search"])) $cond .= " AND (data like '%".esc_sql(sanitize_text_field($_GET["search"]))."%' OR posted_data LIKE '%".esc_sql(sanitize_text_field($_GET["search"]))."%')";
if ($rawfrom != '') $cond .= " AND (`time` >= '".esc_sql( date("Y-m-d",strtotime($rawfrom)))."')";
if ($rawto != '') $cond .= " AND (`time` <= '".esc_sql(date("Y-m-d",strtotime($rawto)))." 23:59:59')";
if ($this->item != 0) $cond .= " AND formid=".intval($this->item);


$events_query = "SELECT count(id) as ck FROM ".$wpdb->prefix.$this->table_messages." WHERE 1=1 ".$cond." ORDER BY `time` DESC";
$events = $wpdb->get_results( $events_query );
$total_pages = ceil($events[0]->ck / $records_per_page);

$events_query = "SELECT * FROM ".$wpdb->prefix.$this->table_messages." WHERE 1=1 ".$cond." ORDER BY `time` DESC LIMIT ".intval(($current_page-1)*$records_per_page).",".intval($records_per_page);
/**
 * Allows modify the query of messages, passing the query as parameter
 * returns the new query
 */
$events_query = apply_filters( 'cpappb_messages_query', $events_query );
$events = $wpdb->get_results( $events_query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared


if ($message) echo "<div id='setting-error-settings_updated' class='updated'><h2>".esc_html($message)."</h2></div>";

$nonce = wp_create_nonce( 'cpappb_actions_booking' );

?>
<script type="text/javascript">
 function cp_UpsItem(id)
 {
     var status = document.getElementById("sb"+id).options[document.getElementById("sb"+id).selectedIndex].value;
     document.location = 'admin.php?page=<?php echo esc_js($this->menu_parameter); ?>&anonce=<?php echo esc_js($nonce); ?>&cal=<?php echo intval($_GET["cal"]); ?>&list=1&ud='+id+'&status='+status+'&r='+Math.random();
 }
 function cp_updateMessageItem(id,status)
 {
    document.location = 'admin.php?page=<?php echo esc_js($this->menu_parameter); ?>&anonce=<?php echo esc_js($nonce); ?>&cal=<?php echo intval($_GET["cal"]); ?>&list=1&status='+status+'&lu='+id+'&r='+Math.random( );
 }
 function cp_resendMessageItem(id)
 {
	if (confirm('Are you sure that you want to resend the notification emails for this item?'))
    {
        document.location = 'admin.php?page=<?php echo esc_js($this->menu_parameter); ?>&anonce=<?php echo esc_js($nonce); ?>&cal=<?php echo intval($_GET["cal"]); ?>&list=1&resend='+id+'&r='+Math.random( );
	}
 } 
 function cp_deleteMessageItem(id)
 {
    if (confirm('Are you sure that you want to delete this item?'))
    {
        document.location = 'admin.php?page=<?php echo esc_js($this->menu_parameter); ?>&anonce=<?php echo esc_js($nonce); ?>&cal=<?php echo intval($_GET["cal"]); ?>&list=1&ld='+id+'&r='+Math.random();
    }
 }
 function cp_deletemarked()
 {
    if (confirm('Are you sure that you want to delete the marked items?'))
        document.dex_table_form.submit();
 }
 function cp_statusmarked()
 {
    if (confirm('Are you sure that you want to change the status of the marked items?')) 
    {                
        document.dex_table_form.delmark.value = '';
        document.dex_table_form.statusmark.value = '1';
        var status = document.getElementById("statusbox_markeditems").options[document.getElementById("statusbox_markeditems").selectedIndex].value;
        document.dex_table_form.sbmi.value = status;        
        document.dex_table_form.submit();
    }
 }  
 function cp_deleteall()
 {
    if (confirm('Are you sure that you want to delete ALL bookings for this form?'))
    {
        if (confirm('Please note that this action cannot be undone. ALL THE BOOKINGS of this form will be DELETED. Are you sure that you want to delete ALL bookings for this form?'))
            document.location = 'admin.php?page=<?php echo esc_js($this->menu_parameter); ?>&cal=<?php echo intval($_GET["cal"]); ?>&list=1&del=all&anonce=<?php echo esc_js($nonce); ?>&r='+Math.random();
    }
 }
 function cp_markall()
 {
     var ischecked = document.getElementById("cpcontrolck").checked;
     <?php for ($i=0; $i<$records_per_page; $i++) if (isset($events[$i])) { ?>
     document.forms.dex_table_form.c<?php echo intval($i); ?>.checked = ischecked;
     <?php } ?>
 }
</script>

<h1><?php esc_html_e('Booking Orders','appointment-hour-booking'); ?> - <?php if ($this->item != 0) echo esc_html($myform[0]->form_name); else echo 'All forms'; ?></h1>

<div class="ahb-buttons-container">
	<a href="<?php print esc_attr(admin_url('admin.php?page='.$this->menu_parameter));?>" class="ahb-return-link">&larr;<?php esc_html_e('Return to the calendars list','appointment-hour-booking'); ?></a>
	<div class="clear"></div>
</div>

<div class="ahb-section-container">
	<div class="ahb-section">
      <form action="admin.php" method="get">
        <input type="hidden" name="page" value="<?php echo esc_attr($this->menu_parameter); ?>" />
        <input type="hidden" name="cal" value="<?php echo esc_attr($this->item); ?>" />
        <input type="hidden" name="list" value="1" />
        <input type="hidden" name="anonce" value="<?php echo esc_attr($nonce); ?>" />
		<nobr><label><?php esc_html_e('Search for','appointment-hour-booking'); ?>:</label> <input type="text" name="search" value="<?php echo esc_attr((!empty($_GET["search"])?sanitize_text_field($_GET["search"]):'')); ?>">&nbsp;&nbsp;</nobr>
		<nobr><label><?php esc_html_e('From','appointment-hour-booking'); ?>:</label> <input autocomplete="off" type="text" id="dfrom" name="dfrom" value="<?php echo esc_attr((!empty($_GET["dfrom"])?sanitize_text_field($_GET["dfrom"]):'')); ?>" >&nbsp;&nbsp;</nobr>
		<nobr><label><?php esc_html_e('To','appointment-hour-booking'); ?>:</label> <input autocomplete="off" type="text" id="dto" name="dto" value="<?php echo esc_attr((!empty($_GET["dto"])?sanitize_text_field($_GET["dto"]):'')); ?>" >&nbsp;&nbsp;</nobr>
		<nobr><label><?php esc_html_e('Item','appointment-hour-booking'); ?>:</label> <select id="cal" name="cal">
          <?php if ($current_user_access) { ?> <option value="0">[<?php esc_html_e('All Items','appointment-hour-booking'); ?>]</option><?php } ?>
   <?php
    $myrows = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix.$this->table_items ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $saved_id = $this->item;
    foreach ($myrows as $item)
    {
        $this->setId($item->id);
        if ($current_user_access || @in_array($current_user->ID, unserialize($this->get_option("cp_user_access",serialize(array())))))
            echo '<option value="'.esc_attr($item->id).'"'.(intval($item->id)==intval($saved_id)?" selected":"").'>'.esc_html($item->form_name).'</option>';
    }
    $this->setId($saved_id);
   ?>
    </select></nobr>
       <div style="float:right;margin-top:3px;">
		<nobr>
            <input type="submit" name="ds" value="<?php esc_html_e('Filter','appointment-hour-booking'); ?>" class="button-primary button" style="">
			<input type="submit" name="<?php echo esc_attr($this->prefix); ?>_csv1" value="<?php esc_html_e('Export to CSV','appointment-hour-booking'); ?>" class="button" style="margin-left:10px;">
		</nobr>
       </div>
      </form>
      <div style="clear:both"></div>
	</div>
</div>


<?php


echo paginate_links(  array(        // phpcs:ignore WordPress.Security.EscapeOutput
    'base'         => 'admin.php?page='.esc_attr($this->menu_parameter).'&cal='.intval($this->item).'&list=1%_%&dfrom='.urlencode(sanitize_text_field((!empty($_GET["dfrom"])?sanitize_text_field($_GET["dfrom"]):''))).'&dto='.urlencode(sanitize_text_field((!empty($_GET["dto"])?sanitize_text_field($_GET["dto"]):''))).'&search='.urlencode($this->clean_sanitize((!empty($_GET["search"])?sanitize_text_field($_GET["search"]):''))),
    'format'       => '&p=%#%',
    'total'        => intval($total_pages),
    'current'      => intval($current_page),
    'show_all'     => False,
    'end_size'     => 1,
    'mid_size'     => 2,
    'prev_next'    => True,
    'prev_text'    => esc_html(__('&laquo; Previous', 'appointment-hour-booking')),
    'next_text'    => esc_html(__('Next &raquo;', 'appointment-hour-booking')),
    'type'         => 'plain',
    'add_args'     => False
    ) );

?>

<div id="dex_printable_contents">
<form name="dex_table_form" id="dex_table_form" action="admin.php" method="get">
 <input type="hidden" name="page" value="<?php echo esc_attr($this->menu_parameter); ?>" />
 <input type="hidden" name="cal" value="<?php echo intval($_GET["cal"]); ?>" />
 <input type="hidden" name="list" value="1" />
 <input type="hidden" name="delmark" value="1" />
 <input type="hidden" name="statusmark" value="" />
 <input type="hidden" name="sbmi" value="" /> 
 <input type="hidden" name="anonce" value="<?php echo esc_attr($nonce); ?>" />
<div class="ahb-orderssection-container" style="background:#f6f6f6;padding-bottom:20px;">
<table border="0" style="width:100%;" class="ahb-orders-list" cellpadding="10" cellspacing="10">
	<thead>
	<tr>
      <th width="10"><input type="checkbox" name="cpcontrolck" id="cpcontrolck" value="" onclick="cp_markall();"></th>
      <th width="30"><?php esc_html_e('ID','appointment-hour-booking'); ?></th>
	  <th style="text-align:left" width="130"><?php esc_html_e('Submission Date','appointment-hour-booking'); ?></th>
	  <th style="text-align:left"><?php esc_html_e('Email','appointment-hour-booking'); ?></th>
	  <th style="text-align:left"><?php esc_html_e('Message','appointment-hour-booking'); ?></th>
      <th width="130"><?php esc_html_e('Paid Status','appointment-hour-booking'); ?></th>
	  <th  class="cpnopr"><?php esc_html_e('Options','appointment-hour-booking'); ?></th>
	</tr>
	</thead>
	<tbody id="the-list">
	 <?php for ($i=0; $i<$records_per_page; $i++) if (isset($events[$i])) {
              $posted_data = unserialize($events[$i]->posted_data);
              $cancelled = 0;
              $status = '';
              if (!is_array($posted_data))
                  $posted_data = array();
              if (!is_array($posted_data["apps"]))
                  $posted_data["apps"] = array();			  
              for($k=0; $k<count($posted_data["apps"]); $k++)
                  if ($posted_data["apps"][$k]["cancelled"] != '')
                  {
                      $cancelled++;
                      $status = $posted_data["apps"][$k]["cancelled"];
                  }
              if ($cancelled && $cancelled != count($posted_data["apps"])) 
                 $status = '';                    
     ?>
	  <tr class='<?php if ( $cancelled && $cancelled == count( $posted_data["apps"] ) && $status != 'Attended') { ?>cpappb_cancelled <?php } ?><?php if (($i%2)) { ?>alternate <?php } ?>author-self status-draft format-default iedit' valign="top">
        <th><input type="checkbox" name="c<?php echo intval($i); ?>" value="<?php echo intval($events[$i]->id); ?>" /></th>
        <th><?php echo intval($events[$i]->id); ?></th>
		<td><?php echo esc_html($this->format_date(substr($events[$i]->time,0,16)).date(" H:i",strtotime($events[$i]->time))); ?></td>
		<td><?php echo esc_html(sanitize_email($events[$i]->notifyto)); ?></td>
		<td><?php
            if ( $cancelled && $cancelled != count( $posted_data["apps"] ) ) echo '<div style="color:#ff0000;font-weight:bold;">* '.esc_html(__('Contains','appointment-hour-booking')).' '.esc_html($cancelled).' '.esc_html(__('non-approved or cancelled dates','appointment-hour-booking')).'. <a href="?page='.esc_attr($this->menu_parameter).'&cal='.intval($this->item).'&schedule=1">'.esc_html(__('See details in schedule','appointment-hour-booking')).'</a>.</div>';
          ?><?php
		        $data = str_replace("\n","<br />",str_replace('<','&lt;',$events[$i]->data));
		        foreach ($posted_data as $item => $value)
		            if (strpos($item,"_url") && $value != '')
		            {
		                $data = str_replace ($posted_data[str_replace("_url","",$item)],'<a href="'.$value[0].'" target="_blank">'.$posted_data[str_replace("_url","",$item)].'</a><br />',$data);
		            }
		        $data = str_replace("&lt;img ","<img ", $data);

                echo $this->filter_allowed_tags(apply_filters( 'cpappb_booking_orders_item', $data, $posted_data )); // phpcs:ignore WordPress.Security.EscapeOutput
		    ?></td>
        <td align="center"><?php echo '<span style="color:#006799;font-weight:bold;">'.(!empty($posted_data["paid"]) && $posted_data["paid"]=='1'?esc_html(__('Paid','appointment-hour-booking')).'</span><br /><em class="cpappsoft">'.esc_html($posted_data["payment_type"]):'').'</em>'; ?></td>
		<td class="cpnopr" style="text-align:center;">
          <input class="button ahbsbutton" type="button" name="caldelete_<?php echo intval($events[$i]->id); ?>" value="<?php esc_html_e('Toggle Payment','appointment-hour-booking'); ?>" onclick="cp_updateMessageItem(<?php echo intval($events[$i]->id); ?>,<?php echo (!empty($posted_data["paid"]) && $posted_data["paid"]?'0':'1'); ?>);" />
		  <input class="button ahbsbutton" type="button" name="calresend_<?php echo intval($events[$i]->id); ?>" value="<?php esc_html_e('Resend Emails','appointment-hour-booking'); ?>" onclick="cp_resendMessageItem(<?php echo intval($events[$i]->id); ?>);" />
		  <input class="button ahbsbutton" type="button" name="caldelete_<?php echo intval($events[$i]->id); ?>" value="<?php esc_html_e('Delete','appointment-hour-booking'); ?>" onclick="cp_deleteMessageItem(<?php echo intval($events[$i]->id); ?>);" />
          <hr />
          <nobr><?php $this->render_status_box('sb'.intval($events[$i]->id), $status); ?><input class="button" type="button" name="calups_<?php echo intval($events[$i]->id); ?>" value="<?php esc_html_e('Update Status','appointment-hour-booking'); ?>" onclick="cp_UpsItem(<?php echo intval($events[$i]->id); ?>);" /></nobr>
		</td>
      </tr>
     <?php } ?>
	</tbody>
</table>
</div>
</form>
</div>

<div class="ahb-buttons-container">
    <input type="button" value="Print" class="button button-primary" onclick="do_dexapp_print();" />
	<a href="<?php print esc_attr(admin_url('admin.php?page='.$this->menu_parameter));?>" class="ahb-return-link">&larr;<?php esc_html_e('Return to the calendars list','appointment-hour-booking'); ?></a>
	<div class="clear"></div>
</div>

<div style="clear:both"></div>

<div class="ahb-section-container"  style="background-color:#ffffee;">   
	<div class="ahb-section">
      <?php $this->render_status_box('statusbox_markeditems', ''); ?>
      <input style="float:none" class="button" type="button" name="pbutton" value="<?php esc_html_e('Change status of marked items','appointment-hour-booking'); ?>" onclick="cp_statusmarked();" />
    </div>  
</div>    
<div style="clear:both"></div>

<div style="clear:both"></div>
<div class="ahb-section-container" style="background-color:#ffcccc;">   
	<div class="ahb-section">
      <input style="margin-right:40px;" class="button" type="button" name="pbutton" value="<?php esc_html_e('Delete marked items','appointment-hour-booking'); ?>" onclick="cp_deletemarked();" /> 
      <input class="button" type="button" name="pbutton" value="<?php esc_html_e('Delete All Bookings','appointment-hour-booking'); ?>" onclick="cp_deleteall();" />
      <div style="clear:both"></div>
    </div>  
</div>   


<script type="text/javascript">
 function do_dexapp_print()
 {
      w=window.open();
      w.document.write("<style>.cpnopr{display:none;};table{border:2px solid black;width:100%;}th{border-bottom:2px solid black;text-align:left}td{padding-left:10px;border-bottom:1px solid black;}</style>"+document.getElementById('dex_printable_contents').innerHTML);
      w.print();
      w.close();
 }

<?php

	 $dformatc = $this->get_option('date_format', 'mm/dd/yy');
	 if ($dformatc == 'd M, y') $dformatc = 'dd/mm/yy';

?>
 var $j = jQuery.noConflict();
 $j(function() {
 	$j("#dfrom").datepicker({
                    dateFormat: '<?php echo esc_js($dformatc); ?>'
                 });
 	$j("#dto").datepicker({
                    dateFormat: '<?php echo esc_js($dformatc); ?>'
                 });
 });

</script>

