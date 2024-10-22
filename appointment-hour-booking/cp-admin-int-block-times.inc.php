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

define('CPAPPHOURBK_BLOCK_TIMES', true);

$message = '';
$opensecond = false;

if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST[$this->prefix.'_pform_process'] ) )
    echo '<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>Booking added. It appears now in the <a href="?page='.esc_attr($this->menu_parameter).'&cal='.intval($this->item).'&list=1">bookings list</a>. </strong></p></div>';
else if ($this->get_param($this->prefix.'_blockmultiple') == '1' && is_admin() )
{
    $opensecond = true;
	echo '<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>Blocked time added. It appears listed in the <a href="?page='.esc_attr($this->menu_parameter).'&cal='.intval($this->item).'&list=1">bookings list</a>. You can un-block it from the <a href="?page='.esc_attr($this->menu_parameter).'&cal='.intval($this->item).'&list=1">bookings list</a>.</strong></p></div>';
}

$selected = array();
if (!empty($_POST["selectedcalendar"]))
{
	if (is_array($_POST["selectedcalendar"]))
		foreach ($_POST["selectedcalendar"] as $item)
            $selected[] = intval($item);
}
else
    $selected = array($this->item);

$nonce = wp_create_nonce( 'cpappb_actions_admin' );


?>
<style>
	.clear{clear:both;}
	.ahb-first-button{margin-right:10px !important;}
    .ahb-buttons-container{margin:1em 1em 1em 0;}
    .ahb-return-link{float:right;}
    #fbuilder .donotdisplayfield { display:none !important;}
    #fbuilder .captcha { display:none !important;}
</style>
<div class="wrap">

<h1><?php esc_html_e('Block Times','appointment-hour-booking'); ?></h1>

<div class="ahb-buttons-container">
	<a href="<?php print esc_attr(admin_url('admin.php?page='.$this->menu_parameter));?>" class="ahb-return-link">&larr;<?php esc_html_e('Return to the calendars list','appointment-hour-booking'); ?></a>
    <div class="clear"></div>
</div>


<div class="ahb-adintsection-container">
	<div class="ahb-breadcrumb">
		<div class="ahb-step<?php if (!$opensecond) { ?> ahb-step-active<?php } ?>" data-step="1">
			<i>A</i>
			<label><?php esc_html_e('Single - Block Existing Time-slot','appointment-hour-booking'); ?></label>
		</div>
		<div class="ahb-step<?php if ($opensecond) { ?> ahb-step-active<?php } ?>" data-step="2">
			<i>B</i>
			<label><?php esc_html_e('Multiple - Block time range','appointment-hour-booking'); ?></label>
		</div>
	</div>

    <div class="ahb-adintsection<?php if (!$opensecond) { ?> ahb-adintsection-active"<?php } ?> data-step="1">
       <div class="inside"> 
	   
            <p><?php _e('This page is for <strong>blocking some of the available times</strong>. For services with multiple capacity be sure to select the "quantity" to be blocked.','appointment-hour-booking'); ?> <?php esc_html_e('To un-block times, delete the "blocked" entry from the','appointment-hour-booking'); ?> <a href="?page=<?php echo esc_attr($this->menu_parameter.'&cal='.$this->item); ?>&list=1"><?php esc_html_e('booking orders list','appointment-hour-booking'); ?></a>.</p> </p>
            
            
            <p><?php _e('If you want to block complete dates please use instead the <a href="https://apphourbooking.dwbooster.com/customdownloads/invalid-dates.png" target="_blank">invalid dates feature</a>.','appointment-hour-booking'); ?></p>
            
            <script>var cpapphourbk_in_admin=true;</script>
            
            <?php $this->output_filter_content(array('id' => intval($this->item) ));  ?>
       </div>
     </div>
     

    <div class="ahb-adintsection<?php if ($opensecond) { ?> ahb-adintsection-active"<?php } ?>" data-step="2">
      <div class="inside" id="fbuilder">   

         <p><?php _e('This page is for <strong>blocking a range of time</strong> for ALL services and optionally for all booking forms.','appointment-hour-booking'); ?></p> 


         <p><?php _e('If you want to block complete dates please use instead the <a href="https://apphourbooking.dwbooster.com/customdownloads/invalid-dates.png" target="_blank">invalid dates feature</a>.','appointment-hour-booking'); ?></p> 
 
        <form class="cpp_form" name="cp_appbooking_pform_2" id="cp_appbooking_pform_2" action="" method="post" enctype="multipart/form-data" onsubmit="return validateblockedmultiple();">
        <input type="hidden" name="anonce" value="<?php echo esc_attr($nonce); ?>" />
        <input type="hidden" name="<?php echo esc_attr($this->prefix); ?>_blockmultiple" value="1" />
        <strong><?php esc_html_e('Booking Form','appointment-hour-booking'); ?>:</strong><br />
        <select name="selectedcalendar[]" id="selectedcalendar" multiple required>
         <!--<option value="0">[<?php esc_html_e('ALL BOOKING FORMS','appointment-hour-booking'); ?>]</option>-->
              <?php
                $myrows = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix.$this->table_items );                                                                     
                foreach ($myrows as $item)     
                    if ( $current_user_access || @in_array($current_user->ID, unserialize( $item->cp_user_access )) )  
                    {
                        echo '<option value="'.intval($item->id).'"'.(!empty($selected) && is_array($selected) && in_array($item->id,$selected)?' selected':'').'>'.esc_html($item->form_name).'</option>';
                    }
              ?>                          
        </select><br /><em><?php esc_html_e('CTRL+click to mark multiple','appointment-hour-booking'); ?></em><br /><br />
        
        <strong><?php esc_html_e('Date','appointment-hour-booking'); ?>:</strong><br />
        <div id="ahb_bt_datepicker"></div>
        <em><?php esc_html_e('You can select multiple dates','appointment-hour-booking'); ?></em>
        <input autocomplete="off" type="hidden" id="bdate" name="bdate" required value="" ><br /><br />
        
        <strong><?php esc_html_e('Start time','appointment-hour-booking'); ?>:</strong><br />
        <select name="h1"><?php
            for ($i=0;$i<24;$i++)
            {
                echo '<option value="'.intval($i).'" '.(($i==8)?"selected":"").'>'.intval($i).'</option>';
            }
            ?></select> : <select name="m1"><?php
            for ($i=0;$i<60;$i=$i+5)
            {
                echo '<option value="'.intval($i).'" '.(($i==0)?"selected":"").'>'.($i<10?"0":"").intval($i).'</option>';
            }
            ?></select>
        <!--<input type="text" name="starttime" value="08:00" required>--><br /><br />
        
        <strong><?php esc_html_e('End time','appointment-hour-booking'); ?>:</strong><br />
        <select name="h2"><?php
            for ($i=0;$i<24;$i++)
            {
                echo '<option value="'.intval($i).'" '.(($i==17)?"selected":"").'>'.intval($i).'</option>';
            }
            ?></select> : <select name="m2"><?php
            for ($i=0;$i<60;$i=$i+5)
            {
                echo '<option value="'.intval($i).'" '.(($i==0)?"selected":"").'>'.($i<10?"0":"").intval($i).'</option>';
            }
            ?></select><!--<input type="text" name="endtime" value="17:00" required>--><br /><br />
        
        <input type="submit" value="<?php esc_html_e('Block time','appointment-hour-booking'); ?>" />
        </form>
        
         
       </div>	
    </div>     


