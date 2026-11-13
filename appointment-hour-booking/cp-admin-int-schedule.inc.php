<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$this->item = intval($_GET["cal"]);

$current_user = wp_get_current_user();
$current_user_access = current_user_can('manage_options');

if ( !is_admin() || (!$current_user_access && !@in_array($current_user->ID, unserialize($this->get_option("cp_user_access","")))))
{
    echo 'Direct access not allowed.';
    exit;
}

$firstday = 0;
if ($this->item != 0)
{
    $myform = $wpdb->get_results( $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'cpappbk_forms WHERE id=%d' ,$this->item) ); 
    $raw_form_str = $this->cleanJSON( $myform[0]->form_structure );
    $form_data = json_decode( $raw_form_str );
    foreach($form_data[0] as $item)
    {
        if ($item->ftype == 'fapp')
        {
            $firstday = $item->firstDay;
            break;
        }
    }
}

$default_from = gmdate("Y-m-d",strtotime("today -10 days"));
$default_to = gmdate("Y-m-d",strtotime("today +30 days"));

$rawfrom = (isset($_GET["dfrom"]) ? sanitize_text_field($_GET["dfrom"]) : '');
$rawto = (isset($_GET["dto"]) ? sanitize_text_field(@$_GET["dto"]) : '');

$rawfrom = str_replace(',',' ',$rawfrom);
$rawto = str_replace(',',' ',$rawto);

if ($this->get_option('date_format', 'mm/dd/yy') == 'dd/mm/yy' || $this->get_option('date_format', 'mm/dd/yy') == 'd M, y')
{
    $rawfrom = str_replace('/','.',$rawfrom);
    $rawto = str_replace('/','.',$rawto);
}

$dfrom = ($rawfrom != '' ? gmdate("Y-m-d", strtotime($rawfrom)) : $default_from);
$dto = ($rawto != '' ? gmdate("Y-m-d", strtotime($rawto)) : $default_to);

if ($this->get_option('date_format', 'mm/dd/yy') == 'd M, y')
{
	$dfrom_formatted = gmdate("d/m/Y", strtotime($dfrom));
    $dto_formatted = gmdate("d/m/Y", strtotime($dto));
}
else
{
    $dfrom_formatted = $this->format_date($dfrom);
    $dto_formatted = $this->format_date($dto);
}

?>


<h1><?php esc_html_e('Schedule','appointment-hour-booking'); ?> - <?php if ($this->item != 0) echo esc_html($myform[0]->form_name); else echo 'All forms'; ?></h1>

<div class="ahb-buttons-container">

    <?php if (!isset($_GET["calendarview"])) { ?>
     <input type="button" value="<?php esc_html_e('Change to Schedule Calendar View','appointment-hour-booking'); ?>" class="button button-primary" onclick="document.location='?page=<?php echo esc_attr($this->menu_parameter); ?>&cal=<?php echo intval($this->item); ?>&schedule=1&calendarview=1';" />
    <?php } else { ?>
     <input type="button" value="<?php esc_html_e('Change to Schedule List View','appointment-hour-booking'); ?>" class="button button-primary" onclick="document.location='?page=<?php echo esc_attr($this->menu_parameter); ?>&cal=<?php echo intval($this->item); ?>&schedule=1';" />
	 <input type="button" value="<?php esc_html_e('Customize Contents of this Calendar','appointment-hour-booking'); ?>" class="button" style="margin-left:20px;" onclick="document.location='?page=<?php echo esc_attr($this->menu_parameter); ?>_settings&gotab=schedulecalarea';" />
    <?php } ?>
	<a href="<?php print esc_attr(admin_url('admin.php?page='.$this->menu_parameter));?>" class="ahb-return-link">&larr;<?php esc_html_e('Return to the calendars list','appointment-hour-booking'); ?></a>
	<div class="clear"></div>
</div>

