<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


global $wpdb;

$this->item = intval($_GET["cal"]);

$current_user = wp_get_current_user();
$current_user_access = current_user_can('manage_options');

if ( !is_admin() || (!$current_user_access && !@in_array($current_user->ID, unserialize($this->get_option("cp_user_access","")))))
{
    echo 'Direct access not allowed.';
    exit;
}

if ( !is_admin() )
{
    echo 'Direct access not allowed.';
    exit;
}

global $numberofdates;
$numberofdates = [];


if ($this->item != 0)
    $myform = $wpdb->get_results( $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.$this->table_items .' WHERE id=%d', $this->item) );  // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared


$current_page = intval( (empty($_GET["p"])?0:$_GET["p"]));
if (!$current_page) $current_page = 1;
$records_per_page = 50;

$date_start = '';
$date_end = '';

$rawfrom = (isset($_GET["dfrom"]) ? sanitize_text_field($_GET["dfrom"]) : '');
$rawto = (isset($_GET["dto"]) ? sanitize_text_field($_GET["dto"]) : '');
if ($this->get_option('date_format', 'mm/dd/yy') == 'dd/mm/yy')
{
    $rawfrom = str_replace('/','.',$rawfrom);
    $rawto = str_replace('/','.',$rawto);
}

$cond = '';
if (!empty($_GET["search"])) $cond .= " AND (data like '%".esc_sql(sanitize_text_field($_GET["search"]))."%' OR posted_data LIKE '%".esc_sql(sanitize_text_field($_GET["search"]))."%')";
if ($rawfrom != '')
{
    $date_start = date("Y-m-d",strtotime($rawfrom));
    $cond .= " AND (`time` >= '".esc_sql( $date_start )."')";
}
if ($rawto != '')
{
    $date_end = date("Y-m-d",strtotime($rawto));
    $cond .= " AND (`time` <= '".esc_sql($date_end)." 23:59:59')";
}
if ($this->item != 0) $cond .= " AND formid=".intval($this->item);

