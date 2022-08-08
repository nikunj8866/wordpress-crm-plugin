<?php



if (!class_exists('UpiCRMAJAX')) {



    class UpiCRMAJAX {



        function wp_ajax_upicrm_on_load_callback() {

            ignore_user_abort(true);

            set_time_limit(0);

            ini_set('memory_limit', '-1');

            $UpiCRMIntegrationsLib = new UpiCRMIntegrationsLib();

            $UpiCRMIntegrationsLib->send_waiting_slave();

            //echo 'UpiCRM';

            die();

        }

        function wp_ajax_sync_webmail_callback()
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
                    print_r($msg);
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
                                   $UpiCRMLeadsChangesLog->add_sync_email_log($args);
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
            wp_die();
        }



    }



    add_action('wp_ajax_upicrm_on_load', array(new UpiCRMAJAX, 'wp_ajax_upicrm_on_load_callback'));

    add_action('wp_ajax_nopriv_upicrm_on_load', array(new UpiCRMAJAX, 'wp_ajax_upicrm_on_load_callback'));

    add_action('wp_ajax_sync_webmail', array(new UpiCRMAJAX, 'wp_ajax_sync_webmail_callback'));

    add_action('wp_ajax_nopriv_sync_webmail', array(new UpiCRMAJAX, 'wp_ajax_sync_webmail_callback'));

}

?>