<?php

if (!class_exists('UpiCRMStatistics')) {

    class UpiCRMStatistics extends WP_Widget {

        var $wpdb;

        public function __construct() {
            global $wpdb;
            $this->wpdb = &$wpdb;
        }

        function get_total_leads($check_date = 0, $from_date = 0, $to_date = 0) {
            $user_id = get_current_user_id();
            $query = "SELECT leads.`lead_id` FROM " . upicrm_db() . "leads as leads";
            $query .= " LEFT JOIN " . upicrm_db() . "lead_users as lead_users";
            $query .= " ON lead_users.lead_id = leads.lead_id";
            $query .= $this->build_date_query($check_date, $from_date, $to_date);
            if(strpos($query, "WHERE") !== false){
                $query .= " AND leads.is_trash = 0";
            }
            else{
                $query .= " WHERE leads.is_trash = 0";
            }
            $query .= " AND lead_users.user_id = {$user_id} ";
            
            $rows = $this->wpdb->get_results($query);
            return $this->wpdb->num_rows;
        }

        function get_total_leads_by_user_id($user_id, $check_date = 0, $from_date = 0, $to_date = 0) {
            $UpiCRMUsers = new UpiCRMUsers();
            $query = "SELECT leads.lead_id FROM " . upicrm_db() . "leads as leads LEFT JOIN " . upicrm_db() . "lead_users as lead_users  ON lead_users.lead_id = leads.lead_id WHERE (lead_users.user_id = {$user_id} ";
                        
            foreach ($UpiCRMUsers->get_childrens_by_parent_id($user_id) as $obj) {
                $query .= "OR lead_users.user_id = {$obj->user_id} ";
            }
            $query .= ")";
            $query .= $this->build_date_query($check_date, $from_date, $to_date, "AND");
            $query .= " group by leads.lead_id";
            $rows = $this->wpdb->get_results($query);
            //echo $this->wpdb->last_query;
            return $this->wpdb->num_rows;
        }

        function get_total_leads_status_by_user_id($user_id = 0, $check_date = 0, $from_date = 0, $to_date = 0) {
            $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
            $UpiCRMUsers = new UpiCRMUsers();
            $color = $this->color_array();


            $query = "SELECT count(*) AS `count` ,`lead_status_id` FROM " . upicrm_db() . "leads as leads";
          //  $query .= $this->build_date_query($check_date, $from_date, $to_date);
            if ($user_id > 0) {
                $query .= " LEFT JOIN " . upicrm_db() . "lead_users as lead_users";
                $query .= " ON lead_users.lead_id = leads.lead_id";
                $query .= " WHERE lead_users.user_id = {$user_id} ";
                // foreach ($UpiCRMUsers->get_childrens_by_parent_id($user_id) as $obj) {
                //     $query .= "OR lead_users.user_id = {$obj->user_id} ";
                // }
                // $query .= ')';
                $query .= $this->build_date_query($check_date, $from_date, $to_date, "AND");
            }
            else
            {
                $query .= $this->build_date_query($check_date, $from_date, $to_date);    
            }
            if(strpos($query, "WHERE") !== false){
                $query .= " AND leads.is_trash = 0";
            }
            else{
                $query .= " WHERE leads.is_trash = 0";
            }

            $query .= " group by leads.lead_status_id";
            
            $rows = $this->wpdb->get_results($query);

            $i = 0;
            foreach ($UpiCRMLeadsStatus->get_as_array() as $key => $value) {
                $arr[$i]['lead_status_id'] = $key;
                $arr[$i]['lead_status_name'] = $value;
                $arr[$i]['count'] = 0;
                $arr[$i]['color'] = $color[$i];
                foreach ($rows as $row) {
                    if ($row->lead_status_id == $key) {
                        $arr[$i]['count'] = $row->count;
                    }
                }
                $i++;
            }
            return $arr;
        }

        function get_total_leads_assigned_by_user_id($user_id = 0, $check_date = 0, $from_date = 0, $to_date = 0) {
            $UpiCRMUsers = new UpiCRMUsers();
            $color = $this->color_array();


            $query = "SELECT count(*) AS `count` ,lead_users.user_id FROM " . upicrm_db() . "lead_users as lead_users";

            $query .= " LEFT JOIN " . upicrm_db() . "leads as leads";
            $query .= " ON lead_users.lead_id = leads.lead_id";

            if ($user_id > 0) {
                $query .= " WHERE lead_users.user_id = {$user_id} ";
                // foreach ($UpiCRMUsers->get_childrens_by_parent_id($user_id) as $obj) {
                //     $query .= "OR lead_users.user_id = {$obj->user_id} ";
                // }
                // $query .= ')';
                $query .= $this->build_date_query_lead_user($check_date, $from_date, $to_date, "AND");
            }
            else
            {
                $query .= $this->build_date_query_lead_user($check_date, $from_date, $to_date);    
            }

            if(strpos($query, "WHERE") !== false){
                $query .= " AND leads.is_trash = 0";
            }
            else{
                $query .= " WHERE leads.is_trash = 0";
            }

            $query .= " group by lead_users.user_id";
            
            $rows = $this->wpdb->get_results($query);

            $i = 0;
            $users_ARR = $UpiCRMUsers->get_as_array();

            if ($user_id > 0) {
                foreach ($users_ARR as $key => $value) {
                    $delete = true;
                    foreach ($rows as $row) {
                        if ($row->user_id == $key) {
                            $delete = false;
                        }
                    }
                    if ($delete) {
                        unset($users_ARR[$key]);
                    }
                }
            }

            foreach ($users_ARR as $key => $value) {
                $arr[$i]['user_id'] = $key;
                $arr[$i]['user_name'] = $value;
                $arr[$i]['count'] = 0;
                $arr[$i]['color'] = $color[$i];
                foreach ($rows as $row) {
                    if ($row->user_id == $key) {
                        $arr[$i]['count'] = $row->count;
                    }
                }
                $i++;
            }

            return $arr ? $arr : array();
        }

        function get_total_closed_leads_by_user_id($user_id = 0, $check_date = 0, $status = 0, $from_date = 0, $to_date = 0) {
            $UpiCRMUsers = new UpiCRMUsers();
            $color = $this->color_array();


            $query = "SELECT count(*) AS `count` ,lead_users.user_id FROM " . upicrm_db() . "lead_users as lead_users";

            $query .= " LEFT JOIN " . upicrm_db() . "leads as leads";
            $query .= " ON lead_users.lead_id = leads.lead_id";

            if ($user_id > 0) {
                $query .= " WHERE lead_users.user_id = {$user_id} ";
                // foreach ($UpiCRMUsers->get_childrens_by_parent_id($user_id) as $obj) {
                //     $query .= "OR lead_users.user_id = {$obj->user_id} ";
                // }
                // $query .= ')';
                $query .= $this->build_date_query_lead_user($check_date, $from_date, $to_date, "AND");
            }
            else
            {
                $query .= $this->build_date_query_lead_user($check_date, $from_date, $to_date);    
            }
            if($status > 0)
            {
                $query .= " AND leads.lead_status_id = {$status} ";
                // if ($user_id > 0) {
                //     $query .= " AND leads.lead_status_id = {$status} ";
                // }
                // else{
                //     $query .= " WHERE leads.lead_status_id = {$status} ";
                // }
            }
            if(strpos($query, "WHERE") !== false){
                $query .= " AND leads.is_trash = 0";
            }
            else{
                $query .= " WHERE leads.is_trash = 0";
            }
            $query .= " group by lead_users.user_id";
            
            $rows = $this->wpdb->get_results($query);

            $i = 0;
            $users_ARR = $UpiCRMUsers->get_as_array();

            if ($user_id > 0) {
                foreach ($users_ARR as $key => $value) {
                    $delete = true;
                    foreach ($rows as $row) {
                        if ($row->user_id == $key) {
                            $delete = false;
                        }
                    }
                    if ($delete) {
                        unset($users_ARR[$key]);
                    }
                }
            }

            foreach ($users_ARR as $key => $value) {
                $arr[$i]['user_id'] = $key;
                $arr[$i]['user_name'] = $value;
                $arr[$i]['count'] = 0;
                $arr[$i]['color'] = $color[$i];
                foreach ($rows as $row) {
                    if ($row->user_id == $key) {
                        $arr[$i]['count'] = $row->count;
                    }
                }
                $i++;
            }

            return $arr ? $arr : array();
        }
        function get_all_active_forms_lead_count( $user_id = 0, $check_date = 0, $from_date = 0, $to_date = 0 )
        {
            $UpiCRMLeads = new UpiCRMLeads();
            $rows = $this->wpdb->get_results("SELECT DISTINCT source_id,source_type FROM `" . upicrm_db() . "fields_mapping` GROUP BY source_id,source_type");
            $arr = array();
            if($rows)
            {   
                $i = 0;
                foreach ($rows as $row) {
                    $query = "SELECT count(*) AS `count` FROM " . upicrm_db() . "leads as leads";
                    if ($user_id > 0) {
                        $query .= " WHERE leads.user_id = {$user_id} ";
                        // foreach ($UpiCRMUsers->get_childrens_by_parent_id($user_id) as $obj) {
                        //     $query .= "OR lead_users.user_id = {$obj->user_id} ";
                        // }
                        // $query .= ')';
                        $query .= $this->build_date_query($check_date, $from_date, $to_date, "AND");
                    }
                    else
                    {
                        $query .= $this->build_date_query_form($check_date, $from_date, $to_date);    
                    }
                    $query .= " AND source_type = {$row->source_type} AND source_id = {$row->source_id}";

                    if(strpos($query, "WHERE") !== false){
                        $query .= " AND leads.is_trash = 0";
                    }
                    else{
                        $query .= " WHERE leads.is_trash = 0";
                    }
                    
                    $leadRow = $this->wpdb->get_results($query);
                    $formName = $UpiCRMLeads->get_source_form_name($row->source_id, $row->source_type);
                    foreach ($leadRow as $leadrow) {
                            $arr[$i]['form_name'] = $formName;
                            $arr[$i]['count'] = $leadrow->count;
                    }
                    $i++;
                }
            }

            return $arr;
            
        }

        function get_total_leads_group_field_by_user_id($user_id = 0, $field_id, $check_date = 0, $from_date = 0, $to_date = 0) {
            $UpiCRMUIBuilder = new UpiCRMUIBuilder();
            $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
            $UpiCRMLeads = new UpiCRMLeads();
            $UpiCRMUsers = new UpiCRMUsers();
            $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
            $get_content = array();

            $getNamesMap = $UpiCRMFieldsMapping->get();
            $list_option = $UpiCRMUIBuilder->get_list_option();
            $getLeads = $UpiCRMLeads->get($user_id, '', '', '', $check_date, $from_date, $to_date);

            foreach ($getLeads as $leadObj) {
                foreach ($list_option as $key => $arr) {
                    foreach ($arr as $key2 => $value) {
                        if ($key == "content" && $key2 == $field_id) {
                            $get_content[] = $UpiCRMUIBuilder->lead_routing($leadObj, $key, $key2, $getNamesMap, true);
                        }
                    }
                }
            }
            $return = array();
            foreach ($get_content as $content) {
                $is_exist = false;
                foreach ($return as $key => $value) {
                    if (strtoupper($key) == strtoupper($content)) {
                        $is_exist = true;
                        break;
                    }
                }
                if ($is_exist) {
                    $return[strtoupper($content)] ++;
                } else {
                    $return[strtoupper($content)] = 1;
                }
            }
            unset($return['']);
            return $return;
        }

        function get_total_leads_group_field_name_by_user_id($user_id = 0, $field_name, $check_date = 0, $from_date = 0, $to_date = 0) {
            $UpiCRMUIBuilder = new UpiCRMUIBuilder();
            $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
            $UpiCRMLeads = new UpiCRMLeads();
            $UpiCRMUsers = new UpiCRMUsers();
            $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
            $UpiCRMFields = new UpiCRMFields();
            $get_content = array();

            $field_id = $UpiCRMFields->get_id_by_name($field_name);
            $getNamesMap = $UpiCRMFieldsMapping->get();
            $list_option = $UpiCRMUIBuilder->get_list_option();
            $getLeads = $UpiCRMLeads->get($user_id, '', '', '', $check_date, $from_date, $to_date);

            foreach ($getLeads as $leadObj) {
                foreach ($list_option as $key => $arr) {
                    foreach ($arr as $key2 => $value) {
                        if ($key == "content" && $key2 == $field_id) {
                            $get_content[] = $UpiCRMUIBuilder->lead_routing($leadObj, $key, $key2, $getNamesMap, true);
                        }
                    }
                }
            }
            $return = array();
            foreach ($get_content as $content) {
                $is_exist = false;
                foreach ($return as $key => $value) {
                    if (strtoupper($key) == strtoupper($content)) {
                        $is_exist = true;
                        break;
                    }
                }
                if ($is_exist) {
                    $return[strtoupper($content)] ++;
                } else {
                    $return[strtoupper($content)] = 1;
                }
            }
            unset($return['']);
            return $return;
        }

        function get_total_leads_by_weeks($week = 0) {
            if ($week == 0) {
                //$saturday = date("Y-m-d",strtotime('last saturday'));
                $weekAgo = date("Y-m-d", strtotime('-7 days'));
                $query = "SELECT count(*) AS `count` FROM " . upicrm_db() . "leads 
            WHERE (`time` BETWEEN '{$weekAgo}' AND NOW())";
            } else {
                $star_week = $week - 1;
                $weekAgo = date("Y-m-d", strtotime("-{$star_week} weeks"));
                $weekAgo2 = date("Y-m-d", strtotime("-{$week} weeks"));

                $query = "SELECT count(*) AS `count` FROM " . upicrm_db() . "leads 
            WHERE (`time` BETWEEN '{$weekAgo2}' AND '{$weekAgo}')";
            }
            /* echo $query;
              echo "<br />"; */
            $rows = $this->wpdb->get_results($query);
            return $rows[0]->count ? $rows[0]->count : 0;
        }

        function color_array() {
            return array("blue", "red", "green", "orange", "yellow", "pink", "purple", "greenLight", "greenDark", "orangeDark", '#885886', '#578C28', '#1A5665', '#578C28', '#314788', '#314788', '#4255B3', '#5C6B3F', '#AD3598');
        }

        function build_date_query($check_date = 0, $from_date = 0, $to_date = 0, $opr = "WHERE") {
            $query = "";
            if ($check_date > 0) {
                $query .= " {$opr} leads.time > DATE_SUB(CURDATE(), INTERVAL {$check_date} DAY)";
            }
            if ($check_date === "custom") {
                $query .= " {$opr} ";
                if ($from_date > 0) {
                    $query .= "leads.time >= CAST('{$from_date}' AS DATE)";
                }
                if ($from_date > 0 && $to_date > 0) {
                    $query .= " AND ";
                }
                if ($to_date > 0) {
                    $query .= "leads.time <= CAST('{$to_date}' AS DATE)";
                }
            }

            return $query;
        }

        function build_date_query_form($check_date = 0, $from_date = 0, $to_date = 0, $opr = "WHERE") {
            $query = "";
            if ($check_date > 0) {
                $query .= " {$opr} leads.time > DATE_SUB(CURDATE(), INTERVAL {$check_date} DAY)";
            }
            if ($check_date === "custom") {
                $query .= " {$opr} ";
                if ($from_date > 0) {
                    $query .= "leads.time >= CAST('{$from_date}' AS DATE)";
                }
                if ($from_date > 0 && $to_date > 0) {
                    $query .= " AND ";
                }
                if ($to_date > 0) {
                    $query .= "leads.time <= CAST('{$to_date}' AS DATE)";
                }
            }
            if(empty($query))
            {
                $query = " {$opr} 1 = 1 ";
            }

            return $query;
        }

        function build_date_query_lead_user($check_date = 0, $from_date = 0, $to_date = 0, $opr = "WHERE") {
            $query = "";
            if ($check_date > 0) {
                $query .= " {$opr} leads.time > DATE_SUB(CURDATE(), INTERVAL {$check_date} DAY)";
            }
            if ($check_date === "custom") {
                $query .= " {$opr} ";
                if ($from_date > 0) {
                    $query .= "leads.time >= CAST('{$from_date}' AS DATE)";
                }
                if ($from_date > 0 && $to_date > 0) {
                    $query .= " AND ";
                }
                if ($to_date > 0) {
                    $query .= "leads.time <= CAST('{$to_date}' AS DATE)";
                }
            }
            
            if(empty($query))
            {
                $query = " {$opr} 1 = 1 ";
            }
            
            return $query;
        }
        

    }

}