$events = $wpdb->get_results( "SELECT ipaddr,time,notifyto,posted_data FROM ".$wpdb->prefix.$this->table_messages." WHERE 1=1 ".$cond." ORDER BY `time` DESC" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

// general initialization
$fields = array();
$fields["date"] = array();
$fields["ip"] = array();
$fields["notifyto"] = array();
foreach ($events as $item)
{
    if (empty($fields["date"]["k".substr($item->time,0,10)])) $fields["date"]["k".substr($item->time,0,10)] = 0;
    if (empty($fields["time"]["k".substr($item->time,11,2)])) $fields["time"]["k".substr($item->time,11,2)] = 0;
    if (empty($fields["notifyto"]["k".$item->notifyto])) $fields["notifyto"]["k".$item->notifyto] = 0;
    if (empty($fields["ip"]["k".$item->ipaddr])) $fields["ip"]["k".$item->ipaddr] = 0;    
    
    $fields["date"]["k".substr($item->time,0,10)]++;
    $fields["time"]["k".substr($item->time,11,2)]++;
    $fields["notifyto"]["k".$item->notifyto]++;
    $fields["ip"]["k".$item->ipaddr]++;
    $params = unserialize($item->posted_data);
    foreach ($params as $param => $value)
        if (!is_array($value) && strlen($value) < 100)
        {
            if (empty($fields[$param]["k".$value])) $fields[$param]["k".$value] = 0;
            $fields[$param]["k".$value]++;
        }
}


// line graphs
$hourly_messages = '';
$max_hourly_messages = 200;
for ($i=0;$i<=23;$i++)
    if (isset($fields['time']['k'.($i<10?'0':'').$i]))
    {
        $hourly_messages .= $fields['time']['k'.($i<10?'0':'').$i].($i<23?',':'');
        if ($max_hourly_messages < intval($fields['time']['k'.($i<10?'0':'').$i]))
            $max_hourly_messages = intval($fields['time']['k'.($i<10?'0':'').$i]);
    }        
    else
        $hourly_messages .='0'.($i<23?',':'');

if ($date_start == '')
{
    $kkeys = array_keys($fields["date"]);
    if (count($kkeys))
        $date_start = substr(min($kkeys),1);
    else
        $date_start = date("Y-m-d");
}

if ($date_end == '')
{
    $kkeys = array_keys($fields["date"]);    
    if (count($kkeys))
        $date_end = substr(max($kkeys),1);
    else
        $date_end = date("Y-m-d");    
}

$daily_messages = '';
$max_daily_messages = 200;
$date = $date_start;
while ($date <= $date_end)
{
    if (isset($fields['date']['k'.$date]))
    {
        $daily_messages .= ','.$fields['date']['k'.$date];
        if ($max_daily_messages < intval($fields['date']['k'.$date]))
            $max_daily_messages = intval($fields['date']['k'.$date]);        
    }        
    else
        $daily_messages .=',0';
    $date = date("Y-m-d",strtotime($date." +1 day"));
}
$daily_messages = substr($daily_messages,1);

if (!isset($_GET["field"]))
    $field_filter = 'time';
else
    $field_filter = sanitize_key($_GET["field"]);

$color_array = array('ffb3ba','ffdfba','ffffba', 'baffc9', 'bae1ff', 'a8e6cf', 'dcedc1', 'ffd3b6', 'ffaaa5', 'ff8b94', 'eea990', 'adcbe3', 'e2f4c7');


if ($this->item)
{
    $form = json_decode($this->cleanJSON($this->get_option('form_structure', CP_APPBOOK_DEFAULT_form_structure)));
    $form = $form[0];
}
else
    $form = array();

?>

<h1><?php esc_html_e('Stats','appointment-hour-booking'); ?> - <?php echo esc_html($this->get_option("form_name")); ?></h1>

<div class="ahb-buttons-container">
	<a href="<?php print esc_attr(admin_url('admin.php?page='.$this->menu_parameter));?>" class="ahb-return-link">&larr;<?php esc_html_e('Return to the calendars list','appointment-hour-booking'); ?></a>
	<div class="clear"></div>
</div>

<?php do_action("cp_apphourbooking_do_action_reportstats"); ?>

<?php require_once dirname( __FILE__ ).'/cp-full-stats.inc.php'; ?>


<div class="ahb-section-container">
	<div class="ahb-section">
       <form action="admin.php" method="get">
        <input type="hidden" name="page" value="<?php echo esc_attr($this->menu_parameter); ?>" />
        <input type="hidden" name="cal" value="<?php echo intval($this->item); ?>" />
        <input type="hidden" name="report" value="1" />
        <input type="hidden" name="field" value="<?php echo esc_attr($field_filter); ?>" />
		<nobr><label><?php esc_html_e('Search for','appointment-hour-booking'); ?>:</label> <input type="text" name="search" value="<?php echo esc_attr( (!empty($_GET["search"])?sanitize_text_field($_GET["search"]):'')); ?>">&nbsp;&nbsp;</nobr>
		<nobr><label><?php esc_html_e('From','appointment-hour-booking'); ?>:</label> <input autocomplete="off" type="text" id="dfrom" name="dfrom" value="<?php echo esc_attr((!empty($_GET["dfrom"])?sanitize_text_field($_GET["dfrom"]):'')); ?>" >&nbsp;&nbsp;</nobr>
		<nobr><label><?php esc_html_e('To','appointment-hour-booking'); ?>:</label> <input autocomplete="off" type="text" id="dto" name="dto" value="<?php echo esc_attr((!empty($_GET["dto"])?sanitize_text_field($_GET["dto"]):'')); ?>" >&nbsp;&nbsp;</nobr>		
		<nobr><label><?php esc_html_e('Item','appointment-hour-booking'); ?>:</label> <select id="cal" name="cal">
          <?php if ($current_user_access) { ?> <option value="0">[<?php esc_html_e('All Items','appointment-hour-booking'); ?>]</option><?php } ?>
   <?php
    $myrows = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix.$this->table_items );
	$saveditem = $this->item;
    foreach ($myrows as $item)
    {
        $this->setId($item->id);
        $options = unserialize($this->get_option('cp_user_access', serialize(array()))); 
		$this->setId($saveditem);
        if (!is_array($options))
             $options = array();        
        if ($current_user_access || @in_array($current_user->ID, $options))
            echo '<option value="'.intval($item->id).'"'.(intval($item->id)==intval($this->item)?" selected":"").'>'.esc_html($item->form_name).'</option>';
    }
   ?>
    </select></nobr>
		<nobr>
			<input type="submit" name="<?php echo esc_attr($this->prefix); ?>_csv3" value="<?php esc_html_e('Export to CSV','appointment-hour-booking'); ?>" class="button" style="float:right;margin-left:10px;">
			<input type="submit" name="ds" value="<?php esc_html_e('Filter','appointment-hour-booking'); ?>" class="button-primary button" style="float:right;">
		</nobr>
       </form>
       <div style="clear:both"></div>
	</div>