<?php if (!isset($_GET["calendarview"])) { ?>

<div class="ahb-section-container">
	<div class="ahb-section">
      <form action="admin.php" method="get">
        <input type="hidden" name="page" value="<?php echo esc_attr($this->menu_parameter); ?>" />
        <input type="hidden" name="cal" value="<?php echo intval($this->item); ?>" />
        <input type="hidden" name="schedule" value="1" />
		<nobr><label><?php esc_html_e('From','appointment-hour-booking'); ?>:</label> <input autocomplete="off" type="text" id="dfrom" name="dfrom" value="<?php echo esc_attr($dfrom_formatted); ?>" >&nbsp;&nbsp;</nobr>
		<nobr><label><?php esc_html_e('To','appointment-hour-booking'); ?>:</label> <input autocomplete="off" type="text" id="dto" name="dto" value="<?php echo esc_attr($dto_formatted); ?>" >&nbsp;&nbsp;</nobr>
        <nobr><?php esc_html_e('Paid Status','appointment-hour-booking'); ?>: <select id="paid" name="paid">
         <option value=""><?php esc_html_e('All','appointment-hour-booking'); ?></option>
         <option value="1" <?php if (!empty($_GET["paid"])) echo ' selected'; ?>><?php esc_html_e('Paid only','appointment-hour-booking'); ?></option>
      </select></nobr>
        <nobr><?php esc_html_e('Booking Status','appointment-hour-booking'); ?>: <?php $this->render_status_box('status', (!isset($_GET["status"])?'all':sanitize_text_field($_GET["status"])), true); ?></nobr>
		<nobr><label><?php esc_html_e('Item','appointment-hour-booking'); ?>:</label> <select id="cal" name="cal">
          <?php if ($current_user_access) { ?> <option value="0">[<?php esc_html_e('All Items','appointment-hour-booking'); ?>]</option><?php } ?>
   <?php
    $myrows = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."cpappbk_forms" );  
    $saved_id = $this->item;
    foreach ($myrows as $item)
    {
        $this->setId($item->id);
        if ($current_user_access || @in_array($current_user->ID, unserialize($this->get_option("cp_user_access",""))))
           echo '<option value="'.intval($item->id).'"'.(intval($item->id)==intval($saved_id)?" selected":"").'>'.esc_html($item->form_name).'</option>';
    }
    $this->setId($saved_id);
   ?>
    </select></nobr>
       <div style="float:right;margin-top:3px;">
		<nobr>
            <input type="submit" name="ds" value="<?php esc_html_e('Filter','appointment-hour-booking'); ?>" class="button-primary button" style="">
			<input type="submit" name="<?php echo esc_attr($this->prefix); ?>_csv2" value="<?php esc_html_e('Export to CSV','appointment-hour-booking'); ?>" class="button" style="margin-left:10px;">
		</nobr>
       </div>
      </form>
      <div style="clear:both"></div>
	</div>
</div>


<br />

<div id="dex_printable_contents">

 <div class="cpapp_no_wrap">
  <div class="cpappb_field_0 cpappb_field_header"><?php esc_html_e('Date','appointment-hour-booking'); ?></div>
  <div class="cpappb_field_1 cpappb_field_header"><?php esc_html_e('Slot','appointment-hour-booking'); ?></div>
  <div class="cpappb_field_2 cpappb_field_header"><?php esc_html_e('Service','appointment-hour-booking'); ?></div>
  <div class="cpappb_field_3 cpappb_field_header"><?php esc_html_e('Qty','appointment-hour-booking'); ?></div>
  <div class="cpappb_field_4 cpappb_field_header"><?php esc_html_e('Paid','appointment-hour-booking'); ?></div>
  <div class="cpappb_field_5 cpappb_field_header"><?php esc_html_e('Email','appointment-hour-booking'); ?></div>
  <div class="cpappb_field_6 cpappb_field_header"><?php esc_html_e('Data','appointment-hour-booking'); ?></div>
  <div class="cpappb_field_7 cpappb_field_header"><?php esc_html_e('Status','appointment-hour-booking'); ?></div>
  <div class="cpappb_field_8 cpappb_field_header"><?php esc_html_e('Options','appointment-hour-booking'); ?></div>
  <div class="cpapp_break"></div>
 </div>
 <div class="cpapp_break"></div>
<?php

echo $this->filter_list( array(   // phpcs:ignore WordPress.Security.EscapeOutput
                               'calendar' => ($this->item != 0 ? intval($this->item) : ''),
                               'fields' => 'DATE,TIME,SERVICE,quantity,paid,email,data,cancelled,options',
	    	                   'from' => esc_html($dfrom),
	    	                   'to' => esc_html($dto),
                               'paidonly' => esc_html( (!empty($_GET["paid"])?sanitize_text_field($_GET["paid"]):'')),
                               'status' => (!isset($_GET["status"])?'all':esc_html(sanitize_text_field($_GET["status"])))
                               ) );

?>
</div>


<div class="ahb-buttons-container">
    <input type="button" value="<?php esc_html_e('Print','appointment-hour-booking'); ?>" class="button button-primary" onclick="do_dexapp_print();" />
	<a href="<?php print esc_attr(admin_url('admin.php?page='.$this->menu_parameter));?>" class="ahb-return-link">&larr;<?php esc_html_e('Return to the calendars list','appointment-hour-booking'); ?></a>
	<div class="clear"></div>
</div>