</div>	

</div>


<script>
 
   function validateblockedmultiple() {
       if (document.getElementById("bdate").value == '')
       {
           alert('<?php echo esc_js(__('Please select at least one date in the calendar','appointment-hour-booking')); ?>');
           return false;
       }
       else
           return true;
   }
 
	jQuery(function(){
		var $ = jQuery;
		$(document).on('click', '.ahb-step', function(){
			var s = $(this).data('step');
			ahbGoToStep(s);
		});

		window['ahbGoToStep'] = function(s){
			$('.ahb-step.ahb-step-active').removeClass('ahb-step-active');
			$('.ahb-step[data-step="'+s+'"]').addClass('ahb-step-active');
			$('.ahb-adintsection.ahb-adintsection-active').removeClass('ahb-adintsection-active');
			$('.ahb-adintsection[data-step="'+s+'"]').addClass('ahb-adintsection-active');
            $(window).scrollTop( $("#topadminsection").offset().top );
		};
    var dates = [];///$("#bdate").val().split(',');
 	$("#ahb_bt_datepicker").datepicker({
                    dateFormat: '<?php echo /**$this->get_option('date_format', 'mm/dd/yy') */'yy-mm-dd'; ?>',
                    firstDay: 0,
                    beforeShowDay: function(d) {
                        var dd = $.datepicker.formatDate("yy-mm-dd", d);
                        for (var i = 0; i < dates.length; i++) 
                        {
                            
                            if (dates[i] == dd) {
                                return [true, 'ahb_bt_selected'];
                            }
                        }
                        setTimeout(function () {
                            $(".ui-state-active").removeClass("ui-state-active");
                        },20);
                        return [true, ''];
                    },
                    onSelect: function(selectedDate) {
                        if ($.inArray(selectedDate,dates) > -1)
                            dates.splice($.inArray(selectedDate,dates), 1);
                        else
                            dates[dates.length] = selectedDate;
                        $("#bdate").val(dates.join(","));
                        $(".ui-state-active").removeClass("ui-state-active")        

                    }
                 });
 
	}); 
  jQuery( function() {
    jQuery( "#admin-tabs" ).tabs();
  } );
  </script>