</div> 
<div class="container">  
  <div class="col6">  
    <div class="ahb-graphs" >
	  <div class="ahb-statssection-header">
	  	<h3><?php esc_html_e('Submissions per day in the selected date range.','appointment-hour-booking'); ?></h3>
	  	<span><?php esc_html_e('Days from','appointment-hour-booking'); ?> <?php echo esc_html($date_start); ?> to <?php echo esc_html($date_end); ?></span>
	  </div>
	  <div class="ahb-statssection">
          <canvas id="chartperday"  st="<?php echo esc_attr($date_start); ?>" et="<?php echo esc_attr($date_end); ?>" label="<?php esc_html_e('Submissions.','appointment-hour-booking'); ?>" questions='[{"color":"#008ec2","values":[<?php echo esc_html($daily_messages); ?>]}]'></canvas>
	  </div>
    </div> 
  </div>  
  <div class="col6">  
    <div class="ahb-graphs" >
	  <div class="ahb-statssection-header">
	  	<h3><?php esc_html_e('Total submissions per hour in the selected date range.','appointment-hour-booking'); ?></h3>
	  	<span><?php esc_html_e('Days from','appointment-hour-booking'); ?> <?php echo esc_html($date_start); ?> to <?php echo esc_html($date_end); ?></span>
	  </div>
	  <div class="ahb-statssection">
          <canvas id="chartperhour"  st="<?php echo esc_attr($date_start); ?>" et="<?php echo esc_attr($date_end); ?>" label="<?php esc_html_e('Submissions.','appointment-hour-booking'); ?>" questions='[{"color":"#008ec2","values":[<?php echo esc_html($hourly_messages); ?>]}]''></canvas>
	  </div>
    </div> 
  </div>
</div>

<br />

