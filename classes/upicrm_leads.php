<?php
/**
 * Class UpiCRMLeads
 */
if (!class_exists('UpiCRMLeads')) {

    class UpiCRMLeads extends WP_Widget {

        var $wpdb;

        public function __construct() {
            global $wpdb;
            $this->wpdb = &$wpdb;
        }

        /**
         * @param $lead_content_arr
         * @param $source_type
         * @param $source_id
         * @param bool $sendEmail
         * @param bool $isIntegration
         * @param bool $integrationArr
         * @return int
         */
        function add($lead_content_arr, $source_type, $source_id, $sendEmail = true, $isIntegration = false, $integrationArr = false) {
            //add lead (content as array)
//        print'<pre>';print_r($lead_content_arr);print'</pre>';
            $upicrm_cancel_lead_form = get_option('upicrm_cancel_lead_form');
            $default_lead = get_option('upicrm_default_lead_status');
            //sync email
            $this->sync_email_log();
            if (!isset($upicrm_cancel_lead_form[$source_type][$source_id])) {
                $UpiCRMMails = new UpiCRMMails();
                $UpiCRMLeadsRoute = new UpiCRMLeadsRoute();
                $UpiCRMIntegrations = new UpiCRMIntegrations();
                $UpiCRMUsers = new UpiCRMUsers();
                $UpiCRMFieldsMapping  = new UpiCRMFieldsMapping();
                $user = get_users(array('role' => 'Administrator'));
                $user_id = get_option('upicrm_default_lead');

                if (!$isIntegration) {
                    $ins['lead_content'] = json_encode($lead_content_arr); //save this in JSON format
                } else {
                    /* foreach (json_decode($lead_content_arr,true) as $key => $value) {
                      echo $key."\n";
                      } */

                    $ins['lead_content'] = $lead_content_arr;
                }
                $emailKey =  $UpiCRMFieldsMapping->get_form_field_id( $source_id, 8);
                if($emailKey)
                {
                    $ins['lead_email'] = $lead_content_arr[$emailKey];
                }
                $ins['source_type'] = $source_type;
                $ins['source_id'] = $source_id;
                $ins['user_ip'] = !$isIntegration ? $_SERVER['REMOTE_ADDR'] : $integrationArr['user_ip'];
                $ins['user_agent'] = !$isIntegration ? $_SERVER['HTTP_USER_AGENT'] : $integrationArr['user_agent'];
                $ins['user_referer'] = !$isIntegration ? $_SESSION['upicrm_referer'] : $integrationArr['user_referer'];

                $ins['lead_status_id'] = !$default_lead ? 9 : $default_lead ;
                $ins['user_id'] = $user_id;
                 
               
                $aff_id = @apply_filters( 'determine_current_user', false );
                if ($aff_id) {
                    @wp_set_current_user( $aff_id );
                }
                $ins['affiliate_id'] = get_current_user_id();
                $ins['affiliate_type'] = @get_user_meta(get_current_user_id(), 'upicrm_user_affiliate_type',1) ? get_user_meta(get_current_user_id(), 'upicrm_user_affiliate_type',1) : 0;

                
                $user_lead_id = upicrm_get_user_lead_id();
                if ($user_lead_id != 0) {
                    $ins['old_user_lead_id'] = $user_lead_id;
                }

                $this->wpdb->insert(upicrm_db() . "leads", $ins);
                $last_id = $this->wpdb->insert_id;
                //Insert value in the lead_users table
                $leadUserAr = array();
                $leadUserAr['user_id'] = $user_id;
                $leadUserAr['lead_id'] = $last_id;
                
                $this->assign_lead_to_users($leadUserAr);

                if ($user_lead_id == 0 && !$isIntegration)
                    upicrm_set_new_user($last_id);

                if (!$isIntegration) {
                    if (isset($_SESSION['utm_source']) || isset($_SESSION['utm_medium']) || isset($_SESSION['utm_term']) || isset($_SESSION['utm_content']) || isset($_SESSION['utm_campaign'])) {
                        $ins_campaign['lead_id'] = $last_id;
                        $ins_campaign['utm_source'] = $_SESSION['utm_source'];
                        $ins_campaign['utm_medium'] = $_SESSION['utm_medium'];
                        $ins_campaign['utm_term'] = $_SESSION['utm_term'];
                        $ins_campaign['utm_content'] = $_SESSION['utm_content'];
                        $ins_campaign['utm_campaign'] = $_SESSION['utm_campaign'];
                        $this->wpdb->insert(upicrm_db() . "leads_campaign", $ins_campaign);
                    }
                } else {
                    if (isset($integrationArr['utm_source']) || isset($integrationArr['utm_medium']) || isset($integrationArr['utm_term']) || isset($integrationArr['utm_content']) || isset($integrationArr['utm_campaign'])) {
                        $ins_campaign['lead_id'] = $last_id;
                        $ins_campaign['utm_source'] = $integrationArr['utm_source'];
                        $ins_campaign['utm_medium'] = $integrationArr['utm_medium'];
                        $ins_campaign['utm_term'] = $integrationArr['utm_term'];
                        $ins_campaign['utm_content'] = $integrationArr['utm_content'];
                        $ins_campaign['utm_campaign'] = $integrationArr['utm_campaign'];
                        $this->wpdb->insert(upicrm_db() . "leads_campaign", $ins_campaign);
                    }
                }

                $UpiCRMLeadsRoute->do_route($last_id);

                //$leadObj = $this->get_by_id($last_id);
                do_action('upicrm_after_new_lead', $last_id);

                if ($sendEmail && !$isIntegration && !get_option('upicrm_cancel_email_alerts')) {
                    $UpiCRMMails->send($last_id, "new_lead");
                }
               

                //send integrations
                $UpiCRMIntegrationsLib = new UpiCRMIntegrationsLib();
                $UpiCRMIntegrationsLib->send_slave($last_id);

                $UpiCRMWebServiceLib = new UpiCRMWebServiceLib();
                $UpiCRMWebServiceLib->send($last_id, 1);

                if (get_option('upicrm_send_csv_email')) {
                    $UpiCRMMails->send_csv($last_id);
                }
                
                if (get_option('upicrm_remove_info_from_db')) {
                    $this->clear_by_id($last_id);
                }

                return $last_id;
            }
        }

        /**
         *  update lead by id
         * @param $lead_id
         * @param $updateArr
         */
        function check_lead_email_exist( $email )
        {
            $query = "SELECT lead_id FROM ".upicrm_db() . "leads WHERE lead_content LIKE '%$email%' LIMIT 1";
            $rows = $this->wpdb->get_results($query);
            if($rows)
            {
                return $rows[0]->lead_id;
            }
            else
            {
                return false;
            }
        }
        function update_by_id($lead_id, $updateArr) {
            $this->wpdb->update(upicrm_db() . "leads", $updateArr, array("lead_id" => $lead_id));
        }

        function assign_lead_to_users($addArr) {
            $this->wpdb->insert(upicrm_db() . "lead_users", $addArr);
        }

        function get_users_of_lead($lead_id) {
            $query = "SELECT user_id FROM  ". upicrm_db() ."lead_users WHERE lead_id='$lead_id'";
            
            $rows = $this->wpdb->get_results($query);
            return $rows;
        }

        function remove_existing_lead_users($lead_id, $user_id)
        {
            $deleteUser = "DELETE FROM ".upicrm_db() . "lead_users WHERE lead_id='$lead_id' AND user_id ='$user_id'";
            $this->wpdb->query($deleteUser);
        }

        function get_all_lead_users($lead_id, $main_user_id)
        {
            $users = $this->wpdb->get_results("SELECT user_id FROM ".upicrm_db() . "lead_users WHERE lead_id='$lead_id' AND user_id !='$main_user_id'");
            $userAr = array();
            foreach($users as $u)
            {
                array_push($userAr, $u->user_id);
            }
            return $userAr;
        }

        /**
         *  get leads
         * @param int $user_id
         * @param int $page
         * @param int $limit
         * @param string $orderBy
         * @param int $check_date
         * @param int $from_date
         * @param int $to_date
         * @return array|null|object
         */
        function get($user_id = 0, $page = 0, $limit = 0, $orderBy = "DESC", $check_date = 0, $from_date = 0, $to_date = 0, $affiliate_id=0, $last_lead = 0) {

            $UpiCRMUsers = new UpiCRMUsers();
            $UpiCRMAffiliate = new UpiCRMAffiliate();

            $query = "SELECT *,  " . upicrm_db() . "leads.lead_id AS `lead_id`, " . upicrm_db() . "leads_integration.integration_is_slave AS `is_slave`, " . upicrm_db() . "leads.user_id AS `user_id` FROM " . upicrm_db() . "leads";
            $query .= " LEFT JOIN " . upicrm_db() . "leads_campaign";
            $query .= " ON " . upicrm_db() . "leads_campaign.lead_id = " . upicrm_db() . "leads.lead_id";
            $query .= " LEFT JOIN " . upicrm_db() . "leads_integration";
            $query .= " ON " . upicrm_db() . "leads_integration.lead_id = " . upicrm_db() . "leads.lead_id";
            $query .= " LEFT JOIN " . upicrm_db() . "integrations";
            $query .= " ON " . upicrm_db() . "integrations.integration_id = " . upicrm_db() . "leads_integration.integration_id";
            $query .= " LEFT JOIN " . upicrm_db() . "users";
            $query .= " ON " . upicrm_db() . "users.user_id = " . upicrm_db() . "leads.user_id";
            if ($user_id != 0) {
            $query .= " LEFT JOIN " . upicrm_db() . "lead_users";
            $query .= " ON " . upicrm_db() . "lead_users.lead_id = " . upicrm_db() . "leads.lead_id";
            }
            if ($check_date > 0) {
                $query .= " WHERE " . upicrm_db() . "leads.time > DATE_SUB(CURDATE(), INTERVAL {$check_date} DAY)";
            }
            if ($check_date === "custom") {
                $query .= " WHERE ";
                if ($from_date > 0) {
                    $query .= upicrm_db() . "leads.time >= CAST('{$from_date}' AS DATE)";
                }
                if ($from_date > 0 && $to_date > 0) {
                    $query .= " AND ";
                }
                if ($to_date > 0) {
                    $to_date = date('Y-m-d', strtotime($to_date . ' + 1 day'));
                    $query .= upicrm_db() . "leads.time <= CAST('{$to_date}' AS DATE)";
                }
            }
            if ($user_id != 0) {
                if ($check_date > 0) {
                    $SQLopretor = "AND";
                } else {
                    $SQLopretor = "WHERE";
                }
                $users = $UpiCRMUsers->get_childrens_by_parent_id($user_id);
                $child_user_query = "";
                foreach ($users as $user) {
                    $child_user_query .= " OR " . upicrm_db() . "lead_users.user_id = {$user->user_id}";
                }
                $query .= " {$SQLopretor} (" . upicrm_db() . "lead_users.user_id = {$user_id} {$child_user_query})";
            }
            
            if ($affiliate_id > 0) {
                if ($check_date > 0 && $user_id == 0) {
                    $SQLopretor = "AND";
                } else {
                    $SQLopretor = "WHERE";
                }
                
                $users = $UpiCRMAffiliate->get_childrens_by_parent_id($affiliate_id);
                $child_user_query = "";
                if ($users) {
                    foreach ($users as $user) {
                        $child_user_query .= " OR " . upicrm_db() . "leads.affiliate_id = {$user->ID}";
                    }
                }

                $query .= " {$SQLopretor} (" . upicrm_db() . "leads.affiliate_id = {$affiliate_id} {$child_user_query})";
               
            }
            
            if ($last_lead > 0) {
                if ($check_date > 0 && $user_id == 0 && $affiliate_id == 0) {
                    $SQLopretor = "AND";
                } else {
                    $SQLopretor = "WHERE";
                }
                
                $query .= " {$SQLopretor} " . upicrm_db() . "leads.lead_id > {$last_lead}";
            }
            if(strpos($query, "WHERE") !== false){
                $query .= " AND " . upicrm_db() . "leads.is_trash = 0";
            }
            else{
                $query .= " WHERE " . upicrm_db() . "leads.is_trash = 0";
            }

             $query .= " GROUP BY " . upicrm_db() . "leads.`lead_id` ORDER BY " . upicrm_db() . "leads.`lead_id` {$orderBy}";
            //$query .= " ORDER BY " . upicrm_db() . "leads.`lead_id` {$orderBy}";
            

            if ($limit > 0) {
                $lim1 = ($page - 1) * $limit;
                $query .= " LIMIT {$lim1},{$limit}";
            }

           //echo $query."<br /><br />";die();
            
            $rows = $this->wpdb->get_results($query);
//            
            return $rows;
        }

        function get_trash($user_id = 0, $page = 0, $limit = 0, $orderBy = "DESC", $check_date = 0, $from_date = 0, $to_date = 0, $affiliate_id=0, $last_lead = 0) {

            $UpiCRMUsers = new UpiCRMUsers();
            $UpiCRMAffiliate = new UpiCRMAffiliate();

            $query = "SELECT *,  " . upicrm_db() . "leads.lead_id AS `lead_id`, " . upicrm_db() . "leads_integration.integration_is_slave AS `is_slave`, " . upicrm_db() . "leads.user_id AS `user_id` FROM " . upicrm_db() . "leads";
            $query .= " LEFT JOIN " . upicrm_db() . "leads_campaign";
            $query .= " ON " . upicrm_db() . "leads_campaign.lead_id = " . upicrm_db() . "leads.lead_id";
            $query .= " LEFT JOIN " . upicrm_db() . "leads_integration";
            $query .= " ON " . upicrm_db() . "leads_integration.lead_id = " . upicrm_db() . "leads.lead_id";
            $query .= " LEFT JOIN " . upicrm_db() . "integrations";
            $query .= " ON " . upicrm_db() . "integrations.integration_id = " . upicrm_db() . "leads_integration.integration_id";
            $query .= " LEFT JOIN " . upicrm_db() . "users";
            $query .= " ON " . upicrm_db() . "users.user_id = " . upicrm_db() . "leads.user_id";
            if ($user_id != 0) {
            $query .= " LEFT JOIN " . upicrm_db() . "lead_users";
            $query .= " ON " . upicrm_db() . "lead_users.lead_id = " . upicrm_db() . "leads.lead_id";
            }
            if ($check_date > 0) {
                $query .= " WHERE " . upicrm_db() . "leads.time > DATE_SUB(CURDATE(), INTERVAL {$check_date} DAY)";
            }
            if ($check_date === "custom") {
                $query .= " WHERE ";
                if ($from_date > 0) {
                    $query .= upicrm_db() . "leads.time >= CAST('{$from_date}' AS DATE)";
                }
                if ($from_date > 0 && $to_date > 0) {
                    $query .= " AND ";
                }
                if ($to_date > 0) {
                    $to_date = date('Y-m-d', strtotime($to_date . ' + 1 day'));
                    $query .= upicrm_db() . "leads.time <= CAST('{$to_date}' AS DATE)";
                }
            }
            if ($user_id != 0) {
                if ($check_date > 0) {
                    $SQLopretor = "AND";
                } else {
                    $SQLopretor = "WHERE";
                }
                $users = $UpiCRMUsers->get_childrens_by_parent_id($user_id);
                $child_user_query = "";
                foreach ($users as $user) {
                    $child_user_query .= " OR " . upicrm_db() . "lead_users.user_id = {$user->user_id}";
                }
                $query .= " {$SQLopretor} (" . upicrm_db() . "lead_users.user_id = {$user_id} {$child_user_query})";
            }
            
            if ($affiliate_id > 0) {
                if ($check_date > 0 && $user_id == 0) {
                    $SQLopretor = "AND";
                } else {
                    $SQLopretor = "WHERE";
                }
                
                $users = $UpiCRMAffiliate->get_childrens_by_parent_id($affiliate_id);
                $child_user_query = "";
                if ($users) {
                    foreach ($users as $user) {
                        $child_user_query .= " OR " . upicrm_db() . "leads.affiliate_id = {$user->ID}";
                    }
                }

                $query .= " {$SQLopretor} (" . upicrm_db() . "leads.affiliate_id = {$affiliate_id} {$child_user_query})";
               
            }
            
            if ($last_lead > 0) {
                if ($check_date > 0 && $user_id == 0 && $affiliate_id == 0) {
                    $SQLopretor = "AND";
                } else {
                    $SQLopretor = "WHERE";
                }
                
                $query .= " {$SQLopretor} " . upicrm_db() . "leads.lead_id > {$last_lead}";
            }
            
            if(strpos($query, "WHERE") !== false){
                $query .= " AND " . upicrm_db() . "leads.is_trash = 1";
            }
            else{
                $query .= " WHERE " . upicrm_db() . "leads.is_trash = 1";
            }

            $query .= " GROUP BY " . upicrm_db() . "leads.`lead_id` ORDER BY " . upicrm_db() . "leads.`lead_id` {$orderBy}";
            

            if ($limit > 0) {
                $lim1 = ($page - 1) * $limit;
                $query .= " LIMIT {$lim1},{$limit}";
            }

           //echo $query."<br /><br />";die();
            
            $rows = $this->wpdb->get_results($query);
//            
            return $rows;
        }


        /**
         * @param int $user_id
         * @return int
         */
        function get_total($user_id = 0) {
            $query = "SELECT `lead_id` FROM " . upicrm_db() . "leads";
            if ($user_id > 0) {
                $UpiCRMUsers = new UpiCRMUsers();
                $query .= " LEFT JOIN " . upicrm_db() . "users";
                $query .= " ON " . upicrm_db() . "users.user_id = " . upicrm_db() . "leads.user_id";
                $users = $UpiCRMUsers->get_childrens_by_parent_id($user_id);
                $child_user_query = "";
                foreach ($users as $user) {
                    $child_user_query .= " OR " . upicrm_db() . "leads.user_id = {$user->user_id}";
                }
                $query .= " WHERE " . upicrm_db() . "leads.user_id = {$user_id} {$child_user_query}";
                //$query.= " WHERE `user_id` = {$user_id}"; 
            }
            $rows = $this->wpdb->get_results($query);
            return $this->wpdb->num_rows;
        }

        /**
         * get the name of the form by source_id & source_type
         * @param $source_id
         * @param $source_type
         * @return string
         */
        function get_source_form_name($source_id = 0, $source_type = 0) {
            global $SourceTypeID;

            $form_name = '';
            switch ($source_type) {
                case $SourceTypeID['gform']:
                    $UpiCRMgform = new UpiCRMgform();
                    if ($UpiCRMgform->is_active()) {
                        $form_name = $UpiCRMgform->form_name($source_id);
                    }
                    break;
                case $SourceTypeID['wpcf7']:
                    $UpiCRMwpcf7 = new UpiCRMwpcf7();
                    if ($UpiCRMwpcf7->is_active()) {
                        $form_name = $UpiCRMwpcf7->form_name($source_id);
                    }
                    break;
                case $SourceTypeID['ninja']:
                    $UpiCRMninja = new UpiCRMninja();
                    if ($UpiCRMninja->is_active()) {
                        $form_name = $UpiCRMninja->form_name($source_id);
                    }
                    break;
                case $SourceTypeID['caldera']:
                    $UpiCRMcaldera = new UpiCRMcaldera();
                    if ($UpiCRMcaldera->is_active()) {
                        $form_name = UpiCRMcaldera::form_name($source_id);
                    }
                    break;
                case $SourceTypeID['wpforms']:
                    $UpiCRMwpforms = new UpiCRMwpforms();
                    if ($UpiCRMwpforms->is_active()) {
                        $form_name = $UpiCRMwpforms->form_name($source_id);
                    }
                    break;
                case $SourceTypeID['elementor']:
                    $UpiCRMElementor = new UpiCRMElementor();
                    if ($UpiCRMElementor->is_active()) {
                        $form_name = $UpiCRMElementor->form_name($source_id);
                    }
                    break;
            }

            if (!$form_name) {
                $arg_arr = [
                    "source_type" => $source_type,
                    "source_id" => $source_id
                ];
                $form_name = apply_filters("upicrm_get_name_$source_type", $arg_arr);
            }

            return is_array($form_name) ? '' : $form_name;
        }

        /**
         * @param $user_id
         * @param $lead_id
         */
        function change_user($user_id, $lead_id) {
            //change lead user id
            $this->wpdb->update(upicrm_db() . "leads", array("user_id" => $user_id), array("lead_id" => $lead_id));
            do_action('upicrm_after_lead_change_user', $lead_id);
        }

        /**
         * @param $lead_status_id
         * @param $lead_id
         */
        function change_status($lead_status_id, $lead_id) {
            //change lead status id
            $this->wpdb->update(upicrm_db() . "leads", array("lead_status_id" => $lead_status_id), array("lead_id" => $lead_id));
            do_action('upicrm_after_lead_change_status', $lead_id);
        }

        /**
         * @param $lead_id
         */

        function remove_lead($lead_id) {
            //delete lead
            $this->wpdb->delete(upicrm_db() . "leads", array("lead_id" => $lead_id));
            $this->wpdb->delete(upicrm_db() . "leads_campaign", array("lead_id" => $lead_id));
            $this->wpdb->delete(upicrm_db() . "leads_changes_log", array("lead_id" => $lead_id));
            $this->wpdb->delete(upicrm_db() . "lead_users", array("lead_id" => $lead_id));
        }

        /**
         * @param $lead_ids
         */
        function remove_leads($lead_ids) {
            //delete leads
            foreach ($lead_ids as $lead_id) {
                $this->wpdb->delete(upicrm_db() . "leads", array("lead_id" => $lead_id));
                $this->wpdb->delete(upicrm_db() . "leads_campaign", array("lead_id" => $lead_id));
                $this->wpdb->delete(upicrm_db() . "leads_changes_log", array("lead_id" => $lead_id));
                $this->wpdb->delete(upicrm_db() . "lead_users", array("lead_id" => $lead_id));
            }
        }

        function trash_lead($lead_id) {
            //trash lead
            $UpiCRMLeadsChangesLog = new UpiCRMLeadsChangesLog();
            $this->wpdb->update(upicrm_db() . "leads", array("is_trash" => 1), array("lead_id" => $lead_id));
            $addLog = [
                'user_id' => get_current_user_id(),
                'lead_id' => $lead_id,
                'log_comments' => 'move lead to trash.'
            ];
            $UpiCRMLeadsChangesLog->add($addLog);
        }

        function trash_leads($lead_ids) {
            //trash leads
            $UpiCRMLeadsChangesLog = new UpiCRMLeadsChangesLog();
            foreach ($lead_ids as $lead_id) {
                $this->wpdb->update(upicrm_db() . "leads", array("is_trash" => 1), array("lead_id" => $lead_id));
                $addLog = [
                    'user_id' => get_current_user_id(),
                    'lead_id' => $lead_id,
                    'log_comments' => 'move lead to trash.'
                ];
                $UpiCRMLeadsChangesLog->add($addLog);
            }
        }

        function restore_lead($lead_id) {
            //restore lead
            $UpiCRMLeadsChangesLog = new UpiCRMLeadsChangesLog();
            $this->wpdb->update(upicrm_db() . "leads", array("is_trash" => 0), array("lead_id" => $lead_id));
            $addLog = [
                'user_id' => get_current_user_id(),
                'lead_id' => $lead_id,
                'log_comments' => 'restore lead from trash.'
            ];
            $UpiCRMLeadsChangesLog->add($addLog);
        }

        function restore_leads($lead_ids) {
            //restore leads
            $UpiCRMLeadsChangesLog = new UpiCRMLeadsChangesLog();
            foreach ($lead_ids as $lead_id) {
                $this->wpdb->update(upicrm_db() . "leads", array("is_trash" => 0), array("lead_id" => $lead_id));
                $addLog = [
                    'user_id' => get_current_user_id(),
                    'lead_id' => $lead_id,
                    'log_comments' => 'restore lead from trash.'
                ];
                $UpiCRMLeadsChangesLog->add($addLog);
            }
        }

        /**
         *  delete all leads mapping
         */
        function empty_all() {

            $this->wpdb->query("TRUNCATE TABLE " . upicrm_db() . "leads");
            $this->wpdb->query("TRUNCATE TABLE " . upicrm_db() . "leads_changes_log");
            $this->wpdb->query("TRUNCATE TABLE " . upicrm_db() . "leads_campaign");
            $this->wpdb->query("TRUNCATE TABLE " . upicrm_db() . "lead_users");
        }

        /**
         *  get lead by id
         * @param $lead_id
         * @return mixed
         */
        function get_by_id($lead_id) {

            $query = "SELECT *,  " . upicrm_db() . "leads.lead_id AS `lead_id` FROM " . upicrm_db() . "leads";
            $query .= " LEFT JOIN " . upicrm_db() . "leads_campaign";
            $query .= " ON " . upicrm_db() . "leads_campaign.lead_id = " . upicrm_db() . "leads.lead_id";
            $query .= " LEFT JOIN " . upicrm_db() . "leads_integration";
            $query .= " ON " . upicrm_db() . "leads_integration.lead_id = " . upicrm_db() . "leads.lead_id";
            $query .= " LEFT JOIN " . upicrm_db() . "integrations";
            $query .= " ON " . upicrm_db() . "integrations.integration_id = " . upicrm_db() . "leads_integration.integration_id";
            $query .= " WHERE " . upicrm_db() . "leads.lead_id = {$lead_id}";

            $rows = $this->wpdb->get_results($query);
            return $rows[0];
        }

        function clear_by_id($lead_id) {
            $upicrm_text_on_lead_clear = get_option('upicrm_text_on_lead_clear');
            $query = "SELECT " . upicrm_db() . "leads.lead_content FROM " . upicrm_db() . "leads";
            $query .= " WHERE " . upicrm_db() . "leads.lead_id = {$lead_id}";
            $rows = $this->wpdb->get_results($query);
            if (isset($rows[0]->lead_content)) {
                $lead_content = json_decode($rows[0]->lead_content, true);
                foreach ($lead_content as $key => $value) {
                    $new_lead_content[$key] = $upicrm_text_on_lead_clear;
                }
                $arr['lead_content'] = json_encode($new_lead_content);
                $arr['user_ip'] = $upicrm_text_on_lead_clear;
                $this->update_by_id($lead_id, $arr);
            }
        }

        function user_has_access_lead( $lead_id = 0, $user_id = 0 )
        {
            $UpiCRMUsers = new UpiCRMUsers();
            if($user_id == 0)
            {
                $user_id = get_current_user_id();
            }
            if( $lead_id > 0 )
            {
                $query = "SELECT * FROM ". upicrm_db() . "leads as leads";
                $query .= " LEFT JOIN " . upicrm_db() . "lead_users as lead_users";
                $query .= " ON lead_users.lead_id = leads.lead_id";
                $query .= " WHERE leads.lead_id = '$lead_id'";
                if ($UpiCRMUsers->get_permission() == 1) {
                    $query .= "  AND lead_users.user_id = '$user_id'";
                }
               
                $rows = $this->wpdb->get_results($query);
                return $this->wpdb->num_rows;
                
            }
            return false;
        }

        function get_lead_id_from_email( $email ){
            if($email)
            {
                $query = "SELECT lead_id FROM ". upicrm_db() . "leads
                          WHERE lead_email = '$email'";
                $rows = $this->wpdb->get_result();
                if($rows)
                {
                    return $rows[0]->lead_id;
                }

            }  
            return false;
        }

        function sync_email_log()
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
        }
    }

}