<?php

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

function getStatsBy($arr)
{
    foreach($arr as $key => $value)
       echo '<div><b>'.esc_html(substr($key,1)).'</b> '.esc_html($value.' '.__('appointments','appointment-hour-booking')).' </div>';
}
function getStatsByMonthly($arr, $is_incoming = false)
{
    $dt = ($is_incoming ? strtotime("-11 months") : time());
    $dt = strtotime(date("Y-m-01", $dt));
    for ($i=0;$i<12;$i++)
    {
        $key = "x".date("Ym",$dt);
        echo '<div><b>'.esc_html(date("Y M",$dt)).'</b> '.esc_html((isset($arr[$key])?$arr[$key]:0).' '.__('appointments','appointment-hour-booking')).' </div>'; 
        $dt = strtotime( "+1 month" ,$dt);    
    }    
}
function getStatsByWeekly($arr, $is_incoming = false)
{
    $dt = ($is_incoming ? strtotime("-11 weeks") : time());
    for ($i=0;$i<12;$i++)
    {
        $key = "x".date("YW",$dt);
        echo '<div><b>'.esc_html(date("Y W",$dt)).'</b> '.esc_html((isset($arr[$key])?$arr[$key]:'0').' '.__('appointments','appointment-hour-booking')).' </div>';         
        $dt = strtotime("+1 week",$dt);    
    }     
}
function getStatsByDaily($arr, $is_incoming = false)
{
    $dt = ($is_incoming ? strtotime("-29 days") : time());
    for ($i=0;$i<30;$i++)
    {
        $key = "x".date("Ymd",$dt);
        echo '<div><b>'.esc_html(date("Y M d",$dt)).'</b> '.esc_html((isset($arr[$key])?$arr[$key]:'0').' '.__('appointments','appointment-hour-booking')).' </div>'; 
        $dt = strtotime("+1 day",$dt);    
    }    
}

echo '<div class="ahb-section-container"><table id="cTable" width="100%">';
echo '<tr><th colspan="4" style="background-color:#b0b0b0">'.__('Submission time stats (date in which the appointment request was received)','appointment-hour-booking').'</th></tr>';
echo '<tr><th>'.__('Incoming- Yearly Stats','appointment-hour-booking').'</th><th>'.__('Incoming - Monthly Stats (lastest 12 months)','appointment-hour-booking').'</th><th>'.__('Incoming - Weekly Stats (lastest 12 weeks)','appointment-hour-booking').'</th><th>'.__('Incoming - Daily Stats (lastest 30 days)','appointment-hour-booking').'</th></tr>';
echo '<tr><td>';
getStatsBy($yearly_incoming);
echo '</td><td>';
getStatsByMonthly($monthly_incoming, true);
echo '</td><td>';
getStatsByWeekly($weekly_incoming, true);
echo '</td><td>';
getStatsByDaily($daily_incoming, true);
echo '</td></tr>';
echo '</table></div>';

echo '<div class="ahb-section-container"><table id="cTable" width="100%">';
echo '<tr><th colspan="4" style="background-color:#b0b0b0">'.__('Booked times stats (appointment date)','appointment-hour-booking').'</th></tr>';
echo '<tr><th>'.__('Yearly Stats','appointment-hour-booking').'</th><th>'.__('Monthly Stats (next 12 months)','appointment-hour-booking').'</th><th>'.__('Weekly Stats (next 12 weeks)','appointment-hour-booking').'</th><th>'.__('Daily Stats(next 30 days)','appointment-hour-booking').'</th></tr>';
echo '<tr><td>';
getStatsBy($yearly);
echo '</td><td>';
getStatsByMonthly($monthly);
echo '</td><td>';
getStatsByWeekly($weekly);
echo '</td><td>';
getStatsByDaily($daily);
echo '</td></tr>';
echo '</table></div>';


?>
<style>
#cTable th{background:#ccc}
#cTable td{vertical-align:top}
</style>