<div class="ahb-statssection-container" style="background:#f6f6f6;">
	<div class="ahb-statssection-header">
        <form action="admin.php" name="cfm_formrep" method="get">
         <input type="hidden" name="page" value="<?php echo esc_attr($this->menu_parameter); ?>" />
         <input type="hidden" name="cal" value="<?php echo intval($this->item); ?>" />
         <input type="hidden" name="report" value="1" />
         <input type="hidden" name="search" value="<?php echo esc_attr((!empty($_GET["search"])?sanitize_text_field($_GET["search"]):'')); ?>" />
         <input type="hidden" name="dfrom" value="<?php echo esc_attr((!empty($_GET["dfrom"])?sanitize_text_field($_GET["dfrom"]):'')); ?>" />
         <input type="hidden" name="dto" value="<?php echo esc_attr((!empty($_GET["dto"])?sanitize_text_field($_GET["dto"]):'')); ?>" />
		 <h3><?php esc_html_e('Select field for the report','appointment-hour-booking'); ?>: <select name="field" onchange="document.cfm_formrep.submit();">
              <?php
                   foreach ($fields as $item => $value)
                       echo '<option value="'.esc_attr($item).'"'.($field_filter==$item?' selected':'').'>'.esc_html($this->get_form_field_label($item,$form)).'</option>';
              ?>
         </select></h3>
        </form>
	</div>
	<div class="ahb-statssection">
        <div id="dex_printable_contents">

        <div style="width:100%;padding:0;background:white;border:1px solid #e6e6e6;">
         <div style="padding:10px;background:#ECECEC;color:#21759B;font-weight: bold;">
           <?php esc_html_e('Report of values for','appointment-hour-booking'); ?>: <em><?php echo esc_html($this->get_form_field_label(sanitize_text_field($field_filter),$form)); ?></em>
         </div>

        <div style="padding:10px;">
        <?php
          $arr = $fields[$field_filter];
          if (!is_array($arr))
              $arr = array();
          arsort($arr, SORT_NUMERIC);
          $total = 0;
          /* $totalsize = 600; */
          foreach ($arr as $item => $value)
              $total += $value;
          /* $max = max($arr);
          $totalsize = round(600 / ($max/$total) ); */
          $count = 0;
          foreach ($arr as $item => $value)
          {
              echo esc_html($value.' times: '.(strlen($item)>50?substr($item,1,50).'...':substr($item,1)));
              echo '<div style="width:'.esc_html(round($value/$total*100)).'%;border:1px solid white;margin-bottom:3px;font-size:9px;text-align:center;font-weight:bold;background-color:#'.esc_html($color_array[$count]).'">'.esc_html(round($value/$total*100,2)).'%</div>';
              $count++;
              if ($count >= count($color_array)) $count = count($color_array)-1;
          }
        ?>
        </div>

         <div style="padding-right:5px;padding-left:5px;margin-bottom:20px;color:#888888;">&nbsp;&nbsp;* <?php esc_html_e('Number of times that appears each value. Percent in relation to the total of submissions.','appointment-hour-booking'); ?><br />&nbsp;&nbsp;&nbsp;&nbsp; <?php esc_html_e('Date range from','appointment-hour-booking'); ?> <?php echo esc_html($date_start); ?> <?php esc_html_e('to','appointment-hour-booking'); ?> <?php echo esc_html($date_end); ?>.</div>
        </div>

        <div style="clear:both"></div>
        </div>
	</div>
</div>



<div class="ahb-buttons-container">
	<input type="button" value="<?php esc_html_e('Print Stats','appointment-hour-booking'); ?>" onclick="do_dexapp_print();" class="button button-primary" />
	<a href="<?php print esc_attr(admin_url('admin.php?page='.$this->menu_parameter));?>" class="ahb-return-link">&larr;<?php esc_html_e('Return to the calendars list','appointment-hour-booking'); ?></a>
	<div class="clear"></div>
</div>

<script type="text/javascript">

 function do_dexapp_print()
 {
      w=window.open();
      w.document.write("<style>.cpnopr{display:none;};table{border:2px solid black;width:100%;}th{border-bottom:2px solid black;text-align:left}td{padding-left:10px;border-bottom:1px solid black;}</style>"+document.getElementById('dex_printable_contents').innerHTML);
      w.print();
      w.close();
 }

 var $j = jQuery.noConflict();
 $j(function() {
 	$j("#dfrom").datepicker({
                    dateFormat: 'yy-mm-dd'
                 });
 	$j("#dto").datepicker({
                    dateFormat: 'yy-mm-dd'
                 });
 });

</script>


