<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CP_AppBooking_GDPR {

    /** @var CP_AppBookingPlugin */
    private $plugin;

    const CRON_HOOK = 'cpappb_gdpr_daily_retention';

    public function __construct( $plugin ) {
        $this->plugin = $plugin;
    }

    public function init() {
        // WordPress Core Privacy Tools (WP 4.9.6+)
        add_action( 'admin_init', array( $this, 'add_privacy_policy_content' ) );
        add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_exporter' ) );
        add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_eraser' ) );
        add_action( 'cpappb_item_deleted', array( $this, 'cleanup_files_on_delete' ), 10, 1 );

        // Automated data retention cron
        add_action( self::CRON_HOOK, array( $this, 'run_retention_cleanup' ) );
        $this->sync_cron_schedule();
    }

    /**
     * Centralized GDPR option getter with safe defaults and backward compatibility
     * with the legacy cp_cpappb_storeip option.
     */
    public static function get_setting( $key, $default = '' ) {
        $saved = get_option( 'cp_cpappb_gdpr_settings', array() );
        if ( ! is_array( $saved ) ) {
            $saved = array();
        }

        if ( isset( $saved[ $key ] ) ) {
            return $saved[ $key ];
        }

        // Preserve legacy IP-disabled setting if GDPR settings have not been saved yet
        if ( $key === 'ip_mode' ) {
            $legacy_default = defined( 'CP_APPBOOK_DEFAULT_track_IP' ) ? CP_APPBOOK_DEFAULT_track_IP : true;
            $legacy_storeip = get_option( 'cp_cpappb_storeip', $legacy_default );
            if ( $legacy_storeip === '' || $legacy_storeip === false || $legacy_storeip === '0' ) {
                return 'none';
            }
            return 'anonymize';
        }

        $defaults = array(
            'ip_mode'            => 'anonymize', // 'full', 'anonymize', 'none'
            'retention_days'     => 0,           // 0 = disabled
            'retention_action'   => 'anonymize', // 'anonymize' or 'delete'
            'erasure_action'     => 'anonymize', // 'anonymize' or 'delete'
            'delete_uploads'     => '1',         // Delete uploaded files on erase/retention
            'ai_gdpr_mode'       => 'redact',    // 'redact', 'block', 'allow'
            'captcha_cookieless' => '1',         // Transient-based, zero cookies/sessions
        );

        return isset( $defaults[ $key ] ) ? $defaults[ $key ] : $default;
    }

    /**
     * Format or suppress the visitor IP according to GDPR settings.
     */
    public static function get_processed_ip( $raw_ip = '' ) {
        $mode = self::get_setting( 'ip_mode', 'anonymize' );
        if ( $mode === 'none' ) {
            return '';
        }

        if ( $raw_ip === '' && ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $raw_ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
        }

        if ( $raw_ip === '' ) {
            return '';
        }

        if ( $mode === 'anonymize' && function_exists( 'wp_privacy_anonymize_ip' ) ) {
            return wp_privacy_anonymize_ip( $raw_ip );
        }

        return $raw_ip;
    }

    /**
     * Redact PII before transmitting booking text to external AI services.
     */
    public static function sanitize_for_ai( $text ) {
        $text = preg_replace( '/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/', '[REDACTED_EMAIL]', $text );
        $text = preg_replace( '/\b(?:\d{1,3}\.){3}\d{1,3}\b/', '[REDACTED_IP]', $text );
        $text = preg_replace( '/(?:\+?\d[\d\-\.\s\(\)]{6,}\d)/', '[REDACTED_PHONE]', $text );
        return $text;
    }

    /**
     * Register suggested privacy policy text in Settings -> Privacy -> Policy Guide.
     */
    public function add_privacy_policy_content() {
        if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
            return;
        }

        $ip_mode        = self::get_setting( 'ip_mode', 'anonymize' );
        $retention_days = intval( self::get_setting( 'retention_days', 0 ) );
        $cookieless     = ( self::get_setting( 'captcha_cookieless', '1' ) === '1' );
        $ai_enabled     = ( get_option( 'cpappb_ai_reply_enabled', '0' ) === '1' && self::get_setting( 'ai_gdpr_mode', 'redact' ) !== 'block' );

        $content  = '<h3>' . esc_html__( 'Appointment Bookings & Personal Data', 'appointment-hour-booking' ) . '</h3>';
        $content .= '<p>' . esc_html__( 'When you submit an appointment booking through this website, we collect the information entered into the booking form (such as your email address, selected service, appointment date and time, and any additional form fields or uploaded files) in order to schedule and manage your appointment.', 'appointment-hour-booking' ) . '</p>';

        if ( $ip_mode === 'full' ) {
            $content .= '<p>' . esc_html__( 'For security and anti-spam purposes, your IP address is recorded with your booking submission.', 'appointment-hour-booking' ) . '</p>';
        } elseif ( $ip_mode === 'anonymize' ) {
            $content .= '<p>' . esc_html__( 'For security and anti-spam purposes, an anonymized version of your IP address is recorded with your booking submission.', 'appointment-hour-booking' ) . '</p>';
        }

        if ( $retention_days > 0 ) {
            $action_label = ( self::get_setting( 'retention_action', 'anonymize' ) === 'delete' )
                ? __( 'permanently deleted', 'appointment-hour-booking' )
                : __( 'anonymized', 'appointment-hour-booking' );
            $content .= '<p>' . sprintf(
                /* translators: 1: number of days, 2: action */
                esc_html__( 'Completed appointment records are automatically %2$s after %1$d days.', 'appointment-hour-booking' ),
                $retention_days,
                $action_label
            ) . '</p>';
        }

        $content .= '<h3>' . esc_html__( 'Anti-Spam CAPTCHA & Cookies', 'appointment-hour-booking' ) . '</h3>';
        if ( $cookieless ) {
            $content .= '<p>' . esc_html__( 'When CAPTCHA anti-spam protection is enabled on a booking form, verification is handled via temporary server-side tokens without setting browser cookies or PHP sessions.', 'appointment-hour-booking' ) . '</p>';
        } else {
            $content .= '<p>' . esc_html__( 'When CAPTCHA anti-spam protection is enabled on a booking form, a strictly necessary PHP session identifier and temporary verification cookie are used to validate human submissions.', 'appointment-hour-booking' ) . '</p>';
        }

        if ( $ai_enabled ) {
            $content .= '<h3>' . esc_html__( 'AI-Assisted Customer Replies', 'appointment-hour-booking' ) . '</h3>';
            $content .= '<p>' . esc_html__( 'Site administrators may use an external AI text generation service (Pollinations.ai) to draft replies to booking inquiries. Personal identifiers such as email addresses, phone numbers, and IP addresses are redacted prior to transmission when redaction is enabled.', 'appointment-hour-booking' ) . '</p>';
        }

        wp_add_privacy_policy_content(
            'Appointment Hour Booking',
            wp_kses_post( wpautop( $content, false ) )
        );
    }

    /**
     * Register WP Personal Data Exporter.
     */
    public function register_exporter( $exporters ) {
        $exporters['appointment-hour-booking'] = array(
            'exporter_friendly_name' => __( 'Appointment Hour Booking', 'appointment-hour-booking' ),
            'callback'               => array( $this, 'export_personal_data' ),
        );
        return $exporters;
    }

    public function export_personal_data( $email_address, $page = 1 ) {
        global $wpdb;

        $email_address = sanitize_email( $email_address );
        $per_page      = 50;
        $page          = max( 1, intval( $page ) );
        $offset        = ( $page - 1 ) * $per_page;
        $table         = $wpdb->prefix . $this->plugin->table_messages;
        $like_email    = '%' . $wpdb->esc_like( $email_address ) . '%';

        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table} WHERE LOWER(notifyto) = LOWER(%s) OR posted_data LIKE %s ORDER BY id ASC LIMIT %d OFFSET %d",
            $email_address,
            $like_email,
            $per_page,
            $offset
        ) );

        $export_items = array();

        foreach ( $rows as $row ) {
            $posted = maybe_unserialize( $row->posted_data );
            if ( ! $this->row_matches_email( $row, $posted, $email_address ) ) {
                continue;
            }

            $item_data = array(
                array( 'name' => __( 'Booking ID', 'appointment-hour-booking' ), 'value' => $row->id ),
                array( 'name' => __( 'Submission Date', 'appointment-hour-booking' ), 'value' => $row->time ),
                array( 'name' => __( 'Email', 'appointment-hour-booking' ), 'value' => $row->notifyto ),
            );

            if ( ! empty( $row->ipaddr ) ) {
                $item_data[] = array( 'name' => __( 'IP Address', 'appointment-hour-booking' ), 'value' => $row->ipaddr );
            }

            if ( is_array( $posted ) && ! empty( $posted['apps'] ) && is_array( $posted['apps'] ) ) {
                $slots = array();
                foreach ( $posted['apps'] as $app ) {
                    $status  = empty( $app['cancelled'] ) ? __( 'Approved', 'appointment-hour-booking' ) : $app['cancelled'];
                    $service = ! empty( $app['service'] ) ? ' - ' . $app['service'] : '';
                    $quant   = ( isset( $app['quant'] ) && intval( $app['quant'] ) > 1 ) ? ' x' . intval( $app['quant'] ) : '';
                    $slots[] = trim( ( isset( $app['date'] ) ? $app['date'] : '' ) . ' ' . ( isset( $app['slot'] ) ? $app['slot'] : '' ) . $service . $quant . ' (' . $status . ')' );
                }
                $item_data[] = array( 'name' => __( 'Booked Appointments', 'appointment-hour-booking' ), 'value' => implode( '; ', $slots ) );
            }

            if ( ! empty( $row->data ) ) {
                $item_data[] = array( 'name' => __( 'Submission Details', 'appointment-hour-booking' ), 'value' => $row->data );
            }

            $export_items[] = array(
                'group_id'    => 'cpappb_bookings',
                'group_label' => __( 'Appointment Bookings', 'appointment-hour-booking' ),
                'item_id'     => 'booking-' . $row->id,
                'data'        => $item_data,
            );
        }

        return array(
            'data' => $export_items,
            'done' => count( $rows ) < $per_page,
        );
    }

    /**
     * Register WP Personal Data Eraser.
     */
    public function register_eraser( $erasers ) {
        $erasers['appointment-hour-booking'] = array(
            'eraser_friendly_name' => __( 'Appointment Hour Booking', 'appointment-hour-booking' ),
            'callback'             => array( $this, 'erase_personal_data' ),
        );
        return $erasers;
    }

    public function erase_personal_data( $email_address, $page = 1 ) {
        global $wpdb;

        $email_address = sanitize_email( $email_address );
        $per_page      = 50;
        $table         = $wpdb->prefix . $this->plugin->table_messages;
        $like_email    = '%' . $wpdb->esc_like( $email_address ) . '%';
        $action        = self::get_setting( 'erasure_action', 'anonymize' );

        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table} WHERE LOWER(notifyto) = LOWER(%s) OR posted_data LIKE %s ORDER BY id ASC LIMIT %d",
            $email_address,
            $like_email,
            $per_page
        ) );

        $items_removed  = false;
        $items_retained = false;
        $messages       = array();

        foreach ( $rows as $row ) {
            $posted = maybe_unserialize( $row->posted_data );
            if ( ! $this->row_matches_email( $row, $posted, $email_address ) ) {
                continue;
            }

            if ( $action === 'delete' ) {
                $this->delete_booking_row( $row, $posted );
                $items_removed = true;
            } else {
                $this->anonymize_booking_row( $row, $posted );
                $items_removed  = true;
                $items_retained = true;
            }
        }

        if ( $items_removed ) {
            do_action( 'cpappb_cache_clean', 0 );
        }

        if ( $items_retained ) {
            $messages[] = __( 'Appointment dates, times, services, and statuses were retained in anonymized form to preserve calendar availability integrity; all personal details and uploaded files were removed.', 'appointment-hour-booking' );
        }

        return array(
            'items_removed'  => $items_removed,
            'items_retained' => $items_retained,
            'messages'       => $messages,
            'done'           => ( count( $rows ) < $per_page || ! $items_removed ),
        );
    }

    /**
     * Automated WP-Cron retention cleanup (skips bookings that still have future appointments).
     */
    public function run_retention_cleanup() {
        global $wpdb;

        $days = intval( self::get_setting( 'retention_days', 0 ) );
        if ( $days <= 0 ) {
            return 0;
        }

        $cutoff_datetime   = gmdate( 'Y-m-d H:i:s', strtotime( '-' . $days . ' days' ) );
        $cutoff_date       = gmdate( 'Y-m-d', strtotime( '-' . $days . ' days' ) );
        $action            = self::get_setting( 'retention_action', 'anonymize' );
        $table             = $wpdb->prefix . $this->plugin->table_messages;
        $blocked_indicator = isset( $this->plugin->blocked_by_admin_indicator ) ? $this->plugin->blocked_by_admin_indicator : 'BLOCKED@BY.ADMIN';

        if ( $action === 'anonymize' ) {
            $rows = $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM {$table} WHERE time < %s AND notifyto <> %s AND posted_data NOT LIKE %s ORDER BY id ASC LIMIT 200",
                $cutoff_datetime,
                $blocked_indicator,
                '%' . $wpdb->esc_like( 'gdpr_anonymized' ) . '%'
            ) );
        } else {
            $rows = $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM {$table} WHERE time < %s AND notifyto <> %s ORDER BY id ASC LIMIT 200",
                $cutoff_datetime,
                $blocked_indicator
            ) );
        }

        $processed = 0;

        foreach ( $rows as $row ) {
            $posted = maybe_unserialize( $row->posted_data );

            if ( is_array( $posted ) && ! empty( $posted['gdpr_anonymized'] ) && $action === 'anonymize' ) {
                continue;
            }

            if ( is_array( $posted ) && ! empty( $posted['apps'] ) && is_array( $posted['apps'] ) ) {
                $has_recent_or_future_slot = false;
                foreach ( $posted['apps'] as $app ) {
                    if ( ! empty( $app['date'] ) && $app['date'] > $cutoff_date ) {
                        $has_recent_or_future_slot = true;
                        break;
                    }
                }
                if ( $has_recent_or_future_slot ) {
                    continue;
                }
            }

            if ( $action === 'delete' ) {
                $this->delete_booking_row( $row, $posted );
            } else {
                $this->anonymize_booking_row( $row, $posted );
            }
            $processed++;
        }

        if ( $processed > 0 ) {
            do_action( 'cpappb_cache_clean', 0 );
        }

        return $processed;
    }

    /**
     * One-click admin utility: anonymize or clear all previously stored IP addresses.
     */
    public function purge_existing_ips( $mode = 'anonymize' ) {
        global $wpdb;
        $table = $wpdb->prefix . $this->plugin->table_messages;

        if ( $mode === 'none' ) {
            return $wpdb->query( "UPDATE {$table} SET ipaddr = '' WHERE ipaddr <> ''" );
        }

        $rows    = $wpdb->get_results( "SELECT id, ipaddr FROM {$table} WHERE ipaddr <> ''" );
        $updated = 0;
        foreach ( $rows as $row ) {
            $anon = function_exists( 'wp_privacy_anonymize_ip' ) ? wp_privacy_anonymize_ip( $row->ipaddr ) : '';
            if ( $anon !== $row->ipaddr ) {
                $wpdb->update( $table, array( 'ipaddr' => $anon ), array( 'id' => $row->id ), array( '%s' ), array( '%d' ) );
                $updated++;
            }
        }
        return $updated;
    }

    public function sync_cron_schedule() {
        $days      = intval( self::get_setting( 'retention_days', 0 ) );
        $scheduled = wp_next_scheduled( self::CRON_HOOK );

        if ( $days > 0 && ! $scheduled ) {
            wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::CRON_HOOK );
        } elseif ( $days <= 0 && $scheduled ) {
            wp_clear_scheduled_hook( self::CRON_HOOK );
        }
    }

    public static function deactivate() {
        wp_clear_scheduled_hook( self::CRON_HOOK );
    }

    /**
     * Anonymize a single booking record by ID (used by Booking Orders UI).
     */
    public function anonymize_booking_by_id( $id ) {
        global $wpdb;
        $id = intval( $id );
        if ( $id <= 0 ) {
            return false;
        }

        $table = $wpdb->prefix . $this->plugin->table_messages;
        $row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
        if ( ! $row ) {
            return false;
        }

        $posted = maybe_unserialize( $row->posted_data );
        if ( is_array( $posted ) && ! empty( $posted['gdpr_anonymized'] ) ) {
            return true;
        }

        $this->anonymize_booking_row( $row, $posted );
        do_action( 'cpappb_cache_clean', intval( $row->formid ) );
        return true;
    }

    /**
     * Remove uploaded files from wp-content/uploads before a booking row is deleted.
     */
    public function cleanup_files_on_delete( $id ) {
        global $wpdb;
        if ( self::get_setting( 'delete_uploads', '1' ) !== '1' ) {
            return;
        }

        $table  = $wpdb->prefix . $this->plugin->table_messages;
        $posted = $wpdb->get_var( $wpdb->prepare( "SELECT posted_data FROM {$table} WHERE id = %d", intval( $id ) ) );
        if ( ! empty( $posted ) ) {
            $this->delete_uploaded_files( maybe_unserialize( $posted ) );
        }
    }

    private function row_matches_email( $row, $posted, $email_address ) {
        if ( strcasecmp( trim( (string) $row->notifyto ), $email_address ) === 0 ) {
            return true;
        }
        if ( is_array( $posted ) ) {
            foreach ( $posted as $val ) {
                if ( is_string( $val ) && strcasecmp( trim( $val ), $email_address ) === 0 ) {
                    return true;
                }
            }
        }
        return false;
    }

    private function delete_booking_row( $row, $posted ) {
        global $wpdb;
        if ( self::get_setting( 'delete_uploads', '1' ) === '1' ) {
            $this->delete_uploaded_files( $posted );
        }
        $wpdb->delete(
            $wpdb->prefix . $this->plugin->table_messages,
            array( 'id' => intval( $row->id ) ),
            array( '%d' )
        );
    }

    private function anonymize_booking_row( $row, $posted ) {
        global $wpdb;

        if ( self::get_setting( 'delete_uploads', '1' ) === '1' ) {
            $this->delete_uploaded_files( $posted );
        }

        $safe_posted = array(
            'gdpr_anonymized'   => current_time( 'mysql' ),
            'formid'            => is_array( $posted ) && isset( $posted['formid'] ) ? $posted['formid'] : $row->formid,
            'formname'          => is_array( $posted ) && isset( $posted['formname'] ) ? $posted['formname'] : '',
            'request_timestamp' => is_array( $posted ) && isset( $posted['request_timestamp'] ) ? $posted['request_timestamp'] : $row->time,
            'final_price'       => is_array( $posted ) && isset( $posted['final_price'] ) ? $posted['final_price'] : '0.00',
            'final_price_short' => is_array( $posted ) && isset( $posted['final_price_short'] ) ? $posted['final_price_short'] : '0',
            'apps'              => is_array( $posted ) && isset( $posted['apps'] ) ? $posted['apps'] : array(),
            'paid'              => is_array( $posted ) && isset( $posted['paid'] ) ? $posted['paid'] : '',
            'payment_type'      => is_array( $posted ) && isset( $posted['payment_type'] ) ? $posted['payment_type'] : '',
        );

        if ( is_array( $posted ) ) {
            foreach ( $posted as $k => $v ) {
                if ( preg_match( '/^app_(service|status|duration|price|date|slot|starttime|endtime|quantity)_\d+$/', $k ) ) {
                    $safe_posted[ $k ] = $v;
                }
            }
        }

        $anon_summary = __( '[Personal data anonymized per GDPR request]', 'appointment-hour-booking' );
        if ( ! empty( $safe_posted['apps'] ) ) {
            $anon_summary .= "\n" . $this->plugin->get_appointments_text( $safe_posted['apps'] );
        }

        $wpdb->update(
            $wpdb->prefix . $this->plugin->table_messages,
            array(
                'ipaddr'      => '',
                'notifyto'    => '',
                'data'        => $anon_summary,
                'posted_data' => serialize( $safe_posted ),
            ),
            array( 'id' => intval( $row->id ) ),
            array( '%s', '%s', '%s', '%s' ),
            array( '%d' )
        );
    }

    private function delete_uploaded_files( $posted ) {
        if ( ! is_array( $posted ) ) {
            return;
        }
        $upload_dir = wp_upload_dir();
        $base_dir   = ! empty( $upload_dir['basedir'] ) ? realpath( $upload_dir['basedir'] ) : false;
        if ( ! $base_dir ) {
            return;
        }

        foreach ( $posted as $key => $val ) {
            if ( substr( (string) $key, -5 ) === '_link' && is_array( $val ) ) {
                foreach ( $val as $file_path ) {
                    $real = is_string( $file_path ) ? realpath( $file_path ) : false;
                    if ( $real && strpos( $real, $base_dir ) === 0 && is_file( $real ) ) {
                        wp_delete_file( $real );
                    }
                }
            }
        }
    }
}
