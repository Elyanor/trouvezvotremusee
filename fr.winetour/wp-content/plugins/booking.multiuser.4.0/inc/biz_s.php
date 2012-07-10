<?php
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
*/

if (  (! isset( $_GET['merchant_return_link'] ) ) && (! isset( $_GET['payed_booking'] ) ) && (!function_exists ('get_option')  )  ) { die('You do not have permission to direct access to this file !!!'); }
require_once(WPDEV_BK_PLUGIN_DIR. '/inc/lib_s.php' );
if (file_exists(WPDEV_BK_PLUGIN_DIR. '/inc/payments/index.php')) { require_once(WPDEV_BK_PLUGIN_DIR. '/inc/payments/index.php' ); }
if (file_exists(WPDEV_BK_PLUGIN_DIR. '/inc/biz_m.php')) { require_once(WPDEV_BK_PLUGIN_DIR. '/inc/biz_m.php' ); }

if (!class_exists('wpdev_bk_biz_s')) {
    class wpdev_bk_biz_s {
        
        var $wpdev_bk_biz_m;
        
        // Constructor
        function wpdev_bk_biz_s() {

            add_bk_action('wpdev_booking_settings_show_content', array(&$this, 'settings_menu_content'));
            add_bk_action('wpdev_booking_settings_top_menu_submenu_line', array(&$this, 'wpdev_booking_settings_top_menu_submenu_line'));

            

            add_filter('wpdev_booking_form', array(&$this, 'add_paypal_form'));                     // Filter for inserting paypal form
            add_action('wpdev_new_booking', array(&$this, 'show_paypal_form_in_ajax_request'),1,5); // Make showing Paypal in Ajax


            add_action('settings_advanced_set_time_format', array(&$this, 'settings_advanced_set_time_format'));    // Write General Settings
            add_action('settings_advanced_set_range_selections', array(&$this, 'settings_advanced_set_range_selections'));    // Write General Settings
            add_action('settings_advanced_set_fixed_time', array(&$this, 'settings_advanced_set_fixed_time'));    // Write General Settings

            add_bk_action('wpdev_bk_general_settings_cost_section', array(&$this, 'wpdev_bk_general_settings_cost_section'));          // Section of settings in general settings page
            add_bk_action('wpdev_bk_general_settings_pending_auto_cancelation', array(&$this, 'wpdev_bk_general_settings_pending_auto_cancelation'));          // Section of settings in general settings page


            // Resources settings //
            add_bk_action('resources_settings_table_headers', array($this, 'resources_settings_table_headers'));
            add_bk_action('resources_settings_table_footers', array($this, 'resources_settings_table_footers'));
            add_bk_action('resources_settings_table_collumns', array($this, 'resources_settings_table_collumns'));
            add_bk_filter('get_sql_4_update_bk_resources_cost', array(&$this, 'get_sql_4_update_bk_resources'));



            add_action('wpdev_bk_js_define_variables', array(&$this, 'js_define_variables') );      // Write JS variables
            add_action('wpdev_bk_js_write_files', array(&$this, 'js_write_files') );                // Write JS files

           
            
            add_filter('wpdev_booking_form_content', array(&$this, 'wpdev_booking_form_content'),10,2 );


            add_filter('wpdev_get_booking_cost', array(&$this, 'get_booking_cost'),10,4 );
            add_bk_filter('wpdev_get_bk_booking_cost', array(&$this, 'get_booking_cost'));

            add_filter('wpdev_booking_get_additional_info_to_dates', array(&$this, 'wpdev_booking_get_additional_info_to_dates'),10,2 );



            add_bk_action('wpdev_booking_activation', array(&$this, 'pro_activate'));
            add_bk_action('wpdev_booking_deactivation', array(&$this, 'pro_deactivate'));


            add_bk_action('wpdev_booking_post_inserted', array(&$this, 'booking_post_inserted'));
            add_bk_filter('get_booking_cost_from_db', array(&$this, 'get_booking_cost_from_db'));
            add_bk_filter('wpdev_get_payment_form', array(&$this, 'get_payment_form') );
            add_bk_filter('get_currency_info', array(&$this, 'get_currency_info') );



            add_bk_action('wpdev_show_autofill_button', array(&$this, 'wpdev_show_autofill_button'));          // Ajax POST request for updating remark

            add_bk_action('write_content_for_popups', array(&$this, 'premium_content_for_popups'));
            add_bk_action('wpdev_booking_emails_settings', array(&$this, 'wpdev_booking_emails_settings'));

            add_bk_action('wpdev_save_bk_cost', array(&$this, 'wpdev_save_bk_cost'));          // Ajax POST request for updating cost
            add_bk_action('wpdev_send_payment_request', array(&$this, 'wpdev_send_payment_request'));          // Ajax POST request for email sending payment request
            add_bk_action('wpdev_change_payment_status', array(&$this, 'wpdev_change_payment_status'));          // Ajax POST request for email sending payment request


            add_bk_action('check_pending_not_paid_auto_cancell_bookings', array(&$this, 'check_pending_not_paid_auto_cancell_bookings'));          //Check and delete all Pending not paid bookings, which older then a 1-n days


            add_bk_filter('get_sql_4_insert_bk_resources_fields_p', array(&$this, 'get_sql_4_insert_bk_resources_fields'));
            add_bk_filter('get_sql_4_insert_bk_resources_values_p', array(&$this, 'get_sql_4_insert_bk_resources_values'));


            //WTB AJOUT FONCTION
            add_bk_filter('is_need_to_validate_reservation', array(&$this, 'is_need_to_validate_reservation'));

             if ( class_exists('wpdev_bk_biz_m')) {
                    $this->wpdev_bk_biz_m = new wpdev_bk_biz_m();
            } else { $this->wpdev_bk_biz_m = false; }/**/
  
        }


     //   S U P P O R T     F U N C T I O N S    //////////////////////////////////////////////////////////////////////////////////////////////////


                // Reset to Payment form
                function reset_to_default_form($form_type ){
                       return '[calendar] \n\        
        <div style="text-align:left"> \n\
        <p>'. __('Start time', 'wpdev-booking').': [starttime]  '. __('End time', 'wpdev-booking').': [endtime]</p> \n\
        \n\
        <p>'. __('First Name (required)', 'wpdev-booking').':<br />  [text* name] </p> \n\
        \n\
        <p>'. __('Last Name (required)', 'wpdev-booking').':<br />  [text* secondname] </p> \n\
        \n\
        <p>'. __('Email (required)', 'wpdev-booking').':<br />  [email* email] </p> \n\
        \n\
        <p>'. __('Address (required)', 'wpdev-booking').':<br />  [text* address] </p>  \n\
         \n\
        <p>'. __('City(required)', 'wpdev-booking').':<br />  [text* city] </p>  \n\
         \n\
        <p>'. __('Post code(required)', 'wpdev-booking').':<br />  [text* postcode] </p>  \n\
         \n\
        <p>'. __('Country(required)', 'wpdev-booking').':<br />  [country] </p>  \n\
         \n\
        <p>'. __('Phone', 'wpdev-booking').':<br />  [text phone] </p> \n\
        \n\
        <p>'. __('Visitors', 'wpdev-booking').':<br />  [select visitors "1" "2" "3" "4"] '. __('Children', 'wpdev-booking').': [checkbox children ""]</p> \n\
        \n\
        <p>'. __('Details', 'wpdev-booking').':<br /> [textarea details] </p> \n\
        \n\
        <p>[captcha]</p> \n\
        \n\
        <p>[submit "'. __('Send', 'wpdev-booking').'"]</p> \n\
        </div>';
                 }



        // Get booking types from DB
        function get_booking_type($booking_id) {
            global $wpdb;
            $types_list = $wpdb->get_results($wpdb->prepare( "SELECT title, cost FROM ".$wpdb->prefix ."bookingtypes  WHERE booking_type_id = " . $booking_id ));
            return $types_list;
        }

        // Get cost of booking resource
        function get_cost_of_booking_resource($bk_type_id) {
            global $wpdb;
            $cost = $wpdb->get_var($wpdb->prepare( "SELECT cost FROM ".$wpdb->prefix ."bookingtypes  WHERE booking_type_id = " . $bk_type_id ));
            return (isset( $cost) ) ? $cost : 0 ;
        }

        // Get booking types from DB
        function get_booking_types() {
            global $wpdb;

            if ( class_exists('wpdev_bk_biz_l')) {  // If Business Large then get resources from that
                $types_list = apply_bk_filter('get_booking_types_hierarhy_linear',array() );
                for ($i = 0; $i < count($types_list); $i++) {
                    $types_list[$i]['obj']->count = $types_list[$i]['count'];
                    $types_list[$i] = $types_list[$i]['obj'];
                    //if ( ($booking_type_id != 0) && ($booking_type_id == $types_list[$i]->booking_type_id ) ) return $types_list[$i];
                }
                //if ($booking_type_id == 0)
                   
            } else $types_list = $wpdb->get_results($wpdb->prepare( "SELECT booking_type_id as id, title, cost FROM ".$wpdb->prefix ."bookingtypes  ORDER BY title" ));
            
            $types_list = apply_bk_filter('multiuser_resource_list', $types_list);
            return $types_list;
        }

        // Check if table exist
        function is_field_in_table_exists( $tablename , $fieldname) {
            global $wpdb;
            if (strpos($tablename, $wpdb->prefix) ===false) $tablename = $wpdb->prefix . $tablename ;
            $sql_check_table = "SHOW COLUMNS FROM " . $tablename ;

            $res = $wpdb->get_results($wpdb->prepare($sql_check_table));

            foreach ($res as $fld) {
                if ($fld->Field == $fieldname) return 1;
            }

            return 0;

        }
        // Check if index exist
        function is_index_in_table_exists( $tablename , $fieldindex) {
            global $wpdb;
            if (strpos($tablename, $wpdb->prefix) ===false) $tablename = $wpdb->prefix . $tablename ;
            $sql_check_table = "SHOW INDEX FROM ". $tablename ." WHERE Key_name = '".$fieldindex."'; ";
            $res = $wpdb->get_results($wpdb->prepare($sql_check_table));
            if (count($res)>0) return 1;
            else               return 0;
        }


        // Get currency description
        function get_currency_info( $payment_system = 'paypal'){

            if ($payment_system == 'paypal')
                 $cost_currency = get_bk_option( 'booking_paypal_curency' );
            elseif ($payment_system == 'sage')
                 $cost_currency = get_bk_option( 'booking_sage_curency' );
            elseif ($payment_system == 'ipay88')
                 $cost_currency = get_bk_option( 'booking_ipay88_curency' );
            else $cost_currency = get_bk_option( 'booking_paypal_curency' );

            if ($cost_currency == 'USD' ) $cost_currency = '$';
            elseif ($cost_currency == 'EUR' ) $cost_currency = '&euro;';
            elseif ($cost_currency == 'GBP' ) $cost_currency = '&#163;';
            elseif ($cost_currency == 'JPY' ) $cost_currency = '&#165;';
            else  $cost_currency = ' ' . $cost_currency . ' ';
            return $cost_currency;
        }



        // Get Fields and Values for Insert new resource
        function get_sql_4_insert_bk_resources_fields( $blank ){
          return ', cost ';
        }
        function get_sql_4_insert_bk_resources_values( $blank , $sufix ){
            $cost = 0;

            if (isset($_POST['type_parent_new'])){
               $cost = $this->get_booking_type( $_POST['type_parent_new'] ) ; // Get cost of parent element
              if (count($cost)>0) $cost = $cost[0]->cost;
              else                $cost = '0';
            }

            $update_values =  ' , '. $cost . ' ';

            return  $update_values;
        }

     //  C O S T    I n s e r t i n g    ///////////////////////////////////////////////////////////////////////////////////////

        //  Update C O S T    ---  Function call after booking is inserted or modificated in post request
        function booking_post_inserted($booking_id, $booking_type, $booking_days_count, $times_array){
               global $wpdb;

               // Check if total cost field exist and get cost from that field
               $fin_summ = apply_bk_filter('check_if_cost_exist_in_field', false, $_POST["form"], $booking_type );

               if ($fin_summ == false)
                    $summ        = $this->get_booking_cost( $booking_type, $booking_days_count, $times_array , $_POST["form"] );
               else $summ = $fin_summ;


               $summ = floatval(  $summ);
               $summ = round($summ,2);

                $update_sql = "UPDATE ".$wpdb->prefix ."booking AS bk SET bk.cost='$summ' WHERE bk.booking_id=$booking_id;";
                if ( false === $wpdb->query($wpdb->prepare( $update_sql ) ) ){
                    ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $bktype; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during updating cost in BD',__FILE__,__LINE__ ); ?></div>'; </script> <?php
                    die();
                }/**/

        }

        // Get Cost from DB
        function get_booking_cost_from_db($booking_cost, $booking_id) {
            global $wpdb;
            $slct_sql = "SELECT cost FROM ".$wpdb->prefix ."booking WHERE booking_id IN ($booking_id) LIMIT 0,1";
            $slct_sql_results  = $wpdb->get_results($wpdb->prepare( $slct_sql ));
            if ( count($slct_sql_results) > 0 ) { return $slct_sql_results[0]->cost; }
            return '';
        }

        // Check and delete all Pending not paid bookings, which older then a 1-n days
        function check_pending_not_paid_auto_cancell_bookings($bk_type) {

                if ( defined('WP_ADMIN') ) if ( WP_ADMIN === true )  return;
                $is_check_active   =  get_bk_option( 'booking_auto_cancel_pending_unpaid_bk_is_active' );   // Is this function Active
                if ($is_check_active != 'On') return;

                global $wpdb;
                $num_of_hours_ago  =  get_bk_option( 'booking_auto_cancel_pending_unpaid_bk_time' );        // Num of hours ago for specific booking

                // TODO: add here in a future possibility to cancel not ALL, but specific bookings with booking payment type: Error or Failed
                // Right now all bookings, which  have no successfuly payed status or pending are canceled.
                $labels_payment_status_ok = get_payment_status_ok();
                $labels_payment_status_ok = implode( ', ', $labels_payment_status_ok);

                $labels_payment_status_pending = get_payment_status_pending();
                $labels_payment_status_pending = implode( ', ', $labels_payment_status_pending);
                $labels_payment_status_ok .= ', ' . $labels_payment_status_pending;

                // Cancell only Pending, Old (hours) and not Paid bookings
                $slct_sql = "SELECT DISTINCT bk.booking_id as id, bk.modification_date as date,  dt.approved AS approved, bk.pay_status AS pay_status
                             FROM ".$wpdb->prefix ."booking AS bk

                             INNER JOIN ".$wpdb->prefix ."bookingdates as dt
                             ON    bk.booking_id = dt.booking_id

                              WHERE bk.pay_status NOT IN ( " . $labels_payment_status_ok . " ) AND
                                    dt.approved=0 AND
                                    bk.modification_date < ( NOW() - INTERVAL ".$num_of_hours_ago." HOUR ) ";

                $pending_not_paid  = $wpdb->get_results($wpdb->prepare( $slct_sql ));
                $approved_id = array();
                foreach ($pending_not_paid as $value) {
                   $approved_id []= $value->id;
                }
                $approved_id_str = join( ',', $approved_id);

                if ( count($approved_id)>0 ) {

                    // Send decline emails
                    $auto_cancel_pending_unpaid_bk_is_send_email =  get_bk_option( 'booking_auto_cancel_pending_unpaid_bk_is_send_email' );
                    if ($auto_cancel_pending_unpaid_bk_is_send_email == 'On') {
                        $auto_cancel_pending_unpaid_bk_email_reason  =  get_bk_option( 'booking_auto_cancel_pending_unpaid_bk_email_reason' );
                        foreach ($approved_id as $booking_id) {
                            sendDeclineEmails($booking_id,1, $auto_cancel_pending_unpaid_bk_email_reason );
                        }
                    }

                    // Auto cancellation
                    if ( false === $wpdb->query($wpdb->prepare( "DELETE FROM ".$wpdb->prefix ."bookingdates WHERE booking_id IN ($approved_id_str)") ) ){
                        ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $bk_type; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php echo 'Error during auto deleting dates at DB of pending bookings'; ?></div>'; </script> <?php
                        die();
                    }
                    if ( false === $wpdb->query($wpdb->prepare( "DELETE FROM ".$wpdb->prefix ."booking WHERE booking_id IN ($approved_id_str)") ) ){
                        ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $bk_type; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php echo 'Error auto deleting booking at DB of pending bookings' ; ?></div>'; </script> <?php
                        die();
                    }
                }

        }


     //  R E S O U R C E     T A B L E     C O S T    C o l l  u m n    ////////////////////////////////////////////////////////////////////////////

           // Show headers collumns
           function resources_settings_table_headers(){

              ?>
                <th style="width:80px;text-align:center;" rel="tooltip" class="tooltip_bottom"  title="<?php _e('Setting cost for the resource', 'wpdev-booking');?>">
                 <?php _e('Cost', 'wpdev-booking'); ?>
                        <?php echo ' <span style="font-weight:bold;color:#8F340E;">'; ?>
                          <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'day')    _e('/ day', 'wpdev-booking');    ?>
                          <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'night')  _e('/ night', 'wpdev-booking');  ?>
                          <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'fixed')  _e('fixed', 'wpdev-booking');?>
                          <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'hour')   _e('/ hour', 'wpdev-booking');   ?>
                        <?php echo "</span>"; ?>

                </th>
              <?php
           }

           // Show footers collumns
           function resources_settings_table_footers(){
                if ((isset($_POST['submit_resources']))) {
                    update_bk_option( 'booking_paypal_price_period' , $_POST['paypal_price_period'] );
                }
              ?>
                <td style="width:80px;border-top: 1px solid #ccc;text-align:center;font-weight:bold;">
                                 <span class="description"><?php
                                    _e('Setting cost', 'wpdev-booking');
                                    ?>: 
                                     <select id="paypal_price_period" name="paypal_price_period" style="width:75px;">
                                         <option <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'day') echo "selected"; ?> value="day"><?php _e('per day', 'wpdev-booking'); ?></option>
                                         <option <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'night') echo "selected"; ?> value="night"><?php _e('per night', 'wpdev-booking'); ?></option>
                                         <option <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'fixed') echo "selected"; ?> value="fixed"><?php _e('fixed', 'wpdev-booking'); ?></option>
                                         <?php //if ( class_exists('wpdev_bk_time')) { ?>
                                         <option <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'hour') echo "selected"; ?> value="hour"><?php _e('per hour', 'wpdev-booking'); ?></option>
                                         <?php //} ?>
                                     </select>
                                    <?php
                                   // _e('for resources', 'wpdev-booking');
                                 ?></span>
                </td>
              <?php
           }


           // Show Resources Collumns
           function resources_settings_table_collumns( $bt, $all_id, $alternative_color ){
                ?>
                    <?php // Show Costs  ?>
                    <td style="text-align:center;font-weight:bold;<?php if ($bt->cost<=0) { echo 'color:#ccc;'; } ?>" <?php echo $alternative_color; ?> >
                         <?php echo ' <span style="font-weight:normal;font-size:11px;">';  echo $this->get_currency_info(); echo "</span>"; ?>
                        <input  maxlength="17" type="text"
                                        style="width:50px;font-size:11px;;font-weight:bold;<?php if ($bt->cost<=0) { echo 'border-color:#C1BDBD;color:#ccc;'; } ?>"
                                        value="<?php echo $bt->cost; ?>"
                                        name="resource_cost<?php echo $bt->id; ?>" id="resource_cost<?php echo $bt->id; ?>" />
                    </td>



                <?php
           }

                    // Update SQL dfor editing bk resources
                    function get_sql_4_update_bk_resources($blank, $bt){

                        $sql_res = " , cost = '".$_POST['resource_cost'.$bt->id]."' ";
                        return $sql_res;
                    }


     //   C L I E N T     S I D E    //////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // Define JavaScript variables
        function js_define_variables(){
            ?>
                    <script  type="text/javascript">
                        var days_select_count= <?php if (get_bk_option( 'booking_range_selection_days_count') == '') echo '5'; else echo (0+get_bk_option( 'booking_range_selection_days_count')); ?>;
                        var range_start_day= <?php if (get_bk_option( 'booking_range_start_day') == '') echo '-1'; else echo (0+get_bk_option( 'booking_range_start_day')); ?>;
                        var days_select_count_dynamic= <?php if (get_bk_option( 'booking_range_selection_days_count_dynamic') == '') echo '0'; else echo (0+get_bk_option( 'booking_range_selection_days_count_dynamic')); ?>;
                        var range_start_day_dynamic= <?php if (get_bk_option( 'booking_range_start_day_dynamic') == '') echo '-1'; else echo (0+get_bk_option( 'booking_range_start_day_dynamic')); ?>;
                        <?php if ( get_bk_option( 'booking_range_selection_is_active') == 'On' ) { ?>
                            <?php if ( get_bk_option( 'booking_range_selection_type') == 'dynamic' ) { ?>
                                var is_select_range = 0;
                                wpdev_bk_is_dynamic_range_selection = true;
                                multiple_day_selections = 0;  // if we set range selections so then nomultiple selections
                            <?php } else { ?>
                                var is_select_range = 1;
                            <?php }  ?>
                        <?php } else { ?>
                            var is_select_range = 0;
                        <?php }  ?>
                        var bk_discreet_days_in_range_slections = [<?php
                            if ( get_bk_option( 'booking_range_selection_type') == 'dynamic' ) {
                                // TODO: Get from settings number of discreet days, which can be setup fr range selections.
                                //for ($i = 0; $i < 5; $i++) { }
                                $range_selection_days_count_dynamic_specific   = get_bk_option( 'booking_range_selection_days_specific_num_dynamic');
                                echo $range_selection_days_count_dynamic_specific;
                            }
                        ?>]; <?php // list of number of days, which only can be selectable inside of calendar for exmaple: 4 days or 7 days. ?>
                        var bk_max_days_in_range_slections = <?php echo (0+get_bk_option( 'booking_range_selection_days_max_count_dynamic')); ?>;   // Maximum days selection in range selections
                        var message_starttime_error = '<?php echo esc_js(__('Start Time is invalid, probably by requesting time(s) already booked, or already in the past!', 'wpdev-booking')); ?>';
                        var message_endtime_error   =   '<?php echo esc_js(__('End Time is invalid, probably by requesting time(s) already booked, or already in the past, or less then start time if only 1 day selected.!', 'wpdev-booking')); ?>';
                        var message_rangetime_error   =   '<?php echo esc_js(__('Probably by requesting time(s) already booked, or already in the past!', 'wpdev-booking')); ?>';
                        var message_durationtime_error   =   '<?php echo esc_js(__('Probably by requesting time(s) already booked, or already in the past!', 'wpdev-booking')); ?>';
                        <?php if ( get_bk_option( 'booking_recurrent_time' ) !== 'On') { ?>
                            var is_booking_recurrent_time = false;
                        <?php } else { ?>
                            var is_booking_recurrent_time = true ;
                        <?php } ?>

                    </script>
            <?php
        }

        // Write JS files
        function js_write_files(){
            wp_enqueue_script ('biz_s', WPDEV_BK_PLUGIN_URL . '/inc/js/biz_s.js');
        }

        //    A d d    E l e m e n t s     t o     B o o k  i n g     F o r m   //
            // Add Paypal place for inserting to the Booking FORM ////////////////////
            function add_paypal_form($form_content) {

                $is_turned_off = apply_bk_filter('is_payment_forms_off', true);
                if ($is_turned_off)  return $form_content ;

                if (strpos($_SERVER['REQUEST_URI'],'booking.php')!==false) return $form_content ;

                $str_start = strpos($form_content, 'booking_form');
                $str_fin = strpos($form_content, '"', $str_start);

                $my_boook_type = substr($form_content,$str_start, ($str_fin-$str_start) );

                $form_content .= '<div  id="paypal'.$my_boook_type.'"></div>';
                return $form_content;
            }


            // Add  F I X E D   R a n g e    T I M E   to   Form        //////////////
            function wpdev_booking_form_content ($my_form_content, $bk_type){
                if( get_bk_option( 'booking_range_selection_time_is_active') == 'On' )  {
                    if ( strpos($my_form_content, 'name="starttime') !== false )  $my_form_content = str_replace( 'name="starttime', 'name="advanced_stime', $my_form_content);
                    if ( strpos($my_form_content, 'name="endtime') !== false )  $my_form_content = str_replace( 'name="endtime', 'name="advanced_etime', $my_form_content);

                    $my_form_content .= '<input name="starttime'.$bk_type.'"  id="starttime'.$bk_type.'" type="text" value="'.get_bk_option( 'booking_range_selection_start_time').'" style="display:none;">';
                    $my_form_content .= '<input name="endtime'.$bk_type.'"  id="endtime'.$bk_type.'" type="text" value="'.get_bk_option( 'booking_range_selection_end_time').'"  style="display:none;">';
                }
                return $my_form_content;
            }


        // A D V A N C E D     I N F O      I N T O      F O R M   ///////////////////////////////////////////////////
        function wpdev_booking_get_additional_info_to_dates($blank, $type_id ) {  $start_script_code = '';
return '';
            // TODO: stop working here according names in tooltips
            global $wpdb;


             $sql_req =  "SELECT DISTINCT dt.booking_date, bk.*
                          FROM ".$wpdb->prefix ."bookingdates as dt
                          INNER JOIN ".$wpdb->prefix ."booking as bk
                         ON    bk.booking_id = dt.booking_id
                         WHERE  dt.booking_date >= CURDATE()  AND bk.booking_type = $type_id
                         ORDER BY dt.booking_date" ;
             $results = $wpdb->get_results($wpdb->prepare( $sql_req ));
//debuge($results)     ;
             $return_array = array();
             foreach ($results as $value) {

                 $form_data = get_form_content($value->form, $type_id);

                 $single_day_info =array();
                 foreach ($form_data['_all_'] as $kkey => $vvalue) {
                     $kkey = substr($kkey, 0 , -1*strlen( $type_id . '' ));
                     $single_day_info[$kkey] = $vvalue;
                 }
                 //$return_array[$value->booking_date] = $single_day_info;
                 //$return_array[$value->booking_date]['id'] = $value->booking_id;
                 //$return_array[$value->booking_date]['cost'] = $value->cost ;


                 $key_a = explode(' ', $value->booking_date);
                 $date_key =  $key_a[0];
                 if (isset($return_array[$date_key .':' .$value->booking_id ]['id']))
                         $return_array[$date_key .':' .$value->booking_id ]['dates'] .= $value->booking_date . ',';
                 else {
                     $return_array[$date_key .':' .$value->booking_id ] = $single_day_info;
                     $return_array[$date_key .':' .$value->booking_id ]['id'] = $value->booking_id;
                     $return_array[$date_key .':' .$value->booking_id ]['cost'] = $value->cost ;
                     $return_array[$date_key .':' .$value->booking_id ]['dates'] = $value->booking_date . ',';
                 }

                 $my_time_tag = explode(':', $key_a[1]);
                 if ($my_time_tag[2] == '01') $return_array[$date_key .':' .$value->booking_id ]['starttime'] = $my_time_tag[0] . ':' . $my_time_tag[1];
                 if ($my_time_tag[2] == '02') $return_array[$date_key .':' .$value->booking_id ]['endtime'] = $my_time_tag[0] . ':' . $my_time_tag[1];

             }
//debuge($return_array)     ;die;
             $start_script_code .= " dates_additional_info[". $type_id ."] = []; ";

             foreach ($return_array as $key=>$value) {
                 $key_a = explode(':', $key);
                 $date_key = explode('-', $key_a[0]);
                 //$my_time_tag = explode(':', $key_a[1]);

                 $my_day_tag =   ($date_key[1]+0)."-".($date_key[2]+0)."-".($date_key[0]);

                 $start_script_code .= " if ( dates_additional_info[". $type_id ."]['".$my_day_tag."'] == undefined ) ";
                       $start_script_code .= "dates_additional_info[". $type_id ."]['".$my_day_tag."'] = [];   ";

                 $start_script_code .= " numbb = dates_additional_info[". $type_id ."]['".$my_day_tag."'].length; ";
                 $start_script_code .= " dates_additional_info[". $type_id ."]['".$my_day_tag."'][ numbb ] = [] ;   ";

                 //$start_script_code .= " dates_additional_info[". $type_id ."]['".$my_day_tag."'][ numbb ]['time'] = '".$key_a[1]."' ;  ";
                 foreach ($value as $kkey=>$vvalue) {
                     $start_script_code .= "  dates_additional_info[". $type_id ."]['".$my_day_tag."'][ numbb ]['".$kkey."'] = '".$vvalue."' ;  ";
                 }
             }

//debuge($start_script_code); die;

            return $start_script_code;
        }



    //  A d m i n    p a n e l   ->   Booking   ///////////////////////////////////////////////////////////////////////
        

        // Save booking cost, after direct edit at admin panel from Ajax request
        function wpdev_save_bk_cost(){ global $wpdb;

               $booking_id = $_POST[ "booking_id" ];
               $cost = $_POST[ "cost" ];

               $summ = floatval(  $cost );
               $summ = round($summ,2);

               if ( $summ >= 0 ) {
                   $update_sql = "UPDATE ".$wpdb->prefix ."booking AS bk SET bk.cost='$summ' WHERE bk.booking_id=$booking_id;";

                    if ( false === $wpdb->query($wpdb->prepare( $update_sql ) ) ){
                        ?> <script type="text/javascript"> document.getElementById('ajax_message').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during cost saving' ,__FILE__,__LINE__); ?></div>'; </script> <?php
                        die();
                    }
                    ?>
                        <script type="text/javascript">
                            document.getElementById('ajax_message').innerHTML = '<?php echo __('Cost saved successfuly', 'wpdev-booking'); ?>';
                            jQuery('#ajax_message').fadeOut(3000);
                        </script>
                    <?php
               } else {
                    ?>
                        <script type="text/javascript">
                            document.getElementById('ajax_message').innerHTML = '<?php echo __('Cost is not correct. It have to be more then 0', 'wpdev-booking'); ?>';
                            jQuery('#ajax_message').fadeOut(5000);
                        </script>
                    <?php

               }

        }



    //  P a y m e n t     r e q u e s t  //HASH_EDIT  ///////////////////////////////////////////////////////////////////////

        // Show   P a y m e n t   R E Q U E  S T    request
        function premium_content_for_popups(){
            ?><div id="sendPaymentRequestModal" class="modal" >
                  <div class="modal-header">
                      <a class="close" data-dismiss="modal">&times;</a>
                      <h3><?php _e('Send payment request to customer','wpdev-booking'); ?></h3>
                  </div>
                  <div class="modal-body">
                    <textarea cols="87" rows="5" id="payment_request_reason"  name="payment_request_reason"></textarea>
                    <label class="help-block"><?php printf(__('Type your %sreason of payment%s request', 'wpdev-booking'),'<b>',',</b>');?></label>
                  </div>
                  <div class="modal-footer">
                    <a href="javascript:void(0);"
                       onclick="javascript:
                                   sendPaymentRequestByEmail(payment_request_id , document.getElementById('payment_request_reason').value,
                                   '<?php echo getBookingLocale(); ?>' );
                                   wpdev_bk_dialog_close();
                                   jQuery('#sendPaymentRequestModal').modal('hide');"
                                   class="btn btn-primary" >
                        <?php _e('Send Request','wpdev-booking'); ?>
                    </a>
                    <a href="#" class="btn" data-dismiss="modal"><?php _e('Close','wpdev-booking'); ?></a>
                  </div>
                </div><?php
        }

        // P A Y M E N T    R E Q U E S T    -->  Show Paypal form in
        function get_payment_form($booking_id, $booking_type ){//, $booking_days_count, $times_array , $booking_form ){

                    global $wpdb;

                    $bk_title    = $this->get_booking_type( $booking_type );
                    $summ        = $this->get_booking_cost_from_db( '', $booking_id );

                     $sql = "SELECT * FROM ".$wpdb->prefix ."booking as bk WHERE bk.booking_id IN ($booking_id)";
                     $result_bk = $wpdb->get_results($wpdb->prepare( $sql ));

                     if (  ( count($result_bk)>0 )  ) {

                        $sdform = $result_bk[0]->form;

                        $dates = get_dates_str($result_bk[0]->booking_id);
                        //    $my_dates_4_send = change_date_format($dates);

                        $my_d_c = explode(',', $dates);
                        $my_dates_4_send = '';
                        foreach ($my_d_c as $value) {

                            $my_single_date = substr(trim($value),0,10);
                            if( strpos($my_single_date, '-') !== false)     $my_single_date = explode('-',$my_single_date);
                            else                                            $my_single_date = explode('.',$my_single_date);
                            $my_dates_4_send .=  $my_single_date[2].'-'.$my_single_date[1].'-'.$my_single_date[0].  ', ' ;
                            
                        }
                        $dates = substr($my_dates_4_send,0,-2) ;
                        $booking_days_count = $dates;

                        $start_time = trim($my_d_c[0]);
                        $end_time   = trim($my_d_c[count($my_d_c)-1]);
                        $start_time = substr($start_time,-8,5);
                        $end_time = substr($end_time,-8,5);

                     } else { return ''; }

                    ///////////////////////////////////////////////////////////////////////////


                    $wp_nonce = ceil( time() / ( 86400 / 2 ));

                    $update_sql = "UPDATE ".$wpdb->prefix ."booking AS bk SET bk.pay_status='$wp_nonce' WHERE bk.booking_id=$booking_id;";
                    if ( false === $wpdb->query($wpdb->prepare( $update_sql ) ) ){
                        ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $booking_type; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during updating wp_nonce status in BD' ,__FILE__,__LINE__); ?></div>'; </script> <?php
                        die();
                    }

                    $summ = round($summ,2);

                    if ( ($summ + 0) == 0)  $real_payment_form = '';
                    else {

                        $output = apply_bk_filter('wpdev_bk_define_payment_forms', '', $booking_id, $summ,$bk_title, $booking_days_count, $booking_type, $sdform, $wp_nonce );

                        $real_payment_form = '<script type="text/javascript">';
                        $real_payment_form .=   'document.getElementById("booking_form_div'.$booking_type.'" ).style.display="none";';
                        $real_payment_form .=   'makeScroll("#booking_form'.$booking_type.'" );';
                        $real_payment_form .=   'document.getElementById("submiting'.$booking_type.'").innerHTML ="";';
                        $real_payment_form .= '</script>';
                        $real_payment_form .=  $output;
                    }

                    return $real_payment_form ;
        }

        function update_payment_request_count($booking_id, $value){
                    global $wpdb;
                    $value++;
                    $update_sql = "UPDATE ".$wpdb->prefix ."booking AS bk SET bk.pay_request=$value WHERE bk.booking_id=$booking_id;";
                    if ( false === $wpdb->query($wpdb->prepare( $update_sql ) ) ){
                        ?> <script type="text/javascript"> document.getElementById('ajax_message').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during updating wp_payment_request_count status in BD',__FILE__,__LINE__); ?></div>'; </script> <?php
                        die();
                    }

        }

        // Send Email request to customer for payment
        function wpdev_send_payment_request(){ global $wpdb;

               $booking_id = $_POST[ "booking_id" ];
               $reason = $_POST[ "reason" ];

               $sql = "SELECT * FROM ".$wpdb->prefix ."booking as bk WHERE bk.booking_id IN ($booking_id)";
               $result_bk = $wpdb->get_results($wpdb->prepare( $sql ));

               if (  ( count($result_bk)>0 )  ) {

                $mail_sender      = htmlspecialchars_decode( get_bk_option( 'booking_email_payment_request_adress'));
                $mail_subject     = htmlspecialchars_decode( get_bk_option( 'booking_email_payment_request_subject'));
                $mail_body        = htmlspecialchars_decode( get_bk_option( 'booking_email_payment_request_content'));
                $mail_subject =  apply_bk_filter('wpdev_check_for_active_language', $mail_subject );
                $mail_body    =  apply_bk_filter('wpdev_check_for_active_language', $mail_body );

                $is_email_payment_request_adress   = get_bk_option( 'booking_is_email_payment_request_adress' );

                 $reason  = htmlspecialchars( str_replace('\"','"', $reason ));
                 $reason  =  str_replace("\'","'",$reason );


                foreach ($result_bk as $res) {

                    if (function_exists ('get_booking_title')) $bk_title = get_booking_title( $res->booking_type );
                    else $bk_title = '';

                    $booking_form_show = get_form_content ($res->form, $res->booking_type);


                    $mail_body_to_send = str_replace('[bookingtype]', $bk_title, $mail_body);
                    if (get_bk_option( 'booking_date_view_type') == 'short') $my_dates_4_send = get_dates_short_format( get_dates_str($res->booking_id) );
                    else                                                  $my_dates_4_send = change_date_format(get_dates_str($res->booking_id));
                    $mail_body_to_send = str_replace('[dates]',$my_dates_4_send , $mail_body_to_send);


                    $my_dates4emeil_check_in_out = explode(',',get_dates_str($res->booking_id));
                    $my_check_in_date = change_date_format($my_dates4emeil_check_in_out[0] );
                    $my_check_out_date = change_date_format($my_dates4emeil_check_in_out[ count($my_dates4emeil_check_in_out)-1 ] );
                    $mail_body_to_send = str_replace('[check_in_date]',$my_check_in_date , $mail_body_to_send);
                    $mail_body_to_send = str_replace('[check_out_date]',$my_check_out_date , $mail_body_to_send);
                    $mail_body_to_send = str_replace('[id]',$res->booking_id , $mail_body_to_send);

                    $mail_body_to_send = str_replace('[content]', $booking_form_show['content'], $mail_body_to_send);
                    $mail_body_to_send = str_replace('[paymentreason]', $reason, $mail_body_to_send);
                    $mail_body_to_send = str_replace('[name]', $booking_form_show['name'], $mail_body_to_send);
                    if (isset($res->cost)) $mail_body_to_send = str_replace('[cost]', $res->cost, $mail_body_to_send);
                    $mail_body_to_send = str_replace('[siteurl]', htmlspecialchars_decode( '<a href="'.site_url().'">' . site_url() . '</a>'), $mail_body_to_send);
                    $mail_body_to_send = apply_bk_filter('wpdev_booking_set_booking_edit_link_at_email', $mail_body_to_send, $res->booking_id );


                    if ( isset($booking_form_show['secondname']) ) $mail_body_to_send = str_replace('[secondname]', $booking_form_show['secondname'], $mail_body_to_send);
                    $mail_subject1 = $mail_subject;
                    $mail_subject1 = str_replace('[name]', $booking_form_show['name'], $mail_subject1);
                    if ( isset($booking_form_show['secondname']) ) $mail_subject1 = str_replace('[secondname]', $booking_form_show['secondname'], $mail_subject1);

                    $mail_recipient =  $booking_form_show['email'];

                    $mail_headers = "From: $mail_sender\n";
                    $mail_headers .= "Content-Type: text/html\n";

                    $is_send_emeils=1;
                    if ( $is_email_payment_request_adress != 'Off')
                        if ($is_send_emeils != 0 )
                            if ( ( strpos($mail_recipient,'@blank.com') === false ) && ( strpos($mail_body_to_send,'admin@blank.com') === false ) ) {
                                @wp_mail($mail_recipient, $mail_subject1, $mail_body_to_send, $mail_headers);

                                $is_email_payment_request_send_copy_to_admin = get_bk_option( 'booking_is_email_payment_request_send_copy_to_admin' );
                                $mail_recipient =  htmlspecialchars_decode( get_bk_option( 'booking_email_reservation_adress') );
                                if ( $is_email_payment_request_send_copy_to_admin == 'On')
                                    @wp_mail($mail_recipient, $mail_subject1, $mail_body_to_send, $mail_headers);
                         
                                $this->update_payment_request_count($res->booking_id, ($res->pay_request) );
                            }
                    /////////////////////////////////////////////////////////////////////////
                }

                    ?>
                        <script type="text/javascript">
                            document.getElementById('ajax_message').innerHTML = '<?php echo __('Request is sent', 'wpdev-booking'); ?>';
                            jQuery('#ajax_message').fadeOut(3000);
                        </script>
                    <?php

               } else {
                    ?> <script type="text/javascript"> document.getElementById('ajax_message').innerHTML = '<?php echo __('Request is failed', 'wpdev-booking'); ?>'; jQuery('#ajax_message').fadeOut(3000); </script> <?php
               }
        }

        // Chnage the status of payment
        function wpdev_change_payment_status($booking_id = '', $payment_status = '', $payment_status_show = false  ){ global $wpdb;

               if ($booking_id === '') {
                   $booking_id      = $_POST[ "booking_id" ];
                   $payment_status  = $_POST[ "payment_status" ];
                   $payment_status_show  = $_POST[ "payment_status_show" ];
               }

               $sql = "SELECT * FROM ".$wpdb->prefix ."booking as bk WHERE bk.booking_id IN ($booking_id)";
               $result_bk = $wpdb->get_results($wpdb->prepare( $sql ));

               if (  ( count($result_bk)>0 )  ) {

                   $update_sql = "UPDATE ".$wpdb->prefix ."booking AS bk SET bk.pay_status='$payment_status' WHERE bk.booking_id=$booking_id;";
                   if ( false === $wpdb->query($wpdb->prepare( $update_sql ) ) ){
                        ?> <script type="text/javascript"> document.getElementById('ajax_message').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during updating wp_nonce status in BD' ,__FILE__,__LINE__); ?></div>'; </script> <?php
                        die();
                   }
                   if ($payment_status_show !== false ) {
                       ?><script type="text/javascript">
                            document.getElementById('ajax_message').innerHTML = '<?php echo __('The payment status is changed successfully', 'wpdev-booking'); ?>';
                            jQuery('#ajax_message').fadeOut(3000);
                            set_booking_row_payment_status('<?php echo $booking_id; ?>','<?php echo $payment_status; ?>','<?php echo $payment_status_show; ?>');
                         </script><?php
                   }
               } else {
                   if ($payment_status_show !== false ) {
                        ?> <script type="text/javascript"> document.getElementById('ajax_message').innerHTML = '<?php echo __('The changing of payment status is failed', 'wpdev-booking'); ?>'; jQuery('#ajax_message').fadeOut(3000); </script> <?php
                   }
               }

        }

        // Show   S e t t i n g s   of   E m a i l - send payment request email to customer
        function wpdev_booking_emails_settings() {
            if( isset($_POST['email_payment_request_adress']) ){

                 $email_payment_request_adress  = htmlspecialchars( str_replace('\"','"',$_POST['email_payment_request_adress']));
                 $email_payment_request_subject = htmlspecialchars( str_replace('\"','"',$_POST['email_payment_request_subject']));
                 $email_payment_request_content = htmlspecialchars( str_replace('\"','"',$_POST['email_payment_request_content']));

                 $email_payment_request_adress      =  str_replace("\'","'",$email_payment_request_adress);
                 $email_payment_request_subject     =  str_replace("\'","'",$email_payment_request_subject);
                 $email_payment_request_content     =  str_replace("\'","'",$email_payment_request_content);


                 if (isset( $_POST['is_email_payment_request_adress'] ))         $is_email_payment_request_adress = 'On';
                 else                                                   $is_email_payment_request_adress = 'Off';
                 update_bk_option( 'booking_is_email_payment_request_adress' , $is_email_payment_request_adress );

                 if (isset( $_POST['is_email_payment_request_send_copy_to_admin'] ))            $is_email_payment_request_send_copy_to_admin = 'On';
                 else                                               $is_email_payment_request_send_copy_to_admin = 'Off';
                 update_bk_option( 'booking_is_email_payment_request_send_copy_to_admin' , $is_email_payment_request_send_copy_to_admin );


                 update_bk_option( 'booking_email_payment_request_adress' , $email_payment_request_adress );
                 update_bk_option( 'booking_email_payment_request_subject' , $email_payment_request_subject );
                 update_bk_option( 'booking_email_payment_request_content' , $email_payment_request_content );


            }
             $email_payment_request_adress      = get_bk_option( 'booking_email_payment_request_adress');
             $email_payment_request_subject     = get_bk_option( 'booking_email_payment_request_subject');
             $email_payment_request_content     = get_bk_option( 'booking_email_payment_request_content');
             $is_email_payment_request_adress   = get_bk_option( 'booking_is_email_payment_request_adress' );
             $is_email_payment_request_send_copy_to_admin = get_bk_option( 'booking_is_email_payment_request_send_copy_to_admin'  );
            ?>

                    <div id="visibility_container_email_payment_request" class="visibility_container" style="display:none;">

                        <div class='meta-box'> <div <?php $my_close_open_win_id = 'bk_settings_emails_to_person_with_pay_request'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle','wpdev-booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');" ><br></div>
                              <h3 class='hndle'><span><?php _e('Email to "Person" with payment request', 'wpdev-booking'); ?></span></h3> <div class="inside">

            <table class="form-table email-table" >
                <tbody>
                    <tr><td colspan="2"  class="th-title">
                            <div style="float:left;"><h2><?php _e('Email to "Person" with payment request', 'wpdev-booking'); ?></h2></div>
                            <div style="float:right;font-weight: bold;"><label for="is_email_payment_request_adress" ><?php _e('Active', 'wpdev-booking'); ?>: </label><input id="is_email_payment_request_adress" type="checkbox" <?php if ($is_email_payment_request_adress == 'On') echo "checked"; ?>  value="<?php echo $is_email_payment_request_adress; ?>" name="is_email_payment_request_adress"  onchange="document.getElementById('booking_is_email_payment_request_adress_dublicated').checked=this.checked;"  /></div>
                            <div style="float:right;font-weight: bold;margin:0px 50px;"><label for="is_email_payment_request_send_copy_to_admin" ><?php _e('Send copy of this email to Admin', 'wpdev-booking'); ?>: </label><input id="is_email_payment_request_send_copy_to_admin" type="checkbox" <?php if ($is_email_payment_request_send_copy_to_admin == 'On') echo "checked"; ?>  value="<?php echo $is_email_payment_request_send_copy_to_admin; ?>" name="is_email_payment_request_send_copy_to_admin"/></div>
                        </td></tr>

                    <tr valign="top">
                        <th scope="row"><label for="admin_cal_count" ><?php _e('From', 'wpdev-booking'); ?>:</label></th>
                        <td><input id="email_payment_request_adress"  name="email_payment_request_adress" class="regular-text code" type="text" size="45" value="<?php echo $email_payment_request_adress; ?>" />
                            <span class="description"><?php printf(__('Type default %sadmin email%s from where this email is sending', 'wpdev-booking'),'<b>','</b>');?></span>
                        </td>
                    </tr>

                    <tr valign="top">
                            <th scope="row"><label for="admin_cal_count" ><?php _e('Subject', 'wpdev-booking'); ?>:</label></th>
                            <td><input id="email_payment_request_subject"  name="email_payment_request_subject" class="regular-text code" type="text" size="45" value="<?php echo $email_payment_request_subject; ?>" />
                                <span class="description"><?php printf(__('Type email subject for %spayment request%s. You can use these %s shortcodes.', 'wpdev-booking'),'<b>','</b>', '<code>[name]</code>, <code>[secondname]</code>');?></span>
                            </td>
                    </tr>

                    <tr valign="top">
                        <td colspan="2">
                              <span class="description"><?php printf(__('Type your %semail message for payment request%s', 'wpdev-booking'),'<b>','</b>');?></span>
                              <textarea id="email_payment_request_content" name="email_payment_request_content" style="width:100%;" rows="2"><?php echo ($email_payment_request_content); ?></textarea>
                              <div class="shortcode_help_section" style="margin-top:10px;">
                              <span class="description"><?php printf(__('Use this shortcodes: ', 'wpdev-booking'));?></span>
                              <span class="description"><?php printf(__('%s - inserting ID of booking ', 'wpdev-booking'),'<code>[id]</code>');?>, </span>
                              <span class="description"><?php printf(__('%s - inserting name of person, who made booking (field %s requred at form for this bookmark), ', 'wpdev-booking'),'<code>[name]</code>','[text name]');?></span>
                              <span class="description"><?php printf(__('%s - inserting dates of booking, ', 'wpdev-booking'),'<code>[dates]</code>');?></span>
                              <span class="description"><?php printf(__('%s - inserting check in date (first day of booking), ', 'wpdev-booking'),'<code>[check_in_date]</code>');?></span>
                              <span class="description"><?php printf(__('%s - inserting check out date (last day of booking), ', 'wpdev-booking'),'<code>[check_out_date]</code>');?></span>
                              <span class="description"><?php printf(__('%s - inserting type of booking resource, ', 'wpdev-booking'),'<code>[bookingtype]</code>');?></span>
                              <span class="description"><?php printf(__('%s - inserting detail person info', 'wpdev-booking'),'<code>[content]</code>');?></span>,
                              <span class="description"><?php printf(__('%s - inserting cost of payment', 'wpdev-booking'),'<code>[cost]</code>');?></span>,
                              <span class="description"><?php printf(__('%s - inserting reason of payment, you can enter it before sending email', 'wpdev-booking'),'<code>[paymentreason]</code>');?></span>,
                              <span class="description"><?php printf(__('%s - inserting link to payment page for visitor at client side of site, ', 'wpdev-booking'),'<code>[visitorbookingpayurl]</code>');?></span>
                              <span class="description"><?php printf(__('%s - inserting link of booking editing by visitor at client side of site, ', 'wpdev-booking'),'<code>[visitorbookingediturl]</code>');?></span>
                              <span class="description"><?php printf(__('%s - inserting link for booking cancellation by visitor at client side of site, ', 'wpdev-booking'),'<code>[visitorbookingcancelurl]</code>');?></span>
                              <span class="description"><?php printf(__('%s - inserting new line', 'wpdev-booking'),'<code>&lt;br/&gt;</code>');?></span>
                              <br/><?php  echo (   (sprintf(__('You need to make payment %s for booking %s at %s. %s You can make payment at this page: %s  Thank you, booking service.', 'wpdev-booking'),'[cost]','[bookingtype]','[dates]','&lt;br/&gt;&lt;br/&gt;[paymentreason]&lt;br/&gt;&lt;br/&gt;[content]&lt;br/&gt;&lt;br/&gt;', '[visitorbookingpayurl]&lt;br/&gt;&lt;br/&gt;'  ))); ?>
                              <?php make_bk_action('show_additional_translation_shortcode_help'); ?>
                              </div>
                        </td>
                    </tr>
                </tbody>
            </table>

                        </div> </div> </div>

                    </div>
            <?php
        }



    // P A Y M E N T    A J A X   F O R M   //////////////////////////////////////////////////////////////////////

        // Claculate the cost for specific days(times) based on base_cost for specific period
        function get_cost_for_period($period, $base_cost, $days, $times = array(array('00','00','01'), array('24','00','02')) ) {

                $fin_cost = 0 ;

                $is_time_apply_to_cost  = get_bk_option( 'booking_is_time_apply_to_cost'  );

                if ($is_time_apply_to_cost == 'On') {                           // Make some corrections if TIME IS APPLY TO THE COST
                    if ($period == 'day') {
                        $period = 'hour';
                        $base_cost = $base_cost / 24 ;
                    } else if ($period == 'night') {
                        $period = 'hour';
                        $base_cost = $base_cost / 24 ;
                    } else if ($period == 'hour') {                             // Skip here evrything fine
                    } else {                                                    // Skip here evrything fine
                    }
                }

                if ($period == 'day') {

                    $fin_cost = count($days) * $base_cost;

                } else if ($period == 'night') {

                    $night_count = (count($days)>1) ? (count($days)-1) : 1;
                    $fin_cost = $night_count * $base_cost;

                } else if ($period == 'hour') {

                    $start_time = $times[0];
                    $end_time   = $times[1];
                    if ($end_time == array('00','00','00')) $end_time = array('24','00','00');

                    if (count($days)<=1) {
                            
                            $m_dif =  ($end_time[0] * 60 + intval($end_time[1]) ) - ($start_time[0] * 60 + intval($start_time[1]) ) ;
                            $fin_cost =   $m_dif * $base_cost / 60;

                    } else {
                        $full_days_count = count($days) - 2;

                        $full_days_cost =   $full_days_count* 24 * 60 * $base_cost / 60;
                        $check_in_cost  = ( 24 * 60  - ($start_time[0] * 60 + intval($start_time[1]) ) ) * $base_cost / 60;
                        $check_out_cost = ( $end_time[0] * 60 + intval($end_time[1]) )  * $base_cost / 60;
                        $fin_cost = $check_in_cost + $full_days_cost + $check_out_cost ;
                    }

                } else { // Fixed

                    $fin_cost = $base_cost;
                }

                $fin_cost = round( $fin_cost ,2 );

                return  $fin_cost;
        }


        // C A L C U L A T E     C O S T     f o r      B o o k i n g
        function get_booking_cost($booking_type, $booking_days_count, $times_array, $post_form, $is_discount_calculate = true, $is_only_original_cost = false){

                    $paypal_price_period    = get_bk_option( 'booking_paypal_price_period' );
                    $is_time_apply_to_cost  = get_bk_option( 'booking_is_time_apply_to_cost'  );
                    if ( ($is_time_apply_to_cost == 'Off') && ($paypal_price_period != 'hour') ) $times_array = array(array('00','00','01'), array('24','00','02'));

                    $days_array     = explode(',', $booking_days_count);
                    $days_count     = count($days_array);

                    $paypal_dayprice        = $this->get_cost_of_booking_resource( $booking_type ) ;
                    $paypal_dayprice_orig   = $paypal_dayprice;
                    

                    if ( ( get_bk_option( 'booking_recurrent_time' ) !== 'On') || 
                         ( ( $times_array[0][0]=='00' ) && ( $times_array[0][1]=='00' ) && ( $times_array[1][0]=='00' ) && ( $times_array[1][1]=='00' ) )
                        ) {
                        if ( ! class_exists('wpdev_bk_biz_m') ) {

                            $summ = $this->get_cost_for_period(
                                                                get_bk_option( 'booking_paypal_price_period' ),
                                                                $this->get_cost_of_booking_resource( $booking_type ) ,
                                                                $days_array,
                                                                $times_array
                                );

                        } else  {

                            $paypal_dayprice        = apply_bk_filter('wpdev_season_rates', $paypal_dayprice, $days_array, $booking_type, $times_array);  // Its return array with day costs
//debuge($paypal_dayprice);
                            if (is_array($paypal_dayprice)) {
                                $summ = 0.0;
                                for ($ki = 0; $ki < count($paypal_dayprice); $ki++) { $summ += $paypal_dayprice[$ki]; }
                            } else {
                                $summ = (1* $paypal_dayprice * $days_count );
                            }

                        }

                    } else { // Recurent time in evry days calculation

                        $final_summ = 0;
                        $temp_days = $days_array;
                        $temp_paypal_dayprice = $paypal_dayprice;

                        foreach ($temp_days as $days_array) {  // lOOP EACH DAY

                            $days_array = array($days_array);
                            $paypal_dayprice = $temp_paypal_dayprice;


                            if ( ! class_exists('wpdev_bk_biz_m') ) {

                                $summ = $this->get_cost_for_period(
                                                                    get_bk_option( 'booking_paypal_price_period' ),
                                                                    $this->get_cost_of_booking_resource( $booking_type ) ,
                                                                    $days_array,
                                                                    $times_array
                                    );
                                
                                if (get_bk_option( 'booking_paypal_price_period' ) == 'fixed')          $final_summ = 0; // if we are have fixed cost calculation so we will not gathering all costs but get just last one.

                                // Set first day as 0, if we have true all these conditions
                                if (   (get_bk_option( 'booking_paypal_price_period' ) == 'night')
                                    && (get_bk_option( 'booking_is_time_apply_to_cost' ) != 'On' )
                                    && ( count($temp_days)>1 ) && ($final_summ == 0 ) && ($summ > 0) )  $final_summ = -1*$summ + 0.000001;  // last number is need for definition its only for first day and make its little more than 0, then at final cost there is ROUND to the 2 nd number after comma.



                            } else  {

                                $paypal_dayprice        = apply_bk_filter('wpdev_season_rates', $paypal_dayprice, $days_array, $booking_type, $times_array);  // Its return array with day costs

                                if (is_array($paypal_dayprice)) {
                                    $summ = 0.0;
                                    for ($ki = 0; $ki < count($paypal_dayprice); $ki++) { $summ += $paypal_dayprice[$ki]; }
                                } else {
                                    $summ = (1* $paypal_dayprice * $days_count );
                                }

                            }

                            $final_summ += $summ;
                            $summ = 0.0;
                        }

                        $paypal_dayprice = $temp_paypal_dayprice;
                        $days_array = $temp_days;
                        $summ = $final_summ;
                    }

                    if (get_bk_option( 'booking_paypal_price_period' ) == 'fixed') {
                            if (is_array($paypal_dayprice))  $summ = $paypal_dayprice[0] ;
                            else                             $summ = $paypal_dayprice ;
                    }


                    $summ = round($summ,2);
                    $summ_original_without_additional = $summ ;

                    if ($is_only_original_cost)
                        return $summ_original_without_additional;
                    

                    $summ = apply_bk_filter('advanced_cost_apply', $summ , $post_form, $booking_type, $days_array );    // Apply advanced cost managemnt

                    if ($is_discount_calculate)
                        $summ = apply_bk_filter('coupons_discount_apply', $summ , $post_form, $booking_type ); // Apply discounts based on coupons

                    $summ = round($summ,2);
                    return $summ;
        }


        // Show Paypal form from Ajax request
        function show_paypal_form_in_ajax_request($booking_id, $booking_type, $booking_days_count, $times_array , $booking_form ){

                    $bk_title    = $this->get_booking_type( $booking_type );
                    $summ        = $this->get_booking_cost( $booking_type, $booking_days_count, $times_array, $booking_form );

                    $summ_deposit = apply_bk_filter('fixed_deposit_amount_apply', $summ , $booking_form, $booking_type ); // Apply fixed deposit

                    $is_deposit = false;
                    if ($summ_deposit != $summ ) {
                        $is_deposit = true;
                        $summ__full = $summ;
                        $summ       = $summ_deposit;
                    }

                    // Check for additional calendars
                    $summ_additional_calendars = apply_bk_filter('check_cost_for_additional_calendars', $summ, $booking_form, $booking_type,  $times_array  ); // Apply cost according additional calendars
                    if (isset($summ_additional_calendars))
                        if( is_array($summ_additional_calendars) )
                            $summ = $summ_additional_calendars[0];

 
                    ///////////////////////////////////////////////////////////////////////////

                    global $wpdb;
                    $wp_nonce = ceil( time() / ( 86400 / 2 ));

                    $update_sql = "UPDATE ".$wpdb->prefix ."booking AS bk SET bk.pay_status='$wp_nonce' WHERE bk.booking_id=$booking_id;";
                    if ( false === $wpdb->query($wpdb->prepare( $update_sql ) ) ){
                        ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $bktype; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during updating wp_nonce status in BD' ,__FILE__,__LINE__); ?></div>'; </script> <?php
                        die();
                    }

                    $summ = round($summ,2);
                    $output = apply_bk_filter('wpdev_bk_define_payment_forms', '', $booking_id, $summ,$bk_title, $booking_days_count, $booking_type, $_POST["form"], $wp_nonce,$is_deposit );

                    // Just make some Notes about deposit and balances
                    if ($is_deposit)
                        if (($summ__full-$summ_deposit)>0) {

                            $summ_show           = wpdev_bk_cost_number_format ( $summ_deposit );
                            $full_summ_show      = wpdev_bk_cost_number_format ( $summ__full );
                            $balance_summ_show   = wpdev_bk_cost_number_format ( ($summ__full-$summ_deposit) );

                            $cost__title_deposit  = __('deposit', 'wpdev-booking').": ";
                            $cost__title_total    = __('Total cost', 'wpdev-booking').": ";
                            $cost__title_balace   = __('balance', 'wpdev-booking').": ";

                            $today_day = date('m.d.Y')  ;

                            $paypal_curency =  get_bk_option( 'booking_paypal_curency' );
                            $cost_currency  = $this->get_currency_info();
                            if ($cost_currency == $paypal_curency) {
                                $cost_currency_1 = '';
                                $cost_currency_2 = " " . $cost_currency;
                            } else {
                                $cost_currency_1 = $cost_currency . '';
                                $cost_currency_2 = "" ;
                            }
                            $cost_summ_with_title='';
                            $cost_summ_with_title .= $cost__title_total . $cost_currency_1 . $full_summ_show . $cost_currency_2 . " /";
                            $cost_summ_with_title .= $cost__title_deposit . $cost_currency_1 . $summ_show . $cost_currency_2 . ", ";
                            $cost_summ_with_title .= $cost__title_balace . $cost_currency_1 . $balance_summ_show . $cost_currency_2 . "/";
                            $cost_summ_with_title .= ' - '  . $today_day .'';

                            make_bk_action('wpdev_make_update_of_remark' , $booking_id , $cost_summ_with_title , true );
                    } // fin. notes.



                    $is_turned_off = apply_bk_filter('is_payment_forms_off', true);
                    if ($is_turned_off)  return;


                    //MODIF WTB MAXIME 19/06/2012 : test si besoin de validation
                    $is_need_to_validate_reservation = apply_bk_filter('is_need_to_validate_reservation', $booking_type);
                    if($is_need_to_validate_reservation) {
                        mailAttenteConfirmation($booking_id);
                        mailDemandeProprietePourConfirmation($booking_id)

                        ?>
                        <p><?php 
                            $message = __('Veuillez patienter, votre réservation est traitée.. Vous allez être rediriger vers une autre page dans quelques instant. ', 'wpdev-booking'); 
                            echo $message;
                            ?></p>
                        <script type="text/javascript">
                            document.getElementById('booking_form_div<?php echo $booking_type; ?>').innerHTML = "";
                            setTimeout(function() { makeScroll(".su-tabs-current" ); }, 500);
                        </script>
                        <?php

                        return;
                    }

                    if ( ($summ + 0) > 0){
                        ?>
                        <script type="text/javascript">
                           document.getElementById('submiting<?php echo $booking_type; ?>').innerHTML ='';
                           if (document.getElementById('paypalbooking_form<?php echo $booking_type; ?>') != null) {
                              document.getElementById('paypalbooking_form<?php echo $booking_type; ?>').innerHTML = '<div class=\"\" style=\"height:200px;margin:20px 0px;\" ><?php echo $output; ?></div>';
                              setTimeout(function() { makeScroll("#paypalbooking_form<?php echo $booking_type; ?>" ); }, 500);
                          }
                        </script>
                        <?php
                    }
        }

        function is_need_to_validate_reservation($booking_type){ global $wpdb;

            $requete = "SELECT atosValidation FROM ". $wpdb->prefix ."bookingtypes WHERE booking_type_id = '" .$booking_type. "'";
            $result = $wpdb->get_results($wpdb->prepare($requete));

            if($result[0]->atosValidation == "true")
                return true;

            return false;
        }