<script type='text/javascript' src='<?php echo esc_attr(plugins_url('js/chart.js', __FILE__)); ?>'></script>
<script type="text/javascript">
var $ = jQuery.noConflict();
$j(document).ready(function(){
Chart.defaults.set({
            elements: {
                line: {
                    borderWidth: 2, // Set global borderWidth for line charts,
                    tension: 0.4
                }
            }
        });
Chart.defaults.set({
    responsive: true, // Enable responsive behavior
    maintainAspectRatio: false, // Set global maintainAspectRatio to false
    scales: {
        y: {
            beginAtZero: true, // Start the Y-axis at zero
            ticks: {
                precision: 0
            }
        }
    },
    plugins: {
        legend: {
            display: false // Disable the legend
        },
    },    
});
drawChartDaily($("#chartDailyIncoming"));
drawChartDaily($("#chartDaily"));
drawChartWeekly($("#chartWeeklyIncoming"));
drawChartWeekly($("#chartWeekly"));
drawChartMonthly($("#chartMonthlyIncoming"));
drawChartMonthly($("#chartMonthly"));
function drawChartMonthly(obj)
{
    function getLabels(obj)
    {       
        for (var i = 0; i < obj.length; i++)
            obj[i] = $.datepicker.formatDate( ((i==0 || (i==obj.length - 1))?"M yy":"M"),$.datepicker.parseDate("yymmdd", obj[i]+"01"));
        return obj;
    }
    new Chart(obj, {
        type: 'line',
        data: {
          labels: getLabels(obj.attr("x").split(',')),
          datasets: [{
            label: obj.attr("label"),
            data: obj.attr("y").split(','),
            startdate: obj.attr("x").split(',')
          }]
        },         
        options: { 
            plugins: {
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            return $.datepicker.formatDate( "M yy",$.datepicker.parseDate("yymmdd", context[0].dataset.startdate[context[0].parsed.x]+"01"));;
                        }
                    }
                }
            }
        }   
      });    
}
function drawChartWeekly(obj)
{
    function getLabels(obj)
    {       
        for (var i = 0; i < obj.length; i++)
            obj[i] =((i==0 || (i==obj.length - 1))?obj[i].substring(0, 4)+ " Week ":"") + obj[i].substring(4, 6);
        return obj;
    }
    new Chart(obj, {
        type: 'line',
        data: {
          labels: getLabels(obj.attr("x").split(',')),
          datasets: [{
            label: obj.attr("label"),
            data: obj.attr("y").split(','),
            startdate: obj.attr("x").split(',')
          }]
        },         
        options: { 
            plugins: {
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            var s = context[0].dataset.startdate[context[0].parsed.x];
                            return s.substring(0, 4)+ " Week #" + s.substring(4, 6);
                        }
                    }
                }
            }
        }   
      });    
}
function drawChartDaily(obj)
{
    function getLabels(obj)
    {       
        for (var i = 0; i < obj.length; i++)
            obj[i] = $.datepicker.formatDate( ((i==0 || (i==obj.length - 1))?"M dd":"dd"),$.datepicker.parseDate("yymmdd", obj[i]));
        return obj;
    }
    new Chart(obj, {
        type: 'line',
        data: {
          labels: getLabels(obj.attr("x").split(',')),
          datasets: [{
            label: obj.attr("label"),
            data: obj.attr("y").split(','),
            startdate: obj.attr("x").split(',')
          }]
        },         
        options: { 
            plugins: {
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            return $.datepicker.formatDate( "M d, yy",$.datepicker.parseDate("yymmdd", context[0].dataset.startdate[context[0].parsed.x]));;
                        }
                    }
                }
            }
        }   
      });    
}           
            
chartperday($("#chartperday")); 
function chartperday(obj)
{           
    var data = jQuery.parseJSON(obj.attr("questions"));
    var st = $.datepicker.parseDate("yy-mm-dd", obj.attr("st"));
    var et = $.datepicker.parseDate("yy-mm-dd", obj.attr("et"));
    var l = [];
    while (st < et)
    {
        l[l.length] = $.datepicker.formatDate("yy-mm-dd", st);
        st.setDate(st.getDate() + 1);
    }        
    new Chart(obj, {
        type: 'line',
        data: {
          labels: l,
          datasets: [{
            label: obj.attr("label"),
            data: data[0].values
          }]
        },
      });    
}    
chartperhour($("#chartperhour"));
function chartperhour(obj)
{           
    var data = jQuery.parseJSON(obj.attr("questions"));
    var l = [];
        for (var i = 0; i < 24; i++)
            l[l.length] = i;
    new Chart(obj, {
        type: 'line',
        data: {
          labels: l,
          datasets: [{
            label: obj.attr("label"),
            data: data[0].values
          }]
        },
      });    
}
});
</script>