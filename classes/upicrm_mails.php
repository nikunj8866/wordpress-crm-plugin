<?php

/**
 * Class UpiCRMMails
 */
if (!class_exists('UpiCRMMails')) {

    class UpiCRMMails extends WP_Widget {

        var $wpdb;

        public function __construct() {
            global $wpdb;
            $this->wpdb = &$wpdb;
        }

        function get() {
            //get mails
            $query = "SELECT * FROM " . upicrm_db() . "mails WHERE `mail_event` NOT LIKE 'custom_%'";
            $rows = $this->wpdb->get_results($query);
            return $rows;
        }

        function get_all() {
            //get mails
            $query = "SELECT * FROM " . upicrm_db() . "mails";
            $rows = $this->wpdb->get_results($query);
            return $rows;
        }

        function get_custom() {
            //get mails
            $query = "SELECT * FROM " . upicrm_db() . "mails WHERE `mail_event` LIKE 'custom_%'";
            $rows = $this->wpdb->get_results($query);
            return $rows;
        }

        function add($insertArr) {
            //add mails
            $this->wpdb->insert(upicrm_db() . "mails", $insertArr);
        }

        function update($updateArr, $mail_id) {
            //update lead mail
            $this->wpdb->update(upicrm_db() . "mails", $updateArr, array("mail_id" => $mail_id));
        }

        function remove($mail_id) {
            //delete mail template
            $this->wpdb->delete(upicrm_db() . "mails", array("mail_id" => $mail_id));
        }

        function get_new_id_to_insert() {
            //use this anytime you want to add custom email with: 'custom_' in the start of the string
            $query = "SELECT AUTO_INCREMENT as `county`
        FROM  INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA  = '" . DB_NAME . "'
        AND TABLE_NAME = '" . upicrm_db() . "mails'";
            $rows = $this->wpdb->get_results($query);
            return @$rows[0]->county ? $rows[0]->county : 0;
        }

        function update2($arr) {
            //update mail by arr - key = mail_event

            foreach ($arr as $key => $mail) {
                if ($key != "submit") {
                    foreach ($mail as $key2 => $value) {
                        $updateArr[$key2] = $mail[$key2];
                    }
                    $this->wpdb->update(upicrm_db() . "mails", $updateArr, array("mail_event" => $key));
                }
            }
        }

        function check_parent_email_exist($msg_id)
        {
            global $wpdb;
            $query = "SELECT * FROM " . upicrm_db() . "leads_email_log WHERE `msg_id` = '$msg_id'";
            $rows = $this->wpdb->get_results($query);
            return $this->wpdb->num_rows;
        }

        function send($lead_id, $event, $to = "", $cancel_cc = false) {
            //send mail
            add_filter('wp_mail_from_name', array($this, 'filter_change_mail_from_name'));

            $UpiCRMUIBuilder = new UpiCRMUIBuilder();
            $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
            $UpiCRMLeads = new UpiCRMLeads();
            $UpiCRMUsers = new UpiCRMUsers();
            $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();

            $lead = $UpiCRMLeads->get_by_id($lead_id);
            $getNamesMap = $UpiCRMFieldsMapping->get_all_by($lead->source_id, $lead->source_type);
            $list_option = $UpiCRMUIBuilder->get_list_option();
            $mail = $this->get_by_event($event);

            $message = nl2br($mail->mail_content);
            $subject = $mail->mail_subject;
            $default_email = get_option('upicrm_default_email');
            $extra_email = get_option('upicrm_extra_email');

            $LeadVarText = '<table width="100%" border="0" cellpadding="5" cellspacing="2">';
            foreach ($list_option as $key => $arr) {
                foreach ($arr as $key2 => $value) {
                    $getValue = $UpiCRMUIBuilder->lead_routing($lead, $key, $key2, $getNamesMap, true);
                    if ($getValue != "") {
                        $LeadVarText .= '<tr bgcolor="#E6E6FA"><td><strong>' . $value . '</strong></td></tr>';
                        $LeadVarText .= '<tr bgcolor="#ffffff"><td>&nbsp;&nbsp;&nbsp;' . $getValue;
                        $LeadVarText .= '</td></tr>';
                    }
                }
            }

            $LeadVarTextNoHTML = "";
            foreach ($list_option as $key => $arr) {
                foreach ($arr as $key2 => $value) {
                    $getValue = $UpiCRMUIBuilder->lead_routing($lead, $key, $key2, $getNamesMap, true);
                    if ($getValue != "") {
                        $fields[$value] = $getValue;
                        $LeadVarTextNoHTML .= "{$value}: {$getValue}" . "\r\n";
                    }
                }
            }
            $message = str_replace("[lead-plaintext]", $LeadVarTextNoHTML, $message);
            $message = str_replace("[lead]", $LeadVarText, $message);
            $message = str_replace("[url]", get_site_url(), $message);
            $message = str_replace("[assigned-to]", $UpiCRMUsers->get_by_id($lead->user_id), $message);
            $message = str_replace("[lead-status]", $UpiCRMLeadsStatus->get_status_name_by_id($lead->lead_status_id), $message);
            foreach ($list_option as $key => $arr) {
                foreach ($arr as $key2 => $value) {
                    if (isset($fields)) {
                        $message = str_replace("[field-$value]", $fields[$value], $message);
                    }
                }
            }
            $subject = str_replace("[url]", get_site_url(), $subject);
            $subject = str_replace("[assigned-to]", $UpiCRMUsers->get_by_id($lead->user_id), $subject);
            $subject = str_replace("[lead-status]", $UpiCRMLeadsStatus->get_status_name_by_id($lead->lead_status_id), $subject);
            foreach ($list_option as $key => $arr) {
                foreach ($arr as $key2 => $value) {
                    if (isset($fields)) {
                        $subject = str_replace("[field-$value]", $fields[$value], $subject);
                    }
                }
            }

            $headers = "";
            if (!get_option('upicrm_cancel_email_from')) {
                $headers .= "From: researchGiant.com <crm@researchgiant.com>" . "\r\n";
            }

            $headers .= 'MIME-Version: 1.0' . "\r\n";
            if (get_option('upicrm_email_format') == 1)
                $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
            if (get_option('upicrm_email_format') == 2)
                $headers .= 'Content-type: text/plain; charset=UTF-8' . "\r\n";
            $cc = "";
            if ($extra_email != "")
                $cc .= $extra_email . " ";
            if ($to != "")
                $cc .= $default_email;
            else {
                $to = get_userdata($lead->user_id)->user_email;
            }
            if ($mail->mail_cc != "")
                $cc .= $mail->mail_cc;

            if ($event == 'change_lead_status' && get_the_author_meta('upicrm_user_manager_status_change_note', $lead->user_id) == 1) {
                $MyUser = $UpiCRMUsers->get_inside_by_user_id($lead->user_id);
                $user_info = get_userdata($MyUser->user_parent_id);
                $cc .= ' ' . $user_info->user_email;
            }
            $cc = str_replace(" ", ",", $cc);

            if (!$cancel_cc) {
                $headers .= "Cc: {$cc}" . "\r\n";
            }

            /* add_filter( 'wp_mail_from', 'custom_wp_mail_from' );
              add_filter( 'wp_mail_from', function($email){
              return $mail->mail_from;
              }); */
            $is_mail_sent = wp_mail($to, $subject, $message, $headers);
            if (!$is_mail_sent && !get_option('upicrm_cancel_email_failsafe')) {
                //echo('WP_MAIL not sending, try send from MAIL....');
                $message = wordwrap($message, 70, "\r\n");
                @mail($to, $subject, $message, $headers);
            }
        }

        function get_by_event($mail_event) {
            $query = "SELECT * FROM " . upicrm_db() . "mails WHERE `mail_event`='{$mail_event}'";
            $rows = $this->wpdb->get_results($query);
            return $rows[0];
        }

        function get_by_id($mail_id) {
            $query = "SELECT * FROM " . upicrm_db() . "mails WHERE `mail_id`='{$mail_id}'";
            $rows = $this->wpdb->get_results($query);
            return $rows[0];
        }

        function filter_change_mail_from_name($old) {
            return get_option('upicrm_sender_email');
            //return 'UpiCRM';
        }
        
        function send_csv($lead_id) {
            
            $to = get_option('upicrm_send_csv_get_mail');
            $message = __('Attached CSV file', 'upicrm');
            $subject = __('New CSV lead from UpiCRM', 'upicrm');
            $headers = 'From: '.get_option('upicrm_sender_email').' <crm@researchgiant.com>' . "\r\n";

            
            $path = upicrm_lead_to_csv_output($lead_id);
            $attachments = array($path);

            $is_mail_sent = @wp_mail($to, $subject, $message, $headers,$attachments);
            if ($is_mail_sent) {
                upicrm_remove_lead_to_csv_file($path);
            }
        }

        function send_lead_email($sendAr = array())
        {
            $UpiCRMUIBuilder = new UpiCRMUIBuilder();
            $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
            $UpiCRMLeads = new UpiCRMLeads();
            $UpiCRMUsers = new UpiCRMUsers();
            $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
            $UpiCRMLeadsChangesLog = new UpiCRMLeadsChangesLog();

            $lead_id = $sendAr['lead_id'];
            $to = $sendAr['mailTo'];
            $cc = $sendAr['mailCC'];
            $subject = $sendAr['mailSubject'];
            $content = $sendAr['mailBody'];

            $lead = $UpiCRMLeads->get_by_id($lead_id);
            $getNamesMap = $UpiCRMFieldsMapping->get_all_by($lead->source_id, $lead->source_type);
            $list_option = $UpiCRMUIBuilder->get_list_option();
            $mail = $this->get_by_event($event);

            $message = nl2br($content);

            $default_email = get_option('upicrm_default_email');
            $extra_email = get_option('upicrm_extra_email');

            $LeadVarText = '<table width="100%" border="0" cellpadding="5" cellspacing="2">';
            foreach ($list_option as $key => $arr) {
                foreach ($arr as $key2 => $value) {
                    $getValue = $UpiCRMUIBuilder->lead_routing($lead, $key, $key2, $getNamesMap, true);
                    if ($getValue != "") {
                        $LeadVarText .= '<tr bgcolor="#E6E6FA"><td><strong>' . $value . '</strong></td></tr>';
                        $LeadVarText .= '<tr bgcolor="#ffffff"><td>&nbsp;&nbsp;&nbsp;' . $getValue;
                        $LeadVarText .= '</td></tr>';
                    }
                }
            }

            $LeadVarText .= '</table>';

            $LeadVarTextNoHTML = "";
            foreach ($list_option as $key => $arr) {
                foreach ($arr as $key2 => $value) {
                    $getValue = $UpiCRMUIBuilder->lead_routing($lead, $key, $key2, $getNamesMap, true);
                    if ($getValue != "") {
                        $fields[$value] = $getValue;
                        $LeadVarTextNoHTML .= "{$value}: {$getValue}" . "\r\n";
                    }
                }
            }
            $message = str_replace("[lead-plaintext]", $LeadVarTextNoHTML, $message);
            $message = str_replace("[lead]", $LeadVarText, $message);
            $message = str_replace("[url]", get_site_url(), $message);
            $message = str_replace("[assigned-to]", $UpiCRMUsers->get_by_id($lead->user_id), $message);
            $message = str_replace("[lead-status]", $UpiCRMLeadsStatus->get_status_name_by_id($lead->lead_status_id), $message);
            foreach ($list_option as $key => $arr) {
                foreach ($arr as $key2 => $value) {
                    if (isset($fields)) {
                        $message = str_replace("[field-$key2]", $fields[$value], $message);
                    }
                }
            }

            $subject = str_replace("[url]", get_site_url(), $subject);
            $subject = str_replace("[assigned-to]", $UpiCRMUsers->get_by_id($lead->user_id), $subject);
            $subject = str_replace("[lead-status]", $UpiCRMLeadsStatus->get_status_name_by_id($lead->lead_status_id), $subject);
            foreach ($list_option as $key => $arr) {
                foreach ($arr as $key2 => $value) {
                    if (isset($fields)) {
                        $subject = str_replace("[field-$key2]", $fields[$value], $subject);
                    }
                }
            }

            $headers = "";
            if (!get_option('upicrm_cancel_email_from')) {
                $headers .= "From: researchGiant.com <crm@researchgiant.com>" . "\r\n";
            }
            $headers .= "Reply-To: crm@researchgiant.com" . "\r\n";

            $headers .= 'MIME-Version: 1.0' . "\r\n";
            if (get_option('upicrm_email_format') == 1)
                $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
            if (get_option('upicrm_email_format') == 2)
                $headers .= 'Content-type: text/plain; charset=UTF-8' . "\r\n";
            
            $cc = str_replace(" ", ",", $cc);

            if (!$cancel_cc) {
                $headers .= "Cc: {$cc}" . "\r\n";
            }

            /* add_filter( 'wp_mail_from', 'custom_wp_mail_from' );
              add_filter( 'wp_mail_from', function($email){
              return $mail->mail_from;
              }); */
            $message .= '<div style="display:none" id="lead_id_hidden">'.$lead_id.'</div>';
            $is_mail_sent = wp_mail($to, $subject, $message, $headers);
            
            if (!$is_mail_sent && !get_option('upicrm_cancel_email_failsafe')) {
                //echo('WP_MAIL not sending, try send from MAIL....');
                $message = wordwrap($message, 70, "\r\n");
                @mail($to, $subject, $message, $headers);
            }
            $msg_id = get_option('last_send_msg_id', true);
            $_POST['msg_id'] = $msg_id;
            $_POST['mailBody'] = $message;
            $_POST['mailSubject'] = $subject;
            $_POST['timestamp'] = time();
            $_POST['from'] = $UpiCRMUsers->get_by_id(get_current_user_id());
            $html =  $UpiCRMLeadsChangesLog->add_email_send_log($_POST);
            return $html;
        }

        function send_reply_lead_email($sendAr = array())
        {
            $UpiCRMUIBuilder = new UpiCRMUIBuilder();
            $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
            $UpiCRMLeads = new UpiCRMLeads();
            $UpiCRMUsers = new UpiCRMUsers();
            $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
            $UpiCRMLeadsChangesLog = new UpiCRMLeadsChangesLog();

            $lead_id = $sendAr['lead_id'];
            $to = $sendAr['reply_mailTo'];
            $cc = $sendAr['reply_mailCC'];
            $subject = $sendAr['reply_mailSubject'];
            $content = $sendAr['reply_mailBody'];
            $parent_email_id = $sendAr['parent_email_id'];

            $lead = $UpiCRMLeads->get_by_id($lead_id);
            $getNamesMap = $UpiCRMFieldsMapping->get_all_by($lead->source_id, $lead->source_type);
            $list_option = $UpiCRMUIBuilder->get_list_option();
            $mail = $this->get_by_event($event);

            $message = nl2br($content);

            $default_email = get_option('upicrm_default_email');
            $extra_email = get_option('upicrm_extra_email');

            $LeadVarText = '<table width="100%" border="0" cellpadding="5" cellspacing="2">';
            foreach ($list_option as $key => $arr) {
                foreach ($arr as $key2 => $value) {
                    $getValue = $UpiCRMUIBuilder->lead_routing($lead, $key, $key2, $getNamesMap, true);
                    if ($getValue != "") {
                        $LeadVarText .= '<tr bgcolor="#E6E6FA"><td><strong>' . $value . '</strong></td></tr>';
                        $LeadVarText .= '<tr bgcolor="#ffffff"><td>&nbsp;&nbsp;&nbsp;' . $getValue;
                        $LeadVarText .= '</td></tr>';
                    }
                }
            }

            $LeadVarText .= '</table>';

            $LeadVarTextNoHTML = "";
            foreach ($list_option as $key => $arr) {
                foreach ($arr as $key2 => $value) {
                    $getValue = $UpiCRMUIBuilder->lead_routing($lead, $key, $key2, $getNamesMap, true);
                    if ($getValue != "") {
                        $fields[$value] = $getValue;
                        $LeadVarTextNoHTML .= "{$value}: {$getValue}" . "\r\n";
                    }
                }
            }
            $message = str_replace("[lead-plaintext]", $LeadVarTextNoHTML, $message);
            $message = str_replace("[lead]", $LeadVarText, $message);
            $message = str_replace("[url]", get_site_url(), $message);
            $message = str_replace("[assigned-to]", $UpiCRMUsers->get_by_id($lead->user_id), $message);
            $message = str_replace("[lead-status]", $UpiCRMLeadsStatus->get_status_name_by_id($lead->lead_status_id), $message);
            foreach ($list_option as $key => $arr) {
                foreach ($arr as $key2 => $value) {
                    if (isset($fields)) {
                        $message = str_replace("[field-$key2]", $fields[$value], $message);
                    }
                }
            }
            $subject = str_replace("[url]", get_site_url(), $subject);
            $subject = str_replace("[assigned-to]", $UpiCRMUsers->get_by_id($lead->user_id), $subject);
            $subject = str_replace("[lead-status]", $UpiCRMLeadsStatus->get_status_name_by_id($lead->lead_status_id), $subject);
            foreach ($list_option as $key => $arr) {
                foreach ($arr as $key2 => $value) {
                    if (isset($fields)) {
                        $subject = str_replace("[field-$key2]", $fields[$value], $subject);
                    }
                }
            }

            $headers = "";
            if (!get_option('upicrm_cancel_email_from')) {
                $headers .= "From: researchGiant.com <crm@researchgiant.com>" . "\r\n";
            }
            $headers .= "Reply-To: crm@researchgiant.com" . "\r\n";

            $headers .= 'MIME-Version: 1.0' . "\r\n";
            if (get_option('upicrm_email_format') == 1)
                $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
            if (get_option('upicrm_email_format') == 2)
                $headers .= 'Content-type: text/plain; charset=UTF-8' . "\r\n";
            
            $cc = str_replace(" ", ",", $cc);

            if (!$cancel_cc) {
                $headers .= "Cc: {$cc}" . "\r\n";
            }

            /* add_filter( 'wp_mail_from', 'custom_wp_mail_from' );
              add_filter( 'wp_mail_from', function($email){
              return $mail->mail_from;
              }); */
            $message .= '<div style="display:none" id="lead_id_hidden">'.$lead_id.'</div>';
            $is_mail_sent = wp_mail($to, $subject, $message, $headers);
            
            if (!$is_mail_sent && !get_option('upicrm_cancel_email_failsafe')) {
                //echo('WP_MAIL not sending, try send from MAIL....');
                $message = wordwrap($message, 70, "\r\n");
                @mail($to, $subject, $message, $headers);
            }
            $msg_id = get_option('last_send_msg_id', true);
            $_POST['msg_id'] = $msg_id;
            $_POST['mailBody'] = $message;
            $_POST['mailSubject'] = $subject;
            $_POST['timestamp'] = time();
            $_POST['from'] = $UpiCRMUsers->get_by_id(get_current_user_id());
            $html =  $UpiCRMLeadsChangesLog->add_reply_email_send_log($_POST);
            return $html;
        }

        function get_email_content_in_format($lead_id, $content){
            $UpiCRMUIBuilder = new UpiCRMUIBuilder();
            $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
            $UpiCRMUsers = new UpiCRMUsers();
            $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
            $UpiCRMLeads = new UpiCRMLeads();
            $lead = $UpiCRMLeads->get_by_id($lead_id);
            $getNamesMap = $UpiCRMFieldsMapping->get_all_by($lead->source_id, $lead->source_type);
            $list_option = $UpiCRMUIBuilder->get_list_option();

            $message = nl2br($content);

            $LeadVarTextNoHTML = "";
            foreach ($list_option as $key => $arr) {
                foreach ($arr as $key2 => $value) {
                    $getValue = $UpiCRMUIBuilder->lead_routing($lead, $key, $key2, $getNamesMap, true);
                    if ($getValue != "") {
                        $fields[$value] = $getValue;
                        $LeadVarTextNoHTML .= "{$value}: {$getValue}" . "\r\n";
                    }
                }
            }

            $message = str_replace("[lead-plaintext]", $LeadVarTextNoHTML, $message);
            $message = str_replace("[lead]", $LeadVarText, $message);
            $message = str_replace("[url]", get_site_url(), $message);
            $message = str_replace("[assigned-to]", $UpiCRMUsers->get_by_id($lead->user_id), $message);
            $message = str_replace("[lead-status]", $UpiCRMLeadsStatus->get_status_name_by_id($lead->lead_status_id), $message);
            foreach ($list_option as $key => $arr) {
                foreach ($arr as $key2 => $value) {
                    if (isset($fields)) {
                        $message = str_replace("[field-$key2]", $fields[$value], $message);
                    }
                }
            }
            return $message;
        }
        function get_email_subject_in_format($lead_id, $subject){
            $UpiCRMUIBuilder = new UpiCRMUIBuilder();
            $UpiCRMUsers = new UpiCRMUsers();
            $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
            $UpiCRMLeads = new UpiCRMLeads();
            $lead = $UpiCRMLeads->get_by_id($lead_id);
            $list_option = $UpiCRMUIBuilder->get_list_option();
            $subject = str_replace("[url]", get_site_url(), $subject);
            $subject = str_replace("[assigned-to]", $UpiCRMUsers->get_by_id($lead->user_id), $subject);
            $subject = str_replace("[lead-status]", $UpiCRMLeadsStatus->get_status_name_by_id($lead->lead_status_id), $subject);
            foreach ($list_option as $key => $arr) {
                foreach ($arr as $key2 => $value) {
                    if (isset($fields)) {
                        $subject = str_replace("[field-$key2]", $fields[$value], $subject);
                    }
                }
            }
            return $subject;
        }
    }
}