//   A D M I N     S I D E    //////////////////////////////////////////////////////////////////////////////////////////////////////////////


      //  A d m i n    p a n e l   ->   Settings -> Add Booking   ///////////////////////////////////////////////////////////////////////
      //__________________________________________________________//
        // Show button for autofill form at the admin panel
        function wpdev_show_autofill_button(){
            ?>
                <div id="autofillform" class="topmenuitemborder" style="float:right;border:none;background:none;margin:0px;">
                    <?php //echo '<a href="javascript:;" onclick="javascript:autofill_bk_form();" class="bktypetitlenew autofillformbutton ">' .  __('Auto fill form','wpdev-booking')  . '</a>'; ?>
                    <?php echo '<input type="button" class="button-primary" onclick="javascript:autofill_bk_form();" value="' .  __('Auto fill form','wpdev-booking')  . '" />'; ?>
                </div>
                 <script type="text/javascript">

                function autofill_bk_form(){

                    var my_element_value = 'admin';
                    var form_elements = jQuery('.booking_form_div input');

                    jQuery.each(form_elements, function(){

                        if (  (this.type !== 'button') && (this.type !== 'hidden') ) {

                            this.value = my_element_value;
                            if (this.name.search('email') != -1 ) {
                                this.value = my_element_value + '@blank.com';
                            }
                            if (this.name.search('starttime') != -1 ) { this.name = 'temp'; this.value=''; } // set name of time to someother name
                            if (this.name.search('endtime') != -1 ) { this.name = 'temp2'; this.value=''; }  // set name of time to someother name
                        }
                    });

                    //jQuery('.booking_form').submit();
                    //var form_elements_text = jQuery('.booking_form_div textarea');
                    //jQuery.each(form_elements_text, function(){ this.value = my_element_value; });
                }
                </script>
                <?php

        }


      //  A d m i n    p a n e l   ->   Settings -> Payment       ///////////////////////////////////////////////////////////////////////
      //__________________________________________________________//
        //Show settings page depends from selecting TAB
        function settings_menu_content(){

            $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'not_low_level_user'); //Anxo customizarion
            if (! $is_can) return; //Anxo customizarion

            switch ($_GET['tab']) {

             case 'payment':
                $this->show_settings_content();
                return false;
                break;

             default:
                return true;
                break;
            }

        }

        //Show Settings page
        function show_settings_content() { ?>

                <div class="clear" style="height:0px;"></div>
                <div id="ajax_working"></div>
                <div id="poststuff" class="metabox-holder">
                    <form  name="post_settings_payment_integration" action="" method="post" id="post_settings_payment_integration" >
                        <?php $this->show_billing_settings();      ?>
                        <?php make_bk_action('wpdev_bk_payment_show_settings_content' );  ?>
                    </form>
                </div>
        <?php
        }


        // Show settings for autofill options at the Payment form.
        function show_billing_settings(){

                if ( isset( $_POST['sage_billing_customer_email'] ) ) {
                      update_bk_option( 'booking_billing_customer_email', $_POST['sage_billing_customer_email'] );
                      update_bk_option( 'booking_billing_firstnames', $_POST['sage_billing_firstnames'] );
                      update_bk_option( 'booking_billing_surname', $_POST['sage_billing_surname'] );
                      update_bk_option( 'booking_billing_phone', $_POST['sage_billing_phone'] );
                      update_bk_option( 'booking_billing_address1', $_POST['sage_billing_address1'] );
                      update_bk_option( 'booking_billing_city', $_POST['sage_billing_city'] );
                      update_bk_option( 'booking_billing_country', $_POST['sage_billing_country'] );
                      update_bk_option( 'booking_billing_post_code', $_POST['sage_billing_post_code'] );
                }

                $sage_billing_customer_email =  get_bk_option( 'booking_billing_customer_email' );
                $sage_billing_firstnames =  get_bk_option( 'booking_billing_firstnames' );
                $sage_billing_surname    =  get_bk_option( 'booking_billing_surname' );
                $sage_billing_phone     =  get_bk_option( 'booking_billing_phone' );
                $sage_billing_address1  =  get_bk_option( 'booking_billing_address1' );
                $sage_billing_city      =  get_bk_option( 'booking_billing_city' );
                $sage_billing_country   =  get_bk_option( 'booking_billing_country' );
                $sage_billing_post_code =  get_bk_option( 'booking_billing_post_code' );


            ?>
                <div id="visibility_container_billing" class="visibility_container" style="display:none;">
                <div class='meta-box'>  
                     <div <?php $my_close_open_win_id = 'bk_settings_costs_billing'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle','wpdev-booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                        <h3 class='hndle'><span><?php _e('Billing form fields customization', 'wpdev-booking'); ?></span></h3> <div class="inside">
                            <!--form  name="post_option_billing_form" action="" method="post" id="post_option_billing_form" -->
                                <table class="form-table settings-table">
                                    <tbody>

                                        <?php $all_form_fields = $this->get_fields_from_booking_form();
                                        //debuge($all_form_fields[1][2]);
                                        $fields_orig_names = $all_form_fields[1][2];
                                        ?>

                                        <tr valign="top">
                                          <th scope="row" colspan="2">
                                            <h2 style="padding:0px;margin:0px;" ><?php printf(__('Please set required billing fields, they will automatically %sassign  to billing booking form%s', 'wpdev-booking'),'<b>','</b>' ); ?>:</h2>
                                          </th>
                                        </tr>
                                        <tr valign="top">
                                          <th scope="row" colspan="2">
                                            <span class="description"><?php printf(__('Please, select field from your booking form. This field will be automatically assign to current field in biilling form.', 'wpdev-booking'),'<b>','</b>');?></span>
                                          </th>
                                        </tr>

                                        <tr valign="top">
                                          <th scope="row">
                                            <label for="sage_billing_customer_email" ><?php _e('Customer Email', 'wpdev-booking'); ?>:</label>
                                          </th>
                                          <td>
                                             <select id="sage_billing_customer_email" name="sage_billing_customer_email">
                                                <?php foreach ( $fields_orig_names as $key => $field_names) { ?>
                                                  <option <?php if($sage_billing_customer_email == $field_names) echo "selected"; ?> value="<?php echo $field_names; ?>"><?php echo $field_names; ?></option>
                                                <?php } ?>
                                             </select>
                                          </td>
                                        </tr>

                                        <tr valign="top">
                                          <th scope="row">
                                            <label for="sage_billing_firstnames" ><?php _e('First Name(s)', 'wpdev-booking'); ?>:</label>
                                          </th>
                                          <td>
                                             <select id="sage_billing_firstnames" name="sage_billing_firstnames">
                                                <?php foreach ( $fields_orig_names as $key => $field_names) { ?>
                                                  <option <?php if($sage_billing_firstnames == $field_names) echo "selected"; ?> value="<?php echo $field_names; ?>"><?php echo $field_names; ?></option>
                                                <?php } ?>
                                             </select>
                                          </td>
                                        </tr>

                                        <tr valign="top">
                                          <th scope="row">
                                            <label for="sage_billing_surname" ><?php _e('Last name', 'wpdev-booking'); ?>:</label>
                                          </th>
                                          <td>
                                             <select id="sage_billing_surname" name="sage_billing_surname">
                                                <?php foreach ( $fields_orig_names as $key => $field_names) { ?>
                                                  <option <?php if($sage_billing_surname == $field_names) echo "selected"; ?> value="<?php echo $field_names; ?>"><?php echo $field_names; ?></option>
                                                <?php } ?>
                                             </select>
                                          </td>
                                        </tr>


                                        <tr valign="top">
                                          <th scope="row">
                                            <label for="sage_billing_phone" ><?php _e('Phone', 'wpdev-booking'); ?>:</label>
                                          </th>
                                          <td>
                                             <select id="sage_billing_phone" name="sage_billing_phone">
                                                <?php foreach ( $fields_orig_names as $key => $field_names) { ?>
                                                  <option <?php if($sage_billing_phone == $field_names) echo "selected"; ?> value="<?php echo $field_names; ?>"><?php echo $field_names; ?></option>
                                                <?php } ?>
                                             </select>
                                          </td>
                                        </tr>

                                        <tr valign="top">
                                            <td scope="row" colspan="2">
                                              <div style="height: 0px; clear: both;" class="clear topmenuitemseparatorv"></div>
                                            </td>
                                        </tr>


                                        <tr valign="top">
                                          <th scope="row">
                                            <label for="sage_billing_address1" ><?php _e('Billing Address', 'wpdev-booking'); ?>:</label>
                                          </th>
                                          <td>
                                             <select id="sage_billing_address1" name="sage_billing_address1">
                                                <?php foreach ( $fields_orig_names as $key => $field_names) { ?>
                                                  <option <?php if($sage_billing_address1 == $field_names) echo "selected"; ?> value="<?php echo $field_names; ?>"><?php echo $field_names; ?></option>
                                                <?php } ?>
                                             </select>
                                          </td>
                                        </tr>

                                        <tr valign="top">
                                          <th scope="row">
                                            <label for="sage_billing_city" ><?php _e('Billing City', 'wpdev-booking'); ?>:</label>
                                          </th>
                                          <td>
                                             <select id="sage_billing_city" name="sage_billing_city">
                                                <?php foreach ( $fields_orig_names as $key => $field_names) { ?>
                                                  <option <?php if($sage_billing_city == $field_names) echo "selected"; ?> value="<?php echo $field_names; ?>"><?php echo $field_names; ?></option>
                                                <?php } ?>
                                             </select>
                                          </td>
                                        </tr>

                                        <tr valign="top">
                                          <th scope="row">
                                            <label for="sage_billing_post_code" ><?php _e('Post Code', 'wpdev-booking'); ?>:</label>
                                          </th>
                                          <td>
                                             <select id="sage_billing_post_code" name="sage_billing_post_code">
                                                <?php foreach ( $fields_orig_names as $key => $field_names) { ?>
                                                  <option <?php if($sage_billing_post_code == $field_names) echo "selected"; ?> value="<?php echo $field_names; ?>"><?php echo $field_names; ?></option>
                                                <?php } ?>
                                             </select>
                                          </td>
                                        </tr>

                                        <tr valign="top">
                                          <th scope="row">
                                            <label for="sage_billing_country" ><?php _e('Country', 'wpdev-booking'); ?>:</label>
                                          </th>
                                          <td>
                                             <select id="sage_billing_country" name="sage_billing_country">
                                                <?php foreach ( $fields_orig_names as $key => $field_names) { ?>
                                                  <option <?php if($sage_billing_country == $field_names) echo "selected"; ?> value="<?php echo $field_names; ?>"><?php echo $field_names; ?></option>
                                                <?php } ?>
                                             </select>
                                          </td>
                                        </tr>

                                        <?php if (get_bk_option( 'booking_sage_is_active' ) == 'On') { ?>
                                        <tr valign="top">
                                          <th scope="row" colspan="2">
                                              <span style="font-size:11px;color:#f00;" class="description"><?php printf(__('These %sfields confuguration is obligatory, for Sage payment%s system!', 'wpdev-booking'),'<b>','</b>');?></span>
                                          </th>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                                <div class="clear" style="height:10px;"></div>
                                <input class="button-primary" style="float:right;" type="submit" value="<?php _e('Save', 'wpdev-booking'); ?>" name="billing_form_submit"/>
                                <div class="clear" style="height:10px;"></div>
                            <!--/form-->
               </div> </div> </div>
                </div>
                                <?php
        }


            // Get fields from booking form at the settings page or return false if no fields
            function get_fields_from_booking_form(){
                $booking_form  = get_bk_option( 'booking_form' );
                $types = 'text[*]?|email[*]?|time[*]?|textarea[*]?|select[*]?|checkbox[*]?|radio|acceptance|captchac|captchar|file[*]?|quiz';
                $regex = '%\[\s*(' . $types . ')(\s+[a-zA-Z][0-9a-zA-Z:._-]*)([-0-9a-zA-Z:#_/|\s]*)?((?:\s*(?:"[^"]*"|\'[^\']*\'))*)?\s*\]%';
                $regex2 = '%\[\s*(country[*]?|starttime[*]?|endtime[*]?)(\s*[a-zA-Z]*[0-9a-zA-Z:._-]*)([-0-9a-zA-Z:#_/|\s]*)*((?:\s*(?:"[^"]*"|\'[^\']*\'))*)?\s*\]%';
                $fields_count = preg_match_all($regex, $booking_form, $fields_matches) ;
                $fields_count2 = preg_match_all($regex2, $booking_form, $fields_matches2) ;

                //Gathering Together 2 arrays $fields_matches  and $fields_matches2
                foreach ($fields_matches2 as $key => $value) {
                    if ($key == 2) $value = $fields_matches2[1];
                    foreach ($value as $v) {
                        $fields_matches[$key][count($fields_matches[$key])]  = $v;
                    }
                }
                $fields_count += $fields_count2;

                if ($fields_count>0) return array($fields_count, $fields_matches);
                else return false;
            }




    // S e t t i n g s

        // Set Advanced Settings - list of function for each row
        function settings_advanced_set_time_format(){
             if ( isset( $_POST['booking_time_format'] ) ) {
                 update_bk_option( 'booking_time_format' , $_POST['booking_time_format'] );
             }
             $booking_time_format = get_bk_option( 'booking_time_format');
            ?>
                <tr valign="top" class="ver_premium">
                <th scope="row"><label for="booking_time_format" ><?php _e('Time Format', 'wpdev-booking'); ?>:</label><br/>
                </th>
                    <td>
                        <fieldset>
                        <?php
                                $time_formats =  array( 'g:i a', 'g:i A', 'H:i' ) ;
                                $custom = TRUE;
                                foreach ( $time_formats as $format ) {
                                        echo "\t<label title='" . esc_attr($format) . "'>";
                                        echo "<input type='radio' name='booking_time_format' value='" . esc_attr($format) . "'";
                                        if ( get_bk_option( 'booking_time_format') === $format ) {  echo " checked='checked'"; $custom = FALSE; }
                                        echo ' /> ' . date_i18n( $format ) . "</label> &nbsp;&nbsp;&nbsp; \n";
                                }
                                echo '	<label><input type="radio" name="booking_time_format" id="time_format_custom_radio" value="'.$booking_time_format.'"';
                                if ( $custom )  echo ' checked="checked"';
                                echo '/> ' . __('Custom', 'wpdev-booking') . ': </label>';?>
                                    <input id="booking_time_format_custom" class="regular-text code" type="text" size="45" value="<?php echo $booking_time_format; ?>" name="booking_time_format_custom"
                                           onchange="javascript:document.getElementById('time_format_custom_radio').value = this.value;document.getElementById('time_format_custom_radio').checked=true;"
                                           />
                       <?php
                                echo ' ' . date_i18n( $booking_time_format ) . "\n";
                                echo '&nbsp;&nbsp;&nbsp;&nbsp;';
                        ?>
                                    <?php printf(__('Type your time format for showing in emails and booking table. %sDocumentation on time formatting.%s', 'wpdev-booking'),'<br/><a href="http://php.net/manual/en/function.date.php" target="_blank">','</a>');?>
                        </fieldset>

                    </td>
                </tr>

            <?php
        }

        function settings_advanced_set_range_selections(){
             if ( isset( $_POST['range_selection_days_count'] ) ) {

                     if (isset( $_POST['range_selection_is_active'] ))     $range_selection_is_active = 'On';
                     else                                                  $range_selection_is_active = 'Off';
                     update_bk_option( 'booking_range_selection_is_active' ,  $range_selection_is_active );

                     $range_selection_days_count =  $_POST['range_selection_days_count'];
                     update_bk_option( 'booking_range_selection_days_count' , $range_selection_days_count );

                     $range_start_day =  $_POST['range_start_day'];
                     update_bk_option( 'booking_range_start_day' , $range_start_day );

                     $range_selection_days_count_dynamic =  $_POST['range_selection_days_count_dynamic'];
                     update_bk_option( 'booking_range_selection_days_count_dynamic' , $range_selection_days_count_dynamic );

                     update_bk_option( 'booking_range_selection_days_max_count_dynamic' , $_POST['range_selection_days_count_dynamic_max'] );
                     update_bk_option( 'booking_range_selection_days_specific_num_dynamic' , $_POST['range_selection_days_count_dynamic_specific'] );


                     $range_start_day_dynamic =  $_POST['range_start_day_dynamic'];
                     update_bk_option( 'booking_range_start_day_dynamic' , $range_start_day_dynamic );

                     $range_selection_type = $_POST['range_selection_type'];
                     update_bk_option( 'booking_range_selection_type' , $range_selection_type );


             }
                    $range_selection_type = get_bk_option( 'booking_range_selection_type'); if( get_bk_option( 'booking_range_selection_type') == false) $range_selection_type = 'fixed';
                    $range_selection_is_active = get_bk_option( 'booking_range_selection_is_active');
                    $range_selection_days_count = get_bk_option( 'booking_range_selection_days_count');
                    $range_start_day = get_bk_option( 'booking_range_start_day');
                    $range_selection_days_count_dynamic = get_bk_option( 'booking_range_selection_days_count_dynamic');
                    $range_start_day_dynamic   = get_bk_option( 'booking_range_start_day_dynamic');

                    $range_selection_days_count_dynamic_max        = get_bk_option( 'booking_range_selection_days_max_count_dynamic');
                    $range_selection_days_count_dynamic_specific   = get_bk_option( 'booking_range_selection_days_specific_num_dynamic');
            ?>

                <tr valign="top"  class="ver_premium">
                    <th scope="row">
                        <label for="range_selection_is_active" ><?php _e('Range days selection', 'wpdev-booking'); ?>:</label>
                    </th>
                    <td>
                        <input <?php if ($range_selection_is_active == 'On') echo "checked";/**/ ?>  value="<?php echo $range_selection_is_active; ?>" name="range_selection_is_active" id="range_selection_is_active" type="checkbox"
                             onclick="javascript: if (this.checked) jQuery('#togle_settings_range_type_selection').slideDown('normal'); else  jQuery('#togle_settings_range_type_selection').slideUp('normal');"
                                                                                                     />

                        <span class="description"><?php _e(' Check this  if you want to use selection range in calendar. For example: select a week or only five days for booking.', 'wpdev-booking');?></span>
                    </td>
                </tr>

                <tr valign="top"  class="ver_premium"><td colspan="2">

                        <table id="togle_settings_range_type_selection" style="<?php if ($range_selection_is_active != 'On') echo "display:none;";/**/ ?>" class="hided_settings_table">
                    <tr valign="top"><td>

                            <div style="width:100%;">
                                <div style="float:left;width:180px;height: 30px;"></div>
                                <div style="float:left;width:400px;font-weight: bold;"><label for="range_start_day" ><?php _e('Selection of FIXED number of days by ONE mouse click', 'wpdev-booking'); ?>: </label><input  <?php if ($range_selection_type == 'fixed') echo 'checked="checked"';/**/ ?> value="fixed" type="radio" id="range_selection_type"  name="range_selection_type"  onclick="javascript: jQuery('#togle_settings_range').slideDown('normal');jQuery('#togle_settings_range_dynamic').slideUp('normal');"  /></div>
                                <div style="float:left;width:420px;font-weight: bold;"><label for="range_start_day" ><?php _e('Selection of DYNAMIC number of days by TWO mouse click', 'wpdev-booking'); ?>: </label><input  <?php if ($range_selection_type == 'dynamic') echo 'checked="checked"';/**/ ?> value="dynamic" type="radio" id="range_selection_type"  name="range_selection_type"  onclick="javascript: jQuery('#togle_settings_range').slideUp('normal');jQuery('#togle_settings_range_dynamic').slideDown('normal');"  /></div>
                            </div>
                            <div style="width:100%;clear: both;"></div>

                            <table id="togle_settings_range" style="<?php if ($range_selection_type != 'fixed') echo 'display:none;';/**/ ?>" class="hided_settings_table">
                                <tr valign="top">
                                <th scope="row"><label for="range_selection_days_count" ><?php _e('Count of days', 'wpdev-booking'); ?>:</label><br><?php printf(__('in %srange to select%s', 'wpdev-booking'),'<span style="color:#888;font-weight:bold;">','</span>'); ?></th>
                                    <td><input value="<?php echo $range_selection_days_count; ?>" name="range_selection_days_count" id="range_selection_days_count" class="regular-text code" type="text" size="45"  />
                                        <span class="description"><?php printf(__('Type your %snumber of days for range selection%s', 'wpdev-booking'),'<b>','</b>');?></span>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="range_start_day" ><?php _e('Start day of range', 'wpdev-booking'); ?>:</label></th>
                                    <td>
                                        <select id="range_start_day" name="range_start_day" style="width:150px;">
                                            <option <?php if($range_start_day == '-1') echo "selected"; ?> value="-1"><?php _e('Any day of week', 'wpdev-booking'); ?></option>
                                            <option <?php if($range_start_day == '0') echo "selected"; ?> value="0"><?php _e('Sunday', 'wpdev-booking'); ?></option>
                                            <option <?php if($range_start_day == '1') echo "selected"; ?> value="1"><?php _e('Monday', 'wpdev-booking'); ?></option>
                                            <option <?php if($range_start_day == '2') echo "selected"; ?> value="2"><?php _e('Tuesday', 'wpdev-booking'); ?></option>
                                            <option <?php if($range_start_day == '3') echo "selected"; ?> value="3"><?php _e('Wednesday', 'wpdev-booking'); ?></option>
                                            <option <?php if($range_start_day == '4') echo "selected"; ?> value="4"><?php _e('Thursday', 'wpdev-booking'); ?></option>
                                            <option <?php if($range_start_day == '5') echo "selected"; ?> value="5"><?php _e('Friday', 'wpdev-booking'); ?></option>
                                            <option <?php if($range_start_day == '6') echo "selected"; ?> value="6"><?php _e('Saturday', 'wpdev-booking'); ?></option>
                                        </select>
                                        <span class="description"><?php _e('Select your start day of range selection at week', 'wpdev-booking');?></span>
                                    </td>
                                </tr>
                            </table>

                            <table id="togle_settings_range_dynamic" style="<?php if ($range_selection_type != 'dynamic') echo 'display:none;';/**/ ?>" class="hided_settings_table">
                                <tr valign="top">
                                    <th>
                                        <label><?php _e('Days selection number', 'wpdev-booking'); ?>:</label>
                                    </th>
                                    <td style="padding-top:5px;">
                                        <?php _e('min', 'wpdev-booking'); ?>:
                                        <input value="<?php echo $range_selection_days_count_dynamic; ?>" name="range_selection_days_count_dynamic" id="range_selection_days_count_dynamic" class="regular-text code" type="text" size="10" style="width:50px;"  />
                                        &nbsp;&nbsp;&nbsp;
                                         <?php _e('max', 'wpdev-booking'); ?>:
                                        <input value="<?php echo $range_selection_days_count_dynamic_max; ?>" name="range_selection_days_count_dynamic_max" id="range_selection_days_count_dynamic_max" class="regular-text code" type="text" size="10" style="width:50px;"  />
                                     
                                        <br /><span class="description"><?php printf(__('Type your %sminimum and maximum number of days for range selection%s', 'wpdev-booking'),'<b>','</b>');?></span>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row">
                                        <label for="range_selection_days_count_dynamic" ><?php _e('Specific days selections', 'wpdev-booking'); ?>:</label>
                                    </th>
                                    <td>
                                        <input value="<?php echo $range_selection_days_count_dynamic_specific; ?>" name="range_selection_days_count_dynamic_specific" id="range_selection_days_count_dynamic_specific" class="regular-text code" type="text" size="45"  />
                                        <span class="description"><?php printf(__('Type your %sspecific%s days number, which can be selected by visitors or leave it empty. Its can be several days seperated by comma. Example: 5,7', 'wpdev-booking'),'<b>','</b>');?></span>
                                    </td>
                                </tr>

                                <!--tr valign="top">
                                    <th scope="row">
                                        <label for="range_selection_days_count_dynamic" ><?php _e('Minimum days count', 'wpdev-booking'); ?>:</label>
                                        <br />
                                        <?php printf(__('in %srange to select%s', 'wpdev-booking'),'<span style="color:#888;font-weight:bold;">','</span>'); ?>
                                    </th>
                                    <td>
                                        <input value="<?php echo $range_selection_days_count_dynamic; ?>" name="range_selection_days_count_dynamic" id="range_selection_days_count_dynamic" class="regular-text code" type="text" size="45"  />
                                        <span class="description"><?php printf(__('Type your %sminimum number of days for range selection%s', 'wpdev-booking'),'<b>','</b>');?></span>
                                    </td>
                                </tr-->
                                <tr valign="top">
                                    <th scope="row"><label for="range_start_day_dynamic" ><?php _e('Start day of range', 'wpdev-booking'); ?>:</label></th>
                                    <td>
                                        <select id="range_start_day_dynamic" name="range_start_day_dynamic" style="width:150px;">
                                            <option <?php if($range_start_day_dynamic == '-1') echo "selected"; ?> value="-1"><?php _e('Any day of week', 'wpdev-booking'); ?></option>
                                            <option <?php if($range_start_day_dynamic == '0') echo "selected"; ?> value="0"><?php _e('Sunday', 'wpdev-booking'); ?></option>
                                            <option <?php if($range_start_day_dynamic == '1') echo "selected"; ?> value="1"><?php _e('Monday', 'wpdev-booking'); ?></option>
                                            <option <?php if($range_start_day_dynamic == '2') echo "selected"; ?> value="2"><?php _e('Tuesday', 'wpdev-booking'); ?></option>
                                            <option <?php if($range_start_day_dynamic == '3') echo "selected"; ?> value="3"><?php _e('Wednesday', 'wpdev-booking'); ?></option>
                                            <option <?php if($range_start_day_dynamic == '4') echo "selected"; ?> value="4"><?php _e('Thursday', 'wpdev-booking'); ?></option>
                                            <option <?php if($range_start_day_dynamic == '5') echo "selected"; ?> value="5"><?php _e('Friday', 'wpdev-booking'); ?></option>
                                            <option <?php if($range_start_day_dynamic == '6') echo "selected"; ?> value="6"><?php _e('Saturday', 'wpdev-booking'); ?></option>
                                        </select>
                                        <span class="description"><?php _e('Select your start day of range selection at week', 'wpdev-booking');?></span>
                                    </td>
                                </tr>
                            </table>


                    </td></tr>
                    </table>

                </td></tr>


            <?php
        }

        function settings_advanced_set_fixed_time(){
             if ( isset( $_POST['range_selection_start_time'] ) ) {

                     if (isset( $_POST['booking_recurrent_time'] ))     $booking_recurrent_time = 'On';
                     else                                               $booking_recurrent_time = 'Off';
                     update_bk_option( 'booking_recurrent_time' ,  $booking_recurrent_time );

                     if (isset( $_POST['range_selection_time_is_active'] ))     $range_selection_time_is_active = 'On';
                     else                                                  $range_selection_time_is_active = 'Off';
                     update_bk_option( 'booking_range_selection_time_is_active' ,  $range_selection_time_is_active );

                     $range_selection_start_time =  $_POST['range_selection_start_time'];
                     update_bk_option( 'booking_range_selection_start_time' , $range_selection_start_time );

                     $range_selection_end_time =  $_POST['range_selection_end_time'];
                     update_bk_option( 'booking_range_selection_end_time' , $range_selection_end_time );

             }
                $range_selection_time_is_active = get_bk_option( 'booking_range_selection_time_is_active');
                $range_selection_start_time  = get_bk_option( 'booking_range_selection_start_time');
                $range_selection_end_time  = get_bk_option( 'booking_range_selection_end_time');

                $booking_recurrent_time  = get_bk_option( 'booking_recurrent_time' ) ;
            ?>

                <tr valign="top" class="ver_premium">
                    <th scope="row">
                        <label for="booking_recurrent_time" ><?php _e('Use recurent time', 'wpdev-booking'); ?>:</label><br/><?php _e('for several days selection', 'wpdev-booking'); ?>
                    </th>
                    <td>
                        <input <?php if ($booking_recurrent_time == 'On') echo "checked";/**/ ?>  value="<?php echo $booking_recurrent_time; ?>" name="booking_recurrent_time" id="booking_recurrent_time" type="checkbox"  />
                        <span class="description"><?php _e(' Check this if you want to use recurent time for booking of several days. Its mean that middle days will be partially booked by actual times, otherwise time in booking form used as check in/out time for first and last time of booking.', 'wpdev-booking');?></span>
                    </td>
                </tr>

                <tr valign="top" class="ver_premium">
                    <th scope="row">
                        <label for="range_selection_time_is_active" ><?php _e('Use fixed time', 'wpdev-booking'); ?>:</label><br/><?php _e('in range selections', 'wpdev-booking'); ?>
                    </th>
                    <td>
                        <input <?php if ($range_selection_time_is_active == 'On') echo "checked";/**/ ?>  value="<?php echo $range_selection_time_is_active; ?>" name="range_selection_time_is_active" id="range_selection_time_is_active" type="checkbox"
                             onclick="javascript: if (this.checked) { alert('<?php _e('Warning','wpdev-booking'); echo '! '; _e('It will overwrite start time and end time from from customization.','wpdev-booking');?> '); jQuery('#togle_settings_range_times').slideDown('normal');} else  jQuery('#togle_settings_range_times').slideUp('normal');"
                                                                                                          />
                        <span class="description"><?php _e(' Check this  if you want to use a part of the day (not full day) at start and end day of selection range . It will overwrite start time and end time from from customization.', 'wpdev-booking');?></span>
                    </td>
                </tr>

                <tr valign="top" class="ver_premium"><td colspan="2">
                    <table id="togle_settings_range_times" style="<?php if ($range_selection_time_is_active != 'On') echo "display:none;";/**/ ?>" class="hided_settings_table">
                        <tr>
                        <th scope="row"><label for="range_selection_start_time" ><?php _e('Start time', 'wpdev-booking'); ?>:</label><br><?php printf(__('%sstart booking time%s', 'wpdev-booking'),'<span style="color:#888;font-weight:bold;">','</span>'); ?></th>
                            <td><input value="<?php echo $range_selection_start_time; ?>" name="range_selection_start_time" id="range_selection_start_time" class="wpdev-validates-as-time" type="text" size="5"  />
                                <span class="description"><?php printf(__('Type your %sstart%s time of booking for range selection', 'wpdev-booking'),'<b>','</b>');?></span>
                            </td>
                        </tr>

                        <tr>
                        <th scope="row"><label for="range_selection_end_time" ><?php _e('End time', 'wpdev-booking'); ?>:</label><br><?php printf(__('%send booking time%s', 'wpdev-booking'),'<span style="color:#888;font-weight:bold;">','</span>'); ?></th>
                        <td><input value="<?php echo $range_selection_end_time; ?>" name="range_selection_end_time" id="range_selection_end_time" class="wpdev-validates-as-time" type="text" size="5"   />
                                <span class="description"><?php printf(__('Type your %send%s time of booking for range selection', 'wpdev-booking'),'<b>','</b>');?></span>
                            </td>
                        </tr>
                    </table>
                </td></tr>


             <?php
        }


        // Show Cost Section in General booking Settings page
        function wpdev_bk_general_settings_cost_section() {

                 if ( isset( $_POST['paypal_price_period'] ) ) {
                     update_bk_option( 'booking_paypal_price_period' , $_POST['paypal_price_period'] );

                     if (isset( $_POST['is_time_apply_to_cost'] ))     $is_time_apply_to_cost = 'On';
                     else                                              $is_time_apply_to_cost = 'Off';
                     update_bk_option( 'booking_is_time_apply_to_cost' , $is_time_apply_to_cost );


                 }
                 $is_time_apply_to_cost = get_bk_option( 'booking_is_time_apply_to_cost'  );
              ?>
                                <div class='meta-box'>
                                  <div <?php $my_close_open_win_id = 'bk_settings_general_cost_options'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle','wpdev-booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                                        <h3 class='hndle'><span><?php _e('Costs', 'wpdev-booking'); ?></span></h3> <div class="inside">

                                            <table class="form-table"><tbody>

                                               <tr valign="top" class="ver_premium_hotel">
                                                    <th scope="row">
                                                        <label for="paypal_price_period" ><?php _e('Set the cost', 'wpdev-booking'); ?>:</label>
                                                    </th>
                                                    <td>

                                                     <select id="paypal_price_period" name="paypal_price_period">
                                                         <option <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'day') echo "selected"; ?> value="day"><?php _e('for 1 day', 'wpdev-booking'); ?></option>
                                                         <option <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'night') echo "selected"; ?> value="night"><?php _e('for 1 night', 'wpdev-booking'); ?></option>
                                                         <option <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'fixed') echo "selected"; ?> value="fixed"><?php _e('fixed summ', 'wpdev-booking'); ?></option>
                                                         <option <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'hour') echo "selected"; ?> value="hour"><?php _e('for 1 hour', 'wpdev-booking'); ?></option>
                                                     </select>
                                                     <span class="description"><?php _e(' Select your cost configuration.', 'wpdev-booking');?></span>

                                                    </td>
                                                </tr>


                                               <tr valign="top" class="ver_premium_hotel">
                                                    <th scope="row">
                                                        <label for="paypal_price_period" ><?php _e('Time impact to cost', 'wpdev-booking'); ?>:</label>
                                                    </th>
                                                    <td>

                                                    <input <?php if ($is_time_apply_to_cost == 'On') echo "checked";/**/ ?>  value="<?php echo $is_time_apply_to_cost; ?>" name="is_time_apply_to_cost" id="is_time_apply_to_cost" type="checkbox" style="margin:-3px 3px 0 0;" />
                                                        <span class="description"><?php printf(__(' Check this checkbox if you want that %stime selection%s at booking form is %sapply to cost calculation%s.', 'wpdev-booking'),'<strong>','</strong>','<strong>','</strong>');?></span>
                                                    </td>
                                                </tr>

                                                <?php make_bk_action('show_settings_for_activating_fixed_deposit'); ?>

                                            </tbody></table>
                                        <div class="clear" style="height:10px;"></div>



                               </div> </div> </div>

              <?php
        }

        // Show Auto Cancel Pending Section in General booking Settings page
        function wpdev_bk_general_settings_pending_auto_cancelation(){

                if ( isset( $_POST['Submit'] ) ) {



                      if (isset( $_POST['auto_approve_new_bookings_is_active'] ))      $auto_approve_new_bookings_is_active = 'On';
                      else                                                             $auto_approve_new_bookings_is_active = 'Off';
                      update_bk_option( 'booking_auto_approve_new_bookings_is_active', $auto_approve_new_bookings_is_active );

                      if (isset( $_POST['auto_cancel_pending_unpaid_bk_is_active'] ))      $auto_cancel_pending_unpaid_bk_is_active = 'On';
                      else                                                                 $auto_cancel_pending_unpaid_bk_is_active = 'Off';
                      update_bk_option( 'booking_auto_cancel_pending_unpaid_bk_is_active', $auto_cancel_pending_unpaid_bk_is_active );
                      if (isset($_POST['auto_cancel_pending_unpaid_bk_time']))
                        update_bk_option( 'booking_auto_cancel_pending_unpaid_bk_time', $_POST['auto_cancel_pending_unpaid_bk_time'] );

                      if (isset( $_POST['auto_cancel_pending_unpaid_bk_is_send_email'] ))      $auto_cancel_pending_unpaid_bk_is_send_email = 'On';
                      else                                                                     $auto_cancel_pending_unpaid_bk_is_send_email = 'Off';
                      update_bk_option( 'booking_auto_cancel_pending_unpaid_bk_is_send_email', $auto_cancel_pending_unpaid_bk_is_send_email );
                      if (isset($_POST['auto_cancel_pending_unpaid_bk_email_reason']))
                        update_bk_option( 'booking_auto_cancel_pending_unpaid_bk_email_reason', $_POST['auto_cancel_pending_unpaid_bk_email_reason'] );

                }
                $auto_approve_new_bookings_is_active       =  get_bk_option( 'booking_auto_approve_new_bookings_is_active' );
                $auto_cancel_pending_unpaid_bk_is_active   =  get_bk_option( 'booking_auto_cancel_pending_unpaid_bk_is_active' );
                $auto_cancel_pending_unpaid_bk_time        =  get_bk_option( 'booking_auto_cancel_pending_unpaid_bk_time' );

                $auto_cancel_pending_unpaid_bk_is_send_email =  get_bk_option( 'booking_auto_cancel_pending_unpaid_bk_is_send_email' );
                $auto_cancel_pending_unpaid_bk_email_reason  =  get_bk_option( 'booking_auto_cancel_pending_unpaid_bk_email_reason' );
            ?>
                <div class='meta-box'>
                     <div <?php $my_close_open_win_id = 'bk_settings_auto_cancel_pending_nk'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle','wpdev-booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                        <h3 class='hndle'><span><?php _e('Auto cancelation / auto approvement of bookings', 'wpdev-booking'); ?></span></h3> <div class="inside">

                                <table class="form-table settings-table">
                                    <tbody>

                                        <tr valign="top">
                                            <th scope="row">
                                                <label for="auto_approve_new_bookings_is_active" ><?php _e('Auto approve all new bookings', 'wpdev-booking'); ?>:</label>
                                            </th>
                                            <td>                  
                                                <input <?php if ($auto_approve_new_bookings_is_active == 'On') echo "checked"; ?>
                                                       value="<?php echo $auto_approve_new_bookings_is_active; ?>"
                                                       name="auto_approve_new_bookings_is_active"
                                                       id="auto_approve_new_bookings_is_active" type="checkbox" />
                                                <span class="description"><?php printf(__(' Check this checkbox to %sactivate%s auto approve of all new pending bookings.', 'wpdev-booking'),'<b>','</b>');?></span>
                                            </td>
                                        </tr>

                                        <tr valign="top">
                                            <th scope="row">
                                                <label for="auto_cancel_pending_unpaid_bk_is_active" ><?php _e('Auto cancel bookings', 'wpdev-booking'); ?>:</label>
                                            </th>
                                            <td>
                                                <input onMouseDown="javascript:
                                                        document.getElementById('auto_cancel_pending_unpaid_bk_time').disabled=this.checked;
                                                        document.getElementById('auto_cancel_pending_unpaid_bk_is_send_email').disabled=this.checked;
                                                        document.getElementById('auto_cancel_pending_unpaid_bk_email_reason').disabled=this.checked;
                                                                   "  <?php if ($auto_cancel_pending_unpaid_bk_is_active == 'On') echo "checked";/**/ ?>  value="<?php echo $auto_cancel_pending_unpaid_bk_is_active; ?>" name="auto_cancel_pending_unpaid_bk_is_active" id="auto_cancel_pending_unpaid_bk_is_active" type="checkbox" />
                                                <span class="description"><?php printf(__(' Check this checkbox to %sactivate%s cancel pending not paid bookings.', 'wpdev-booking'),'<b>','</b>');?></span>
                                            </td>
                                        </tr>

                                        <tr valign="top">
                                          <th scope="row">
                                            <label for="auto_cancel_pending_unpaid_bk_time" ><?php _e('Cancel bookings older', 'wpdev-booking'); ?>:</label>
                                          </th>
                                          <td>
                                              <select id="auto_cancel_pending_unpaid_bk_time" name="auto_cancel_pending_unpaid_bk_time" <?php if ($auto_cancel_pending_unpaid_bk_is_active != 'On') echo ' disabled="DISABLED" '; ?> >

                                                  <option <?php if($auto_cancel_pending_unpaid_bk_time == '1') echo "selected"; ?> value="1"><?php echo '1 '; _e('hour','wpdev-booking'); ?></option>
                                                  <?php
                                                    for ($i = 2; $i < 24; $i++) {
                                                      ?> <option <?php if($auto_cancel_pending_unpaid_bk_time == $i) echo "selected"; ?> value="<?php echo $i; ?>"><?php echo $i,' ';  _e('hours','wpdev-booking'); ?></option> <?php
                                                    }
                                                  ?>
                                                  <option <?php if($auto_cancel_pending_unpaid_bk_time == '24') echo "selected"; ?> value="24"><?php echo '1 '; _e('day','wpdev-booking'); ?></option>
                                                  <?php
                                                    for ($i = 2; $i < 32; $i++) {
                                                      ?> <option <?php if($auto_cancel_pending_unpaid_bk_time == ($i*24) ) echo "selected"; ?> value="<?php echo ($i*24); ?>"><?php echo $i,' ';  _e('days','wpdev-booking'); ?></option> <?php
                                                    }
                                                  ?>
                                             </select>
                                             <span class="description"><?php _e(' Cancel only pending, not paid bookings, which is older then at this selection.', 'wpdev-booking');?></span>
                                          </td>
                                        </tr>



                                        <tr valign="top">
                                            <th scope="row">
                                                <label for="auto_cancel_pending_unpaid_bk_is_send_email" ><?php _e('Is send cancellation email', 'wpdev-booking'); ?>:</label>
                                            </th>
                                            <td>
                                                <input onMouseDown="javascript: document.getElementById('auto_cancel_pending_unpaid_bk_email_reason').disabled=this.checked; "  <?php if ($auto_cancel_pending_unpaid_bk_is_send_email == 'On') echo "checked";/**/ ?>  value="<?php echo $auto_cancel_pending_unpaid_bk_is_send_email; ?>" name="auto_cancel_pending_unpaid_bk_is_send_email" id="auto_cancel_pending_unpaid_bk_is_send_email" type="checkbox"  <?php if ($auto_cancel_pending_unpaid_bk_is_active != 'On') echo ' disabled="DISABLED" '; ?>  />
                                                <span class="description"><?php printf(__(' Check this checkbox to %ssend%s cancellation email for this process.', 'wpdev-booking'),'<b>','</b>');?></span>
                                            </td>
                                        </tr>

                                        <tr valign="top">
                                        <th scope="row" style="width:170px;"><label for="auto_cancel_pending_unpaid_bk_email_reason" ><?php _e('Description of cancellation', 'wpdev-booking'); ?>:</label></th>
                                            <td><input value="<?php echo $auto_cancel_pending_unpaid_bk_email_reason; ?>" name="auto_cancel_pending_unpaid_bk_email_reason" id="auto_cancel_pending_unpaid_bk_email_reason" class="regular-text code" type="text" size="45"  style="width:300px;" <?php if ( ($auto_cancel_pending_unpaid_bk_is_active != 'On') || ($auto_cancel_pending_unpaid_bk_is_send_email != 'On') ) echo ' disabled="DISABLED" '; ?>  />
                                                <span class="description"><?php printf(__('Type description of %scancellation%s for email template.', 'wpdev-booking'),'<b>','</b>');?></span>
                                            </td>
                                        </tr>



                                    </tbody>
                                </table>

               </div> </div> </div>
            <?php

        }


        function wpdev_booking_settings_top_menu_submenu_line(){

            if ( (isset($_GET['tab'])) && ( $_GET['tab'] == 'payment') ) {
            ?>
                <div class="booking-submenu-tab-container">
                    <div class="nav-tabs booking-submenu-tab-insidecontainer">
                        <script type="text/javascript">
                            function recheck_active_itmes_in_top_menu( internal_checkbox, top_checkbox ){
                                if (document.getElementById( internal_checkbox ).checked != document.getElementById( top_checkbox ).checked ) {
                                    document.getElementById( top_checkbox ).checked = document.getElementById( internal_checkbox ).checked;
                                    if ( document.getElementById( top_checkbox ).checked )
                                        jQuery('#' + top_checkbox ).parent().removeClass('booking-submenu-tab-disabled');
                                    else
                                        jQuery('#' + top_checkbox ).parent().addClass('booking-submenu-tab-disabled');
                                }
                            }
                        </script>
                        <?php make_bk_action('wpdev_bk_payment_show_tab_in_top_settings' );  ?>
                        <span class="booking-submenu-tab-separator-vertical"></span>

                        <a href="#" onclick="javascript:jQuery('.visibility_container').css('display','none');jQuery('#visibility_container_billing').css('display','block');jQuery('.nav-tab').removeClass('booking-submenu-tab-selected');jQuery(this).addClass('booking-submenu-tab-selected');"
                           rel="tooltip" class="tooltip_bottom nav-tab  booking-submenu-tab" original-title="<?php _e('Customization of billing fields, which automatically assign from booking form to billing form', 'wpdev-booking');?>" >
                            <?php _e('Billing form fields', 'wpdev-booking');?></a>

                        <input type="button" class="button-primary" value="<?php _e('Save settings','wpdev-booking'); ?>" style="float:right;margin:0px 5px 0px 0px;"
                               onclick="document.forms['post_settings_payment_integration'].submit();">
                    </div>
                </div>
              <?php
            }
        }

     //   A C T I V A T I O N   A N D   D E A C T I V A T I O N    O F   T H I S   P L U G I N  ///////////////////////////////////////////////////

            // Activate
            function pro_activate() {
                global $wpdb;

                update_bk_option( 'booking_skin', WPDEV_BK_PLUGIN_URL . '/inc/skins/premium-marine.css');

                add_bk_option( 'booking_recurrent_time' , 'Off' );

                make_bk_action( 'wpdev_bk_payment_activate_system');


                add_bk_option( 'booking_billing_customer_email', '' );
                add_bk_option( 'booking_billing_firstnames', '' );
                add_bk_option( 'booking_billing_surname', '' );
                add_bk_option( 'booking_billing_phone', '' );
                add_bk_option( 'booking_billing_address1', '' );
                add_bk_option( 'booking_billing_city', '' );
                add_bk_option( 'booking_billing_country', '' );
                add_bk_option( 'booking_billing_post_code', '' );
                /////////////////////////////////////////////////////////////////////////////////////////////////////////////


                add_bk_option( 'booking_auto_approve_new_bookings_is_active' , 'Off' );
                add_bk_option( 'booking_auto_cancel_pending_unpaid_bk_is_active' , 'Off' );
                add_bk_option( 'booking_auto_cancel_pending_unpaid_bk_time' ,'24');
                add_bk_option( 'booking_auto_cancel_pending_unpaid_bk_is_send_email' , 'On' );
                add_bk_option( 'booking_auto_cancel_pending_unpaid_bk_email_reason', __('This booking is cancelled, because we do not receive payment and administrator do not approve it.','wpdev-booking') );


                add_bk_option( 'booking_range_selection_type', 'fixed');
                add_bk_option( 'booking_range_selection_is_active', 'Off');
                add_bk_option( 'booking_range_selection_days_count','3');
                add_bk_option( 'booking_range_selection_days_max_count_dynamic',365);
                add_bk_option( 'booking_range_selection_days_specific_num_dynamic','');
                add_bk_option( 'booking_range_start_day' , '-1' );
                add_bk_option( 'booking_range_selection_days_count_dynamic','1');
                add_bk_option( 'booking_range_start_day_dynamic' , '-1' );
                add_bk_option( 'booking_range_selection_time_is_active', 'Off');
                add_bk_option( 'booking_range_selection_start_time','12:00');
                add_bk_option( 'booking_range_selection_end_time','14:00');

                add_bk_option( 'booking_time_format', 'H:i');


                add_bk_option( 'booking_email_payment_request_adress',htmlspecialchars('"Booking system" <' .get_option('admin_email').'>'));
                add_bk_option( 'booking_email_payment_request_subject',__('You need to make payment for booking', 'wpdev-booking'));
                $blg_title = get_option('blogname'); $blg_title = str_replace('"', '', $blg_title);$blg_title = str_replace("'", '', $blg_title);
                add_bk_option( 'booking_email_payment_request_content',htmlspecialchars(sprintf(__('You need to make payment %s for booking %s at %s. %s You can make payment at this page: %s  Thank you, %s', 'wpdev-booking'),'[cost]','[bookingtype]','[dates]','<br/><br/>[paymentreason]<br/><br/>[content]<br/><br/>', '[visitorbookingpayurl]<br/><br/>' , $blg_title.'<br/>[siteurl]')));

                add_bk_option( 'booking_is_email_payment_request_adress', 'On' );
                add_bk_option( 'booking_is_email_payment_request_send_copy_to_admin' , 'Off' );

              if ( wpdev_bk_is_this_demo() )
                 update_bk_option( 'booking_form', str_replace('\\n\\','', $this->reset_to_default_form('payment') ) );


                if  ($this->is_field_in_table_exists('bookingtypes','cost') == 0){
                    $simple_sql = "ALTER TABLE ".$wpdb->prefix ."bookingtypes ADD cost VARCHAR(100) NOT NULL DEFAULT '0'";
                    $wpdb->query($wpdb->prepare($simple_sql));
                    $wpdb->query($wpdb->prepare( "UPDATE ".$wpdb->prefix ."bookingtypes SET cost = '25'"));
                }


                if  ($this->is_field_in_table_exists('booking','cost') == 0){ // Add remark field
                    $simple_sql = "ALTER TABLE ".$wpdb->prefix ."booking ADD cost FLOAT(7,2) NOT NULL DEFAULT 0.00";
                    $wpdb->query($wpdb->prepare($simple_sql));
                }

                if  ($this->is_field_in_table_exists('booking','pay_status') == 0){ // Add remark field
                    $simple_sql = "ALTER TABLE ".$wpdb->prefix ."booking ADD pay_status VARCHAR(200) NOT NULL DEFAULT ''";
                    $wpdb->query($wpdb->prepare($simple_sql));
                }

                if  ($this->is_field_in_table_exists('booking','pay_request') == 0){ // Add remark field
                    $simple_sql = "ALTER TABLE ".$wpdb->prefix ."booking ADD pay_request SMALLINT(3) NOT NULL DEFAULT 0";
                    $wpdb->query($wpdb->prepare($simple_sql));
                }


                if ( wpdev_bk_is_this_demo() )        {
                    update_bk_option( 'booking_is_use_captcha' , 'Off' );
                    update_bk_option( 'booking_is_show_legend' , 'Off' );

                    update_bk_option( 'booking_billing_customer_email', ' email' );
                    update_bk_option( 'booking_billing_firstnames', ' name' );
                    update_bk_option( 'booking_billing_surname', ' secondname' );
                    update_bk_option( 'booking_billing_address1', ' address' );
                    update_bk_option( 'booking_billing_city', ' city' );
                    update_bk_option( 'booking_billing_country', 'country' );
                    update_bk_option( 'booking_billing_post_code', ' postcode' );

                    update_bk_option( 'booking_sage_vendor_name', 'wpdevelop' );
                    update_bk_option( 'booking_sage_encryption_password', 'FfCDQjLiM524VtE7' );
                    update_bk_option( 'booking_sage_curency', 'USD' );
                    update_bk_option( 'booking_sage_transaction_type', 'PAYMENT' );

                    update_bk_option( 'booking_sage_is_active', 'On' );

                    $wpdb->query($wpdb->prepare( "UPDATE ".$wpdb->prefix ."bookingtypes SET cost = '30' WHERE 	booking_type_id=1"));
                    $wpdb->query($wpdb->prepare( "UPDATE ".$wpdb->prefix ."bookingtypes SET cost = '35' WHERE 	booking_type_id=2"));
                    $wpdb->query($wpdb->prepare( "UPDATE ".$wpdb->prefix ."bookingtypes SET cost = '40' WHERE 	booking_type_id=3"));
                    $wpdb->query($wpdb->prepare( "UPDATE ".$wpdb->prefix ."bookingtypes SET cost = '50' WHERE 	booking_type_id=4"));

                    update_bk_option( 'booking_multiple_day_selections' , 'Off' );

                    //form fields setting
                     update_bk_option( 'booking_form',  '        [calendar]
        <div style="text-align:left">
        <p>Time: [select rangetime "10:00 - 12:00" "12:00 - 14:00" "14:00 - 16:00" "16:00 - 18:00" "18:00 - 20:00" ]</p>
        <p>First Name (required):<br />  [text* name] </p>
        <p>Last Name (required):<br />  [text* secondname] </p>
        <p>Email (required):<br />  [email* email] </p>
        <p>Address (required):<br />  [text* address] </p>
        <p>City(required):<br />  [text* city] </p>
        <p>Post code(required):<br />  [text* postcode] </p>
        <p>Country(required):<br />  [country] </p>
        <p>Phone:<br />  [text phone] </p>
        <p>Visitors:<br />  [select visitors "1" "2" "3" "4"] Children: [checkbox children ""]</p>
        <p>Details:<br /> [textarea details] </p>
        <p>[captcha]</p>
        <p>[submit "Send"]</p>
        </div>' );

                     update_bk_option( 'booking_form_show',  ' <div style="text-align:left">
        <strong>Time range </strong>:  <span class="fieldvalue">[rangetime]</span><br/>
        <strong>First Name</strong>:<span class="fieldvalue">[name]</span><br/>
        <strong>Last Name</strong>:<span class="fieldvalue">[secondname]</span><br/>
        <strong>Email</strong>:<span class="fieldvalue">[email]</span><br/>
        <strong>Address</strong>:<span class="fieldvalue">[address]</span><br/>
        <strong>City</strong>:<span class="fieldvalue">[city]</span><br/>
        <strong>Post code</strong>:<span class="fieldvalue">[postcode]</span><br/>
        <strong>Country</strong>:<span class="fieldvalue">[country]</span><br/>
        <strong>Phone</strong>:<span class="fieldvalue">[phone]</span><br/>
        <strong>Number of visitors</strong>:<span class="fieldvalue"> [visitors]</span><br/>
        <strong>Children</strong>:<span class="fieldvalue"> [children]</span><br/>
        <strong>Details</strong>:<br /><span class="fieldvalue"> [details]</span>
        </div>' );

                update_bk_option( 'booking_paypal_return_url', site_url() .'/successful/' );
                update_bk_option( 'booking_paypal_cancel_return_url', site_url() . '/failed/' );
                update_bk_option( 'booking_sage_order_Successful', site_url() .'/successful/' );
                update_bk_option( 'booking_sage_order_Failed', site_url() . '/failed/' );
                update_bk_option( 'booking_sage_is_auto_approve_cancell_booking' , 'On' );

                        $wp_queries = array();
                        $wp_queries[] = "UPDATE ".$wpdb->prefix ."bookingtypes SET title = '". __('Resource #1', 'wpdev-booking') ."' WHERE title = '". __('Appartment #1', 'wpdev-booking') ."' ;";
                        $wp_queries[] = "UPDATE ".$wpdb->prefix ."bookingtypes SET title = '". __('Resource #2', 'wpdev-booking') ."' WHERE title = '". __('Appartment #2', 'wpdev-booking') ."' ;";
                        $wp_queries[] = "UPDATE ".$wpdb->prefix ."bookingtypes SET title = '". __('Resource #3', 'wpdev-booking') ."' WHERE title = '". __('Appartment #3', 'wpdev-booking') ."' ;";
                        foreach ($wp_queries as $wp_q) $wpdb->query($wpdb->prepare($wp_q));

                        update_bk_option( 'booking_time_format' , 'g:i a' );
                }



            }

            //Decativate
            function pro_deactivate(){


                delete_bk_option( 'booking_recurrent_time' );

                make_bk_action( 'wpdev_bk_payment_deactivate_system');


                delete_bk_option( 'booking_email_payment_request_adress');
                delete_bk_option( 'booking_email_payment_request_subject');
                delete_bk_option( 'booking_email_payment_request_content');
                delete_bk_option( 'booking_is_email_payment_request_adress' );
                delete_bk_option( 'booking_is_email_payment_request_send_copy_to_admin' );

                delete_bk_option( 'booking_billing_customer_email' );
                delete_bk_option( 'booking_billing_firstnames' );
                delete_bk_option( 'booking_billing_surname' );
                delete_bk_option( 'booking_billing_phone' );
                delete_bk_option( 'booking_billing_address1' );
                delete_bk_option( 'booking_billing_city' );
                delete_bk_option( 'booking_billing_country' );
                delete_bk_option( 'booking_billing_post_code' );


                delete_bk_option( 'booking_auto_approve_new_bookings_is_active'  );
                delete_bk_option( 'booking_auto_cancel_pending_unpaid_bk_is_active' );
                delete_bk_option( 'booking_auto_cancel_pending_unpaid_bk_time' );
                delete_bk_option( 'booking_auto_cancel_pending_unpaid_bk_is_send_email' );
                delete_bk_option( 'booking_auto_cancel_pending_unpaid_bk_email_reason' );


                delete_bk_option( 'booking_range_selection_type');
                delete_bk_option( 'booking_range_selection_is_active');
                delete_bk_option( 'booking_range_selection_days_count');
                delete_bk_option( 'booking_range_selection_days_max_count_dynamic');
                delete_bk_option( 'booking_range_selection_days_specific_num_dynamic');

                delete_bk_option( 'booking_range_start_day'   );
                delete_bk_option( 'booking_range_selection_days_count_dynamic');
                delete_bk_option( 'booking_range_start_day_dynamic'   );
                delete_bk_option( 'booking_range_selection_time_is_active');
                delete_bk_option( 'booking_range_selection_start_time');
                delete_bk_option( 'booking_range_selection_end_time');

                delete_bk_option( 'booking_time_format');
            }

    }
}

?>