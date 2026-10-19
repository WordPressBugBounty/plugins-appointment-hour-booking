<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
    echo 'Direct access not allowed.';
    exit;
}

$nonce = wp_create_nonce( 'cpappb_actions_pwizard' );
?>

<h1><?php esc_html_e( 'Publish Appointment Hour Booking Form', 'appointment-hour-booking' ); ?></h1>

<style type="text/css">
.ahb-buttons-container { margin: 1em 1em 1em 0; }
.ahb-return-link { float: right; }
.ahb-section-container {
    border: 1px solid #e6e6e6;
    border-radius: 4px;
    margin: 1em 1em 1em 0;
    background: #ffffff;
    max-width: 850px;
}
.ahb-section { padding: 24px; display: none; }
.ahb-section label { font-weight: 600; }
.ahb-section-active { display: block; }
.ahb-section select, .ahb-section input[type="text"] { width: 100%; max-width: 450px; }
.cpmvcontainer { font-size: 15px !important; }
.ahb-shortcode-box {
    display: flex;
    align-items: center;
    gap: 10px;
    border: 1px dashed #8c8f94;
    background-color: #f6f7f7;
    padding: 12px 15px;
    border-radius: 4px;
    margin: 10px 0;
    max-width: 450px;
}
.ahb-shortcode-box code {
    font-size: 14px;
    font-weight: bold;
    flex-grow: 1;
    background: transparent;
}
</style>

<div class="ahb-buttons-container">
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $this->menu_parameter ) ); ?>" class="ahb-return-link">
        &larr; <?php esc_html_e( 'Return to the calendars list', 'appointment-hour-booking' ); ?>
    </a>
    <div class="clear"></div>
</div>

<form method="post" action="?page=cp_apphourbooking&pwizard=1" name="regForm" id="regForm">
    <input name="cp_apphourbooking_do_action_loaded" type="hidden" value="wizard" />
    <input name="anonce" type="hidden" value="<?php echo esc_attr( $nonce ); ?>" />

<?php if ( $this->get_param( 'cp_apphourbooking_do_action_loaded' ) == 'wizard' ) : 
    $cal_id = intval( $this->get_param( 'cpapphourbk_id' ) );
