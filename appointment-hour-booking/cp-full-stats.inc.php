<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wpdb;

$this->item = intval($_GET["cal"]);

$current_user = wp_get_current_user();
$current_user_access = current_user_can('edit_pages');

if ( !is_admin() || (!$current_user_access && !@in_array($current_user->ID, unserialize($this->get_option("cp_user_access","")))))
{
    echo 'Direct access not allowed.';
    exit;
}

// pre-select time-slots
$selection = array();
$rows = $wpdb->get_results( $wpdb->prepare("SELECT time,posted_data FROM ".$wpdb->prefix.$this->table_messages." WHERE notifyto<>%s AND formid=%d ORDER BY time DESC LIMIT 0,100000", $this->blocked_by_admin_indicator, $this->item) );  // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

$yearly_incoming = array();
$monthly_incoming = array();
$weekly_incoming = array();
$daily_incoming = array();

$yearly = array();
$monthly = array();
$weekly = array();
$daily = array();
$currentdate = strtotime(date("Y-m-d"));


$blockedstatuses = explode(",", get_option('cp_cpappb_statuses_block',',Attended'));

foreach($rows as $item)
{        
    $data = unserialize($item->posted_data);
    foreach($data["apps"] as $app)
        if ( in_array($app["cancelled"],$blockedstatuses) )
        {       
                $dt = strtotime($app["date"]);
                $dt_incoming = strtotime($item->time);
                if ($dt>=$currentdate)
                {  
                    //$selection[] = array($app["date"]." ".$app["slot"], $app["date"], $app["slot"]);
					if (!isset($yearly["x".date("Y",$dt)])) $yearly["x".date("Y",$dt)] = 0;
                    $yearly["x".date("Y",$dt)]++;
					if (!isset($monthly["x".date("Ym",$dt)])) $monthly["x".date("Ym",$dt)] = 0;
                    $monthly["x".date("Ym",$dt)]++;
					if (!isset($weekly["x".date("YW",$dt)])) $weekly["x".date("YW",$dt)] = 0;
                    $weekly["x".date("YW",$dt)]++;
					if (!isset($daily["x".date("Ymd",$dt)])) $daily["x".date("Ymd",$dt)] = 0;
                    $daily["x".date("Ymd",$dt)]++;                                   
                }
                if (empty($yearly_incoming["x".date("Y",$dt_incoming)])) $yearly_incoming["x".date("Y",$dt_incoming)] = 0;
                if (empty($monthly_incoming["x".date("Ym",$dt_incoming)])) $monthly_incoming["x".date("Ym",$dt_incoming)] = 0;
                if (empty($weekly_incoming["x".date("YW",$dt_incoming)])) $weekly_incoming["x".date("YW",$dt_incoming)] = 0;
                if (empty($daily_incoming["x".date("Ymd",$dt_incoming)])) $daily_incoming["x".date("Ymd",$dt_incoming)] = 0;                
                
                $yearly_incoming["x".date("Y",$dt_incoming)]++;
                $monthly_incoming["x".date("Ym",$dt_incoming)]++;
                $weekly_incoming["x".date("YW",$dt_incoming)]++;
                $daily_incoming["x".date("Ymd",$dt_incoming)]++;                  
        }    
}


function getMonthly($arr, $is_incoming = false)
{
    $str = '';
    $dt = ($is_incoming ? strtotime("-11 months") : time());
    $dt = strtotime(date("Y-m-01", $dt));
    $x = []; $y = [];
    for ($i=0;$i<12;$i++)
    {
        $x[] = date("Ym",$dt);
        $key = "x".date("Ym",$dt);
        $y[] = (isset($arr[$key])?$arr[$key]:'0'); 
        $dt = strtotime( "+1 month" ,$dt);    
    }   
    echo ' x="'.esc_html(join(",", $x)).'" y="'.esc_html(join(",", $y)).'" '; 
}
function getWeekly($arr, $is_incoming = false)
{
    $str = '';
    $dt = ($is_incoming ? strtotime("-11 weeks") : time());
    $x = []; $y = [];
    for ($i=0;$i<12;$i++)
    {
        $x[] = date("YW",$dt);
        $key = "x".date("YW",$dt);
        $y[] = (isset($arr[$key])?$arr[$key]:'0');         
        $dt = strtotime("+1 week",$dt);    
    }   
    echo ' x="'.esc_html(join(",", $x)).'" y="'.esc_html(join(",", $y)).'" ';
}
function getDaily($arr, $is_incoming = false)
{
    $str = '';
    $dt = ($is_incoming ? strtotime("-29 days") : time());
    $x = []; $y = [];
    for ($i=0;$i<30;$i++)
    {
        $x[] = date("Ymd",$dt);
        $key = "x".date("Ymd",$dt); 
        $y[] = (isset($arr[$key])?$arr[$key]:'0'); 
        $dt = strtotime("+1 day",$dt);    
    }
    echo ' x="'.esc_html(join(",", $x)).'" y="'.esc_html(join(",", $y)).'" ';
}

