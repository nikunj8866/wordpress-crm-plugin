<?php

if (!class_exists('UpiCRMLeadsChangesLog')) {

    class UpiCRMLeadsChangesLog extends WP_Widget {



        var $wpdb;



        public function __construct() {

            global $wpdb;

            $this->wpdb = &$wpdb;

        }



        function add($lead) {

            if (get_option('upicrm_enable_audit_log')) {

                $lead = (object)$lead;

                //print_r($lead);

                $add = [

                    'lead_status_id' => isset($lead->lead_status_id) ? $lead->lead_status_id : 0,

                    'user_id' => isset($lead->user_id) ? $lead->user_id : 0,

                    'email_log_id' => isset($lead->email_log_id) ? $lead->email_log_id : 0,

                    'lead_id' => $lead->lead_id,

                    'maker_user_id' => get_current_user_id(),

                    'maker_lead_route_id' =>  isset($lead->maker_lead_route_id) ? $lead->maker_lead_route_id : 0,

                    'lead_management_comment' => $lead->lead_management_comment,

                    'lead_change_log_edit_text' => isset($lead->lead_change_log_edit_text) ? json_encode($lead->lead_change_log_edit_text) : '',

                    'log_comments' => isset($lead->log_comments) ? $lead->log_comments : ''

                ]; 



                $this->wpdb->insert(upicrm_db()."leads_changes_log", $add);
               
                $this->wpdb->update(upicrm_db()."leads", ['lead_log_text' => $this->get_text($add)] , ["lead_id" => $add['lead_id']]);

            }

        }

        

        function get_text($lead,$full=false) {

            $UpiCRMUsers = new UpiCRMUsers(); 

            $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();

             mysqli_set_charset($dblink, "utf8");    



            

            $lead = (object)$lead;

            if ($lead->lead_change_log_time) {

                $text = $lead->lead_change_log_time;

            } else {

                 $text = date("Y-m-d H:i:s");

            }

            $text.= " - ";

            if ($lead->maker_user_id > 0) {

                $user_name = 'User '.$UpiCRMUsers->get_by_id($lead->maker_user_id);

            }

            if($lead->log_comments)
            {
                return $text.$user_name.': '.$lead->log_comments;
            }

            if ($lead->lead_change_log_edit_text) {

                return $text.$user_name.' edit lead';

            }

            if ($lead->lead_management_comment) {

                return $text.$user_name.' add management comment: '.$lead->lead_management_comment;

            }

            if ($lead->user_id > 0) {

                return $text.$user_name.' assigned lead to: '.$UpiCRMUsers->get_by_id($lead->user_id);

            }

            if ($lead->lead_status_id > 0) {

                return $text.$user_name.' change lead status to: '.$UpiCRMLeadsStatus->get_status_name_by_id($lead->lead_status_id);

            }

            

            return '';

        }

        function get_text_activity($log)
        {
            $UpiCRMUsers = new UpiCRMUsers(); 

            $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();

            mysqli_set_charset($dblink, "utf8"); 
            $text = '';  
            if ($log->maker_user_id > 0) {

                $user_name = 'User '.$UpiCRMUsers->get_by_id($log->maker_user_id);

            }

            if($log->log_comments)
            {
                $text .= $user_name.': '.$log->log_comments;
            }
            else if ($log->lead_change_log_edit_text) {

                $text .= $user_name.' edit lead';

            }
            else if ($log->lead_management_comment) {

                $text .= $user_name.' add management comment: '.$log->lead_management_comment;

            }
            else if ($log->user_id > 0) {

                $text .= $user_name.' assigned lead to: '.$UpiCRMUsers->get_by_id($log->user_id);

            }
            else if ($log->lead_status_id > 0) {

                $text .= $user_name.' change lead status to: '.$UpiCRMLeadsStatus->get_status_name_by_id($log->lead_status_id);

            }
            else if($log->email_log_id > 0)
            {
                $text .= $user_name.': Send email to '.$log->to;
            }

            return $text;   
        }
        
        function get_text_email($log)
        {
            $UpiCRMUsers = new UpiCRMUsers(); 

            $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
            $UpiCRMMails = new UpiCRMMails();

            mysqli_set_charset($dblink, "utf8"); 
            $text = '';  
            if ($log->maker_user_id > 0) {

                $user_name = 'User '.$UpiCRMUsers->get_by_id($log->maker_user_id);

            }
            if($log->email_log_id > 0)
            {
                $text .= $user_name.': Send email to '.$log->to;
            }
            if($log->cc)
            {
                $text .= '<br>CC: '. $log->cc;
            }
            if($log->subject)
            {
                $text .= '<br>subject: '. $UpiCRMMails->get_email_subject_in_format($log->lead_id, $log->subject);
            }
            if($log->mail_content)
            {
                $text .= '<br>Content: '. $log->mail_content. $UpiCRMMails->get_email_content_in_format($log->lead_id, $log->mail_content);
            }


            return $text;   
        }

        function get_by_lead_id($lead_id) {

            $query = "SELECT * FROM ".upicrm_db()."leads_changes_log as leads_changes_log";
            $query .= " LEFT JOIN " . upicrm_db() . "leads_email_log as leads_email_log";
            $query .= " ON leads_email_log.email_log_id = leads_changes_log.email_log_id";
            $query .= " where leads_changes_log.lead_id = '{$lead_id}'";
            $query .= " order by lead_change_log_id DESC";
            $rows = $this->wpdb->get_results($query);

            return $rows;

        }

        function remove_activity( $activity_id )
        {
            $query = "DELETE FROM ".upicrm_db() . "leads_changes_log WHERE lead_change_log_id='$activity_id' ";
            $this->wpdb->query($query);
        }

        function add_email_send_log($sendAr = array())
        {
            $UpiCRMMails = new UpiCRMMails();
            $lead_id = $sendAr['lead_id'];
            $to = $sendAr['mailTo'];
            $cc = $sendAr['mailCC'];
            $subject = $sendAr['mailSubject'];
            $content = $sendAr['mailBody'];
            $msg_id = $sendAr['msg_id'];
            $from = $sendAr['from'];
            $timestamp = $sendAr['timestamp'];

            $add = [

                'lead_id' => $lead_id,

                'user' => get_current_user_id(),

                'to' =>  $to,

                'fromaddress' =>  $from,

                'cc' => $cc,

                'subject' => $subject,

                'mail_content' => $content,

                'timestamp' => $timestamp,

                'msg_id' => $msg_id

            ]; 


            
            $this->wpdb->insert(upicrm_db()."leads_email_log", $add);
            $last_id = $this->wpdb->insert_id;
            // $addLog = [
            //     'lead_id' => $lead_id,
            //     'email_log_id' => $last_id
            // ];
            // $this->add($addLog);
            $html = '<div class="mail-item">
                                <span><strong>Email - '. $subject .'</strong></span> <span>from '. $from .'</span>
                                <span class="mail-date"> '.date('M d, Y \a\t h:i a', $timestamp) .'</span>
                                <span class="mail-to">to '.$to .'</span>
                                <span class="action">
                                    <a href="javascript:void(0)" class="reply-mail" data-subject="'.str_replace("Re: ", "", $subject).'" data-parent="'.$msg_id.'" >Reply</a>
                                    <a href="javascript:void(0)" class="remove-mail-log" data-id="'. $last_id .'">Remove</a>
                                </span>
                                <div class="mail-content">
                                    '.$content.'
                                </div>
                            </div>';
            return $html;
        }

        function add_reply_email_send_log($sendAr = array())
        {
            $UpiCRMMails = new UpiCRMMails();
            $lead_id = $sendAr['lead_id'];
            $to = $sendAr['reply_mailTo'];
            $cc = $sendAr['reply_mailCC'];
            $subject = $sendAr['mailSubject'];
            $content = $sendAr['mailBody'];
            $msg_id = $sendAr['msg_id'];
            $from = $sendAr['from'];
            $timestamp = $sendAr['timestamp'];
            $parent_msg_id = $sendAr['parent_email_id'];

            $add = [

                'lead_id' => $lead_id,

                'user' => get_current_user_id(),

                'to' =>  $to,

                'fromaddress' =>  $from,

                'cc' => $cc,

                'subject' => $subject,

                'mail_content' => $content,

                'timestamp' => $timestamp,

                'msg_id' => $msg_id,

                'parent_msg_id' => $parent_msg_id

            ]; 


            
            $this->wpdb->insert(upicrm_db()."leads_email_log", $add);
            $last_id = $this->wpdb->insert_id;

            // $addLog = [
            //     'lead_id' => $lead_id,
            //     'email_log_id' => $last_id
            // ];
            // $this->add($addLog);
            $html = '<div class="mail-item">
                                <span><strong>Email - '. $subject .'</strong></span> <span>from '. $from .'</span>
                                <span class="mail-date"> '.date('M d, Y \a\t h:i a', $timestamp) .'</span>
                                <span class="mail-to">to '.$to .'</span>
                                <span class="action">
                                    <a href="javascript:void(0)" class="reply-mail" data-subject="'.str_replace("Re: ", "", $subject).'" data-parent="'.$msg_id.'" >Reply</a>
                                    <a href="javascript:void(0)" class="remove-mail-log" data-id="'. $last_id .'">Remove</a>
                                </span>
                                <div class="mail-content">
                                    '.$content.'
                                </div>
                            </div>';
            return $html;
        }

        function add_sync_email_log($sendAr = array())
        {
            $lead_id = $sendAr['lead_id'];
            $to = $sendAr['to'];
            $cc = $sendAr['cc'];
            $subject = $sendAr['subject'];
            $content = $sendAr['mail_content'];
            $timestamp = $sendAr['timestamp'];
            $is_sync = $sendAr['is_sync'];
            $uid = $sendAr['uid'];
            $msg_id = $sendAr['msg_id'];
            $parent_msg_id = $sendAr['parent_msg_id'];
            $from = $sendAr['from'];
            $add = [

                'lead_id' => $lead_id,

                'user' => 0,

                'to' =>  $to,

                'fromaddress' =>  $from,

                'cc' => $cc,

                'subject' => $subject,

                'mail_content' => $content,

                'timestamp' => $timestamp,

                'is_sync' => $is_sync,
                
                'uid' => $uid,

                'msg_id' => $msg_id,

                'parent_msg_id' => $parent_msg_id,

            ]; 


            
            $this->wpdb->insert(upicrm_db()."leads_email_log", $add);
            $last_id = $this->wpdb->insert_id;
            // $addLog = [
            //     'lead_id' => $lead_id,
            //     'email_log_id' => $last_id
            // ];
            // $this->add($addLog);
        }

        function get_email_by_lead_id($lead_id) {

            $query = "SELECT * FROM ".upicrm_db()."leads_email_log as leads_email_log";
            $query .= " LEFT JOIN " . upicrm_db() . "leads_changes_log as leads_changes_log";
            $query .= " ON leads_changes_log.email_log_id = leads_email_log.email_log_id";
            $query .= " where leads_email_log.lead_id = '{$lead_id}'";
            $query .= " order by leads_email_log.email_log_id DESC";

            $rows = $this->wpdb->get_results($query);

            return $rows;

        }

        function get_all_parent_mail_log($lead_id)
        {
            $UpiCRMLeads = new UpiCRMLeads();
            $UpiCRMMails = new UpiCRMMails();
            $UpiCRMLeadsChangesLog = new UpiCRMLeadsChangesLog();
            $ImapReader_class = new ImapReader_class(IMAP_HOST, IMAP_PORT, IMAP_USERNAME, IMAP_PASSWORD);
            $ImapReader_class->connect();
            $last_id = get_option('last_uid', true);
            if(!$last_id)
            {
                $last_id = 0;
            }

            $header = $ImapReader_class->search_greater_uid( $last_id + 1 );
            
            $lead_id_inner = '';
            $newlast_id = '';
            $updated = 0;
            if($ImapReader_class->count() > 0)
            {
                for ($i = 0; ($i < $ImapReader_class->count()); $i++)
                {
                    $msg = $ImapReader_class->get($i);
                    $msg = $ImapReader_class->fetch($msg);
                    if($msg->uid > $last_id)
                    {
                        if($UpiCRMMails->check_parent_email_exist( $msg->in_reply_to) > 0)
                        {
                           $from_email = $ImapReader_class->_get_from_email($msg->from);
                           if($from_email)
                           {
                           // $lead_id = $UpiCRMLeads->get_lead_id_from_email($from_email);
                                   $lead_id_inner = '';
                                   $content = '';
                                   foreach ($msg->content as $key => $content)
                                   {
                                       if($content->mime == "TEXT/HTML")
                                       {
                                       $content = $content->data;
                                       }
                                   }
                                   preg_match('/<div style="display:none" id="(.*?)lead_id_hidden">(.*?)<\/div>/s', $content, $match);
                                   if($match)
                                   {
                                       $lead_id_inner = $match[2];
                                   }
                                   $args = array(
                                       'lead_id' => $lead_id_inner,
                                       'to' => $msg->to,
                                       'from' => $msg->from,
                                       'cc' => $msg->cc,
                                       'subject' => $msg->subject,
                                       'mail_content' => $content,
                                       'timestamp' => $msg->timestamp,
                                       'is_sync' => 1,
                                       'uid' => $msg->uid,
                                       'msg_id' => $msg->message_id,
                                       'parent_msg_id' => $msg->in_reply_to
                                   );
                                   $this->add_sync_email_log($args);
                                   $newlast_id = $msg->uid;
                                   $default_lead = get_option('upicrm_default_lead_status');
                                   $UpiCRMLeads->change_status($default_lead, $lead_id_inner);
                                   $updated++;
                           }
                       }
                    }
                }
                if($updated > 0)
                {
                    update_option('last_uid', $newlast_id);
                }
            }
            $query = "SELECT * FROM ".upicrm_db()."leads_email_log as leads_email_log";
            $query .= " where lead_id='$lead_id'";
            $query .= " order by lead_change_log_time DESC";
            $rows = $this->wpdb->get_results($query);
            return $rows;
        }

        function remove_email_log_activity( $activity_id )
        {
            $query = "DELETE FROM ".upicrm_db() . "leads_email_log WHERE email_log_id='$activity_id' ";
            $this->wpdb->query($query);
            $query = "DELETE FROM ".upicrm_db() . "leads_changes_log WHERE email_log_id='$activity_id' ";
            $this->wpdb->query($query);
        }
           
    }

}

?>