?>
<style type="text/css">
.ahb-success-card {
    background: #ffffff;
    border: 1px solid #dcdcde;
    border-left: 4px solid #00a32a;
    border-radius: 6px;
    padding: 28px 32px;
    margin: 20px 0;
    max-width: 780px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.04);
    box-sizing: border-box;
}
.ahb-success-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 0 0 16px 0;
}
.ahb-success-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: #edfaef;
    color: #00a32a;
    border-radius: 50%;
    font-size: 18px;
    font-weight: bold;
    flex-shrink: 0;
}
.ahb-success-header h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: #1d2327;
    line-height: 1.3;
}
.ahb-url-box {
    background: #f6f7f7;
    border: 1px solid #dcdcde;
    border-radius: 4px;
    padding: 12px 16px;
    margin: 16px 0 24px 0;
    font-size: 14px;
    color: #3c434a;
    word-break: break-all;
}
.ahb-url-box a {
    font-weight: 600;
    color: #2271b1;
    text-decoration: none;
    margin-left: 4px;
}
.ahb-url-box a:hover {
    text-decoration: underline;
}
.ahb-actions-label {
    font-size: 14px;
    font-weight: 600;
    color: #1d2327;
    margin: 0 0 12px 0;
}
.ahb-action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 24px;
    clear: both;
}
.ahb-action-buttons .button {
    float: none !important;
    margin: 0 !important;
    padding: 6px 18px !important;
    height: auto !important;
    line-height: 1.6 !important;
    font-size: 14px !important;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.ahb-draft-notice {
    background: #fcf9e8;
    border: 1px solid #ebd778;
    border-radius: 4px;
    padding: 12px 16px;
    font-size: 13px;
    color: #50575e;
    line-height: 1.5;
    clear: both;
}
.ahb-draft-notice strong {
    color: #1d2327;
}
</style>

<div class="ahb-success-card">
    <div class="ahb-success-header">
        <span class="ahb-success-icon">✓</span>
        <h2><?php esc_html_e( 'Great! Form successfully published', 'appointment-hour-booking' ); ?></h2>
    </div>

    <div class="ahb-url-box">
        <?php esc_html_e( 'The booking form was placed into the page:', 'appointment-hour-booking' ); ?>
        <a href="<?php echo esc_url( $this->postURL ); ?>" target="_blank" rel="noopener noreferrer">
            <?php echo esc_html( $this->postURL ); ?> &#8599;
        </a>
    </div>

    <p class="ahb-actions-label"><?php esc_html_e( 'Next steps:', 'appointment-hour-booking' ); ?></p>

    <div class="ahb-action-buttons">
        <button class="button button-primary" type="button" onclick="window.open('<?php echo esc_js( $this->postURL ); ?>', '_blank');">
            <?php esc_html_e( 'View the Published Booking Form', 'appointment-hour-booking' ); ?>
        </button>
        <button class="button button-secondary" type="button" onclick="window.location.href='?page=cp_apphourbooking&cal=<?php echo $cal_id; ?>';">
            <?php esc_html_e( 'Edit booking form settings and availability', 'appointment-hour-booking' ); ?>
        </button>
    </div>

    <div class="ahb-draft-notice">
        <strong><?php esc_html_e( 'Note:', 'appointment-hour-booking' ); ?></strong>
        <?php esc_html_e( 'If the calendar was published in a new draft page or post, remember to publish it when ready.', 'appointment-hour-booking' ); ?>
    </div>
</div>
<?php else : ?>
    <div class="ahb-section-container">
        <div class="ahb-section ahb-section-active">
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th><label for="cpapphourbk_id"><?php esc_html_e( 'Select calendar', 'appointment-hour-booking' ); ?></label></th>
                        <td>
                            <select id="cpapphourbk_id" name="cpapphourbk_id" onchange="ahb_update_shortcode(this.value);">
                                <?php
                                $myrows = $wpdb->get_results( "SELECT id, form_name FROM " . $wpdb->prefix . "cpappbk_forms" );
                                foreach ( $myrows as $item ) {
                                    if ( empty( $this->item ) ) $this->item = $item->id;
                                    echo '<option value="' . esc_attr( $item->id ) . '"' . selected( $item->id, $this->item, false ) . '>' . esc_html( $item->form_name ) . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th><label for="whereto"><?php esc_html_e( 'Where to publish it?', 'appointment-hour-booking' ); ?></label></th>
                        <td>
                            <select name="whereto" id="whereto" onchange="mvpublish_displayoption(this);">
                                <option value="0"><?php esc_html_e( 'Into a new page', 'appointment-hour-booking' ); ?></option>
                                <option value="1"><?php esc_html_e( 'Into a new post', 'appointment-hour-booking' ); ?></option>
                                <option value="2"><?php esc_html_e( 'Into an existing page', 'appointment-hour-booking' ); ?></option>
                                <option value="3"><?php esc_html_e( 'Into an existing post', 'appointment-hour-booking' ); ?></option>
                                <option value="4" style="color:#999999"><?php esc_html_e( 'Widget in a sidebar, header or footer - upgrade required -', 'appointment-hour-booking' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top" id="posttitle_row">
                        <th><label for="posttitle"><?php esc_html_e( 'Page/Post Title', 'appointment-hour-booking' ); ?></label></th>
                        <td>
                            <input type="text" name="posttitle" id="posttitle" value="<?php echo esc_attr__( 'Booking Form', 'appointment-hour-booking' ); ?>" />
                        </td>
                    </tr>
                    <tr valign="top" id="publishstatus_row">
                        <th><label for="publishstatus"><?php esc_html_e( 'Initial Status', 'appointment-hour-booking' ); ?></label></th>
                        <td>
                            <select name="publishstatus" id="publishstatus">
                                <option value="draft"><?php esc_html_e( 'Draft (Preview first)', 'appointment-hour-booking' ); ?></option>
                                <option value="publish"><?php esc_html_e( 'Published immediately', 'appointment-hour-booking' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top" id="select_page_row" style="display:none">
                        <th><label for="publishpage"><?php esc_html_e( 'Select existing page', 'appointment-hour-booking' ); ?></label></th>
                        <td>
                            <?php
                            wp_dropdown_pages( array(
                                'name'              => 'publishpage',
                                'id'                => 'publishpage',
                                'show_option_none'  => esc_html__( '— Select a page —', 'appointment-hour-booking' ),
                                'option_none_value' => '0',
                            ) );
                            ?>
                        </td>
                    </tr>
                    <tr valign="top" id="select_post_row" style="display:none">
                        <th><label for="publishpost"><?php esc_html_e( 'Select existing post', 'appointment-hour-booking' ); ?></label></th>
                        <td>
                            <select name="publishpost" id="publishpost">
                                <?php
                                $posts_list = get_posts( array( 'numberposts' => 100, 'post_status' => array( 'publish', 'draft' ) ) );
                                foreach ( $posts_list as $p ) {
                                    echo '<option value="' . esc_attr( $p->ID ) . '">' . esc_html( $p->post_title ) . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th><label><?php esc_html_e( 'Quick Shortcode', 'appointment-hour-booking' ); ?></label></th>
                        <td>
                            <div style="border: 1px dotted black; background-color: #FFFACD; padding: 15px; font-weight: bold; margin: 10px; display: flex; align-items: center; justify-content: space-between; max-width: 450px;">
                                <span id="ahb_shortcode_text">[<?php echo esc_html($this->shorttag); ?> id="<?php echo intval($this->item); ?>"]</span>
                                <button type="button" class="button button-small" onclick="ahb_copy_shortcode(this);">
                                    <?php esc_html_e('Copy', 'appointment-hour-booking'); ?>
                                </button>
                            </div>
                            <p class="description"><?php esc_html_e( 'You can also paste this shortcode directly into any page, post, or builder (Gutenberg / Elementor).', 'appointment-hour-booking' ); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>
            <hr />
            <div class="ahb-buttons-container">
                <input type="submit" id="subbtnnow" value="<?php esc_attr_e( 'Publish Calendar', 'appointment-hour-booking' ); ?>" class="button button-primary" style="float:right;" />
                <div class="clear"></div>
            </div>
        </div>
    </div>
<?php endif; ?>
</form>

<script type="text/javascript">
function ahb_update_shortcode(calId) {
    var codeEl = document.getElementById('ahb_shortcode_text');
    if (codeEl) {
        codeEl.textContent = '[<?php echo esc_js( $this->shorttag ); ?> id="' + parseInt(calId, 10) + '"]';
    }
}

function ahb_copy_shortcode(btn) {
    var codeEl = document.getElementById('ahb_shortcode_text');
    if (!codeEl) return;

    var text = (codeEl.textContent || codeEl.innerText).trim();

    function showCopiedFeedback() {
        var originalText = btn.getAttribute('data-original') || btn.textContent;
        btn.setAttribute('data-original', originalText);
        btn.textContent = '✓ Copied!';
        setTimeout(function() {
            btn.textContent = originalText;
        }, 2000);
    }

    // 1. Si el navegador permite navigator.clipboard (HTTPS / localhost)
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(showCopiedFeedback).catch(function() {
            ahb_fallback_copy(text, showCopiedFeedback);
        });
    } else {
        // 2. Respaldo compatible con sitios HTTP y navegadores antiguos
        ahb_fallback_copy(text, showCopiedFeedback);
    }
}

function ahb_fallback_copy(text, callback) {
    var tempTextArea = document.createElement('textarea');
    tempTextArea.value = text;
    
    // Evita que la página haga scroll al insertar el elemento
    tempTextArea.style.position = 'fixed';
    tempTextArea.style.top = '0';
    tempTextArea.style.left = '-9999px';
    tempTextArea.setAttribute('readonly', '');
    
    document.body.appendChild(tempTextArea);
    tempTextArea.focus();
    tempTextArea.select();

    try {
        var successful = document.execCommand('copy');
        if (successful && typeof callback === 'function') {
            callback();
        }
    } catch (err) {
        console.error('No se pudo copiar el shortcode:', err);
    }

    document.body.removeChild(tempTextArea);
}

function mvpublish_displayoption(sel) {
    var val = sel.value;
    document.getElementById("posttitle_row").style.display = (val === '0' || val === '1') ? '' : 'none';
    document.getElementById("publishstatus_row").style.display = (val === '0' || val === '1') ? '' : 'none';
    document.getElementById("select_page_row").style.display = (val === '2') ? '' : 'none';
    document.getElementById("select_post_row").style.display = (val === '3') ? '' : 'none';

    if (val === '4') {
        alert('Widget option available only in commercial versions. Upgrade required for this option.');
        sel.value = '0';
        mvpublish_displayoption(sel);
    }
}
</script>