?>
<div class="ahb-statssection-header"><h2><?php esc_html_e('Submission time stats (date in which the appointment request was received)','appointment-hour-booking')?></h2></div>
<div class="container">  
  <div class="col4">  
    <div class="ahb-graphs" >
    	<div class="ahb-statssection-header">
    		<h3><?php esc_html_e('Incoming - Monthly Stats (lastest 12 months)','appointment-hour-booking'); ?></h3>
    	</div>
    	<div class="ahb-statssection">
            <canvas id="chartMonthlyIncoming" label="<?php esc_html_e('Submissions','appointment-hour-booking'); ?>" <?php (getMonthly($monthly_incoming, true)); ?> ></canvas>
    	</div>
    </div> 
  </div>
  <div class="col4">  
    <div class="ahb-graphs" >
    	<div class="ahb-statssection-header">
    		<h3><?php esc_html_e('Incoming - Weekly Stats (lastest 12 weeks)','appointment-hour-booking'); ?></h3>
    	</div>
    	<div class="ahb-statssection">
            <canvas id="chartWeeklyIncoming" label="<?php esc_html_e('Submissions','appointment-hour-booking'); ?>" <?php (getWeekly($weekly_incoming, true)); ?> ></canvas>
    	</div>
    </div> 
  </div>
  <div class="col4">  
    <div class="ahb-graphs" >
    	<div class="ahb-statssection-header">
    		<h3><?php esc_html_e('Daily Stats(next 30 days)','appointment-hour-booking'); ?></h3>
    	</div>
    	<div class="ahb-statssection">
            <canvas id="chartDailyIncoming" label="<?php esc_html_e('Submissions','appointment-hour-booking'); ?>" <?php (getDaily($daily_incoming, true)); ?>  ></canvas>
    	</div>
    </div> 
  </div> 
</div>

<div class="ahb-statssection-header margin-top-15"><h2><?php esc_html_e('Booked times stats (appointment date)','appointment-hour-booking')?></h2></div>
<div class="container">
  <div class="col4">  
    <div class="ahb-graphs" >
    	<div class="ahb-statssection-header">
    		<h3><?php esc_html_e('Monthly Stats (next 12 months)','appointment-hour-booking'); ?></h3>
    	</div>
    	<div class="ahb-statssection">
            <canvas id="chartMonthly" label="<?php esc_html_e('Booked times','appointment-hour-booking'); ?>" <?php (getMonthly($monthly)); ?> ></canvas>
    	</div>
    </div> 
  </div>
  <div class="col4">  
    <div class="ahb-graphs" >
    	<div class="ahb-statssection-header">
    		<h3><?php esc_html_e('Weekly Stats (next 12 weeks)','appointment-hour-booking'); ?></h3>
    	</div>
    	<div class="ahb-statssection">
            <canvas id="chartWeekly" label="<?php esc_html_e('Booked times','appointment-hour-booking'); ?>" <?php (getWeekly($weekly)); ?> ></canvas>
    	</div>
    </div> 
  </div>
  <div class="col4">  
    <div class="ahb-graphs" >
    	<div class="ahb-statssection-header">
    		<h3><?php esc_html_e('Daily Stats(next 30 days)','appointment-hour-booking'); ?></h3>
    	</div>
    	<div class="ahb-statssection">
            <canvas id="chartDaily" label="<?php esc_html_e('Booked times','appointment-hour-booking'); ?>" <?php (getDaily($daily)); ?>  ></canvas>
    	</div>
    </div> 
  </div> 
</div>
<style>
#cTable th{background:#ccc}
#cTable td{vertical-align:top}
</style>