<?php } else { ?>

<div id="cpabc_printable_contents">

<p>
    <?php 
    echo wp_kses( 
        __( 'The purpose of this page is to <strong>display the bookings/schedule in a calendar view</strong>. You can add bookings from the public booking form or', 'appointment-hour-booking' ),
        array(
            'strong' => array(),
        )
    ); 
    ?> 
    <a href="?page=<?php echo esc_attr( $this->menu_parameter ); ?>&cal=<?php echo intval( $this->item ); ?>&addbk=1">
        <?php esc_html_e( 'add bookings from the dashboard', 'appointment-hour-booking' ); ?>
    </a> 
    <?php 
    echo wp_kses( 
        __( 'and the bookings will appear in this calendar.<br />For CSV export, print and filter options switch to the "<strong>List View</strong>" with the button above this text.', 'appointment-hour-booking' ),
        array(
            'br'     => array(),
            'strong' => array(),
        )
    ); 
    ?> 
</p>
<div class="clearer"></div>

            <script type="text/javascript">
             var pathCalendar = "<?php echo esc_js($this->get_site_url( true )); ?>?cp_app_action=mv&formid=<?php echo intval($this->item); ?>";
             var dc_subjects = "";var dc_locations = "";
             initMultiViewCal("cal<?php echo intval($this->item); ?>", <?php echo intval($this->item); ?>,
          {viewDay:true,
          viewWeek:true,
          viewMonth:true,
          viewNMonth:true,
          viewList:true,
          viewdefault:"week",
          numberOfMonths:12,
          showtooltip:false,
          tooltipon:0,
          shownavigate:false,
          url:"",
          target:0,
          start_weekday: <?php echo intval($firstday); ?>,
          language:"en-GB",
          cssStyle:"cupertino",
          edition:true,
          btoday:true,
          dialogWidth: 330,
          bnavigation:true,
          brefresh:true,
          bnew:false,
          path:pathCalendar,
          userAdd:false,
          userEdit:false,
          userDel:false,
          userEditOwner:false,
          userDelOwner:false,
          showtooltipdwm:true,
          userOwner:0, cellheight:60, hourswidth:64, palette:0, paletteDefault:"2563eb",
          <?php
                $otherparams = get_option('cp_cpappb_schcalcontent_otherparams','');
                if ($otherparams != '')
                    $otherparams = rtrim($otherparams,",").",";
                echo str_replace('&quot;','"',esc_html($otherparams)); // phpcs:ignore WordPress.Security.EscapeOutput
          ?>
          paletteFull:["94a3b8","f87171","fb923c","fbbf24","4ade80","2dd4bf","38bdf8","60a5fa","a78bfa","f472b6","64748b","ef4444","f97316","f59e0b","22c55e","14b8a6","06b6d4","3b82f6","8b5cf6","ec4899","475569","dc2626","ea580c","d97706","16a34a","0d9488","0891b2","2563eb","7c3aed","db2777","334155","b91c1c","c2410c","b45309","15803d","0f766e","0e7490","1d4ed8","6d28d9","be185d","1e293b","991b1b","9a3412","92400e","166534","115e59","155e75","1e40af","5b21b6","9d174d","0f172a","7f1d1d","7c2d12","78350f","14532d","134e4a","164e63","1e3a8a","4c1d95","831843","6366f1","4f46e5","10b981","059669","f43f5e","e11d48","0284c7","65a30d","c026d3","52525b"]});
            </script>

            <div id="multicalendar"><div id="cal<?php echo intval($this->item); ?>" class="multicalendar"></div></div>

             <div style="clear:both;height:20px" ></div>

</div>

<?php } ?>

<script type="text/javascript">
 function do_dexapp_print()
 {
      w=window.open();
      w.document.write("<style>.cpappb_field_header {font-weight: bold;background-color: #dcdcdc;}.cpapp_break { clear: both; }.cpappb_field_0, .cpappb_field_1,.cpappb_field_2, .cpappb_field_3,.cpappb_field_4, .cpappb_field_5,.cpappb_field_6, .cpappb_field_7,.cpappb_field_8, .cpappb_field_9,.cpappb_field_10, .cpappb_field_11{float: left; min-width: 80px;padding-right:11px;border-bottom: 1px dotted #777777;margin-left: 1px;     padding: 5px;margin: 2px;}.cpappb_field_0 {color: #44aa44;font-weight: bold; }.cpappb_field_1 {color: #aaaa44;font-weight: bold; }.cpappb_field_2{width:180px;}.cpappb_field_3{min-width: 20px;width:20px ;max-width:20px;}.cpappb_field_5{width:200px;overflow:hidden;}.cpappb_field_4,.cpappb_field_6{display:none;}.cpnopr{display:none;};table{border:2px solid black;width:100%;}th{border-bottom:2px solid black;text-align:left}td{padding-left:10px;border-bottom:1px solid black;}.cpappb_field_8 {display:none;}</style>"+document.getElementById('dex_printable_contents').innerHTML);
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